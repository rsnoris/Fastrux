<?php
/**
 * Fastrux — Shipper Dashboard Data API
 * GET ?user_id=USR-XXXXXXXX → returns quotes for that user (by user_id or email fallback)
 * GET ?email=x@y.com        → returns quotes matched by email
 * POST action=add_response  → staff-only: save a response on a quote (not used here)
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

define('DATA_DIR',    __DIR__ . '/data/');
define('QUOTES_JSON', DATA_DIR . 'quote_submissions.json');

function readQuotes(): array {
    if (!file_exists(QUOTES_JSON)) {
        return [];
    }
    $data = json_decode(file_get_contents(QUOTES_JSON), true);
    return is_array($data) ? $data : [];
}

function respond(bool $success, string $message, array $extra = []): void {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $rawUserId = trim($_GET['user_id'] ?? '');
    // Validate user_id format (must match USR-XXXXXXXX pattern)
    $userId    = preg_match('/^USR-[A-Z0-9]{8}$/', $rawUserId) ? $rawUserId : '';

    // Email fallback — validated via filter
    $rawEmail = trim($_GET['email'] ?? '');
    $email    = filter_var($rawEmail, FILTER_VALIDATE_EMAIL) ? strtolower($rawEmail) : '';

    if ($userId === '' && $email === '') {
        respond(false, 'user_id or email parameter is required.');
    }

    $quotes = readQuotes();

    // Filter to only the requesting user's quotes
    $filtered = array_values(array_filter($quotes, function ($q) use ($userId, $email) {
        if ($userId !== '' && ($q['user_id'] ?? '') === $userId) {
            return true;
        }
        if ($email !== '' && strtolower($q['email'] ?? '') === $email) {
            return true;
        }
        return false;
    }));

    respond(true, 'OK', ['quotes' => $filtered, 'total' => count($filtered)]);
}

respond(false, 'Method not allowed.');
