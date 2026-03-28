<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <link rel="icon" href="favicon.svg" type="image/svg+xml" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Notifications — Fastrux Logistics</title>
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

    /* Toolbar */
    .ntf-toolbar {
      display: flex; align-items: center; justify-content: space-between;
      margin-bottom: 20px; gap: 12px; flex-wrap: wrap;
    }
    .ntf-toolbar h1 { font-size: 22px; font-weight: 800; }
    .ntf-toolbar-actions { display: flex; gap: 10px; align-items: center; }

    /* Filter tabs */
    .ntf-filter-tabs {
      display: flex; gap: 4px;
      background: var(--card); border: 1px solid var(--border);
      border-radius: var(--radius-md); padding: 4px;
      margin-bottom: 20px;
    }
    .ntf-filter-tab {
      padding: 7px 16px; border-radius: calc(var(--radius-md) - 2px);
      font-size: 13px; font-weight: 600; cursor: pointer;
      background: none; border: none; color: var(--muted-foreground);
      font-family: inherit; transition: background .15s, color .15s;
    }
    .ntf-filter-tab.active {
      background: var(--primary); color: #fff;
    }

    /* Notification card */
    .ntf-list { display: flex; flex-direction: column; gap: 10px; }
    .ntf-card {
      background: var(--card); border: 1px solid var(--border);
      border-radius: var(--radius-xl); padding: 18px 20px;
      display: flex; gap: 16px; align-items: flex-start;
      cursor: pointer; transition: box-shadow .15s, border-color .15s;
      text-decoration: none; color: inherit;
    }
    .ntf-card:hover { box-shadow: var(--shadow-md); border-color: var(--primary); }
    .ntf-card.unread { border-left: 3px solid var(--primary); }

    .ntf-icon-wrap {
      width: 44px; height: 44px; border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      flex-shrink: 0; font-size: 20px;
    }
    .ntf-icon-wrap.payment   { background: #dcfce7; color: #16a34a; }
    .ntf-icon-wrap.load      { background: #dbeafe; color: #1d4ed8; }
    .ntf-icon-wrap.document  { background: #fef9c3; color: #b45309; }
    .ntf-icon-wrap.message   { background: #f3e8ff; color: #7c3aed; }
    .ntf-icon-wrap.account   { background: #ffedd5; color: #c2410c; }
    .ntf-icon-wrap.rating    { background: #fef3c7; color: #d97706; }
    .ntf-icon-wrap.system    { background: var(--muted); color: var(--muted-foreground); }

    .ntf-content { flex: 1; min-width: 0; }
    .ntf-title {
      font-size: 14px; font-weight: 700; margin-bottom: 4px;
      white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .ntf-card.unread .ntf-title { color: var(--primary); }
    .ntf-body {
      font-size: 13px; color: var(--muted-foreground); line-height: 1.5;
      display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
      overflow: hidden;
    }
    .ntf-meta {
      font-size: 11px; color: var(--muted-foreground); margin-top: 6px;
      display: flex; align-items: center; gap: 8px;
    }
    .ntf-type-badge {
      display: inline-block; padding: 2px 8px;
      border-radius: 20px; font-size: 10px; font-weight: 700;
      background: var(--muted); color: var(--muted-foreground);
      text-transform: uppercase; letter-spacing: .5px;
    }
    .ntf-unread-dot {
      display: inline-block; width: 8px; height: 8px;
      background: var(--primary); border-radius: 50%; flex-shrink: 0;
      align-self: center;
    }

    /* Empty state */
    .ntf-empty {
      display: flex; flex-direction: column; align-items: center;
      justify-content: center; min-height: 280px;
      color: var(--muted-foreground); text-align: center; padding: 24px;
      background: var(--card); border: 1px solid var(--border);
      border-radius: var(--radius-xl);
    }
    .ntf-empty iconify-icon { font-size: 48px; opacity: .3; margin-bottom: 12px; }
    .ntf-empty p { font-size: 15px; }

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

    @media (max-width: 640px) {
      .ntf-toolbar { flex-direction: column; align-items: flex-start; }
    }
  </style>
</head>
<body>

  <!-- Header -->
  <header class="dash-header">
    <div class="container dash-header-inner">
      <a href="index" class="dash-brand">
        <iconify-icon icon="lucide:truck" style="font-size:24px"></iconify-icon>
        Fastrux <span>&nbsp;/ Notifications</span>
      </a>
      <div style="display:flex;align-items:center;gap:10px;">
        <a href="messages" class="btn btn-outline" style="padding:8px 14px;font-size:13px;">
          <iconify-icon icon="lucide:mail" style="font-size:15px;margin-right:6px"></iconify-icon>
          Messages
        </a>
        <a href="index" class="btn btn-primary" style="padding:8px 14px;font-size:13px;">
          <iconify-icon icon="lucide:home" style="font-size:15px;margin-right:6px"></iconify-icon>
          Main Site
        </a>
      </div>
    </div>
  </header>

  <div class="page-content">
    <div class="container">

      <!-- Toolbar -->
      <div class="ntf-toolbar">
        <h1>
          <iconify-icon icon="lucide:bell" style="vertical-align:middle;margin-right:8px;color:var(--primary)"></iconify-icon>
          Notifications
        </h1>
        <div class="ntf-toolbar-actions">
          <button class="btn btn-outline" style="font-size:13px;padding:8px 14px;" onclick="markAllRead()">
            <iconify-icon icon="lucide:check-check" style="vertical-align:middle;margin-right:6px;"></iconify-icon>
            Mark all read
          </button>
        </div>
      </div>

      <!-- Filter tabs -->
      <div class="ntf-filter-tabs">
        <button class="ntf-filter-tab active" id="filterAll" onclick="setFilter('all')">All</button>
        <button class="ntf-filter-tab" id="filterUnread" onclick="setFilter('unread')">Unread</button>
        <button class="ntf-filter-tab" id="filterPayment" onclick="setFilter('payment')">Payments</button>
        <button class="ntf-filter-tab" id="filterLoad" onclick="setFilter('load')">Loads</button>
        <button class="ntf-filter-tab" id="filterSystem" onclick="setFilter('system')">System</button>
      </div>

      <!-- List -->
      <div class="ntf-list" id="ntfList">
        <div class="ntf-empty">
          <iconify-icon icon="lucide:loader-circle"></iconify-icon>
          <p>Loading notifications…</p>
        </div>
      </div>

    </div>
  </div>

  <div id="toast"></div>

  <script>
  (function () {
    'use strict';

    var API           = 'notifications_data.php';
    var POLL_INTERVAL = 60000;

    var user = null;
    try { user = JSON.parse(localStorage.getItem('fx_user')); } catch (e) {}
    if (!user || !user.id) { window.location.href = 'login'; return; }

    var userId        = user.id;
    var notifications = [];
    var currentFilter = 'all';

    // ── Toast ─────────────────────────────────────────────
    function toast(msg, type) {
      var el = document.getElementById('toast');
      el.textContent = msg;
      el.className = 'show ' + (type || '');
      clearTimeout(el._t);
      el._t = setTimeout(function () { el.className = ''; }, 3500);
    }

    // ── Escape HTML ───────────────────────────────────────
    function esc(s) {
      return (s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // ── Format date ───────────────────────────────────────
    function fmtDate(dt) {
      if (!dt) return '';
      var d = new Date(dt.replace(' ', 'T'));
      var now = new Date();
      var diff = now - d;
      if (diff < 60000)    return 'just now';
      if (diff < 3600000)  return Math.floor(diff / 60000) + 'm ago';
      if (diff < 86400000) return Math.floor(diff / 3600000) + 'h ago';
      return dt.slice(0, 10);
    }

    // ── Map type → icon group ──────────────────────────────
    function typeGroup(type) {
      if (type.indexOf('payment') !== -1 || type.indexOf('wallet') !== -1 || type.indexOf('invoice') !== -1) return 'payment';
      if (type.indexOf('load') !== -1 || type.indexOf('driver') !== -1) return 'load';
      if (type.indexOf('document') !== -1) return 'document';
      if (type.indexOf('message') !== -1) return 'message';
      if (type.indexOf('account') !== -1) return 'account';
      if (type.indexOf('rating') !== -1) return 'rating';
      return 'system';
    }

    // ── Map type → icon ───────────────────────────────────
    function typeIcon(type) {
      var g = typeGroup(type);
      var icons = {
        payment:  'lucide:banknote',
        load:     'lucide:package',
        document: 'lucide:file-text',
        message:  'lucide:mail',
        account:  'lucide:user-check',
        rating:   'lucide:star',
        system:   'lucide:bell',
      };
      return icons[g] || 'lucide:bell';
    }

    // ── Map type → readable label ─────────────────────────
    function typeLabel(type) {
      return (type || 'system').replace(/_/g, ' ');
    }

    // ── Filter notifications ───────────────────────────────
    function filtered() {
      if (currentFilter === 'all')     return notifications;
      if (currentFilter === 'unread')  return notifications.filter(function (n) { return !n.read_at; });
      if (currentFilter === 'payment') return notifications.filter(function (n) { return typeGroup(n.type) === 'payment'; });
      if (currentFilter === 'load')    return notifications.filter(function (n) { return typeGroup(n.type) === 'load'; });
      if (currentFilter === 'system')  return notifications.filter(function (n) { return typeGroup(n.type) === 'system'; });
      return notifications;
    }

    // ── Set filter ────────────────────────────────────────
    window.setFilter = function (f) {
      currentFilter = f;
      ['filterAll','filterUnread','filterPayment','filterLoad','filterSystem'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) el.className = 'ntf-filter-tab' + (id === 'filter' + f.charAt(0).toUpperCase() + f.slice(1) ? ' active' : '');
      });
      render();
    };

    // ── Render list ───────────────────────────────────────
    function render() {
      var list = filtered();
      var el   = document.getElementById('ntfList');
      if (!list.length) {
        el.innerHTML =
          '<div class="ntf-empty">' +
            '<iconify-icon icon="lucide:bell-off"></iconify-icon>' +
            '<p>No notifications' + (currentFilter !== 'all' ? ' in this category' : '') + '.</p>' +
          '</div>';
        return;
      }
      el.innerHTML = list.map(function (n) {
        var grp   = typeGroup(n.type);
        var icon  = typeIcon(n.type);
        var label = typeLabel(n.type);
        var href  = n.link || '#';
        var unread = !n.read_at;
        return '<a class="ntf-card' + (unread ? ' unread' : '') + '" href="' + esc(href) + '"' +
          ' onclick="handleClick(event,\'' + esc(n.id) + '\',\'' + esc(href) + '\')">' +
          '<div class="ntf-icon-wrap ' + grp + '">' +
            '<iconify-icon icon="' + icon + '"></iconify-icon>' +
          '</div>' +
          '<div class="ntf-content">' +
            '<div class="ntf-title">' +
              (unread ? '<span class="ntf-unread-dot" style="display:inline-block;width:8px;height:8px;background:var(--primary);border-radius:50%;margin-right:6px;vertical-align:middle;"></span>' : '') +
              esc(n.title) +
            '</div>' +
            (n.body ? '<div class="ntf-body">' + esc(n.body) + '</div>' : '') +
            '<div class="ntf-meta">' +
              '<span class="ntf-type-badge">' + esc(label) + '</span>' +
              '<span>' + fmtDate(n.created_at) + '</span>' +
            '</div>' +
          '</div>' +
        '</a>';
      }).join('');
    }

    // ── Handle click (mark read then navigate) ────────────
    window.handleClick = function (e, notifId, href) {
      e.preventDefault();
      fetch(API, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'mark_read', user_id: userId, notif_id: notifId }),
      }).then(function () {
        var n = notifications.find(function (x) { return x.id === notifId; });
        if (n) n.read_at = new Date().toISOString();
        render();
        if (href && href !== '#') window.location.href = href;
      }).catch(function () {
        if (href && href !== '#') window.location.href = href;
      });
    };

    // ── Mark all read ──────────────────────────────────────
    window.markAllRead = function () {
      fetch(API, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'mark_all_read', user_id: userId }),
      }).then(function () {
        var now = new Date().toISOString();
        notifications.forEach(function (n) { if (!n.read_at) n.read_at = now; });
        render();
        toast('All notifications marked as read.', 'success');
      }).catch(function () { toast('Failed to update.', 'error'); });
    };

    // ── Load notifications ─────────────────────────────────
    function load() {
      fetch(API + '?action=list&user_id=' + encodeURIComponent(userId))
        .then(function (r) { return r.json(); })
        .then(function (data) {
          if (data.success) {
            notifications = data.notifications || [];
            render();
          }
        })
        .catch(function () { toast('Failed to load notifications.', 'error'); });
    }

    // ── Init ──────────────────────────────────────────────
    load();
    setInterval(load, POLL_INTERVAL);
  })();
  </script>
</body>
</html>
