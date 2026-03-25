<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <link rel="icon" href="favicon.svg" type="image/svg+xml" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>My Wallet — Fastrux Logistics</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="shared.css" />
  <script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>
  <style>
    body { background: var(--muted); }

    /* ── Dash header (reused pattern) ─────────────────────────────────────── */
    .dash-header {
      background: var(--card);
      border-bottom: 1px solid var(--border);
      position: sticky; top: 0; z-index: 100;
    }
    .dash-header-inner {
      display: flex; align-items: center; justify-content: space-between; height: 64px;
    }
    .dash-brand {
      display: flex; align-items: center; gap: 10px;
      font-size: 18px; font-weight: 800; color: var(--primary); text-decoration: none;
    }
    .dash-brand span { color: var(--foreground); font-weight: 400; font-size: 14px; }
    .dash-nav { display: flex; align-items: center; gap: 8px; }
    .dash-nav a {
      padding: 6px 14px; border-radius: var(--radius-md);
      font-size: 13px; font-weight: 600; color: var(--muted-foreground);
      text-decoration: none; transition: background .15s, color .15s;
    }
    .dash-nav a:hover { background: var(--muted); color: var(--foreground); }
    .dash-nav a.active { background: var(--secondary); color: var(--primary); }

    /* ── Page layout ──────────────────────────────────────────────────────── */
    .page-content { padding: 32px 0 48px; }
    .wallet-layout {
      display: grid;
      grid-template-columns: 260px 1fr;
      gap: 24px;
      align-items: start;
    }

    /* ── Sidebar ──────────────────────────────────────────────────────────── */
    .wallet-sidebar {
      position: sticky;
      top: 80px;
    }
    .sidebar-card {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius-xl);
      padding: 20px;
      margin-bottom: 12px;
    }
    .balance-label { font-size: 12px; color: var(--muted-foreground); margin-bottom: 4px; }
    .balance-amount { font-size: 36px; font-weight: 800; color: var(--primary); line-height: 1; }
    .balance-currency { font-size: 13px; color: var(--muted-foreground); margin-top: 4px; }
    .wallet-status-badge {
      display: inline-flex; align-items: center; gap: 5px;
      padding: 3px 10px; border-radius: 20px;
      font-size: 12px; font-weight: 700;
      margin-top: 10px;
    }
    .wallet-status-badge.active   { background: #d1fae5; color: #065f46; }
    .wallet-status-badge.frozen   { background: #dbeafe; color: #1e40af; }
    .wallet-status-badge.closed   { background: #fee2e2; color: #991b1b; }

    .sidebar-nav { display: flex; flex-direction: column; gap: 2px; }
    .sidebar-nav-btn {
      display: flex; align-items: center; gap: 10px;
      padding: 10px 12px; border-radius: var(--radius-md);
      font-size: 13px; font-weight: 600; color: var(--muted-foreground);
      background: none; border: none; cursor: pointer; font-family: inherit;
      text-align: left; transition: background .15s, color .15s; width: 100%;
    }
    .sidebar-nav-btn:hover { background: var(--muted); color: var(--foreground); }
    .sidebar-nav-btn.active { background: var(--secondary); color: var(--primary); }

    /* ── Content cards ────────────────────────────────────────────────────── */
    .content-card {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius-xl);
      padding: 24px;
      margin-bottom: 20px;
    }
    .content-card-header {
      display: flex; align-items: flex-start; justify-content: space-between;
      margin-bottom: 20px; gap: 12px;
    }
    .content-card-title    { font-size: 16px; font-weight: 700; margin-bottom: 2px; }
    .content-card-subtitle { font-size: 13px; color: var(--muted-foreground); }

    /* ── Tabs ─────────────────────────────────────────────────────────────── */
    .wallet-tab { display: none; }
    .wallet-tab.active { display: block; }

    /* ── Forms ────────────────────────────────────────────────────────────── */
    .form-group { margin-bottom: 14px; }
    .form-group label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 5px; }
    .form-control {
      width: 100%; padding: 9px 12px; font-size: 14px;
      border: 1px solid var(--border); border-radius: var(--radius-md);
      background: var(--input); color: var(--foreground); font-family: inherit;
      box-sizing: border-box;
    }
    .form-control:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(11,111,255,.12); }
    .form-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .form-section-title {
      font-size: 12px; font-weight: 700; text-transform: uppercase;
      letter-spacing: .6px; color: var(--muted-foreground);
      margin: 20px 0 12px; border-top: 1px solid var(--border); padding-top: 16px;
    }
    .form-feedback {
      padding: 10px 14px; border-radius: var(--radius-md);
      font-size: 13px; margin-bottom: 14px; display: none;
    }
    .form-feedback.success { background: #d1fae5; color: #065f46; display: block; }
    .form-feedback.error   { background: #fee2e2; color: #991b1b; display: block; }

    /* ── Buttons ──────────────────────────────────────────────────────────── */
    .btn {
      display: inline-flex; align-items: center; justify-content: center;
      gap: 6px; padding: 9px 18px; border-radius: var(--radius-md);
      font-size: 13px; font-weight: 700; font-family: inherit;
      cursor: pointer; border: none; transition: opacity .15s, background .15s;
      text-decoration: none;
    }
    .btn:disabled { opacity: .5; cursor: not-allowed; }
    .btn-primary   { background: var(--primary);     color: #fff; }
    .btn-outline   { background: transparent; border: 1.5px solid var(--border); color: var(--foreground); }
    .btn-danger    { background: var(--destructive);  color: #fff; }
    .btn-sm        { padding: 6px 12px; font-size: 12px; }

    /* ── Transaction list ─────────────────────────────────────────────────── */
    .tx-list { display: flex; flex-direction: column; gap: 0; }
    .tx-item {
      display: flex; align-items: center; gap: 14px;
      padding: 14px 0; border-bottom: 1px solid var(--border);
    }
    .tx-item:last-child { border-bottom: none; }
    .tx-icon {
      width: 40px; height: 40px; border-radius: 50%; flex-shrink: 0;
      display: flex; align-items: center; justify-content: center; font-size: 18px;
    }
    .tx-icon.deposit     { background: #d1fae5; color: #059669; }
    .tx-icon.withdrawal  { background: #fee2e2; color: #dc2626; }
    .tx-icon.transfer_in { background: #dbeafe; color: #2563eb; }
    .tx-icon.transfer_out{ background: #fef3c7; color: #d97706; }
    .tx-icon.payment     { background: #f3e8ff; color: #7c3aed; }
    .tx-icon.invoice_payment { background: #f3e8ff; color: #7c3aed; }
    .tx-icon.card_payment{ background: #fee2e2; color: #dc2626; }
    .tx-icon.default     { background: var(--muted); color: var(--muted-foreground); }
    .tx-body { flex: 1; min-width: 0; }
    .tx-desc { font-size: 14px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .tx-meta { font-size: 12px; color: var(--muted-foreground); margin-top: 2px; }
    .tx-amount { font-size: 15px; font-weight: 800; white-space: nowrap; }
    .tx-amount.credit { color: var(--success, #059669); }
    .tx-amount.debit  { color: var(--destructive, #dc2626); }
    .tx-status {
      font-size: 11px; font-weight: 700; padding: 2px 8px;
      border-radius: 20px; margin-left: 6px;
    }
    .tx-status.completed { background: #d1fae5; color: #065f46; }
    .tx-status.pending   { background: #fef3c7; color: #92400e; }
    .tx-status.failed    { background: #fee2e2; color: #991b1b; }

    /* ── Invoice list ─────────────────────────────────────────────────────── */
    .inv-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .inv-table th {
      text-align: left; padding: 8px 12px;
      background: var(--muted); color: var(--muted-foreground);
      font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px;
    }
    .inv-table td { padding: 12px 12px; border-bottom: 1px solid var(--border); }
    .inv-table tr:last-child td { border-bottom: none; }
    .inv-table tr:hover td { background: var(--muted); }
    .inv-badge {
      display: inline-block; padding: 2px 8px; border-radius: 20px;
      font-size: 11px; font-weight: 700;
    }
    .inv-badge.pending  { background: #fef3c7; color: #92400e; }
    .inv-badge.partial  { background: #dbeafe; color: #1e40af; }
    .inv-badge.paid     { background: #d1fae5; color: #065f46; }
    .inv-badge.overdue  { background: #fee2e2; color: #991b1b; }
    .inv-badge.cancelled{ background: var(--muted); color: var(--muted-foreground); }
    .inv-badge.draft    { background: #f3e8ff; color: #6d28d9; }

    /* ── Toast ────────────────────────────────────────────────────────────── */
    #toast {
      position: fixed; bottom: 24px; right: 24px;
      background: var(--foreground); color: #fff;
      padding: 12px 20px; border-radius: var(--radius-md);
      font-size: 14px; z-index: 9999; display: none;
      box-shadow: var(--shadow-xl); max-width: 360px;
    }
    #toast.show    { display: block; animation: slideUp .3s ease; }
    #toast.success { background: #059669; }
    #toast.error   { background: var(--destructive, #dc2626); }
    @keyframes slideUp { from { opacity:0; transform: translateY(16px); } to { opacity:1; transform:translateY(0); } }

    /* ── Modal (pay invoice) ──────────────────────────────────────────────── */
    .modal-overlay {
      position: fixed; inset: 0; background: rgba(0,0,0,.45);
      display: flex; align-items: center; justify-content: center;
      z-index: 1000; display: none;
    }
    .modal-overlay.open { display: flex; }
    .modal {
      background: var(--card); border-radius: var(--radius-xl);
      padding: 28px; max-width: 520px; width: 94%; max-height: 90vh;
      overflow-y: auto; box-shadow: var(--shadow-xl);
    }
    .modal-header {
      display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;
    }
    .modal-title   { font-size: 18px; font-weight: 700; }
    .modal-close   { background: none; border: none; cursor: pointer; color: var(--muted-foreground); font-size: 22px; }

    /* ── Transfer modal ───────────────────────────────────────────────────── */
    #transferModal .modal { max-width: 480px; }

    @media (max-width: 768px) {
      .wallet-layout { grid-template-columns: 1fr; }
      .form-row-2    { grid-template-columns: 1fr; }
      .wallet-sidebar { position: static; }
    }
  </style>
</head>
<body>

<!-- ── Header ─────────────────────────────────────────────────────────────── -->
<header class="dash-header">
  <div class="container dash-header-inner">
    <a href="index" class="dash-brand">
      <iconify-icon icon="lucide:truck" style="font-size:22px"></iconify-icon>
      Fastrux <span>/ Wallet</span>
    </a>
    <nav class="dash-nav">
      <a href="loadboard">Loadboard</a>
      <a href="marketplace">Marketplace</a>
      <a href="account" id="headerAccountLink">Account</a>
    </nav>
  </div>
</header>

<!-- ── Main ───────────────────────────────────────────────────────────────── -->
<main>
  <!-- Not-logged-in prompt -->
  <div class="container page-content" id="loginPrompt" style="display:none;">
    <div class="content-card" style="max-width:480px;margin:0 auto;text-align:center;padding:48px 24px;">
      <iconify-icon icon="lucide:lock-keyhole" style="font-size:48px;color:var(--primary);margin-bottom:16px;display:block;"></iconify-icon>
      <p style="font-size:18px;font-weight:700;margin-bottom:8px;">Sign in to access your wallet</p>
      <p style="font-size:14px;color:var(--muted-foreground);margin-bottom:24px;">You need to be logged in to manage your digital wallet.</p>
      <div style="display:flex;gap:12px;justify-content:center;">
        <a href="login?redirect=wallet" class="btn btn-primary">Sign In</a>
        <a href="register?redirect=wallet" class="btn btn-outline">Create Account</a>
      </div>
    </div>
  </div>

  <!-- Wallet UI (visible when logged in) -->
  <div class="container page-content" id="walletLayout" style="display:none;">
    <div class="wallet-layout">

      <!-- ── Sidebar ──────────────────────────────────────────────────────── -->
      <aside class="wallet-sidebar">
        <div class="sidebar-card">
          <div class="balance-label">Available Balance</div>
          <div class="balance-amount" id="sidebarBalance">$0.00</div>
          <div class="balance-currency" id="sidebarCurrency">USD</div>
          <div id="walletStatusBadge" class="wallet-status-badge active">
            <iconify-icon icon="lucide:circle-check" style="font-size:13px;"></iconify-icon>
            Active
          </div>
        </div>
        <div class="sidebar-card">
          <nav class="sidebar-nav">
            <button class="sidebar-nav-btn active" onclick="showTab('overview')" id="nav-overview">
              <iconify-icon icon="lucide:layout-dashboard" style="font-size:16px;"></iconify-icon>
              Overview
            </button>
            <button class="sidebar-nav-btn" onclick="showTab('deposit')" id="nav-deposit">
              <iconify-icon icon="lucide:plus-circle" style="font-size:16px;"></iconify-icon>
              Deposit
            </button>
            <button class="sidebar-nav-btn" onclick="showTab('withdraw')" id="nav-withdraw">
              <iconify-icon icon="lucide:minus-circle" style="font-size:16px;"></iconify-icon>
              Withdraw
            </button>
            <button class="sidebar-nav-btn" onclick="showTab('transfer')" id="nav-transfer">
              <iconify-icon icon="lucide:arrow-right-left" style="font-size:16px;"></iconify-icon>
              Transfer
            </button>
            <button class="sidebar-nav-btn" onclick="showTab('invoices')" id="nav-invoices">
              <iconify-icon icon="lucide:file-text" style="font-size:16px;"></iconify-icon>
              Invoices
            </button>
            <button class="sidebar-nav-btn" onclick="showTab('ledger')" id="nav-ledger">
              <iconify-icon icon="lucide:book-open" style="font-size:16px;"></iconify-icon>
              Ledger
            </button>
          </nav>
        </div>
      </aside>

      <!-- ── Main content ─────────────────────────────────────────────────── -->
      <div id="walletContent">

        <!-- ── OVERVIEW tab ─────────────────────────────────────────────── -->
        <div class="wallet-tab active" id="tab-overview">
          <!-- Stats row -->
          <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:20px;">
            <div class="content-card" style="margin-bottom:0;text-align:center;">
              <div style="font-size:11px;color:var(--muted-foreground);text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Balance</div>
              <div style="font-size:26px;font-weight:800;color:var(--primary);" id="overviewBalance">$0.00</div>
            </div>
            <div class="content-card" style="margin-bottom:0;text-align:center;">
              <div style="font-size:11px;color:var(--muted-foreground);text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Total Deposited</div>
              <div style="font-size:26px;font-weight:800;color:#059669;" id="overviewDeposited">$0.00</div>
            </div>
            <div class="content-card" style="margin-bottom:0;text-align:center;">
              <div style="font-size:11px;color:var(--muted-foreground);text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Total Spent</div>
              <div style="font-size:26px;font-weight:800;color:#dc2626;" id="overviewSpent">$0.00</div>
            </div>
          </div>

          <!-- Recent transactions -->
          <div class="content-card">
            <div class="content-card-header">
              <div>
                <div class="content-card-title">Recent Transactions</div>
                <div class="content-card-subtitle">Your 10 most recent wallet movements.</div>
              </div>
              <button class="btn btn-outline btn-sm" onclick="showTab('ledger')">View All</button>
            </div>
            <div id="recentTxList">
              <div style="text-align:center;padding:32px;color:var(--muted-foreground);">
                <iconify-icon icon="lucide:inbox" style="font-size:40px;display:block;margin:0 auto 12px;opacity:.4;"></iconify-icon>
                No transactions yet.
              </div>
            </div>
          </div>
        </div><!-- /tab-overview -->

        <!-- ── DEPOSIT tab ───────────────────────────────────────────────── -->
        <div class="wallet-tab" id="tab-deposit">
          <div class="content-card">
            <div class="content-card-header">
              <div>
                <div class="content-card-title">Deposit Funds</div>
                <div class="content-card-subtitle">Add money to your wallet using a card.</div>
              </div>
            </div>

            <div style="display:flex;gap:6px;margin-bottom:18px;">
              <span style="display:inline-block;padding:3px 8px;border:1px solid var(--border);border-radius:4px;font-size:11px;font-weight:700;letter-spacing:.5px;color:var(--muted-foreground);">VISA</span>
              <span style="display:inline-block;padding:3px 8px;border:1px solid var(--border);border-radius:4px;font-size:11px;font-weight:700;letter-spacing:.5px;color:var(--muted-foreground);">MASTERCARD</span>
              <span style="display:inline-block;padding:3px 8px;border:1px solid var(--border);border-radius:4px;font-size:11px;font-weight:700;letter-spacing:.5px;color:var(--muted-foreground);">AMEX</span>
            </div>

            <div class="form-feedback" id="depositFeedback"></div>
            <form id="depositForm" novalidate>
              <div class="form-row-2">
                <div class="form-group">
                  <label for="dep_amount">Amount (USD) *</label>
                  <input class="form-control" type="number" id="dep_amount" min="1" max="10000" step="0.01" placeholder="e.g. 100.00" required />
                </div>
                <div class="form-group">
                  <label for="dep_description">Note</label>
                  <input class="form-control" type="text" id="dep_description" placeholder="e.g. Top-up" maxlength="120" />
                </div>
              </div>

              <div class="form-section-title">Card Details</div>
              <div class="form-group">
                <label for="dep_card_name">Cardholder Name *</label>
                <input class="form-control" type="text" id="dep_card_name" placeholder="Jane Smith" required autocomplete="cc-name" />
              </div>
              <div class="form-group" style="position:relative;">
                <label for="dep_card_number">Card Number *</label>
                <input class="form-control" type="text" id="dep_card_number" placeholder="•••• •••• •••• ••••" maxlength="19" required autocomplete="cc-number" inputmode="numeric" style="padding-right:40px;" />
                <iconify-icon icon="lucide:credit-card" style="position:absolute;right:12px;bottom:10px;font-size:18px;color:var(--muted-foreground);pointer-events:none;"></iconify-icon>
              </div>
              <div class="form-row-2">
                <div class="form-group">
                  <label for="dep_expiry">Expiry (MM / YY) *</label>
                  <input class="form-control" type="text" id="dep_expiry" placeholder="MM / YY" maxlength="7" required autocomplete="cc-exp" inputmode="numeric" />
                </div>
                <div class="form-group">
                  <label for="dep_cvv">CVV *</label>
                  <input class="form-control" type="password" id="dep_cvv" placeholder="•••" maxlength="4" required autocomplete="cc-csc" inputmode="numeric" />
                </div>
              </div>
              <div class="form-group">
                <label for="dep_billing">Billing Address *</label>
                <input class="form-control" type="text" id="dep_billing" placeholder="123 Main St, City, Postcode" required autocomplete="billing street-address" />
              </div>

              <p style="font-size:12px;color:var(--muted-foreground);margin:4px 0 18px;display:flex;align-items:center;gap:6px;">
                <iconify-icon icon="lucide:lock" style="font-size:13px;flex-shrink:0;"></iconify-icon>
                Your payment is processed securely. Full card details are never stored on our servers.
              </p>

              <div style="display:flex;justify-content:flex-end;">
                <button type="submit" class="btn btn-primary" id="depositBtn">
                  <iconify-icon icon="lucide:plus-circle" style="font-size:15px;"></iconify-icon>
                  Add Funds
                </button>
              </div>
            </form>
          </div>
        </div><!-- /tab-deposit -->

        <!-- ── WITHDRAW tab ──────────────────────────────────────────────── -->
        <div class="wallet-tab" id="tab-withdraw">
          <div class="content-card">
            <div class="content-card-header">
              <div>
                <div class="content-card-title">Withdraw Funds</div>
                <div class="content-card-subtitle">Send funds from your wallet to a bank account.</div>
              </div>
            </div>
            <div class="form-feedback" id="withdrawFeedback"></div>
            <form id="withdrawForm" novalidate>
              <div class="form-row-2">
                <div class="form-group">
                  <label for="wd_amount">Amount (USD) *</label>
                  <input class="form-control" type="number" id="wd_amount" min="1" step="0.01" placeholder="e.g. 50.00" required />
                </div>
                <div class="form-group">
                  <label for="wd_description">Note</label>
                  <input class="form-control" type="text" id="wd_description" placeholder="e.g. Monthly payout" maxlength="120" />
                </div>
              </div>

              <div class="form-section-title">Bank Account</div>
              <div class="form-row-2">
                <div class="form-group">
                  <label for="wd_bank_last4">Account Number (last 4) *</label>
                  <input class="form-control" type="text" id="wd_bank_last4" placeholder="e.g. 4321" maxlength="4" inputmode="numeric" required />
                </div>
                <div class="form-group">
                  <label for="wd_routing">Routing Number</label>
                  <input class="form-control" type="text" id="wd_routing" placeholder="9-digit routing" maxlength="9" inputmode="numeric" />
                </div>
              </div>

              <p style="font-size:12px;color:var(--muted-foreground);margin-bottom:18px;">
                Withdrawals are processed within 1-3 business days via ACH transfer.
              </p>

              <div style="display:flex;justify-content:flex-end;">
                <button type="submit" class="btn btn-primary" id="withdrawBtn">
                  <iconify-icon icon="lucide:minus-circle" style="font-size:15px;"></iconify-icon>
                  Withdraw Funds
                </button>
              </div>
            </form>
          </div>
        </div><!-- /tab-withdraw -->

        <!-- ── TRANSFER tab ──────────────────────────────────────────────── -->
        <div class="wallet-tab" id="tab-transfer">
          <div class="content-card">
            <div class="content-card-header">
              <div>
                <div class="content-card-title">Transfer to Another User</div>
                <div class="content-card-subtitle">Send funds instantly from your wallet to another Fastrux user.</div>
              </div>
            </div>
            <div class="form-feedback" id="transferFeedback"></div>
            <form id="transferForm" novalidate>
              <div class="form-group">
                <label for="tr_to_user_id">Recipient User ID *</label>
                <input class="form-control" type="text" id="tr_to_user_id" placeholder="USR-XXXXXXXXXXXXXXXX" required />
              </div>
              <div class="form-row-2">
                <div class="form-group">
                  <label for="tr_amount">Amount (USD) *</label>
                  <input class="form-control" type="number" id="tr_amount" min="0.01" step="0.01" placeholder="e.g. 25.00" required />
                </div>
                <div class="form-group">
                  <label for="tr_description">Note</label>
                  <input class="form-control" type="text" id="tr_description" placeholder="e.g. Payment for service" maxlength="120" />
                </div>
              </div>
              <div style="display:flex;justify-content:flex-end;">
                <button type="submit" class="btn btn-primary" id="transferBtn">
                  <iconify-icon icon="lucide:send" style="font-size:15px;"></iconify-icon>
                  Send Transfer
                </button>
              </div>
            </form>
          </div>
        </div><!-- /tab-transfer -->

        <!-- ── INVOICES tab ──────────────────────────────────────────────── -->
        <div class="wallet-tab" id="tab-invoices">
          <!-- Create invoice -->
          <div class="content-card" style="margin-bottom:20px;">
            <div class="content-card-header">
              <div>
                <div class="content-card-title">Create Invoice</div>
                <div class="content-card-subtitle">Generate a payment request for another Fastrux user.</div>
              </div>
            </div>
            <div class="form-feedback" id="invCreateFeedback"></div>
            <form id="invoiceCreateForm" novalidate>
              <div class="form-row-2">
                <div class="form-group">
                  <label for="inv_payer">Payer User ID *</label>
                  <input class="form-control" type="text" id="inv_payer" placeholder="USR-XXXXXXXXXXXXXXXX" required />
                </div>
                <div class="form-group">
                  <label for="inv_due_date">Due Date</label>
                  <input class="form-control" type="date" id="inv_due_date" />
                </div>
              </div>
              <div class="form-group">
                <label for="inv_description">Description</label>
                <input class="form-control" type="text" id="inv_description" placeholder="e.g. Freight services for load FX-XXX" maxlength="300" />
              </div>

              <div class="form-section-title">Line Items</div>
              <div id="lineItemsContainer">
                <div class="line-item-row" style="display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:8px;align-items:end;margin-bottom:8px;">
                  <div class="form-group" style="margin-bottom:0;">
                    <label style="font-size:12px;">Description *</label>
                    <input class="form-control" type="text" name="li_desc[]" placeholder="Service description" required />
                  </div>
                  <div class="form-group" style="margin-bottom:0;">
                    <label style="font-size:12px;">Qty *</label>
                    <input class="form-control" type="number" name="li_qty[]" min="0.01" step="0.01" value="1" required />
                  </div>
                  <div class="form-group" style="margin-bottom:0;">
                    <label style="font-size:12px;">Unit Price *</label>
                    <input class="form-control" type="number" name="li_price[]" min="0" step="0.01" placeholder="0.00" required />
                  </div>
                  <button type="button" class="btn btn-outline btn-sm" style="margin-bottom:0;" onclick="removeLineItem(this)">✕</button>
                </div>
              </div>

              <div style="display:flex;align-items:center;justify-content:space-between;margin:8px 0 16px;">
                <button type="button" class="btn btn-outline btn-sm" onclick="addLineItem()">
                  <iconify-icon icon="lucide:plus" style="font-size:13px;"></iconify-icon> Add Line Item
                </button>
                <div style="font-size:14px;font-weight:700;">
                  Total: <span id="invoiceTotal" style="color:var(--primary);">$0.00</span>
                </div>
              </div>

              <div style="display:flex;justify-content:flex-end;">
                <button type="submit" class="btn btn-primary" id="createInvBtn">
                  <iconify-icon icon="lucide:file-plus" style="font-size:15px;"></iconify-icon>
                  Create Invoice
                </button>
              </div>
            </form>
          </div>

          <!-- Invoice list -->
          <div class="content-card">
            <div class="content-card-header">
              <div>
                <div class="content-card-title">My Invoices</div>
                <div class="content-card-subtitle">Invoices you have issued or received.</div>
              </div>
              <div style="display:flex;gap:8px;">
                <select class="form-control" id="invFilterRole" style="width:110px;padding:6px 10px;font-size:12px;" onchange="loadInvoices()">
                  <option value="">All</option>
                  <option value="issuer">Issued</option>
                  <option value="payer">Received</option>
                </select>
                <select class="form-control" id="invFilterStatus" style="width:110px;padding:6px 10px;font-size:12px;" onchange="loadInvoices()">
                  <option value="">All statuses</option>
                  <option value="pending">Pending</option>
                  <option value="partial">Partial</option>
                  <option value="paid">Paid</option>
                  <option value="overdue">Overdue</option>
                  <option value="cancelled">Cancelled</option>
                </select>
                <button class="btn btn-outline btn-sm" onclick="loadInvoices()">
                  <iconify-icon icon="lucide:refresh-cw" style="font-size:13px;"></iconify-icon>
                </button>
              </div>
            </div>
            <div id="invoiceList">
              <div style="text-align:center;padding:32px;color:var(--muted-foreground);">
                <iconify-icon icon="lucide:inbox" style="font-size:40px;display:block;margin:0 auto 12px;opacity:.4;"></iconify-icon>
                No invoices yet.
              </div>
            </div>
          </div>
        </div><!-- /tab-invoices -->

        <!-- ── LEDGER tab ────────────────────────────────────────────────── -->
        <div class="wallet-tab" id="tab-ledger">
          <div class="content-card">
            <div class="content-card-header">
              <div>
                <div class="content-card-title">Ledger Entries</div>
                <div class="content-card-subtitle">Immutable double-entry accounting record.</div>
              </div>
              <div style="display:flex;gap:8px;">
                <select class="form-control" id="ledgerTypeFilter" style="width:120px;padding:6px 10px;font-size:12px;" onchange="loadLedger()">
                  <option value="">All types</option>
                  <option value="debit">Debit</option>
                  <option value="credit">Credit</option>
                </select>
                <button class="btn btn-outline btn-sm" onclick="loadLedger()">
                  <iconify-icon icon="lucide:refresh-cw" style="font-size:13px;"></iconify-icon>
                </button>
              </div>
            </div>
            <!-- Ledger summary -->
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px;" id="ledgerSummary">
              <div style="padding:12px;background:var(--muted);border-radius:var(--radius-md);text-align:center;">
                <div style="font-size:11px;color:var(--muted-foreground);text-transform:uppercase;letter-spacing:.5px;">Total Credits</div>
                <div style="font-size:20px;font-weight:800;color:#059669;" id="ledgerCredits">$0.00</div>
              </div>
              <div style="padding:12px;background:var(--muted);border-radius:var(--radius-md);text-align:center;">
                <div style="font-size:11px;color:var(--muted-foreground);text-transform:uppercase;letter-spacing:.5px;">Total Debits</div>
                <div style="font-size:20px;font-weight:800;color:#dc2626;" id="ledgerDebits">$0.00</div>
              </div>
              <div style="padding:12px;background:var(--muted);border-radius:var(--radius-md);text-align:center;">
                <div style="font-size:11px;color:var(--muted-foreground);text-transform:uppercase;letter-spacing:.5px;">Net Balance</div>
                <div style="font-size:20px;font-weight:800;color:var(--primary);" id="ledgerNet">$0.00</div>
              </div>
            </div>

            <div id="ledgerList">
              <div style="text-align:center;padding:32px;color:var(--muted-foreground);">
                <iconify-icon icon="lucide:inbox" style="font-size:40px;display:block;margin:0 auto 12px;opacity:.4;"></iconify-icon>
                No ledger entries yet.
              </div>
            </div>
            <div id="ledgerPager" style="display:flex;justify-content:center;gap:8px;margin-top:16px;display:none;"></div>
          </div>
        </div><!-- /tab-ledger -->

      </div><!-- /walletContent -->
    </div><!-- /wallet-layout -->
  </div><!-- /walletLayout -->
</main>

<!-- ── Pay Invoice Modal ───────────────────────────────────────────────────── -->
<div class="modal-overlay" id="payInvoiceModal">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Pay Invoice</span>
      <button class="modal-close" onclick="closePayModal()">×</button>
    </div>
    <div id="payModalMeta" style="background:var(--muted);border-radius:var(--radius-md);padding:14px;margin-bottom:18px;font-size:13px;"></div>
    <div class="form-feedback" id="payInvFeedback"></div>
    <form id="payInvoiceForm" novalidate>
      <input type="hidden" id="payInvoiceId" />
      <div class="form-row-2" style="margin-bottom:14px;">
        <div class="form-group" style="margin-bottom:0;">
          <label for="payAmount">Amount *</label>
          <input class="form-control" type="number" id="payAmount" min="0.01" step="0.01" required placeholder="0.00" />
        </div>
        <div class="form-group" style="margin-bottom:0;">
          <label for="payMethod">Payment Method *</label>
          <select class="form-control" id="payMethod" onchange="togglePayCardSection()">
            <option value="wallet">Wallet</option>
            <option value="card">Card</option>
          </select>
        </div>
      </div>

      <div id="payCardSection" style="display:none;">
        <div class="form-section-title" style="margin-top:4px;">Card Details</div>
        <div class="form-group">
          <label for="payCardName">Cardholder Name *</label>
          <input class="form-control" type="text" id="payCardName" autocomplete="cc-name" placeholder="Jane Smith" />
        </div>
        <div class="form-group">
          <label for="payCardNumber">Card Number *</label>
          <input class="form-control" type="text" id="payCardNumber" placeholder="•••• •••• •••• ••••" maxlength="19" inputmode="numeric" autocomplete="cc-number" />
        </div>
        <div class="form-row-2">
          <div class="form-group">
            <label for="payExpiry">Expiry (MM / YY) *</label>
            <input class="form-control" type="text" id="payExpiry" placeholder="MM / YY" maxlength="7" inputmode="numeric" autocomplete="cc-exp" />
          </div>
          <div class="form-group">
            <label for="payCvv">CVV *</label>
            <input class="form-control" type="password" id="payCvv" placeholder="•••" maxlength="4" inputmode="numeric" autocomplete="cc-csc" />
          </div>
        </div>
        <div class="form-group">
          <label for="payBilling">Billing Address *</label>
          <input class="form-control" type="text" id="payBilling" placeholder="123 Main St, City" autocomplete="billing street-address" />
        </div>
      </div>

      <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:6px;">
        <button type="button" class="btn btn-outline" onclick="closePayModal()">Cancel</button>
        <button type="submit" class="btn btn-primary" id="payInvSubmitBtn">
          <iconify-icon icon="lucide:credit-card" style="font-size:15px;"></iconify-icon>
          Pay Now
        </button>
      </div>
    </form>
  </div>
</div>

<!-- ── Toast ───────────────────────────────────────────────────────────────── -->
<div id="toast"></div>

<script>
// ── Auth ──────────────────────────────────────────────────────────────────────
let currentUser = null;
try { currentUser = JSON.parse(localStorage.getItem('fx_user')); } catch(e) {}

const walletLayout = document.getElementById('walletLayout');
const loginPrompt  = document.getElementById('loginPrompt');

if (!currentUser || !currentUser.id) {
  loginPrompt.style.display  = 'block';
  walletLayout.style.display = 'none';
} else {
  walletLayout.style.display = 'block';
  loginPrompt.style.display  = 'none';
  initWallet();
}

// ── State ─────────────────────────────────────────────────────────────────────
let walletData     = null;
let ledgerOffset   = 0;
const LEDGER_LIMIT = 20;
let currentPayInvoice = null;

// ── Utilities ─────────────────────────────────────────────────────────────────
function fmtCurrency(val, symbol = '$') {
  return symbol + parseFloat(val || 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

function showToast(msg, type = 'default') {
  const t = document.getElementById('toast');
  t.textContent  = msg;
  t.className    = 'show ' + type;
  clearTimeout(t._tid);
  t._tid = setTimeout(() => { t.className = ''; }, 3500);
}

function setFeedback(id, msg, type) {
  const el = document.getElementById(id);
  if (!el) return;
  el.textContent = msg;
  el.className   = 'form-feedback ' + type;
}

function clearFeedback(id) {
  const el = document.getElementById(id);
  if (el) { el.textContent = ''; el.className = 'form-feedback'; }
}

// ── Tab navigation ────────────────────────────────────────────────────────────
function showTab(name) {
  document.querySelectorAll('.wallet-tab').forEach(t => t.classList.remove('active'));
  document.querySelectorAll('.sidebar-nav-btn').forEach(b => b.classList.remove('active'));
  const tab = document.getElementById('tab-' + name);
  if (tab) tab.classList.add('active');
  const btn = document.getElementById('nav-' + name);
  if (btn) btn.classList.add('active');

  if (name === 'invoices') loadInvoices();
  if (name === 'ledger')   { ledgerOffset = 0; loadLedger(); loadLedgerSummary(); }
}

// ── Load wallet ───────────────────────────────────────────────────────────────
async function initWallet() {
  try {
    const res  = await fetch('wallet_data.php?action=get_wallet&user_id=' + encodeURIComponent(currentUser.id));
    const data = await res.json();
    if (data.success) {
      walletData = data.wallet;
      refreshBalanceDisplay();
      renderRecentTransactions(data.wallet.transactions || []);
    }
  } catch(e) {
    showToast('Could not load wallet.', 'error');
  }
}

function refreshBalanceDisplay() {
  if (!walletData) return;
  const bal = parseFloat(walletData.balance || 0);
  const cur = walletData.currency || 'USD';
  document.getElementById('sidebarBalance').textContent  = fmtCurrency(bal);
  document.getElementById('sidebarCurrency').textContent = cur;
  document.getElementById('overviewBalance').textContent = fmtCurrency(bal);

  const badge = document.getElementById('walletStatusBadge');
  const status = walletData.status || 'active';
  badge.className = 'wallet-status-badge ' + status;
  const icons = { active: 'lucide:circle-check', frozen: 'lucide:snowflake', closed: 'lucide:x-circle' };
  badge.innerHTML = `<iconify-icon icon="${icons[status] || 'lucide:circle'}" style="font-size:13px;"></iconify-icon> ${status.charAt(0).toUpperCase() + status.slice(1)}`;

  // Compute overview stats
  let deposited = 0, spent = 0;
  (walletData.transactions || []).forEach(tx => {
    const a = parseFloat(tx.amount || 0);
    if (['deposit'].includes(tx.type))                          deposited += a;
    if (['withdrawal','payment','card_payment','transfer_out','invoice_payment'].includes(tx.type)) spent += a;
  });
  document.getElementById('overviewDeposited').textContent = fmtCurrency(deposited);
  document.getElementById('overviewSpent').textContent     = fmtCurrency(spent);
}

// ── Recent transactions ───────────────────────────────────────────────────────
const TX_ICONS = {
  deposit:          { cls: 'deposit',      icon: 'lucide:arrow-down-circle' },
  withdrawal:       { cls: 'withdrawal',   icon: 'lucide:arrow-up-circle' },
  transfer_in:      { cls: 'transfer_in',  icon: 'lucide:arrow-right' },
  transfer_out:     { cls: 'transfer_out', icon: 'lucide:arrow-left' },
  payment:          { cls: 'payment',      icon: 'lucide:zap' },
  card_payment:     { cls: 'card_payment', icon: 'lucide:credit-card' },
  invoice_payment:  { cls: 'invoice_payment', icon: 'lucide:file-text' },
  invoice_receipt:  { cls: 'deposit',      icon: 'lucide:file-check' },
};

function txIsCredit(type) {
  return ['deposit','transfer_in','invoice_receipt'].includes(type);
}

function renderTxItem(tx) {
  const meta    = TX_ICONS[tx.type] || { cls: 'default', icon: 'lucide:circle' };
  const credit  = txIsCredit(tx.type);
  const amount  = parseFloat(tx.amount || 0);
  const status  = tx.status || 'completed';
  return `
    <div class="tx-item">
      <div class="tx-icon ${meta.cls}">
        <iconify-icon icon="${meta.icon}"></iconify-icon>
      </div>
      <div class="tx-body">
        <div class="tx-desc">${escHtml(tx.description || tx.type)}</div>
        <div class="tx-meta">${tx.timestamp || ''} &nbsp;·&nbsp; ${(tx.currency || 'USD')}</div>
      </div>
      <div>
        <span class="tx-amount ${credit ? 'credit' : 'debit'}">${credit ? '+' : '-'}${fmtCurrency(amount)}</span>
        <span class="tx-status ${status}">${status}</span>
      </div>
    </div>`;
}

function renderRecentTransactions(txs) {
  const el = document.getElementById('recentTxList');
  if (!txs.length) {
    el.innerHTML = `<div style="text-align:center;padding:32px;color:var(--muted-foreground);">
      <iconify-icon icon="lucide:inbox" style="font-size:40px;display:block;margin:0 auto 12px;opacity:.4;"></iconify-icon>No transactions yet.</div>`;
    return;
  }
  const recent = [...txs].reverse().slice(0, 10);
  el.innerHTML = `<div class="tx-list">${recent.map(renderTxItem).join('')}</div>`;
}

// ── Deposit form ──────────────────────────────────────────────────────────────
document.getElementById('depositForm').addEventListener('submit', async e => {
  e.preventDefault();
  clearFeedback('depositFeedback');

  const cardRaw    = document.getElementById('dep_card_number').value.replace(/\s/g, '');
  const cardLast4  = cardRaw.slice(-4);
  const expiryRaw  = document.getElementById('dep_expiry').value.replace(/\s/g, '');
  const expiryFmt  = expiryRaw.replace('/', '/');

  const body = new FormData();
  body.append('action',          'add_funds');
  body.append('user_id',         currentUser.id);
  body.append('amount',          document.getElementById('dep_amount').value);
  body.append('description',     document.getElementById('dep_description').value);
  body.append('card_name',       document.getElementById('dep_card_name').value);
  body.append('card_last4',      cardLast4);
  body.append('card_expiry',     expiryFmt);
  body.append('billing_address', document.getElementById('dep_billing').value);

  const btn = document.getElementById('depositBtn');
  btn.disabled = true;

  try {
    const res  = await fetch('wallet_data.php', { method: 'POST', body });
    const data = await res.json();
    if (data.success) {
      walletData.balance       = data.balance;
      walletData.transactions  = data.transactions;
      walletData.currency      = data.currency || walletData.currency;
      refreshBalanceDisplay();
      renderRecentTransactions(walletData.transactions);
      setFeedback('depositFeedback', `Funds added! New balance: ${fmtCurrency(data.balance)}`, 'success');
      document.getElementById('depositForm').reset();
      showToast('Deposit successful!', 'success');
    } else {
      setFeedback('depositFeedback', data.message || 'Deposit failed.', 'error');
    }
  } catch(err) {
    setFeedback('depositFeedback', 'Network error. Please try again.', 'error');
  } finally {
    btn.disabled = false;
  }
});

// Card number formatting
document.getElementById('dep_card_number').addEventListener('input', function() {
  let v = this.value.replace(/\D/g, '').slice(0, 16);
  this.value = v.replace(/(.{4})/g, '$1 ').trim();
});

document.getElementById('dep_expiry').addEventListener('input', function() {
  let v = this.value.replace(/\D/g, '').slice(0, 4);
  if (v.length >= 3) v = v.slice(0,2) + ' / ' + v.slice(2);
  this.value = v;
});

// ── Withdraw form ─────────────────────────────────────────────────────────────
document.getElementById('withdrawForm').addEventListener('submit', async e => {
  e.preventDefault();
  clearFeedback('withdrawFeedback');

  const body = new FormData();
  body.append('action',               'withdraw');
  body.append('user_id',              currentUser.id);
  body.append('amount',               document.getElementById('wd_amount').value);
  body.append('description',          document.getElementById('wd_description').value);
  body.append('bank_account_last4',   document.getElementById('wd_bank_last4').value);
  body.append('bank_routing',         document.getElementById('wd_routing').value);

  const btn = document.getElementById('withdrawBtn');
  btn.disabled = true;

  try {
    const res  = await fetch('wallet_data.php', { method: 'POST', body });
    const data = await res.json();
    if (data.success) {
      walletData.balance      = data.balance;
      walletData.transactions = data.transactions;
      refreshBalanceDisplay();
      renderRecentTransactions(walletData.transactions);
      setFeedback('withdrawFeedback', `Withdrawal queued. New balance: ${fmtCurrency(data.balance)}`, 'success');
      document.getElementById('withdrawForm').reset();
      showToast('Withdrawal submitted!', 'success');
    } else {
      setFeedback('withdrawFeedback', data.message || 'Withdrawal failed.', 'error');
    }
  } catch(err) {
    setFeedback('withdrawFeedback', 'Network error. Please try again.', 'error');
  } finally {
    btn.disabled = false;
  }
});

// ── Transfer form ─────────────────────────────────────────────────────────────
document.getElementById('transferForm').addEventListener('submit', async e => {
  e.preventDefault();
  clearFeedback('transferFeedback');

  const body = new FormData();
  body.append('action',       'transfer');
  body.append('from_user_id', currentUser.id);
  body.append('user_id',      currentUser.id);
  body.append('to_user_id',   document.getElementById('tr_to_user_id').value);
  body.append('amount',       document.getElementById('tr_amount').value);
  body.append('description',  document.getElementById('tr_description').value);

  const btn = document.getElementById('transferBtn');
  btn.disabled = true;

  try {
    const res  = await fetch('wallet_data.php', { method: 'POST', body });
    const data = await res.json();
    if (data.success) {
      walletData.balance = data.from_balance;
      refreshBalanceDisplay();
      await initWallet();
      setFeedback('transferFeedback', `Transfer sent! New balance: ${fmtCurrency(data.from_balance)}`, 'success');
      document.getElementById('transferForm').reset();
      showToast('Transfer complete!', 'success');
    } else {
      setFeedback('transferFeedback', data.message || 'Transfer failed.', 'error');
    }
  } catch(err) {
    setFeedback('transferFeedback', 'Network error. Please try again.', 'error');
  } finally {
    btn.disabled = false;
  }
});

// ── Invoice line items ────────────────────────────────────────────────────────
function addLineItem() {
  const c = document.getElementById('lineItemsContainer');
  const row = document.createElement('div');
  row.className = 'line-item-row';
  row.style = 'display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:8px;align-items:end;margin-bottom:8px;';
  row.innerHTML = `
    <div class="form-group" style="margin-bottom:0;">
      <input class="form-control" type="text" name="li_desc[]" placeholder="Service description" required />
    </div>
    <div class="form-group" style="margin-bottom:0;">
      <input class="form-control" type="number" name="li_qty[]" min="0.01" step="0.01" value="1" required />
    </div>
    <div class="form-group" style="margin-bottom:0;">
      <input class="form-control" type="number" name="li_price[]" min="0" step="0.01" placeholder="0.00" required />
    </div>
    <button type="button" class="btn btn-outline btn-sm" onclick="removeLineItem(this)">✕</button>`;
  c.appendChild(row);
  row.querySelectorAll('input').forEach(i => i.addEventListener('input', updateInvoiceTotal));
}

function removeLineItem(btn) {
  const rows = document.querySelectorAll('.line-item-row');
  if (rows.length > 1) { btn.closest('.line-item-row').remove(); updateInvoiceTotal(); }
}

function updateInvoiceTotal() {
  let total = 0;
  document.querySelectorAll('.line-item-row').forEach(row => {
    const qty   = parseFloat(row.querySelector('[name="li_qty[]"]')?.value  || 0);
    const price = parseFloat(row.querySelector('[name="li_price[]"]')?.value || 0);
    total += qty * price;
  });
  document.getElementById('invoiceTotal').textContent = fmtCurrency(total);
}

document.getElementById('lineItemsContainer').addEventListener('input', updateInvoiceTotal);

// ── Create invoice form ───────────────────────────────────────────────────────
document.getElementById('invoiceCreateForm').addEventListener('submit', async e => {
  e.preventDefault();
  clearFeedback('invCreateFeedback');

  const lineItems = [];
  document.querySelectorAll('.line-item-row').forEach(row => {
    const desc  = row.querySelector('[name="li_desc[]"]')?.value.trim()  || '';
    const qty   = parseFloat(row.querySelector('[name="li_qty[]"]')?.value  || 0);
    const price = parseFloat(row.querySelector('[name="li_price[]"]')?.value || 0);
    if (desc) lineItems.push({ description: desc, quantity: qty, unit_price: price });
  });

  if (!lineItems.length) {
    setFeedback('invCreateFeedback', 'At least one line item is required.', 'error');
    return;
  }

  const body = new FormData();
  body.append('action',         'create');
  body.append('issuer_user_id', currentUser.id);
  body.append('payer_user_id',  document.getElementById('inv_payer').value);
  body.append('description',    document.getElementById('inv_description').value);
  body.append('due_date',       document.getElementById('inv_due_date').value);
  body.append('currency',       'USD');
  body.append('line_items',     JSON.stringify(lineItems));

  const btn = document.getElementById('createInvBtn');
  btn.disabled = true;

  try {
    const res  = await fetch('invoice_data.php', { method: 'POST', body });
    const data = await res.json();
    if (data.success) {
      setFeedback('invCreateFeedback', `Invoice ${data.invoice.id} created successfully.`, 'success');
      document.getElementById('invoiceCreateForm').reset();
      updateInvoiceTotal();
      loadInvoices();
      showToast('Invoice created!', 'success');
    } else {
      setFeedback('invCreateFeedback', data.message || 'Could not create invoice.', 'error');
    }
  } catch(err) {
    setFeedback('invCreateFeedback', 'Network error.', 'error');
  } finally {
    btn.disabled = false;
  }
});

// ── Load invoices ─────────────────────────────────────────────────────────────
async function loadInvoices() {
  const role   = document.getElementById('invFilterRole').value;
  const status = document.getElementById('invFilterStatus').value;
  const params = new URLSearchParams({
    action: 'list',
    user_id: currentUser.id,
    limit: '50', offset: '0',
  });
  if (role)   params.append('role',   role);
  if (status) params.append('status', status);

  try {
    const res  = await fetch('invoice_data.php?' + params.toString());
    const data = await res.json();
    if (data.success) renderInvoiceList(data.invoices || []);
  } catch(e) {}
}

function renderInvoiceList(invoices) {
  const el = document.getElementById('invoiceList');
  if (!invoices.length) {
    el.innerHTML = `<div style="text-align:center;padding:32px;color:var(--muted-foreground);">
      <iconify-icon icon="lucide:inbox" style="font-size:40px;display:block;margin:0 auto 12px;opacity:.4;"></iconify-icon>No invoices.</div>`;
    return;
  }
  const rows = invoices.map(inv => {
    const amtDue  = parseFloat(inv.amount_due  || 0);
    const amtPaid = parseFloat(inv.amount_paid || 0);
    const total   = parseFloat(inv.total_amount || 0);
    const canPay  = ['pending','partial','overdue'].includes(inv.status) && inv.payer_user_id === currentUser.id;
    return `<tr>
      <td style="font-weight:600;">${escHtml(inv.id)}</td>
      <td>${escHtml(inv.description || '—')}</td>
      <td>${fmtCurrency(total)}</td>
      <td style="color:#059669;">${fmtCurrency(amtPaid)}</td>
      <td style="color:#dc2626;font-weight:700;">${fmtCurrency(amtDue)}</td>
      <td>${inv.due_date || '—'}</td>
      <td><span class="inv-badge ${inv.status}">${inv.status}</span></td>
      <td>
        ${canPay ? `<button class="btn btn-primary btn-sm" onclick='openPayModal(${JSON.stringify(inv)})'>Pay</button>` : ''}
      </td>
    </tr>`;
  }).join('');
  el.innerHTML = `<table class="inv-table">
    <thead><tr><th>ID</th><th>Description</th><th>Total</th><th>Paid</th><th>Due</th><th>Due Date</th><th>Status</th><th></th></tr></thead>
    <tbody>${rows}</tbody>
  </table>`;
}

// ── Pay invoice modal ─────────────────────────────────────────────────────────
function openPayModal(invoice) {
  currentPayInvoice = invoice;
  document.getElementById('payInvoiceId').value = invoice.id;
  document.getElementById('payAmount').value     = parseFloat(invoice.amount_due || 0).toFixed(2);
  document.getElementById('payAmount').max        = parseFloat(invoice.amount_due || 0);
  document.getElementById('payInvFeedback').className = 'form-feedback';

  document.getElementById('payModalMeta').innerHTML = `
    <strong>${escHtml(invoice.id)}</strong><br>
    ${escHtml(invoice.description || '')}<br>
    <span style="color:var(--muted-foreground);">Amount due: </span><strong style="color:var(--primary);">${fmtCurrency(invoice.amount_due)}</strong>
    &nbsp;·&nbsp;
    <span style="color:var(--muted-foreground);">Wallet balance: </span><strong>${fmtCurrency(walletData?.balance || 0)}</strong>`;

  document.getElementById('payInvoiceModal').classList.add('open');
}

function closePayModal() {
  document.getElementById('payInvoiceModal').classList.remove('open');
  currentPayInvoice = null;
}

function togglePayCardSection() {
  const method = document.getElementById('payMethod').value;
  document.getElementById('payCardSection').style.display = method === 'card' ? '' : 'none';
}

document.getElementById('payInvoiceForm').addEventListener('submit', async e => {
  e.preventDefault();
  clearFeedback('payInvFeedback');

  const method    = document.getElementById('payMethod').value;
  const body      = new FormData();
  body.append('action',         'pay');
  body.append('invoice_id',     document.getElementById('payInvoiceId').value);
  body.append('payer_user_id',  currentUser.id);
  body.append('amount',         document.getElementById('payAmount').value);
  body.append('payment_method', method);

  if (method === 'card') {
    const cardRaw = document.getElementById('payCardNumber').value.replace(/\s/g, '');
    body.append('card_name',       document.getElementById('payCardName').value);
    body.append('card_last4',      cardRaw.slice(-4));
    body.append('card_expiry',     document.getElementById('payExpiry').value.replace(/\s/g, ''));
    body.append('billing_address', document.getElementById('payBilling').value);
  }

  const btn = document.getElementById('payInvSubmitBtn');
  btn.disabled = true;

  try {
    const res  = await fetch('invoice_data.php', { method: 'POST', body });
    const data = await res.json();
    if (data.success) {
      closePayModal();
      loadInvoices();
      if (data.wallet_balance !== undefined) {
        walletData.balance = data.wallet_balance;
        refreshBalanceDisplay();
        await initWallet();
      }
      showToast('Invoice paid successfully!', 'success');
    } else {
      setFeedback('payInvFeedback', data.message || 'Payment failed.', 'error');
    }
  } catch(err) {
    setFeedback('payInvFeedback', 'Network error.', 'error');
  } finally {
    btn.disabled = false;
  }
});

// ── Ledger ────────────────────────────────────────────────────────────────────
async function loadLedger() {
  const type   = document.getElementById('ledgerTypeFilter').value;
  const params = new URLSearchParams({
    action: 'list',
    user_id: currentUser.id,
    limit: String(LEDGER_LIMIT),
    offset: String(ledgerOffset),
  });
  if (type) params.append('type', type);

  try {
    const res  = await fetch('ledger_data.php?' + params.toString());
    const data = await res.json();
    if (data.success) renderLedger(data.entries || [], data.total || 0);
  } catch(e) {}
}

function renderLedger(entries, total) {
  const el = document.getElementById('ledgerList');
  if (!entries.length) {
    el.innerHTML = `<div style="text-align:center;padding:32px;color:var(--muted-foreground);">
      <iconify-icon icon="lucide:inbox" style="font-size:40px;display:block;margin:0 auto 12px;opacity:.4;"></iconify-icon>No ledger entries.</div>`;
    document.getElementById('ledgerPager').style.display = 'none';
    return;
  }
  const rows = entries.map(e => {
    const isCredit = e.type === 'credit';
    return `<tr>
      <td style="font-family:monospace;font-size:12px;">${escHtml(e.id)}</td>
      <td><span class="tx-status ${isCredit ? 'completed' : 'failed'}" style="font-size:11px;">${e.type}</span></td>
      <td>${escHtml(e.description || '—')}</td>
      <td style="font-weight:700;${isCredit ? 'color:#059669;' : 'color:#dc2626;'}">${isCredit ? '+' : '-'}${fmtCurrency(e.amount)} ${e.currency}</td>
      <td style="font-size:12px;color:var(--muted-foreground);">${e.timestamp || ''}</td>
    </tr>`;
  }).join('');
  el.innerHTML = `<table class="inv-table">
    <thead><tr><th>ID</th><th>Type</th><th>Description</th><th>Amount</th><th>Timestamp</th></tr></thead>
    <tbody>${rows}</tbody>
  </table>`;

  // Pager
  const pager = document.getElementById('ledgerPager');
  pager.style.display = total > LEDGER_LIMIT ? 'flex' : 'none';
  pager.innerHTML = '';
  if (ledgerOffset > 0) {
    const prev = document.createElement('button');
    prev.className = 'btn btn-outline btn-sm';
    prev.textContent = '← Previous';
    prev.onclick = () => { ledgerOffset -= LEDGER_LIMIT; loadLedger(); };
    pager.appendChild(prev);
  }
  if (ledgerOffset + LEDGER_LIMIT < total) {
    const next = document.createElement('button');
    next.className = 'btn btn-outline btn-sm';
    next.textContent = 'Next →';
    next.onclick = () => { ledgerOffset += LEDGER_LIMIT; loadLedger(); };
    pager.appendChild(next);
  }
}

async function loadLedgerSummary() {
  try {
    const res  = await fetch('ledger_data.php?action=summary&user_id=' + encodeURIComponent(currentUser.id));
    const data = await res.json();
    if (data.success) {
      document.getElementById('ledgerCredits').textContent = fmtCurrency(data.total_credits);
      document.getElementById('ledgerDebits').textContent  = fmtCurrency(data.total_debits);
      document.getElementById('ledgerNet').textContent     = fmtCurrency(data.net_balance);
    }
  } catch(e) {}
}

// ── XSS-safe escaping ─────────────────────────────────────────────────────────
function escHtml(s) {
  if (!s) return '';
  return String(s)
    .replace(/&/g,'&amp;')
    .replace(/</g,'&lt;')
    .replace(/>/g,'&gt;')
    .replace(/"/g,'&quot;')
    .replace(/'/g,'&#39;');
}

// Close modal on backdrop click
document.getElementById('payInvoiceModal').addEventListener('click', function(e) {
  if (e.target === this) closePayModal();
});
</script>
</body>
</html>
