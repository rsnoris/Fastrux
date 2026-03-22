<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <link rel="icon" href="favicon.svg" type="image/svg+xml" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Gas Station Dashboard — Fastrux Marketplace</title>
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
      background: #fefce8; border: 1px solid #fde68a;
      color: #92400e; border-radius: 12px;
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
    .stat-icon.yellow { background: #fefce8; color: #92400e; }
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

    .fuel-tag {
      display: inline-block;
      background: #fefce8; color: #92400e;
      border-radius: 4px; padding: 1px 7px; font-size: 11px; font-weight: 600;
      margin: 1px 2px;
    }
    .amenity-tag {
      display: inline-block;
      background: #f0fdf4; color: #15803d;
      border-radius: 4px; padding: 1px 7px; font-size: 11px; font-weight: 600;
      margin: 1px 2px;
    }

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
      max-width: 600px; width: 100%; max-height: 90vh; overflow-y: auto;
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

    .check-grid {
      display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: 6px;
    }
    .check-grid label {
      display: flex; align-items: center; gap: 8px;
      font-size: 14px; font-weight: 400; cursor: pointer;
    }
  </style>
</head>
<body>

  <!-- Dashboard header -->
  <header class="dash-header">
    <div class="container dash-header-inner">
      <a href="index.php" class="dash-brand">
        <iconify-icon icon="lucide:truck" style="font-size:24px"></iconify-icon>
        Fastrux <span>/ Gas Station Dashboard</span>
      </a>
      <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
        <span class="dash-badge"><iconify-icon icon="lucide:fuel"></iconify-icon> Gas Station Partner</span>
        <a href="messages.php" class="btn btn-outline" style="padding:8px 16px;font-size:14px;">
          <iconify-icon icon="lucide:message-circle" style="margin-right:6px;font-size:15px;vertical-align:middle;"></iconify-icon>Messages
        </a>
        <a href="documents.php" class="btn btn-outline" style="padding:8px 16px;font-size:14px;">
          <iconify-icon icon="lucide:file-text" style="margin-right:6px;font-size:15px;vertical-align:middle;"></iconify-icon>Documents
        </a>
        <a href="marketplace.php" class="btn btn-outline" style="padding:8px 16px;font-size:14px;">
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
        <div class="stat-icon yellow"><iconify-icon icon="lucide:fuel"></iconify-icon></div>
        <div><div class="stat-label">Fuel Types Listed</div><div class="stat-value" id="statFuels">—</div></div>
      </div>
    </div>

    <!-- Listings table -->
    <div class="section-card">
      <div class="section-header">
        <span class="section-title">My Gas Station Listings</span>
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
        <span class="modal-title" id="modalTitle">New Gas Station Listing</span>
        <button class="modal-close" onclick="closeModal()" aria-label="Close">✕</button>
      </div>
      <div class="modal-body">
        <div class="form-feedback" id="modalFeedback" style="display:none;margin-bottom:16px;"></div>
        <form id="listingForm">
          <input type="hidden" id="listingId" name="listing_id" />

          <div class="form-group">
            <label for="lTitle">Station / Listing Name *</label>
            <input class="form-control" type="text" id="lTitle" name="title" placeholder="e.g. Route 66 Truck Stop" required />
          </div>

          <div class="form-group">
            <label for="lDesc">Description</label>
            <textarea class="form-control" id="lDesc" name="description" rows="3"
              placeholder="Describe your station, facilities, and what makes it ideal for truckers…" style="resize:vertical;"></textarea>
          </div>

          <div class="form-group">
            <label for="lLocation">Location / Address *</label>
            <input class="form-control" type="text" id="lLocation" name="location" placeholder="e.g. 4200 I-40 W, Amarillo, TX" required />
          </div>

          <div class="form-group">
            <label>Fuel Types Available *</label>
            <div class="check-grid">
              <label><input type="checkbox" name="fuel_types[]" value="regular" style="accent-color:var(--primary);"> Regular (87)</label>
              <label><input type="checkbox" name="fuel_types[]" value="premium" style="accent-color:var(--primary);"> Premium</label>
              <label><input type="checkbox" name="fuel_types[]" value="diesel" style="accent-color:var(--primary);"> Diesel</label>
              <label><input type="checkbox" name="fuel_types[]" value="e85" style="accent-color:var(--primary);"> E85 / Ethanol</label>
              <label><input type="checkbox" name="fuel_types[]" value="ev_charging" style="accent-color:var(--primary);"> EV Charging</label>
              <label><input type="checkbox" name="fuel_types[]" value="def_fluid" style="accent-color:var(--primary);"> DEF Fluid</label>
            </div>
          </div>

          <div class="form-group">
            <label>Amenities</label>
            <div class="check-grid">
              <label><input type="checkbox" name="amenities[]" value="convenience_store" style="accent-color:var(--primary);"> Convenience Store</label>
              <label><input type="checkbox" name="amenities[]" value="restrooms" style="accent-color:var(--primary);"> Restrooms</label>
              <label><input type="checkbox" name="amenities[]" value="atm" style="accent-color:var(--primary);"> ATM</label>
              <label><input type="checkbox" name="amenities[]" value="car_wash" style="accent-color:var(--primary);"> Car Wash</label>
              <label><input type="checkbox" name="amenities[]" value="truck_accessible" style="accent-color:var(--primary);"> Truck-Accessible Lanes</label>
              <label><input type="checkbox" name="amenities[]" value="tire_service" style="accent-color:var(--primary);"> Tire Service</label>
              <label><input type="checkbox" name="amenities[]" value="scales" style="accent-color:var(--primary);"> Weigh Scales</label>
              <label><input type="checkbox" name="amenities[]" value="shower" style="accent-color:var(--primary);"> Driver Showers</label>
            </div>
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">
            <div class="form-group" style="margin-bottom:0;">
              <label for="lPriceRegular">Regular ($/gal)</label>
              <input class="form-control" type="text" id="lPriceRegular" name="price_regular" placeholder="e.g. 3.49" />
            </div>
            <div class="form-group" style="margin-bottom:0;">
              <label for="lPriceDiesel">Diesel ($/gal)</label>
              <input class="form-control" type="text" id="lPriceDiesel" name="price_diesel" placeholder="e.g. 3.99" />
            </div>
            <div class="form-group" style="margin-bottom:0;">
              <label for="lPriceEv">EV ($/kWh)</label>
              <input class="form-control" type="text" id="lPriceEv" name="price_ev" placeholder="e.g. 0.35" />
            </div>
          </div>

          <div class="form-group" style="margin-top:16px;">
            <label for="lHours">Operating Hours</label>
            <input class="form-control" type="text" id="lHours" name="hours" placeholder="e.g. 24/7 or Mon–Fri 6am–10pm" />
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div class="form-group" style="margin-bottom:0;">
              <label for="lEmail">Contact Email</label>
              <input class="form-control" type="email" id="lEmail" name="contact_email" placeholder="station@example.com" />
            </div>
            <div class="form-group" style="margin-bottom:0;">
              <label for="lPhone">Contact Phone</label>
              <input class="form-control" type="tel" id="lPhone" name="contact_phone" placeholder="+1 (555) 000-0000" />
            </div>
          </div>

          <div class="form-group" style="margin-top:16px;">
            <label for="lWebsite">Website <span style="color:var(--muted-foreground);font-weight:400;">(optional)</span></label>
            <input class="form-control" type="url" id="lWebsite" name="website" placeholder="https://example.com" />
          </div>

          <div class="form-group">
            <label for="lNotes">Additional Notes <span style="color:var(--muted-foreground);font-weight:400;">(optional)</span></label>
            <textarea class="form-control" id="lNotes" name="notes" rows="2"
              placeholder="Any other info for drivers…" style="resize:vertical;"></textarea>
          </div>

          <div class="form-group" id="statusGroup" style="display:none;">
            <label for="lStatus">Listing Status</label>
            <select class="form-control" id="lStatus" name="status">
              <option value="active">Active</option>
              <option value="paused">Paused</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline" onclick="closeModal()" style="padding:9px 20px;">Cancel</button>
        <button class="btn btn-primary" id="saveBtn" onclick="saveListing()" style="padding:9px 20px;">Save Listing</button>
      </div>
    </div>
  </div>

  <script>
    // ── Auth guard ────────────────────────────────────────────
    var user = null;
    try { user = JSON.parse(localStorage.getItem('fx_user')); } catch(e){}
    if (!user || !user.id || user.role !== 'gas_station') {
      window.location.href = 'gas-station-login.php?redirect=' + encodeURIComponent(window.location.href);
    }

    document.getElementById('userGreeting').textContent = 'Hi, ' + (user.first_name || user.email);

    var listings = [];

    function loadListings() {
      fetch('marketplace_data.php?action=my_listings&user_id=' + encodeURIComponent(user.id) + '&type=gas_station')
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
      var fuels  = new Set();
      listings.forEach(l => (l.fuel_types||[]).forEach(f => fuels.add(f)));

      document.getElementById('statTotal').textContent  = total;
      document.getElementById('statActive').textContent = active;
      document.getElementById('statPaused').textContent = paused;
      document.getElementById('statFuels').textContent  = fuels.size;
    }

    const FUEL_LABELS = {
      regular: 'Regular', premium: 'Premium', diesel: 'Diesel',
      e85: 'E85', ev_charging: 'EV Charging', def_fluid: 'DEF Fluid'
    };
    const AMENITY_LABELS = {
      convenience_store: 'C-Store', restrooms: 'Restrooms', atm: 'ATM', car_wash: 'Car Wash',
      truck_accessible: 'Truck Lanes', tire_service: 'Tires', scales: 'Scales', shower: 'Showers'
    };

    function renderListings() {
      var container = document.getElementById('listingsContainer');
      if (!listings.length) {
        container.innerHTML = '<div class="empty-state"><iconify-icon icon="lucide:fuel"></iconify-icon>No listings yet. Create your first gas station listing.</div>';
        return;
      }
      var html = '<div style="overflow-x:auto;"><table class="data-table"><thead><tr>' +
        '<th>Name / Location</th><th>Fuel Types</th><th>Amenities</th><th>Hours</th><th>Status</th><th>Created</th><th>Actions</th>' +
        '</tr></thead><tbody>';
      listings.forEach(function(l) {
        var fuelTags = (l.fuel_types||[]).map(f => '<span class="fuel-tag">' + (FUEL_LABELS[f]||f) + '</span>').join('');
        var amenTags = (l.amenities||[]).slice(0,3).map(a => '<span class="amenity-tag">' + (AMENITY_LABELS[a]||a) + '</span>').join('');
        if ((l.amenities||[]).length > 3) amenTags += '<span class="amenity-tag">+' + ((l.amenities||[]).length - 3) + '</span>';
        var badge = '<span class="badge ' + (l.status||'active') + '">' + (l.status||'active') + '</span>';
        var date  = l.created_at ? l.created_at.split(' ')[0] : '—';
        html += '<tr>' +
          '<td><strong>' + esc(l.title) + '</strong>' + (l.location ? '<br><small style="color:var(--muted-foreground);">' + esc(l.location) + '</small>' : '') + '</td>' +
          '<td>' + (fuelTags || '—') + '</td>' +
          '<td>' + (amenTags || '—') + '</td>' +
          '<td>' + esc(l.hours || '—') + '</td>' +
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

    function openModal(listingData) {
      document.getElementById('listingId').value = '';
      document.getElementById('listingForm').reset();
      document.getElementById('statusGroup').style.display = 'none';
      document.getElementById('modalTitle').textContent = 'New Gas Station Listing';
      document.getElementById('modalFeedback').style.display = 'none';

      if (listingData) {
        document.getElementById('modalTitle').textContent = 'Edit Gas Station Listing';
        document.getElementById('listingId').value    = listingData.id;
        document.getElementById('lTitle').value       = listingData.title        || '';
        document.getElementById('lDesc').value        = listingData.description  || '';
        document.getElementById('lLocation').value    = listingData.location     || '';
        document.getElementById('lPriceRegular').value= listingData.price_regular|| '';
        document.getElementById('lPriceDiesel').value = listingData.price_diesel || '';
        document.getElementById('lPriceEv').value     = listingData.price_ev     || '';
        document.getElementById('lHours').value       = listingData.hours        || '';
        document.getElementById('lEmail').value       = listingData.contact_email|| '';
        document.getElementById('lPhone').value       = listingData.contact_phone|| '';
        document.getElementById('lWebsite').value     = listingData.website      || '';
        document.getElementById('lNotes').value       = listingData.notes        || '';
        document.getElementById('lStatus').value      = listingData.status       || 'active';
        document.getElementById('statusGroup').style.display = 'block';

        document.querySelectorAll('input[name="fuel_types[]"]').forEach(cb => {
          cb.checked = (listingData.fuel_types||[]).includes(cb.value);
        });
        document.querySelectorAll('input[name="amenities[]"]').forEach(cb => {
          cb.checked = (listingData.amenities||[]).includes(cb.value);
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

      const form     = document.getElementById('listingForm');
      const formData = new FormData(form);
      formData.append('user_id', user.id);

      const isEdit = !!document.getElementById('listingId').value;
      formData.append('action', isEdit ? 'update_gas_station_listing' : 'create_gas_station_listing');

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
      formData.append('list_type', 'gas_station');
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
      window.location.href = 'gas-station-login.php';
    }

    document.getElementById('listingModal').addEventListener('click', function(e) {
      if (e.target === this) closeModal();
    });

    loadListings();
  </script>
</body>
</html>
