<?php
/**
 * Fastrux — Unified Form Processor (MySQL backend)
 * Handles: contact | quote | newsletter | driver_onboard | login | register | kyc_update | kyc_load
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/audit.php';

define('DATA_DIR', __DIR__ . '/data/');

function clean(string $value): string {
    return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
}

function respond(bool $success, string $message, array $extra = []): void {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit;
}

function validEmail(string $email): bool {
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Method not allowed.');
}

try {
    $type = clean($_POST['form_type'] ?? '');
    switch ($type) {
        case 'contact':        handleContact();       break;
        case 'quote':          handleQuote();         break;
        case 'newsletter':     handleNewsletter();    break;
        case 'driver_onboard': handleDriverOnboard(); break;
        case 'login':          handleLogin();         break;
        case 'register':       handleRegister();      break;
        case 'kyc_update':     handleKycUpdate();     break;
        case 'kyc_load':       handleKycLoad();       break;
        default:               respond(false, 'Unknown form type.');
    }
} catch (\Throwable $e) {
    error_log('Fastrux process_form error: ' . $e->getMessage());
    respond(false, 'A server error occurred. Please try again later.');
}

function handleContact(): void
{
    $firstName = clean($_POST['first_name'] ?? '');
    $lastName  = clean($_POST['last_name']  ?? '');
    $email     = clean($_POST['email']      ?? '');
    $phone     = clean($_POST['phone']      ?? '');
    $subject   = clean($_POST['subject']    ?? '');
    $message   = clean($_POST['message']    ?? '');

    if (!$firstName || !$lastName) respond(false, 'First and last name are required.');
    if (!validEmail($email))       respond(false, 'A valid email address is required.');
    if (!$subject)                 respond(false, 'Please select a subject.');
    if (!$message)                 respond(false, 'Message cannot be empty.');

    $id = 'CNT-' . strtoupper(substr(md5(uniqid()), 0, 8));
    $db = getDb();
    $db->prepare(
        'INSERT INTO contacts (id, first_name, last_name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?, ?, ?)'
    )->execute([$id, $firstName, $lastName, $email, $phone, $subject, $message]);
    auditLog('contact.submitted', null, 'contact', $id, ['email' => $email, 'subject' => $subject]);
    respond(true, "Your message has been received. We'll get back to you within one business day.", ['reference' => $id]);
}

function handleQuote(): void
{
    $firstName   = clean($_POST['first_name']   ?? '');
    $lastName    = clean($_POST['last_name']    ?? '');
    $company     = clean($_POST['company']      ?? '');
    $email       = clean($_POST['email']        ?? '');
    $phone       = clean($_POST['phone']        ?? '');  // optional; stored empty when not present in form
    $service     = clean($_POST['service']      ?? '');
    $origin      = clean($_POST['origin']       ?? '');
    $destination = clean($_POST['destination']  ?? '');
    $weight      = clean($_POST['weight']       ?? '');
    $volume      = clean($_POST['volume']       ?? '');
    $notes       = clean($_POST['notes']        ?? '');

    if (!$firstName || !$lastName)  respond(false, 'First and last name are required.');
    if (!validEmail($email))        respond(false, 'A valid email address is required.');
    if (!$service)                  respond(false, 'Please select a service type.');
    if (!$origin || !$destination)  respond(false, 'Origin and destination are required.');

    $id = 'QUO-' . strtoupper(substr(md5(uniqid()), 0, 8));
    $db = getDb();
    $db->prepare(
        'INSERT INTO quotes (id, first_name, last_name, company, email, phone, service, origin, destination, weight_kg, volume_m3, notes)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    )->execute([$id, $firstName, $lastName, $company, $email, $phone, $service, $origin, $destination, $weight, $volume, $notes]);
    auditLog('quote.submitted', null, 'quote', $id, ['email' => $email, 'service' => $service, 'origin' => $origin, 'destination' => $destination]);
    respond(true, 'Quote request received! Our team will respond within 24 hours.', ['reference' => $id]);
}

function handleNewsletter(): void
{
    $email = clean($_POST['email'] ?? '');
    if (!validEmail($email)) respond(false, 'A valid email address is required.');
    $db   = getDb();
    $stmt = $db->prepare('SELECT id FROM newsletter_subscribers WHERE email = ?');
    $stmt->execute([strtolower($email)]);
    if ($stmt->fetch()) respond(false, "You're already subscribed — thank you!");
    $id = 'SUB-' . strtoupper(substr(md5(uniqid()), 0, 8));
    $db->prepare('INSERT INTO newsletter_subscribers (id, email) VALUES (?, ?)')->execute([$id, strtolower($email)]);
    auditLog('newsletter.subscribed', null, 'newsletter_subscriber', $id, ['email' => $email]);
    respond(true, "You're subscribed! Welcome to the Fastrux newsletter.", ['reference' => $id]);
}

function handleDriverOnboard(): void
{
    $firstName      = clean($_POST['first_name']       ?? '');
    $lastName       = clean($_POST['last_name']        ?? '');
    $email          = clean($_POST['email']            ?? '');
    $phone          = clean($_POST['phone']            ?? '');
    $dob            = clean($_POST['dob']              ?? '');
    $address        = clean($_POST['address']          ?? '');
    $licenseNumber  = clean($_POST['license_number']   ?? '');
    $licenseExpiry  = clean($_POST['license_expiry']   ?? '');
    $yearsExp       = clean($_POST['years_experience'] ?? '');
    $vanMake        = clean($_POST['van_make']         ?? '');
    $vanModel       = clean($_POST['van_model']        ?? '');
    $vanYear        = clean($_POST['van_year']         ?? '');
    $vanColor       = clean($_POST['van_color']        ?? '');
    $vanReg         = clean($_POST['van_reg']          ?? '');
    $vanType        = clean($_POST['van_type']         ?? '');
    $insuranceExpiry= clean($_POST['insurance_expiry'] ?? '');
    $motExpiry      = clean($_POST['mot_expiry']       ?? '');
    $cargoLength    = clean($_POST['cargo_length']     ?? '');
    $cargoWidth     = clean($_POST['cargo_width']      ?? '');
    $cargoHeight    = clean($_POST['cargo_height']     ?? '');
    $payloadKg      = clean($_POST['payload_kg']       ?? '');
    $volumeM3       = clean($_POST['volume_m3']        ?? '');
    $extLength      = clean($_POST['ext_length']       ?? '');
    $extWidth       = clean($_POST['ext_width']        ?? '');
    $extHeight      = clean($_POST['ext_height']       ?? '');
    $tailLift       = clean($_POST['tail_lift']        ?? 'no');
    $availability   = isset($_POST['availability']) && is_array($_POST['availability'])
                        ? array_map('trim', $_POST['availability']) : [];
    $workType       = clean($_POST['work_type']        ?? '');
    $operatingAreas = clean($_POST['operating_areas']  ?? '');
    $notes          = clean($_POST['notes']            ?? '');

    if (!$firstName || !$lastName)                       respond(false, 'First and last name are required.');
    if (!validEmail($email))                             respond(false, 'A valid email address is required.');
    if (!$phone)                                         respond(false, 'Phone number is required.');
    if (!$licenseNumber)                                 respond(false, 'Driver licence number is required.');
    if (!$vanMake || !$vanModel)                         respond(false, 'Van make and model are required.');
    if (!$vanReg)                                        respond(false, 'Vehicle registration number is required.');
    if (!$cargoLength || !$cargoWidth || !$cargoHeight)  respond(false, 'All three cargo dimensions are required.');
    if (!$payloadKg)                                     respond(false, 'Max payload is required.');
    if (empty($availability))                            respond(false, 'Please select at least one day of availability.');
    if (!$operatingAreas)                                respond(false, 'Operating areas are required.');

    $id        = 'DRV-' . strtoupper(substr(md5(uniqid()), 0, 8));
    $driverDir = DATA_DIR . 'drivers/' . $id . '/';
    if (!is_dir($driverDir)) mkdir($driverDir, 0755, true);

    $uploadFields = ['photo_front', 'photo_side', 'photo_interior', 'doc_licence', 'doc_insurance', 'doc_mot'];
    $savedFiles   = array_fill_keys($uploadFields, []);
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];
    $maxSize      = 10 * 1024 * 1024;

    foreach ($uploadFields as $field) {
        if (!isset($_FILES[$field])) continue;
        $files = $_FILES[$field];
        if (!is_array($files['name'])) {
            $files = ['name' => [$files['name']], 'type' => [$files['type']],
                      'tmp_name' => [$files['tmp_name']], 'error' => [$files['error']], 'size' => [$files['size']]];
        }
        foreach ($files['name'] as $i => $origName) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;
            if ($files['size'][$i] > $maxSize) respond(false, "File '$origName' exceeds the 10 MB limit.");
            $finfo    = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($files['tmp_name'][$i]);
            if (!in_array($mimeType, $allowedTypes, true))
                respond(false, "File '$origName' has an unsupported type ($mimeType). Allowed: JPG, PNG, PDF.");
            $ext     = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
            $safeExt = in_array($ext, ['jpg','jpeg','png','gif','webp','pdf'], true) ? $ext : 'bin';
            $saveName = $field . '_' . ($i + 1) . '.' . $safeExt;
            $savePath = $driverDir . $saveName;
            if (move_uploaded_file($files['tmp_name'][$i], $savePath)) {
                $savedFiles[$field][] = 'data/drivers/' . $id . '/' . $saveName;
            }
        }
    }

    $n  = fn($v) => ($v === '' || $v === null) ? null : $v;
    $db = getDb();
    $db->prepare(
        'INSERT INTO driver_applications
            (id, status, first_name, last_name, email, phone, dob, address,
             license_number, license_expiry, years_experience,
             van_make, van_model, van_year, van_color, van_reg, van_type,
             insurance_expiry, mot_expiry,
             cargo_length, cargo_width, cargo_height, payload_kg, volume_m3,
             ext_length, ext_width, ext_height, tail_lift,
             availability, work_type, operating_areas, notes,
             photo_front_paths, photo_side_paths, photo_interior_paths,
             doc_licence_paths, doc_insurance_paths, doc_mot_paths)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
    )->execute([
        $id, 'pending', $firstName, $lastName, $email, $phone,
        $n($dob), $address, $licenseNumber, $n($licenseExpiry), $n($yearsExp),
        $vanMake, $vanModel, $n($vanYear), $vanColor, strtoupper($vanReg), $vanType,
        $n($insuranceExpiry), $n($motExpiry),
        $n($cargoLength), $n($cargoWidth), $n($cargoHeight), $n($payloadKg), $n($volumeM3),
        $n($extLength), $n($extWidth), $n($extHeight), ($tailLift === 'yes') ? 1 : 0,
        json_encode($availability), $workType, $operatingAreas, $notes,
        json_encode($savedFiles['photo_front']), json_encode($savedFiles['photo_side']),
        json_encode($savedFiles['photo_interior']), json_encode($savedFiles['doc_licence']),
        json_encode($savedFiles['doc_insurance']), json_encode($savedFiles['doc_mot']),
    ]);
    auditLog('driver.onboarded', null, 'driver_application', $id, ['email' => $email, 'van_reg' => strtoupper($vanReg)]);
    respond(true, "Your driver application has been received. We'll be in touch within 2 working days.", ['reference' => $id]);
}

function handleLogin(): void
{
    $email    = clean($_POST['email']    ?? '');
    $password = $_POST['password']       ?? '';
    if (!$email || !$password) respond(false, 'Email and password are required.');
    if (!validEmail($email))   respond(false, 'Please enter a valid email address.');

    $db   = getDb();
    $stmt = $db->prepare('SELECT * FROM users WHERE email = ? AND status = ?');
    $stmt->execute([strtolower($email), 'active']);
    $user = $stmt->fetch();

    if (!$user) {
        auditLog('user.login_failed', null, 'user', null, ['email' => $email, 'reason' => 'not_found']);
        respond(false, 'No account found for that email address.');
    }
    if (!password_verify($password, $user['password_hash'])) {
        auditLog('user.login_failed', null, 'user', $user['id'], ['email' => $email, 'reason' => 'wrong_password']);
        respond(false, 'Incorrect password. Please try again.');
    }
    $db->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?')->execute([$user['id']]);
    auditLog('user.login', $user['id'], 'user', $user['id'], ['email' => $email, 'role' => $user['role']]);
    respond(true, 'Login successful. Welcome back, ' . htmlspecialchars($user['first_name'], ENT_QUOTES, 'UTF-8') . '!', [
        'user' => ['id' => $user['id'], 'first_name' => $user['first_name'],
                   'last_name' => $user['last_name'], 'email' => $user['email'], 'role' => $user['role']],
    ]);
}

function handleRegister(): void
{
    $firstName = clean($_POST['firstName'] ?? '');
    $lastName  = clean($_POST['lastName']  ?? '');
    $email     = clean($_POST['email']     ?? '');
    $company   = clean($_POST['company']   ?? '');
    $password  = $_POST['password']        ?? '';
    $role      = clean($_POST['role']      ?? 'shipper');

    if (!$firstName || !$lastName) respond(false, 'First and last name are required.');
    if (!validEmail($email))       respond(false, 'A valid email address is required.');
    if (strlen($password) < 8)     respond(false, 'Password must be at least 8 characters long.');

    $allowedRegRoles = ['shipper', 'driver', 'owner_operator', 'corporate_staff'];
    if (!in_array($role, $allowedRegRoles, true)) $role = 'shipper';

    $db   = getDb();
    $stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([strtolower($email)]);
    if ($stmt->fetch()) respond(false, 'An account with that email address already exists.');

    $id = 'USR-' . strtoupper(substr(md5(uniqid()), 0, 8));
    $db->prepare(
        'INSERT INTO users (id, first_name, last_name, email, company, role, password_hash) VALUES (?, ?, ?, ?, ?, ?, ?)'
    )->execute([$id, $firstName, $lastName, strtolower($email), $company, $role, password_hash($password, PASSWORD_DEFAULT)]);
    auditLog('user.registered', $id, 'user', $id, ['email' => $email, 'role' => $role]);
    respond(true, 'Account created successfully! You can now sign in.', ['reference' => $id, 'role' => $role]);
}

function handleKycUpdate(): void
{
    $userId  = clean($_POST['user_id']   ?? '');
    $section = clean($_POST['section']   ?? '');
    $role    = clean($_POST['user_role'] ?? 'customer');
    if (!$userId) respond(false, 'User ID is required.');
    $allowedRoles = ['shipper', 'customer', 'driver', 'owner_operator', 'corporate_staff'];
    if (!in_array($role, $allowedRoles, true)) $role = 'shipper';

    $db   = getDb();
    $stmt = $db->prepare('SELECT data FROM kyc_data WHERE user_id = ? AND section = ?');
    $stmt->execute([$userId, $section]);
    $row      = $stmt->fetch();
    $existing = $row ? (json_decode($row['data'], true) ?? []) : [];
    $ts       = date('Y-m-d H:i:s');

    if ($section === 'profile') {
        $firstName = clean($_POST['first_name'] ?? '');
        $lastName  = clean($_POST['last_name']  ?? '');
        $email     = clean($_POST['email']      ?? '');
        $phone     = clean($_POST['phone']      ?? '');
        $dob       = clean($_POST['dob']        ?? '');
        $address   = clean($_POST['address']    ?? '');
        $company   = clean($_POST['company']    ?? '');
        if (!$firstName || !$lastName) respond(false, 'First and last name are required.');
        if (!validEmail($email))       respond(false, 'A valid email address is required.');
        $data = array_merge($existing, [
            'user_id' => $userId, 'role' => $role, 'first_name' => $firstName,
            'last_name' => $lastName, 'email' => $email, 'phone' => $phone,
            'dob' => $dob, 'address' => $address, 'company' => $company, 'updated_at' => $ts,
        ]);
        if (!isset($data['created_at'])) $data['created_at'] = $ts;
        upsertKyc($db, $userId, $section, $data);
        auditLog('kyc.profile_updated', $userId, 'user', $userId);
        respond(true, 'Profile saved successfully.', ['kyc' => $data]);
    }

    if ($section === 'kyc') {
        $nationalId  = clean($_POST['national_id'] ?? '');
        $idExpiry    = clean($_POST['id_expiry']   ?? '');
        $nationality = clean($_POST['nationality'] ?? '');
        $ssnLast4    = clean($_POST['ssn_last4']   ?? '');
        if (!$nationalId || !$idExpiry || !$nationality)
            respond(false, 'National ID, expiry date, and nationality are required.');
        $kycData = ['national_id' => $nationalId, 'id_expiry' => $idExpiry, 'nationality' => $nationality,
                    'ssn_last4' => $ssnLast4, 'kyc_status' => 'pending', 'kyc_submitted_at' => $ts];
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
        $data = array_merge($existing, $kycData);
        if (!isset($data['created_at'])) $data['created_at'] = $ts;
        $data['updated_at'] = $ts;
        upsertKyc($db, $userId, $section, $data);
        auditLog('kyc.identity_updated', $userId, 'user', $userId);
        respond(true, 'KYC information saved. Your details are pending review.', ['kyc' => $data]);
    }

    if ($section === 'documents') {
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];
        $maxSize      = 10 * 1024 * 1024;
        $docFields    = ['doc_id_front', 'doc_id_back', 'doc_address_proof', 'doc_licence'];
        $savedFiles   = [];
        $safeUserId   = preg_replace('/[^A-Za-z0-9_\-]/', '', $userId);
        if (!$safeUserId) respond(false, 'Invalid user ID format.');
        $userDir = DATA_DIR . 'users/' . $role . '/' . $safeUserId . '/';
        if (!is_dir($userDir)) mkdir($userDir, 0755, true);
        foreach ($docFields as $field) {
            if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) continue;
            $file = $_FILES[$field];
            if ($file['size'] > $maxSize) respond(false, "File '{$file['name']}' exceeds the 10 MB limit.");
            $finfo    = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']);
            if (!in_array($mimeType, $allowedMimes, true))
                respond(false, "File '{$file['name']}' has an unsupported type. Allowed: JPG, PNG, PDF.");
            $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $safeExt = in_array($ext, ['jpg','jpeg','png','gif','webp','pdf'], true) ? $ext : 'bin';
            $saveName = $field . '.' . $safeExt;
            $savePath = $userDir . $saveName;
            if (move_uploaded_file($file['tmp_name'], $savePath)) {
                $savedFiles[$field]   = 'data/users/' . $role . '/' . $safeUserId . '/' . $saveName;
                $existing[$field]     = $savedFiles[$field];
            }
        }
        $existing['documents_uploaded_at'] = $ts;
        $existing['updated_at']            = $ts;
        if (!isset($existing['created_at'])) $existing['created_at'] = $ts;
        upsertKyc($db, $userId, $section, $existing);
        auditLog('kyc.documents_uploaded', $userId, 'user', $userId, ['files' => array_keys($savedFiles)]);
        respond(true, 'Documents uploaded successfully.', ['files' => array_keys($savedFiles)]);
    }

    if ($section === 'security') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword     = $_POST['new_password']      ?? '';
        $confirmPassword = $_POST['confirm_password']  ?? '';
        if (!$currentPassword || !$newPassword || !$confirmPassword) respond(false, 'All password fields are required.');
        if (strlen($newPassword) < 8) respond(false, 'New password must be at least 8 characters long.');
        if ($newPassword !== $confirmPassword) respond(false, 'New passwords do not match.');
        $stmt = $db->prepare('SELECT password_hash FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        if (!$user) respond(false, 'User account not found.');
        if (!password_verify($currentPassword, $user['password_hash'])) {
            auditLog('user.password_change_failed', $userId, 'user', $userId);
            respond(false, 'Current password is incorrect.');
        }
        $db->prepare('UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?')
           ->execute([password_hash($newPassword, PASSWORD_DEFAULT), $userId]);
        auditLog('user.password_changed', $userId, 'user', $userId);
        respond(true, 'Password updated successfully.');
    }

    respond(false, 'Unknown section.');
}

function upsertKyc(PDO $db, string $userId, string $section, array $data): void
{
    $db->prepare(
        'INSERT INTO kyc_data (user_id, section, data) VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE data = VALUES(data), updated_at = NOW()'
    )->execute([$userId, $section, json_encode($data, JSON_UNESCAPED_UNICODE)]);
}

function handleKycLoad(): void
{
    $userId = clean($_POST['user_id'] ?? '');
    if (!$userId) respond(false, 'User ID is required.');
    $safeId = preg_replace('/[^A-Za-z0-9_\-]/', '', $userId);
    if (!$safeId) respond(false, 'Invalid user ID format.');
    $db   = getDb();
    $stmt = $db->prepare('SELECT section, data FROM kyc_data WHERE user_id = ? ORDER BY section');
    $stmt->execute([$safeId]);
    $rows = $stmt->fetchAll();
    if (empty($rows)) respond(true, 'No KYC data found.', ['kyc' => []]);
    $merged = [];
    foreach ($rows as $row) {
        $merged = array_merge($merged, json_decode($row['data'], true) ?? []);
    }
    auditLog('kyc.loaded', $userId, 'user', $userId);
    respond(true, 'KYC data loaded.', ['kyc' => $merged]);
}
