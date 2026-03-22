<?php
// Offers Tracking Board — served as PHP so credentials stay server-side
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <link rel="icon" href="favicon.svg" type="image/svg+xml" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Offers Tracking Board — Fastrux</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="shared.css" />
  <!-- Leaflet map -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV/XN/WLcE=" crossorigin=""></script>
  <script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>

  <style>
    /* ── Layout ────────────────────────────────────────────── */
    body { background: var(--muted); }

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

    /* ── Telegram config banner ──────────────────────────── */
    .tg-banner {
      background: #e6f0ff;
      border-bottom: 1px solid #c3d9ff;
      padding: 10px 0;
      transition: max-height .3s ease, padding .3s ease;
    }
    .tg-banner.hidden-banner {
      max-height: 0;
      padding: 0;
      overflow: hidden;
      border-bottom: none;
    }
    .tg-banner-inner {
      display: flex;
      align-items: center;
      gap: 12px;
      flex-wrap: wrap;
    }
    .tg-banner-inner label {
      font-size: 13px;
      font-weight: 600;
      color: var(--secondary-foreground);
      white-space: nowrap;
    }
    .tg-banner-inner input {
      flex: 1;
      min-width: 200px;
      padding: 7px 12px;
      border: 1.5px solid var(--border);
      border-radius: var(--radius-md);
      font-family: var(--font-family-body);
      font-size: 13px;
      background: var(--input);
    }
    .tg-banner-inner input:focus { outline: none; border-color: var(--primary); }
    .tg-status { font-size: 12px; font-weight: 600; white-space: nowrap; }
    .tg-status.ok  { color: var(--success); }
    .tg-status.not { color: var(--muted-foreground); }

    /* ── Stats strip ─────────────────────────────────────── */
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
    .stat-pill .sp-icon {
      width: 36px; height: 36px;
      border-radius: var(--radius-md);
      display: flex; align-items: center; justify-content: center;
      font-size: 18px;
    }
    .sp-icon.blue   { background: #dbeafe; color: #1d4ed8; }
    .sp-icon.green  { background: #dcfce7; color: #16a34a; }
    .sp-icon.amber  { background: #fef9c3; color: #d97706; }
    .sp-icon.violet { background: #ede9fe; color: #7c3aed; }
    .sp-icon.grey   { background: var(--muted); color: var(--muted-foreground); }
    .stat-pill .sp-val  { font-size: 22px; font-weight: 800; line-height: 1; }
    .stat-pill .sp-lbl  { font-size: 12px; color: var(--muted-foreground); }

    /* ── Two-column board ────────────────────────────────── */
    .board {
      display: grid;
      grid-template-columns: 420px 1fr;
      gap: 20px;
      align-items: start;
    }

    /* ── Panel cards ─────────────────────────────────────── */
    .panel {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius-xl);
      overflow: hidden;
      box-shadow: var(--shadow-sm);
    }
    .panel-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 16px 20px;
      border-bottom: 1px solid var(--border);
      background: var(--card);
    }
    .panel-title {
      font-size: 15px;
      font-weight: 700;
      color: var(--foreground);
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .panel-body { padding: 16px; }

    /* ── Map ─────────────────────────────────────────────── */
    #map {
      height: 420px;
      border-radius: 0;
      z-index: 1;
    }

    /* ── Load list ───────────────────────────────────────── */
    .load-list {
      display: flex;
      flex-direction: column;
      gap: 10px;
      max-height: 540px;
      overflow-y: auto;
      padding-right: 2px;
    }
    .load-item {
      border: 2px solid var(--border);
      border-radius: var(--radius-lg);
      padding: 14px 16px;
      cursor: pointer;
      transition: border-color .2s, background .2s;
      position: relative;
    }
    .load-item:hover  { border-color: var(--primary); background: var(--secondary); }
    .load-item.active { border-color: var(--primary); background: var(--secondary); }
    .load-id   { font-family: monospace; font-size: 12px; font-weight: 700; color: var(--primary); margin-bottom: 4px; }
    .load-route {
      font-size: 13px;
      font-weight: 600;
      color: var(--foreground);
      margin-bottom: 6px;
      display: flex;
      align-items: flex-start;
      gap: 6px;
    }
    .load-meta { display: flex; flex-wrap: wrap; gap: 6px; align-items: center; margin-top: 8px; }
    .load-chip {
      background: var(--muted);
      border-radius: var(--radius-sm);
      padding: 3px 7px;
      font-size: 11px;
      color: var(--muted-foreground);
      font-weight: 500;
    }
    .load-chip.tl { background: #e6f9ee; color: var(--success); }
    .load-date  { font-size: 12px; color: var(--muted-foreground); }

    /* ── Badge overrides ─────────────────────────────────── */
    .badge-open            { background:#dbeafe; color:#1d4ed8; }
    .badge-pending_payment { background:#fef9c3; color:#a16207; }
    .badge-matched         { background:#dcfce7; color:#16a34a; }
    .badge-in_transit      { background:#fef9c3; color:#d97706; }
    .badge-completed       { background:#e5e7eb; color:#374151; }
    .badge-cancelled       { background:#fef2f2; color:var(--destructive); }

    .badge-available { background:#dcfce7; color:#16a34a; }
    .badge-busy      { background:#fef9c3; color:#d97706; }
    .badge-offline   { background:#f3f4f6; color:#6b7280; }
    .badge-dot { display:inline-block; width:6px; height:6px; border-radius:50%; background:currentColor; margin-right:5px; vertical-align:middle; }

    /* ── Driver list (right panel, below map) ────────────── */
    .driver-list {
      display: flex;
      flex-direction: column;
      gap: 8px;
      padding: 16px;
      max-height: 340px;
      overflow-y: auto;
    }
    .driver-item {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 12px 14px;
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      background: var(--card);
      transition: border-color .2s;
    }
    .driver-item:hover { border-color: var(--primary); }
    .driver-avatar {
      width: 38px; height: 38px;
      border-radius: 50%;
      background: var(--secondary);
      display: flex; align-items: center; justify-content: center;
      font-weight: 700; font-size: 15px;
      color: var(--primary);
      flex-shrink: 0;
    }
    .driver-info { flex: 1; min-width: 0; }
    .driver-name { font-size: 14px; font-weight: 600; color: var(--foreground); }
    .driver-sub  { font-size: 12px; color: var(--muted-foreground); margin-top: 2px; }
    .driver-dist { font-size: 12px; color: var(--primary); font-weight: 600; white-space: nowrap; }

    /* ── Empty state ─────────────────────────────────────── */
    .empty-state {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 40px 20px;
      text-align: center;
      color: var(--muted-foreground);
      gap: 12px;
    }
    .empty-state iconify-icon { font-size: 40px; opacity: .5; }
    .empty-state p { font-size: 14px; }

    /* ── Add load form ───────────────────────────────────── */
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .form-group { margin-bottom: 14px; }
    .form-group label {
      display: block;
      font-size: 13px;
      font-weight: 600;
      margin-bottom: 5px;
      color: var(--foreground);
    }
    .form-group input,
    .form-group select,
    .form-group textarea {
      width: 100%;
      padding: 9px 12px;
      border: 1.5px solid var(--border);
      border-radius: var(--radius-md);
      font-family: var(--font-family-body);
      font-size: 14px;
      background: var(--input);
      color: var(--foreground);
      transition: border-color .2s;
    }
    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus { outline: none; border-color: var(--primary); }
    .form-group textarea { resize: vertical; min-height: 70px; }
    .form-group .hint { font-size: 11px; color: var(--muted-foreground); margin-top: 4px; }
    .req { color: var(--destructive); }

    /* ── Modal ───────────────────────────────────────────── */
    .modal-backdrop {
      position: fixed; inset: 0;
      background: rgba(0,0,0,.45);
      display: flex;
      align-items: flex-start;
      justify-content: center;
      z-index: 500;
      padding: 40px 16px;
      overflow-y: auto;
    }
    .modal-backdrop.hidden { display: none; }
    .modal {
      background: var(--card);
      border-radius: var(--radius-xl);
      width: 100%;
      max-width: 680px;
      box-shadow: var(--shadow-xl);
      overflow: hidden;
    }
    .modal-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 20px 24px 16px;
      border-bottom: 1px solid var(--border);
    }
    .modal-header h2 { font-size: 18px; font-weight: 700; }
    .modal-close {
      background: none;
      border: 1.5px solid var(--border);
      border-radius: var(--radius-md);
      padding: 6px;
      cursor: pointer;
      color: var(--muted-foreground);
      display: flex; align-items: center;
    }
    .modal-close:hover { border-color: var(--destructive); color: var(--destructive); }
    .modal-body  { padding: 20px 24px; }
    .modal-footer {
      display: flex;
      align-items: center;
      justify-content: flex-end;
      gap: 10px;
      padding: 14px 24px;
      border-top: 1px solid var(--border);
      background: var(--muted);
    }

    /* toast */
    .toast {
      position: fixed;
      bottom: 24px;
      right: 24px;
      background: var(--foreground);
      color: #fff;
      padding: 12px 20px;
      border-radius: var(--radius-lg);
      font-size: 14px;
      font-weight: 500;
      z-index: 9999;
      opacity: 0;
      transform: translateY(8px);
      transition: opacity .3s, transform .3s;
      max-width: 360px;
      pointer-events: none;
    }
    .toast.show { opacity: 1; transform: translateY(0); }
    .toast.toast-success { background: var(--success); }
    .toast.toast-error   { background: var(--destructive); }

    /* Leaflet popup tweaks */
    .leaflet-popup-content { font-family: 'Inter', sans-serif; font-size: 13px; }
    .popup-name  { font-weight: 700; font-size: 14px; margin-bottom: 4px; }
    .popup-reg   { font-family: monospace; font-size: 12px; background: var(--muted); padding: 2px 6px; border-radius: 4px; }
    .popup-btn   { margin-top: 10px; width: 100%; padding: 8px; background: var(--primary); color: #fff; border: none; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; }
    .popup-btn:hover { opacity: .9; }

    /* ── Responsive ──────────────────────────────────────── */
    @media (max-width: 900px) {
      .board { grid-template-columns: 1fr; }
      #map   { height: 320px; }
      .stats-strip .stat-pill { min-width: 120px; }
    }
    @media (max-width: 600px) {
      .form-row { grid-template-columns: 1fr; }
      .stats-strip { gap: 10px; }
    }

    /* ── Payment modal ───────────────────────────────────── */
    .payment-tracking-id {
      display: inline-flex; align-items: center; gap: 8px;
      background: var(--secondary);
      border: 1.5px solid var(--primary);
      border-radius: var(--radius-md);
      padding: 10px 18px;
      font-family: monospace; font-size: 17px; font-weight: 700;
      color: var(--primary);
      margin: 12px 0 20px;
      letter-spacing: .04em;
    }
    .card-input-wrap {
      position: relative;
    }
    .card-input-wrap iconify-icon {
      position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
      font-size: 20px; color: var(--muted-foreground); pointer-events: none;
    }
    .card-network-icons {
      display: flex; gap: 6px; margin-bottom: 14px;
    }
    .card-network-badge {
      background: var(--muted); border: 1px solid var(--border);
      border-radius: 4px; padding: 4px 8px;
      font-size: 11px; font-weight: 700; color: var(--muted-foreground);
      letter-spacing: .04em;
    }
    .payment-success {
      text-align: center; padding: 24px 0 8px;
    }
    .payment-success-icon {
      width: 64px; height: 64px; background: #e6f9ee; border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      margin: 0 auto 16px; color: var(--success); font-size: 32px;
    }
    .payment-success h3 { font-size: 22px; font-weight: 700; margin-bottom: 8px; }
    .payment-success p  { color: var(--muted-foreground); font-size: 14px; margin-bottom: 6px; }
    .pay-alert-error {
      background: #fef2f2; color: #b91c1c;
      border: 1px solid #fca5a5;
      padding: 10px 14px; border-radius: 6px;
      font-size: 13px; margin-top: 8px;
    }
    .pay-method-tab {
      flex: 1; display: flex; align-items: center; justify-content: center;
      padding: 8px 12px; border-radius: var(--radius-md);
      border: 1.5px solid var(--border); background: var(--background);
      font-size: 13px; font-weight: 600; cursor: pointer; color: var(--muted-foreground);
      transition: all .15s;
    }
    .pay-method-tab.active {
      border-color: var(--primary); background: var(--secondary); color: var(--primary);
    }
    .pay-method-tab:hover:not(.active) {
      background: var(--muted);
    }
  </style>
</head>
<body>

  <!-- ── Header ── -->
  <header class="dash-header">
    <div class="container dash-header-inner">
      <a href="index.php" class="dash-brand">
        <iconify-icon icon="lucide:truck" style="font-size:24px"></iconify-icon>
        Fastrux <span>&nbsp;/ Offers Tracking</span>
      </a>
      <div style="display:flex;align-items:center;gap:10px;">
        <button class="btn btn-ghost" id="tgToggleBtn" style="padding:8px 14px;font-size:13px;" title="Telegram settings">
          <iconify-icon icon="lucide:send" style="font-size:15px;margin-right:6px"></iconify-icon>
          Telegram
        </button>
        <button class="btn btn-ghost" id="smsToggleBtn" style="padding:8px 14px;font-size:13px;" title="SMS settings">
          <iconify-icon icon="lucide:message-square" style="font-size:15px;margin-right:6px"></iconify-icon>
          SMS
        </button>
        <a href="driver-dashboard.php" class="btn btn-outline" style="padding:8px 14px;font-size:13px;">
          <iconify-icon icon="lucide:users" style="font-size:15px;margin-right:6px"></iconify-icon>
          Drivers
        </a>
        <a href="driver-location.php" class="btn btn-outline" style="padding:8px 14px;font-size:13px;">
          <iconify-icon icon="lucide:locate" style="font-size:15px;margin-right:6px"></iconify-icon>
          My Location
        </a>
        <a href="index.php" class="btn btn-primary" style="padding:8px 14px;font-size:13px;">
          <iconify-icon icon="lucide:home" style="font-size:15px;margin-right:6px"></iconify-icon>
          Main Site
        </a>
      </div>
    </div>
  </header>

  <!-- ── Telegram config banner ── -->
  <div class="tg-banner hidden-banner" id="tgBanner">
    <div class="container tg-banner-inner">
      <iconify-icon icon="lucide:send" style="font-size:18px;color:#0088cc;flex-shrink:0;"></iconify-icon>
      <label for="tgTokenInput">Bot Token:</label>
      <input type="password" id="tgTokenInput" placeholder="Enter Telegram bot token (from @BotFather)" autocomplete="off" />
      <button class="btn btn-primary" id="saveTgBtn" style="padding:8px 16px;font-size:13px;white-space:nowrap;">Save Token</button>
      <span class="tg-status not" id="tgStatus">Not configured</span>
      <button class="btn btn-ghost" id="tgBannerClose" style="padding:6px 10px;font-size:13px;" title="Close">
        <iconify-icon icon="lucide:x" style="font-size:14px"></iconify-icon>
      </button>
    </div>
  </div>

  <!-- ── SMS config banner ── -->
  <div class="tg-banner hidden-banner" id="smsBanner" style="background:#f0fdf4;border-bottom-color:#bbf7d0;">
    <div class="container tg-banner-inner" style="flex-wrap:wrap;gap:10px;">
      <iconify-icon icon="lucide:message-square" style="font-size:18px;color:#16a34a;flex-shrink:0;"></iconify-icon>
      <label for="smsAccountSid" style="white-space:nowrap;color:#15803d;">Account SID:</label>
      <input type="password" id="smsAccountSid" placeholder="Twilio Account SID" autocomplete="off" style="max-width:200px;" />
      <label for="smsAuthToken" style="white-space:nowrap;color:#15803d;">Auth Token:</label>
      <input type="password" id="smsAuthToken" placeholder="Twilio Auth Token" autocomplete="off" style="max-width:200px;" />
      <label for="smsFromNumber" style="white-space:nowrap;color:#15803d;">From:</label>
      <input type="text" id="smsFromNumber" placeholder="+15551234567" autocomplete="off" style="max-width:150px;" />
      <button class="btn btn-primary" id="saveSmsCfgBtn" style="padding:8px 16px;font-size:13px;white-space:nowrap;background:#16a34a;border-color:#16a34a;">Save SMS Config</button>
      <span class="tg-status not" id="smsStatus" style="color:#15803d;">Not configured</span>
      <button class="btn btn-ghost" id="smsBannerClose" style="padding:6px 10px;font-size:13px;" title="Close">
        <iconify-icon icon="lucide:x" style="font-size:14px"></iconify-icon>
      </button>
    </div>
  </div>

  <!-- ── Main content ── -->
  <div class="container" style="padding-top:28px;padding-bottom:48px;">

    <!-- Page header -->
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
      <div>
        <h1 style="font-size:26px;font-weight:800;margin-bottom:4px;">Offers Tracking Board</h1>
        <p style="color:var(--muted-foreground);font-size:14px;">
          Match load requests with available drivers and send offers via Telegram and SMS.
        </p>
      </div>
      <div style="display:flex;gap:10px;">
        <button class="btn btn-outline" id="refreshBtn" style="padding:9px 14px;font-size:13px;" title="Refresh">
          <iconify-icon icon="lucide:refresh-cw" style="font-size:15px;margin-right:6px"></iconify-icon>
          Refresh
        </button>
        <button class="btn btn-primary" id="openAddLoadBtn" style="padding:9px 16px;font-size:13px;">
          <iconify-icon icon="lucide:plus" style="font-size:15px;margin-right:6px"></iconify-icon>
          New Load Request
        </button>
      </div>
    </div>

    <!-- Stats strip -->
    <div class="stats-strip" id="statsStrip">
      <div class="stat-pill">
        <div class="sp-icon blue"><iconify-icon icon="lucide:package"></iconify-icon></div>
        <div><div class="sp-val" id="statLoadsOpen">—</div><div class="sp-lbl">Open Loads</div></div>
      </div>
      <div class="stat-pill">
        <div class="sp-icon violet"><iconify-icon icon="lucide:link"></iconify-icon></div>
        <div><div class="sp-val" id="statLoadsMatched">—</div><div class="sp-lbl">Matched</div></div>
      </div>
      <div class="stat-pill">
        <div class="sp-icon green"><iconify-icon icon="lucide:truck"></iconify-icon></div>
        <div><div class="sp-val" id="statDriversAvail">—</div><div class="sp-lbl">Available Drivers</div></div>
      </div>
      <div class="stat-pill">
        <div class="sp-icon amber"><iconify-icon icon="lucide:map-pin"></iconify-icon></div>
        <div><div class="sp-val" id="statDriversLoc">—</div><div class="sp-lbl">With Location</div></div>
      </div>
      <div class="stat-pill">
        <div class="sp-icon grey"><iconify-icon icon="lucide:users"></iconify-icon></div>
        <div><div class="sp-val" id="statDriversTotal">—</div><div class="sp-lbl">Total Drivers</div></div>
      </div>
    </div>

    <!-- Board grid -->
    <div class="board">

      <!-- ── Left: Load Requests ── -->
      <div>
        <div class="panel">
          <div class="panel-header">
            <div class="panel-title">
              <iconify-icon icon="lucide:package" style="font-size:16px;color:var(--primary)"></iconify-icon>
              Load Requests
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
              <select id="loadStatusFilter" style="padding:6px 10px;border:1.5px solid var(--border);border-radius:var(--radius-md);font-size:13px;background:var(--input);cursor:pointer;">
                <option value="">All Statuses</option>
                <option value="pending_payment">Pending Payment</option>
                <option value="open">Open</option>
                <option value="matched">Matched</option>
                <option value="in_transit">In Transit</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>
          </div>
          <div class="panel-body" style="padding:12px;">
            <div class="load-list" id="loadList">
              <div class="empty-state">
                <iconify-icon icon="lucide:loader-circle" style="animation:spin 1s linear infinite"></iconify-icon>
                <p>Loading…</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ── Right: Map + Driver list ── -->
      <div style="display:flex;flex-direction:column;gap:16px;">

        <!-- Map panel -->
        <div class="panel">
          <div class="panel-header">
            <div class="panel-title">
              <iconify-icon icon="lucide:map" style="font-size:16px;color:var(--primary)"></iconify-icon>
              Driver Locations
            </div>
            <div style="display:flex;align-items:center;gap:8px;font-size:12px;color:var(--muted-foreground);">
              <span id="mapHint">Click a load to highlight nearby drivers</span>
              <button class="btn btn-ghost" id="fitMapBtn" style="padding:5px 10px;font-size:12px;" title="Fit all markers">
                <iconify-icon icon="lucide:maximize-2" style="font-size:13px"></iconify-icon>
              </button>
            </div>
          </div>
          <!-- Legend -->
          <div style="padding:8px 16px;border-bottom:1px solid var(--border);background:var(--muted);display:flex;gap:16px;flex-wrap:wrap;font-size:12px;color:var(--muted-foreground);">
            <span>🟢 Available</span>
            <span>🟡 Busy</span>
            <span>⚫ Offline</span>
            <span>📦 Load Pickup</span>
            <span>🏁 Delivery</span>
          </div>
          <div id="map"></div>
        </div>

        <!-- Driver list (filtered by proximity to selected load) -->
        <div class="panel">
          <div class="panel-header">
            <div class="panel-title">
              <iconify-icon icon="lucide:users" style="font-size:16px;color:var(--primary)"></iconify-icon>
              <span id="driverPanelTitle">All Drivers</span>
            </div>
            <select id="driverStatusFilter" style="padding:6px 10px;border:1.5px solid var(--border);border-radius:var(--radius-md);font-size:13px;background:var(--input);cursor:pointer;">
              <option value="">All Statuses</option>
              <option value="available">Available</option>
              <option value="busy">Busy</option>
              <option value="offline">Offline</option>
            </select>
          </div>
          <div class="driver-list" id="driverList">
            <div class="empty-state">
              <iconify-icon icon="lucide:loader-circle" style="animation:spin 1s linear infinite"></iconify-icon>
              <p>Loading…</p>
            </div>
          </div>
        </div>

      </div><!-- /right -->
    </div><!-- /board -->

  </div><!-- /container -->

  <!-- ── Add Load Modal ── -->
  <div class="modal-backdrop hidden" id="addLoadModal">
    <div class="modal">
      <div class="modal-header">
        <h2>New Load Request</h2>
        <button class="modal-close" id="closeAddLoadModal">
          <iconify-icon icon="lucide:x" style="font-size:18px"></iconify-icon>
        </button>
      </div>
      <div class="modal-body">
        <form id="addLoadForm">
          <!-- Pickup -->
          <div style="font-size:13px;font-weight:700;color:var(--primary);margin-bottom:10px;display:flex;align-items:center;gap:6px;">
            <iconify-icon icon="lucide:map-pin" style="font-size:15px"></iconify-icon>
            Pickup Details
          </div>
          <div class="form-group">
            <label>Pickup Address <span class="req">*</span></label>
            <input type="text" name="pickup_address" id="pickupAddr" placeholder="e.g. 10 Downing St, London SW1A 2AA" required />
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Pickup Latitude</label>
              <input type="number" name="pickup_lat" id="pickupLat" step="0.000001" placeholder="e.g. 51.503300" />
              <p class="hint">Leave blank to geocode automatically</p>
            </div>
            <div class="form-group">
              <label>Pickup Longitude</label>
              <input type="number" name="pickup_lng" id="pickupLng" step="0.000001" placeholder="e.g. -0.127800" />
            </div>
          </div>

          <!-- Delivery -->
          <div style="font-size:13px;font-weight:700;color:var(--destructive);margin-bottom:10px;margin-top:4px;display:flex;align-items:center;gap:6px;">
            <iconify-icon icon="lucide:flag" style="font-size:15px"></iconify-icon>
            Delivery Details
          </div>
          <div class="form-group">
            <label>Delivery Address <span class="req">*</span></label>
            <input type="text" name="delivery_address" id="delivAddr" placeholder="e.g. 1 Piccadilly, Manchester M1 1RG" required />
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Delivery Latitude</label>
              <input type="number" name="delivery_lat" id="delivLat" step="0.000001" placeholder="e.g. 53.480800" />
            </div>
            <div class="form-group">
              <label>Delivery Longitude</label>
              <input type="number" name="delivery_lng" id="delivLng" step="0.000001" placeholder="e.g. -2.242600" />
            </div>
          </div>

          <!-- Cargo -->
          <div style="font-size:13px;font-weight:700;color:var(--foreground);margin-bottom:10px;margin-top:4px;display:flex;align-items:center;gap:6px;">
            <iconify-icon icon="lucide:package" style="font-size:15px"></iconify-icon>
            Cargo Details
          </div>
          <div class="form-group">
            <label>Cargo Description <span class="req">*</span></label>
            <input type="text" name="cargo_description" placeholder="e.g. Palletised electronics, fragile" required />
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Weight (kg)</label>
              <input type="number" name="weight_kg" min="0" step="0.1" placeholder="e.g. 500" />
            </div>
            <div class="form-group">
              <label>Volume (m³)</label>
              <input type="number" name="volume_m3" min="0" step="0.01" placeholder="e.g. 2.5" />
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Scheduled Date <span class="req">*</span></label>
              <input type="date" name="scheduled_date" required />
            </div>
            <div class="form-group">
              <label>Requires Tail Lift?</label>
              <select name="requires_tail_lift">
                <option value="no">No</option>
                <option value="yes">Yes</option>
              </select>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Freight Value (USD) <span class="req">*</span></label>
              <input type="number" name="freight_value" id="freightValue" min="1" max="1000000" step="0.01" placeholder="e.g. 1500.00" required />
              <p class="hint">Amount you agree to pay for this load.</p>
            </div>
          </div>

          <!-- Contact -->
          <div style="font-size:13px;font-weight:700;color:var(--foreground);margin-bottom:10px;margin-top:4px;display:flex;align-items:center;gap:6px;">
            <iconify-icon icon="lucide:phone" style="font-size:15px"></iconify-icon>
            Contact
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Contact Name</label>
              <input type="text" name="contact_name" placeholder="e.g. Sarah Johnson" />
            </div>
            <div class="form-group">
              <label>Contact Phone</label>
              <input type="tel" name="contact_phone" placeholder="e.g. +44 7700 900000" />
            </div>
          </div>
          <div class="form-group">
            <label>Notes</label>
            <textarea name="notes" placeholder="Any special instructions or requirements…"></textarea>
          </div>
        </form>
        <div id="addLoadAlert" style="display:none;padding:10px 14px;border-radius:var(--radius-md);font-size:13px;margin-top:8px;"></div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline" id="cancelAddLoad">Cancel</button>
        <button class="btn btn-primary" id="submitAddLoad">
          <iconify-icon icon="lucide:plus" style="font-size:15px;margin-right:6px"></iconify-icon>
          Create Load Request
        </button>
      </div>
    </div>
  </div>

  <!-- ── Payment Modal ── -->
  <div class="modal-backdrop hidden" id="paymentModal">
    <div class="modal">
      <!-- Payment form state -->
      <div id="paymentContent">
        <div class="modal-header">
          <h2>
            <iconify-icon icon="lucide:credit-card" style="font-size:18px;margin-right:8px;vertical-align:middle;color:var(--primary)"></iconify-icon>
            Payment Details
          </h2>
          <button class="modal-close" id="closePaymentModal">
            <iconify-icon icon="lucide:x" style="font-size:18px"></iconify-icon>
          </button>
        </div>
        <div class="modal-body">
          <p style="font-size:13px;color:var(--muted-foreground);margin-bottom:4px;">Load request created with tracking ID:</p>
          <div class="payment-tracking-id" id="paymentTrackingId">
            <iconify-icon icon="lucide:tag" style="font-size:16px"></iconify-icon>
            <span id="payTrackingIdText">—</span>
          </div>

          <!-- Amount due -->
          <div style="display:flex;align-items:center;justify-content:space-between;margin:12px 0 16px;padding:12px 16px;background:var(--muted);border-radius:var(--radius-md);">
            <span style="font-size:13px;color:var(--muted-foreground);">Amount Due</span>
            <span style="font-size:22px;font-weight:800;color:var(--primary);" id="payAmountDisplay">$0.00</span>
          </div>

          <!-- Payment method tabs -->
          <div style="display:flex;gap:8px;margin-bottom:16px;">
            <button type="button" class="pay-method-tab active" id="tabCard" onclick="switchPayMethod('card')">
              <iconify-icon icon="lucide:credit-card" style="font-size:14px;margin-right:4px;"></iconify-icon>Credit Card
            </button>
            <button type="button" class="pay-method-tab" id="tabWallet" onclick="switchPayMethod('wallet')">
              <iconify-icon icon="lucide:wallet" style="font-size:14px;margin-right:4px;"></iconify-icon>Wallet
              <span id="walletBalanceBadge" style="font-size:11px;color:var(--muted-foreground);margin-left:4px;"></span>
            </button>
          </div>

          <!-- Card payment form -->
          <div id="cardPaySection">
            <div class="card-network-icons">
              <span class="card-network-badge">VISA</span>
              <span class="card-network-badge">MASTERCARD</span>
              <span class="card-network-badge">AMEX</span>
              <span class="card-network-badge">DISCOVER</span>
            </div>

            <form id="paymentForm" novalidate>
              <div class="form-group">
                <label>Cardholder Name <span class="req">*</span></label>
                <input type="text" id="payCardName" placeholder="Jane Smith" required autocomplete="cc-name" />
              </div>
              <div class="form-group card-input-wrap">
                <label>Card Number <span class="req">*</span></label>
                <input type="text" id="payCardNumber" placeholder="1234 5678 9012 3456"
                       maxlength="19" required autocomplete="cc-number" inputmode="numeric" />
                <iconify-icon icon="lucide:credit-card"></iconify-icon>
              </div>
              <div class="form-row">
                <div class="form-group">
                  <label>Expiry <span class="req">*</span></label>
                  <input type="text" id="payExpiry" placeholder="MM / YY" maxlength="7" required autocomplete="cc-exp" inputmode="numeric" />
                </div>
                <div class="form-group">
                  <label>CVV <span class="req">*</span></label>
                  <input type="text" id="payCvv" placeholder="•••" maxlength="4" required autocomplete="cc-csc" inputmode="numeric" />
                </div>
              </div>
              <div class="form-group" style="margin-bottom:4px;">
                <label>Billing Address <span class="req">*</span></label>
                <input type="text" id="payBillingAddress" placeholder="123 Main St, City, Postcode" required autocomplete="billing street-address" />
              </div>
            </form>
          </div>

          <!-- Wallet payment section -->
          <div id="walletPaySection" style="display:none;">
            <div style="padding:16px;background:var(--muted);border-radius:var(--radius-md);text-align:center;">
              <iconify-icon icon="lucide:wallet" style="font-size:36px;color:var(--primary);display:block;margin:0 auto 8px;"></iconify-icon>
              <div style="font-size:13px;color:var(--muted-foreground);margin-bottom:4px;">Your wallet balance</div>
              <div style="font-size:28px;font-weight:800;color:var(--primary);" id="payWalletBalance">$0.00</div>
              <div style="font-size:12px;color:var(--muted-foreground);margin-top:8px;" id="walletPayNote"></div>
            </div>
          </div>

          <div id="paymentAlert" style="display:none;margin-top:10px;padding:10px 14px;border-radius:var(--radius-md);font-size:13px;"></div>

          <p style="font-size:11px;color:var(--muted-foreground);margin-top:14px;display:flex;align-items:center;gap:6px;">
            <iconify-icon icon="lucide:lock" style="font-size:13px;flex-shrink:0;"></iconify-icon>
            Your payment is processed securely. Card details are never stored on our servers.
          </p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline" id="cancelPayment">Cancel</button>
          <button class="btn btn-primary" id="submitPayment" style="min-width:140px;">
            <iconify-icon icon="lucide:lock" style="font-size:15px;margin-right:6px"></iconify-icon>
            Pay &amp; Confirm
          </button>
        </div>
      </div>

      <!-- Success state (hidden initially) -->
      <div id="paymentSuccess" style="display:none;">
        <div class="modal-header" style="border-bottom:none;">
          <span></span>
          <button class="modal-close" id="closePaymentSuccess">
            <iconify-icon icon="lucide:x" style="font-size:18px"></iconify-icon>
          </button>
        </div>
        <div class="modal-body">
          <div class="payment-success">
            <div class="payment-success-icon">
              <iconify-icon icon="lucide:check"></iconify-icon>
            </div>
            <h3>Payment Confirmed!</h3>
            <p>Your load request has been confirmed and is now active.</p>
            <p>Tracking ID: <strong id="paySuccessId" style="color:var(--primary);font-family:monospace;font-size:15px;">—</strong></p>
            <p style="margin-top:4px;font-size:13px;color:var(--muted-foreground);">Payment ID: <strong id="paySuccessPaymentId" style="font-family:monospace;">—</strong></p>
            <p style="margin-top:8px;">Our team will match you with an available driver shortly.</p>
            <button class="btn btn-primary" id="payDoneBtn" style="margin-top:20px;padding:12px 32px;">
              Done
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ── Toast notification ── -->
  <div class="toast" id="toast"></div>

  <script>
  // ═══════════════════════════════════════════════════════════
  //  STATE
  // ═══════════════════════════════════════════════════════════
  let allDrivers    = [];
  let allLoads      = [];
  let selectedLoadId = null;
  let map           = null;
  let driverMarkers = {};   // { driverId: L.marker }
  let loadMarkerA   = null; // pickup marker
  let loadMarkerB   = null; // delivery marker
  let routeLine     = null;
  let telegramOk    = false;

  // ═══════════════════════════════════════════════════════════
  //  TOAST
  // ═══════════════════════════════════════════════════════════
  let toastTimer = null;
  function showToast(msg, type = 'default') { // type: default | success | error
    const el = document.getElementById('toast');
    el.textContent  = msg;
    el.className    = 'toast show' + (type !== 'default' ? ' toast-' + type : '');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => el.classList.remove('show'), 4000);
  }

  // ═══════════════════════════════════════════════════════════
  //  MAP INITIALISATION
  // ═══════════════════════════════════════════════════════════
  function initMap() {
    map = L.map('map', { preferCanvas: true }).setView([52.5, -1.5], 6); // UK centre
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
      maxZoom: 18,
    }).addTo(map);
  }

  // ── Custom marker icons ──────────────────────────────────
  function driverIcon(status) {
    const colours = { available: '#16a34a', busy: '#d97706', offline: '#9ca3af' };
    const c = colours[status] || '#9ca3af';
    const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="32" height="40" viewBox="0 0 32 40">
      <ellipse cx="16" cy="38" rx="7" ry="2.5" fill="rgba(0,0,0,.15)"/>
      <path d="M16 0 C8 0 2 6.5 2 14.5 C2 24 16 38 16 38 C16 38 30 24 30 14.5 C30 6.5 24 0 16 0Z" fill="${c}" stroke="#fff" stroke-width="2"/>
      <text x="16" y="18" text-anchor="middle" fill="#fff" font-size="14" font-family="sans-serif">🚚</text>
    </svg>`;
    return L.divIcon({
      html: `<div style="transform:translate(-16px,-40px)">${svg}</div>`,
      iconSize: [0, 0], iconAnchor: [0, 0], popupAnchor: [16, -40],
      className: '',
    });
  }

  function pickupIcon() {
    const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="32" height="40" viewBox="0 0 32 40">
      <ellipse cx="16" cy="38" rx="7" ry="2.5" fill="rgba(0,0,0,.15)"/>
      <path d="M16 0 C8 0 2 6.5 2 14.5 C2 24 16 38 16 38 C16 38 30 24 30 14.5 C30 6.5 24 0 16 0Z" fill="#1d4ed8" stroke="#fff" stroke-width="2"/>
      <text x="16" y="18" text-anchor="middle" fill="#fff" font-size="14" font-family="sans-serif">📦</text>
    </svg>`;
    return L.divIcon({
      html: `<div style="transform:translate(-16px,-40px)">${svg}</div>`,
      iconSize: [0, 0], iconAnchor: [0, 0], popupAnchor: [16, -40], className: '',
    });
  }

  function deliveryIcon() {
    const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="32" height="40" viewBox="0 0 32 40">
      <ellipse cx="16" cy="38" rx="7" ry="2.5" fill="rgba(0,0,0,.15)"/>
      <path d="M16 0 C8 0 2 6.5 2 14.5 C2 24 16 38 16 38 C16 38 30 24 30 14.5 C30 6.5 24 0 16 0Z" fill="#dc2626" stroke="#fff" stroke-width="2"/>
      <text x="16" y="18" text-anchor="middle" fill="#fff" font-size="14" font-family="sans-serif">🏁</text>
    </svg>`;
    return L.divIcon({
      html: `<div style="transform:translate(-16px,-40px)">${svg}</div>`,
      iconSize: [0, 0], iconAnchor: [0, 0], popupAnchor: [16, -40], className: '',
    });
  }

  // ═══════════════════════════════════════════════════════════
  //  DATA LOADING
  // ═══════════════════════════════════════════════════════════
  async function loadData() {
    try {
      const user = getCurrentUser();
      // owner_operator users see only their own drivers and loads;
      // corporate_staff (and other roles) see all data.
      const scopeParam = (user && user.role === 'owner_operator' && user.id)
        ? '&user_id=' + encodeURIComponent(user.id)
        : '';

      const [driversRes, loadsRes] = await Promise.all([
        fetch('offers_tracking_data.php?action=get_drivers&t=' + Date.now() + scopeParam),
        fetch('offers_tracking_data.php?action=get_loads&t='   + Date.now() + scopeParam),
      ]);
      const driversData = await driversRes.json();
      const loadsData   = await loadsRes.json();

      allDrivers = driversData.drivers || [];
      allLoads   = loadsData.loads     || [];

      updateStats();
      renderDriverMarkers();
      renderDriverList();
      renderLoadList();
    } catch (err) {
      showToast('Could not load data: ' + err.message, 'error');
    }
  }

  // ═══════════════════════════════════════════════════════════
  //  STATS
  // ═══════════════════════════════════════════════════════════
  function updateStats() {
    const approvedDrivers = allDrivers.filter(d => d.driver_status === 'approved');
    document.getElementById('statLoadsOpen').textContent    = allLoads.filter(l => l.status === 'open').length;
    document.getElementById('statLoadsMatched').textContent = allLoads.filter(l => l.status === 'matched').length;
    document.getElementById('statDriversAvail').textContent = approvedDrivers.filter(d => d.location_status === 'available').length;
    document.getElementById('statDriversLoc').textContent   = approvedDrivers.filter(d => d.lat !== null && d.lng !== null).length;
    document.getElementById('statDriversTotal').textContent = approvedDrivers.length;
  }

  // ═══════════════════════════════════════════════════════════
  //  DRIVER MARKERS ON MAP
  // ═══════════════════════════════════════════════════════════
  function renderDriverMarkers() {
    // Remove old markers
    Object.values(driverMarkers).forEach(m => map.removeLayer(m));
    driverMarkers = {};

    allDrivers.forEach(d => {
      if (d.lat === null || d.lng === null) return;
      if (d.driver_status !== 'approved') return;

      const marker = L.marker([d.lat, d.lng], { icon: driverIcon(d.location_status) });
      const updatedAgo = d.location_updated
        ? timeSince(new Date(d.location_updated.replace(' ', 'T')))
        : 'unknown';

      marker.bindPopup(`
        <div class="popup-name">${escHtml(d.name)}</div>
        <div style="margin-bottom:6px;">
          <span class="popup-reg">${escHtml(d.van_reg || '—')}</span>
          &nbsp;·&nbsp; ${escHtml((d.van_type || '').replace(/_/g,' '))}
        </div>
        <div style="font-size:12px;color:#6b7280;">
          📍 ${d.lat.toFixed(5)}, ${d.lng.toFixed(5)}<br>
          🕐 Updated ${escHtml(updatedAgo)} ago
        </div>
        ${d.payload_kg ? `<div style="font-size:12px;margin-top:4px;">⚖️ ${d.payload_kg} kg &nbsp;|&nbsp; 📦 ${d.volume_m3 || '?'} m³</div>` : ''}
        <button class="popup-btn" onclick="sendOfferFromPopup('${escHtml(d.id)}')">
          Send Offer for Selected Load
        </button>
      `);

      marker.addTo(map);
      driverMarkers[d.id] = marker;
    });
  }

  // ═══════════════════════════════════════════════════════════
  //  LOAD LIST
  // ═══════════════════════════════════════════════════════════
  function renderLoadList() {
    const list   = document.getElementById('loadList');
    const filter = document.getElementById('loadStatusFilter').value;

    const loads = filter ? allLoads.filter(l => l.status === filter) : allLoads;

    if (!loads.length) {
      list.innerHTML = `<div class="empty-state">
        <iconify-icon icon="lucide:inbox"></iconify-icon>
        <p>No load requests yet.<br>
           <button class="btn btn-primary" onclick="document.getElementById('openAddLoadBtn').click()" style="margin-top:10px;padding:8px 16px;font-size:13px;">
             <iconify-icon icon="lucide:plus" style="font-size:14px;margin-right:5px"></iconify-icon>
             Add First Load
           </button>
        </p>
      </div>`;
      return;
    }

    list.innerHTML = loads.map(l => {
      const badgeCls  = 'badge-' + (l.status || 'open');
      const isActive  = l.id === selectedLoadId;
      const tgSent    = l.telegram_sent_at ? '📨 ' : '';
      const tl        = l.requires_tail_lift ? '<span class="load-chip tl">Tail Lift</span>' : '';
      const date      = l.scheduled_date
        ? new Date(l.scheduled_date + 'T00:00:00').toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' })
        : '';
      const freightHtml = l.freight_value
        ? `<span class="load-chip" style="font-weight:700;color:var(--primary);">💵 $${parseFloat(l.freight_value).toFixed(2)}</span>`
        : '';
      const paymentBadge = l.payment_status === 'paid'
        ? `<span style="font-size:10px;background:#e6f9ee;color:#15803d;border-radius:3px;padding:2px 6px;font-weight:700;margin-left:4px;">✓ Paid</span>`
        : (l.payment_status === 'unpaid'
          ? `<span style="font-size:10px;background:#fef9c3;color:#a16207;border-radius:3px;padding:2px 6px;font-weight:700;margin-left:4px;">Unpaid</span>`
          : '');
      return `
        <div class="load-item${isActive ? ' active' : ''}" onclick="selectLoad('${escHtml(l.id)}')">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px;">
            <span class="load-id">${tgSent}${escHtml(l.id)}${paymentBadge}</span>
            <span class="badge ${badgeCls}" style="font-size:11px;padding:3px 8px;">
              <span class="badge-dot"></span>${capitalize(l.status || 'open')}
            </span>
          </div>
          <div class="load-route">
            <iconify-icon icon="lucide:arrow-right-circle" style="font-size:14px;color:var(--primary);flex-shrink:0;margin-top:1px;"></iconify-icon>
            <span>${escHtml(l.pickup_address)} → ${escHtml(l.delivery_address)}</span>
          </div>
          <div class="load-meta">
            ${freightHtml}
            ${l.weight_kg ? `<span class="load-chip">⚖️ ${l.weight_kg} kg</span>` : ''}
            ${l.volume_m3  ? `<span class="load-chip">📦 ${l.volume_m3} m³</span>` : ''}
            ${tl}
            ${date ? `<span class="load-date">📅 ${date}</span>` : ''}
          </div>
          ${l.cargo_description ? `<div style="font-size:12px;color:var(--muted-foreground);margin-top:6px;">${escHtml(l.cargo_description)}</div>` : ''}
        </div>`;
    }).join('');
  }

  // ═══════════════════════════════════════════════════════════
  //  SELECT LOAD
  // ═══════════════════════════════════════════════════════════
  function selectLoad(loadId) {
    selectedLoadId = (selectedLoadId === loadId) ? null : loadId; // toggle
    renderLoadList();
    renderDriverList();
    updateLoadMarkers();
  }

  function updateLoadMarkers() {
    // Remove previous load markers
    if (loadMarkerA) { map.removeLayer(loadMarkerA); loadMarkerA = null; }
    if (loadMarkerB) { map.removeLayer(loadMarkerB); loadMarkerB = null; }
    if (routeLine)   { map.removeLayer(routeLine);   routeLine   = null; }

    if (!selectedLoadId) return;
    const load = allLoads.find(l => l.id === selectedLoadId);
    if (!load) return;

    const bounds = [];

    if (load.pickup_lat && load.pickup_lng) {
      loadMarkerA = L.marker([load.pickup_lat, load.pickup_lng], { icon: pickupIcon() })
        .bindPopup(`<b>📦 Pickup</b><br>${escHtml(load.pickup_address)}`)
        .addTo(map);
      bounds.push([load.pickup_lat, load.pickup_lng]);
    }
    if (load.delivery_lat && load.delivery_lng) {
      loadMarkerB = L.marker([load.delivery_lat, load.delivery_lng], { icon: deliveryIcon() })
        .bindPopup(`<b>🏁 Delivery</b><br>${escHtml(load.delivery_address)}`)
        .addTo(map);
      bounds.push([load.delivery_lat, load.delivery_lng]);
    }

    // Draw dashed route line
    if (bounds.length === 2) {
      routeLine = L.polyline(bounds, {
        color: '#0b6fff', weight: 2.5, dashArray: '6 6', opacity: 0.7,
      }).addTo(map);
    }

    // Add driver positions to bounds for fitting
    allDrivers.forEach(d => {
      if (d.lat !== null && d.lng !== null && d.driver_status === 'approved') {
        bounds.push([d.lat, d.lng]);
      }
    });

    if (bounds.length) {
      map.fitBounds(bounds, { padding: [40, 40], maxZoom: 12 });
    }
  }

  // ═══════════════════════════════════════════════════════════
  //  DRIVER LIST (sorted by distance to selected load pickup)
  // ═══════════════════════════════════════════════════════════
  function renderDriverList() {
    const list        = document.getElementById('driverList');
    const statusFilter = document.getElementById('driverStatusFilter').value;
    const load        = selectedLoadId ? allLoads.find(l => l.id === selectedLoadId) : null;

    // Only approved drivers
    let drivers = allDrivers.filter(d => d.driver_status === 'approved');

    if (statusFilter) {
      drivers = drivers.filter(d => d.location_status === statusFilter);
    }

    // Sort by distance to pickup if load selected and driver has location
    if (load && load.pickup_lat && load.pickup_lng) {
      drivers = drivers.map(d => ({
        ...d,
        _dist: (d.lat !== null && d.lng !== null)
          ? haversineKm(d.lat, d.lng, load.pickup_lat, load.pickup_lng)
          : Infinity,
      })).sort((a, b) => a._dist - b._dist);
      document.getElementById('driverPanelTitle').textContent = `Drivers — Nearest to Pickup`;
    } else {
      drivers = drivers.map(d => ({ ...d, _dist: null }));
      document.getElementById('driverPanelTitle').textContent = 'All Drivers';
    }

    if (!drivers.length) {
      list.innerHTML = `<div class="empty-state">
        <iconify-icon icon="lucide:user-x"></iconify-icon>
        <p>No approved drivers found.</p>
      </div>`;
      return;
    }

    const canOffer = load && load.status === 'open' && load.payment_status === 'paid';

    list.innerHTML = drivers.map(d => {
      const initials = ((d.name || '?')[0] || '?').toUpperCase();
      const statusBadge = `<span class="badge badge-${d.location_status}" style="font-size:11px;padding:2px 7px;">
        <span class="badge-dot"></span>${capitalize(d.location_status)}
      </span>`;
      const dist = (d._dist !== null && d._dist !== Infinity)
        ? `<span class="driver-dist">~${d._dist < 1 ? (d._dist * 1000).toFixed(0) + ' m' : d._dist.toFixed(1) + ' km'}</span>`
        : (d.lat !== null ? '<span style="font-size:11px;color:var(--muted-foreground);">Located</span>'
                          : '<span style="font-size:11px;color:var(--muted-foreground);">No location</span>');

      const offerBtn = canOffer
        ? `<button class="btn btn-primary" style="padding:6px 12px;font-size:12px;white-space:nowrap;" onclick="sendOffer('${escHtml(d.id)}','${escHtml(selectedLoadId)}')">
             <iconify-icon icon="lucide:send" style="font-size:12px;margin-right:4px"></iconify-icon>Send Offer
           </button>`
        : '';

      return `<div class="driver-item">
        <div class="driver-avatar">${escHtml(initials)}</div>
        <div class="driver-info">
          <div class="driver-name">${escHtml(d.name)}</div>
          <div class="driver-sub">
            ${escHtml(d.van_reg || '—')} &nbsp;·&nbsp;
            ${escHtml((d.van_type || '').replace(/_/g,' '))}
            ${d.payload_kg ? ` &nbsp;·&nbsp; ${d.payload_kg} kg` : ''}
          </div>
          <div style="margin-top:4px;display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
            ${statusBadge}
            ${dist}
          </div>
        </div>
        ${offerBtn}
      </div>`;
    }).join('');
  }

  // ═══════════════════════════════════════════════════════════
  //  SEND OFFER
  // ═══════════════════════════════════════════════════════════
  async function sendOffer(driverId, loadId) {
    if (!loadId) { showToast('Please select a load first.', 'error'); return; }

    const driver = allDrivers.find(d => d.id === driverId);
    const load   = allLoads.find(l => l.id === loadId);
    if (!driver || !load) return;

    const confirmed = confirm(
      `Assign ${driver.name} to load ${load.id}?\n\n` +
      `Pickup: ${load.pickup_address}\nDelivery: ${load.delivery_address}\n\n` +
      (driver.telegram_chat_id ? '✅ Telegram notification will be sent.' : '⚠️  No Telegram chat ID — driver will NOT be notified via Telegram.') + '\n' +
      (driver.phone ? '✅ SMS notification will be attempted via phone.' : '⚠️  No phone number — driver will NOT be notified via SMS.')
    );
    if (!confirmed) return;

    try {
      const fd = new FormData();
      fd.append('action',    'assign_driver');
      fd.append('load_id',   loadId);
      fd.append('driver_id', driverId);

      const res  = await fetch('offers_tracking_data.php', { method: 'POST', body: fd });
      const data = await res.json();

      if (data.success) {
        showToast(data.message, 'success');
        await loadData();
        selectedLoadId = null;
        renderLoadList();
        renderDriverList();
        updateLoadMarkers();
      } else {
        showToast('Error: ' + data.message, 'error');
      }
    } catch (err) {
      showToast('Network error: ' + err.message, 'error');
    }
  }

  // Called from map popup (uses currently selected load)
  function sendOfferFromPopup(driverId) {
    if (!selectedLoadId) {
      showToast('Please select a load from the left panel first.', 'error');
      return;
    }
    sendOffer(driverId, selectedLoadId);
  }

  // ═══════════════════════════════════════════════════════════
  //  ADD LOAD FORM (with auto-geocode)
  // ═══════════════════════════════════════════════════════════
  async function geocodeAddress(address, latField, lngField) {
    if (!address) return;
    try {
      const url  = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}&limit=1`;
      const res  = await fetch(url, { headers: { 'Accept-Language': 'en' } });
      const data = await res.json();
      if (data && data[0]) {
        document.getElementById(latField).value = parseFloat(data[0].lat).toFixed(6);
        document.getElementById(lngField).value = parseFloat(data[0].lon).toFixed(6);
      }
    } catch (_) { /* geocoding is optional */ }
  }

  document.getElementById('pickupAddr').addEventListener('blur', function () {
    if (!document.getElementById('pickupLat').value) {
      geocodeAddress(this.value, 'pickupLat', 'pickupLng');
    }
  });
  document.getElementById('delivAddr').addEventListener('blur', function () {
    if (!document.getElementById('delivLat').value) {
      geocodeAddress(this.value, 'delivLat', 'delivLng');
    }
  });

  document.getElementById('submitAddLoad').addEventListener('click', async () => {
    const form  = document.getElementById('addLoadForm');
    const alert = document.getElementById('addLoadAlert');
    const btn   = document.getElementById('submitAddLoad');
    const orig  = btn.innerHTML;

    if (!form.checkValidity()) { form.reportValidity(); return; }

    btn.disabled = true;
    btn.innerHTML = '<iconify-icon icon="lucide:loader-circle" style="font-size:15px;margin-right:6px;animation:spin 1s linear infinite"></iconify-icon>Creating…';
    alert.style.display = 'none';

    try {
      const fd = new FormData(form);
      fd.append('action', 'add_load');
      const user = getCurrentUser();
      if (user && user.id) {
        fd.append('created_by', user.id);
      }

      const res  = await fetch('offers_tracking_data.php', { method: 'POST', body: fd });
      const data = await res.json();

      if (data.success) {
        form.reset();
        document.getElementById('addLoadModal').classList.add('hidden');
        // Show payment modal with the new tracking ID and freight value
        openPaymentModal(data.load.id, data.load.freight_value || 0);
        await loadData();
      } else {
        alert.textContent   = data.message;
        alert.style.cssText = 'display:block;background:#fef2f2;color:#b91c1c;border:1px solid #fca5a5;padding:10px 14px;border-radius:6px;font-size:13px;margin-top:8px;';
      }
    } catch (err) {
      alert.textContent   = 'Network error: ' + err.message;
      alert.style.cssText = 'display:block;background:#fef2f2;color:#b91c1c;border:1px solid #fca5a5;padding:10px 14px;border-radius:6px;font-size:13px;margin-top:8px;';
    }

    btn.disabled = false;
    btn.innerHTML = orig;
  });

  // ═══════════════════════════════════════════════════════════
  //  TELEGRAM CONFIG
  // ═══════════════════════════════════════════════════════════
  async function loadTelegramStatus() {
    try {
      const res  = await fetch('offers_tracking_data.php?action=get_telegram_config&t=' + Date.now());
      const data = await res.json();
      telegramOk = data.configured || false;
      const el   = document.getElementById('tgStatus');
      el.textContent = telegramOk
        ? '✅ Configured (' + (data.masked_token || '') + ')'
        : '⚠️ Not configured';
      el.className = 'tg-status ' + (telegramOk ? 'ok' : 'not');
    } catch (_) {}
  }

  document.getElementById('saveTgBtn').addEventListener('click', async () => {
    const token = document.getElementById('tgTokenInput').value.trim();
    if (!token) { showToast('Please enter a bot token.', 'error'); return; }

    const btn  = document.getElementById('saveTgBtn');
    const orig = btn.textContent;
    btn.disabled    = true;
    btn.textContent = 'Saving…';

    try {
      const fd = new FormData();
      fd.append('action',    'save_telegram_config');
      fd.append('bot_token', token);

      const res  = await fetch('offers_tracking_data.php', { method: 'POST', body: fd });
      const data = await res.json();

      if (data.success) {
        showToast('Telegram bot token saved!', 'success');
        document.getElementById('tgTokenInput').value = '';
        loadTelegramStatus();
      } else {
        showToast('Error: ' + data.message, 'error');
      }
    } catch (err) {
      showToast('Network error: ' + err.message, 'error');
    }

    btn.disabled    = false;
    btn.textContent = orig;
  });

  // ═══════════════════════════════════════════════════════════
  //  SMS CONFIG (Twilio)
  // ═══════════════════════════════════════════════════════════
  async function loadSmsStatus() {
    try {
      const res  = await fetch('offers_tracking_data.php?action=get_sms_config&t=' + Date.now());
      const data = await res.json();
      const smsOk = data.configured || false;
      const el    = document.getElementById('smsStatus');
      el.textContent = smsOk
        ? '✅ Configured (' + (data.masked_sid || '') + ')'
        : '⚠️ Not configured';
      el.className = 'tg-status ' + (smsOk ? 'ok' : 'not');
    } catch (_) {}
  }

  document.getElementById('saveSmsCfgBtn').addEventListener('click', async () => {
    const sid  = document.getElementById('smsAccountSid').value.trim();
    const auth = document.getElementById('smsAuthToken').value.trim();
    const from = document.getElementById('smsFromNumber').value.trim();
    if (!sid || !auth || !from) { showToast('Please fill in all SMS / Twilio fields.', 'error'); return; }

    const btn  = document.getElementById('saveSmsCfgBtn');
    const orig = btn.textContent;
    btn.disabled    = true;
    btn.textContent = 'Saving…';

    try {
      const fd = new FormData();
      fd.append('action',      'save_sms_config');
      fd.append('account_sid', sid);
      fd.append('auth_token',  auth);
      fd.append('from_number', from);

      const res  = await fetch('offers_tracking_data.php', { method: 'POST', body: fd });
      const data = await res.json();

      if (data.success) {
        showToast('SMS (Twilio) config saved!', 'success');
        document.getElementById('smsAccountSid').value  = '';
        document.getElementById('smsAuthToken').value   = '';
        document.getElementById('smsFromNumber').value  = '';
        loadSmsStatus();
      } else {
        showToast('Error: ' + data.message, 'error');
      }
    } catch (err) {
      showToast('Network error: ' + err.message, 'error');
    }

    btn.disabled    = false;
    btn.textContent = orig;
  });

  // ═══════════════════════════════════════════════════════════
  //  FIT MAP
  // ═══════════════════════════════════════════════════════════
  document.getElementById('fitMapBtn').addEventListener('click', () => {
    const pts = allDrivers
      .filter(d => d.lat !== null && d.lng !== null && d.driver_status === 'approved')
      .map(d => [d.lat, d.lng]);
    if (pts.length) {
      map.fitBounds(pts, { padding: [40, 40], maxZoom: 12 });
    } else {
      map.setView([52.5, -1.5], 6);
    }
  });

  // ═══════════════════════════════════════════════════════════
  //  AUTH HELPERS
  // ═══════════════════════════════════════════════════════════
  function getCurrentUser() {
    try { return JSON.parse(localStorage.getItem('fx_user') || 'null'); } catch { return null; }
  }

  function requireAuth() {
    const user = getCurrentUser();
    if (!user) {
      window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.pathname + window.location.search);
      return false;
    }
    return true;
  }

  // ── Page-level auth guard — employee roles only ─────────
  (function() {
    const user = getCurrentUser();
    const employeeRoles = ['driver', 'owner_operator', 'corporate_staff'];
    if (!user || !user.id || !employeeRoles.includes(user.role)) {
      window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.pathname + window.location.search);
    }
  })();

  // ═══════════════════════════════════════════════════════════
  //  MODALS
  // ═══════════════════════════════════════════════════════════
  document.getElementById('openAddLoadBtn').addEventListener('click', () => {
    if (!requireAuth()) return;
    document.getElementById('addLoadModal').classList.remove('hidden');
  });
  document.getElementById('closeAddLoadModal').addEventListener('click', () => {
    document.getElementById('addLoadModal').classList.add('hidden');
  });
  document.getElementById('cancelAddLoad').addEventListener('click', () => {
    document.getElementById('addLoadModal').classList.add('hidden');
  });
  document.getElementById('addLoadModal').addEventListener('click', e => {
    if (e.target === document.getElementById('addLoadModal'))
      document.getElementById('addLoadModal').classList.add('hidden');
  });
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
      document.getElementById('addLoadModal').classList.add('hidden');
      document.getElementById('paymentModal').classList.add('hidden');
    }
  });

  // ── Payment modal handlers ────────────────────────────────
  let currentPayLoadId = '';
  let currentPayAmount = 0;
  let currentPayMethod = 'card';

  function fmtCurrency(n) {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(parseFloat(n) || 0);
  }

  function switchPayMethod(method) {
    currentPayMethod = method;
    document.getElementById('tabCard').classList.toggle('active',   method === 'card');
    document.getElementById('tabWallet').classList.toggle('active', method === 'wallet');
    document.getElementById('cardPaySection').style.display   = method === 'card'   ? '' : 'none';
    document.getElementById('walletPaySection').style.display = method === 'wallet' ? '' : 'none';
  }

  async function loadWalletBalanceForPayment() {
    const user = getCurrentUser();
    if (!user || !user.id) return;
    try {
      const res  = await fetch('wallet_data.php?action=balance&user_id=' + encodeURIComponent(user.id));
      const data = await res.json();
      if (data.success) {
        const bal = parseFloat(data.balance) || 0;
        document.getElementById('payWalletBalance').textContent    = fmtCurrency(bal);
        document.getElementById('walletBalanceBadge').textContent  = fmtCurrency(bal);
        const sufficient = bal >= currentPayAmount;
        document.getElementById('walletPayNote').textContent = sufficient
          ? 'Sufficient balance to cover this payment.'
          : 'Insufficient balance — please top up your wallet or pay by card.';
        document.getElementById('walletPayNote').style.color = sufficient ? 'var(--success)' : '#b91c1c';
      }
    } catch (_) {}
  }

  function openPaymentModal(trackId, amount) {
    currentPayLoadId = trackId;
    currentPayAmount = parseFloat(amount) || 0;
    currentPayMethod = 'card';

    document.getElementById('payTrackingIdText').textContent  = trackId;
    document.getElementById('paySuccessId').textContent       = trackId;
    document.getElementById('payAmountDisplay').textContent   = fmtCurrency(currentPayAmount);
    document.getElementById('paymentContent').style.display   = '';
    document.getElementById('paymentSuccess').style.display   = 'none';
    document.getElementById('paymentForm').reset();
    document.getElementById('paymentAlert').style.display     = 'none';
    switchPayMethod('card');
    document.getElementById('paymentModal').classList.remove('hidden');
    loadWalletBalanceForPayment();
  }

  function closePaymentModal() {
    document.getElementById('paymentModal').classList.add('hidden');
  }
  document.getElementById('closePaymentModal').addEventListener('click', closePaymentModal);
  document.getElementById('cancelPayment').addEventListener('click', closePaymentModal);
  document.getElementById('closePaymentSuccess').addEventListener('click', closePaymentModal);
  document.getElementById('payDoneBtn').addEventListener('click', closePaymentModal);
  document.getElementById('paymentModal').addEventListener('click', e => {
    if (e.target === document.getElementById('paymentModal')) closePaymentModal();
  });

  // Format card number with spaces
  document.getElementById('payCardNumber').addEventListener('input', function () {
    let v = this.value.replace(/\D/g, '').substring(0, 16);
    this.value = v.match(/.{1,4}/g)?.join(' ') ?? v;
  });
  // Format expiry MM / YY
  document.getElementById('payExpiry').addEventListener('input', function () {
    let v = this.value.replace(/\D/g, '').substring(0, 4);
    if (v.length > 2) v = v.substring(0, 2) + ' / ' + v.substring(2);
    this.value = v;
  });
  // CVV digits only
  document.getElementById('payCvv').addEventListener('input', function () {
    this.value = this.value.replace(/\D/g, '').substring(0, 4);
  });

  // Luhn algorithm check for card number validity
  function luhnCheck(num) {
    let sum = 0, alt = false;
    for (let i = num.length - 1; i >= 0; i--) {
      let n = parseInt(num[i], 10);
      if (alt) { n *= 2; if (n > 9) n -= 9; }
      sum += n;
      alt = !alt;
    }
    return sum % 10 === 0;
  }

  document.getElementById('submitPayment').addEventListener('click', async () => {
    const btn   = document.getElementById('submitPayment');
    const orig  = btn.innerHTML;

    document.getElementById('paymentAlert').style.display = 'none';

    const user = getCurrentUser();
    if (!user || !user.id) {
      showPayAlert('You must be logged in to make a payment.'); return;
    }

    if (!currentPayLoadId) {
      showPayAlert('No load ID found. Please try again.'); return;
    }

    if (currentPayAmount <= 0) {
      showPayAlert('Invalid payment amount.'); return;
    }

    const fd = new FormData();
    fd.append('action',         'process_payment');
    fd.append('load_id',        currentPayLoadId);
    fd.append('user_id',        user.id);
    fd.append('amount',         currentPayAmount);
    fd.append('payment_method', currentPayMethod);

    if (currentPayMethod === 'card') {
      const cardName   = document.getElementById('payCardName').value.trim();
      const cardNumber = document.getElementById('payCardNumber').value.replace(/\s/g, '');
      const expiry     = document.getElementById('payExpiry').value.replace(/\s/g, '');
      const cvv        = document.getElementById('payCvv').value.trim();
      const billing    = document.getElementById('payBillingAddress').value.trim();

      // Validate card details
      if (!cardName) {
        showPayAlert('Please enter the cardholder name.'); return;
      }
      if (cardNumber.length < 13 || cardNumber.length > 16 || !/^\d+$/.test(cardNumber) || !luhnCheck(cardNumber)) {
        showPayAlert('Please enter a valid card number.'); return;
      }
      const expiryClean = expiry.replace('/', '');
      if (expiryClean.length !== 4) {
        showPayAlert('Please enter a valid expiry date (MM/YY).'); return;
      }
      const mm = parseInt(expiryClean.substring(0, 2), 10);
      const twoDigitYear = parseInt(expiryClean.substring(2), 10);
      // Map 2-digit year: 00-49 → 2000-2049, 50-99 → 2050-2099
      const yy = twoDigitYear < 50 ? 2000 + twoDigitYear : 2050 + (twoDigitYear - 50);
      const now = new Date();
      if (mm < 1 || mm > 12 || yy < now.getFullYear() || (yy === now.getFullYear() && mm < now.getMonth() + 1)) {
        showPayAlert('Your card appears to be expired.'); return;
      }
      if (cvv.length < 3) {
        showPayAlert('Please enter a valid CVV (3–4 digits).'); return;
      }
      if (!billing) {
        showPayAlert('Please enter a billing address.'); return;
      }

      // Only send last 4 digits — never send the full card number or CVV to our server
      fd.append('card_name',       cardName);
      fd.append('card_last4',      cardNumber.slice(-4));
      fd.append('card_expiry',     expiryClean.substring(0, 2) + '/' + expiryClean.substring(2));
      fd.append('billing_address', billing);
    }

    btn.disabled  = true;
    btn.innerHTML = '<iconify-icon icon="lucide:loader-circle" style="font-size:15px;margin-right:6px;animation:spin 1s linear infinite"></iconify-icon>Processing…';

    try {
      const res  = await fetch('payment_data.php', { method: 'POST', body: fd });
      const data = await res.json();

      if (data.success) {
        document.getElementById('paySuccessPaymentId').textContent = data.payment_id || '—';
        document.getElementById('paymentContent').style.display = 'none';
        document.getElementById('paymentSuccess').style.display = '';
        await loadData();
      } else {
        showPayAlert(data.message || 'Payment failed. Please try again.');
      }
    } catch (err) {
      showPayAlert('Network error: ' + err.message);
    }

    btn.disabled  = false;
    btn.innerHTML = orig;
  });

  function showPayAlert(msg) {
    const el = document.getElementById('paymentAlert');
    el.textContent = msg;
    el.className   = 'pay-alert-error';
    el.style.display = 'block';
  }

  // ── Telegram banner toggle ───────────────────────────────
  document.getElementById('tgToggleBtn').addEventListener('click', () => {
    document.getElementById('tgBanner').classList.toggle('hidden-banner');
  });
  document.getElementById('tgBannerClose').addEventListener('click', () => {
    document.getElementById('tgBanner').classList.add('hidden-banner');
  });

  // ── SMS banner toggle ─────────────────────────────────────
  document.getElementById('smsToggleBtn').addEventListener('click', () => {
    document.getElementById('smsBanner').classList.toggle('hidden-banner');
  });
  document.getElementById('smsBannerClose').addEventListener('click', () => {
    document.getElementById('smsBanner').classList.add('hidden-banner');
  });

  // ── Filters ──────────────────────────────────────────────
  document.getElementById('loadStatusFilter').addEventListener('change', renderLoadList);
  document.getElementById('driverStatusFilter').addEventListener('change', renderDriverList);
  document.getElementById('refreshBtn').addEventListener('click', loadData);

  // ═══════════════════════════════════════════════════════════
  //  UTILITIES
  // ═══════════════════════════════════════════════════════════
  function escHtml(str) {
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function capitalize(str) {
    return String(str).replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
  }

  // Haversine distance in km
  function haversineKm(lat1, lng1, lat2, lng2) {
    const R    = 6371;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLng = (lng2 - lng1) * Math.PI / 180;
    const a    = Math.sin(dLat/2) * Math.sin(dLat/2)
               + Math.cos(lat1 * Math.PI/180) * Math.cos(lat2 * Math.PI/180)
               * Math.sin(dLng/2) * Math.sin(dLng/2);
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
  }

  function timeSince(date) {
    const secs = Math.floor((Date.now() - date.getTime()) / 1000);
    if (secs < 60)   return secs + 's';
    if (secs < 3600) return Math.floor(secs / 60) + 'm';
    if (secs < 86400) return Math.floor(secs / 3600) + 'h';
    return Math.floor(secs / 86400) + 'd';
  }

  // ═══════════════════════════════════════════════════════════
  //  INIT
  // ═══════════════════════════════════════════════════════════
  initMap();
  loadData();
  loadTelegramStatus();
  loadSmsStatus();

  // Auto-refresh every 30 seconds
  setInterval(loadData, 30000);
  </script>
</body>
</html>
