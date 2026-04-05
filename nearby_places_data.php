<?php
/**
 * Fastrux — Nearby Places Data API
 *
 * Provides Points of Interest (POI) data for the Maps page.
 * Sources: local JSON seed data + live Overpass/OSM cache + user-submitted marketplace listings.
 *
 * GET  ?action=nearby&lat=XX&lng=YY[&radius=50][&category=all|gas_station|hotel|restaurant|library|movie_theater|tms_terminal|carrier|insurance]
 * GET  ?action=categories         — returns list of available categories with counts
 * GET  ?action=get_place&id=XXX   — returns a single place by ID
 */

// Suppress PHP notices/warnings that would corrupt the JSON output
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);
ob_start();  // Buffer any accidental output

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

define('NP_DATA_DIR', __DIR__ . '/data/');
define('NP_SEED_DIR', __DIR__ . '/seed_data/');
define('NP_LIVE_DIR', __DIR__ . '/data/live_data/');

// Live-data cache TTL: 24 hours
define('NP_LIVE_TTL', 86400);

// ── Helpers ───────────────────────────────────────────────

function npClean(string $value): string {
    return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
}

function npRespond(bool $success, string $message, array $extra = []): void {
    ob_clean();  // Discard any accidental output before our JSON
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit;
}

function npLoadJson(string $filename): array {
    $path = NP_DATA_DIR . $filename;
    if (!file_exists($path)) return [];
    $data = json_decode(file_get_contents($path), true);
    return is_array($data) ? $data : [];
}

function npLoadSeed(string $filename): array {
    $path = NP_SEED_DIR . $filename;
    if (!file_exists($path)) return [];
    $data = json_decode(file_get_contents($path), true);
    return is_array($data) ? $data : [];
}

/**
 * Load cached live Overpass data for a category.
 * Returns [] if the file does not exist or is older than NP_LIVE_TTL.
 */
function npLoadLive(string $filename): array {
    $path = NP_LIVE_DIR . $filename;
    if (!file_exists($path)) return [];
    if ((time() - filemtime($path)) > NP_LIVE_TTL) return []; // stale
    $data = json_decode(file_get_contents($path), true);
    return is_array($data) ? $data : [];
}

/**
 * Haversine distance in miles between two lat/lng points.
 */
function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float {
    $earthRadius = 3958.8; // miles
    $dLat = deg2rad($lat2 - $lat1);
    $dLng = deg2rad($lng2 - $lng1);
    $a = sin($dLat / 2) ** 2
       + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return $earthRadius * $c;
}

/**
 * Normalize a place record to a common schema for the map.
 */
function normalizePlace(array $p, string $category, float $distance): array {
    return [
        'id'       => $p['id']      ?? '',
        'name'     => $p['name']    ?? '',
        'address'  => $p['address'] ?? '',
        'city'     => $p['city']    ?? '',
        'state'    => $p['state']   ?? '',
        'lat'      => (float)($p['lat'] ?? 0),
        'lng'      => (float)($p['lng'] ?? 0),
        'phone'    => $p['phone']   ?? '',
        'category' => $category,
        'distance' => round($distance, 1),
        'meta'     => buildMeta($p, $category),
    ];
}

/**
 * Build a small metadata block for popup display, per category.
 */
