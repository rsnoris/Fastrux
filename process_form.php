<?php
/**
 * Fastrux — Unified Form Processor
 * Handles: contact | quote | newsletter | driver_onboard | login | register
 * Saves:   /data/{type}.csv  +  /data/{type}.json
 *          Driver uploads saved to /data/drivers/{id}/
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// ── Config ────────────────────────────────────────────────
define('DATA_DIR', __DIR__ . '/data/');

// Create data directory if it doesn't exist
if (!is_dir(DATA_DIR)) {
    mkdir(DATA_DIR, 0755, true);
}

// ── Helpers ───────────────────────────────────────────────

/**
 * Sanitise a single input value.
 */
function clean(string $value): string {
    return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
}

/**
 * Append a row to a CSV file.
 * First call creates the file and writes the header row.
 */
function appendCsv(string $filename, array $headers, array $row): void {
    $path      = DATA_DIR . $filename;
    $newFile   = !file_exists($path);
    $fp        = fopen($path, 'a');

    if ($fp === false) {
        throw new RuntimeException("Cannot open file: $path");
    }

    if ($newFile) {
        fputcsv($fp, $headers);          // write header on first creation
    }
    fputcsv($fp, $row);
    fclose($fp);
}

/**
 * Append an entry to a JSON file (array of objects).
 * File is created if it doesn't exist.
 */
function appendJson(string $filename, array $entry): void {
    $path = DATA_DIR . $filename;

    if (file_exists($path)) {
        $existing = json_decode(file_get_contents($path), true);
        if (!is_array($existing)) {
            $existing = [];
        }
    } else {
        $existing = [];
    }

    $existing[] = $entry;
    file_put_contents($path, json_encode($existing, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

/**
 * Return a JSON response and exit.
 */
function respond(bool $success, string $message, array $extra = []): void {
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message,
    ], $extra));
    exit;
}

/**
 * Validate an email address.
 */
function validEmail(string $email): bool {
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
}

// ── Route by form type ────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Method not allowed.');
}

$type = clean($_POST['form_type'] ?? '');

switch ($type) {
    case 'contact':
        handleContact();
        break;
    case 'quote':
        handleQuote();
        break;
    case 'newsletter':
        handleNewsletter();
        break;
    case 'driver_onboard':
        handleDriverOnboard();
        break;
    case 'login':
        handleLogin();
        break;
    case 'register':
        handleRegister();
        break;
    default:
        respond(false, 'Unknown form type.');
}

// ══════════════════════════════════════════════════════════
//  HANDLERS
// ══════════════════════════════════════════════════════════

function handleContact(): void
{
    // ── Collect & validate ──
    $firstName = clean($_POST['first_name'] ?? '');
    $lastName  = clean($_POST['last_name']  ?? '');
    $email     = clean($_POST['email']      ?? '');
    $phone     = clean($_POST['phone']      ?? '');
    $subject   = clean($_POST['subject']    ?? '');
    $message   = clean($_POST['message']    ?? '');

    if (!$firstName || !$lastName) {
        respond(false, 'First and last name are required.');
    }
    if (!validEmail($email)) {
        respond(false, 'A valid email address is required.');
    }
    if (!$subject) {
        respond(false, 'Please select a subject.');
    }
    if (!$message) {
        respond(false, 'Message cannot be empty.');
    }

    // ── Build entry ──
    $timestamp = date('Y-m-d H:i:s');
    $id        = 'CNT-' . strtoupper(substr(md5(uniqid()), 0, 8));

    $entry = [
        'id'         => $id,
        'timestamp'  => $timestamp,
        'first_name' => $firstName,
        'last_name'  => $lastName,
        'email'      => $email,
        'phone'      => $phone,
        'subject'    => $subject,
        'message'    => $message,
    ];

    // ── Save ──
    $headers = ['id', 'timestamp', 'first_name', 'last_name', 'email', 'phone', 'subject', 'message'];
    appendCsv('contact_submissions.csv', $headers, array_values($entry));
    appendJson('contact_submissions.json', $entry);

    respond(true, 'Your message has been received. We\'ll get back to you within one business day.', [
        'reference' => $id,
    ]);
}

