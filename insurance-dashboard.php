<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <link rel="icon" href="favicon.svg" type="image/svg+xml" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Insurance Dashboard — Fastrux Marketplace</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="shared.css" />
  <script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>
  <style>
    body { background: var(--muted); }
    .dash-header {
      background: var(--card); border-bottom: 1px solid var(--border);
      padding: 0; position: sticky; top: 0; z-index: 100;
    }
    .dash-header-inner {
      display: flex; align-items: center; justify-content: space-between; height: 64px;
    }
    .dash-brand {
      display: flex; align-items: center; gap: 10px;
      font-size: 18px; font-weight: 800; color: var(--primary); text-decoration: none;
    }
    .dash-brand span { color: var(--foreground); font-weight: 400; font-size: 14px; }
    .dash-badge {
      display: inline-flex; align-items: center; gap: 5px;
      background: #eff6ff; border: 1px solid #bfdbfe;
      color: #1d4ed8; border-radius: 12px;
      padding: 3px 10px; font-size: 12px; font-weight: 600;
    }
    .stats-grid {
      display: grid; grid-template-columns: repeat(4, 1fr);
      gap: 20px; margin-bottom: 28px;
    }
    @media(max-width:900px) { .stats-grid { grid-template-columns: repeat(2,1fr); } }
    @media(max-width:500px) { .stats-grid { grid-template-columns: 1fr; } }
    .stat-card {
      background: var(--card); border: 1px solid var(--border);
      border-radius: var(--radius-xl); padding: 24px;
      display: flex; align-items: center; gap: 16px;
    }
    .stat-icon {
      width: 48px; height: 48px; min-width: 48px;
      border-radius: var(--radius-md);
      display: flex; align-items: center; justify-content: center; font-size: 22px;
    }
    .stat-icon.blue   { background: var(--secondary); color: var(--primary); }
    .stat-icon.green  { background: #e6f9ee; color: var(--success); }
    .stat-icon.amber  { background: #fff7e6; color: #d97706; }
    .stat-icon.purple { background: #f3e8ff; color: #7c3aed; }
    .stat-label { font-size: 13px; color: var(--muted-foreground); font-weight: 500; margin-bottom: 4px; }
    .stat-value { font-size: 28px; font-weight: 800; line-height: 1; }

    .section-card {
      background: var(--card); border: 1px solid var(--border);
      border-radius: var(--radius-xl); margin-bottom: 24px; overflow: hidden;
    }
    .section-header {
      padding: 20px 24px; border-bottom: 1px solid var(--border);
      display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;
    }
    .section-title { font-size: 16px; font-weight: 700; }

    /* Table */
    .data-table { width: 100%; border-collapse: collapse; }
    .data-table th {
      text-align: left; padding: 12px 16px;
      font-size: 12px; font-weight: 600; color: var(--muted-foreground);
      text-transform: uppercase; letter-spacing: .05em;
      border-bottom: 1px solid var(--border); background: var(--muted);
    }
    .data-table td {
      padding: 14px 16px; font-size: 14px;
      border-bottom: 1px solid var(--border); vertical-align: middle;
    }
    .data-table tr:last-child td { border-bottom: none; }
    .data-table tbody tr:hover { background: var(--muted); }

    /* Badge */
    .badge {
      display: inline-flex; align-items: center; gap: 4px;
      padding: 2px 10px; border-radius: 20px;
      font-size: 12px; font-weight: 600;
    }
    .badge.active   { background: #e6f9ee; color: #16a34a; }
    .badge.inactive { background: #f3f4f6; color: #6b7280; }
    .badge.paused   { background: #fff7e6; color: #d97706; }

    .coverage-tag {
      display: inline-block;
      background: #eff6ff; color: #1d4ed8;
      border-radius: 4px; padding: 1px 7px; font-size: 11px; font-weight: 600;
      margin: 1px 2px;
    }

    @keyframes spin { to { transform: rotate(360deg); } }
    .empty-state { text-align: center; padding: 60px 24px; color: var(--muted-foreground); }
    .empty-state iconify-icon { font-size: 48px; display: block; margin: 0 auto 12px; }

    /* Modal */
    .modal-overlay {
      position: fixed; inset: 0; background: rgba(0,0,0,.5);
      display: none; align-items: center; justify-content: center; z-index: 1000; padding: 20px;
    }
    .modal-overlay.open { display: flex; }
    .modal {
      background: var(--card); border-radius: var(--radius-xl);
      max-width: 580px; width: 100%; max-height: 90vh; overflow-y: auto;
      box-shadow: 0 20px 60px rgba(0,0,0,.2);
    }
    .modal-header {
      padding: 20px 24px; border-bottom: 1px solid var(--border);
      display: flex; align-items: center; justify-content: space-between;
    }
    .modal-title { font-size: 16px; font-weight: 700; }
    .modal-close {
      background: none; border: none; cursor: pointer; font-size: 20px;
      color: var(--muted-foreground); line-height: 1;
    }
    .modal-body  { padding: 24px; }
    .modal-footer{ padding: 16px 24px; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; gap: 12px; }

    .coverage-check-grid {
      display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: 6px;
    }
    .coverage-check-grid label {
      display: flex; align-items: center; gap: 8px;
      font-size: 14px; font-weight: 400; cursor: pointer;
    }
  </style>
</head>
<body>

  <!-- Dashboard header -->
  <header class="dash-header">
    <div class="container dash-header-inner">
      <a href="index" class="dash-brand">
        <iconify-icon icon="lucide:truck" style="font-size:24px"></iconify-icon>
        Fastrux <span>/ Insurance Dashboard</span>
      </a>
      <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
        <span class="dash-badge"><iconify-icon icon="lucide:shield-check"></iconify-icon> Insurance Partner</span>
        <a href="messages" class="btn btn-outline" style="padding:8px 16px;font-size:14px;">
          <iconify-icon icon="lucide:message-circle" style="margin-right:6px;font-size:15px;vertical-align:middle;"></iconify-icon>Messages
        </a>
        <a href="documents" class="btn btn-outline" style="padding:8px 16px;font-size:14px;">
          <iconify-icon icon="lucide:file-text" style="margin-right:6px;font-size:15px;vertical-align:middle;"></iconify-icon>Documents
        </a>
        <a href="marketplace" class="btn btn-outline" style="padding:8px 16px;font-size:14px;">
          <iconify-icon icon="lucide:store" style="margin-right:6px;font-size:15px;vertical-align:middle;"></iconify-icon>Marketplace
        </a>
        <span id="userGreeting" style="font-size:14px;color:var(--muted-foreground);"></span>
        <button onclick="logout()" class="btn btn-outline" style="padding:8px 16px;font-size:14px;color:var(--destructive);border-color:var(--destructive);">
          <iconify-icon icon="lucide:log-out" style="margin-right:4px;font-size:14px;vertical-align:middle;"></iconify-icon>Logout
        </button>
      </div>
    </div>
  </header>

  <div class="container" style="padding-top:32px;padding-bottom:48px;">

    <!-- Stats -->
    <div class="stats-grid" id="statsGrid">
      <div class="stat-card">
        <div class="stat-icon blue"><iconify-icon icon="lucide:list"></iconify-icon></div>
        <div><div class="stat-label">Total Listings</div><div class="stat-value" id="statTotal">—</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon green"><iconify-icon icon="lucide:check-circle"></iconify-icon></div>
        <div><div class="stat-label">Active Listings</div><div class="stat-value" id="statActive">—</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon amber"><iconify-icon icon="lucide:pause-circle"></iconify-icon></div>
        <div><div class="stat-label">Paused</div><div class="stat-value" id="statPaused">—</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon purple"><iconify-icon icon="lucide:shield"></iconify-icon></div>
        <div><div class="stat-label">Coverage Types</div><div class="stat-value" id="statCoverage">—</div></div>
      </div>
    </div>

    <!-- Listings table -->
    <div class="section-card">
      <div class="section-header">
        <span class="section-title">My Insurance Listings</span>
        <button class="btn btn-primary" onclick="openModal()" style="padding:9px 18px;font-size:14px;">
          <iconify-icon icon="lucide:plus" style="margin-right:6px;font-size:15px;vertical-align:middle;"></iconify-icon>New Listing
        </button>
      </div>
      <div id="listingsContainer">
        <div class="empty-state">
          <iconify-icon icon="lucide:loader-circle" style="animation:spin 1s linear infinite;color:var(--primary);"></iconify-icon>
          Loading listings…
        </div>
      </div>
    </div>

  </div>

  <!-- Create / Edit Listing Modal -->
  <div class="modal-overlay" id="listingModal">
    <div class="modal">
      <div class="modal-header">
        <span class="modal-title" id="modalTitle">New Insurance Listing</span>
        <button class="modal-close" onclick="closeModal()">✕</button>
      </div>
      <div class="modal-body">
        <div class="form-feedback" id="modalFeedback"></div>
        <form id="listingForm">
          <input type="hidden" id="listingId" name="listing_id" value="" />
          <div class="form-group">
            <label for="lTitle">Listing Title *</label>
            <input class="form-control" type="text" id="lTitle" name="title" placeholder="e.g. Spot Cargo Insurance — Southeast US" required />
          </div>
          <div class="form-group">
            <label for="lDesc">Description</label>
            <textarea class="form-control" id="lDesc" name="description" rows="3"
              placeholder="Describe the coverage, eligibility, and terms…" style="resize:vertical;"></textarea>
          </div>
          <div class="form-group">
            <label>Coverage Types *</label>
            <div class="coverage-check-grid">
              <label><input type="checkbox" name="coverage_types[]" value="cargo" style="accent-color:var(--primary);"> Cargo Insurance</label>
              <label><input type="checkbox" name="coverage_types[]" value="liability" style="accent-color:var(--primary);"> Liability</label>
              <label><input type="checkbox" name="coverage_types[]" value="physical_damage" style="accent-color:var(--primary);"> Physical Damage</label>
              <label><input type="checkbox" name="coverage_types[]" value="workers_comp" style="accent-color:var(--primary);"> Workers' Comp</label>
              <label><input type="checkbox" name="coverage_types[]" value="general_liability" style="accent-color:var(--primary);"> General Liability</label>
              <label><input type="checkbox" name="coverage_types[]" value="occupational_accident" style="accent-color:var(--primary);"> Occupational Accident</label>
              <label><input type="checkbox" name="coverage_types[]" value="bobtail" style="accent-color:var(--primary);"> Bobtail</label>
              <label><input type="checkbox" name="coverage_types[]" value="non_trucking" style="accent-color:var(--primary);"> Non-Trucking Liability</label>
            </div>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="form-group">
              <label for="lMinCov">Min Coverage Amount</label>
              <input class="form-control" type="text" id="lMinCov" name="min_coverage" placeholder="e.g. $50,000" />
            </div>
            <div class="form-group">
              <label for="lMaxCov">Max Coverage Amount</label>
              <input class="form-control" type="text" id="lMaxCov" name="max_coverage" placeholder="e.g. $1,000,000" />
            </div>
          </div>
          <div class="form-group">
            <label for="lPremium">Premium Range</label>
            <input class="form-control" type="text" id="lPremium" name="premium_range" placeholder="e.g. $500–$2,000 / load" />
          </div>
          <div class="form-group">
            <label for="lArea">Service Area</label>
            <input class="form-control" type="text" id="lArea" name="service_area" placeholder="e.g. Nationwide, Southeast US" />
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="form-group">
              <label for="lEmail">Contact Email</label>
              <input class="form-control" type="email" id="lEmail" name="contact_email" placeholder="claims@company.com" />
            </div>
            <div class="form-group">
              <label for="lPhone">Contact Phone</label>
              <input class="form-control" type="tel" id="lPhone" name="contact_phone" placeholder="+1 (555) 000-0000" />
            </div>
          </div>
          <div class="form-group">
            <label for="lWebsite">Website</label>
            <input class="form-control" type="url" id="lWebsite" name="website" placeholder="https://example.com" />
          </div>
          <div class="form-group">
            <label for="lNotes">Additional Notes</label>
            <textarea class="form-control" id="lNotes" name="notes" rows="2"
              placeholder="Any extra information for potential clients…" style="resize:vertical;"></textarea>
          </div>
          <div class="form-group" id="statusGroup" style="display:none;">
            <label for="lStatus">Status</label>
            <select class="form-control" id="lStatus" name="status">
              <option value="active">Active</option>
              <option value="paused">Paused</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline" onclick="closeModal()" style="padding:10px 20px;">Cancel</button>
        <button class="btn btn-primary" id="saveBtn" onclick="saveListing()" style="padding:10px 20px;">
          Save Listing
        </button>
      </div>
    </div>
  </div>

  <script>
    // ── Auth guard ────────────────────────────────────────────
    var user = null;
    try { user = JSON.parse(localStorage.getItem('fx_user')); } catch(e){}
    if (!user || !user.id || user.role !== 'insurance_company') {
      window.location.href = 'insurance-login?redirect=' + encodeURIComponent(window.location.href);
    }

    document.getElementById('userGreeting').textContent = 'Hi, ' + (user.first_name || user.email);

    // ── State ─────────────────────────────────────────────────
    var listings = [];

    // ── Load listings ─────────────────────────────────────────
    function loadListings() {
      fetch('marketplace_data.php?action=my_listings&user_id=' + encodeURIComponent(user.id) + '&type=insurance')
        .then(r => r.json())
        .then(data => {
          listings = data.listings || [];
          renderListings();
          renderStats();
        })
        .catch(() => {
          document.getElementById('listingsContainer').innerHTML =
            '<div class="empty-state"><iconify-icon icon="lucide:alert-circle"></iconify-icon>Failed to load listings.</div>';
        });
    }

    function renderStats() {
      var total  = listings.length;
      var active = listings.filter(l => l.status === 'active').length;
      var paused = listings.filter(l => l.status === 'paused').length;
      var types  = new Set();
      listings.forEach(l => (l.coverage_types||[]).forEach(t => types.add(t)));

      document.getElementById('statTotal').textContent   = total;
      document.getElementById('statActive').textContent  = active;
      document.getElementById('statPaused').textContent  = paused;
      document.getElementById('statCoverage').textContent= types.size;
    }

    const COVERAGE_LABELS = {
      cargo: 'Cargo', liability: 'Liability', physical_damage: 'Physical Damage',
      workers_comp: "Workers' Comp", general_liability: 'General Liability',
      occupational_accident: 'Occupational Accident', bobtail: 'Bobtail', non_trucking: 'Non-Trucking'
    };

    function renderListings() {
      var container = document.getElementById('listingsContainer');
      if (!listings.length) {
        container.innerHTML = '<div class="empty-state"><iconify-icon icon="lucide:file-plus"></iconify-icon>No listings yet. Create your first insurance listing.</div>';
        return;
      }
      var html = '<div style="overflow-x:auto;"><table class="data-table"><thead><tr>' +
        '<th>Title</th><th>Coverage Types</th><th>Premium Range</th><th>Service Area</th><th>Status</th><th>Created</th><th>Actions</th>' +
        '</tr></thead><tbody>';
      listings.forEach(function(l) {
        var tags = (l.coverage_types||[]).map(t => '<span class="coverage-tag">' + (COVERAGE_LABELS[t]||t) + '</span>').join('');
        var badge = '<span class="badge ' + (l.status||'active') + '">' + (l.status||'active') + '</span>';
        var date  = l.created_at ? l.created_at.split(' ')[0] : '—';
        html += '<tr>' +
          '<td><strong>' + esc(l.title) + '</strong>' + (l.service_area ? '<br><small style="color:var(--muted-foreground);">' + esc(l.service_area) + '</small>' : '') + '</td>' +
          '<td>' + (tags || '—') + '</td>' +
          '<td>' + esc(l.premium_range || '—') + '</td>' +
          '<td>' + esc(l.service_area || '—') + '</td>' +
          '<td>' + badge + '</td>' +
          '<td style="white-space:nowrap;">' + date + '</td>' +
          '<td style="white-space:nowrap;">' +
            '<button class="btn btn-outline" onclick="editListing(\'' + l.id + '\')" style="padding:6px 12px;font-size:12px;margin-right:6px;">Edit</button>' +
            '<button class="btn btn-outline" onclick="deleteListing(\'' + l.id + '\')" style="padding:6px 12px;font-size:12px;color:var(--destructive);border-color:var(--destructive);">Delete</button>' +
          '</td>' +
        '</tr>';
      });
      html += '</tbody></table></div>';
      container.innerHTML = html;
    }

    function esc(str) {
      var d = document.createElement('div'); d.textContent = str || ''; return d.innerHTML;
    }

    // ── Modal ─────────────────────────────────────────────────
    function openModal(listingData) {
      document.getElementById('listingId').value  = '';
      document.getElementById('listingForm').reset();
      document.getElementById('statusGroup').style.display = 'none';
      document.getElementById('modalTitle').textContent = 'New Insurance Listing';
      document.getElementById('modalFeedback').style.display = 'none';

      if (listingData) {
        document.getElementById('modalTitle').textContent = 'Edit Insurance Listing';
        document.getElementById('listingId').value  = listingData.id;
        document.getElementById('lTitle').value     = listingData.title      || '';
        document.getElementById('lDesc').value      = listingData.description|| '';
        document.getElementById('lMinCov').value    = listingData.min_coverage|| '';
        document.getElementById('lMaxCov').value    = listingData.max_coverage|| '';
        document.getElementById('lPremium').value   = listingData.premium_range|| '';
        document.getElementById('lArea').value      = listingData.service_area|| '';
        document.getElementById('lEmail').value     = listingData.contact_email|| '';
        document.getElementById('lPhone').value     = listingData.contact_phone|| '';
        document.getElementById('lWebsite').value   = listingData.website    || '';
        document.getElementById('lNotes').value     = listingData.notes      || '';
        document.getElementById('lStatus').value    = listingData.status     || 'active';
        document.getElementById('statusGroup').style.display = 'block';

        // Restore checkboxes
        document.querySelectorAll('input[name="coverage_types[]"]').forEach(cb => {
          cb.checked = (listingData.coverage_types||[]).includes(cb.value);
        });
      }

      document.getElementById('listingModal').classList.add('open');
    }

    function closeModal() {
      document.getElementById('listingModal').classList.remove('open');
    }

    function editListing(id) {
      var l = listings.find(x => x.id === id);
      if (l) openModal(l);
    }

    async function saveListing() {
      const btn      = document.getElementById('saveBtn');
      const feedback = document.getElementById('modalFeedback');
      const origHTML = btn.innerHTML;
      btn.disabled   = true;
      btn.innerHTML  = '<iconify-icon icon="lucide:loader-circle" style="font-size:15px;margin-right:6px;animation:spin 1s linear infinite;vertical-align:middle;"></iconify-icon>Saving…';
      feedback.style.display = 'none';

      const form   = document.getElementById('listingForm');
      const formData = new FormData(form);
      formData.append('user_id', user.id);

      const isEdit = !!document.getElementById('listingId').value;
      formData.append('action', isEdit ? 'update_insurance_listing' : 'create_insurance_listing');

      try {
        const res  = await fetch('marketplace_data.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
          closeModal();
          loadListings();
        } else {
          feedback.className   = 'form-feedback error';
          feedback.textContent = '✗ ' + data.message;
          feedback.style.display = 'flex';
          btn.disabled  = false;
          btn.innerHTML = origHTML;
        }
      } catch {
        feedback.className   = 'form-feedback error';
        feedback.textContent = '✗ Network error — please try again.';
        feedback.style.display = 'flex';
        btn.disabled  = false;
        btn.innerHTML = origHTML;
      }
    }

    async function deleteListing(id) {
      if (!confirm('Delete this listing? This action cannot be undone.')) return;
      const formData = new FormData();
      formData.append('action', 'delete_listing');
      formData.append('user_id', user.id);
      formData.append('listing_id', id);
      formData.append('list_type', 'insurance');
      try {
        const res  = await fetch('marketplace_data.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
          loadListings();
        } else {
          alert('Error: ' + data.message);
        }
      } catch {
        alert('Network error — please try again.');
      }
    }

    function logout() {
      localStorage.removeItem('fx_user');
      window.location.href = 'insurance-login';
    }

    // Close modal on backdrop click
    document.getElementById('listingModal').addEventListener('click', function(e) {
      if (e.target === this) closeModal();
    });

    // Init
    loadListings();
  </script>
</body>
</html>
