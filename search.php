<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <link rel="icon" href="favicon.svg" type="image/svg+xml" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Search — Fastrux Logistics</title>
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

    /* Search bar */
    .search-hero {
      background: linear-gradient(135deg, var(--primary) 0%, #0950c7 100%);
      padding: 40px 0;
      margin-bottom: 32px;
    }
    .search-hero h1 {
      font-size: 26px; font-weight: 800; color: #fff;
      text-align: center; margin-bottom: 20px;
    }
    .search-bar-wrap {
      display: flex; gap: 10px; max-width: 680px; margin: 0 auto;
    }
    .search-input {
      flex: 1; padding: 13px 18px; font-size: 16px;
      border: none; border-radius: var(--radius-md);
      background: #fff; color: var(--foreground);
      font-family: inherit; outline: none;
      box-shadow: 0 4px 20px rgba(0,0,0,.2);
    }
    .search-btn {
      padding: 13px 22px; border: none; border-radius: var(--radius-md);
      background: var(--accent); color: var(--accent-foreground);
      font-weight: 700; font-size: 15px; cursor: pointer;
      font-family: inherit; white-space: nowrap;
      box-shadow: 0 4px 14px rgba(255,176,32,.4);
      transition: opacity .2s;
    }
    .search-btn:hover { opacity: .88; }

    /* Scope tabs */
    .scope-tabs {
      display: flex; gap: 4px;
      background: var(--card); border: 1px solid var(--border);
      border-radius: var(--radius-md); padding: 4px;
      margin-bottom: 24px; flex-wrap: wrap;
    }
    .scope-tab {
      padding: 7px 16px; border-radius: calc(var(--radius-md) - 2px);
      font-size: 13px; font-weight: 600; cursor: pointer;
      background: none; border: none; color: var(--muted-foreground);
      font-family: inherit; transition: background .15s, color .15s;
      display: flex; align-items: center; gap: 6px;
    }
    .scope-tab.active { background: var(--primary); color: #fff; }
    .scope-tab .count-chip {
      background: rgba(255,255,255,.25); color: inherit;
      padding: 0 6px; border-radius: 20px; font-size: 11px;
    }
    .scope-tab:not(.active) .count-chip {
      background: var(--muted); color: var(--muted-foreground);
    }

    /* Results */
    .results-header {
      font-size: 13px; color: var(--muted-foreground); margin-bottom: 16px;
    }
    .results-list { display: flex; flex-direction: column; gap: 10px; }

    .result-card {
      background: var(--card); border: 1px solid var(--border);
      border-radius: var(--radius-xl); padding: 18px 20px;
      display: flex; gap: 16px; align-items: flex-start;
      text-decoration: none; color: inherit;
      transition: box-shadow .15s, border-color .15s;
    }
    .result-card:hover { box-shadow: var(--shadow-md); border-color: var(--primary); }

    .result-icon {
      width: 44px; height: 44px; border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 20px; flex-shrink: 0;
    }
    .result-icon.load    { background: #dbeafe; color: #1d4ed8; }
    .result-icon.user    { background: #f3e8ff; color: #7c3aed; }
    .result-icon.message { background: #dcfce7; color: #16a34a; }
    .result-icon.rating  { background: #fef3c7; color: #d97706; }

    .result-body { flex: 1; min-width: 0; }
    .result-title {
      font-size: 15px; font-weight: 700; margin-bottom: 4px;
      white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .result-subtitle { font-size: 13px; color: var(--muted-foreground); line-height: 1.5; }
    .result-meta {
      font-size: 11px; color: var(--muted-foreground); margin-top: 6px;
      display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
    }
    .result-badge {
      display: inline-block; padding: 2px 8px; border-radius: 20px;
      font-size: 10px; font-weight: 700; background: var(--muted);
      color: var(--muted-foreground); text-transform: uppercase;
    }

    /* Empty / loading */
    .search-empty {
      text-align: center; padding: 60px 24px;
      color: var(--muted-foreground);
      background: var(--card); border: 1px solid var(--border);
      border-radius: var(--radius-xl);
    }
    .search-empty iconify-icon { font-size: 52px; opacity: .25; display: block; margin: 0 auto 14px; }
    .search-empty p { font-size: 15px; margin-bottom: 8px; }
    .search-empty small { font-size: 13px; }

    @media (max-width: 640px) {
      .search-bar-wrap { flex-direction: column; }
      .search-btn { width: 100%; }
    }
  </style>
</head>
<body>

  <!-- Header -->
  <header class="dash-header">
    <div class="container dash-header-inner">
      <a href="index" class="dash-brand">
        <iconify-icon icon="lucide:truck" style="font-size:24px"></iconify-icon>
        Fastrux <span>&nbsp;/ Search</span>
      </a>
      <div style="display:flex;align-items:center;gap:10px;">
        <a href="loadboard" class="btn btn-outline" style="padding:8px 14px;font-size:13px;">
          <iconify-icon icon="lucide:package" style="font-size:15px;margin-right:6px"></iconify-icon>
          Loadboard
        </a>
        <a href="messages" class="btn btn-primary" style="padding:8px 14px;font-size:13px;">
          <iconify-icon icon="lucide:mail" style="font-size:15px;margin-right:6px"></iconify-icon>
          Messages
        </a>
      </div>
    </div>
  </header>

  <!-- Search hero -->
  <div class="search-hero">
    <div class="container">
      <h1>
        <iconify-icon icon="lucide:search" style="vertical-align:middle;margin-right:8px;"></iconify-icon>
        Search Fastrux
      </h1>
      <div class="search-bar-wrap">
        <input type="text" class="search-input" id="searchInput"
               placeholder="Search loads, messages, ratings…"
               maxlength="200" autofocus autocomplete="off" />
        <button class="search-btn" onclick="runSearch()">
          <iconify-icon icon="lucide:search" style="vertical-align:middle;margin-right:6px;"></iconify-icon>
          Search
        </button>
      </div>
    </div>
  </div>

  <div class="page-content">
    <div class="container">

      <!-- Scope tabs (hidden until first search) -->
      <div id="scopeSection" style="display:none;">
        <div class="scope-tabs" id="scopeTabs">
          <button class="scope-tab active" id="scopeAll"     onclick="setScope('all')">All <span class="count-chip" id="cntAll">0</span></button>
          <button class="scope-tab"        id="scopeLoads"   onclick="setScope('loads')">Loads <span class="count-chip" id="cntLoads">0</span></button>
          <button class="scope-tab"        id="scopeUsers"   onclick="setScope('users')" id="tabUsers" style="display:none;">Users <span class="count-chip" id="cntUsers">0</span></button>
          <button class="scope-tab"        id="scopeMessages" onclick="setScope('messages')">Messages <span class="count-chip" id="cntMessages">0</span></button>
          <button class="scope-tab"        id="scopeRatings" onclick="setScope('ratings')">Ratings <span class="count-chip" id="cntRatings">0</span></button>
        </div>
        <div class="results-header" id="resultsHeader"></div>
      </div>

      <!-- Results container -->
      <div id="resultsContainer">
        <div class="search-empty">
          <iconify-icon icon="lucide:search"></iconify-icon>
          <p>Search across loads, messages, ratings and more.</p>
          <small>Type at least 2 characters and press Search.</small>
        </div>
      </div>

    </div>
  </div>

  <script>
  (function () {
    'use strict';

    var API = 'search_data.php';

    var user = null;
    try { user = JSON.parse(localStorage.getItem('fx_user')); } catch (e) {}
    if (!user || !user.id) { window.location.href = 'login'; return; }

    var userId      = user.id;
    var role        = user.role || 'shipper';
    var isAdmin     = role === 'admin' || role === 'super_admin';
    var lastResults = null;
    var currentScope = 'all';

    // Show users tab for admins
    if (isAdmin) {
      var uTab = document.getElementById('tabUsers');
      if (uTab) uTab.style.display = '';
    }

    // ── Escape HTML ───────────────────────────────────────
    function esc(s) {
      return (s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // ── Format date ───────────────────────────────────────
    function fmtDate(dt) { return dt ? dt.slice(0,16).replace('T',' ') : ''; }

    // ── Star string ───────────────────────────────────────
    function stars(score) {
      var s = Math.max(0, Math.min(5, parseInt(score, 10) || 0));
      return '★'.repeat(s) + '☆'.repeat(5 - s);
    }

    // ── Set scope and re-render ────────────────────────────
    window.setScope = function (scope) {
      currentScope = scope;
      ['All','Loads','Users','Messages','Ratings'].forEach(function (n) {
        var el = document.getElementById('scope' + n);
        if (el) el.className = 'scope-tab' + (scope === n.toLowerCase() ? ' active' : '');
      });
      if (lastResults) renderResults(lastResults, scope);
    };

    // ── Build result card ──────────────────────────────────
    function buildCard(item) {
      var type = item._type;
      var href = '#';
      var icon, title, subtitle, meta;

      if (type === 'load') {
        href     = 'loadboard.php';
        icon     = '<iconify-icon icon="lucide:package"></iconify-icon>';
        title    = item.title || item.id;
        subtitle = [item.pickup_city, item.pickup_state].filter(Boolean).join(', ') +
                   (item.delivery_city ? ' → ' + [item.delivery_city, item.delivery_state].filter(Boolean).join(', ') : '') +
                   (item.commodity ? ' · ' + item.commodity : '');
        meta     = '<span class="result-badge">' + esc(item.status || 'open') + '</span>' +
                   (item.rate ? '<span>$' + parseFloat(item.rate).toLocaleString() + '</span>' : '') +
                   (item.posted_at ? '<span>' + fmtDate(item.posted_at) + '</span>' : '');
      } else if (type === 'user') {
        href     = 'admin-dashboard.php';
        icon     = '<iconify-icon icon="lucide:user"></iconify-icon>';
        var name = [item.first_name || item.name, item.last_name].filter(Boolean).join(' ');
        title    = name || item.id;
        subtitle = (item.email ? item.email : '') + (item.company_name ? ' · ' + item.company_name : '');
        meta     = '<span class="result-badge">' + esc(item.role || '') + '</span>' +
                   '<span>' + esc(item.id || '') + '</span>';
      } else if (type === 'message') {
        href     = 'messages.php';
        icon     = '<iconify-icon icon="lucide:mail"></iconify-icon>';
        title    = item.subject || '(no subject)';
        subtitle = 'From: ' + esc(item.sender_name || '') + ' → ' + esc(item.recipient_name || '');
        meta     = (item.read_at ? '<span class="result-badge">read</span>' : '<span class="result-badge" style="background:#dbeafe;color:#1d4ed8;">unread</span>') +
                   '<span>' + fmtDate(item.sent_at) + '</span>';
      } else if (type === 'rating') {
        href     = 'ratings.php';
        icon     = '<iconify-icon icon="lucide:star"></iconify-icon>';
        title    = 'Rating: ' + stars(item.score);
        subtitle = item.comment || '(no comment)';
        meta     = '<span>' + esc(item.rater_id) + ' → ' + esc(item.ratee_id) + '</span>' +
                   (item.load_id ? '<span>Load: ' + esc(item.load_id) + '</span>' : '') +
                   '<span>' + fmtDate(item.created_at) + '</span>';
      } else {
        return '';
      }

      return '<a class="result-card" href="' + esc(href) + '">' +
        '<div class="result-icon ' + type + '">' + icon + '</div>' +
        '<div class="result-body">' +
          '<div class="result-title">' + esc(title) + '</div>' +
          (subtitle ? '<div class="result-subtitle">' + esc(subtitle) + '</div>' : '') +
          '<div class="result-meta">' + meta + '</div>' +
        '</div>' +
      '</a>';
    }

    // ── Render results ─────────────────────────────────────
    function renderResults(data, scope) {
      var totals = data.totals || {};
      // Update count chips
      document.getElementById('cntAll').textContent      = totals.total || 0;
      document.getElementById('cntLoads').textContent    = totals.loads || 0;
      if (document.getElementById('cntUsers'))
        document.getElementById('cntUsers').textContent  = totals.users || 0;
      document.getElementById('cntMessages').textContent = totals.messages || 0;
      document.getElementById('cntRatings').textContent  = totals.ratings || 0;

      var results = data.results || {};
      var items = [];
      if (scope === 'all') {
        items = (results.loads || []).concat(results.users || [])
                  .concat(results.messages || []).concat(results.ratings || []);
      } else {
        items = results[scope] || [];
      }

      var total = scope === 'all' ? (totals.total || 0) : items.length;
      document.getElementById('resultsHeader').textContent =
        total + ' result' + (total !== 1 ? 's' : '') + ' for "' + data.query + '"';

      var container = document.getElementById('resultsContainer');
      if (!items.length) {
        container.innerHTML =
          '<div class="search-empty">' +
            '<iconify-icon icon="lucide:search-x"></iconify-icon>' +
            '<p>No results found for "' + esc(data.query) + '".</p>' +
            '<small>Try a different search term or broader scope.</small>' +
          '</div>';
        return;
      }

      container.innerHTML = '<div class="results-list">' +
        items.map(buildCard).join('') +
      '</div>';
    }

    // ── Run search ─────────────────────────────────────────
    window.runSearch = function () {
      var q = (document.getElementById('searchInput').value || '').trim();
      if (q.length < 2) {
        document.getElementById('resultsContainer').innerHTML =
          '<div class="search-empty"><iconify-icon icon="lucide:alert-circle"></iconify-icon>' +
          '<p>Please enter at least 2 characters.</p></div>';
        return;
      }

      document.getElementById('resultsContainer').innerHTML =
        '<div class="search-empty"><iconify-icon icon="lucide:loader-circle"></iconify-icon><p>Searching…</p></div>';
      document.getElementById('scopeSection').style.display = 'none';

      var url = API + '?q=' + encodeURIComponent(q) + '&user_id=' + encodeURIComponent(userId) + '&scope=all';
      fetch(url)
        .then(function (r) { return r.json(); })
        .then(function (data) {
          if (!data.success) {
            document.getElementById('resultsContainer').innerHTML =
              '<div class="search-empty"><iconify-icon icon="lucide:alert-triangle"></iconify-icon>' +
              '<p>' + esc(data.message || 'Search failed.') + '</p></div>';
            return;
          }
          lastResults = data;
          document.getElementById('scopeSection').style.display = '';
          renderResults(data, currentScope);
        })
        .catch(function () {
          document.getElementById('resultsContainer').innerHTML =
            '<div class="search-empty"><iconify-icon icon="lucide:wifi-off"></iconify-icon>' +
            '<p>Network error. Please try again.</p></div>';
        });
    };

    // ── Enter key ─────────────────────────────────────────
    document.getElementById('searchInput').addEventListener('keydown', function (e) {
      if (e.key === 'Enter') runSearch();
    });

    // ── Pre-fill from URL ?q= ─────────────────────────────
    var urlParams = new URLSearchParams(window.location.search);
    var preQ = urlParams.get('q');
    if (preQ) {
      document.getElementById('searchInput').value = preQ;
      runSearch();
    }
  })();
  </script>
</body>
</html>
