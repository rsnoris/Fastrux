<?php
/**
 * Fastrux — Invoice Data API
 *
 * ─── GET endpoints ───────────────────────────────────────────────────────────
 *  ?action=get&invoice_id=INV-XXXX
 *      → { success, invoice }
 *
 *  ?action=list&user_id=USR-XXXX[&role=issuer|payer&status=pending&limit=50&offset=0]
 *      → { success, invoices[], total }
 *
 * ─── POST endpoints ──────────────────────────────────────────────────────────
 *  action=create
 *      body: issuer_user_id, payer_user_id, line_items (JSON array), due_date,
 *            description, currency, [idempotency_key]
 *      → { success, invoice }
 *
 *  action=pay
 *      body: invoice_id, payer_user_id, amount, payment_method (wallet|card),
 *            [card_name, card_last4, card_expiry, billing_address],
 *            [idempotency_key]
 *      → { success, invoice, transaction_id }
 *
 *  action=cancel
 *      body: invoice_id, user_id, reason
 *      → { success, invoice }
 *
 * ─── Invoice statuses ────────────────────────────────────────────────────────
 *  draft → pending → partial → paid | cancelled | overdue
 *
 * ─── Security / PCI-DSS notes ────────────────────────────────────────────────
 *  • Raw card PANs/CVVs never accepted — only last-4 digits
 *  • Idempotency keys prevent duplicate payments
 *  • All payment mutations recorded in the double-entry ledger
 */

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
$allowedOrigin = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? '');
header('Access-Control-Allow-Origin: ' . $allowedOrigin);
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Idempotency-Key');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

define('INV_DATA_DIR',      __DIR__ . '/data/');
define('INVOICES_JSON',     INV_DATA_DIR . 'invoices.json');
define('INV_PAYMENTS_JSON', INV_DATA_DIR . 'payments.json');
define('INV_WALLETS_DIR',   INV_DATA_DIR . 'wallets/');
define('INV_LEDGER_JSON',   INV_DATA_DIR . 'ledger_entries.json');
define('INV_IDEM_JSON',     INV_DATA_DIR . 'idempotency_keys.json');
define('MAX_LINE_ITEMS',    50);
define('MAX_INVOICE_AMOUNT', 500000.00);

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

function loadWallet(string $safeUserId): ?array
{
    if (!is_dir(INV_WALLETS_DIR)) {
        mkdir(INV_WALLETS_DIR, 0755, true);
    }
    $path = INV_WALLETS_DIR . $safeUserId . '.json';
    if (file_exists($path)) {
        $data = json_decode(file_get_contents($path), true);
        if (is_array($data)) {
            return $data;
        }
    }
    return null;
}

