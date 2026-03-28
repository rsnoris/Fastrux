<?php
/**
 * Fastrux — Data Export API
 *
 * Generates downloadable CSV or JSON reports for admin / accounting.
 *
 * GET  ?type=loads&user_id=USR-X[&format=csv|json][&status=all|open|completed...]
 * GET  ?type=payments&user_id=USR-X[&format=csv|json]
 * GET  ?type=messages&user_id=USR-X[&format=csv|json]
 * GET  ?type=ratings&user_id=USR-X[&format=csv|json]
 * GET  ?type=audit_log&user_id=USR-X[&format=csv|json]    — admin/super_admin only
 * GET  ?type=users&user_id=USR-X[&format=csv|json]        — admin/super_admin only
 *
 * Access rules:
 *   • Non-admins may only export their OWN records (loads they posted, their payments, etc.)
 *   • Admins may export all records
 *   • Passwords / hashes are NEVER included in exports
 *
 * Response:
 *   • format=csv  → text/csv with Content-Disposition: attachment
 *   • format=json → application/json download
 */

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
// Same-origin only — exports may contain PII
$allowedOrigin = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? '');
header('Access-Control-Allow-Origin: ' . $allowedOrigin);
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

define('EXP_DATA_DIR',    __DIR__ . '/data/');
define('EXP_USERS_JSON',  EXP_DATA_DIR . 'registered_users.json');
define('EXP_LOADS_JSON',  EXP_DATA_DIR . 'loadboard_loads.json');
define('EXP_PAY_JSON',    EXP_DATA_DIR . 'payments.json');
define('EXP_MSG_JSON',    EXP_DATA_DIR . 'messages.json');
define('EXP_RTG_JSON',    EXP_DATA_DIR . 'ratings.json');
define('EXP_AUDIT_JSON',  EXP_DATA_DIR . 'audit_log.json');

require_once __DIR__ . '/audit_helper.php';

// ── Helpers ──────────────────────────────────────────────────────

function expClean(string $v): string {
    return htmlspecialchars(strip_tags(trim($v)), ENT_QUOTES, 'UTF-8');
}

function expValidateUserId(string $raw): string {
    $raw = trim($raw);
    return preg_match('/^USR-[A-Z0-9]{8}$/', $raw) ? $raw : '';
}

function expReadJson(string $file): array {
    if (!file_exists($file)) return [];
    $d = json_decode(file_get_contents($file), true);
    return is_array($d) ? $d : [];
}

/**
 * Output CSV. Headers must be sent BEFORE calling this.
 */
function expOutputCsv(array $headers, array $rows, string $filename): void {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    // BOM for Excel UTF-8 compatibility
    echo "\xEF\xBB\xBF";
    $fp = fopen('php://output', 'w');
    fputcsv($fp, $headers);
    foreach ($rows as $row) {
        fputcsv($fp, $row);
    }
    fclose($fp);
}

/**
 * Output JSON download.
 */
