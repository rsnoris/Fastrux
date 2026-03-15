<?php
/**
 * Fastrux — Unified Form Processor
 * Handles: contact | quote | newsletter
 * Saves:   /data/{type}.csv  +  /data/{type}.json
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