<?php
/**
 * offers_tracking_data.php — Offers Tracking Board API (MySQL backend)
 *
 * Handles driver location updates, load request management,
 * driver-load matching, and Telegram/SMS notifications.
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/audit.php';

// ── Helpers ──────────────────────────────────────────────────────
function respond(bool $ok, string $msg = '', array $extra = []): void
{
    echo json_encode(array_merge(['success' => $ok, 'message' => $msg], $extra));
    exit;
}

function clean(string $s): string
{
    return htmlspecialchars(strip_tags(trim($s)), ENT_QUOTES, 'UTF-8');
}

/**
 * Read a config value from app_config table.
 */
function getConfig(string $key): string
{
    try {
        $stmt = getDb()->prepare('SELECT config_value FROM app_config WHERE config_key = ?');
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        return $row ? $row['config_value'] : '';
    } catch (Throwable $e) {
        return '';
    }
}

/**
 * Save a config value into app_config (upsert).
 */
function setConfig(string $key, string $value): void
{
    getDb()->prepare(
        'INSERT INTO app_config (config_key, config_value) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE config_value = VALUES(config_value), updated_at = NOW()'
    )->execute([$key, $value]);
}

/**
 * Send an SMS via Twilio REST API.
 */
function sendTwilioSms(string $to, string $body): array
{
    $sid  = getConfig('twilio_account_sid');
    $auth = getConfig('twilio_auth_token');
    $from = getConfig('twilio_from_number');

    if (!$sid || !$auth || !$from) {
        return ['sent' => false, 'error' => 'SMS not configured'];
    }

    $url     = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";
    $payload = http_build_query(['To' => $to, 'From' => $from, 'Body' => $body]);
    $ctx     = stream_context_create(['http' => [
        'method'        => 'POST',
        'header'        => "Content-Type: application/x-www-form-urlencoded\r\n"
                         . "Authorization: Basic " . base64_encode("{$sid}:{$auth}") . "\r\n",
        'content'       => $payload,
        'timeout'       => 10,
        'ignore_errors' => true,
    ]]);

    $resp = file_get_contents($url, false, $ctx);
    if ($resp === false) return ['sent' => false, 'error' => 'Could not reach Twilio API'];
    $respData = json_decode($resp, true);
    if (isset($respData['sid'])) return ['sent' => true, 'error' => ''];
    return ['sent' => false, 'error' => $respData['message'] ?? 'Twilio error'];
}

// ── GET endpoints ────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';

    switch ($action) {

        case 'get_drivers':
            $db   = getDb();
            $rows = $db->query(
                'SELECT da.*,
                        dl.lat, dl.lng, dl.status AS location_status, dl.updated_at AS location_updated
                 FROM   driver_applications da
                 LEFT JOIN driver_locations dl ON dl.driver_id = da.id
                 ORDER BY da.created_at DESC'
            )->fetchAll();

            $result = array_map(function (array $d): array {
                return [
                    'id'               => $d['id'],
                    'name'             => trim($d['first_name'] . ' ' . $d['last_name']),
                    'phone'            => $d['phone']            ?? '',
                    'email'            => $d['email']            ?? '',
                    'van_reg'          => $d['van_reg']          ?? '',
                    'van_type'         => $d['van_type']         ?? '',
                    'payload_kg'       => $d['payload_kg'],
                    'volume_m3'        => $d['volume_m3'],
                    'tail_lift'        => $d['tail_lift'] ? 'yes' : 'no',
                    'operating_areas'  => $d['operating_areas']  ?? '',
                    'driver_status'    => $d['status']           ?? 'pending',
                    'telegram_chat_id' => $d['telegram_chat_id'] ?? '',
                    'lat'              => $d['lat'],
                    'lng'              => $d['lng'],
                    'location_status'  => $d['location_status']  ?? 'offline',
                    'location_updated' => $d['location_updated'],
                ];
            }, $rows);

            respond(true, '', ['drivers' => $result]);

        case 'get_loads':
            $loads = getDb()->query('SELECT * FROM loads ORDER BY created_at DESC')->fetchAll();
            // Deserialise boolean
            foreach ($loads as &$l) {
                $l['requires_tail_lift'] = (bool) $l['requires_tail_lift'];
            }
            unset($l);
            respond(true, '', ['loads' => $loads]);

        case 'get_telegram_config':
            $token  = getConfig('telegram_bot_token');
            $masked = '';
            if ($token) {
                $parts  = explode(':', $token, 2);
                $masked = ($parts[0] ?? '') . ':***' . substr($parts[1] ?? '', -4);
            }
            respond(true, '', ['configured' => $token !== '', 'masked_token' => $masked]);

        case 'get_sms_config':
            $sid       = getConfig('twilio_account_sid');
            $maskedSid = $sid
                ? substr($sid, 0, 6) . '...' . substr($sid, -4)
                : '';
            respond(true, '', [
                'configured' => $sid !== '' && getConfig('twilio_auth_token') !== '' && getConfig('twilio_from_number') !== '',
                'masked_sid' => $maskedSid,
            ]);

        default:
            respond(false, 'Unknown action');
    }
}

