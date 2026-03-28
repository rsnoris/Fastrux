<?php
/**
 * Fastrux — GDPR Compliance API
 *
 * Implements the two core GDPR / CCPA data subject rights:
 *   1. Right to Access   — export all personal data for a user
 *   2. Right to Erasure  — delete / anonymise all personal data for a user
 *
 * GET  ?action=export_data&user_id=USR-XXXX
 *      Returns a JSON archive of all personal data held for the user.
 *      Excludes: financial ledger entries (legal obligation to retain)
 *
 * POST action=request_deletion
 *      body: { user_id, confirmation: "DELETE MY ACCOUNT" }
 *      Anonymises the user record and scrubs personal data from:
 *        - registered_users.json   (name, email, phone → anonymised)
 *        - messages.json           (sender_name / recipient_name)
 *        - notifications.json      (title/body that may contain PII — scrubbed)
 *      Financial records (payments, ledger, audit_log) are RETAINED for legal
 *      compliance but the user ID is preserved to maintain accounting integrity.
 *      The user account status is set to "deleted".
 *
 * POST action=withdraw_consent
 *      body: { user_id }
 *      Records consent withdrawal in the audit log (does not delete data).
 *
 * Access: Users may only act on their own data. Admins may act on any user.
 */

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
$allowedOrigin = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? '');
header('Access-Control-Allow-Origin: ' . $allowedOrigin);
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

define('GDPR_DATA_DIR',   __DIR__ . '/data/');
define('GDPR_USERS_JSON', GDPR_DATA_DIR . 'registered_users.json');
define('GDPR_MSG_JSON',   GDPR_DATA_DIR . 'messages.json');
define('GDPR_NTF_JSON',   GDPR_DATA_DIR . 'notifications.json');
define('GDPR_PAY_JSON',   GDPR_DATA_DIR . 'payments.json');
define('GDPR_RTG_JSON',   GDPR_DATA_DIR . 'ratings.json');

require_once __DIR__ . '/audit_helper.php';

// ── Helpers ──────────────────────────────────────────────────────

function gdprClean(string $v): string {
    return htmlspecialchars(strip_tags(trim($v)), ENT_QUOTES, 'UTF-8');
}

function gdprRespond(bool $ok, string $msg = '', array $extra = []): void {
    echo json_encode(array_merge(['success' => $ok, 'message' => $msg], $extra));
    exit;
}

function gdprValidateUserId(string $raw): string {
    $raw = trim($raw);
    return preg_match('/^USR-[A-Z0-9]{8}$/', $raw) ? $raw : '';
}

function gdprReadJson(string $file): array {
    if (!file_exists($file)) return [];
    $d = json_decode(file_get_contents($file), true);
    return is_array($d) ? $d : [];
}

