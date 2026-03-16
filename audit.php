<?php
/**
 * Fastrux — Audit Log API
 *
 * GET  ?action=list   → retrieve recent audit events (optional filters)
 * GET  ?action=stats  → KPI statistics for the Observability dashboard
 * POST                → log a new audit event from the client
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/audit_helper.php';

define('DATA_DIR', __DIR__ . '/data/');
define('AUDIT_LOG', AUDIT_DATA_DIR . 'audit_log.json');

// ── Helpers ──────────────────────────────────────────────────────

function respond(bool $ok, string $msg = '', array $extra = []): void
{
    echo json_encode(array_merge(['success' => $ok, 'message' => $msg], $extra));
    exit;
}

function clean(string $s): string
{
    return htmlspecialchars(strip_tags(trim($s)), ENT_QUOTES, 'UTF-8');
}

function readJson(string $file): array
{
    if (!file_exists($file)) {
        return [];
    }
    $data = json_decode(file_get_contents($file), true);
    return is_array($data) ? $data : [];
}

// ── POST — log an event from client-side ─────────────────────────

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action     = clean($_POST['action']      ?? '');
    $userId     = clean($_POST['user_id']     ?? '');
    $entityType = clean($_POST['entity_type'] ?? '');
    $entityId   = clean($_POST['entity_id']   ?? '');
    $details    = clean($_POST['details']     ?? '');

    if (!$action) {
        respond(false, 'action is required');
    }

    auditLog($action, $userId, $entityType, $entityId, $details);
    respond(true, 'Event logged');
}

// ── GET ───────────────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? 'list';

    if ($action === 'stats') {
        respond(true, '', computeStats());
    }

    // ── list ──
    $log = readJson(AUDIT_LOG);

    $userId  = trim($_GET['user_id']     ?? '');
    $type    = trim($_GET['entity_type'] ?? '');
    $keyword = strtolower(trim($_GET['q'] ?? ''));
    $since   = trim($_GET['since']       ?? '');
    $until   = trim($_GET['until']       ?? '');
    $limit   = min((int) ($_GET['limit'] ?? 200), 500);

    // Newest first
    $filtered = array_reverse($log);

    if ($userId) {
        $filtered = array_filter($filtered, fn($e) => ($e['user_id'] ?? '') === $userId);
    }
    if ($type) {
        $filtered = array_filter($filtered, fn($e) => ($e['entity_type'] ?? '') === $type);
    }
    if ($since) {
        $filtered = array_filter($filtered, fn($e) => ($e['timestamp'] ?? '') >= $since);
    }
    if ($until) {
        $filtered = array_filter($filtered, fn($e) => ($e['timestamp'] ?? '') <= $until . ' 23:59:59');
    }
    if ($keyword) {
        $filtered = array_filter($filtered, function ($e) use ($keyword) {
            return str_contains(strtolower($e['action']      ?? ''), $keyword)
                || str_contains(strtolower($e['user_id']     ?? ''), $keyword)
                || str_contains(strtolower($e['entity_type'] ?? ''), $keyword)
                || str_contains(strtolower($e['entity_id']   ?? ''), $keyword)
                || str_contains(strtolower($e['details']     ?? ''), $keyword)
                || str_contains(strtolower($e['ip_address']  ?? ''), $keyword);
        });
    }

    $filtered = array_values(array_slice($filtered, 0, $limit));

    respond(true, '', ['events' => $filtered, 'total' => count($log)]);
}

respond(false, 'Method not allowed');

// ── Stats computation ─────────────────────────────────────────────

function computeStats(): array
{
    $readJ = function (string $file): array {
        if (!file_exists($file)) {
            return [];
        }
        $d = json_decode(file_get_contents($file), true);
        return is_array($d) ? $d : [];
    };

    $users      = $readJ(DATA_DIR . 'registered_users.json');
    $drivers    = $readJ(DATA_DIR . 'driver_submissions.json');
    $quotes     = $readJ(DATA_DIR . 'quote_submissions.json');
    $loads      = $readJ(DATA_DIR . 'load_requests.json');
    $contacts   = $readJ(DATA_DIR . 'contact_submissions.json');
    $newsletter = $readJ(DATA_DIR . 'newsletter_subscribers.json');
    $auditLog   = $readJ(AUDIT_LOG);

    // Users by role
    $usersByRole = [];
    foreach ($users as $u) {
        $role = $u['role'] ?? 'unknown';
        $usersByRole[$role] = ($usersByRole[$role] ?? 0) + 1;
    }

    // Drivers by status
    $driversByStatus = [];
    foreach ($drivers as $d) {
        $status = $d['status'] ?? 'pending';
        $driversByStatus[$status] = ($driversByStatus[$status] ?? 0) + 1;
    }

    // Quotes by status
    $quotesByStatus = [];
    foreach ($quotes as $q) {
        $status = $q['status'] ?? 'pending';
        $quotesByStatus[$status] = ($quotesByStatus[$status] ?? 0) + 1;
    }

    // Quotes by service type
    $quotesByService = [];
    foreach ($quotes as $q) {
        $svc = $q['service'] ?? 'unknown';
        $quotesByService[$svc] = ($quotesByService[$svc] ?? 0) + 1;
    }

    // Loads by status
    $loadsByStatus = [];
    foreach ($loads as $l) {
        $status = $l['status'] ?? 'unknown';
        $loadsByStatus[$status] = ($loadsByStatus[$status] ?? 0) + 1;
    }

    // Activity per day for the last 14 days
    $activityByDay = [];
    for ($i = 13; $i >= 0; $i--) {
        $day = (new DateTimeImmutable())->modify("-{$i} days")->format('Y-m-d');
        $activityByDay[$day] = 0;
    }
    foreach ($auditLog as $e) {
        $day = substr($e['timestamp'] ?? '', 0, 10);
        if (isset($activityByDay[$day])) {
            $activityByDay[$day]++;
        }
    }

    // Registrations per day for the last 14 days
    $regsByDay = [];
    for ($i = 13; $i >= 0; $i--) {
        $day = (new DateTimeImmutable())->modify("-{$i} days")->format('Y-m-d');
        $regsByDay[$day] = 0;
    }
    foreach ($users as $u) {
        $day = substr($u['timestamp'] ?? '', 0, 10);
        if (isset($regsByDay[$day])) {
            $regsByDay[$day]++;
        }
    }

    // Top actions
    $actionCounts = [];
    foreach ($auditLog as $e) {
        $act = $e['action'] ?? 'unknown';
        $actionCounts[$act] = ($actionCounts[$act] ?? 0) + 1;
    }
    arsort($actionCounts);
    $topActions = array_slice($actionCounts, 0, 10, true);

    // Top active users
    $userActivity = [];
    foreach ($auditLog as $e) {
        $uid = $e['user_id'] ?? '';
        if (!$uid) {
            continue;
        }
        if (!isset($userActivity[$uid])) {
            $userActivity[$uid] = ['user_id' => $uid, 'count' => 0];
        }
        $userActivity[$uid]['count']++;
    }
    usort($userActivity, fn($a, $b) => $b['count'] - $a['count']);
    $topUsers = array_slice($userActivity, 0, 10);

    // Events in the last 24 h and 7 d
    $now   = new DateTimeImmutable();
    $h24   = $now->modify('-24 hours')->format('Y-m-d H:i:s');
    $d7    = $now->modify('-7 days')->format('Y-m-d H:i:s');
    $last24h = count(array_filter($auditLog, fn($e) => ($e['timestamp'] ?? '') >= $h24));
    $last7d  = count(array_filter($auditLog, fn($e) => ($e['timestamp'] ?? '') >= $d7));

    return [
        'summary' => [
            'total_users'        => count($users),
            'total_drivers'      => count($drivers),
            'total_quotes'       => count($quotes),
            'total_loads'        => count($loads),
            'total_contacts'     => count($contacts),
            'newsletter_subs'    => count($newsletter),
            'total_audit_events' => count($auditLog),
            'audit_last_24h'     => $last24h,
            'audit_last_7d'      => $last7d,
        ],
        'users_by_role'      => $usersByRole,
        'drivers_by_status'  => $driversByStatus,
        'quotes_by_status'   => $quotesByStatus,
        'quotes_by_service'  => $quotesByService,
        'loads_by_status'    => $loadsByStatus,
        'activity_by_day'    => $activityByDay,
        'regs_by_day'        => $regsByDay,
        'top_actions'        => $topActions,
        'top_users'          => $topUsers,
    ];
}
