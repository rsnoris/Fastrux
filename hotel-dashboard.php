<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <link rel="icon" href="favicon.svg" type="image/svg+xml" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Hotel Dashboard — Fastrux Marketplace</title>
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
      background: #fdf4ff; border: 1px solid #e9d5ff;
      color: #6b21a8; border-radius: 12px;
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
    .stat-icon.purple { background: #fdf4ff; color: #6b21a8; }
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

    .amenity-tag {
      display: inline-block;
      background: #fdf4ff; color: #6b21a8;
      border-radius: 4px; padding: 1px 7px; font-size: 11px; font-weight: 600;
      margin: 1px 2px;
    }
    .star-display { color: #f59e0b; font-size: 14px; }

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
      <a href="index" class="dash-brand">
        <iconify-icon icon="lucide:truck" style="font-size:24px"></iconify-icon>
        Fastrux <span>/ Hotel Dashboard</span>
      </a>
      <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
        <span class="dash-badge"><iconify-icon icon="lucide:hotel"></iconify-icon> Hotel Partner</span>
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
        <a href="account" class="btn btn-outline" style="padding:8px 16px;font-size:14px;">
          <iconify-icon icon="lucide:settings" style="margin-right:6px;font-size:15px;vertical-align:middle;"></iconify-icon>Account
        </a>
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
        <div class="stat-icon purple"><iconify-icon icon="lucide:star"></iconify-icon></div>
        <div><div class="stat-label">Avg. Star Rating</div><div class="stat-value" id="statStars">—</div></div>
      </div>
    </div>

    <!-- Listings table -->
    <div class="section-card">
      <div class="section-header">
        <span class="section-title">My Hotel Listings</span>
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
        <span class="modal-title" id="modalTitle">New Hotel Listing</span>
        <button class="modal-close" onclick="closeModal()" aria-label="Close">✕</button>
      </div>
      <div class="modal-body">
        <div class="form-feedback" id="modalFeedback" style="display:none;margin-bottom:16px;"></div>
        <form id="listingForm">
          <input type="hidden" id="listingId" name="listing_id" />

          <div class="form-group">
            <label for="lTitle">Hotel / Property Name *</label>
            <input class="form-control" type="text" id="lTitle" name="title" placeholder="e.g. Route 40 Motor Inn" required />
          </div>

          <div class="form-group">
            <label for="lDesc">Description</label>
            <textarea class="form-control" id="lDesc" name="description" rows="3"
              placeholder="Describe your property, room types, and what makes it great for truckers and logistics professionals…"
              style="resize:vertical;"></textarea>
          </div>

          <div class="form-group">
            <label for="lLocation">Location / Address *</label>
            <input class="form-control" type="text" id="lLocation" name="location" placeholder="e.g. 1200 Hwy 40, Elk City, OK" required />
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div class="form-group" style="margin-bottom:0;">
              <label for="lStars">Star Rating <span style="color:var(--muted-foreground);font-weight:400;">(1–5)</span></label>
              <select class="form-control" id="lStars" name="star_rating">
                <option value="">— Select —</option>
                <option value="1">1 ★</option>
                <option value="2">2 ★★</option>
                <option value="3">3 ★★★</option>
                <option value="4">4 ★★★★</option>
                <option value="5">5 ★★★★★</option>
              </select>
            </div>
            <div class="form-group" style="margin-bottom:0;">
              <label for="lPrice">Price Per Night <span style="color:var(--muted-foreground);font-weight:400;">(USD)</span></label>
              <input class="form-control" type="text" id="lPrice" name="price_per_night" placeholder="e.g. 89 or 75–120" />
            </div>
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:16px;">
            <div class="form-group" style="margin-bottom:0;">
              <label for="lCheckIn">Check-In Time</label>
              <input class="form-control" type="text" id="lCheckIn" name="check_in_time" placeholder="e.g. 3:00 PM" />
            </div>
            <div class="form-group" style="margin-bottom:0;">
              <label for="lCheckOut">Check-Out Time</label>
              <input class="form-control" type="text" id="lCheckOut" name="check_out_time" placeholder="e.g. 11:00 AM" />
            </div>
          </div>

          <div class="form-group" style="margin-top:16px;">
            <label>Amenities</label>
            <div class="check-grid">
              <label><input type="checkbox" name="amenities[]" value="parking" style="accent-color:var(--primary);"> Free Parking</label>
              <label><input type="checkbox" name="amenities[]" value="truck_parking" style="accent-color:var(--primary);"> Truck Parking</label>
              <label><input type="checkbox" name="amenities[]" value="wifi" style="accent-color:var(--primary);"> Free Wi-Fi</label>
              <label><input type="checkbox" name="amenities[]" value="breakfast" style="accent-color:var(--primary);"> Breakfast Included</label>
              <label><input type="checkbox" name="amenities[]" value="gym" style="accent-color:var(--primary);"> Gym / Fitness</label>
              <label><input type="checkbox" name="amenities[]" value="laundry" style="accent-color:var(--primary);"> Laundry</label>
              <label><input type="checkbox" name="amenities[]" value="restaurant" style="accent-color:var(--primary);"> Restaurant On-Site</label>
              <label><input type="checkbox" name="amenities[]" value="pet_friendly" style="accent-color:var(--primary);"> Pet Friendly</label>
              <label><input type="checkbox" name="amenities[]" value="pool" style="accent-color:var(--primary);"> Swimming Pool</label>
              <label><input type="checkbox" name="amenities[]" value="spa" style="accent-color:var(--primary);"> Spa / Hot Tub</label>
            </div>
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div class="form-group" style="margin-bottom:0;">
              <label for="lEmail">Contact Email</label>
              <input class="form-control" type="email" id="lEmail" name="contact_email" placeholder="reservations@hotel.com" />
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
              placeholder="Any other info for drivers and logistics teams…" style="resize:vertical;"></textarea>
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
    if (!user || !user.id || user.role !== 'hotel') {
      window.location.href = 'hotel-login?redirect=' + encodeURIComponent(window.location.href);
    }

    document.getElementById('userGreeting').textContent = 'Hi, ' + (user.first_name || user.email);

    var listings = [];

    function loadListings() {
      fetch('marketplace_data.php?action=my_listings&user_id=' + encodeURIComponent(user.id) + '&type=hotel')
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
      var stars  = listings.filter(l => l.star_rating).map(l => parseInt(l.star_rating));
      var avgStars = stars.length ? (stars.reduce((a,b) => a+b, 0) / stars.length).toFixed(1) : '—';

      document.getElementById('statTotal').textContent  = total;
      document.getElementById('statActive').textContent = active;
      document.getElementById('statPaused').textContent = paused;
      document.getElementById('statStars').textContent  = avgStars;
    }

    const AMENITY_LABELS = {
      parking: 'Parking', truck_parking: 'Truck Parking', wifi: 'Wi-Fi',
      breakfast: 'Breakfast', gym: 'Gym', laundry: 'Laundry',
      restaurant: 'Restaurant', pet_friendly: 'Pets OK', pool: 'Pool', spa: 'Spa'
    };

    function starStr(n) {
      if (!n) return '';
      return '★'.repeat(parseInt(n));
    }

    function renderListings() {
      var container = document.getElementById('listingsContainer');
      if (!listings.length) {
        container.innerHTML = '<div class="empty-state"><iconify-icon icon="lucide:hotel"></iconify-icon>No listings yet. Create your first hotel listing.</div>';
        return;
      }
      var html = '<div style="overflow-x:auto;"><table class="data-table"><thead><tr>' +
        '<th>Name / Location</th><th>Rating</th><th>Price/Night</th><th>Amenities</th><th>Status</th><th>Created</th><th>Actions</th>' +
        '</tr></thead><tbody>';
      listings.forEach(function(l) {
        var amenTags = (l.amenities||[]).slice(0,3).map(a => '<span class="amenity-tag">' + (AMENITY_LABELS[a]||a) + '</span>').join('');
        if ((l.amenities||[]).length > 3) amenTags += '<span class="amenity-tag">+' + ((l.amenities||[]).length - 3) + '</span>';
        var badge = '<span class="badge ' + (l.status||'active') + '">' + (l.status||'active') + '</span>';
        var date  = l.created_at ? l.created_at.split(' ')[0] : '—';
        var starsHtml = l.star_rating ? '<span class="star-display">' + starStr(l.star_rating) + '</span>' : '—';
        html += '<tr>' +
          '<td><strong>' + esc(l.title) + '</strong>' + (l.location ? '<br><small style="color:var(--muted-foreground);">' + esc(l.location) + '</small>' : '') + '</td>' +
          '<td>' + starsHtml + '</td>' +
          '<td>' + esc(l.price_per_night ? '$' + l.price_per_night : '—') + '</td>' +
          '<td>' + (amenTags || '—') + '</td>' +
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
      document.getElementById('modalTitle').textContent = 'New Hotel Listing';
      document.getElementById('modalFeedback').style.display = 'none';

      if (listingData) {
        document.getElementById('modalTitle').textContent = 'Edit Hotel Listing';
        document.getElementById('listingId').value   = listingData.id;
        document.getElementById('lTitle').value      = listingData.title          || '';
        document.getElementById('lDesc').value       = listingData.description    || '';
        document.getElementById('lLocation').value   = listingData.location       || '';
        document.getElementById('lStars').value      = listingData.star_rating    || '';
        document.getElementById('lPrice').value      = listingData.price_per_night|| '';
        document.getElementById('lCheckIn').value    = listingData.check_in_time  || '';
        document.getElementById('lCheckOut').value   = listingData.check_out_time || '';
        document.getElementById('lEmail').value      = listingData.contact_email  || '';
        document.getElementById('lPhone').value      = listingData.contact_phone  || '';
        document.getElementById('lWebsite').value    = listingData.website        || '';
        document.getElementById('lNotes').value      = listingData.notes          || '';
        document.getElementById('lStatus').value     = listingData.status         || 'active';
        document.getElementById('statusGroup').style.display = 'block';

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
      formData.append('action', isEdit ? 'update_hotel_listing' : 'create_hotel_listing');

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
      formData.append('list_type', 'hotel');
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
      window.location.href = 'hotel-login';
    }

    document.getElementById('listingModal').addEventListener('click', function(e) {
      if (e.target === this) closeModal();
    });

    loadListings();
  </script>
</body>
</html>