function handleQuote(): void
{
    // ── Collect & validate ──
    $firstName   = clean($_POST['first_name']   ?? '');
    $lastName    = clean($_POST['last_name']    ?? '');
    $company     = clean($_POST['company']      ?? '');
    $email       = clean($_POST['email']        ?? '');
    $service     = clean($_POST['service']      ?? '');
    $origin      = clean($_POST['origin']       ?? '');
    $destination = clean($_POST['destination']  ?? '');
    $weight      = clean($_POST['weight']       ?? '');
    $volume      = clean($_POST['volume']       ?? '');
    $notes       = clean($_POST['notes']        ?? '');

    if (!$firstName || !$lastName) {
        respond(false, 'First and last name are required.');
    }
    if (!validEmail($email)) {
        respond(false, 'A valid email address is required.');
    }
    if (!$service) {
        respond(false, 'Please select a service type.');
    }
    if (!$origin || !$destination) {
        respond(false, 'Origin and destination are required.');
    }

    // ── Build entry ──
    $timestamp = date('Y-m-d H:i:s');
    $id        = 'QUO-' . strtoupper(substr(md5(uniqid()), 0, 8));

    $entry = [
        'id'          => $id,
        'timestamp'   => $timestamp,
        'first_name'  => $firstName,
        'last_name'   => $lastName,
        'company'     => $company,
        'email'       => $email,
        'service'     => $service,
        'origin'      => $origin,
        'destination' => $destination,
        'weight_kg'   => $weight,
        'volume_m3'   => $volume,
        'notes'       => $notes,
    ];

    // ── Save ──
    $headers = ['id', 'timestamp', 'first_name', 'last_name', 'company', 'email', 'service', 'origin', 'destination', 'weight_kg', 'volume_m3', 'notes'];
    appendCsv('quote_submissions.csv', $headers, array_values($entry));
    appendJson('quote_submissions.json', $entry);

    respond(true, 'Quote request received! Our team will respond within 24 hours.', [
        'reference' => $id,
    ]);
}

function handleNewsletter(): void
{
    $email = clean($_POST['email'] ?? '');

    if (!validEmail($email)) {
        respond(false, 'A valid email address is required.');
    }

    // ── Duplicate check ──
    $jsonPath = DATA_DIR . 'newsletter_subscribers.json';
    if (file_exists($jsonPath)) {
        $existing = json_decode(file_get_contents($jsonPath), true) ?? [];
        foreach ($existing as $sub) {
            if (isset($sub['email']) && strtolower($sub['email']) === strtolower($email)) {
                respond(false, 'You\'re already subscribed — thank you!');
            }
        }
    }

    // ── Build entry ──
    $timestamp = date('Y-m-d H:i:s');
    $id        = 'SUB-' . strtoupper(substr(md5(uniqid()), 0, 8));

    $entry = [
        'id'        => $id,
        'timestamp' => $timestamp,
        'email'     => $email,
    ];

    $headers = ['id', 'timestamp', 'email'];
    appendCsv('newsletter_subscribers.csv', $headers, array_values($entry));
    appendJson('newsletter_subscribers.json', $entry);

    respond(true, 'You\'re subscribed! Welcome to the Fastrux newsletter.', [
        'reference' => $id,
    ]);
}

// ══════════════════════════════════════════════════════════
//  DRIVER ONBOARDING HANDLER
// ══════════════════════════════════════════════════════════

