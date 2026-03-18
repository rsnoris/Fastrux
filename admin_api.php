<?php
/**
 * Fastrux — Admin API
 *
 * Provides RBAC-controlled endpoints for admin operations.
 * All mutating endpoints verify the requesting user's role server-side
 * by looking up their ID in registered_users.json.
 *
 * GET  ?action=pending_staff          → list accounts with status=pending_approval (admin+)
 * GET  ?action=users                  → list all users (admin+)
 * POST action=approve_staff           → approve a staff account (admin+)
 * POST action=reject_staff            → reject a staff account (admin+)
 * POST action=change_role             → change a user's role (super_admin only)
 * POST action=create_admin            → create an admin/super_admin account (super_admin only)
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

define('ADMIN_DATA_DIR', __DIR__ . '/data/');
define('USERS_FILE',     ADMIN_DATA_DIR . 'registered_users.json');

// ── Helpers ───────────────────────────────────────────────────────

function adminRespond(bool $ok, string $msg = '', array $extra = []): void
{
    echo json_encode(array_merge(['success' => $ok, 'message' => $msg], $extra));
    exit;
}

function adminClean(string $s): string
{
    return htmlspecialchars(strip_tags(trim($s)), ENT_QUOTES, 'UTF-8');
}

function readUsersFile(): array
{
    if (!file_exists(USERS_FILE)) {
        return [];
    }
    $data = json_decode(file_get_contents(USERS_FILE), true);
    return is_array($data) ? $data : [];
}

function saveUsersFile(array $users): void
{
    file_put_contents(
        USERS_FILE,
        json_encode(array_values($users), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );
}

/**
 * Verify the requesting user exists in the user store and has at least the required role level.
 * Returns the verified user array or calls adminRespond(false, ...) and exits.
 */
function requireRole(string $requestingUserId, string $minimumRole): array
{
    if (!$requestingUserId) {
        adminRespond(false, 'Authentication required.');
    }

    $adminRoles    = ['admin', 'super_admin'];
    $superAdminOnly = ['super_admin'];

    $users = readUsersFile();
    $actor = null;
    foreach ($users as $u) {
        if (($u['id'] ?? '') === $requestingUserId) {
            $actor = $u;
            break;
        }
    }

    if (!$actor) {
        adminRespond(false, 'User not found.');
    }

    $role = $actor['role'] ?? '';

    if ($minimumRole === 'admin' && !in_array($role, $adminRoles, true)) {
        adminRespond(false, 'Access denied. Admin privileges required.');
    }

    if ($minimumRole === 'super_admin' && !in_array($role, $superAdminOnly, true)) {
        adminRespond(false, 'Access denied. Super-Admin privileges required.');
    }

    return $actor;
}

// ── Route ─────────────────────────────────────────────────────────

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $action          = adminClean($_GET['action']           ?? 'users');
    $requestingUserId = adminClean($_GET['requesting_user_id'] ?? '');

    requireRole($requestingUserId, 'admin');

    $users = readUsersFile();

    if ($action === 'pending_staff') {
        $pending = array_values(array_filter($users, function ($u) {
            return ($u['status'] ?? 'active') === 'pending_approval';
        }));
        // Strip password hashes before returning
        $safe = array_map(fn($u) => array_diff_key($u, ['password_hash' => '']), $pending);
        adminRespond(true, '', ['users' => array_values($safe), 'total' => count($safe)]);
    }

    if ($action === 'users') {
        $safe = array_map(fn($u) => array_diff_key($u, ['password_hash' => '']), $users);
        adminRespond(true, '', ['users' => array_values($safe), 'total' => count($safe)]);
    }

    adminRespond(false, 'Unknown action.');
}

