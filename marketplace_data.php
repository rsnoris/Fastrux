<?php
/**
 * Fastrux — Marketplace Data API
 * Handles insurance listings and truck listings for the Marketplace.
 *
 * GET  ?action=list_insurance[&status=active][&coverage_type=cargo]
 * GET  ?action=list_trucks[&status=active][&listing_type=lease|sale]
 * GET  ?action=get_listing&type=insurance|truck&id=LST-XXXXXXXX
 * GET  ?action=my_listings&user_id=USR-XXXXXXXX&type=insurance|truck
 * POST action=create_insurance_listing
 * POST action=update_insurance_listing
 * POST action=delete_listing
 * POST action=create_truck_listing
 * POST action=update_truck_listing
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

define('MKT_DATA_DIR', __DIR__ . '/data/');

require_once __DIR__ . '/audit_helper.php';

if (!is_dir(MKT_DATA_DIR)) {
    mkdir(MKT_DATA_DIR, 0755, true);
}

// ── Helpers ───────────────────────────────────────────────

function mktClean(string $value): string {
    return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
}

function mktRespond(bool $success, string $message, array $extra = []): void {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit;
}

function loadJson(string $filename): array {
    $path = MKT_DATA_DIR . $filename;
    if (!file_exists($path)) return [];
    $data = json_decode(file_get_contents($path), true);
    return is_array($data) ? $data : [];
}

function saveJson(string $filename, array $data): void {
    file_put_contents(
        MKT_DATA_DIR . $filename,
        json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );
}

function appendToJson(string $filename, array $entry): void {
    $existing   = loadJson($filename);
    $existing[] = $entry;
    saveJson($filename, $existing);
}

/**
 * Verify that a user_id belongs to a user with a given role (or any of a list).
 */
function verifyUserRole(string $userId, array $allowedRoles): ?array {
    $users = loadJson('registered_users.json');
    foreach ($users as $u) {
        if (($u['id'] ?? '') === $userId) {
            if (in_array($u['role'] ?? '', $allowedRoles, true)) {
                return $u;
            }
            return null;
        }
    }
    return null;
}

// ── Router ─────────────────────────────────────────────────

$method = $_SERVER['REQUEST_METHOD'];
$action = mktClean($_GET['action'] ?? $_POST['action'] ?? '');

if ($method === 'GET') {
    switch ($action) {
        case 'list_insurance':
            listInsuranceListings();
            break;
        case 'list_trucks':
            listTruckListings();
            break;
        case 'get_listing':
            getListing();
            break;
        case 'my_listings':
            myListings();
            break;
        default:
            mktRespond(false, 'Unknown action.');
    }
} elseif ($method === 'POST') {
    switch ($action) {
        case 'create_insurance_listing':
            createInsuranceListing();
            break;
        case 'update_insurance_listing':
            updateInsuranceListing();
            break;
        case 'create_truck_listing':
            createTruckListing();
            break;
        case 'update_truck_listing':
            updateTruckListing();
            break;
        case 'delete_listing':
            deleteListing();
            break;
        default:
            mktRespond(false, 'Unknown action.');
    }
} else {
    mktRespond(false, 'Method not allowed.');
}

// ══════════════════════════════════════════════════════════
//  GET HANDLERS
// ══════════════════════════════════════════════════════════

function listInsuranceListings(): void {
    $status       = mktClean($_GET['status']        ?? 'active');
    $coverageType = mktClean($_GET['coverage_type'] ?? '');

    $listings = loadJson('insurance_listings.json');

    // Filter by status
    if ($status !== 'all') {
        $listings = array_values(array_filter($listings, fn($l) => ($l['status'] ?? 'active') === $status));
    }

    // Filter by coverage type
    if ($coverageType !== '') {
        $listings = array_values(array_filter($listings, function ($l) use ($coverageType) {
            $types = $l['coverage_types'] ?? [];
            return in_array($coverageType, $types, true);
        }));
    }

    // Sort by newest first
    usort($listings, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));

    mktRespond(true, 'OK', ['listings' => $listings, 'total' => count($listings)]);
}

function listTruckListings(): void {
    $status      = mktClean($_GET['status']       ?? 'active');
    $listingType = mktClean($_GET['listing_type'] ?? ''); // lease | sale | ''

    $listings = loadJson('truck_listings.json');

    if ($status !== 'all') {
        $listings = array_values(array_filter($listings, fn($l) => ($l['status'] ?? 'active') === $status));
    }

    if ($listingType !== '') {
        $listings = array_values(array_filter($listings, fn($l) => ($l['listing_type'] ?? '') === $listingType));
    }

    usort($listings, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));

    mktRespond(true, 'OK', ['listings' => $listings, 'total' => count($listings)]);
}

