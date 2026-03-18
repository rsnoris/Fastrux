<?php
/**
 * loadboard_data.php — Loadboard API
 *
 * GET  ?action=find_loads          — Search/filter available loads
 * GET  ?action=density_data        — Load density per region for map
 * GET  ?action=my_loads&user_id=   — Loads in user's personal categories
 * POST action=update_load_status   — Save/hide/contact/book a load for a user
 * POST action=update_load_outcome  — Mark delivered/cancelled/rejected
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

define('DATA_DIR',         __DIR__ . '/data/');
define('LOADS_BOARD_JSON', DATA_DIR . 'loadboard_loads.json');
define('USER_LOADS_JSON',  DATA_DIR . 'loadboard_user_loads.json');

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

// ── Seed sample loads if none exist ─────────────────────────────
function seedLoads(): array
{
    $cities = [
        ['city' => 'Chicago', 'state' => 'IL', 'lat' => 41.8781, 'lng' => -87.6298],
        ['city' => 'Houston', 'state' => 'TX', 'lat' => 29.7604, 'lng' => -95.3698],
        ['city' => 'Los Angeles', 'state' => 'CA', 'lat' => 34.0522, 'lng' => -118.2437],
        ['city' => 'New York', 'state' => 'NY', 'lat' => 40.7128, 'lng' => -74.006],
        ['city' => 'Phoenix', 'state' => 'AZ', 'lat' => 33.4484, 'lng' => -112.074],
        ['city' => 'Philadelphia', 'state' => 'PA', 'lat' => 39.9526, 'lng' => -75.1652],
        ['city' => 'San Antonio', 'state' => 'TX', 'lat' => 29.4241, 'lng' => -98.4936],
        ['city' => 'Dallas', 'state' => 'TX', 'lat' => 32.7767, 'lng' => -96.797],
        ['city' => 'San Jose', 'state' => 'CA', 'lat' => 37.3382, 'lng' => -121.8863],
        ['city' => 'Austin', 'state' => 'TX', 'lat' => 30.2672, 'lng' => -97.7431],
        ['city' => 'Jacksonville', 'state' => 'FL', 'lat' => 30.3322, 'lng' => -81.6557],
        ['city' => 'Columbus', 'state' => 'OH', 'lat' => 39.9612, 'lng' => -82.9988],
        ['city' => 'Indianapolis', 'state' => 'IN', 'lat' => 39.7684, 'lng' => -86.1581],
        ['city' => 'Charlotte', 'state' => 'NC', 'lat' => 35.2271, 'lng' => -80.8431],
        ['city' => 'Memphis', 'state' => 'TN', 'lat' => 35.1495, 'lng' => -90.0489],
        ['city' => 'Nashville', 'state' => 'TN', 'lat' => 36.1627, 'lng' => -86.7816],
        ['city' => 'Atlanta', 'state' => 'GA', 'lat' => 33.749, 'lng' => -84.388],
        ['city' => 'Seattle', 'state' => 'WA', 'lat' => 47.6062, 'lng' => -122.3321],
        ['city' => 'Denver', 'state' => 'CO', 'lat' => 39.7392, 'lng' => -104.9903],
        ['city' => 'Kansas City', 'state' => 'MO', 'lat' => 39.0997, 'lng' => -94.5786],
        ['city' => 'Minneapolis', 'state' => 'MN', 'lat' => 44.9778, 'lng' => -93.265],
        ['city' => 'Miami', 'state' => 'FL', 'lat' => 25.7617, 'lng' => -80.1918],
        ['city' => 'Tampa', 'state' => 'FL', 'lat' => 27.9506, 'lng' => -82.4572],
        ['city' => 'Portland', 'state' => 'OR', 'lat' => 45.5051, 'lng' => -122.675],
        ['city' => 'Las Vegas', 'state' => 'NV', 'lat' => 36.1699, 'lng' => -115.1398],
        ['city' => 'Salt Lake City', 'state' => 'UT', 'lat' => 40.7608, 'lng' => -111.891],
        ['city' => 'St. Louis', 'state' => 'MO', 'lat' => 38.627, 'lng' => -90.1994],
        ['city' => 'Detroit', 'state' => 'MI', 'lat' => 42.3314, 'lng' => -83.0458],
        ['city' => 'Baltimore', 'state' => 'MD', 'lat' => 39.2904, 'lng' => -76.6122],
        ['city' => 'Boston', 'state' => 'MA', 'lat' => 42.3601, 'lng' => -71.0589],
    ];

    $equipment = ['Dry Van', 'Reefer', 'Flatbed', 'Step Deck', 'Tanker', 'Lowboy', 'Power Only', 'Intermodal'];
    $loadTypes = ['FTL', 'LTL', 'Partial'];
    $commodities = [
        'Auto Parts', 'Electronics', 'Produce', 'Frozen Goods', 'Steel Coils',
        'Lumber', 'Construction Equipment', 'Chemicals', 'Furniture', 'Beverages',
        'Paper Products', 'Plastics', 'Agricultural Products', 'Medical Supplies',
        'Retail Goods', 'Machinery', 'Glass Products', 'Textiles', 'Packaging',
    ];

    $loads = [];
    $now = time();

    for ($i = 1; $i <= 80; $i++) {
        $fromIdx = array_rand($cities);
        do { $toIdx = array_rand($cities); } while ($toIdx === $fromIdx);

        $from = $cities[$fromIdx];
        $to   = $cities[$toIdx];

        // Calculate approximate distance (miles)
        $lat1 = deg2rad($from['lat']); $lon1 = deg2rad($from['lng']);
        $lat2 = deg2rad($to['lat']);   $lon2 = deg2rad($to['lng']);
        $dlat = $lat2 - $lat1; $dlon = $lon2 - $lon1;
        $a = sin($dlat/2)**2 + cos($lat1)*cos($lat2)*sin($dlon/2)**2;
        $distance = (int)(2 * 3959 * asin(sqrt($a)));

        $eqIdx   = array_rand($equipment);
        $ltIdx   = array_rand($loadTypes);
        $cmdIdx  = array_rand($commodities);
        $weight  = rand(5, 44) * 1000;
        $rpm     = round(rand(200, 400) / 100, 2); // rate per mile
        $total   = (int)($distance * $rpm);
        $pickupTs = $now + rand(86400, 86400 * 14); // 1–14 days from now
        $hazmat  = rand(0, 9) === 0; // 10% chance

        $loads[] = [
            'id'           => 'LB-' . strtoupper(substr(md5($i . 'seed'), 0, 8)),
            'origin_city'  => $from['city'],
            'origin_state' => $from['state'],
            'origin_lat'   => $from['lat'],
            'origin_lng'   => $from['lng'],
            'dest_city'    => $to['city'],
            'dest_state'   => $to['state'],
            'dest_lat'     => $to['lat'],
            'dest_lng'     => $to['lng'],
            'distance_mi'  => $distance,
            'equipment'    => $equipment[$eqIdx],
            'load_type'    => $loadTypes[$ltIdx],
            'commodity'    => $commodities[$cmdIdx],
            'weight_lbs'   => $weight,
            'rate_total'   => $total,
            'rate_per_mile'=> $rpm,
            'pickup_ts'    => $pickupTs,
            'pickup_date'  => date('Y-m-d', $pickupTs),
            'hazmat'       => $hazmat,
            'posted_ts'    => $now - rand(0, 86400 * 3),
            'posted_by'    => 'SHP-' . strtoupper(substr(md5($i), 0, 6)),
            'contact_name' => 'Dispatcher ' . $i,
            'contact_phone'=> '+1-800-' . str_pad(rand(100, 999), 3, '0') . '-' . str_pad(rand(1000, 9999), 4, '0'),
            'notes'        => '',
        ];
    }

    return $loads;
}

function getLoads(): array
{
    $loads = readJson(LOADS_BOARD_JSON);
    if (empty($loads)) {
        $loads = seedLoads();
        writeJson(LOADS_BOARD_JSON, $loads);
    }
    return $loads;
}

// ── Route ────────────────────────────────────────────────────────
$method = $_SERVER['REQUEST_METHOD'];
$action = $method === 'GET'
    ? clean($_GET['action'] ?? '')
    : clean((json_decode(file_get_contents('php://input'), true)['action'] ?? $_POST['action'] ?? ''));

// ── GET: find_loads ──────────────────────────────────────────────
if ($method === 'GET' && $action === 'find_loads') {
    $loads = getLoads();

    // Filters
    $originCity   = strtolower(clean($_GET['origin_city']   ?? ''));
    $originState  = strtoupper(clean($_GET['origin_state']  ?? ''));
    $destCity     = strtolower(clean($_GET['dest_city']     ?? ''));
    $destState    = strtoupper(clean($_GET['dest_state']    ?? ''));
    $equipment    = clean($_GET['equipment']   ?? '');
    $loadType     = clean($_GET['load_type']   ?? '');
    $minWeight    = (int)($_GET['min_weight']  ?? 0);
    $maxWeight    = (int)($_GET['max_weight']  ?? 0);
    $minDist      = (int)($_GET['min_dist']    ?? 0);
    $maxDist      = (int)($_GET['max_dist']    ?? 0);
    $minRate      = (float)($_GET['min_rate']  ?? 0);
    $maxRate      = (float)($_GET['max_rate']  ?? 0);
    $pickupFrom   = clean($_GET['pickup_from'] ?? '');
    $pickupTo     = clean($_GET['pickup_to']   ?? '');
    $hazmat       = isset($_GET['hazmat']) ? filter_var($_GET['hazmat'], FILTER_VALIDATE_BOOLEAN) : null;
    $commodity    = strtolower(clean($_GET['commodity'] ?? ''));

    $filtered = array_values(array_filter($loads, function($l) use (
        $originCity, $originState, $destCity, $destState, $equipment, $loadType,
        $minWeight, $maxWeight, $minDist, $maxDist, $minRate, $maxRate,
        $pickupFrom, $pickupTo, $hazmat, $commodity
    ) {
        if ($originCity  && strpos(strtolower($l['origin_city']),  $originCity)  === false) return false;
        if ($originState && $l['origin_state'] !== $originState) return false;
        if ($destCity    && strpos(strtolower($l['dest_city']),    $destCity)    === false) return false;
        if ($destState   && $l['dest_state']   !== $destState)   return false;
        if ($equipment   && $l['equipment']    !== $equipment)   return false;
        if ($loadType    && $l['load_type']    !== $loadType)    return false;
        if ($minWeight   && $l['weight_lbs']   < $minWeight)     return false;
        if ($maxWeight   && $l['weight_lbs']   > $maxWeight)     return false;
        if ($minDist     && $l['distance_mi']  < $minDist)       return false;
        if ($maxDist     && $l['distance_mi']  > $maxDist)       return false;
        if ($minRate     && $l['rate_per_mile'] < $minRate)      return false;
        if ($maxRate     && $l['rate_per_mile'] > $maxRate)      return false;
        if ($pickupFrom  && $l['pickup_date']  < $pickupFrom)    return false;
        if ($pickupTo    && $l['pickup_date']  > $pickupTo)      return false;
        if ($hazmat !== null && (bool)$l['hazmat'] !== $hazmat)  return false;
        if ($commodity   && strpos(strtolower($l['commodity']), $commodity) === false) return false;
        return true;
    }));

    // Sort newest posted first
    usort($filtered, fn($a, $b) => $b['posted_ts'] - $a['posted_ts']);

    respond(true, '', ['loads' => $filtered, 'total' => count($filtered)]);
}

// ── GET: density_data ────────────────────────────────────────────
if ($method === 'GET' && $action === 'density_data') {
    $loads = getLoads();

    $regionCounts = [];
    foreach ($loads as $l) {
        $key = $l['origin_state'];
        if (!isset($regionCounts[$key])) {
            $regionCounts[$key] = [
                'state'      => $l['origin_state'],
                'count'      => 0,
                'lat'        => $l['origin_lat'],
                'lng'        => $l['origin_lng'],
                'city'       => $l['origin_city'],
                'top_equip'  => [],
            ];
        }
        $regionCounts[$key]['count']++;
        $eq = $l['equipment'];
        $regionCounts[$key]['top_equip'][$eq] = ($regionCounts[$key]['top_equip'][$eq] ?? 0) + 1;
    }

    // Simplify top equipment
    foreach ($regionCounts as &$r) {
        arsort($r['top_equip']);
        $r['top_equip'] = array_slice(array_keys($r['top_equip']), 0, 3);
    }
    unset($r);

    $regions = array_values($regionCounts);
    usort($regions, fn($a, $b) => $b['count'] - $a['count']);

    respond(true, '', ['regions' => $regions]);
}

// ── GET: my_loads ─────────────────────────────────────────────────
if ($method === 'GET' && $action === 'my_loads') {
    $userId = validateUserId(clean($_GET['user_id'] ?? ''));
    if (!$userId) {
        respond(false, 'Invalid user_id');
    }

    $allUserLoads = readJson(USER_LOADS_JSON);
    $userLoads    = $allUserLoads[$userId] ?? [];

    respond(true, '', ['loads' => $userLoads]);
}

// ── POST: update_load_status ──────────────────────────────────────
if ($method === 'POST') {
    $raw    = json_decode(file_get_contents('php://input'), true) ?? [];
    $action = clean($raw['action'] ?? $_POST['action'] ?? '');

    if ($action === 'update_load_status') {
        $userId  = validateUserId(clean($raw['user_id'] ?? ''));
        $loadId  = clean($raw['load_id'] ?? '');
        $status  = clean($raw['status'] ?? ''); // saved|hidden|contacted|booked|pickup_complete|delivered|cancelled|rejected|remove

        if (!$userId) respond(false, 'Invalid user_id');
        if (!$loadId) respond(false, 'Missing load_id');

        $validStatuses = ['saved', 'hidden', 'contacted', 'booked', 'pickup_complete', 'delivered', 'cancelled', 'rejected', 'remove'];
        if (!in_array($status, $validStatuses, true)) respond(false, 'Invalid status');

        // Look up load details
        $loads     = getLoads();
        $loadData  = null;
        foreach ($loads as $l) {
            if ($l['id'] === $loadId) { $loadData = $l; break; }
        }
        if (!$loadData) respond(false, 'Load not found');

        $allUserLoads = readJson(USER_LOADS_JSON);
        if (!isset($allUserLoads[$userId])) {
            $allUserLoads[$userId] = [];
        }

        if ($status === 'remove') {
            // Remove load from user's list
            $allUserLoads[$userId] = array_values(array_filter(
                $allUserLoads[$userId],
                fn($ul) => $ul['load_id'] !== $loadId
            ));
        } else {
            // Update or add
            $found = false;
            foreach ($allUserLoads[$userId] as &$ul) {
                if ($ul['load_id'] === $loadId) {
                    $ul['status']     = $status;
                    $ul['updated_ts'] = time();
                    $found = true;
                    break;
                }
            }
            unset($ul);

            if (!$found) {
                $allUserLoads[$userId][] = [
                    'load_id'     => $loadId,
                    'status'      => $status,
                    'saved_ts'    => time(),
                    'updated_ts'  => time(),
                    'load_snapshot' => [
                        'origin'      => $loadData['origin_city'] . ', ' . $loadData['origin_state'],
                        'destination' => $loadData['dest_city']   . ', ' . $loadData['dest_state'],
                        'equipment'   => $loadData['equipment'],
                        'load_type'   => $loadData['load_type'],
                        'distance_mi' => $loadData['distance_mi'],
                        'rate_total'  => $loadData['rate_total'],
                        'pickup_date' => $loadData['pickup_date'],
                        'weight_lbs'  => $loadData['weight_lbs'],
                        'commodity'   => $loadData['commodity'],
                    ],
                ];
            }
        }

        writeJson(USER_LOADS_JSON, $allUserLoads);
        respond(true, 'Load status updated');
    }

    // ── POST: track_view (recently viewed) ───────────────────────
    if ($action === 'track_view') {
        $userId = validateUserId(clean($raw['user_id'] ?? ''));
        $loadId = clean($raw['load_id'] ?? '');

        if (!$userId || !$loadId) respond(false, 'Missing params');

        $loads    = getLoads();
        $loadData = null;
        foreach ($loads as $l) {
            if ($l['id'] === $loadId) { $loadData = $l; break; }
        }
        if (!$loadData) respond(false, 'Load not found');

        $allUserLoads = readJson(USER_LOADS_JSON);
        if (!isset($allUserLoads[$userId])) {
            $allUserLoads[$userId] = [];
        }

        // Check if already tracked
        foreach ($allUserLoads[$userId] as $ul) {
            if ($ul['load_id'] === $loadId) {
                // Already tracked, just update view_ts
                foreach ($allUserLoads[$userId] as &$ul2) {
                    if ($ul2['load_id'] === $loadId) {
                        $ul2['view_ts'] = time();
                    }
                }
                unset($ul2);
                writeJson(USER_LOADS_JSON, $allUserLoads);
                respond(true, 'View tracked');
            }
        }

        // Add new recently-viewed entry
        $allUserLoads[$userId][] = [
            'load_id'     => $loadId,
            'status'      => 'viewed',
            'saved_ts'    => time(),
            'updated_ts'  => time(),
            'view_ts'     => time(),
            'load_snapshot' => [
                'origin'      => $loadData['origin_city'] . ', ' . $loadData['origin_state'],
                'destination' => $loadData['dest_city']   . ', ' . $loadData['dest_state'],
                'equipment'   => $loadData['equipment'],
                'load_type'   => $loadData['load_type'],
                'distance_mi' => $loadData['distance_mi'],
                'rate_total'  => $loadData['rate_total'],
                'pickup_date' => $loadData['pickup_date'],
                'weight_lbs'  => $loadData['weight_lbs'],
                'commodity'   => $loadData['commodity'],
            ],
        ];

        // Keep only last 20 viewed
        $viewed = array_filter($allUserLoads[$userId], fn($ul) => $ul['status'] === 'viewed');
        if (count($viewed) > 20) {
            usort($viewed, fn($a, $b) => ($b['view_ts'] ?? 0) - ($a['view_ts'] ?? 0));
            $viewed = array_slice($viewed, 0, 20);
            $nonViewed = array_filter($allUserLoads[$userId], fn($ul) => $ul['status'] !== 'viewed');
            $allUserLoads[$userId] = array_values(array_merge(array_values($nonViewed), array_values($viewed)));
        }

        writeJson(USER_LOADS_JSON, $allUserLoads);
        respond(true, 'View tracked');
    }
}

respond(false, 'Unknown action');
