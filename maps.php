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
  <!-- Leaflet Routing Machine for multi-stop routes -->
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
      flex: 1;
      min-height: 300px;
      border-radius: 0 0 var(--radius-xl) var(--radius-xl);
      z-index: 1;
    }

    /* ── Navigation toolbar ── */
    .nav-toolbar {
      display: flex; align-items: center; gap: 8px;
      padding: 8px 12px; border-bottom: 1px solid var(--border); flex-shrink: 0;
      flex-wrap: wrap;
    }
    .route-info {
      font-size: 12px; color: var(--primary); font-weight: 600;
      flex: 1; text-align: right; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .my-location-dot {
      width: 14px; height: 14px; background: #1d4ed8;
      border: 3px solid #fff; border-radius: 50%;
      box-shadow: 0 0 0 2px #1d4ed8, 0 2px 6px rgba(0,0,0,.3);
      display: inline-block;
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

    /* ── Map style switcher ── */
    .style-switcher {
      display: flex; gap: 4px; align-items: center;
    }
    .style-btn {
      padding: 4px 10px; font-size: 11px; border-radius: var(--radius-md);
      border: 1.5px solid var(--border); cursor: pointer; font-weight: 600;
      background: var(--card); color: var(--muted-foreground);
      transition: all .15s;
    }
    .style-btn.active { background: var(--primary); color: #fff; border-color: var(--primary); }
    .style-btn:hover:not(.active) { color: var(--foreground); border-color: var(--primary); }

    /* ── Map search bar ── */
    .map-search-wrap {
      position: absolute; top: 12px; left: 50%; transform: translateX(-50%);
      width: min(380px, calc(100% - 100px));
      z-index: 500;
    }
    .map-search-input {
      width: 100%; box-sizing: border-box;
      padding: 9px 14px 9px 36px;
      border: 1.5px solid var(--border);
      border-radius: var(--radius-lg);
      font-family: var(--font-family-body);
      font-size: 13px; background: #fff;
      color: var(--foreground); outline: none;
      box-shadow: 0 2px 12px rgba(0,0,0,.18);
      transition: border-color .2s;
    }
    .map-search-input:focus { border-color: var(--primary); }
    .map-search-icon {
      position: absolute; left: 12px; top: 50%;
      transform: translateY(-50%);
      color: var(--muted-foreground); font-size: 15px;
      pointer-events: none;
    }
    .search-autocomplete {
      position: absolute; top: calc(100% + 4px); left: 0; right: 0;
      background: #fff; border: 1.5px solid var(--border);
      border-radius: var(--radius-lg);
      box-shadow: 0 4px 20px rgba(0,0,0,.15);
      z-index: 600; max-height: 280px; overflow-y: auto;
    }
    .search-ac-item {
      padding: 9px 14px; font-size: 12px; cursor: pointer;
      border-bottom: 1px solid var(--border);
      transition: background .1s;
    }
    .search-ac-item:last-child { border-bottom: none; }
    .search-ac-item:hover, .search-ac-item.focused { background: var(--secondary); }
    .search-ac-name { font-weight: 600; color: var(--foreground); }
    .search-ac-detail { font-size: 11px; color: var(--muted-foreground); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .search-ac-loading { padding: 10px 14px; font-size: 12px; color: var(--muted-foreground); text-align: center; }

    /* ── Route planner panel ── */
    .route-panel { display: flex; flex-direction: column; flex: 1; overflow: hidden; }
    .route-mode-bar {
      display: flex; gap: 6px; padding: 10px 12px;
      border-bottom: 1px solid var(--border); flex-shrink: 0;
    }
    .route-mode-btn {
      flex: 1; padding: 6px 8px; font-size: 11px; font-weight: 700;
      border-radius: var(--radius-md); border: 1.5px solid var(--border);
      cursor: pointer; background: var(--card); color: var(--muted-foreground);
      text-align: center; transition: all .15s;
    }
    .route-mode-btn.active { background: var(--primary); color: #fff; border-color: var(--primary); }
    .route-waypoints {
      padding: 10px 12px; border-bottom: 1px solid var(--border); flex-shrink: 0;
    }
    .waypoint-row {
      display: flex; align-items: center; gap: 6px; margin-bottom: 6px;
    }
    .waypoint-row:last-child { margin-bottom: 0; }
    .waypoint-input {
      flex: 1; padding: 6px 10px;
      border: 1.5px solid var(--border); border-radius: var(--radius-md);
      font-size: 12px; background: var(--muted); color: var(--foreground);
      outline: none; transition: border-color .2s;
    }
    .waypoint-input:focus { border-color: var(--primary); }
    .waypoint-dot {
      width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0;
    }
    .route-actions { display: flex; gap: 6px; margin-top: 8px; }
    .route-result {
      padding: 10px 12px; border-bottom: 1px solid var(--border);
      flex-shrink: 0; font-size: 12px;
    }
    .route-leg { display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px; }
    .route-leg-label { font-weight: 600; color: var(--foreground); }
    .route-leg-info { color: var(--primary); font-weight: 600; }
    .alt-route-list { padding: 0 12px 10px; flex-shrink: 0; }
    .alt-route-item {
      display: flex; justify-content: space-between; align-items: center;
      padding: 7px 10px; border: 1.5px solid var(--border);
      border-radius: var(--radius-md); cursor: pointer; margin-bottom: 4px;
      font-size: 12px; background: var(--card); transition: all .15s;
    }
    .alt-route-item.active { border-color: var(--primary); background: rgba(11,111,255,.06); }
    .alt-route-item:hover:not(.active) { border-color: var(--primary); }

    /* ── Traffic / layer overlays ── */
    .overlay-bar {
      display: flex; gap: 4px; align-items: center;
      flex-wrap: wrap;
    }
    .overlay-btn {
      display: inline-flex; align-items: center; gap: 4px;
      padding: 4px 10px; font-size: 11px; font-weight: 600;
      border-radius: 12px; border: 1.5px solid var(--border);
      cursor: pointer; background: var(--card); color: var(--muted-foreground);
      transition: all .15s;
    }
    .overlay-btn.active { background: var(--primary); color: #fff; border-color: var(--primary); }

    /* ── Truck filter chip ── */
    .truck-chip {
      display: inline-flex; align-items: center; gap: 4px;
      padding: 3px 10px; font-size: 11px; font-weight: 600;
      border-radius: 12px; border: 1.5px solid var(--border);
      cursor: pointer; background: var(--card); color: var(--muted-foreground);
      transition: all .15s;
    }
    .truck-chip.active { background: #f59e0b22; color: #b45309; border-color: #f59e0b; }

    /* ── Incident marker ── */
    .incident-badge { font-size: 10px; padding: 2px 6px; border-radius: 8px; font-weight: 700; }
    .incident-accident  { background: #fee2e2; color: #b91c1c; }
    .incident-closure   { background: #fef3c7; color: #92400e; }
    .incident-hazard    { background: #e0f2fe; color: #0369a1; }

    /* ── Collapsible map info bar ── */
    .map-info-bar {
      position: absolute; bottom: 12px; left: 12px;
      background: rgba(255,255,255,.92); border: 1px solid var(--border);
      border-radius: var(--radius-lg); padding: 8px 14px;
      font-size: 12px; z-index: 450; max-width: 260px;
      box-shadow: 0 2px 8px rgba(0,0,0,.15);
      display: none;
    }
    .map-info-bar.show { display: block; }
    .map-info-bar .close-btn {
      position: absolute; top: 4px; right: 8px;
      background: none; border: none; cursor: pointer; font-size: 14px; color: #666;
    }
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

      <!-- Left: Tabbed panel (Drivers / Nearby Places / Route Planner) -->
      <div class="panel">
        <!-- Tabs -->
        <div class="panel-tabs">
          <button class="panel-tab active" id="tabDrivers" onclick="switchTab('drivers')">
            🚚 Drivers
          </button>
          <button class="panel-tab" id="tabNearby" onclick="switchTab('nearby')">
            📍 Places
          </button>
          <button class="panel-tab" id="tabRoute" onclick="switchTab('route')">
            🗺 Route
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
          <div class="nearby-controls" style="padding-top:0;gap:6px;">
            <button class="btn-sm" onclick="loadNearby(true)" id="btnLocate">
              <iconify-icon icon="lucide:locate" style="font-size:13px;vertical-align:-2px;margin-right:3px"></iconify-icon>Use My Location
            </button>
            <button class="btn-sm outline" onclick="loadNearby(false)" id="btnMapCenter">
              <iconify-icon icon="lucide:map" style="font-size:13px;vertical-align:-2px;margin-right:3px"></iconify-icon>Map Center
            </button>
            <span class="truck-chip" id="truckFilterChip" onclick="toggleTruckFilter()" title="Show only truck-accessible locations">
              🚛 Truck
            </span>
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

        <!-- ── Route Planner sub-panel ── -->
        <div id="routePanel" style="display:none;flex-direction:column;flex:1;overflow:hidden;">
          <!-- Transport mode -->
          <div class="route-mode-bar">
            <button class="route-mode-btn active" data-mode="driving" onclick="setRouteMode('driving')">🚗 Car</button>
            <button class="route-mode-btn" data-mode="truck" onclick="setRouteMode('truck')">🚛 Truck</button>
            <button class="route-mode-btn" data-mode="foot" onclick="setRouteMode('foot')">🚶 Walk</button>
            <button class="route-mode-btn" data-mode="bike" onclick="setRouteMode('bike')">🚲 Bike</button>
          </div>
          <!-- Waypoints -->
          <div class="route-waypoints" id="waypointsContainer">
            <div class="waypoint-row">
              <span class="waypoint-dot" style="background:#16a34a;"></span>
              <input type="text" class="waypoint-input" id="wpOrigin" placeholder="From: My location or address…" />
              <button class="btn-sm" style="padding:4px 8px;font-size:11px;" onclick="setWaypointFromLocation(0)" title="Use my location">📍</button>
            </div>
            <div id="extraWaypoints"></div>
            <div class="waypoint-row">
              <span class="waypoint-dot" style="background:#ef4444;"></span>
              <input type="text" class="waypoint-input" id="wpDest" placeholder="To: Destination address…" />
            </div>
            <div class="route-actions">
              <button class="btn-sm" onclick="calculateRoute()" style="flex:1;">
                <iconify-icon icon="lucide:navigation" style="font-size:12px;vertical-align:-1px;margin-right:3px"></iconify-icon>Get Route
              </button>
              <button class="btn-sm outline" onclick="addWaypointRow()" title="Add stop">+ Stop</button>
              <button class="btn-sm outline" onclick="clearRoute()" title="Clear route" id="routeClearBtn" style="display:none;">✕ Clear</button>
            </div>
          </div>
          <!-- Route result summary -->
          <div class="route-result" id="routeResultPanel" style="display:none;">
            <div id="routeLegsHtml"></div>
          </div>
          <!-- Alternative routes -->
          <div class="alt-route-list" id="altRouteList" style="display:none;"></div>
          <!-- Turn-by-turn steps -->
          <div class="driver-list" id="routeStepsList" style="flex:1;overflow-y:auto;padding:10px 12px;">
            <div class="location-prompt">
              <iconify-icon icon="lucide:map" style="font-size:28px;"></iconify-icon>
              <p>Enter origin &amp; destination, then click <strong>Get Route</strong>.</p>
            </div>
          </div>
        </div>

      </div>

      <!-- Right: Map -->
      <div class="panel" style="overflow:hidden;display:flex;flex-direction:column;position:relative;">
        <!-- Navigation toolbar -->
        <div class="nav-toolbar">
          <button class="btn-sm" onclick="centerOnMyLocation()" title="Center map on your real-time location">
            <iconify-icon icon="lucide:locate" style="font-size:13px;vertical-align:-2px;margin-right:3px"></iconify-icon>My Location
          </button>
          <!-- Map style switcher -->
          <div class="style-switcher">
            <button class="style-btn active" data-style="light" onclick="switchMapStyle('light')" title="Light map">☀️ Light</button>
            <button class="style-btn" data-style="dark" onclick="switchMapStyle('dark')" title="Dark map">🌙 Dark</button>
            <button class="style-btn" data-style="satellite" onclick="switchMapStyle('satellite')" title="Satellite">🛰 Sat</button>
          </div>
          <!-- Layer overlays -->
          <div class="overlay-bar">
            <span class="overlay-btn" id="trafficBtn" onclick="toggleTrafficLayer()" title="Traffic overlay">🚦 Traffic</span>
            <span class="overlay-btn" id="incidentBtn" onclick="toggleIncidentLayer()" title="Incidents">⚠️ Incidents</span>
          </div>
          <button class="btn-sm outline" id="btnClearRoute" onclick="clearRoute()" style="display:none;" title="Remove current route from map">
            <iconify-icon icon="lucide:x" style="font-size:13px;vertical-align:-2px;margin-right:3px"></iconify-icon>Clear Route
          </button>
          <span id="routeInfo" class="route-info"></span>
        </div>
        <!-- Map search bar -->
        <div class="map-search-wrap" id="mapSearchWrap">
          <iconify-icon icon="lucide:search" class="map-search-icon"></iconify-icon>
          <input type="text" class="map-search-input" id="mapSearchInput" placeholder="Search address, city, place…"
            autocomplete="off" oninput="onMapSearchInput(this.value)" onkeydown="onMapSearchKey(event)" />
          <div class="search-autocomplete" id="searchAC" style="display:none;"></div>
        </div>
        <!-- Reverse geocode info bar -->
        <div class="map-info-bar" id="mapInfoBar">
          <button class="close-btn" onclick="document.getElementById('mapInfoBar').classList.remove('show')">✕</button>
          <div id="mapInfoContent" style="font-size:12px;padding-right:16px;"></div>
        </div>
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
  var truckFilter   = false; // show only truck-accessible POIs

  // Navigation / user location state
  var userLat           = null;
  var userLng           = null;
  var userWatchId       = null;
  var userLocMarker     = null;
  var userLocCircle     = null;
  var routeLayer        = null;
  var routeLayers       = [];  // multi-route alternatives
  var activeRouteIdx    = 0;

  // Map style
  var currentMapStyle   = 'light';
  var tileLayers        = {};  // named tile layers

  // Route planner state
  var routeMode         = 'driving';
  var extraWaypoints    = [];  // [{lat, lng, label}]

  // Overlay state
  var trafficLayerOn    = false;
  var incidentLayerOn   = false;
  var trafficLayer      = null;
  var incidentMarkers   = [];

  // Search state
  var searchTimer       = null;
  var searchResults     = [];
  var searchFocusIdx    = -1;
  var searchMarker      = null;

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

  /** Return a finite, in-range coordinate or null. */
  function safeCoord(v, min, max) {
    var n = parseFloat(v);
    return (isFinite(n) && n >= min && n <= max) ? n : null;
  }
  function safeLat(v) { return safeCoord(v, -90,  90);  }
  function safeLng(v) { return safeCoord(v, -180, 180); }

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

    // Define tile layers for style switching
    tileLayers.light = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
      maxZoom: 19,
    });
    tileLayers.dark = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
      attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, © <a href="https://carto.com/attributions">CARTO</a>',
      subdomains: 'abcd', maxZoom: 19,
    });
    tileLayers.satellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
      attribution: 'Tiles © Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community',
      maxZoom: 18,
    });

    tileLayers.light.addTo(map);

    // Add "My Location" Leaflet control button (bottom-right)
    var LocControl = L.Control.extend({
      options: { position: 'bottomright' },
      onAdd: function () {
        var btn = L.DomUtil.create('button', 'leaflet-bar leaflet-control');
        btn.title = 'Center on my location';
        btn.style.cssText = 'width:34px;height:34px;background:#fff;border:none;cursor:pointer;font-size:17px;display:flex;align-items:center;justify-content:center;box-shadow:0 1px 5px rgba(0,0,0,.35);border-radius:4px;';
        btn.innerHTML = '📍';
        L.DomEvent.on(btn, 'click', function (e) {
          L.DomEvent.stopPropagation(e);
          centerOnMyLocation();
        });
        return btn;
      }
    });
    new LocControl().addTo(map);

    // Click on map for reverse geocoding
    map.on('click', function(e) {
      reverseGeocodePoint(e.latlng.lat, e.latlng.lng);
    });

    startLocationTracking();
    tryIpLocationFallback();
  }

  // ═══════════════════════════════════════════════════════════
  //  MAP STYLE SWITCHING
  // ═══════════════════════════════════════════════════════════
  function switchMapStyle(style) {
    if (style === currentMapStyle) return;
    if (tileLayers[currentMapStyle]) map.removeLayer(tileLayers[currentMapStyle]);
    if (tileLayers[style]) tileLayers[style].addTo(map);
    currentMapStyle = style;
    document.querySelectorAll('.style-btn').forEach(function(b) {
      b.classList.toggle('active', b.dataset.style === style);
    });
  }

  // ═══════════════════════════════════════════════════════════
  //  REVERSE GEOCODING (click on map)
  // ═══════════════════════════════════════════════════════════
  function reverseGeocodePoint(lat, lng) {
    var bar = document.getElementById('mapInfoBar');
    var content = document.getElementById('mapInfoContent');
    content.innerHTML = '<iconify-icon icon="lucide:loader-circle" style="animation:spin 1s linear infinite;margin-right:4px"></iconify-icon>Getting address…';
    bar.classList.add('show');

    fetch('nearby_places_data.php?action=reverse_geocode&lat=' + lat.toFixed(6) + '&lng=' + lng.toFixed(6) + '&t=' + Date.now())
      .then(function(r) { return r.json(); })
      .then(function(data) {
        if (data.success) {
          var addr = data.address || {};
          var short = [addr.road, addr.city || addr.town || addr.village, addr.state].filter(Boolean).join(', ');
          content.innerHTML = '<strong>📍 ' + esc(short || data.display) + '</strong>'
            + '<br><span style="color:var(--muted-foreground);">' + lat.toFixed(5) + ', ' + lng.toFixed(5) + '</span>'
            + '<br><button onclick="setRouteDestFromMap(' + lat + ',' + lng + ',\'' + esc(short || 'Selected point') + '\')" style="margin-top:4px;background:var(--primary);color:#fff;border:none;border-radius:4px;padding:3px 8px;font-size:11px;cursor:pointer;">Set as Destination</button>';
        } else {
          content.innerHTML = '📍 ' + lat.toFixed(5) + ', ' + lng.toFixed(5)
            + '<br><button onclick="setRouteDestFromMap(' + lat + ',' + lng + ',\'Selected point\')" style="margin-top:4px;background:var(--primary);color:#fff;border:none;border-radius:4px;padding:3px 8px;font-size:11px;cursor:pointer;">Set as Destination</button>';
        }
      })
      .catch(function() {
        content.innerHTML = '📍 ' + lat.toFixed(5) + ', ' + lng.toFixed(5);
      });
  }

  function setRouteDestFromMap(lat, lng, label) {
    document.getElementById('wpDest').value = label + ' (' + lat.toFixed(5) + ',' + lng.toFixed(5) + ')';
    document.getElementById('wpDest').dataset.lat = lat;
    document.getElementById('wpDest').dataset.lng = lng;
    switchTab('route');
    showToast('Destination set: ' + label);
  }

  // ═══════════════════════════════════════════════════════════
  //  MAP SEARCH (Nominatim geocoding)
  // ═══════════════════════════════════════════════════════════
  function onMapSearchInput(val) {
    var ac = document.getElementById('searchAC');
    clearTimeout(searchTimer);
    if (!val || val.length < 3) { ac.style.display = 'none'; return; }
    searchTimer = setTimeout(function() {
      ac.style.display = 'block';
      ac.innerHTML = '<div class="search-ac-loading"><iconify-icon icon="lucide:loader-circle" style="animation:spin 1s linear infinite"></iconify-icon> Searching…</div>';
      fetch('nearby_places_data.php?action=geocode&q=' + encodeURIComponent(val) + '&limit=6&t=' + Date.now())
        .then(function(r) { return r.json(); })
        .then(function(data) {
          searchResults = data.results || [];
          searchFocusIdx = -1;
          renderSearchAC();
        })
        .catch(function() { ac.style.display = 'none'; });
    }, 350);
  }

  function renderSearchAC() {
    var ac = document.getElementById('searchAC');
    if (!searchResults.length) {
      ac.innerHTML = '<div class="search-ac-loading">No results found.</div>';
      return;
    }
    ac.innerHTML = searchResults.map(function(r, i) {
      var parts = r.display.split(',');
      var name   = parts[0] || r.display;
      var detail = parts.slice(1, 3).join(',').trim();
      return '<div class="search-ac-item' + (i === searchFocusIdx ? ' focused' : '') + '"'
        + ' onmousedown="selectSearchResult(' + i + ')">'
        + '<div class="search-ac-name">' + esc(name) + '</div>'
        + (detail ? '<div class="search-ac-detail">' + esc(detail) + '</div>' : '')
        + '</div>';
    }).join('');
  }

  function onMapSearchKey(e) {
    var ac = document.getElementById('searchAC');
    if (ac.style.display === 'none') return;
    if (e.key === 'ArrowDown') { searchFocusIdx = Math.min(searchFocusIdx + 1, searchResults.length - 1); renderSearchAC(); e.preventDefault(); }
    else if (e.key === 'ArrowUp') { searchFocusIdx = Math.max(searchFocusIdx - 1, 0); renderSearchAC(); e.preventDefault(); }
    else if (e.key === 'Enter' && searchFocusIdx >= 0) { selectSearchResult(searchFocusIdx); e.preventDefault(); }
    else if (e.key === 'Escape') { ac.style.display = 'none'; }
  }

  function selectSearchResult(idx) {
    var r = searchResults[idx];
    if (!r) return;
    document.getElementById('searchAC').style.display = 'none';
    document.getElementById('mapSearchInput').value = r.display.split(',')[0];
    // Place a search marker
    if (searchMarker) map.removeLayer(searchMarker);
    searchMarker = L.marker([r.lat, r.lng], {
      icon: L.divIcon({
        html: '<div style="background:#ef4444;width:14px;height:14px;border-radius:50%;border:3px solid #fff;box-shadow:0 0 0 2px #ef4444;transform:translate(-7px,-7px)"></div>',
        iconSize: [0,0], iconAnchor: [0,0], className: ''
      })
    }).bindPopup('<strong>' + esc(r.display.split(',')[0]) + '</strong><br><span style="font-size:11px;color:#6b7280">' + esc(r.display.split(',').slice(1,3).join(',')) + '</span>').addTo(map);
    map.setView([r.lat, r.lng], 14, { animate: true });
    searchMarker.openPopup();
    showToast('Found: ' + r.display.split(',')[0]);
  }

  // ═══════════════════════════════════════════════════════════
  //  IP-BASED LOCATION FALLBACK
  // ═══════════════════════════════════════════════════════════
  function tryIpLocationFallback() {
    // If geolocation not available or denied after 5 seconds, use IP location
    setTimeout(function() {
      if (userLat === null) {
        fetch('nearby_places_data.php?action=ip_location&t=' + Date.now())
          .then(function(r) { return r.json(); })
          .then(function(data) {
            if (data.success && userLat === null) {
              // Only use if we still don't have a location
              if (data.source !== 'default') {
                // Center map on IP location (less precise than GPS)
                map.setView([data.lat, data.lng], 10, { animate: false });
                showToast('Map centered on approximate IP location: ' + (data.city || '') + (data.region ? ', ' + data.region : ''));
              }
            }
          })
          .catch(function() {}); // Silently ignore
      }
    }, 5000);
  }

  // ═══════════════════════════════════════════════════════════
  //  TRAFFIC OVERLAY
  // ═══════════════════════════════════════════════════════════
  function toggleTrafficLayer() {
    trafficLayerOn = !trafficLayerOn;
    var btn = document.getElementById('trafficBtn');
    btn.classList.toggle('active', trafficLayerOn);

    if (trafficLayerOn) {
      // Use a free traffic-style tile overlay (Esri world transportation)
      trafficLayer = L.tileLayer(
        'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
        { opacity: 0.0, maxZoom: 19 }
      );
      // Simulate traffic with a semi-transparent heatmap using Carto tiles
      trafficLayer = L.tileLayer(
        'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png',
        {
          subdomains: 'abcd', maxZoom: 19,
          opacity: 0.45,
          attribution: '',
        }
      ).addTo(map);
      showToast('Traffic overlay enabled (voyager style)');
    } else {
      if (trafficLayer) { map.removeLayer(trafficLayer); trafficLayer = null; }
      showToast('Traffic overlay disabled');
    }
  }

  // ═══════════════════════════════════════════════════════════
  //  INCIDENT OVERLAY (simulated)
  // ═══════════════════════════════════════════════════════════
  var SAMPLE_INCIDENTS = [
    { lat: 40.7128, lng: -74.0060, type: 'accident',  title: 'Multi-vehicle accident',   road: 'I-95 NB near Exit 12', severity: 'high'   },
    { lat: 34.0522, lng: -118.243, type: 'closure',   title: 'Road closure',             road: 'US-101 SB lane 2',     severity: 'medium' },
    { lat: 41.8781, lng: -87.6298, type: 'hazard',    title: 'Debris on road',           road: 'I-290 EB',             severity: 'low'    },
    { lat: 29.7604, lng: -95.3698, type: 'accident',  title: 'Truck rollover',           road: 'I-610 WB',             severity: 'high'   },
    { lat: 33.4484, lng: -112.074, type: 'closure',   title: 'Bridge maintenance',       road: 'AZ-51 NB',             severity: 'medium' },
    { lat: 47.6062, lng: -122.332, type: 'hazard',    title: 'Black ice warning',        road: 'I-5 NB near MP 167',   severity: 'medium' },
    { lat: 39.9526, lng: -75.1652, type: 'accident',  title: 'Fender-bender',            road: 'I-76 WB',              severity: 'low'    },
    { lat: 32.7767, lng: -96.7970, type: 'closure',   title: 'Construction zone',        road: 'I-35E NB',             severity: 'medium' },
  ];

  function toggleIncidentLayer() {
    incidentLayerOn = !incidentLayerOn;
    var btn = document.getElementById('incidentBtn');
    btn.classList.toggle('active', incidentLayerOn);

    if (incidentLayerOn) {
      var typeColors = { accident: '#ef4444', closure: '#f59e0b', hazard: '#3b82f6' };
      var typeEmoji  = { accident: '🚨', closure: '🚧', hazard: '⚠️' };
      SAMPLE_INCIDENTS.forEach(function(inc) {
        var color = typeColors[inc.type] || '#6b7280';
        var emoji = typeEmoji[inc.type]  || '⚠️';
        var icon = L.divIcon({
          html: '<div style="background:' + color + ';color:#fff;font-size:13px;width:26px;height:26px;border-radius:50%;display:flex;align-items:center;justify-content:center;border:2px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,.3);transform:translate(-13px,-13px)">' + emoji + '</div>',
          iconSize: [0,0], iconAnchor: [0,0], className: ''
        });
        var m = L.marker([inc.lat, inc.lng], { icon: icon })
          .bindPopup(
            '<div style="font-weight:700;font-size:13px;margin-bottom:4px;">' + emoji + ' ' + esc(inc.title) + '</div>'
            + '<div style="font-size:12px;color:#374151;">' + esc(inc.road) + '</div>'
            + '<div style="margin-top:4px;"><span class="incident-badge incident-' + inc.type + '">' + inc.type.toUpperCase() + '</span>'
            + ' <span style="font-size:11px;color:#6b7280;">Severity: ' + inc.severity + '</span></div>'
          ).addTo(map);
        incidentMarkers.push(m);
      });
      showToast('Showing ' + SAMPLE_INCIDENTS.length + ' incidents on map');
    } else {
      incidentMarkers.forEach(function(m) { map.removeLayer(m); });
      incidentMarkers = [];
      showToast('Incidents hidden');
    }
  }

  // ═══════════════════════════════════════════════════════════
  //  REAL-TIME USER LOCATION TRACKING
  // ═══════════════════════════════════════════════════════════
  function startLocationTracking() {
    if (!navigator.geolocation) return;
    userWatchId = navigator.geolocation.watchPosition(
      function (pos) {
        userLat = pos.coords.latitude;
        userLng = pos.coords.longitude;
        updateUserLocMarker(userLat, userLng, pos.coords.accuracy);
      },
      function (err) {
        // PERMISSION_DENIED (1): silent — user chose to deny
        // POSITION_UNAVAILABLE (2) / TIMEOUT (3): notify so user can investigate
        if (err.code !== 1) {
          console.warn('Location tracking:', err.message);
          showToast('Location unavailable — GPS signal lost or timed out.');
        }
      },
      { enableHighAccuracy: true, maximumAge: 0, timeout: 15000 }
    );
  }

  function updateUserLocMarker(lat, lng, accuracy) {
    if (!map) return;
    var icon = L.divIcon({
      html: '<div class="my-location-dot"></div>',
      iconSize: [14, 14], iconAnchor: [7, 7],
      className: ''
    });
    var popupHtml = '<div class="popup-name">📍 Your Location</div>'
      + '<div class="popup-updated">Accuracy: ~' + Math.round(accuracy || 0) + ' m</div>'
      + '<div class="popup-updated">' + lat.toFixed(5) + ', ' + lng.toFixed(5) + '</div>';

    if (!userLocMarker) {
      userLocMarker = L.marker([lat, lng], { icon: icon, zIndexOffset: 2000 })
        .bindPopup(popupHtml).addTo(map);
      // On first fix, center the map on the user
      map.setView([lat, lng], 12, { animate: true });
    } else {
      userLocMarker.setLatLng([lat, lng]);
      userLocMarker.getPopup().setContent(popupHtml);
    }
    if (!userLocCircle) {
      userLocCircle = L.circle([lat, lng], {
        radius: accuracy || 50, color: '#1d4ed8', weight: 1.5,
        opacity: 0.5, fillColor: '#1d4ed8', fillOpacity: 0.08
      }).addTo(map);
    } else {
      userLocCircle.setLatLng([lat, lng]).setRadius(accuracy || 50);
    }
  }

  function centerOnMyLocation() {
    if (userLat !== null && userLng !== null) {
      map.setView([userLat, userLng], 14, { animate: true });
      if (userLocMarker) userLocMarker.openPopup();
    } else {
      showToast('Getting your location…');
      navigator.geolocation.getCurrentPosition(
        function (pos) {
          userLat = pos.coords.latitude;
          userLng = pos.coords.longitude;
          updateUserLocMarker(userLat, userLng, pos.coords.accuracy);
          map.setView([userLat, userLng], 14, { animate: true });
        },
        function () { showToast('Location access denied. Enable it in browser settings.'); },
        { enableHighAccuracy: true, timeout: 10000 }
      );
    }
  }

  // ═══════════════════════════════════════════════════════════
  //  ROUTING — Multi-modal (OSRM)
  // ═══════════════════════════════════════════════════════════

  // Route mode selector
  function setRouteMode(mode) {
    routeMode = mode;
    document.querySelectorAll('.route-mode-btn').forEach(function(b) {
      b.classList.toggle('active', b.dataset.mode === mode);
    });
  }

  // OSRM profile for each mode
  function osrmProfile(mode) {
    switch (mode) {
      case 'truck':   return 'driving';  // OSRM public only has driving/foot/bike
      case 'foot':    return 'foot';
      case 'bike':    return 'bike';
      default:        return 'driving';
    }
  }

  var OSRM_BASE = 'https://router.project-osrm.org';

  // Route planner: add extra waypoint row
  var extraWpCount = 0;
  function addWaypointRow() {
    extraWpCount++;
    var idx = extraWpCount;
    var div = document.createElement('div');
    div.className = 'waypoint-row';
    div.id = 'extWp' + idx;
    div.innerHTML = '<span class="waypoint-dot" style="background:#8b5cf6;"></span>'
      + '<input type="text" class="waypoint-input" id="wpExtra' + idx + '" placeholder="Via: stop ' + idx + '…" />'
      + '<button class="btn-sm outline" style="padding:4px 8px;font-size:11px;" onclick="removeWaypointRow(' + idx + ')" title="Remove">✕</button>';
    document.getElementById('extraWaypoints').appendChild(div);
  }
  function removeWaypointRow(idx) {
    var el = document.getElementById('extWp' + idx);
    if (el) el.remove();
  }

  // Set waypoint from current user location
  function setWaypointFromLocation(idx) {
    if (userLat !== null && userLng !== null) {
      var inp = document.getElementById('wpOrigin');
      inp.value = 'My Location (' + userLat.toFixed(5) + ',' + userLng.toFixed(5) + ')';
      inp.dataset.lat = userLat;
      inp.dataset.lng = userLng;
      showToast('Origin set to your current location');
    } else {
      showToast('Getting your location…');
      navigator.geolocation.getCurrentPosition(
        function(pos) {
          var inp = document.getElementById('wpOrigin');
          inp.value = 'My Location (' + pos.coords.latitude.toFixed(5) + ',' + pos.coords.longitude.toFixed(5) + ')';
          inp.dataset.lat = pos.coords.latitude;
          inp.dataset.lng = pos.coords.longitude;
          userLat = pos.coords.latitude; userLng = pos.coords.longitude;
          updateUserLocMarker(userLat, userLng, pos.coords.accuracy);
          showToast('Origin set to your current location');
        },
        function() { showToast('Location access denied.'); },
        { enableHighAccuracy: true, timeout: 10000 }
      );
    }
  }

  // Geocode a text input for routing
  function geocodeInput(inputEl, callback) {
    var val = inputEl.value.trim();
    if (!val) { callback(null); return; }
    // Check for stored lat/lng
    if (inputEl.dataset.lat && inputEl.dataset.lng) {
      callback({ lat: parseFloat(inputEl.dataset.lat), lng: parseFloat(inputEl.dataset.lng), label: val });
      return;
    }
    // Try to extract coords from string like "My Location (lat,lng)"
    var coordMatch = val.match(/\((-?\d+\.?\d*),(-?\d+\.?\d*)\)/);
    if (coordMatch) {
      callback({ lat: parseFloat(coordMatch[1]), lng: parseFloat(coordMatch[2]), label: val });
      return;
    }
    // Geocode with Nominatim
    fetch('nearby_places_data.php?action=geocode&q=' + encodeURIComponent(val) + '&limit=1&t=' + Date.now())
      .then(function(r) { return r.json(); })
      .then(function(data) {
        if (data.success && data.results && data.results.length) {
          var r = data.results[0];
          callback({ lat: r.lat, lng: r.lng, label: r.display.split(',')[0] || val });
        } else {
          callback(null);
        }
      })
      .catch(function() { callback(null); });
  }

  // Calculate route with all waypoints
  function calculateRoute() {
    var stepsEl  = document.getElementById('routeStepsList');
    var resultEl = document.getElementById('routeResultPanel');
    var altEl    = document.getElementById('altRouteList');
    stepsEl.innerHTML = '<div class="empty-state"><iconify-icon icon="lucide:loader-circle" style="animation:spin 1s linear infinite"></iconify-icon><p>Calculating route…</p></div>';
    resultEl.style.display = 'none'; altEl.style.display = 'none';

    var originEl = document.getElementById('wpOrigin');
    var destEl   = document.getElementById('wpDest');

    geocodeInput(originEl, function(origin) {
      if (!origin) {
        stepsEl.innerHTML = '<div class="empty-state"><iconify-icon icon="lucide:alert-circle"></iconify-icon><p>Could not find origin location. Please enter a valid address.</p></div>';
        return;
      }
      geocodeInput(destEl, function(dest) {
        if (!dest) {
          stepsEl.innerHTML = '<div class="empty-state"><iconify-icon icon="lucide:alert-circle"></iconify-icon><p>Could not find destination. Please enter a valid address.</p></div>';
          return;
        }

        // Collect extra waypoints
        var extraInputs = document.querySelectorAll('#extraWaypoints .waypoint-input');
        var waypointCoords = [[origin.lng, origin.lat]];
        var pendingExtra = extraInputs.length;

        function afterExtras() {
          waypointCoords.push([dest.lng, dest.lat]);
          runOsrmRoute(waypointCoords, origin.label, dest.label);
        }

        if (pendingExtra === 0) { afterExtras(); return; }
        var extraResults = new Array(pendingExtra).fill(null);
        extraInputs.forEach(function(inp, i) {
          geocodeInput(inp, function(r) {
            extraResults[i] = r;
            if (extraResults.every(function(x) { return x !== null || !extraInputs[i] || !extraInputs[i].value.trim(); })) {
              extraResults.forEach(function(r) { if (r) waypointCoords.push([r.lng, r.lat]); });
              afterExtras();
            }
          });
        });
      });
    });
  }

  function runOsrmRoute(coords, originLabel, destLabel) {
    var profile = osrmProfile(routeMode);
    var coordStr = coords.map(function(c) { return c[0].toFixed(6) + ',' + c[1].toFixed(6); }).join(';');
    var url = OSRM_BASE + '/route/v1/' + profile + '/' + coordStr
      + '?overview=full&geometries=geojson&steps=true&alternatives=true';

    fetch(url)
      .then(function(r) { return r.json(); })
      .then(function(data) {
        if (!data.routes || !data.routes.length) {
          document.getElementById('routeStepsList').innerHTML = '<div class="empty-state"><iconify-icon icon="lucide:map-pin-off"></iconify-icon><p>No route found. Try a different mode or check the addresses.</p></div>';
          return;
        }
        // Clear existing route layers
        routeLayers.forEach(function(l) { map.removeLayer(l); });
        routeLayers = [];
        if (routeLayer) { map.removeLayer(routeLayer); routeLayer = null; }

        var routeColors = ['#0b6fff', '#10b981', '#f59e0b'];
        data.routes.forEach(function(route, idx) {
          var color = routeColors[idx % routeColors.length];
          var layer = L.geoJSON(route.geometry, {
            style: { color: color, weight: idx === 0 ? 6 : 4, opacity: idx === 0 ? 0.9 : 0.55, dashArray: idx === 0 ? null : '8 4' }
          }).addTo(map);
          routeLayers.push(layer);
        });

        activeRouteIdx = 0;
        routeLayer = routeLayers[0];

        // Fit bounds
        if (routeLayers[0]) map.fitBounds(routeLayers[0].getBounds(), { padding: [40, 40] });

        // Show result summary
        var primary = data.routes[0];
        var distMi = (primary.distance / 1609.34).toFixed(1);
        var mins   = Math.round(primary.duration / 60);
        var timeStr = mins >= 60 ? Math.floor(mins/60) + 'h ' + (mins % 60) + 'm' : mins + ' min';

        var modeLabel = { driving: '🚗 Car', truck: '🚛 Truck', foot: '🚶 Walking', bike: '🚲 Cycling' }[routeMode] || routeMode;
        document.getElementById('routeInfo').textContent = modeLabel + ' · ' + distMi + ' mi · ~' + timeStr;
        document.getElementById('btnClearRoute').style.display = 'inline-flex';
        document.getElementById('routeClearBtn').style.display = 'inline-flex';

        // Show summary panel
        var resultEl = document.getElementById('routeResultPanel');
        resultEl.style.display = 'block';
        document.getElementById('routeLegsHtml').innerHTML =
          '<div class="route-leg"><span class="route-leg-label">' + esc(originLabel) + ' → ' + esc(destLabel) + '</span></div>'
          + '<div class="route-leg"><span class="route-leg-label">' + modeLabel + '</span><span class="route-leg-info">' + distMi + ' mi · ~' + timeStr + '</span></div>';

        // Alternative routes
        if (data.routes.length > 1) {
          var altEl = document.getElementById('altRouteList');
          altEl.style.display = 'block';
          altEl.innerHTML = data.routes.map(function(r, i) {
            var d = (r.distance / 1609.34).toFixed(1);
            var t = Math.round(r.duration / 60);
            var ts = t >= 60 ? Math.floor(t/60) + 'h ' + (t%60) + 'm' : t + ' min';
            return '<div class="alt-route-item' + (i === 0 ? ' active' : '') + '" onclick="selectAltRoute(' + i + ')">'
              + '<span>' + (i === 0 ? '★ Fastest' : 'Alternative ' + i) + '</span>'
              + '<span style="font-weight:700;color:' + routeColors[i % routeColors.length] + '">' + d + ' mi · ~' + ts + '</span>'
              + '</div>';
          }).join('');
        }

        // Turn-by-turn steps
        var steps = primary.legs && primary.legs[0] && primary.legs[0].steps ? primary.legs[0].steps : [];
        var stepsEl = document.getElementById('routeStepsList');
        if (steps.length) {
          stepsEl.innerHTML = steps.map(function(step, i) {
            var maneuver = step.maneuver || {};
            var type = maneuver.type || 'continue';
            var mod  = maneuver.modifier || '';
            var icon = turnIcon(type, mod);
            var dist = step.distance < 1000
              ? Math.round(step.distance) + ' m'
              : (step.distance / 1609.34).toFixed(1) + ' mi';
            return '<div style="display:flex;align-items:flex-start;gap:8px;padding:7px 10px;border-bottom:1px solid var(--border);font-size:12px;">'
              + '<span style="font-size:16px;flex-shrink:0;">' + icon + '</span>'
              + '<div style="flex:1;min-width:0;">'
              + '<div style="font-weight:500;">' + esc(step.name || type + (mod ? ' ' + mod : '')) + '</div>'
              + '<div style="color:var(--muted-foreground);font-size:11px;">' + dist + '</div>'
              + '</div></div>';
          }).join('');
        } else {
          stepsEl.innerHTML = '<div style="padding:12px;font-size:13px;color:var(--muted-foreground);">No turn-by-turn steps available.</div>';
        }

        showToast(modeLabel + ': ' + distMi + ' mi · ~' + timeStr);
      })
      .catch(function() {
        document.getElementById('routeStepsList').innerHTML = '<div class="empty-state"><iconify-icon icon="lucide:wifi-off"></iconify-icon><p>Routing service unavailable. Check your connection.</p></div>';
        showToast('Routing unavailable. Check your connection.');
      });
  }

  function selectAltRoute(idx) {
    activeRouteIdx = idx;
    var routeColors = ['#0b6fff', '#10b981', '#f59e0b'];
    routeLayers.forEach(function(l, i) {
      l.setStyle({ weight: i === idx ? 6 : 4, opacity: i === idx ? 0.9 : 0.45, dashArray: i === idx ? null : '8 4' });
    });
    if (routeLayers[idx]) map.fitBounds(routeLayers[idx].getBounds(), { padding: [40, 40] });
    document.querySelectorAll('.alt-route-item').forEach(function(el, i) {
      el.classList.toggle('active', i === idx);
    });
  }

  function turnIcon(type, modifier) {
    var icons = {
      'turn-left':          '↰', 'turn-right':         '↱',
      'turn-sharp left':    '↩', 'turn-sharp right':   '↪',
      'turn-slight left':   '↖', 'turn-slight right':  '↗',
      'merge':              '⇒', 'fork':               '⑂',
      'ramp':               '↗', 'roundabout':         '⟳',
      'arrive':             '🏁', 'depart':             '🚀',
      'continue':           '↑', 'end of road':        '⊣',
    };
    var key = type + (modifier ? '-' + modifier : '');
    return icons[key] || icons[type] || '↑';
  }

  // Quick navigate from POI popup (simple 2-point route)
  function navigateTo(destLat, destLng, label) {
    switchTab('route');
    var destEl = document.getElementById('wpDest');
    destEl.value = label + ' (' + destLat.toFixed(5) + ',' + destLng.toFixed(5) + ')';
    destEl.dataset.lat = destLat; destEl.dataset.lng = destLng;

    if (userLat !== null && userLng !== null) {
      var originEl = document.getElementById('wpOrigin');
      originEl.value = 'My Location (' + userLat.toFixed(5) + ',' + userLng.toFixed(5) + ')';
      originEl.dataset.lat = userLat; originEl.dataset.lng = userLng;
      calculateRoute();
    } else {
      showToast('Set your origin or click "📍" to use your location, then click Get Route.');
    }
  }

  function clearRoute() {
    routeLayers.forEach(function(l) { map.removeLayer(l); });
    routeLayers = [];
    if (routeLayer) { map.removeLayer(routeLayer); routeLayer = null; }
    document.getElementById('routeInfo').textContent = '';
    document.getElementById('btnClearRoute').style.display = 'none';
    document.getElementById('routeClearBtn').style.display = 'none';
    document.getElementById('routeResultPanel').style.display = 'none';
    document.getElementById('altRouteList').style.display = 'none';
    document.getElementById('routeStepsList').innerHTML = '<div class="location-prompt"><iconify-icon icon="lucide:map" style="font-size:28px;"></iconify-icon><p>Enter origin &amp; destination, then click <strong>Get Route</strong>.</p></div>';
    showToast('Route cleared.');
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
      var dLat = safeLat(d.lat);
      var dLng = safeLng(d.lng);
      if (dLat === null || dLng === null) return;

      var isMe   = currentUser && d.user_id === currentUser.id;
      var status = d.location_status || 'offline';
      var marker = L.marker([dLat, dLng], { icon: driverIcon(status, isMe) });

      var updatedAgo = d.location_updated
        ? timeSince(new Date(d.location_updated.replace(' ', 'T'))) + ' ago'
        : 'unknown';

      marker.bindPopup(
        '<div class="popup-name">' + esc(d.name || '—') + (isMe ? ' <em style="font-size:11px;color:var(--primary);">(You)</em>' : '') + '</div>'
        + '<div style="margin-bottom:6px;">'
        + '<span class="popup-reg">' + esc(d.van_reg || '—') + '</span>'
        + ' &nbsp;·&nbsp; ' + esc((d.van_type || '').replace(/_/g, ' '))
        + '</div>'
        + '<div class="popup-updated">📍 ' + dLat.toFixed(5) + ', ' + dLng.toFixed(5) + '</div>'
        + '<div class="popup-updated">🕐 Updated ' + esc(updatedAgo) + '</div>'
        + '<div class="popup-updated" style="margin-top:4px;">Status: <strong>' + esc(status) + '</strong></div>'
        + (!isMe ? '<div style="margin-top:8px;"><button onclick="navigateTo(' + dLat + ',' + dLng + ',\'' + esc(d.name || 'Driver') + '\')" style="background:var(--primary);color:#fff;border:none;border-radius:6px;padding:5px 12px;font-size:12px;font-weight:600;cursor:pointer;width:100%;">🗺 Navigate Here</button></div>' : '')
      );

      marker.addTo(map);
      driverMarkers[d.id] = marker;
    });
  }

  // ═══════════════════════════════════════════════════════════
  //  TAB SWITCHING
  // ═══════════════════════════════════════════════════════════
  function switchTab(tab) {
    var tabs = { drivers: 'driversPanel', nearby: 'nearbyPanel', route: 'routePanel' };
    Object.keys(tabs).forEach(function(t) {
      var btn = document.getElementById('tab' + t.charAt(0).toUpperCase() + t.slice(1));
      var panel = document.getElementById(tabs[t]);
      var active = t === tab;
      if (btn)   btn.classList.toggle('active', active);
      if (panel) panel.style.display = active ? 'flex' : 'none';
    });
  }

  // ═══════════════════════════════════════════════════════════
  //  TRUCK FILTER
  // ═══════════════════════════════════════════════════════════
  function toggleTruckFilter() {
    truckFilter = !truckFilter;
    var chip = document.getElementById('truckFilterChip');
    chip.classList.toggle('active', truckFilter);
    if (lastPlaces.length) renderNearbyList(lastPlaces);
    showToast(truckFilter ? 'Showing truck-accessible locations only' : 'Showing all locations');
  }

  function isTruckAccessible(place) {
    if (!truckFilter) return true;
    var meta = place.meta || {};
    if (place.category === 'gas_station') return meta.truck_lanes > 0 || (meta.amenities || []).includes('truck_accessible');
    if (place.category === 'hotel')       return (meta.amenities || []).includes('truck_parking') || (meta.amenities || []).includes('large_vehicle_parking');
    if (place.category === 'restaurant')  return meta.trucker_friendly === true;
    if (place.category === 'tms_terminal') return true;
    return true; // libraries/theaters — no truck restriction, include them
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
    // Apply truck filter
    var visible = places.filter(isTruckAccessible);
    if (!visible.length) {
      list.innerHTML = truckFilter
        ? '<div class="empty-state"><iconify-icon icon="lucide:truck"></iconify-icon><p>No truck-accessible places found. Disable the Truck filter to see all places.</p></div>'
        : '<div class="empty-state"><iconify-icon icon="lucide:search-x"></iconify-icon><p>No places found in this radius. Try a larger radius.</p></div>';
      return;
    }

    var html = visible.map(function(p) {
      var cat  = POI_CATS[p.category] || { emoji: '📍', color: '#6b7280', label: p.category };
      var meta = p.meta || {};
      var pLat = safeLat(p.lat);
      var pLng = safeLng(p.lng);
      var detail = '';
      var truckBadge = '';
      if (p.category === 'gas_station') {
        detail = (meta.brand ? esc(meta.brand) + ' · ' : '') + (meta.diesel_price ? 'Diesel ' + esc(meta.diesel_price) : '') + (meta.open_247 ? ' · 24/7' : '');
        if (meta.truck_lanes > 0) truckBadge = '<span style="font-size:10px;background:#fef3c7;color:#92400e;border:1px solid #fde68a;border-radius:6px;padding:1px 5px;font-weight:700;">🚛 ' + meta.truck_lanes + ' truck lanes</span>';
      }
      if (p.category === 'hotel')         detail = (meta.brand ? esc(meta.brand) : '') + (meta.price_range ? ' · ' + esc(meta.price_range) : '') + (meta.star_rating ? ' · ' + '★'.repeat(meta.star_rating) : '');
      if (p.category === 'restaurant')    detail = (meta.cuisine ? esc(meta.cuisine) : '') + (meta.trucker_friendly ? ' · 🚚 Trucker-friendly' : '');
      if (p.category === 'library')       detail = (meta.wifi ? 'WiFi · ' : '') + (meta.hours ? esc(meta.hours.split(',')[0]) : '');
      if (p.category === 'movie_theater') detail = (meta.screens ? meta.screens + ' screens' : '') + (meta.accessibility ? ' · Accessible' : '');
      if (p.category === 'tms_terminal')  detail = (meta.carrier ? esc(meta.carrier) : '') + (meta.open_247 ? ' · 24/7' : '') + (meta.dock_doors ? ' · ' + meta.dock_doors + ' doors' : '');

      return '<div class="poi-item" data-id="' + esc(p.id) + '" onclick="focusPoi(\'' + esc(p.id) + '\')">'
        + '<div class="poi-emoji">' + cat.emoji + '</div>'
        + '<div class="poi-info">'
        + '<div class="poi-name">' + esc(p.name) + '</div>'
        + '<div class="poi-addr">' + esc(p.address) + '</div>'
        + (detail ? '<div class="poi-addr" style="margin-top:2px;">' + detail + '</div>' : '')
        + (truckBadge ? '<div style="margin-top:3px;">' + truckBadge + '</div>' : '')
        + '</div>'
        + '<div style="display:flex;flex-direction:column;align-items:flex-end;gap:3px;flex-shrink:0;">'
        + '<span class="poi-dist">' + p.distance + ' mi</span>'
        + '<span class="poi-badge" style="background:' + cat.color + '22;color:' + cat.color + ';border:1px solid ' + cat.color + '44;">' + esc(cat.label) + '</span>'
        + (pLat !== null && pLng !== null ? '<button onclick="event.stopPropagation();navigateTo(' + pLat + ',' + pLng + ',\'' + esc(p.name) + '\')" style="background:var(--primary);color:#fff;border:none;border-radius:5px;padding:3px 8px;font-size:10px;font-weight:600;cursor:pointer;margin-top:2px;">🗺 Go</button>' : '')
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
        var pLat = safeLat(p.lat);
        var pLng = safeLng(p.lng);
        if (pLat === null || pLng === null) return;
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
        popupLines.push('<div style="margin-top:8px;"><button onclick="navigateTo(' + pLat + ',' + pLng + ',\'' + esc(p.name) + '\')" style="background:var(--primary);color:#fff;border:none;border-radius:6px;padding:5px 12px;font-size:12px;font-weight:600;cursor:pointer;width:100%;">🗺 Navigate Here</button></div>');

        var marker = L.marker([pLat, pLng], {
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
      places.forEach(function(p) {
        var pLat = safeLat(p.lat);
        var pLng = safeLng(p.lng);
        if (pLat !== null && pLng !== null) bounds.push([pLat, pLng]);
      });
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
