<?php
/**
 * Fastrux — Health Check Endpoint
 *
 * GET /health.php
 *   → { status, version, timestamp, checks: { data_dir, data_writable, php_version } }
 *
 * Designed for:
 *   • Load balancer health probes (AWS ALB, GCP LB, nginx upstream_check)
 *   • Container orchestration readiness/liveness probes (Kubernetes, ECS)
 *   • Uptime monitoring services (UptimeRobot, Pingdom, Better Uptime)
 *   • On-call alert pipelines (PagerDuty, OpsGenie)
 *
 * Response codes:
 *   200  All checks passing — service is healthy
 *   503  One or more checks failing — service is degraded
 *
 * This endpoint does NOT expose sensitive configuration or credentials.
 * It only verifies that the application can function (filesystem writable, PHP OK).
 */

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');

define('HEALTH_VERSION', '1.0.0');
define('HEALTH_DATA_DIR', __DIR__ . '/data/');

// ── Run checks ────────────────────────────────────────────────────

$checks  = [];
$healthy = true;

// 1. PHP version (require ≥ 8.0)
$phpOk             = version_compare(PHP_VERSION, '8.0.0', '>=');
$checks['php']     = [
    'status'  => $phpOk ? 'ok' : 'fail',
    'version' => PHP_VERSION,
    'detail'  => $phpOk ? 'PHP ' . PHP_VERSION : 'PHP ≥ 8.0 required; found ' . PHP_VERSION,
];
if (!$phpOk) $healthy = false;

// 2. Data directory exists (or can be created)
$dataDirExists = is_dir(HEALTH_DATA_DIR);
if (!$dataDirExists) {
    $dataDirExists = @mkdir(HEALTH_DATA_DIR, 0755, true);
}
$checks['data_dir'] = [
    'status' => $dataDirExists ? 'ok' : 'fail',
    'path'   => HEALTH_DATA_DIR,
    'detail' => $dataDirExists ? 'Directory exists' : 'Cannot create data directory',
];
if (!$dataDirExists) $healthy = false;

// 3. Data directory is writable
$writable = $dataDirExists && is_writable(HEALTH_DATA_DIR);
$checks['data_writable'] = [
    'status' => $writable ? 'ok' : 'fail',
    'detail' => $writable ? 'Data directory is writable' : 'Data directory is not writable',
];
if (!$writable) $healthy = false;

// 4. Required PHP extensions
$requiredExtensions = ['json', 'mbstring', 'session'];
$missingExts = [];
foreach ($requiredExtensions as $ext) {
    if (!extension_loaded($ext)) {
        $missingExts[] = $ext;
    }
}
$extOk = empty($missingExts);
$checks['php_extensions'] = [
    'status'   => $extOk ? 'ok' : 'fail',
    'required' => $requiredExtensions,
    'missing'  => $missingExts,
    'detail'   => $extOk ? 'All required extensions loaded' : 'Missing extensions: ' . implode(', ', $missingExts),
];
if (!$extOk) $healthy = false;

// 5. Seed data directory (needed for nearby places / maps)
$seedDirOk = is_dir(__DIR__ . '/seed_data');
$checks['seed_data'] = [
    'status' => $seedDirOk ? 'ok' : 'warn',
    'detail' => $seedDirOk ? 'Seed data directory present' : 'Seed data directory missing (maps/POI degraded)',
];
// This is a warning only — don't mark unhealthy

// ── Build response ─────────────────────────────────────────────────

$statusCode = $healthy ? 200 : 503;
http_response_code($statusCode);

$response = [
    'status'    => $healthy ? 'healthy' : 'unhealthy',
    'version'   => HEALTH_VERSION,
    'timestamp' => date('c'),                // ISO 8601
    'uptime_s'  => (int)(microtime(true) - ($_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true))),
    'checks'    => $checks,
];

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
