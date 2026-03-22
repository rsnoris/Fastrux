<?php
/**
 * Fastrux — Wallet Data API
 *
 * GET  ?action=balance&user_id=USR-XXXXXXXX
 *   → { success, balance, transactions[] }
 *
 * POST action=add_funds
 *   body: user_id, amount, description, card_name, card_last4, card_expiry, billing_address
 *   → { success, message, balance, transactions[], payment_id }
 */

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
// Restrict CORS to same origin — wallet/payment APIs must not be callable cross-site (PCI-DSS Req 6.4)
$allowedOrigin = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? '');
header('Access-Control-Allow-Origin: ' . $allowedOrigin);
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

define('DATA_DIR', __DIR__ . '/data/');
define('PAYMENTS_JSON', DATA_DIR . 'payments.json');

require_once __DIR__ . '/audit_helper.php';

function respond(bool $success, string $message, array $extra = []): void
{
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit;
}

function clean(string $value): string
{
    return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
}

/**
 * Return the path to a user's wallet file, creating the wallets directory if needed.
 */
function walletPath(string $safeUserId): string
{
    $dir = DATA_DIR . 'wallets/';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    return $dir . $safeUserId . '.json';
}

/**
 * Load a user's wallet, returning defaults if none exists.
 */
function loadWallet(string $safeUserId): array
{
    $path = walletPath($safeUserId);
    if (file_exists($path)) {
        $data = json_decode(file_get_contents($path), true);
        if (is_array($data)) {
            return $data;
        }
    }
    return [
        'user_id'      => $safeUserId,
        'balance'      => 0.00,
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
 * Validate and sanitize a user ID — must match USR-XXXXXXXX.
 * Returns the safe user ID or empty string if invalid.
 */
function sanitizeUserId(string $raw): string
{
    $trimmed = trim($raw);
    // Accept USR- prefix followed by 1-16 alphanumeric/dash/underscore characters
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
    if (!is_dir(DATA_DIR)) {
        mkdir(DATA_DIR, 0755, true);
    }
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
}

// ── Route ─────────────────────────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action    = clean($_GET['action']  ?? '');
    $rawUserId = $_GET['user_id'] ?? '';
    $userId    = sanitizeUserId($rawUserId);

    if ($action !== 'balance') {
        respond(false, 'Unknown action.');
    }
    if (!$userId) {
        respond(false, 'A valid user_id is required.');
    }

    $wallet = loadWallet($userId);
    respond(true, 'OK', [
        'balance'      => round((float)($wallet['balance'] ?? 0), 2),
        'transactions' => $wallet['transactions'] ?? [],
    ]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action    = clean($_POST['action']  ?? '');
    $rawUserId = $_POST['user_id'] ?? '';
    $userId    = sanitizeUserId($rawUserId);

    if (!$userId) {
        respond(false, 'A valid user_id is required.');
    }

    if ($action === 'add_funds') {
        $rawAmount   = $_POST['amount']      ?? '';
        $description = clean($_POST['description'] ?? '');

        // Validate amount
        if (!is_numeric($rawAmount)) {
            respond(false, 'Amount must be a number.');
        }
        $amount = round((float)$rawAmount, 2);
        if ($amount <= 0) {
            respond(false, 'Amount must be greater than zero.');
        }
        if ($amount > 10000) {
            respond(false, 'Maximum single deposit is $10,000.');
        }

        // ── Validate card payment details ─────────────────────────────────────

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

        // ── Generate a unique payment ID ──────────────────────────────────────

        $payments    = readJson(PAYMENTS_JSON);
        $existingIds = array_column($payments, 'id');
        do {
            $paymentId = 'PAY-' . strtoupper(bin2hex(random_bytes(8)));
        } while (in_array($paymentId, $existingIds, true));

        // ── Record payment ────────────────────────────────────────────────────

        $payment = [
            'id'              => $paymentId,
            'type'            => 'wallet_topup',
            'user_id'         => $userId,
            'amount'          => $amount,
            'currency'        => 'USD',
            'payment_method'  => 'card',
            'card_name'       => $cardName,
            'card_last4'      => $cardLast4,
            // card_expiry is NOT stored post-authorisation (PCI-DSS Req 3.3 — minimise stored cardholder data)
            'billing_address' => $billingAddress,
            'status'          => 'completed',
            'created_at'      => date('Y-m-d H:i:s'),
        ];
        $payments[] = $payment;
        writeJson(PAYMENTS_JSON, $payments);

        // ── Credit wallet ─────────────────────────────────────────────────────

        $wallet = loadWallet($userId);

        $txId = 'TXN-' . strtoupper(bin2hex(random_bytes(4)));
        $transaction = [
            'id'          => $txId,
            'type'        => 'deposit',
            'amount'      => $amount,
            'description' => $description ?: "Card deposit (**** {$cardLast4})",
            'reference'   => $paymentId,
            'timestamp'   => date('Y-m-d H:i:s'),
        ];

        $wallet['balance']        = round((float)($wallet['balance'] ?? 0) + $amount, 2);
        $wallet['transactions'][] = $transaction;

        // Cap transaction history at 500 entries
        if (count($wallet['transactions']) > 500) {
            $wallet['transactions'] = array_slice($wallet['transactions'], -500);
        }

        saveWallet($userId, $wallet);
        auditLog('wallet.funds_added', $userId, 'wallet', $userId, "Added \${$amount} to wallet via card **** {$cardLast4} for user {$userId} (payment {$paymentId})");

        respond(true, 'Funds added successfully.', [
            'balance'      => $wallet['balance'],
            'transactions' => $wallet['transactions'],
            'payment_id'   => $paymentId,
        ]);
    }

    if ($action === 'withdraw') {
        $rawAmount   = $_POST['amount']      ?? '';
        $description = clean($_POST['description'] ?? '');

        if (!is_numeric($rawAmount)) {
            respond(false, 'Amount must be a number.');
        }
        $amount = round((float)$rawAmount, 2);
        if ($amount <= 0) {
            respond(false, 'Amount must be greater than zero.');
        }

        $wallet = loadWallet($userId);
        $balance = round((float)($wallet['balance'] ?? 0), 2);

        if ($balance < $amount) {
            respond(false, sprintf(
                'Insufficient balance. Available: $%.2f, Requested: $%.2f.',
                $balance,
                $amount
            ));
        }

        $txId = 'TXN-' . strtoupper(bin2hex(random_bytes(4)));
        $transaction = [
            'id'          => $txId,
            'type'        => 'withdrawal',
            'amount'      => $amount,
            'description' => $description ?: 'Funds withdrawn',
            'timestamp'   => date('Y-m-d H:i:s'),
        ];

        $wallet['balance']        = round($balance - $amount, 2);
        $wallet['transactions'][] = $transaction;

        if (count($wallet['transactions']) > 500) {
            $wallet['transactions'] = array_slice($wallet['transactions'], -500);
        }

        saveWallet($userId, $wallet);
        auditLog('wallet.funds_withdrawn', $userId, 'wallet', $userId, "Withdrew \${$amount} from wallet for user {$userId}");

        respond(true, 'Withdrawal successful.', [
            'balance'      => $wallet['balance'],
            'transactions' => $wallet['transactions'],
        ]);
    }

    respond(false, 'Unknown action.');
}

respond(false, 'Method not allowed.');
