<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <link rel="icon" href="favicon.svg" type="image/svg+xml" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
  <title>Driver Location — Fastrux</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="shared.css" />
  <script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>
  <style>
    body { background: var(--muted); }

    .loc-header {
      background: var(--card);
      border-bottom: 1px solid var(--border);
      padding: 0;
      position: sticky;
      top: 0;
      z-index: 100;
    }
    .loc-header-inner {
      display: flex;
      align-items: center;
      justify-content: space-between;
      height: 64px;
      max-width: 600px;
      margin: 0 auto;
      padding: 0 20px;
    }
    .loc-brand {
      display: flex; align-items: center; gap: 10px;
      font-size: 18px; font-weight: 800; color: var(--primary);
      text-decoration: none;
    }
    .loc-brand span { color: var(--foreground); font-weight: 400; font-size: 14px; }

    .loc-wrap {
      max-width: 600px;
      margin: 32px auto;
      padding: 0 20px 48px;
    }

    .loc-card {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius-xl);
      padding: 28px;
      margin-bottom: 20px;
      box-shadow: var(--shadow-sm);
    }

    .loc-card h2 {
      font-size: 17px;
      font-weight: 700;
      margin-bottom: 4px;
      color: var(--foreground);
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .loc-card .sub {
      font-size: 13px;
      color: var(--muted-foreground);
      margin-bottom: 20px;
    }

    .form-group { margin-bottom: 16px; }
    .form-group label {
      display: block;
      font-size: 13px;
      font-weight: 600;
      margin-bottom: 6px;
      color: var(--foreground);
    }
    .form-group input,
    .form-group select {
      width: 100%;
      padding: 10px 14px;
      border: 1.5px solid var(--border);
      border-radius: var(--radius-md);
      font-family: var(--font-family-body);
      font-size: 15px;
      background: var(--input);
      color: var(--foreground);
      transition: border-color .2s;
    }
    .form-group input:focus,
    .form-group select:focus {
      outline: none;
      border-color: var(--primary);
    }

    /* Status indicator */
    .status-box {
      background: var(--muted);
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      padding: 16px 20px;
      margin-bottom: 20px;
      display: flex;
      flex-direction: column;
      gap: 8px;
    }
    .status-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      font-size: 14px;
    }
    .status-row .lbl { color: var(--muted-foreground); font-size: 13px; }
    .status-row .val { font-weight: 600; font-size: 13px; }
    .dot-live { display:inline-block; width:8px; height:8px; border-radius:50%; background:var(--success); margin-right:6px; animation:pulse 2s infinite; }
    .dot-off  { display:inline-block; width:8px; height:8px; border-radius:50%; background:var(--muted-foreground); margin-right:6px; }

    @keyframes pulse {
      0%, 100% { opacity: 1; }
      50%       { opacity: 0.4; }
    }

    .alert {
      padding: 12px 16px;
      border-radius: var(--radius-md);
      font-size: 14px;
      margin-bottom: 16px;
      display: none;
    }
    .alert-success { background:#e6f9ee; color:var(--success); border:1px solid #a7f3c6; }
    .alert-error   { background:#fef2f2; color:var(--destructive); border:1px solid #fca5a5; }
    .alert-warn    { background:#fff7e6; color:#92400e; border:1px solid #fcd34d; }
    .alert.show    { display:block; }

    .coord-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
      margin-bottom: 12px;
    }

    .big-btn {
      width: 100%;
      padding: 14px;
      font-size: 16px;
      font-weight: 600;
      border-radius: var(--radius-lg);
    }

    .history-list {
      display: flex;
      flex-direction: column;
      gap: 8px;
      max-height: 200px;
      overflow-y: auto;
    }
    .history-item {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 13px;
      color: var(--muted-foreground);
      padding: 6px 0;
      border-bottom: 1px solid var(--border);
    }
    .history-item:last-child { border-bottom: none; }

    @media (max-width: 480px) {
      .coord-grid { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>

  <!-- Header -->
  <header class="loc-header">
    <div class="loc-header-inner">
      <a href="index.php" class="loc-brand">
        <iconify-icon icon="lucide:truck" style="font-size:22px"></iconify-icon>
        Fastrux <span>&nbsp;/ My Location</span>
      </a>
      <a href="offers-tracking.php" class="btn btn-outline" style="padding:7px 14px;font-size:13px;">
        <iconify-icon icon="lucide:map" style="font-size:14px;margin-right:6px"></iconify-icon>
        Offers Board
      </a>
    </div>
  </header>

  <div class="loc-wrap">

    <!-- Intro card -->
    <div class="loc-card">
      <h2>
        <iconify-icon icon="lucide:map-pin" style="font-size:20px;color:var(--primary)"></iconify-icon>
        Share Your Location
      </h2>
      <p class="sub">
        Keep the dispatch team updated on where you are so new load offers can be matched to you automatically.
      </p>

      <!-- Driver ID form -->
      <div class="form-group">
        <label for="driverIdInput">Your Driver ID</label>
        <input type="text" id="driverIdInput" placeholder="e.g. DRV-A1B2C3D4" autocomplete="off" spellcheck="false" />
      </div>

      <div class="form-group">
        <label for="availabilitySelect">Availability Status</label>
        <select id="availabilitySelect">
          <option value="available">🟢 Available — ready for new loads</option>
          <option value="busy">🟡 Busy — on a current job</option>
          <option value="offline">⚫ Offline — not available today</option>
        </select>
      </div>

      <div id="alertBox" class="alert"></div>

      <button class="btn btn-primary big-btn" id="shareBtn">
        <iconify-icon icon="lucide:locate" style="font-size:16px;margin-right:8px"></iconify-icon>
        Share My Location
      </button>
    </div>

    <!-- Live status card -->
    <div class="loc-card" id="statusCard" style="display:none;">
      <h2>
        <iconify-icon icon="lucide:radio" style="font-size:18px;color:var(--success)"></iconify-icon>
        Live Tracking Active
      </h2>
      <p class="sub" id="autoUpdateNote">Your location is being shared automatically every 60 seconds.</p>

      <div class="status-box">
        <div class="status-row">
          <span class="lbl">Status</span>
          <span class="val" id="statusLabel"><span class="dot-live"></span>Active</span>
        </div>
        <div class="status-row">
          <span class="lbl">Latitude</span>
          <span class="val" id="latVal">—</span>
        </div>
        <div class="status-row">
          <span class="lbl">Longitude</span>
          <span class="val" id="lngVal">—</span>
        </div>
        <div class="status-row">
          <span class="lbl">Accuracy</span>
          <span class="val" id="accVal">—</span>
        </div>
        <div class="status-row">
          <span class="lbl">Last Update</span>
          <span class="val" id="lastUpdate">—</span>
        </div>
        <div class="status-row">
          <span class="lbl">Availability</span>
          <span class="val" id="availLabel">—</span>
        </div>
      </div>

      <button class="btn btn-outline big-btn" id="stopBtn" style="color:var(--destructive);border-color:var(--destructive);">
        <iconify-icon icon="lucide:stop-circle" style="font-size:16px;margin-right:8px"></iconify-icon>
        Stop Sharing
      </button>
    </div>

    <!-- Update history -->
    <div class="loc-card" id="historyCard" style="display:none;">
      <h2 style="margin-bottom:12px;">
        <iconify-icon icon="lucide:history" style="font-size:18px;color:var(--muted-foreground)"></iconify-icon>
        Recent Updates
      </h2>
      <div class="history-list" id="historyList"></div>
    </div>

  </div>

  <script>
    // ── Auth guard — employee roles only ─────────────────────
    (function() {
      var user = null;
      try { user = JSON.parse(localStorage.getItem('fx_user')); } catch(e) {}
      var employeeRoles = ['driver', 'owner_operator', 'corporate_staff'];
      if (!user || !user.id || employeeRoles.indexOf(user.role) === -1) {
        window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.pathname);
      }
    })();

    // ── State ────────────────────────────────────────────────────
    let watchId      = null;   // geolocation watch ID
    let intervalId   = null;   // fallback polling interval
    let lastPosition = null;
    const history    = [];
    const UPDATE_INTERVAL_MS = 60000; // 60 seconds

    // ── URL param pre-fill ────────────────────────────────────────
    (function () {
      const params = new URLSearchParams(window.location.search);
      const id     = params.get('driver_id') || params.get('id') || '';
      const avail  = params.get('status') || '';
      if (id)    document.getElementById('driverIdInput').value    = id;
      if (avail) document.getElementById('availabilitySelect').value = avail;
    })();

    // ── Helpers ──────────────────────────────────────────────────
    function showAlert(msg, type) { // type: 'success' | 'error' | 'warn'
      const box = document.getElementById('alertBox');
      box.textContent = msg;
      box.className   = `alert alert-${type} show`;
    }
    function hideAlert() {
      document.getElementById('alertBox').classList.remove('show');
    }

    function addHistory(lat, lng, status) {
      const ts = new Date().toLocaleTimeString('en-GB');
      history.unshift({ ts, lat: lat.toFixed(5), lng: lng.toFixed(5), status });
      if (history.length > 10) history.pop();
      renderHistory();
    }

    function renderHistory() {
      const list = document.getElementById('historyList');
      if (!history.length) { list.innerHTML = '<p style="font-size:13px;color:var(--muted-foreground);">No updates yet.</p>'; return; }
      list.innerHTML = history.map(h => `
        <div class="history-item">
          <iconify-icon icon="lucide:check-circle-2" style="font-size:15px;color:var(--success);flex-shrink:0;"></iconify-icon>
          <span style="flex:1;">${h.ts} — ${h.lat}, ${h.lng}</span>
          <span style="color:var(--muted-foreground);font-size:11px;">${h.status}</span>
        </div>`).join('');
      document.getElementById('historyCard').style.display = '';
    }

    // ── Send location to server ───────────────────────────────────
    async function sendLocation(lat, lng) {
      const driverId    = document.getElementById('driverIdInput').value.trim();
      const avail       = document.getElementById('availabilitySelect').value;

      if (!driverId) { showAlert('Please enter your Driver ID first.', 'warn'); return; }

      try {
        const fd = new FormData();
        fd.append('action',    'update_location');
        fd.append('driver_id', driverId);
        fd.append('lat',       lat.toFixed(7));
        fd.append('lng',       lng.toFixed(7));
        fd.append('status',    avail);

        const res  = await fetch('offers_tracking_data.php', { method: 'POST', body: fd });
        const data = await res.json();

        const now = new Date().toLocaleTimeString('en-GB');
        document.getElementById('latVal').textContent    = lat.toFixed(6);
        document.getElementById('lngVal').textContent    = lng.toFixed(6);
        document.getElementById('lastUpdate').textContent = now;
        const availMap = { available: '🟢 Available', busy: '🟡 Busy', offline: '⚫ Offline' };
        document.getElementById('availLabel').textContent = availMap[avail] || avail;

        if (data.success) {
          addHistory(lat, lng, avail);
          hideAlert();
        } else {
          showAlert('Server error: ' + data.message, 'error');
        }
      } catch (err) {
        showAlert('Network error: ' + err.message, 'error');
      }
    }

    // ── Geolocation success callback ─────────────────────────────
    function onPosition(pos) {
      const { latitude: lat, longitude: lng, accuracy } = pos.coords;
      lastPosition = { lat, lng };
      document.getElementById('accVal').textContent = Math.round(accuracy) + ' m';
      sendLocation(lat, lng);
    }

    function onGeoError(err) {
      const msgs = {
        1: 'Location permission denied. Please allow location access in your browser settings.',
        2: 'Location unavailable. Check your device GPS.',
        3: 'Location request timed out. Trying again…',
      };
      showAlert(msgs[err.code] || 'Geolocation error.', 'error');
    }

    // ── Start sharing ─────────────────────────────────────────────
    function startSharing() {
      const driverId = document.getElementById('driverIdInput').value.trim();
      if (!driverId) { showAlert('Please enter your Driver ID before sharing.', 'warn'); return; }
      if (!navigator.geolocation) {
        showAlert('Geolocation is not supported by this browser.', 'error');
        return;
      }

      hideAlert();

      const opts = { enableHighAccuracy: true, timeout: 15000, maximumAge: 30000 };

      // Use watchPosition for real-time updates
      watchId = navigator.geolocation.watchPosition(onPosition, onGeoError, opts);

      // Also poll every UPDATE_INTERVAL_MS to ensure regular updates
      intervalId = setInterval(() => {
        navigator.geolocation.getCurrentPosition(onPosition, onGeoError, opts);
      }, UPDATE_INTERVAL_MS);

      // Get first position immediately
      navigator.geolocation.getCurrentPosition(onPosition, onGeoError, opts);

      // Show status UI
      document.getElementById('statusCard').style.display = '';
      document.getElementById('statusLabel').innerHTML = '<span class="dot-live"></span>Active';
      document.getElementById('shareBtn').disabled = true;
      document.getElementById('shareBtn').innerHTML =
        '<iconify-icon icon="lucide:check" style="font-size:16px;margin-right:8px"></iconify-icon>Sharing…';

      // Persist driver ID to localStorage
      localStorage.setItem('fastrux_driver_id', driverId);
    }

    // ── Stop sharing ──────────────────────────────────────────────
    function stopSharing() {
      if (watchId    !== null) { navigator.geolocation.clearWatch(watchId); watchId = null; }
      if (intervalId !== null) { clearInterval(intervalId); intervalId = null; }

      document.getElementById('statusLabel').innerHTML = '<span class="dot-off"></span>Stopped';
      document.getElementById('statusCard').style.display = 'none';
      document.getElementById('shareBtn').disabled = false;
      document.getElementById('shareBtn').innerHTML =
        '<iconify-icon icon="lucide:locate" style="font-size:16px;margin-right:8px"></iconify-icon>Share My Location';

      // Mark driver as offline
      const driverId = document.getElementById('driverIdInput').value.trim();
      if (driverId && lastPosition) {
        const fd = new FormData();
        fd.append('action',    'update_location');
        fd.append('driver_id', driverId);
        fd.append('lat',       lastPosition.lat.toFixed(7));
        fd.append('lng',       lastPosition.lng.toFixed(7));
        fd.append('status',    'offline');
        fetch('offers_tracking_data.php', { method: 'POST', body: fd }).catch(() => {});
      }
    }

    // ── Event listeners ───────────────────────────────────────────
    document.getElementById('shareBtn').addEventListener('click', startSharing);
    document.getElementById('stopBtn').addEventListener('click', stopSharing);

    // Restore driver ID from localStorage
    const saved = localStorage.getItem('fastrux_driver_id');
    if (saved && !document.getElementById('driverIdInput').value) {
      document.getElementById('driverIdInput').value = saved;
    }

    // Handle page unload — set offline
    window.addEventListener('beforeunload', () => {
      if (watchId !== null) stopSharing();
    });
  </script>
</body>
</html>
