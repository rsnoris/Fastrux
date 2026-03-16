<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <link rel="icon" href="favicon.svg" type="image/svg+xml" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Ground Transport — Fastrux Logistics</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="shared.css" />
  <script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>
  <style>
    .service-intro-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 64px;
      align-items: center;
    }
    .service-intro-grid img {
      width: 100%;
      min-height: 380px;
      object-fit: cover;
      border-radius: var(--radius-xl);
      box-shadow: 0 20px 48px rgba(0,0,0,.1);
    }
    .service-intro-content h2 { font-size: 34px; font-weight: 700; margin-bottom: 20px; line-height: 1.25; }
    .service-intro-content p  { color: var(--muted-foreground); margin-bottom: 16px; font-size: 16px; line-height: 1.75; }

    .fleet-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 24px;
    }
    .fleet-card {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      padding: 28px 20px;
      text-align: center;
      transition: box-shadow .2s, transform .2s;
    }
    .fleet-card:hover {
      box-shadow: 0 6px 24px rgba(11,111,255,.1);
      transform: translateY(-2px);
    }
    .fleet-icon {
      width: 64px; height: 64px;
      background: var(--secondary);
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      color: var(--primary);
      margin: 0 auto 16px;
    }
    .fleet-card h3  { font-size: 16px; font-weight: 600; margin-bottom: 6px; }
    .fleet-card p   { font-size: 13px; color: var(--muted-foreground); line-height: 1.5; margin-bottom: 12px; }
    .fleet-capacity {
      font-size: 12px; font-weight: 700;
      color: var(--primary); background: var(--secondary);
      padding: 3px 10px; border-radius: 999px;
      display: inline-block;
    }

    .features-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 28px;
    }
    .feature-card {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      padding: 32px 28px;
    }
    .feature-icon {
      width: 52px; height: 52px;
      background: var(--secondary);
      border-radius: var(--radius-md);
      display: flex; align-items: center; justify-content: center;
      color: var(--primary);
      margin-bottom: 20px;
    }
    .feature-card h3 { font-size: 17px; font-weight: 600; margin-bottom: 10px; }
    .feature-card p  { font-size: 14px; color: var(--muted-foreground); line-height: 1.65; }

    .levels-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 28px;
    }
    .level-card {
      background: var(--card);
      border: 2px solid var(--border);
      border-radius: var(--radius-xl);
      padding: 36px 28px;
      display: flex;
      flex-direction: column;
      transition: border-color .2s, box-shadow .2s;
    }
    .level-card:hover,
    .level-card.featured {
      border-color: var(--primary);
      box-shadow: 0 8px 32px rgba(11,111,255,.12);
    }
    .level-card h3   { font-size: 20px; font-weight: 700; margin-bottom: 6px; }
    .level-ideal     { font-size: 13px; color: var(--muted-foreground); margin-bottom: 20px; font-style: italic; }
    .level-features  { list-style: none; flex: 1; }
    .level-features li {
      display: flex; align-items: flex-start; gap: 10px;
      font-size: 14px; padding: 8px 0;
      border-bottom: 1px solid var(--border);
      color: var(--foreground); line-height: 1.4;
    }
    .level-features li:last-child { border-bottom: none; }

    .coverage-card {
      background: linear-gradient(135deg, var(--secondary) 0%, #c7dcff 100%);
      border-radius: var(--radius-xl);
      padding: 80px 48px;
      text-align: center;
    }
    .coverage-card h2 { font-size: 28px; font-weight: 700; margin-bottom: 12px; }
    .coverage-card p  { color: var(--muted-foreground); max-width: 480px; margin: 0 auto 32px; }
    .coverage-cities  { display: flex; flex-wrap: wrap; justify-content: center; gap: 10px; margin-bottom: 32px; }
    .city-tag {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: 999px;
      padding: 6px 16px;
      font-size: 13px; font-weight: 500;
      color: var(--foreground);
    }

    .stats-banner {
      background: linear-gradient(135deg, var(--primary) 0%, #0950c7 100%);
      border-radius: var(--radius-xl);
      padding: 56px 48px;
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 32px;
      text-align: center;
      color: #fff;
    }
    .stats-banner h3 { font-size: 40px; font-weight: 800; margin-bottom: 8px; }
    .stats-banner p  { font-size: 14px; opacity: .85; }

    .other-services-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 24px;
    }
    .other-service-link {
      display: flex; gap: 16px; align-items: center;
      text-decoration: none;
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      padding: 24px;
      transition: box-shadow .2s, transform .2s;
    }
    .other-service-link:hover { box-shadow: 0 6px 24px rgba(11,111,255,.1); transform: translateY(-2px); }
    .other-service-icon {
      width: 48px; height: 48px; min-width: 48px;
      background: var(--secondary);
      border-radius: var(--radius-md);
      display: flex; align-items: center; justify-content: center;
      color: var(--primary);
    }
    .other-service-link h3 { font-size: 16px; font-weight: 600; margin-bottom: 4px; color: var(--foreground); }
    .other-service-link p  { font-size: 13px; color: var(--muted-foreground); }

    @media (max-width: 1024px) {
      .fleet-grid    { grid-template-columns: repeat(2, 1fr); }
      .features-grid { grid-template-columns: repeat(2, 1fr); }
      .levels-grid   { grid-template-columns: 1fr; max-width: 480px; margin: 0 auto; }
      .stats-banner  { grid-template-columns: repeat(2, 1fr); }
      .other-services-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 768px) {
      .service-intro-grid { grid-template-columns: 1fr; }
      .features-grid      { grid-template-columns: 1fr; }
      .coverage-card      { padding: 48px 24px; }
      .stats-banner       { padding: 40px 24px; }
    }
    @media (max-width: 480px) {
      .fleet-grid          { grid-template-columns: 1fr; }
      .stats-banner        { grid-template-columns: repeat(2, 1fr); }
      .other-services-grid { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>

  <!-- ── HEADER ── -->
  <header class="header">
    <div class="container header-content">
      <a href="index.php" class="logo">
        <iconify-icon icon="lucide:truck" style="font-size:28px;color:var(--primary)"></iconify-icon>
        Fastrux
      </a>
      <nav class="nav-links">
        <a class="nav-link" href="index.php">Home</a>
        <a class="nav-link active" href="index.php#services">Services</a>
        <a class="nav-link" href="track.php">Tracking</a>
        <a class="nav-link" href="about.php">About Us</a>
        <a class="nav-link" href="contact.php">Contact</a>
        <a class="nav-link" href="driver-onboarding.php">Drive with Us</a>
      </nav>
      <div class="header-actions">
        <a class="nav-link" href="login.php">Login</a>
        <a class="btn btn-primary" href="quote.php">Get a Quote</a>
      </div>
      <button class="hamburger" id="hamburger" aria-label="Toggle menu">
        <span></span><span></span><span></span>
      </button>
    </div>
  </header>
  <nav class="mobile-menu" id="mobileMenu">
    <a class="nav-link" href="index.php">Home</a>
    <a class="nav-link active" href="index.php#services">Services</a>
    <a class="nav-link" href="track.php">Tracking</a>
    <a class="nav-link" href="about.php">About Us</a>
    <a class="nav-link" href="contact.php">Contact</a>
        <a class="nav-link" href="driver-onboarding.php">Drive with Us</a>
    <div class="header-actions" style="margin-top:8px;">
      <a class="btn btn-outline" href="login.php">Login</a>
      <a class="btn btn-primary" href="quote.php">Get a Quote</a>
    </div>
  </nav>

  <!-- ── HERO ── -->
  <div class="page-hero">
    <div class="container" style="text-align:center;">
      <div style="display:inline-flex;align-items:center;justify-content:center;width:72px;height:72px;background:rgba(255,255,255,.15);border-radius:var(--radius-lg);margin-bottom:20px;">
        <iconify-icon icon="lucide:truck" style="font-size:36px"></iconify-icon>
      </div>
      <h1>Ground Transport</h1>
      <p>Reliable road freight across the nation. Modern fleet, live GPS tracking, and guaranteed delivery windows.</p>
    </div>
  </div>

  <!-- ── BREADCRUMB ── -->
  <div style="background:var(--card);border-bottom:1px solid var(--border);padding:12px 0;">
    <div class="container">
      <nav style="font-size:13px;color:var(--muted-foreground);display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
        <a href="index.php" style="color:var(--primary);">Home</a>
        <iconify-icon icon="lucide:chevron-right" style="font-size:14px"></iconify-icon>
        <a href="index.php#services" style="color:var(--primary);">Services</a>
        <iconify-icon icon="lucide:chevron-right" style="font-size:14px"></iconify-icon>
        <span>Ground Transport</span>
      </nav>
    </div>
  </div>

  <!-- ── INTRO ── -->
  <section class="section">
    <div class="container service-intro-grid">
      <div class="service-intro-content">
        <h2>Road Freight That Goes the Extra Mile</h2>
        <p>Fastrux operates one of North America's most modern commercial truck fleets — over 1,200 vehicles spanning vans, curtainsiders, flatbeds, and refrigerated trailers. Whether it's a single pallet or a full truckload, we cover every size of domestic shipment.</p>
        <p>Our network reaches every ZIP code in the contiguous United States plus major Canadian and Mexican destinations. Real-time GPS tracking on every vehicle means you always know exactly where your freight is and when it will arrive.</p>
        <div style="display:flex;gap:16px;margin-top:24px;flex-wrap:wrap;">
          <a class="btn btn-primary" href="quote.php">Get Ground Freight Quote</a>
          <a class="btn btn-outline" href="contact.php">Speak to a Specialist</a>
        </div>
      </div>
      <img
        src="https://images.unsplash.com/photo-1601584115197-04ecc0da31d7?w=800&q=80"
        alt="Fastrux ground transport fleet on the highway"
        loading="lazy"
      />
    </div>
  </section>

  <!-- ── STATS ── -->
  <section style="padding:0 0 96px;">
    <div class="container">
      <div class="stats-banner">
        <div><h3>1,200+</h3><p>Vehicles in our fleet</p></div>
        <div><h3>48</h3><p>US states covered</p></div>
        <div><h3>99.1%</h3><p>On-time delivery rate</p></div>
        <div><h3>Same Day</h3><p>Available in 40+ metro areas</p></div>
      </div>
    </div>
  </section>

  <!-- ── FLEET ── -->
  <section class="section section-alt">
    <div class="container">
      <h2 class="section-title">Our Fleet</h2>
      <p class="section-subtitle">The right vehicle for every shipment size and cargo type.</p>
      <div class="fleet-grid">
        <div class="fleet-card">
          <div class="fleet-icon">
            <iconify-icon icon="lucide:package" style="font-size:28px"></iconify-icon>
          </div>
          <h3>Cargo Van</h3>
          <p>Perfect for urgent small parcel and last-mile delivery in urban areas.</p>
          <span class="fleet-capacity">Up to 1,000 kg</span>
        </div>
        <div class="fleet-card">
          <div class="fleet-icon">
            <iconify-icon icon="lucide:truck" style="font-size:28px"></iconify-icon>
          </div>
          <h3>Box Truck</h3>
          <p>Mid-sized closed-body truck for regional LTL freight and retail deliveries.</p>
          <span class="fleet-capacity">Up to 5,000 kg</span>
        </div>
        <div class="fleet-card">
          <div class="fleet-icon">
            <iconify-icon icon="lucide:container" style="font-size:28px"></iconify-icon>
          </div>
          <h3>Semi-Trailer (FTL)</h3>
          <p>53-ft curtainsider for full truckload interstate freight at maximum capacity.</p>
          <span class="fleet-capacity">Up to 24,000 kg</span>
        </div>
        <div class="fleet-card">
          <div class="fleet-icon">
            <iconify-icon icon="lucide:thermometer" style="font-size:28px"></iconify-icon>
          </div>
          <h3>Reefer Trailer</h3>
          <p>Temperature-controlled refrigerated transport for perishables and pharma cargo.</p>
          <span class="fleet-capacity">Up to 22,000 kg</span>
        </div>
      </div>
    </div>
  </section>

  <!-- ── SERVICE LEVELS ── -->
  <section class="section">
    <div class="container">
      <h2 class="section-title">Service Levels</h2>
      <p class="section-subtitle">Choose the delivery speed that fits your timeline and budget.</p>
      <div class="levels-grid">

        <!-- LTL Economy -->
        <div class="level-card">
          <h3>LTL — Economy</h3>
          <p class="level-ideal">Ideal for shipments under 5 pallets</p>
          <ul class="level-features">
            <li><iconify-icon icon="lucide:check" style="font-size:15px;color:var(--primary)"></iconify-icon>Less-than-truckload consolidation</li>
            <li><iconify-icon icon="lucide:check" style="font-size:15px;color:var(--primary)"></iconify-icon>3–5 business day delivery</li>
            <li><iconify-icon icon="lucide:check" style="font-size:15px;color:var(--primary)"></iconify-icon>Online booking and tracking</li>
            <li><iconify-icon icon="lucide:check" style="font-size:15px;color:var(--primary)"></iconify-icon>Liftgate service available</li>
            <li><iconify-icon icon="lucide:check" style="font-size:15px;color:var(--primary)"></iconify-icon>Digital proof of delivery</li>
          </ul>
          <a class="btn btn-outline" href="quote.php" style="width:100%;margin-top:24px;">Get Quote</a>
        </div>

        <!-- FTL Standard — featured -->
        <div class="level-card featured">
          <h3>FTL — Standard</h3>
          <p class="level-ideal">Ideal for 6+ pallets or dedicated loads</p>
          <ul class="level-features">
            <li><iconify-icon icon="lucide:check" style="font-size:15px;color:var(--primary)"></iconify-icon>Dedicated full truckload</li>
            <li><iconify-icon icon="lucide:check" style="font-size:15px;color:var(--primary)"></iconify-icon>1–3 business day delivery</li>
            <li><iconify-icon icon="lucide:check" style="font-size:15px;color:var(--primary)"></iconify-icon>Real-time GPS tracking</li>
            <li><iconify-icon icon="lucide:check" style="font-size:15px;color:var(--primary)"></iconify-icon>Guaranteed delivery window</li>
            <li><iconify-icon icon="lucide:check" style="font-size:15px;color:var(--primary)"></iconify-icon>Dedicated account manager</li>
            <li><iconify-icon icon="lucide:check" style="font-size:15px;color:var(--primary)"></iconify-icon>Digital POD &amp; e-invoicing</li>
          </ul>
          <a class="btn btn-primary" href="quote.php" style="width:100%;margin-top:24px;">Get Quote</a>
        </div>

        <!-- Express Same Day -->
        <div class="level-card">
          <h3>Express — Same Day</h3>
          <p class="level-ideal">Ideal for urgent, time-critical cargo</p>
          <ul class="level-features">
            <li><iconify-icon icon="lucide:check" style="font-size:15px;color:var(--primary)"></iconify-icon>Same-day collection &amp; delivery</li>
            <li><iconify-icon icon="lucide:check" style="font-size:15px;color:var(--primary)"></iconify-icon>Available in 40+ metro areas</li>
            <li><iconify-icon icon="lucide:check" style="font-size:15px;color:var(--primary)"></iconify-icon>Minute-by-minute live tracking</li>
            <li><iconify-icon icon="lucide:check" style="font-size:15px;color:var(--primary)"></iconify-icon>Priority dispatch team</li>
            <li><iconify-icon icon="lucide:check" style="font-size:15px;color:var(--primary)"></iconify-icon>24/7 operations support</li>
            <li><iconify-icon icon="lucide:check" style="font-size:15px;color:var(--primary)"></iconify-icon>Premium cargo insurance</li>
          </ul>
          <a class="btn btn-outline" href="quote.php" style="width:100%;margin-top:24px;">Get Quote</a>
        </div>

      </div>
    </div>
  </section>

  <!-- ── FEATURES ── -->
  <section class="section section-alt">
    <div class="container">
      <h2 class="section-title">Everything Included as Standard</h2>
      <p class="section-subtitle">No hidden extras — every Fastrux ground freight booking comes fully equipped.</p>
      <div class="features-grid">
        <div class="feature-card">
          <div class="feature-icon"><iconify-icon icon="lucide:map-pin" style="font-size:24px"></iconify-icon></div>
          <h3>Live GPS Tracking</h3>
          <p>Track your truck on our web portal or mobile app with ETAs that update in real time as your driver progresses the route.</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon"><iconify-icon icon="lucide:bell" style="font-size:24px"></iconify-icon></div>
          <h3>Delivery Notifications</h3>
          <p>Automated SMS and email alerts at collection, in transit, out for delivery, and on arrival — keeping every stakeholder informed.</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon"><iconify-icon icon="lucide:clipboard-check" style="font-size:24px"></iconify-icon></div>
          <h3>Digital Proof of Delivery</h3>
          <p>Electronic signature capture and timestamped photographic POD delivered to your inbox within minutes of drop-off.</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon"><iconify-icon icon="lucide:shield-check" style="font-size:24px"></iconify-icon></div>
          <h3>Cargo Insurance</h3>
          <p>All road freight shipments are covered by default. Enhanced limits available for high-value goods at checkout.</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon"><iconify-icon icon="lucide:leaf" style="font-size:24px"></iconify-icon></div>
          <h3>Eco Fleet</h3>
          <p>40 electric delivery vans operating across New York and Los Angeles, with EV expansion to 15 more cities by end of 2026.</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon"><iconify-icon icon="lucide:headphones" style="font-size:24px"></iconify-icon></div>
          <h3>24/7 Dispatch Support</h3>
          <p>Our operations centre is staffed around the clock for urgent reroutes, missed-delivery reschedules, and driver coordination.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ── COVERAGE ── -->
  <section class="section">
    <div class="container">
      <div class="coverage-card">
        <iconify-icon icon="lucide:map" style="font-size:56px;color:var(--primary);opacity:.35;margin-bottom:24px;display:block;"></iconify-icon>
        <h2>Nationwide Coverage</h2>
        <p>From coast to coast, our ground network reaches every corner of the contiguous United States, plus key Canadian and Mexican markets.</p>
        <div class="coverage-cities">
          <span class="city-tag">New York</span>
          <span class="city-tag">Los Angeles</span>
          <span class="city-tag">Chicago</span>
          <span class="city-tag">Houston</span>
          <span class="city-tag">Phoenix</span>
          <span class="city-tag">Philadelphia</span>
          <span class="city-tag">San Antonio</span>
          <span class="city-tag">San Diego</span>
          <span class="city-tag">Dallas</span>
          <span class="city-tag">San Jose</span>
          <span class="city-tag">Austin</span>
          <span class="city-tag">Seattle</span>
          <span class="city-tag">Denver</span>
          <span class="city-tag">Miami</span>
          <span class="city-tag">Atlanta</span>
          <span class="city-tag">Toronto</span>
          <span class="city-tag">Monterrey</span>
          <span class="city-tag">+ All 48 States</span>
        </div>
        <a class="btn btn-primary" href="quote.php" style="padding:14px 32px;font-size:16px;">
          Check My Route
          <iconify-icon icon="lucide:arrow-right" style="font-size:16px;margin-left:8px"></iconify-icon>
        </a>
      </div>
    </div>
  </section>

  <!-- ── CTA ── -->
  <section class="section section-alt" style="text-align:center;">
    <div class="container" style="max-width:580px;">
      <h2 class="section-title">Ready to move your cargo?</h2>
      <p class="section-subtitle" style="margin-bottom:32px;">Get an instant ground freight quote. Same-day collection available in 40+ cities.</p>
      <div style="display:flex;gap:16px;justify-content:center;flex-wrap:wrap;">
        <a class="btn btn-primary" href="quote.php" style="padding:14px 32px;font-size:16px;">Get a Free Quote</a>
        <a class="btn btn-outline" href="contact.php" style="padding:14px 32px;font-size:16px;">Talk to a Specialist</a>
      </div>
    </div>
  </section>

  <!-- ── OTHER SERVICES ── -->
  <section class="section">
    <div class="container">
      <h2 class="section-title">Explore Other Services</h2>
      <p class="section-subtitle">Complete your end-to-end supply chain with our full service offering.</p>
      <div class="other-services-grid">
        <a href="ocean-freight.php" class="other-service-link">
          <div class="other-service-icon"><iconify-icon icon="lucide:ship" style="font-size:24px"></iconify-icon></div>
          <div><h3>Ocean Freight</h3><p>Cost-effective maritime shipping for large volumes.</p></div>
        </a>
        <a href="air-freight.php" class="other-service-link">
          <div class="other-service-icon"><iconify-icon icon="lucide:plane" style="font-size:24px"></iconify-icon></div>
          <div><h3>Air Freight</h3><p>Express delivery for time-critical global cargo.</p></div>
        </a>
        <a href="warehousing.php" class="other-service-link">
          <div class="other-service-icon"><iconify-icon icon="lucide:package-check" style="font-size:24px"></iconify-icon></div>
          <div><h3>Warehousing</h3><p>Secure storage, inventory management, and fulfilment.</p></div>
        </a>
      </div>
    </div>
  </section>

  <!-- ── FOOTER ── -->
  <footer class="footer">
    <div class="container">
      <div class="footer-grid">
        <div class="footer-brand">
          <a href="index.php" class="logo">
            <iconify-icon icon="lucide:truck" style="font-size:24px;color:var(--primary)"></iconify-icon>
            Fastrux
          </a>
          <p>Delivering excellence in logistics and supply chain management worldwide. Your trusted partner for seamless transportation.</p>
        </div>
        <div>
          <h4 class="footer-heading">Services</h4>
          <div class="footer-links">
            <a href="ocean-freight.php">Ocean Freight</a>
            <a href="air-freight.php">Air Freight</a>
            <a href="ground-transport.php">Ground Transport</a>
            <a href="warehousing.php">Warehousing</a>
          </div>
        </div>
        <div>
          <h4 class="footer-heading">Company</h4>
          <div class="footer-links">
            <a href="about.php">About Us</a>
            <a href="careers.php">Careers</a>
            <a href="driver-onboarding.php">Drive with Us</a>
            <a href="news.php">News &amp; Media</a>
            <a href="contact.php">Contact</a>
          </div>
        </div>
        <div>
          <h4 class="footer-heading">Contact</h4>
          <div class="footer-links">
            <div class="footer-contact-item">
              <iconify-icon icon="lucide:map-pin"></iconify-icon> 1008 Oak Chase way, Leander, TX 78641
            </div>
            <div class="footer-contact-item">
              <iconify-icon icon="lucide:phone"></iconify-icon>
              <a href="tel:+2038896129">+203-889-6129</a>
            </div>
            <div class="footer-contact-item">
              <iconify-icon icon="lucide:mail"></iconify-icon>
              <a href="mailto:support@fastrux.com">support@fastrux.com</a>
            </div>
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
  </script>
</body>
</html>