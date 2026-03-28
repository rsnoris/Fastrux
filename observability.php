<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <link rel="icon" href="favicon.svg" type="image/svg+xml" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Observability — Fastrux Logistics</title>
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

    /* ── Section titles ── */
    .section-heading {
      font-size: 18px;
      font-weight: 700;
      margin-bottom: 16px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    /* ── KPI grid ── */
    .kpi-grid {
      display: grid;
      grid-template-columns: repeat(5, 1fr);
      gap: 16px;
      margin-bottom: 28px;
    }
    .kpi-card {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius-xl);
      padding: 20px 20px 18px;
    }
    .kpi-icon {
      width: 40px; height: 40px;
      border-radius: var(--radius-md);
      display: flex; align-items: center; justify-content: center;
      font-size: 20px;
      margin-bottom: 12px;
    }
    .kpi-icon.blue   { background: var(--secondary); color: var(--primary); }
    .kpi-icon.green  { background: #e6f9ee; color: var(--success); }
    .kpi-icon.amber  { background: #fff7e6; color: #d97706; }
    .kpi-icon.red    { background: #fef2f2; color: var(--destructive); }
    .kpi-icon.purple { background: #f3e8ff; color: #7c3aed; }
    .kpi-icon.teal   { background: #e0f7f7; color: #0d9488; }
    .kpi-icon.pink   { background: #fce7f3; color: #db2777; }
    .kpi-icon.indigo { background: #e0e7ff; color: #4338ca; }
    .kpi-icon.slate  { background: #f1f5f9; color: #475569; }
    .kpi-label { font-size: 12px; color: var(--muted-foreground); font-weight: 500; margin-bottom: 4px; }
    .kpi-value { font-size: 28px; font-weight: 800; line-height: 1; }
    .kpi-sub   { font-size: 12px; color: var(--muted-foreground); margin-top: 4px; }

    /* ── Charts row ── */
    .charts-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
      margin-bottom: 28px;
    }
    .chart-card {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius-xl);
      padding: 20px 24px 24px;
    }
    .chart-title {
      font-size: 15px;
      font-weight: 700;
      margin-bottom: 16px;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .chart-wrap { position: relative; height: 200px; }

    /* ── Breakdown grid ── */
    .breakdown-row {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 16px;
      margin-bottom: 28px;
    }
    .breakdown-card {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius-xl);
      padding: 18px 20px;
    }
    .breakdown-title {
      font-size: 13px;
      font-weight: 700;
      color: var(--muted-foreground);
      text-transform: uppercase;
      letter-spacing: .06em;
      margin-bottom: 14px;
    }
    .breakdown-item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 10px;
      font-size: 13px;
    }
    .breakdown-item:last-child { margin-bottom: 0; }
    .breakdown-label { font-weight: 500; }
    .breakdown-count {
      font-weight: 700;
      background: var(--secondary);
      color: var(--primary);
      padding: 2px 8px;
      border-radius: 999px;
      font-size: 12px;
    }

    /* ── Toolbar ── */
    .toolbar {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 16px;
      flex-wrap: wrap;
    }
    .toolbar-search {
      position: relative;
      flex: 1;
      min-width: 220px;
    }
    .toolbar-search iconify-icon {
      position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
      color: var(--muted-foreground); font-size: 16px;
    }
    .toolbar-search input {
      width: 100%;
      padding: 10px 16px 10px 36px;
      border: 1.5px solid var(--border);
      border-radius: var(--radius-md);
      font-family: var(--font-family-body);
      font-size: 14px;
      background: var(--card);
      color: var(--foreground);
      outline: none;
      transition: border-color .2s;
    }
    .toolbar-search input:focus { border-color: var(--primary); }
    .filter-select {
      padding: 10px 36px 10px 12px;
      border: 1.5px solid var(--border);
      border-radius: var(--radius-md);
      font-family: var(--font-family-body);
      font-size: 14px;
      background: var(--card);
      color: var(--foreground);
      outline: none;
      cursor: pointer;
      appearance: none;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 10px center;
    }
    .filter-select:focus { border-color: var(--primary); }

    /* ── Table ── */
    .table-card {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius-xl);
      overflow: hidden;
      margin-bottom: 40px;
    }
    .table-wrap { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; }
    thead th {
      background: var(--muted);
      font-size: 12px; font-weight: 700;
      text-transform: uppercase; letter-spacing: .06em;
      color: var(--muted-foreground);
      padding: 12px 14px;
      text-align: left;
      border-bottom: 1px solid var(--border);
      white-space: nowrap;
    }
    tbody tr { border-bottom: 1px solid var(--border); transition: background .12s; }
    tbody tr:last-child { border-bottom: none; }
    tbody tr:hover { background: var(--secondary); }
    td { padding: 12px 14px; font-size: 13px; vertical-align: middle; }
    .td-mono  { font-family: monospace; font-size: 12px; color: var(--muted-foreground); }
    .td-bold  { font-weight: 600; }
    .td-badge {
      display: inline-flex; align-items: center;
      padding: 3px 8px; border-radius: 999px;
      font-size: 11px; font-weight: 600; white-space: nowrap;
    }
    .td-badge.login          { background: #e0e7ff; color: #4338ca; }
    .td-badge.register       { background: #e6f9ee; color: var(--success); }
    .td-badge.quote          { background: #fff7e6; color: #d97706; }
    .td-badge.driver         { background: var(--secondary); color: var(--primary); }
    .td-badge.load           { background: #fce7f3; color: #db2777; }
    .td-badge.kyc            { background: #f0fdf4; color: #16a34a; }
    .td-badge.contact        { background: #f1f5f9; color: #475569; }
    .td-badge.status_changed { background: #fef3c7; color: #92400e; }
    .td-badge.default        { background: var(--muted); color: var(--muted-foreground); }

    /* Pagination */
    .pagination {
      display: flex; align-items: center; justify-content: space-between;
      padding: 12px 16px; border-top: 1px solid var(--border);
      font-size: 13px; color: var(--muted-foreground);
    }
    .pag-btns { display: flex; gap: 6px; }
    .pag-btn {
      width: 30px; height: 30px;
      display: flex; align-items: center; justify-content: center;
      border: 1px solid var(--border);
      border-radius: var(--radius-sm);
      cursor: pointer; background: var(--card);
      font-size: 13px; font-weight: 500;
      transition: background .15s;
    }
    .pag-btn:hover { background: var(--secondary); }
    .pag-btn.active { background: var(--primary); color: #fff; border-color: var(--primary); }
    .pag-btn[disabled] { opacity: .4; cursor: default; pointer-events: none; }

    /* Empty / loading */
    .empty-state {
      text-align: center; padding: 48px 24px; color: var(--muted-foreground);
    }
    .empty-state iconify-icon { font-size: 40px; margin-bottom: 12px; opacity: .35; }
    @keyframes spin { to { transform: rotate(360deg); } }
    @keyframes shimmer {
      0%   { background-position: -400px 0; }
      100% { background-position:  400px 0; }
    }
    .skeleton {
      background: linear-gradient(90deg, var(--muted) 25%, #e8edf4 50%, var(--muted) 75%);
      background-size: 800px 100%;
      animation: shimmer 1.4s infinite;
      border-radius: var(--radius-sm);
    }

    /* Auto-refresh indicator */
    .refresh-dot {
      width: 8px; height: 8px; border-radius: 50%;
      background: var(--success);
      display: inline-block;
      margin-right: 5px;
    }
    .refresh-dot.paused { background: var(--muted-foreground); }

    @media (max-width: 1200px) {
      .kpi-grid        { grid-template-columns: repeat(3, 1fr); }
      .breakdown-row   { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 900px) {
      .kpi-grid        { grid-template-columns: repeat(2, 1fr); }
      .charts-row      { grid-template-columns: 1fr; }
    }
    @media (max-width: 600px) {
      .kpi-grid        { grid-template-columns: 1fr 1fr; }
      .breakdown-row   { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>

  <!-- ── Header ── -->
  <header class="dash-header">
    <div class="container dash-header-inner">
      <a href="index" class="dash-brand">
        <iconify-icon icon="lucide:truck" style="font-size:24px"></iconify-icon>
        Fastrux <span>&nbsp;/ Observability</span>
      </a>
      <div style="display:flex;align-items:center;gap:10px;">
        <span id="refreshLabel" style="font-size:13px;color:var(--muted-foreground);display:flex;align-items:center;">
          <span class="refresh-dot" id="refreshDot"></span>
          Auto-refresh 30s
        </span>
        <button class="btn btn-outline" id="pauseBtn" style="padding:8px 14px;font-size:13px;">Pause</button>
        <button class="btn btn-outline" id="refreshNowBtn" style="padding:8px 14px;font-size:13px;">
          <iconify-icon icon="lucide:refresh-cw" style="font-size:14px;margin-right:5px"></iconify-icon>
          Refresh Now
        </button>
        <a href="driver-dashboard" class="btn btn-outline" style="padding:8px 14px;font-size:13px;">Drivers</a>
        <a href="quote-dashboard"  class="btn btn-outline" style="padding:8px 14px;font-size:13px;">Quotes</a>
        <a href="admin-dashboard"  class="btn btn-outline" style="padding:8px 14px;font-size:13px;">
          <iconify-icon icon="lucide:shield" style="font-size:14px;margin-right:5px"></iconify-icon>Admin
        </a>
        <a href="index"            class="btn btn-primary" style="padding:8px 14px;font-size:13px;">
          <iconify-icon icon="lucide:home" style="font-size:14px;margin-right:5px"></iconify-icon>Main Site
        </a>
      </div>
    </div>
  </header>

  <div class="container" style="padding-top:28px;">

    <!-- Page heading -->
    <div style="margin-bottom:24px;">
      <h1 style="font-size:26px;font-weight:800;margin-bottom:6px;">Internal Observability Dashboard</h1>
      <p style="color:var(--muted-foreground);font-size:14px;">
        Real-time KPIs, activity trends, and a full audit trail of user actions across the platform.
      </p>
    </div>

    <!-- ── KPI Cards ── -->
    <div class="kpi-grid" id="kpiGrid">
      <!-- Populated by JS -->
      <?php for ($i = 0; $i < 9; $i++): ?>
      <div class="kpi-card">
        <div class="kpi-icon blue skeleton" style="width:40px;height:40px;border-radius:8px;"></div>
        <div class="skeleton" style="width:70%;height:12px;margin:10px 0 6px;"></div>
        <div class="skeleton" style="width:40%;height:24px;"></div>
      </div>
      <?php endfor; ?>
    </div>

    <!-- ── Activity Charts ── -->
    <div class="charts-row">
      <div class="chart-card">
        <div class="chart-title">
          <iconify-icon icon="lucide:activity" style="color:var(--primary);font-size:17px"></iconify-icon>
          Audit Events — Last 14 Days
        </div>
        <div class="chart-wrap"><canvas id="activityChart"></canvas></div>
      </div>
      <div class="chart-card">
        <div class="chart-title">
          <iconify-icon icon="lucide:user-plus" style="color:var(--success);font-size:17px"></iconify-icon>
          User Registrations — Last 14 Days
        </div>
        <div class="chart-wrap"><canvas id="regsChart"></canvas></div>
      </div>
    </div>

    <!-- ── Breakdown Cards ── -->
    <div class="breakdown-row" id="breakdownRow">
      <!-- Populated by JS -->
    </div>

    <!-- ── Audit Trail Table ── -->
    <div class="section-heading" style="margin-top:4px;">
      <iconify-icon icon="lucide:shield-check" style="color:var(--primary);font-size:20px"></iconify-icon>
      Audit Trail
    </div>

    <div class="toolbar">
      <div class="toolbar-search">
        <iconify-icon icon="lucide:search"></iconify-icon>
        <input type="text" id="searchInput" placeholder="Search action, user ID, entity, IP…" />
      </div>
      <select class="filter-select" id="typeFilter">
        <option value="">All Entity Types</option>
        <option value="user">User</option>
        <option value="driver">Driver</option>
        <option value="quote">Quote</option>
        <option value="load">Load</option>
        <option value="contact">Contact</option>
        <option value="newsletter">Newsletter</option>
      </select>
      <input type="date" id="sinceDate" class="filter-select" style="min-width:140px;" title="From date" />
      <input type="date" id="untilDate" class="filter-select" style="min-width:140px;" title="To date" />
      <button class="btn btn-outline" id="clearFiltersBtn" style="padding:10px 14px;font-size:13px;">
        <iconify-icon icon="lucide:x" style="font-size:14px;margin-right:4px"></iconify-icon>Clear
      </button>
    </div>

    <div class="table-card">
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Timestamp</th>
              <th>Action</th>
              <th>User ID</th>
              <th>Entity Type</th>
              <th>Entity ID</th>
              <th>Details</th>
              <th>IP Address</th>
            </tr>
          </thead>
          <tbody id="auditTableBody">
            <tr><td colspan="7" style="text-align:center;padding:36px;color:var(--muted-foreground);">
              <iconify-icon icon="lucide:loader-circle" style="font-size:22px;animation:spin 1s linear infinite"></iconify-icon>
            </td></tr>
          </tbody>
        </table>
      </div>
      <div class="pagination" id="pagination" style="display:none;">
        <span id="paginationInfo"></span>
        <div class="pag-btns" id="paginationBtns"></div>
      </div>
    </div>

  </div><!-- /container -->

  <script>
    // ── Auth guard — admin / super_admin only ─────────────────
    (function () {
      var user = null;
      try { user = JSON.parse(localStorage.getItem('fx_user')); } catch (e) {}
      if (!user || !user.id) {
        window.location.href = 'admin-login?redirect=' + encodeURIComponent(window.location.pathname);
        return;
      }
      var adminRoles = ['admin', 'super_admin'];
      if (adminRoles.indexOf(user.role) === -1) {
        // Redirect non-admins to their appropriate dashboard
        var dashMap = {
          corporate_staff:   'staff-dashboard',
          driver:            'driver-dashboard',
          owner_operator:    'driver-dashboard',
          shipper:           'shipper-dashboard',
          customer:          'shipper-dashboard',
          insurance_company: 'insurance-dashboard',
          trucking_company:  'trucking-dashboard',
          gas_station:       'gas-station-dashboard',
          hotel:             'hotel-dashboard',
        };
        window.location.href = dashMap[user.role] || 'login';
      }
    })();

    // ── State ─────────────────────────────────────────────────────
    var allEvents    = [];
    var filtered     = [];
    var currentPage  = 1;
    var perPage      = 25;
    var actChart     = null;
    var regsChart    = null;
    var refreshTimer = null;
    var isPaused     = false;

    // ── Helpers ───────────────────────────────────────────────────
    function esc(s) {
      return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function fmtDate(ts) {
      if (!ts) return '—';
      var d = new Date(ts.replace(' ', 'T'));
      return d.toLocaleString('en-GB', { day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit', second:'2-digit' });
    }

    function badgeClass(action) {
      if (!action) return 'default';
      var a = action.toLowerCase();
      if (a.includes('login'))   return 'login';
      if (a.includes('register')) return 'register';
      if (a.includes('quote'))   return 'quote';
      if (a.includes('driver'))  return 'driver';
      if (a.includes('load'))    return 'load';
      if (a.includes('kyc'))     return 'kyc';
      if (a.includes('contact')) return 'contact';
      if (a.includes('status'))  return 'status_changed';
      return 'default';
    }

    // ── Load stats + audit log ────────────────────────────────────
    async function loadAll() {
      await Promise.all([loadStats(), loadAuditLog()]);
      scheduleRefresh();
    }

    async function loadStats() {
      try {
        var res  = await fetch('audit.php?action=stats&t=' + Date.now());
        var data = await res.json();
        if (data.success) {
          renderKpis(data.summary);
          renderBreakdowns(data);
          renderActivityChart(data.activity_by_day || {});
          renderRegsChart(data.regs_by_day || {});
        }
      } catch (err) {
        console.error('Stats load error:', err);
      }
    }

    async function loadAuditLog() {
      var params = new URLSearchParams({ action: 'list', limit: 500 });
      var q     = document.getElementById('searchInput').value.trim();
      var type  = document.getElementById('typeFilter').value;
      var since = document.getElementById('sinceDate').value;
      var until = document.getElementById('untilDate').value;
      if (q)     params.set('q',           q);
      if (type)  params.set('entity_type', type);
      if (since) params.set('since',       since);
      if (until) params.set('until',       until);

      try {
        var res  = await fetch('audit.php?' + params.toString() + '&t=' + Date.now());
        var data = await res.json();
        allEvents = data.events || [];
        applyTableFilters();
      } catch (err) {
        document.getElementById('auditTableBody').innerHTML =
          '<tr><td colspan="7"><div class="empty-state">' +
          '<iconify-icon icon="lucide:alert-circle"></iconify-icon>' +
          '<p>Could not load audit log: ' + esc(err.message) + '</p></div></td></tr>';
      }
    }

    // ── Render KPI cards ──────────────────────────────────────────
    function renderKpis(s) {
      if (!s) return;
      var cards = [
        { icon:'lucide:users',         cls:'blue',   label:'Registered Users',   val:s.total_users        || 0, sub:'' },
        { icon:'lucide:truck',          cls:'green',  label:'Driver Applications',val:s.total_drivers      || 0, sub:'' },
        { icon:'lucide:file-text',      cls:'amber',  label:'Quote Requests',     val:s.total_quotes       || 0, sub:'' },
        { icon:'lucide:package',        cls:'pink',   label:'Load Requests',      val:s.total_loads        || 0, sub:'' },
        { icon:'lucide:mail',           cls:'teal',   label:'Contact Messages',   val:s.total_contacts     || 0, sub:'' },
        { icon:'lucide:bell',           cls:'purple', label:'Newsletter Subs',    val:s.newsletter_subs    || 0, sub:'' },
        { icon:'lucide:shield-check',   cls:'indigo', label:'Total Audit Events', val:s.total_audit_events || 0, sub:'' },
        { icon:'lucide:clock',          cls:'amber',  label:'Events (last 24 h)', val:s.audit_last_24h     || 0, sub:'' },
        { icon:'lucide:calendar-days',  cls:'slate',  label:'Events (last 7 d)',  val:s.audit_last_7d      || 0, sub:'' },
      ];
      document.getElementById('kpiGrid').innerHTML = cards.map(function (c) {
        return '<div class="kpi-card">' +
          '<div class="kpi-icon ' + c.cls + '">' +
            '<iconify-icon icon="' + c.icon + '"></iconify-icon>' +
          '</div>' +
          '<div class="kpi-label">' + c.label + '</div>' +
          '<div class="kpi-value">' + c.val.toLocaleString() + '</div>' +
          (c.sub ? '<div class="kpi-sub">' + c.sub + '</div>' : '') +
        '</div>';
      }).join('');
    }

    // ── Render breakdown cards ────────────────────────────────────
    function renderBreakdowns(data) {
      var sections = [
        { title: 'Users by Role',        obj: data.users_by_role      || {} },
        { title: 'Drivers by Status',    obj: data.drivers_by_status  || {} },
        { title: 'Quotes by Status',     obj: data.quotes_by_status   || {} },
        { title: 'Loads by Status',      obj: data.loads_by_status    || {} },
      ];
      document.getElementById('breakdownRow').innerHTML = sections.map(function (sec) {
        var keys   = Object.keys(sec.obj);
        var total  = keys.reduce(function (acc, k) { return acc + (sec.obj[k] || 0); }, 0);
        var rows   = keys.length
          ? keys.sort().map(function (k) {
              return '<div class="breakdown-item">' +
                '<span class="breakdown-label">' + esc(k) + '</span>' +
                '<span class="breakdown-count">' + sec.obj[k] + '</span>' +
              '</div>';
            }).join('')
          : '<p style="font-size:12px;color:var(--muted-foreground);text-align:center;">No data yet</p>';
        return '<div class="breakdown-card">' +
          '<div class="breakdown-title">' + esc(sec.title) + ' <span style="font-size:11px;font-weight:400;margin-left:4px;">(' + total + ' total)</span></div>' +
          rows +
        '</div>';
      }).join('');
    }

    // ── Charts ────────────────────────────────────────────────────
    var CHART_DEFAULTS = {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        x: { grid: { display: false }, ticks: { font: { size: 11 } } },
        y: { beginAtZero: true, ticks: { precision: 0, font: { size: 11 } }, grid: { color: '#e5e7eb' } },
      },
    };

    function shortDate(d) {
      // 'YYYY-MM-DD' → 'DD Mon'
      var p = d.split('-');
      var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
      return parseInt(p[2]) + ' ' + (months[parseInt(p[1]) - 1] || '');
    }

    function renderActivityChart(byDay) {
      var labels = Object.keys(byDay).map(shortDate);
      var values = Object.values(byDay);
      if (actChart) actChart.destroy();
      actChart = new Chart(document.getElementById('activityChart'), {
        type: 'bar',
        data: {
          labels: labels,
          datasets: [{ data: values, backgroundColor: 'rgba(79,70,229,.7)', borderRadius: 4 }],
        },
        options: CHART_DEFAULTS,
      });
    }

    function renderRegsChart(byDay) {
      var labels = Object.keys(byDay).map(shortDate);
      var values = Object.values(byDay);
      if (regsChart) regsChart.destroy();
      regsChart = new Chart(document.getElementById('regsChart'), {
        type: 'line',
        data: {
          labels: labels,
          datasets: [{
            data: values,
            borderColor: '#10b981',
            backgroundColor: 'rgba(16,185,129,.12)',
            borderWidth: 2,
            pointRadius: 3,
            fill: true,
            tension: 0.4,
          }],
        },
        options: CHART_DEFAULTS,
      });
    }

    // ── Audit table ───────────────────────────────────────────────
    function applyTableFilters() {
      filtered    = allEvents;
      currentPage = 1;
      renderTable();
    }

    function renderTable() {
      var tbody = document.getElementById('auditTableBody');
      var start = (currentPage - 1) * perPage;
      var page  = filtered.slice(start, start + perPage);

      if (!filtered.length) {
        tbody.innerHTML = '<tr><td colspan="7"><div class="empty-state">' +
          '<iconify-icon icon="lucide:inbox"></iconify-icon>' +
          '<p>No audit events found.</p></div></td></tr>';
        document.getElementById('pagination').style.display = 'none';
        return;
      }

      tbody.innerHTML = page.map(function (e) {
        var bc = badgeClass(e.action || '');
        return '<tr>' +
          '<td class="td-mono">' + esc(fmtDate(e.timestamp || '')) + '</td>' +
          '<td><span class="td-badge ' + bc + '">' + esc(e.action || '—') + '</span></td>' +
          '<td class="td-mono">' + esc(e.user_id     || '—') + '</td>' +
          '<td class="td-bold">' + esc(e.entity_type || '—') + '</td>' +
          '<td class="td-mono">' + esc(e.entity_id   || '—') + '</td>' +
          '<td style="max-width:260px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="' + esc(e.details || '') + '">' + esc(e.details || '—') + '</td>' +
          '<td class="td-mono">' + esc(e.ip_address  || '—') + '</td>' +
        '</tr>';
      }).join('');

      // Pagination
      var totalPages = Math.ceil(filtered.length / perPage);
      var pg = document.getElementById('pagination');
      if (totalPages > 1) {
        pg.style.display = 'flex';
        document.getElementById('paginationInfo').textContent =
          'Showing ' + (start + 1) + '–' + Math.min(start + perPage, filtered.length) + ' of ' + filtered.length;
        var btns = document.getElementById('paginationBtns');
        btns.innerHTML = '';
        var prev = document.createElement('button');
        prev.className = 'pag-btn'; prev.textContent = '‹';
        if (currentPage === 1) prev.disabled = true;
        prev.addEventListener('click', function () { currentPage--; renderTable(); });
        btns.appendChild(prev);

        var startPg = Math.max(1, currentPage - 2);
        var endPg   = Math.min(totalPages, startPg + 4);
        for (var p = startPg; p <= endPg; p++) {
          (function (pg2) {
            var b = document.createElement('button');
            b.className = 'pag-btn' + (pg2 === currentPage ? ' active' : '');
            b.textContent = pg2;
            b.addEventListener('click', function () { currentPage = pg2; renderTable(); });
            btns.appendChild(b);
          })(p);
        }

        var next = document.createElement('button');
        next.className = 'pag-btn'; next.textContent = '›';
        if (currentPage === totalPages) next.disabled = true;
        next.addEventListener('click', function () { currentPage++; renderTable(); });
        btns.appendChild(next);
      } else {
        pg.style.display = 'none';
      }
    }

    // ── Auto-refresh ──────────────────────────────────────────────
    function scheduleRefresh() {
      clearTimeout(refreshTimer);
      if (!isPaused) {
        refreshTimer = setTimeout(function () { loadAll(); }, 30000);
      }
    }

    document.getElementById('pauseBtn').addEventListener('click', function () {
      isPaused = !isPaused;
      var dot = document.getElementById('refreshDot');
      var lbl = document.getElementById('refreshLabel');
      this.textContent = isPaused ? 'Resume' : 'Pause';
      dot.classList.toggle('paused', isPaused);
      lbl.innerHTML = '<span class="refresh-dot' + (isPaused ? ' paused' : '') + '" id="refreshDot"></span>' +
        (isPaused ? 'Auto-refresh paused' : 'Auto-refresh 30s');
      if (!isPaused) { loadAll(); } else { clearTimeout(refreshTimer); }
    });

    document.getElementById('refreshNowBtn').addEventListener('click', function () {
      clearTimeout(refreshTimer);
      loadAll();
    });

    // ── Filter listeners ──────────────────────────────────────────
    ['searchInput', 'typeFilter', 'sinceDate', 'untilDate'].forEach(function (id) {
      document.getElementById(id).addEventListener('change', function () { loadAuditLog(); });
    });
    document.getElementById('searchInput').addEventListener('input', function () {
      clearTimeout(this._t);
      this._t = setTimeout(function () { loadAuditLog(); }, 300);
    });
    document.getElementById('clearFiltersBtn').addEventListener('click', function () {
      document.getElementById('searchInput').value = '';
      document.getElementById('typeFilter').value  = '';
      document.getElementById('sinceDate').value   = '';
      document.getElementById('untilDate').value   = '';
      loadAuditLog();
    });

    // ── Init ─────────────────────────────────────────────────────
    loadAll();
  </script>
</body>
</html>
