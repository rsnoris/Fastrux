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
 * Generate deterministic seed driving logs for a driver+period when no real
 * data exists.  Uses a hash of (driver_id, day_offset) so each driver gets
 * different-looking but reproducible data.
 */
function generateSeedLogs(string $driverId, string $period): array
{
    // US city pairs used as source / destination examples
    $cityPairs = [
        [
            'from' => 'Chicago, IL', 'from_lat' => 41.8781, 'from_lng' => -87.6298,
            'to'   => 'Milwaukee, WI', 'to_lat' => 43.0389, 'to_lng' => -87.9065,
        ],
        [
            'from' => 'Dallas, TX', 'from_lat' => 32.7767, 'from_lng' => -96.7970,
            'to'   => 'Houston, TX', 'to_lat' => 29.7604, 'to_lng' => -95.3698,
        ],
        [
            'from' => 'Atlanta, GA', 'from_lat' => 33.7490, 'from_lng' => -84.3880,
            'to'   => 'Nashville, TN', 'to_lat' => 36.1627, 'to_lng' => -86.7816,
        ],
        [
            'from' => 'Los Angeles, CA', 'from_lat' => 34.0522, 'from_lng' => -118.2437,
            'to'   => 'San Diego, CA', 'to_lat' => 32.7157, 'to_lng' => -117.1611,
        ],
        [
            'from' => 'New York, NY', 'from_lat' => 40.7128, 'from_lng' => -74.0060,
            'to'   => 'Philadelphia, PA', 'to_lat' => 39.9526, 'to_lng' => -75.1652,
        ],
        [
            'from' => 'Phoenix, AZ', 'from_lat' => 33.4484, 'from_lng' => -112.0740,
            'to'   => 'Tucson, AZ', 'to_lat' => 32.2226, 'to_lng' => -110.9747,
        ],
        [
            'from' => 'Denver, CO', 'from_lat' => 39.7392, 'from_lng' => -104.9903,
            'to'   => 'Colorado Springs, CO', 'to_lat' => 38.8339, 'to_lng' => -104.8214,
        ],
        [
            'from' => 'Seattle, WA', 'from_lat' => 47.6062, 'from_lng' => -122.3321,
            'to'   => 'Portland, OR', 'to_lat' => 45.5231, 'to_lng' => -122.6765,
        ],
        [
            'from' => 'Miami, FL', 'from_lat' => 25.7617, 'from_lng' => -80.1918,
            'to'   => 'Tampa, FL', 'to_lat' => 27.9506, 'to_lng' => -82.4572,
        ],
        [
            'from' => 'Detroit, MI', 'from_lat' => 42.3314, 'from_lng' => -83.0458,
            'to'   => 'Cleveland, OH', 'to_lat' => 41.4993, 'to_lng' => -81.6944,
        ],
    ];

    // Determine how many days of history to generate
    $daysInMonth = (int) date('t'); // actual days in current month
    $daysMap = ['daily' => 1, 'weekly' => 7, 'monthly' => $daysInMonth, 'annual' => 365];
    $days    = $daysMap[$period] ?? 7;

    // Use a numeric seed derived from driver_id for reproducibility
    $seed = crc32($driverId);

    $logs = [];
    $now  = time();

    for ($dayOffset = 0; $dayOffset < $days; $dayOffset++) {
        // Pseudo-random number of drives per day (1–4) using the seed
        $hash       = abs(crc32($driverId . '_' . $dayOffset));
        $drivesDay  = ($hash % 3) + 1; // 1, 2, or 3 drives per day

        for ($d = 0; $d < $drivesDay; $d++) {
            $h2        = abs(crc32($driverId . '_' . $dayOffset . '_' . $d));
            $pair      = $cityPairs[$h2 % count($cityPairs)];

            // Occasionally swap source/destination for variety
            if (($h2 >> 4) % 2 === 1) {
                $pair = [
                    'from' => $pair['to'], 'from_lat' => $pair['to_lat'], 'from_lng' => $pair['to_lng'],
                    'to'   => $pair['from'], 'to_lat'  => $pair['from_lat'], 'to_lng'   => $pair['from_lng'],
                ];
            }

            // Slight coordinate jitter (~±0.1 degree) so each log looks distinct on a mini-map
            $jitter = function (float $v, int $seed): float {
                return $v + (($seed % 100) - 50) * 0.002;
            };

            // Timestamps within the day
            $dayStart  = $now - ($dayOffset * 86400);
            $startHour = 6 + (($h2 >> 8) % 14); // 06:00 – 20:00
            $startMin  = ($h2 >> 16) % 60;
            $startTs   = $dayStart - ($dayStart % 86400) + $startHour * 3600 + $startMin * 60;
            $durationMin = 25 + ($h2 % 180); // 25–204 minutes

            $logs[] = [
                'log_id'              => 'LOG-' . strtoupper(substr(md5($driverId . $dayOffset . $d), 0, 8)),
                'driver_id'           => $driverId,
                'started_at'          => date('Y-m-d H:i:s', $startTs),
                'ended_at'            => date('Y-m-d H:i:s', $startTs + $durationMin * 60),
                'from_address'        => $pair['from'],
                'from_lat'            => round($jitter($pair['from_lat'], $h2 ^ 0xA1), 5),
                'from_lng'            => round($jitter($pair['from_lng'], $h2 ^ 0xB2), 5),
                'to_address'          => $pair['to'],
                'to_lat'              => round($jitter($pair['to_lat'], $h2 ^ 0xC3), 5),
                'to_lng'              => round($jitter($pair['to_lng'], $h2 ^ 0xD4), 5),
                'distance_mi'         => round(40 + ($h2 % 220), 1),
                'duration_min'        => $durationMin,
                'speeding_events'     => ($h2 >> 2) % 6,          // 0–5
                'distractedness'      => ($h2 >> 6) % 4,          // 0–3
                'rapid_accel_events'  => ($h2 >> 10) % 5,         // 0–4
                'hard_braking_events' => ($h2 >> 14) % 5,         // 0–4
            ];
        }
    }

    // Sort newest first
    usort($logs, function (array $a, array $b): int {
        return strcmp($b['started_at'], $a['started_at']);
    });

    return $logs;
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

            // Load stored logs then fall back to seed data
            $allLogs = drReadJson(DR_LOGS_FILE);

            // Filter by driver
            $driverLogs = array_values(array_filter($allLogs, function (array $l) use ($driverId): bool {
                return ($l['driver_id'] ?? '') === $driverId;
            }));

            // If no real data, generate seed logs
            if (empty($driverLogs)) {
                $driverLogs = generateSeedLogs($driverId, $period);
            } else {
                // Filter by period boundary
                $since = periodBoundary($period);
                $driverLogs = array_values(array_filter($driverLogs, function (array $l) use ($since): bool {
                    return strtotime($l['started_at'] ?? '0') >= $since;
                }));

                // Sort newest first
                usort($driverLogs, function (array $a, array $b): int {
                    return strcmp($b['started_at'], $a['started_at']);
                });
            }

            // Build summary
            $summary = [
                'total_drives'       => count($driverLogs),
                'total_speeding'     => array_sum(array_column($driverLogs, 'speeding_events')),
                'total_distractedness' => array_sum(array_column($driverLogs, 'distractedness')),
                'total_rapid_accel'  => array_sum(array_column($driverLogs, 'rapid_accel_events')),
                'total_hard_braking' => array_sum(array_column($driverLogs, 'hard_braking_events')),
                'total_distance_mi'  => round(array_sum(array_column($driverLogs, 'distance_mi')), 1),
                'total_duration_min' => array_sum(array_column($driverLogs, 'duration_min')),
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
