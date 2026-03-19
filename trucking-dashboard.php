<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <link rel="icon" href="favicon.svg" type="image/svg+xml" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Trucking Dashboard — Fastrux Marketplace</title>
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
      background: #f0fdf4; border: 1px solid #86efac;
      color: #15803d; border-radius: 12px;
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

    .badge {
      display: inline-flex; align-items: center; gap: 4px;
      padding: 2px 10px; border-radius: 20px;
      font-size: 12px; font-weight: 600;
    }
    .badge.active   { background: #e6f9ee; color: #16a34a; }
    .badge.inactive { background: #f3f4f6; color: #6b7280; }
    .badge.paused   { background: #fff7e6; color: #d97706; }
    .badge.lease    { background: #eff6ff; color: #1d4ed8; }
    .badge.sale     { background: #fef3c7; color: #92400e; }

    @keyframes spin { to { transform: rotate(360deg); } }
    .empty-state { text-align: center; padding: 60px 24px; color: var(--muted-foreground); }
    .empty-state iconify-icon { font-size: 48px; display: block; margin: 0 auto 12px; }

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
  </style>
</head>
<body>

  <header class="dash-header">
    <div class="container dash-header-inner">
      <a href="/" class="dash-brand">
        <iconify-icon icon="lucide:truck" style="font-size:24px"></iconify-icon>
        Fastrux <span>/ Trucking Dashboard</span>
      </a>
      <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
        <span class="dash-badge"><iconify-icon icon="lucide:truck"></iconify-icon> Trucking Partner</span>
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
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon blue"><iconify-icon icon="lucide:list"></iconify-icon></div>
        <div><div class="stat-label">Total Listings</div><div class="stat-value" id="statTotal">—</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon green"><iconify-icon icon="lucide:check-circle"></iconify-icon></div>
        <div><div class="stat-label">Active</div><div class="stat-value" id="statActive">—</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon amber"><iconify-icon icon="lucide:tag"></iconify-icon></div>
        <div><div class="stat-label">For Sale</div><div class="stat-value" id="statSale">—</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon purple"><iconify-icon icon="lucide:refresh-cw"></iconify-icon></div>
        <div><div class="stat-label">For Lease</div><div class="stat-value" id="statLease">—</div></div>
      </div>
    </div>

    <!-- Listings -->
    <div class="section-card">
      <div class="section-header">
        <span class="section-title">My Truck Listings</span>
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

  <!-- Create / Edit Modal -->
  <div class="modal-overlay" id="listingModal">
    <div class="modal">
      <div class="modal-header">
        <span class="modal-title" id="modalTitle">New Truck Listing</span>
        <button class="modal-close" onclick="closeModal()">✕</button>
      </div>
      <div class="modal-body">
        <div class="form-feedback" id="modalFeedback"></div>
        <form id="listingForm">
          <input type="hidden" id="listingId" name="listing_id" value="" />
          <div class="form-group">
            <label for="lTitle">Listing Title *</label>
            <input class="form-control" type="text" id="lTitle" name="title"
              placeholder="e.g. 2022 Freightliner Cascadia — For Lease" required />
          </div>
          <div class="form-group">
            <label for="lListingType">Listing Type *</label>
            <select class="form-control" id="lListingType" name="listing_type" required>
              <option value="sale">For Sale</option>
              <option value="lease">For Lease</option>
            </select>
          </div>
          <div class="form-group">
            <label for="lTruckType">Truck Type *</label>
            <select class="form-control" id="lTruckType" name="truck_type" required>
              <option value="">— Select —</option>
              <option value="semi_truck">Semi Truck / Tractor</option>
              <option value="box_truck">Box Truck</option>
              <option value="flatbed">Flatbed</option>
              <option value="refrigerated">Refrigerated / Reefer</option>
              <option value="tanker">Tanker</option>
              <option value="dump_truck">Dump Truck</option>
              <option value="cargo_van">Cargo Van</option>
              <option value="other">Other</option>
            </select>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
            <div class="form-group">
              <label for="lYear">Year</label>
              <input class="form-control" type="number" id="lYear" name="year"
                placeholder="e.g. 2022" min="1980" max="2030" />
            </div>
            <div class="form-group">
              <label for="lMake">Make</label>
              <input class="form-control" type="text" id="lMake" name="make"
                placeholder="e.g. Freightliner" />
            </div>
            <div class="form-group">
              <label for="lModel">Model</label>
              <input class="form-control" type="text" id="lModel" name="model"
                placeholder="e.g. Cascadia" />
            </div>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="form-group">
              <label for="lMileage">Mileage</label>
              <input class="form-control" type="text" id="lMileage" name="mileage"
                placeholder="e.g. 450,000 miles" />
            </div>
            <div class="form-group">
              <label for="lPrice">Price / Lease Rate</label>
              <input class="form-control" type="text" id="lPrice" name="price"
                placeholder="e.g. $45,000 or $1,200/mo" />
            </div>
          </div>
          <div class="form-group" id="leaseTermsGroup">
            <label for="lLeaseTerms">Lease Terms</label>
            <input class="form-control" type="text" id="lLeaseTerms" name="lease_terms"
              placeholder="e.g. 12-month minimum, $0 down" />
          </div>
          <div class="form-group">
            <label for="lLocation">Location</label>
            <input class="form-control" type="text" id="lLocation" name="location"
              placeholder="e.g. Dallas, TX" />
          </div>
          <div class="form-group">
            <label for="lDot">DOT Number</label>
            <input class="form-control" type="text" id="lDot" name="dot_number"
              placeholder="e.g. 1234567" />
          </div>
          <div class="form-group">
            <label for="lDesc">Description</label>
            <textarea class="form-control" id="lDesc" name="description" rows="3"
              placeholder="Additional details, condition, features…" style="resize:vertical;"></textarea>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="form-group">
              <label for="lEmail">Contact Email</label>
              <input class="form-control" type="email" id="lEmail" name="contact_email"
                placeholder="fleet@company.com" />
            </div>
            <div class="form-group">
              <label for="lPhone">Contact Phone</label>
              <input class="form-control" type="tel" id="lPhone" name="contact_phone"
                placeholder="+1 (555) 000-0000" />
            </div>
          </div>
          <div class="form-group">
            <label for="lNotes">Additional Notes</label>
            <textarea class="form-control" id="lNotes" name="notes" rows="2"
              placeholder="Financing available, inspection welcome…" style="resize:vertical;"></textarea>
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
    if (!user || !user.id || user.role !== 'trucking_company') {
      window.location.href = 'trucking-login?redirect=' + encodeURIComponent(window.location.href);
    }

    document.getElementById('userGreeting').textContent = 'Hi, ' + (user.first_name || user.email);

    // Toggle lease terms visibility
    document.getElementById('lListingType').addEventListener('change', function() {
      document.getElementById('leaseTermsGroup').style.display = this.value === 'lease' ? 'block' : 'none';
    });

    var listings = [];

    function loadListings() {
      fetch('marketplace_data.php?action=my_listings&user_id=' + encodeURIComponent(user.id) + '&type=truck')
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
      var sale   = listings.filter(l => l.listing_type === 'sale').length;
      var lease  = listings.filter(l => l.listing_type === 'lease').length;
      document.getElementById('statTotal').textContent  = total;
      document.getElementById('statActive').textContent = active;
      document.getElementById('statSale').textContent   = sale;
      document.getElementById('statLease').textContent  = lease;
    }

    const TRUCK_LABELS = {
      semi_truck: 'Semi Truck', box_truck: 'Box Truck', flatbed: 'Flatbed',
      refrigerated: 'Refrigerated', tanker: 'Tanker', dump_truck: 'Dump Truck',
      cargo_van: 'Cargo Van', other: 'Other'
    };

    function renderListings() {
      var container = document.getElementById('listingsContainer');
      if (!listings.length) {
        container.innerHTML = '<div class="empty-state"><iconify-icon icon="lucide:truck"></iconify-icon>No truck listings yet. Add your first listing.</div>';
        return;
      }
      var html = '<div style="overflow-x:auto;"><table class="data-table"><thead><tr>' +
        '<th>Title</th><th>Type</th><th>Vehicle</th><th>Price</th><th>Location</th><th>Status</th><th>Created</th><th>Actions</th>' +
        '</tr></thead><tbody>';
      listings.forEach(function(l) {
        var typeBadge = '<span class="badge ' + (l.listing_type||'sale') + '">' + (l.listing_type === 'lease' ? 'Lease' : 'Sale') + '</span>';
        var statusBadge = '<span class="badge ' + (l.status||'active') + '">' + (l.status||'active') + '</span>';
        var vehicle = [l.year, l.make, l.model].filter(Boolean).join(' ') || (TRUCK_LABELS[l.truck_type] || l.truck_type || '—');
        var date    = l.created_at ? l.created_at.split(' ')[0] : '—';
        html += '<tr>' +
          '<td><strong>' + esc(l.title) + '</strong></td>' +
          '<td>' + typeBadge + '</td>' +
          '<td>' + esc(vehicle) + '<br><small style="color:var(--muted-foreground);">' + esc(TRUCK_LABELS[l.truck_type]||l.truck_type||'') + '</small></td>' +
          '<td>' + esc(l.price || '—') + '</td>' +
          '<td>' + esc(l.location || '—') + '</td>' +
          '<td>' + statusBadge + '</td>' +
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

    function openModal(listingData) {
      document.getElementById('listingId').value  = '';
      document.getElementById('listingForm').reset();
      document.getElementById('statusGroup').style.display = 'none';
      document.getElementById('leaseTermsGroup').style.display = 'none';
      document.getElementById('modalTitle').textContent = 'New Truck Listing';
      document.getElementById('modalFeedback').style.display = 'none';

      if (listingData) {
        document.getElementById('modalTitle').textContent    = 'Edit Truck Listing';
        document.getElementById('listingId').value           = listingData.id;
        document.getElementById('lTitle').value              = listingData.title         || '';
        document.getElementById('lListingType').value        = listingData.listing_type  || 'sale';
        document.getElementById('lTruckType').value          = listingData.truck_type    || '';
        document.getElementById('lYear').value               = listingData.year          || '';
        document.getElementById('lMake').value               = listingData.make          || '';
        document.getElementById('lModel').value              = listingData.model         || '';
        document.getElementById('lMileage').value            = listingData.mileage       || '';
        document.getElementById('lPrice').value              = listingData.price         || '';
        document.getElementById('lLeaseTerms').value         = listingData.lease_terms   || '';
        document.getElementById('lLocation').value           = listingData.location      || '';
        document.getElementById('lDot').value                = listingData.dot_number    || '';
        document.getElementById('lDesc').value               = listingData.description   || '';
        document.getElementById('lEmail').value              = listingData.contact_email || '';
        document.getElementById('lPhone').value              = listingData.contact_phone || '';
        document.getElementById('lNotes').value              = listingData.notes         || '';
        document.getElementById('lStatus').value             = listingData.status        || 'active';
        document.getElementById('statusGroup').style.display = 'block';
        document.getElementById('leaseTermsGroup').style.display =
          listingData.listing_type === 'lease' ? 'block' : 'none';
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

      const form     = document.getElementById('listingForm');
      const formData = new FormData(form);
      formData.append('user_id', user.id);

      const isEdit = !!document.getElementById('listingId').value;
      formData.append('action', isEdit ? 'update_truck_listing' : 'create_truck_listing');

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
      if (!confirm('Delete this truck listing? This cannot be undone.')) return;
      const formData = new FormData();
      formData.append('action', 'delete_listing');
      formData.append('user_id', user.id);
      formData.append('listing_id', id);
      formData.append('list_type', 'truck');
      try {
        const res  = await fetch('marketplace_data.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) { loadListings(); } else { alert('Error: ' + data.message); }
      } catch { alert('Network error — please try again.'); }
    }

    function logout() {
      localStorage.removeItem('fx_user');
      window.location.href = 'trucking-login';
    }

    document.getElementById('listingModal').addEventListener('click', function(e) {
      if (e.target === this) closeModal();
    });

    loadListings();
  </script>
</body>
</html>
