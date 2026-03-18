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

require_once __DIR__ . '/audit_helper.php';

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
    case 'kyc_update':
        handleKycUpdate();
        break;
    case 'kyc_load':
        handleKycLoad();
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
    auditLog('contact.submitted', '', 'contact', $id, "Contact form submitted by {$firstName} {$lastName} ({$email})");

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
    $rawUserId   = clean($_POST['user_id']      ?? '');
    // Validate user_id format (must match USR-XXXXXXXX pattern)
    $userId      = preg_match('/^USR-[A-Z0-9]{8}$/', $rawUserId) ? $rawUserId : '';

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
        'user_id'     => $userId,
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
    $headers = ['id', 'timestamp', 'user_id', 'first_name', 'last_name', 'company', 'email', 'service', 'origin', 'destination', 'weight_kg', 'volume_m3', 'notes'];
    appendCsv('quote_submissions.csv', $headers, array_values($entry));
    appendJson('quote_submissions.json', $entry);
    auditLog('quote.submitted', $userId, 'quote', $id, "Quote requested by {$firstName} {$lastName} ({$email}) — service: {$service}, {$origin} → {$destination}");

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
    auditLog('newsletter.subscribed', '', 'newsletter', $id, "Newsletter subscription: {$email}");

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
    $rawSubmittedBy = clean($_POST['submitted_by'] ?? '');
    // Validate submitted_by format (must match USR-XXXXXXXX pattern)
    $submittedBy = preg_match('/^USR-[A-Z0-9]{8}$/', $rawSubmittedBy) ? $rawSubmittedBy : '';
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
        'submitted_by'     => $submittedBy,
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
    auditLog('driver.applied', '', 'driver', $id, "Driver application submitted by {$firstName} {$lastName} ({$email}), van: {$vanReg}");

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
        auditLog('user.login_failed', '', 'user', '', "Failed login attempt for email: {$email}");
        respond(false, 'No account found for that email address.');
    }

    if (!password_verify($password, $user['password_hash'] ?? '')) {
        auditLog('user.login_failed', $user['id'] ?? '', 'user', $user['id'] ?? '', "Failed login attempt (wrong password) for {$email}");
        respond(false, 'Incorrect password. Please try again.');
    }

    // Block accounts that are pending admin approval
    if (($user['status'] ?? 'active') === 'pending_approval') {
        auditLog('user.login_blocked', $user['id'] ?? '', 'user', $user['id'] ?? '', "Login blocked — account pending admin approval: {$email}");
        respond(false, 'Your account is pending admin approval. You will be notified once it is activated.');
    }

    // Block rejected accounts
    if (($user['status'] ?? 'active') === 'rejected') {
        auditLog('user.login_blocked', $user['id'] ?? '', 'user', $user['id'] ?? '', "Login blocked — account rejected: {$email}");
        respond(false, 'Your account application was not approved. Please contact support for more information.');
    }

    auditLog('user.login', $user['id'] ?? '', 'user', $user['id'] ?? '', "User logged in: {$email} (role: " . ($user['role'] ?? 'shipper') . ')');
    respond(true, 'Login successful. Welcome back, ' . htmlspecialchars($user['first_name'], ENT_QUOTES, 'UTF-8') . '!', [
        'user' => [
            'id'         => $user['id'],
            'first_name' => $user['first_name'],
            'last_name'  => $user['last_name'],
            'email'      => $user['email'],
            'role'       => $user['role'] ?? 'shipper',
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
    $role      = clean($_POST['role']      ?? 'shipper');

    if (!$firstName || !$lastName) {
        respond(false, 'First and last name are required.');
    }
    if (!validEmail($email)) {
        respond(false, 'A valid email address is required.');
    }
    if (strlen($password) < 8) {
        respond(false, 'Password must be at least 8 characters long.');
    }

    // Sanitize role — admin/super_admin cannot self-register via public form
    $allowedRegRoles = ['shipper', 'driver', 'owner_operator', 'corporate_staff', 'insurance_company', 'trucking_company'];
    if (!in_array($role, $allowedRegRoles, true)) {
        $role = 'shipper';
    }

    // Company name is required for company accounts
    if (in_array($role, ['insurance_company', 'trucking_company'], true) && !$company) {
        respond(false, 'Company name is required for company accounts.');
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

    // corporate_staff accounts require admin approval before they can log in
    $status = ($role === 'corporate_staff') ? 'pending_approval' : 'active';

    $entry = [
        'id'            => $id,
        'timestamp'     => $timestamp,
        'first_name'    => $firstName,
        'last_name'     => $lastName,
        'email'         => $email,
        'company'       => $company,
        'role'          => $role,
        'status'        => $status,
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
    ];

    // ── Capture role-specific registration fields ──
    if ($role === 'insurance_company') {
        $allowedCoverages = ['cargo', 'liability', 'physical_damage', 'workers_comp',
                             'general_liability', 'occupational_accident', 'bobtail', 'non_trucking'];
        $rawCoverages = $_POST['coverage_types'] ?? [];
        $safeCoverages = [];
        if (is_array($rawCoverages)) {
            foreach ($rawCoverages as $c) {
                $c = clean($c);
                if (in_array($c, $allowedCoverages, true)) {
                    $safeCoverages[] = $c;
                }
            }
        }
        $entry['insurance_license']      = clean($_POST['insurance_license']      ?? '');
        $entry['state_of_incorporation'] = clean($_POST['state_of_incorporation'] ?? '');
        $entry['coverage_types']         = $safeCoverages;
        $entry['years_in_business']      = clean($_POST['years_in_business']      ?? '');
        $entry['contact_phone']          = clean($_POST['contact_phone']          ?? '');
        $entry['website']                = clean($_POST['website']                ?? '');
    }

    if ($role === 'trucking_company') {
        $entry['dot_number']   = clean($_POST['dot_number']   ?? '');
        $entry['mc_number']    = clean($_POST['mc_number']    ?? '');
        $entry['fleet_size']   = clean($_POST['fleet_size']   ?? '');
        $entry['truck_types']  = clean($_POST['truck_types']  ?? '');
        $entry['service_area'] = clean($_POST['service_area'] ?? '');
        $entry['contact_phone']= clean($_POST['contact_phone']?? '');
        $entry['website']      = clean($_POST['website']      ?? '');
    }

    appendJson('registered_users.json', $entry);

    $headers = ['id', 'timestamp', 'first_name', 'last_name', 'email', 'company', 'role', 'status'];
    appendCsv('registered_users.csv', $headers, [$id, $timestamp, $firstName, $lastName, $email, $company, $role, $status]);
    auditLog('user.registered', $id, 'user', $id, "New account registered: {$email} (role: {$role}, status: {$status})");

    if ($status === 'pending_approval') {
        respond(true, 'Staff account request submitted! An administrator will review and activate your account shortly.', [
            'reference'         => $id,
            'role'              => $role,
            'pending_approval'  => true,
        ]);
    }

    respond(true, 'Account created successfully! You can now sign in.', [
        'reference' => $id,
        'role'      => $role,
    ]);
}

// ══════════════════════════════════════════════════════════
//  KYC UPDATE HANDLER
//  Saves user KYC/profile data to /data/users/{role}/{id}/
// ══════════════════════════════════════════════════════════

function handleKycUpdate(): void
{
    $userId  = clean($_POST['user_id']    ?? '');
    $section = clean($_POST['section']    ?? '');
    $role    = clean($_POST['user_role']  ?? 'customer');

    if (!$userId) {
        respond(false, 'User ID is required.');
    }

    // Sanitize role to a safe directory name
    $allowedRoles = ['shipper', 'customer', 'driver', 'owner_operator', 'corporate_staff', 'admin', 'super_admin', 'insurance_company', 'trucking_company'];
    if (!in_array($role, $allowedRoles, true)) {
        $role = 'shipper';
    }

    // Build user folder: /data/users/{role}/{userId}/
    // Strip all non-alphanumeric/dash/underscore characters to prevent path traversal
    $safeUserId = preg_replace('/[^A-Za-z0-9_\-]/', '', $userId);
    if (!$safeUserId) {
        respond(false, 'Invalid user ID format.');
    }
    $userDir = DATA_DIR . 'users/' . $role . '/' . $safeUserId . '/';
    if (!is_dir($userDir)) {
        mkdir($userDir, 0755, true);
    }

    $kycFile = $userDir . 'kyc.json';

    // Load existing KYC data
    $existing = [];
    if (file_exists($kycFile)) {
        $existing = json_decode(file_get_contents($kycFile), true) ?? [];
    }

    $timestamp = date('Y-m-d H:i:s');

    if ($section === 'profile') {
        $firstName = clean($_POST['first_name'] ?? '');
        $lastName  = clean($_POST['last_name']  ?? '');
        $email     = clean($_POST['email']      ?? '');
        $phone     = clean($_POST['phone']      ?? '');
        $dob       = clean($_POST['dob']        ?? '');
        $address   = clean($_POST['address']    ?? '');
        $company   = clean($_POST['company']    ?? '');

        if (!$firstName || !$lastName) {
            respond(false, 'First and last name are required.');
        }
        if (!validEmail($email)) {
            respond(false, 'A valid email address is required.');
        }

        $existing = array_merge($existing, [
            'user_id'    => $userId,
            'role'       => $role,
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'email'      => $email,
            'phone'      => $phone,
            'dob'        => $dob,
            'address'    => $address,
            'company'    => $company,
            'updated_at' => $timestamp,
        ]);

        if (!isset($existing['created_at'])) {
            $existing['created_at'] = $timestamp;
        }

        file_put_contents($kycFile, json_encode($existing, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        auditLog('kyc.profile_updated', $userId, 'user', $userId, "Profile updated for user {$userId} (role: {$role})");
        respond(true, 'Profile saved successfully.', ['kyc' => $existing]);
    }

    if ($section === 'kyc') {
        // Core identity fields
        $nationalId   = clean($_POST['national_id']  ?? '');
        $idExpiry     = clean($_POST['id_expiry']    ?? '');
        $nationality  = clean($_POST['nationality']  ?? '');
        $ssnLast4     = clean($_POST['ssn_last4']    ?? '');

        if (!$nationalId || !$idExpiry || !$nationality) {
            respond(false, 'National ID, expiry date, and nationality are required.');
        }

        $kycData = [
            'national_id'  => $nationalId,
            'id_expiry'    => $idExpiry,
            'nationality'  => $nationality,
            'ssn_last4'    => $ssnLast4,
            'kyc_status'   => 'pending',
            'kyc_submitted_at' => $timestamp,
        ];

        // Role-specific fields
        if ($role === 'customer') {
            $kycData['business_type']    = clean($_POST['business_type']    ?? '');
            $kycData['tax_id']           = clean($_POST['tax_id']           ?? '');
            $kycData['billing_address']  = clean($_POST['billing_address']  ?? '');
            $kycData['annual_shipments'] = clean($_POST['annual_shipments'] ?? '');
            $kycData['primary_service']  = clean($_POST['primary_service']  ?? '');
        } elseif ($role === 'driver') {
            $kycData['license_number']   = clean($_POST['license_number']   ?? '');
            $kycData['license_expiry']   = clean($_POST['license_expiry']   ?? '');
            $kycData['van_make']         = clean($_POST['van_make']         ?? '');
            $kycData['van_model']        = clean($_POST['van_model']        ?? '');
            $kycData['van_reg']          = strtoupper(clean($_POST['van_reg'] ?? ''));
            $kycData['insurance_expiry'] = clean($_POST['insurance_expiry'] ?? '');
            $kycData['years_experience'] = clean($_POST['years_experience'] ?? '');
            $kycData['operating_areas']  = clean($_POST['operating_areas']  ?? '');
        } elseif ($role === 'owner_operator') {
            $kycData['business_name']       = clean($_POST['business_name']       ?? '');
            $kycData['mc_number']           = clean($_POST['mc_number']           ?? '');
            $kycData['fleet_size']          = clean($_POST['fleet_size']          ?? '');
            $kycData['oo_tax_id']           = clean($_POST['oo_tax_id']           ?? '');
            $kycData['oo_license_number']   = clean($_POST['oo_license_number']   ?? '');
            $kycData['oo_insurance_expiry'] = clean($_POST['oo_insurance_expiry'] ?? '');
            $kycData['oo_operating_areas']  = clean($_POST['oo_operating_areas']  ?? '');
        }

        $existing = array_merge($existing, $kycData);
        if (!isset($existing['created_at'])) {
            $existing['created_at'] = $timestamp;
        }
        $existing['updated_at'] = $timestamp;

        file_put_contents($kycFile, json_encode($existing, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        auditLog('kyc.identity_submitted', $userId, 'user', $userId, "KYC identity submitted for user {$userId} (role: {$role})");
        respond(true, 'KYC information saved. Your details are pending review.', ['kyc' => $existing]);
    }

    if ($section === 'documents') {
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];
        $maxSize      = 10 * 1024 * 1024;
        $docFields    = ['doc_id_front', 'doc_id_back', 'doc_address_proof', 'doc_licence'];
        $savedFiles   = [];

        foreach ($docFields as $field) {
            if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
                continue;
            }
            $file = $_FILES[$field];
            if ($file['size'] > $maxSize) {
                respond(false, "File '{$file['name']}' exceeds the 10 MB limit.");
            }
            $finfo    = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']);
            if (!in_array($mimeType, $allowedMimes, true)) {
                respond(false, "File '{$file['name']}' has an unsupported type. Allowed: JPG, PNG, PDF.");
            }
            $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $safeExt  = in_array($ext, ['jpg','jpeg','png','gif','webp','pdf'], true) ? $ext : 'bin';
            $saveName = $field . '.' . $safeExt;
            $savePath = $userDir . $saveName;
            if (move_uploaded_file($file['tmp_name'], $savePath)) {
                $savedFiles[$field] = $savePath;
                $existing[$field]   = $savePath;
            }
        }

        $existing['documents_uploaded_at'] = $timestamp;
        $existing['updated_at']            = $timestamp;
        if (!isset($existing['created_at'])) {
            $existing['created_at'] = $timestamp;
        }

        file_put_contents($kycFile, json_encode($existing, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        auditLog('kyc.documents_uploaded', $userId, 'user', $userId, "KYC documents uploaded for user {$userId}: " . implode(', ', array_keys($savedFiles)));
        respond(true, 'Documents uploaded successfully.', ['files' => array_keys($savedFiles)]);
    }

    if ($section === 'security') {
        $currentPassword  = $_POST['current_password']  ?? '';
        $newPassword      = $_POST['new_password']       ?? '';
        $confirmPassword  = $_POST['confirm_password']   ?? '';

        if (!$currentPassword || !$newPassword || !$confirmPassword) {
            respond(false, 'All password fields are required.');
        }
        if (strlen($newPassword) < 8) {
            respond(false, 'New password must be at least 8 characters long.');
        }
        if ($newPassword !== $confirmPassword) {
            respond(false, 'New passwords do not match.');
        }

        // Verify current password against registered users
        $usersPath = DATA_DIR . 'registered_users.json';
        if (!file_exists($usersPath)) {
            respond(false, 'User account not found.');
        }
        $users = json_decode(file_get_contents($usersPath), true) ?? [];
        $found = false;
        foreach ($users as &$u) {
            if (($u['id'] ?? '') === $userId) {
                if (!password_verify($currentPassword, $u['password_hash'] ?? '')) {
                    respond(false, 'Current password is incorrect.');
                }
                $u['password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
                $u['updated_at']    = $timestamp;
                $found = true;
                break;
            }
        }
        unset($u);

        if (!$found) {
            respond(false, 'User account not found.');
        }

        file_put_contents(
            $usersPath,
            json_encode(array_values($users), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
        auditLog('user.password_changed', $userId, 'user', $userId, "Password changed for user {$userId}");
        respond(true, 'Password updated successfully.');
    }

    respond(false, 'Unknown section.');
}

// ══════════════════════════════════════════════════════════
//  KYC LOAD HANDLER
//  Retrieves saved KYC data for a user
// ══════════════════════════════════════════════════════════

function handleKycLoad(): void
{
    $userId = clean($_POST['user_id'] ?? '');
    if (!$userId) {
        respond(false, 'User ID is required.');
    }

    $safeId = preg_replace('/[^A-Za-z0-9_\-]/', '', $userId);
    if (!$safeId) {
        respond(false, 'Invalid user ID format.');
    }

    // Search across all role folders
    $roles = ['shipper', 'customer', 'driver', 'owner_operator', 'corporate_staff', 'admin', 'super_admin'];
    foreach ($roles as $role) {
        $kycFile = DATA_DIR . 'users/' . $role . '/' . $safeId . '/kyc.json';
        if (file_exists($kycFile)) {
            $kyc = json_decode(file_get_contents($kycFile), true) ?? [];
            respond(true, 'KYC data loaded.', ['kyc' => $kyc]);
        }
    }

    // No data yet — return empty
    respond(true, 'No KYC data found.', ['kyc' => []]);
}