function gdprWriteJson(string $file, array $data): void {
    file_put_contents($file, json_encode(array_values($data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
}

/**
 * Resolve requesting user role; verifies the actor is allowed to operate on $targetId.
 * Returns the actor's user record.
 */
function gdprResolveActor(string $actorId, string $targetId): array {
    $users    = gdprReadJson(GDPR_USERS_JSON);
    $actorRec = null;
    foreach ($users as $u) {
        if (($u['id'] ?? '') === $actorId) { $actorRec = $u; break; }
    }
    if (!$actorRec) gdprRespond(false, 'Actor user not found.', [], 403);

    $role    = $actorRec['role'] ?? '';
    $isAdmin = in_array($role, ['admin', 'super_admin'], true);
    if (!$isAdmin && $actorId !== $targetId) {
        gdprRespond(false, 'You may only manage your own data.', [], 403);
    }
    return $actorRec;
}

// ── GET ──────────────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action   = gdprClean($_GET['action']    ?? '');
    $userId   = gdprValidateUserId(trim($_GET['user_id'] ?? ''));
    $actorId  = gdprValidateUserId(trim($_GET['actor_id'] ?? $userId));

    if (!$userId)  gdprRespond(false, 'Valid user_id required.');
    if (!$actorId) $actorId = $userId;

    // ── export_data ──────────────────────────────────────────────
    if ($action === 'export_data') {
        gdprResolveActor($actorId, $userId);

        // 1. User profile (strip password)
        $users    = gdprReadJson(GDPR_USERS_JSON);
        $profile  = null;
        foreach ($users as $u) {
            if (($u['id'] ?? '') === $userId) {
                $profile = array_diff_key($u, array_flip(['password_hash', 'password', 'raw_password']));
                break;
            }
        }

        // 2. Messages (sent or received)
        $messages = gdprReadJson(GDPR_MSG_JSON);
        $myMsgs   = array_values(array_filter($messages, function ($m) use ($userId) {
            return ($m['sender_id'] ?? '') === $userId || ($m['recipient_id'] ?? '') === $userId;
        }));

        // 3. Notifications
        $notifs   = gdprReadJson(GDPR_NTF_JSON);
        $myNotifs = array_values(array_filter($notifs, function ($n) use ($userId) {
            return ($n['user_id'] ?? '') === $userId;
        }));

        // 4. Payments
        $payments = gdprReadJson(GDPR_PAY_JSON);
        $myPays   = array_values(array_filter($payments, function ($p) use ($userId) {
            return ($p['user_id'] ?? '') === $userId;
        }));

        // 5. Ratings given
        $ratings   = gdprReadJson(GDPR_RTG_JSON);
        $myRatings = array_values(array_filter($ratings, function ($r) use ($userId) {
            return ($r['rater_id'] ?? '') === $userId || ($r['ratee_id'] ?? '') === $userId;
        }));

        auditLog('gdpr.export_data', $actorId, 'user', $userId, 'GDPR data export requested');

        // Override Content-Type for download
        header('Content-Type: application/json; charset=UTF-8');
        header('Content-Disposition: attachment; filename="fastrux_gdpr_export_' . $userId . '_' . date('Ymd') . '.json"');
        echo json_encode([
            'export_generated_at' => date('c'),
            'data_controller'     => 'Fastrux Logistics',
            'subject_user_id'     => $userId,
            'profile'             => $profile,
            'messages'            => $myMsgs,
            'notifications'       => $myNotifs,
            'payments'            => $myPays,
            'ratings'             => $myRatings,
            '_note'               => 'Financial records (ledger, transactions) are retained for legal compliance obligations and not included in this export.',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    gdprRespond(false, 'Unknown action.');
}

// ── POST ─────────────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body    = json_decode(file_get_contents('php://input'), true) ?? [];
    $action  = gdprClean($body['action'] ?? '');
    $userId  = gdprValidateUserId(gdprClean($body['user_id'] ?? ''));
    $actorId = gdprValidateUserId(gdprClean($body['actor_id'] ?? $userId));

    if (!$userId)  gdprRespond(false, 'Valid user_id required.');
    if (!$actorId) $actorId = $userId;

    // ── request_deletion ─────────────────────────────────────────
    if ($action === 'request_deletion') {
        $confirmation = gdprClean($body['confirmation'] ?? '');
        if ($confirmation !== 'DELETE MY ACCOUNT') {
            gdprRespond(false, 'Confirmation phrase must be exactly: DELETE MY ACCOUNT');
        }

        gdprResolveActor($actorId, $userId);

        $anonName  = '[deleted user]';
        $anonEmail = 'deleted_' . $userId . '@fastrux.invalid';

        // 1. Anonymise user profile
        $users = gdprReadJson(GDPR_USERS_JSON);
        foreach ($users as &$u) {
            if (($u['id'] ?? '') === $userId) {
                $u['first_name']    = $anonName;
                $u['last_name']     = '';
                $u['name']          = $anonName;
                $u['email']         = $anonEmail;
                $u['phone']         = '';
                $u['company_name']  = '';
                $u['address']       = '';
                $u['password_hash'] = '';
                $u['status']        = 'deleted';
                $u['deleted_at']    = date('Y-m-d H:i:s');
                break;
            }
        }
        unset($u);
        gdprWriteJson(GDPR_USERS_JSON, $users);

        // 2. Anonymise sender/recipient names in messages (do NOT delete — audit trail)
        $messages = gdprReadJson(GDPR_MSG_JSON);
        foreach ($messages as &$m) {
            if (($m['sender_id'] ?? '') === $userId) {
                $m['sender_name'] = $anonName;
            }
            if (($m['recipient_id'] ?? '') === $userId) {
                $m['recipient_name'] = $anonName;
            }
        }
        unset($m);
        gdprWriteJson(GDPR_MSG_JSON, $messages);

        // 3. Delete personal notification content (user-specific, not audit-relevant)
        $notifs = gdprReadJson(GDPR_NTF_JSON);
        $notifs = array_filter($notifs, function ($n) use ($userId) {
            return ($n['user_id'] ?? '') !== $userId;
        });
        gdprWriteJson(GDPR_NTF_JSON, array_values($notifs));

        auditLog('gdpr.account_deleted', $actorId, 'user', $userId, 'GDPR erasure — account anonymised');

        gdprRespond(true, 'Account data anonymised. Financial records retained for legal compliance.', [
            'deleted_at' => date('Y-m-d H:i:s'),
            'retained'   => ['payments', 'ledger_entries', 'audit_log'],
        ]);
    }

    // ── withdraw_consent ─────────────────────────────────────────
    if ($action === 'withdraw_consent') {
        gdprResolveActor($actorId, $userId);

        auditLog('gdpr.consent_withdrawn', $actorId, 'user', $userId, 'User withdrew marketing/processing consent');

        // Record consent withdrawal on the user profile
        $users = gdprReadJson(GDPR_USERS_JSON);
        foreach ($users as &$u) {
            if (($u['id'] ?? '') === $userId) {
                $u['consent_withdrawn_at'] = date('Y-m-d H:i:s');
                $u['marketing_consent']    = false;
                break;
            }
        }
        unset($u);
        gdprWriteJson(GDPR_USERS_JSON, $users);

        gdprRespond(true, 'Consent withdrawal recorded.', [
            'withdrawn_at' => date('Y-m-d H:i:s'),
        ]);
    }

    gdprRespond(false, 'Unknown action.');
}

gdprRespond(false, 'Method not allowed.');