function buildMeta(array $p, string $category): array {
    switch ($category) {
        case 'gas_station':
            return [
                'brand'        => $p['brand']        ?? ($p['company_name'] ?? ''),
                'fuel_types'   => $p['fuel_types']   ?? [],
                'amenities'    => $p['amenities']    ?? [],
                'diesel_price' => isset($p['diesel_price']) ? '$' . number_format((float)$p['diesel_price'], 2) : null,
                'truck_lanes'  => $p['truck_lanes']  ?? null,
                'open_247'     => $p['open_247']     ?? false,
            ];
        case 'hotel':
            return [
                'brand'       => $p['brand']       ?? ($p['hotel_name'] ?? ''),
                'chain'       => $p['chain']       ?? '',
                'star_rating' => $p['star_rating'] ?? null,
                'price_range' => $p['price_range'] ?? null,
                'amenities'   => $p['amenities']   ?? [],
                'check_in'    => $p['check_in']    ?? '',
                'check_out'   => $p['check_out']   ?? '',
                'booking_url' => $p['booking_url'] ?? ($p['booking_website'] ?? ''),
            ];
        case 'restaurant':
            return [
                'cuisine'         => $p['cuisine']         ?? '',
                'hours'           => $p['hours']           ?? '',
                'trucker_friendly'=> $p['trucker_friendly'] ?? false,
                'parking'         => $p['parking']         ?? '',
                'rating'          => $p['rating']          ?? null,
            ];
        case 'library':
            return [
                'hours'   => $p['hours']   ?? '',
                'wifi'    => $p['wifi']    ?? false,
                'parking' => $p['parking'] ?? '',
                'website' => $p['website'] ?? '',
            ];
        case 'movie_theater':
            return [
                'screens'       => $p['screens']       ?? null,
                'parking'       => $p['parking']       ?? '',
                'accessibility' => $p['accessibility'] ?? false,
                'website'       => $p['website']       ?? '',
            ];
        case 'tms_terminal':
            return [
                'carrier'    => $p['carrier']    ?? '',
                'type'       => $p['type']       ?? '',
                'services'   => $p['services']   ?? [],
                'dock_doors' => $p['dock_doors'] ?? null,
                'open_247'   => $p['open_247']   ?? false,
            ];
        case 'carrier':
            return [
                'dot_number'   => $p['dot_number']   ?? '',
                'mc_number'    => $p['mc_number']    ?? '',
                'services'     => $p['services']     ?? [],
                'cargo_types'  => $p['cargo_types']  ?? [],
                'fleet_size'   => $p['fleet_size']   ?? null,
                'contact_type' => $p['contact_type'] ?? '',
                'website'      => $p['website']      ?? '',
            ];
        case 'insurance':
            return [
                'specialties'    => $p['specialties']    ?? [],
                'coverage_types' => $p['coverage_types'] ?? [],
                'min_coverage'   => $p['min_coverage']   ?? '',
                'max_coverage'   => $p['max_coverage']   ?? '',
                'instant_quote'  => $p['instant_quote']  ?? false,
                'am_best_rating' => $p['am_best_rating'] ?? '',
                'website'        => $p['website']        ?? '',
            ];
        default:
            return [];
    }
}

// ── Router ─────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    npRespond(false, 'Method not allowed.');
}

$action = npClean($_GET['action'] ?? '');

switch ($action) {
    case 'nearby':
        handleNearby();
        break;
    case 'categories':
        handleCategories();
        break;
    case 'get_place':
        handleGetPlace();
        break;
    case 'geocode':
        handleGeocode();
        break;
    case 'reverse_geocode':
        handleReverseGeocode();
        break;
    case 'ip_location':
        handleIpLocation();
        break;
    default:
        npRespond(false, 'Unknown action.');
}

// ══════════════════════════════════════════════════════════
//  HANDLERS
// ══════════════════════════════════════════════════════════

