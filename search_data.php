<?php
/**
 * Fastrux — Global Search API
 *
 * Searches across: loads (loadboard), users (admin), messages, ratings
 *
 * GET  ?q=<term>&user_id=USR-XXXX[&scope=loads|users|messages|all]
 *   → { success, results: { loads[], users[], messages[], ratings[] }, totals, query }
 *
 * Access rules:
 *   • loads    — any authenticated user (filters by ownership unless admin)
 *   • users    — admin / super_admin only
 *   • messages — own messages only
 *   • ratings  — own ratings only
 *   • all      — combines above, applying the same access rules per scope
 */

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
// Restrict CORS to same origin — search results may contain PII
$allowedOrigin = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? '');
header('Access-Control-Allow-Origin: ' . $allowedOrigin);
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

define('SEARCH_DATA_DIR',   __DIR__ . '/data/');
define('SEARCH_LOADS_JSON', SEARCH_DATA_DIR . 'loadboard_loads.json');
define('SEARCH_USERS_JSON', SEARCH_DATA_DIR . 'registered_users.json');
define('SEARCH_MSG_JSON',   SEARCH_DATA_DIR . 'messages.json');
define('SEARCH_RTG_JSON',   SEARCH_DATA_DIR . 'ratings.json');
define('SEARCH_MAX_RESULTS', 50); // max results per scope

require_once __DIR__ . '/audit_helper.php';

// ── Helpers ──────────────────────────────────────────────────────

function srClean(string $v): string {
    return htmlspecialchars(strip_tags(trim($v)), ENT_QUOTES, 'UTF-8');
}

function srRespond(bool $ok, string $msg = '', array $extra = []): void {
    echo json_encode(array_merge(['success' => $ok, 'message' => $msg], $extra));
    exit;
}

function srValidateUserId(string $raw): string {
    $raw = trim($raw);
    return preg_match('/^USR-[A-Z0-9]{8}$/', $raw) ? $raw : '';
}

function srReadJson(string $file): array {
    if (!file_exists($file)) return [];
    $d = json_decode(file_get_contents($file), true);
    return is_array($d) ? $d : [];
}

/**
 * Case-insensitive substring match against multiple fields of a record.
 */
function srMatches(array $record, string $query, array $fields): bool {
    $q = mb_strtolower($query);
    foreach ($fields as $field) {
        $val = mb_strtolower((string)($record[$field] ?? ''));
        if ($val !== '' && mb_strpos($val, $q) !== false) {
            return true;
        }
    }
    return false;
}

/**
 * Safely strip sensitive fields from a user record.
 */
function srSafeUser(array $u): array {
    return array_diff_key($u, array_flip(['password_hash', 'password', 'raw_password']));
}

// ── Input ─────────────────────────────────────────────────────────

$rawQuery  = trim($_GET['q'] ?? '');
$userId    = srValidateUserId(trim($_GET['user_id'] ?? ''));
$scope     = srClean($_GET['scope'] ?? 'all');

if (!$userId) srRespond(false, 'Valid user_id required.');
if (mb_strlen($rawQuery) < 2) srRespond(false, 'Search query must be at least 2 characters.');

$query = srClean(mb_substr($rawQuery, 0, 200));

// Look up requesting user's role
$allUsers  = srReadJson(SEARCH_USERS_JSON);
$reqUser   = null;
foreach ($allUsers as $u) {
    if (($u['id'] ?? '') === $userId) { $reqUser = $u; break; }
}
if (!$reqUser) srRespond(false, 'User not found.');

$role        = $reqUser['role'] ?? 'shipper';
$adminRoles  = ['admin', 'super_admin'];
$isAdmin     = in_array($role, $adminRoles, true);

$allowedScopes = ['loads', 'users', 'messages', 'ratings', 'all'];
if (!in_array($scope, $allowedScopes, true)) $scope = 'all';

// ── Search functions ──────────────────────────────────────────────

$results = ['loads' => [], 'users' => [], 'messages' => [], 'ratings' => []];

