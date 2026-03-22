<?php
/**
 * Fastrux — Payment Data API
 *
 * POST action=process_payment
 *   body: load_id, user_id, amount, card_name, card_last4, card_expiry,
 *         billing_address, payment_method (card|wallet)
 *   → { success, message, payment_id, payment }
 *
 * GET  ?action=get_payment&payment_id=PAY-XXXX
 *   → { success, payment }
 *
 * GET  ?action=list_payments&user_id=USR-XXXX
 *   → { success, payments[] }
 */

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
// Restrict CORS to same origin — payment APIs must not be callable cross-site (PCI-DSS Req 6.4)
$allowedOrigin = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? '');
header('Access-Control-Allow-Origin: ' . $allowedOrigin);
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

define('DATA_DIR',          __DIR__ . '/data/');
define('PAYMENTS_JSON',     DATA_DIR . 'payments.json');
define('LOADS_JSON',        DATA_DIR . 'load_requests.json');
define('WALLETS_DIR',       DATA_DIR . 'wallets/');
define('MAX_PAYMENT_AMOUNT', 1000000);

require_once __DIR__ . '/audit_helper.php';

// ── Helpers ──────────────────────────────────────────────────────────────────

function respond(bool $ok, string $msg = '', array $extra = []): void
{
    echo json_encode(array_merge(['success' => $ok, 'message' => $msg], $extra));
    exit;
}

function clean(string $s): string
{
    return htmlspecialchars(strip_tags(trim($s)), ENT_QUOTES, 'UTF-8');
}

/** Validate USR-XXXXXXXX format. */
function validateUserId(string $raw): string
{
    $raw = trim($raw);
    if (preg_match('/^USR-[A-Za-z0-9_\-]{1,16}$/', $raw)) {
        return $raw;
    }
    return '';
}

