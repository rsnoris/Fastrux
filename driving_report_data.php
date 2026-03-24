<?php
/**
 * driving_report_data.php — Driver Driving Report API
 *
 * GET  ?action=get_driving_report&driver_id=DRV-XXX&period=daily|weekly|monthly|annual
 *      Returns driving log entries and summary stats for the requested period.
 *
 * POST ?action=add_drive_log  (JSON body with a single drive log entry)
 *      Appends a new drive log entry to data/driving_logs.json.
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

define('DR_DATA_DIR',  __DIR__ . '/data/');
define('DR_LOGS_FILE', DR_DATA_DIR . 'driving_logs.json');

// ── Helpers ──────────────────────────────────────────────────────

function drRespond(bool $ok, string $msg = '', array $extra = []): void
{
    echo json_encode(array_merge(['success' => $ok, 'message' => $msg], $extra));
    exit;
}

function drReadJson(string $file): array
{
    if (!file_exists($file)) {
        return [];
    }
    $raw  = file_get_contents($file);
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function drWriteJson(string $file, array $data): void
{
    if (!is_dir(DR_DATA_DIR)) {
        mkdir(DR_DATA_DIR, 0755, true);
    }
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function drValidateDriverId(string $raw): string
{
    $raw = trim($raw);
    return preg_match('/^DRV-[A-Z0-9]{6,}$/', $raw) ? $raw : '';
}

/**
 * Compute period boundary timestamps.
 */
function periodBoundary(string $period): int
{
    $now = time();
    switch ($period) {
        case 'daily':   return strtotime('today midnight', $now);
        case 'weekly':  return strtotime('monday this week midnight', $now);
        case 'monthly': return strtotime('first day of this month midnight', $now);
        case 'annual':  return strtotime('first day of january this year midnight', $now);
        default:        return strtotime('monday this week midnight', $now);
    }
}

// ── GET ──────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';

    switch ($action) {

        case 'get_driving_report':
            $driverId = drValidateDriverId($_GET['driver_id'] ?? '');
            $period   = in_array($_GET['period'] ?? '', ['daily', 'weekly', 'monthly', 'annual'])
                        ? ($_GET['period'])
                        : 'weekly';

            if ($driverId === '') {
                drRespond(false, 'Invalid driver_id');
            }

            // Load stored logs
            $allLogs = drReadJson(DR_LOGS_FILE);

            // Filter by driver
            $driverLogs = array_values(array_filter($allLogs, function (array $l) use ($driverId): bool {
                return ($l['driver_id'] ?? '') === $driverId;
            }));

            // Filter by period boundary
            $since = periodBoundary($period);
            $driverLogs = array_values(array_filter($driverLogs, function (array $l) use ($since): bool {
                return strtotime($l['started_at'] ?? '0') >= $since;
            }));

            // Sort newest first
            usort($driverLogs, function (array $a, array $b): int {
                return strcmp($b['started_at'], $a['started_at']);
            });

            // Build summary
            $summary = [
                'total_drives'         => count($driverLogs),
                'total_speeding'       => array_sum(array_column($driverLogs, 'speeding_events')),
                'total_distractedness' => array_sum(array_column($driverLogs, 'distractedness')),
                'total_rapid_accel'    => array_sum(array_column($driverLogs, 'rapid_accel_events')),
                'total_hard_braking'   => array_sum(array_column($driverLogs, 'hard_braking_events')),
                'total_distance_mi'    => round(array_sum(array_column($driverLogs, 'distance_mi')), 1),
                'total_duration_min'   => array_sum(array_column($driverLogs, 'duration_min')),
            ];

            drRespond(true, '', ['summary' => $summary, 'logs' => $driverLogs]);

        default:
            drRespond(false, 'Unknown action');
    }
}

// ── POST ─────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? (json_decode(file_get_contents('php://input'), true)['action'] ?? '');

    switch ($action) {

        case 'add_drive_log':
            $body = json_decode(file_get_contents('php://input'), true) ?? [];

            $driverId = drValidateDriverId($body['driver_id'] ?? '');
            if ($driverId === '') {
                drRespond(false, 'Invalid driver_id');
            }

            $required = ['from_address', 'from_lat', 'from_lng', 'to_address', 'to_lat', 'to_lng'];
            foreach ($required as $f) {
                if (empty($body[$f])) {
                    drRespond(false, "Missing field: $f");
                }
            }

            $logs = drReadJson(DR_LOGS_FILE);
            $newLog = [
                'log_id'              => 'LOG-' . strtoupper(bin2hex(random_bytes(4))),
                'driver_id'           => $driverId,
                'started_at'          => $body['started_at'] ?? date('Y-m-d H:i:s'),
                'ended_at'            => $body['ended_at']   ?? date('Y-m-d H:i:s'),
                'from_address'        => htmlspecialchars(strip_tags(trim($body['from_address'])), ENT_QUOTES, 'UTF-8'),
                'from_lat'            => (float) $body['from_lat'],
                'from_lng'            => (float) $body['from_lng'],
                'to_address'          => htmlspecialchars(strip_tags(trim($body['to_address'])), ENT_QUOTES, 'UTF-8'),
                'to_lat'              => (float) $body['to_lat'],
                'to_lng'              => (float) $body['to_lng'],
                'distance_mi'         => (float) ($body['distance_mi'] ?? 0),
                'duration_min'        => (int)   ($body['duration_min'] ?? 0),
                'speeding_events'     => (int)   ($body['speeding_events'] ?? 0),
                'distractedness'      => (int)   ($body['distractedness'] ?? 0),
                'rapid_accel_events'  => (int)   ($body['rapid_accel_events'] ?? 0),
                'hard_braking_events' => (int)   ($body['hard_braking_events'] ?? 0),
            ];
            $logs[] = $newLog;
            drWriteJson(DR_LOGS_FILE, $logs);
            drRespond(true, 'Log added', ['log' => $newLog]);

        default:
            drRespond(false, 'Unknown action');
    }
}
