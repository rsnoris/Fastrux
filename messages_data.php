<?php
/**
 * Fastrux — Messages Data API
 * Handles in-app messaging and notifications for all user roles.
 *
 * GET  ?action=inbox&user_id=USR-XXXXXXXX          — messages received by user
 * GET  ?action=sent&user_id=USR-XXXXXXXX           — messages sent by user
 * GET  ?action=unread_count&user_id=USR-XXXXXXXX   — count of unread messages
 * GET  ?action=thread&user_id=USR-XXXXXXXX&other_id=USR-YYYYYYYY — conversation thread
 * POST action=send         — send a message (JSON body)
 * POST action=mark_read    — mark a message as read (JSON body)
 * POST action=mark_all_read — mark all messages for a user as read
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

define('MSG_DATA_DIR', __DIR__ . '/data/');
define('MSG_JSON',     MSG_DATA_DIR . 'messages.json');
define('USERS_JSON',   MSG_DATA_DIR . 'registered_users.json');

require_once __DIR__ . '/audit_helper.php';

// ── Helpers ──────────────────────────────────────────────────────

function msgClean(string $v): string {
    return htmlspecialchars(strip_tags(trim($v)), ENT_QUOTES, 'UTF-8');
}

function msgRespond(bool $ok, string $msg = '', array $extra = []): void {
    echo json_encode(array_merge(['success' => $ok, 'message' => $msg], $extra));
    exit;
}

function validateUserId(string $raw): string {
    return preg_match('/^USR-[A-Z0-9]{8}$/', $raw) ? $raw : '';
}

function validateMsgId(string $raw): string {
    return preg_match('/^MSG-[A-Z0-9]{8}$/', $raw) ? $raw : '';
}

function readMessages(): array {
    if (!file_exists(MSG_JSON)) return [];
    $d = json_decode(file_get_contents(MSG_JSON), true);
    return is_array($d) ? $d : [];
}

function writeMessages(array $data): void {
    if (!is_dir(MSG_DATA_DIR)) mkdir(MSG_DATA_DIR, 0755, true);
    // Keep most recent 50,000 messages
    if (count($data) > 50000) $data = array_slice($data, -50000);
    file_put_contents(MSG_JSON, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function getUserName(string $userId): string {
    if (!file_exists(USERS_JSON)) return $userId;
    $users = json_decode(file_get_contents(USERS_JSON), true);
    if (!is_array($users)) return $userId;
    foreach ($users as $u) {
        if (($u['id'] ?? '') === $userId) {
            $first = $u['first_name'] ?? $u['name'] ?? '';
            $last  = $u['last_name'] ?? '';
            $name  = trim($first . ' ' . $last);
            return $name ?: $userId;
        }
    }
    return $userId;
}

// ── GET ──────────────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = msgClean($_GET['action'] ?? '');
    $userId = validateUserId(trim($_GET['user_id'] ?? ''));
    if (!$userId) msgRespond(false, 'Valid user_id required.');

    $messages = readMessages();

    // ── inbox ─────────────────────────────────────────────────────
    if ($action === 'inbox') {
        $inbox = array_values(array_filter($messages, function ($m) use ($userId) {
            return ($m['recipient_id'] ?? '') === $userId;
        }));
        // Sort by sent_at desc
        usort($inbox, fn($a, $b) => strcmp($b['sent_at'] ?? '', $a['sent_at'] ?? ''));
        msgRespond(true, 'OK', ['messages' => $inbox, 'total' => count($inbox)]);
    }

    // ── sent ─────────────────────────────────────────────────────
    if ($action === 'sent') {
        $sent = array_values(array_filter($messages, function ($m) use ($userId) {
            return ($m['sender_id'] ?? '') === $userId;
        }));
        usort($sent, fn($a, $b) => strcmp($b['sent_at'] ?? '', $a['sent_at'] ?? ''));
        msgRespond(true, 'OK', ['messages' => $sent, 'total' => count($sent)]);
    }

    // ── unread_count ─────────────────────────────────────────────
    if ($action === 'unread_count') {
        $count = count(array_filter($messages, function ($m) use ($userId) {
            return ($m['recipient_id'] ?? '') === $userId && !($m['read_at'] ?? null);
        }));
        msgRespond(true, 'OK', ['unread_count' => $count]);
    }

    // ── thread ───────────────────────────────────────────────────
    if ($action === 'thread') {
        $otherId = validateUserId(trim($_GET['other_id'] ?? ''));
        if (!$otherId) msgRespond(false, 'Valid other_id required.');
        $thread = array_values(array_filter($messages, function ($m) use ($userId, $otherId) {
            $sid = $m['sender_id'] ?? '';
            $rid = $m['recipient_id'] ?? '';
            return ($sid === $userId && $rid === $otherId) || ($sid === $otherId && $rid === $userId);
        }));
        usort($thread, fn($a, $b) => strcmp($a['sent_at'] ?? '', $b['sent_at'] ?? ''));
        msgRespond(true, 'OK', ['messages' => $thread, 'total' => count($thread)]);
    }

    msgRespond(false, 'Unknown action.');
}

// ── POST ─────────────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body   = json_decode(file_get_contents('php://input'), true) ?? [];
    $action = msgClean($body['action'] ?? '');

    // ── send ─────────────────────────────────────────────────────
    if ($action === 'send') {
        $senderId    = validateUserId(msgClean($body['sender_id'] ?? ''));
        $recipientId = validateUserId(msgClean($body['recipient_id'] ?? ''));
        $subject     = msgClean(substr($body['subject'] ?? '', 0, 200));
        $bodyText    = msgClean(substr($body['body'] ?? '', 0, 5000));

        if (!$senderId)    msgRespond(false, 'Valid sender_id required.');
        if (!$recipientId) msgRespond(false, 'Valid recipient_id required.');
        if ($senderId === $recipientId) msgRespond(false, 'Cannot send message to yourself.');
        if (!$bodyText)    msgRespond(false, 'Message body is required.');

        $msgId = 'MSG-' . strtoupper(substr(md5(uniqid('', true)), 0, 8));

        $entry = [
            'id'            => $msgId,
            'sender_id'     => $senderId,
            'sender_name'   => getUserName($senderId),
            'recipient_id'  => $recipientId,
            'recipient_name'=> getUserName($recipientId),
            'subject'       => $subject,
            'body'          => $bodyText,
            'sent_at'       => date('Y-m-d H:i:s'),
            'read_at'       => null,
        ];

        $messages   = readMessages();
        $messages[] = $entry;
        writeMessages($messages);

        auditLog('message.send', $senderId, 'message', $msgId, 'To: ' . $recipientId . ' — ' . ($subject ?: '(no subject)'));
        msgRespond(true, 'Message sent.', ['message' => $entry]);
    }

    // ── mark_read ─────────────────────────────────────────────────
    if ($action === 'mark_read') {
        $msgId  = validateMsgId(msgClean($body['msg_id'] ?? ''));
        $userId = validateUserId(msgClean($body['user_id'] ?? ''));
        if (!$msgId || !$userId) msgRespond(false, 'msg_id and user_id required.');

        $messages = readMessages();
        $found    = false;
        foreach ($messages as &$m) {
            if (($m['id'] ?? '') === $msgId && ($m['recipient_id'] ?? '') === $userId) {
                $m['read_at'] = date('Y-m-d H:i:s');
                $found = true;
                break;
            }
        }
        unset($m);
        if (!$found) msgRespond(false, 'Message not found.');
        writeMessages($messages);
        msgRespond(true, 'Marked as read.');
    }

    // ── mark_all_read ─────────────────────────────────────────────
    if ($action === 'mark_all_read') {
        $userId = validateUserId(msgClean($body['user_id'] ?? ''));
        if (!$userId) msgRespond(false, 'Valid user_id required.');

        $messages = readMessages();
        $now = date('Y-m-d H:i:s');
        foreach ($messages as &$m) {
            if (($m['recipient_id'] ?? '') === $userId && !($m['read_at'] ?? null)) {
                $m['read_at'] = $now;
            }
        }
        unset($m);
        writeMessages($messages);
        msgRespond(true, 'All messages marked as read.');
    }

    msgRespond(false, 'Unknown action.');
}

msgRespond(false, 'Method not allowed.');