function handleDriverOnboard(): void
{
    // ── Collect personal details ──
    $firstName      = clean($_POST['first_name']      ?? '');
    $lastName       = clean($_POST['last_name']       ?? '');
    $email          = clean($_POST['email']           ?? '');
    $phone          = clean($_POST['phone']           ?? '');
    $dob            = clean($_POST['dob']             ?? '');
    $address        = clean($_POST['address']         ?? '');
    $licenseNumber  = clean($_POST['license_number']  ?? '');
    $licenseExpiry  = clean($_POST['license_expiry']  ?? '');
    $yearsExp       = clean($_POST['years_experience']?? '');

    // ── Collect vehicle details ──
    $vanMake        = clean($_POST['van_make']        ?? '');
    $vanModel       = clean($_POST['van_model']       ?? '');
    $vanYear        = clean($_POST['van_year']        ?? '');
    $vanColor       = clean($_POST['van_color']       ?? '');
    $vanReg         = clean($_POST['van_reg']         ?? '');
    $vanType        = clean($_POST['van_type']        ?? '');
    $insuranceExpiry= clean($_POST['insurance_expiry']?? '');
    $motExpiry      = clean($_POST['mot_expiry']      ?? '');

    // ── Collect dimensions ──
    $cargoLength    = clean($_POST['cargo_length']    ?? '');
    $cargoWidth     = clean($_POST['cargo_width']     ?? '');
    $cargoHeight    = clean($_POST['cargo_height']    ?? '');
    $payloadKg      = clean($_POST['payload_kg']      ?? '');
    $volumeM3       = clean($_POST['volume_m3']       ?? '');
    $extLength      = clean($_POST['ext_length']      ?? '');
    $extWidth       = clean($_POST['ext_width']       ?? '');
    $extHeight      = clean($_POST['ext_height']      ?? '');
    $tailLift       = clean($_POST['tail_lift']       ?? 'no');

    // ── Collect availability ──
    $availability   = isset($_POST['availability']) && is_array($_POST['availability'])
                        ? array_map('trim', $_POST['availability'])
                        : [];
    $workType       = clean($_POST['work_type']       ?? '');
    $operatingAreas = clean($_POST['operating_areas'] ?? '');
    $notes          = clean($_POST['notes']           ?? '');

    // ── Validate required fields ──
    if (!$firstName || !$lastName) {
        respond(false, 'First and last name are required.');
    }
    if (!validEmail($email)) {
        respond(false, 'A valid email address is required.');
    }
    if (!$phone) {
        respond(false, 'Phone number is required.');
    }
    if (!$licenseNumber) {
        respond(false, 'Driver licence number is required.');
    }
    if (!$vanMake || !$vanModel) {
        respond(false, 'Van make and model are required.');
    }
    if (!$vanReg) {
        respond(false, 'Vehicle registration number is required.');
    }
    if (!$cargoLength || !$cargoWidth || !$cargoHeight) {
        respond(false, 'All three cargo dimensions (length, width, height) are required.');
    }
    if (!$payloadKg) {
        respond(false, 'Max payload is required.');
    }
    if (empty($availability)) {
        respond(false, 'Please select at least one day of availability.');
    }
    if (!$operatingAreas) {
        respond(false, 'Operating areas are required.');
    }

    // ── Build IDs and paths ──
    $timestamp  = date('Y-m-d H:i:s');
    $id         = 'DRV-' . strtoupper(substr(md5(uniqid()), 0, 8));
    $driverDir  = DATA_DIR . 'drivers/' . $id . '/';

    if (!is_dir($driverDir)) {
        mkdir($driverDir, 0755, true);
    }

    // ── Handle file uploads ──
    $uploadFields = ['photo_front', 'photo_side', 'photo_interior', 'doc_licence', 'doc_insurance', 'doc_mot'];
    $savedFiles   = [];
    $allowedTypes = [
        'image/jpeg', 'image/png', 'image/gif', 'image/webp',
        'application/pdf',
    ];
    $maxSize = 10 * 1024 * 1024; // 10 MB

    foreach ($uploadFields as $field) {
        $savedFiles[$field] = [];

        if (!isset($_FILES[$field])) {
            continue;
        }

        // Normalise single-upload vs multiple-upload $_FILES structure
        $files = $_FILES[$field];
        if (!is_array($files['name'])) {
            $files = [
                'name'     => [$files['name']],
                'type'     => [$files['type']],
                'tmp_name' => [$files['tmp_name']],
                'error'    => [$files['error']],
                'size'     => [$files['size']],
            ];
        }

        foreach ($files['name'] as $i => $origName) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                continue; // skip failed uploads
            }
            if ($files['size'][$i] > $maxSize) {
                respond(false, "File '$origName' exceeds the 10 MB limit.");
            }

            // Validate MIME by actual file content, not browser-reported type
            $finfo    = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($files['tmp_name'][$i]);
            if (!in_array($mimeType, $allowedTypes, true)) {
                respond(false, "File '$origName' has an unsupported type ($mimeType). Allowed: JPG, PNG, PDF.");
            }

            // Build safe filename
            $ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
            $safeExt  = in_array($ext, ['jpg','jpeg','png','gif','webp','pdf'], true) ? $ext : 'bin';
            $saveName = $field . '_' . ($i + 1) . '.' . $safeExt;
            $savePath = $driverDir . $saveName;

            if (move_uploaded_file($files['tmp_name'][$i], $savePath)) {
                $savedFiles[$field][] = $savePath;
            }
        }
    }

    // ── Build entry ──
    $entry = [
        'id'               => $id,
        'timestamp'        => $timestamp,
        'status'           => 'pending',
        // Personal
        'first_name'       => $firstName,
        'last_name'        => $lastName,
        'email'            => $email,
        'phone'            => $phone,
        'dob'              => $dob,
        'address'          => $address,
        'license_number'   => $licenseNumber,
        'license_expiry'   => $licenseExpiry,
        'years_experience' => $yearsExp,
        // Vehicle
        'van_make'         => $vanMake,
        'van_model'        => $vanModel,
        'van_year'         => $vanYear,
        'van_color'        => $vanColor,
        'van_reg'          => strtoupper($vanReg),
        'van_type'         => $vanType,
        'insurance_expiry' => $insuranceExpiry,
        'mot_expiry'       => $motExpiry,
        // Dimensions
        'cargo_length'     => $cargoLength,
        'cargo_width'      => $cargoWidth,
        'cargo_height'     => $cargoHeight,
        'payload_kg'       => $payloadKg,
        'volume_m3'        => $volumeM3,
        'ext_length'       => $extLength,
        'ext_width'        => $extWidth,
        'ext_height'       => $extHeight,
        'tail_lift'        => $tailLift,
        // Availability
        'availability'     => $availability,
        'work_type'        => $workType,
        'operating_areas'  => $operatingAreas,
        'notes'            => $notes,
        // Files
        'photo_front'      => $savedFiles['photo_front'],
        'photo_side'       => $savedFiles['photo_side'],
        'photo_interior'   => $savedFiles['photo_interior'],
        'doc_licence'      => $savedFiles['doc_licence'],
        'doc_insurance'    => $savedFiles['doc_insurance'],
        'doc_mot'          => $savedFiles['doc_mot'],
    ];

    // ── Save to main JSON & CSV ──
    appendJson('driver_submissions.json', $entry);

    $csvHeaders = [
        'id','timestamp','status',
        'first_name','last_name','email','phone','dob','address',
        'license_number','license_expiry','years_experience',
        'van_make','van_model','van_year','van_color','van_reg','van_type',
        'insurance_expiry','mot_expiry',
        'cargo_length','cargo_width','cargo_height',
        'payload_kg','volume_m3','ext_length','ext_width','ext_height','tail_lift',
        'availability','work_type','operating_areas','notes',
    ];
    $csvRow = [
        $id, $timestamp, 'pending',
        $firstName, $lastName, $email, $phone, $dob, $address,
        $licenseNumber, $licenseExpiry, $yearsExp,
        $vanMake, $vanModel, $vanYear, $vanColor, strtoupper($vanReg), $vanType,
        $insuranceExpiry, $motExpiry,
        $cargoLength, $cargoWidth, $cargoHeight,
        $payloadKg, $volumeM3, $extLength, $extWidth, $extHeight, $tailLift,
        implode(', ', $availability), $workType, $operatingAreas, $notes,
    ];
    appendCsv('driver_submissions.csv', $csvHeaders, $csvRow);

    respond(true, 'Your driver application has been received. We\'ll be in touch within 2 working days.', [
        'reference' => $id,
    ]);
}

