<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <link rel="icon" href="favicon.svg" type="image/svg+xml" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>System Architecture — Fastrux Logistics</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="shared.css" />
  <script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>
  <style>
    /* ── TOC sidebar ── */
    .arch-layout {
      display: grid;
      grid-template-columns: 240px 1fr;
      gap: 40px;
      align-items: start;
    }
    .toc {
      position: sticky;
      top: 88px;
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius-xl);
      padding: 24px;
      box-shadow: var(--shadow-sm);
    }
    .toc h4 {
      font-size: 12px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .08em;
      color: var(--muted-foreground);
      margin-bottom: 16px;
    }
    .toc-links { display: flex; flex-direction: column; gap: 4px; }
    .toc-link {
      font-size: 14px;
      color: var(--muted-foreground);
      padding: 6px 10px;
      border-radius: var(--radius-md);
      transition: background .15s, color .15s;
      cursor: pointer;
    }
    .toc-link:hover, .toc-link.active {
      background: var(--secondary);
      color: var(--primary);
    }
    .toc-link.sub { padding-left: 22px; font-size: 13px; }

    /* ── Section anchors ── */
    .arch-section { scroll-margin-top: 88px; }

    /* ── Section headings ── */
    .arch-h2 {
      font-size: 28px;
      font-weight: 700;
      margin-bottom: 8px;
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .arch-h2 iconify-icon {
      width: 36px; height: 36px;
      background: var(--secondary);
      border-radius: var(--radius-md);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      color: var(--primary);
      flex-shrink: 0;
    }
    .arch-h3 {
      font-size: 18px;
      font-weight: 600;
      margin: 28px 0 12px;
      color: var(--foreground);
      border-left: 3px solid var(--primary);
      padding-left: 12px;
    }
    .arch-lead {
      color: var(--muted-foreground);
      margin-bottom: 28px;
      font-size: 15px;
      line-height: 1.7;
    }

    /* ── Tech stack tags ── */
    .tag-list { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 24px; }
    .tag {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 13px;
      font-weight: 500;
      padding: 5px 12px;
      border-radius: 999px;
      background: var(--secondary);
      color: var(--secondary-foreground);
      border: 1px solid var(--border);
    }
    .tag.green  { background: #dcfce7; color: #15803d; border-color: #bbf7d0; }
    .tag.orange { background: #fff7ed; color: #c2410c; border-color: #fed7aa; }
    .tag.purple { background: #f5f3ff; color: #6d28d9; border-color: #ddd6fe; }

    /* ── Cards grid ── */
    .info-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 20px;
      margin-bottom: 32px;
    }
    .info-card {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius-xl);
      padding: 20px;
      box-shadow: var(--shadow-sm);
    }
    .info-card .ic-icon {
      width: 40px; height: 40px;
      border-radius: var(--radius-md);
      display: flex; align-items: center; justify-content: center;
      margin-bottom: 12px;
      font-size: 20px;
    }
    .ic-blue   { background: #dbeafe; color: #1d4ed8; }
    .ic-green  { background: #dcfce7; color: #15803d; }
    .ic-purple { background: #f5f3ff; color: #6d28d9; }
    .ic-orange { background: #fff7ed; color: #c2410c; }
    .ic-teal   { background: #ccfbf1; color: #0f766e; }
    .ic-red    { background: #fee2e2; color: #b91c1c; }
    .info-card h4 { font-size: 15px; font-weight: 600; margin-bottom: 6px; }
    .info-card p  { font-size: 13px; color: var(--muted-foreground); line-height: 1.6; }

    /* ── Data flow steps ── */
    .flow-steps { display: flex; flex-direction: column; gap: 0; margin-bottom: 32px; }
    .flow-step {
      display: flex;
      gap: 20px;
      position: relative;
    }
    .flow-step:not(:last-child)::after {
      content: '';
      position: absolute;
      left: 19px;
      top: 44px;
      bottom: 0;
      width: 2px;
      background: linear-gradient(to bottom, var(--primary), var(--secondary));
    }
    .step-num {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: var(--primary);
      color: #fff;
      font-size: 15px;
      font-weight: 700;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      z-index: 1;
    }
    .step-body {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius-xl);
      padding: 16px 20px;
      margin-bottom: 16px;
      flex: 1;
      box-shadow: var(--shadow-sm);
    }
    .step-body h5 { font-size: 14px; font-weight: 600; margin-bottom: 4px; }
    .step-body p  { font-size: 13px; color: var(--muted-foreground); line-height: 1.6; }
    .step-file {
      display: inline-block;
      font-size: 11px;
      font-family: monospace;
      background: var(--muted);
      border-radius: var(--radius-sm);
      padding: 2px 7px;
      color: var(--muted-foreground);
      margin-top: 6px;
    }

    /* ── DFD SVG diagrams ── */
    .dfd-wrap {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius-xl);
      padding: 24px;
      box-shadow: var(--shadow-sm);
      overflow-x: auto;
      margin-bottom: 32px;
    }
    .dfd-wrap svg { display: block; margin: 0 auto; }
    .dfd-title {
      font-size: 14px;
      font-weight: 600;
      color: var(--muted-foreground);
      text-align: center;
      margin-bottom: 16px;
      text-transform: uppercase;
      letter-spacing: .06em;
    }

    /* ── Role use-case cards ── */
    .role-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 20px;
      margin-bottom: 32px;
    }
    .role-card {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius-xl);
      padding: 20px;
      box-shadow: var(--shadow-sm);
    }
    .role-card-header {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 14px;
    }
    .role-badge-icon {
      width: 40px; height: 40px;
      border-radius: var(--radius-md);
      display: flex; align-items: center; justify-content: center;
      font-size: 20px;
      flex-shrink: 0;
    }
    .role-card-header h4 { font-size: 16px; font-weight: 600; }
    .role-card-header p  { font-size: 12px; color: var(--muted-foreground); }
    .uc-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 8px; }
    .uc-list li {
      font-size: 13px;
      color: var(--muted-foreground);
      padding-left: 20px;
      position: relative;
      line-height: 1.5;
    }
    .uc-list li::before {
      content: '→';
      position: absolute;
      left: 0;
      color: var(--primary);
      font-weight: 600;
    }

    /* ── API table ── */
    .api-table-wrap {
      overflow-x: auto;
      border-radius: var(--radius-xl);
      border: 1px solid var(--border);
      margin-bottom: 32px;
    }
    .api-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 13px;
    }
    .api-table th {
      background: var(--muted);
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .06em;
      color: var(--muted-foreground);
      padding: 12px 16px;
      text-align: left;
      border-bottom: 1px solid var(--border);
    }
    .api-table td {
      padding: 11px 16px;
      border-bottom: 1px solid var(--border);
      vertical-align: top;
      color: var(--foreground);
    }
    .api-table tr:last-child td { border-bottom: none; }
    .api-table tr:hover td { background: var(--secondary); }
    .method-badge {
      display: inline-block;
      font-size: 11px;
      font-weight: 700;
      padding: 2px 8px;
      border-radius: var(--radius-sm);
      font-family: monospace;
    }
    .m-get  { background: #dcfce7; color: #15803d; }
    .m-post { background: #dbeafe; color: #1d4ed8; }
    code {
      font-family: monospace;
      font-size: 12px;
      background: var(--muted);
      padding: 2px 6px;
      border-radius: var(--radius-sm);
      color: var(--foreground);
    }

    /* ── File index ── */
    .file-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 10px;
      margin-bottom: 32px;
    }
    .file-item {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      padding: 12px 14px;
      display: flex;
      align-items: flex-start;
      gap: 10px;
      box-shadow: var(--shadow-sm);
      transition: box-shadow .15s;
    }
    .file-item:hover { box-shadow: var(--shadow-md); }
    .file-item iconify-icon { color: var(--primary); margin-top: 2px; flex-shrink: 0; }
    .file-item-name  { font-size: 13px; font-weight: 600; font-family: monospace; color: var(--foreground); }
    .file-item-desc  { font-size: 12px; color: var(--muted-foreground); margin-top: 2px; }

    /* ── Live stats ── */
    .live-stats-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 20px;
      margin-bottom: 32px;
    }
    .live-stat-card {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius-xl);
      padding: 20px;
      box-shadow: var(--shadow-sm);
      text-align: center;
    }
    .live-stat-card .ls-icon {
      width: 44px; height: 44px;
      border-radius: var(--radius-md);
      display: flex; align-items: center; justify-content: center;
      font-size: 22px;
      margin: 0 auto 12px;
    }
    .live-stat-card .ls-value {
      font-size: 32px;
      font-weight: 800;
      color: var(--primary);
      line-height: 1;
      margin-bottom: 6px;
    }
    .live-stat-card .ls-label { font-size: 13px; color: var(--muted-foreground); }

    /* ── Divider ── */
    .arch-divider {
      border: none;
      border-top: 1px solid var(--border);
      margin: 48px 0;
    }

    /* ── Responsive ── */
    @media (max-width: 1024px) {
      .arch-layout { grid-template-columns: 1fr; }
      .toc { display: none; }
      .info-grid { grid-template-columns: repeat(2, 1fr); }
      .live-stats-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 768px) {
      .info-grid  { grid-template-columns: 1fr; }
      .role-grid  { grid-template-columns: 1fr; }
      .file-grid  { grid-template-columns: repeat(2, 1fr); }
      .live-stats-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 480px) {
      .file-grid { grid-template-columns: 1fr; }
      .live-stats-grid { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>

<?php
/* ══════════════════════════════════════════════════════════════
   Helper: read a JSON data file and return decoded array or []
══════════════════════════════════════════════════════════════ */
function readJsonFile(string $path): array {
  if (file_exists($path)) {
    $raw = file_get_contents($path);
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
  }
  return [];
}

/* ── Live counters ── */
$users        = readJsonFile(__DIR__ . '/data/registered_users.json');
$auditEvents  = readJsonFile(__DIR__ . '/data/audit_log.json');
$quotes       = readJsonFile(__DIR__ . '/data/quote_submissions.json');
$drivers      = readJsonFile(__DIR__ . '/data/driver_submissions.json');
$loadRequests = readJsonFile(__DIR__ . '/data/load_requests.json');
$insListings  = readJsonFile(__DIR__ . '/data/insurance_listings.json');
$truckListings= readJsonFile(__DIR__ . '/data/truck_listings.json');

$totalUsers       = count($users);
$totalAuditEvents = count($auditEvents);
$totalQuotes      = count($quotes);
$totalDrivers     = count($drivers);
$totalListings    = count($insListings) + count($truckListings);
$totalLoads       = count($loadRequests);

/* ── Dynamic file index ── */
$phpFiles = glob(__DIR__ . '/*.php');
sort($phpFiles);

/* ── File descriptions map ── */
$fileDescriptions = [
  'index.php'               => 'Landing page — hero, services, CTAs',
  'login.php'               => 'Email/password login form',
  'register.php'            => 'New user registration (all roles)',
  'forgot-password.php'     => 'Password recovery flow',
  'account.php'             => 'User account & KYC management',
  'quote.php'               => 'Freight quote request form',
  'quote-dashboard.php'     => 'Quote management dashboard',
  'track.php'               => 'Shipment tracking interface',
  'marketplace.php'         => 'Insurance & truck marketplace listings',
  'offers-tracking.php'     => 'Load board & driver offer matching',
  'driver-onboarding.php'   => 'Driver application & document upload',
  'driver-location.php'     => 'Real-time driver GPS location',
  'driver-dashboard.php'    => 'Dashboard for drivers & owner-operators',
  'shipper-dashboard.php'   => 'Dashboard for shippers & customers',
  'staff-dashboard.php'     => 'Corporate staff management dashboard',
  'admin-dashboard.php'     => 'Admin — user approval & role management',
  'insurance-dashboard.php' => 'Insurance company listing dashboard',
  'trucking-dashboard.php'  => 'Trucking company listing dashboard',
  'observability.php'       => 'System monitoring & audit log viewer',
  'process_form.php'        => 'API — central form processor (POST)',
  'admin_api.php'           => 'API — admin user/role management',
  'dashboard_data.php'      => 'API — driver submission data & exports',
  'shipper_data.php'        => 'API — shipper quote data',
  'marketplace_data.php'    => 'API — marketplace CRUD operations',
  'offers_tracking_data.php'=> 'API — load requests, matching, notifications',
  'audit.php'               => 'API — audit event log (GET/POST)',
  'audit_helper.php'        => 'Helper — centralised auditLog() function',
  'careers.php'             => 'Careers — open positions',
  'contact.php'             => 'Contact — enquiry form',
  'news.php'                => 'News & media articles',
  'privacy.php'             => 'Privacy policy',
  'terms.php'               => 'Terms of service',
  'air-freight.php'         => 'Service page — air freight',
  'ocean-freight.php'       => 'Service page — ocean freight',
  'ground-transport.php'    => 'Service page — ground transport',
  'warehousing.php'         => 'Service page — warehousing',
  'fastrux_logistics.php'   => 'Legacy logistics overview page',
  'insurance-login.php'     => 'Insurance company login shortcut',
  'trucking-login.php'      => 'Trucking company login shortcut',
  'architecture.php'        => 'This page — system architecture & data flow',
];

/* ── Categorise files ── */
$apiFiles = ['process_form.php','admin_api.php','dashboard_data.php','shipper_data.php',
             'marketplace_data.php','offers_tracking_data.php','audit.php','audit_helper.php'];
$dashFiles = ['shipper-dashboard.php','driver-dashboard.php','staff-dashboard.php',
              'admin-dashboard.php','insurance-dashboard.php','trucking-dashboard.php',
              'quote-dashboard.php','observability.php'];

function fileCategory(string $base, array $apiFiles, array $dashFiles): string {
  if (in_array($base, $apiFiles))   return 'api';
  if (in_array($base, $dashFiles))  return 'dash';
  return 'page';
}
?>

  <!-- ── HEADER ── -->
  <header class="header">
    <div class="container header-content">
      <a href="index" class="logo">
        <iconify-icon icon="lucide:truck" style="font-size:28px;color:var(--primary)"></iconify-icon>
        Fastrux
      </a>
      <nav class="nav-links">
        <a class="nav-link" href="index">Home</a>
        <a class="nav-link" href="index#services">Services</a>
        <a class="nav-link" href="track">Tracking</a>
        <a class="nav-link" href="marketplace">Marketplace</a>
        <a class="nav-link" href="contact">Contact</a>
      </nav>
      <div class="header-actions">
        <a class="nav-link" href="login">Login</a>
        <a class="btn btn-primary" href="quote">Get a Quote</a>
      </div>
      <button class="hamburger" id="hamburger" aria-label="Toggle menu">
        <span></span><span></span><span></span>
      </button>
    </div>
  </header>
  <nav class="mobile-menu" id="mobileMenu">
    <a class="nav-link" href="index">Home</a>
    <a class="nav-link" href="index#services">Services</a>
    <a class="nav-link" href="track">Tracking</a>
    <a class="nav-link" href="marketplace">Marketplace</a>
    <a class="nav-link" href="contact">Contact</a>
    <div class="header-actions" style="margin-top:8px;">
      <a class="btn btn-outline" href="login">Login</a>
      <a class="btn btn-primary" href="quote">Get a Quote</a>
    </div>
  </nav>

  <!-- ── HERO ── -->
  <div class="page-hero">
    <div class="container">
      <h1>System Architecture</h1>
      <p>End-to-end data flow, diagrams, use cases, and live component index for the Fastrux platform.</p>
    </div>
  </div>

  <!-- ── MAIN CONTENT ── -->
  <section class="section">
    <div class="container">
      <div class="arch-layout">

        <!-- ── TOC ── -->
        <aside class="toc" id="toc">
          <h4>On this page</h4>
          <div class="toc-links" id="tocLinks">
            <a class="toc-link" href="#overview">System Overview</a>
            <a class="toc-link" href="#live-stats">Live Stats</a>
            <a class="toc-link" href="#architecture-diagram">Architecture Diagram</a>
            <a class="toc-link" href="#data-flow">Data Flow</a>
            <a class="toc-link sub" href="#flow-registration">Registration</a>
            <a class="toc-link sub" href="#flow-quote">Quote Request</a>
            <a class="toc-link sub" href="#flow-driver">Driver Onboarding</a>
            <a class="toc-link sub" href="#flow-admin">Admin Approval</a>
            <a class="toc-link sub" href="#flow-marketplace">Marketplace</a>
            <a class="toc-link sub" href="#flow-offers">Load Matching</a>
            <a class="toc-link sub" href="#flow-audit">Audit Trail</a>
            <a class="toc-link" href="#use-cases">Use Cases by Role</a>
            <a class="toc-link" href="#api-reference">API Reference</a>
            <a class="toc-link" href="#file-index">File Index</a>
            <a class="toc-link" href="#data-storage">Data Storage</a>
          </div>
        </aside>

        <!-- ── CONTENT ── -->
        <div>

          <!-- ══ 1. OVERVIEW ══ -->
          <div class="arch-section" id="overview">
            <h2 class="arch-h2">
              <iconify-icon icon="lucide:layout-dashboard" style="font-size:20px;padding:8px"></iconify-icon>
              System Overview
            </h2>
            <p class="arch-lead">
              Fastrux is a flat-structure PHP web application serving a global logistics and freight management
              platform. It features multi-role authentication, service request workflows, real-time tracking,
              a marketplace, and comprehensive audit logging — all backed by a file-based JSON store.
            </p>

            <h3 class="arch-h3">Technology Stack</h3>
            <div class="tag-list">
              <span class="tag"><iconify-icon icon="lucide:code-2"></iconify-icon> PHP 8 (no framework)</span>
              <span class="tag"><iconify-icon icon="lucide:file-code"></iconify-icon> HTML5 / CSS3 / Vanilla JS</span>
              <span class="tag green"><iconify-icon icon="lucide:database"></iconify-icon> JSON flat-file store</span>
              <span class="tag orange"><iconify-icon icon="lucide:bell"></iconify-icon> Telegram Bot API</span>
              <span class="tag orange"><iconify-icon icon="lucide:message-circle"></iconify-icon> Twilio SMS</span>
              <span class="tag purple"><iconify-icon icon="lucide:palette"></iconify-icon> Custom CSS design system</span>
              <span class="tag purple"><iconify-icon icon="lucide:box"></iconify-icon> Iconify icons (Lucide set)</span>
              <span class="tag"><iconify-icon icon="lucide:type"></iconify-icon> Inter font (Google Fonts)</span>
            </div>

            <h3 class="arch-h3">Design Principles</h3>
            <div class="info-grid">
              <div class="info-card">
                <div class="ic-icon ic-blue"><iconify-icon icon="lucide:layers"></iconify-icon></div>
                <h4>Flat Structure</h4>
                <p>All PHP pages live at the root. No MVC framework — each file is self-contained with embedded styles and scripts.</p>
              </div>
              <div class="info-card">
                <div class="ic-icon ic-green"><iconify-icon icon="lucide:shield"></iconify-icon></div>
                <h4>RBAC Security</h4>
                <p>Seven user roles with server-side verification on every write operation. Audit log captures every state change.</p>
              </div>
              <div class="info-card">
                <div class="ic-icon ic-purple"><iconify-icon icon="lucide:smartphone"></iconify-icon></div>
                <h4>Client-Side Auth</h4>
                <p>Authenticated user stored in <code>localStorage['fx_user']</code>. <code>auth-nav.js</code> adapts navigation dynamically.</p>
              </div>
              <div class="info-card">
                <div class="ic-icon ic-orange"><iconify-icon icon="lucide:file-json"></iconify-icon></div>
                <h4>File-Based Storage</h4>
                <p>Data persisted as JSON files in <code>/data/</code> (git-ignored). No SQL database required — ideal for MVP scale.</p>
              </div>
              <div class="info-card">
                <div class="ic-icon ic-teal"><iconify-icon icon="lucide:globe"></iconify-icon></div>
                <h4>REST-Like APIs</h4>
                <p>10+ PHP data endpoints return JSON. Forms POST via <code>fetch()</code> to <code>process_form.php</code> or dedicated API files.</p>
              </div>
              <div class="info-card">
                <div class="ic-icon ic-red"><iconify-icon icon="lucide:activity"></iconify-icon></div>
                <h4>Observability</h4>
                <p>Full audit log with IP, user-agent, timestamps. Admin-only observability dashboard for real-time KPIs and event search.</p>
              </div>
            </div>
          </div>

          <hr class="arch-divider" />

          <!-- ══ 2. LIVE STATS ══ -->
          <div class="arch-section" id="live-stats">
            <h2 class="arch-h2">
              <iconify-icon icon="lucide:activity" style="font-size:20px;padding:8px"></iconify-icon>
              Live Stats
            </h2>
            <p class="arch-lead">
              Platform counters read directly from the live data store at page load — always up to date.
            </p>
            <div class="live-stats-grid">
              <div class="live-stat-card">
                <div class="ls-icon ic-blue"><iconify-icon icon="lucide:users"></iconify-icon></div>
                <div class="ls-value"><?= $totalUsers ?: '—' ?></div>
                <div class="ls-label">Registered Users</div>
              </div>
              <div class="live-stat-card">
                <div class="ls-icon ic-green"><iconify-icon icon="lucide:package"></iconify-icon></div>
                <div class="ls-value"><?= $totalQuotes ?: '—' ?></div>
                <div class="ls-label">Quote Requests</div>
              </div>
              <div class="live-stat-card">
                <div class="ls-icon ic-orange"><iconify-icon icon="lucide:truck"></iconify-icon></div>
                <div class="ls-value"><?= $totalDrivers ?: '—' ?></div>
                <div class="ls-label">Driver Applications</div>
              </div>
              <div class="live-stat-card">
                <div class="ls-icon ic-purple"><iconify-icon icon="lucide:store"></iconify-icon></div>
                <div class="ls-value"><?= $totalListings ?: '—' ?></div>
                <div class="ls-label">Marketplace Listings</div>
              </div>
              <div class="live-stat-card">
                <div class="ls-icon ic-teal"><iconify-icon icon="lucide:map-pin"></iconify-icon></div>
                <div class="ls-value"><?= $totalLoads ?: '—' ?></div>
                <div class="ls-label">Load Requests</div>
              </div>
              <div class="live-stat-card">
                <div class="ls-icon ic-red"><iconify-icon icon="lucide:clipboard-list"></iconify-icon></div>
                <div class="ls-value"><?= $totalAuditEvents ?: '—' ?></div>
                <div class="ls-label">Audit Events</div>
              </div>
              <div class="live-stat-card">
                <div class="ls-icon ic-blue"><iconify-icon icon="lucide:file-code"></iconify-icon></div>
                <div class="ls-value"><?= count($phpFiles) ?></div>
                <div class="ls-label">PHP Files</div>
              </div>
              <div class="live-stat-card">
                <div class="ls-icon ic-green"><iconify-icon icon="lucide:server"></iconify-icon></div>
                <div class="ls-value"><?= count($apiFiles) ?></div>
                <div class="ls-label">API Endpoints</div>
              </div>
            </div>
            <p style="font-size:12px;color:var(--muted-foreground);text-align:right;">
              <iconify-icon icon="lucide:refresh-cw" style="font-size:12px"></iconify-icon>
              Last updated: <?= date('Y-m-d H:i:s T') ?>
            </p>
          </div>

          <hr class="arch-divider" />

          <!-- ══ 3. ARCHITECTURE DIAGRAM ══ -->
          <div class="arch-section" id="architecture-diagram">
            <h2 class="arch-h2">
              <iconify-icon icon="lucide:git-branch" style="font-size:20px;padding:8px"></iconify-icon>
              Architecture Diagram
            </h2>
            <p class="arch-lead">
              High-level component map showing how the browser, PHP pages, API endpoints, data store, and
              external services interact.
            </p>
            <div class="dfd-wrap">
              <div class="dfd-title">High-Level Component Map</div>
              <svg viewBox="0 0 860 520" xmlns="http://www.w3.org/2000/svg" style="width:100%;max-width:860px;font-family:Inter,sans-serif;font-size:13px">
                <defs>
                  <marker id="arr" markerWidth="8" markerHeight="6" refX="8" refY="3" orient="auto">
                    <polygon points="0 0, 8 3, 0 6" fill="#0b6fff"/>
                  </marker>
                  <marker id="arr-gray" markerWidth="8" markerHeight="6" refX="8" refY="3" orient="auto">
                    <polygon points="0 0, 8 3, 0 6" fill="#6b7280"/>
                  </marker>
                </defs>

                <!-- Browser layer -->
                <rect x="10" y="10" width="840" height="90" rx="10" fill="#eaf3ff" stroke="#0b6fff" stroke-width="1.5"/>
                <text x="430" y="32" text-anchor="middle" font-weight="700" fill="#0b2545" font-size="14">Browser (Client)</text>
                <rect x="30"  y="44" width="120" height="44" rx="8" fill="#fff" stroke="#0b6fff" stroke-width="1"/>
                <text x="90"  y="71" text-anchor="middle" fill="#0b2545">PHP Pages</text>
                <rect x="175" y="44" width="120" height="44" rx="8" fill="#fff" stroke="#0b6fff" stroke-width="1"/>
                <text x="235" y="71" text-anchor="middle" fill="#0b2545">shared.css</text>
                <rect x="320" y="44" width="120" height="44" rx="8" fill="#fff" stroke="#0b6fff" stroke-width="1"/>
                <text x="380" y="71" text-anchor="middle" fill="#0b2545">auth-nav.js</text>
                <rect x="465" y="44" width="120" height="44" rx="8" fill="#fff" stroke="#0b6fff" stroke-width="1"/>
                <text x="525" y="71" text-anchor="middle" fill="#0b2545">localStorage</text>
                <rect x="610" y="44" width="130" height="44" rx="8" fill="#fff" stroke="#0b6fff" stroke-width="1"/>
                <text x="675" y="71" text-anchor="middle" fill="#0b2545">fetch() / XHR</text>

                <!-- Arrow down to PHP server -->
                <line x1="430" y1="100" x2="430" y2="145" stroke="#0b6fff" stroke-width="2" marker-end="url(#arr)"/>
                <text x="440" y="130" fill="#6b7280" font-size="11">HTTP POST/GET</text>

                <!-- PHP server layer -->
                <rect x="10" y="148" width="840" height="100" rx="10" fill="#f0fdf4" stroke="#16a34a" stroke-width="1.5"/>
                <text x="430" y="170" text-anchor="middle" font-weight="700" fill="#0b2545" font-size="14">PHP Server Layer</text>
                <rect x="30"  y="182" width="160" height="52" rx="8" fill="#fff" stroke="#16a34a" stroke-width="1"/>
                <text x="110" y="208" text-anchor="middle" fill="#0b2545" font-weight="600">process_form.php</text>
                <text x="110" y="223" text-anchor="middle" fill="#6b7280" font-size="11">Login · Register · Forms</text>
                <rect x="210" y="182" width="140" height="52" rx="8" fill="#fff" stroke="#16a34a" stroke-width="1"/>
                <text x="280" y="208" text-anchor="middle" fill="#0b2545" font-weight="600">admin_api.php</text>
                <text x="280" y="223" text-anchor="middle" fill="#6b7280" font-size="11">RBAC · User Mgmt</text>
                <rect x="370" y="182" width="150" height="52" rx="8" fill="#fff" stroke="#16a34a" stroke-width="1"/>
                <text x="445" y="208" text-anchor="middle" fill="#0b2545" font-weight="600">marketplace_data.php</text>
                <text x="445" y="223" text-anchor="middle" fill="#6b7280" font-size="11">Listings CRUD</text>
                <rect x="540" y="182" width="155" height="52" rx="8" fill="#fff" stroke="#16a34a" stroke-width="1"/>
                <text x="617" y="208" text-anchor="middle" fill="#0b2545" font-weight="600">offers_tracking_data.php</text>
                <text x="617" y="223" text-anchor="middle" fill="#6b7280" font-size="11">Load Matching · GPS</text>
                <rect x="715" y="182" width="120" height="52" rx="8" fill="#fff" stroke="#16a34a" stroke-width="1"/>
                <text x="775" y="208" text-anchor="middle" fill="#0b2545" font-weight="600">audit.php</text>
                <text x="775" y="223" text-anchor="middle" fill="#6b7280" font-size="11">Audit Events</text>

                <!-- Arrow down to data store -->
                <line x1="430" y1="248" x2="430" y2="293" stroke="#16a34a" stroke-width="2" marker-end="url(#arr)"/>
                <text x="440" y="278" fill="#6b7280" font-size="11">file_get / file_put</text>

                <!-- Data store layer -->
                <rect x="10" y="296" width="520" height="100" rx="10" fill="#fff7ed" stroke="#c2410c" stroke-width="1.5"/>
                <text x="270" y="318" text-anchor="middle" font-weight="700" fill="#0b2545" font-size="14">Data Store  /data/*.json</text>
                <rect x="30"  y="330" width="130" height="50" rx="8" fill="#fff" stroke="#c2410c" stroke-width="1"/>
                <text x="95"  y="352" text-anchor="middle" fill="#0b2545" font-size="12">registered_users</text>
                <text x="95"  y="367" text-anchor="middle" fill="#6b7280" font-size="11">· audit_log</text>
                <rect x="180" y="330" width="130" height="50" rx="8" fill="#fff" stroke="#c2410c" stroke-width="1"/>
                <text x="245" y="352" text-anchor="middle" fill="#0b2545" font-size="12">quote_submissions</text>
                <text x="245" y="367" text-anchor="middle" fill="#6b7280" font-size="11">· driver_submissions</text>
                <rect x="330" y="330" width="180" height="50" rx="8" fill="#fff" stroke="#c2410c" stroke-width="1"/>
                <text x="420" y="352" text-anchor="middle" fill="#0b2545" font-size="12">insurance_listings</text>
                <text x="420" y="367" text-anchor="middle" fill="#6b7280" font-size="11">· truck_listings · load_requests</text>

                <!-- External services layer -->
                <rect x="550" y="296" width="300" height="100" rx="10" fill="#f5f3ff" stroke="#6d28d9" stroke-width="1.5"/>
                <text x="700" y="318" text-anchor="middle" font-weight="700" fill="#0b2545" font-size="14">External Services</text>
                <rect x="570" y="330" width="120" height="50" rx="8" fill="#fff" stroke="#6d28d9" stroke-width="1"/>
                <text x="630" y="352" text-anchor="middle" fill="#0b2545" font-size="12">Telegram Bot</text>
                <text x="630" y="367" text-anchor="middle" fill="#6b7280" font-size="11">Driver notifications</text>
                <rect x="710" y="330" width="120" height="50" rx="8" fill="#fff" stroke="#6d28d9" stroke-width="1"/>
                <text x="770" y="352" text-anchor="middle" fill="#0b2545" font-size="12">Twilio SMS</text>
                <text x="770" y="367" text-anchor="middle" fill="#6b7280" font-size="11">Offer alerts</text>

                <!-- Arrow from PHP to external -->
                <line x1="715" y1="248" x2="715" y2="293" stroke="#6d28d9" stroke-width="2" marker-end="url(#arr)"/>
                <text x="720" y="278" fill="#6b7280" font-size="11">API calls</text>

                <!-- Role layer -->
                <rect x="10" y="416" width="840" height="90" rx="10" fill="#f8fafc" stroke="#6b7280" stroke-width="1.5" stroke-dasharray="6,3"/>
                <text x="430" y="438" text-anchor="middle" font-weight="700" fill="#0b2545" font-size="14">User Roles</text>
                <rect x="30"  y="450" width="100" height="40" rx="6" fill="#dbeafe" stroke="#1d4ed8" stroke-width="1"/>
                <text x="80"  y="475" text-anchor="middle" fill="#1d4ed8" font-size="12" font-weight="600">Shipper</text>
                <rect x="148" y="450" width="100" height="40" rx="6" fill="#dcfce7" stroke="#15803d" stroke-width="1"/>
                <text x="198" y="475" text-anchor="middle" fill="#15803d" font-size="12" font-weight="600">Driver</text>
                <rect x="266" y="450" width="120" height="40" rx="6" fill="#ccfbf1" stroke="#0f766e" stroke-width="1"/>
                <text x="326" y="475" text-anchor="middle" fill="#0f766e" font-size="12" font-weight="600">Owner/Operator</text>
                <rect x="404" y="450" width="110" height="40" rx="6" fill="#fef9c3" stroke="#a16207" stroke-width="1"/>
                <text x="459" y="475" text-anchor="middle" fill="#a16207" font-size="12" font-weight="600">Corp. Staff</text>
                <rect x="532" y="450" width="80" height="40" rx="6" fill="#fee2e2" stroke="#b91c1c" stroke-width="1"/>
                <text x="572" y="475" text-anchor="middle" fill="#b91c1c" font-size="12" font-weight="600">Admin</text>
                <rect x="630" y="450" width="110" height="40" rx="6" fill="#f5f3ff" stroke="#6d28d9" stroke-width="1"/>
                <text x="685" y="475" text-anchor="middle" fill="#6d28d9" font-size="12" font-weight="600">Insurance Co.</text>
                <rect x="758" y="450" width="90" height="40" rx="6" fill="#fff7ed" stroke="#c2410c" stroke-width="1"/>
                <text x="803" y="475" text-anchor="middle" fill="#c2410c" font-size="12" font-weight="600">Trucking Co.</text>
              </svg>
            </div>
          </div>

          <hr class="arch-divider" />

          <!-- ══ 4. DATA FLOW ══ -->
          <div class="arch-section" id="data-flow">
            <h2 class="arch-h2">
              <iconify-icon icon="lucide:workflow" style="font-size:20px;padding:8px"></iconify-icon>
              Data Flow
            </h2>
            <p class="arch-lead">
              Step-by-step walk-through of each major workflow, from the user action through the API to the
              data store and back.
            </p>

            <!-- Registration flow -->
            <div class="arch-section" id="flow-registration">
              <h3 class="arch-h3">1 · User Registration</h3>
              <div class="dfd-wrap">
                <div class="dfd-title">Registration Data Flow Diagram</div>
                <svg viewBox="0 0 700 160" xmlns="http://www.w3.org/2000/svg" style="width:100%;max-width:700px;font-family:Inter,sans-serif;font-size:12px">
                  <defs>
                    <marker id="a1" markerWidth="8" markerHeight="6" refX="8" refY="3" orient="auto"><polygon points="0 0,8 3,0 6" fill="#0b6fff"/></marker>
                  </defs>
                  <!-- Nodes -->
                  <rect x="10"  y="55" width="110" height="50" rx="8" fill="#eaf3ff" stroke="#0b6fff" stroke-width="1.5"/>
                  <text x="65"  y="77" text-anchor="middle" fill="#0b2545" font-weight="600">User</text>
                  <text x="65"  y="92" text-anchor="middle" fill="#6b7280" font-size="11">register.php</text>
                  <rect x="180" y="55" width="130" height="50" rx="8" fill="#f0fdf4" stroke="#16a34a" stroke-width="1.5"/>
                  <text x="245" y="77" text-anchor="middle" fill="#0b2545" font-weight="600">process_form.php</text>
                  <text x="245" y="92" text-anchor="middle" fill="#6b7280" font-size="11">handleRegister()</text>
                  <rect x="370" y="55" width="120" height="50" rx="8" fill="#fff7ed" stroke="#c2410c" stroke-width="1.5"/>
                  <text x="430" y="77" text-anchor="middle" fill="#0b2545" font-weight="600">registered_users</text>
                  <text x="430" y="92" text-anchor="middle" fill="#6b7280" font-size="11">.json</text>
                  <rect x="550" y="55" width="130" height="50" rx="8" fill="#f5f3ff" stroke="#6d28d9" stroke-width="1.5"/>
                  <text x="615" y="77" text-anchor="middle" fill="#0b2545" font-weight="600">audit_log.json</text>
                  <text x="615" y="92" text-anchor="middle" fill="#6b7280" font-size="11">auditLog()</text>
                  <!-- Arrows -->
                  <line x1="120" y1="80" x2="178" y2="80" stroke="#0b6fff" stroke-width="2" marker-end="url(#a1)"/>
                  <text x="147" y="74" text-anchor="middle" fill="#6b7280" font-size="10">POST</text>
                  <line x1="310" y1="80" x2="368" y2="80" stroke="#0b6fff" stroke-width="2" marker-end="url(#a1)"/>
                  <text x="337" y="74" text-anchor="middle" fill="#6b7280" font-size="10">append</text>
                  <line x1="490" y1="80" x2="548" y2="80" stroke="#0b6fff" stroke-width="2" marker-end="url(#a1)"/>
                  <text x="517" y="74" text-anchor="middle" fill="#6b7280" font-size="10">log event</text>
                  <!-- Return -->
                  <line x1="245" y1="105" x2="245" y2="140" stroke="#0b6fff" stroke-width="1.5" stroke-dasharray="4,3"/>
                  <line x1="245" y1="140" x2="65" y2="140" stroke="#0b6fff" stroke-width="1.5" stroke-dasharray="4,3"/>
                  <line x1="65" y1="140" x2="65" y2="108" stroke="#0b6fff" stroke-width="1.5" stroke-dasharray="4,3" marker-end="url(#a1)"/>
                  <text x="155" y="154" text-anchor="middle" fill="#6b7280" font-size="10">JSON {success, user} → localStorage</text>
                </svg>
              </div>
              <div class="flow-steps">
                <div class="flow-step">
                  <div class="step-num">1</div>
                  <div class="step-body">
                    <h5>User fills registration form</h5>
                    <p>The user selects a role (shipper, driver, owner_operator, corporate_staff, insurance_company, trucking_company, or admin) and submits name, email, and password.</p>
                    <span class="step-file">register.php</span>
                  </div>
                </div>
                <div class="flow-step">
                  <div class="step-num">2</div>
                  <div class="step-body">
                    <h5>Form POSTed via fetch()</h5>
                    <p>JavaScript serialises the form as FormData and sends an async POST to <code>process_form.php</code> with <code>form_type=register</code>.</p>
                    <span class="step-file">register.php → process_form.php</span>
                  </div>
                </div>
                <div class="flow-step">
                  <div class="step-num">3</div>
                  <div class="step-body">
                    <h5>Server validates & creates user</h5>
                    <p><code>handleRegister()</code> checks for duplicate email, hashes the password, assigns a <code>USR-XXXX</code> ID, sets initial status (<em>pending</em> for corporate_staff, <em>active</em> for all others), and appends the record to <code>registered_users.json</code>.</p>
                    <span class="step-file">process_form.php → data/registered_users.json</span>
                  </div>
                </div>
                <div class="flow-step">
                  <div class="step-num">4</div>
                  <div class="step-body">
                    <h5>Audit event logged</h5>
                    <p><code>auditLog('user_registered', …)</code> appends a timestamped record — including IP address and user-agent — to <code>audit_log.json</code>.</p>
                    <span class="step-file">audit_helper.php → data/audit_log.json</span>
                  </div>
                </div>
                <div class="flow-step">
                  <div class="step-num">5</div>
                  <div class="step-body">
                    <h5>Response returned to browser</h5>
                    <p>Server returns <code>{"success": true, "user": {...}}</code>. JavaScript saves the user object to <code>localStorage['fx_user']</code> and redirects to the appropriate role dashboard.</p>
                    <span class="step-file">process_form.php → register.php (JS) → dashboard</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Quote flow -->
            <div class="arch-section" id="flow-quote">
              <h3 class="arch-h3">2 · Quote Request</h3>
              <div class="dfd-wrap">
                <div class="dfd-title">Quote Request Data Flow Diagram</div>
                <svg viewBox="0 0 700 160" xmlns="http://www.w3.org/2000/svg" style="width:100%;max-width:700px;font-family:Inter,sans-serif;font-size:12px">
                  <defs>
                    <marker id="a2" markerWidth="8" markerHeight="6" refX="8" refY="3" orient="auto"><polygon points="0 0,8 3,0 6" fill="#0b6fff"/></marker>
                  </defs>
                  <rect x="10"  y="55" width="100" height="50" rx="8" fill="#eaf3ff" stroke="#0b6fff" stroke-width="1.5"/>
                  <text x="60"  y="77" text-anchor="middle" fill="#0b2545" font-weight="600">Shipper</text>
                  <text x="60"  y="92" text-anchor="middle" fill="#6b7280" font-size="11">quote.php</text>
                  <rect x="168" y="55" width="130" height="50" rx="8" fill="#f0fdf4" stroke="#16a34a" stroke-width="1.5"/>
                  <text x="233" y="77" text-anchor="middle" fill="#0b2545" font-weight="600">process_form.php</text>
                  <text x="233" y="92" text-anchor="middle" fill="#6b7280" font-size="11">handleQuote()</text>
                  <rect x="356" y="55" width="130" height="50" rx="8" fill="#fff7ed" stroke="#c2410c" stroke-width="1.5"/>
                  <text x="421" y="77" text-anchor="middle" fill="#0b2545" font-weight="600">quote_submissions</text>
                  <text x="421" y="92" text-anchor="middle" fill="#6b7280" font-size="11">.json / .csv</text>
                  <rect x="546" y="55" width="130" height="50" rx="8" fill="#f5f3ff" stroke="#6d28d9" stroke-width="1.5"/>
                  <text x="611" y="77" text-anchor="middle" fill="#0b2545" font-weight="600">Staff Dashboard</text>
                  <text x="611" y="92" text-anchor="middle" fill="#6b7280" font-size="11">staff-dashboard.php</text>
                  <line x1="110" y1="80" x2="166" y2="80" stroke="#0b6fff" stroke-width="2" marker-end="url(#a2)"/>
                  <text x="136" y="74" text-anchor="middle" fill="#6b7280" font-size="10">POST</text>
                  <line x1="298" y1="80" x2="354" y2="80" stroke="#0b6fff" stroke-width="2" marker-end="url(#a2)"/>
                  <text x="324" y="74" text-anchor="middle" fill="#6b7280" font-size="10">save</text>
                  <line x1="486" y1="80" x2="544" y2="80" stroke="#0b6fff" stroke-width="2" marker-end="url(#a2)"/>
                  <text x="513" y="74" text-anchor="middle" fill="#6b7280" font-size="10">reads</text>
                  <line x1="233" y1="105" x2="233" y2="140" stroke="#0b6fff" stroke-width="1.5" stroke-dasharray="4,3"/>
                  <line x1="233" y1="140" x2="60"  y2="140" stroke="#0b6fff" stroke-width="1.5" stroke-dasharray="4,3"/>
                  <line x1="60"  y1="140" x2="60"  y2="108" stroke="#0b6fff" stroke-width="1.5" stroke-dasharray="4,3" marker-end="url(#a2)"/>
                  <text x="144" y="154" text-anchor="middle" fill="#6b7280" font-size="10">QUO-XXXX reference number</text>
                </svg>
              </div>
              <div class="flow-steps">
                <div class="flow-step">
                  <div class="step-num">1</div>
                  <div class="step-body">
                    <h5>Shipper submits quote form</h5>
                    <p>User provides service type, origin/destination, weight, volume, and contact details. Optionally linked to a logged-in user via <code>user_id</code>.</p>
                    <span class="step-file">quote.php</span>
                  </div>
                </div>
                <div class="flow-step">
                  <div class="step-num">2</div>
                  <div class="step-body">
                    <h5>Server saves quote</h5>
                    <p><code>handleQuote()</code> generates a <code>QUO-XXXX</code> reference, appends to <code>quote_submissions.json</code>, and regenerates the CSV export.</p>
                    <span class="step-file">process_form.php → data/quote_submissions.json</span>
                  </div>
                </div>
                <div class="flow-step">
                  <div class="step-num">3</div>
                  <div class="step-body">
                    <h5>Staff & shippers view quotes</h5>
                    <p>Corporate staff see all quotes via <code>staff-dashboard.php</code>; shippers see their own via <code>shipper-dashboard.php</code> which calls <code>shipper_data.php?user_id=…</code>.</p>
                    <span class="step-file">shipper_data.php · staff-dashboard.php</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Driver onboarding flow -->
            <div class="arch-section" id="flow-driver">
              <h3 class="arch-h3">3 · Driver Onboarding</h3>
              <div class="dfd-wrap">
                <div class="dfd-title">Driver Onboarding Data Flow Diagram</div>
                <svg viewBox="0 0 780 200" xmlns="http://www.w3.org/2000/svg" style="width:100%;max-width:780px;font-family:Inter,sans-serif;font-size:12px">
                  <defs>
                    <marker id="a3" markerWidth="8" markerHeight="6" refX="8" refY="3" orient="auto"><polygon points="0 0,8 3,0 6" fill="#0b6fff"/></marker>
                  </defs>
                  <rect x="10"  y="65" width="100" height="50" rx="8" fill="#eaf3ff" stroke="#0b6fff" stroke-width="1.5"/>
                  <text x="60"  y="87" text-anchor="middle" fill="#0b2545" font-weight="600">Driver</text>
                  <text x="60"  y="102" text-anchor="middle" fill="#6b7280" font-size="11">onboarding form</text>
                  <rect x="166" y="65" width="130" height="50" rx="8" fill="#f0fdf4" stroke="#16a34a" stroke-width="1.5"/>
                  <text x="231" y="87" text-anchor="middle" fill="#0b2545" font-weight="600">process_form.php</text>
                  <text x="231" y="102" text-anchor="middle" fill="#6b7280" font-size="11">handleDriverOnboard()</text>
                  <rect x="352" y="10" width="130" height="50" rx="8" fill="#fff7ed" stroke="#c2410c" stroke-width="1.5"/>
                  <text x="417" y="32" text-anchor="middle" fill="#0b2545" font-weight="600">driver_submissions</text>
                  <text x="417" y="47" text-anchor="middle" fill="#6b7280" font-size="11">.json (metadata)</text>
                  <rect x="352" y="120" width="130" height="50" rx="8" fill="#fff7ed" stroke="#c2410c" stroke-width="1.5"/>
                  <text x="417" y="142" text-anchor="middle" fill="#0b2545" font-weight="600">data/drivers/</text>
                  <text x="417" y="157" text-anchor="middle" fill="#6b7280" font-size="11">{id}/ (file uploads)</text>
                  <rect x="540" y="65" width="130" height="50" rx="8" fill="#f5f3ff" stroke="#6d28d9" stroke-width="1.5"/>
                  <text x="605" y="87" text-anchor="middle" fill="#0b2545" font-weight="600">dashboard_data.php</text>
                  <text x="605" y="102" text-anchor="middle" fill="#6b7280" font-size="11">Staff review & status</text>
                  <rect x="690" y="65" width="80" height="50" rx="8" fill="#fef9c3" stroke="#a16207" stroke-width="1.5"/>
                  <text x="730" y="87" text-anchor="middle" fill="#0b2545" font-weight="600">Staff</text>
                  <text x="730" y="102" text-anchor="middle" fill="#6b7280" font-size="11">approves</text>
                  <line x1="110" y1="90" x2="164" y2="90" stroke="#0b6fff" stroke-width="2" marker-end="url(#a3)"/>
                  <line x1="296" y1="80" x2="350" y2="42" stroke="#0b6fff" stroke-width="2" marker-end="url(#a3)"/>
                  <line x1="296" y1="100" x2="350" y2="138" stroke="#0b6fff" stroke-width="2" marker-end="url(#a3)"/>
                  <line x1="482" y1="90" x2="538" y2="90" stroke="#0b6fff" stroke-width="2" marker-end="url(#a3)"/>
                  <line x1="670" y1="90" x2="688" y2="90" stroke="#0b6fff" stroke-width="2" marker-end="url(#a3)"/>
                  <text x="136" y="84" text-anchor="middle" fill="#6b7280" font-size="10">multipart/POST</text>
                  <text x="418" y="88" text-anchor="middle" fill="#6b7280" font-size="10">reads</text>
                </svg>
              </div>
              <div class="flow-steps">
                <div class="flow-step">
                  <div class="step-num">1</div>
                  <div class="step-body">
                    <h5>Driver fills multi-step form</h5>
                    <p>Driver provides personal details, vehicle information, license/insurance numbers, and uploads photos and documents (licence, insurance certificate, vehicle photos).</p>
                    <span class="step-file">driver-onboarding.php</span>
                  </div>
                </div>
                <div class="flow-step">
                  <div class="step-num">2</div>
                  <div class="step-body">
                    <h5>Files uploaded & metadata saved</h5>
                    <p>Binary files are written to <code>data/drivers/{DRV-id}/</code>. JSON metadata (with base64 previews) is appended to <code>driver_submissions.json</code> with status <em>pending</em>.</p>
                    <span class="step-file">process_form.php → data/driver_submissions.json + data/drivers/</span>
                  </div>
                </div>
                <div class="flow-step">
                  <div class="step-num">3</div>
                  <div class="step-body">
                    <h5>Staff reviews & approves</h5>
                    <p>Corporate staff and admins view all driver applications via <code>dashboard_data.php</code>, then POST <code>action=update_status</code> to approve or reject. Status transitions: pending → approved / rejected.</p>
                    <span class="step-file">dashboard_data.php → data/driver_submissions.json</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Admin approval flow -->
            <div class="arch-section" id="flow-admin">
              <h3 class="arch-h3">4 · Admin Approval (Corporate Staff)</h3>
              <div class="flow-steps">
                <div class="flow-step">
                  <div class="step-num">1</div>
                  <div class="step-body">
                    <h5>Staff registers with pending status</h5>
                    <p>Users who register with role <em>corporate_staff</em> receive status <em>pending</em> immediately. They cannot access the staff dashboard until approved.</p>
                    <span class="step-file">process_form.php</span>
                  </div>
                </div>
                <div class="flow-step">
                  <div class="step-num">2</div>
                  <div class="step-body">
                    <h5>Admin sees pending list</h5>
                    <p>Admin dashboard calls <code>GET admin_api.php?action=pending_staff&amp;requesting_user_id=…</code> to retrieve all pending corporate staff accounts.</p>
                    <span class="step-file">admin_api.php → data/registered_users.json</span>
                  </div>
                </div>
                <div class="flow-step">
                  <div class="step-num">3</div>
                  <div class="step-body">
                    <h5>Admin approves or rejects</h5>
                    <p>Clicking Approve/Reject POSTs <code>action=approve_staff</code> or <code>action=reject_staff</code> to <code>admin_api.php</code>. Server verifies the requesting user is admin/super_admin, then updates the target user's status in <code>registered_users.json</code>.</p>
                    <span class="step-file">admin_api.php → data/registered_users.json</span>
                  </div>
                </div>
                <div class="flow-step">
                  <div class="step-num">4</div>
                  <div class="step-body">
                    <h5>Role & user management (super_admin only)</h5>
                    <p>Super admins can additionally change any user's role (<code>action=change_role</code>) and create new admin accounts (<code>action=create_admin</code>). All changes are audit-logged.</p>
                    <span class="step-file">admin_api.php → data/registered_users.json + audit_log.json</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Marketplace flow -->
            <div class="arch-section" id="flow-marketplace">
              <h3 class="arch-h3">5 · Marketplace</h3>
              <div class="flow-steps">
                <div class="flow-step">
                  <div class="step-num">1</div>
                  <div class="step-body">
                    <h5>Company logs in and opens dashboard</h5>
                    <p>Insurance companies and trucking companies authenticate via the standard login flow and are routed to their respective dashboards (<code>insurance-dashboard.php</code> or <code>trucking-dashboard.php</code>).</p>
                    <span class="step-file">insurance-dashboard.php · trucking-dashboard.php</span>
                  </div>
                </div>
                <div class="flow-step">
                  <div class="step-num">2</div>
                  <div class="step-body">
                    <h5>Company creates a listing</h5>
                    <p>Dashboard POSTs <code>action=create_insurance_listing</code> or <code>action=create_truck_listing</code> to <code>marketplace_data.php</code>. Server verifies role, generates listing ID, appends to the relevant JSON file.</p>
                    <span class="step-file">marketplace_data.php → data/insurance_listings.json / truck_listings.json</span>
                  </div>
                </div>
                <div class="flow-step">
                  <div class="step-num">3</div>
                  <div class="step-body">
                    <h5>Public marketplace displays listings</h5>
                    <p><code>marketplace.php</code> fetches <code>GET marketplace_data.php?action=list_insurance</code> and <code>?action=list_trucks</code> to render all active listings to any visitor.</p>
                    <span class="step-file">marketplace.php → marketplace_data.php → JSON files</span>
                  </div>
                </div>
                <div class="flow-step">
                  <div class="step-num">4</div>
                  <div class="step-body">
                    <h5>Company updates or deletes listing</h5>
                    <p>Dashboard POSTs <code>update_insurance_listing</code> / <code>update_truck_listing</code> or <code>delete_listing</code>. Server checks that the requesting user owns the listing before modifying it.</p>
                    <span class="step-file">marketplace_data.php → JSON files</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Load matching flow -->
            <div class="arch-section" id="flow-offers">
              <h3 class="arch-h3">6 · Load Matching &amp; Notifications</h3>
              <div class="dfd-wrap">
                <div class="dfd-title">Load Matching Data Flow Diagram</div>
                <svg viewBox="0 0 840 180" xmlns="http://www.w3.org/2000/svg" style="width:100%;max-width:840px;font-family:Inter,sans-serif;font-size:12px">
                  <defs>
                    <marker id="a4" markerWidth="8" markerHeight="6" refX="8" refY="3" orient="auto"><polygon points="0 0,8 3,0 6" fill="#0b6fff"/></marker>
                  </defs>
                  <rect x="10"  y="60" width="100" height="50" rx="8" fill="#eaf3ff" stroke="#0b6fff" stroke-width="1.5"/>
                  <text x="60"  y="82" text-anchor="middle" fill="#0b2545" font-weight="600">Dispatcher</text>
                  <text x="60"  y="97" text-anchor="middle" fill="#6b7280" font-size="11">offers-tracking</text>
                  <rect x="166" y="60" width="140" height="50" rx="8" fill="#f0fdf4" stroke="#16a34a" stroke-width="1.5"/>
                  <text x="236" y="82" text-anchor="middle" fill="#0b2545" font-weight="600">offers_tracking</text>
                  <text x="236" y="97" text-anchor="middle" fill="#6b7280" font-size="11">_data.php</text>
                  <rect x="362" y="60" width="120" height="50" rx="8" fill="#fff7ed" stroke="#c2410c" stroke-width="1.5"/>
                  <text x="422" y="82" text-anchor="middle" fill="#0b2545" font-weight="600">load_requests</text>
                  <text x="422" y="97" text-anchor="middle" fill="#6b7280" font-size="11">.json</text>
                  <rect x="540" y="10"  width="120" height="50" rx="8" fill="#ccfbf1" stroke="#0f766e" stroke-width="1.5"/>
                  <text x="600" y="32" text-anchor="middle" fill="#0b2545" font-weight="600">driver_locations</text>
                  <text x="600" y="47" text-anchor="middle" fill="#6b7280" font-size="11">.json (GPS)</text>
                  <rect x="540" y="115" width="120" height="50" rx="8" fill="#f5f3ff" stroke="#6d28d9" stroke-width="1.5"/>
                  <text x="600" y="137" text-anchor="middle" fill="#0b2545" font-weight="600">Telegram / SMS</text>
                  <text x="600" y="152" text-anchor="middle" fill="#6b7280" font-size="11">notification sent</text>
                  <rect x="720" y="60" width="100" height="50" rx="8" fill="#dcfce7" stroke="#15803d" stroke-width="1.5"/>
                  <text x="770" y="82" text-anchor="middle" fill="#0b2545" font-weight="600">Driver</text>
                  <text x="770" y="97" text-anchor="middle" fill="#6b7280" font-size="11">receives offer</text>
                  <line x1="110" y1="85" x2="164" y2="85" stroke="#0b6fff" stroke-width="2" marker-end="url(#a4)"/>
                  <line x1="306" y1="85" x2="360" y2="85" stroke="#0b6fff" stroke-width="2" marker-end="url(#a4)"/>
                  <line x1="482" y1="75" x2="538" y2="42" stroke="#0b6fff" stroke-width="2" marker-end="url(#a4)"/>
                  <line x1="482" y1="95" x2="538" y2="132" stroke="#0b6fff" stroke-width="2" marker-end="url(#a4)"/>
                  <line x1="660" y1="140" x2="770" y2="110" stroke="#6d28d9" stroke-width="2" marker-end="url(#a4)"/>
                  <text x="136" y="79" text-anchor="middle" fill="#6b7280" font-size="10">POST load</text>
                  <text x="332" y="79" text-anchor="middle" fill="#6b7280" font-size="10">save</text>
                  <text x="516" y="57" text-anchor="middle" fill="#6b7280" font-size="10">match</text>
                  <text x="516" y="112" text-anchor="middle" fill="#6b7280" font-size="10">notify</text>
                </svg>
              </div>
              <div class="flow-steps">
                <div class="flow-step">
                  <div class="step-num">1</div>
                  <div class="step-body">
                    <h5>Driver updates GPS location</h5>
                    <p>Driver's browser (or mobile) POSTs <code>action=update_location</code> with lat/lon. Server upserts the driver's entry in <code>driver_locations.json</code>.</p>
                    <span class="step-file">offers_tracking_data.php → data/driver_locations.json</span>
                  </div>
                </div>
                <div class="flow-step">
                  <div class="step-num">2</div>
                  <div class="step-body">
                    <h5>Dispatcher creates load request</h5>
                    <p>Dispatcher fills the load board form. POST <code>action=create_load_request</code> stores the load in <code>load_requests.json</code> with status <em>open</em>.</p>
                    <span class="step-file">offers-tracking.php → offers_tracking_data.php → data/load_requests.json</span>
                  </div>
                </div>
                <div class="flow-step">
                  <div class="step-num">3</div>
                  <div class="step-body">
                    <h5>Match nearby drivers</h5>
                    <p><code>action=match_offers</code> returns a ranked list of drivers close to the pickup location by comparing against <code>driver_locations.json</code>.</p>
                    <span class="step-file">offers_tracking_data.php</span>
                  </div>
                </div>
                <div class="flow-step">
                  <div class="step-num">4</div>
                  <div class="step-body">
                    <h5>Offer sent via Telegram &amp; SMS</h5>
                    <p><code>action=send_offer</code> reads <code>telegram_config.json</code> and <code>sms_config.json</code>, then dispatches messages through the Telegram Bot API and Twilio REST API respectively.</p>
                    <span class="step-file">offers_tracking_data.php → Telegram API / Twilio API</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Audit trail flow -->
            <div class="arch-section" id="flow-audit">
              <h3 class="arch-h3">7 · Audit Trail</h3>
              <div class="flow-steps">
                <div class="flow-step">
                  <div class="step-num">1</div>
                  <div class="step-body">
                    <h5>Any write operation calls auditLog()</h5>
                    <p>Every API endpoint that modifies data calls <code>auditLog($action, $userId, $entityType, $entityId, $details)</code>. The helper captures IP address, user-agent, and a UTC timestamp.</p>
                    <span class="step-file">audit_helper.php</span>
                  </div>
                </div>
                <div class="flow-step">
                  <div class="step-num">2</div>
                  <div class="step-body">
                    <h5>Event appended to audit_log.json</h5>
                    <p>The helper reads the current log array, appends the new event, and atomically writes the full array back to <code>data/audit_log.json</code>.</p>
                    <span class="step-file">audit_helper.php → data/audit_log.json</span>
                  </div>
                </div>
                <div class="flow-step">
                  <div class="step-num">3</div>
                  <div class="step-body">
                    <h5>Admin queries events via audit.php</h5>
                    <p><code>GET audit.php?action=list</code> supports filtering by user, entity type, action, and date range plus full-text search. <code>?action=stats</code> returns KPI counts for the observability dashboard.</p>
                    <span class="step-file">audit.php → observability.php</span>
                  </div>
                </div>
              </div>
            </div>

          </div><!-- /data-flow -->

          <hr class="arch-divider" />

          <!-- ══ 5. USE CASES ══ -->
          <div class="arch-section" id="use-cases">
            <h2 class="arch-h2">
              <iconify-icon icon="lucide:users" style="font-size:20px;padding:8px"></iconify-icon>
              Use Cases by Role
            </h2>
            <p class="arch-lead">
              Each role has a dedicated authentication path and dashboard. Use cases describe the primary
              capabilities available once logged in.
            </p>
            <div class="role-grid">

              <div class="role-card">
                <div class="role-card-header">
                  <div class="role-badge-icon ic-blue"><iconify-icon icon="lucide:package"></iconify-icon></div>
                  <div>
                    <h4>Shipper / Customer</h4>
                    <p>Dashboard: shipper-dashboard.php</p>
                  </div>
                </div>
                <ul class="uc-list">
                  <li>Register and immediately gain active access</li>
                  <li>Submit freight quote requests with origin, destination, weight &amp; volume</li>
                  <li>View own quote history and reference numbers</li>
                  <li>Track shipments via the tracking interface</li>
                  <li>Update KYC information (account.php)</li>
                  <li>Browse marketplace listings for insurance and trucks</li>
                </ul>
              </div>

              <div class="role-card">
                <div class="role-card-header">
                  <div class="role-badge-icon ic-green"><iconify-icon icon="lucide:truck"></iconify-icon></div>
                  <div>
                    <h4>Driver / Owner-Operator</h4>
                    <p>Dashboard: driver-dashboard.php</p>
                  </div>
                </div>
                <ul class="uc-list">
                  <li>Complete a multi-step onboarding application with document uploads</li>
                  <li>Track application status (pending → approved / rejected)</li>
                  <li>Update GPS location in real time</li>
                  <li>Receive load offers via Telegram and SMS</li>
                  <li>View assigned loads on the offers-tracking board</li>
                  <li>Update KYC information and vehicle details</li>
                </ul>
              </div>

              <div class="role-card">
                <div class="role-card-header">
                  <div class="role-badge-icon ic-teal" style="background:#ccfbf1;color:#0f766e"><iconify-icon icon="lucide:briefcase"></iconify-icon></div>
                  <div>
                    <h4>Corporate Staff</h4>
                    <p>Dashboard: staff-dashboard.php (requires admin approval)</p>
                  </div>
                </div>
                <ul class="uc-list">
                  <li>Register with status <em>pending</em> until an admin approves</li>
                  <li>View and manage all driver applications</li>
                  <li>Approve or reject driver submissions</li>
                  <li>Export driver records as CSV</li>
                  <li>View all quote submissions from shippers</li>
                  <li>Manage load board and dispatch offers</li>
                </ul>
              </div>

              <div class="role-card">
                <div class="role-card-header">
                  <div class="role-badge-icon ic-red" style="background:#fee2e2;color:#b91c1c"><iconify-icon icon="lucide:shield-check"></iconify-icon></div>
                  <div>
                    <h4>Admin</h4>
                    <p>Dashboard: admin-dashboard.php</p>
                  </div>
                </div>
                <ul class="uc-list">
                  <li>View all registered users across all roles</li>
                  <li>Approve or reject pending corporate staff accounts</li>
                  <li>View all system activity via observability dashboard</li>
                  <li>Search, filter, and export audit log events</li>
                  <li>View KPI metrics: registrations, quotes, driver apps</li>
                </ul>
              </div>

              <div class="role-card">
                <div class="role-card-header">
                  <div class="role-badge-icon" style="background:#1d1d1d;color:#fff"><iconify-icon icon="lucide:star"></iconify-icon></div>
                  <div>
                    <h4>Super Admin</h4>
                    <p>Dashboard: admin-dashboard.php (elevated permissions)</p>
                  </div>
                </div>
                <ul class="uc-list">
                  <li>All Admin capabilities</li>
                  <li>Change any user's role to any valid role</li>
                  <li>Create new admin accounts directly</li>
                  <li>Full audit log access with no restrictions</li>
                  <li>System-wide configuration management</li>
                </ul>
              </div>

              <div class="role-card">
                <div class="role-card-header">
                  <div class="role-badge-icon ic-purple"><iconify-icon icon="lucide:shield"></iconify-icon></div>
                  <div>
                    <h4>Insurance Company</h4>
                    <p>Dashboard: insurance-dashboard.php</p>
                  </div>
                </div>
                <ul class="uc-list">
                  <li>Register and immediately gain active access</li>
                  <li>Create spot insurance listings with pricing and coverage details</li>
                  <li>Update existing listings (coverage, expiry, price)</li>
                  <li>Delete expired or withdrawn listings</li>
                  <li>View all own listings with status indicators</li>
                  <li>Monitor enquiries from shippers via the marketplace</li>
                </ul>
              </div>

              <div class="role-card">
                <div class="role-card-header">
                  <div class="role-badge-icon ic-orange"><iconify-icon icon="lucide:truck"></iconify-icon></div>
                  <div>
                    <h4>Trucking Company</h4>
                    <p>Dashboard: trucking-dashboard.php</p>
                  </div>
                </div>
                <ul class="uc-list">
                  <li>Register and immediately gain active access</li>
                  <li>Post trucks available for lease or sale</li>
                  <li>Update truck listings (availability, price, specs)</li>
                  <li>Delete sold or unavailable trucks</li>
                  <li>View all own listings with occupancy status</li>
                  <li>Connect with potential lessees via the marketplace</li>
                </ul>
              </div>

            </div>
          </div>

          <hr class="arch-divider" />

          <!-- ══ 6. API REFERENCE ══ -->
          <div class="arch-section" id="api-reference">
            <h2 class="arch-h2">
              <iconify-icon icon="lucide:plug" style="font-size:20px;padding:8px"></iconify-icon>
              API Reference
            </h2>
            <p class="arch-lead">
              All API endpoints are plain PHP files that return JSON. Authentication is enforced by passing
              <code>requesting_user_id</code> (for write operations); the server cross-references
              <code>registered_users.json</code> to verify role and status.
            </p>

            <h3 class="arch-h3">process_form.php — Form Processor</h3>
            <div class="api-table-wrap">
              <table class="api-table">
                <thead><tr><th>Method</th><th>form_type</th><th>Handler</th><th>Description</th></tr></thead>
                <tbody>
                  <tr><td><span class="method-badge m-post">POST</span></td><td><code>contact</code></td><td><code>handleContact()</code></td><td>Submit a contact enquiry; returns <code>CNT-XXXX</code> reference</td></tr>
                  <tr><td><span class="method-badge m-post">POST</span></td><td><code>quote</code></td><td><code>handleQuote()</code></td><td>Submit a freight quote request; returns <code>QUO-XXXX</code></td></tr>
                  <tr><td><span class="method-badge m-post">POST</span></td><td><code>newsletter</code></td><td><code>handleNewsletter()</code></td><td>Add email to newsletter subscribers list</td></tr>
                  <tr><td><span class="method-badge m-post">POST</span></td><td><code>driver_onboard</code></td><td><code>handleDriverOnboard()</code></td><td>Submit driver application with file uploads; returns <code>DRV-XXXX</code></td></tr>
                  <tr><td><span class="method-badge m-post">POST</span></td><td><code>login</code></td><td><code>handleLogin()</code></td><td>Verify credentials; return user object for localStorage</td></tr>
                  <tr><td><span class="method-badge m-post">POST</span></td><td><code>register</code></td><td><code>handleRegister()</code></td><td>Create new user account; return user object</td></tr>
                  <tr><td><span class="method-badge m-post">POST</span></td><td><code>kyc_update</code></td><td><code>handleKycUpdate()</code></td><td>Update KYC documents for existing user</td></tr>
                </tbody>
              </table>
            </div>

            <h3 class="arch-h3">admin_api.php — Admin Operations</h3>
            <div class="api-table-wrap">
              <table class="api-table">
                <thead><tr><th>Method</th><th>action</th><th>Auth Required</th><th>Description</th></tr></thead>
                <tbody>
                  <tr><td><span class="method-badge m-get">GET</span></td><td><code>pending_staff</code></td><td>admin / super_admin</td><td>List all users with <em>pending</em> status and role <em>corporate_staff</em></td></tr>
                  <tr><td><span class="method-badge m-get">GET</span></td><td><code>users</code></td><td>admin / super_admin</td><td>List all registered users across all roles</td></tr>
                  <tr><td><span class="method-badge m-post">POST</span></td><td><code>approve_staff</code></td><td>admin / super_admin</td><td>Set corporate_staff user status to <em>active</em></td></tr>
                  <tr><td><span class="method-badge m-post">POST</span></td><td><code>reject_staff</code></td><td>admin / super_admin</td><td>Set corporate_staff user status to <em>rejected</em></td></tr>
                  <tr><td><span class="method-badge m-post">POST</span></td><td><code>change_role</code></td><td>super_admin only</td><td>Change target user's role to any valid role</td></tr>
                  <tr><td><span class="method-badge m-post">POST</span></td><td><code>create_admin</code></td><td>super_admin only</td><td>Create a new admin-role user account directly</td></tr>
                </tbody>
              </table>
            </div>

            <h3 class="arch-h3">marketplace_data.php — Marketplace CRUD</h3>
            <div class="api-table-wrap">
              <table class="api-table">
                <thead><tr><th>Method</th><th>action</th><th>Auth Required</th><th>Description</th></tr></thead>
                <tbody>
                  <tr><td><span class="method-badge m-get">GET</span></td><td><code>list_insurance</code></td><td>Public</td><td>Return all active insurance listings</td></tr>
                  <tr><td><span class="method-badge m-get">GET</span></td><td><code>list_trucks</code></td><td>Public</td><td>Return all active truck listings</td></tr>
                  <tr><td><span class="method-badge m-get">GET</span></td><td><code>my_listings</code></td><td>Any role</td><td>Return listings owned by the requesting user</td></tr>
                  <tr><td><span class="method-badge m-post">POST</span></td><td><code>create_insurance_listing</code></td><td>insurance_company</td><td>Create a new spot insurance listing</td></tr>
                  <tr><td><span class="method-badge m-post">POST</span></td><td><code>create_truck_listing</code></td><td>trucking_company</td><td>Create a new truck-for-lease/sale listing</td></tr>
                  <tr><td><span class="method-badge m-post">POST</span></td><td><code>update_insurance_listing</code></td><td>Owner only</td><td>Update coverage, price, or expiry of a listing</td></tr>
                  <tr><td><span class="method-badge m-post">POST</span></td><td><code>update_truck_listing</code></td><td>Owner only</td><td>Update truck details or availability</td></tr>
                  <tr><td><span class="method-badge m-post">POST</span></td><td><code>delete_listing</code></td><td>Owner only</td><td>Remove a listing from the marketplace</td></tr>
                </tbody>
              </table>
            </div>

            <h3 class="arch-h3">offers_tracking_data.php — Load Board</h3>
            <div class="api-table-wrap">
              <table class="api-table">
                <thead><tr><th>Method</th><th>action</th><th>Description</th></tr></thead>
                <tbody>
                  <tr><td><span class="method-badge m-post">POST</span></td><td><code>update_location</code></td><td>Upsert driver GPS coordinates in <code>driver_locations.json</code></td></tr>
                  <tr><td><span class="method-badge m-post">POST</span></td><td><code>create_load_request</code></td><td>Post a new freight load to the board</td></tr>
                  <tr><td><span class="method-badge m-get">GET</span></td><td><code>match_offers</code></td><td>Return drivers nearest to a load's pickup point</td></tr>
                  <tr><td><span class="method-badge m-post">POST</span></td><td><code>send_offer</code></td><td>Send a load offer to a driver via Telegram &amp; SMS</td></tr>
                </tbody>
              </table>
            </div>

            <h3 class="arch-h3">dashboard_data.php &amp; shipper_data.php</h3>
            <div class="api-table-wrap">
              <table class="api-table">
                <thead><tr><th>Method</th><th>Endpoint</th><th>Description</th></tr></thead>
                <tbody>
                  <tr><td><span class="method-badge m-get">GET</span></td><td><code>dashboard_data.php?user_id=USR-*</code></td><td>Driver submissions visible to this user (staff see all)</td></tr>
                  <tr><td><span class="method-badge m-get">GET</span></td><td><code>dashboard_data.php?export=csv</code></td><td>Download all driver submissions as CSV</td></tr>
                  <tr><td><span class="method-badge m-post">POST</span></td><td><code>dashboard_data.php (action=update_status)</code></td><td>Approve or reject a driver application</td></tr>
                  <tr><td><span class="method-badge m-get">GET</span></td><td><code>shipper_data.php?user_id=USR-*</code></td><td>Quote submissions for a specific user</td></tr>
                  <tr><td><span class="method-badge m-get">GET</span></td><td><code>shipper_data.php?email=*</code></td><td>Quote submissions for a specific email address</td></tr>
                </tbody>
              </table>
            </div>

            <h3 class="arch-h3">audit.php — Audit Log</h3>
            <div class="api-table-wrap">
              <table class="api-table">
                <thead><tr><th>Method</th><th>action</th><th>Description</th></tr></thead>
                <tbody>
                  <tr><td><span class="method-badge m-get">GET</span></td><td><code>list</code></td><td>Paginated, searchable audit events with filters (user, type, action, date range)</td></tr>
                  <tr><td><span class="method-badge m-get">GET</span></td><td><code>stats</code></td><td>KPI counts: total events, unique users, events today, event-type breakdown</td></tr>
                  <tr><td><span class="method-badge m-post">POST</span></td><td>(body)</td><td>Directly append an audit event (used internally by audit_helper.php)</td></tr>
                </tbody>
              </table>
            </div>
          </div>

          <hr class="arch-divider" />

          <!-- ══ 7. FILE INDEX ══ -->
          <div class="arch-section" id="file-index">
            <h2 class="arch-h2">
              <iconify-icon icon="lucide:folder-open" style="font-size:20px;padding:8px"></iconify-icon>
              File Index
              <span style="font-size:14px;font-weight:400;color:var(--muted-foreground);margin-left:8px;">
                <?= count($phpFiles) ?> PHP files — auto-scanned at page load
              </span>
            </h2>
            <p class="arch-lead">
              Every <code>.php</code> file in the project root, scanned dynamically so this list stays
              current as the codebase grows.
            </p>
            <div class="file-grid">
              <?php foreach ($phpFiles as $filePath):
                $base = basename($filePath);
                $desc = $fileDescriptions[$base] ?? 'PHP file';
                $cat  = fileCategory($base, $apiFiles, $dashFiles);
                if ($cat === 'api') {
                  $icon = 'lucide:plug';
                  $iconStyle = 'color:#15803d';
                } elseif ($cat === 'dash') {
                  $icon = 'lucide:layout-dashboard';
                  $iconStyle = 'color:#6d28d9';
                } else {
                  $icon = 'lucide:file-code';
                  $iconStyle = 'color:var(--primary)';
                }
              ?>
              <div class="file-item">
                <iconify-icon icon="<?= htmlspecialchars($icon) ?>" style="font-size:18px;<?= $iconStyle ?>"></iconify-icon>
                <div>
                  <div class="file-item-name"><?= htmlspecialchars($base) ?></div>
                  <div class="file-item-desc"><?= htmlspecialchars($desc) ?></div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
            <p style="font-size:12px;color:var(--muted-foreground);">
              <iconify-icon icon="lucide:info" style="font-size:12px"></iconify-icon>
              Icon key:
              <iconify-icon icon="lucide:file-code" style="font-size:12px;color:var(--primary)"></iconify-icon> Page &nbsp;
              <iconify-icon icon="lucide:layout-dashboard" style="font-size:12px;color:#6d28d9"></iconify-icon> Dashboard &nbsp;
              <iconify-icon icon="lucide:plug" style="font-size:12px;color:#15803d"></iconify-icon> API endpoint
            </p>
          </div>

          <hr class="arch-divider" />

          <!-- ══ 8. DATA STORAGE ══ -->
          <div class="arch-section" id="data-storage">
            <h2 class="arch-h2">
              <iconify-icon icon="lucide:database" style="font-size:20px;padding:8px"></iconify-icon>
              Data Storage
            </h2>
            <p class="arch-lead">
              Fastrux uses a flat-file JSON storage model. All files live in <code>/data/</code> which is
              excluded from git via <code>.gitignore</code> for privacy. The directory is created on first
              write if it does not exist.
            </p>
            <div class="api-table-wrap">
              <table class="api-table">
                <thead><tr><th>File</th><th>Written by</th><th>Read by</th><th>Purpose</th></tr></thead>
                <tbody>
                  <tr>
                    <td><code>registered_users.json</code></td>
                    <td><code>process_form.php</code>, <code>admin_api.php</code></td>
                    <td><code>process_form.php</code>, <code>admin_api.php</code>, all API endpoints (RBAC)</td>
                    <td>Master user registry: id, name, email, password hash, role, status, dates</td>
                  </tr>
                  <tr>
                    <td><code>audit_log.json</code></td>
                    <td><code>audit_helper.php</code></td>
                    <td><code>audit.php</code>, <code>observability.php</code></td>
                    <td>Append-only audit trail: action, userId, entityType, entityId, IP, timestamp</td>
                  </tr>
                  <tr>
                    <td><code>quote_submissions.json</code></td>
                    <td><code>process_form.php</code></td>
                    <td><code>shipper_data.php</code>, <code>staff-dashboard.php</code></td>
                    <td>All freight quote requests with QUO-XXXX reference numbers</td>
                  </tr>
                  <tr>
                    <td><code>quote_submissions.csv</code></td>
                    <td><code>process_form.php</code> (auto-regenerated)</td>
                    <td>Staff/admin download</td>
                    <td>CSV mirror of quote submissions for export</td>
                  </tr>
                  <tr>
                    <td><code>driver_submissions.json</code></td>
                    <td><code>process_form.php</code>, <code>dashboard_data.php</code></td>
                    <td><code>dashboard_data.php</code></td>
                    <td>Driver applications with status, DRV-XXXX reference, and file metadata</td>
                  </tr>
                  <tr>
                    <td><code>driver_submissions.csv</code></td>
                    <td><code>process_form.php</code> (auto-regenerated)</td>
                    <td>Staff/admin download</td>
                    <td>CSV mirror of driver submissions</td>
                  </tr>
                  <tr>
                    <td><code>contact_submissions.json</code></td>
                    <td><code>process_form.php</code></td>
                    <td>Staff review</td>
                    <td>Contact form enquiries with CNT-XXXX reference numbers</td>
                  </tr>
                  <tr>
                    <td><code>newsletter_subscribers.json</code></td>
                    <td><code>process_form.php</code></td>
                    <td>Marketing export</td>
                    <td>Email newsletter subscriber list</td>
                  </tr>
                  <tr>
                    <td><code>load_requests.json</code></td>
                    <td><code>offers_tracking_data.php</code></td>
                    <td><code>offers_tracking_data.php</code>, <code>offers-tracking.php</code></td>
                    <td>Open and matched freight load board requests</td>
                  </tr>
                  <tr>
                    <td><code>driver_locations.json</code></td>
                    <td><code>offers_tracking_data.php</code></td>
                    <td><code>offers_tracking_data.php</code>, <code>driver-location.php</code></td>
                    <td>Real-time GPS coordinates for each active driver</td>
                  </tr>
                  <tr>
                    <td><code>insurance_listings.json</code></td>
                    <td><code>marketplace_data.php</code></td>
                    <td><code>marketplace_data.php</code>, <code>marketplace.php</code></td>
                    <td>Insurance company spot-coverage listings</td>
                  </tr>
                  <tr>
                    <td><code>truck_listings.json</code></td>
                    <td><code>marketplace_data.php</code></td>
                    <td><code>marketplace_data.php</code>, <code>marketplace.php</code></td>
                    <td>Trucking company lease/sale listings</td>
                  </tr>
                  <tr>
                    <td><code>telegram_config.json</code></td>
                    <td>Manual / admin UI</td>
                    <td><code>offers_tracking_data.php</code></td>
                    <td>Telegram bot token and target chat IDs</td>
                  </tr>
                  <tr>
                    <td><code>sms_config.json</code></td>
                    <td>Manual / admin UI</td>
                    <td><code>offers_tracking_data.php</code></td>
                    <td>Twilio account SID, auth token, and sender phone number</td>
                  </tr>
                  <tr>
                    <td><code>drivers/{id}/*</code></td>
                    <td><code>process_form.php</code> (multipart upload)</td>
                    <td>Staff review via <code>dashboard_data.php</code></td>
                    <td>Per-driver uploaded files: photo_front, photo_side, doc_licence, doc_insurance, etc.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

        </div><!-- /content column -->
      </div><!-- /arch-layout -->
    </div>
  </section>

  <!-- ── FOOTER ── -->
  <footer class="footer">
    <div class="container">
      <div class="footer-grid">
        <div class="footer-brand">
          <a href="index" class="logo" style="margin-bottom:12px;">
            <iconify-icon icon="lucide:truck" style="font-size:24px;color:var(--primary)"></iconify-icon>
            Fastrux
          </a>
          <p>Global logistics &amp; freight management platform serving clients in 180+ countries.</p>
        </div>
        <div>
          <h4 class="footer-heading">Services</h4>
          <div class="footer-links">
            <a href="ocean-freight">Ocean Freight</a>
            <a href="air-freight">Air Freight</a>
            <a href="ground-transport">Ground Transport</a>
            <a href="warehousing">Warehousing</a>
          </div>
        </div>
        <div>
          <h4 class="footer-heading">Company</h4>
          <div class="footer-links">
            <a href="careers">Careers</a>
            <a href="driver-onboarding">Drive with Us</a>
            <a class="nav-link" href="loadboard">Loadboard</a>
            <a href="news">News &amp; Media</a>
            <a href="contact">Contact</a>
          </div>
        </div>
        <div>
          <h4 class="footer-heading">Contact</h4>
          <div class="footer-links">
            <div class="footer-contact-item"><iconify-icon icon="lucide:map-pin"></iconify-icon> 1008 Oak Chase way, Leander, TX 78641</div>
            <div class="footer-contact-item"><iconify-icon icon="lucide:phone"></iconify-icon><a href="tel:+12038896129">+1-203-889-6129</a></div>
            <div class="footer-contact-item"><iconify-icon icon="lucide:mail"></iconify-icon><a href="mailto:RSNORI1@gmail.com">RSNORI1@gmail.com</a></div>
          </div>
        </div>
      </div>
      <div class="footer-bottom">
        <div>© 2026 Fastrux Logistics. All rights reserved.</div>
        <div class="social-links">
          <a href="https://facebook.com"  target="_blank" rel="noopener" aria-label="Facebook"><iconify-icon icon="lucide:facebook"  style="font-size:20px"></iconify-icon></a>
          <a href="https://twitter.com"   target="_blank" rel="noopener" aria-label="Twitter"><iconify-icon icon="lucide:twitter"   style="font-size:20px"></iconify-icon></a>
          <a href="https://linkedin.com"  target="_blank" rel="noopener" aria-label="LinkedIn"><iconify-icon icon="lucide:linkedin"  style="font-size:20px"></iconify-icon></a>
          <a href="https://instagram.com" target="_blank" rel="noopener" aria-label="Instagram"><iconify-icon icon="lucide:instagram" style="font-size:20px"></iconify-icon></a>
        </div>
      </div>
    </div>
  </footer>

  <script>
    /* ── Hamburger ── */
    const ham = document.getElementById('hamburger');
    const mob = document.getElementById('mobileMenu');
    ham.addEventListener('click', () => {
      ham.classList.toggle('open');
      mob.classList.toggle('open');
    });

    /* ── Active TOC link on scroll ── */
    const sections = document.querySelectorAll('.arch-section[id]');
    const tocLinks  = document.querySelectorAll('.toc-link');

    function updateToc() {
      let current = '';
      sections.forEach(s => {
        const top = s.getBoundingClientRect().top;
        if (top <= 120) current = s.id;
      });
      tocLinks.forEach(l => {
        const href = l.getAttribute('href').slice(1);
        l.classList.toggle('active', href === current);
      });
    }
    window.addEventListener('scroll', updateToc, { passive: true });
    updateToc();

    /* ── Smooth scroll for TOC links ── */
    tocLinks.forEach(link => {
      link.addEventListener('click', e => {
        e.preventDefault();
        const target = document.getElementById(link.getAttribute('href').slice(1));
        if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      });
    });
  </script>
  <script src="auth-nav.js"></script>
</body>
</html>
