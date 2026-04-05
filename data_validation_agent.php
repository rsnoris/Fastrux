<?php
/**
 * Fastrux — Data Validation & Live-Refresh Agent
 *
 * Validates seed/live POI data against freely-available public APIs:
 *   • Overpass API (OpenStreetMap) — gas stations, hotels, restaurants,
 *     carriers (logistics offices/freight terminals), insurance company offices
 *   • Nominatim (OpenStreetMap) — coordinate → address verification
 *
 * Intended to be called:
 *   1) Via CLI cron:    php data_validation_agent.php [--action=full_refresh]
 *                                                     [--category=gas_station]
 *   2) Via HTTP (admin-only):
 *      POST data_validation_agent.php
 *           action=full_refresh|validate_seed|refresh_live
 *           category=all|gas_station|hotel|restaurant|carrier|insurance
 *           admin_key=<FASTRUX_AGENT_KEY env var>
 *
 * Output written to:
 *   data/live_data/{category}.json     — Overpass live POI data (per category)
 *   data/validation_log.json           — Timestamped validation run log
 *
 * Overpass rate-limit: ≤ 1 request / second with polite 1-second sleeps.
 */

ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

// ── Environment detection ──────────────────────────────────────────────────
define('IS_CLI', PHP_SAPI === 'cli');
define('AGENT_DATA_DIR', __DIR__ . '/data/');
define('AGENT_SEED_DIR', __DIR__ . '/seed_data/');
define('AGENT_LIVE_DIR', AGENT_DATA_DIR . 'live_data/');
define('AGENT_LOG_FILE', AGENT_DATA_DIR . 'validation_log.json');

// Live-data cache TTL: 24 hours
define('AGENT_CACHE_TTL', 86400);

// Overpass API endpoint
define('OVERPASS_API_URL', 'https://overpass-api.de/api/interpreter');

// Nominatim endpoint
define('NOMINATIM_API_URL', 'https://nominatim.openstreetmap.org');

// Max validation age (48 h) before a seed record is marked stale
define('AGENT_STALE_AGE', 172800);

// HTTP timeout for external calls (seconds)
define('AGENT_HTTP_TIMEOUT', 30);

// ── Setup ──────────────────────────────────────────────────────────────────
if (!is_dir(AGENT_LIVE_DIR)) {
    mkdir(AGENT_LIVE_DIR, 0755, true);
}

// ── Auth ───────────────────────────────────────────────────────────────────
if (!IS_CLI) {
    header('Content-Type: application/json');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        agentRespond(false, 'POST required.');
    }

    $envKey = getenv('FASTRUX_AGENT_KEY');
    if (empty($envKey)) {
        agentRespond(false, 'Agent key not configured on server.');
    }
    $providedKey = $_POST['admin_key'] ?? '';
    if (!hash_equals($envKey, $providedKey)) {
        http_response_code(403);
        agentRespond(false, 'Invalid admin key.');
    }
}

// ── Parse arguments ────────────────────────────────────────────────────────
if (IS_CLI) {
    $opts = getopt('', ['action:', 'category:']);
    $action   = $opts['action']   ?? 'full_refresh';
    $category = $opts['category'] ?? 'all';
} else {
    $action   = agentClean($_POST['action']   ?? 'full_refresh');
    $category = agentClean($_POST['category'] ?? 'all');
}

$validActions = ['full_refresh', 'validate_seed', 'refresh_live'];
if (!in_array($action, $validActions, true)) {
    agentRespond(false, 'Invalid action. Use: ' . implode(', ', $validActions));
}

$validCategories = ['all', 'gas_station', 'hotel', 'restaurant', 'carrier', 'insurance'];
if (!in_array($category, $validCategories, true)) {
    agentRespond(false, 'Invalid category. Use: ' . implode(', ', $validCategories));
}

// ── Category configuration ─────────────────────────────────────────────────
/**
 * Overpass queries for each category.
 * {{BBOX}} is replaced with south,west,north,east bounding box string.
 */