function handleNearby(): void {
    $lat      = filter_var($_GET['lat']    ?? null, FILTER_VALIDATE_FLOAT);
    $lng      = filter_var($_GET['lng']    ?? null, FILTER_VALIDATE_FLOAT);
    $rawRadius = filter_var($_GET['radius'] ?? 50, FILTER_VALIDATE_FLOAT);
    $radius    = ($rawRadius === false || $rawRadius <= 0) ? 50.0 : min($rawRadius, 500.0);
    $category = npClean($_GET['category'] ?? 'all');

    if ($lat === false || $lng === false || $lat === null || $lng === null) {
        npRespond(false, 'Valid lat and lng parameters are required.');
    }

    $allPlaces = [];

    $categoryMap = [
        'gas_station'   => ['gas_stations_seed.json',     'gas_station',   'gas_station.json'],
        'hotel'         => ['hotels_seed.json',            'hotel',         'hotel.json'],
        'restaurant'    => ['restaurants.json',            'restaurant',    'restaurant.json'],
        'library'       => ['libraries.json',              'library',       null],
        'movie_theater' => ['movie_theaters.json',         'movie_theater', null],
        'tms_terminal'  => ['tms_terminals.json',          'tms_terminal',  null],
        'carrier'       => ['carriers.json',               'carrier',       'carrier.json'],
        'insurance'     => ['insurance_companies.json',    'insurance',     'insurance.json'],
    ];

    $categoriesToLoad = ($category === 'all')
        ? array_keys($categoryMap)
        : (isset($categoryMap[$category]) ? [$category] : []);

    if (empty($categoriesToLoad)) {
        npRespond(false, 'Invalid category.');
    }

    foreach ($categoriesToLoad as $cat) {
        [$file, $catLabel, $liveFile] = $categoryMap[$cat];

        // Load seed entries
        $places = npLoadSeed($file);

        // Merge live Overpass data when available (for supported categories)
        $liveSeenOsmIds = [];
        if ($liveFile !== null) {
            $livePlaces = npLoadLive($liveFile);
            foreach ($livePlaces as $lp) {
                $pLat = (float)($lp['lat'] ?? 0);
                $pLng = (float)($lp['lng'] ?? 0);
                if ($pLat === 0.0 && $pLng === 0.0) continue;
                $dist = haversine($lat, $lng, $pLat, $pLng);
                if ($dist <= $radius) {
                    $allPlaces[] = normalizePlace($lp, $catLabel, $dist);
                    if (!empty($lp['osm_id'])) {
                        $liveSeenOsmIds[$lp['osm_id']] = true;
                    }
                }
            }
        }

        // Add seed entries (always included — they are curated/validated)
        foreach ($places as $place) {
            $pLat = (float)($place['lat'] ?? 0);
            $pLng = (float)($place['lng'] ?? 0);
            if ($pLat === 0.0 && $pLng === 0.0) continue;

            $dist = haversine($lat, $lng, $pLat, $pLng);
            if ($dist <= $radius) {
                $allPlaces[] = normalizePlace($place, $catLabel, $dist);
            }
        }

        // Also merge user-submitted marketplace listings for gas stations & hotels
        if ($cat === 'gas_station') {
            $mktListings = npLoadJson('gas_station_listings.json');
            foreach ($mktListings as $listing) {
                if (($listing['status'] ?? '') !== 'active') continue;
                $pLat = (float)($listing['lat'] ?? 0);
                $pLng = (float)($listing['lng'] ?? 0);
                if ($pLat === 0.0 && $pLng === 0.0) continue;
                $dist = haversine($lat, $lng, $pLat, $pLng);
                if ($dist <= $radius) {
                    // Map marketplace fields to our schema
                    $listing['brand']      = $listing['company_name'] ?? '';
                    $listing['fuel_types'] = $listing['fuel_types']   ?? [];
                    $listing['amenities']  = $listing['amenities']    ?? [];
                    $allPlaces[] = normalizePlace($listing, 'gas_station', $dist);
                }
            }
        }
        if ($cat === 'hotel') {
            $mktListings = npLoadJson('hotel_listings.json');
            foreach ($mktListings as $listing) {
                if (($listing['status'] ?? '') !== 'active') continue;
                $pLat = (float)($listing['lat'] ?? 0);
                $pLng = (float)($listing['lng'] ?? 0);
                if ($pLat === 0.0 && $pLng === 0.0) continue;
                $dist = haversine($lat, $lng, $pLat, $pLng);
                if ($dist <= $radius) {
                    $listing['brand']   = $listing['hotel_name']   ?? '';
                    $listing['amenities'] = $listing['amenities']  ?? [];
                    $allPlaces[] = normalizePlace($listing, 'hotel', $dist);
                }
            }
        }
    }

    // Sort by distance
    usort($allPlaces, fn($a, $b) => $a['distance'] <=> $b['distance']);

    // Group by category for response
    $grouped = [];
    foreach ($allPlaces as $place) {
        $grouped[$place['category']][] = $place;
    }

    npRespond(true, 'OK', [
        'count'    => count($allPlaces),
        'places'   => $allPlaces,
        'grouped'  => $grouped,
        'center'   => ['lat' => $lat, 'lng' => $lng],
        'radius'   => $radius,
        'category' => $category,
    ]);
}

