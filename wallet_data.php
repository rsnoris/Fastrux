<?php
/**
 * Fastrux — Wallet Data API  (production-grade digital wallet)
 *
 * ─── GET endpoints ───────────────────────────────────────────────────────────
 *  ?action=balance&user_id=USR-XXXX
 *      → { success, wallet_id, currency, balance, status, transactions[] }
 *
 *  ?action=get_wallet&user_id=USR-XXXX
 *      → { success, wallet }
 *
 *  ?action=list_transactions&user_id=USR-XXXX[&limit=50&offset=0]
 *      → { success, transactions[], total }
 *
 * ─── POST endpoints ──────────────────────────────────────────────────────────
 *  action=create_wallet
 *      body: user_id, currency (USD|EUR|GBP|CAD), [tenant_id]
 *      → { success, wallet }
 *
 *  action=add_funds  (deposit)
 *      body: user_id, amount, description, card_name, card_last4,
 *            card_expiry, billing_address, [idempotency_key]
 *      → { success, balance, transactions[], payment_id }
 *
 *  action=withdraw
 *      body: user_id, amount, description, bank_account_last4,
 *            bank_routing, [idempotency_key]
 *      → { success, balance, transactions[] }
 *
 *  action=transfer
 *      body: from_user_id, to_user_id, amount, description, [idempotency_key]
 *      → { success, balance, transaction_id }
 *
 *  action=freeze_wallet
 *      body: user_id, reason
 *      → { success, status }
 *
 *  action=unfreeze_wallet
 *      body: user_id, reason
 *      → { success, status }
 *
 *  action=close_wallet
 *      body: user_id, reason
 *      → { success, status }
 *
 * ─── Security / PCI-DSS notes ────────────────────────────────────────────────
 *  • Raw card PANs and CVVs are never accepted — only last-4 digits & cardholder name
 *  • card_expiry is validated in memory and never persisted (PCI-DSS Req 3.3)
 *  • Idempotency keys prevent duplicate transactions
 *  • All financial mutations are recorded in the double-entry ledger
 *  • CORS restricted to same origin (PCI-DSS Req 6.4)
 */

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
// Restrict CORS to same origin — wallet/payment APIs must not be callable cross-site (PCI-DSS Req 6.4)
$allowedOrigin = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? '');
header('Access-Control-Allow-Origin: ' . $allowedOrigin);
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Idempotency-Key');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

define('DATA_DIR',            __DIR__ . '/data/');
define('PAYMENTS_JSON',       DATA_DIR . 'payments.json');
define('WALLETS_DIR',         DATA_DIR . 'wallets/');
define('LEDGER_JSON',         DATA_DIR . 'ledger_entries.json');
define('IDEMPOTENCY_JSON',    DATA_DIR . 'idempotency_keys.json');
define('MAX_DEPOSIT_AMOUNT',  10000.00);
define('MAX_TRANSFER_AMOUNT', 50000.00);
define('RATE_LIMIT_WINDOW',   60);    // seconds
define('RATE_LIMIT_MAX_OPS',  20);    // max operations per window per user

require_once __DIR__ . '/audit_helper.php';

// ── Helpers ──────────────────────────────────────────────────────────────────

function respond(bool $success, string $message, array $extra = []): void
{
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit;
}

function clean(string $value): string
{
    return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
}

function sanitizeUserId(string $raw): string
{
    $trimmed = trim($raw);
    if (preg_match('/^USR-[A-Za-z0-9_\-]{1,16}$/', $trimmed)) {
        return $trimmed;
    }
    return '';
}

