<?php
/**
 * Fastrux — Wallet Data API
 *
 * GET  ?action=balance&user_id=USR-XXXXXXXX
 *   → { success, balance, transactions[] }
 *
 * POST action=add_funds
 *   body: user_id, amount, description
 *   → { success, message, balance, transactions[] }
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

define('DATA_DIR', __DIR__ . '/data/');

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
 * Save a wallet to disk.
 */
function saveWallet(string $safeUserId, array $wallet): void
{
    $wallet['updated_at'] = date('Y-m-d H:i:s');
    file_put_contents(
        walletPath($safeUserId),
        json_encode($wallet, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
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

        $wallet = loadWallet($userId);

        $txId = 'TXN-' . strtoupper(bin2hex(random_bytes(4)));
        $transaction = [
            'id'          => $txId,
            'type'        => 'deposit',
            'amount'      => $amount,
            'description' => $description ?: 'Funds added',
            'timestamp'   => date('Y-m-d H:i:s'),
        ];

        $wallet['balance']        = round((float)($wallet['balance'] ?? 0) + $amount, 2);
        $wallet['transactions'][] = $transaction;

        // Cap transaction history at 500 entries
        if (count($wallet['transactions']) > 500) {
            $wallet['transactions'] = array_slice($wallet['transactions'], -500);
        }

        saveWallet($userId, $wallet);
        auditLog('wallet.funds_added', $userId, 'wallet', $userId, "Added \${$amount} to wallet for user {$userId}");

        respond(true, 'Funds added successfully.', [
            'balance'      => $wallet['balance'],
            'transactions' => $wallet['transactions'],
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
