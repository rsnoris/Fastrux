<?php
/**
 * Fastrux — Quote Requests Dashboard
 * Displays and manages all incoming quote requests.
 *
 * GET             → renders the dashboard HTML
 * GET ?export=csv → downloads all quotes as CSV
 * POST            → updates status of a quote entry
 */

define('DATA_DIR',   __DIR__ . '/data/');
define('QUOTES_JSON', DATA_DIR . 'quote_submissions.json');
define('QUOTES_CSV',  DATA_DIR . 'quote_submissions.csv');

// ── Helpers ──────────────────────────────────────────────────

function readQuotes(): array {
    if (!file_exists(QUOTES_JSON)) {
        return [];
    }
    $data = json_decode(file_get_contents(QUOTES_JSON), true);
    return is_array($data) ? $data : [];
}

function writeQuotes(array $quotes): void {
    if (!is_dir(DATA_DIR)) {
        mkdir(DATA_DIR, 0755, true);
    }
    file_put_contents(
        QUOTES_JSON,
        json_encode(array_values($quotes), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );
}

// ── CSV export ───────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['export']) && $_GET['export'] === 'csv') {
    $quotes = readQuotes();
    if (empty($quotes)) {
        header('Content-Type: text/plain');
        echo 'No quote submissions yet.';
        exit;
    }

    $allKeys = [];
    foreach ($quotes as $q) {
        foreach (array_keys($q) as $k) {
            if (!in_array($k, $allKeys, true)) {
                $allKeys[] = $k;
            }
        }
    }

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="quote_requests_' . date('Ymd_His') . '.csv"');
    $fp = fopen('php://output', 'w');
    fputcsv($fp, $allKeys);
    foreach ($quotes as $q) {
        $row = [];
        foreach ($allKeys as $k) {
            $val = $q[$k] ?? '';
            if (is_array($val)) {
                $val = implode(', ', $val);
            }
            $row[] = $val;
        }
        fputcsv($fp, $row);
    }
    fclose($fp);
    exit;
}

// ── POST — update status or add staff response ───────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $action  = trim(strip_tags($_POST['action']   ?? ''));
    $quoteId = trim(strip_tags($_POST['quote_id'] ?? ''));

    if ($action === 'add_response') {
        $response = trim($_POST['staff_response'] ?? '');
        if (!$quoteId) {
            echo json_encode(['success' => false, 'message' => 'Quote ID is required.']);
            exit;
        }
        $quotes = readQuotes();
        $found  = false;
        foreach ($quotes as &$q) {
            if (($q['id'] ?? '') === $quoteId) {
                $q['staff_response']    = $response;
                $q['response_added_at'] = date('Y-m-d H:i:s');
                $found = true;
                break;
            }
        }
        unset($q);
        if (!$found) {
            echo json_encode(['success' => false, 'message' => 'Quote not found.']);
            exit;
        }
        writeQuotes($quotes);
        echo json_encode(['success' => true, 'message' => 'Response saved successfully.']);
        exit;
    }

    $status  = trim(strip_tags($_POST['status']   ?? ''));

    if ($action !== 'update_status') {
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
        exit;
    }

    $allowed = ['new', 'in_progress', 'completed', 'declined'];
    if (!in_array($status, $allowed, true)) {
        echo json_encode(['success' => false, 'message' => 'Invalid status value.']);
        exit;
    }

    if (!$quoteId) {
        echo json_encode(['success' => false, 'message' => 'Quote ID is required.']);
        exit;
    }

    $quotes = readQuotes();
    $found  = false;
    foreach ($quotes as &$q) {
        if (($q['id'] ?? '') === $quoteId) {
            $q['status']     = $status;
            $q['updated_at'] = date('Y-m-d H:i:s');
            $found = true;
            break;
        }
    }
    unset($q);

    if (!$found) {
        echo json_encode(['success' => false, 'message' => 'Quote not found.']);
        exit;
    }

    writeQuotes($quotes);
    echo json_encode(['success' => true, 'message' => 'Status updated successfully.']);
    exit;
}

// ── Load data for page render ─────────────────────────────────
$quotes = readQuotes();