function handleCategories(): void {
    $seedCounts = [
        'gas_station'   => count(npLoadSeed('gas_stations_seed.json')),
        'hotel'         => count(npLoadSeed('hotels_seed.json')),
        'restaurant'    => count(npLoadSeed('restaurants.json')),
        'library'       => count(npLoadSeed('libraries.json')),
        'movie_theater' => count(npLoadSeed('movie_theaters.json')),
        'tms_terminal'  => count(npLoadSeed('tms_terminals.json')),
        'carrier'       => count(npLoadSeed('carriers.json')),
        'insurance'     => count(npLoadSeed('insurance_companies.json')),
    ];

    // Add live data counts where available
    $liveCounts = [
        'gas_station' => count(npLoadLive('gas_station.json')),
        'hotel'       => count(npLoadLive('hotel.json')),
        'restaurant'  => count(npLoadLive('restaurant.json')),
        'carrier'     => count(npLoadLive('carrier.json')),
        'insurance'   => count(npLoadLive('insurance.json')),
    ];

    $totalCount = function (string $cat) use ($seedCounts, $liveCounts): int {
        $seed = $seedCounts[$cat] ?? 0;
        $live = $liveCounts[$cat] ?? 0;
        return $seed + $live;
    };

    $categories = [
        ['id' => 'gas_station',   'label' => 'Gas Stations',        'icon' => '⛽', 'count' => $totalCount('gas_station'),   'color' => '#f59e0b'],
        ['id' => 'hotel',         'label' => 'Hotels',               'icon' => '🏨', 'count' => $totalCount('hotel'),         'color' => '#8b5cf6'],
        ['id' => 'restaurant',    'label' => 'Restaurants',          'icon' => '🍽️', 'count' => $totalCount('restaurant'),    'color' => '#ef4444'],
        ['id' => 'carrier',       'label' => 'Carriers',             'icon' => '🚛', 'count' => $totalCount('carrier'),       'color' => '#0ea5e9'],
        ['id' => 'insurance',     'label' => 'Insurance Companies',  'icon' => '🛡️', 'count' => $totalCount('insurance'),     'color' => '#14b8a6'],
        ['id' => 'library',       'label' => 'Libraries',            'icon' => '📚', 'count' => $seedCounts['library'],       'color' => '#3b82f6'],
        ['id' => 'movie_theater', 'label' => 'Movie Theaters',       'icon' => '🎬', 'count' => $seedCounts['movie_theater'], 'color' => '#ec4899'],
        ['id' => 'tms_terminal',  'label' => 'TMS / Freight Hubs',   'icon' => '🏭', 'count' => $seedCounts['tms_terminal'],  'color' => '#10b981'],
    ];

    npRespond(true, 'OK', ['categories' => $categories]);
}

function handleGetPlace(): void {
    $id = npClean($_GET['id'] ?? '');
    if ($id === '') {
        npRespond(false, 'Place ID is required.');
    }

    $fileMap = [
        'GAS' => ['gas_stations_seed.json',     'gas_station'],
        'HTL' => ['hotels_seed.json',            'hotel'],
        'RST' => ['restaurants.json',            'restaurant'],
        'LIB' => ['libraries.json',              'library'],
        'THR' => ['movie_theaters.json',         'movie_theater'],
        'TMS' => ['tms_terminals.json',          'tms_terminal'],
        'CAR' => ['carriers.json',               'carrier'],
        'INS' => ['insurance_companies.json',    'insurance'],
    ];

    $prefix = strtoupper(substr($id, 0, 3));

    // Handle OSM live-data IDs (e.g. "OSM-gas_station-12345")
    if ($prefix === 'OSM') {
        $liveFileMap = [
            'gas_station' => 'gas_station.json',
            'hotel'       => 'hotel.json',
            'restaurant'  => 'restaurant.json',
            'carrier'     => 'carrier.json',
            'insurance'   => 'insurance.json',
        ];
        foreach ($liveFileMap as $catKey => $liveFile) {
            $livePlaces = npLoadLive($liveFile);
            foreach ($livePlaces as $place) {
                if (($place['id'] ?? '') === $id) {
                    npRespond(true, 'OK', ['place' => normalizePlace($place, $catKey, 0)]);
                }
            }
        }
        npRespond(false, 'Place not found.');
    }

    if (!isset($fileMap[$prefix])) {
        npRespond(false, 'Place not found.');
    }

    [$file, $category] = $fileMap[$prefix];
    $places = npLoadSeed($file);
    foreach ($places as $place) {
        if (($place['id'] ?? '') === $id) {
            npRespond(true, 'OK', ['place' => normalizePlace($place, $category, 0)]);
        }
    }

    npRespond(false, 'Place not found.');
}

