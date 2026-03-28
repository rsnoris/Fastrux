<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <link rel="icon" href="favicon.svg" type="image/svg+xml" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Privacy &amp; Data — Fastrux Logistics</title>
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

    .page-content { padding: 32px 0; }

    /* Hero */
    .gdpr-hero {
      background: linear-gradient(135deg, var(--primary) 0%, #0950c7 100%);
      padding: 40px 0; margin-bottom: 32px; text-align: center;
    }
    .gdpr-hero h1 { font-size: 26px; font-weight: 800; color: #fff; margin-bottom: 8px; }
    .gdpr-hero p  { font-size: 15px; color: rgba(255,255,255,.85); max-width: 560px; margin: 0 auto; }

    /* Grid */
    .gdpr-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
      gap: 24px;
    }

    /* Card */
    .gdpr-card {
      background: var(--card); border: 1px solid var(--border);
      border-radius: var(--radius-xl); padding: 28px;
    }
    .gdpr-card-header {
      display: flex; align-items: center; gap: 12px; margin-bottom: 16px;
    }
    .gdpr-card-icon {
      width: 48px; height: 48px; border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 22px; flex-shrink: 0;
    }
    .gdpr-card-icon.blue   { background: #dbeafe; color: #1d4ed8; }
    .gdpr-card-icon.orange { background: #ffedd5; color: #c2410c; }
    .gdpr-card-icon.red    { background: #fee2e2; color: #b91c1c; }
    .gdpr-card-icon.green  { background: #dcfce7; color: #15803d; }

    .gdpr-card h2 { font-size: 17px; font-weight: 700; margin: 0; }
    .gdpr-card p  { font-size: 13px; color: var(--muted-foreground); margin-bottom: 18px; line-height: 1.6; }

    .form-field { margin-bottom: 14px; }
    .form-field label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; }
    .form-field input {
      width: 100%; padding: 9px 12px; font-size: 14px;
      border: 1px solid var(--border); border-radius: var(--radius-md);
      background: var(--input); color: var(--foreground); font-family: inherit;
      box-sizing: border-box;
    }
    .form-field input:focus {
      outline: none; border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(11,111,255,.12);
    }

    /* Warning box */
    .warning-box {
      background: #fff7ed; border: 1px solid #fed7aa;
      border-radius: var(--radius-md); padding: 14px 16px;
      font-size: 13px; color: #c2410c; margin-bottom: 16px;
      display: flex; gap: 10px; align-items: flex-start;
    }
    .warning-box iconify-icon { font-size: 18px; flex-shrink: 0; margin-top: 1px; }

    /* Toast */
    #toast {
      position: fixed; bottom: 24px; right: 24px;
      background: var(--foreground); color: #fff;
      padding: 12px 20px; border-radius: var(--radius-md);
      font-size: 14px; z-index: 9999; display: none;
      box-shadow: var(--shadow-xl); max-width: 360px;
    }
    #toast.show { display: block; animation: slideUp .3s ease; }
    #toast.success { background: var(--success); }
    #toast.error   { background: var(--destructive); }
    @keyframes slideUp { from { opacity:0; transform: translateY(16px); } to { opacity:1; transform: translateY(0); } }
  </style>
</head>
<body>

  <!-- Header -->
  <header class="dash-header">
    <div class="container dash-header-inner">
      <a href="index" class="dash-brand">
        <iconify-icon icon="lucide:truck" style="font-size:24px"></iconify-icon>
        Fastrux <span>&nbsp;/ Privacy &amp; Data</span>
      </a>
      <div style="display:flex;align-items:center;gap:10px;">
        <a href="account" class="btn btn-outline" style="padding:8px 14px;font-size:13px;">
          <iconify-icon icon="lucide:user" style="font-size:15px;margin-right:6px"></iconify-icon>
          My Account
        </a>
        <a href="index" class="btn btn-primary" style="padding:8px 14px;font-size:13px;">
          <iconify-icon icon="lucide:home" style="font-size:15px;margin-right:6px"></iconify-icon>
          Main Site
        </a>
      </div>
    </div>
  </header>

  <!-- Hero -->
  <div class="gdpr-hero">
    <div class="container">
      <h1>
        <iconify-icon icon="lucide:shield-check" style="vertical-align:middle;margin-right:8px;"></iconify-icon>
        Privacy &amp; Your Data Rights
      </h1>
      <p>Under GDPR and CCPA you have the right to access, export, and delete your personal data held by Fastrux. Use the tools below to exercise your rights.</p>
    </div>
  </div>

  <div class="page-content">
    <div class="container">
      <div class="gdpr-grid">

        <!-- Export my data -->
        <div class="gdpr-card">
          <div class="gdpr-card-header">
            <div class="gdpr-card-icon blue">
              <iconify-icon icon="lucide:download"></iconify-icon>
            </div>
            <h2>Download My Data</h2>
          </div>
          <p>Download a complete JSON archive of all personal data Fastrux holds about you — including your profile, messages, notifications, payments, and ratings.</p>
          <p style="font-size:12px;color:var(--muted-foreground);margin-bottom:18px;">
            Financial ledger records are retained for legal compliance obligations and are not included.
          </p>
          <button class="btn btn-primary" style="width:100%;" onclick="downloadMyData()">
            <iconify-icon icon="lucide:download" style="vertical-align:middle;margin-right:6px;"></iconify-icon>
            Download My Data Archive
          </button>
        </div>

        <!-- Withdraw consent -->
        <div class="gdpr-card">
          <div class="gdpr-card-header">
            <div class="gdpr-card-icon green">
              <iconify-icon icon="lucide:toggle-left"></iconify-icon>
            </div>
            <h2>Withdraw Consent</h2>
          </div>
          <p>Withdraw your consent for Fastrux to use your data for marketing and non-essential processing. Your account will remain active but you will no longer receive marketing communications.</p>
          <button class="btn btn-outline" style="width:100%;" onclick="withdrawConsent()">
            <iconify-icon icon="lucide:toggle-left" style="vertical-align:middle;margin-right:6px;"></iconify-icon>
            Withdraw Marketing Consent
          </button>
        </div>

        <!-- Delete my account -->
        <div class="gdpr-card" style="border-color:#fee2e2;">
          <div class="gdpr-card-header">
            <div class="gdpr-card-icon red">
              <iconify-icon icon="lucide:trash-2"></iconify-icon>
            </div>
            <h2>Delete My Account</h2>
          </div>
          <p>Permanently anonymise your account. Your name, email, and contact details will be erased. Your account cannot be recovered after this action.</p>
          <div class="warning-box">
            <iconify-icon icon="lucide:alert-triangle"></iconify-icon>
            <span>This action is <strong>irreversible</strong>. Financial records are retained for legal compliance. You will be logged out immediately.</span>
          </div>
          <div class="form-field">
            <label for="deleteConfirm">Type <code style="background:var(--muted);padding:1px 5px;border-radius:3px;">DELETE MY ACCOUNT</code> to confirm</label>
            <input type="text" id="deleteConfirm" placeholder="DELETE MY ACCOUNT" autocomplete="off" />
          </div>
          <button class="btn" style="width:100%;background:#b91c1c;color:#fff;" onclick="deleteAccount()">
            <iconify-icon icon="lucide:trash-2" style="vertical-align:middle;margin-right:6px;"></iconify-icon>
            Permanently Delete My Account
          </button>
        </div>

        <!-- Data we hold -->
        <div class="gdpr-card">
          <div class="gdpr-card-header">
            <div class="gdpr-card-icon orange">
              <iconify-icon icon="lucide:info"></iconify-icon>
            </div>
            <h2>Data We Hold About You</h2>
          </div>
          <p>Fastrux collects and processes the following categories of personal data:</p>
          <ul style="font-size:13px;color:var(--muted-foreground);line-height:1.8;padding-left:18px;margin-bottom:16px;">
            <li><strong>Identity data</strong> — name, email, phone number</li>
            <li><strong>Account data</strong> — role, status, registration date</li>
            <li><strong>Transaction data</strong> — payments, invoices, wallet activity</li>
            <li><strong>Communications</strong> — in-app messages, notifications</li>
            <li><strong>Usage data</strong> — audit log, IP addresses (anonymised after 90 days)</li>
            <li><strong>Load data</strong> — loads posted, bids, delivery history</li>
          </ul>
          <a href="privacy" class="btn btn-outline" style="width:100%;font-size:13px;">
            <iconify-icon icon="lucide:file-text" style="vertical-align:middle;margin-right:6px;"></iconify-icon>
            Read Full Privacy Policy
          </a>
        </div>

      </div>
    </div>
  </div>

  <div id="toast"></div>

  <script>
  (function () {
    'use strict';

    var API = 'gdpr_data.php';

    var user = null;
    try { user = JSON.parse(localStorage.getItem('fx_user')); } catch (e) {}
    if (!user || !user.id) { window.location.href = 'login'; return; }

    var userId = user.id;

    // ── Toast ─────────────────────────────────────────────
    function toast(msg, type) {
      var el = document.getElementById('toast');
      el.textContent = msg;
      el.className = 'show ' + (type || '');
      clearTimeout(el._t);
      el._t = setTimeout(function () { el.className = ''; }, 4000);
    }

    // ── Download my data ──────────────────────────────────
    window.downloadMyData = function () {
      var url = API + '?action=export_data&user_id=' + encodeURIComponent(userId) + '&actor_id=' + encodeURIComponent(userId);
      // Trigger download via anchor
      var a = document.createElement('a');
      a.href = url;
      a.download = 'fastrux_data_' + userId + '.json';
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      toast('Your data archive download has started.', 'success');
    };

    // ── Withdraw consent ──────────────────────────────────
    window.withdrawConsent = function () {
      if (!confirm('Withdraw your marketing and non-essential processing consent? Your account stays active.')) return;
      fetch(API, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'withdraw_consent', user_id: userId, actor_id: userId }),
      })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          if (data.success) {
            toast('Consent withdrawn. You will no longer receive marketing communications.', 'success');
          } else {
            toast(data.message || 'Failed to withdraw consent.', 'error');
          }
        })
        .catch(function () { toast('Network error.', 'error'); });
    };

    // ── Delete account ────────────────────────────────────
    window.deleteAccount = function () {
      var confirm_text = (document.getElementById('deleteConfirm').value || '').trim();
      if (confirm_text !== 'DELETE MY ACCOUNT') {
        toast('Please type exactly: DELETE MY ACCOUNT', 'error');
        return;
      }
      if (!confirm('This will permanently anonymise your account. This cannot be undone. Continue?')) return;

      var btn = document.querySelector('[onclick="deleteAccount()"]');
      if (btn) btn.disabled = true;

      fetch(API, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          action: 'request_deletion',
          user_id: userId,
          actor_id: userId,
          confirmation: 'DELETE MY ACCOUNT',
        }),
      })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          if (data.success) {
            toast('Account deleted. You will be logged out.', 'success');
            setTimeout(function () {
              localStorage.removeItem('fx_user');
              window.location.href = 'index';
            }, 2500);
          } else {
            if (btn) btn.disabled = false;
            toast(data.message || 'Failed to delete account.', 'error');
          }
        })
        .catch(function () {
          if (btn) btn.disabled = false;
          toast('Network error.', 'error');
        });
    };
  })();
  </script>
</body>
</html>
