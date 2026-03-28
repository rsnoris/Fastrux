<?php
/**
 * Fastrux — Ratings & Reviews Data API
 * Allows shippers and drivers to rate each other after load completion.
 *
 * GET  ?action=get_ratings&user_id=USR-XXXXXXXX        — ratings received by a user
 * GET  ?action=get_summary&user_id=USR-XXXXXXXX        — avg score + total count for a user
 * GET  ?action=get_given&user_id=USR-XXXXXXXX          — ratings given by a user
 * GET  ?action=check_rated&rater_id=USR-X&load_id=LOAD — whether rater already rated for load
 * POST action=create_rating  — submit a new rating (JSON body)
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

define('RTG_DATA_DIR', __DIR__ . '/data/');
define('RTG_JSON',     RTG_DATA_DIR . 'ratings.json');
define('RTG_MAX_STORE', 50000);
define('RTG_MIN_SCORE', 1);
define('RTG_MAX_SCORE', 5);

require_once __DIR__ . '/audit_helper.php';

// ── Helpers ──────────────────────────────────────────────────────

function rtgClean(string $v): string {
    return htmlspecialchars(strip_tags(trim($v)), ENT_QUOTES, 'UTF-8');
}

function rtgRespond(bool $ok, string $msg = '', array $extra = []): void {
    echo json_encode(array_merge(['success' => $ok, 'message' => $msg], $extra));
    exit;
}

function validateRtgUserId(string $raw): string {
    return preg_match('/^USR-[A-Z0-9]{8}$/', $raw) ? $raw : '';
}

function validateRtgId(string $raw): string {
    return preg_match('/^RTG-[A-Z0-9]{8}$/', $raw) ? $raw : '';
}

function readRatings(): array {
    if (!file_exists(RTG_JSON)) return [];
    $d = json_decode(file_get_contents(RTG_JSON), true);
    return is_array($d) ? $d : [];
}

function writeRatings(array $data): void {
    if (!is_dir(RTG_DATA_DIR)) mkdir(RTG_DATA_DIR, 0755, true);
    if (count($data) > RTG_MAX_STORE) $data = array_slice($data, -RTG_MAX_STORE);
    file_put_contents(RTG_JSON, json_encode(array_values($data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function computeSummary(array $ratings): array {
    if (!count($ratings)) return ['average' => null, 'total' => 0, 'breakdown' => [1=>0,2=>0,3=>0,4=>0,5=>0]];
    $sum  = 0;
    $breakdown = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
    foreach ($ratings as $r) {
        $s = (int)($r['score'] ?? 0);
        if ($s >= 1 && $s <= 5) {
            $sum += $s;
            $breakdown[$s]++;
        }
    }
    $total = count($ratings);
    return [
        'average'   => round($sum / $total, 2),
        'total'     => $total,
        'breakdown' => $breakdown,
    ];
}

// ── GET ──────────────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = rtgClean($_GET['action'] ?? '');

    // ── get_ratings ───────────────────────────────────────────────
    if ($action === 'get_ratings') {
        $userId = validateRtgUserId(trim($_GET['user_id'] ?? ''));
        if (!$userId) rtgRespond(false, 'Valid user_id required.');

        $all    = readRatings();
        $mine   = array_values(array_filter($all, function ($r) use ($userId) {
            return ($r['ratee_id'] ?? '') === $userId;
        }));
        usort($mine, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));
        $summary = computeSummary($mine);
        rtgRespond(true, 'OK', ['ratings' => $mine, 'summary' => $summary]);
    }

    // ── get_summary ───────────────────────────────────────────────
    if ($action === 'get_summary') {
        $userId = validateRtgUserId(trim($_GET['user_id'] ?? ''));
        if (!$userId) rtgRespond(false, 'Valid user_id required.');

        $all  = readRatings();
        $mine = array_values(array_filter($all, function ($r) use ($userId) {
            return ($r['ratee_id'] ?? '') === $userId;
        }));
        rtgRespond(true, 'OK', ['summary' => computeSummary($mine)]);
    }

    // ── get_given ─────────────────────────────────────────────────
    if ($action === 'get_given') {
        $userId = validateRtgUserId(trim($_GET['user_id'] ?? ''));
        if (!$userId) rtgRespond(false, 'Valid user_id required.');

        $all   = readRatings();
        $given = array_values(array_filter($all, function ($r) use ($userId) {
            return ($r['rater_id'] ?? '') === $userId;
        }));
        usort($given, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));
        rtgRespond(true, 'OK', ['ratings' => $given, 'total' => count($given)]);
    }

    // ── check_rated ───────────────────────────────────────────────
    if ($action === 'check_rated') {
        $raterId = validateRtgUserId(trim($_GET['rater_id'] ?? ''));
        $loadId  = rtgClean($_GET['load_id'] ?? '');
        if (!$raterId || !$loadId) rtgRespond(false, 'rater_id and load_id required.');

        $all = readRatings();
        $already = false;
        foreach ($all as $r) {
            if (($r['rater_id'] ?? '') === $raterId && ($r['load_id'] ?? '') === $loadId) {
                $already = true;
                break;
            }
        }
        rtgRespond(true, 'OK', ['already_rated' => $already]);
    }

    rtgRespond(false, 'Unknown action.');
}

// ── POST ─────────────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body   = json_decode(file_get_contents('php://input'), true) ?? [];
    $action = rtgClean($body['action'] ?? '');

    // ── create_rating ─────────────────────────────────────────────
    if ($action === 'create_rating') {
        $raterId = validateRtgUserId(rtgClean($body['rater_id'] ?? ''));
        $rateeId = validateRtgUserId(rtgClean($body['ratee_id'] ?? ''));
        $score   = (int)($body['score'] ?? 0);
        $comment = rtgClean(substr($body['comment'] ?? '', 0, 1000));
        $loadId  = rtgClean(substr($body['load_id'] ?? '', 0, 100));

        if (!$raterId) rtgRespond(false, 'Valid rater_id required.');
        if (!$rateeId) rtgRespond(false, 'Valid ratee_id required.');
        if ($raterId === $rateeId) rtgRespond(false, 'Cannot rate yourself.');
        if ($score < RTG_MIN_SCORE || $score > RTG_MAX_SCORE) {
            rtgRespond(false, 'Score must be between ' . RTG_MIN_SCORE . ' and ' . RTG_MAX_SCORE . '.');
        }

        $ratings = readRatings();

        // Prevent duplicate rating for the same load
        if ($loadId) {
            foreach ($ratings as $r) {
                if (($r['rater_id'] ?? '') === $raterId && ($r['load_id'] ?? '') === $loadId) {
                    rtgRespond(false, 'You have already rated this load.');
                }
            }
        }

        $rtgId = 'RTG-' . strtoupper(substr(md5(uniqid('', true)), 0, 8));

        $entry = [
            'id'         => $rtgId,
            'rater_id'   => $raterId,
            'ratee_id'   => $rateeId,
            'load_id'    => $loadId,
            'score'      => $score,
            'comment'    => $comment,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $ratings[] = $entry;
        writeRatings($ratings);

        auditLog('rating.create', $raterId, 'rating', $rtgId,
            'Rated ' . $rateeId . ': ' . $score . '/5' . ($loadId ? ' for load ' . $loadId : ''));

        rtgRespond(true, 'Rating submitted.', ['rating' => $entry]);
    }

    rtgRespond(false, 'Unknown action.');
}

rtgRespond(false, 'Method not allowed.');
