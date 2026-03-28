<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <link rel="icon" href="favicon.svg" type="image/svg+xml" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Ratings &amp; Reviews — Fastrux Logistics</title>
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

    /* Layout */
    .rtg-layout {
      display: grid;
      grid-template-columns: 1fr 340px;
      gap: 24px;
      align-items: start;
    }

    /* Toolbar */
    .rtg-toolbar {
      display: flex; align-items: center; justify-content: space-between;
      margin-bottom: 20px; flex-wrap: wrap; gap: 12px;
    }
    .rtg-toolbar h1 { font-size: 22px; font-weight: 800; }

    /* Tab buttons */
    .rtg-tabs {
      display: flex; gap: 4px;
      background: var(--card); border: 1px solid var(--border);
      border-radius: var(--radius-md); padding: 4px;
      margin-bottom: 20px;
    }
    .rtg-tab {
      padding: 7px 18px; border-radius: calc(var(--radius-md) - 2px);
      font-size: 13px; font-weight: 600; cursor: pointer;
      background: none; border: none; color: var(--muted-foreground);
      font-family: inherit; transition: background .15s, color .15s;
    }
    .rtg-tab.active { background: var(--primary); color: #fff; }

    /* Summary card */
    .rtg-summary-card {
      background: var(--card); border: 1px solid var(--border);
      border-radius: var(--radius-xl); padding: 24px;
      margin-bottom: 24px; text-align: center;
    }
    .rtg-big-score {
      font-size: 56px; font-weight: 900; color: var(--primary);
      line-height: 1; margin-bottom: 4px;
    }
    .rtg-stars { font-size: 28px; letter-spacing: 2px; margin-bottom: 8px; }
    .rtg-star-filled  { color: #f59e0b; }
    .rtg-star-empty   { color: var(--border); }
    .rtg-total { font-size: 13px; color: var(--muted-foreground); }

    /* Breakdown bars */
    .rtg-breakdown { margin-top: 20px; display: flex; flex-direction: column; gap: 6px; }
    .rtg-bar-row { display: flex; align-items: center; gap: 8px; font-size: 12px; }
    .rtg-bar-label { width: 16px; text-align: right; color: var(--muted-foreground); font-weight: 600; }
    .rtg-bar-track {
      flex: 1; height: 8px; background: var(--muted);
      border-radius: 4px; overflow: hidden;
    }
    .rtg-bar-fill { height: 100%; background: #f59e0b; border-radius: 4px; transition: width .4s; }
    .rtg-bar-count { width: 24px; text-align: right; color: var(--muted-foreground); }

    /* Submit rating card */
    .rtg-submit-card {
      background: var(--card); border: 1px solid var(--border);
      border-radius: var(--radius-xl); padding: 24px;
    }
    .rtg-submit-card h3 {
      font-size: 16px; font-weight: 700; margin-bottom: 16px;
      display: flex; align-items: center; gap: 8px;
    }
    .form-field { margin-bottom: 14px; }
    .form-field label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; }
    .form-field input, .form-field select, .form-field textarea {
      width: 100%; padding: 9px 12px; font-size: 14px;
      border: 1px solid var(--border); border-radius: var(--radius-md);
      background: var(--input); color: var(--foreground); font-family: inherit;
      box-sizing: border-box;
    }
    .form-field input:focus, .form-field select:focus, .form-field textarea:focus {
      outline: none; border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(11,111,255,.12);
    }
    .form-field textarea { resize: vertical; min-height: 90px; }

    /* Star selector */
    .star-selector { display: flex; gap: 4px; margin-top: 2px; }
    .star-btn {
      font-size: 28px; cursor: pointer; background: none; border: none;
      color: var(--border); transition: color .1s, transform .1s;
      padding: 0; line-height: 1;
    }
    .star-btn.selected, .star-btn:hover { color: #f59e0b; transform: scale(1.15); }

    /* Review list */
    .rtg-list { display: flex; flex-direction: column; gap: 12px; }
    .rtg-card {
      background: var(--card); border: 1px solid var(--border);
      border-radius: var(--radius-xl); padding: 20px;
    }
    .rtg-card-header {
      display: flex; align-items: center; justify-content: space-between;
      margin-bottom: 8px; flex-wrap: wrap; gap: 8px;
    }
    .rtg-card-rater { font-size: 13px; font-weight: 600; }
    .rtg-card-date  { font-size: 11px; color: var(--muted-foreground); }
    .rtg-card-stars { font-size: 18px; letter-spacing: 1px; margin-bottom: 6px; }
    .rtg-card-comment {
      font-size: 14px; color: var(--muted-foreground); line-height: 1.6;
    }
    .rtg-card-load {
      display: inline-block; margin-top: 8px; font-size: 11px;
      background: var(--muted); color: var(--muted-foreground);
      padding: 2px 8px; border-radius: 20px;
    }

    /* Empty state */
    .rtg-empty {
      display: flex; flex-direction: column; align-items: center;
      justify-content: center; min-height: 220px;
      color: var(--muted-foreground); text-align: center; padding: 24px;
      background: var(--card); border: 1px solid var(--border);
      border-radius: var(--radius-xl);
    }
    .rtg-empty iconify-icon { font-size: 48px; opacity: .3; margin-bottom: 12px; }
    .rtg-empty p { font-size: 15px; }

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

    @media (max-width: 900px) {
      .rtg-layout { grid-template-columns: 1fr; }
    }
    @media (max-width: 640px) {
      .rtg-toolbar { flex-direction: column; align-items: flex-start; }
    }
  </style>
</head>
<body>

  <!-- Header -->
  <header class="dash-header">
    <div class="container dash-header-inner">
      <a href="index" class="dash-brand">
        <iconify-icon icon="lucide:truck" style="font-size:24px"></iconify-icon>
        Fastrux <span>&nbsp;/ Ratings &amp; Reviews</span>
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
      <div class="rtg-toolbar">
        <h1>
          <iconify-icon icon="lucide:star" style="vertical-align:middle;margin-right:8px;color:#f59e0b"></iconify-icon>
          Ratings &amp; Reviews
        </h1>
      </div>

      <!-- Tab buttons -->
      <div class="rtg-tabs">
        <button class="rtg-tab active" id="tabReceived" onclick="switchTab('received')">Received</button>
        <button class="rtg-tab" id="tabGiven"    onclick="switchTab('given')">Given</button>
      </div>

      <!-- Two-column layout -->
      <div class="rtg-layout">

        <!-- Left: reviews list -->
        <div>
          <div class="rtg-list" id="rtgList">
            <div class="rtg-empty">
              <iconify-icon icon="lucide:loader-circle"></iconify-icon>
              <p>Loading reviews…</p>
            </div>
          </div>
        </div>

        <!-- Right: summary + submit form -->
        <div>

          <!-- Summary card -->
          <div class="rtg-summary-card" id="rtgSummary">
            <div class="rtg-big-score" id="summaryScore">—</div>
            <div class="rtg-stars" id="summaryStars"></div>
            <div class="rtg-total" id="summaryTotal">No ratings yet</div>
            <div class="rtg-breakdown" id="summaryBreakdown"></div>
          </div>

          <!-- Submit rating card -->
          <div class="rtg-submit-card">
            <h3>
              <iconify-icon icon="lucide:pencil-line" style="color:var(--primary)"></iconify-icon>
              Submit a Rating
            </h3>
            <div class="form-field">
              <label for="rtgRatee">Rate a user (ID)</label>
              <input type="text" id="rtgRatee" placeholder="USR-XXXXXXXX" maxlength="12" />
            </div>
            <div class="form-field">
              <label for="rtgLoadId">Load ID (optional)</label>
              <input type="text" id="rtgLoadId" placeholder="e.g. LOAD-00001" maxlength="100" />
            </div>
            <div class="form-field">
              <label>Score</label>
              <div class="star-selector" id="starSelector">
                <button class="star-btn" data-val="1" onclick="selectStar(1)" title="1 star">★</button>
                <button class="star-btn" data-val="2" onclick="selectStar(2)" title="2 stars">★</button>
                <button class="star-btn" data-val="3" onclick="selectStar(3)" title="3 stars">★</button>
                <button class="star-btn" data-val="4" onclick="selectStar(4)" title="4 stars">★</button>
                <button class="star-btn" data-val="5" onclick="selectStar(5)" title="5 stars">★</button>
              </div>
            </div>
            <div class="form-field">
              <label for="rtgComment">Comment (optional)</label>
              <textarea id="rtgComment" placeholder="Share your experience…" maxlength="1000"></textarea>
            </div>
            <button class="btn btn-primary" style="width:100%;" onclick="submitRating()">
              <iconify-icon icon="lucide:send" style="vertical-align:middle;margin-right:6px;"></iconify-icon>
              Submit Rating
            </button>
          </div>

        </div>
      </div><!-- /.rtg-layout -->

    </div>
  </div>

  <div id="toast"></div>

  <script>
  (function () {
    'use strict';

    var API = 'ratings_data.php';

    var user = null;
    try { user = JSON.parse(localStorage.getItem('fx_user')); } catch (e) {}
    if (!user || !user.id) { window.location.href = 'login'; return; }

    var userId     = user.id;
    var ratings    = [];
    var currentTab = 'received';
    var selectedScore = 0;

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
    function fmtDate(dt) { return dt ? dt.slice(0, 16).replace('T', ' ') : ''; }

    // ── Render stars (filled / empty) ─────────────────────
    function renderStars(score, total) {
      var out = '';
      for (var i = 1; i <= total; i++) {
        out += '<span class="' + (i <= score ? 'rtg-star-filled' : 'rtg-star-empty') + '">★</span>';
      }
      return out;
    }

    // ── Star selector ─────────────────────────────────────
    window.selectStar = function (val) {
      selectedScore = val;
      document.querySelectorAll('.star-btn').forEach(function (btn) {
        var v = parseInt(btn.getAttribute('data-val'), 10);
        btn.className = 'star-btn' + (v <= val ? ' selected' : '');
      });
    };

    // ── Switch tab ────────────────────────────────────────
    window.switchTab = function (tab) {
      currentTab = tab;
      document.getElementById('tabReceived').className = 'rtg-tab' + (tab === 'received' ? ' active' : '');
      document.getElementById('tabGiven').className    = 'rtg-tab' + (tab === 'given'    ? ' active' : '');
      loadRatings();
    };

    // ── Load ratings ──────────────────────────────────────
    function loadRatings() {
      var url;
      if (currentTab === 'received') {
        url = API + '?action=get_ratings&user_id=' + encodeURIComponent(userId);
      } else {
        url = API + '?action=get_given&user_id=' + encodeURIComponent(userId);
      }
      fetch(url)
        .then(function (r) { return r.json(); })
        .then(function (data) {
          if (data.success) {
            ratings = data.ratings || [];
            renderList();
            if (currentTab === 'received' && data.summary) {
              renderSummary(data.summary);
            } else if (currentTab === 'given') {
              loadSummary();
            }
          }
        })
        .catch(function () { toast('Failed to load ratings.', 'error'); });
    }

    // ── Load summary separately (for "given" tab) ─────────
    function loadSummary() {
      fetch(API + '?action=get_summary&user_id=' + encodeURIComponent(userId))
        .then(function (r) { return r.json(); })
        .then(function (data) { if (data.success && data.summary) renderSummary(data.summary); })
        .catch(function () {});
    }

    // ── Render summary ────────────────────────────────────
    function renderSummary(s) {
      var scoreEl     = document.getElementById('summaryScore');
      var starsEl     = document.getElementById('summaryStars');
      var totalEl     = document.getElementById('summaryTotal');
      var breakdownEl = document.getElementById('summaryBreakdown');

      if (!s.total) {
        scoreEl.textContent = '—';
        starsEl.innerHTML   = '';
        totalEl.textContent = 'No ratings yet';
        breakdownEl.innerHTML = '';
        return;
      }
      var avg = parseFloat(s.average) || 0;
      scoreEl.textContent = avg.toFixed(1);
      starsEl.innerHTML   = renderStars(Math.round(avg), 5);
      totalEl.textContent = s.total + ' review' + (s.total !== 1 ? 's' : '');

      var bd = s.breakdown || {};
      var maxCount = Math.max.apply(null, [1,2,3,4,5].map(function(k){ return bd[k] || 0; }));
      breakdownEl.innerHTML = [5,4,3,2,1].map(function (star) {
        var cnt = bd[star] || 0;
        var pct = maxCount > 0 ? Math.round(cnt / maxCount * 100) : 0;
        return '<div class="rtg-bar-row">' +
          '<span class="rtg-bar-label">' + star + '</span>' +
          '<span style="font-size:12px;color:#f59e0b;">★</span>' +
          '<div class="rtg-bar-track"><div class="rtg-bar-fill" style="width:' + pct + '%"></div></div>' +
          '<span class="rtg-bar-count">' + cnt + '</span>' +
        '</div>';
      }).join('');
    }

    // ── Render review list ────────────────────────────────
    function renderList() {
      var el = document.getElementById('rtgList');
      if (!ratings.length) {
        el.innerHTML =
          '<div class="rtg-empty">' +
            '<iconify-icon icon="lucide:star-off"></iconify-icon>' +
            '<p>No reviews ' + (currentTab === 'received' ? 'received' : 'given') + ' yet.</p>' +
          '</div>';
        return;
      }
      el.innerHTML = ratings.map(function (r) {
        var score   = Math.max(0, Math.min(5, parseInt(r.score, 10) || 0));
        var nameKey = currentTab === 'received' ? r.rater_id : r.ratee_id;
        var label   = currentTab === 'received' ? 'From: ' : 'To: ';
        return '<div class="rtg-card">' +
          '<div class="rtg-card-header">' +
            '<span class="rtg-card-rater">' + label + esc(nameKey) + '</span>' +
            '<span class="rtg-card-date">' + fmtDate(r.created_at) + '</span>' +
          '</div>' +
          '<div class="rtg-card-stars">' + renderStars(score, 5) + '</div>' +
          (r.comment ? '<div class="rtg-card-comment">' + esc(r.comment) + '</div>' : '') +
          (r.load_id ? '<span class="rtg-card-load">Load: ' + esc(r.load_id) + '</span>' : '') +
        '</div>';
      }).join('');
    }

    // ── Submit rating ─────────────────────────────────────
    window.submitRating = function () {
      var rateeId = (document.getElementById('rtgRatee').value || '').trim().toUpperCase();
      var loadId  = (document.getElementById('rtgLoadId').value || '').trim();
      var comment = (document.getElementById('rtgComment').value || '').trim();

      if (!rateeId) { toast('Please enter a user ID to rate.', 'error'); return; }
      if (!/^USR-[A-Z0-9]{8}$/.test(rateeId)) { toast('Invalid user ID format (USR-XXXXXXXX).', 'error'); return; }
      if (rateeId === userId)  { toast('You cannot rate yourself.', 'error'); return; }
      if (!selectedScore)      { toast('Please select a score (1–5 stars).', 'error'); return; }

      var btn = document.querySelector('.rtg-submit-card .btn-primary');
      if (btn) btn.disabled = true;

      fetch(API, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          action:   'create_rating',
          rater_id: userId,
          ratee_id: rateeId,
          load_id:  loadId,
          score:    selectedScore,
          comment:  comment,
        }),
      })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          if (btn) btn.disabled = false;
          if (data.success) {
            toast('Rating submitted!', 'success');
            document.getElementById('rtgRatee').value   = '';
            document.getElementById('rtgLoadId').value  = '';
            document.getElementById('rtgComment').value = '';
            selectedScore = 0;
            document.querySelectorAll('.star-btn').forEach(function (b) { b.className = 'star-btn'; });
            if (currentTab === 'received') loadRatings();
          } else {
            toast(data.message || 'Failed to submit rating.', 'error');
          }
        })
        .catch(function () {
          if (btn) btn.disabled = false;
          toast('Network error.', 'error');
        });
    };

    // ── Init ──────────────────────────────────────────────
    loadRatings();
  })();
  </script>
</body>
</html>
