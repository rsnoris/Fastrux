<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <link rel="icon" href="favicon.svg" type="image/svg+xml" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Staff Dashboard — Fastrux</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="shared.css" />
  <script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
  <style>
    body { background: var(--muted); }

    /* ── Header ── */
    .dash-header {
      background: var(--card);
      border-bottom: 1px solid var(--border);
      padding: 0;
      position: sticky;
      top: 0;
      z-index: 100;
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

    /* ── Page layout ── */
    .page-content { padding: 32px 0; }

    /* ── Welcome banner ── */
    .welcome-banner {
      background: linear-gradient(135deg, var(--primary) 0%, #1e40af 100%);
      color: #fff;
      border-radius: var(--radius-xl);
      padding: 28px 32px;
      margin-bottom: 28px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 20px;
    }
    .welcome-banner h1 { font-size: 22px; font-weight: 800; margin: 0 0 4px; }
    .welcome-banner p  { margin: 0; opacity: .85; font-size: 14px; }
    .role-badge {
      background: rgba(255,255,255,.2);
      border: 1px solid rgba(255,255,255,.35);
      border-radius: 999px;
      padding: 6px 14px;
      font-size: 13px;
      font-weight: 600;
      white-space: nowrap;
    }

    /* ── Stats grid ── */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 20px;
      margin-bottom: 28px;
    }
    .stat-card {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius-xl);
      padding: 24px;
      display: flex;
      align-items: center;
      gap: 16px;
    }
    .stat-icon {
      width: 48px; height: 48px; min-width: 48px;
      border-radius: var(--radius-md);
      display: flex; align-items: center; justify-content: center;
      font-size: 22px;
    }
    .stat-icon.blue  { background: var(--secondary); color: var(--primary); }
    .stat-icon.green { background: #e6f9ee; color: var(--success); }
    .stat-icon.amber { background: #fff7e6; color: #d97706; }
    .stat-icon.red   { background: #fef2f2; color: var(--destructive); }
    .stat-label { font-size: 13px; color: var(--muted-foreground); font-weight: 500; margin-bottom: 4px; }
    .stat-value { font-size: 28px; font-weight: 800; line-height: 1; }

    /* ── Cards ── */
    .card {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius-xl);
      padding: 24px;
      margin-bottom: 24px;
    }
    .card-title {
      font-size: 16px; font-weight: 700;
      margin: 0 0 18px;
      display: flex; align-items: center; gap: 8px;
    }

    /* ── Two-column charts row ── */
    .charts-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
      margin-bottom: 24px;
    }
    .chart-wrap { position: relative; height: 220px; }

    /* ── Activity log table ── */
    .log-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .log-table th {
      background: var(--muted); color: var(--muted-foreground);
      font-size: 11px; font-weight: 600; text-transform: uppercase;
      padding: 8px 12px; text-align: left; border-bottom: 1px solid var(--border);
    }
    .log-table td {
      padding: 10px 12px;
      border-bottom: 1px solid var(--border);
      vertical-align: middle;
    }
    .log-table tr:last-child td { border-bottom: none; }
    .log-table tr:hover td { background: var(--muted); }

    /* ── Action badges ── */
    .action-badge {
      display: inline-flex; align-items: center; gap: 5px;
      padding: 3px 9px; border-radius: 999px;
      font-size: 11px; font-weight: 600;
    }
    .action-badge.login    { background: #dbeafe; color: #1d4ed8; }
    .action-badge.register { background: #dcfce7; color: #15803d; }
    .action-badge.kyc      { background: #fef9c3; color: #854d0e; }
    .action-badge.driver   { background: #ede9fe; color: #6d28d9; }
    .action-badge.quote    { background: #ffe4e6; color: #be123c; }
    .action-badge.default  { background: var(--muted); color: var(--muted-foreground); }

    /* ── Refresh indicator ── */
    .refresh-dot {
      display: inline-block; width: 8px; height: 8px;
      border-radius: 50%; background: var(--success);
      margin-right: 6px; animation: pulse 2s infinite;
    }
    @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.4} }

    @media (max-width: 900px) {
      .stats-grid  { grid-template-columns: repeat(2, 1fr); }
      .charts-row  { grid-template-columns: 1fr; }
    }
    @media (max-width: 540px) {
      .stats-grid  { grid-template-columns: 1fr 1fr; }
      .welcome-banner { flex-direction: column; align-items: flex-start; }
    }
  </style>
</head>
<body>

  <!-- ── Header ── -->
  <header class="dash-header">
    <div class="container dash-header-inner">
      <a href="index" class="dash-brand">
        <iconify-icon icon="lucide:truck" style="font-size:24px"></iconify-icon>
        Fastrux <span>&nbsp;/ Staff Dashboard</span>
      </a>
      <div style="display:flex;align-items:center;gap:12px;">
        <span id="refreshLabel" style="font-size:13px;color:var(--muted-foreground);display:flex;align-items:center;">
          <span class="refresh-dot"></span>Live
        </span>
        <a href="messages" class="btn btn-outline" style="padding:8px 14px;font-size:13px;">
          <iconify-icon icon="lucide:message-circle" style="font-size:14px;margin-right:5px"></iconify-icon>Messages
        </a>
        <a href="documents" class="btn btn-outline" style="padding:8px 14px;font-size:13px;">
          <iconify-icon icon="lucide:file-text" style="font-size:14px;margin-right:5px"></iconify-icon>Documents
        </a>
        <a href="driver-dashboard" class="btn btn-outline" style="padding:8px 14px;font-size:13px;">
          <iconify-icon icon="lucide:users" style="font-size:14px;margin-right:5px"></iconify-icon>Drivers
        </a>
        <a href="quote-dashboard" class="btn btn-outline" style="padding:8px 14px;font-size:13px;">
          <iconify-icon icon="lucide:file-text" style="font-size:14px;margin-right:5px"></iconify-icon>Quotes
        </a>
        <a href="index" class="btn btn-primary" style="padding:8px 14px;font-size:13px;">
          <iconify-icon icon="lucide:home" style="font-size:14px;margin-right:5px"></iconify-icon>Main Site
        </a>
        <a href="account" style="font-size:13px;color:var(--muted-foreground);text-decoration:none;display:flex;align-items:center;gap:5px;">
          <iconify-icon icon="lucide:settings" style="font-size:14px"></iconify-icon>
          Account
        </a>
      </div>
    </div>
  </header>

  <!-- ── Main Content ── -->
  <main class="container page-content">

    <!-- Welcome banner -->
    <div class="welcome-banner">
      <div>
        <h1>Welcome, <span id="staffName">Staff</span>!</h1>
        <p>Here's your operational overview for today. All data refreshes automatically.</p>
      </div>
      <span class="role-badge" id="staffRoleBadge">Corporate Staff</span>
    </div>

    <!-- KPI Stats -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon blue"><iconify-icon icon="lucide:users"></iconify-icon></div>
        <div>
          <div class="stat-label">Total Users</div>
          <div class="stat-value" id="statUsers">—</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon green"><iconify-icon icon="lucide:truck"></iconify-icon></div>
        <div>
          <div class="stat-label">Drivers</div>
          <div class="stat-value" id="statDrivers">—</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon amber"><iconify-icon icon="lucide:file-text"></iconify-icon></div>
        <div>
          <div class="stat-label">Quote Requests</div>
          <div class="stat-value" id="statQuotes">—</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon red"><iconify-icon icon="lucide:activity"></iconify-icon></div>
        <div>
          <div class="stat-label">Events (24h)</div>
          <div class="stat-value" id="statEvents24h">—</div>
        </div>
      </div>
    </div>

    <!-- Charts row -->
    <div class="charts-row">
      <div class="card">
        <div class="card-title">
          <iconify-icon icon="lucide:bar-chart-2"></iconify-icon>
          Platform Activity (14 days)
        </div>
        <div class="chart-wrap"><canvas id="activityChart"></canvas></div>
      </div>
      <div class="card">
        <div class="card-title">
          <iconify-icon icon="lucide:user-plus"></iconify-icon>
          New Registrations (14 days)
        </div>
        <div class="chart-wrap"><canvas id="regsChart"></canvas></div>
      </div>
    </div>

    <!-- Recent audit events -->
    <div class="card">
      <div class="card-title" style="justify-content:space-between;flex-wrap:wrap;gap:8px;">
        <span style="display:flex;align-items:center;gap:8px;">
          <iconify-icon icon="lucide:shield-check"></iconify-icon>
          Recent Platform Activity
        </span>
        <span id="eventCount" style="font-size:13px;font-weight:400;color:var(--muted-foreground);"></span>
      </div>
      <div style="overflow-x:auto;">
        <table class="log-table" id="logTable">
          <thead>
            <tr>
              <th>Time</th>
              <th>Action</th>
              <th>User ID</th>
              <th>Entity</th>
              <th>Details</th>
            </tr>
          </thead>
          <tbody id="logBody">
            <tr><td colspan="5" style="text-align:center;padding:20px;color:var(--muted-foreground);">Loading…</td></tr>
          </tbody>
        </table>
      </div>
    </div>

  </main>

  <script>
    // ── Auth guard — corporate_staff only ─────────────────────────
    var currentUser = null;
    (function () {
      try { currentUser = JSON.parse(localStorage.getItem('fx_user')); } catch (e) {}
      if (!currentUser || !currentUser.id) {
        window.location.href = 'login?redirect=' + encodeURIComponent(window.location.pathname);
        return;
      }
      if (currentUser.role !== 'corporate_staff') {
        // Redirect to each role's correct dashboard
        var dashMap = {
          admin:             'admin-dashboard',
          super_admin:       'admin-dashboard',
          driver:            'driver-dashboard',
          owner_operator:    'driver-dashboard',
          shipper:           'shipper-dashboard',
          customer:          'shipper-dashboard',
          insurance_company: 'insurance-dashboard',
          trucking_company:  'trucking-dashboard',
          gas_station:       'gas-station-dashboard',
          hotel:             'hotel-dashboard',
        };
        window.location.href = dashMap[currentUser.role] || 'login';
      }
    })();

    // ── Populate welcome ───────────────────────────────────────────
    (function () {
      if (!currentUser) return;
      document.getElementById('staffName').textContent = currentUser.first_name || 'Staff';
      document.getElementById('staffRoleBadge').textContent = 'Corporate Staff';
    })();

    // ── Helpers ───────────────────────────────────────────────────
    function esc(s) {
      return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function fmtTime(ts) {
      if (!ts) return '—';
      var d = new Date(ts.replace(' ', 'T'));
      return d.toLocaleString('en-GB', { day:'2-digit', month:'short', hour:'2-digit', minute:'2-digit' });
    }

    function actionClass(action) {
      var a = (action || '').toLowerCase();
      if (a.includes('login'))    return 'login';
      if (a.includes('register')) return 'register';
      if (a.includes('kyc'))      return 'kyc';
      if (a.includes('driver'))   return 'driver';
      if (a.includes('quote'))    return 'quote';
      return 'default';
    }

    // ── Load observability stats ───────────────────────────────────
    var actChart = null;
    var regsChart = null;

    async function loadStats() {
      try {
        var res  = await fetch('audit.php?action=stats&t=' + Date.now());
        var data = await res.json();
        if (!data.success) return;
        var s = data.summary || {};
        document.getElementById('statUsers').textContent    = s.total_users    ?? '—';
        document.getElementById('statDrivers').textContent  = s.total_drivers  ?? '—';
        document.getElementById('statQuotes').textContent   = s.total_quotes   ?? '—';
        document.getElementById('statEvents24h').textContent = s.audit_last_24h ?? '—';

        renderActivityChart(data.activity_by_day || {});
        renderRegsChart(data.regs_by_day || {});
      } catch (e) {
        console.error('Stats load failed', e);
      }
    }

    function renderActivityChart(byDay) {
      var labels = Object.keys(byDay);
      var vals   = Object.values(byDay);
      var ctx    = document.getElementById('activityChart').getContext('2d');
      if (actChart) actChart.destroy();
      actChart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: labels,
          datasets: [{ label: 'Events', data: vals, backgroundColor: '#3b82f680', borderColor: '#3b82f6', borderWidth: 1, borderRadius: 4 }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
      });
    }

    function renderRegsChart(byDay) {
      var labels = Object.keys(byDay);
      var vals   = Object.values(byDay);
      var ctx    = document.getElementById('regsChart').getContext('2d');
      if (regsChart) regsChart.destroy();
      regsChart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: labels,
          datasets: [{ label: 'Registrations', data: vals, borderColor: '#22c55e', backgroundColor: '#22c55e18', fill: true, tension: 0.4, pointRadius: 3 }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
      });
    }

    // ── Load recent audit events ───────────────────────────────────
    async function loadEvents() {
      try {
        var res  = await fetch('audit.php?action=list&limit=50&t=' + Date.now());
        var data = await res.json();
        if (!data.success) return;
        var events = data.events || [];
        document.getElementById('eventCount').textContent = events.length + ' recent events';
        var tbody = document.getElementById('logBody');
        if (!events.length) {
          tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:20px;color:var(--muted-foreground);">No events recorded yet.</td></tr>';
          return;
        }
        tbody.innerHTML = events.map(function (e) {
          var cls = actionClass(e.action);
          return '<tr>' +
            '<td style="white-space:nowrap;color:var(--muted-foreground);">' + esc(fmtTime(e.timestamp)) + '</td>' +
            '<td><span class="action-badge ' + cls + '">' + esc(e.action || '—') + '</span></td>' +
            '<td style="font-family:monospace;font-size:12px;">' + esc(e.user_id || '—') + '</td>' +
            '<td>' + esc((e.entity_type || '') + (e.entity_id ? ' · ' + e.entity_id : '')) + '</td>' +
            '<td style="max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="' + esc(e.details) + '">' + esc(e.details || '—') + '</td>' +
            '</tr>';
        }).join('');
      } catch (e) {
        console.error('Events load failed', e);
      }
    }

    // ── Init ──────────────────────────────────────────────────────
    loadStats();
    loadEvents();
    // Auto-refresh every 60 seconds
    setInterval(function () { loadStats(); loadEvents(); }, 60000);
  </script>
</body>
</html>