function expOutputJson(array $data, string $filename): void {
    header('Content-Type: application/json; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function expError(string $msg, int $code = 400): void {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $msg]);
    exit;
}

// ── Input ──────────────────────────────────────────────────────────

$userId  = expValidateUserId(trim($_GET['user_id'] ?? ''));
$type    = expClean($_GET['type']   ?? '');
$format  = expClean($_GET['format'] ?? 'csv');
$status  = expClean($_GET['status'] ?? 'all');

if (!$userId)  expError('Valid user_id required.');
if (!in_array($type,   ['loads','payments','messages','ratings','audit_log','users'], true)) {
    expError('type must be one of: loads, payments, messages, ratings, audit_log, users.');
}
if (!in_array($format, ['csv','json'], true)) $format = 'csv';

// Resolve requesting user & role
$allUsers = expReadJson(EXP_USERS_JSON);
$reqUser  = null;
foreach ($allUsers as $u) {
    if (($u['id'] ?? '') === $userId) { $reqUser = $u; break; }
}
if (!$reqUser) expError('User not found.', 403);

$role    = $reqUser['role'] ?? '';
$isAdmin = in_array($role, ['admin', 'super_admin'], true);

// Admin-only types
if (in_array($type, ['audit_log', 'users'], true) && !$isAdmin) {
    expError('Admin privileges required.', 403);
}

// ── Collect data ───────────────────────────────────────────────────

$now      = date('Ymd_His');
$rows     = [];
$headers  = [];
$filename = 'fastrux_' . $type . '_' . $now;

if ($type === 'loads') {
    $loads   = expReadJson(EXP_LOADS_JSON);
    $headers = ['ID','Title','Status','Pickup City','Pickup State','Delivery City','Delivery State',
                'Commodity','Weight (lbs)','Rate ($)','Load Type','Equipment','Posted By','Posted At','Notes'];
    foreach ($loads as $l) {
        if (!$isAdmin && ($l['posted_by'] ?? '') !== $userId) continue;
        if ($status !== 'all' && ($l['status'] ?? '') !== $status) continue;
        $rows[] = [
            $l['id']             ?? '',
            $l['title']          ?? '',
            $l['status']         ?? '',
            $l['pickup_city']    ?? '',
            $l['pickup_state']   ?? '',
            $l['delivery_city']  ?? '',
            $l['delivery_state'] ?? '',
            $l['commodity']      ?? '',
            $l['weight']         ?? '',
            $l['rate']           ?? '',
            $l['load_type']      ?? '',
            $l['equipment']      ?? '',
            $l['posted_by']      ?? '',
            $l['posted_at']      ?? '',
            $l['notes']          ?? '',
        ];
    }
}

if ($type === 'payments') {
    $payments = expReadJson(EXP_PAY_JSON);
    $headers  = ['ID','Load ID','User ID','Amount ($)','Payment Method','Status','Card Last4','Created At'];
    foreach ($payments as $p) {
        if (!$isAdmin && ($p['user_id'] ?? '') !== $userId) continue;
        $rows[] = [
            $p['id']             ?? '',
            $p['load_id']        ?? '',
            $p['user_id']        ?? '',
            $p['amount']         ?? '',
            $p['payment_method'] ?? '',
            $p['status']         ?? '',
            $p['card_last4']     ?? '',
            $p['created_at']     ?? $p['paid_at'] ?? '',
        ];
    }
}

if ($type === 'messages') {
    $messages = expReadJson(EXP_MSG_JSON);
    $headers  = ['ID','Sender ID','Sender Name','Recipient ID','Recipient Name','Subject','Sent At','Read At'];
    foreach ($messages as $m) {
        if (!$isAdmin) {
            $mine = ($m['sender_id'] ?? '') === $userId || ($m['recipient_id'] ?? '') === $userId;
            if (!$mine) continue;
        }
        $rows[] = [
            $m['id']             ?? '',
            $m['sender_id']      ?? '',
            $m['sender_name']    ?? '',
            $m['recipient_id']   ?? '',
            $m['recipient_name'] ?? '',
            $m['subject']        ?? '',
            $m['sent_at']        ?? '',
            $m['read_at']        ?? '',
        ];
    }
}

if ($type === 'ratings') {
    $ratings = expReadJson(EXP_RTG_JSON);
    $headers = ['ID','Rater ID','Ratee ID','Score','Comment','Load ID','Created At'];
    foreach ($ratings as $r) {
        if (!$isAdmin) {
            $mine = ($r['rater_id'] ?? '') === $userId || ($r['ratee_id'] ?? '') === $userId;
            if (!$mine) continue;
        }
        $rows[] = [
            $r['id']         ?? '',
            $r['rater_id']   ?? '',
            $r['ratee_id']   ?? '',
            $r['score']      ?? '',
            $r['comment']    ?? '',
            $r['load_id']    ?? '',
            $r['created_at'] ?? '',
        ];
    }
}

if ($type === 'audit_log') {
    $logs    = expReadJson(EXP_AUDIT_JSON);
    $headers = ['ID','Timestamp','Action','User ID','Entity Type','Entity ID','Details','IP Address'];
    foreach ($logs as $entry) {
        $rows[] = [
            $entry['id']          ?? '',
            $entry['timestamp']   ?? '',
            $entry['action']      ?? '',
            $entry['user_id']     ?? '',
            $entry['entity_type'] ?? '',
            $entry['entity_id']   ?? '',
            $entry['details']     ?? '',
            $entry['ip_address']  ?? '',
        ];
    }
}

if ($type === 'users') {
    $headers = ['ID','First Name','Last Name','Email','Role','Status','Company','Phone','Created At'];
    foreach ($allUsers as $u) {
        $rows[] = [
            $u['id']           ?? '',
            $u['first_name']   ?? $u['name'] ?? '',
            $u['last_name']    ?? '',
            $u['email']        ?? '',
            $u['role']         ?? '',
            $u['status']       ?? '',
            $u['company_name'] ?? '',
            $u['phone']        ?? '',
            $u['created_at']   ?? $u['registered_at'] ?? '',
        ];
        // Never export password hashes
    }
}

// ── Audit the export ────────────────────────────────────────────────

auditLog('data.export', $userId, 'export', $type, 'format=' . $format . ' rows=' . count($rows));

// ── Output ──────────────────────────────────────────────────────────

if ($format === 'json') {
    expOutputJson(['type' => $type, 'exported_at' => date('c'), 'rows' => $rows, 'total' => count($rows)], $filename . '.json');
} else {
    expOutputCsv($headers, $rows, $filename . '.csv');
}