function saveWallet(string $safeUserId, array $wallet): void
{
    $wallet['updated_at'] = date('Y-m-d H:i:s');
    file_put_contents(
        INV_WALLETS_DIR . $safeUserId . '.json',
        json_encode($wallet, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        LOCK_EX
    );
}

function appendLedgerEntry(
    string $entryType,
    string $account,
    float  $amount,
    string $currency,
    string $txId,
    string $description
): void {
    $entries   = readJson(INV_LEDGER_JSON);
    $entries[] = [
        'id'          => 'LED-' . strtoupper(bin2hex(random_bytes(6))),
        'type'        => $entryType,
        'account'     => $account,
        'amount'      => round($amount, 2),
        'currency'    => $currency,
        'tx_ref'      => $txId,
        'description' => $description,
        'timestamp'   => date('Y-m-d H:i:s'),
    ];
    writeJson(INV_LEDGER_JSON, $entries);
}

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

function checkIdempotency(string $key): ?array
{
    if ($key === '') {
        return null;
    }
    $store  = readJson(INV_IDEM_JSON);
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
    $store  = readJson(INV_IDEM_JSON);
    $cutoff = date('Y-m-d H:i:s', strtotime('-24 hours'));
    $store  = array_values(array_filter($store, function ($e) use ($cutoff): bool {
        return ($e['created_at'] ?? '') >= $cutoff;
    }));
    $store[] = [
        'key'        => $key,
        'response'   => $response,
        'created_at' => date('Y-m-d H:i:s'),
    ];
    writeJson(INV_IDEM_JSON, $store);
}

function findInvoice(array $invoices, string $invoiceId): int
{
    foreach ($invoices as $i => $inv) {
        if (($inv['id'] ?? '') === $invoiceId) {
            return $i;
        }
    }
    return -1;
}

function resolveInvoiceStatus(array $invoice): string
{
    $total   = (float)($invoice['total_amount']   ?? 0);
    $paid    = (float)($invoice['amount_paid']    ?? 0);
    $current = $invoice['status'] ?? 'pending';

    if (in_array($current, ['cancelled', 'paid'], true)) {
        return $current;
    }
    if ($paid <= 0) {
        // Check overdue
        $due = $invoice['due_date'] ?? '';
        if ($due && $due < date('Y-m-d')) {
            return 'overdue';
        }
        return 'pending';
    }
    if ($paid >= $total) {
        return 'paid';
    }
    return 'partial';
}

// ── GET ───────────────────────────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = clean($_GET['action'] ?? '');

    if ($action === 'get') {
        $invoiceId = clean($_GET['invoice_id'] ?? '');
        if (!preg_match('/^INV-[A-Z0-9]{12}$/', $invoiceId)) {
            respond(false, 'A valid invoice_id is required (format: INV-XXXXXXXXXXXX).');
        }
        $invoices = readJson(INVOICES_JSON);
        $idx      = findInvoice($invoices, $invoiceId);
        if ($idx === -1) {
            respond(false, 'Invoice not found.');
        }
        respond(true, 'OK', ['invoice' => $invoices[$idx]]);
    }

    if ($action === 'list') {
        $userId = sanitizeUserId($_GET['user_id'] ?? '');
        if (!$userId) {
            respond(false, 'A valid user_id is required.');
        }
        $role   = clean($_GET['role']   ?? '');        // issuer | payer (optional)
        $status = clean($_GET['status'] ?? '');        // optional filter
        $limit  = max(1, min(200, (int)($_GET['limit']  ?? 50)));
        $offset = max(0, (int)($_GET['offset'] ?? 0));

        $invoices = readJson(INVOICES_JSON);

        $filtered = array_values(array_filter($invoices, function (array $inv) use ($userId, $role, $status): bool {
            if ($role === 'issuer' && ($inv['issuer_user_id'] ?? '') !== $userId) {
                return false;
            } elseif ($role === 'payer' && ($inv['payer_user_id'] ?? '') !== $userId) {
                return false;
            } elseif ($role === '') {
                // No role filter — show invoices where user is issuer or payer
                if (($inv['issuer_user_id'] ?? '') !== $userId && ($inv['payer_user_id'] ?? '') !== $userId) {
                    return false;
                }
            }
            if ($status !== '' && ($inv['status'] ?? '') !== $status) {
                return false;
            }
            return true;
        }));

        // Newest first
        usort($filtered, function (array $a, array $b): int {
            return strcmp($b['created_at'] ?? '', $a['created_at'] ?? '');
        });

        $total = count($filtered);
        $page  = array_values(array_slice($filtered, $offset, $limit));
        respond(true, 'OK', ['invoices' => $page, 'total' => $total]);
    }

    respond(false, 'Unknown action. Supported: get, list.');
}

