<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <link rel="icon" href="favicon.svg" type="image/svg+xml" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Loadboard — Fastrux</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="shared.css" />
  <!-- Leaflet map -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV/XN/WLcE=" crossorigin=""></script>
  <script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>

  <style>
    /* ── Layout ───────────────────────────────────────────── */
    body { background: var(--muted); }

    .dash-header {
      background: var(--card);
      border-bottom: 1px solid var(--border);
      position: sticky; top: 0; z-index: 200;
    }
    .dash-header-inner {
      display: flex; align-items: center;
      justify-content: space-between; height: 64px;
    }
    .dash-brand {
      display: flex; align-items: center; gap: 10px;
      font-size: 18px; font-weight: 800; color: var(--primary);
      text-decoration: none;
    }
    .dash-brand span { color: var(--foreground); font-weight: 400; font-size: 14px; }

    /* ── Top-level tabs ───────────────────────────────────── */
    .page-tabs {
      display: flex; gap: 0;
      border-bottom: 2px solid var(--border);
      margin-bottom: 28px;
    }
    .page-tab {
      padding: 13px 22px;
      font-size: 14px; font-weight: 600;
      color: var(--muted-foreground);
      cursor: pointer;
      border-bottom: 3px solid transparent;
      margin-bottom: -2px;
      transition: color .2s, border-color .2s;
      display: flex; align-items: center; gap: 8px;
      white-space: nowrap;
      background: none; border-top: none; border-left: none; border-right: none;
      font-family: var(--font-family-body);
    }
    .page-tab:hover { color: var(--primary); }
    .page-tab.active {
      color: var(--primary);
      border-bottom-color: var(--primary);
    }
    .tab-badge {
      background: var(--primary);
      color: #fff;
      border-radius: 20px;
      font-size: 11px;
      font-weight: 700;
      padding: 2px 7px;
      min-width: 20px;
      text-align: center;
    }
    .tab-panel { display: none; }
    .tab-panel.active { display: block; }

    /* ── Filter bar (Find Loads) ──────────────────────────── */
    .filter-bar {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius-xl);
      padding: 20px 24px;
      margin-bottom: 20px;
      box-shadow: var(--shadow-sm);
    }
    .filter-bar-title {
      font-size: 15px; font-weight: 700; margin-bottom: 16px;
      display: flex; align-items: center; gap: 8px;
      color: var(--foreground);
    }
    .filter-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
      gap: 12px;
    }
    .filter-group label {
      display: block; font-size: 12px; font-weight: 600;
      margin-bottom: 5px; color: var(--muted-foreground);
      text-transform: uppercase; letter-spacing: .04em;
    }
    .filter-group input,
    .filter-group select {
      width: 100%; padding: 9px 12px;
      border: 1.5px solid var(--border);
      border-radius: var(--radius-md);
      font-family: var(--font-family-body); font-size: 13px;
      background: var(--input); color: var(--foreground);
      transition: border-color .2s; outline: none;
    }
    .filter-group input:focus,
    .filter-group select:focus { border-color: var(--primary); }
    .filter-row-actions {
      display: flex; align-items: center; gap: 10px;
      margin-top: 16px; flex-wrap: wrap;
    }
    .active-filters {
      display: flex; flex-wrap: wrap; gap: 8px;
      margin-top: 12px;
    }
    .filter-chip {
      display: flex; align-items: center; gap: 5px;
      background: var(--secondary); color: var(--primary);
      border: 1px solid var(--primary);
      border-radius: 20px; padding: 4px 10px;
      font-size: 12px; font-weight: 600;
    }
    .filter-chip button {
      background: none; border: none; cursor: pointer;
      color: var(--primary); padding: 0; margin-left: 2px;
      display: flex; align-items: center;
    }

    /* ── Load results ─────────────────────────────────────── */
    .results-header {
      display: flex; align-items: center;
      justify-content: space-between; margin-bottom: 14px;
      flex-wrap: wrap; gap: 10px;
    }
    .results-count { font-size: 14px; color: var(--muted-foreground); }
    .results-count strong { color: var(--foreground); font-weight: 700; }

    .loads-table-wrap {
      background: var(--card); border: 1px solid var(--border);
      border-radius: var(--radius-xl); overflow: hidden;
      box-shadow: var(--shadow-sm);
    }
    .loads-table {
      width: 100%; border-collapse: collapse; font-size: 13px;
    }
    .loads-table th {
      padding: 11px 14px; text-align: left;
      font-size: 11px; font-weight: 700;
      text-transform: uppercase; letter-spacing: .05em;
      color: var(--muted-foreground);
      border-bottom: 1px solid var(--border);
      background: var(--muted); white-space: nowrap;
    }
    .loads-table td {
      padding: 12px 14px; vertical-align: middle;
      border-bottom: 1px solid var(--border);
    }
    .loads-table tr:last-child td { border-bottom: none; }
    .loads-table tbody tr:hover { background: var(--secondary); }
    .load-id-cell {
      font-family: monospace; font-size: 12px;
      font-weight: 700; color: var(--primary);
    }
    .route-cell { font-weight: 600; }
    .route-arrow { color: var(--muted-foreground); font-size: 12px; margin: 0 4px; }
    .badge {
      display: inline-flex; align-items: center;
      padding: 3px 8px; border-radius: 20px;
      font-size: 11px; font-weight: 700;
      white-space: nowrap;
    }
    .badge-ftl   { background: #dbeafe; color: #1d4ed8; }
    .badge-ltl   { background: #fef9c3; color: #d97706; }
    .badge-partial { background: #ede9fe; color: #7c3aed; }
    .badge-hazmat { background: #fef2f2; color: #b91c1c; }
    .badge-equip  { background: var(--muted); color: var(--muted-foreground); }
    .rate-cell { font-weight: 700; color: var(--success); }
    .actions-cell { display: flex; gap: 6px; }
    .btn-icon {
      display: inline-flex; align-items: center; justify-content: center;
      width: 32px; height: 32px;
      border: 1.5px solid var(--border); border-radius: var(--radius-md);
      background: var(--card); cursor: pointer;
      color: var(--muted-foreground); transition: all .2s;
      font-size: 14px;
    }
    .btn-icon:hover { border-color: var(--primary); color: var(--primary); background: var(--secondary); }
    .btn-icon.active-save { border-color: #d97706; color: #d97706; background: #fff7e6; }
    .btn-icon.active-hide { border-color: var(--muted-foreground); color: var(--muted-foreground); background: var(--muted); }

    /* ── Pagination ───────────────────────────────────────── */
    .pagination {
      display: flex; align-items: center;
      justify-content: center; gap: 6px;
      padding: 16px;
      border-top: 1px solid var(--border);
    }
    .page-btn {
      display: inline-flex; align-items: center; justify-content: center;
      min-width: 34px; height: 34px; padding: 0 8px;
      border: 1.5px solid var(--border); border-radius: var(--radius-md);
      background: var(--card); cursor: pointer;
      font-size: 13px; font-weight: 500;
      color: var(--foreground); transition: all .2s;
    }
    .page-btn:hover:not(:disabled) { border-color: var(--primary); color: var(--primary); }
    .page-btn.active { background: var(--primary); color: #fff; border-color: var(--primary); }
    .page-btn:disabled { opacity: .4; cursor: not-allowed; }

    /* ── Density Map ──────────────────────────────────────── */
    #densityMap {
      height: 440px; border-radius: var(--radius-xl) var(--radius-xl) 0 0;
      z-index: 1; border: 1px solid var(--border);
      border-bottom: none;
    }
    .map-legend {
      background: var(--card); border: 1px solid var(--border);
      border-top: none; border-radius: 0 0 var(--radius-xl) var(--radius-xl);
      padding: 12px 20px;
      display: flex; align-items: center; gap: 16px;
      flex-wrap: wrap; font-size: 12px; color: var(--muted-foreground);
      box-shadow: var(--shadow-sm);
      margin-bottom: 20px;
    }
    .legend-item { display: flex; align-items: center; gap: 6px; }
    .legend-dot {
      width: 14px; height: 14px; border-radius: 50%;
      display: inline-block;
    }

    .density-table-wrap {
      background: var(--card); border: 1px solid var(--border);
      border-radius: var(--radius-xl); overflow: hidden;
      box-shadow: var(--shadow-sm); margin-top: 20px;
    }
    .density-table {
      width: 100%; border-collapse: collapse; font-size: 13px;
    }
    .density-table th {
      padding: 11px 16px; text-align: left;
      font-size: 11px; font-weight: 700;
      text-transform: uppercase; letter-spacing: .05em;
      color: var(--muted-foreground);
      border-bottom: 1px solid var(--border);
      background: var(--muted);
    }
    .density-table td {
      padding: 11px 16px; border-bottom: 1px solid var(--border);
      vertical-align: middle;
    }
    .density-table tr:last-child td { border-bottom: none; }
    .density-bar {
      height: 8px; border-radius: 4px; background: var(--primary);
      min-width: 8px; max-width: 200px;
    }

    /* ── My Loads ─────────────────────────────────────────── */
    .my-loads-sections { display: flex; flex-direction: column; gap: 24px; }
    .my-loads-section {
      background: var(--card); border: 1px solid var(--border);
      border-radius: var(--radius-xl); overflow: hidden;
      box-shadow: var(--shadow-sm);
    }
    .section-header {
      display: flex; align-items: center;
      justify-content: space-between;
      padding: 14px 20px; border-bottom: 1px solid var(--border);
      background: var(--card); cursor: pointer;
    }
    .section-title {
      font-size: 14px; font-weight: 700;
      display: flex; align-items: center; gap: 8px;
    }
    .section-count {
      background: var(--muted); color: var(--muted-foreground);
      border-radius: 20px; font-size: 11px; font-weight: 700;
      padding: 2px 8px;
    }
    .section-body { padding: 0; }
    .section-body.collapsed { display: none; }
    .my-load-row {
      display: flex; align-items: center; gap: 12px;
      padding: 13px 20px;
      border-bottom: 1px solid var(--border);
      transition: background .15s;
    }
    .my-load-row:last-child { border-bottom: none; }
    .my-load-row:hover { background: var(--secondary); }
    .ml-route { flex: 1; min-width: 0; }
    .ml-route .origin-dest {
      font-weight: 600; font-size: 13px;
      white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .ml-route .meta { font-size: 12px; color: var(--muted-foreground); margin-top: 2px; }
    .ml-rate { font-weight: 700; color: var(--success); font-size: 14px; white-space: nowrap; }
    .ml-actions { display: flex; gap: 6px; flex-shrink: 0; }
    .ml-status-badge {
      padding: 3px 9px; border-radius: 20px;
      font-size: 11px; font-weight: 700;
      white-space: nowrap;
    }
    .status-viewed       { background: #f3f4f6; color: #6b7280; }
    .status-saved        { background: #fff7e6; color: #d97706; }
    .status-hidden       { background: var(--muted); color: var(--muted-foreground); }
    .status-contacted    { background: #dbeafe; color: #1d4ed8; }
    .status-booked       { background: #dcfce7; color: #16a34a; }
    .status-pickup_complete { background: #e0f2fe; color: #0369a1; }
    .status-delivered    { background: #f0fdf4; color: #15803d; }
    .status-cancelled    { background: #fef2f2; color: #b91c1c; }
    .status-rejected     { background: #fff1f2; color: #be123c; }

    /* ── Panel / common ───────────────────────────────────── */
    .panel {
      background: var(--card); border: 1px solid var(--border);
      border-radius: var(--radius-xl); overflow: hidden; box-shadow: var(--shadow-sm);
    }
    .panel-header {
      display: flex; align-items: center;
      justify-content: space-between;
      padding: 14px 20px; border-bottom: 1px solid var(--border);
    }
    .panel-title {
      font-size: 15px; font-weight: 700;
      display: flex; align-items: center; gap: 8px;
    }

    /* ── Empty state ──────────────────────────────────────── */
    .empty-state {
      display: flex; flex-direction: column;
      align-items: center; justify-content: center;
      padding: 48px 20px; text-align: center;
      color: var(--muted-foreground); gap: 12px;
    }
    .empty-state iconify-icon { font-size: 44px; opacity: .4; }
    .empty-state p { font-size: 14px; }

    /* ── Load detail drawer ───────────────────────────────── */
    .drawer-backdrop {
      position: fixed; inset: 0;
      background: rgba(0,0,0,.35);
      z-index: 400; display: none;
    }
    .drawer-backdrop.open { display: flex; justify-content: flex-end; }
    .drawer {
      width: 420px; max-width: 100vw;
      height: 100vh; overflow-y: auto;
      background: var(--card);
      box-shadow: -4px 0 32px rgba(0,0,0,.15);
      display: flex; flex-direction: column;
    }
    .drawer-header {
      padding: 20px 24px 16px;
      border-bottom: 1px solid var(--border);
      display: flex; align-items: flex-start;
      justify-content: space-between; gap: 12px;
      position: sticky; top: 0; background: var(--card); z-index: 10;
    }
    .drawer-body { padding: 20px 24px; flex: 1; }
    .drawer-footer {
      padding: 16px 24px;
      border-top: 1px solid var(--border);
      background: var(--muted);
      display: flex; gap: 10px; flex-wrap: wrap;
    }
    .detail-row {
      display: flex; gap: 16px;
      margin-bottom: 16px; align-items: flex-start;
    }
    .detail-icon {
      width: 36px; height: 36px; min-width: 36px;
      border-radius: var(--radius-md);
      background: var(--secondary);
      display: flex; align-items: center; justify-content: center;
      font-size: 16px; color: var(--primary);
    }
    .detail-content { flex: 1; }
    .detail-label { font-size: 11px; font-weight: 700; color: var(--muted-foreground); text-transform: uppercase; letter-spacing: .04em; margin-bottom: 2px; }
    .detail-value { font-size: 14px; font-weight: 500; color: var(--foreground); }

    /* ── Toast ────────────────────────────────────────────── */
    .toast {
      position: fixed; bottom: 24px; right: 24px;
      background: var(--foreground); color: #fff;
      padding: 12px 20px; border-radius: var(--radius-lg);
      font-size: 14px; font-weight: 500; z-index: 9999;
      opacity: 0; transform: translateY(8px);
      transition: opacity .3s, transform .3s;
      max-width: 360px; pointer-events: none;
    }
    .toast.show { opacity: 1; transform: translateY(0); }
    .toast.toast-success { background: var(--success); }
    .toast.toast-error   { background: var(--destructive); }

    /* ── Loading spinner ──────────────────────────────────── */
    .loading-overlay {
      display: flex; align-items: center;
      justify-content: center; padding: 48px;
      color: var(--muted-foreground); gap: 10px; font-size: 14px;
    }
    .loading-overlay iconify-icon {
      animation: spin 1s linear infinite; font-size: 20px;
    }

    /* ── Density layout ───────────────────────────────────── */
    .density-layout {
      display: grid;
      grid-template-columns: 1fr 320px;
      gap: 20px;
      align-items: start;
    }

    /* ── Responsive ───────────────────────────────────────── */
    @media (max-width: 1024px) {
      .density-layout { grid-template-columns: 1fr; }
    }
    @media (max-width: 768px) {
      .filter-grid { grid-template-columns: 1fr 1fr; }
      .loads-table th:nth-child(n+5),
      .loads-table td:nth-child(n+5) { display: none; }
      #densityMap { height: 300px; }
      .drawer { width: 100vw; }
    }
    @media (max-width: 480px) {
      .filter-grid { grid-template-columns: 1fr; }
      .loads-table th:nth-child(n+4),
      .loads-table td:nth-child(n+4) { display: none; }
      .page-tab { padding: 10px 14px; font-size: 13px; }
    }

    /* Leaflet popup */
    .leaflet-popup-content { font-family: 'Inter', sans-serif; font-size: 13px; min-width: 180px; }
    .popup-title { font-weight: 700; font-size: 14px; margin-bottom: 6px; color: var(--foreground); }
    .popup-count { font-size: 28px; font-weight: 800; color: var(--primary); line-height: 1; margin-bottom: 4px; }
    .popup-sub   { font-size: 12px; color: var(--muted-foreground); }
    .popup-equip { margin-top: 8px; display: flex; flex-wrap: wrap; gap: 4px; }
    .popup-equip span { background: var(--muted); border-radius: 4px; padding: 2px 7px; font-size: 11px; }
    .popup-btn {
      margin-top: 12px; width: 100%; padding: 8px;
      background: var(--primary); color: #fff; border: none;
      border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer;
    }
    .popup-btn:hover { opacity: .9; }
  </style>
</head>
<body>

  <!-- ── Header ───────────────────────────────────────────── -->
  <header class="dash-header">
    <div class="container dash-header-inner">
      <a href="index" class="dash-brand">
        <iconify-icon icon="lucide:truck" style="font-size:24px"></iconify-icon>
        Fastrux <span>&nbsp;/ Loadboard</span>
      </a>
      <div style="display:flex;align-items:center;gap:10px;">
        <button class="btn btn-outline" id="refreshBtn" style="padding:8px 14px;font-size:13px;" title="Refresh">
          <iconify-icon icon="lucide:refresh-cw" style="font-size:15px;margin-right:6px"></iconify-icon>
          Refresh
        </button>
        <a href="driver-dashboard" class="btn btn-outline" style="padding:8px 14px;font-size:13px;">
          <iconify-icon icon="lucide:layout-dashboard" style="font-size:15px;margin-right:6px"></iconify-icon>
          Dashboard
        </a>
        <a href="index" class="btn btn-primary" style="padding:8px 14px;font-size:13px;">
          <iconify-icon icon="lucide:home" style="font-size:15px;margin-right:6px"></iconify-icon>
          Main Site
        </a>
      </div>
    </div>
  </header>

  <!-- ── Main ─────────────────────────────────────────────── -->
  <div class="container" style="padding-top:28px;padding-bottom:48px;">

    <!-- Page header -->
    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
      <div>
        <h1 style="font-size:26px;font-weight:800;margin-bottom:4px;">Loadboard</h1>
        <p style="color:var(--muted-foreground);font-size:14px;">
          Find available loads, check regional availability, and manage your load pipeline.
        </p>
      </div>
      <div id="userWelcome" style="text-align:right;display:none;">
        <div style="font-size:13px;color:var(--muted-foreground);">Logged in as</div>
        <div style="font-size:15px;font-weight:700;" id="userNameDisplay"></div>
      </div>
    </div>

    <!-- Top-level tabs -->
    <div class="page-tabs" role="tablist">
      <button class="page-tab active" role="tab" data-tab="find-loads">
        <iconify-icon icon="lucide:search"></iconify-icon>
        Find Loads
      </button>
      <button class="page-tab" role="tab" data-tab="availability">
        <iconify-icon icon="lucide:map"></iconify-icon>
        Load Availability
      </button>
      <button class="page-tab" role="tab" data-tab="my-loads">
        <iconify-icon icon="lucide:briefcase"></iconify-icon>
        My Loads
        <span class="tab-badge" id="myLoadsBadge" style="display:none;">0</span>
      </button>
    </div>

    <!-- ══════════════════════════════════════════════════════
         TAB 1: FIND LOADS
    ══════════════════════════════════════════════════════ -->
    <div class="tab-panel active" id="tab-find-loads">

      <!-- Filter bar -->
      <div class="filter-bar">
        <div class="filter-bar-title">
          <iconify-icon icon="lucide:sliders-horizontal" style="color:var(--primary);font-size:18px"></iconify-icon>
          Search Filters
          <span style="font-size:12px;font-weight:400;color:var(--muted-foreground);margin-left:4px;">(Set multiple filters to narrow results)</span>
        </div>

        <div class="filter-grid">
          <div class="filter-group">
            <label>Origin City</label>
            <input type="text" id="fOriginCity" placeholder="e.g. Chicago" />
          </div>
          <div class="filter-group">
            <label>Origin State</label>
            <input type="text" id="fOriginState" placeholder="e.g. IL" maxlength="2" />
          </div>
          <div class="filter-group">
            <label>Destination City</label>
            <input type="text" id="fDestCity" placeholder="e.g. Dallas" />
          </div>
          <div class="filter-group">
            <label>Destination State</label>
            <input type="text" id="fDestState" placeholder="e.g. TX" maxlength="2" />
          </div>
          <div class="filter-group">
            <label>Equipment Type</label>
            <select id="fEquipment">
              <option value="">All Equipment</option>
              <option>Dry Van</option>
              <option>Reefer</option>
              <option>Flatbed</option>
              <option>Step Deck</option>
              <option>Tanker</option>
              <option>Lowboy</option>
              <option>Power Only</option>
              <option>Intermodal</option>
            </select>
          </div>
          <div class="filter-group">
            <label>Load Type</label>
            <select id="fLoadType">
              <option value="">All Types</option>
              <option value="FTL">Full Truckload (FTL)</option>
              <option value="LTL">Less than Truckload (LTL)</option>
              <option value="Partial">Partial</option>
            </select>
          </div>
          <div class="filter-group">
            <label>Pickup Date From</label>
            <input type="date" id="fPickupFrom" />
          </div>
          <div class="filter-group">
            <label>Pickup Date To</label>
            <input type="date" id="fPickupTo" />
          </div>
          <div class="filter-group">
            <label>Min Weight (lbs)</label>
            <input type="number" id="fMinWeight" placeholder="e.g. 10000" min="0" step="1000" />
          </div>
          <div class="filter-group">
            <label>Max Weight (lbs)</label>
            <input type="number" id="fMaxWeight" placeholder="e.g. 44000" min="0" step="1000" />
          </div>
          <div class="filter-group">
            <label>Min Distance (mi)</label>
            <input type="number" id="fMinDist" placeholder="e.g. 100" min="0" step="50" />
          </div>
          <div class="filter-group">
            <label>Max Distance (mi)</label>
            <input type="number" id="fMaxDist" placeholder="e.g. 1500" min="0" step="50" />
          </div>
          <div class="filter-group">
            <label>Min Rate/Mile ($)</label>
            <input type="number" id="fMinRate" placeholder="e.g. 2.00" min="0" step="0.25" />
          </div>
          <div class="filter-group">
            <label>Commodity</label>
            <input type="text" id="fCommodity" placeholder="e.g. Electronics" />
          </div>
          <div class="filter-group">
            <label>Hazmat</label>
            <select id="fHazmat">
              <option value="">Any</option>
              <option value="true">Hazmat Only</option>
              <option value="false">No Hazmat</option>
            </select>
          </div>
        </div>

        <div class="filter-row-actions">
          <button class="btn btn-primary" id="searchBtn" style="padding:10px 20px;font-size:14px;">
            <iconify-icon icon="lucide:search" style="font-size:15px;margin-right:7px"></iconify-icon>
            Search Loads
          </button>
          <button class="btn btn-outline" id="clearFiltersBtn" style="padding:10px 16px;font-size:14px;">
            <iconify-icon icon="lucide:x" style="font-size:15px;margin-right:7px"></iconify-icon>
            Clear Filters
          </button>
          <div id="activeFiltersContainer" class="active-filters"></div>
        </div>
      </div>

      <!-- Results -->
      <div class="results-header">
        <div class="results-count">
          Showing <strong id="resultsCount">—</strong> loads
        </div>
        <div style="display:flex;align-items:center;gap:10px;">
          <label style="font-size:13px;color:var(--muted-foreground);">Sort by:</label>
          <select id="sortSelect" style="padding:7px 12px;border:1.5px solid var(--border);border-radius:var(--radius-md);font-size:13px;background:var(--input);">
            <option value="posted">Newest Posted</option>
            <option value="pickup">Pickup Date</option>
            <option value="rate_desc">Rate (High→Low)</option>
            <option value="rate_asc">Rate (Low→High)</option>
            <option value="dist_desc">Distance (Long→Short)</option>
            <option value="dist_asc">Distance (Short→Long)</option>
          </select>
        </div>
      </div>

      <div class="loads-table-wrap">
        <div id="loadsTableBody">
          <div class="loading-overlay">
            <iconify-icon icon="lucide:loader-circle"></iconify-icon>
            Loading loads…
          </div>
        </div>
        <div class="pagination" id="paginationBar" style="display:none;"></div>
      </div>

    </div><!-- /tab-find-loads -->

    <!-- ══════════════════════════════════════════════════════
         TAB 2: LOAD AVAILABILITY (DENSITY MAP)
    ══════════════════════════════════════════════════════ -->
    <div class="tab-panel" id="tab-availability">

      <div class="density-layout">
        <!-- Map + legend + table -->
        <div>
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;flex-wrap:wrap;gap:10px;">
            <div>
              <h2 style="font-size:18px;font-weight:700;margin-bottom:4px;">Load Density Map</h2>
              <p style="font-size:13px;color:var(--muted-foreground);">
                Circle size and color indicate the concentration of available loads by origin region. Click a circle for details.
              </p>
            </div>
            <button class="btn btn-outline" id="refreshMapBtn" style="padding:8px 14px;font-size:13px;">
              <iconify-icon icon="lucide:refresh-cw" style="font-size:15px;margin-right:6px"></iconify-icon>
              Refresh Map
            </button>
          </div>

          <div id="densityMap"></div>
          <div class="map-legend">
            <strong style="color:var(--foreground);">Density:</strong>
            <div class="legend-item"><span class="legend-dot" style="background:#16a34a;"></span> Low (1–3)</div>
            <div class="legend-item"><span class="legend-dot" style="background:#d97706;"></span> Medium (4–7)</div>
            <div class="legend-item"><span class="legend-dot" style="background:#e02424;"></span> High (8+)</div>
            <span style="margin-left:auto;color:var(--muted-foreground);">Click a circle to see region details</span>
          </div>

          <!-- Density table -->
          <div class="density-table-wrap">
            <div style="padding:14px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:8px;">
              <iconify-icon icon="lucide:table-2" style="font-size:16px;color:var(--primary)"></iconify-icon>
              <span style="font-size:14px;font-weight:700;">Loads by Region</span>
            </div>
            <div id="densityTableBody">
              <div class="loading-overlay">
                <iconify-icon icon="lucide:loader-circle"></iconify-icon>
                Loading density data…
              </div>
            </div>
          </div>
        </div>

        <!-- Density list (sidebar) -->
        <div>
          <div class="panel">
            <div class="panel-header">
              <div class="panel-title">
                <iconify-icon icon="lucide:bar-chart-2" style="font-size:16px;color:var(--primary)"></iconify-icon>
                Density List
              </div>
            </div>
            <div id="densityList" style="max-height:700px;overflow-y:auto;">
              <div class="loading-overlay">
                <iconify-icon icon="lucide:loader-circle"></iconify-icon>
                Loading…
              </div>
            </div>
          </div>
        </div>
      </div>

    </div><!-- /tab-availability -->

    <!-- ══════════════════════════════════════════════════════
         TAB 3: MY LOADS
    ══════════════════════════════════════════════════════ -->
    <div class="tab-panel" id="tab-my-loads">

      <div id="myLoadsAuthWarn" style="display:none;">
        <div class="empty-state">
          <iconify-icon icon="lucide:lock"></iconify-icon>
          <p>Please <a href="login" style="color:var(--primary);font-weight:600;">log in</a> to view your saved loads.</p>
        </div>
      </div>

      <div id="myLoadsSections" style="display:none;">
        <!-- Recently Viewed -->
        <div class="my-loads-section" id="sec-recently-viewed">
          <div class="section-header" onclick="toggleSection(this)">
            <div class="section-title">
              <iconify-icon icon="lucide:clock" style="color:#6b7280"></iconify-icon>
              Recently Viewed
              <span class="section-count" id="cnt-viewed">0</span>
            </div>
            <iconify-icon icon="lucide:chevron-down" class="section-chevron"></iconify-icon>
          </div>
          <div class="section-body" id="body-viewed"></div>
        </div>

        <!-- Hidden -->
        <div class="my-loads-section" id="sec-hidden" style="margin-top:16px;">
          <div class="section-header" onclick="toggleSection(this)">
            <div class="section-title">
              <iconify-icon icon="lucide:eye-off" style="color:#6b7280"></iconify-icon>
              Hidden Loads
              <span class="section-count" id="cnt-hidden">0</span>
            </div>
            <iconify-icon icon="lucide:chevron-down" class="section-chevron"></iconify-icon>
          </div>
          <div class="section-body collapsed" id="body-hidden"></div>
        </div>

        <!-- Potential Loads group -->
        <div style="margin-top:24px;">
          <h3 style="font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted-foreground);margin-bottom:10px;">
            Potential Loads
          </h3>
          <!-- Saved -->
          <div class="my-loads-section" style="margin-bottom:12px;" id="sec-saved">
            <div class="section-header" onclick="toggleSection(this)">
              <div class="section-title">
                <iconify-icon icon="lucide:bookmark" style="color:#d97706"></iconify-icon>
                Saved
                <span class="section-count" id="cnt-saved">0</span>
              </div>
              <iconify-icon icon="lucide:chevron-down" class="section-chevron"></iconify-icon>
            </div>
            <div class="section-body collapsed" id="body-saved"></div>
          </div>
          <!-- Contacted -->
          <div class="my-loads-section" id="sec-contacted">
            <div class="section-header" onclick="toggleSection(this)">
              <div class="section-title">
                <iconify-icon icon="lucide:phone-call" style="color:#1d4ed8"></iconify-icon>
                Contacted
                <span class="section-count" id="cnt-contacted">0</span>
              </div>
              <iconify-icon icon="lucide:chevron-down" class="section-chevron"></iconify-icon>
            </div>
            <div class="section-body collapsed" id="body-contacted"></div>
          </div>
        </div>

        <!-- Working Loads group -->
        <div style="margin-top:24px;">
          <h3 style="font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted-foreground);margin-bottom:10px;">
            Working Loads
          </h3>
          <!-- Booked -->
          <div class="my-loads-section" style="margin-bottom:12px;" id="sec-booked">
            <div class="section-header" onclick="toggleSection(this)">
              <div class="section-title">
                <iconify-icon icon="lucide:calendar-check" style="color:#16a34a"></iconify-icon>
                Booked
                <span class="section-count" id="cnt-booked">0</span>
              </div>
              <iconify-icon icon="lucide:chevron-down" class="section-chevron"></iconify-icon>
            </div>
            <div class="section-body collapsed" id="body-booked"></div>
          </div>
          <!-- Pickup Complete -->
          <div class="my-loads-section" id="sec-pickup_complete">
            <div class="section-header" onclick="toggleSection(this)">
              <div class="section-title">
                <iconify-icon icon="lucide:package-check" style="color:#0369a1"></iconify-icon>
                Pickup Complete
                <span class="section-count" id="cnt-pickup_complete">0</span>
              </div>
              <iconify-icon icon="lucide:chevron-down" class="section-chevron"></iconify-icon>
            </div>
            <div class="section-body collapsed" id="body-pickup_complete"></div>
          </div>
        </div>

        <!-- Completed Loads group -->
        <div style="margin-top:24px;">
          <h3 style="font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted-foreground);margin-bottom:10px;">
            Completed Loads
          </h3>
          <!-- Delivered -->
          <div class="my-loads-section" style="margin-bottom:12px;" id="sec-delivered">
            <div class="section-header" onclick="toggleSection(this)">
              <div class="section-title">
                <iconify-icon icon="lucide:check-circle-2" style="color:#15803d"></iconify-icon>
                Delivered
                <span class="section-count" id="cnt-delivered">0</span>
              </div>
              <iconify-icon icon="lucide:chevron-down" class="section-chevron"></iconify-icon>
            </div>
            <div class="section-body collapsed" id="body-delivered"></div>
          </div>
          <!-- Cancelled -->
          <div class="my-loads-section" style="margin-bottom:12px;" id="sec-cancelled">
            <div class="section-header" onclick="toggleSection(this)">
              <div class="section-title">
                <iconify-icon icon="lucide:x-circle" style="color:#b91c1c"></iconify-icon>
                Cancelled
                <span class="section-count" id="cnt-cancelled">0</span>
              </div>
              <iconify-icon icon="lucide:chevron-down" class="section-chevron"></iconify-icon>
            </div>
            <div class="section-body collapsed" id="body-cancelled"></div>
          </div>
          <!-- Rejected -->
          <div class="my-loads-section" id="sec-rejected">
            <div class="section-header" onclick="toggleSection(this)">
              <div class="section-title">
                <iconify-icon icon="lucide:ban" style="color:#be123c"></iconify-icon>
                Rejected
                <span class="section-count" id="cnt-rejected">0</span>
              </div>
              <iconify-icon icon="lucide:chevron-down" class="section-chevron"></iconify-icon>
            </div>
            <div class="section-body collapsed" id="body-rejected"></div>
          </div>
        </div>

      </div><!-- /myLoadsSections -->

    </div><!-- /tab-my-loads -->

  </div><!-- /container -->

  <!-- ── Load Detail Drawer ───────────────────────────────── -->
  <div class="drawer-backdrop" id="drawerBackdrop">
    <div class="drawer" id="drawer">
      <div class="drawer-header">
        <div>
          <div id="drawerLoadId" style="font-family:monospace;font-size:12px;font-weight:700;color:var(--primary);margin-bottom:4px;"></div>
          <div id="drawerTitle" style="font-size:18px;font-weight:700;"></div>
        </div>
        <button class="btn-icon" id="closeDrawer" style="flex-shrink:0;width:36px;height:36px;">
          <iconify-icon icon="lucide:x" style="font-size:18px"></iconify-icon>
        </button>
      </div>
      <div class="drawer-body" id="drawerBody"></div>
      <div class="drawer-footer" id="drawerFooter"></div>
    </div>
  </div>

  <!-- ── Toast ─────────────────────────────────────────────── -->
  <div class="toast" id="toast"></div>

  <script>
  // ═══════════════════════════════════════════════════════════
  //  UTILITIES
  // ═══════════════════════════════════════════════════════════
  function escHtml(str) {
    return String(str || '')
      .replace(/&/g,'&amp;').replace(/</g,'&lt;')
      .replace(/>/g,'&gt;').replace(/"/g,'&quot;')
      .replace(/'/g,'&#39;');
  }
  function fmtMoney(n) {
    return '$' + Number(n).toLocaleString('en-US', {minimumFractionDigits:0, maximumFractionDigits:0});
  }
  function fmtWeight(lbs) {
    return Number(lbs).toLocaleString('en-US') + ' lbs';
  }
  function fmtDate(iso) {
    if (!iso) return '—';
    const [y,m,d] = iso.split('-');
    return new Date(+y, +m-1, +d).toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'});
  }
  function timeAgo(ts) {
    const diff = Math.floor(Date.now()/1000) - ts;
    if (diff < 3600) return Math.floor(diff/60) + 'm ago';
    if (diff < 86400) return Math.floor(diff/3600) + 'h ago';
    return Math.floor(diff/86400) + 'd ago';
  }

  // Toast
  let toastTimer;
  function showToast(msg, type='') {
    const el = document.getElementById('toast');
    el.textContent = msg;
    el.className = 'toast show' + (type ? ' toast-'+type : '');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => el.className = 'toast', 3200);
  }

  // Auth
  function getCurrentUser() {
    try { return JSON.parse(localStorage.getItem('fx_user')) || null; } catch(e) { return null; }
  }

  // ═══════════════════════════════════════════════════════════
  //  USER LOADS (localStorage cache + server sync)
  // ═══════════════════════════════════════════════════════════
  let userLoadsCache = {}; // loadId -> {status, ...}

  async function loadMyLoads() {
    const user = getCurrentUser();
    if (!user) return;
    try {
      const res  = await fetch('loadboard_data.php?action=my_loads&user_id=' + encodeURIComponent(user.id));
      const data = await res.json();
      if (data.success) {
        userLoadsCache = {};
        (data.loads || []).forEach(ul => { userLoadsCache[ul.load_id] = ul; });
        updateMyLoadsBadge();
        renderMyLoads(data.loads || []);
      }
    } catch(e) { /* ignore */ }
  }

  async function updateLoadStatus(loadId, status) {
    const user = getCurrentUser();
    if (!user) { showToast('Please log in to manage loads', 'error'); return; }
    try {
      const res  = await fetch('loadboard_data.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action:'update_load_status', user_id: user.id, load_id: loadId, status})
      });
      const data = await res.json();
      if (data.success) {
        showToast('Load ' + statusLabel(status), 'success');
        await loadMyLoads();
        // Re-render current page to update action buttons
        renderLoadsTable();
      } else {
        showToast(data.message || 'Error', 'error');
      }
    } catch(e) { showToast('Network error', 'error'); }
  }

  async function trackView(loadId) {
    const user = getCurrentUser();
    if (!user) return;
    try {
      await fetch('loadboard_data.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action:'track_view', user_id: user.id, load_id: loadId})
      });
      await loadMyLoads();
    } catch(e) {}
  }

  function statusLabel(status) {
    const labels = {
      saved:'saved', hidden:'hidden', contacted:'contacted',
      booked:'booked', pickup_complete:'pickup complete',
      delivered:'delivered', cancelled:'cancelled', rejected:'rejected',
      remove:'removed from list', viewed:'viewed'
    };
    return labels[status] || status;
  }

  function updateMyLoadsBadge() {
    const active = Object.values(userLoadsCache).filter(ul =>
      !['viewed','hidden','delivered','cancelled','rejected','remove'].includes(ul.status)
    ).length;
    const badge = document.getElementById('myLoadsBadge');
    if (active > 0) {
      badge.textContent = active;
      badge.style.display = '';
    } else {
      badge.style.display = 'none';
    }
  }

  // ═══════════════════════════════════════════════════════════
  //  FIND LOADS — Data + Rendering
  // ═══════════════════════════════════════════════════════════
  let allLoads   = [];
  let filteredLoads = [];
  let currentPage   = 1;
  const PAGE_SIZE   = 25;

  async function fetchLoads(params = {}) {
    const qs = new URLSearchParams({action:'find_loads', ...params}).toString();
    try {
      const res  = await fetch('loadboard_data.php?' + qs);
      const data = await res.json();
      if (data.success) {
        allLoads = data.loads || [];
        applySort();
      }
    } catch(e) {
      document.getElementById('loadsTableBody').innerHTML = '<div class="empty-state"><iconify-icon icon="lucide:wifi-off"></iconify-icon><p>Network error — please refresh.</p></div>';
    }
  }

  function applySort() {
    const sort = document.getElementById('sortSelect').value;
    filteredLoads = [...allLoads];
    if (sort === 'pickup')     filteredLoads.sort((a,b) => a.pickup_ts - b.pickup_ts);
    else if (sort === 'rate_desc')  filteredLoads.sort((a,b) => b.rate_total - a.rate_total);
    else if (sort === 'rate_asc')   filteredLoads.sort((a,b) => a.rate_total - b.rate_total);
    else if (sort === 'dist_desc')  filteredLoads.sort((a,b) => b.distance_mi - a.distance_mi);
    else if (sort === 'dist_asc')   filteredLoads.sort((a,b) => a.distance_mi - b.distance_mi);
    // default: newest posted (already sorted by server)
    currentPage = 1;
    renderLoadsTable();
  }

  function getFilterParams() {
    const vals = {
      origin_city:  document.getElementById('fOriginCity').value.trim(),
      origin_state: document.getElementById('fOriginState').value.trim().toUpperCase(),
      dest_city:    document.getElementById('fDestCity').value.trim(),
      dest_state:   document.getElementById('fDestState').value.trim().toUpperCase(),
      equipment:    document.getElementById('fEquipment').value,
      load_type:    document.getElementById('fLoadType').value,
      pickup_from:  document.getElementById('fPickupFrom').value,
      pickup_to:    document.getElementById('fPickupTo').value,
      min_weight:   document.getElementById('fMinWeight').value,
      max_weight:   document.getElementById('fMaxWeight').value,
      min_dist:     document.getElementById('fMinDist').value,
      max_dist:     document.getElementById('fMaxDist').value,
      min_rate:     document.getElementById('fMinRate').value,
      commodity:    document.getElementById('fCommodity').value.trim(),
      hazmat:       document.getElementById('fHazmat').value,
    };
    // Remove empty values
    return Object.fromEntries(Object.entries(vals).filter(([,v]) => v !== ''));
  }

  function renderActiveFilters(params) {
    const labels = {
      origin_city:'Origin City', origin_state:'Origin State',
      dest_city:'Dest City', dest_state:'Dest State',
      equipment:'Equipment', load_type:'Load Type',
      pickup_from:'Pickup From', pickup_to:'Pickup To',
      min_weight:'Min Weight', max_weight:'Max Weight',
      min_dist:'Min Dist', max_dist:'Max Dist',
      min_rate:'Min Rate/mi', commodity:'Commodity', hazmat:'Hazmat'
    };
    const cont = document.getElementById('activeFiltersContainer');
    cont.innerHTML = '';
    Object.entries(params).forEach(([k, v]) => {
      if (!v) return;
      const chip = document.createElement('div');
      chip.className = 'filter-chip';
      chip.innerHTML = escHtml((labels[k] || k) + ': ' + v)
        + '<button title="Remove filter" data-key="' + escHtml(k) + '"><iconify-icon icon="lucide:x" style="font-size:12px"></iconify-icon></button>';
      chip.querySelector('button').addEventListener('click', () => {
        clearFilterField(k);
        doSearch();
      });
      cont.appendChild(chip);
    });
  }

  function clearFilterField(key) {
    const map = {
      origin_city: 'fOriginCity', origin_state: 'fOriginState',
      dest_city: 'fDestCity', dest_state: 'fDestState',
      equipment: 'fEquipment', load_type: 'fLoadType',
      pickup_from: 'fPickupFrom', pickup_to: 'fPickupTo',
      min_weight: 'fMinWeight', max_weight: 'fMaxWeight',
      min_dist: 'fMinDist', max_dist: 'fMaxDist',
      min_rate: 'fMinRate', commodity: 'fCommodity', hazmat: 'fHazmat',
    };
    const el = document.getElementById(map[key]);
    if (el) el.value = '';
  }

  function renderLoadsTable() {
    const container = document.getElementById('loadsTableBody');
    document.getElementById('resultsCount').textContent = filteredLoads.length;

    if (!filteredLoads.length) {
      container.innerHTML = '<div class="empty-state"><iconify-icon icon="lucide:package-x"></iconify-icon><p>No loads match your filters.</p></div>';
      document.getElementById('paginationBar').style.display = 'none';
      return;
    }

    const start = (currentPage - 1) * PAGE_SIZE;
    const page  = filteredLoads.slice(start, start + PAGE_SIZE);

    let html = '<table class="loads-table"><thead><tr>'
      + '<th>Load ID</th><th>Route</th><th>Equipment</th><th>Type</th>'
      + '<th>Distance</th><th>Rate</th><th>Pickup Date</th><th>Weight</th><th>Actions</th>'
      + '</tr></thead><tbody>';

    page.forEach(l => {
      const ul = userLoadsCache[l.id];
      const st = ul ? ul.status : '';
      const savedClass = st === 'saved' ? ' active-save' : '';
      const hideClass  = st === 'hidden' ? ' active-hide' : '';

      html += `<tr>
        <td class="load-id-cell">${escHtml(l.id)}</td>
        <td class="route-cell">
          <span>${escHtml(l.origin_city)}, ${escHtml(l.origin_state)}</span>
          <span class="route-arrow">→</span>
          <span>${escHtml(l.dest_city)}, ${escHtml(l.dest_state)}</span>
          ${l.hazmat ? '<br><span class="badge badge-hazmat" style="margin-top:4px;">⚠ Hazmat</span>' : ''}
        </td>
        <td><span class="badge badge-equip">${escHtml(l.equipment)}</span></td>
        <td><span class="badge badge-${l.load_type.toLowerCase()}">${escHtml(l.load_type)}</span></td>
        <td>${l.distance_mi.toLocaleString()} mi</td>
        <td class="rate-cell">${fmtMoney(l.rate_total)}<br><span style="font-size:11px;font-weight:500;color:var(--muted-foreground);">$${l.rate_per_mile}/mi</span></td>
        <td>${fmtDate(l.pickup_date)}</td>
        <td>${fmtWeight(l.weight_lbs)}</td>
        <td class="actions-cell">
          <button class="btn-icon${savedClass}" title="${st==='saved'?'Unsave':'Save'} load" onclick="handleSave('${escHtml(l.id)}','${st}')">
            <iconify-icon icon="lucide:bookmark"></iconify-icon>
          </button>
          <button class="btn-icon${hideClass}" title="${st==='hidden'?'Unhide':'Hide'} load" onclick="handleHide('${escHtml(l.id)}','${st}')">
            <iconify-icon icon="lucide:eye-off"></iconify-icon>
          </button>
          <button class="btn-icon" title="View details" onclick="openDrawer('${escHtml(l.id)}')">
            <iconify-icon icon="lucide:arrow-right"></iconify-icon>
          </button>
        </td>
      </tr>`;
    });
    html += '</tbody></table>';
    container.innerHTML = html;

    // Pagination
    renderPagination();
  }

  function renderPagination() {
    const total = filteredLoads.length;
    const pages = Math.ceil(total / PAGE_SIZE);
    const bar = document.getElementById('paginationBar');
    if (pages <= 1) { bar.style.display = 'none'; return; }
    bar.style.display = 'flex';

    let html = `<button class="page-btn" onclick="goPage(${currentPage-1})" ${currentPage===1?'disabled':''}>
      <iconify-icon icon="lucide:chevron-left" style="font-size:14px"></iconify-icon>
    </button>`;

    const range = 2;
    for (let p = 1; p <= pages; p++) {
      if (p === 1 || p === pages || (p >= currentPage - range && p <= currentPage + range)) {
        html += `<button class="page-btn${p===currentPage?' active':''}" onclick="goPage(${p})">${p}</button>`;
      } else if (p === currentPage - range - 1 || p === currentPage + range + 1) {
        html += `<span style="padding:0 4px;color:var(--muted-foreground)">…</span>`;
      }
    }

    html += `<button class="page-btn" onclick="goPage(${currentPage+1})" ${currentPage===pages?'disabled':''}>
      <iconify-icon icon="lucide:chevron-right" style="font-size:14px"></iconify-icon>
    </button>`;
    bar.innerHTML = html;
  }

  function goPage(p) {
    const pages = Math.ceil(filteredLoads.length / PAGE_SIZE);
    if (p < 1 || p > pages) return;
    currentPage = p;
    renderLoadsTable();
    window.scrollTo({top: 0, behavior: 'smooth'});
  }

  function doSearch() {
    const params = getFilterParams();
    renderActiveFilters(params);
    document.getElementById('loadsTableBody').innerHTML =
      '<div class="loading-overlay"><iconify-icon icon="lucide:loader-circle"></iconify-icon> Searching…</div>';
    fetchLoads(params);
  }

  // Load action handlers
  function handleSave(loadId, currentStatus) {
    const newStatus = currentStatus === 'saved' ? 'remove' : 'saved';
    updateLoadStatus(loadId, newStatus);
  }
  function handleHide(loadId, currentStatus) {
    const newStatus = currentStatus === 'hidden' ? 'remove' : 'hidden';
    updateLoadStatus(loadId, newStatus);
  }

  // ═══════════════════════════════════════════════════════════
  //  LOAD DETAIL DRAWER
  // ═══════════════════════════════════════════════════════════
  let drawerLoadData = null;

  function openDrawer(loadId) {
    const load = allLoads.find(l => l.id === loadId);
    if (!load) return;
    drawerLoadData = load;

    document.getElementById('drawerLoadId').textContent = load.id;
    document.getElementById('drawerTitle').textContent =
      load.origin_city + ', ' + load.origin_state + ' → ' + load.dest_city + ', ' + load.dest_state;

    const ul = userLoadsCache[loadId];
    const st = ul ? ul.status : '';

    document.getElementById('drawerBody').innerHTML = `
      <div class="detail-row">
        <div class="detail-icon"><iconify-icon icon="lucide:map-pin"></iconify-icon></div>
        <div class="detail-content">
          <div class="detail-label">Route</div>
          <div class="detail-value">${escHtml(load.origin_city)}, ${escHtml(load.origin_state)} → ${escHtml(load.dest_city)}, ${escHtml(load.dest_state)}</div>
        </div>
      </div>
      <div class="detail-row">
        <div class="detail-icon"><iconify-icon icon="lucide:ruler"></iconify-icon></div>
        <div class="detail-content">
          <div class="detail-label">Distance</div>
          <div class="detail-value">${load.distance_mi.toLocaleString()} miles</div>
        </div>
      </div>
      <div class="detail-row">
        <div class="detail-icon"><iconify-icon icon="lucide:truck"></iconify-icon></div>
        <div class="detail-content">
          <div class="detail-label">Equipment</div>
          <div class="detail-value">${escHtml(load.equipment)} &nbsp;·&nbsp; <span class="badge badge-${load.load_type.toLowerCase()}">${escHtml(load.load_type)}</span></div>
        </div>
      </div>
      <div class="detail-row">
        <div class="detail-icon"><iconify-icon icon="lucide:package"></iconify-icon></div>
        <div class="detail-content">
          <div class="detail-label">Commodity</div>
          <div class="detail-value">${escHtml(load.commodity)} &nbsp; ${load.hazmat ? '<span class="badge badge-hazmat">⚠ Hazmat</span>' : ''}</div>
        </div>
      </div>
      <div class="detail-row">
        <div class="detail-icon"><iconify-icon icon="lucide:weight"></iconify-icon></div>
        <div class="detail-content">
          <div class="detail-label">Weight</div>
          <div class="detail-value">${fmtWeight(load.weight_lbs)}</div>
        </div>
      </div>
      <div class="detail-row">
        <div class="detail-icon" style="background:#e6f9ee;color:var(--success)"><iconify-icon icon="lucide:dollar-sign"></iconify-icon></div>
        <div class="detail-content">
          <div class="detail-label">Rate</div>
          <div class="detail-value" style="font-size:18px;font-weight:800;color:var(--success);">${fmtMoney(load.rate_total)}</div>
          <div style="font-size:12px;color:var(--muted-foreground);">$${load.rate_per_mile}/mile</div>
        </div>
      </div>
      <div class="detail-row">
        <div class="detail-icon"><iconify-icon icon="lucide:calendar"></iconify-icon></div>
        <div class="detail-content">
          <div class="detail-label">Pickup Date</div>
          <div class="detail-value">${fmtDate(load.pickup_date)}</div>
        </div>
      </div>
      <div class="detail-row">
        <div class="detail-icon"><iconify-icon icon="lucide:user"></iconify-icon></div>
        <div class="detail-content">
          <div class="detail-label">Contact</div>
          <div class="detail-value">${escHtml(load.contact_name)}</div>
          <div style="font-size:13px;color:var(--muted-foreground);">${escHtml(load.contact_phone)}</div>
        </div>
      </div>
      <div class="detail-row">
        <div class="detail-icon"><iconify-icon icon="lucide:clock"></iconify-icon></div>
        <div class="detail-content">
          <div class="detail-label">Posted</div>
          <div class="detail-value">${timeAgo(load.posted_ts)}</div>
        </div>
      </div>
      ${st ? '<div style="margin-top:8px;"><span class="ml-status-badge status-'+escHtml(st)+'">'+escHtml(statusLabel(st))+'</span></div>' : ''}
    `;

    // Footer action buttons based on current status
    let footerHtml = '';
    if (!st || st === 'viewed') {
      footerHtml += `<button class="btn btn-primary" style="flex:1" onclick="updateLoadStatus('${escHtml(loadId)}','saved');closeDrawer()">
        <iconify-icon icon="lucide:bookmark" style="margin-right:6px"></iconify-icon>Save Load
      </button>
      <button class="btn btn-outline" onclick="updateLoadStatus('${escHtml(loadId)}','contacted');closeDrawer()">
        <iconify-icon icon="lucide:phone-call" style="margin-right:6px"></iconify-icon>Mark Contacted
      </button>`;
    } else if (st === 'saved') {
      footerHtml += `<button class="btn btn-primary" style="flex:1" onclick="updateLoadStatus('${escHtml(loadId)}','contacted');closeDrawer()">
        <iconify-icon icon="lucide:phone-call" style="margin-right:6px"></iconify-icon>Mark Contacted
      </button>
      <button class="btn btn-outline" onclick="updateLoadStatus('${escHtml(loadId)}','booked');closeDrawer()">
        <iconify-icon icon="lucide:calendar-check" style="margin-right:6px"></iconify-icon>Book Load
      </button>`;
    } else if (st === 'contacted') {
      footerHtml += `<button class="btn btn-primary" style="flex:1" onclick="updateLoadStatus('${escHtml(loadId)}','booked');closeDrawer()">
        <iconify-icon icon="lucide:calendar-check" style="margin-right:6px"></iconify-icon>Book Load
      </button>
      <button class="btn btn-outline" onclick="updateLoadStatus('${escHtml(loadId)}','rejected');closeDrawer()">
        <iconify-icon icon="lucide:ban" style="margin-right:6px"></iconify-icon>Reject
      </button>`;
    } else if (st === 'booked') {
      footerHtml += `<button class="btn btn-primary" style="flex:1" onclick="updateLoadStatus('${escHtml(loadId)}','pickup_complete');closeDrawer()">
        <iconify-icon icon="lucide:package-check" style="margin-right:6px"></iconify-icon>Mark Pickup Complete
      </button>
      <button class="btn btn-outline" onclick="updateLoadStatus('${escHtml(loadId)}','cancelled');closeDrawer()">
        <iconify-icon icon="lucide:x-circle" style="margin-right:6px"></iconify-icon>Cancel
      </button>`;
    } else if (st === 'pickup_complete') {
      footerHtml += `<button class="btn btn-primary" style="flex:1;background:var(--success);border-color:var(--success)" onclick="updateLoadStatus('${escHtml(loadId)}','delivered');closeDrawer()">
        <iconify-icon icon="lucide:check-circle-2" style="margin-right:6px"></iconify-icon>Mark Delivered
      </button>`;
    }
    if (!['delivered','cancelled','rejected'].includes(st)) {
      footerHtml += `<button class="btn btn-ghost" onclick="updateLoadStatus('${escHtml(loadId)}','hidden');closeDrawer()">
        <iconify-icon icon="lucide:eye-off" style="margin-right:6px"></iconify-icon>Hide
      </button>`;
    }
    footerHtml += `<button class="btn btn-ghost" onclick="closeDrawer()">Close</button>`;
    document.getElementById('drawerFooter').innerHTML = footerHtml;

    document.getElementById('drawerBackdrop').classList.add('open');
    trackView(loadId);
  }

  function closeDrawer() {
    document.getElementById('drawerBackdrop').classList.remove('open');
    drawerLoadData = null;
  }

  document.getElementById('closeDrawer').addEventListener('click', closeDrawer);
  document.getElementById('drawerBackdrop').addEventListener('click', function(e) {
    if (e.target === this) closeDrawer();
  });

  // ═══════════════════════════════════════════════════════════
  //  DENSITY MAP
  // ═══════════════════════════════════════════════════════════
  let densityMap = null;
  let densityLayers = [];

  function getDensityColor(count) {
    if (count >= 8) return '#e02424';
    if (count >= 4) return '#d97706';
    return '#16a34a';
  }
  function getDensityRadius(count) {
    return Math.min(60, 20 + count * 5);
  }

  async function loadDensityData() {
    try {
      const res  = await fetch('loadboard_data.php?action=density_data');
      const data = await res.json();
      if (!data.success) return;
      const regions = data.regions || [];
      renderDensityMap(regions);
      renderDensityTable(regions);
      renderDensityList(regions);
    } catch(e) {
      document.getElementById('densityTableBody').innerHTML =
        '<div class="empty-state"><iconify-icon icon="lucide:wifi-off"></iconify-icon><p>Network error</p></div>';
    }
  }

  function renderDensityMap(regions) {
    if (typeof L === 'undefined') {
      document.getElementById('densityMap').innerHTML =
        '<div style="height:100%;display:flex;align-items:center;justify-content:center;background:var(--muted);border-radius:var(--radius-xl) var(--radius-xl) 0 0;color:var(--muted-foreground);gap:10px;font-size:14px;">'
        + '<iconify-icon icon="lucide:map" style="font-size:32px;opacity:.4"></iconify-icon>'
        + '<span>Map unavailable — external resources blocked</span></div>';
      return;
    }
    if (!densityMap) {
      densityMap = L.map('densityMap').setView([39.5, -98.35], 4);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 18,
      }).addTo(densityMap);
    }

    // Remove old layers
    densityLayers.forEach(l => densityMap.removeLayer(l));
    densityLayers = [];

    regions.forEach(r => {
      const color  = getDensityColor(r.count);
      const radius = getDensityRadius(r.count);

      const circle = L.circleMarker([r.lat, r.lng], {
        radius,
        fillColor: color,
        color: color,
        weight: 2,
        opacity: 0.9,
        fillOpacity: 0.35,
      }).addTo(densityMap);

      const equip = (r.top_equip || []).map(e => `<span>${escHtml(e)}</span>`).join('');
      circle.bindPopup(`
        <div>
          <div class="popup-title">${escHtml(r.city)}, ${escHtml(r.state)}</div>
          <div class="popup-count">${r.count}</div>
          <div class="popup-sub">available loads</div>
          ${equip ? '<div class="popup-equip">' + equip + '</div>' : ''}
          <button class="popup-btn" onclick="filterByRegion('${escHtml(r.state)}')">
            View Loads in ${escHtml(r.state)}
          </button>
        </div>
      `);

      densityLayers.push(circle);
    });
  }

  function filterByRegion(state) {
    // Switch to Find Loads tab and filter by origin state
    document.querySelectorAll('.page-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    document.querySelector('[data-tab="find-loads"]').classList.add('active');
    document.getElementById('tab-find-loads').classList.add('active');
    document.getElementById('fOriginState').value = state;
    doSearch();
  }

  function renderDensityTable(regions) {
    if (!regions.length) {
      document.getElementById('densityTableBody').innerHTML =
        '<div class="empty-state"><iconify-icon icon="lucide:inbox"></iconify-icon><p>No data</p></div>';
      return;
    }
    const maxCount = Math.max(...regions.map(r => r.count), 1);
    let html = `<table class="density-table">
      <thead><tr>
        <th>Region</th>
        <th>Loads Available</th>
        <th>Visual</th>
        <th>Top Equipment</th>
        <th></th>
      </tr></thead><tbody>`;

    regions.forEach(r => {
      const pct = Math.round((r.count / maxCount) * 100);
      const color = getDensityColor(r.count);
      const equip = (r.top_equip || []).map(e => `<span class="badge badge-equip">${escHtml(e)}</span>`).join(' ');
      html += `<tr>
        <td><strong>${escHtml(r.city)}, ${escHtml(r.state)}</strong></td>
        <td><strong style="color:var(--primary);font-size:16px;">${r.count}</strong></td>
        <td><div class="density-bar" style="width:${Math.max(pct,4)}%;background:${color};"></div></td>
        <td>${equip}</td>
        <td>
          <button class="btn btn-outline" style="padding:5px 12px;font-size:12px;"
            onclick="filterByRegion('${escHtml(r.state)}')">
            View
          </button>
        </td>
      </tr>`;
    });
    html += '</tbody></table>';
    document.getElementById('densityTableBody').innerHTML = html;
  }

  function renderDensityList(regions) {
    if (!regions.length) {
      document.getElementById('densityList').innerHTML =
        '<div class="empty-state"><iconify-icon icon="lucide:inbox"></iconify-icon><p>No data</p></div>';
      return;
    }
    const maxCount = Math.max(...regions.map(r => r.count), 1);
    let html = '';
    regions.forEach((r, i) => {
      const color = getDensityColor(r.count);
      const pct   = Math.round((r.count / maxCount) * 100);
      html += `
        <div style="padding:12px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:12px;cursor:pointer;transition:background .15s;"
             onmouseover="this.style.background='var(--secondary)'" onmouseout="this.style.background=''"
             onclick="filterByRegion('${escHtml(r.state)}')">
          <div style="width:28px;height:28px;border-radius:50%;background:${color};opacity:.7;flex-shrink:0;display:flex;align-items:center;justify-content:center;color:#fff;font-size:12px;font-weight:800;">${i+1}</div>
          <div style="flex:1;min-width:0;">
            <div style="font-weight:600;font-size:13px;">${escHtml(r.city)}, ${escHtml(r.state)}</div>
            <div style="margin-top:4px;background:var(--muted);border-radius:4px;height:6px;overflow:hidden;">
              <div style="width:${pct}%;height:100%;background:${color};border-radius:4px;"></div>
            </div>
          </div>
          <div style="font-weight:800;font-size:16px;color:var(--primary);flex-shrink:0;">${r.count}</div>
        </div>`;
    });
    document.getElementById('densityList').innerHTML = html;
  }

  // ═══════════════════════════════════════════════════════════
  //  MY LOADS
  // ═══════════════════════════════════════════════════════════
  function renderMyLoads(loads) {
    const user = getCurrentUser();
    if (!user) {
      document.getElementById('myLoadsAuthWarn').style.display = '';
      document.getElementById('myLoadsSections').style.display = 'none';
      return;
    }
    document.getElementById('myLoadsAuthWarn').style.display = 'none';
    document.getElementById('myLoadsSections').style.display = '';

    const groups = {
      viewed: [], hidden: [], saved: [], contacted: [],
      booked: [], pickup_complete: [],
      delivered: [], cancelled: [], rejected: []
    };

    loads.forEach(ul => {
      if (groups[ul.status]) groups[ul.status].push(ul);
    });

    // Sort recently viewed by view_ts desc
    groups.viewed.sort((a,b) => (b.view_ts||0) - (a.view_ts||0));

    Object.entries(groups).forEach(([status, items]) => {
      const countEl = document.getElementById('cnt-' + status);
      const bodyEl  = document.getElementById('body-' + status);
      if (!countEl || !bodyEl) return;
      countEl.textContent = items.length;
      if (!items.length) {
        bodyEl.innerHTML = '<div class="empty-state" style="padding:24px;"><iconify-icon icon="lucide:inbox" style="font-size:28px;opacity:.3"></iconify-icon><p style="font-size:13px;">No loads here yet.</p></div>';
        return;
      }
      bodyEl.innerHTML = items.map(ul => renderMyLoadRow(ul, status)).join('');
    });
  }

  function renderMyLoadRow(ul, status) {
    const s = ul.load_snapshot || {};
    const actions = getMyLoadActions(ul.load_id, status);
    return `
      <div class="my-load-row">
        <div style="width:8px;height:8px;border-radius:50%;background:${statusColor(status)};flex-shrink:0;margin-top:4px;"></div>
        <div class="ml-route">
          <div class="origin-dest">${escHtml(s.origin||'—')} → ${escHtml(s.destination||'—')}</div>
          <div class="meta">
            ${s.equipment ? escHtml(s.equipment) + ' · ' : ''}
            ${s.load_type ? escHtml(s.load_type) + ' · ' : ''}
            ${s.distance_mi ? s.distance_mi.toLocaleString() + ' mi · ' : ''}
            ${s.pickup_date ? fmtDate(s.pickup_date) : ''}
          </div>
        </div>
        ${s.rate_total ? '<div class="ml-rate">' + fmtMoney(s.rate_total) + '</div>' : ''}
        <span class="ml-status-badge status-${escHtml(status)}">${escHtml(statusLabel(status))}</span>
        <div class="ml-actions">${actions}</div>
      </div>`;
  }

  function statusColor(status) {
    const colors = {
      viewed:'#9ca3af', hidden:'#9ca3af', saved:'#d97706',
      contacted:'#1d4ed8', booked:'#16a34a', pickup_complete:'#0369a1',
      delivered:'#15803d', cancelled:'#b91c1c', rejected:'#be123c'
    };
    return colors[status] || '#9ca3af';
  }

  function getMyLoadActions(loadId, status) {
    let html = '';
    // View details
    html += `<button class="btn-icon" title="View details" onclick="openDrawer('${escHtml(loadId)}')">
      <iconify-icon icon="lucide:arrow-right"></iconify-icon>
    </button>`;

    // Progression actions
    if (status === 'saved') {
      html += `<button class="btn-icon" title="Mark Contacted" onclick="updateLoadStatus('${escHtml(loadId)}','contacted')">
        <iconify-icon icon="lucide:phone-call"></iconify-icon>
      </button>`;
    } else if (status === 'contacted') {
      html += `<button class="btn-icon" title="Book Load" onclick="updateLoadStatus('${escHtml(loadId)}','booked')">
        <iconify-icon icon="lucide:calendar-check"></iconify-icon>
      </button>`;
    } else if (status === 'booked') {
      html += `<button class="btn-icon" title="Pickup Complete" onclick="updateLoadStatus('${escHtml(loadId)}','pickup_complete')">
        <iconify-icon icon="lucide:package-check"></iconify-icon>
      </button>`;
    } else if (status === 'pickup_complete') {
      html += `<button class="btn-icon" title="Mark Delivered" onclick="updateLoadStatus('${escHtml(loadId)}','delivered')" style="border-color:var(--success);color:var(--success);">
        <iconify-icon icon="lucide:check-circle-2"></iconify-icon>
      </button>`;
    }

    // Remove from list
    html += `<button class="btn-icon" title="Remove" onclick="updateLoadStatus('${escHtml(loadId)}','remove')">
      <iconify-icon icon="lucide:trash-2"></iconify-icon>
    </button>`;

    return html;
  }

  // ═══════════════════════════════════════════════════════════
  //  SECTION COLLAPSING (My Loads)
  // ═══════════════════════════════════════════════════════════
  function toggleSection(headerEl) {
    const body    = headerEl.nextElementSibling;
    const chevron = headerEl.querySelector('.section-chevron');
    body.classList.toggle('collapsed');
    chevron.setAttribute('icon', body.classList.contains('collapsed') ? 'lucide:chevron-down' : 'lucide:chevron-up');
  }

  // ═══════════════════════════════════════════════════════════
  //  TABS
  // ═══════════════════════════════════════════════════════════
  let densityLoaded = false;
  let myLoadsLoaded = false;

  document.querySelectorAll('.page-tab').forEach(tab => {
    tab.addEventListener('click', () => {
      document.querySelectorAll('.page-tab').forEach(t => t.classList.remove('active'));
      document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
      tab.classList.add('active');
      const id = tab.getAttribute('data-tab');
      document.getElementById('tab-' + id).classList.add('active');

      // Lazy load
      if (id === 'availability' && !densityLoaded) {
        densityLoaded = true;
        setTimeout(() => {
          loadDensityData();
          if (densityMap) densityMap.invalidateSize();
        }, 100);
      }
      if (id === 'my-loads' && !myLoadsLoaded) {
        myLoadsLoaded = true;
        loadMyLoads();
      }
      if (id === 'availability' && densityMap) {
        setTimeout(() => densityMap.invalidateSize(), 200);
      }
    });
  });

  // ═══════════════════════════════════════════════════════════
  //  EVENTS
  // ═══════════════════════════════════════════════════════════
  document.getElementById('searchBtn').addEventListener('click', doSearch);
  document.getElementById('clearFiltersBtn').addEventListener('click', () => {
    ['fOriginCity','fOriginState','fDestCity','fDestState','fEquipment','fLoadType',
     'fPickupFrom','fPickupTo','fMinWeight','fMaxWeight','fMinDist','fMaxDist','fMinRate','fCommodity','fHazmat']
      .forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
    document.getElementById('activeFiltersContainer').innerHTML = '';
    doSearch();
  });
  document.getElementById('sortSelect').addEventListener('change', applySort);
  document.getElementById('refreshBtn').addEventListener('click', () => {
    doSearch();
    if (densityLoaded) loadDensityData();
    if (myLoadsLoaded) loadMyLoads();
  });
  document.getElementById('refreshMapBtn').addEventListener('click', loadDensityData);

  // Enter key on filter inputs
  document.querySelectorAll('.filter-group input, .filter-group select').forEach(el => {
    el.addEventListener('keydown', e => { if (e.key === 'Enter') doSearch(); });
  });

  // ═══════════════════════════════════════════════════════════
  //  USER DISPLAY
  // ═══════════════════════════════════════════════════════════
  function initUserDisplay() {
    const user = getCurrentUser();
    if (user) {
      document.getElementById('userWelcome').style.display = '';
      document.getElementById('userNameDisplay').textContent = user.name || user.email || user.id;
    }
  }

  // ═══════════════════════════════════════════════════════════
  //  INIT
  // ═══════════════════════════════════════════════════════════
  (async function init() {
    initUserDisplay();

    // Load user loads cache in background
    const user = getCurrentUser();
    if (user) {
      await loadMyLoads();
    } else {
      // Still render "my loads" warning if tab is visited
      document.getElementById('myLoadsAuthWarn').style.display = '';
      document.getElementById('myLoadsSections').style.display = 'none';
    }

    // Fetch loads for Find Loads tab
    await fetchLoads();
  })();
  </script>
</body>
</html>
