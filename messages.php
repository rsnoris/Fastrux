<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <link rel="icon" href="favicon.svg" type="image/svg+xml" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Messages — Fastrux Logistics</title>
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

    /* Layout: sidebar + main */
    .msg-layout {
      display: grid;
      grid-template-columns: 280px 1fr;
      gap: 20px;
      align-items: start;
    }

    /* Sidebar */
    .msg-sidebar {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius-xl);
      overflow: hidden;
    }
    .msg-sidebar-header {
      padding: 16px 20px;
      border-bottom: 1px solid var(--border);
      display: flex; align-items: center; justify-content: space-between;
    }
    .msg-sidebar-header h3 { font-size: 14px; font-weight: 700; }
    .unread-badge {
      background: var(--primary); color: #fff;
      font-size: 11px; font-weight: 700;
      padding: 2px 7px; border-radius: 20px;
      display: none;
    }
    .tab-btns {
      display: flex; border-bottom: 1px solid var(--border);
    }
    .tab-btn {
      flex: 1; padding: 10px; font-size: 13px; font-weight: 600;
      background: none; border: none; cursor: pointer;
      color: var(--muted-foreground); border-bottom: 2px solid transparent;
      font-family: inherit; transition: color .2s;
    }
    .tab-btn.active {
      color: var(--primary); border-bottom-color: var(--primary);
    }
    .msg-list { max-height: 500px; overflow-y: auto; }
    .msg-item {
      padding: 14px 20px;
      border-bottom: 1px solid var(--border);
      cursor: pointer;
      transition: background .15s;
    }
    .msg-item:hover, .msg-item.active { background: var(--muted); }
    .msg-item.unread .msg-item-subject { font-weight: 700; }
    .msg-item-subject { font-size: 13px; color: var(--foreground); margin-bottom: 2px;
      white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .msg-item-meta { font-size: 11px; color: var(--muted-foreground); display: flex; gap: 6px; }
    .msg-item-from { font-weight: 600; }
    .msg-unread-dot {
      display: inline-block; width: 8px; height: 8px;
      background: var(--primary); border-radius: 50%;
      margin-right: 6px; vertical-align: middle;
    }
    .msg-empty { text-align: center; padding: 32px 16px; color: var(--muted-foreground); font-size: 13px; }

    /* Compose button */
    .compose-btn {
      display: block; width: calc(100% - 32px);
      margin: 16px; padding: 10px;
      text-align: center; font-size: 13px;
    }

    /* Main panel */
    .msg-main {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius-xl);
      min-height: 520px;
    }

    /* Empty / placeholder */
    .msg-placeholder {
      display: flex; flex-direction: column; align-items: center; justify-content: center;
      min-height: 400px; color: var(--muted-foreground); text-align: center; padding: 24px;
    }
    .msg-placeholder iconify-icon { font-size: 48px; opacity: .3; margin-bottom: 12px; }
    .msg-placeholder p { font-size: 15px; }

    /* Message detail */
    .msg-detail { padding: 28px; }
    .msg-detail-subject { font-size: 20px; font-weight: 700; margin-bottom: 12px; }
    .msg-detail-meta { font-size: 13px; color: var(--muted-foreground); margin-bottom: 20px; }
    .msg-detail-body {
      font-size: 15px; line-height: 1.7; color: var(--foreground);
      background: var(--muted); padding: 20px; border-radius: var(--radius-lg);
      white-space: pre-wrap; word-break: break-word;
    }
    .msg-detail-actions { margin-top: 20px; display: flex; gap: 10px; }

    /* Compose form */
    .compose-form { padding: 28px; }
    .compose-form h2 { font-size: 18px; font-weight: 700; margin-bottom: 20px; }
    .form-field { margin-bottom: 16px; }
    .form-field label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; }
    .form-field input, .form-field select, .form-field textarea {
      width: 100%; padding: 9px 12px; font-size: 14px;
      border: 1px solid var(--border); border-radius: var(--radius-md);
      background: var(--input); color: var(--foreground); font-family: inherit;
    }
    .form-field input:focus, .form-field select:focus, .form-field textarea:focus {
      outline: none; border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(11,111,255,.12);
    }
    .form-field textarea { resize: vertical; min-height: 140px; }

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

    @media (max-width: 768px) {
      .msg-layout { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>

  <!-- Header -->
  <header class="dash-header">
    <div class="container dash-header-inner">
      <a href="/" class="dash-brand">
        <iconify-icon icon="lucide:truck" style="font-size:24px"></iconify-icon>
        Fastrux <span>&nbsp;/ Messages</span>
      </a>
      <div style="display:flex;align-items:center;gap:10px;">
        <a href="documents" class="btn btn-outline" style="padding:8px 14px;font-size:13px;">
          <iconify-icon icon="lucide:file-text" style="font-size:15px;margin-right:6px"></iconify-icon>
          Documents
        </a>
        <a href="/" class="btn btn-primary" style="padding:8px 14px;font-size:13px;">
          <iconify-icon icon="lucide:home" style="font-size:15px;margin-right:6px"></iconify-icon>
          Main Site
        </a>
      </div>
    </div>
  </header>

  <div class="page-content">
    <div class="container">
      <div class="msg-layout">

        <!-- Sidebar -->
        <div class="msg-sidebar">
          <div class="msg-sidebar-header">
            <h3>Inbox</h3>
            <span class="unread-badge" id="unreadBadge">0</span>
          </div>
          <div class="tab-btns">
            <button class="tab-btn active" id="tabInbox" onclick="switchTab('inbox')">Inbox</button>
            <button class="tab-btn" id="tabSent" onclick="switchTab('sent')">Sent</button>
          </div>
          <div class="msg-list" id="msgList">
            <div class="msg-empty">Loading…</div>
          </div>
          <button class="btn btn-primary compose-btn" onclick="showCompose()">
            <iconify-icon icon="lucide:pencil" style="vertical-align:middle;margin-right:6px;"></iconify-icon>
            New Message
          </button>
        </div>

        <!-- Main panel -->
        <div class="msg-main" id="msgMain">
          <div class="msg-placeholder">
            <iconify-icon icon="lucide:mail"></iconify-icon>
            <p>Select a message to read or compose a new one.</p>
          </div>
        </div>

      </div>
    </div>
  </div>

  <div id="toast"></div>

  <script>
  (function () {
    'use strict';

    var API          = 'messages_data.php';
    var UAPI         = 'admin_api.php';
    var POLL_INTERVAL = 60000; // ms — how often to refresh messages

    var user = null;
    try { user = JSON.parse(localStorage.getItem('fx_user')); } catch (e) {}
    if (!user || !user.id) { window.location.href = 'login'; return; }

    var userId   = user.id;
    var messages = [];
    var currentTab = 'inbox';
    var allUsers   = [];

    // ── Toast ──────────────────────────────────────────────
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
    function fmtDate(dt) { return dt ? dt.slice(0, 16).replace('T',' ') : ''; }

    // ── Load users (for compose recipient list) ───────────
    function loadUsers() {
      fetch(UAPI + '?action=users&requesting_user_id=' + encodeURIComponent(userId))
        .then(function (r) { return r.json(); })
        .then(function (data) {
          if (data.success && data.users) {
            allUsers = data.users.filter(function (u) { return u.id !== userId; });
          }
        })
        .catch(function () {});
    }

    // ── Switch inbox/sent tab ─────────────────────────────
    window.switchTab = function (tab) {
      currentTab = tab;
      document.getElementById('tabInbox').className = 'tab-btn' + (tab === 'inbox' ? ' active' : '');
      document.getElementById('tabSent').className  = 'tab-btn' + (tab === 'sent'  ? ' active' : '');
      loadMessages();
    };

    // ── Load messages ─────────────────────────────────────
    function loadMessages() {
      var url = API + '?action=' + currentTab + '&user_id=' + encodeURIComponent(userId);
      fetch(url)
        .then(function (r) { return r.json(); })
        .then(function (data) {
          messages = (data.messages || []);
          renderList();
          updateUnreadCount();
        })
        .catch(function () { toast('Failed to load messages.', 'error'); });
    }

    // ── Update unread badge ───────────────────────────────
    function updateUnreadCount() {
      fetch(API + '?action=unread_count&user_id=' + encodeURIComponent(userId))
        .then(function (r) { return r.json(); })
        .then(function (data) {
          var cnt = data.unread_count || 0;
          var badge = document.getElementById('unreadBadge');
          badge.textContent = cnt;
          badge.style.display = cnt > 0 ? 'inline-block' : 'none';
        })
        .catch(function () {});
    }

    // ── Render sidebar list ───────────────────────────────
    function renderList() {
      var el = document.getElementById('msgList');
      if (!messages.length) {
        el.innerHTML = '<div class="msg-empty">No messages.</div>';
        return;
      }
      el.innerHTML = messages.map(function (m) {
        var isUnread = currentTab === 'inbox' && !m.read_at;
        var other = currentTab === 'inbox' ? m.sender_name : m.recipient_name;
        var subj  = m.subject || '(no subject)';
        return '<div class="msg-item' + (isUnread ? ' unread' : '') + '" onclick="openMessage(\'' + esc(m.id) + '\')">' +
          '<div class="msg-item-subject">' + (isUnread ? '<span class="msg-unread-dot"></span>' : '') + esc(subj) + '</div>' +
          '<div class="msg-item-meta">' +
            '<span class="msg-item-from">' + esc(other) + '</span>' +
            '<span>' + fmtDate(m.sent_at) + '</span>' +
          '</div>' +
        '</div>';
      }).join('');
    }

    // ── Open a message ────────────────────────────────────
    window.openMessage = function (msgId) {
      var m = messages.find(function (x) { return x.id === msgId; });
      if (!m) return;

      // Mark as read if inbox + unread
      if (currentTab === 'inbox' && !m.read_at) {
        fetch(API, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ action: 'mark_read', msg_id: msgId, user_id: userId }),
        }).then(function () { m.read_at = new Date().toISOString(); renderList(); updateUnreadCount(); })
          .catch(function () {});
      }

      var other = currentTab === 'inbox' ? m.sender_id : m.recipient_id;
      var otherName = currentTab === 'inbox' ? m.sender_name : m.recipient_name;
      var metaFrom = currentTab === 'inbox'
        ? 'From: ' + esc(m.sender_name) + ' &nbsp;·&nbsp; ' + fmtDate(m.sent_at)
        : 'To: ' + esc(m.recipient_name) + ' &nbsp;·&nbsp; ' + fmtDate(m.sent_at) + (m.read_at ? ' &nbsp;·&nbsp; Read' : ' &nbsp;·&nbsp; Unread');

      document.getElementById('msgMain').innerHTML =
        '<div class="msg-detail">' +
          '<div class="msg-detail-subject">' + esc(m.subject || '(no subject)') + '</div>' +
          '<div class="msg-detail-meta">' + metaFrom + '</div>' +
          '<div class="msg-detail-body">' + esc(m.body) + '</div>' +
          '<div class="msg-detail-actions">' +
            (currentTab === 'inbox'
              ? '<button class="btn btn-primary" onclick="showReply(\'' + esc(other) + '\',\'' + esc(otherName) + '\',\'Re: ' + esc(m.subject || '') + '\')">Reply</button>'
              : '') +
            '<button class="btn btn-outline" onclick="showCompose()">New Message</button>' +
          '</div>' +
        '</div>';
    };

    // ── Show compose ──────────────────────────────────────
    window.showCompose = function (recipientId, recipientName, subject) {
      var recipientsOptions = allUsers.map(function (u) {
        var name = (u.first_name || u.name || '') + ' ' + (u.last_name || '');
        var selected = (u.id === recipientId) ? ' selected' : '';
        return '<option value="' + esc(u.id) + '"' + selected + '>' + esc(name.trim() || u.id) + ' (' + esc(u.role || '') + ')</option>';
      }).join('');

      if (!recipientsOptions) {
        recipientsOptions = '<option value="">Loading users…</option>';
      }

      document.getElementById('msgMain').innerHTML =
        '<div class="compose-form">' +
          '<h2><iconify-icon icon="lucide:pencil" style="vertical-align:middle;margin-right:8px;color:var(--primary)"></iconify-icon>New Message</h2>' +
          '<div class="form-field">' +
            '<label for="cmpTo">To</label>' +
            '<select id="cmpTo">' +
              '<option value="">— Select recipient —</option>' +
              recipientsOptions +
            '</select>' +
          '</div>' +
          '<div class="form-field">' +
            '<label for="cmpSubject">Subject</label>' +
            '<input type="text" id="cmpSubject" placeholder="Message subject" maxlength="200" value="' + esc(subject || '') + '" />' +
          '</div>' +
          '<div class="form-field">' +
            '<label for="cmpBody">Message</label>' +
            '<textarea id="cmpBody" placeholder="Write your message here…" maxlength="5000"></textarea>' +
          '</div>' +
          '<div style="display:flex;gap:10px;">' +
            '<button class="btn btn-primary" onclick="sendMessage()">Send Message</button>' +
            '<button class="btn btn-outline" onclick="cancelCompose()">Cancel</button>' +
          '</div>' +
        '</div>';
    };

    // ── Show reply ────────────────────────────────────────
    window.showReply = function (recipientId, recipientName, subject) {
      showCompose(recipientId, recipientName, subject);
    };

    // ── Cancel compose ────────────────────────────────────
    window.cancelCompose = function () {
      document.getElementById('msgMain').innerHTML =
        '<div class="msg-placeholder"><iconify-icon icon="lucide:mail"></iconify-icon><p>Select a message to read or compose a new one.</p></div>';
    };

    // ── Send message ──────────────────────────────────────
    window.sendMessage = function () {
      var to      = (document.getElementById('cmpTo') || {}).value || '';
      var subject = (document.getElementById('cmpSubject') || {}).value.trim();
      var body    = (document.getElementById('cmpBody') || {}).value.trim();

      if (!to)   { toast('Please select a recipient.', 'error'); return; }
      if (!body) { toast('Please write a message.', 'error'); return; }

      var btn = document.querySelector('.compose-form .btn-primary');
      if (btn) btn.disabled = true;

      fetch(API, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'send', sender_id: userId, recipient_id: to, subject: subject, body: body }),
      })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          if (btn) btn.disabled = false;
          if (data.success) {
            toast('Message sent!', 'success');
            if (currentTab === 'sent') loadMessages();
            cancelCompose();
          } else {
            toast(data.message || 'Failed to send.', 'error');
          }
        })
        .catch(function () {
          if (btn) btn.disabled = false;
          toast('Network error.', 'error');
        });
    };

    // ── Mark all read ─────────────────────────────────────
    function markAllRead() {
      fetch(API, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'mark_all_read', user_id: userId }),
      }).then(function () { loadMessages(); }).catch(function () {});
    }

    // ── Init ──────────────────────────────────────────────
    loadUsers();
    loadMessages();

    // Auto-refresh every 60 seconds
    setInterval(loadMessages, POLL_INTERVAL);
  })();
  </script>
</body>
</html>
