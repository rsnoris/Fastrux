<?php
/**
 * Fastrux — Ledger Data API  (immutable double-entry accounting)
 *
 * ─── GET endpoints ───────────────────────────────────────────────────────────
 *  ?action=list&user_id=USR-XXXX[&limit=50&offset=0&type=debit|credit]
 *      → { success, entries[], total }
 *
 *  ?action=get&entry_id=LED-XXXX
 *      → { success, entry }
 *
 *  ?action=summary&user_id=USR-XXXX
 *      → { success, total_debits, total_credits, net_balance, currency }
 *
 * ─── Security / PCI-DSS notes ────────────────────────────────────────────────
 *  • Ledger entries are immutable — no POST mutation endpoint is exposed
 *  • Writes happen only from wallet_data.php and invoice_data.php
 *  • CORS restricted to same origin (PCI-DSS Req 6.4)
 */

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
$allowedOrigin = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? '');
header('Access-Control-Allow-Origin: ' . $allowedOrigin);
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

define('LEDGER_DATA_DIR',  __DIR__ . '/data/');
define('LEDGER_JSON_FILE', LEDGER_DATA_DIR . 'ledger_entries.json');

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

// ── Only GET is allowed — ledger is immutable from outside ───────────────────

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    respond(false, 'Method not allowed. Ledger is read-only via this endpoint.');
}

$action = clean($_GET['action'] ?? '');

if (!in_array($action, ['list', 'get', 'summary'], true)) {
    respond(false, 'Unknown action. Supported: list, get, summary.');
}

// ── list ─────────────────────────────────────────────────────────────────────
if ($action === 'list') {
    $userId  = sanitizeUserId($_GET['user_id'] ?? '');
    if (!$userId) {
        respond(false, 'A valid user_id is required.');
    }

    $limit  = max(1, min(200, (int)($_GET['limit']  ?? 50)));
    $offset = max(0, (int)($_GET['offset'] ?? 0));
    $type   = clean($_GET['type'] ?? '');   // optional: 'debit' or 'credit'

    $all = readJson(LEDGER_JSON_FILE);

    // Filter to entries that involve this user
    $account = "user:{$userId}";
    $filtered = array_values(array_filter($all, function (array $e) use ($account, $type): bool {
        $match = ($e['account'] ?? '') === $account;
        if ($match && $type !== '') {
            $match = ($e['type'] ?? '') === $type;
        }
        return $match;
    }));

    // Newest first
    $filtered = array_reverse($filtered);
    $total    = count($filtered);
    $page     = array_values(array_slice($filtered, $offset, $limit));

    respond(true, 'OK', ['entries' => $page, 'total' => $total]);
}

// ── get ──────────────────────────────────────────────────────────────────────
if ($action === 'get') {
    $entryId = clean($_GET['entry_id'] ?? '');
    if (!preg_match('/^LED-[A-Z0-9]{12}$/', $entryId)) {
        respond(false, 'A valid entry_id is required (format: LED-XXXXXXXXXXXX).');
    }

    $all = readJson(LEDGER_JSON_FILE);
    foreach ($all as $entry) {
        if (($entry['id'] ?? '') === $entryId) {
            respond(true, 'OK', ['entry' => $entry]);
        }
    }
    respond(false, 'Ledger entry not found.');
}

// ── summary ──────────────────────────────────────────────────────────────────
if ($action === 'summary') {
    $userId = sanitizeUserId($_GET['user_id'] ?? '');
    if (!$userId) {
        respond(false, 'A valid user_id is required.');
    }

    $all     = readJson(LEDGER_JSON_FILE);
    $account = "user:{$userId}";

    $totalDebits  = 0.0;
    $totalCredits = 0.0;
    $currency     = 'USD';

    foreach ($all as $entry) {
        if (($entry['account'] ?? '') !== $account) {
            continue;
        }
        $amount   = (float)($entry['amount'] ?? 0);
        $currency = $entry['currency'] ?? $currency;
        if (($entry['type'] ?? '') === 'debit') {
            $totalDebits += $amount;
        } elseif (($entry['type'] ?? '') === 'credit') {
            $totalCredits += $amount;
        }
    }

    respond(true, 'OK', [
        'total_debits'  => round($totalDebits,  2),
        'total_credits' => round($totalCredits, 2),
        'net_balance'   => round($totalCredits - $totalDebits, 2),
        'currency'      => $currency,
    ]);
}