function getListing(): void {
    $type = mktClean($_GET['type'] ?? '');
    $id   = mktClean($_GET['id']   ?? '');

    if (!$id) {
        mktRespond(false, 'Listing ID is required.');
    }

    $file     = $type === 'truck' ? 'truck_listings.json' : 'insurance_listings.json';
    $listings = loadJson($file);

    foreach ($listings as $l) {
        if (($l['id'] ?? '') === $id) {
            mktRespond(true, 'OK', ['listing' => $l]);
        }
    }

    mktRespond(false, 'Listing not found.');
}

function myListings(): void {
    $userId = mktClean($_GET['user_id'] ?? '');
    $type   = mktClean($_GET['type']    ?? 'insurance');

    if (!$userId) {
        mktRespond(false, 'user_id is required.');
    }

    $file     = $type === 'truck' ? 'truck_listings.json' : 'insurance_listings.json';
    $listings = loadJson($file);

    $mine = array_values(array_filter($listings, fn($l) => ($l['user_id'] ?? '') === $userId));
    usort($mine, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));

    mktRespond(true, 'OK', ['listings' => $mine, 'total' => count($mine)]);
}

// ══════════════════════════════════════════════════════════
//  POST HANDLERS — Insurance
// ══════════════════════════════════════════════════════════

function createInsuranceListing(): void {
    $userId = mktClean($_POST['user_id'] ?? '');
    if (!$userId) {
        mktRespond(false, 'user_id is required.');
    }

    $user = verifyUserRole($userId, ['insurance_company', 'admin', 'super_admin']);
    if (!$user) {
        mktRespond(false, 'Access denied. Only insurance companies can create insurance listings.');
    }

    $title         = mktClean($_POST['title']          ?? '');
    $description   = mktClean($_POST['description']    ?? '');
    $coverageTypes = $_POST['coverage_types'] ?? [];  // array of strings
    $minCoverage   = mktClean($_POST['min_coverage']   ?? '');
    $maxCoverage   = mktClean($_POST['max_coverage']   ?? '');
    $premiumRange  = mktClean($_POST['premium_range']  ?? '');
    $serviceArea   = mktClean($_POST['service_area']   ?? '');
    $contactEmail  = mktClean($_POST['contact_email']  ?? '');
    $contactPhone  = mktClean($_POST['contact_phone']  ?? '');
    $website       = mktClean($_POST['website']        ?? '');
    $notes         = mktClean($_POST['notes']          ?? '');

    if (!$title) {
        mktRespond(false, 'Listing title is required.');
    }
    if (empty($coverageTypes)) {
        mktRespond(false, 'At least one coverage type is required.');
    }

    // Sanitize coverage types
    $allowedCoverages = ['cargo', 'liability', 'physical_damage', 'workers_comp', 'general_liability', 'occupational_accident', 'bobtail', 'non_trucking'];
    $safeCoverages = array_values(array_filter(
        is_array($coverageTypes) ? $coverageTypes : [$coverageTypes],
        fn($c) => in_array(mktClean($c), $allowedCoverages, true)
    ));
    $safeCoverages = array_map('mktClean', $safeCoverages);

    $id = 'LST-' . strtoupper(substr(md5(uniqid()), 0, 8));
    $now = date('Y-m-d H:i:s');

    $entry = [
        'id'             => $id,
        'type'           => 'insurance',
        'user_id'        => $userId,
        'company_name'   => $user['company'] ?? ($user['first_name'] . ' ' . $user['last_name']),
        'title'          => $title,
        'description'    => $description,
        'coverage_types' => $safeCoverages,
        'min_coverage'   => $minCoverage,
        'max_coverage'   => $maxCoverage,
        'premium_range'  => $premiumRange,
        'service_area'   => $serviceArea,
        'contact_email'  => $contactEmail ?: ($user['email'] ?? ''),
        'contact_phone'  => $contactPhone,
        'website'        => $website,
        'notes'          => $notes,
        'status'         => 'active',
        'created_at'     => $now,
        'updated_at'     => $now,
    ];

    appendToJson('insurance_listings.json', $entry);
    auditLog('marketplace.insurance_listing_created', $userId, 'insurance_listing', $id, "Insurance listing created: {$title}");

    mktRespond(true, 'Insurance listing created successfully.', ['id' => $id]);
}

