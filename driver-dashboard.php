<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <link rel="icon" href="favicon.svg" type="image/svg+xml" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Driver Submissions Dashboard — Fastrux</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="shared.css" />
  <script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>
  <style>
    /* ── Dashboard layout ── */
    body { background: var(--muted); }

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

    /* ── Stats cards ── */
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
    .stat-label { font-size: 13px; color: var(--muted-foreground); font-weight: 500; margin-bottom: 4px; }
    .stat-value { font-size: 28px; font-weight: 800; line-height: 1; }

    /* ── Toolbar ── */
    .toolbar {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 20px;
      flex-wrap: wrap;
    }
    .toolbar-search {
      position: relative; flex: 1; min-width: 240px;
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
    }
    .table-wrap {
      overflow-x: auto;
    }
    table {
      width: 100%;
      border-collapse: collapse;
    }
    thead th {
      background: var(--muted);
      font-size: 12px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .06em;
      color: var(--muted-foreground);
      padding: 12px 16px;
      text-align: left;
      white-space: nowrap;
      border-bottom: 1px solid var(--border);
    }
    thead th.sortable { cursor: pointer; user-select: none; }
    thead th.sortable:hover { color: var(--foreground); }
    tbody tr {
      border-bottom: 1px solid var(--border);
      transition: background .12s;
    }
    tbody tr:last-child { border-bottom: none; }
    tbody tr:hover { background: var(--secondary); }
    td {
      padding: 14px 16px;
      font-size: 14px;
      vertical-align: middle;
    }
    .td-name {
      font-weight: 600;
      white-space: nowrap;
    }
    .td-sub { font-size: 12px; color: var(--muted-foreground); margin-top: 2px; }
    .td-mono {
      font-family: monospace;
      font-size: 13px;
      color: var(--primary);
      font-weight: 600;
    }

    /* Status badges */
    .badge {
      display: inline-flex; align-items: center; gap: 5px;
      padding: 4px 10px;
      border-radius: 999px;
      font-size: 12px;
      font-weight: 600;
      white-space: nowrap;
    }
    .badge-pending  { background:#fff7e6; color:#d97706; border:1px solid #fde68a; }
    .badge-approved { background:#e6f9ee; color:var(--success); border:1px solid #a7f0c4; }
    .badge-rejected { background:#fef2f2; color:var(--destructive); border:1px solid #fecaca; }
    .badge-dot { width:6px; height:6px; border-radius:50%; background:currentColor; }

    /* Action buttons */
    .action-btn {
      background: none; border: none; cursor: pointer;
      color: var(--muted-foreground); padding: 6px;
      border-radius: var(--radius-sm);
      display: inline-flex; align-items: center;
      transition: color .15s, background .15s;
    }
    .action-btn:hover { color: var(--primary); background: var(--secondary); }
    .action-btn.danger:hover { color: var(--destructive); background: #fef2f2; }

    /* Pagination */
    .pagination {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 14px 16px;
      border-top: 1px solid var(--border);
      font-size: 13px;
      color: var(--muted-foreground);
    }
    .pag-btns { display: flex; gap: 6px; }
    .pag-btn {
      width: 32px; height: 32px;
      display: flex; align-items: center; justify-content: center;
      border: 1px solid var(--border);
      border-radius: var(--radius-sm);
      cursor: pointer; background: var(--card);
      font-size: 13px; font-weight: 500;
      transition: background .15s;
    }
    .pag-btn:hover { background: var(--secondary); }
    .pag-btn.active { background: var(--primary); color: #fff; border-color: var(--primary); }
    .pag-btn:disabled { opacity: .4; cursor: default; }

    /* ── Detail Modal ── */
    .modal-backdrop {
      position: fixed; inset: 0;
      background: rgba(0,0,0,.45);
      display: flex; align-items: flex-start; justify-content: center;
      padding: 40px 20px;
      z-index: 1000;
      overflow-y: auto;
    }
    .modal-backdrop.hidden { display: none; }
    .modal {
      background: var(--card);
      border-radius: var(--radius-xl);
      width: 100%; max-width: 860px;
      box-shadow: var(--shadow-xl);
      overflow: hidden;
      margin: auto;
    }
    .modal-header {
      display: flex; align-items: center; justify-content: space-between;
      padding: 20px 28px;
      border-bottom: 1px solid var(--border);
    }
    .modal-header h2 { font-size: 18px; font-weight: 700; }
    .modal-close {
      background: none; border: none; cursor: pointer;
      color: var(--muted-foreground); padding: 6px;
      border-radius: var(--radius-sm);
      display: flex; align-items: center;
      transition: color .15s;
    }
    .modal-close:hover { color: var(--foreground); }
    .modal-body { padding: 28px; overflow-y: auto; max-height: 70vh; }
    .modal-footer {
      padding: 16px 28px;
      border-top: 1px solid var(--border);
      display: flex; gap: 10px; justify-content: flex-end;
    }

    /* Detail sections */
    .detail-section { margin-bottom: 28px; }
    .detail-section-title {
      font-size: 12px; font-weight: 700;
      text-transform: uppercase; letter-spacing: .07em;
      color: var(--muted-foreground);
      margin-bottom: 14px;
      padding-bottom: 8px;
      border-bottom: 1px solid var(--border);
      display: flex; align-items: center; gap: 8px;
    }
    .detail-grid {
      display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;
    }
    .detail-item label {
      font-size: 12px; font-weight: 600; color: var(--muted-foreground);
      text-transform: uppercase; letter-spacing: .04em; display: block; margin-bottom: 3px;
    }
    .detail-item p { font-size: 14px; font-weight: 500; color: var(--foreground); word-break: break-word; }

    .photos-grid {
      display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px;
    }
    .photo-thumb {
      aspect-ratio: 1;
      background: var(--muted);
      border-radius: var(--radius-md);
      overflow: hidden;
      border: 1px solid var(--border);
      cursor: pointer;
      position: relative;
    }
    .photo-thumb img {
      width: 100%; height: 100%; object-fit: cover;
    }
    .photo-thumb .photo-label {
      position: absolute; bottom: 0; left: 0; right: 0;
      background: rgba(0,0,0,.55); color: #fff;
      font-size: 10px; font-weight: 600;
      padding: 4px 6px;
      text-transform: uppercase;
      letter-spacing: .04em;
    }
    .photo-thumb.no-img {
      display: flex; flex-direction: column; align-items: center; justify-content: center;
      gap: 6px; color: var(--muted-foreground); font-size: 12px;
    }

    /* Status update select in modal */
    .status-row {
      display: flex; align-items: center; gap: 12px;
    }
    .status-row label { font-size: 14px; font-weight: 600; white-space: nowrap; }
    .status-row select {
      padding: 8px 32px 8px 12px;
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

    /* Empty state */
    .empty-state {
      text-align: center; padding: 60px 40px; color: var(--muted-foreground);
    }
    .empty-state iconify-icon { font-size: 48px; margin-bottom: 16px; opacity: .4; }
    .empty-state p { font-size: 15px; }

    /* Loading skeleton */
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

    @media (max-width: 1024px) {
      .stats-grid { grid-template-columns: repeat(2, 1fr); }
      .detail-grid { grid-template-columns: repeat(2, 1fr); }
      .photos-grid { grid-template-columns: repeat(3, 1fr); }
    }
    @media (max-width: 640px) {
      .stats-grid { grid-template-columns: 1fr 1fr; }
      .photos-grid { grid-template-columns: repeat(2, 1fr); }
    }
  </style>
</head>
<body>

  <!-- ── Dashboard Header ── -->
  <header class="dash-header">
    <div class="container dash-header-inner">
      <a href="index" class="dash-brand">
        <iconify-icon icon="lucide:truck" style="font-size:24px"></iconify-icon>
        Fastrux <span>&nbsp;/ Driver Dashboard</span>
      </a>
      <div style="display:flex;align-items:center;gap:12px;">
        <a href="messages" class="btn btn-outline" style="padding:8px 16px;font-size:13px;">
          <iconify-icon icon="lucide:message-circle" style="font-size:15px;margin-right:6px"></iconify-icon>
          Messages
        </a>
        <a href="documents" class="btn btn-outline" style="padding:8px 16px;font-size:13px;">
          <iconify-icon icon="lucide:file-text" style="font-size:15px;margin-right:6px"></iconify-icon>
          Documents
        </a>
        <a href="maps" class="btn btn-outline" style="padding:8px 16px;font-size:13px;">
          <iconify-icon icon="lucide:map" style="font-size:15px;margin-right:6px"></iconify-icon>
          Live Map
        </a>
        <a href="offers-tracking" class="btn btn-outline" style="padding:8px 16px;font-size:13px;">
          <iconify-icon icon="lucide:map" style="font-size:15px;margin-right:6px"></iconify-icon>
          Offers Tracking
        </a>
        <a href="driver-onboarding" class="btn btn-outline" style="padding:8px 16px;font-size:13px;">
          <iconify-icon icon="lucide:plus" style="font-size:15px;margin-right:6px"></iconify-icon>
          New Application
        </a>
        <a href="index" class="btn btn-primary" style="padding:8px 16px;font-size:13px;">
          <iconify-icon icon="lucide:home" style="font-size:15px;margin-right:6px"></iconify-icon>
          Main Site
        </a>
        <a href="account" style="font-size:13px;color:var(--muted-foreground);text-decoration:none;display:flex;align-items:center;gap:5px;">
          <iconify-icon icon="lucide:settings" style="font-size:15px"></iconify-icon>
          Account
        </a>
      </div>
    </div>
  </header>

  <!-- ── Main content ── -->
  <div class="container" style="padding-top:32px;padding-bottom:48px;">

    <!-- Page title -->
    <div style="margin-bottom:28px;">
      <h1 style="font-size:28px;font-weight:800;margin-bottom:6px;">Driver Applications</h1>
      <p style="color:var(--muted-foreground);font-size:15px;">Review and manage all driver onboarding submissions.</p>
    </div>

    <!-- Stats -->
    <div class="stats-grid" id="statsGrid">
      <div class="stat-card">
        <div class="stat-icon blue"><iconify-icon icon="lucide:users"></iconify-icon></div>
        <div><div class="stat-label">Total Applications</div><div class="stat-value" id="statTotal">—</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon amber"><iconify-icon icon="lucide:clock"></iconify-icon></div>
        <div><div class="stat-label">Pending Review</div><div class="stat-value" id="statPending">—</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon green"><iconify-icon icon="lucide:check-circle-2"></iconify-icon></div>
        <div><div class="stat-label">Approved</div><div class="stat-value" id="statApproved">—</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon red"><iconify-icon icon="lucide:x-circle"></iconify-icon></div>
        <div><div class="stat-label">Rejected</div><div class="stat-value" id="statRejected">—</div></div>
      </div>
    </div>

    <!-- Toolbar -->
    <div class="toolbar">
      <div class="toolbar-search">
        <iconify-icon icon="lucide:search"></iconify-icon>
        <input type="text" id="searchInput" placeholder="Search by name, email, van reg, reference…" />
      </div>
      <select class="filter-select" id="statusFilter">
        <option value="">All Statuses</option>
        <option value="pending">Pending</option>
        <option value="approved">Approved</option>
        <option value="rejected">Rejected</option>
      </select>
      <select class="filter-select" id="sortOrder">
        <option value="newest">Newest First</option>
        <option value="oldest">Oldest First</option>
        <option value="name_az">Name A–Z</option>
      </select>
      <button class="btn btn-outline" id="refreshBtn" style="padding:10px 16px;font-size:14px;" title="Refresh data">
        <iconify-icon icon="lucide:refresh-cw" style="font-size:15px"></iconify-icon>
      </button>
      <a href="dashboard_data?export=csv" class="btn btn-outline" style="padding:10px 16px;font-size:14px;" title="Export CSV">
        <iconify-icon icon="lucide:download" style="font-size:15px;margin-right:6px"></iconify-icon>
        Export CSV
      </a>
    </div>

    <!-- Table -->
    <div class="table-card">
      <div class="table-wrap">
        <table id="submissionsTable">
          <thead>
            <tr>
              <th class="sortable" data-col="id">Reference</th>
              <th class="sortable" data-col="name">Driver</th>
              <th>Contact</th>
              <th>Vehicle</th>
              <th>Dimensions</th>
              <th class="sortable" data-col="timestamp">Submitted</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="tableBody">
            <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--muted-foreground);">
              <iconify-icon icon="lucide:loader-circle" style="font-size:24px;animation:spin 1s linear infinite"></iconify-icon>
            </td></tr>
          </tbody>
        </table>
      </div>
      <div class="pagination" id="pagination" style="display:none;">
        <span id="paginationInfo">Showing 0–0 of 0</span>
        <div class="pag-btns" id="paginationBtns"></div>
      </div>
    </div>

  </div><!-- /container -->

  <!-- ── Detail Modal ── -->
  <div class="modal-backdrop hidden" id="modalBackdrop">
    <div class="modal" id="detailModal">
      <div class="modal-header">
        <h2 id="modalTitle">Driver Application</h2>
        <button class="modal-close" id="modalClose" title="Close">
          <iconify-icon icon="lucide:x" style="font-size:20px"></iconify-icon>
        </button>
      </div>
      <div class="modal-body" id="modalBody">
        <!-- Populated by JS -->
      </div>
      <div class="modal-footer">
        <div class="status-row">
          <label for="statusSelect">Update Status:</label>
          <select id="statusSelect">
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
          </select>
        </div>
        <button class="btn btn-primary" id="saveStatusBtn" style="margin-left:auto;">
          <iconify-icon icon="lucide:save" style="font-size:15px;margin-right:6px"></iconify-icon>
          Save Status
        </button>
        <button class="btn btn-outline" id="modalCloseBtn">Close</button>
      </div>
    </div>
  </div>

  <script>
    // ── Auth guard — employee roles only ─────────────────────
    (function() {
      var user = null;
      try { user = JSON.parse(localStorage.getItem('fx_user')); } catch(e) {}
      var allowedRoles = ['driver', 'owner_operator', 'corporate_staff', 'admin', 'super_admin'];
      if (!user || !user.id || allowedRoles.indexOf(user.role) === -1) {
        window.location.href = 'login?redirect=' + encodeURIComponent(window.location.pathname);
      }
    })();

    // ── State ────────────────────────────────────────────────
    let allDrivers    = [];
    let filtered      = [];
    let currentPage   = 1;
    const perPage     = 15;
    let activeDriverId = null;

    // ── Load data ────────────────────────────────────────────
    async function loadData() {
      try {
        var currentUser = null;
        try { currentUser = JSON.parse(localStorage.getItem('fx_user')); } catch(e) {}
        var url = 'dashboard_data.php?t=' + Date.now();
        if (currentUser && currentUser.id) {
          url += '&user_id=' + encodeURIComponent(currentUser.id);
        }
        const res  = await fetch(url);
        const data = await res.json();
        allDrivers = data.drivers || [];
        applyFilters();
        updateStats();
      } catch (err) {
        document.getElementById('tableBody').innerHTML = `
          <tr><td colspan="8">
            <div class="empty-state">
              <iconify-icon icon="lucide:alert-circle"></iconify-icon>
              <p>Could not load data. Make sure <code>dashboard_data.php</code> is accessible.<br>
              <small style="color:var(--muted-foreground);">${err.message}</small></p>
            </div>
          </td></tr>`;
      }
    }

    // ── Stats ────────────────────────────────────────────────
    function updateStats() {
      document.getElementById('statTotal').textContent    = allDrivers.length;
      document.getElementById('statPending').textContent  = allDrivers.filter(d => (d.status || 'pending') === 'pending').length;
      document.getElementById('statApproved').textContent = allDrivers.filter(d => d.status === 'approved').length;
      document.getElementById('statRejected').textContent = allDrivers.filter(d => d.status === 'rejected').length;
    }

    // ── Filters ──────────────────────────────────────────────
    function applyFilters() {
      const q      = document.getElementById('searchInput').value.toLowerCase();
      const status = document.getElementById('statusFilter').value;
      const sort   = document.getElementById('sortOrder').value;

      filtered = allDrivers.filter(d => {
        const name    = ((d.first_name || '') + ' ' + (d.last_name || '')).toLowerCase();
        const email   = (d.email || '').toLowerCase();
        const reg     = (d.van_reg || '').toLowerCase();
        const ref     = (d.id || '').toLowerCase();
        const matchQ  = !q || name.includes(q) || email.includes(q) || reg.includes(q) || ref.includes(q);
        const matchS  = !status || (d.status || 'pending') === status;
        return matchQ && matchS;
      });

      filtered.sort((a, b) => {
        if (sort === 'oldest')  return new Date(a.timestamp) - new Date(b.timestamp);
        if (sort === 'name_az') return (a.first_name||'').localeCompare(b.first_name||'');
        return new Date(b.timestamp) - new Date(a.timestamp); // newest
      });

      currentPage = 1;
      renderTable();
    }

    // ── Render table ─────────────────────────────────────────
    function renderTable() {
      const tbody = document.getElementById('tableBody');
      const start = (currentPage - 1) * perPage;
      const page  = filtered.slice(start, start + perPage);

      if (!filtered.length) {
        tbody.innerHTML = `<tr><td colspan="8"><div class="empty-state">
          <iconify-icon icon="lucide:inbox"></iconify-icon>
          <p>No submissions found${document.getElementById('searchInput').value ? ' matching your search' : ' yet'}.</p>
        </div></td></tr>`;
        document.getElementById('pagination').style.display = 'none';
        return;
      }

      tbody.innerHTML = page.map(d => {
        const name    = escHtml((d.first_name||'') + ' ' + (d.last_name||''));
        const email   = escHtml(d.email || '—');
        const phone   = escHtml(d.phone || '—');
        const vehicle = escHtml([d.van_year, d.van_make, d.van_model].filter(Boolean).join(' ') || '—');
        const reg     = escHtml(d.van_reg || '—');
        const dims    = d.cargo_length
          ? `${d.cargo_length}m × ${d.cargo_width}m × ${d.cargo_height}m`
          : '—';
        const payload = d.payload_kg ? d.payload_kg + ' kg' : '';
        const ts      = d.timestamp ? new Date(d.timestamp).toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' }) : '—';
        const status  = d.status || 'pending';
        const badgeCls = status === 'approved' ? 'badge-approved' : status === 'rejected' ? 'badge-rejected' : 'badge-pending';

        return `<tr>
          <td class="td-mono">${escHtml(d.id || '—')}</td>
          <td>
            <div class="td-name">${name}</div>
            <div class="td-sub">${escHtml(d.address||'')}</div>
          </td>
          <td>
            <div>${email}</div>
            <div class="td-sub">${phone}</div>
          </td>
          <td>
            <div>${vehicle}</div>
            <div class="td-sub">${reg}</div>
          </td>
          <td>
            <div style="font-size:13px;">${dims}</div>
            <div class="td-sub">${payload}</div>
          </td>
          <td style="white-space:nowrap;">${ts}</td>
          <td><span class="badge ${badgeCls}"><span class="badge-dot"></span>${capitalize(status)}</span></td>
          <td style="white-space:nowrap;">
            <button class="action-btn" title="View details" onclick="openModal('${escHtml(d.id||'')}')">
              <iconify-icon icon="lucide:eye" style="font-size:17px"></iconify-icon>
            </button>
          </td>
        </tr>`;
      }).join('');

      renderPagination();
    }

    // ── Pagination ───────────────────────────────────────────
    function renderPagination() {
      const total = filtered.length;
      const pages = Math.ceil(total / perPage);
      const start = (currentPage - 1) * perPage + 1;
      const end   = Math.min(currentPage * perPage, total);
      const pag   = document.getElementById('pagination');
      const info  = document.getElementById('paginationInfo');
      const btns  = document.getElementById('paginationBtns');

      pag.style.display = total > 0 ? '' : 'none';
      info.textContent  = `Showing ${start}–${end} of ${total}`;

      let html = `<button class="pag-btn" onclick="goPage(${currentPage-1})" ${currentPage===1?'disabled':''}>
        <iconify-icon icon="lucide:chevron-left" style="font-size:14px"></iconify-icon>
      </button>`;
      for (let p = 1; p <= pages; p++) {
        if (pages > 7 && Math.abs(p - currentPage) > 2 && p !== 1 && p !== pages) { html += ''; continue; }
        html += `<button class="pag-btn ${p===currentPage?'active':''}" onclick="goPage(${p})">${p}</button>`;
      }
      html += `<button class="pag-btn" onclick="goPage(${currentPage+1})" ${currentPage===pages?'disabled':''}>
        <iconify-icon icon="lucide:chevron-right" style="font-size:14px"></iconify-icon>
      </button>`;
      btns.innerHTML = html;
    }

    function goPage(p) {
      const pages = Math.ceil(filtered.length / perPage);
      currentPage = Math.min(Math.max(p, 1), pages);
      renderTable();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // ── Modal ────────────────────────────────────────────────
    function openModal(driverId) {
      const driver = allDrivers.find(d => d.id === driverId);
      if (!driver) return;
      activeDriverId = driverId;

      const d = driver;
      const dob    = d.dob    ? new Date(d.dob).toLocaleDateString('en-GB')    : '—';
      const licExp = d.license_expiry ? new Date(d.license_expiry).toLocaleDateString('en-GB') : '—';
      const insExp = d.insurance_expiry ? new Date(d.insurance_expiry).toLocaleDateString('en-GB') : '—';
      const motExp = d.mot_expiry ? new Date(d.mot_expiry).toLocaleDateString('en-GB') : '—';
      const avail  = Array.isArray(d.availability) ? d.availability.map(capitalize).join(', ') : (d.availability || '—');

      const photoKeys = [
        { key: 'photo_front',    label: 'Front' },
        { key: 'photo_side',     label: 'Side' },
        { key: 'photo_interior', label: 'Interior' },
        { key: 'doc_licence',    label: 'Licence' },
        { key: 'doc_insurance',  label: 'Insurance' },
        { key: 'doc_mot',        label: 'MOT' },
      ];

      const photosHTML = photoKeys.map(pk => {
        const files = d[pk.key];
        if (files && files.length) {
          return files.map(f => {
            const isImg = /\.(jpg|jpeg|png|gif|webp)$/i.test(f);
            return `<div class="photo-thumb">
              ${isImg
                ? `<img src="${escHtml(f)}" alt="${pk.label}" loading="lazy" />`
                : `<div style="width:100%;height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;background:var(--secondary);gap:6px;padding:8px;text-align:center;">
                     <iconify-icon icon="lucide:file-text" style="font-size:28px;color:var(--primary)"></iconify-icon>
                     <span style="font-size:10px;color:var(--muted-foreground);word-break:break-all;">${f.split('/').pop()}</span>
                   </div>`}
              <div class="photo-label">${pk.label}</div>
            </div>`;
          }).join('');
        }
        return `<div class="photo-thumb no-img">
          <iconify-icon icon="lucide:image-off" style="font-size:24px"></iconify-icon>
          <span>${pk.label}</span>
        </div>`;
      }).join('');

      document.getElementById('modalBody').innerHTML = `
        <div class="detail-section">
          <div class="detail-section-title">
            <iconify-icon icon="lucide:user"></iconify-icon>
            Personal Details
          </div>
          <div class="detail-grid">
            <div class="detail-item"><label>Full Name</label><p>${escHtml((d.first_name||'') + ' ' + (d.last_name||''))}</p></div>
            <div class="detail-item"><label>Email</label><p>${escHtml(d.email||'—')}</p></div>
            <div class="detail-item"><label>Phone</label><p>${escHtml(d.phone||'—')}</p></div>
            <div class="detail-item"><label>Date of Birth</label><p>${dob}</p></div>
            <div class="detail-item"><label>Home Town</label><p>${escHtml(d.address||'—')}</p></div>
            <div class="detail-item"><label>Experience</label><p>${escHtml((d.years_experience||'—').replace(/_/g,' '))}</p></div>
            <div class="detail-item"><label>Licence No.</label><p>${escHtml(d.license_number||'—')}</p></div>
            <div class="detail-item"><label>Licence Expiry</label><p>${licExp}</p></div>
          </div>
        </div>

        <div class="detail-section">
          <div class="detail-section-title">
            <iconify-icon icon="lucide:truck"></iconify-icon>
            Vehicle Details
          </div>
          <div class="detail-grid">
            <div class="detail-item"><label>Make</label><p>${escHtml(d.van_make||'—')}</p></div>
            <div class="detail-item"><label>Model</label><p>${escHtml(d.van_model||'—')}</p></div>
            <div class="detail-item"><label>Year</label><p>${escHtml(String(d.van_year||'—'))}</p></div>
            <div class="detail-item"><label>Colour</label><p>${escHtml(d.van_color||'—')}</p></div>
            <div class="detail-item"><label>Registration</label><p style="font-family:monospace;font-weight:700;">${escHtml(d.van_reg||'—')}</p></div>
            <div class="detail-item"><label>Type</label><p>${escHtml((d.van_type||'—').replace(/_/g,' '))}</p></div>
            <div class="detail-item"><label>Insurance Expiry</label><p>${insExp}</p></div>
            <div class="detail-item"><label>MOT Expiry</label><p>${motExp}</p></div>
            <div class="detail-item"><label>Tail Lift</label><p>${capitalize(d.tail_lift||'no')}</p></div>
          </div>
        </div>

        <div class="detail-section">
          <div class="detail-section-title">
            <iconify-icon icon="lucide:ruler"></iconify-icon>
            Van Dimensions
          </div>
          <div class="detail-grid">
            <div class="detail-item"><label>Cargo Length</label><p>${d.cargo_length ? d.cargo_length + ' m' : '—'}</p></div>
            <div class="detail-item"><label>Cargo Width</label><p>${d.cargo_width  ? d.cargo_width  + ' m' : '—'}</p></div>
            <div class="detail-item"><label>Cargo Height</label><p>${d.cargo_height ? d.cargo_height + ' m' : '—'}</p></div>
            <div class="detail-item"><label>Payload</label><p>${d.payload_kg ? d.payload_kg + ' kg' : '—'}</p></div>
            <div class="detail-item"><label>Volume</label><p>${d.volume_m3 ? d.volume_m3 + ' m³' : '—'}</p></div>
            <div class="detail-item"><label>Ext. Length</label><p>${d.ext_length ? d.ext_length + ' m' : '—'}</p></div>
            <div class="detail-item"><label>Ext. Width</label><p>${d.ext_width  ? d.ext_width  + ' m' : '—'}</p></div>
            <div class="detail-item"><label>Ext. Height</label><p>${d.ext_height ? d.ext_height + ' m' : '—'}</p></div>
          </div>
        </div>

        <div class="detail-section">
          <div class="detail-section-title">
            <iconify-icon icon="lucide:calendar"></iconify-icon>
            Availability &amp; Preferences
          </div>
          <div class="detail-grid">
            <div class="detail-item"><label>Days Available</label><p>${avail}</p></div>
            <div class="detail-item"><label>Work Pattern</label><p>${escHtml((d.work_type||'—').replace(/_/g,' '))}</p></div>
            <div class="detail-item" style="grid-column:span 3;"><label>Operating Areas</label><p>${escHtml(d.operating_areas||'—')}</p></div>
            ${d.notes ? `<div class="detail-item" style="grid-column:span 3;"><label>Notes</label><p>${escHtml(d.notes)}</p></div>` : ''}
          </div>
        </div>

        <div class="detail-section">
          <div class="detail-section-title">
            <iconify-icon icon="lucide:image"></iconify-icon>
            Photos &amp; Documents
          </div>
          <div class="photos-grid">
            ${photosHTML}
          </div>
        </div>

        <div class="detail-section" style="margin-bottom:0;">
          <div class="detail-section-title">
            <iconify-icon icon="lucide:info"></iconify-icon>
            Submission Info
          </div>
          <div class="detail-grid">
            <div class="detail-item"><label>Reference</label><p style="font-family:monospace;font-weight:700;color:var(--primary);">${escHtml(d.id||'—')}</p></div>
            <div class="detail-item"><label>Submitted</label><p>${d.timestamp ? new Date(d.timestamp).toLocaleString('en-GB') : '—'}</p></div>
            <div class="detail-item"><label>Current Status</label><p><span class="badge ${(d.status||'pending')==='approved'?'badge-approved':(d.status==='rejected'?'badge-rejected':'badge-pending')}">${capitalize(d.status||'pending')}</span></p></div>
          </div>
        </div>

        <div class="detail-section" style="margin-bottom:0;margin-top:16px;">
          <div class="detail-section-title">
            <iconify-icon icon="lucide:send"></iconify-icon>
            Telegram Notifications
          </div>
          <div class="detail-grid">
            <div class="detail-item" style="grid-column:span 2;">
              <label>Telegram Chat ID</label>
              <input type="text" id="telegramChatIdInput" value="${escHtml(d.telegram_chat_id||'')}"
                placeholder="e.g. 123456789 — get via @userinfobot"
                style="width:100%;padding:8px 12px;border:1.5px solid var(--border);border-radius:var(--radius-md);font-family:var(--font-family-body);font-size:14px;background:var(--input);" />
              <p style="font-size:12px;color:var(--muted-foreground);margin-top:4px;">
                The driver's Telegram numeric chat ID. The driver can get it by messaging
                <a href="https://t.me/userinfobot" target="_blank" rel="noopener" style="color:var(--primary);">@userinfobot</a> on Telegram.
              </p>
            </div>
          </div>
        </div>
      `;

      document.getElementById('statusSelect').value = d.status || 'pending';
      document.getElementById('modalTitle').textContent = 'Application — ' + (d.first_name||'') + ' ' + (d.last_name||'');
      document.getElementById('modalBackdrop').classList.remove('hidden');
      document.body.style.overflow = 'hidden';
    }

    function closeModal() {
      document.getElementById('modalBackdrop').classList.add('hidden');
      document.body.style.overflow = '';
      activeDriverId = null;
    }

    // ── Save status ──────────────────────────────────────────
    document.getElementById('saveStatusBtn').addEventListener('click', async () => {
      if (!activeDriverId) return;
      const newStatus = document.getElementById('statusSelect').value;
      const btn = document.getElementById('saveStatusBtn');
      const orig = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = '<iconify-icon icon="lucide:loader-circle" style="font-size:15px;margin-right:6px;animation:spin 1s linear infinite"></iconify-icon>Saving…';
      try {
        const fd = new FormData();
        fd.append('action', 'update_status');
        fd.append('driver_id', activeDriverId);
        fd.append('status', newStatus);
        const chatIdInput = document.getElementById('telegramChatIdInput');
        if (chatIdInput) fd.append('telegram_chat_id', chatIdInput.value.trim());
        const res  = await fetch('dashboard_data.php', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.success) {
          const d = allDrivers.find(d => d.id === activeDriverId);
          if (d) d.status = newStatus;
          updateStats();
          renderTable();
          closeModal();
        } else {
          alert('Error: ' + data.message);
        }
      } catch (err) {
        alert('Network error: ' + err.message);
      }
      btn.disabled = false;
      btn.innerHTML = orig;
    });

    // ── Event listeners ──────────────────────────────────────
    document.getElementById('modalClose').addEventListener('click', closeModal);
    document.getElementById('modalCloseBtn').addEventListener('click', closeModal);
    document.getElementById('modalBackdrop').addEventListener('click', e => {
      if (e.target === document.getElementById('modalBackdrop')) closeModal();
    });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

    document.getElementById('searchInput').addEventListener('input',  applyFilters);
    document.getElementById('statusFilter').addEventListener('change', applyFilters);
    document.getElementById('sortOrder').addEventListener('change',    applyFilters);
    document.getElementById('refreshBtn').addEventListener('click', loadData);

    // ── Helpers ──────────────────────────────────────────────
    function escHtml(str) {
      return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
    function capitalize(str) {
      return String(str).charAt(0).toUpperCase() + String(str).slice(1);
    }

    // ── Init ─────────────────────────────────────────────────
    loadData();
  </script>
</body>
</html>
