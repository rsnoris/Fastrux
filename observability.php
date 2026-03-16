<?php
/**
 * observability.php — Internal Observability Dashboard
 *
 * Provides KPI metrics, audit trail, and operational visibility
 * for corporate staff and administrators.
 *
 * Access: restricted to corporate_staff and admin roles.
 * Authentication is validated via the user ID passed in the query string
 * (mirrors the existing localStorage-based auth used across the app).
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/audit.php';

// ── Auth gate ─────────────────────────────────────────────
// Accepts ?user_id=USR-XXXX (supplied by the frontend after login).
// Validates that the user is corporate_staff or admin against the database.
//
// NOTE: This follows the same client-side authentication pattern used
// throughout the app (localStorage + server-side role verification).
// For production hardening, replace this with PHP session-based auth
// so user IDs cannot be guessed or spoofed via the URL.
$authUserId = preg_replace('/[^A-Za-z0-9_\-]/', '', $_GET['user_id'] ?? '');
$authRole   = '';
$authName   = 'Staff';

if ($authUserId) {
    try {
        $db   = getDb();
        $stmt = $db->prepare('SELECT first_name, last_name, role FROM users WHERE id = ? AND status = ?');
        $stmt->execute([$authUserId, 'active']);
        $authUser = $stmt->fetch();
        if ($authUser) {
            $authRole = $authUser['role'];
            $authName = htmlspecialchars($authUser['first_name'] . ' ' . $authUser['last_name'], ENT_QUOTES, 'UTF-8');
        }
    } catch (Throwable $e) {
        // DB not yet configured — show setup notice below
    }
}

$isAuthorised = in_array($authRole, ['corporate_staff', 'admin'], true);

// ── Data queries (only run when authorised & DB available) ─
$kpis     = [];
$auditRows = [];
$dbError  = '';

if ($isAuthorised) {
    try {
        $db = getDb();

        // KPI queries
        $kpis['total_users']           = (int) $db->query('SELECT COUNT(*) FROM users')->fetchColumn();
        $kpis['users_today']           = (int) $db->query('SELECT COUNT(*) FROM users WHERE DATE(created_at) = CURDATE()')->fetchColumn();
        $kpis['users_this_month']      = (int) $db->query('SELECT COUNT(*) FROM users WHERE YEAR(created_at) = YEAR(NOW()) AND MONTH(created_at) = MONTH(NOW())')->fetchColumn();

        $kpis['total_drivers']         = (int) $db->query('SELECT COUNT(*) FROM driver_applications')->fetchColumn();
        $kpis['drivers_pending']       = (int) $db->query("SELECT COUNT(*) FROM driver_applications WHERE status = 'pending'")->fetchColumn();
        $kpis['drivers_approved']      = (int) $db->query("SELECT COUNT(*) FROM driver_applications WHERE status = 'approved'")->fetchColumn();
        $kpis['drivers_active']        = (int) $db->query("SELECT COUNT(*) FROM driver_locations WHERE status IN ('available','busy')")->fetchColumn();

        $kpis['total_quotes']          = (int) $db->query('SELECT COUNT(*) FROM quotes')->fetchColumn();
        $kpis['quotes_today']          = (int) $db->query('SELECT COUNT(*) FROM quotes WHERE DATE(created_at) = CURDATE()')->fetchColumn();
        $kpis['quotes_this_month']     = (int) $db->query('SELECT COUNT(*) FROM quotes WHERE YEAR(created_at) = YEAR(NOW()) AND MONTH(created_at) = MONTH(NOW())')->fetchColumn();
        $kpis['quotes_pending']        = (int) $db->query("SELECT COUNT(*) FROM quotes WHERE status = 'pending'")->fetchColumn();

        $kpis['total_loads']           = (int) $db->query('SELECT COUNT(*) FROM loads')->fetchColumn();
        $kpis['loads_open']            = (int) $db->query("SELECT COUNT(*) FROM loads WHERE status = 'open'")->fetchColumn();
        $kpis['loads_in_transit']      = (int) $db->query("SELECT COUNT(*) FROM loads WHERE status = 'in_transit'")->fetchColumn();
        $kpis['loads_completed_month'] = (int) $db->query("SELECT COUNT(*) FROM loads WHERE status = 'completed' AND YEAR(updated_at) = YEAR(NOW()) AND MONTH(updated_at) = MONTH(NOW())")->fetchColumn();

        $kpis['total_contacts']        = (int) $db->query('SELECT COUNT(*) FROM contacts')->fetchColumn();
        $kpis['contacts_today']        = (int) $db->query('SELECT COUNT(*) FROM contacts WHERE DATE(created_at) = CURDATE()')->fetchColumn();
        $kpis['total_subscribers']     = (int) $db->query('SELECT COUNT(*) FROM newsletter_subscribers')->fetchColumn();

        // 30-day activity trends (events per day)
        $trends = $db->query(
            "SELECT DATE(created_at) AS day, COUNT(*) AS cnt
             FROM audit_log
             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)
             GROUP BY day
             ORDER BY day"
        )->fetchAll();

        $kpis['trend_days']  = array_column($trends, 'day');
        $kpis['trend_counts'] = array_map('intval', array_column($trends, 'cnt'));

        // Users by role
        $roleRows = $db->query(
            "SELECT role, COUNT(*) AS cnt FROM users GROUP BY role"
        )->fetchAll();
        $kpis['users_by_role'] = array_column($roleRows, 'cnt', 'role');

        // Load status distribution
        $loadStatusRows = $db->query(
            "SELECT status, COUNT(*) AS cnt FROM loads GROUP BY status"
        )->fetchAll();
        $kpis['loads_by_status'] = array_column($loadStatusRows, 'cnt', 'status');

        // Audit trail (last 200 events)
        $auditFilter = $_GET['audit_action'] ?? '';
        $auditLimit  = min((int) ($_GET['limit'] ?? 50), 200);
        $auditOffset = max((int) ($_GET['offset'] ?? 0), 0);

        if ($auditFilter) {
            $auditStmt = $db->prepare(
                "SELECT al.*, CONCAT(u.first_name, ' ', u.last_name) AS user_name, u.email AS user_email, u.role AS user_role
                 FROM audit_log al
                 LEFT JOIN users u ON u.id = al.user_id
                 WHERE al.action LIKE ?
                 ORDER BY al.created_at DESC
                 LIMIT ? OFFSET ?"
            );
            $auditStmt->execute(['%' . $auditFilter . '%', $auditLimit, $auditOffset]);
        } else {
            $auditStmt = $db->prepare(
                "SELECT al.*, CONCAT(u.first_name, ' ', u.last_name) AS user_name, u.email AS user_email, u.role AS user_role
                 FROM audit_log al
                 LEFT JOIN users u ON u.id = al.user_id
                 ORDER BY al.created_at DESC
                 LIMIT ? OFFSET ?"
            );
            $auditStmt->execute([$auditLimit, $auditOffset]);
        }
        $auditRows = $auditStmt->fetchAll();

        // Distinct action labels for filter dropdown
        $auditActions = $db->query('SELECT DISTINCT action FROM audit_log ORDER BY action')->fetchAll(PDO::FETCH_COLUMN);

        // Total audit log count for pagination
        $auditTotal = (int) $db->query('SELECT COUNT(*) FROM audit_log')->fetchColumn();

        auditLog('observability.viewed', $authUserId, 'dashboard', 'observability');

    } catch (Throwable $e) {
        $dbError = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <link rel="icon" href="favicon.svg" type="image/svg+xml" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Observability Dashboard — Fastrux</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="shared.css" />
  <script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
  <style>
    body { background: var(--muted); }

    .obs-layout {
      display: grid;
      grid-template-columns: 240px 1fr;
      gap: 24px;
      padding-top: 28px;
      padding-bottom: 48px;
    }

    /* ── Sidebar ── */
    .obs-sidebar {
      position: sticky;
      top: 88px;
      height: fit-content;
    }
    .obs-sidebar-card { background: var(--card); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 20px; }
    .obs-sidebar-title { font-size: 11px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--muted-foreground); margin-bottom: 10px; }
    .obs-nav-item {
      display: flex; align-items: center; gap: 10px;
      padding: 9px 12px; border-radius: var(--radius-md);
      color: var(--foreground); text-decoration: none; font-size: 14px; font-weight: 500;
      cursor: pointer; transition: background .15s;
    }
    .obs-nav-item:hover     { background: var(--muted); }
    .obs-nav-item.active    { background: var(--secondary); color: var(--primary); font-weight: 600; }
    .obs-divider { border: none; border-top: 1px solid var(--border); margin: 12px 0; }

    /* ── Main content ── */
    .obs-main { min-width: 0; }
    .obs-section { display: none; }
    .obs-section.active { display: block; }

    /* ── Section header ── */
    .obs-section-header { margin-bottom: 20px; }
    .obs-section-header h2 { font-size: 22px; font-weight: 700; margin: 0 0 4px; }
    .obs-section-header p  { font-size: 14px; color: var(--muted-foreground); margin: 0; }

    /* ── KPI cards ── */
    .kpi-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 16px; margin-bottom: 24px; }
    .kpi-card {
      background: var(--card); border: 1px solid var(--border); border-radius: var(--radius-lg);
      padding: 18px 20px;
    }
    .kpi-card-label { font-size: 12px; font-weight: 600; color: var(--muted-foreground); text-transform: uppercase; letter-spacing: .06em; margin-bottom: 8px; }
    .kpi-card-value { font-size: 30px; font-weight: 800; color: var(--foreground); line-height: 1; }
    .kpi-card-sub   { font-size: 12px; color: var(--muted-foreground); margin-top: 6px; }
    .kpi-card.accent-blue  { border-top: 3px solid var(--primary); }
    .kpi-card.accent-green { border-top: 3px solid var(--success); }
    .kpi-card.accent-amber { border-top: 3px solid var(--warning); }
    .kpi-card.accent-red   { border-top: 3px solid var(--destructive); }
    .kpi-card.accent-purple{ border-top: 3px solid #8b5cf6; }

    /* ── Charts row ── */
    .charts-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px; }
    .chart-card {
      background: var(--card); border: 1px solid var(--border); border-radius: var(--radius-lg);
      padding: 20px;
    }
    .chart-card h3 { font-size: 15px; font-weight: 700; margin: 0 0 16px; }
    .chart-card.wide { grid-column: span 2; }

    /* ── Table card ── */
    .table-card {
      background: var(--card); border: 1px solid var(--border); border-radius: var(--radius-lg);
      overflow: hidden; margin-bottom: 24px;
    }
    .table-card-header {
      display: flex; align-items: center; justify-content: space-between;
      padding: 16px 20px; border-bottom: 1px solid var(--border);
    }
    .table-card-header h3 { font-size: 16px; font-weight: 700; margin: 0; }
    .table-wrapper { overflow-x: auto; }
    table.obs-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    table.obs-table th {
      text-align: left; padding: 10px 14px;
      background: var(--muted); font-weight: 600; font-size: 11px;
      text-transform: uppercase; letter-spacing: .06em; color: var(--muted-foreground);
      border-bottom: 1px solid var(--border);
    }
    table.obs-table td { padding: 10px 14px; border-bottom: 1px solid var(--border); vertical-align: top; }
    table.obs-table tr:last-child td { border-bottom: none; }
    table.obs-table tr:hover td { background: var(--muted); }

    /* ── Badges ── */
    .badge {
      display: inline-block; padding: 2px 8px; border-radius: 99px;
      font-size: 11px; font-weight: 600;
    }
    .badge-blue   { background: #dbeafe; color: #1d4ed8; }
    .badge-green  { background: #dcfce7; color: #15803d; }
    .badge-amber  { background: #fef3c7; color: #b45309; }
    .badge-red    { background: #fee2e2; color: #b91c1c; }
    .badge-gray   { background: var(--muted); color: var(--muted-foreground); }
    .badge-purple { background: #ede9fe; color: #6d28d9; }

    /* ── Filter bar ── */
    .filter-bar {
      display: flex; align-items: center; gap: 10px;
      padding: 14px 20px; background: var(--muted);
      border-bottom: 1px solid var(--border);
    }
    .filter-bar label { font-size: 13px; font-weight: 600; }
    .filter-bar select, .filter-bar input {
      border: 1px solid var(--border); border-radius: var(--radius-md);
      padding: 6px 10px; font-size: 13px; background: var(--input);
    }
    .filter-bar .btn-filter {
      padding: 7px 14px; border-radius: var(--radius-md);
      background: var(--primary); color: white; border: none;
      font-size: 13px; font-weight: 600; cursor: pointer;
    }
    .filter-bar .btn-reset {
      padding: 7px 14px; border-radius: var(--radius-md);
      background: var(--muted); color: var(--foreground); border: 1px solid var(--border);
      font-size: 13px; font-weight: 600; cursor: pointer;
    }

    /* ── Pagination ── */
    .pagination { display: flex; align-items: center; gap: 8px; padding: 14px 20px; border-top: 1px solid var(--border); }
    .pagination span { font-size: 13px; color: var(--muted-foreground); }
    .pagination a {
      padding: 5px 12px; border-radius: var(--radius-md);
      border: 1px solid var(--border); font-size: 13px; text-decoration: none;
      color: var(--foreground); background: var(--card);
    }
    .pagination a:hover    { background: var(--muted); }
    .pagination a.active   { background: var(--primary); color: white; border-color: var(--primary); }
    .pagination a.disabled { opacity: .4; pointer-events: none; }

    /* ── Setup notice ── */
    .setup-notice {
      background: #fffbeb; border: 1px solid #fbbf24; border-radius: var(--radius-lg);
      padding: 20px 24px; margin-bottom: 24px;
    }
    .setup-notice h3 { margin: 0 0 8px; font-size: 16px; color: #92400e; }
    .setup-notice p  { margin: 0; font-size: 14px; color: #78350f; }
    .setup-notice code { background: #fef3c7; padding: 2px 6px; border-radius: 4px; font-family: monospace; font-size: 13px; }

    /* ── Details JSON ── */
    .details-json { font-family: monospace; font-size: 11px; color: var(--muted-foreground); max-width: 260px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

    @media (max-width: 900px) {
      .obs-layout { grid-template-columns: 1fr; }
      .obs-sidebar { position: static; }
      .charts-row { grid-template-columns: 1fr; }
      .chart-card.wide { grid-column: span 1; }
    }
  </style>
</head>
<body>

<!-- ── Nav ── -->
<header class="header">
  <div class="container header-content">
    <a class="logo" href="index.php">
      <span class="logo-icon"><iconify-icon icon="mdi:truck-fast" width="28" height="28"></iconify-icon></span>
      <span class="logo-text">Fastrux</span>
    </a>
    <nav class="nav-links">
      <a class="nav-link" href="index.php">Home</a>
      <a class="nav-link" href="index.php#services">Services</a>
      <a class="nav-link" href="track.php">Tracking</a>
      <a class="nav-link" href="about.php">About Us</a>
      <a class="nav-link" href="contact.php">Contact</a>
    </nav>
    <div class="header-actions">
      <a class="nav-link active" href="observability.php">Observability</a>
      <a class="btn btn-secondary" href="account.php">
        <iconify-icon icon="mdi:account-circle" width="18" height="18"></iconify-icon>
        My Account
      </a>
    </div>
  </div>
</header>

<div class="container obs-layout">

  <!-- ── Sidebar ── -->
  <aside class="obs-sidebar">
    <div class="obs-sidebar-card">
      <div class="obs-sidebar-title">Observability</div>
      <div class="obs-nav-item active" data-section="overview" onclick="showSection('overview', this)">
        <iconify-icon icon="mdi:view-dashboard-outline" width="18" height="18"></iconify-icon>
        Overview
      </div>
      <div class="obs-nav-item" data-section="users" onclick="showSection('users', this)">
        <iconify-icon icon="mdi:account-group-outline" width="18" height="18"></iconify-icon>
        Users
      </div>
      <div class="obs-nav-item" data-section="drivers" onclick="showSection('drivers', this)">
        <iconify-icon icon="mdi:truck-outline" width="18" height="18"></iconify-icon>
        Drivers
      </div>
      <div class="obs-nav-item" data-section="quotes" onclick="showSection('quotes', this)">
        <iconify-icon icon="mdi:file-document-outline" width="18" height="18"></iconify-icon>
        Quotes
      </div>
      <div class="obs-nav-item" data-section="loads" onclick="showSection('loads', this)">
        <iconify-icon icon="mdi:package-variant-closed" width="18" height="18"></iconify-icon>
        Loads
      </div>
      <hr class="obs-divider" />
      <div class="obs-nav-item" data-section="audit" onclick="showSection('audit', this)">
        <iconify-icon icon="mdi:shield-search" width="18" height="18"></iconify-icon>
        Audit Trail
      </div>
    </div>

    <?php if ($isAuthorised): ?>
    <div class="obs-sidebar-card" style="margin-top:12px; font-size:13px;">
      <div style="font-weight:600; margin-bottom:4px;"><?= $authName ?></div>
      <div style="color:var(--muted-foreground);"><?= htmlspecialchars($authRole, ENT_QUOTES, 'UTF-8') ?></div>
    </div>
    <?php endif; ?>
  </aside>

  <!-- ── Main ── -->
  <main class="obs-main">

    <?php if (!$isAuthorised): ?>
    <!-- Access gate -->
    <div class="table-card" style="padding:48px; text-align:center;">
      <iconify-icon icon="mdi:lock-outline" width="48" height="48" style="color:var(--muted-foreground)"></iconify-icon>
      <h2 style="margin:16px 0 8px;">Access Restricted</h2>
      <p style="color:var(--muted-foreground); margin:0 0 24px; max-width:420px; margin-inline:auto;">
        This dashboard is available to <strong>corporate staff</strong> and <strong>admin</strong> users only.
        Please sign in with an authorised account and append <code>?user_id=YOUR_USER_ID</code> to the URL.
      </p>
      <a class="btn btn-primary" href="login.php">
        <iconify-icon icon="mdi:login" width="16" height="16"></iconify-icon>
        Sign In
      </a>
    </div>

    <?php else: ?>

    <?php if ($dbError): ?>
    <div class="setup-notice">
      <h3>⚠ Database connection error</h3>
      <p>Could not connect to MySQL. Please ensure the database is running and the environment variables are set:<br/>
         <code>DB_HOST</code>, <code>DB_PORT</code>, <code>DB_NAME</code>, <code>DB_USER</code>, <code>DB_PASS</code></p>
      <p style="margin-top:10px; font-family:monospace; font-size:12px;"><?= $dbError ?></p>
    </div>
    <?php endif; ?>

    <!-- ══ OVERVIEW SECTION ══════════════════════════════════ -->
    <div id="section-overview" class="obs-section active">
      <div class="obs-section-header">
        <h2>Overview</h2>
        <p>Platform health at a glance — all key metrics in one view.</p>
      </div>

      <!-- KPI row 1: Users -->
      <div style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.07em; color:var(--muted-foreground); margin-bottom:10px;">Users</div>
      <div class="kpi-grid">
        <div class="kpi-card accent-blue">
          <div class="kpi-card-label">Total Users</div>
          <div class="kpi-card-value"><?= number_format($kpis['total_users'] ?? 0) ?></div>
          <div class="kpi-card-sub">+<?= $kpis['users_today'] ?? 0 ?> today</div>
        </div>
        <div class="kpi-card accent-green">
          <div class="kpi-card-label">This Month</div>
          <div class="kpi-card-value"><?= number_format($kpis['users_this_month'] ?? 0) ?></div>
          <div class="kpi-card-sub">New registrations</div>
        </div>
        <div class="kpi-card accent-purple">
          <div class="kpi-card-label">Subscribers</div>
          <div class="kpi-card-value"><?= number_format($kpis['total_subscribers'] ?? 0) ?></div>
          <div class="kpi-card-sub">Newsletter</div>
        </div>
      </div>

      <!-- KPI row 2: Drivers -->
      <div style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.07em; color:var(--muted-foreground); margin-bottom:10px;">Drivers</div>
      <div class="kpi-grid">
        <div class="kpi-card accent-blue">
          <div class="kpi-card-label">Total Drivers</div>
          <div class="kpi-card-value"><?= number_format($kpis['total_drivers'] ?? 0) ?></div>
          <div class="kpi-card-sub">Applications</div>
        </div>
        <div class="kpi-card accent-amber">
          <div class="kpi-card-label">Pending Review</div>
          <div class="kpi-card-value"><?= number_format($kpis['drivers_pending'] ?? 0) ?></div>
          <div class="kpi-card-sub">Awaiting approval</div>
        </div>
        <div class="kpi-card accent-green">
          <div class="kpi-card-label">Approved</div>
          <div class="kpi-card-value"><?= number_format($kpis['drivers_approved'] ?? 0) ?></div>
          <div class="kpi-card-sub">Active drivers</div>
        </div>
        <div class="kpi-card accent-blue">
          <div class="kpi-card-label">Online Now</div>
          <div class="kpi-card-value"><?= number_format($kpis['drivers_active'] ?? 0) ?></div>
          <div class="kpi-card-sub">Available or busy</div>
        </div>
      </div>

      <!-- KPI row 3: Quotes -->
      <div style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.07em; color:var(--muted-foreground); margin-bottom:10px;">Quotes</div>
      <div class="kpi-grid">
        <div class="kpi-card accent-blue">
          <div class="kpi-card-label">Total Quotes</div>
          <div class="kpi-card-value"><?= number_format($kpis['total_quotes'] ?? 0) ?></div>
          <div class="kpi-card-sub">All time</div>
        </div>
        <div class="kpi-card accent-amber">
          <div class="kpi-card-label">Pending</div>
          <div class="kpi-card-value"><?= number_format($kpis['quotes_pending'] ?? 0) ?></div>
          <div class="kpi-card-sub">Awaiting response</div>
        </div>
        <div class="kpi-card accent-green">
          <div class="kpi-card-label">Today</div>
          <div class="kpi-card-value"><?= number_format($kpis['quotes_today'] ?? 0) ?></div>
          <div class="kpi-card-sub">New requests</div>
        </div>
        <div class="kpi-card accent-blue">
          <div class="kpi-card-label">This Month</div>
          <div class="kpi-card-value"><?= number_format($kpis['quotes_this_month'] ?? 0) ?></div>
          <div class="kpi-card-sub">Quote requests</div>
        </div>
      </div>

      <!-- KPI row 4: Loads -->
      <div style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.07em; color:var(--muted-foreground); margin-bottom:10px;">Loads</div>
      <div class="kpi-grid">
        <div class="kpi-card accent-blue">
          <div class="kpi-card-label">Total Loads</div>
          <div class="kpi-card-value"><?= number_format($kpis['total_loads'] ?? 0) ?></div>
          <div class="kpi-card-sub">All time</div>
        </div>
        <div class="kpi-card accent-amber">
          <div class="kpi-card-label">Open</div>
          <div class="kpi-card-value"><?= number_format($kpis['loads_open'] ?? 0) ?></div>
          <div class="kpi-card-sub">Awaiting driver</div>
        </div>
        <div class="kpi-card accent-blue">
          <div class="kpi-card-label">In Transit</div>
          <div class="kpi-card-value"><?= number_format($kpis['loads_in_transit'] ?? 0) ?></div>
          <div class="kpi-card-sub">Currently moving</div>
        </div>
        <div class="kpi-card accent-green">
          <div class="kpi-card-label">Completed (month)</div>
          <div class="kpi-card-value"><?= number_format($kpis['loads_completed_month'] ?? 0) ?></div>
          <div class="kpi-card-sub">This month</div>
        </div>
      </div>

      <!-- Charts -->
      <div class="charts-row">
        <div class="chart-card wide">
          <h3>Activity Trend — Last 30 Days</h3>
          <canvas id="chartTrend" height="80"></canvas>
        </div>
        <div class="chart-card">
          <h3>Users by Role</h3>
          <canvas id="chartRoles" height="180"></canvas>
        </div>
        <div class="chart-card">
          <h3>Load Status Distribution</h3>
          <canvas id="chartLoads" height="180"></canvas>
        </div>
      </div>
    </div>

    <!-- ══ USERS SECTION ══════════════════════════════════════ -->
    <div id="section-users" class="obs-section">
      <div class="obs-section-header">
        <h2>Users</h2>
        <p>All registered user accounts and their roles.</p>
      </div>
      <?php if (!$dbError): ?>
      <?php
        $users = getDb()->query('SELECT id, first_name, last_name, email, role, status, last_login_at, created_at FROM users ORDER BY created_at DESC LIMIT 200')->fetchAll();
      ?>
      <div class="table-card">
        <div class="table-card-header">
          <h3>Registered Users (<?= count($users) ?>)</h3>
        </div>
        <div class="table-wrapper">
          <table class="obs-table">
            <thead>
              <tr>
                <th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Last Login</th><th>Registered</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $u): ?>
              <tr>
                <td><code style="font-size:11px;"><?= htmlspecialchars($u['id'], ENT_QUOTES, 'UTF-8') ?></code></td>
                <td><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?php
                  $roleBadge = ['shipper' => 'badge-blue', 'customer' => 'badge-purple', 'driver' => 'badge-green', 'owner_operator' => 'badge-amber', 'corporate_staff' => 'badge-red', 'admin' => 'badge-red'];
                  echo '<span class="badge ' . ($roleBadge[$u['role']] ?? 'badge-gray') . '">' . htmlspecialchars($u['role'], ENT_QUOTES, 'UTF-8') . '</span>';
                ?></td>
                <td><?= $u['status'] === 'active' ? '<span class="badge badge-green">active</span>' : '<span class="badge badge-red">' . htmlspecialchars($u['status'], ENT_QUOTES, 'UTF-8') . '</span>' ?></td>
                <td style="font-size:12px; color:var(--muted-foreground);"><?= $u['last_login_at'] ? htmlspecialchars($u['last_login_at'], ENT_QUOTES, 'UTF-8') : '—' ?></td>
                <td style="font-size:12px; color:var(--muted-foreground);"><?= htmlspecialchars($u['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <!-- ══ DRIVERS SECTION ══════════════════════════════════════ -->
    <div id="section-drivers" class="obs-section">
      <div class="obs-section-header">
        <h2>Drivers</h2>
        <p>Driver applications and their current approval status.</p>
      </div>
      <?php if (!$dbError): ?>
      <?php
        $driverList = getDb()->query(
          'SELECT da.id, da.first_name, da.last_name, da.email, da.phone, da.van_reg, da.van_type, da.status,
                  da.payload_kg, da.volume_m3, da.operating_areas, da.created_at,
                  dl.status AS loc_status, dl.updated_at AS loc_updated
           FROM driver_applications da
           LEFT JOIN driver_locations dl ON dl.driver_id = da.id
           ORDER BY da.created_at DESC LIMIT 200'
        )->fetchAll();
      ?>
      <div class="table-card">
        <div class="table-card-header">
          <h3>Driver Applications (<?= count($driverList) ?>)</h3>
        </div>
        <div class="table-wrapper">
          <table class="obs-table">
            <thead>
              <tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Vehicle</th><th>Status</th><th>Location</th><th>Applied</th></tr>
            </thead>
            <tbody>
            <?php foreach ($driverList as $dr): ?>
              <tr>
                <td><code style="font-size:11px;"><?= htmlspecialchars($dr['id'], ENT_QUOTES, 'UTF-8') ?></code></td>
                <td><?= htmlspecialchars($dr['first_name'] . ' ' . $dr['last_name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($dr['email'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($dr['phone'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($dr['van_reg'] . ' ' . $dr['van_type'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?php
                  $sb = ['pending' => 'badge-amber', 'approved' => 'badge-green', 'rejected' => 'badge-red'];
                  echo '<span class="badge ' . ($sb[$dr['status']] ?? 'badge-gray') . '">' . htmlspecialchars($dr['status'], ENT_QUOTES, 'UTF-8') . '</span>';
                ?></td>
                <td><?php
                  $ls = $dr['loc_status'] ?? null;
                  if ($ls) {
                    $lb = ['available' => 'badge-green', 'busy' => 'badge-amber', 'offline' => 'badge-gray'];
                    echo '<span class="badge ' . ($lb[$ls] ?? 'badge-gray') . '">' . htmlspecialchars($ls, ENT_QUOTES, 'UTF-8') . '</span>';
                  } else {
                    echo '<span class="badge badge-gray">offline</span>';
                  }
                ?></td>
                <td style="font-size:12px; color:var(--muted-foreground);"><?= htmlspecialchars($dr['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <!-- ══ QUOTES SECTION ══════════════════════════════════════ -->
    <div id="section-quotes" class="obs-section">
      <div class="obs-section-header">
        <h2>Quotes</h2>
        <p>All freight quote requests submitted through the platform.</p>
      </div>
      <?php if (!$dbError): ?>
      <?php
        $quoteList = getDb()->query(
          'SELECT id, first_name, last_name, email, service, origin, destination, weight_kg, volume_m3, status, created_at
           FROM quotes ORDER BY created_at DESC LIMIT 200'
        )->fetchAll();
      ?>
      <div class="table-card">
        <div class="table-card-header">
          <h3>Quote Requests (<?= count($quoteList) ?>)</h3>
        </div>
        <div class="table-wrapper">
          <table class="obs-table">
            <thead>
              <tr><th>ID</th><th>Name</th><th>Email</th><th>Service</th><th>Route</th><th>Status</th><th>Submitted</th></tr>
            </thead>
            <tbody>
            <?php foreach ($quoteList as $q): ?>
              <tr>
                <td><code style="font-size:11px;"><?= htmlspecialchars($q['id'], ENT_QUOTES, 'UTF-8') ?></code></td>
                <td><?= htmlspecialchars($q['first_name'] . ' ' . $q['last_name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($q['email'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($q['service'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($q['origin'] . ' → ' . $q['destination'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?php
                  $qb = ['pending' => 'badge-amber', 'quoted' => 'badge-blue', 'accepted' => 'badge-green', 'rejected' => 'badge-red'];
                  echo '<span class="badge ' . ($qb[$q['status']] ?? 'badge-gray') . '">' . htmlspecialchars($q['status'], ENT_QUOTES, 'UTF-8') . '</span>';
                ?></td>
                <td style="font-size:12px; color:var(--muted-foreground);"><?= htmlspecialchars($q['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <!-- ══ LOADS SECTION ══════════════════════════════════════ -->
    <div id="section-loads" class="obs-section">
      <div class="obs-section-header">
        <h2>Loads</h2>
        <p>All freight load requests and their current status.</p>
      </div>
      <?php if (!$dbError): ?>
      <?php
        $loadList = getDb()->query(
          'SELECT l.id, l.pickup_address, l.delivery_address, l.cargo_description,
                  l.weight_kg, l.volume_m3, l.scheduled_date, l.status,
                  l.assigned_driver_id, da.first_name, da.last_name,
                  l.created_at, l.updated_at
           FROM loads l
           LEFT JOIN driver_applications da ON da.id = l.assigned_driver_id
           ORDER BY l.created_at DESC LIMIT 200'
        )->fetchAll();
      ?>
      <div class="table-card">
        <div class="table-card-header">
          <h3>Load Requests (<?= count($loadList) ?>)</h3>
        </div>
        <div class="table-wrapper">
          <table class="obs-table">
            <thead>
              <tr><th>ID</th><th>Pickup</th><th>Delivery</th><th>Cargo</th><th>Status</th><th>Assigned Driver</th><th>Date</th><th>Created</th></tr>
            </thead>
            <tbody>
            <?php foreach ($loadList as $l): ?>
              <tr>
                <td><code style="font-size:11px;"><?= htmlspecialchars($l['id'], ENT_QUOTES, 'UTF-8') ?></code></td>
                <td style="max-width:160px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="<?= htmlspecialchars($l['pickup_address'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($l['pickup_address'], ENT_QUOTES, 'UTF-8') ?></td>
                <td style="max-width:160px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="<?= htmlspecialchars($l['delivery_address'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($l['delivery_address'], ENT_QUOTES, 'UTF-8') ?></td>
                <td style="max-width:120px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?= htmlspecialchars($l['cargo_description'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?php
                  $lb = ['open' => 'badge-blue', 'matched' => 'badge-purple', 'in_transit' => 'badge-amber', 'completed' => 'badge-green', 'cancelled' => 'badge-red'];
                  echo '<span class="badge ' . ($lb[$l['status']] ?? 'badge-gray') . '">' . htmlspecialchars($l['status'], ENT_QUOTES, 'UTF-8') . '</span>';
                ?></td>
                <td><?= $l['assigned_driver_id'] ? htmlspecialchars($l['first_name'] . ' ' . $l['last_name'], ENT_QUOTES, 'UTF-8') : '<span style="color:var(--muted-foreground)">—</span>' ?></td>
                <td style="font-size:12px;"><?= htmlspecialchars($l['scheduled_date'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                <td style="font-size:12px; color:var(--muted-foreground);"><?= htmlspecialchars($l['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <!-- ══ AUDIT TRAIL SECTION ═══════════════════════════════════ -->
    <div id="section-audit" class="obs-section">
      <div class="obs-section-header">
        <h2>Audit Trail</h2>
        <p>Full log of all user actions across the platform, ordered newest first.</p>
      </div>

      <?php
        $auditFilter  = htmlspecialchars($_GET['audit_action'] ?? '', ENT_QUOTES, 'UTF-8');
        $auditLimit   = min((int) ($_GET['limit'] ?? 50), 200);
        $auditOffset  = max((int) ($_GET['offset'] ?? 0), 0);
        $auditActions = [];
        $auditTotal   = 0;
        if (!$dbError) {
          $auditActions = getDb()->query('SELECT DISTINCT action FROM audit_log ORDER BY action')->fetchAll(PDO::FETCH_COLUMN);
          $auditTotal   = (int) getDb()->query('SELECT COUNT(*) FROM audit_log')->fetchColumn();
        }
      ?>

      <div class="table-card">
        <!-- Filter bar -->
        <form method="get" action="">
          <input type="hidden" name="user_id" value="<?= htmlspecialchars($_GET['user_id'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
          <div class="filter-bar">
            <label for="filterAction">Action</label>
            <select name="audit_action" id="filterAction">
              <option value="">All actions</option>
              <?php foreach ($auditActions as $a): ?>
                <option value="<?= htmlspecialchars($a, ENT_QUOTES, 'UTF-8') ?>" <?= $auditFilter === $a ? 'selected' : '' ?>>
                  <?= htmlspecialchars($a, ENT_QUOTES, 'UTF-8') ?>
                </option>
              <?php endforeach; ?>
            </select>
            <label for="filterLimit">Show</label>
            <select name="limit" id="filterLimit">
              <?php foreach ([25, 50, 100, 200] as $lv): ?>
                <option value="<?= $lv ?>" <?= $auditLimit === $lv ? 'selected' : '' ?>><?= $lv ?> rows</option>
              <?php endforeach; ?>
            </select>
            <button type="submit" class="btn-filter">Filter</button>
            <a href="?user_id=<?= urlencode($_GET['user_id'] ?? '') ?>" class="btn-reset">Reset</a>
          </div>
        </form>

        <div class="table-card-header" style="border-top:1px solid var(--border);">
          <h3>Events</h3>
          <span style="font-size:13px; color:var(--muted-foreground);"><?= number_format($auditTotal) ?> total</span>
        </div>
        <div class="table-wrapper">
          <table class="obs-table">
            <thead>
              <tr>
                <th>#</th><th>Time</th><th>Action</th><th>User</th><th>Entity</th><th>IP Address</th><th>Details</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($auditRows as $ev): ?>
              <tr>
                <td style="color:var(--muted-foreground); font-size:12px;"><?= (int) $ev['id'] ?></td>
                <td style="white-space:nowrap; font-size:12px; color:var(--muted-foreground);">
                  <?= htmlspecialchars($ev['created_at'], ENT_QUOTES, 'UTF-8') ?>
                </td>
                <td>
                  <?php
                    $actionParts = explode('.', $ev['action'], 2);
                    $ns  = $actionParts[0] ?? '';
                    $act = $actionParts[1] ?? $ev['action'];
                    $nsBadge = ['user' => 'badge-blue', 'quote' => 'badge-purple', 'driver' => 'badge-green', 'load' => 'badge-amber', 'contact' => 'badge-gray', 'kyc' => 'badge-red', 'config' => 'badge-gray', 'newsletter' => 'badge-purple', 'observability' => 'badge-gray'];
                    echo '<span class="badge ' . ($nsBadge[$ns] ?? 'badge-gray') . '" style="margin-right:4px">' . htmlspecialchars($ns, ENT_QUOTES, 'UTF-8') . '</span>';
                    echo htmlspecialchars($act, ENT_QUOTES, 'UTF-8');
                  ?>
                </td>
                <td>
                  <?php if ($ev['user_id']): ?>
                    <div style="font-size:12px; font-weight:600;"><?= htmlspecialchars($ev['user_name'] ?? 'Unknown', ENT_QUOTES, 'UTF-8') ?></div>
                    <div style="font-size:11px; color:var(--muted-foreground);"><?= htmlspecialchars($ev['user_email'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                  <?php else: ?>
                    <span style="color:var(--muted-foreground); font-size:12px;">Anonymous</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($ev['entity_type']): ?>
                    <div style="font-size:12px;"><?= htmlspecialchars($ev['entity_type'], ENT_QUOTES, 'UTF-8') ?></div>
                    <code style="font-size:10px; color:var(--muted-foreground);"><?= htmlspecialchars($ev['entity_id'] ?? '', ENT_QUOTES, 'UTF-8') ?></code>
                  <?php else: ?>
                    <span style="color:var(--muted-foreground)">—</span>
                  <?php endif; ?>
                </td>
                <td style="font-size:12px; color:var(--muted-foreground);">
                  <?= htmlspecialchars($ev['ip_address'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
                </td>
                <td>
                  <?php if ($ev['details']): ?>
                    <div class="details-json" title="<?= htmlspecialchars($ev['details'], ENT_QUOTES, 'UTF-8') ?>">
                      <?= htmlspecialchars($ev['details'], ENT_QUOTES, 'UTF-8') ?>
                    </div>
                  <?php else: ?>
                    <span style="color:var(--muted-foreground)">—</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($auditRows)): ?>
              <tr><td colspan="7" style="text-align:center; padding:32px; color:var(--muted-foreground);">No audit events found.</td></tr>
            <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <?php if ($auditTotal > $auditLimit): ?>
        <div class="pagination">
          <?php
            $userId  = urlencode($_GET['user_id'] ?? '');
            $aFilt   = urlencode($auditFilter);
            $prevOff = $auditOffset - $auditLimit;
            $nextOff = $auditOffset + $auditLimit;
            $hasPrev = $prevOff >= 0;
            $hasNext = $nextOff < $auditTotal;
          ?>
          <a href="?user_id=<?= $userId ?>&audit_action=<?= $aFilt ?>&limit=<?= $auditLimit ?>&offset=<?= max($prevOff, 0) ?>#section-audit"
             class="<?= $hasPrev ? '' : 'disabled' ?>">← Previous</a>
          <span>
            <?= $auditOffset + 1 ?>–<?= min($auditOffset + $auditLimit, $auditTotal) ?>
            of <?= number_format($auditTotal) ?>
          </span>
          <a href="?user_id=<?= $userId ?>&audit_action=<?= $aFilt ?>&limit=<?= $auditLimit ?>&offset=<?= $nextOff ?>#section-audit"
             class="<?= $hasNext ? '' : 'disabled' ?>">Next →</a>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <!-- end sections -->

    <?php endif; // isAuthorised ?>
  </main>
</div>

<!-- ── Footer ── -->
<footer class="footer" style="margin-top:0;">
  <div class="container footer-content">
    <span class="footer-copy">© <?= date('Y') ?> Fastrux Logistics. Internal Dashboard — Authorised Access Only.</span>
  </div>
</footer>

<script>
// ── Section navigation ──────────────────────────────────
function showSection(id, el) {
  document.querySelectorAll('.obs-section').forEach(s => s.classList.remove('active'));
  document.querySelectorAll('.obs-nav-item').forEach(n => n.classList.remove('active'));
  document.getElementById('section-' + id).classList.add('active');
  el.classList.add('active');
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Jump to section from URL hash
(function() {
  var hash = window.location.hash.replace('#section-', '');
  if (hash) {
    var el = document.querySelector('[data-section="' + hash + '"]');
    if (el) showSection(hash, el);
  }
})();

// ── Charts ──────────────────────────────────────────────
<?php if ($isAuthorised && !$dbError): ?>

// Trend chart
const trendDays   = <?= json_encode($kpis['trend_days'] ?? []) ?>;
const trendCounts = <?= json_encode($kpis['trend_counts'] ?? []) ?>;
new Chart(document.getElementById('chartTrend'), {
  type: 'line',
  data: {
    labels: trendDays,
    datasets: [{
      label: 'Events per day',
      data: trendCounts,
      borderColor: '#0b6fff',
      backgroundColor: 'rgba(11,111,255,0.08)',
      borderWidth: 2,
      pointRadius: 3,
      tension: 0.3,
      fill: true,
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
  }
});

// Roles doughnut
const rolesData = <?= json_encode($kpis['users_by_role'] ?? new stdClass()) ?>;
const roleLabels  = Object.keys(rolesData);
const roleValues  = Object.values(rolesData);
new Chart(document.getElementById('chartRoles'), {
  type: 'doughnut',
  data: {
    labels: roleLabels,
    datasets: [{
      data: roleValues,
      backgroundColor: ['#0b6fff','#16a34a','#f59e0b','#8b5cf6','#e02424','#06b6d4'],
      borderWidth: 2, borderColor: '#fff'
    }]
  },
  options: { responsive: true, plugins: { legend: { position: 'right', labels: { font: { size: 12 } } } } }
});

// Loads status doughnut
const loadsData = <?= json_encode($kpis['loads_by_status'] ?? new stdClass()) ?>;
const loadLabels  = Object.keys(loadsData);
const loadValues  = Object.values(loadsData);
new Chart(document.getElementById('chartLoads'), {
  type: 'doughnut',
  data: {
    labels: loadLabels,
    datasets: [{
      data: loadValues,
      backgroundColor: ['#0b6fff','#8b5cf6','#f59e0b','#16a34a','#e02424'],
      borderWidth: 2, borderColor: '#fff'
    }]
  },
  options: { responsive: true, plugins: { legend: { position: 'right', labels: { font: { size: 12 } } } } }
});

<?php endif; ?>
</script>
</body>
</html>