function updateInsuranceListing(): void {
    $userId    = mktClean($_POST['user_id']    ?? '');
    $listingId = mktClean($_POST['listing_id'] ?? '');

    if (!$userId || !$listingId) {
        mktRespond(false, 'user_id and listing_id are required.');
    }

    $user = verifyUserRole($userId, ['insurance_company', 'admin', 'super_admin']);
    if (!$user) {
        mktRespond(false, 'Access denied.');
    }

    $listings = loadJson('insurance_listings.json');
    $found    = false;

    foreach ($listings as &$l) {
        if (($l['id'] ?? '') !== $listingId) continue;

        // Ownership check (admins bypass)
        if (($l['user_id'] ?? '') !== $userId && !in_array($user['role'] ?? '', ['admin', 'super_admin'], true)) {
            mktRespond(false, 'You do not own this listing.');
        }

        $fields = ['title', 'description', 'min_coverage', 'max_coverage', 'premium_range',
                   'service_area', 'contact_email', 'contact_phone', 'website', 'notes', 'status'];
        foreach ($fields as $f) {
            if (isset($_POST[$f])) {
                $l[$f] = mktClean($_POST[$f]);
            }
        }

        if (isset($_POST['coverage_types'])) {
            $allowedCoverages = ['cargo', 'liability', 'physical_damage', 'workers_comp', 'general_liability', 'occupational_accident', 'bobtail', 'non_trucking'];
            $raw = is_array($_POST['coverage_types']) ? $_POST['coverage_types'] : [$_POST['coverage_types']];
            $l['coverage_types'] = array_values(array_filter(
                array_map('mktClean', $raw),
                fn($c) => in_array($c, $allowedCoverages, true)
            ));
        }

        $l['updated_at'] = date('Y-m-d H:i:s');
        $found = true;
        break;
    }
    unset($l);

    if (!$found) {
        mktRespond(false, 'Listing not found.');
    }

    saveJson('insurance_listings.json', $listings);
    auditLog('marketplace.insurance_listing_updated', $userId, 'insurance_listing', $listingId, "Insurance listing updated: {$listingId}");

    mktRespond(true, 'Listing updated successfully.');
}

// ══════════════════════════════════════════════════════════
//  POST HANDLERS — Trucks
// ══════════════════════════════════════════════════════════

function createTruckListing(): void {
    $userId = mktClean($_POST['user_id'] ?? '');
    if (!$userId) {
        mktRespond(false, 'user_id is required.');
    }

    $user = verifyUserRole($userId, ['trucking_company', 'admin', 'super_admin']);
    if (!$user) {
        mktRespond(false, 'Access denied. Only trucking companies can create truck listings.');
    }

    $title       = mktClean($_POST['title']        ?? '');
    $description = mktClean($_POST['description']  ?? '');
    $truckType   = mktClean($_POST['truck_type']   ?? '');
    $year        = mktClean($_POST['year']         ?? '');
    $make        = mktClean($_POST['make']         ?? '');
    $model       = mktClean($_POST['model']        ?? '');
    $mileage     = mktClean($_POST['mileage']      ?? '');
    $price       = mktClean($_POST['price']        ?? '');
    $listingType = mktClean($_POST['listing_type'] ?? 'sale'); // lease | sale
    $leaseTerms  = mktClean($_POST['lease_terms']  ?? '');
    $location    = mktClean($_POST['location']     ?? '');
    $dotNumber   = mktClean($_POST['dot_number']   ?? '');
    $contactEmail= mktClean($_POST['contact_email']?? '');
    $contactPhone= mktClean($_POST['contact_phone']?? '');
    $notes       = mktClean($_POST['notes']        ?? '');

    if (!$title) {
        mktRespond(false, 'Listing title is required.');
    }
    if (!$truckType) {
        mktRespond(false, 'Truck type is required.');
    }

    $allowedListingTypes = ['lease', 'sale'];
    if (!in_array($listingType, $allowedListingTypes, true)) {
        $listingType = 'sale';
    }

    $allowedTruckTypes = ['semi_truck', 'box_truck', 'flatbed', 'refrigerated', 'tanker', 'dump_truck', 'cargo_van', 'other'];
    if (!in_array($truckType, $allowedTruckTypes, true)) {
        mktRespond(false, 'Invalid truck type.');
    }

    $id  = 'TRK-' . strtoupper(substr(md5(uniqid()), 0, 8));
    $now = date('Y-m-d H:i:s');

    $entry = [
        'id'            => $id,
        'type'          => 'truck',
        'user_id'       => $userId,
        'company_name'  => $user['company'] ?? ($user['first_name'] . ' ' . $user['last_name']),
        'title'         => $title,
        'description'   => $description,
        'truck_type'    => $truckType,
        'year'          => $year,
        'make'          => $make,
        'model'         => $model,
        'mileage'       => $mileage,
        'price'         => $price,
        'listing_type'  => $listingType,
        'lease_terms'   => $leaseTerms,
        'location'      => $location,
        'dot_number'    => $dotNumber,
        'contact_email' => $contactEmail ?: ($user['email'] ?? ''),
        'contact_phone' => $contactPhone,
        'notes'         => $notes,
        'status'        => 'active',
        'created_at'    => $now,
        'updated_at'    => $now,
    ];

    appendToJson('truck_listings.json', $entry);
    auditLog('marketplace.truck_listing_created', $userId, 'truck_listing', $id, "Truck listing created: {$title}");

    mktRespond(true, 'Truck listing created successfully.', ['id' => $id]);
}