function readJson(string $file): array
{
    if (!file_exists($file)) {
        return [];
    }
    $raw  = file_get_contents($file);
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function writeJson(string $file, array $data): void
{
    $dir = dirname($file);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
}

/**
 * Return the path to a user's wallet file, creating the directory if needed.
 */
function walletPath(string $safeUserId): string
{
    if (!is_dir(WALLETS_DIR)) {
        mkdir(WALLETS_DIR, 0755, true);
    }
    return WALLETS_DIR . $safeUserId . '.json';
}

/**
 * Load a user's wallet.  Returns null if the wallet does not exist yet.
 */
function loadWallet(string $safeUserId): ?array
{
    $path = walletPath($safeUserId);
    if (file_exists($path)) {
        $data = json_decode(file_get_contents($path), true);
        if (is_array($data)) {
            return $data;
        }
    }
    return null;
}

/**
 * Create a brand-new wallet record for a user.
 */
function createWalletRecord(string $safeUserId, string $currency = 'USD', string $tenantId = ''): array
{
    return [
        'wallet_id'    => 'WAL-' . strtoupper(bin2hex(random_bytes(6))),
        'user_id'      => $safeUserId,
        'currency'     => $currency,
        'balance'      => 0.00,
        'status'       => 'active',   // active | frozen | closed
        'tenant_id'    => $tenantId,
        'transactions' => [],
        'created_at'   => date('Y-m-d H:i:s'),
        'updated_at'   => date('Y-m-d H:i:s'),
    ];
}

/**
 * Save a wallet to disk atomically (LOCK_EX prevents concurrent-write corruption).
 */
function saveWallet(string $safeUserId, array $wallet): void
{
    $wallet['updated_at'] = date('Y-m-d H:i:s');
    file_put_contents(
        walletPath($safeUserId),
        json_encode($wallet, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        LOCK_EX
    );
}

/**
 * Append a double-entry ledger row.
 * Each financial event produces two entries: one debit and one credit.
 *
 * @param string $entryType  credit | debit
 * @param string $account    e.g. 'user:USR-XXX', 'system:float', 'system:external'
 * @param float  $amount
 * @param string $currency
 * @param string $txId       Reference transaction ID
 * @param string $description
 */
function appendLedgerEntry(
    string $entryType,
    string $account,
    float  $amount,
    string $currency,
    string $txId,
    string $description
): void {
    $entries   = readJson(LEDGER_JSON);
    $entries[] = [
        'id'          => 'LED-' . strtoupper(bin2hex(random_bytes(6))),
        'type'        => $entryType,   // debit | credit
        'account'     => $account,
        'amount'      => round($amount, 2),
        'currency'    => $currency,
        'tx_ref'      => $txId,
        'description' => $description,
        'timestamp'   => date('Y-m-d H:i:s'),
    ];
    writeJson(LEDGER_JSON, $entries);
}

/**
 * Write both sides of a double-entry accounting record.
 *
 * Convention:
 *   debit  = money leaving the account (funds moving out)
 *   credit = money entering the account (funds moving in)
 */
function recordDoubleEntry(
    string $debitAccount,
    string $creditAccount,
    float  $amount,
    string $currency,
    string $txId,
    string $description
): void {
    appendLedgerEntry('debit',  $debitAccount,  $amount, $currency, $txId, $description);
    appendLedgerEntry('credit', $creditAccount, $amount, $currency, $txId, $description);
}

/**
 * Idempotency key check/store.
 * Returns null if the key is new; returns the cached response array if seen before.
 */
function checkIdempotency(string $key): ?array
{
    if ($key === '') {
        return null;
    }
    $store = readJson(IDEMPOTENCY_JSON);
    // Clean up entries older than 24 hours
    $cutoff = date('Y-m-d H:i:s', strtotime('-24 hours'));
    $store  = array_values(array_filter($store, function ($e) use ($cutoff): bool {
        return ($e['created_at'] ?? '') >= $cutoff;
    }));
    foreach ($store as $entry) {
        if (($entry['key'] ?? '') === $key) {
            return $entry['response'] ?? [];
        }
    }
    return null;
}

function storeIdempotency(string $key, array $response): void
{
    if ($key === '') {
        return;
    }
    $store  = readJson(IDEMPOTENCY_JSON);
    $cutoff = date('Y-m-d H:i:s', strtotime('-24 hours'));
    $store  = array_values(array_filter($store, function ($e) use ($cutoff): bool {
        return ($e['created_at'] ?? '') >= $cutoff;
    }));
    $store[] = [
        'key'        => $key,
        'response'   => $response,
        'created_at' => date('Y-m-d H:i:s'),
    ];
    writeJson(IDEMPOTENCY_JSON, $store);
}

/**
 * Simple per-user rate limiting using a JSON store.
 * Returns true when the caller is within the allowed rate.
 */
function checkRateLimit(string $userId): bool
{
    $rateFile = DATA_DIR . 'rate_limits.json';
    $store    = readJson($rateFile);
    $now      = time();
    $window   = $now - RATE_LIMIT_WINDOW;

    // Filter to the current window for this user
    $userOps = array_filter(
        $store[$userId] ?? [],
        function (int $ts) use ($window): bool { return $ts >= $window; }
    );

    if (count($userOps) >= RATE_LIMIT_MAX_OPS) {
        return false;
    }

    $userOps[] = $now;
    $store[$userId] = array_values($userOps);
    writeJson($rateFile, $store);
    return true;
}

/** Validate and normalise a supported currency code. */
function validCurrency(string $raw): string
{
    $upper = strtoupper(trim($raw));
    return in_array($upper, ['USD', 'EUR', 'GBP', 'CAD'], true) ? $upper : '';
}

// ── GET ───────────────────────────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action    = clean($_GET['action']  ?? '');
    $rawUserId = $_GET['user_id'] ?? '';
    $userId    = sanitizeUserId($rawUserId);

    if (!in_array($action, ['balance', 'get_wallet', 'list_transactions'], true)) {
        respond(false, 'Unknown action.');
    }
    if (!$userId) {
        respond(false, 'A valid user_id is required.');
    }

    $wallet = loadWallet($userId);
    if ($wallet === null) {
        // Auto-provision a USD wallet on first read (backward-compatible behaviour)
        $wallet = createWalletRecord($userId, 'USD');
        saveWallet($userId, $wallet);
    }

    if ($action === 'balance') {
        respond(true, 'OK', [
            'wallet_id'    => $wallet['wallet_id']  ?? '',
            'currency'     => $wallet['currency']   ?? 'USD',
            'balance'      => round((float)($wallet['balance'] ?? 0), 2),
            'status'       => $wallet['status']     ?? 'active',
            'transactions' => $wallet['transactions'] ?? [],
        ]);
    }

    if ($action === 'get_wallet') {
        $safe = $wallet;
        respond(true, 'OK', ['wallet' => $safe]);
    }

    if ($action === 'list_transactions') {
        $limit  = max(1, min(200, (int)($_GET['limit']  ?? 50)));
        $offset = max(0, (int)($_GET['offset'] ?? 0));
        $all    = array_reverse($wallet['transactions'] ?? []);
        $total  = count($all);
        $page   = array_values(array_slice($all, $offset, $limit));
        respond(true, 'OK', ['transactions' => $page, 'total' => $total]);
    }
}

// ── POST ──────────────────────────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action    = clean($_POST['action']  ?? '');
    $rawUserId = $_POST['user_id'] ?? '';
    $userId    = sanitizeUserId($rawUserId);

    // Idempotency key (optional; from header or body)
    $idempotencyKey = clean(
        $_SERVER['HTTP_X_IDEMPOTENCY_KEY'] ?? ($_POST['idempotency_key'] ?? '')
    );
    if ($idempotencyKey !== '') {
        // Sanitise: allow only printable ASCII, max 128 chars
        $idempotencyKey = substr(preg_replace('/[^\x20-\x7E]/', '', $idempotencyKey), 0, 128);
    }

    // ── create_wallet does not require an existing user ID ────────────────────
    if ($action === 'create_wallet') {
        if (!$userId) {
            respond(false, 'A valid user_id is required.');
        }

        // Idempotency check
        if ($idempotencyKey !== '') {
            $cached = checkIdempotency($idempotencyKey);
            if ($cached !== null) {
                respond(true, 'Idempotent replay.', $cached);
            }
        }

        $rawCurrency = $_POST['currency'] ?? 'USD';
        $currency    = validCurrency($rawCurrency);
        if (!$currency) {
            respond(false, 'Unsupported currency. Allowed: USD, EUR, GBP, CAD.');
        }
        $tenantId = clean($_POST['tenant_id'] ?? '');

        // Check if wallet already exists
        $existing = loadWallet($userId);
        if ($existing !== null) {
            respond(false, 'A wallet already exists for this user.', ['wallet' => $existing]);
        }

        $wallet = createWalletRecord($userId, $currency, $tenantId);
        saveWallet($userId, $wallet);
        auditLog('wallet.created', $userId, 'wallet', $wallet['wallet_id'], "Wallet {$wallet['wallet_id']} created for user {$userId} ({$currency})");

        $responseExtra = ['wallet' => $wallet];
        storeIdempotency($idempotencyKey, $responseExtra);
        respond(true, 'Wallet created successfully.', $responseExtra);
    }

    if (!$userId) {
        respond(false, 'A valid user_id is required.');
    }

    // Rate limiting — applied to all mutating operations
    if (!checkRateLimit($userId)) {
        http_response_code(429);
        respond(false, 'Too many requests. Please try again shortly.');
    }

    // Load wallet (must exist for all remaining actions except create_wallet)
    $wallet = loadWallet($userId);
    if ($wallet === null) {
        // Auto-provision on first POST (backward-compatible)
        $wallet = createWalletRecord($userId, 'USD');
        saveWallet($userId, $wallet);
    }

    // ── add_funds (deposit) ───────────────────────────────────────────────────
    if ($action === 'add_funds') {
        // Idempotency check
        if ($idempotencyKey !== '') {
            $cached = checkIdempotency($idempotencyKey);
            if ($cached !== null) {
                respond(true, 'Idempotent replay.', $cached);
            }
        }

        if (($wallet['status'] ?? 'active') !== 'active') {
            respond(false, 'Wallet is not active. Deposits are not permitted.');
        }

        $rawAmount   = $_POST['amount']      ?? '';
        $description = clean($_POST['description'] ?? '');

        if (!is_numeric($rawAmount)) {
            respond(false, 'Amount must be a number.');
        }
        $amount = round((float)$rawAmount, 2);
        if ($amount <= 0) {
            respond(false, 'Amount must be greater than zero.');
        }
        if ($amount > MAX_DEPOSIT_AMOUNT) {
            respond(false, sprintf('Maximum single deposit is $%.2f.', MAX_DEPOSIT_AMOUNT));
        }

        // ── Validate tokenised card details (no raw PAN/CVV accepted) ─────────
        $cardName       = clean($_POST['card_name']       ?? '');
        $cardLast4      = clean($_POST['card_last4']      ?? '');
        $cardExpiry     = clean($_POST['card_expiry']     ?? '');
        $billingAddress = clean($_POST['billing_address'] ?? '');

        if (!$cardName) {
            respond(false, 'Cardholder name is required.');
        }
        if (!preg_match('/^\d{4}$/', $cardLast4)) {
            respond(false, 'card_last4 must be exactly 4 digits.');
        }
        if (!preg_match('/^\d{2}\/\d{2}$/', $cardExpiry)) {
            respond(false, 'card_expiry must be in MM/YY format.');
        }
        [$expMm, $expYy] = explode('/', $cardExpiry);
        $expMm   = (int)$expMm;
        $expYy   = (int)$expYy;
        $expYear = $expYy < 50 ? 2000 + $expYy : 2050 + ($expYy - 50);
        $nowYear = (int)date('Y');
        $nowMon  = (int)date('n');
        if ($expMm < 1 || $expMm > 12 || $expYear < $nowYear || ($expYear === $nowYear && $expMm < $nowMon)) {
            respond(false, 'The card expiry date is invalid or the card has expired.');
        }
        if (!$billingAddress) {
            respond(false, 'Billing address is required.');
        }

        // ── Generate unique payment ID ────────────────────────────────────────
        $payments    = readJson(PAYMENTS_JSON);
        $existingIds = array_column($payments, 'id');
        do {
            $paymentId = 'PAY-' . strtoupper(bin2hex(random_bytes(8)));
        } while (in_array($paymentId, $existingIds, true));

        // ── Record payment (tokenised details only — PCI-DSS Req 3.3) ─────────
        $payment = [
            'id'              => $paymentId,
            'type'            => 'wallet_topup',
            'user_id'         => $userId,
            'amount'          => $amount,
            'currency'        => $wallet['currency'] ?? 'USD',
            'payment_method'  => 'card',
            'card_name'       => $cardName,
            'card_last4'      => $cardLast4,
            // card_expiry is NOT stored post-authorisation (PCI-DSS Req 3.3)
            'billing_address' => $billingAddress,
            'status'          => 'completed',
            'created_at'      => date('Y-m-d H:i:s'),
        ];
        $payments[] = $payment;
        writeJson(PAYMENTS_JSON, $payments);

        // ── Credit wallet ─────────────────────────────────────────────────────
        $txId = 'TXN-' . strtoupper(bin2hex(random_bytes(6)));
        $transaction = [
            'id'          => $txId,
            'type'        => 'deposit',
            'amount'      => $amount,
            'currency'    => $wallet['currency'] ?? 'USD',
            'description' => $description ?: "Card deposit (**** {$cardLast4})",
            'reference'   => $paymentId,
            'status'      => 'completed',
            'timestamp'   => date('Y-m-d H:i:s'),
        ];

        $wallet['balance']        = round((float)($wallet['balance'] ?? 0) + $amount, 2);
        $wallet['transactions'][] = $transaction;
        if (count($wallet['transactions']) > 500) {
            $wallet['transactions'] = array_slice($wallet['transactions'], -500);
        }
        saveWallet($userId, $wallet);

        // ── Double-entry ledger: debit external/card, credit user wallet ───────
        $currency = $wallet['currency'] ?? 'USD';
        recordDoubleEntry(
            'system:external_card',
            "user:{$userId}",
            $amount,
            $currency,
            $txId,
            "Deposit via card **** {$cardLast4}"
        );

        auditLog(
            'wallet.funds_added',
            $userId,
            'wallet',
            $userId,
            "Added \${$amount} to wallet via card **** {$cardLast4} (payment {$paymentId})"
        );

        $responseExtra = [
            'balance'        => $wallet['balance'],
            'currency'       => $currency,
            'transactions'   => $wallet['transactions'],
            'payment_id'     => $paymentId,
            'transaction_id' => $txId,
        ];
        storeIdempotency($idempotencyKey, $responseExtra);
        respond(true, 'Funds added successfully.', $responseExtra);
    }

    // ── withdraw ──────────────────────────────────────────────────────────────
    if ($action === 'withdraw') {
        if ($idempotencyKey !== '') {
            $cached = checkIdempotency($idempotencyKey);
            if ($cached !== null) {
                respond(true, 'Idempotent replay.', $cached);
            }
        }

        if (($wallet['status'] ?? 'active') !== 'active') {
            respond(false, 'Wallet is not active. Withdrawals are not permitted.');
        }

        $rawAmount   = $_POST['amount']      ?? '';
        $description = clean($_POST['description'] ?? '');

        if (!is_numeric($rawAmount)) {
            respond(false, 'Amount must be a number.');
        }
        $amount = round((float)$rawAmount, 2);
        if ($amount <= 0) {
            respond(false, 'Amount must be greater than zero.');
        }

        // ── Bank account details for withdrawal (tokenised — no raw routing stored) ─
        $bankLast4   = clean($_POST['bank_account_last4'] ?? '');
        $bankRouting = clean($_POST['bank_routing']       ?? '');

        if ($bankLast4 !== '' && !preg_match('/^\d{4}$/', $bankLast4)) {
            respond(false, 'bank_account_last4 must be exactly 4 digits.');
        }

        $balance = round((float)($wallet['balance'] ?? 0), 2);
        if ($balance < $amount) {
            respond(false, sprintf(
                'Insufficient balance. Available: $%.2f, Requested: $%.2f.',
                $balance,
                $amount
            ));
        }

        $txId = 'TXN-' . strtoupper(bin2hex(random_bytes(6)));
        $transaction = [
            'id'          => $txId,
            'type'        => 'withdrawal',
            'amount'      => $amount,
            'currency'    => $wallet['currency'] ?? 'USD',
            'description' => $description ?: 'Funds withdrawn' . ($bankLast4 ? " to account **** {$bankLast4}" : ''),
            'status'      => 'completed',
            'timestamp'   => date('Y-m-d H:i:s'),
        ];

        $wallet['balance']        = round($balance - $amount, 2);
        $wallet['transactions'][] = $transaction;
        if (count($wallet['transactions']) > 500) {
            $wallet['transactions'] = array_slice($wallet['transactions'], -500);
        }
        saveWallet($userId, $wallet);

        // ── Double-entry ledger: debit user wallet, credit external/bank ───────
        $currency = $wallet['currency'] ?? 'USD';
        recordDoubleEntry(
            "user:{$userId}",
            'system:external_bank',
            $amount,
            $currency,
            $txId,
            'Withdrawal to bank' . ($bankLast4 ? " **** {$bankLast4}" : '')
        );

        auditLog(
            'wallet.funds_withdrawn',
            $userId,
            'wallet',
            $userId,
            "Withdrew \${$amount} from wallet (tx {$txId})"
        );

        $responseExtra = [
            'balance'        => $wallet['balance'],
            'currency'       => $currency,
            'transactions'   => $wallet['transactions'],
            'transaction_id' => $txId,
        ];
        storeIdempotency($idempotencyKey, $responseExtra);
        respond(true, 'Withdrawal successful.', $responseExtra);
    }

    // ── transfer (P2P) ────────────────────────────────────────────────────────
    if ($action === 'transfer') {
        $fromUserId = sanitizeUserId($_POST['from_user_id'] ?? ($_POST['user_id'] ?? ''));
        $toRaw      = $_POST['to_user_id'] ?? '';
        $toUserId   = sanitizeUserId($toRaw);

        if (!$fromUserId) {
            respond(false, 'A valid from_user_id is required.');
        }
        if (!$toUserId) {
            respond(false, 'A valid to_user_id is required.');
        }
        if ($fromUserId === $toUserId) {
            respond(false, 'Cannot transfer to the same wallet.');
        }

        if ($idempotencyKey !== '') {
            $cached = checkIdempotency($idempotencyKey);
            if ($cached !== null) {
                respond(true, 'Idempotent replay.', $cached);
            }
        }

        $rawAmount   = $_POST['amount']      ?? '';
        $description = clean($_POST['description'] ?? '');

        if (!is_numeric($rawAmount)) {
            respond(false, 'Amount must be a number.');
        }
        $amount = round((float)$rawAmount, 2);
        if ($amount <= 0) {
            respond(false, 'Amount must be greater than zero.');
        }
        if ($amount > MAX_TRANSFER_AMOUNT) {
            respond(false, sprintf('Maximum single transfer is $%.2f.', MAX_TRANSFER_AMOUNT));
        }

        $fromWallet = loadWallet($fromUserId);
        if ($fromWallet === null) {
            respond(false, 'Sender wallet not found.');
        }
        if (($fromWallet['status'] ?? 'active') !== 'active') {
            respond(false, 'Sender wallet is not active.');
        }

        $toWallet = loadWallet($toUserId);
        if ($toWallet === null) {
            respond(false, 'Recipient wallet not found.');
        }
        if (($toWallet['status'] ?? 'active') !== 'active') {
            respond(false, 'Recipient wallet is not active.');
        }

        $fromBalance = round((float)($fromWallet['balance'] ?? 0), 2);
        if ($fromBalance < $amount) {
            respond(false, sprintf(
                'Insufficient balance. Available: $%.2f, Required: $%.2f.',
                $fromBalance,
                $amount
            ));
        }

        $txId    = 'TXN-' . strtoupper(bin2hex(random_bytes(6)));
        $now     = date('Y-m-d H:i:s');
        $desc    = $description ?: "Transfer from {$fromUserId} to {$toUserId}";
        $currency = $fromWallet['currency'] ?? 'USD';

        // Debit sender
        $fromWallet['balance']        = round($fromBalance - $amount, 2);
        $fromWallet['transactions'][] = [
            'id'          => $txId,
            'type'        => 'transfer_out',
            'amount'      => $amount,
            'currency'    => $currency,
            'description' => $desc,
            'to_user_id'  => $toUserId,
            'status'      => 'completed',
            'timestamp'   => $now,
        ];
        if (count($fromWallet['transactions']) > 500) {
            $fromWallet['transactions'] = array_slice($fromWallet['transactions'], -500);
        }
        saveWallet($fromUserId, $fromWallet);

        // Credit recipient
        $toWallet['balance']        = round((float)($toWallet['balance'] ?? 0) + $amount, 2);
        $toWallet['transactions'][] = [
            'id'            => $txId,
            'type'          => 'transfer_in',
            'amount'        => $amount,
            'currency'      => $currency,
            'description'   => $desc,
            'from_user_id'  => $fromUserId,
            'status'        => 'completed',
            'timestamp'     => $now,
        ];
        if (count($toWallet['transactions']) > 500) {
            $toWallet['transactions'] = array_slice($toWallet['transactions'], -500);
        }
        saveWallet($toUserId, $toWallet);

        // ── Double-entry ledger ────────────────────────────────────────────────
        recordDoubleEntry(
            "user:{$fromUserId}",
            "user:{$toUserId}",
            $amount,
            $currency,
            $txId,
            $desc
        );

        auditLog(
            'wallet.transfer',
            $fromUserId,
            'wallet',
            $txId,
            "Transfer \${$amount} from {$fromUserId} to {$toUserId} (tx {$txId})"
        );

        $responseExtra = [
            'transaction_id' => $txId,
            'from_balance'   => $fromWallet['balance'],
            'currency'       => $currency,
        ];
        storeIdempotency($idempotencyKey, $responseExtra);
        respond(true, 'Transfer completed successfully.', $responseExtra);
    }

    // ── freeze_wallet ─────────────────────────────────────────────────────────
    if ($action === 'freeze_wallet') {
        $reason = clean($_POST['reason'] ?? '');
        if (($wallet['status'] ?? 'active') === 'closed') {
            respond(false, 'Wallet is closed and cannot be frozen.');
        }
        $wallet['status'] = 'frozen';
        saveWallet($userId, $wallet);
        auditLog('wallet.frozen', $userId, 'wallet', $userId, "Wallet frozen. Reason: {$reason}");
        respond(true, 'Wallet frozen.', ['status' => 'frozen']);
    }

    // ── unfreeze_wallet ───────────────────────────────────────────────────────
    if ($action === 'unfreeze_wallet') {
        $reason = clean($_POST['reason'] ?? '');
        if (($wallet['status'] ?? '') !== 'frozen') {
            respond(false, 'Wallet is not currently frozen.');
        }
        $wallet['status'] = 'active';
        saveWallet($userId, $wallet);
        auditLog('wallet.unfrozen', $userId, 'wallet', $userId, "Wallet unfrozen. Reason: {$reason}");
        respond(true, 'Wallet unfrozen.', ['status' => 'active']);
    }

    // ── close_wallet ──────────────────────────────────────────────────────────
    if ($action === 'close_wallet') {
        $reason = clean($_POST['reason'] ?? '');
        if (($wallet['status'] ?? '') === 'closed') {
            respond(false, 'Wallet is already closed.');
        }
        if (round((float)($wallet['balance'] ?? 0), 2) > 0) {
            respond(false, 'Cannot close a wallet with a positive balance. Please withdraw funds first.');
        }
        $wallet['status'] = 'closed';
        saveWallet($userId, $wallet);
        auditLog('wallet.closed', $userId, 'wallet', $userId, "Wallet closed. Reason: {$reason}");
        respond(true, 'Wallet closed.', ['status' => 'closed']);
    }

    respond(false, 'Unknown action.');
}

respond(false, 'Method not allowed.');