$categoryConfig = [
    'gas_station' => [
        'seed_file'      => 'gas_stations_seed.json',
        'live_file'      => 'gas_station.json',
        'id_prefix'      => 'GAS',
        'overpass_query' => '[out:json][timeout:30];node[amenity=fuel]({{BBOX}});out body;',
        'normalize_fn'   => 'normalizeOverpassGasStation',
    ],
    'hotel' => [
        'seed_file'      => 'hotels_seed.json',
        'live_file'      => 'hotel.json',
        'id_prefix'      => 'HTL',
        'overpass_query' => '[out:json][timeout:30];(node[tourism=hotel]({{BBOX}});node[tourism=motel]({{BBOX}});node[tourism=hostel]({{BBOX}}););out body;',
        'normalize_fn'   => 'normalizeOverpassHotel',
    ],
    'restaurant' => [
        'seed_file'      => 'restaurants.json',
        'live_file'      => 'restaurant.json',
        'id_prefix'      => 'RST',
        'overpass_query' => '[out:json][timeout:30];node[amenity=restaurant]({{BBOX}});out body;',
        'normalize_fn'   => 'normalizeOverpassRestaurant',
    ],
    'carrier' => [
        'seed_file'      => 'carriers.json',
        'live_file'      => 'carrier.json',
        'id_prefix'      => 'CAR',
        'overpass_query' => '[out:json][timeout:30];(node[office=logistics]({{BBOX}});node[office=transport]({{BBOX}});node[amenity=freight_terminal]({{BBOX}});node[office=moving_company]({{BBOX}}););out body;',
        'normalize_fn'   => 'normalizeOverpassCarrier',
    ],
    'insurance' => [
        'seed_file'      => 'insurance_companies.json',
        'live_file'      => 'insurance.json',
        'id_prefix'      => 'INS',
        'overpass_query' => '[out:json][timeout:30];node[office=insurance]({{BBOX}});out body;',
        'normalize_fn'   => 'normalizeOverpassInsurance',
    ],
];

// Major US metro area bounding boxes [south, west, north, east] for live refresh
$usBboxes = [
    'Northeast'   => [38.5, -79.5, 45.5, -67.5],
    'Southeast'   => [24.5, -88.0, 37.5, -75.5],
    'Midwest'     => [36.5, -97.0, 49.5, -80.5],
    'South'       => [25.5, -106.5, 37.5, -88.5],
    'Mountain'    => [31.0, -117.0, 49.5, -103.5],
    'Pacific'     => [32.0, -124.5, 49.5, -114.5],
];

// ── Run selected action ────────────────────────────────────────────────────
$categoriesToRun = ($category === 'all')
    ? array_keys($categoryConfig)
    : [$category];

$results = [];

foreach ($categoriesToRun as $cat) {
    $cfg = $categoryConfig[$cat];

    if (in_array($action, ['full_refresh', 'refresh_live'], true)) {
        $results[$cat]['live_refresh'] = refreshLiveData($cat, $cfg, $usBboxes);
    }

    if (in_array($action, ['full_refresh', 'validate_seed'], true)) {
        $results[$cat]['seed_validation'] = validateSeedData($cat, $cfg);
    }
}

// Write validation log
appendValidationLog($action, $category, $results);

agentRespond(true, 'Agent completed successfully.', [
    'action'   => $action,
    'category' => $category,
    'results'  => $results,
]);

// ══════════════════════════════════════════════════════════════════════════
//  ACTION HANDLERS
// ══════════════════════════════════════════════════════════════════════════

/**
 * Fetch live POI data from Overpass API for all US regional bounding boxes
 * and merge results into data/live_data/{category}.json.
 */