// ── POST ──────────────────────────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = clean($_POST['action'] ?? '');

    // Idempotency key
    $idempotencyKey = clean(
        $_SERVER['HTTP_X_IDEMPOTENCY_KEY'] ?? ($_POST['idempotency_key'] ?? '')
    );
    if ($idempotencyKey !== '') {
        $idempotencyKey = substr(preg_replace('/[^\x20-\x7E]/', '', $idempotencyKey), 0, 128);
    }

    // ── create ────────────────────────────────────────────────────────────────
    if ($action === 'create') {
        $issuerUserId = sanitizeUserId($_POST['issuer_user_id'] ?? '');
        $payerUserId  = sanitizeUserId($_POST['payer_user_id']  ?? '');

        if (!$issuerUserId) {
            respond(false, 'A valid issuer_user_id is required.');
        }
        if (!$payerUserId) {
            respond(false, 'A valid payer_user_id is required.');
        }

        if ($idempotencyKey !== '') {
            $cached = checkIdempotency($idempotencyKey);
            if ($cached !== null) {
                respond(true, 'Idempotent replay.', $cached);
            }
        }

        // Parse line items
        $rawLineItems = $_POST['line_items'] ?? '[]';
        if (is_string($rawLineItems)) {
            $lineItems = json_decode($rawLineItems, true);
        } else {
            $lineItems = [];
        }
        if (!is_array($lineItems) || count($lineItems) === 0) {
            respond(false, 'At least one line item is required.');
        }
        if (count($lineItems) > MAX_LINE_ITEMS) {
            respond(false, 'Too many line items (max ' . MAX_LINE_ITEMS . ').');
        }

        $totalAmount = 0.0;
        $sanitisedItems = [];
        foreach ($lineItems as $item) {
            if (!is_array($item)) {
                respond(false, 'Each line item must be an object.');
            }
            $desc  = clean($item['description'] ?? '');
            $qty   = max(0, (float)($item['quantity']   ?? 0));
            $price = max(0, (float)($item['unit_price'] ?? 0));
            if ($desc === '') {
                respond(false, 'Each line item requires a description.');
            }
            if ($qty <= 0) {
                respond(false, "Line item '{$desc}': quantity must be greater than zero.");
            }
            if ($price < 0) {
                respond(false, "Line item '{$desc}': unit_price must be non-negative.");
            }
            $lineTotal       = round($qty * $price, 2);
            $sanitisedItems[] = [
                'description' => $desc,
                'quantity'    => $qty,
                'unit_price'  => $price,
                'line_total'  => $lineTotal,
            ];
            $totalAmount += $lineTotal;
        }
        $totalAmount = round($totalAmount, 2);

        if ($totalAmount <= 0) {
            respond(false, 'Invoice total must be greater than zero.');
        }
        if ($totalAmount > MAX_INVOICE_AMOUNT) {
            respond(false, sprintf('Invoice total exceeds maximum allowed ($%.2f).', MAX_INVOICE_AMOUNT));
        }

        $rawCurrency = strtoupper(trim($_POST['currency'] ?? 'USD'));
        $currency    = in_array($rawCurrency, ['USD', 'EUR', 'GBP', 'CAD'], true) ? $rawCurrency : 'USD';

        $dueDate     = clean($_POST['due_date']    ?? '');
        $description = clean($_POST['description'] ?? '');

        if ($dueDate !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDate)) {
            respond(false, 'due_date must be in YYYY-MM-DD format.');
        }

        $invoices    = readJson(INVOICES_JSON);
        $existingIds = array_column($invoices, 'id');
        do {
            $invoiceId = 'INV-' . strtoupper(bin2hex(random_bytes(6)));
        } while (in_array($invoiceId, $existingIds, true));

        $invoice = [
            'id'              => $invoiceId,
            'issuer_user_id'  => $issuerUserId,
            'payer_user_id'   => $payerUserId,
            'description'     => $description,
            'currency'        => $currency,
            'line_items'      => $sanitisedItems,
            'total_amount'    => $totalAmount,
            'amount_paid'     => 0.00,
            'amount_due'      => $totalAmount,
            'status'          => 'pending',
            'due_date'        => $dueDate,
            'payments'        => [],
            'created_at'      => date('Y-m-d H:i:s'),
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        $invoices[] = $invoice;
        writeJson(INVOICES_JSON, $invoices);
        auditLog('invoice.created', $issuerUserId, 'invoice', $invoiceId, "Invoice {$invoiceId} created: \${$totalAmount} {$currency} from {$issuerUserId} to {$payerUserId}");

        $responseExtra = ['invoice' => $invoice];
        storeIdempotency($idempotencyKey, $responseExtra);
        respond(true, 'Invoice created successfully.', $responseExtra);
    }

    // ── pay ───────────────────────────────────────────────────────────────────
    if ($action === 'pay') {
        $payerUserId = sanitizeUserId($_POST['payer_user_id'] ?? ($_POST['user_id'] ?? ''));
        if (!$payerUserId) {
            respond(false, 'A valid payer_user_id is required.');
        }

        $invoiceId = clean($_POST['invoice_id'] ?? '');
        if (!preg_match('/^INV-[A-Z0-9]{12}$/', $invoiceId)) {
            respond(false, 'A valid invoice_id is required.');
        }

        if ($idempotencyKey !== '') {
            $cached = checkIdempotency($idempotencyKey);
            if ($cached !== null) {
                respond(true, 'Idempotent replay.', $cached);
            }
        }

        $rawAmount = $_POST['amount'] ?? '';
        if (!is_numeric($rawAmount)) {
            respond(false, 'Amount must be a number.');
        }
        $amount = round((float)$rawAmount, 2);
        if ($amount <= 0) {
            respond(false, 'Amount must be greater than zero.');
        }

        $paymentMethod = clean($_POST['payment_method'] ?? 'wallet');
        if (!in_array($paymentMethod, ['wallet', 'card'], true)) {
            respond(false, 'Invalid payment_method. Must be "wallet" or "card".');
        }

        $invoices = readJson(INVOICES_JSON);
        $idx      = findInvoice($invoices, $invoiceId);
        if ($idx === -1) {
            respond(false, 'Invoice not found.');
        }

        $invoice = $invoices[$idx];

        if (($invoice['payer_user_id'] ?? '') !== $payerUserId) {
            respond(false, 'You are not authorised to pay this invoice.');
        }

        if (in_array($invoice['status'] ?? '', ['paid', 'cancelled'], true)) {
            respond(false, 'Invoice is already ' . $invoice['status'] . '.');
        }

        $amountDue = round((float)($invoice['amount_due'] ?? 0), 2);
        if ($amount > $amountDue) {
            respond(false, sprintf(
                'Payment amount ($%.2f) exceeds amount due ($%.2f).',
                $amount,
                $amountDue
            ));
        }

        $currency = $invoice['currency'] ?? 'USD';

        // ── Wallet payment ─────────────────────────────────────────────────────
        if ($paymentMethod === 'wallet') {
            $wallet = loadWallet($payerUserId);
            if ($wallet === null) {
                respond(false, 'Payer wallet not found.');
            }
            if (($wallet['status'] ?? 'active') !== 'active') {
                respond(false, 'Payer wallet is not active.');
            }
            $walletBalance = round((float)($wallet['balance'] ?? 0), 2);
            if ($walletBalance < $amount) {
                respond(false, sprintf(
                    'Insufficient wallet balance. Available: $%.2f, Required: $%.2f.',
                    $walletBalance,
                    $amount
                ));
            }

            $txId = 'TXN-' . strtoupper(bin2hex(random_bytes(6)));
            $now  = date('Y-m-d H:i:s');

            // Debit payer wallet
            $wallet['balance']        = round($walletBalance - $amount, 2);
            $wallet['transactions'][] = [
                'id'          => $txId,
                'type'        => 'invoice_payment',
                'amount'      => $amount,
                'currency'    => $currency,
                'description' => "Invoice payment {$invoiceId}",
                'reference'   => $invoiceId,
                'status'      => 'completed',
                'timestamp'   => $now,
            ];
            if (count($wallet['transactions']) > 500) {
                $wallet['transactions'] = array_slice($wallet['transactions'], -500);
            }
            saveWallet($payerUserId, $wallet);

            // Credit issuer wallet (if they have one)
            $issuerUserId = $invoice['issuer_user_id'] ?? '';
            $issuerWallet = $issuerUserId ? loadWallet($issuerUserId) : null;
            if ($issuerWallet !== null) {
                $issuerWallet['balance']        = round((float)($issuerWallet['balance'] ?? 0) + $amount, 2);
                $issuerWallet['transactions'][] = [
                    'id'          => $txId,
                    'type'        => 'invoice_receipt',
                    'amount'      => $amount,
                    'currency'    => $currency,
                    'description' => "Invoice receipt {$invoiceId} from {$payerUserId}",
                    'reference'   => $invoiceId,
                    'status'      => 'completed',
                    'timestamp'   => $now,
                ];
                if (count($issuerWallet['transactions']) > 500) {
                    $issuerWallet['transactions'] = array_slice($issuerWallet['transactions'], -500);
                }
                saveWallet($issuerUserId, $issuerWallet);
            }

            // Double-entry ledger
            recordDoubleEntry(
                "user:{$payerUserId}",
                $issuerUserId ? "user:{$issuerUserId}" : 'system:revenue',
                $amount,
                $currency,
                $txId,
                "Invoice {$invoiceId} payment"
            );

            // Record payment in invoice
            $invoices[$idx]['payments'][]  = [
                'tx_id'          => $txId,
                'amount'         => $amount,
                'payment_method' => 'wallet',
                'timestamp'      => $now,
            ];
            $invoices[$idx]['amount_paid'] = round((float)($invoices[$idx]['amount_paid'] ?? 0) + $amount, 2);
            $invoices[$idx]['amount_due']  = round((float)($invoices[$idx]['total_amount'] ?? 0) - (float)$invoices[$idx]['amount_paid'], 2);
            $invoices[$idx]['status']      = resolveInvoiceStatus($invoices[$idx]);
            $invoices[$idx]['updated_at']  = $now;
            writeJson(INVOICES_JSON, $invoices);

            auditLog('invoice.paid', $payerUserId, 'invoice', $invoiceId, "Invoice {$invoiceId} payment of \${$amount} via wallet (tx {$txId})");

            $responseExtra = [
                'invoice'        => $invoices[$idx],
                'transaction_id' => $txId,
                'wallet_balance' => $wallet['balance'],
            ];
            storeIdempotency($idempotencyKey, $responseExtra);
            respond(true, 'Invoice payment successful.', $responseExtra);
        }

        // ── Card payment ───────────────────────────────────────────────────────
        if ($paymentMethod === 'card') {
            $cardName       = clean($_POST['card_name']       ?? '');
            $cardLast4      = clean($_POST['card_last4']      ?? '');
            $cardExpiry     = clean($_POST['card_expiry']     ?? '');
            $billingAddress = clean($_POST['billing_address'] ?? '');

            if (!$cardName) {
                respond(false, 'Cardholder name is required for card payment.');
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
                respond(false, 'Card expiry is invalid or expired.');
            }
            if (!$billingAddress) {
                respond(false, 'Billing address is required.');
            }

            // Generate payment record
            $payments    = readJson(INV_PAYMENTS_JSON);
            $existingIds = array_column($payments, 'id');
            do {
                $paymentId = 'PAY-' . strtoupper(bin2hex(random_bytes(8)));
            } while (in_array($paymentId, $existingIds, true));

            $txId = 'TXN-' . strtoupper(bin2hex(random_bytes(6)));
            $now  = date('Y-m-d H:i:s');

            $payment = [
                'id'              => $paymentId,
                'type'            => 'invoice_payment',
                'invoice_id'      => $invoiceId,
                'user_id'         => $payerUserId,
                'amount'          => $amount,
                'currency'        => $currency,
                'payment_method'  => 'card',
                'card_name'       => $cardName,
                'card_last4'      => $cardLast4,
                // card_expiry NOT stored (PCI-DSS Req 3.3)
                'billing_address' => $billingAddress,
                'status'          => 'completed',
                'created_at'      => $now,
            ];
            $payments[] = $payment;
            writeJson(INV_PAYMENTS_JSON, $payments);

            // Double-entry ledger
            recordDoubleEntry(
                'system:external_card',
                $invoice['issuer_user_id'] ? "user:{$invoice['issuer_user_id']}" : 'system:revenue',
                $amount,
                $currency,
                $txId,
                "Invoice {$invoiceId} card payment **** {$cardLast4}"
            );

            // Record payment in invoice
            $invoices[$idx]['payments'][]  = [
                'tx_id'          => $txId,
                'payment_id'     => $paymentId,
                'amount'         => $amount,
                'payment_method' => 'card',
                'card_last4'     => $cardLast4,
                'timestamp'      => $now,
            ];
            $invoices[$idx]['amount_paid'] = round((float)($invoices[$idx]['amount_paid'] ?? 0) + $amount, 2);
            $invoices[$idx]['amount_due']  = round((float)($invoices[$idx]['total_amount'] ?? 0) - (float)$invoices[$idx]['amount_paid'], 2);
            $invoices[$idx]['status']      = resolveInvoiceStatus($invoices[$idx]);
            $invoices[$idx]['updated_at']  = $now;
            writeJson(INVOICES_JSON, $invoices);

            auditLog('invoice.paid', $payerUserId, 'invoice', $invoiceId, "Invoice {$invoiceId} payment of \${$amount} via card **** {$cardLast4} (tx {$txId})");

            $responseExtra = [
                'invoice'        => $invoices[$idx],
                'transaction_id' => $txId,
                'payment_id'     => $paymentId,
            ];
            storeIdempotency($idempotencyKey, $responseExtra);
            respond(true, 'Invoice payment successful.', $responseExtra);
        }
    }

    // ── cancel ────────────────────────────────────────────────────────────────
    if ($action === 'cancel') {
        $userId    = sanitizeUserId($_POST['user_id'] ?? '');
        $invoiceId = clean($_POST['invoice_id'] ?? '');

        if (!$userId) {
            respond(false, 'A valid user_id is required.');
        }
        if (!preg_match('/^INV-[A-Z0-9]{12}$/', $invoiceId)) {
            respond(false, 'A valid invoice_id is required.');
        }

        $reason   = clean($_POST['reason'] ?? '');
        $invoices = readJson(INVOICES_JSON);
        $idx      = findInvoice($invoices, $invoiceId);
        if ($idx === -1) {
            respond(false, 'Invoice not found.');
        }
        $invoice = $invoices[$idx];

        // Only issuer or payer may cancel
        if (($invoice['issuer_user_id'] ?? '') !== $userId && ($invoice['payer_user_id'] ?? '') !== $userId) {
            respond(false, 'You are not authorised to cancel this invoice.');
        }
        if (($invoice['status'] ?? '') === 'paid') {
            respond(false, 'Cannot cancel a fully-paid invoice.');
        }
        if (($invoice['status'] ?? '') === 'cancelled') {
            respond(false, 'Invoice is already cancelled.');
        }

        $invoices[$idx]['status']     = 'cancelled';
        $invoices[$idx]['updated_at'] = date('Y-m-d H:i:s');
        $invoices[$idx]['cancel_reason'] = $reason;
        writeJson(INVOICES_JSON, $invoices);

        auditLog('invoice.cancelled', $userId, 'invoice', $invoiceId, "Invoice {$invoiceId} cancelled. Reason: {$reason}");
        respond(true, 'Invoice cancelled.', ['invoice' => $invoices[$idx]]);
    }

    respond(false, 'Unknown action. Supported: create, pay, cancel.');
}

respond(false, 'Method not allowed.');
