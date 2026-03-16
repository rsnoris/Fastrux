<?php
/**
 * Fastrux — Driver Dashboard Data API (MySQL backend)
 * GET          → returns all driver applications as JSON
 * GET ?export=csv → download as CSV
 * POST         → updates driver application status
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/audit.php';

function respond(bool $success, string $message, array $extra = []): void {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit;
}

// ── CSV export ───────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['export']) && $_GET['export'] === 'csv') {
    $db      = getDb();
    $drivers = $db->query('SELECT * FROM driver_applications ORDER BY created_at DESC')->fetchAll();

    if (empty($drivers)) {
        header('Content-Type: text/plain');
        echo 'No submissions yet.';
        exit;
    }

    // Skip binary/JSON columns for CSV
    $skip = ['photo_front_paths', 'photo_side_paths', 'photo_interior_paths',
             'doc_licence_paths', 'doc_insurance_paths', 'doc_mot_paths', 'availability'];
    $cols = array_filter(array_keys($drivers[0]), fn($k) => !in_array($k, $skip, true));

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="driver_submissions_' . date('Ymd_His') . '.csv"');
    $fp = fopen('php://output', 'w');
    fputcsv($fp, $cols);
    foreach ($drivers as $d) {
        $row = [];
        foreach ($cols as $k) {
            $val = $d[$k] ?? '';
            if (is_array($val)) $val = implode(', ', $val);
            $row[] = $val;
        }
        fputcsv($fp, $row);
    }
    fclose($fp);
    exit;
}

// ── GET — return all drivers ─────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $db      = getDb();
    $drivers = $db->query('SELECT * FROM driver_applications ORDER BY created_at DESC')->fetchAll();

    // Decode JSON path arrays for file fields
    $fileFields = ['photo_front_paths', 'photo_side_paths', 'photo_interior_paths',
                   'doc_licence_paths', 'doc_insurance_paths', 'doc_mot_paths'];
    foreach ($drivers as &$d) {
        foreach ($fileFields as $f) {
            $d[$f] = json_decode($d[$f] ?? '[]', true) ?: [];
        }
        // Expose availability as decoded array too
        $d['availability'] = json_decode($d['availability'] ?? '[]', true) ?: [];
    }
    unset($d);

    respond(true, 'OK', ['drivers' => $drivers, 'total' => count($drivers)]);
}

// ── POST — update status ─────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action   = $_POST['action']    ?? '';
    $driverId = $_POST['driver_id'] ?? '';
    $status   = $_POST['status']    ?? '';

    if ($action !== 'update_status') respond(false, 'Unknown action.');

    $allowed = ['pending', 'approved', 'rejected'];
    if (!in_array($status, $allowed, true)) respond(false, 'Invalid status value.');
    if (!$driverId) respond(false, 'Driver ID is required.');

    $db    = getDb();
    $extra = '';
    $args  = [$status, $driverId];

    if (isset($_POST['telegram_chat_id'])) {
        $chatId = htmlspecialchars(strip_tags(trim($_POST['telegram_chat_id'])), ENT_QUOTES, 'UTF-8');
        $extra  = ', telegram_chat_id = ?';
        $args   = [$status, $chatId, $driverId];
    }

    $stmt = $db->prepare("UPDATE driver_applications SET status = ?, updated_at = NOW(){$extra} WHERE id = ?");
    $stmt->execute($args);

    if ($stmt->rowCount() === 0) respond(false, 'Driver not found.');

    auditLog('driver.status_updated', null, 'driver_application', $driverId, ['status' => $status]);
    respond(true, 'Status updated successfully.');
}

respond(false, 'Method not allowed.');