// ══════════════════════════════════════════════════════════
//  LOGIN HANDLER  (stores login attempts; no real auth)
// ══════════════════════════════════════════════════════════

function handleLogin(): void
{
    $email    = clean($_POST['email']    ?? '');
    $password = clean($_POST['password'] ?? '');

    if (!$email || !$password) {
        respond(false, 'Email and password are required.');
    }
    if (!validEmail($email)) {
        respond(false, 'Please enter a valid email address.');
    }

    // Look up email in registered users
    $usersPath = DATA_DIR . 'registered_users.json';
    if (!file_exists($usersPath)) {
        respond(false, 'No account found for that email address.');
    }

    $users = json_decode(file_get_contents($usersPath), true) ?? [];
    $user  = null;
    foreach ($users as $u) {
        if (isset($u['email']) && strtolower($u['email']) === strtolower($email)) {
            $user = $u;
            break;
        }
    }

    if (!$user) {
        respond(false, 'No account found for that email address.');
    }

    if (!password_verify($password, $user['password_hash'] ?? '')) {
        respond(false, 'Incorrect password. Please try again.');
    }

    respond(true, 'Login successful. Welcome back, ' . htmlspecialchars($user['first_name'], ENT_QUOTES, 'UTF-8') . '!', [
        'user' => [
            'id'         => $user['id'],
            'first_name' => $user['first_name'],
            'last_name'  => $user['last_name'],
            'email'      => $user['email'],
        ],
    ]);
}