// ── Loads ─────────────────────────────────────────────────────────
if ($scope === 'all' || $scope === 'loads') {
    $loads = srReadJson(SEARCH_LOADS_JSON);
    $loadFields = ['title', 'description', 'pickup_city', 'pickup_state', 'delivery_city',
                   'delivery_state', 'commodity', 'id', 'status', 'load_type'];
    $matched = [];
    foreach ($loads as $load) {
        if (!$isAdmin) {
            // Non-admins see only their own loads or public listings
            $isOwner   = ($load['posted_by'] ?? '') === $userId;
            $isPublic  = ($load['status'] ?? '') === 'open';
            if (!$isOwner && !$isPublic) continue;
        }
        if (srMatches($load, $query, $loadFields)) {
            $matched[] = [
                'id'            => $load['id'] ?? '',
                'title'         => $load['title'] ?? '',
                'status'        => $load['status'] ?? '',
                'pickup_city'   => $load['pickup_city'] ?? '',
                'pickup_state'  => $load['pickup_state'] ?? '',
                'delivery_city' => $load['delivery_city'] ?? '',
                'delivery_state'=> $load['delivery_state'] ?? '',
                'commodity'     => $load['commodity'] ?? '',
                'rate'          => $load['rate'] ?? null,
                'posted_at'     => $load['posted_at'] ?? '',
                '_type'         => 'load',
            ];
        }
        if (count($matched) >= SEARCH_MAX_RESULTS) break;
    }
    $results['loads'] = $matched;
}

// ── Users (admin only) ────────────────────────────────────────────
if (($scope === 'all' || $scope === 'users') && $isAdmin) {
    $userFields = ['first_name', 'last_name', 'name', 'email', 'role', 'id', 'company_name', 'phone'];
    $matched = [];
    foreach ($allUsers as $u) {
        if (srMatches($u, $query, $userFields)) {
            $safe = srSafeUser($u);
            $safe['_type'] = 'user';
            $matched[] = $safe;
        }
        if (count($matched) >= SEARCH_MAX_RESULTS) break;
    }
    $results['users'] = $matched;
}

// ── Messages (own only) ───────────────────────────────────────────
if ($scope === 'all' || $scope === 'messages') {
    $messages    = srReadJson(SEARCH_MSG_JSON);
    $msgFields   = ['subject', 'body', 'sender_name', 'recipient_name'];
    $matched     = [];
    foreach ($messages as $m) {
        $isMine = ($m['sender_id'] ?? '') === $userId || ($m['recipient_id'] ?? '') === $userId;
        if (!$isMine && !$isAdmin) continue;
        if (srMatches($m, $query, $msgFields)) {
            $matched[] = [
                'id'             => $m['id'] ?? '',
                'subject'        => $m['subject'] ?? '',
                'sender_name'    => $m['sender_name'] ?? '',
                'recipient_name' => $m['recipient_name'] ?? '',
                'sent_at'        => $m['sent_at'] ?? '',
                'read_at'        => $m['read_at'] ?? null,
                '_type'          => 'message',
            ];
        }
        if (count($matched) >= SEARCH_MAX_RESULTS) break;
    }
    $results['messages'] = $matched;
}

// ── Ratings (own only) ────────────────────────────────────────────
if ($scope === 'all' || $scope === 'ratings') {
    $ratings   = srReadJson(SEARCH_RTG_JSON);
    $rtgFields = ['comment', 'rater_id', 'ratee_id', 'load_id'];
    $matched   = [];
    foreach ($ratings as $r) {
        $isMine = ($r['rater_id'] ?? '') === $userId || ($r['ratee_id'] ?? '') === $userId;
        if (!$isMine && !$isAdmin) continue;
        if (srMatches($r, $query, $rtgFields)) {
            $matched[] = [
                'id'         => $r['id'] ?? '',
                'rater_id'   => $r['rater_id'] ?? '',
                'ratee_id'   => $r['ratee_id'] ?? '',
                'score'      => $r['score'] ?? null,
                'comment'    => $r['comment'] ?? '',
                'load_id'    => $r['load_id'] ?? '',
                'created_at' => $r['created_at'] ?? '',
                '_type'      => 'rating',
            ];
        }
        if (count($matched) >= SEARCH_MAX_RESULTS) break;
    }
    $results['ratings'] = $matched;
}

// ── Totals ────────────────────────────────────────────────────────
$totals = [
    'loads'    => count($results['loads']),
    'users'    => count($results['users']),
    'messages' => count($results['messages']),
    'ratings'  => count($results['ratings']),
    'total'    => array_sum(array_map('count', $results)),
];

auditLog('search.query', $userId, 'search', '', 'q=' . $query . ' scope=' . $scope . ' hits=' . $totals['total']);

srRespond(true, 'OK', [
    'query'   => $query,
    'scope'   => $scope,
    'results' => $results,
    'totals'  => $totals,
]);
