<?php
/**
 * Fastrux — Notifications Data API
 * Handles system-generated in-app notifications for all user roles.
 *
 * GET  ?action=list&user_id=USR-XXXXXXXX             — all notifications for user (newest first)
 * GET  ?action=unread_count&user_id=USR-XXXXXXXX     — count of unread notifications
 * GET  ?action=mark_read&user_id=USR-XXXXXXXX&notif_id=NTF-XXXXXXXX — mark one as read
 * POST action=create      — create a notification (JSON body: user_id, type, title, body, link)
 * POST action=mark_read   — mark one notification as read (JSON body: user_id, notif_id)
 * POST action=mark_all_read — mark all notifications for a user as read (JSON body: user_id)
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

define('NTF_DATA_DIR', __DIR__ . '/data/');
define('NTF_JSON',     NTF_DATA_DIR . 'notifications.json');
define('NTF_MAX_STORE', 10000);

// Allowed notification types
define('NTF_TYPES', [
    'payment_received',
    'payment_sent',
    'load_posted',
    'load_accepted',
    'load_declined',
    'load_completed',
    'driver_assigned',
    'document_uploaded',
    'message_received',
    'account_approved',
    'account_status_changed',
    'invoice_created',
    'invoice_paid',
    'wallet_deposit',
    'wallet_withdrawal',
    'wallet_transfer',
    'rating_received',
    'system',
]);

require_once __DIR__ . '/audit_helper.php';

// ── Helpers ──────────────────────────────────────────────────────

function ntfClean(string $v): string {
    return htmlspecialchars(strip_tags(trim($v)), ENT_QUOTES, 'UTF-8');
}

function ntfRespond(bool $ok, string $msg = '', array $extra = []): void {
    echo json_encode(array_merge(['success' => $ok, 'message' => $msg], $extra));
    exit;
}

function validateNtfUserId(string $raw): string {
    return preg_match('/^USR-[A-Z0-9]{8}$/', $raw) ? $raw : '';
}

function validateNtfId(string $raw): string {
    return preg_match('/^NTF-[A-Z0-9]{8}$/', $raw) ? $raw : '';
}

function readNotifications(): array {
    if (!file_exists(NTF_JSON)) return [];
    $d = json_decode(file_get_contents(NTF_JSON), true);
    return is_array($d) ? $d : [];
}

function writeNotifications(array $data): void {
    if (!is_dir(NTF_DATA_DIR)) mkdir(NTF_DATA_DIR, 0755, true);
    if (count($data) > NTF_MAX_STORE) $data = array_slice($data, -NTF_MAX_STORE);
    file_put_contents(NTF_JSON, json_encode(array_values($data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// ── GET ──────────────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = ntfClean($_GET['action'] ?? '');
    $userId = validateNtfUserId(trim($_GET['user_id'] ?? ''));
    if (!$userId) ntfRespond(false, 'Valid user_id required.');

    $all = readNotifications();

    // ── list ─────────────────────────────────────────────────────
    if ($action === 'list') {
        $mine = array_values(array_filter($all, function ($n) use ($userId) {
            return ($n['user_id'] ?? '') === $userId;
        }));
        usort($mine, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));
        ntfRespond(true, 'OK', ['notifications' => $mine, 'total' => count($mine)]);
    }

    // ── unread_count ─────────────────────────────────────────────
    if ($action === 'unread_count') {
        $count = count(array_filter($all, function ($n) use ($userId) {
            return ($n['user_id'] ?? '') === $userId && !($n['read_at'] ?? null);
        }));
        ntfRespond(true, 'OK', ['unread_count' => $count]);
    }

    // ── mark_read (GET convenience) ───────────────────────────────
    if ($action === 'mark_read') {
        $notifId = validateNtfId(trim($_GET['notif_id'] ?? ''));
        if (!$notifId) ntfRespond(false, 'Valid notif_id required.');

        $notifications = readNotifications();
        $found = false;
        foreach ($notifications as &$n) {
            if (($n['id'] ?? '') === $notifId && ($n['user_id'] ?? '') === $userId) {
                $n['read_at'] = date('Y-m-d H:i:s');
                $found = true;
                break;
            }
        }
        unset($n);
        if (!$found) ntfRespond(false, 'Notification not found.');
        writeNotifications($notifications);
        ntfRespond(true, 'Marked as read.');
    }

    ntfRespond(false, 'Unknown action.');
}

// ── POST ─────────────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body   = json_decode(file_get_contents('php://input'), true) ?? [];
    $action = ntfClean($body['action'] ?? '');

    // ── create ────────────────────────────────────────────────────
    if ($action === 'create') {
        $userId = validateNtfUserId(ntfClean($body['user_id'] ?? ''));
        $type   = ntfClean($body['type'] ?? 'system');
        $title  = ntfClean(substr($body['title'] ?? '', 0, 200));
        $text   = ntfClean(substr($body['body'] ?? '', 0, 1000));
        $link   = ntfClean(substr($body['link'] ?? '', 0, 500));

        if (!$userId) ntfRespond(false, 'Valid user_id required.');
        if (!in_array($type, NTF_TYPES, true)) $type = 'system';
        if (!$title)  ntfRespond(false, 'title is required.');

        $ntfId = 'NTF-' . strtoupper(substr(md5(uniqid('', true)), 0, 8));

        $entry = [
            'id'         => $ntfId,
            'user_id'    => $userId,
            'type'       => $type,
            'title'      => $title,
            'body'       => $text,
            'link'       => $link,
            'read_at'    => null,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $notifications   = readNotifications();
        $notifications[] = $entry;
        writeNotifications($notifications);

        auditLog('notification.create', $userId, 'notification', $ntfId, $type . ': ' . $title);
        ntfRespond(true, 'Notification created.', ['notification' => $entry]);
    }

    // ── mark_read ─────────────────────────────────────────────────
    if ($action === 'mark_read') {
        $userId  = validateNtfUserId(ntfClean($body['user_id'] ?? ''));
        $notifId = validateNtfId(ntfClean($body['notif_id'] ?? ''));
        if (!$userId || !$notifId) ntfRespond(false, 'user_id and notif_id required.');

        $notifications = readNotifications();
        $found = false;
        foreach ($notifications as &$n) {
            if (($n['id'] ?? '') === $notifId && ($n['user_id'] ?? '') === $userId) {
                $n['read_at'] = date('Y-m-d H:i:s');
                $found = true;
                break;
            }
        }
        unset($n);
        if (!$found) ntfRespond(false, 'Notification not found.');
        writeNotifications($notifications);
        ntfRespond(true, 'Marked as read.');
    }

    // ── mark_all_read ─────────────────────────────────────────────
    if ($action === 'mark_all_read') {
        $userId = validateNtfUserId(ntfClean($body['user_id'] ?? ''));
        if (!$userId) ntfRespond(false, 'Valid user_id required.');

        $notifications = readNotifications();
        $now = date('Y-m-d H:i:s');
        foreach ($notifications as &$n) {
            if (($n['user_id'] ?? '') === $userId && !($n['read_at'] ?? null)) {
                $n['read_at'] = $now;
            }
        }
        unset($n);
        writeNotifications($notifications);
        ntfRespond(true, 'All notifications marked as read.');
    }

    ntfRespond(false, 'Unknown action.');
}

ntfRespond(false, 'Method not allowed.');
