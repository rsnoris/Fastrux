<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <link rel="icon" href="favicon.svg" type="image/svg+xml" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Live Map & Nearby Places — Fastrux</title>
  <script>
    // ── Early auth guard — redirect to login before any content renders ──
    (function () {
      var user = null;
      try { user = JSON.parse(localStorage.getItem('fx_user')); } catch (e) {}
      var allowed = ['shipper', 'customer', 'driver', 'owner_operator', 'corporate_staff', 'admin', 'super_admin'];
      if (!user || !user.id || allowed.indexOf(user.role) === -1) {
        window.location.replace('login?redirect=' + encodeURIComponent(window.location.pathname));
      }
    })();
  </script>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="shared.css" />
  <!-- Leaflet map -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV/XN/WLcE=" crossorigin=""></script>
  <script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>

  <style>
    body { background: var(--muted); }

    /* ── Dashboard header ── */
    .dash-header {
      background: var(--card);
      border-bottom: 1px solid var(--border);
      position: sticky;
      top: 0;
      z-index: 200;
    }
    .dash-header-inner {
      display: flex;
      align-items: center;
      justify-content: space-between;
      height: 64px;
    }
    .dash-brand {
      display: flex; align-items: center; gap: 10px;
      font-size: 18px; font-weight: 800; color: var(--primary);
      text-decoration: none;
    }
    .dash-brand span { color: var(--foreground); font-weight: 400; font-size: 14px; }

    /* ── Stats strip ── */
    .stats-strip {
      display: flex;
      gap: 16px;
      margin-bottom: 20px;
      flex-wrap: wrap;
    }
    .stat-pill {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      padding: 12px 20px;
      display: flex;
      align-items: center;
      gap: 10px;
      flex: 1;
      min-width: 140px;
    }
    .stat-pill-icon {
      width: 36px; height: 36px; min-width: 36px;
      border-radius: var(--radius-md);
      display: flex; align-items: center; justify-content: center;
      font-size: 18px;
    }
    .stat-pill-icon.green  { background: #e6f9ee; color: var(--success); }
    .stat-pill-icon.amber  { background: #fff7e6; color: #d97706; }
    .stat-pill-icon.gray   { background: var(--secondary); color: var(--muted-foreground); }
    .stat-pill-icon.blue   { background: var(--secondary); color: var(--primary); }
    .stat-pill-label { font-size: 12px; color: var(--muted-foreground); font-weight: 500; }
    .stat-pill-value { font-size: 22px; font-weight: 800; line-height: 1; }

    /* ── Layout ── */
    .map-layout {
      display: grid;
      grid-template-columns: 320px 1fr;
      gap: 16px;
      height: calc(100vh - 180px);
      min-height: 480px;
    }
    @media (max-width: 768px) {
      .map-layout { grid-template-columns: 1fr; height: auto; }
    }

    /* ── Driver list panel ── */
    .panel {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius-xl);
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }
    .panel-header {
      padding: 16px;
      border-bottom: 1px solid var(--border);
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      flex-shrink: 0;
    }
    .panel-title {
      display: flex; align-items: center; gap: 8px;
      font-size: 15px; font-weight: 700;
    }
    .driver-list {
      flex: 1;
      overflow-y: auto;
      padding: 12px;
      display: flex;
      flex-direction: column;
      gap: 8px;
    }
    .driver-item {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 12px 14px;
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      background: var(--card);
      cursor: pointer;
      transition: border-color .2s, box-shadow .2s;
    }
    .driver-item:hover,
    .driver-item.active { border-color: var(--primary); box-shadow: 0 0 0 2px rgba(11,111,255,.12); }
    .driver-avatar {
      width: 38px; height: 38px;
      border-radius: 50%;
      background: var(--secondary);
      display: flex; align-items: center; justify-content: center;
      font-size: 14px; font-weight: 700; color: var(--primary);
      flex-shrink: 0;
    }
    .driver-info { flex: 1; min-width: 0; }
    .driver-name { font-size: 14px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .driver-meta { font-size: 12px; color: var(--muted-foreground); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .status-dot {
      width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0;
    }
    .status-dot.available { background: var(--success); }
    .status-dot.busy      { background: #d97706; }
    .status-dot.offline   { background: var(--muted-foreground); }

    /* ── Map ── */
    #map {
      height: 100%;
      min-height: 400px;
      border-radius: var(--radius-xl);
      z-index: 1;
    }

    /* ── Search bar ── */
    .search-wrap {
      padding: 10px 12px;
      border-bottom: 1px solid var(--border);
      flex-shrink: 0;
    }
    .search-wrap input {
      width: 100%;
      padding: 8px 12px 8px 32px;
      border: 1.5px solid var(--border);
      border-radius: var(--radius-md);
      font-family: var(--font-family-body);
      font-size: 13px;
      background: var(--muted);
      color: var(--foreground);
      outline: none;
      box-sizing: border-box;
      transition: border-color .2s;
    }
    .search-wrap input:focus { border-color: var(--primary); }
    .search-wrap { position: relative; }
    .search-wrap iconify-icon {
      position: absolute;
      left: 22px; top: 50%; transform: translateY(-50%);
      color: var(--muted-foreground); font-size: 14px;
      pointer-events: none;
    }

    /* ── Empty/loading states ── */
    .empty-state {
      display: flex; flex-direction: column; align-items: center; justify-content: center;
      padding: 40px 16px; gap: 10px; color: var(--muted-foreground);
      font-size: 14px; text-align: center;
    }
    .empty-state iconify-icon { font-size: 32px; }

    /* ── Popup ── */
    .leaflet-popup-content { min-width: 180px; }
    .popup-name { font-weight: 700; font-size: 14px; margin-bottom: 4px; }
    .popup-reg  { font-size: 12px; background: var(--secondary); padding: 2px 7px; border-radius: 4px; font-weight: 600; }
    .popup-updated { font-size: 12px; color: #6b7280; margin-top: 6px; }

    /* ── Status filter ── */
    .status-filter {
      padding: 6px 10px;
      border: 1.5px solid var(--border);
      border-radius: var(--radius-md);
      font-size: 13px;
      background: var(--input);
      cursor: pointer;
    }

    /* ── Auto-refresh indicator ── */
    .refresh-indicator {
      display: flex; align-items: center; gap: 6px;
      font-size: 12px; color: var(--muted-foreground);
    }
    .pulse-dot {
      width: 8px; height: 8px; border-radius: 50%;
      background: var(--success);
      animation: pulse-anim 2s ease-in-out infinite;
    }
    @keyframes pulse-anim {
      0%, 100% { opacity: 1; transform: scale(1); }
      50%       { opacity: .5; transform: scale(.85); }
    }

    /* ── Toast ── */
    .toast {
      position: fixed; bottom: 24px; right: 24px;
      background: var(--foreground); color: var(--background);
      padding: 12px 20px; border-radius: var(--radius-lg);
      font-size: 14px; font-weight: 500;
      transform: translateY(80px); opacity: 0;
      transition: transform .3s ease, opacity .3s ease;
      z-index: 9999;
    }
    .toast.show { transform: translateY(0); opacity: 1; }

    @keyframes spin { to { transform: rotate(360deg); } }

    /* ── My location button (driver only) ── */
    .btn-locate {
      display: flex; align-items: center; gap: 6px;
      padding: 8px 14px; font-size: 13px;
    }

    /* ── Panel tabs ── */
    .panel-tabs {
      display: flex; border-bottom: 1px solid var(--border); flex-shrink: 0;
    }
    .panel-tab {
      flex: 1; padding: 10px 6px; font-size: 12px; font-weight: 600;
      text-align: center; cursor: pointer; border: none; background: none;
      color: var(--muted-foreground); border-bottom: 2px solid transparent;
      transition: color .15s, border-color .15s;
    }
    .panel-tab.active { color: var(--primary); border-bottom-color: var(--primary); }
    .panel-tab:hover:not(.active) { color: var(--foreground); }

    /* ── Nearby controls bar ── */
    .nearby-controls {
      padding: 8px 12px; border-bottom: 1px solid var(--border); flex-shrink: 0;
      display: flex; gap: 8px; align-items: center; flex-wrap: wrap;
    }
    .nearby-controls select, .nearby-controls input[type=number] {
      flex: 1; min-width: 80px; padding: 6px 10px;
      border: 1.5px solid var(--border); border-radius: var(--radius-md);
      font-size: 12px; background: var(--muted); color: var(--foreground);
      outline: none; transition: border-color .2s;
    }
    .nearby-controls select:focus, .nearby-controls input:focus { border-color: var(--primary); }
    .btn-sm {
      padding: 6px 12px; font-size: 12px; border-radius: var(--radius-md);
      border: none; cursor: pointer; font-weight: 600; white-space: nowrap;
      background: var(--primary); color: #fff; transition: opacity .15s;
    }
    .btn-sm:hover { opacity: .85; }
    .btn-sm.outline { background: var(--card); color: var(--primary); border: 1.5px solid var(--primary); }

    /* ── POI list items ── */
    .poi-item {
      display: flex; align-items: flex-start; gap: 10px;
      padding: 10px 12px; border: 1px solid var(--border);
      border-radius: var(--radius-lg); background: var(--card);
      cursor: pointer; transition: border-color .2s, box-shadow .2s;
      font-size: 13px;
    }
    .poi-item:hover { border-color: var(--primary); box-shadow: 0 0 0 2px rgba(11,111,255,.1); }
    .poi-emoji { font-size: 20px; line-height: 1; flex-shrink: 0; margin-top: 2px; }
    .poi-info { flex: 1; min-width: 0; }
    .poi-name { font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .poi-addr { font-size: 11px; color: var(--muted-foreground); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .poi-dist { font-size: 11px; color: var(--primary); font-weight: 600; white-space: nowrap; }
    .poi-badge {
      font-size: 10px; padding: 1px 6px; border-radius: 8px;
      font-weight: 600; white-space: nowrap; flex-shrink: 0; margin-top: 3px;
    }

    /* ── Category layer toggles ── */
    .layer-toggles {
      display: flex; flex-wrap: wrap; gap: 6px;
      padding: 10px 12px; border-bottom: 1px solid var(--border); flex-shrink: 0;
    }
    .layer-btn {
      display: inline-flex; align-items: center; gap: 4px;
      padding: 4px 10px; font-size: 11px; font-weight: 600;
      border-radius: 12px; border: 1.5px solid; cursor: pointer;
      transition: opacity .15s, background .15s;
    }
    .layer-btn.active { opacity: 1; }
    .layer-btn:not(.active) { opacity: .45; background: transparent !important; }

    /* ── Radius info ── */
    .radius-info {
      font-size: 11px; color: var(--muted-foreground);
      padding: 4px 12px; flex-shrink: 0;
    }

    /* ── Location prompt ── */
    .location-prompt {
      display: flex; flex-direction: column; align-items: center;
      padding: 24px 16px; gap: 10px; color: var(--muted-foreground);
      font-size: 13px; text-align: center;
    }
    .location-prompt iconify-icon { font-size: 32px; color: var(--primary); }
  </style>
</head>
<body>

  <!-- ── Dashboard Header ── -->
  <header class="dash-header">
    <div class="container dash-header-inner">
      <a href="index" class="dash-brand">
        <iconify-icon icon="lucide:truck" style="font-size:24px"></iconify-icon>
        Fastrux <span>&nbsp;/ Live Map &amp; Nearby Places</span>
      </a>
      <div style="display:flex;align-items:center;gap:10px;" id="headerActions">
        <!-- Populated by JS based on role -->
      </div>
    </div>
  </header>

  <!-- ── Main ── -->
  <div class="container" style="padding-top:24px;padding-bottom:32px;">

    <!-- Title + refresh -->
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:20px;">
      <div>
        <h1 style="font-size:24px;font-weight:800;margin-bottom:4px;">Live Map &amp; Nearby Places</h1>
        <p style="color:var(--muted-foreground);font-size:14px;">Real-time drivers · Gas stations · Hotels · Restaurants · Libraries · Theaters · TMS hubs</p>
      </div>
      <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
        <div class="refresh-indicator">
          <span class="pulse-dot" id="pulseDot"></span>
          <span id="lastUpdated">Loading…</span>
        </div>
        <button class="btn btn-outline" style="padding:8px 14px;font-size:13px;" onclick="loadData()">
          <iconify-icon icon="lucide:refresh-cw" style="font-size:14px;margin-right:6px"></iconify-icon>Refresh
        </button>
      </div>
    </div>

    <!-- Stats strip -->
    <div class="stats-strip">
      <div class="stat-pill">
        <div class="stat-pill-icon green"><iconify-icon icon="lucide:check-circle-2"></iconify-icon></div>
        <div>
          <div class="stat-pill-label">Available</div>
          <div class="stat-pill-value" id="statAvailable">—</div>
        </div>
      </div>
      <div class="stat-pill">
        <div class="stat-pill-icon amber"><iconify-icon icon="lucide:truck"></iconify-icon></div>
        <div>
          <div class="stat-pill-label">On Trip</div>
          <div class="stat-pill-value" id="statBusy">—</div>
        </div>
      </div>
      <div class="stat-pill">
        <div class="stat-pill-icon gray"><iconify-icon icon="lucide:moon"></iconify-icon></div>
        <div>
          <div class="stat-pill-label">Offline</div>
          <div class="stat-pill-value" id="statOffline">—</div>
        </div>
      </div>
      <div class="stat-pill">
        <div class="stat-pill-icon blue"><iconify-icon icon="lucide:map-pin"></iconify-icon></div>
        <div>
          <div class="stat-pill-label">Location Shared</div>
          <div class="stat-pill-value" id="statWithLocation">—</div>
        </div>
      </div>
    </div>

    <!-- Map + Driver list layout -->
    <div class="map-layout">

      <!-- Left: Tabbed panel (Drivers / Nearby Places) -->
      <div class="panel">
        <!-- Tabs -->
        <div class="panel-tabs">
          <button class="panel-tab active" id="tabDrivers" onclick="switchTab('drivers')">
            🚚 Drivers
          </button>
          <button class="panel-tab" id="tabNearby" onclick="switchTab('nearby')">
            📍 Nearby Places
          </button>
        </div>

        <!-- ── Drivers sub-panel ── -->
        <div id="driversPanel" style="display:flex;flex-direction:column;flex:1;overflow:hidden;">
          <div class="panel-header" style="border-top:none;">
            <div class="panel-title">
              <iconify-icon icon="lucide:users" style="font-size:16px;color:var(--primary)"></iconify-icon>
              Drivers
            </div>
            <select id="statusFilter" class="status-filter" onchange="applyFilters()">
              <option value="">All</option>
              <option value="available">Available</option>
              <option value="busy">On Trip</option>
              <option value="offline">Offline</option>
            </select>
          </div>
          <div class="search-wrap">
            <iconify-icon icon="lucide:search"></iconify-icon>
            <input type="text" id="searchInput" placeholder="Search driver or van reg…" oninput="applyFilters()" />
          </div>
          <div class="driver-list" id="driverList">
            <div class="empty-state">
              <iconify-icon icon="lucide:loader-circle" style="animation:spin 1s linear infinite"></iconify-icon>
              <p>Loading drivers…</p>
            </div>
          </div>
        </div>

        <!-- ── Nearby Places sub-panel ── -->
        <div id="nearbyPanel" style="display:none;flex-direction:column;flex:1;overflow:hidden;">
          <!-- Location & radius controls -->
          <div class="nearby-controls">
            <label for="nearbyRadius" style="font-size:12px;color:var(--muted-foreground);white-space:nowrap;">Radius:</label>
            <input type="number" id="nearbyRadius" value="50" min="1" max="500" style="width:70px;flex:none;" title="Search radius in miles" aria-label="Search radius in miles" />
            <span style="font-size:12px;color:var(--muted-foreground);white-space:nowrap;">mi</span>
            <span style="font-size:12px;color:var(--muted-foreground);white-space:nowrap;">mi</span>
            <select id="nearbyCategory" style="flex:1;">
              <option value="all">All Categories</option>
              <option value="gas_station">⛽ Gas Stations</option>
              <option value="hotel">🏨 Hotels</option>
              <option value="restaurant">🍽️ Restaurants</option>
              <option value="library">📚 Libraries</option>
              <option value="movie_theater">🎬 Theaters</option>
              <option value="tms_terminal">🏭 TMS / Freight</option>
            </select>
          </div>
          <div class="nearby-controls" style="padding-top:0;">
            <button class="btn-sm" onclick="loadNearby(true)" id="btnLocate">
              <iconify-icon icon="lucide:locate" style="font-size:13px;vertical-align:-2px;margin-right:3px"></iconify-icon>Use My Location
            </button>
            <button class="btn-sm outline" onclick="loadNearby(false)" id="btnMapCenter">
              <iconify-icon icon="lucide:map" style="font-size:13px;vertical-align:-2px;margin-right:3px"></iconify-icon>Map Center
            </button>
          </div>
          <div class="radius-info" id="nearbyInfo">Enter a radius and click a button to find nearby places.</div>
          <!-- Layer visibility toggles -->
          <div class="layer-toggles" id="layerToggles"></div>
          <!-- Results list -->
          <div class="driver-list" id="nearbyList">
            <div class="location-prompt">
              <iconify-icon icon="lucide:map-pin"></iconify-icon>
              <p>Click <strong>Use My Location</strong> or <strong>Map Center</strong> to find nearby places.</p>
            </div>
          </div>
        </div>

      </div>

      <!-- Right: Map -->
      <div class="panel" style="overflow:hidden;">
        <div id="map"></div>
      </div>

    </div>
  </div>

  <!-- Toast -->
  <div class="toast" id="toast"></div>

  <script>
  // ── Current user (already validated by the early auth guard in <head>) ──
  var currentUser = null;
  try { currentUser = JSON.parse(localStorage.getItem('fx_user')); } catch (e) {}

  // ── Populate header actions based on role ────────────────
  (function () {
    var role = (currentUser && currentUser.role) || '';
    var actions = document.getElementById('headerActions');
    var html = '';

    if (role === 'driver' || role === 'owner_operator') {
      html += '<a href="driver-location" class="btn btn-outline btn-locate">'
            + '<iconify-icon icon="lucide:map-pin" style="font-size:15px"></iconify-icon>'
            + 'Share My Location</a>';
      html += '<a href="driver-dashboard" class="btn btn-primary" style="padding:8px 16px;font-size:13px;">'
            + '<iconify-icon icon="lucide:layout-dashboard" style="font-size:15px;margin-right:6px"></iconify-icon>'
            + 'My Dashboard</a>';
    } else if (role === 'shipper' || role === 'customer') {
      html += '<a href="shipper-dashboard" class="btn btn-primary" style="padding:8px 16px;font-size:13px;">'
            + '<iconify-icon icon="lucide:layout-dashboard" style="font-size:15px;margin-right:6px"></iconify-icon>'
            + 'My Dashboard</a>';
    } else {
      html += '<a href="admin-dashboard" class="btn btn-primary" style="padding:8px 16px;font-size:13px;">'
            + '<iconify-icon icon="lucide:layout-dashboard" style="font-size:15px;margin-right:6px"></iconify-icon>'
            + 'Admin Dashboard</a>';
    }

    actions.innerHTML = html;
  })();

  // ═══════════════════════════════════════════════════════════
  //  STATE
  // ═══════════════════════════════════════════════════════════
  var allDrivers    = [];
  var filtered      = [];
  var map           = null;
  var driverMarkers = {};
  var refreshTimer  = null;
  var REFRESH_MS    = 30000;

  // POI state
  var poiLayers     = {};   // category -> L.LayerGroup
  var poiVisible    = {};   // category -> bool
  var lastPlaces    = [];   // last fetched places array

  // POI category metadata
  var POI_CATS = {
    gas_station:   { label: 'Gas Stations',     emoji: '⛽', color: '#f59e0b' },
    hotel:         { label: 'Hotels',            emoji: '🏨', color: '#8b5cf6' },
    restaurant:    { label: 'Restaurants',       emoji: '🍽️', color: '#ef4444' },
    library:       { label: 'Libraries',         emoji: '📚', color: '#3b82f6' },
    movie_theater: { label: 'Movie Theaters',    emoji: '🎬', color: '#ec4899' },
    tms_terminal:  { label: 'TMS / Freight',     emoji: '🏭', color: '#10b981' },
  };

  // ═══════════════════════════════════════════════════════════
  //  HELPERS
  // ═══════════════════════════════════════════════════════════
  function esc(s) {
    return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  function timeSince(date) {
    var secs = Math.floor((Date.now() - date.getTime()) / 1000);
    if (secs < 60)  return secs + 's';
    if (secs < 3600) return Math.floor(secs / 60) + 'm';
    return Math.floor(secs / 3600) + 'h';
  }

  var toastTimer = null;
  function showToast(msg) {
    var el = document.getElementById('toast');
    el.textContent = msg;
    el.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(function () { el.classList.remove('show'); }, 3500);
  }

  // ═══════════════════════════════════════════════════════════
  //  MAP INITIALISATION
  // ═══════════════════════════════════════════════════════════
  function initMap() {
    map = L.map('map', { preferCanvas: true }).setView([39.5, -98.35], 4); // USA centre
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
      maxZoom: 18,
    }).addTo(map);
  }

  // ── Marker icons ─────────────────────────────────────────
  function driverIcon(status, isMe) {
    var colours = { available: '#16a34a', busy: '#d97706', offline: '#9ca3af' };
    var c = colours[status] || '#9ca3af';
    var border = isMe ? '#1d4ed8' : '#fff';
    var bw     = isMe ? 3 : 2;
    var emoji  = isMe ? '📍' : '🚚';
    var svg = '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="40" viewBox="0 0 32 40">'
      + '<ellipse cx="16" cy="38" rx="7" ry="2.5" fill="rgba(0,0,0,.15)"/>'
      + '<path d="M16 0 C8 0 2 6.5 2 14.5 C2 24 16 38 16 38 C16 38 30 24 30 14.5 C30 6.5 24 0 16 0Z"'
      + ' fill="' + c + '" stroke="' + border + '" stroke-width="' + bw + '"/>'
      + '<text x="16" y="18" text-anchor="middle" fill="#fff" font-size="14" font-family="sans-serif">' + emoji + '</text>'
      + '</svg>';
    return L.divIcon({
      html: '<div style="transform:translate(-16px,-40px)">' + svg + '</div>',
      iconSize: [0, 0], iconAnchor: [0, 0], popupAnchor: [16, -40],
      className: '',
    });
  }

  // ═══════════════════════════════════════════════════════════
  //  DATA LOADING
  // ═══════════════════════════════════════════════════════════
  async function loadData() {
    try {
      var res  = await fetch('offers_tracking_data.php?action=get_drivers&t=' + Date.now());
      var data = await res.json();
      allDrivers = (data.drivers || []).filter(function (d) { return d.driver_status === 'approved'; });
      applyFilters();
      updateStats();
      renderMarkers();
      document.getElementById('lastUpdated').textContent = 'Updated ' + new Date().toLocaleTimeString();
    } catch (err) {
      showToast('Could not load driver data.');
    }
  }

  // ═══════════════════════════════════════════════════════════
  //  STATS
  // ═══════════════════════════════════════════════════════════
  function updateStats() {
    document.getElementById('statAvailable').textContent   = allDrivers.filter(function (d) { return d.location_status === 'available'; }).length;
    document.getElementById('statBusy').textContent        = allDrivers.filter(function (d) { return d.location_status === 'busy'; }).length;
    document.getElementById('statOffline').textContent     = allDrivers.filter(function (d) { return d.location_status === 'offline' || !d.location_status; }).length;
    document.getElementById('statWithLocation').textContent = allDrivers.filter(function (d) { return d.lat !== null && d.lng !== null; }).length;
  }

  // ═══════════════════════════════════════════════════════════
  //  FILTERS
  // ═══════════════════════════════════════════════════════════
  function applyFilters() {
    var q  = (document.getElementById('searchInput').value || '').toLowerCase().trim();
    var st = document.getElementById('statusFilter').value;
    filtered = allDrivers.filter(function (d) {
      var searchStr = ((d.name || '') + ' ' + (d.van_reg || '')).toLowerCase();
      var matchQ  = !q  || searchStr.includes(q);
      var matchSt = !st || (d.location_status || 'offline') === st;
      return matchQ && matchSt;
    });
    renderDriverList();
  }

  // ═══════════════════════════════════════════════════════════
  //  DRIVER LIST (sidebar)
  // ═══════════════════════════════════════════════════════════
  function renderDriverList() {
    var list = document.getElementById('driverList');
    if (!filtered.length) {
      list.innerHTML = '<div class="empty-state"><iconify-icon icon="lucide:truck"></iconify-icon><p>No drivers match your filters.</p></div>';
      return;
    }
    var html = filtered.map(function (d) {
      var initials = (d.name || '?').split(' ').map(function (w) { return w[0] || ''; }).slice(0, 2).join('').toUpperCase();
      var status   = d.location_status || 'offline';
      var hasLoc   = d.lat !== null && d.lng !== null;
      var locText  = hasLoc ? (d.lat.toFixed(4) + ', ' + d.lng.toFixed(4)) : 'No location';
      var isMe     = currentUser && d.user_id === currentUser.id;
      return '<div class="driver-item" data-id="' + esc(d.id) + '" onclick="focusDriver(\'' + esc(d.id) + '\')">'
        + '<div class="driver-avatar">' + esc(initials) + '</div>'
        + '<div class="driver-info">'
        + '<div class="driver-name">' + esc(d.name || '—') + (isMe ? ' <span style="font-size:11px;color:var(--primary);">(You)</span>' : '') + '</div>'
        + '<div class="driver-meta">' + esc(d.van_reg || '—') + ' · ' + esc((d.van_type || '').replace(/_/g, ' ')) + '</div>'
        + '<div class="driver-meta" style="font-size:11px;">' + (hasLoc ? '📍 ' + locText : '📍 No location shared') + '</div>'
        + '</div>'
        + '<span class="status-dot ' + esc(status) + '" title="' + esc(status) + '"></span>'
        + '</div>';
    }).join('');
    list.innerHTML = html;
  }

  // ── Focus a driver on the map ─────────────────────────────
  function focusDriver(driverId) {
    // Highlight in list
    document.querySelectorAll('.driver-item').forEach(function (el) {
      el.classList.toggle('active', el.dataset.id === driverId);
    });
    // Pan map to driver
    var marker = driverMarkers[driverId];
    if (marker) {
      map.setView(marker.getLatLng(), 12, { animate: true });
      marker.openPopup();
    } else {
      showToast('This driver has not shared their location yet.');
    }
  }

  // ═══════════════════════════════════════════════════════════
  //  MAP MARKERS
  // ═══════════════════════════════════════════════════════════
  function renderMarkers() {
    // Remove old
    Object.values(driverMarkers).forEach(function (m) { map.removeLayer(m); });
    driverMarkers = {};

    allDrivers.forEach(function (d) {
      if (d.lat === null || d.lng === null) return;

      var isMe   = currentUser && d.user_id === currentUser.id;
      var status = d.location_status || 'offline';
      var marker = L.marker([d.lat, d.lng], { icon: driverIcon(status, isMe) });

      var updatedAgo = d.location_updated
        ? timeSince(new Date(d.location_updated.replace(' ', 'T'))) + ' ago'
        : 'unknown';

      marker.bindPopup(
        '<div class="popup-name">' + esc(d.name || '—') + (isMe ? ' <em style="font-size:11px;color:var(--primary);">(You)</em>' : '') + '</div>'
        + '<div style="margin-bottom:6px;">'
        + '<span class="popup-reg">' + esc(d.van_reg || '—') + '</span>'
        + ' &nbsp;·&nbsp; ' + esc((d.van_type || '').replace(/_/g, ' '))
        + '</div>'
        + '<div class="popup-updated">📍 ' + d.lat.toFixed(5) + ', ' + d.lng.toFixed(5) + '</div>'
        + '<div class="popup-updated">🕐 Updated ' + esc(updatedAgo) + '</div>'
        + '<div class="popup-updated" style="margin-top:4px;">Status: <strong>' + esc(status) + '</strong></div>'
      );

      marker.addTo(map);
      driverMarkers[d.id] = marker;
    });
  }

  // ═══════════════════════════════════════════════════════════
  //  TAB SWITCHING
  // ═══════════════════════════════════════════════════════════
  function switchTab(tab) {
    var isDrivers = tab === 'drivers';
    document.getElementById('tabDrivers').classList.toggle('active', isDrivers);
    document.getElementById('tabNearby').classList.toggle('active', !isDrivers);
    document.getElementById('driversPanel').style.display = isDrivers ? 'flex' : 'none';
    document.getElementById('nearbyPanel').style.display  = isDrivers ? 'none' : 'flex';
  }

  // ═══════════════════════════════════════════════════════════
  //  POI ICON
  // ═══════════════════════════════════════════════════════════
  function poiIcon(category) {
    var cat = POI_CATS[category] || { emoji: '📍', color: '#6b7280' };
    var svg = '<svg xmlns="http://www.w3.org/2000/svg" width="28" height="36" viewBox="0 0 28 36">'
      + '<ellipse cx="14" cy="34" rx="6" ry="2" fill="rgba(0,0,0,.15)"/>'
      + '<path d="M14 0 C7 0 1 6 1 13 C1 22 14 34 14 34 C14 34 27 22 27 13 C27 6 21 0 14 0Z"'
      + ' fill="' + cat.color + '" stroke="#fff" stroke-width="2"/>'
      + '<text x="14" y="17" text-anchor="middle" font-size="12" font-family="sans-serif">' + cat.emoji + '</text>'
      + '</svg>';
    return L.divIcon({
      html: '<div style="transform:translate(-14px,-36px)">' + svg + '</div>',
      iconSize: [0, 0], iconAnchor: [0, 0], popupAnchor: [14, -36],
      className: '',
    });
  }

  // ═══════════════════════════════════════════════════════════
  //  NEARBY PLACES — LOAD
  // ═══════════════════════════════════════════════════════════
  function loadNearby(useGeolocation) {
    var rawRadius = parseFloat(document.getElementById('nearbyRadius').value);
    var radius    = (isNaN(rawRadius) || rawRadius <= 0) ? 50 : Math.min(rawRadius, 500);
    var category = document.getElementById('nearbyCategory').value;
    var list     = document.getElementById('nearbyList');
    var info     = document.getElementById('nearbyInfo');

    list.innerHTML = '<div class="empty-state"><iconify-icon icon="lucide:loader-circle" style="animation:spin 1s linear infinite"></iconify-icon><p>Searching nearby places…</p></div>';
    info.textContent = 'Searching…';

    function fetchNearby(lat, lng) {
      var url = 'nearby_places_data.php?action=nearby'
        + '&lat=' + lat.toFixed(6)
        + '&lng=' + lng.toFixed(6)
        + '&radius=' + radius
        + '&category=' + encodeURIComponent(category)
        + '&t=' + Date.now();

      fetch(url)
        .then(function(r) { return r.json(); })
        .then(function(data) {
          if (!data.success) {
            list.innerHTML = '<div class="empty-state"><iconify-icon icon="lucide:alert-circle"></iconify-icon><p>' + esc(data.message) + '</p></div>';
            info.textContent = 'Error loading places.';
            return;
          }
          lastPlaces = data.places || [];
          info.textContent = lastPlaces.length + ' place' + (lastPlaces.length !== 1 ? 's' : '') + ' within ' + radius + ' mi of ' + lat.toFixed(4) + ', ' + lng.toFixed(4);
          renderNearbyList(lastPlaces);
          renderPoiMarkers(lastPlaces, lat, lng, radius);
          renderLayerToggles(data.grouped || {});
        })
        .catch(function(err) {
          list.innerHTML = '<div class="empty-state"><iconify-icon icon="lucide:wifi-off"></iconify-icon><p>Network error loading places.</p></div>';
          info.textContent = 'Network error.';
          showToast('Could not load nearby places.');
        });
    }

    if (useGeolocation) {
      if (!navigator.geolocation) {
        showToast('Geolocation is not supported by your browser.');
        list.innerHTML = '<div class="empty-state"><iconify-icon icon="lucide:map-pin-off"></iconify-icon><p>Geolocation not available. Try "Map Center".</p></div>';
        info.textContent = '';
        return;
      }
      navigator.geolocation.getCurrentPosition(
        function(pos) { fetchNearby(pos.coords.latitude, pos.coords.longitude); },
        function(err) {
          showToast('Location access denied. Using map center instead.');
          var c = map.getCenter();
          fetchNearby(c.lat, c.lng);
        },
        { enableHighAccuracy: true, timeout: 8000 }
      );
    } else {
      var c = map.getCenter();
      fetchNearby(c.lat, c.lng);
    }
  }

  // ═══════════════════════════════════════════════════════════
  //  NEARBY PLACES — RENDER LIST
  // ═══════════════════════════════════════════════════════════
  function renderNearbyList(places) {
    var list = document.getElementById('nearbyList');
    if (!places.length) {
      list.innerHTML = '<div class="empty-state"><iconify-icon icon="lucide:search-x"></iconify-icon><p>No places found in this radius. Try a larger radius.</p></div>';
      return;
    }

    var html = places.map(function(p) {
      var cat  = POI_CATS[p.category] || { emoji: '📍', color: '#6b7280', label: p.category };
      var meta = p.meta || {};
      var detail = '';
      if (p.category === 'gas_station')   detail = (meta.brand ? esc(meta.brand) + ' · ' : '') + (meta.diesel_price ? 'Diesel ' + esc(meta.diesel_price) : '') + (meta.open_247 ? ' · 24/7' : '');
      if (p.category === 'hotel')         detail = (meta.brand ? esc(meta.brand) : '') + (meta.price_range ? ' · ' + esc(meta.price_range) : '') + (meta.star_rating ? ' · ' + '★'.repeat(meta.star_rating) : '');
      if (p.category === 'restaurant')    detail = (meta.cuisine ? esc(meta.cuisine) : '') + (meta.trucker_friendly ? ' · 🚚 Trucker-friendly' : '');
      if (p.category === 'library')       detail = (meta.wifi ? 'WiFi available · ' : '') + (meta.hours ? esc(meta.hours.split(',')[0]) : '');
      if (p.category === 'movie_theater') detail = (meta.screens ? meta.screens + ' screens' : '') + (meta.accessibility ? ' · Accessible' : '');
      if (p.category === 'tms_terminal')  detail = (meta.carrier ? esc(meta.carrier) : '') + (meta.open_247 ? ' · 24/7' : '') + (meta.dock_doors ? ' · ' + meta.dock_doors + ' doors' : '');

      return '<div class="poi-item" data-id="' + esc(p.id) + '" onclick="focusPoi(\'' + esc(p.id) + '\')">'
        + '<div class="poi-emoji">' + cat.emoji + '</div>'
        + '<div class="poi-info">'
        + '<div class="poi-name">' + esc(p.name) + '</div>'
        + '<div class="poi-addr">' + esc(p.address) + '</div>'
        + (detail ? '<div class="poi-addr" style="margin-top:2px;">' + detail + '</div>' : '')
        + '</div>'
        + '<div style="display:flex;flex-direction:column;align-items:flex-end;gap:3px;flex-shrink:0;">'
        + '<span class="poi-dist">' + p.distance + ' mi</span>'
        + '<span class="poi-badge" style="background:' + cat.color + '22;color:' + cat.color + ';border:1px solid ' + cat.color + '44;">' + esc(cat.label) + '</span>'
        + '</div>'
        + '</div>';
    }).join('');
    list.innerHTML = html;
  }

  // ── Focus a POI on the map ────────────────────────────────
  function focusPoi(placeId) {
    document.querySelectorAll('.poi-item').forEach(function(el) {
      el.classList.toggle('active', el.dataset.id === placeId);
    });
    var found = null;
    Object.values(poiLayers).forEach(function(lg) {
      lg.eachLayer(function(m) {
        if (m.options && m.options.placeId === placeId) found = m;
      });
    });
    if (found) {
      map.setView(found.getLatLng(), 14, { animate: true });
      found.openPopup();
    }
  }

  // ═══════════════════════════════════════════════════════════
  //  POI MAP MARKERS
  // ═══════════════════════════════════════════════════════════
  function renderPoiMarkers(places, centerLat, centerLng, radius) {
    // Clear existing POI layers
    Object.values(poiLayers).forEach(function(lg) { map.removeLayer(lg); });
    poiLayers = {};

    // Draw radius circle
    if (window._radiusCircle) map.removeLayer(window._radiusCircle);
    window._radiusCircle = L.circle([centerLat, centerLng], {
      radius: radius * 1609.34, // miles to meters
      color: '#0b6fff', weight: 1.5, opacity: 0.4,
      fillColor: '#0b6fff', fillOpacity: 0.04,
    }).addTo(map);

    // Group places by category
    var grouped = {};
    places.forEach(function(p) {
      if (!grouped[p.category]) grouped[p.category] = [];
      grouped[p.category].push(p);
    });

    Object.keys(grouped).forEach(function(cat) {
      var lg = L.layerGroup();
      grouped[cat].forEach(function(p) {
        if (!p.lat || !p.lng) return;
        var meta = p.meta || {};
        var cat2 = POI_CATS[cat] || { emoji: '📍', color: '#6b7280', label: cat };

        var popupLines = [
          '<div class="popup-name">' + cat2.emoji + ' ' + esc(p.name) + '</div>',
          '<div class="popup-updated">' + esc(p.address) + '</div>',
        ];
        if (p.phone) popupLines.push('<div class="popup-updated">📞 ' + esc(p.phone) + '</div>');
        if (cat === 'gas_station' && meta.diesel_price) popupLines.push('<div class="popup-updated">⛽ Diesel: ' + esc(meta.diesel_price) + ' | ' + (meta.open_247 ? '24/7' : 'Hours vary') + '</div>');
        if (cat === 'hotel' && meta.brand)      popupLines.push('<div class="popup-updated">🏨 ' + esc(meta.brand) + (meta.price_range ? ' · ' + esc(meta.price_range) : '') + '</div>');
        if (cat === 'restaurant' && meta.cuisine) popupLines.push('<div class="popup-updated">🍽️ ' + esc(meta.cuisine) + (meta.trucker_friendly ? ' · Trucker-friendly' : '') + '</div>');
        if (cat === 'library' && meta.hours)    popupLines.push('<div class="popup-updated">🕐 ' + esc(meta.hours.split(',')[0]) + '</div>');
        if (cat === 'movie_theater' && meta.screens) popupLines.push('<div class="popup-updated">🎬 ' + meta.screens + ' screens</div>');
        if (cat === 'tms_terminal' && meta.carrier) popupLines.push('<div class="popup-updated">🏭 ' + esc(meta.carrier) + (meta.open_247 ? ' · 24/7' : '') + '</div>');
        if (meta.website) {
          var websiteUrl = /^https?:\/\//i.test(meta.website) ? meta.website : 'https://' + meta.website;
          popupLines.push('<div class="popup-updated" style="margin-top:4px;"><a href="' + esc(websiteUrl) + '" target="_blank" rel="noopener noreferrer" style="color:var(--primary);">Visit Website ↗</a></div>');
        }
        popupLines.push('<div class="popup-updated" style="margin-top:4px;color:var(--primary);font-weight:600;">' + p.distance + ' mi away</div>');

        var marker = L.marker([p.lat, p.lng], {
          icon: poiIcon(cat),
          placeId: p.id,
        }).bindPopup(popupLines.join(''));

        lg.addLayer(marker);
      });
      poiLayers[cat] = lg;
      if (poiVisible[cat] !== false) {
        lg.addTo(map);
        poiVisible[cat] = true;
      }
    });

    // Fit map to show all results
    if (places.length > 0) {
      var bounds = [[centerLat, centerLng]];
      places.forEach(function(p) { if (p.lat && p.lng) bounds.push([p.lat, p.lng]); });
      map.fitBounds(bounds, { padding: [30, 30], maxZoom: 12 });
    }
  }

  // ═══════════════════════════════════════════════════════════
  //  LAYER TOGGLE BUTTONS
  // ═══════════════════════════════════════════════════════════
  function renderLayerToggles(grouped) {
    var container = document.getElementById('layerToggles');
    var html = '';
    Object.keys(grouped).forEach(function(cat) {
      var c = POI_CATS[cat] || { emoji: '📍', color: '#6b7280', label: cat };
      var count = grouped[cat].length;
      var isActive = poiVisible[cat] !== false;
      html += '<span class="layer-btn ' + (isActive ? 'active' : '') + '"'
        + ' style="color:' + c.color + ';border-color:' + c.color + ';background:' + (isActive ? c.color + '18' : 'transparent') + '"'
        + ' onclick="toggleLayer(\'' + cat + '\')" data-cat="' + cat + '">'
        + c.emoji + ' ' + count
        + '</span>';
    });
    container.innerHTML = html || '<span style="font-size:12px;color:var(--muted-foreground);">No results to toggle.</span>';
  }

  function toggleLayer(cat) {
    if (!poiLayers[cat]) return;
    if (poiVisible[cat] !== false) {
      map.removeLayer(poiLayers[cat]);
      poiVisible[cat] = false;
    } else {
      poiLayers[cat].addTo(map);
      poiVisible[cat] = true;
    }
    // Update button style
    var btn = document.querySelector('.layer-btn[data-cat="' + cat + '"]');
    if (btn) {
      var c = POI_CATS[cat] || { color: '#6b7280' };
      btn.classList.toggle('active', poiVisible[cat] !== false);
      btn.style.background = poiVisible[cat] ? c.color + '18' : 'transparent';
    }
  }

  // ═══════════════════════════════════════════════════════════
  //  AUTO REFRESH
  // ═══════════════════════════════════════════════════════════
  function startAutoRefresh() {
    clearInterval(refreshTimer);
    refreshTimer = setInterval(loadData, REFRESH_MS);
  }

  // ═══════════════════════════════════════════════════════════
  //  INIT
  // ═══════════════════════════════════════════════════════════
  document.addEventListener('DOMContentLoaded', function () {
    initMap();
    loadData();
    startAutoRefresh();
    // Pre-populate all category layers as visible
    Object.keys(POI_CATS).forEach(function(cat) { poiVisible[cat] = true; });
  });
  </script>
</body>
</html>
