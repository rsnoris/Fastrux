<?php
/**
 * Fastrux — Audit Log Helper
 * Include this file to get the auditLog() function.
 * Safe to include multiple times (include_once).
 */

if (!defined('AUDIT_DATA_DIR')) {
    define('AUDIT_DATA_DIR', __DIR__ . '/data/');
}

/**
 * Write an audit event to data/audit_log.json.
 *
 * @param string $action      e.g. 'user.login', 'driver.status_changed'
 * @param string $userId      ID of the acting user (USR-XXXXXXXX) or ''
 * @param string $entityType  e.g. 'user', 'driver', 'quote', 'load'
 * @param string $entityId    ID of the affected entity or ''
 * @param string $details     Short human-readable description
 */
function auditLog(
    string $action,
    string $userId     = '',
    string $entityType = '',
    string $entityId   = '',
    string $details    = ''
): void {
    $logFile = AUDIT_DATA_DIR . 'audit_log.json';

    // Derive IP — trust first hop of forwarded header, fall back to REMOTE_ADDR
    $rawIp = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
    $ip    = trim(explode(',', $rawIp)[0]);

    $entry = [
        'id'          => 'AUD-' . strtoupper(substr(md5(uniqid('', true)), 0, 8)),
        'timestamp'   => date('Y-m-d H:i:s'),
        'action'      => htmlspecialchars(strip_tags($action),      ENT_QUOTES, 'UTF-8'),
        'user_id'     => htmlspecialchars(strip_tags($userId),      ENT_QUOTES, 'UTF-8'),
        'entity_type' => htmlspecialchars(strip_tags($entityType),  ENT_QUOTES, 'UTF-8'),
        'entity_id'   => htmlspecialchars(strip_tags($entityId),    ENT_QUOTES, 'UTF-8'),
        'details'     => htmlspecialchars(strip_tags($details),     ENT_QUOTES, 'UTF-8'),
        'ip_address'  => htmlspecialchars(strip_tags($ip),          ENT_QUOTES, 'UTF-8'),
        'user_agent'  => htmlspecialchars(
            substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 200),
            ENT_QUOTES,
            'UTF-8'
        ),
    ];

    if (!is_dir(AUDIT_DATA_DIR)) {
        mkdir(AUDIT_DATA_DIR, 0755, true);
    }

    $log = [];
    if (file_exists($logFile)) {
        $existing = json_decode(file_get_contents($logFile), true);
        if (is_array($existing)) {
            $log = $existing;
        }
    }

    $log[] = $entry;

    // Cap log at 10 000 entries (keep most recent)
    if (count($log) > 10000) {
        $log = array_slice($log, -10000);
    }

    file_put_contents(
        $logFile,
        json_encode($log, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );
}
