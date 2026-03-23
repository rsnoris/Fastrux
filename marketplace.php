<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <link rel="icon" href="favicon.svg" type="image/svg+xml" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Marketplace — Fastrux Logistics</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="shared.css" />
  <script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>
  <style>
    /* ── Hero ── */
    .mkt-hero {
      background: linear-gradient(135deg, var(--primary) 0%, #1e3a8a 100%);
      color: #fff; padding: 64px 0 48px;
    }
    .mkt-hero h1 { font-size: clamp(28px,5vw,48px); font-weight: 900; margin-bottom: 12px; }
    .mkt-hero p  { font-size: 18px; opacity: .85; max-width: 560px; margin-bottom: 32px; }

    /* ── Tab strip ── */
    .tab-strip {
      display: flex; gap: 0; background: var(--card);
      border-bottom: 2px solid var(--border); margin-bottom: 32px;
      overflow-x: auto; -webkit-overflow-scrolling: touch;
    }
    .tab-btn {
      display: flex; align-items: center; gap: 8px;
      padding: 16px 24px; font-size: 15px; font-weight: 600;
      cursor: pointer; border: none; background: none;
      color: var(--muted-foreground); border-bottom: 3px solid transparent;
      margin-bottom: -2px; white-space: nowrap; transition: color .2s;
    }
    .tab-btn.active { color: var(--primary); border-bottom-color: var(--primary); }
    .tab-btn:hover  { color: var(--foreground); }

    /* ── Filter bar ── */
    .filter-bar {
      display: flex; gap: 12px; align-items: center; flex-wrap: wrap;
      margin-bottom: 24px;
    }
    .filter-bar input, .filter-bar select {
      padding: 9px 14px; border: 1.5px solid var(--border);
      border-radius: var(--radius-md); font-family: var(--font-family-body);
      font-size: 14px; background: var(--card); color: var(--foreground);
      outline: none; transition: border-color .2s;
    }
    .filter-bar input:focus, .filter-bar select:focus { border-color: var(--primary); }
    .filter-bar input { flex: 1; min-width: 200px; }

    /* ── Listing grid ── */
    .listing-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
      gap: 20px;
    }

    /* ── Listing card ── */
    .listing-card {
      background: var(--card); border: 1px solid var(--border);
      border-radius: var(--radius-xl); padding: 24px;
      display: flex; flex-direction: column; gap: 12px;
      transition: box-shadow .2s, transform .2s;
    }
    .listing-card:hover {
      box-shadow: 0 8px 32px rgba(0,0,0,.08);
      transform: translateY(-2px);
    }
    .listing-card-header {
      display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;
    }
    .listing-card-icon {
      width: 48px; height: 48px; min-width: 48px;
      border-radius: var(--radius-md);
      display: flex; align-items: center; justify-content: center;
      font-size: 22px;
    }
    .listing-card-icon.ins { background: #eff6ff; color: #1d4ed8; }
    .listing-card-icon.trk { background: #f0fdf4; color: #15803d; }
    .listing-card-icon.gas { background: #fefce8; color: #92400e; }
    .listing-card-icon.htl { background: #fdf4ff; color: #6b21a8; }

    .listing-card h3 { font-size: 15px; font-weight: 700; margin-bottom: 2px; line-height: 1.3; }
    .listing-card .company { font-size: 13px; color: var(--muted-foreground); }

    .listing-meta {
      display: flex; flex-wrap: wrap; gap: 8px; font-size: 13px;
    }
    .meta-item {
      display: flex; align-items: center; gap: 4px;
      color: var(--muted-foreground);
    }
    .meta-item iconify-icon { font-size: 14px; }

    .coverage-tag {
      display: inline-block;
      background: #eff6ff; color: #1d4ed8;
      border-radius: 4px; padding: 2px 8px; font-size: 11px; font-weight: 600;
      margin: 2px 2px 2px 0;
    }
    .truck-tag {
      display: inline-block;
      background: #f0fdf4; color: #15803d;
      border-radius: 4px; padding: 2px 8px; font-size: 11px; font-weight: 600;
      margin: 2px 2px 2px 0;
    }
    .listing-type-badge {
      display: inline-flex; align-items: center;
      padding: 3px 10px; border-radius: 20px;
      font-size: 12px; font-weight: 600;
    }
    .badge-lease { background: #eff6ff; color: #1d4ed8; }
    .badge-sale  { background: #fef3c7; color: #92400e; }
    .badge-ins   { background: #eff6ff; color: #1d4ed8; }

    .listing-card .contact-row {
      display: flex; gap: 10px; flex-wrap: wrap; margin-top: auto; padding-top: 8px;
      border-top: 1px solid var(--border);
    }
    .contact-row a {
      display: flex; align-items: center; gap: 5px;
      font-size: 13px; color: var(--primary); font-weight: 500;
      text-decoration: none;
    }
    .contact-row a:hover { text-decoration: underline; }

    @keyframes spin { to { transform: rotate(360deg); } }
    .empty-state { text-align: center; padding: 80px 24px; color: var(--muted-foreground); }
    .empty-state iconify-icon { font-size: 56px; display: block; margin: 0 auto 16px; }
    .empty-state h3 { font-size: 18px; font-weight: 700; margin-bottom: 8px; }
    .empty-state p  { font-size: 14px; margin-bottom: 20px; }

    /* CTA cards at bottom */
    .cta-grid {
      display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 20px; margin-top: 48px;
    }
    .cta-card {
      background: var(--card); border: 1px solid var(--border);
      border-radius: var(--radius-xl); padding: 28px;
      display: flex; flex-direction: column; gap: 10px;
    }
    .cta-card iconify-icon { font-size: 36px; }
    .cta-card h3 { font-size: 18px; font-weight: 800; }
    .cta-card p  { font-size: 14px; color: var(--muted-foreground); margin-bottom: 4px; }
  </style>
</head>
<body>

  <header class="header">
    <div class="container header-content">
      <a href="index" class="logo">
        <iconify-icon icon="lucide:truck" style="font-size:28px;color:var(--primary)"></iconify-icon>
        Fastrux
      </a>
      <nav class="nav-links">
        <a class="nav-link" href="index">Home</a>
        <a class="nav-link" href="index#services">Services</a>
        <a class="nav-link" href="track">Tracking</a>
        <a class="nav-link active" href="marketplace">Marketplace</a>
        <a class="nav-link" href="loadboard">Loadboard</a>
        <a class="nav-link" href="driver-onboarding">Drive with Us</a>
        <a class="nav-link" href="contact">Contact</a>
      </nav>
      <div class="header-actions">
        <a class="nav-link" href="login">Login</a>
        <a class="btn btn-primary" href="register">Join Marketplace</a>
      </div>
      <button class="hamburger" id="hamburger" aria-label="Toggle menu">
        <span></span><span></span><span></span>
      </button>
    </div>
  </header>
  <nav class="mobile-menu" id="mobileMenu">
    <a class="nav-link" href="index">Home</a>
    <a class="nav-link" href="index#services">Services</a>
    <a class="nav-link" href="track">Tracking</a>
    <a class="nav-link active" href="marketplace">Marketplace</a>
    <a class="nav-link" href="loadboard">Loadboard</a>
    <a class="nav-link" href="driver-onboarding">Drive with Us</a>
    <a class="nav-link" href="contact">Contact</a>
    <div class="header-actions" style="margin-top:8px;">
      <a class="btn btn-outline" href="login">Sign In</a>
      <a class="btn btn-primary" href="register">Join Marketplace</a>
    </div>
  </nav>

  <!-- Hero -->
  <section class="mkt-hero">
    <div class="container">
      <h1>Fastrux Marketplace</h1>
      <p>Find spot insurance, discover trucks for lease or purchase, locate driver-friendly gas stations, and book lodging — all in one place.</p>
      <div style="display:flex;gap:12px;flex-wrap:wrap;">
        <a href="#insurance" class="btn" style="background:#fff;color:var(--primary);padding:12px 24px;font-size:15px;font-weight:700;">
          <iconify-icon icon="lucide:shield-check" style="margin-right:8px;vertical-align:middle;"></iconify-icon>Browse Insurance
        </a>
        <a href="#trucks" class="btn" style="background:rgba(255,255,255,.15);color:#fff;padding:12px 24px;font-size:15px;font-weight:700;border:2px solid rgba(255,255,255,.4);">
          <iconify-icon icon="lucide:truck" style="margin-right:8px;vertical-align:middle;"></iconify-icon>Browse Trucks
        </a>
        <a href="#gas-stations" class="btn" style="background:rgba(255,255,255,.15);color:#fff;padding:12px 24px;font-size:15px;font-weight:700;border:2px solid rgba(255,255,255,.4);">
          <iconify-icon icon="lucide:fuel" style="margin-right:8px;vertical-align:middle;"></iconify-icon>Gas Stations
        </a>
        <a href="#hotels" class="btn" style="background:rgba(255,255,255,.15);color:#fff;padding:12px 24px;font-size:15px;font-weight:700;border:2px solid rgba(255,255,255,.4);">
          <iconify-icon icon="lucide:hotel" style="margin-right:8px;vertical-align:middle;"></iconify-icon>Hotels
        </a>
      </div>
    </div>
  </section>

  <!-- Main content -->
  <div class="container" style="padding-top:0;padding-bottom:48px;">

    <!-- Summary stats bar -->
    <div style="background:var(--card);border:1px solid var(--border);border-radius:0 0 var(--radius-xl) var(--radius-xl);padding:20px 28px;margin-bottom:36px;display:flex;gap:32px;flex-wrap:wrap;">
      <div><span id="insCount" style="font-size:24px;font-weight:900;color:var(--primary);">—</span> <span style="font-size:14px;color:var(--muted-foreground);">Insurance Listings</span></div>
      <div style="width:1px;background:var(--border);"></div>
      <div><span id="trkCount" style="font-size:24px;font-weight:900;color:#15803d;">—</span> <span style="font-size:14px;color:var(--muted-foreground);">Truck Listings</span></div>
      <div style="width:1px;background:var(--border);"></div>
      <div><span id="trkLeaseCount" style="font-size:24px;font-weight:900;color:#1d4ed8;">—</span> <span style="font-size:14px;color:var(--muted-foreground);">For Lease</span></div>
      <div style="width:1px;background:var(--border);"></div>
      <div><span id="trkSaleCount" style="font-size:24px;font-weight:900;color:#92400e;">—</span> <span style="font-size:14px;color:var(--muted-foreground);">For Sale</span></div>
      <div style="width:1px;background:var(--border);"></div>
      <div><span id="gasCount" style="font-size:24px;font-weight:900;color:#d97706;">—</span> <span style="font-size:14px;color:var(--muted-foreground);">Gas Stations</span></div>
      <div style="width:1px;background:var(--border);"></div>
      <div><span id="hotelCount" style="font-size:24px;font-weight:900;color:#6b21a8;">—</span> <span style="font-size:14px;color:var(--muted-foreground);">Hotels</span></div>
    </div>

    <!-- Insurance section -->
    <section id="insurance">
      <div class="tab-strip" style="margin-bottom:0;border-radius:var(--radius-xl) var(--radius-xl) 0 0;overflow:hidden;">
        <div style="padding:16px 24px;font-size:18px;font-weight:800;display:flex;align-items:center;gap:10px;">
          <iconify-icon icon="lucide:shield-check" style="color:#1d4ed8;font-size:22px;"></iconify-icon>
          Spot Insurance Offerings
        </div>
      </div>
      <div style="background:var(--card);border:1px solid var(--border);border-top:none;border-radius:0 0 var(--radius-xl) var(--radius-xl);padding:24px;margin-bottom:40px;">
        <div class="filter-bar">
          <input type="text" id="insSearch" placeholder="Search insurance listings…" oninput="filterInsurance()" />
          <select id="insCoverageFilter" onchange="filterInsurance()">
            <option value="">All Coverage Types</option>
            <option value="cargo">Cargo Insurance</option>
            <option value="liability">Liability</option>
            <option value="physical_damage">Physical Damage</option>
            <option value="workers_comp">Workers' Comp</option>
            <option value="general_liability">General Liability</option>
            <option value="occupational_accident">Occupational Accident</option>
            <option value="bobtail">Bobtail</option>
            <option value="non_trucking">Non-Trucking Liability</option>
          </select>
        </div>
        <div class="listing-grid" id="insuranceGrid">
          <div class="empty-state" style="grid-column:1/-1;">
            <iconify-icon icon="lucide:loader-circle" style="animation:spin 1s linear infinite;color:var(--primary);"></iconify-icon>
          </div>
        </div>
      </div>
    </section>

    <!-- Trucks section -->
    <section id="trucks">
      <div class="tab-strip" style="margin-bottom:0;border-radius:var(--radius-xl) var(--radius-xl) 0 0;overflow:hidden;">
        <div style="padding:16px 24px;font-size:18px;font-weight:800;display:flex;align-items:center;gap:10px;">
          <iconify-icon icon="lucide:truck" style="color:#15803d;font-size:22px;"></iconify-icon>
          Trucks for Lease &amp; Sale
        </div>
      </div>
      <div style="background:var(--card);border:1px solid var(--border);border-top:none;border-radius:0 0 var(--radius-xl) var(--radius-xl);padding:24px;margin-bottom:40px;">
        <div class="filter-bar">
          <input type="text" id="trkSearch" placeholder="Search truck listings…" oninput="filterTrucks()" />
          <select id="trkTypeFilter" onchange="filterTrucks()">
            <option value="">All Listing Types</option>
            <option value="lease">For Lease</option>
            <option value="sale">For Sale</option>
          </select>
          <select id="trkVehicleFilter" onchange="filterTrucks()">
            <option value="">All Truck Types</option>
            <option value="semi_truck">Semi Truck</option>
            <option value="box_truck">Box Truck</option>
            <option value="flatbed">Flatbed</option>
            <option value="refrigerated">Refrigerated</option>
            <option value="tanker">Tanker</option>
            <option value="dump_truck">Dump Truck</option>
            <option value="cargo_van">Cargo Van</option>
            <option value="other">Other</option>
          </select>
        </div>
        <div class="listing-grid" id="trucksGrid">
          <div class="empty-state" style="grid-column:1/-1;">
            <iconify-icon icon="lucide:loader-circle" style="animation:spin 1s linear infinite;color:var(--primary);"></iconify-icon>
          </div>
        </div>
      </div>
    </section>

    <!-- Gas Stations section -->
    <section id="gas-stations">
      <div class="tab-strip" style="margin-bottom:0;border-radius:var(--radius-xl) var(--radius-xl) 0 0;overflow:hidden;">
        <div style="padding:16px 24px;font-size:18px;font-weight:800;display:flex;align-items:center;gap:10px;">
          <iconify-icon icon="lucide:fuel" style="color:#d97706;font-size:22px;"></iconify-icon>
          Gas Stations &amp; Fuel Stops
        </div>
      </div>
      <div style="background:var(--card);border:1px solid var(--border);border-top:none;border-radius:0 0 var(--radius-xl) var(--radius-xl);padding:24px;margin-bottom:40px;">
        <div class="filter-bar">
          <input type="text" id="gasSearch" placeholder="Search gas stations…" oninput="filterGasStations()" />
          <select id="gasFuelFilter" onchange="filterGasStations()">
            <option value="">All Fuel Types</option>
            <option value="regular">Regular</option>
            <option value="premium">Premium</option>
            <option value="diesel">Diesel</option>
            <option value="e85">E85 / Ethanol</option>
            <option value="ev_charging">EV Charging</option>
            <option value="def_fluid">DEF Fluid</option>
          </select>
        </div>
        <div class="listing-grid" id="gasGrid">
          <div class="empty-state" style="grid-column:1/-1;">
            <iconify-icon icon="lucide:loader-circle" style="animation:spin 1s linear infinite;color:var(--primary);"></iconify-icon>
          </div>
        </div>
      </div>
    </section>

    <!-- Hotels section -->
    <section id="hotels">
      <div class="tab-strip" style="margin-bottom:0;border-radius:var(--radius-xl) var(--radius-xl) 0 0;overflow:hidden;">
        <div style="padding:16px 24px;font-size:18px;font-weight:800;display:flex;align-items:center;gap:10px;">
          <iconify-icon icon="lucide:hotel" style="color:#6b21a8;font-size:22px;"></iconify-icon>
          Hotels &amp; Lodging
        </div>
      </div>
      <div style="background:var(--card);border:1px solid var(--border);border-top:none;border-radius:0 0 var(--radius-xl) var(--radius-xl);padding:24px;margin-bottom:40px;">
        <div class="filter-bar">
          <input type="text" id="hotelSearch" placeholder="Search hotels…" oninput="filterHotels()" />
          <select id="hotelAmenityFilter" onchange="filterHotels()">
            <option value="">All Amenities</option>
            <option value="truck_parking">Truck Parking</option>
            <option value="parking">Free Parking</option>
            <option value="wifi">Free Wi-Fi</option>
            <option value="breakfast">Breakfast Included</option>
            <option value="laundry">Laundry</option>
            <option value="restaurant">Restaurant</option>
            <option value="pet_friendly">Pet Friendly</option>
            <option value="pool">Pool</option>
          </select>
        </div>
        <div class="listing-grid" id="hotelsGrid">
          <div class="empty-state" style="grid-column:1/-1;">
            <iconify-icon icon="lucide:loader-circle" style="animation:spin 1s linear infinite;color:var(--primary);"></iconify-icon>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA section -->
    <div class="cta-grid">
      <div class="cta-card">
        <iconify-icon icon="lucide:shield-check" style="color:#1d4ed8;"></iconify-icon>
        <h3>Insurance Company?</h3>
        <p>Offer spot insurance to the thousands of carriers and shippers on Fastrux. Reach qualified customers instantly.</p>
        <a href="insurance-login" class="btn btn-primary" style="align-self:flex-start;padding:10px 20px;">Insurance Portal</a>
      </div>
      <div class="cta-card">
        <iconify-icon icon="lucide:truck" style="color:#15803d;"></iconify-icon>
        <h3>Trucking Company?</h3>
        <p>List your idle trucks for lease or sale and connect with owner-operators and fleets looking to grow.</p>
        <a href="trucking-login" class="btn btn-primary" style="align-self:flex-start;padding:10px 20px;">Trucking Portal</a>
      </div>
      <div class="cta-card">
        <iconify-icon icon="lucide:fuel" style="color:#d97706;"></iconify-icon>
        <h3>Gas Station?</h3>
        <p>Connect with thousands of truckers and drivers looking for fuel, DEF fluid, and driver amenities along their routes.</p>
        <a href="gas-station-login" class="btn btn-primary" style="align-self:flex-start;padding:10px 20px;">Gas Station Portal</a>
      </div>
      <div class="cta-card">
        <iconify-icon icon="lucide:hotel" style="color:#6b21a8;"></iconify-icon>
        <h3>Hotel or Motel?</h3>
        <p>Attract drivers and logistics professionals who need rest stops and overnight stays. Highlight truck parking and driver-friendly facilities.</p>
        <a href="hotel-login" class="btn btn-primary" style="align-self:flex-start;padding:10px 20px;">Hotel Portal</a>
      </div>
      <div class="cta-card">
        <iconify-icon icon="lucide:user-plus" style="color:var(--primary);"></iconify-icon>
        <h3>New to Fastrux?</h3>
        <p>Register your company in minutes. All marketplace partners get instant access after registration.</p>
        <a href="register" class="btn btn-outline" style="align-self:flex-start;padding:10px 20px;">Create Account</a>
      </div>
    </div>

  </div>

  <footer class="footer">
    <div class="container">
      <div class="footer-grid">
        <div>
          <div class="footer-brand">
            <iconify-icon icon="lucide:truck" style="font-size:24px;color:var(--primary)"></iconify-icon>
            Fastrux
          </div>
          <p style="font-size:14px;color:var(--muted-foreground);max-width:260px;line-height:1.6;margin-top:8px;">
            The logistics marketplace connecting shippers, carriers, insurance companies, trucking fleets, gas stations, and hotels.
          </p>
        </div>
        <div>
          <div class="footer-heading">Marketplace</div>
          <a href="marketplace#insurance" class="footer-link">Spot Insurance</a>
          <a href="marketplace#trucks" class="footer-link">Trucks for Lease/Sale</a>
          <a href="marketplace#gas-stations" class="footer-link">Gas Stations</a>
          <a href="marketplace#hotels" class="footer-link">Hotels &amp; Lodging</a>
          <a href="insurance-login" class="footer-link">Insurance Portal</a>
          <a href="trucking-login" class="footer-link">Trucking Portal</a>
          <a href="gas-station-login" class="footer-link">Gas Station Portal</a>
          <a href="hotel-login" class="footer-link">Hotel Portal</a>
        </div>
        <div>
          <div class="footer-heading">Quick Links</div>
          <a href="index" class="footer-link">Home</a>
          <a href="track" class="footer-link">Tracking</a>
          <a href="register" class="footer-link">Register</a>
          <a href="contact" class="footer-link">Contact</a>
        </div>
      </div>
      <div class="footer-bottom">
        <div>© 2026 Fastrux Logistics. All rights reserved.</div>
        <div>
          <a href="privacy" style="color:var(--muted-foreground);margin-right:16px;">Privacy</a>
          <a href="terms"   style="color:var(--muted-foreground);">Terms</a>
        </div>
      </div>
    </div>
  </footer>

  <script src="auth-nav.js"></script>
  <script>
    const ham = document.getElementById('hamburger');
    const mob = document.getElementById('mobileMenu');
    ham.addEventListener('click', () => { ham.classList.toggle('open'); mob.classList.toggle('open'); });

    // ── Data ──────────────────────────────────────────────────
    var allInsurance   = [];
    var allTrucks      = [];
    var allGasStations = [];
    var allHotels      = [];

    const COVERAGE_LABELS = {
      cargo: 'Cargo', liability: 'Liability', physical_damage: 'Physical Damage',
      workers_comp: "Workers' Comp", general_liability: 'General Liability',
      occupational_accident: 'Occupational Accident', bobtail: 'Bobtail', non_trucking: 'Non-Trucking'
    };
    const TRUCK_LABELS = {
      semi_truck: 'Semi Truck', box_truck: 'Box Truck', flatbed: 'Flatbed',
      refrigerated: 'Refrigerated', tanker: 'Tanker', dump_truck: 'Dump Truck',
      cargo_van: 'Cargo Van', other: 'Other'
    };
    const FUEL_LABELS = {
      regular: 'Regular', premium: 'Premium', diesel: 'Diesel',
      e85: 'E85', ev_charging: 'EV Charging', def_fluid: 'DEF Fluid'
    };
    const HOTEL_AMENITY_LABELS = {
      parking: 'Parking', truck_parking: 'Truck Parking', wifi: 'Wi-Fi',
      breakfast: 'Breakfast', gym: 'Gym', laundry: 'Laundry',
      restaurant: 'Restaurant', pet_friendly: 'Pets OK', pool: 'Pool', spa: 'Spa'
    };

    function esc(str) {
      var d = document.createElement('div'); d.textContent = str || ''; return d.innerHTML;
    }

    // ── Load all data sets in parallel ────────────────────────
    Promise.all([
      fetch('marketplace_data.php?action=list_insurance&status=active').then(r => r.json()),
      fetch('marketplace_data.php?action=list_trucks&status=active').then(r => r.json()),
      fetch('marketplace_data.php?action=list_gas_stations&status=active').then(r => r.json()),
      fetch('marketplace_data.php?action=list_hotels&status=active').then(r => r.json()),
    ]).then(function([ins, trk, gas, htl]) {
      allInsurance   = ins.listings || [];
      allTrucks      = trk.listings || [];
      allGasStations = gas.listings || [];
      allHotels      = htl.listings || [];

      // Stats bar
      document.getElementById('insCount').textContent      = allInsurance.length;
      document.getElementById('trkCount').textContent      = allTrucks.length;
      document.getElementById('trkLeaseCount').textContent = allTrucks.filter(l => l.listing_type === 'lease').length;
      document.getElementById('trkSaleCount').textContent  = allTrucks.filter(l => l.listing_type === 'sale').length;
      document.getElementById('gasCount').textContent      = allGasStations.length;
      document.getElementById('hotelCount').textContent    = allHotels.length;

      renderInsurance(allInsurance);
      renderTrucks(allTrucks);
      renderGasStations(allGasStations);
      renderHotels(allHotels);
    }).catch(function() {
      ['insuranceGrid','trucksGrid','gasGrid','hotelsGrid'].forEach(function(id) {
        document.getElementById(id).innerHTML =
          '<div class="empty-state" style="grid-column:1/-1;"><iconify-icon icon="lucide:alert-circle"></iconify-icon><h3>Could not load listings</h3><p>Please try refreshing the page.</p></div>';
      });
    });

    // ── Render insurance ──────────────────────────────────────
    function renderInsurance(listings) {
      var grid = document.getElementById('insuranceGrid');
      if (!listings.length) {
        grid.innerHTML = '<div class="empty-state" style="grid-column:1/-1;">' +
          '<iconify-icon icon="lucide:shield-check"></iconify-icon>' +
          '<h3>No insurance listings yet</h3>' +
          '<p>Insurance companies can register and post their offerings here.</p>' +
          '<a href="register" class="btn btn-primary" style="display:inline-flex;padding:10px 20px;">Register as Insurer</a>' +
          '</div>';
        return;
      }
      grid.innerHTML = listings.map(function(l) {
        var tags = (l.coverage_types||[]).map(t => '<span class="coverage-tag">' + esc(COVERAGE_LABELS[t]||t) + '</span>').join('');
        var emailLink = l.contact_email ? '<a href="mailto:' + esc(l.contact_email) + '"><iconify-icon icon="lucide:mail"></iconify-icon>' + esc(l.contact_email) + '</a>' : '';
        var phoneLink = l.contact_phone ? '<a href="tel:' + esc(l.contact_phone) + '"><iconify-icon icon="lucide:phone"></iconify-icon>' + esc(l.contact_phone) + '</a>' : '';
        var webLink   = l.website       ? '<a href="' + esc(l.website) + '" target="_blank" rel="noopener"><iconify-icon icon="lucide:external-link"></iconify-icon>Website</a>' : '';
        return '<div class="listing-card">' +
          '<div class="listing-card-header">' +
            '<div style="flex:1;">' +
              '<h3>' + esc(l.title) + '</h3>' +
              '<div class="company">' + esc(l.company_name) + '</div>' +
            '</div>' +
            '<div class="listing-card-icon ins"><iconify-icon icon="lucide:shield-check"></iconify-icon></div>' +
          '</div>' +
          (l.description ? '<p style="font-size:14px;color:var(--muted-foreground);line-height:1.5;margin:0;">' + esc(l.description) + '</p>' : '') +
          '<div>' + (tags || '<span style="color:var(--muted-foreground);font-size:13px;">Coverage types not specified</span>') + '</div>' +
          '<div class="listing-meta">' +
            (l.premium_range ? '<div class="meta-item"><iconify-icon icon="lucide:dollar-sign"></iconify-icon>' + esc(l.premium_range) + '</div>' : '') +
            (l.min_coverage || l.max_coverage ? '<div class="meta-item"><iconify-icon icon="lucide:trending-up"></iconify-icon>' + (l.min_coverage ? 'From ' + esc(l.min_coverage) : '') + (l.max_coverage ? ' up to ' + esc(l.max_coverage) : '') + '</div>' : '') +
            (l.service_area ? '<div class="meta-item"><iconify-icon icon="lucide:map-pin"></iconify-icon>' + esc(l.service_area) + '</div>' : '') +
          '</div>' +
          (l.notes ? '<p style="font-size:13px;color:var(--muted-foreground);margin:0;border-top:1px solid var(--border);padding-top:10px;">' + esc(l.notes) + '</p>' : '') +
          '<div class="contact-row">' + emailLink + phoneLink + webLink + '</div>' +
        '</div>';
      }).join('');
    }

    // ── Render trucks ─────────────────────────────────────────
    function renderTrucks(listings) {
      var grid = document.getElementById('trucksGrid');
      if (!listings.length) {
        grid.innerHTML = '<div class="empty-state" style="grid-column:1/-1;">' +
          '<iconify-icon icon="lucide:truck"></iconify-icon>' +
          '<h3>No truck listings yet</h3>' +
          '<p>Trucking companies can register and list their fleet for lease or sale.</p>' +
          '<a href="register" class="btn btn-primary" style="display:inline-flex;padding:10px 20px;">Register as Trucking Co.</a>' +
          '</div>';
        return;
      }
      grid.innerHTML = listings.map(function(l) {
        var listBadge = '<span class="listing-type-badge badge-' + (l.listing_type||'sale') + '">' + (l.listing_type === 'lease' ? 'For Lease' : 'For Sale') + '</span>';
        var truckBadge = l.truck_type ? '<span class="truck-tag">' + esc(TRUCK_LABELS[l.truck_type]||l.truck_type) + '</span>' : '';
        var vehicle = [l.year, l.make, l.model].filter(Boolean).join(' ');
        var emailLink = l.contact_email ? '<a href="mailto:' + esc(l.contact_email) + '"><iconify-icon icon="lucide:mail"></iconify-icon>' + esc(l.contact_email) + '</a>' : '';
        var phoneLink = l.contact_phone ? '<a href="tel:' + esc(l.contact_phone) + '"><iconify-icon icon="lucide:phone"></iconify-icon>' + esc(l.contact_phone) + '</a>' : '';
        return '<div class="listing-card">' +
          '<div class="listing-card-header">' +
            '<div style="flex:1;">' +
              '<h3>' + esc(l.title) + '</h3>' +
              '<div class="company">' + esc(l.company_name) + '</div>' +
            '</div>' +
            '<div class="listing-card-icon trk"><iconify-icon icon="lucide:truck"></iconify-icon></div>' +
          '</div>' +
          '<div style="display:flex;gap:6px;flex-wrap:wrap;">' + listBadge + truckBadge + '</div>' +
          (vehicle ? '<div style="font-size:15px;font-weight:600;">' + esc(vehicle) + '</div>' : '') +
          (l.description ? '<p style="font-size:14px;color:var(--muted-foreground);line-height:1.5;margin:0;">' + esc(l.description) + '</p>' : '') +
          '<div class="listing-meta">' +
            (l.price    ? '<div class="meta-item"><iconify-icon icon="lucide:dollar-sign"></iconify-icon>' + esc(l.price)    + '</div>' : '') +
            (l.mileage  ? '<div class="meta-item"><iconify-icon icon="lucide:gauge"></iconify-icon>'       + esc(l.mileage)  + '</div>' : '') +
            (l.location ? '<div class="meta-item"><iconify-icon icon="lucide:map-pin"></iconify-icon>'    + esc(l.location) + '</div>' : '') +
          '</div>' +
          (l.lease_terms && l.listing_type === 'lease' ? '<p style="font-size:13px;color:var(--muted-foreground);margin:0;"><strong>Lease Terms:</strong> ' + esc(l.lease_terms) + '</p>' : '') +
          (l.notes ? '<p style="font-size:13px;color:var(--muted-foreground);margin:0;border-top:1px solid var(--border);padding-top:10px;">' + esc(l.notes) + '</p>' : '') +
          '<div class="contact-row">' + emailLink + phoneLink + '</div>' +
        '</div>';
      }).join('');
    }

    // ── Filter functions ──────────────────────────────────────
    function filterInsurance() {
      var query    = document.getElementById('insSearch').value.toLowerCase();
      var coverage = document.getElementById('insCoverageFilter').value;
      var filtered = allInsurance.filter(function(l) {
        var matchQ = !query ||
          (l.title       || '').toLowerCase().includes(query) ||
          (l.company_name|| '').toLowerCase().includes(query) ||
          (l.description || '').toLowerCase().includes(query) ||
          (l.service_area|| '').toLowerCase().includes(query);
        var matchC = !coverage || (l.coverage_types || []).includes(coverage);
        return matchQ && matchC;
      });
      renderInsurance(filtered);
    }

    function filterTrucks() {
      var query       = document.getElementById('trkSearch').value.toLowerCase();
      var listType    = document.getElementById('trkTypeFilter').value;
      var vehicleType = document.getElementById('trkVehicleFilter').value;
      var filtered = allTrucks.filter(function(l) {
        var matchQ = !query ||
          (l.title        || '').toLowerCase().includes(query) ||
          (l.company_name || '').toLowerCase().includes(query) ||
          (l.make         || '').toLowerCase().includes(query) ||
          (l.model        || '').toLowerCase().includes(query) ||
          (l.location     || '').toLowerCase().includes(query);
        var matchL = !listType    || l.listing_type === listType;
        var matchV = !vehicleType || l.truck_type   === vehicleType;
        return matchQ && matchL && matchV;
      });
      renderTrucks(filtered);
    }

    function filterGasStations() {
      var query    = document.getElementById('gasSearch').value.toLowerCase();
      var fuelType = document.getElementById('gasFuelFilter').value;
      var filtered = allGasStations.filter(function(l) {
        var matchQ = !query ||
          (l.title        || '').toLowerCase().includes(query) ||
          (l.company_name || '').toLowerCase().includes(query) ||
          (l.location     || '').toLowerCase().includes(query) ||
          (l.description  || '').toLowerCase().includes(query);
        var matchF = !fuelType || (l.fuel_types || []).includes(fuelType);
        return matchQ && matchF;
      });
      renderGasStations(filtered);
    }

    function filterHotels() {
      var query   = document.getElementById('hotelSearch').value.toLowerCase();
      var amenity = document.getElementById('hotelAmenityFilter').value;
      var filtered = allHotels.filter(function(l) {
        var matchQ = !query ||
          (l.title        || '').toLowerCase().includes(query) ||
          (l.company_name || '').toLowerCase().includes(query) ||
          (l.location     || '').toLowerCase().includes(query) ||
          (l.description  || '').toLowerCase().includes(query);
        var matchA = !amenity || (l.amenities || []).includes(amenity);
        return matchQ && matchA;
      });
      renderHotels(filtered);
    }

    // ── Render gas stations ───────────────────────────────────
    function renderGasStations(listings) {
      var grid = document.getElementById('gasGrid');
      if (!listings.length) {
        grid.innerHTML = '<div class="empty-state" style="grid-column:1/-1;">' +
          '<iconify-icon icon="lucide:fuel"></iconify-icon>' +
          '<h3>No gas station listings yet</h3>' +
          '<p>Gas station partners can register and list their fuel stops here.</p>' +
          '<a href="register?role=gas_station" class="btn btn-primary" style="display:inline-flex;padding:10px 20px;">Register as Gas Station</a>' +
          '</div>';
        return;
      }
      grid.innerHTML = listings.map(function(l) {
        var fuelTags = (l.fuel_types||[]).map(t => '<span class="coverage-tag" style="background:#fefce8;color:#92400e;">' + esc(FUEL_LABELS[t]||t) + '</span>').join('');
        var emailLink = l.contact_email ? '<a href="mailto:' + esc(l.contact_email) + '"><iconify-icon icon="lucide:mail"></iconify-icon>' + esc(l.contact_email) + '</a>' : '';
        var phoneLink = l.contact_phone ? '<a href="tel:' + esc(l.contact_phone) + '"><iconify-icon icon="lucide:phone"></iconify-icon>' + esc(l.contact_phone) + '</a>' : '';
        var webLink   = l.website       ? '<a href="' + esc(l.website) + '" target="_blank" rel="noopener"><iconify-icon icon="lucide:external-link"></iconify-icon>Website</a>' : '';
        return '<div class="listing-card">' +
          '<div class="listing-card-header">' +
            '<div style="flex:1;">' +
              '<h3>' + esc(l.title) + '</h3>' +
              '<div class="company">' + esc(l.company_name) + '</div>' +
            '</div>' +
            '<div class="listing-card-icon gas"><iconify-icon icon="lucide:fuel"></iconify-icon></div>' +
          '</div>' +
          (l.description ? '<p style="font-size:14px;color:var(--muted-foreground);line-height:1.5;margin:0;">' + esc(l.description) + '</p>' : '') +
          '<div>' + (fuelTags || '<span style="color:var(--muted-foreground);font-size:13px;">Fuel types not specified</span>') + '</div>' +
          '<div class="listing-meta">' +
            (l.location     ? '<div class="meta-item"><iconify-icon icon="lucide:map-pin"></iconify-icon>' + esc(l.location) + '</div>' : '') +
            (l.hours        ? '<div class="meta-item"><iconify-icon icon="lucide:clock"></iconify-icon>' + esc(l.hours) + '</div>' : '') +
            (l.price_regular? '<div class="meta-item"><iconify-icon icon="lucide:dollar-sign"></iconify-icon>Regular: $' + esc(l.price_regular) + '/gal</div>' : '') +
            (l.price_diesel ? '<div class="meta-item"><iconify-icon icon="lucide:dollar-sign"></iconify-icon>Diesel: $' + esc(l.price_diesel) + '/gal</div>' : '') +
          '</div>' +
          (l.notes ? '<p style="font-size:13px;color:var(--muted-foreground);margin:0;border-top:1px solid var(--border);padding-top:10px;">' + esc(l.notes) + '</p>' : '') +
          '<div class="contact-row">' + emailLink + phoneLink + webLink + '</div>' +
        '</div>';
      }).join('');
    }

    // ── Render hotels ─────────────────────────────────────────
    function renderHotels(listings) {
      var grid = document.getElementById('hotelsGrid');
      if (!listings.length) {
        grid.innerHTML = '<div class="empty-state" style="grid-column:1/-1;">' +
          '<iconify-icon icon="lucide:hotel"></iconify-icon>' +
          '<h3>No hotel listings yet</h3>' +
          '<p>Hotel partners can register and list their properties here.</p>' +
          '<a href="register?role=hotel" class="btn btn-primary" style="display:inline-flex;padding:10px 20px;">Register as Hotel</a>' +
          '</div>';
        return;
      }
      grid.innerHTML = listings.map(function(l) {
        var stars = l.star_rating ? '★'.repeat(parseInt(l.star_rating)) : '';
        var amenTags = (l.amenities||[]).slice(0,4).map(a => '<span class="coverage-tag" style="background:#fdf4ff;color:#6b21a8;">' + esc(HOTEL_AMENITY_LABELS[a]||a) + '</span>').join('');
        if ((l.amenities||[]).length > 4) amenTags += '<span class="coverage-tag" style="background:#fdf4ff;color:#6b21a8;">+' + ((l.amenities||[]).length - 4) + '</span>';
        var emailLink = l.contact_email ? '<a href="mailto:' + esc(l.contact_email) + '"><iconify-icon icon="lucide:mail"></iconify-icon>' + esc(l.contact_email) + '</a>' : '';
        var phoneLink = l.contact_phone ? '<a href="tel:' + esc(l.contact_phone) + '"><iconify-icon icon="lucide:phone"></iconify-icon>' + esc(l.contact_phone) + '</a>' : '';
        var webLink   = l.website       ? '<a href="' + esc(l.website) + '" target="_blank" rel="noopener"><iconify-icon icon="lucide:external-link"></iconify-icon>Website</a>' : '';
        return '<div class="listing-card">' +
          '<div class="listing-card-header">' +
            '<div style="flex:1;">' +
              '<h3>' + esc(l.title) + (stars ? ' <span style="color:#f59e0b;font-size:13px;">' + stars + '</span>' : '') + '</h3>' +
              '<div class="company">' + esc(l.company_name) + '</div>' +
            '</div>' +
            '<div class="listing-card-icon htl"><iconify-icon icon="lucide:hotel"></iconify-icon></div>' +
          '</div>' +
          (l.description ? '<p style="font-size:14px;color:var(--muted-foreground);line-height:1.5;margin:0;">' + esc(l.description) + '</p>' : '') +
          '<div>' + (amenTags || '<span style="color:var(--muted-foreground);font-size:13px;">Amenities not specified</span>') + '</div>' +
          '<div class="listing-meta">' +
            (l.location        ? '<div class="meta-item"><iconify-icon icon="lucide:map-pin"></iconify-icon>' + esc(l.location) + '</div>' : '') +
            (l.price_per_night ? '<div class="meta-item"><iconify-icon icon="lucide:dollar-sign"></iconify-icon>From $' + esc(l.price_per_night) + '/night</div>' : '') +
            (l.check_in_time   ? '<div class="meta-item"><iconify-icon icon="lucide:log-in"></iconify-icon>Check-in: ' + esc(l.check_in_time) + '</div>' : '') +
          '</div>' +
          (l.notes ? '<p style="font-size:13px;color:var(--muted-foreground);margin:0;border-top:1px solid var(--border);padding-top:10px;">' + esc(l.notes) + '</p>' : '') +
          '<div class="contact-row">' + emailLink + phoneLink + webLink + '</div>' +
        '</div>';
      }).join('');
    }
  </script>
</body>
</html>
