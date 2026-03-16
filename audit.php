<?php
/**
 * audit.php — Audit log helper
 *
 * Call auditLog() from any API endpoint to record user actions.
 * Failures are silently logged to the PHP error log so they never
 * interrupt the main request flow.
 */

require_once __DIR__ . '/db.php';

/**
 * Write one row to the audit_log table.
 *
 * @param string      $action      Short action label, e.g. 'user.login', 'quote.created'
 * @param string|null $userId      ID of the acting user (null for anonymous)
 * @param string|null $entityType  The type of affected entity, e.g. 'quote', 'driver'
 * @param string|null $entityId    The ID of the affected entity
 * @param array       $details     Any additional structured data to store as JSON
 */
function auditLog(
    string  $action,
    ?string $userId     = null,
    ?string $entityType = null,
    ?string $entityId   = null,
    array   $details    = []
): void {
    try {
        $db = getDb();
        $db->prepare(
            'INSERT INTO audit_log
                (user_id, action, entity_type, entity_id, details, ip_address, user_agent)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        )->execute([
            $userId,
            $action,
            $entityType,
            $entityId,
            $details ? json_encode($details, JSON_UNESCAPED_UNICODE) : null,
            $_SERVER['REMOTE_ADDR']    ?? null,
            substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
        ]);
    } catch (Throwable $e) {
        // Audit failures must never break the main request
        error_log('auditLog error: ' . $e->getMessage());
    }
}
