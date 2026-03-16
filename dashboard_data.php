<?php
/**
 * Fastrux — Driver Dashboard Data API
 * GET  → returns all driver submissions as JSON
 * POST → updates driver application status
 * GET  ?export=csv → download as CSV
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

define('DATA_DIR',    __DIR__ . '/data/');
define('DRIVERS_JSON', DATA_DIR . 'driver_submissions.json');
define('DRIVERS_CSV',  DATA_DIR . 'driver_submissions.csv');

require_once __DIR__ . '/audit_helper.php';

// ── Helpers ─────────────────────────────────────────────────

function respond(bool $success, string $message, array $extra = []): void {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit;
}

function readDrivers(): array {
    if (!file_exists(DRIVERS_JSON)) {
        return [];
    }
    $data = json_decode(file_get_contents(DRIVERS_JSON), true);
    return is_array($data) ? $data : [];
}

function writeDrivers(array $drivers): void {
    if (!is_dir(DATA_DIR)) {
        mkdir(DATA_DIR, 0755, true);
    }
    file_put_contents(
        DRIVERS_JSON,
        json_encode(array_values($drivers), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );
}

// ── CSV export ───────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['export']) && $_GET['export'] === 'csv') {
    $drivers = readDrivers();
    if (empty($drivers)) {
        header('Content-Type: text/plain');
        echo 'No submissions yet.';
        exit;
    }

    // Collect all unique keys across all submissions
    $allKeys = [];
    foreach ($drivers as $d) {
        foreach (array_keys($d) as $k) {
            if (!in_array($k, $allKeys, true)) {
                $allKeys[] = $k;
            }
        }
    }

    // Skip binary-heavy fields
    $skip = ['photo_front', 'photo_side', 'photo_interior', 'doc_licence', 'doc_insurance', 'doc_mot'];
    $cols = array_filter($allKeys, fn($k) => !in_array($k, $skip, true));

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="driver_submissions_' . date('Ymd_His') . '.csv"');
    $fp = fopen('php://output', 'w');
    fputcsv($fp, $cols);
    foreach ($drivers as $d) {
        $row = [];
        foreach ($cols as $k) {
            $val = $d[$k] ?? '';
            if (is_array($val)) {
                $val = implode(', ', $val);
            }
            $row[] = $val;
        }
        fputcsv($fp, $row);
    }
    fclose($fp);
    exit;
}

// ── GET — return all drivers ─────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $drivers = readDrivers();

    // Build photo URLs for each driver (relative to web root)
    foreach ($drivers as &$d) {
        $photoFields = ['photo_front', 'photo_side', 'photo_interior', 'doc_licence', 'doc_insurance', 'doc_mot'];
        foreach ($photoFields as $field) {
            if (isset($d[$field]) && is_array($d[$field])) {
                // Convert stored relative paths to web-accessible paths
                $d[$field] = array_map(function($path) {
                    // Remove leading DATA_DIR prefix if present, return relative path
                    $rel = ltrim(str_replace(__DIR__, '', $path), '/\\');
                    return $rel;
                }, $d[$field]);
            }
        }
    }
    unset($d);

    respond(true, 'OK', ['drivers' => $drivers, 'total' => count($drivers)]);
}

// ── POST — update status ─────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action   = $_POST['action']    ?? '';
    $driverId = $_POST['driver_id'] ?? '';
    $status   = $_POST['status']    ?? '';

    if ($action !== 'update_status') {
        respond(false, 'Unknown action.');
    }

    $allowed = ['pending', 'approved', 'rejected'];
    if (!in_array($status, $allowed, true)) {
        respond(false, 'Invalid status value.');
    }

    if (!$driverId) {
        respond(false, 'Driver ID is required.');
    }

    $drivers = readDrivers();
    $found   = false;
    foreach ($drivers as &$d) {
        if (($d['id'] ?? '') === $driverId) {
            $d['status']     = $status;
            $d['updated_at'] = date('Y-m-d H:i:s');
            // Optionally update telegram_chat_id when provided
            if (isset($_POST['telegram_chat_id'])) {
                $d['telegram_chat_id'] = htmlspecialchars(
                    strip_tags(trim($_POST['telegram_chat_id'])),
                    ENT_QUOTES,
                    'UTF-8'
                );
            }
            $found = true;
            break;
        }
    }
    unset($d);

    if (!$found) {
        respond(false, 'Driver not found.');
    }

    writeDrivers($drivers);
    auditLog('driver.status_changed', '', 'driver', $driverId, "Driver {$driverId} status changed to '{$status}'");
    respond(true, 'Status updated successfully.');
}

respond(false, 'Method not allowed.');
