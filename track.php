<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <link rel="icon" href="favicon.svg" type="image/svg+xml" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Track Shipment — Fastrux Logistics</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="shared.css" />
  <script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>
  <style>
    .track-hero {
      background: linear-gradient(135deg, var(--foreground) 0%, #0b3a73 100%);
      color: #fff;
      padding: 80px 0 60px;
      text-align: center;
    }
    .track-hero h1 { font-size: 40px; font-weight: 800; margin-bottom: 16px; }
    .track-hero p  { font-size: 18px; color: rgba(255,255,255,.75); margin-bottom: 40px; }

    .track-form-wrap {
      background: #fff;
      border-radius: var(--radius-xl);
      padding: 32px;
      max-width: 640px;
      margin: 0 auto;
      box-shadow: var(--shadow-xl);
    }
    .track-input-row {
      display: flex;
      gap: 12px;
    }
    .track-input-row input {
      flex: 1;
    }
    @media (max-width: 480px) {
      .track-input-row { flex-direction: column; }
    }

    .track-result {
      max-width: 800px;
      margin: 48px auto 0;
    }
    .status-banner {
      display: flex;
      align-items: center;
      gap: 16px;
      padding: 20px 28px;
      border-radius: var(--radius-lg);
      background: var(--secondary);
      margin-bottom: 32px;
    }
    .status-icon {
      width: 48px; height: 48px;
      border-radius: 50%;
      background: var(--primary);
      display: flex; align-items: center; justify-content: center;
      color: #fff; flex-shrink: 0;
    }
    .status-label { font-size: 13px; color: var(--muted-foreground); margin-bottom: 2px; }
    .status-value { font-size: 20px; font-weight: 700; color: var(--foreground); }

    .shipment-meta {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 20px;
      margin-bottom: 32px;
    }
    .meta-card {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      padding: 20px;
    }
    .meta-card .label { font-size: 12px; color: var(--muted-foreground); text-transform: uppercase; letter-spacing: .5px; margin-bottom: 6px; }
    .meta-card .value { font-size: 15px; font-weight: 600; color: var(--foreground); }

    .timeline-track { position: relative; padding-left: 28px; }
    .timeline-track::before {
      content: '';
      position: absolute; left: 8px; top: 0; bottom: 0;
      width: 2px; background: var(--border);
    }
    .tl-item { position: relative; margin-bottom: 28px; }
    .tl-item:last-child { margin-bottom: 0; }
    .tl-dot {
      position: absolute; left: -24px; top: 3px;
      width: 16px; height: 16px;
      border-radius: 50%;
      background: var(--muted);
      border: 2px solid var(--border);
    }
    .tl-dot.active {
      background: var(--primary);
      border-color: var(--primary);
    }
    .tl-dot.done {
      background: var(--success);
      border-color: var(--success);
    }
    .tl-date  { font-size: 12px; color: var(--muted-foreground); margin-bottom: 2px; }
    .tl-title { font-size: 15px; font-weight: 600; color: var(--foreground); margin-bottom: 2px; }
    .tl-loc   { font-size: 13px; color: var(--muted-foreground); }

    .track-empty {
      text-align: center; padding: 60px 0;
    }
    .track-empty iconify-icon { font-size: 64px; color: var(--muted-foreground); margin-bottom: 16px; display: block; }
    .track-empty h3 { font-size: 22px; font-weight: 600; margin-bottom: 8px; }
    .track-empty p  { color: var(--muted-foreground); }

    @media (max-width: 600px) {
      .shipment-meta { grid-template-columns: 1fr 1fr; }
    }
    @media (max-width: 400px) {
      .shipment-meta { grid-template-columns: 1fr; }
    }
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
        <a class="nav-link active" href="track">Tracking</a>
        <a class="nav-link" href="contact">Contact</a>
        <a class="nav-link" href="driver-onboarding">Drive with Us</a>
        <a class="nav-link" href="loadboard">Loadboard</a>
        <a class="nav-link" href="marketplace">Marketplace</a>
      </nav>
      <div class="header-actions">
        <a class="nav-link" href="login">Login</a>
        <a class="btn btn-primary" href="quote">Get a Quote</a>
      </div>
      <button class="hamburger" id="hamburger" aria-label="Toggle menu">
        <span></span><span></span><span></span>
      </button>
    </div>
  </header>
  <nav class="mobile-menu" id="mobileMenu">
    <a class="nav-link" href="index">Home</a>
    <a class="nav-link" href="index#services">Services</a>
    <a class="nav-link active" href="track">Tracking</a>
    <a class="nav-link" href="contact">Contact</a>
        <a class="nav-link" href="driver-onboarding">Drive with Us</a>
        <a class="nav-link" href="loadboard">Loadboard</a>
        <a class="nav-link" href="marketplace">Marketplace</a>
    <div class="header-actions" style="margin-top:8px;">
      <a class="btn btn-outline" href="login">Login</a>
      <a class="btn btn-primary" href="quote">Get a Quote</a>
    </div>
  </nav>

  <!-- HERO + SEARCH -->
  <section class="track-hero">
    <div class="container">
      <h1>Track Your Shipment</h1>
      <p>Enter your tracking number to get real-time updates on your cargo.</p>
      <div class="track-form-wrap">
        <div class="track-input-row">
          <input class="form-control" type="text" id="trackingInput"
                 placeholder="e.g. FX-20260001234"
                 aria-label="Tracking number" />
          <button class="btn btn-primary" id="trackBtn" style="padding:12px 28px;white-space:nowrap;">
            <iconify-icon icon="lucide:search" style="font-size:16px;margin-right:6px;vertical-align:middle;"></iconify-icon>
            Track
          </button>
        </div>
        <p style="margin-top:12px;font-size:13px;color:var(--muted-foreground);text-align:left;">
          Your tracking number can be found in the confirmation email sent when your shipment was booked.
        </p>
      </div>
    </div>
  </section>

  <!-- RESULT AREA -->
  <section class="section">
    <div class="container">
      <div id="trackResult" class="track-result" style="display:none;">
        <div class="status-banner">
          <div class="status-icon">
            <iconify-icon icon="lucide:package-check" style="font-size:24px"></iconify-icon>
          </div>
          <div>
            <div class="status-label">Current Status</div>
            <div class="status-value" id="statusValue">In Transit</div>
          </div>
        </div>

        <div class="shipment-meta">
          <div class="meta-card">
            <div class="label">Tracking Number</div>
            <div class="value" id="metaTracking">—</div>
          </div>
          <div class="meta-card">
            <div class="label">Estimated Delivery</div>
            <div class="value" id="metaEta">—</div>
          </div>
          <div class="meta-card">
            <div class="label">Service Type</div>
            <div class="value" id="metaService">—</div>
          </div>
          <div class="meta-card">
            <div class="label">Origin</div>
            <div class="value" id="metaOrigin">—</div>
          </div>
          <div class="meta-card">
            <div class="label">Destination</div>
            <div class="value" id="metaDest">—</div>
          </div>
          <div class="meta-card">
            <div class="label">Weight</div>
            <div class="value" id="metaWeight">—</div>
          </div>
        </div>

        <h3 style="font-size:18px;font-weight:700;margin-bottom:24px;">Tracking History</h3>
        <div class="timeline-track" id="trackTimeline"></div>
      </div>

      <div id="trackEmpty" class="track-empty">
        <iconify-icon icon="lucide:package-search"></iconify-icon>
        <h3>No shipment loaded</h3>
        <p>Enter a tracking number above and click <strong>Track</strong> to see real-time shipment updates.</p>
      </div>

      <div id="trackNotFound" class="track-empty" style="display:none;">
        <iconify-icon icon="lucide:alert-circle"></iconify-icon>
        <h3>Shipment not found</h3>
        <p>We couldn't find a shipment with that tracking number. Please double-check the number or <a href="contact" style="color:var(--primary);">contact support</a>.</p>
      </div>
    </div>
  </section>

  <!-- FOOTER -->
  <footer class="footer">
    <div class="container">
      <div class="footer-grid">
        <div class="footer-brand">
          <a href="index" class="logo">
            <iconify-icon icon="lucide:truck" style="font-size:24px;color:var(--primary)"></iconify-icon>
            Fastrux
          </a>
          <p>Delivering excellence in logistics and supply chain management worldwide. Your trusted partner for seamless transportation.</p>
        </div>
        <div>
          <h4 class="footer-heading">Services</h4>
          <div class="footer-links">
            <a href="ocean-freight">Ocean Freight</a>
            <a href="air-freight">Air Freight</a>
            <a href="ground-transport">Ground Transport</a>
            <a href="warehousing">Warehousing</a>
          </div>
        </div>
        <div>
          <h4 class="footer-heading">Company</h4>
          <div class="footer-links">
            <a href="careers">Careers</a>
            <a href="driver-onboarding">Drive with Us</a>
            <a class="nav-link" href="loadboard">Loadboard</a>
            <a class="nav-link" href="marketplace">Marketplace</a>
            <a href="news">News &amp; Media</a>
            <a href="contact">Contact</a>
          </div>
        </div>
        <div>
          <h4 class="footer-heading">Contact</h4>
          <div class="footer-links">
          </div>
        </div>
      </div>
      <div class="footer-bottom">
        <div>© 2026 Fastrux Logistics. All rights reserved.</div>
        <div class="social-links">
          <a href="https://facebook.com"  target="_blank" rel="noopener" aria-label="Facebook"><iconify-icon icon="lucide:facebook"  style="font-size:20px"></iconify-icon></a>
          <a href="https://twitter.com"   target="_blank" rel="noopener" aria-label="Twitter"><iconify-icon icon="lucide:twitter"   style="font-size:20px"></iconify-icon></a>
          <a href="https://linkedin.com"  target="_blank" rel="noopener" aria-label="LinkedIn"><iconify-icon icon="lucide:linkedin"  style="font-size:20px"></iconify-icon></a>
          <a href="https://instagram.com" target="_blank" rel="noopener" aria-label="Instagram"><iconify-icon icon="lucide:instagram" style="font-size:20px"></iconify-icon></a>
        </div>
      </div>
    </div>
  </footer>

  <script>
    const ham = document.getElementById('hamburger');
    const mob = document.getElementById('mobileMenu');
    ham.addEventListener('click', () => { ham.classList.toggle('open'); mob.classList.toggle('open'); });

    // Demo shipment data keyed by tracking number
    const DEMO_SHIPMENTS = {
      'FX-20260001234': {
        status: 'In Transit',
        eta: 'Mar 18, 2026',
        service: 'Air Freight — Express',
        origin: 'New York, USA',
        destination: 'London, UK',
        weight: '142 kg',
        history: [
          { date: 'Mar 15, 2026 — 06:42 UTC', title: 'Departed origin facility', loc: 'JFK Air Cargo Hub, New York', done: true },
          { date: 'Mar 15, 2026 — 14:10 UTC', title: 'Arrived at transit hub', loc: 'Frankfurt Airport, Germany', done: true },
          { date: 'Mar 15, 2026 — 18:30 UTC', title: 'Customs clearance in progress', loc: 'Frankfurt, Germany', active: true },
          { date: 'Mar 17, 2026 (est.)', title: 'Departs for destination', loc: 'Frankfurt → London Heathrow', done: false },
          { date: 'Mar 18, 2026 (est.)', title: 'Out for delivery', loc: 'London, UK', done: false },
        ]
      },
      'FX-20260005678': {
        status: 'Delivered',
        eta: 'Mar 12, 2026',
        service: 'Ocean Freight — Standard',
        origin: 'Shanghai, China',
        destination: 'Los Angeles, USA',
        weight: '2,400 kg',
        history: [
          { date: 'Feb 28, 2026 — 09:00 UTC', title: 'Shipment picked up', loc: 'Shanghai, China', done: true },
          { date: 'Mar 1, 2026 — 16:00 UTC', title: 'Departed origin port', loc: 'Port of Shanghai', done: true },
          { date: 'Mar 10, 2026 — 11:30 UTC', title: 'Arrived at destination port', loc: 'Port of Los Angeles, USA', done: true },
          { date: 'Mar 11, 2026 — 08:00 UTC', title: 'Customs cleared', loc: 'Los Angeles, USA', done: true },
          { date: 'Mar 12, 2026 — 14:25 UTC', title: 'Delivered', loc: 'Los Angeles, CA', done: true },
        ]
      }
    };

    document.getElementById('trackBtn').addEventListener('click', doTrack);
    document.getElementById('trackingInput').addEventListener('keydown', e => {
      if (e.key === 'Enter') doTrack();
    });

    // Auto-track if 'id' query param is present (e.g. from landing page form)
    (function () {
      const params = new URLSearchParams(window.location.search);
      const preloadId = params.get('id');
      if (preloadId) {
        document.getElementById('trackingInput').value = preloadId;
        doTrack();
      }
    })();

    function doTrack() {
      const num = document.getElementById('trackingInput').value.trim().toUpperCase();
      const result    = document.getElementById('trackResult');
      const empty     = document.getElementById('trackEmpty');
      const notFound  = document.getElementById('trackNotFound');

      result.style.display   = 'none';
      empty.style.display    = 'none';
      notFound.style.display = 'none';

      if (!num) { empty.style.display = 'block'; return; }

      const data = DEMO_SHIPMENTS[num];
      if (!data) { notFound.style.display = 'block'; return; }

      document.getElementById('statusValue').textContent  = data.status;
      document.getElementById('metaTracking').textContent = num;
      document.getElementById('metaEta').textContent      = data.eta;
      document.getElementById('metaService').textContent  = data.service;
      document.getElementById('metaOrigin').textContent   = data.origin;
      document.getElementById('metaDest').textContent     = data.destination;
      document.getElementById('metaWeight').textContent   = data.weight;

      const tl = document.getElementById('trackTimeline');
      tl.innerHTML = data.history.map(h => `
        <div class="tl-item">
          <div class="tl-dot ${h.active ? 'active' : h.done ? 'done' : ''}"></div>
          <div class="tl-date">${h.date}</div>
          <div class="tl-title">${h.title}</div>
          <div class="tl-loc">${h.loc}</div>
        </div>`).join('');

      result.style.display = 'block';
    }
  </script>
  <script src="auth-nav.js"></script>
</body>
</html>