$totalQuotes    = count($quotes);
$newQuotes      = count(array_filter($quotes, fn($q) => ($q['status'] ?? 'new') === 'new'));
$inProgress     = count(array_filter($quotes, fn($q) => ($q['status'] ?? '') === 'in_progress'));
$completed      = count(array_filter($quotes, fn($q) => ($q['status'] ?? '') === 'completed'));

$today = date('Y-m-d');
$todayQuotes = count(array_filter($quotes, fn($q) => isset($q['timestamp']) && str_starts_with($q['timestamp'], $today)));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <link rel="icon" href="favicon.svg" type="image/svg+xml" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Quote Requests Dashboard — Fastrux</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="shared.css" />
  <script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>
  <style>
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

    /* Stats cards */
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

    /* Toolbar */
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
      transition: border-color .2s;
      appearance: none;
      -webkit-appearance: none;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 12px center;
    }
    .filter-select:focus { border-color: var(--primary); }

    /* Table */
    .table-card {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius-xl);
      overflow: hidden;
    }
    .table-responsive { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; }
    thead { background: var(--muted); }
    th {
      padding: 12px 16px;
      text-align: left;
      font-size: 12px;
      font-weight: 600;
      color: var(--muted-foreground);
      text-transform: uppercase;
      letter-spacing: .5px;
      white-space: nowrap;
      border-bottom: 1px solid var(--border);
    }
    td {
      padding: 14px 16px;
      font-size: 14px;
      border-bottom: 1px solid var(--border);
      vertical-align: middle;
    }
    tr:last-child td { border-bottom: none; }
    tr:hover td { background: var(--muted); }

    .quote-id { font-family: monospace; font-size: 12px; color: var(--muted-foreground); }
    .quote-name { font-weight: 600; }
    .quote-company { font-size: 12px; color: var(--muted-foreground); margin-top: 2px; }

    /* Status badges */
    .badge {
      display: inline-flex; align-items: center; gap: 5px;
      padding: 4px 10px;
      border-radius: 999px;
      font-size: 12px;
      font-weight: 600;
      white-space: nowrap;
    }
    .badge-new        { background: var(--secondary); color: var(--primary); }
    .badge-in_progress{ background: #fff7e6; color: #d97706; }
    .badge-completed  { background: #e6f9ee; color: var(--success); }
    .badge-declined   { background: #fef2f2; color: var(--destructive); }

    /* Status select in row */
    .status-select {
      padding: 6px 28px 6px 10px;
      border: 1.5px solid var(--border);
      border-radius: var(--radius-md);
      font-family: var(--font-family-body);
      font-size: 13px;
      background: var(--card);
      color: var(--foreground);
      outline: none;
      cursor: pointer;
      appearance: none;
      -webkit-appearance: none;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 8px center;
      transition: border-color .2s;
    }
    .status-select:focus { border-color: var(--primary); }

    /* Detail panel */
    .detail-panel {
      display: none;
      background: var(--muted);
      border-top: 1px solid var(--border);
    }
    .detail-panel.open { display: table-row; }
    .detail-inner {
      padding: 20px 24px;
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 16px;
    }
    .detail-field { display: flex; flex-direction: column; gap: 3px; }
    .detail-label { font-size: 11px; text-transform: uppercase; letter-spacing: .5px; color: var(--muted-foreground); font-weight: 600; }
    .detail-value { font-size: 14px; color: var(--foreground); }

    .empty-state {
      text-align: center;
      padding: 64px 32px;
      color: var(--muted-foreground);
    }
    .empty-state iconify-icon { display: block; font-size: 48px; margin: 0 auto 16px; }

    /* Feedback toast */
    #toast {
      position: fixed;
      bottom: 24px;
      right: 24px;
      background: var(--foreground);
      color: var(--card);
      padding: 12px 20px;
      border-radius: var(--radius-md);
      font-size: 14px;
      font-weight: 500;
      opacity: 0;
      transform: translateY(8px);
      transition: opacity .25s, transform .25s;
      z-index: 9999;
      pointer-events: none;
    }
    #toast.show { opacity: 1; transform: translateY(0); }

    @media (max-width: 1024px) {
      .stats-grid { grid-template-columns: repeat(2, 1fr); }
      .detail-inner { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 640px) {
      .stats-grid { grid-template-columns: 1fr 1fr; }
      .detail-inner { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>

  <!-- ── HEADER ── -->
  <header class="dash-header">
    <div class="container dash-header-inner">
      <a href="index" class="dash-brand">
        <iconify-icon icon="lucide:truck" style="font-size:24px"></iconify-icon>
        Fastrux <span>/ Quote Requests</span>
      </a>
      <div style="display:flex;gap:12px;align-items:center;">
        <a href="driver-dashboard" class="btn btn-outline" style="font-size:13px;padding:8px 16px;">
          <iconify-icon icon="lucide:users" style="font-size:14px;margin-right:6px"></iconify-icon>Driver Dashboard
        </a>
        <a href="?export=csv" class="btn btn-primary" style="font-size:13px;padding:8px 16px;">
          <iconify-icon icon="lucide:download" style="font-size:14px;margin-right:6px"></iconify-icon>Export CSV
        </a>
      </div>
    </div>
  </header>

  <!-- ── MAIN ── -->
  <main class="container" style="padding-top:32px;padding-bottom:48px;">

    <!-- Stats -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon blue"><iconify-icon icon="lucide:file-text"></iconify-icon></div>
        <div>
          <div class="stat-label">Total Quotes</div>
          <div class="stat-value"><?= $totalQuotes ?></div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon amber"><iconify-icon icon="lucide:inbox"></iconify-icon></div>
        <div>
          <div class="stat-label">New / Pending</div>
          <div class="stat-value"><?= $newQuotes ?></div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon purple"><iconify-icon icon="lucide:loader"></iconify-icon></div>
        <div>
          <div class="stat-label">In Progress</div>
          <div class="stat-value"><?= $inProgress ?></div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon green"><iconify-icon icon="lucide:calendar-check"></iconify-icon></div>
        <div>
          <div class="stat-label">Received Today</div>
          <div class="stat-value"><?= $todayQuotes ?></div>
        </div>
      </div>
    </div>

    <!-- Toolbar -->
    <div class="toolbar">
      <div class="toolbar-search">
        <iconify-icon icon="lucide:search"></iconify-icon>
        <input type="text" id="searchInput" placeholder="Search by name, email, company, reference…" />
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
        <option value="new">New</option>
        <option value="in_progress">In Progress</option>
        <option value="completed">Completed</option>
        <option value="declined">Declined</option>
      </select>
      <div style="font-size:13px;color:var(--muted-foreground);white-space:nowrap;">
        <strong id="visibleCount"><?= $totalQuotes ?></strong> requests
      </div>
    </div>

    <!-- Table -->
    <div class="table-card">
      <?php if (empty($quotes)): ?>
        <div class="empty-state">
          <iconify-icon icon="lucide:inbox"></iconify-icon>
          <p style="font-size:16px;font-weight:600;margin-bottom:8px;">No quote requests yet</p>
          <p>Submitted quotes will appear here automatically.</p>
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table id="quotesTable">
            <thead>
              <tr>
                <th></th>
                <th>Reference</th>
                <th>Contact</th>
                <th>Service</th>
                <th>Route</th>
                <th>Submitted</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody id="tableBody">
              <?php foreach (array_reverse($quotes) as $q):
                $qid      = htmlspecialchars($q['id']          ?? '', ENT_QUOTES, 'UTF-8');
                $fname    = htmlspecialchars($q['first_name']   ?? '', ENT_QUOTES, 'UTF-8');
                $lname    = htmlspecialchars($q['last_name']    ?? '', ENT_QUOTES, 'UTF-8');
                $company  = htmlspecialchars($q['company']      ?? '', ENT_QUOTES, 'UTF-8');
                $email    = htmlspecialchars($q['email']        ?? '', ENT_QUOTES, 'UTF-8');
                $service  = htmlspecialchars($q['service']      ?? '', ENT_QUOTES, 'UTF-8');
                $origin   = htmlspecialchars($q['origin']       ?? '', ENT_QUOTES, 'UTF-8');
                $dest     = htmlspecialchars($q['destination']  ?? '', ENT_QUOTES, 'UTF-8');
                $weight   = htmlspecialchars($q['weight_kg']    ?? '', ENT_QUOTES, 'UTF-8');
                $volume   = htmlspecialchars($q['volume_m3']    ?? '', ENT_QUOTES, 'UTF-8');
                $notes    = htmlspecialchars($q['notes']        ?? '', ENT_QUOTES, 'UTF-8');
                $ts       = htmlspecialchars($q['timestamp']    ?? '', ENT_QUOTES, 'UTF-8');
                $status   = $q['status'] ?? 'new';
                $safeStatus = htmlspecialchars($status, ENT_QUOTES, 'UTF-8');
                $updatedAt = htmlspecialchars($q['updated_at']  ?? '', ENT_QUOTES, 'UTF-8');
                $staffResp = htmlspecialchars($q['staff_response'] ?? '', ENT_QUOTES, 'UTF-8');
                $respAddedAt = htmlspecialchars($q['response_added_at'] ?? '', ENT_QUOTES, 'UTF-8');
              ?>
              <tr class="quote-row"
                  data-id="<?= $qid ?>"
                  data-search="<?= strtolower("$fname $lname $company $email $qid") ?>"
                  data-service="<?= strtolower($service) ?>"
                  data-status="<?= $safeStatus ?>">
                <td>
                  <button class="btn btn-outline toggle-detail" data-id="<?= $qid ?>"
                    style="padding:4px 10px;font-size:12px;min-width:unset;">
                    <iconify-icon icon="lucide:chevron-down" style="font-size:14px"></iconify-icon>
                  </button>
                </td>
                <td><span class="quote-id"><?= $qid ?></span></td>
                <td>
                  <div class="quote-name"><?= "$fname $lname" ?></div>
                  <?php if ($company): ?><div class="quote-company"><?= $company ?></div><?php endif; ?>
                  <div style="font-size:12px;color:var(--muted-foreground);margin-top:2px;">
                    <a href="mailto:<?= $email ?>" style="color:inherit;"><?= $email ?></a>
                  </div>
                </td>
                <td><?= $service ?></td>
                <td style="white-space:nowrap;">
                  <?php if ($origin && $dest): ?>
                    <?= $origin ?> <iconify-icon icon="lucide:arrow-right" style="font-size:12px;vertical-align:middle;margin:0 4px"></iconify-icon> <?= $dest ?>
                  <?php else: ?>
                    <span style="color:var(--muted-foreground)">—</span>
                  <?php endif; ?>
                </td>
                <td style="white-space:nowrap;font-size:13px;"><?= $ts ?></td>
                <td>
                  <select class="status-select" data-id="<?= $qid ?>" onchange="updateStatus(this)">
                    <option value="new"        <?= $status === 'new'         ? 'selected' : '' ?>>New</option>
                    <option value="in_progress"<?= $status === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                    <option value="completed"  <?= $status === 'completed'   ? 'selected' : '' ?>>Completed</option>
                    <option value="declined"   <?= $status === 'declined'    ? 'selected' : '' ?>>Declined</option>
                  </select>
                </td>
              </tr>
              <tr class="detail-panel" id="detail-<?= $qid ?>">
                <td colspan="7">
                  <div class="detail-inner">
                    <div class="detail-field">
                      <span class="detail-label">Weight (kg)</span>
                      <span class="detail-value"><?= $weight ?: '—' ?></span>
                    </div>
                    <div class="detail-field">
                      <span class="detail-label">Volume (m³)</span>
                      <span class="detail-value"><?= $volume ?: '—' ?></span>
                    </div>
                    <div class="detail-field">
                      <span class="detail-label">Last Updated</span>
                      <span class="detail-value"><?= $updatedAt ?: '—' ?></span>
                    </div>
                    <?php if ($notes): ?>
                    <div class="detail-field" style="grid-column: 1 / -1;">
                      <span class="detail-label">Notes</span>
                      <span class="detail-value"><?= $notes ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="detail-field" style="grid-column: 1 / -1;">
                      <span class="detail-label">Staff Response</span>
                      <div style="margin-top:6px;">
                        <textarea class="form-control response-textarea" data-id="<?= $qid ?>"
                          rows="3" placeholder="Enter your response to the shipper…"
                          style="width:100%;resize:vertical;"><?= $staffResp ?></textarea>
                        <div style="display:flex;align-items:center;gap:12px;margin-top:8px;">
                          <button class="btn btn-primary save-response-btn" data-id="<?= $qid ?>"
                            style="padding:7px 18px;font-size:13px;">Save Response</button>
                          <?php if ($respAddedAt): ?>
                          <span style="font-size:12px;color:var(--muted-foreground);">Last saved: <?= $respAddedAt ?></span>
                          <?php endif; ?>
                        </div>
                      </div>
                    </div>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

  </main>

  <!-- Toast notification -->
  <div id="toast"></div>

  <script>
    // ── Auth guard — employee / admin roles only ─────────────
    (function() {
      var user = null;
      try { user = JSON.parse(localStorage.getItem('fx_user')); } catch(e) {}
      var allowedRoles = ['driver', 'owner_operator', 'corporate_staff', 'admin', 'super_admin'];
      if (!user || !user.id || allowedRoles.indexOf(user.role) === -1) {
        window.location.href = 'login?redirect=' + encodeURIComponent(window.location.pathname);
      }
    })();

    // ── Filter / search ─────────────────────────────────────────
    const rows         = Array.from(document.querySelectorAll('.quote-row'));
    const searchInput  = document.getElementById('searchInput');
    const serviceFilter= document.getElementById('serviceFilter');
    const statusFilter = document.getElementById('statusFilter');
    const visibleCount = document.getElementById('visibleCount');

    function applyFilters() {
      const q  = searchInput.value.toLowerCase().trim();
      const sv = serviceFilter.value.toLowerCase();
      const st = statusFilter.value.toLowerCase();
      let n = 0;
      rows.forEach(row => {
        const matchQ  = !q  || row.dataset.search.includes(q);
        const matchSv = !sv || row.dataset.service.includes(sv);
        const matchSt = !st || row.dataset.status === st;
        const show    = matchQ && matchSv && matchSt;
        // Row + its detail panel
        row.style.display = show ? '' : 'none';
        const detail = document.getElementById('detail-' + row.dataset.id);
        if (detail && !show) {
          detail.classList.remove('open');
          detail.style.display = 'none';
        }
        if (show) n++;
      });
      visibleCount.textContent = n;
    }

    searchInput.addEventListener('input', applyFilters);
    serviceFilter.addEventListener('change', applyFilters);
    statusFilter.addEventListener('change', applyFilters);

    // ── Toggle detail panel ──────────────────────────────────────
    document.querySelectorAll('.toggle-detail').forEach(btn => {
      btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        const panel = document.getElementById('detail-' + id);
        if (!panel) return;
        const open = panel.classList.toggle('open');
        panel.style.display = open ? 'table-row' : 'none';
        const icon = btn.querySelector('iconify-icon');
        if (icon) icon.setAttribute('icon', open ? 'lucide:chevron-up' : 'lucide:chevron-down');
      });
    });

    // ── Status update ────────────────────────────────────────────
    function updateStatus(sel) {
      const quoteId = sel.dataset.id;
      const status  = sel.value;
      const fd = new FormData();
      fd.append('action',   'update_status');
      fd.append('quote_id', quoteId);
      fd.append('status',   status);

      fetch('quote-dashboard.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
          showToast(data.success ? '✓ Status updated' : '✗ ' + data.message);
          if (data.success) {
            // Update row data attribute for filter
            const row = document.querySelector(`.quote-row[data-id="${quoteId}"]`);
            if (row) row.dataset.status = status;
          }
        })
        .catch(() => showToast('✗ Network error'));
    }

    // ── Toast ────────────────────────────────────────────────────
    function showToast(msg) {
      const t = document.getElementById('toast');
      t.textContent = msg;
      t.classList.add('show');
      setTimeout(() => t.classList.remove('show'), 2800);
    }

    // ── Save staff response ──────────────────────────────────────
    document.addEventListener('click', function(e) {
      const btn = e.target.closest('.save-response-btn');
      if (!btn) return;
      const quoteId  = btn.dataset.id;
      const textarea = document.querySelector(`.response-textarea[data-id="${quoteId}"]`);
      if (!textarea) return;
      const fd = new FormData();
      fd.append('action',         'add_response');
      fd.append('quote_id',       quoteId);
      fd.append('staff_response', textarea.value);
      btn.disabled = true;
      fetch('quote-dashboard.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
          showToast(data.success ? '✓ Response saved' : '✗ ' + data.message);
        })
        .catch(() => showToast('✗ Network error'))
        .finally(() => { btn.disabled = false; });
    });
  </script>
</body>
</html>