// ══════════════════════════════════════════════════════════
//  REGISTER HANDLER
// ══════════════════════════════════════════════════════════

function handleRegister(): void
{
    $firstName = clean($_POST['firstName'] ?? '');
    $lastName  = clean($_POST['lastName']  ?? '');
    $email     = clean($_POST['email']     ?? '');
    $company   = clean($_POST['company']   ?? '');
    $password  = $_POST['password']        ?? '';  // raw — will be hashed

    if (!$firstName || !$lastName) {
        respond(false, 'First and last name are required.');
    }
    if (!validEmail($email)) {
        respond(false, 'A valid email address is required.');
    }
    if (strlen($password) < 8) {
        respond(false, 'Password must be at least 8 characters long.');
    }

    // ── Duplicate email check ──
    $usersPath = DATA_DIR . 'registered_users.json';
    if (file_exists($usersPath)) {
        $existing = json_decode(file_get_contents($usersPath), true) ?? [];
        foreach ($existing as $u) {
            if (isset($u['email']) && strtolower($u['email']) === strtolower($email)) {
                respond(false, 'An account with that email address already exists.');
            }
        }
    }

    $timestamp = date('Y-m-d H:i:s');
    $id        = 'USR-' . strtoupper(substr(md5(uniqid()), 0, 8));

    $entry = [
        'id'            => $id,
        'timestamp'     => $timestamp,
        'first_name'    => $firstName,
        'last_name'     => $lastName,
        'email'         => $email,
        'company'       => $company,
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
    ];

    appendJson('registered_users.json', $entry);

    $headers = ['id', 'timestamp', 'first_name', 'last_name', 'email', 'company'];
    appendCsv('registered_users.csv', $headers, [$id, $timestamp, $firstName, $lastName, $email, $company]);

    respond(true, 'Account created successfully! You can now sign in.', [
        'reference' => $id,
    ]);
}