// ── Geocoding / Nominatim proxy ─────────────────────────────

/** Shared HTTP stream context for external API calls. */
function npHttpContext(): mixed {
    return stream_context_create(['http' => [
        'timeout' => 5,
        'header'  => "User-Agent: Fastrux-Maps/1.0\r\n",
    ]]);
}

/**
 * Forward geocode: ?action=geocode&q=query[&limit=5]
 * Proxies to Nominatim to avoid browser CORS / user-agent issues.
 */
function handleGeocode(): void {
    $q     = npClean($_GET['q'] ?? '');
    $limit = max(1, min(10, (int)($_GET['limit'] ?? 5)));
    if ($q === '') {
        npRespond(false, 'Query parameter q is required.');
    }

    $url = 'https://nominatim.openstreetmap.org/search?'
         . http_build_query([
             'q'              => $q,
             'format'         => 'json',
             'addressdetails' => 1,
             'limit'          => $limit,
         ]);

    $raw = @file_get_contents($url, false, npHttpContext());
    if ($raw === false) {
        npRespond(false, 'Geocoding service unavailable.');
    }
    $results = json_decode($raw, true);
    if (!is_array($results)) {
        npRespond(false, 'Invalid geocoding response.');
    }

    $places = array_map(function ($r) {
        return [
            'lat'         => (float)$r['lat'],
            'lng'         => (float)$r['lon'],
            'display'     => $r['display_name'] ?? '',
            'type'        => $r['type']        ?? '',
            'address'     => $r['address']     ?? [],
        ];
    }, $results);

    npRespond(true, 'OK', ['results' => $places]);
}

/**
 * Reverse geocode: ?action=reverse_geocode&lat=XX&lng=YY
 */
function handleReverseGeocode(): void {
    $lat = filter_var($_GET['lat'] ?? null, FILTER_VALIDATE_FLOAT);
    $lng = filter_var($_GET['lng'] ?? null, FILTER_VALIDATE_FLOAT);
    if ($lat === false || $lng === false || $lat === null || $lng === null) {
        npRespond(false, 'Valid lat and lng are required.');
    }

    $url = 'https://nominatim.openstreetmap.org/reverse?'
         . http_build_query([
             'lat'    => $lat,
             'lon'    => $lng,
             'format' => 'json',
             'zoom'   => 17,
         ]);

    $raw = @file_get_contents($url, false, npHttpContext());
    if ($raw === false) {
        npRespond(false, 'Reverse geocoding service unavailable.');
    }
    $result = json_decode($raw, true);
    if (!is_array($result)) {
        npRespond(false, 'Invalid reverse geocode response.');
    }

    npRespond(true, 'OK', [
        'display' => $result['display_name'] ?? '',
        'address' => $result['address']      ?? [],
        'lat'     => $lat,
        'lng'     => $lng,
    ]);
}

/**
 * IP-based location fallback: ?action=ip_location
 */
function handleIpLocation(): void {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    // Use ipapi.co free tier for IP geolocation
    $url = 'https://ipapi.co/' . urlencode($ip) . '/json/';
    $raw = @file_get_contents($url, false, npHttpContext());
    if ($raw !== false) {
        $data = json_decode($raw, true);
        if (is_array($data) && isset($data['latitude'], $data['longitude'])) {
            npRespond(true, 'OK', [
                'lat'     => (float)$data['latitude'],
                'lng'     => (float)$data['longitude'],
                'city'    => $data['city']         ?? '',
                'region'  => $data['region']       ?? '',
                'country' => $data['country_name'] ?? '',
                'source'  => 'ip',
            ]);
        }
    }
    // Fallback to geographic center of USA
    npRespond(true, 'OK', [
        'lat'    => 39.5,
        'lng'    => -98.35,
        'city'   => 'United States',
        'source' => 'default',
    ]);
}
