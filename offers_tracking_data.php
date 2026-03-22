<?php
/**
 * offers_tracking_data.php — Offers Tracking Board API
 *
 * Handles driver location updates, load request management,
 * driver–load matching, and Telegram offer notifications.
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ── Constants ────────────────────────────────────────────────────
define('DATA_DIR',       __DIR__ . '/data/');
define('DRIVERS_JSON',   DATA_DIR . 'driver_submissions.json');
define('LOCATIONS_JSON', DATA_DIR . 'driver_locations.json');
define('LOADS_JSON',     DATA_DIR . 'load_requests.json');
define('TELEGRAM_CFG',   DATA_DIR . 'telegram_config.json');
define('SMS_CFG',        DATA_DIR . 'sms_config.json');

require_once __DIR__ . '/audit_helper.php';

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
 * Validate and return a user ID in USR-XXXXXXXX format, or empty string if invalid.
 */
function validateUserId(string $raw): string
{
    $raw = trim($raw);
    return preg_match('/^USR-[A-Z0-9]{8}$/', $raw) ? $raw : '';
}

function readJson(string $file): array
{
    if (!file_exists($file)) {
        return [];
    }
    $raw  = file_get_contents($file);
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function writeJson(string $file, array $data): void
{
    if (!is_dir(DATA_DIR)) {
        mkdir(DATA_DIR, 0755, true);
    }
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

/**
 * Send an SMS via Twilio REST API.
 * Returns ['sent' => bool, 'error' => string].
 */
function sendTwilioSms(string $to, string $body): array
{
    $cfg = readJson(SMS_CFG);
    $sid  = $cfg['account_sid'] ?? '';
    $auth = $cfg['auth_token']  ?? '';
    $from = $cfg['from_number'] ?? '';

    if (!$sid || !$auth || !$from) {
        return ['sent' => false, 'error' => 'SMS not configured'];
    }

    $url     = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";
    $payload = http_build_query(['To' => $to, 'From' => $from, 'Body' => $body]);

    $ctx = stream_context_create([
        'http' => [
            'method'          => 'POST',
            'header'          => "Content-Type: application/x-www-form-urlencoded\r\n"
                               . "Authorization: Basic " . base64_encode("{$sid}:{$auth}") . "\r\n",
            'content'         => $payload,
            'timeout'         => 10,
            'ignore_errors'   => true,
        ],
    ]);
    $resp = file_get_contents($url, false, $ctx);
    if ($resp === false) {
        return ['sent' => false, 'error' => 'Could not reach Twilio API'];
    }
    $respData = json_decode($resp, true);
    if (isset($respData['sid'])) {
        return ['sent' => true, 'error' => ''];
    }
    return ['sent' => false, 'error' => $respData['message'] ?? 'Twilio error'];
}

// ── GET endpoints ────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';

    switch ($action) {

        // Return all drivers merged with their latest location data
        case 'get_drivers':
            $drivers   = readJson(DRIVERS_JSON);
            $locations = readJson(LOCATIONS_JSON);

            // Filter drivers by owner when user_id is provided (owner_operator scope)
            $userId = validateUserId($_GET['user_id'] ?? '');
            if ($userId !== '') {
                $drivers = array_values(array_filter($drivers, function (array $d) use ($userId): bool {
                    return ($d['submitted_by'] ?? '') === $userId;
                }));
            }

            $result = array_map(function (array $d) use ($locations): array {
                $loc = $locations[$d['id']] ?? null;
                return [
                    'id'               => $d['id'],
                    'name'             => trim(($d['first_name'] ?? '') . ' ' . ($d['last_name'] ?? '')),
                    'phone'            => $d['phone']           ?? '',
                    'email'            => $d['email']           ?? '',
                    'van_reg'          => $d['van_reg']         ?? '',
                    'van_type'         => $d['van_type']        ?? '',
                    'payload_kg'       => $d['payload_kg']      ?? null,
                    'volume_m3'        => $d['volume_m3']       ?? null,
                    'tail_lift'        => $d['tail_lift']       ?? 'no',
                    'operating_areas'  => $d['operating_areas'] ?? '',
                    'driver_status'    => $d['status']          ?? 'pending',
                    'telegram_chat_id' => $d['telegram_chat_id'] ?? '',
                    'lat'              => $loc['lat']        ?? null,
                    'lng'              => $loc['lng']        ?? null,
                    'location_status'  => $loc['status']     ?? 'offline',
                    'location_updated' => $loc['updated_at'] ?? null,
                ];
            }, $drivers);

            respond(true, '', ['drivers' => $result]);

        // Return all load requests (newest first), filtered by owner when user_id is provided
        case 'get_loads':
            $loads = readJson(LOADS_JSON);

            $userId = validateUserId($_GET['user_id'] ?? '');
            if ($userId !== '') {
                $loads = array_values(array_filter($loads, function (array $l) use ($userId): bool {
                    return ($l['created_by'] ?? '') === $userId;
                }));
            }

            respond(true, '', ['loads' => array_reverse($loads)]);

        // Return masked Telegram bot-token status
        case 'get_telegram_config':
            $cfg = readJson(TELEGRAM_CFG);
            $masked = '';
            if (!empty($cfg['bot_token'])) {
                $parts  = explode(':', $cfg['bot_token'], 2);
                $masked = ($parts[0] ?? '') . ':***' . substr($parts[1] ?? '', -4);
            }
            respond(true, '', [
                'configured'   => !empty($cfg['bot_token']),
                'masked_token' => $masked,
            ]);

        // Return masked SMS (Twilio) config status
        case 'get_sms_config':
            $smsCfg = readJson(SMS_CFG);
            $maskedSid = '';
            if (!empty($smsCfg['account_sid'])) {
                $maskedSid = substr($smsCfg['account_sid'], 0, 6) . '…' . substr($smsCfg['account_sid'], -4);
            }
            respond(true, '', [
                'configured'  => !empty($smsCfg['account_sid']) && !empty($smsCfg['auth_token']) && !empty($smsCfg['from_number']),
                'masked_sid'  => $maskedSid,
            ]);

        default:
            respond(false, 'Unknown action');
    }
}

// ── POST endpoints ───────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['action'] ?? '';

    switch ($postAction) {

        // Update a driver's GPS coordinates and availability status
        case 'update_location':
            $driverId = clean($_POST['driver_id'] ?? '');
            $lat      = filter_var($_POST['lat'] ?? '', FILTER_VALIDATE_FLOAT);
            $lng      = filter_var($_POST['lng'] ?? '', FILTER_VALIDATE_FLOAT);
            $status   = in_array($_POST['status'] ?? '', ['available', 'busy', 'offline'], true)
                        ? $_POST['status'] : 'available';

            if (!$driverId)            respond(false, 'driver_id is required');
            if ($lat === false || $lng === false) respond(false, 'Valid lat and lng are required');

            $locations              = readJson(LOCATIONS_JSON);
            $locations[$driverId]   = [
                'lat'        => $lat,
                'lng'        => $lng,
                'status'     => $status,
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            writeJson(LOCATIONS_JSON, $locations);
            respond(true, 'Location updated');

        // Create a new load request
        case 'add_load':
            $required = ['pickup_address', 'delivery_address', 'cargo_description', 'scheduled_date'];
            foreach ($required as $field) {
                if (empty(trim($_POST[$field] ?? ''))) {
                    respond(false, "Field '$field' is required");
                }
            }

            $pickupLat    = filter_var($_POST['pickup_lat']    ?? '', FILTER_VALIDATE_FLOAT);
            $pickupLng    = filter_var($_POST['pickup_lng']    ?? '', FILTER_VALIDATE_FLOAT);
            $delivLat     = filter_var($_POST['delivery_lat']  ?? '', FILTER_VALIDATE_FLOAT);
            $delivLng     = filter_var($_POST['delivery_lng']  ?? '', FILTER_VALIDATE_FLOAT);
            $weightKg     = filter_var($_POST['weight_kg']     ?? '', FILTER_VALIDATE_FLOAT);
            $volumeM3     = filter_var($_POST['volume_m3']     ?? '', FILTER_VALIDATE_FLOAT);
            $freightValue = filter_var($_POST['freight_value'] ?? '', FILTER_VALIDATE_FLOAT);

            $rawCreatedBy = trim($_POST['created_by'] ?? '');
            $createdBy    = validateUserId($rawCreatedBy);

            $loads  = readJson(LOADS_JSON);
            $existingIds = array_column($loads, 'id');
            // Generate a unique FX-XXXXXXXXXXXXXXX tracking ID (FX- + 15 digits)
            do {
                $digits = '';
                for ($i = 0; $i < 15; $i++) $digits .= random_int(0, 9);
                $id = 'FX-' . $digits;
            } while (in_array($id, $existingIds, true));
            $load = [
                'id'                 => $id,
                'created_at'         => date('Y-m-d H:i:s'),
                'created_by'         => $createdBy,
                'status'             => 'pending_payment',
                'pickup_address'     => clean($_POST['pickup_address']),
                'pickup_lat'         => ($pickupLat !== false) ? $pickupLat : null,
                'pickup_lng'         => ($pickupLng !== false) ? $pickupLng : null,
                'delivery_address'   => clean($_POST['delivery_address']),
                'delivery_lat'       => ($delivLat  !== false) ? $delivLat  : null,
                'delivery_lng'       => ($delivLng  !== false) ? $delivLng  : null,
                'cargo_description'  => clean($_POST['cargo_description']),
                'weight_kg'          => ($weightKg  !== false) ? $weightKg  : null,
                'volume_m3'          => ($volumeM3  !== false) ? $volumeM3  : null,
                'freight_value'      => ($freightValue !== false && $freightValue > 0) ? round($freightValue, 2) : null,
                'requires_tail_lift' => ($_POST['requires_tail_lift'] ?? 'no') === 'yes',
                'scheduled_date'     => clean($_POST['scheduled_date']),
                'contact_name'       => clean($_POST['contact_name']  ?? ''),
                'contact_phone'      => clean($_POST['contact_phone'] ?? ''),
                'notes'              => clean($_POST['notes']          ?? ''),
                'assigned_driver_id' => null,
                'telegram_sent_at'   => null,
                'payment_id'         => null,
                'payment_status'     => 'unpaid',
                'payment_amount'     => null,
                'payment_method'     => null,
                'paid_at'            => null,
            ];

            $loads[] = $load;
            writeJson(LOADS_JSON, $loads);
            auditLog('load.created', $createdBy, 'load', $id, "Load {$id} created: " . clean($_POST['pickup_address']) . ' → ' . clean($_POST['delivery_address']));
            respond(true, 'Load request created', ['load' => $load]);

        // Update the status of an existing load
        case 'update_load_status':
            $loadId  = clean($_POST['load_id'] ?? '');
            $status  = clean($_POST['status']  ?? '');
            $allowed = ['open', 'matched', 'in_transit', 'completed', 'cancelled'];

            if (!$loadId)               respond(false, 'load_id is required');
            if (!in_array($status, $allowed, true)) respond(false, 'Invalid status');

            $loads = readJson(LOADS_JSON);
            $found = false;
            foreach ($loads as &$load) {
                if ($load['id'] === $loadId) {
                    $load['status'] = $status;
                    $found = true;
                    break;
                }
            }
            unset($load);

            if (!$found) respond(false, 'Load not found');
            writeJson(LOADS_JSON, $loads);
            auditLog('load.status_changed', '', 'load', $loadId, "Load {$loadId} status changed to '{$status}'");
            respond(true, 'Load status updated');

        // Assign a driver to a load and send Telegram notification
        case 'assign_driver':
            $loadId   = clean($_POST['load_id']   ?? '');
            $driverId = clean($_POST['driver_id'] ?? '');

            if (!$loadId || !$driverId) respond(false, 'load_id and driver_id are required');

            $loads   = readJson(LOADS_JSON);
            $drivers = readJson(DRIVERS_JSON);

            $load     = null;
            $driver   = null;
            $loadIdx  = -1;

            foreach ($loads as $i => $l) {
                if ($l['id'] === $loadId) {
                    $load    = $l;
                    $loadIdx = $i;
                    break;
                }
            }
            foreach ($drivers as $d) {
                if ($d['id'] === $driverId) {
                    $driver = $d;
                    break;
                }
            }

            if ($load    === null) respond(false, 'Load not found');
            if ($driver  === null) respond(false, 'Driver not found');

            $telegramSent  = false;
            $telegramError = '';

            // Attempt Telegram notification
            $cfg      = readJson(TELEGRAM_CFG);
            $chatId   = $driver['telegram_chat_id'] ?? '';
            $botToken = $cfg['bot_token']            ?? '';

            if ($chatId && $botToken) {
                $schedDate = !empty($load['scheduled_date'])
                    ? date('d M Y', strtotime($load['scheduled_date'])) : 'TBD';
                $weight   = $load['weight_kg']  ? $load['weight_kg']  . ' kg' : 'N/A';
                $volume   = $load['volume_m3']  ? $load['volume_m3']  . ' m³' : 'N/A';
                $tailLift = ($load['requires_tail_lift'] ?? false) ? '✅ Required' : '❌ Not required';

                $msg = "🚚 *NEW LOAD OFFER — Fastrux*\n\n"
                     . "📦 *Load ID:* `{$load['id']}`\n"
                     . "📍 *Pickup:* {$load['pickup_address']}\n"
                     . "🏁 *Delivery:* {$load['delivery_address']}\n"
                     . "📦 *Cargo:* {$load['cargo_description']}\n"
                     . "⚖️ *Weight:* {$weight} | 📦 *Volume:* {$volume}\n"
                     . "🔩 *Tail Lift:* {$tailLift}\n"
                     . "📅 *Date:* {$schedDate}\n";

                if (!empty($load['contact_name'])) {
                    $msg .= "📞 *Contact:* {$load['contact_name']}";
                    if (!empty($load['contact_phone'])) {
                        $msg .= " — {$load['contact_phone']}";
                    }
                    $msg .= "\n";
                }
                if (!empty($load['notes'])) {
                    $msg .= "📝 *Notes:* {$load['notes']}\n";
                }
                $msg .= "\nReply *YES* to accept or *NO* to decline.";

                $apiUrl  = "https://api.telegram.org/bot{$botToken}/sendMessage";
                $payload = json_encode([
                    'chat_id'    => $chatId,
                    'text'       => $msg,
                    'parse_mode' => 'Markdown',
                ]);

                $ctx  = stream_context_create([
                    'http' => [
                        'method'  => 'POST',
                        'header'  => "Content-Type: application/json\r\n",
                        'content' => $payload,
                        'timeout' => 10,
                    ],
                ]);
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

            // Persist the assignment
            $loads[$loadIdx]['assigned_driver_id'] = $driverId;
            $loads[$loadIdx]['status']             = 'matched';
            if ($telegramSent) {
                $loads[$loadIdx]['telegram_sent_at'] = date('Y-m-d H:i:s');
            }

            // Attempt SMS notification via driver's phone number
            $smsSent  = false;
            $smsError = '';
            $phone    = $driver['phone'] ?? '';
            if ($phone) {
                $schedDate = !empty($load['scheduled_date'])
                    ? date('d M Y', strtotime($load['scheduled_date'])) : 'TBD';
                // Strip tags and special chars from load data before embedding in SMS
                $smsLoadId   = strip_tags($load['id']               ?? '');
                $smsPickup   = strip_tags($load['pickup_address']   ?? '');
                $smsDelivery = strip_tags($load['delivery_address'] ?? '');
                $smsBody = "Fastrux: New load offer [{$smsLoadId}]\n"
                         . "Pickup: {$smsPickup}\n"
                         . "Delivery: {$smsDelivery}\n"
                         . "Date: {$schedDate}\n"
                         . "Reply YES to accept or NO to decline.";
                $smsResult = sendTwilioSms($phone, $smsBody);
                $smsSent  = $smsResult['sent'];
                $smsError = $smsResult['error'];
                if ($smsSent) {
                    $loads[$loadIdx]['sms_sent_at'] = date('Y-m-d H:i:s');
                }
            } else {
                $smsError = 'Driver has no phone number saved';
            }

            writeJson(LOADS_JSON, $loads);

            auditLog('load.driver_assigned', '', 'load', $loadId, "Driver {$driverId} assigned to load {$loadId}");

            $notifParts = [];
            if ($telegramSent) $notifParts[] = 'Telegram';
            if ($smsSent)      $notifParts[] = 'SMS';

            $successMsg = 'Driver assigned';
            if ($notifParts) {
                $successMsg .= ' and notified via ' . implode(' & ', $notifParts);
            }
            $warnings = [];
            if (!$telegramSent && $telegramError) $warnings[] = "Telegram: {$telegramError}";
            if (!$smsSent      && $smsError)      $warnings[] = "SMS: {$smsError}";
            if ($warnings) $successMsg .= ' (' . implode('; ', $warnings) . ')';

            respond(true, $successMsg, [
                'telegram_sent'  => $telegramSent,
                'telegram_error' => $telegramError,
                'sms_sent'       => $smsSent,
                'sms_error'      => $smsError,
            ]);

        // Save Telegram bot token
        case 'save_telegram_config':
            $token = trim($_POST['bot_token'] ?? '');
            if (!$token) respond(false, 'bot_token is required');

            // Validate format: numeric_id:alphanumeric_string (35 or more chars after colon)
            if (!preg_match('/^\d+:[A-Za-z0-9_-]{35,}$/', $token)) {
                respond(false, 'Invalid token format. Expected: 123456789:ABCdef… (at least 35 alphanumeric characters after the colon)');
            }

            writeJson(TELEGRAM_CFG, ['bot_token' => $token]);
            respond(true, 'Telegram bot token saved successfully');

        // Save SMS (Twilio) credentials
        case 'save_sms_config':
            $sid   = trim($_POST['account_sid'] ?? '');
            $auth  = trim($_POST['auth_token']  ?? '');
            $from  = trim($_POST['from_number'] ?? '');

            if (!$sid)  respond(false, 'account_sid is required');
            if (!$auth) respond(false, 'auth_token is required');
            if (!$from) respond(false, 'from_number is required');

            writeJson(SMS_CFG, [
                'account_sid'  => $sid,
                'auth_token'   => $auth,
                'from_number'  => $from,
            ]);
            respond(true, 'SMS (Twilio) config saved successfully');

        default:
            respond(false, 'Unknown action');
    }
}

respond(false, 'Method not allowed');
