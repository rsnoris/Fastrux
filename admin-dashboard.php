<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <link rel="icon" href="favicon.svg" type="image/svg+xml" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard — Fastrux</title>
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

    /* ── Tabs ── */
    .tab-bar {
      background: var(--card);
      border-bottom: 1px solid var(--border);
      display: flex;
      gap: 0;
      overflow-x: auto;
    }
    .tab-item {
      padding: 14px 22px;
      font-size: 14px; font-weight: 500;
      color: var(--muted-foreground);
      cursor: pointer;
      border-bottom: 2px solid transparent;
      white-space: nowrap;
      display: flex; align-items: center; gap: 7px;
      transition: color .15s, border-color .15s;
      background: none; border-top: none; border-left: none; border-right: none;
    }
    .tab-item:hover { color: var(--foreground); }
    .tab-item.active { color: var(--primary); border-bottom-color: var(--primary); font-weight: 600; }

    /* ── Tab panes ── */
    .tab-pane { display: none; }
    .tab-pane.active { display: block; }

    /* ── Page layout ── */
    .page-content { padding: 32px 0; }

    /* ── Welcome banner ── */
    .welcome-banner {
      background: linear-gradient(135deg, #1e3a5f 0%, #1e40af 100%);
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
      border: 1px solid rgba(255,255,255,.35);
      border-radius: 999px;
      padding: 6px 14px;
      font-size: 13px; font-weight: 600;
      white-space: nowrap;
    }
    .role-badge.super-admin { background: #fbbf24; color: #1c1400; border-color: #f59e0b; }
    .role-badge.admin       { background: rgba(255,255,255,.2); color: #fff; }

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
    .stat-icon.blue    { background: var(--secondary); color: var(--primary); }
    .stat-icon.green   { background: #e6f9ee; color: var(--success); }
    .stat-icon.amber   { background: #fff7e6; color: #d97706; }
    .stat-icon.red     { background: #fef2f2; color: var(--destructive); }
    .stat-icon.purple  { background: #ede9fe; color: #7c3aed; }
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

    /* ── Charts row ── */
    .charts-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
      margin-bottom: 24px;
    }
    .chart-wrap { position: relative; height: 220px; }

    /* ── Pending staff table ── */
    .data-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .data-table th {
      background: var(--muted); color: var(--muted-foreground);
      font-size: 11px; font-weight: 600; text-transform: uppercase;
      padding: 8px 12px; text-align: left; border-bottom: 1px solid var(--border);
    }
    .data-table td {
      padding: 12px;
      border-bottom: 1px solid var(--border);
      vertical-align: middle;
    }
    .data-table tr:last-child td { border-bottom: none; }
    .data-table tr:hover td { background: var(--muted); }

    /* ── Status badges ── */
    .badge {
      display: inline-flex; align-items: center; gap: 4px;
      padding: 3px 10px; border-radius: 999px;
      font-size: 11px; font-weight: 600;
    }
    .badge.pending  { background: #fef9c3; color: #854d0e; }
    .badge.active   { background: #dcfce7; color: #15803d; }
    .badge.rejected { background: #fef2f2; color: #be123c; }
    .badge.admin    { background: #dbeafe; color: #1d4ed8; }
    .badge.super    { background: #fef3c7; color: #92400e; }
    .badge.staff    { background: #ede9fe; color: #6d28d9; }
    .badge.driver   { background: #f0fdf4; color: #166534; }
    .badge.shipper  { background: #f0f9ff; color: #0c4a6e; }

    /* ── Action buttons ── */
    .btn-approve { background: #22c55e; color: #fff; border: none; border-radius: var(--radius-md); padding: 6px 12px; font-size: 12px; cursor: pointer; font-weight: 600; }
    .btn-approve:hover { background: #16a34a; }
    .btn-reject  { background: var(--destructive); color: #fff; border: none; border-radius: var(--radius-md); padding: 6px 12px; font-size: 12px; cursor: pointer; font-weight: 600; }
    .btn-reject:hover  { opacity: .85; }

    /* ── Role pill select ── */
    .role-select {
      padding: 5px 8px; border-radius: var(--radius-md); font-size: 12px;
      border: 1px solid var(--border); background: var(--card); cursor: pointer;
    }

    /* ── Audit log table ── */
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
    .action-badge.staff    { background: #fef3c7; color: #92400e; }
    .action-badge.admin    { background: #dbeafe; color: #1e40af; }
    .action-badge.default  { background: var(--muted); color: var(--muted-foreground); }

    /* ── Create admin modal ── */
    .modal-overlay {
      display: none; position: fixed; inset: 0;
      background: rgba(0,0,0,.45); z-index: 200;
      align-items: center; justify-content: center;
    }
    .modal-overlay.open { display: flex; }
    .modal-box {
      background: var(--card); border-radius: var(--radius-xl);
      padding: 32px; width: 100%; max-width: 480px;
      box-shadow: 0 24px 48px rgba(0,0,0,.2);
    }
    .modal-title { font-size: 18px; font-weight: 800; margin: 0 0 20px; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .form-group { margin-bottom: 16px; }
    .form-group label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; }
    .form-group input, .form-group select {
      width: 100%; padding: 10px 14px; border-radius: var(--radius-md);
      border: 1px solid var(--border); background: var(--muted);
      font-size: 14px; font-family: inherit; box-sizing: border-box;
    }
    .form-group input:focus, .form-group select:focus { outline: none; border-color: var(--primary); }

    /* ── Toast ── */
    #toast {
      position: fixed; bottom: 24px; right: 24px; z-index: 9999;
      background: var(--foreground); color: var(--background);
      padding: 12px 20px; border-radius: var(--radius-md);
      font-size: 14px; font-weight: 500;
      opacity: 0; transform: translateY(8px);
      transition: opacity .2s, transform .2s;
      pointer-events: none;
    }
    #toast.show { opacity: 1; transform: translateY(0); }

    /* ── Pending alert ── */
    .pending-alert {
      background: #fffbeb; border: 1px solid #fbbf24;
      border-radius: var(--radius-xl); padding: 16px 20px;
      margin-bottom: 20px;
      display: flex; align-items: center; gap: 10px;
      font-size: 14px; color: #92400e;
    }

    /* ── Super-admin section gating ── */
    .super-only { display: none; }

    @media (max-width: 900px) {
      .stats-grid  { grid-template-columns: repeat(2, 1fr); }
      .charts-row  { grid-template-columns: 1fr; }
    }
    @media (max-width: 540px) {
      .stats-grid  { grid-template-columns: 1fr 1fr; }
      .welcome-banner { flex-direction: column; align-items: flex-start; }
      .form-row { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>

  <!-- ── Header ── -->
  <header class="dash-header">
    <div class="container dash-header-inner">
      <a href="index.php" class="dash-brand">
        <iconify-icon icon="lucide:truck" style="font-size:24px"></iconify-icon>
        Fastrux <span>&nbsp;/ Admin Dashboard</span>
      </a>
      <div style="display:flex;align-items:center;gap:12px;">
        <a href="observability.php" class="btn btn-outline" style="padding:8px 14px;font-size:13px;">
          <iconify-icon icon="lucide:activity" style="font-size:14px;margin-right:5px"></iconify-icon>Observability
        </a>
        <a href="driver-dashboard.php" class="btn btn-outline" style="padding:8px 14px;font-size:13px;">
          <iconify-icon icon="lucide:users" style="font-size:14px;margin-right:5px"></iconify-icon>Drivers
        </a>
        <a href="quote-dashboard.php" class="btn btn-outline" style="padding:8px 14px;font-size:13px;">
          <iconify-icon icon="lucide:file-text" style="font-size:14px;margin-right:5px"></iconify-icon>Quotes
        </a>
        <a href="index.php" class="btn btn-primary" style="padding:8px 14px;font-size:13px;">
          <iconify-icon icon="lucide:home" style="font-size:14px;margin-right:5px"></iconify-icon>Main Site
        </a>
      </div>
    </div>
  </header>

  <!-- ── Tab bar ── -->
  <div class="tab-bar">
    <button class="tab-item active" data-tab="overview" onclick="switchTab('overview')">
      <iconify-icon icon="lucide:layout-dashboard"></iconify-icon> Overview
    </button>
    <button class="tab-item" data-tab="staff-approval" onclick="switchTab('staff-approval')">
      <iconify-icon icon="lucide:user-check"></iconify-icon> Staff Approvals
      <span id="pendingBadge" style="display:none;background:#ef4444;color:#fff;border-radius:999px;padding:1px 7px;font-size:11px;font-weight:700;"></span>
    </button>
    <button class="tab-item" data-tab="users" onclick="switchTab('users')">
      <iconify-icon icon="lucide:users"></iconify-icon> User Management
    </button>
    <button class="tab-item super-only" data-tab="create-admin" onclick="switchTab('create-admin')" id="createAdminTab">
      <iconify-icon icon="lucide:shield-plus"></iconify-icon> Create Admin
    </button>
  </div>

  <!-- ── Main Content ── -->
  <main class="container page-content">

    <!-- ── TAB: Overview ── -->
    <div class="tab-pane active" id="tab-overview">

      <!-- Welcome banner -->
      <div class="welcome-banner">
        <div>
          <h1>Welcome, <span id="adminName">Admin</span>!</h1>
          <p id="adminSubtitle">Full administrative control over the Fastrux platform.</p>
        </div>
        <span class="role-badge" id="adminRoleBadge">Admin</span>
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

      <!-- Roles distribution -->
      <div class="card">
        <div class="card-title">
          <iconify-icon icon="lucide:pie-chart"></iconify-icon>
          Users by Role
        </div>
        <div id="rolesList" style="display:flex;gap:12px;flex-wrap:wrap;"></div>
      </div>

    </div><!-- /tab-overview -->

    <!-- ── TAB: Staff Approvals ── -->
    <div class="tab-pane" id="tab-staff-approval">
      <div class="card">
        <div class="card-title" style="justify-content:space-between;flex-wrap:wrap;gap:8px;">
          <span style="display:flex;align-items:center;gap:8px;">
            <iconify-icon icon="lucide:user-check"></iconify-icon>
            Pending Staff Account Approvals
          </span>
          <button class="btn btn-outline" style="font-size:13px;padding:8px 14px;" onclick="loadPendingStaff()">
            <iconify-icon icon="lucide:refresh-cw" style="font-size:13px;margin-right:4px"></iconify-icon>Refresh
          </button>
        </div>
        <div id="pendingAlert" class="pending-alert" style="display:none;">
          <iconify-icon icon="lucide:alert-triangle" style="font-size:18px;flex-shrink:0;"></iconify-icon>
          <span id="pendingAlertText"></span>
        </div>
        <div style="overflow-x:auto;">
          <table class="data-table" id="pendingTable">
            <thead>
              <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Company</th>
                <th>Registered</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="pendingBody">
              <tr><td colspan="6" style="text-align:center;padding:24px;color:var(--muted-foreground);">Loading…</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div><!-- /tab-staff-approval -->

    <!-- ── TAB: User Management ── -->
    <div class="tab-pane" id="tab-users">
      <div class="card">
        <div class="card-title" style="justify-content:space-between;flex-wrap:wrap;gap:8px;">
          <span style="display:flex;align-items:center;gap:8px;">
            <iconify-icon icon="lucide:users"></iconify-icon>
            All Platform Users
          </span>
          <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
            <input type="text" id="userSearch" placeholder="Search users…" class="form-control"
                   style="padding:8px 12px;font-size:13px;min-width:200px;"
                   oninput="filterUsers()" />
            <button class="btn btn-outline" style="font-size:13px;padding:8px 14px;" onclick="loadAllUsers()">
              <iconify-icon icon="lucide:refresh-cw" style="font-size:13px;margin-right:4px"></iconify-icon>Refresh
            </button>
          </div>
        </div>
        <div style="overflow-x:auto;">
          <table class="data-table" id="usersTable">
            <thead>
              <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Registered</th>
                <th id="changeRoleHeader" style="display:none;">Change Role</th>
              </tr>
            </thead>
            <tbody id="usersBody">
              <tr><td colspan="6" style="text-align:center;padding:24px;color:var(--muted-foreground);">Loading…</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div><!-- /tab-users -->

    <!-- ── TAB: Create Admin (Super-Admin only) ── -->
    <div class="tab-pane super-only" id="tab-create-admin">
      <div class="card" style="max-width:560px;">
        <div class="card-title">
          <iconify-icon icon="lucide:shield-plus"></iconify-icon>
          Create New Admin Account
        </div>
        <p style="color:var(--muted-foreground);font-size:14px;margin-top:-10px;margin-bottom:20px;">
          Only Super-Admins can create Admin or Super-Admin accounts. These accounts have elevated platform privileges.
        </p>
        <div id="createAdminFeedback" class="form-feedback" style="display:none;margin-bottom:16px;"></div>
        <form id="createAdminForm" novalidate>
          <div class="form-row">
            <div class="form-group">
              <label>First name *</label>
              <input type="text" name="firstName" placeholder="Jane" required />
            </div>
            <div class="form-group">
              <label>Last name *</label>
              <input type="text" name="lastName" placeholder="Smith" required />
            </div>
          </div>
          <div class="form-group">
            <label>Email address *</label>
            <input type="email" name="email" placeholder="admin@fastrux.com" required />
          </div>
          <div class="form-group">
            <label>Password * <span style="font-weight:400;font-size:12px;color:var(--muted-foreground);">(min. 8 characters)</span></label>
            <input type="password" name="password" placeholder="••••••••" required minlength="8" />
          </div>
          <div class="form-group">
            <label>Admin role *</label>
            <select name="role">
              <option value="admin">Admin — approve staff, manage users</option>
              <option value="super_admin">Super-Admin — full platform control</option>
            </select>
          </div>
          <button type="submit" class="btn btn-primary" id="createAdminBtn" style="width:100%;padding:12px;">
            Create Admin Account
          </button>
        </form>
      </div>
    </div><!-- /tab-create-admin -->

  </main>

  <!-- Toast notification -->
  <div id="toast"></div>

  <script>
    // ── Auth guard — admin / super_admin only ─────────────────────
    var currentUser = null;
    (function () {
      try { currentUser = JSON.parse(localStorage.getItem('fx_user')); } catch (e) {}
      if (!currentUser || !currentUser.id) {
        window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.pathname);
        return;
      }
      var adminRoles = ['admin', 'super_admin'];
      if (adminRoles.indexOf(currentUser.role) === -1) {
        if (currentUser.role === 'corporate_staff') {
          window.location.href = 'staff-dashboard.php';
        } else if (currentUser.role === 'driver' || currentUser.role === 'owner_operator') {
          window.location.href = 'driver-dashboard.php';
        } else {
          window.location.href = 'shipper-dashboard.php';
        }
      }
    })();

    var isSuperAdmin = currentUser && currentUser.role === 'super_admin';

    // ── Show super-admin exclusive elements ───────────────────────
    (function () {
      if (!currentUser) return;
      document.getElementById('adminName').textContent = currentUser.first_name || 'Admin';

      if (isSuperAdmin) {
        document.getElementById('adminRoleBadge').textContent = 'Super-Admin';
        document.getElementById('adminRoleBadge').classList.add('super-admin');
        document.getElementById('adminSubtitle').textContent = 'Super-Admin: full platform control including role management and admin creation.';
        document.querySelectorAll('.super-only').forEach(function (el) { el.style.display = ''; });
        document.getElementById('changeRoleHeader').style.display = '';
      } else {
        document.getElementById('adminRoleBadge').textContent = 'Admin';
      }
    })();

    // ── Tab switching ──────────────────────────────────────────────
    function switchTab(name) {
      document.querySelectorAll('.tab-item').forEach(function (t) {
        t.classList.toggle('active', t.dataset.tab === name);
      });
      document.querySelectorAll('.tab-pane').forEach(function (p) {
        p.classList.toggle('active', p.id === 'tab-' + name);
      });
      if (name === 'staff-approval') loadPendingStaff();
      if (name === 'users')          loadAllUsers();
    }

    // ── Toast ──────────────────────────────────────────────────────
    function showToast(msg, isError) {
      var t = document.getElementById('toast');
      t.textContent = msg;
      t.style.background = isError ? 'var(--destructive)' : 'var(--foreground)';
      t.classList.add('show');
      setTimeout(function () { t.classList.remove('show'); }, 3000);
    }

    // ── Helpers ───────────────────────────────────────────────────
    function esc(s) {
      return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
    function fmtDate(ts) {
      if (!ts) return '—';
      return new Date(ts.replace(' ', 'T')).toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' });
    }
    function roleBadgeClass(role) {
      var map = { admin: 'admin', super_admin: 'super', corporate_staff: 'staff', driver: 'driver', owner_operator: 'driver', shipper: 'shipper', customer: 'shipper' };
      return map[role] || 'shipper';
    }
    function statusBadgeClass(s) {
      return s === 'active' ? 'active' : (s === 'rejected' ? 'rejected' : 'pending');
    }

    // ── Load overview stats ────────────────────────────────────────
    var actChart = null;
    var regsChart = null;

    async function loadStats() {
      try {
        var res  = await fetch('audit.php?action=stats&t=' + Date.now());
        var data = await res.json();
        if (!data.success) return;
        var s = data.summary || {};
        document.getElementById('statUsers').textContent     = s.total_users    ?? '—';
        document.getElementById('statDrivers').textContent   = s.total_drivers  ?? '—';
        document.getElementById('statQuotes').textContent    = s.total_quotes   ?? '—';
        document.getElementById('statEvents24h').textContent = s.audit_last_24h ?? '—';

        renderActivityChart(data.activity_by_day || {});
        renderRegsChart(data.regs_by_day || {});
        renderRoles(data.users_by_role || {});
      } catch (e) { console.error('Stats load failed', e); }
    }

    function renderActivityChart(byDay) {
      var labels = Object.keys(byDay), vals = Object.values(byDay);
      var ctx    = document.getElementById('activityChart').getContext('2d');
      if (actChart) actChart.destroy();
      actChart = new Chart(ctx, {
        type: 'bar',
        data: { labels, datasets: [{ label: 'Events', data: vals, backgroundColor: '#3b82f680', borderColor: '#3b82f6', borderWidth: 1, borderRadius: 4 }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
      });
    }

    function renderRegsChart(byDay) {
      var labels = Object.keys(byDay), vals = Object.values(byDay);
      var ctx    = document.getElementById('regsChart').getContext('2d');
      if (regsChart) regsChart.destroy();
      regsChart = new Chart(ctx, {
        type: 'line',
        data: { labels, datasets: [{ label: 'Registrations', data: vals, borderColor: '#22c55e', backgroundColor: '#22c55e18', fill: true, tension: 0.4, pointRadius: 3 }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
      });
    }

    function renderRoles(byRole) {
      var el   = document.getElementById('rolesList');
      var html = '';
      var colorMap = { admin: '#1d4ed8', super_admin: '#d97706', corporate_staff: '#7c3aed', driver: '#166534', owner_operator: '#166534', shipper: '#0c4a6e', customer: '#0c4a6e' };
      Object.entries(byRole).forEach(function ([role, count]) {
        var color = colorMap[role] || '#555';
        html += '<div style="background:var(--muted);border-radius:var(--radius-md);padding:12px 18px;display:flex;align-items:center;gap:10px;">' +
          '<span style="width:10px;height:10px;border-radius:50%;background:' + esc(color) + ';display:inline-block;"></span>' +
          '<span style="font-weight:600;font-size:14px;">' + esc(role) + '</span>' +
          '<span style="color:var(--muted-foreground);font-size:13px;">' + esc(String(count)) + '</span>' +
          '</div>';
      });
      el.innerHTML = html || '<span style="color:var(--muted-foreground);font-size:14px;">No users yet.</span>';
    }

    // ── Load pending staff ─────────────────────────────────────────
    async function loadPendingStaff() {
      var tbody = document.getElementById('pendingBody');
      tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:24px;color:var(--muted-foreground);">Loading…</td></tr>';

      try {
        var url  = 'admin_api.php?action=pending_staff&requesting_user_id=' + encodeURIComponent(currentUser.id) + '&t=' + Date.now();
        var res  = await fetch(url);
        var data = await res.json();

        if (!data.success) {
          tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:24px;color:var(--destructive);">Error: ' + esc(data.message) + '</td></tr>';
          return;
        }

        var users = data.users || [];

        // Update pending badge count
        var badge = document.getElementById('pendingBadge');
        if (users.length > 0) {
          badge.style.display = '';
          badge.textContent   = users.length;
          var alertEl   = document.getElementById('pendingAlert');
          var alertText = document.getElementById('pendingAlertText');
          alertEl.style.display = 'flex';
          alertText.textContent = users.length + ' staff account' + (users.length > 1 ? 's are' : ' is') + ' waiting for approval.';
        } else {
          badge.style.display = 'none';
          document.getElementById('pendingAlert').style.display = 'none';
        }

        if (!users.length) {
          tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:24px;color:var(--muted-foreground);">No pending staff applications.</td></tr>';
          return;
        }

        tbody.innerHTML = users.map(function (u) {
          return '<tr>' +
            '<td><strong>' + esc((u.first_name || '') + ' ' + (u.last_name || '')) + '</strong></td>' +
            '<td>' + esc(u.email || '—') + '</td>' +
            '<td>' + esc(u.company || '—') + '</td>' +
            '<td style="white-space:nowrap;">' + esc(fmtDate(u.timestamp)) + '</td>' +
            '<td><span class="badge pending">Pending</span></td>' +
            '<td style="white-space:nowrap;display:flex;gap:8px;">' +
              '<button class="btn-approve" onclick="approveStaff(\'' + esc(u.id) + '\', this)">Approve</button>' +
              '<button class="btn-reject"  onclick="rejectStaff(\''  + esc(u.id) + '\', this)">Reject</button>' +
            '</td>' +
            '</tr>';
        }).join('');
      } catch (e) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:24px;color:var(--destructive);">Network error.</td></tr>';
      }
    }

    async function approveStaff(userId, btn) {
      btn.disabled = true; btn.textContent = '…';
      try {
        var fd = new FormData();
        fd.append('action',              'approve_staff');
        fd.append('requesting_user_id',  currentUser.id);
        fd.append('target_user_id',      userId);
        var res  = await fetch('admin_api.php', { method: 'POST', body: fd });
        var data = await res.json();
        showToast(data.success ? '✓ Staff account approved.' : ('✗ ' + data.message), !data.success);
        if (data.success) loadPendingStaff();
      } catch (e) { showToast('✗ Network error.', true); btn.disabled = false; btn.textContent = 'Approve'; }
    }

    async function rejectStaff(userId, btn) {
      var reason = prompt('Reason for rejection (optional):') ?? '';
      btn.disabled = true; btn.textContent = '…';
      try {
        var fd = new FormData();
        fd.append('action',              'reject_staff');
        fd.append('requesting_user_id',  currentUser.id);
        fd.append('target_user_id',      userId);
        if (reason) fd.append('reason', reason);
        var res  = await fetch('admin_api.php', { method: 'POST', body: fd });
        var data = await res.json();
        showToast(data.success ? '✓ Application rejected.' : ('✗ ' + data.message), !data.success);
        if (data.success) loadPendingStaff();
      } catch (e) { showToast('✗ Network error.', true); btn.disabled = false; btn.textContent = 'Reject'; }
    }

    // ── Load all users ─────────────────────────────────────────────
    var allUsers = [];

    async function loadAllUsers() {
      var tbody = document.getElementById('usersBody');
      tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:24px;color:var(--muted-foreground);">Loading…</td></tr>';
      try {
        var url  = 'admin_api.php?action=users&requesting_user_id=' + encodeURIComponent(currentUser.id) + '&t=' + Date.now();
        var res  = await fetch(url);
        var data = await res.json();
        if (!data.success) {
          tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:24px;color:var(--destructive);">Error: ' + esc(data.message) + '</td></tr>';
          return;
        }
        allUsers = data.users || [];
        renderUsers(allUsers);
      } catch (e) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:24px;color:var(--destructive);">Network error.</td></tr>';
      }
    }

    function filterUsers() {
      var q = (document.getElementById('userSearch').value || '').toLowerCase();
      if (!q) { renderUsers(allUsers); return; }
      var filtered = allUsers.filter(function (u) {
        return ((u.first_name || '') + ' ' + (u.last_name || '') + ' ' + (u.email || '') + ' ' + (u.role || '')).toLowerCase().includes(q);
      });
      renderUsers(filtered);
    }

    function renderUsers(users) {
      var tbody = document.getElementById('usersBody');
      if (!users.length) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:24px;color:var(--muted-foreground);">No users found.</td></tr>';
        return;
      }

      var allRoles = ['shipper', 'customer', 'driver', 'owner_operator', 'corporate_staff', 'admin', 'super_admin'];

      tbody.innerHTML = users.map(function (u) {
        var rclass = roleBadgeClass(u.role);
        var sclass = statusBadgeClass(u.status || 'active');
        var changeRoleCell = '';
        if (isSuperAdmin) {
          var opts = allRoles.map(function (r) {
            return '<option value="' + r + '"' + (r === u.role ? ' selected' : '') + '>' + r + '</option>';
          }).join('');
          changeRoleCell = '<td><div style="display:flex;gap:8px;align-items:center;">' +
            '<select class="role-select" id="rolesel-' + esc(u.id) + '">' + opts + '</select>' +
            '<button class="btn-approve" style="font-size:11px;padding:4px 10px;" onclick="changeRole(\'' + esc(u.id) + '\')">Apply</button>' +
            '</div></td>';
        }
        return '<tr>' +
          '<td><strong>' + esc((u.first_name || '') + ' ' + (u.last_name || '')) + '</strong><br>' +
          '<span style="font-size:11px;color:var(--muted-foreground);font-family:monospace;">' + esc(u.id || '') + '</span></td>' +
          '<td>' + esc(u.email || '—') + '</td>' +
          '<td><span class="badge ' + rclass + '">' + esc(u.role || '—') + '</span></td>' +
          '<td><span class="badge ' + sclass + '">' + esc(u.status || 'active') + '</span></td>' +
          '<td style="white-space:nowrap;">' + esc(fmtDate(u.timestamp)) + '</td>' +
          changeRoleCell +
          '</tr>';
      }).join('');

      // Show/hide the change role column header based on super admin
      document.getElementById('changeRoleHeader').style.display = isSuperAdmin ? '' : 'none';
    }

    async function changeRole(userId) {
      var sel    = document.getElementById('rolesel-' + userId);
      var newRole = sel ? sel.value : '';
      if (!newRole) return;

      if (!confirm('Change role to "' + newRole + '" for user ' + userId + '?')) return;

      try {
        var fd = new FormData();
        fd.append('action',              'change_role');
        fd.append('requesting_user_id',  currentUser.id);
        fd.append('target_user_id',      userId);
        fd.append('new_role',            newRole);
        var res  = await fetch('admin_api.php', { method: 'POST', body: fd });
        var data = await res.json();
        showToast(data.success ? '✓ ' + data.message : ('✗ ' + data.message), !data.success);
        if (data.success) loadAllUsers();
      } catch (e) { showToast('✗ Network error.', true); }
    }

    // ── Create admin form ──────────────────────────────────────────
    var createAdminForm = document.getElementById('createAdminForm');
    if (createAdminForm) {
      createAdminForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        var btn      = document.getElementById('createAdminBtn');
        var feedback = document.getElementById('createAdminFeedback');
        btn.disabled = true;
        btn.textContent = 'Creating…';
        feedback.style.display = 'none';

        try {
          var fd = new FormData(this);
          fd.append('action',             'create_admin');
          fd.append('requesting_user_id', currentUser.id);
          var res  = await fetch('admin_api.php', { method: 'POST', body: fd });
          var data = await res.json();

          feedback.style.display = 'flex';
          if (data.success) {
            feedback.className   = 'form-feedback success';
            feedback.textContent = '✓ Admin account created successfully.';
            createAdminForm.reset();
          } else {
            feedback.className   = 'form-feedback error';
            feedback.textContent = '✗ ' + data.message;
          }
        } catch (e) {
          feedback.style.display = 'flex';
          feedback.className     = 'form-feedback error';
          feedback.textContent   = '✗ Network error.';
        }

        btn.disabled    = false;
        btn.textContent = 'Create Admin Account';
      });
    }

    // ── Init ──────────────────────────────────────────────────────
    loadStats();
    // Check pending staff count for badge
    (async function () {
      try {
        var url  = 'admin_api.php?action=pending_staff&requesting_user_id=' + encodeURIComponent(currentUser.id);
        var res  = await fetch(url);
        var data = await res.json();
        if (data.success && (data.total || 0) > 0) {
          var badge = document.getElementById('pendingBadge');
          badge.style.display = '';
          badge.textContent   = data.total;
        }
      } catch (e) {}
    })();

    setInterval(loadStats, 60000);
  </script>
</body>
</html>