/** Validate load ID FX-XXXXXXXXXXXXXXX format. */
function validateLoadId(string $raw): string
{
    $raw = trim($raw);
    if (preg_match('/^FX-\d{15}$/', $raw)) {
        return $raw;
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

/** Load a user's wallet; create default if none exists. */
function loadWallet(string $userId): array
{
    if (!is_dir(WALLETS_DIR)) {
        mkdir(WALLETS_DIR, 0755, true);
    }
    $path = WALLETS_DIR . $userId . '.json';
    if (file_exists($path)) {
        $data = json_decode(file_get_contents($path), true);
        if (is_array($data)) {
            return $data;
        }
    }
    return [
        'user_id'      => $userId,
        'balance'      => 0.00,
        'transactions' => [],
        'created_at'   => date('Y-m-d H:i:s'),
        'updated_at'   => date('Y-m-d H:i:s'),
    ];
}

/** Save a user's wallet to disk atomically (LOCK_EX prevents concurrent-write corruption). */
function saveWallet(string $userId, array $wallet): void
{
    $wallet['updated_at'] = date('Y-m-d H:i:s');
    $path = WALLETS_DIR . $userId . '.json';
    file_put_contents($path, json_encode($wallet, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
}

// ── GET ───────────────────────────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = clean($_GET['action'] ?? '');

    if ($action === 'get_payment') {
        $paymentId = clean($_GET['payment_id'] ?? '');
        if (!preg_match('/^PAY-[A-Z0-9]{16}$/', $paymentId)) {
            respond(false, 'A valid payment_id is required.');
        }
        $payments = readJson(PAYMENTS_JSON);
        foreach ($payments as $p) {
            if (($p['id'] ?? '') === $paymentId) {
                respond(true, 'OK', ['payment' => $p]);
            }
        }
        respond(false, 'Payment not found.');
    }

    if ($action === 'list_payments') {
        $userId = validateUserId($_GET['user_id'] ?? '');
        if (!$userId) {
            respond(false, 'A valid user_id is required.');
        }
        $payments = readJson(PAYMENTS_JSON);
        $userPayments = array_values(array_filter($payments, function (array $p) use ($userId): bool {
            return ($p['user_id'] ?? '') === $userId;
        }));
        // Return newest first
        $userPayments = array_reverse($userPayments);
        respond(true, 'OK', ['payments' => $userPayments]);
    }

    respond(false, 'Unknown action.');
}

// ── POST ──────────────────────────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = clean($_POST['action'] ?? '');

    if ($action !== 'process_payment') {
        respond(false, 'Unknown action.');
    }

    // ── Validate inputs ──────────────────────────────────────────────────────

    $userId  = validateUserId($_POST['user_id'] ?? '');
    $loadId  = validateLoadId($_POST['load_id'] ?? '');

    if (!$userId) {
        respond(false, 'A valid user_id is required.');
    }
    if (!$loadId) {
        respond(false, 'A valid load_id is required.');
    }

    $rawAmount = $_POST['amount'] ?? '';
    if (!is_numeric($rawAmount)) {
        respond(false, 'Amount must be a number.');
    }
    $amount = round((float)$rawAmount, 2);
    if ($amount <= 0) {
        respond(false, 'Amount must be greater than zero.');
    }
    if ($amount > MAX_PAYMENT_AMOUNT) {
        respond(false, 'Amount exceeds the maximum allowed value.');
    }

    $paymentMethod = clean($_POST['payment_method'] ?? 'card');
    if (!in_array($paymentMethod, ['card', 'wallet'], true)) {
        respond(false, 'Invalid payment method. Must be "card" or "wallet".');
    }

    // ── Verify the load exists and has not already been paid ─────────────────

    $loads = readJson(LOADS_JSON);
    $loadIdx = -1;
    foreach ($loads as $i => $l) {
        if (($l['id'] ?? '') === $loadId) {
            $loadIdx = $i;
            break;
        }
    }
    if ($loadIdx === -1) {
        respond(false, 'Load not found.');
    }
    if (($loads[$loadIdx]['payment_status'] ?? '') === 'paid') {
        respond(false, 'This load has already been paid.');
    }

    // ── Payment method specific validation ───────────────────────────────────

    $cardLast4     = '';
    $cardExpiry    = '';
    $cardName      = '';
    $billingAddress = '';

    if ($paymentMethod === 'card') {
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
        // Server-side expiry check — prevent expired cards from being accepted
        [$expMm, $expYy] = explode('/', $cardExpiry);
        $expMm = (int)$expMm;
        $expYy = (int)$expYy;
        // Map 2-digit year: 00-49 → 2000-2049, 50-99 → 2050-2099
        $expYear = $expYy < 50 ? 2000 + $expYy : 2050 + ($expYy - 50);
        $nowYear = (int)date('Y');
        $nowMon  = (int)date('n');
        if ($expMm < 1 || $expMm > 12 || $expYear < $nowYear || ($expYear === $nowYear && $expMm < $nowMon)) {
            respond(false, 'The card expiry date is invalid or the card has expired.');
        }
        if (!$billingAddress) {
            respond(false, 'Billing address is required.');
        }
    }

    if ($paymentMethod === 'wallet') {
        // Load wallet once and re-validate balance before deducting (single-load to avoid TOCTOU race)
        $wallet  = loadWallet($userId);
        $balance = round((float)($wallet['balance'] ?? 0), 2);
        if ($balance < $amount) {
            respond(false, sprintf(
                'Insufficient wallet balance. Available: $%.2f, Required: $%.2f.',
                $balance,
                $amount
            ));
        }
    }

    // ── Generate a unique payment ID ─────────────────────────────────────────

    $payments    = readJson(PAYMENTS_JSON);
    $existingIds = array_column($payments, 'id');
    do {
        $paymentId = 'PAY-' . strtoupper(bin2hex(random_bytes(8)));
    } while (in_array($paymentId, $existingIds, true));

    // ── Build payment record ──────────────────────────────────────────────────

    $payment = [
        'id'              => $paymentId,
        'load_id'         => $loadId,
        'user_id'         => $userId,
        'amount'          => $amount,
        'currency'        => 'USD',
        'payment_method'  => $paymentMethod,
        'status'          => 'completed',
        'created_at'      => date('Y-m-d H:i:s'),
    ];

    if ($paymentMethod === 'card') {
        $payment['card_name']       = $cardName;
        $payment['card_last4']      = $cardLast4;
        // card_expiry is NOT stored post-authorisation (PCI-DSS Req 3.3 — minimise stored cardholder data)
        $payment['billing_address'] = $billingAddress;
    }

    // ── Persist payment ───────────────────────────────────────────────────────

    $payments[] = $payment;
    writeJson(PAYMENTS_JSON, $payments);

    // ── Update load with payment details ──────────────────────────────────────

    $loads[$loadIdx]['payment_id']     = $paymentId;
    $loads[$loadIdx]['payment_status'] = 'paid';
    $loads[$loadIdx]['payment_amount'] = $amount;
    $loads[$loadIdx]['payment_method'] = $paymentMethod;
    $loads[$loadIdx]['paid_at']        = date('Y-m-d H:i:s');
    // Confirm the load (mark as open/active once paid)
    if (($loads[$loadIdx]['status'] ?? '') === 'pending_payment') {
        $loads[$loadIdx]['status'] = 'open';
    }
    writeJson(LOADS_JSON, $loads);

    // ── Deduct from wallet when paying by wallet ──────────────────────────────

    if ($paymentMethod === 'wallet') {
        // $wallet was already loaded above for the balance check; deduct from it now
        $wallet['balance'] = round((float)($wallet['balance'] ?? 0) - $amount, 2);

        $txId = 'TXN-' . strtoupper(bin2hex(random_bytes(4)));
        $wallet['transactions'][] = [
            'id'          => $txId,
            'type'        => 'payment',
            'amount'      => $amount,
            'description' => "Payment for load {$loadId}",
            'reference'   => $paymentId,
            'timestamp'   => date('Y-m-d H:i:s'),
        ];

        // Cap transaction history
        if (count($wallet['transactions']) > 500) {
            $wallet['transactions'] = array_slice($wallet['transactions'], -500);
        }
        saveWallet($userId, $wallet);
    } else {
        // Card payment: record a card-payment transaction in the wallet
        // (for a complete transaction history, even when not using wallet balance)
        $wallet = loadWallet($userId);
        $txId = 'TXN-' . strtoupper(bin2hex(random_bytes(4)));
        $wallet['transactions'][] = [
            'id'          => $txId,
            'type'        => 'card_payment',
            'amount'      => $amount,
            'description' => "Card payment for load {$loadId} (**** {$cardLast4})",
            'reference'   => $paymentId,
            'timestamp'   => date('Y-m-d H:i:s'),
        ];
        if (count($wallet['transactions']) > 500) {
            $wallet['transactions'] = array_slice($wallet['transactions'], -500);
        }
        saveWallet($userId, $wallet);
    }

    // ── Audit ─────────────────────────────────────────────────────────────────

    auditLog(
        'payment.completed',
        $userId,
        'payment',
        $paymentId,
        "Payment {$paymentId} completed for load {$loadId}: \${$amount} via {$paymentMethod}"
    );

    respond(true, 'Payment processed successfully.', [
        'payment_id' => $paymentId,
        'payment'    => $payment,
    ]);
}

respond(false, 'Method not allowed.');