function updateTruckListing(): void {
    $userId    = mktClean($_POST['user_id']    ?? '');
    $listingId = mktClean($_POST['listing_id'] ?? '');

    if (!$userId || !$listingId) {
        mktRespond(false, 'user_id and listing_id are required.');
    }

    $user = verifyUserRole($userId, ['trucking_company', 'admin', 'super_admin']);
    if (!$user) {
        mktRespond(false, 'Access denied.');
    }

    $listings = loadJson('truck_listings.json');
    $found    = false;

    foreach ($listings as &$l) {
        if (($l['id'] ?? '') !== $listingId) continue;

        if (($l['user_id'] ?? '') !== $userId && !in_array($user['role'] ?? '', ['admin', 'super_admin'], true)) {
            mktRespond(false, 'You do not own this listing.');
        }

        $fields = ['title', 'description', 'truck_type', 'year', 'make', 'model',
                   'mileage', 'price', 'listing_type', 'lease_terms', 'location',
                   'dot_number', 'contact_email', 'contact_phone', 'notes', 'status'];
        foreach ($fields as $f) {
            if (isset($_POST[$f])) {
                $l[$f] = mktClean($_POST[$f]);
            }
        }

        $l['updated_at'] = date('Y-m-d H:i:s');
        $found = true;
        break;
    }
    unset($l);

    if (!$found) {
        mktRespond(false, 'Listing not found.');
    }

    saveJson('truck_listings.json', $listings);
    auditLog('marketplace.truck_listing_updated', $userId, 'truck_listing', $listingId, "Truck listing updated: {$listingId}");

    mktRespond(true, 'Listing updated successfully.');
}

// ══════════════════════════════════════════════════════════
//  POST HANDLER — Delete (shared)
// ══════════════════════════════════════════════════════════

function deleteListing(): void {
    $userId    = mktClean($_POST['user_id']    ?? '');
    $listingId = mktClean($_POST['listing_id'] ?? '');
    $listType  = mktClean($_POST['list_type']  ?? 'insurance'); // insurance | truck

    if (!$userId || !$listingId) {
        mktRespond(false, 'user_id and listing_id are required.');
    }

    $allowedRoles = $listType === 'truck'
        ? ['trucking_company', 'admin', 'super_admin']
        : ['insurance_company', 'admin', 'super_admin'];

    $user = verifyUserRole($userId, $allowedRoles);
    if (!$user) {
        mktRespond(false, 'Access denied.');
    }

    $file     = $listType === 'truck' ? 'truck_listings.json' : 'insurance_listings.json';
    $listings = loadJson($file);
    $original = count($listings);

    $listings = array_values(array_filter($listings, function ($l) use ($listingId, $userId, $user) {
        if (($l['id'] ?? '') !== $listingId) return true;
        if (($l['user_id'] ?? '') !== $userId && !in_array($user['role'] ?? '', ['admin', 'super_admin'], true)) {
            return true; // don't delete — no ownership
        }
        return false;
    }));

    if (count($listings) === $original) {
        mktRespond(false, 'Listing not found or you do not have permission to delete it.');
    }

    saveJson($file, $listings);
    auditLog('marketplace.listing_deleted', $userId, $listType . '_listing', $listingId, "Listing deleted: {$listingId}");

    mktRespond(true, 'Listing deleted successfully.');
}