if ($method === 'POST') {
    $action           = adminClean($_POST['action']              ?? '');
    $requestingUserId = adminClean($_POST['requesting_user_id']  ?? '');
    $targetUserId     = adminClean($_POST['target_user_id']      ?? '');

    if (!$action) {
        adminRespond(false, 'action is required.');
    }

    // ── Approve staff ──────────────────────────────────────────────
    if ($action === 'approve_staff') {
        $actor = requireRole($requestingUserId, 'admin');

        if (!$targetUserId) {
            adminRespond(false, 'target_user_id is required.');
        }

        $users   = readUsersFile();
        $updated = false;
        foreach ($users as &$u) {
            if (($u['id'] ?? '') === $targetUserId) {
                if (($u['role'] ?? '') !== 'corporate_staff') {
                    adminRespond(false, 'Only corporate_staff accounts can be approved via this endpoint.');
                }
                $u['status']      = 'active';
                $u['approved_by'] = $requestingUserId;
                $u['approved_at'] = date('Y-m-d H:i:s');
                $updated          = true;
                break;
            }
        }
        unset($u);

        if (!$updated) {
            adminRespond(false, 'Target user not found.');
        }

        saveUsersFile($users);
        auditLog(
            'staff.approved',
            $requestingUserId,
            'user',
            $targetUserId,
            "Staff account {$targetUserId} approved by {$requestingUserId}"
        );
        adminRespond(true, 'Staff account approved successfully.');
    }

    // ── Reject staff ───────────────────────────────────────────────
    if ($action === 'reject_staff') {
        $actor  = requireRole($requestingUserId, 'admin');
        $reason = adminClean($_POST['reason'] ?? '');

        if (!$targetUserId) {
            adminRespond(false, 'target_user_id is required.');
        }

        $users   = readUsersFile();
        $updated = false;
        foreach ($users as &$u) {
            if (($u['id'] ?? '') === $targetUserId) {
                if (($u['role'] ?? '') !== 'corporate_staff') {
                    adminRespond(false, 'Only corporate_staff accounts can be rejected via this endpoint.');
                }
                $u['status']      = 'rejected';
                $u['rejected_by'] = $requestingUserId;
                $u['rejected_at'] = date('Y-m-d H:i:s');
                if ($reason) {
                    $u['rejection_reason'] = $reason;
                }
                $updated = true;
                break;
            }
        }
        unset($u);

        if (!$updated) {
            adminRespond(false, 'Target user not found.');
        }

        saveUsersFile($users);
        auditLog(
            'staff.rejected',
            $requestingUserId,
            'user',
            $targetUserId,
            "Staff account {$targetUserId} rejected by {$requestingUserId}" . ($reason ? " — reason: {$reason}" : '')
        );
        adminRespond(true, 'Staff account application rejected.');
    }

    // ── Change role (super_admin only) ─────────────────────────────
    if ($action === 'change_role') {
        $actor   = requireRole($requestingUserId, 'super_admin');
        $newRole = adminClean($_POST['new_role'] ?? '');

        $allowedTargetRoles = ['shipper', 'customer', 'driver', 'owner_operator', 'corporate_staff', 'admin', 'super_admin'];
        if (!in_array($newRole, $allowedTargetRoles, true)) {
            adminRespond(false, 'Invalid role specified.');
        }

        if (!$targetUserId) {
            adminRespond(false, 'target_user_id is required.');
        }

        $users      = readUsersFile();
        $updated    = false;
        $oldRole    = '';
        foreach ($users as &$u) {
            if (($u['id'] ?? '') === $targetUserId) {
                $oldRole    = $u['role'] ?? '';
                $u['role']  = $newRole;
                // If promoting to staff-level active, ensure status is active
                if (in_array($newRole, ['admin', 'super_admin', 'shipper', 'customer', 'driver', 'owner_operator'], true)) {
                    $u['status'] = 'active';
                }
                $updated = true;
                break;
            }
        }
        unset($u);

        if (!$updated) {
            adminRespond(false, 'Target user not found.');
        }

        saveUsersFile($users);
        auditLog(
            'user.role_changed',
            $requestingUserId,
            'user',
            $targetUserId,
            "Role changed for {$targetUserId}: {$oldRole} → {$newRole} by {$requestingUserId}"
        );
        adminRespond(true, "User role updated to {$newRole}.");
    }

    // ── Create admin account (super_admin only) ────────────────────
    if ($action === 'create_admin') {
        $actor     = requireRole($requestingUserId, 'super_admin');
        $firstName = adminClean($_POST['firstName'] ?? '');
        $lastName  = adminClean($_POST['lastName']  ?? '');
        $email     = adminClean($_POST['email']      ?? '');
        $password  = $_POST['password']              ?? '';
        $newRole   = adminClean($_POST['role']        ?? 'admin');

        if (!$firstName || !$lastName || !$email || !$password) {
            adminRespond(false, 'firstName, lastName, email, and password are required.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            adminRespond(false, 'Invalid email address.');
        }
        if (strlen($password) < 8) {
            adminRespond(false, 'Password must be at least 8 characters.');
        }
        if (!in_array($newRole, ['admin', 'super_admin'], true)) {
            adminRespond(false, 'Role must be admin or super_admin.');
        }

        // Duplicate check
        $users = readUsersFile();
        foreach ($users as $u) {
            if (isset($u['email']) && strtolower($u['email']) === strtolower($email)) {
                adminRespond(false, 'An account with that email already exists.');
            }
        }

        $timestamp = date('Y-m-d H:i:s');
        $newId     = 'USR-' . strtoupper(substr(md5(uniqid()), 0, 8));

        $entry = [
            'id'            => $newId,
            'timestamp'     => $timestamp,
            'first_name'    => $firstName,
            'last_name'     => $lastName,
            'email'         => $email,
            'company'       => '',
            'role'          => $newRole,
            'status'        => 'active',
            'created_by'    => $requestingUserId,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ];

        $users[] = $entry;
        saveUsersFile($users);

        auditLog(
            'admin.account_created',
            $requestingUserId,
            'user',
            $newId,
            "Admin account created: {$email} (role: {$newRole}) by {$requestingUserId}"
        );
        adminRespond(true, "Admin account created successfully.", ['id' => $newId, 'role' => $newRole]);
    }

    adminRespond(false, 'Unknown action.');
}

adminRespond(false, 'Method not allowed.');
