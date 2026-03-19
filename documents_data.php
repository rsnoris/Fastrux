<?php
/**
 * Fastrux — Documents Data API
 * Handles document upload, download, listing, and deletion for all user roles.
 *
 * GET  ?action=list&user_id=USR-XXXXXXXX[&category=Invoice]
 * GET  ?action=download&doc_id=DOC-XXXXXXXX&user_id=USR-XXXXXXXX
 * POST action=upload        (multipart/form-data)
 * POST action=delete        (JSON body: {doc_id, user_id})
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

define('DOC_DATA_DIR',  __DIR__ . '/data/documents/');
define('DOC_INDEX',     __DIR__ . '/data/documents_index.json');
define('DOC_MAX_SIZE',  20 * 1024 * 1024); // 20 MB

require_once __DIR__ . '/audit_helper.php';

// ── Helpers ──────────────────────────────────────────────────────

function docClean(string $v): string {
    return htmlspecialchars(strip_tags(trim($v)), ENT_QUOTES, 'UTF-8');
}

function docRespond(bool $ok, string $msg = '', array $extra = []): void {
    header('Content-Type: application/json');
    echo json_encode(array_merge(['success' => $ok, 'message' => $msg], $extra));
    exit;
}

function readIndex(): array {
    if (!file_exists(DOC_INDEX)) return [];
    $d = json_decode(file_get_contents(DOC_INDEX), true);
    return is_array($d) ? $d : [];
}

function writeIndex(array $data): void {
    if (!is_dir(dirname(DOC_INDEX))) mkdir(dirname(DOC_INDEX), 0755, true);
    file_put_contents(DOC_INDEX, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function validateUserId(string $raw): string {
    return preg_match('/^USR-[A-Z0-9]{8}$/', $raw) ? $raw : '';
}

function validateDocId(string $raw): string {
    return preg_match('/^DOC-[A-Z0-9]{8}$/', $raw) ? $raw : '';
}

// Allowed document categories
define('ALLOWED_CATEGORIES', [
    'Bill of lading (BOL)',
    'Broker agreement',
    'Cargo insurance',
    'Carrier agreement',
    'Certificate of insurance',
    "Driver's license",
    'Fuel receipt',
    'Invoice',
    'Liability insurance',
    'Lumper receipt',
    'Operating authority',
    'Other',
    'Packing list',
    'Proof of delivery (POD)',
    'Rate confirmation',
    'References',
    'Scale receipt',
    'Shipper agreement',
    'Tax info',
    'Truck wash receipt',
    'Void cheque',
    'W-9',
    'Weight tickets',
]);

// ── GET ──────────────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = docClean($_GET['action'] ?? '');

    // ── list ──────────────────────────────────────────────────────
    if ($action === 'list') {
        $userId = validateUserId(trim($_GET['user_id'] ?? ''));
        if (!$userId) docRespond(false, 'Valid user_id required.');

        $category = docClean($_GET['category'] ?? '');
        if ($category && !in_array($category, ALLOWED_CATEGORIES, true)) {
            docRespond(false, 'Invalid category.');
        }

        $index = readIndex();
        $docs  = array_values(array_filter($index, function ($d) use ($userId, $category) {
            if (($d['user_id'] ?? '') !== $userId) return false;
            if ($category && ($d['category'] ?? '') !== $category) return false;
            return true;
        }));

        // Sort by uploaded_at descending
        usort($docs, fn($a, $b) => strcmp($b['uploaded_at'] ?? '', $a['uploaded_at'] ?? ''));

        docRespond(true, 'OK', ['documents' => $docs, 'total' => count($docs)]);
    }

    // ── download ──────────────────────────────────────────────────
    if ($action === 'download') {
        $docId  = validateDocId(trim($_GET['doc_id'] ?? ''));
        $userId = validateUserId(trim($_GET['user_id'] ?? ''));
        if (!$docId || !$userId) docRespond(false, 'doc_id and user_id required.');

        $index = readIndex();
        $doc   = null;
        foreach ($index as $d) {
            if (($d['id'] ?? '') === $docId && ($d['user_id'] ?? '') === $userId) {
                $doc = $d;
                break;
            }
        }
        if (!$doc) docRespond(false, 'Document not found.');

        $filePath = DOC_DATA_DIR . $doc['stored_name'];
        if (!file_exists($filePath)) docRespond(false, 'File not found on server.');

        // Deliver file
        $mime = $doc['mime_type'] ?? 'application/octet-stream';
        $origName = $doc['original_name'] ?? basename($filePath);
        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . addslashes($origName) . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }

    docRespond(false, 'Unknown action.');
}

// ── POST ─────────────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Detect JSON vs multipart
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    $isJson      = str_contains($contentType, 'application/json');

    if ($isJson) {
        $body   = json_decode(file_get_contents('php://input'), true) ?? [];
        $action = docClean($body['action'] ?? '');
    } else {
        $action = docClean($_POST['action'] ?? '');
    }

    // ── delete ────────────────────────────────────────────────────
    if ($action === 'delete') {
        $docId  = validateDocId(docClean($isJson ? ($body['doc_id'] ?? '') : ($_POST['doc_id'] ?? '')));
        $userId = validateUserId(docClean($isJson ? ($body['user_id'] ?? '') : ($_POST['user_id'] ?? '')));
        if (!$docId || !$userId) docRespond(false, 'doc_id and user_id required.');

        $index   = readIndex();
        $removed = null;
        $newIndex = [];
        foreach ($index as $d) {
            if (($d['id'] ?? '') === $docId && ($d['user_id'] ?? '') === $userId) {
                $removed = $d;
            } else {
                $newIndex[] = $d;
            }
        }
        if (!$removed) docRespond(false, 'Document not found.');

        // Delete physical file
        $filePath = DOC_DATA_DIR . $removed['stored_name'];
        if (file_exists($filePath)) unlink($filePath);

        writeIndex($newIndex);
        auditLog('document.delete', $userId, 'document', $docId, 'Deleted ' . ($removed['original_name'] ?? $docId));
        docRespond(true, 'Document deleted.');
    }

    // ── upload ────────────────────────────────────────────────────
    if ($action === 'upload') {
        $userId   = validateUserId(docClean($_POST['user_id'] ?? ''));
        $category = docClean($_POST['category'] ?? '');
        $notes    = docClean(substr($_POST['notes'] ?? '', 0, 500));

        if (!$userId)  docRespond(false, 'Valid user_id required.');
        if (!$category || !in_array($category, ALLOWED_CATEGORIES, true)) {
            docRespond(false, 'Valid category required.');
        }
        if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
            $errCode = $_FILES['document']['error'] ?? -1;
            docRespond(false, 'File upload failed (error ' . intval($errCode) . ').');
        }

        $file    = $_FILES['document'];
        $maxSize = DOC_MAX_SIZE;
        $allowedMimes = [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain',
            'text/csv',
        ];
        $allowedExts = ['jpg','jpeg','png','gif','webp','pdf','doc','docx','xls','xlsx','txt','csv'];

        if ($file['size'] > $maxSize) docRespond(false, 'File exceeds 20 MB limit.');

        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        if (!in_array($mimeType, $allowedMimes, true)) {
            docRespond(false, 'File type not allowed.');
        }

        $origName = $file['name'];
        $ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExts, true)) $ext = 'bin';

        if (!is_dir(DOC_DATA_DIR)) mkdir(DOC_DATA_DIR, 0755, true);

        $docId      = 'DOC-' . strtoupper(substr(md5(uniqid('', true)), 0, 8));
        $storedName = $docId . '.' . $ext;
        $savePath   = DOC_DATA_DIR . $storedName;

        if (!move_uploaded_file($file['tmp_name'], $savePath)) {
            docRespond(false, 'Failed to save file.');
        }

        $entry = [
            'id'            => $docId,
            'user_id'       => $userId,
            'category'      => $category,
            'original_name' => docClean(substr($origName, 0, 255)),
            'stored_name'   => $storedName,
            'mime_type'     => $mimeType,
            'file_size'     => intval($file['size']),
            'notes'         => $notes,
            'uploaded_at'   => date('Y-m-d H:i:s'),
        ];

        $index   = readIndex();
        $index[] = $entry;
        writeIndex($index);

        auditLog('document.upload', $userId, 'document', $docId, $category . ': ' . $entry['original_name']);
        docRespond(true, 'Document uploaded.', ['document' => $entry]);
    }

    docRespond(false, 'Unknown action.');
}

docRespond(false, 'Method not allowed.');