function refreshLiveData(string $cat, array $cfg, array $bboxes): array {
    $allPlaces  = [];
    $seen       = [];
    $apiErrors  = 0;
    $apiSuccess = 0;

    foreach ($bboxes as $region => $bbox) {
        [$s, $w, $n, $e] = $bbox;
        $bboxStr = "{$s},{$w},{$n},{$e}";
        $query   = str_replace('{{BBOX}}', $bboxStr, $cfg['overpass_query']);

        agentLog("  [{$cat}] Querying Overpass for {$region}…");
        $raw = overpassFetch($query);

        if ($raw === null) {
            $apiErrors++;
            agentLog("  [{$cat}] Overpass error for {$region}");
            continue;
        }

        $data = json_decode($raw, true);
        if (!isset($data['elements']) || !is_array($data['elements'])) {
            $apiErrors++;
            continue;
        }

        $apiSuccess++;
        $normFn = $cfg['normalize_fn'];

        foreach ($data['elements'] as $el) {
            if (!isset($el['lat'], $el['lon'])) continue;
            $tags = $el['tags'] ?? [];
            if (empty($tags['name'])) continue; // skip unnamed

            // Deduplicate by OSM node ID
            $osmId = 'OSM-' . $cat . '-' . $el['id'];
            if (isset($seen[$osmId])) continue;
            $seen[$osmId] = true;

            $place = $normFn($el, $tags, $osmId);
            if ($place !== null) {
                $allPlaces[] = $place;
            }
        }

        // Polite delay — respect Overpass rate limits
        sleep(1);
    }

    // Sort by name for consistent output
    usort($allPlaces, fn($a, $b) => strcmp($a['name'], $b['name']));

    $outPath = AGENT_LIVE_DIR . $cfg['live_file'];
    file_put_contents($outPath, json_encode($allPlaces, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

    $summary = [
        'fetched'     => count($allPlaces),
        'api_success' => $apiSuccess,
        'api_errors'  => $apiErrors,
        'file'        => 'data/live_data/' . $cfg['live_file'],
        'refreshed_at'=> date('c'),
    ];
    agentLog("  [{$cat}] Live refresh: {$summary['fetched']} places from {$apiSuccess} regions.");
    return $summary;
}

/**
 * Validate seed entries by reverse-geocoding their lat/lng with Nominatim
 * and verifying the state/country match.  Writes updated seed with
 * `validated_at` and `validation_status` fields.
 */
function validateSeedData(string $cat, array $cfg): array {
    $seedPath = AGENT_SEED_DIR . $cfg['seed_file'];
    if (!file_exists($seedPath)) {
        return ['error' => 'Seed file not found: ' . $cfg['seed_file']];
    }

    $seeds  = json_decode(file_get_contents($seedPath), true) ?? [];
    $valid  = 0;
    $warn   = 0;
    $failed = 0;

    foreach ($seeds as &$entry) {
        $lat = (float)($entry['lat'] ?? 0);
        $lng = (float)($entry['lng'] ?? 0);

        if ($lat === 0.0 && $lng === 0.0) {
            $entry['validation_status'] = 'missing_coordinates';
            $failed++;
            continue;
        }

        $rev = nominatimReverse($lat, $lng);
        // Brief delay to respect Nominatim's 1 req/sec policy
        usleep(1100000);

        if ($rev === null) {
            $entry['validation_status'] = 'geocode_api_error';
            $warn++;
            continue;
        }

        $country = strtoupper($rev['address']['country_code'] ?? '');
        if ($country !== 'US' && $country !== 'CA') {
            $entry['validation_status'] = 'coordinates_outside_expected_country';
            $warn++;
        } else {
            $entry['validation_status'] = 'valid';
            $valid++;
        }

        $entry['validated_at']      = date('c');
        $entry['geocoded_display']  = $rev['display_name'] ?? '';
    }
    unset($entry);

    // Write validated seed back
    file_put_contents($seedPath, json_encode($seeds, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

    $summary = [
        'total'   => count($seeds),
        'valid'   => $valid,
        'warning' => $warn,
        'failed'  => $failed,
        'validated_at' => date('c'),
    ];
    agentLog("  [{$cat}] Seed validation: {$valid} valid / {$warn} warnings / {$failed} failed");
    return $summary;
}

// ══════════════════════════════════════════════════════════════════════════
//  OVERPASS NORMALIZERS — one per category
// ══════════════════════════════════════════════════════════════════════════

function normalizeOverpassGasStation(array $el, array $tags, string $id): ?array {
    $name = $tags['name'] ?? '';
    if ($name === '') return null;

    $fuelTypes = [];
    if (!empty($tags['fuel:diesel']))  $fuelTypes[] = 'diesel';
    if (!empty($tags['fuel:octane_87'])) $fuelTypes[] = 'regular';
    if (!empty($tags['fuel:octane_91']) || !empty($tags['fuel:octane_95'])) $fuelTypes[] = 'premium';
    if (!empty($tags['fuel:e85']))     $fuelTypes[] = 'e85';
    if (!empty($tags['fuel:lpg']))     $fuelTypes[] = 'lpg';
    if (empty($fuelTypes))             $fuelTypes = ['regular', 'diesel'];

    $amenities = [];
    if (!empty($tags['shop']) && $tags['shop'] === 'convenience') $amenities[] = 'convenience_store';
    if (!empty($tags['toilets']))                                  $amenities[] = 'restrooms';
    if (!empty($tags['car_wash']) && $tags['car_wash'] !== 'no')  $amenities[] = 'car_wash';
    if (!empty($tags['atm']) && $tags['atm'] !== 'no')            $amenities[] = 'atm';
    if (!empty($tags['hgv']) && $tags['hgv'] !== 'no')            $amenities[] = 'truck_accessible';
    if (!empty($tags['hgv:lanes']) && (int)$tags['hgv:lanes'] > 0) $amenities[] = 'truck_accessible';

    return buildOsmPlace($id, $name, $el, $tags, [
        'brand'       => $tags['brand'] ?? '',
        'fuel_types'  => $fuelTypes,
        'amenities'   => array_values(array_unique($amenities)),
        'open_247'    => isset($tags['opening_hours']) && str_contains(strtolower($tags['opening_hours']), '24/7'),
        'truck_lanes' => isset($tags['hgv:lanes']) ? (int)$tags['hgv:lanes'] : null,
    ]);
}

function normalizeOverpassHotel(array $el, array $tags, string $id): ?array {
    $name = $tags['name'] ?? '';
    if ($name === '') return null;

    $stars = isset($tags['stars']) ? (int)$tags['stars'] : null;
    $amenities = [];
    if (!empty($tags['internet_access'])) $amenities[] = 'wifi';
    if (!empty($tags['parking']))         $amenities[] = 'parking';
    if (!empty($tags['swimming_pool']))   $amenities[] = 'pool';
    if (!empty($tags['sauna']))           $amenities[] = 'spa';
    if (!empty($tags['restaurant']))      $amenities[] = 'restaurant';

    return buildOsmPlace($id, $name, $el, $tags, [
        'brand'       => $tags['brand'] ?? '',
        'chain'       => $tags['brand'] ?? '',
        'star_rating' => $stars,
        'amenities'   => array_values(array_unique($amenities)),
        'booking_url' => $tags['website'] ?? '',
        'check_in'    => '3:00 PM',
        'check_out'   => '11:00 AM',
    ]);
}

function normalizeOverpassRestaurant(array $el, array $tags, string $id): ?array {
    $name = $tags['name'] ?? '';
    if ($name === '') return null;

    $trucker = !empty($tags['hgv']) && $tags['hgv'] !== 'no';

    return buildOsmPlace($id, $name, $el, $tags, [
        'cuisine'          => $tags['cuisine'] ?? '',
        'hours'            => $tags['opening_hours'] ?? '',
        'trucker_friendly' => $trucker,
        'parking'          => $tags['parking'] ?? '',
        'rating'           => null,
    ]);
}

function normalizeOverpassCarrier(array $el, array $tags, string $id): ?array {
    $name = $tags['name'] ?? '';
    if ($name === '') return null;

    return buildOsmPlace($id, $name, $el, $tags, [
        'carrier'      => $tags['name'] ?? '',
        'type'         => $tags['office'] ?? $tags['amenity'] ?? 'logistics_office',
        'services'     => [],
        'contact_type' => 'office',
        'website'      => $tags['website'] ?? '',
    ]);
}

function normalizeOverpassInsurance(array $el, array $tags, string $id): ?array {
    $name = $tags['name'] ?? '';
    if ($name === '') return null;

    return buildOsmPlace($id, $name, $el, $tags, [
        'specialties'     => ['commercial_insurance'],
        'coverage_types'  => [],
        'instant_quote'   => false,
        'website'         => $tags['website'] ?? '',
        'am_best_rating'  => null,
    ]);
}

/**
 * Shared helper to build a normalized place from an Overpass node.
 */
function buildOsmPlace(string $id, string $name, array $el, array $tags, array $extra): array {
    $addrParts = [];
    if (!empty($tags['addr:housenumber'])) $addrParts[] = $tags['addr:housenumber'];
    if (!empty($tags['addr:street']))      $addrParts[] = $tags['addr:street'];
    $address = implode(' ', $addrParts);

    return array_merge([
        'id'      => $id,
        'name'    => $name,
        'address' => $address,
        'city'    => $tags['addr:city']       ?? '',
        'state'   => $tags['addr:state']      ?? '',
        'lat'     => (float)$el['lat'],
        'lng'     => (float)$el['lon'],
        'phone'   => $tags['phone'] ?? $tags['contact:phone'] ?? '',
        'source'  => 'openstreetmap',
        'osm_id'  => $el['id'],
        'fetched_at' => date('c'),
    ], $extra);
}

// ══════════════════════════════════════════════════════════════════════════
//  HTTP HELPERS
// ══════════════════════════════════════════════════════════════════════════

function overpassFetch(string $query): ?string {
    $ctx = stream_context_create(['http' => [
        'method'  => 'POST',
        'header'  => "Content-Type: application/x-www-form-urlencoded\r\nUser-Agent: Fastrux-DataAgent/1.0\r\n",
        'content' => 'data=' . urlencode($query),
        'timeout' => AGENT_HTTP_TIMEOUT,
        'ignore_errors' => true,
    ]]);

    $result = @file_get_contents(OVERPASS_API_URL, false, $ctx);
    return ($result !== false) ? $result : null;
}

function nominatimReverse(float $lat, float $lng): ?array {
    $url = NOMINATIM_API_URL . '/reverse?' . http_build_query([
        'lat'    => $lat,
        'lon'    => $lng,
        'format' => 'json',
        'zoom'   => 10,
    ]);

    $ctx = stream_context_create(['http' => [
        'timeout' => AGENT_HTTP_TIMEOUT,
        'header'  => "User-Agent: Fastrux-DataAgent/1.0\r\n",
    ]]);

    $raw = @file_get_contents($url, false, $ctx);
    if ($raw === false) return null;
    $data = json_decode($raw, true);
    return is_array($data) ? $data : null;
}

// ══════════════════════════════════════════════════════════════════════════
//  LOGGING & RESPONSE
// ══════════════════════════════════════════════════════════════════════════

function appendValidationLog(string $action, string $category, array $results): void {
    $logFile = AGENT_LOG_FILE;
    $logs = [];
    if (file_exists($logFile)) {
        $existing = json_decode(file_get_contents($logFile), true);
        $logs = is_array($existing) ? $existing : [];
    }

    $logs[] = [
        'run_at'   => date('c'),
        'action'   => $action,
        'category' => $category,
        'results'  => $results,
    ];

    // Keep last 100 log entries
    if (count($logs) > 100) {
        $logs = array_slice($logs, -100);
    }

    file_put_contents($logFile, json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function agentLog(string $msg): void {
    if (IS_CLI) {
        echo date('[Y-m-d H:i:s]') . ' ' . $msg . PHP_EOL;
    } else {
        error_log('[Fastrux-Agent] ' . $msg);
    }
}

function agentClean(string $s): string {
    return htmlspecialchars(strip_tags(trim($s)), ENT_QUOTES, 'UTF-8');
}

function agentRespond(bool $ok, string $msg, array $extra = []): void {
    if (IS_CLI) {
        if (!$ok) {
            fwrite(STDERR, 'ERROR: ' . $msg . PHP_EOL);
            exit(1);
        }
        echo json_encode(array_merge(['success' => $ok, 'message' => $msg], $extra), JSON_PRETTY_PRINT) . PHP_EOL;
        exit(0);
    }
    echo json_encode(array_merge(['success' => $ok, 'message' => $msg], $extra));
    exit;
}