// ── POST endpoints ───────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['action'] ?? '';

    switch ($postAction) {

        case 'update_location':
            $driverId = clean($_POST['driver_id'] ?? '');
            $lat      = filter_var($_POST['lat'] ?? '', FILTER_VALIDATE_FLOAT);
            $lng      = filter_var($_POST['lng'] ?? '', FILTER_VALIDATE_FLOAT);
            $status   = in_array($_POST['status'] ?? '', ['available', 'busy', 'offline'], true)
                        ? $_POST['status'] : 'available';

            if (!$driverId)                    respond(false, 'driver_id is required');
            if ($lat === false || $lng === false) respond(false, 'Valid lat and lng are required');

            $db = getDb();
            $db->prepare(
                'INSERT INTO driver_locations (driver_id, lat, lng, status)
                 VALUES (?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE lat = VALUES(lat), lng = VALUES(lng),
                                         status = VALUES(status), updated_at = NOW()'
            )->execute([$driverId, $lat, $lng, $status]);

            auditLog('driver.location_updated', null, 'driver_application', $driverId,
                     ['lat' => $lat, 'lng' => $lng, 'status' => $status]);
            respond(true, 'Location updated');

        case 'add_load':
            foreach (['pickup_address', 'delivery_address', 'cargo_description', 'scheduled_date'] as $f) {
                if (empty(trim($_POST[$f] ?? ''))) respond(false, "Field '$f' is required");
            }

            $pickupLat  = filter_var($_POST['pickup_lat']   ?? '', FILTER_VALIDATE_FLOAT);
            $pickupLng  = filter_var($_POST['pickup_lng']   ?? '', FILTER_VALIDATE_FLOAT);
            $delivLat   = filter_var($_POST['delivery_lat'] ?? '', FILTER_VALIDATE_FLOAT);
            $delivLng   = filter_var($_POST['delivery_lng'] ?? '', FILTER_VALIDATE_FLOAT);
            $weightKg   = filter_var($_POST['weight_kg']    ?? '', FILTER_VALIDATE_FLOAT);
            $volumeM3   = filter_var($_POST['volume_m3']    ?? '', FILTER_VALIDATE_FLOAT);

            // Generate unique FX-XXXXXXXXXXXXXXX (FX- + 15 digits)
            $db = getDb();
            do {
                $digits = '';
                for ($i = 0; $i < 15; $i++) $digits .= random_int(0, 9);
                $id = 'FX-' . $digits;
                $exists = $db->prepare('SELECT id FROM loads WHERE id = ?');
                $exists->execute([$id]);
            } while ($exists->fetch());

            $db->prepare(
                'INSERT INTO loads
                    (id, status, pickup_address, pickup_lat, pickup_lng,
                     delivery_address, delivery_lat, delivery_lng,
                     cargo_description, weight_kg, volume_m3, requires_tail_lift,
                     scheduled_date, contact_name, contact_phone, notes)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
            )->execute([
                $id, 'open',
                clean($_POST['pickup_address']),
                ($pickupLat !== false) ? $pickupLat : null,
                ($pickupLng !== false) ? $pickupLng : null,
                clean($_POST['delivery_address']),
                ($delivLat  !== false) ? $delivLat  : null,
                ($delivLng  !== false) ? $delivLng  : null,
                clean($_POST['cargo_description']),
                ($weightKg  !== false) ? $weightKg  : null,
                ($volumeM3  !== false) ? $volumeM3  : null,
                (($_POST['requires_tail_lift'] ?? 'no') === 'yes') ? 1 : 0,
                clean($_POST['scheduled_date']),
                clean($_POST['contact_name']  ?? ''),
                clean($_POST['contact_phone'] ?? ''),
                clean($_POST['notes']         ?? ''),
            ]);

            $loadStmt = $db->prepare('SELECT * FROM loads WHERE id = ?');
            $loadStmt->execute([$id]);
            $load = $loadStmt->fetch();
            auditLog('load.created', null, 'load', $id, ['pickup' => $_POST['pickup_address']]);
            respond(true, 'Load request created', ['load' => $load]);

        case 'update_load_status':
            $loadId  = clean($_POST['load_id'] ?? '');
            $status  = clean($_POST['status']  ?? '');
            $allowed = ['open', 'matched', 'in_transit', 'completed', 'cancelled'];
            if (!$loadId)                          respond(false, 'load_id is required');
            if (!in_array($status, $allowed, true)) respond(false, 'Invalid status');

            $db   = getDb();
            $stmt = $db->prepare('UPDATE loads SET status = ?, updated_at = NOW() WHERE id = ?');
            $stmt->execute([$status, $loadId]);
            if ($stmt->rowCount() === 0) respond(false, 'Load not found');

            auditLog('load.status_updated', null, 'load', $loadId, ['status' => $status]);
            respond(true, 'Load status updated');

        case 'assign_driver':
            $loadId   = clean($_POST['load_id']   ?? '');
            $driverId = clean($_POST['driver_id'] ?? '');
            if (!$loadId || !$driverId) respond(false, 'load_id and driver_id are required');

            $db   = getDb();
            $load = $db->prepare('SELECT * FROM loads WHERE id = ?');
            $load->execute([$loadId]);
            $load = $load->fetch();
            if (!$load) respond(false, 'Load not found');

            $driver = $db->prepare('SELECT * FROM driver_applications WHERE id = ?');
            $driver->execute([$driverId]);
            $driver = $driver->fetch();
            if (!$driver) respond(false, 'Driver not found');

            $telegramSent  = false;
            $telegramError = '';
            $chatId        = $driver['telegram_chat_id'] ?? '';
            $botToken      = getConfig('telegram_bot_token');

            if ($chatId && $botToken) {
                $schedDate = !empty($load['scheduled_date'])
                    ? date('d M Y', strtotime($load['scheduled_date'])) : 'TBD';
                $weight   = $load['weight_kg']  ? $load['weight_kg']  . ' kg' : 'N/A';
                $volume   = $load['volume_m3']  ? $load['volume_m3']  . ' m³' : 'N/A';
                $tailLift = ($load['requires_tail_lift'] ?? 0) ? 'Required' : 'Not required';

                $msg = "*NEW LOAD OFFER - Fastrux*\n\n"
                     . "*Load ID:* `{$load['id']}`\n"
                     . "*Pickup:* {$load['pickup_address']}\n"
                     . "*Delivery:* {$load['delivery_address']}\n"
                     . "*Cargo:* {$load['cargo_description']}\n"
                     . "*Weight:* {$weight} | *Volume:* {$volume}\n"
                     . "*Tail Lift:* {$tailLift}\n"
                     . "*Date:* {$schedDate}\n";
                if (!empty($load['contact_name'])) {
                    $msg .= "*Contact:* {$load['contact_name']}";
                    if (!empty($load['contact_phone'])) $msg .= " - {$load['contact_phone']}";
                    $msg .= "\n";
                }
                if (!empty($load['notes'])) $msg .= "*Notes:* {$load['notes']}\n";
                $msg .= "\nReply YES to accept or NO to decline.";

                $apiUrl  = "https://api.telegram.org/bot{$botToken}/sendMessage";
                $payload = json_encode(['chat_id' => $chatId, 'text' => $msg, 'parse_mode' => 'Markdown']);
                $ctx     = stream_context_create(['http' => [
                    'method'  => 'POST',
                    'header'  => "Content-Type: application/json\r\n",
                    'content' => $payload,
                    'timeout' => 10,
                ]]);
                $resp = @file_get_contents($apiUrl, false, $ctx);
                if ($resp !== false) {
                    $respData = json_decode($resp, true);
                    if ($respData && ($respData['ok'] ?? false)) {
                        $telegramSent = true;
                    } else {
                        $telegramError = $respData['description'] ?? 'Telegram API error';
                    }
                } else {
                    $telegramError = 'Could not reach Telegram API';
                }
            } elseif (!$botToken) {
                $telegramError = 'Telegram bot token not configured';
            } else {
                $telegramError = 'Driver has no Telegram chat ID saved';
            }

            $smsSent  = false;
            $smsError = '';
            $phone    = $driver['phone'] ?? '';
            if ($phone) {
                $schedDate  = !empty($load['scheduled_date'])
                    ? date('d M Y', strtotime($load['scheduled_date'])) : 'TBD';
                $smsLoadId   = strip_tags($load['id']             ?? '');
                $smsPickup   = strip_tags($load['pickup_address'] ?? '');
                $smsDelivery = strip_tags($load['delivery_address'] ?? '');
                $smsBody = "Fastrux: New load offer [{$smsLoadId}]\n"
                         . "Pickup: {$smsPickup}\n"
                         . "Delivery: {$smsDelivery}\n"
                         . "Date: {$schedDate}\n"
                         . "Reply YES to accept or NO to decline.";
                $smsResult = sendTwilioSms($phone, $smsBody);
                $smsSent   = $smsResult['sent'];
                $smsError  = $smsResult['error'];
            } else {
                $smsError = 'Driver has no phone number saved';
            }

            // Persist the assignment
            $db->prepare(
                'UPDATE loads SET assigned_driver_id = ?, status = ?,
                                  telegram_sent_at = ?, sms_sent_at = ?, updated_at = NOW()
                 WHERE id = ?'
            )->execute([
                $driverId,
                'matched',
                $telegramSent ? date('Y-m-d H:i:s') : null,
                $smsSent      ? date('Y-m-d H:i:s') : null,
                $loadId,
            ]);

            auditLog('load.driver_assigned', null, 'load', $loadId, [
                'driver_id' => $driverId, 'telegram_sent' => $telegramSent, 'sms_sent' => $smsSent,
            ]);

            $notifParts = [];
            if ($telegramSent) $notifParts[] = 'Telegram';
            if ($smsSent)      $notifParts[] = 'SMS';
            $successMsg = 'Driver assigned';
            if ($notifParts) $successMsg .= ' and notified via ' . implode(' & ', $notifParts);
            $warnings = [];
            if (!$telegramSent && $telegramError) $warnings[] = "Telegram: {$telegramError}";
            if (!$smsSent      && $smsError)      $warnings[] = "SMS: {$smsError}";
            if ($warnings) $successMsg .= ' (' . implode('; ', $warnings) . ')';

            respond(true, $successMsg, [
                'telegram_sent' => $telegramSent, 'telegram_error' => $telegramError,
                'sms_sent'      => $smsSent,      'sms_error'      => $smsError,
            ]);

        case 'save_telegram_config':
            $token = trim($_POST['bot_token'] ?? '');
            if (!$token) respond(false, 'bot_token is required');
            if (!preg_match('/^\d+:[A-Za-z0-9_-]{35,}$/', $token))
                respond(false, 'Invalid token format. Expected: 123456789:ABCdef... (at least 35 alphanumeric characters after the colon)');
            setConfig('telegram_bot_token', $token);
            auditLog('config.telegram_saved');
            respond(true, 'Telegram bot token saved successfully');

        case 'save_sms_config':
            $sid  = trim($_POST['account_sid'] ?? '');
            $auth = trim($_POST['auth_token']  ?? '');
            $from = trim($_POST['from_number'] ?? '');
            if (!$sid)  respond(false, 'account_sid is required');
            if (!$auth) respond(false, 'auth_token is required');
            if (!$from) respond(false, 'from_number is required');
            setConfig('twilio_account_sid',  $sid);
            setConfig('twilio_auth_token',   $auth);
            setConfig('twilio_from_number',  $from);
            auditLog('config.sms_saved');
            respond(true, 'SMS (Twilio) config saved successfully');

        default:
            respond(false, 'Unknown action');
    }
}

respond(false, 'Method not allowed');
