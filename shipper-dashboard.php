<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <link rel="icon" href="favicon.svg" type="image/svg+xml" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>My Dashboard — Fastrux Logistics</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="shared.css" />
  <script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>
  <style>
    body { background: var(--muted); }

    /* ── Dashboard header ── */
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
    .stat-icon.blue   { background: var(--secondary); color: var(--primary); }
    .stat-icon.green  { background: #e6f9ee; color: var(--success); }
    .stat-icon.amber  { background: #fff7e6; color: #d97706; }
    .stat-icon.purple { background: #f3e8ff; color: #7c3aed; }
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
      appearance: none;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 12px center;
      outline: none;
      cursor: pointer;
      transition: border-color .2s;
    }
    .filter-select:focus { border-color: var(--primary); }

    /* ── Table card ── */
    .table-card {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius-xl);
      overflow: hidden;
    }
    .table-responsive { overflow-x: auto; }
    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 14px;
    }
    thead th {
      background: var(--muted);
      padding: 12px 16px;
      text-align: left;
      font-size: 12px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: .5px;
      color: var(--muted-foreground);
      white-space: nowrap;
      border-bottom: 1px solid var(--border);
    }
    tbody tr { border-bottom: 1px solid var(--border); }
    tbody tr:last-child { border-bottom: none; }
    tbody td { padding: 14px 16px; vertical-align: middle; }
    tbody tr.quote-row:hover { background: var(--muted); }

    .quote-id {
      font-family: 'Courier New', monospace;
      font-size: 12px;
      font-weight: 600;
      color: var(--primary);
      background: var(--secondary);
      padding: 3px 8px;
      border-radius: var(--radius-sm);
    }
    .quote-service { font-weight: 500; }

    /* ── Status badges ── */
    .status-badge {
      display: inline-flex; align-items: center; gap: 5px;
      padding: 4px 10px;
      border-radius: 999px;
      font-size: 12px; font-weight: 600;
      white-space: nowrap;
    }
    .status-badge.new        { background: var(--secondary); color: var(--primary); }
    .status-badge.in_progress{ background: #fff7e6; color: #d97706; }
    .status-badge.completed  { background: #e6f9ee; color: var(--success); }
    .status-badge.declined   { background: #fef2f2; color: var(--destructive); }

    /* ── Detail panel ── */
    .detail-panel { display: none; }
    .detail-panel.open { display: table-row; }
    .detail-inner {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 16px;
      padding: 20px;
      background: var(--muted);
    }
    .detail-field {}
    .detail-label { font-size: 11px; text-transform: uppercase; letter-spacing: .5px; color: var(--muted-foreground); font-weight: 600; }
    .detail-value { font-size: 14px; color: var(--foreground); margin-top: 4px; }

    /* ── Staff response panel ── */
    .staff-response-box {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius-md);
      padding: 14px 16px;
      font-size: 14px;
      line-height: 1.6;
      color: var(--foreground);
      white-space: pre-wrap;
    }
    .staff-response-empty {
      color: var(--muted-foreground);
      font-style: italic;
      font-size: 13px;
    }

    /* ── Empty state ── */
    .empty-state {
      padding: 64px 32px;
      text-align: center;
      color: var(--muted-foreground);
    }
    .empty-state iconify-icon { font-size: 48px; margin-bottom: 16px; opacity: .4; }

    /* ── Loading state ── */
    .loading-state {
      padding: 64px 32px;
      text-align: center;
      color: var(--muted-foreground);
    }
    .loading-state iconify-icon { font-size: 32px; margin-bottom: 12px; animation: spin 1s linear infinite; }

    /* ── Toast ── */
    #toast {
      position: fixed; bottom: 28px; right: 28px;
      background: var(--foreground); color: #fff;
      padding: 12px 20px; border-radius: var(--radius-md);
      font-size: 14px; font-weight: 500;
      opacity: 0; transform: translateY(8px);
      transition: opacity .25s, transform .25s;
      z-index: 9999; pointer-events: none;
    }
    #toast.show { opacity: 1; transform: translateY(0); }

    /* ── User greeting ── */
    .dash-greeting {
      display: flex; align-items: center; gap: 12px;
    }
    .dash-avatar {
      width: 38px; height: 38px;
      background: var(--secondary);
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 15px; font-weight: 700; color: var(--primary);
    }
    .dash-user-name { font-size: 15px; font-weight: 600; }
    .dash-user-role { font-size: 12px; color: var(--muted-foreground); }

    /* ── Responsive ── */
    @media (max-width: 900px) {
      .stats-grid { grid-template-columns: repeat(2, 1fr); }
      .detail-inner { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 600px) {
      .stats-grid { grid-template-columns: 1fr 1fr; }
      .detail-inner { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>

  <!-- ── Dashboard header ── -->
  <header class="dash-header">
    <div class="container dash-header-inner">
      <a class="dash-brand" href="index.php">
        <iconify-icon icon="lucide:truck" style="font-size:26px"></iconify-icon>
        Fastrux <span>/ My Dashboard</span>
      </a>
      <div style="display:flex;align-items:center;gap:16px;">
        <a href="messages.php" class="btn btn-outline" style="display:flex;align-items:center;gap:7px;font-size:14px;padding:9px 18px;">
          <iconify-icon icon="lucide:message-circle" style="font-size:16px"></iconify-icon>
          Messages
        </a>
        <a href="documents.php" class="btn btn-outline" style="display:flex;align-items:center;gap:7px;font-size:14px;padding:9px 18px;">
          <iconify-icon icon="lucide:file-text" style="font-size:16px"></iconify-icon>
          Documents
        </a>
        <a href="maps.php" class="btn btn-outline" style="display:flex;align-items:center;gap:7px;font-size:14px;padding:9px 18px;">
          <iconify-icon icon="lucide:map" style="font-size:16px"></iconify-icon>
          Live Map
        </a>
        <a class="btn btn-primary" href="quote.php" style="display:flex;align-items:center;gap:7px;font-size:14px;padding:9px 18px;">
          <iconify-icon icon="lucide:plus" style="font-size:16px"></iconify-icon>
          Request Quote
        </a>
        <div class="dash-greeting">
          <div class="dash-avatar" id="dashAvatar">?</div>
          <div>
            <div class="dash-user-name" id="dashUserName">Loading…</div>
            <div class="dash-user-role" id="dashUserRole">Shipper</div>
          </div>
        </div>
        <a href="account.php" style="font-size:13px;color:var(--muted-foreground);text-decoration:none;display:flex;align-items:center;gap:5px;">
          <iconify-icon icon="lucide:settings" style="font-size:16px"></iconify-icon>
          Account
        </a>
      </div>
    </div>
  </header>

  <!-- ── Main content ── -->
  <main class="container" style="padding-top:36px;padding-bottom:56px;">

    <!-- Stats -->
    <div class="stats-grid" id="statsGrid">
      <div class="stat-card">
        <div class="stat-icon blue"><iconify-icon icon="lucide:file-text"></iconify-icon></div>
        <div>
          <div class="stat-label">Total Requests</div>
          <div class="stat-value" id="statTotal">—</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon amber"><iconify-icon icon="lucide:clock"></iconify-icon></div>
        <div>
          <div class="stat-label">Pending Review</div>
          <div class="stat-value" id="statPending">—</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon purple"><iconify-icon icon="lucide:loader"></iconify-icon></div>
        <div>
          <div class="stat-label">In Progress</div>
          <div class="stat-value" id="statInProgress">—</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon green"><iconify-icon icon="lucide:check-circle"></iconify-icon></div>
        <div>
          <div class="stat-label">Completed</div>
          <div class="stat-value" id="statCompleted">—</div>
        </div>
      </div>
    </div>

    <!-- Toolbar -->
    <div class="toolbar">
      <div class="toolbar-search">
        <iconify-icon icon="lucide:search"></iconify-icon>
        <input type="text" id="searchInput" placeholder="Search by reference, service, route…" />
      </div>
      <select id="serviceFilter" class="filter-select">
        <option value="">All Services</option>
        <option value="ocean">Ocean Freight</option>
        <option value="air">Air Freight</option>
        <option value="ground">Ground Transport</option>
        <option value="warehousing">Warehousing</option>
      </select>
      <select id="statusFilter" class="filter-select">
        <option value="">All Statuses</option>
        <option value="new">Pending</option>
        <option value="in_progress">In Progress</option>
        <option value="completed">Completed</option>
        <option value="declined">Declined</option>
      </select>
      <div style="font-size:13px;color:var(--muted-foreground);white-space:nowrap;">
        <strong id="visibleCount">0</strong> requests
      </div>
    </div>

    <!-- Table -->
    <div class="table-card" id="tableCard">
      <div class="loading-state" id="loadingState">
        <iconify-icon icon="lucide:loader-circle"></iconify-icon>
        <p>Loading your quote requests…</p>
      </div>
    </div>

  </main>

  <!-- Toast -->
  <div id="toast"></div>

  <script>
    // ── Auth guard — shipper roles only ─────────────────────────
    var currentUser = null;
    (function() {
      try { currentUser = JSON.parse(localStorage.getItem('fx_user')); } catch(e) {}
      var shipperRoles = ['shipper', 'customer'];
      if (!currentUser || !currentUser.id || shipperRoles.indexOf(currentUser.role || 'shipper') === -1) {
        window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.pathname);
      }
    })();

    // ── Populate header ──────────────────────────────────────────
    (function() {
      if (!currentUser) return;
      var initials = ((currentUser.first_name || '?')[0] + (currentUser.last_name || '')[0]).toUpperCase();
      document.getElementById('dashAvatar').textContent   = initials;
      document.getElementById('dashUserName').textContent = (currentUser.first_name || '') + ' ' + (currentUser.last_name || '');
      document.getElementById('dashUserRole').textContent = 'Shipper';
    })();

    // ── Helpers ─────────────────────────────────────────────────
    function statusBadge(status) {
      var labels = { new: 'Pending Review', in_progress: 'In Progress', completed: 'Completed', declined: 'Declined' };
      var label  = labels[status] || status;
      return '<span class="status-badge ' + (status || 'new') + '">' + escHtml(label) + '</span>';
    }

    function escHtml(s) {
      return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function hasResponse(q) {
      return q.staff_response && q.staff_response.trim() !== '';
    }

    // ── Load data ────────────────────────────────────────────────
    var allQuotes  = [];
    var filtered   = [];

    async function loadData() {
      if (!currentUser) return;
      var url = 'shipper_data.php?user_id=' + encodeURIComponent(currentUser.id || '')
                + '&email=' + encodeURIComponent(currentUser.email || '')
                + '&t=' + Date.now();
      try {
        var res  = await fetch(url);
        var data = await res.json();
        if (data.success) {
          allQuotes = (data.quotes || []).slice().reverse(); // newest first
          updateStats();
          renderTable();
        } else {
          showError(data.message || 'Failed to load data.');
        }
      } catch (e) {
        showError('Network error — please refresh the page.');
      }
    }

    function updateStats() {
      var total      = allQuotes.length;
      var pending    = allQuotes.filter(q => (q.status || 'new') === 'new').length;
      var inProgress = allQuotes.filter(q => q.status === 'in_progress').length;
      var completed  = allQuotes.filter(q => q.status === 'completed').length;
      document.getElementById('statTotal').textContent      = total;
      document.getElementById('statPending').textContent    = pending;
      document.getElementById('statInProgress').textContent = inProgress;
      document.getElementById('statCompleted').textContent  = completed;
    }

    function applyFilters() {
      var q  = document.getElementById('searchInput').value.toLowerCase().trim();
      var sv = document.getElementById('serviceFilter').value.toLowerCase();
      var st = document.getElementById('statusFilter').value;
      filtered = allQuotes.filter(function(quote) {
        var searchStr = ((quote.id || '') + ' ' + (quote.service || '') + ' ' + (quote.origin || '') + ' ' + (quote.destination || '')).toLowerCase();
        var matchQ  = !q  || searchStr.includes(q);
        var matchSv = !sv || (quote.service || '').toLowerCase().includes(sv);
        var matchSt = !st || (quote.status || 'new') === st;
        return matchQ && matchSv && matchSt;
      });
      document.getElementById('visibleCount').textContent = filtered.length;
      renderRows();
    }

    function renderTable() {
      filtered = allQuotes.slice();
      document.getElementById('visibleCount').textContent = filtered.length;

      var card = document.getElementById('tableCard');
      if (allQuotes.length === 0) {
        card.innerHTML = '<div class="empty-state">'
          + '<iconify-icon icon="lucide:inbox"></iconify-icon>'
          + '<p style="font-size:16px;font-weight:600;margin-bottom:8px;">No quote requests yet</p>'
          + '<p>Submit your first quote request and we\'ll respond within 24 hours.</p>'
          + '<a class="btn btn-primary" href="quote.php" style="display:inline-flex;align-items:center;gap:7px;margin-top:20px;padding:11px 22px;">'
          + '<iconify-icon icon="lucide:plus" style="font-size:16px"></iconify-icon>Request a Quote</a></div>';
        return;
      }

      card.innerHTML = '<div class="table-responsive"><table id="quotesTable">'
        + '<thead><tr>'
        + '<th></th>'
        + '<th>Reference</th>'
        + '<th>Service</th>'
        + '<th>Route</th>'
        + '<th>Submitted</th>'
        + '<th>Status</th>'
        + '<th>Response</th>'
        + '</tr></thead>'
        + '<tbody id="tableBody"></tbody>'
        + '</table></div>';

      renderRows();
    }

    function renderRows() {
      var tbody = document.getElementById('tableBody');
      if (!tbody) return;
      if (filtered.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:40px;color:var(--muted-foreground);">No requests match your filters.</td></tr>';
        return;
      }

      var html = '';
      filtered.forEach(function(q) {
        var qid    = escHtml(q.id || '');
        var svc    = escHtml(q.service || '');
        var orig   = escHtml(q.origin || '');
        var dest   = escHtml(q.destination || '');
        var ts     = escHtml(q.timestamp || '');
        var status = q.status || 'new';
        var route  = (orig && dest) ? orig + ' → ' + dest : (orig || dest || '—');
        var respIcon = hasResponse(q)
          ? '<iconify-icon icon="lucide:message-square-check" style="font-size:18px;color:var(--success)" title="Staff responded"></iconify-icon>'
          : '<iconify-icon icon="lucide:message-square" style="font-size:18px;color:var(--muted-foreground)" title="Awaiting response"></iconify-icon>';

        html += '<tr class="quote-row" data-id="' + qid + '">'
          + '<td><button class="btn btn-outline toggle-detail" data-id="' + qid + '" style="padding:4px 10px;font-size:12px;min-width:unset;">'
          + '<iconify-icon icon="lucide:chevron-down" style="font-size:14px"></iconify-icon></button></td>'
          + '<td><span class="quote-id">' + qid + '</span></td>'
          + '<td class="quote-service">' + svc + '</td>'
          + '<td style="white-space:nowrap;">' + escHtml(route) + '</td>'
          + '<td style="white-space:nowrap;font-size:13px;">' + ts + '</td>'
          + '<td>' + statusBadge(status) + '</td>'
          + '<td style="text-align:center;">' + respIcon + '</td>'
          + '</tr>'
          + '<tr class="detail-panel" id="detail-' + qid + '">'
          + '<td colspan="7"><div class="detail-inner">'
          + '<div class="detail-field"><span class="detail-label">Reference</span><span class="detail-value quote-id">' + qid + '</span></div>'
          + '<div class="detail-field"><span class="detail-label">Weight (kg)</span><span class="detail-value">' + escHtml(q.weight_kg || '—') + '</span></div>'
          + '<div class="detail-field"><span class="detail-label">Volume (m³)</span><span class="detail-value">' + escHtml(q.volume_m3 || '—') + '</span></div>'
          + (q.notes ? '<div class="detail-field" style="grid-column:1/-1;"><span class="detail-label">Notes</span><span class="detail-value">' + escHtml(q.notes) + '</span></div>' : '')
          + '<div class="detail-field" style="grid-column:1/-1;">'
          + '<span class="detail-label" style="display:flex;align-items:center;gap:6px;">'
          + '<iconify-icon icon="lucide:message-square-text" style="font-size:14px"></iconify-icon> Fastrux Staff Response'
          + '</span>'
          + (hasResponse(q)
              ? '<div class="staff-response-box" style="margin-top:8px;">' + escHtml(q.staff_response) + '</div>'
                + '<div style="font-size:12px;color:var(--muted-foreground);margin-top:6px;">Responded: ' + escHtml(q.response_added_at || '') + '</div>'
              : '<div class="staff-response-empty" style="margin-top:8px;">No response yet — our team will reply within 24 hours.</div>'
            )
          + '</div>'
          + '</div></td></tr>';
      });
      tbody.innerHTML = html;

      // Toggle detail panel
      tbody.querySelectorAll('.toggle-detail').forEach(function(btn) {
        btn.addEventListener('click', function() {
          var id    = btn.dataset.id;
          var panel = document.getElementById('detail-' + id);
          if (!panel) return;
          var open  = panel.classList.toggle('open');
          panel.style.display = open ? 'table-row' : 'none';
          var icon  = btn.querySelector('iconify-icon');
          if (icon) icon.setAttribute('icon', open ? 'lucide:chevron-up' : 'lucide:chevron-down');
        });
      });
    }

    function showError(msg) {
      document.getElementById('tableCard').innerHTML =
        '<div class="empty-state"><iconify-icon icon="lucide:alert-triangle"></iconify-icon>'
        + '<p style="font-size:15px;font-weight:600;margin-bottom:8px;color:var(--destructive);">Error loading data</p>'
        + '<p>' + escHtml(msg) + '</p></div>';
    }

    // ── Toast ────────────────────────────────────────────────────
    function showToast(msg) {
      var t = document.getElementById('toast');
      t.textContent = msg;
      t.classList.add('show');
      setTimeout(function() { t.classList.remove('show'); }, 2800);
    }

    // ── Init ────────────────────────────────────────────────────
    loadData();

    // Attach filter listeners (they will also be attached after table renders,
    // but attach here for the toolbar controls that exist before table render)
    document.getElementById('searchInput').addEventListener('input', applyFilters);
    document.getElementById('serviceFilter').addEventListener('change', applyFilters);
    document.getElementById('statusFilter').addEventListener('change', applyFilters);
  </script>
</body>
</html>
