<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <link rel="icon" href="favicon.svg" type="image/svg+xml" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Air Freight — Fastrux Logistics</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="shared.css" />
  <script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>
  <style>
    /* ── Intro ── */
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
    .service-intro-content h2 {
      font-size: 34px;
      font-weight: 700;
      margin-bottom: 20px;
      line-height: 1.25;
    }
    .service-intro-content p {
      color: var(--muted-foreground);
      margin-bottom: 16px;
      font-size: 16px;
      line-height: 1.75;
    }

    /* ── Feature cards ── */
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
      width: 52px;
      height: 52px;
      background: var(--secondary);
      border-radius: var(--radius-md);
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--primary);
      margin-bottom: 20px;
    }
    .feature-card h3 { font-size: 17px; font-weight: 600; margin-bottom: 10px; }
    .feature-card p  { font-size: 14px; color: var(--muted-foreground); line-height: 1.65; }

    /* ── Service tiers ── */
    .tiers-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 28px;
    }
    .tier-card {
      background: var(--card);
      border: 2px solid var(--border);
      border-radius: var(--radius-xl);
      padding: 40px 32px;
      text-align: center;
      position: relative;
      transition: border-color .2s, box-shadow .2s;
      display: flex;
      flex-direction: column;
    }
    .tier-card:hover,
    .tier-card.featured {
      border-color: var(--primary);
      box-shadow: 0 8px 32px rgba(11,111,255,.12);
    }
    .tier-popular {
      position: absolute;
      top: -14px;
      left: 50%;
      transform: translateX(-50%);
      background: var(--primary);
      color: #fff;
      padding: 4px 16px;
      border-radius: 999px;
      font-size: 12px;
      font-weight: 700;
      white-space: nowrap;
    }
    .tier-icon {
      width: 64px;
      height: 64px;
      background: var(--secondary);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--primary);
      margin: 0 auto 20px;
    }
    .tier-card h3    { font-size: 22px; font-weight: 700; margin-bottom: 8px; }
    .tier-transit    { font-size: 30px; font-weight: 800; color: var(--primary); margin-bottom: 4px; }
    .tier-sublabel   { font-size: 13px; color: var(--muted-foreground); margin-bottom: 24px; }
    .tier-features   { list-style: none; margin-bottom: 32px; text-align: left; flex: 1; }
    .tier-features li {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 14px;
      padding: 8px 0;
      border-bottom: 1px solid var(--border);
      color: var(--foreground);
    }
    .tier-features li:last-child { border-bottom: none; }

    /* ── Routes table ── */
    .routes-table {
      width: 100%;
      border-collapse: collapse;
      background: var(--card);
      border-radius: var(--radius-lg);
      overflow: hidden;
      border: 1px solid var(--border);
    }
    .routes-table th {
      background: var(--secondary);
      padding: 16px 24px;
      text-align: left;
      font-size: 13px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .5px;
      color: var(--foreground);
    }
    .routes-table td {
      padding: 16px 24px;
      font-size: 14px;
      border-top: 1px solid var(--border);
      color: var(--foreground);
    }
    .routes-table tr:hover td { background: var(--secondary); }
    .badge {
      display: inline-block;
      padding: 3px 10px;
      background: var(--secondary);
      color: var(--primary);
      border-radius: 999px;
      font-size: 12px;
      font-weight: 600;
    }
    .badge-green {
      background: #e6f9ee;
      color: var(--success);
    }

    /* ── Stats banner ── */
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
    .stats-banner h3  { font-size: 40px; font-weight: 800; margin-bottom: 8px; }
    .stats-banner p   { font-size: 14px; opacity: .85; }

    /* ── Other services links ── */
    .other-services-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 24px;
    }
    .other-service-link {
      display: flex;
      gap: 16px;
      align-items: center;
      text-decoration: none;
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      padding: 24px;
      transition: box-shadow .2s, transform .2s;
    }
    .other-service-link:hover {
      box-shadow: 0 6px 24px rgba(11,111,255,.1);
      transform: translateY(-2px);
    }
    .other-service-icon {
      width: 48px;
      height: 48px;
      min-width: 48px;
      background: var(--secondary);
      border-radius: var(--radius-md);
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--primary);
    }
    .other-service-link h3 { font-size: 16px; font-weight: 600; margin-bottom: 4px; color: var(--foreground); }
    .other-service-link p  { font-size: 13px; color: var(--muted-foreground); }

    /* ── Responsive ── */
    @media (max-width: 1024px) {
      .features-grid   { grid-template-columns: repeat(2, 1fr); }
      .tiers-grid      { grid-template-columns: 1fr; max-width: 480px; margin: 0 auto; }
      .stats-banner    { grid-template-columns: repeat(2, 1fr); }
      .other-services-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 768px) {
      .service-intro-grid { grid-template-columns: 1fr; }
      .features-grid      { grid-template-columns: 1fr; }
      .stats-banner       { padding: 40px 24px; }
    }
    @media (max-width: 480px) {
      .stats-banner        { grid-template-columns: repeat(2, 1fr); }
      .other-services-grid { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>

  <!-- ── HEADER ── -->
  <header class="header">
    <div class="container header-content">
      <a href="/" class="logo">
        <iconify-icon icon="lucide:truck" style="font-size:28px;color:var(--primary)"></iconify-icon>
        Fastrux
      </a>
      <nav class="nav-links">
        <a class="nav-link" href="/">Home</a>
        <a class="nav-link active" href="/#services">Services</a>
        <a class="nav-link" href="track">Tracking</a>

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
    <a class="nav-link" href="/">Home</a>
    <a class="nav-link active" href="/#services">Services</a>
    <a class="nav-link" href="track">Tracking</a>

    <a class="nav-link" href="contact">Contact</a>
        <a class="nav-link" href="driver-onboarding">Drive with Us</a>
        <a class="nav-link" href="loadboard">Loadboard</a>
        <a class="nav-link" href="marketplace">Marketplace</a>
    <div class="header-actions" style="margin-top:8px;">
      <a class="btn btn-outline" href="login">Login</a>
      <a class="btn btn-primary" href="quote">Get a Quote</a>
    </div>
  </nav>

  <!-- ── HERO ── -->
  <div class="page-hero">
    <div class="container" style="text-align:center;">
      <div style="display:inline-flex;align-items:center;justify-content:center;width:72px;height:72px;background:rgba(255,255,255,.15);border-radius:var(--radius-lg);margin-bottom:20px;">
        <iconify-icon icon="lucide:plane" style="font-size:36px"></iconify-icon>
      </div>
      <h1>Air Freight</h1>
      <p>Rapid global delivery for time-critical shipments. 200+ airline partners, every major airport, unmatched speed.</p>
    </div>
  </div>

  <!-- ── BREADCRUMB ── -->
  <div style="background:var(--card);border-bottom:1px solid var(--border);padding:12px 0;">
    <div class="container">
      <nav style="font-size:13px;color:var(--muted-foreground);display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
        <a href="/" style="color:var(--primary);">Home</a>
        <iconify-icon icon="lucide:chevron-right" style="font-size:14px"></iconify-icon>
        <a href="/#services" style="color:var(--primary);">Services</a>
        <iconify-icon icon="lucide:chevron-right" style="font-size:14px"></iconify-icon>
        <span>Air Freight</span>
      </nav>
    </div>
  </div>

  <!-- ── INTRO ── -->
  <section class="section">
    <div class="container service-intro-grid">
      <div class="service-intro-content">
        <h2>When Speed is Non-Negotiable</h2>
        <p>Fastrux air freight connects your business to 180+ countries through a network of 200+ airline partners and partnerships with every major international hub airport. Whether it's a small urgent parcel or a full-charter aircraft load, we have the solution.</p>
        <p>We offer three service tiers — Standard, Express, and Charter — so you can precisely balance speed and cost for every shipment. All tiers include door-to-door handling, customs clearance, and real-time flight tracking.</p>
        <div style="display:flex;gap:16px;margin-top:24px;flex-wrap:wrap;">
          <a class="btn btn-primary" href="quote">Get Air Freight Quote</a>
          <a class="btn btn-outline" href="contact">Speak to a Specialist</a>
        </div>
      </div>
      <img
        src="https://images.unsplash.com/photo-1436491865332-7a61a109cc05?w=800&q=80"
        alt="Fastrux air freight cargo plane in flight"
        loading="lazy"
      />
    </div>
  </section>

  <!-- ── STATS BANNER ── -->
  <section style="padding:0 0 96px;">
    <div class="container">
      <div class="stats-banner">
        <div><h3>200+</h3><p>Airline partners worldwide</p></div>
        <div><h3>500+</h3><p>Destination airports</p></div>
        <div><h3>24h</h3><p>Fastest transit available</p></div>
        <div><h3>99.2%</h3><p>Flights departed on time</p></div>
      </div>
    </div>
  </section>

  <!-- ── SERVICE TIERS ── -->
  <section class="section section-alt">
    <div class="container">
      <h2 class="section-title">Choose Your Service Level</h2>
      <p class="section-subtitle">From economy consolidation to dedicated charter, we match your urgency and budget precisely.</p>
      <div class="tiers-grid">

        <!-- Standard -->
        <div class="tier-card">
          <div class="tier-icon">
            <iconify-icon icon="lucide:package" style="font-size:28px"></iconify-icon>
          </div>
          <h3>Standard Air</h3>
          <div class="tier-transit">3–5 days</div>
          <div class="tier-sublabel">Economy consolidation</div>
          <ul class="tier-features">
            <li><iconify-icon icon="lucide:check" style="font-size:15px;color:var(--primary)"></iconify-icon> LCL consolidation</li>
            <li><iconify-icon icon="lucide:check" style="font-size:15px;color:var(--primary)"></iconify-icon> Online tracking</li>
            <li><iconify-icon icon="lucide:check" style="font-size:15px;color:var(--primary)"></iconify-icon> Customs clearance</li>
            <li><iconify-icon icon="lucide:check" style="font-size:15px;color:var(--primary)"></iconify-icon> Basic cargo insurance</li>
            <li><iconify-icon icon="lucide:check" style="font-size:15px;color:var(--primary)"></iconify-icon> Airport-to-airport</li>
          </ul>
          <a class="btn btn-outline" href="quote" style="width:100%;">Get Quote</a>
        </div>

        <!-- Express — featured -->
        <div class="tier-card featured">
          <div class="tier-popular">Most Popular</div>
          <div class="tier-icon" style="background:var(--primary);">
            <iconify-icon icon="lucide:zap" style="font-size:28px;color:#fff"></iconify-icon>
          </div>
          <h3>Express Air</h3>
          <div class="tier-transit">24–48 hrs</div>
          <div class="tier-sublabel">Priority next-flight-out</div>
          <ul class="tier-features">
            <li><iconify-icon icon="lucide:check" style="font-size:15px;color:var(--primary)"></iconify-icon> Priority space allocation</li>
            <li><iconify-icon icon="lucide:check" style="font-size:15px;color:var(--primary)"></iconify-icon> Real-time flight tracking</li>
            <li><iconify-icon icon="lucide:check" style="font-size:15px;color:var(--primary)"></iconify-icon> Expedited customs clearance</li>
            <li><iconify-icon icon="lucide:check" style="font-size:15px;color:var(--primary)"></iconify-icon> Full cargo insurance</li>
            <li><iconify-icon icon="lucide:check" style="font-size:15px;color:var(--primary)"></iconify-icon> Door-to-door delivery</li>
            <li><iconify-icon icon="lucide:check" style="font-size:15px;color:var(--primary)"></iconify-icon> Dedicated account manager</li>
          </ul>
          <a class="btn btn-primary" href="quote" style="width:100%;">Get Quote</a>
        </div>

        <!-- Charter -->
        <div class="tier-card">
          <div class="tier-icon">
            <iconify-icon icon="lucide:plane-takeoff" style="font-size:28px"></iconify-icon>
          </div>
          <h3>Air Charter</h3>
          <div class="tier-transit">On demand</div>
          <div class="tier-sublabel">Dedicated full aircraft</div>
          <ul class="tier-features">
            <li><iconify-icon icon="lucide:check" style="font-size:15px;color:var(--primary)"></iconify-icon> Entire aircraft capacity</li>
            <li><iconify-icon icon="lucide:check" style="font-size:15px;color:var(--primary)"></iconify-icon> Custom departure times</li>
            <li><iconify-icon icon="lucide:check" style="font-size:15px;color:var(--primary)"></iconify-icon> Oversized &amp; hazmat cargo</li>
            <li><iconify-icon icon="lucide:check" style="font-size:15px;color:var(--primary)"></iconify-icon> Premium insurance</li>
            <li><iconify-icon icon="lucide:check" style="font-size:15px;color:var(--primary)"></iconify-icon> 24/7 operations team</li>
            <li><iconify-icon icon="lucide:check" style="font-size:15px;color:var(--primary)"></iconify-icon> Live cargo escort option</li>
          </ul>
          <a class="btn btn-outline" href="contact" style="width:100%;">Contact Us</a>
        </div>

      </div>
    </div>
  </section>

  <!-- ── FEATURES ── -->
  <section class="section">
    <div class="container">
      <h2 class="section-title">Everything Included as Standard</h2>
      <p class="section-subtitle">Every Fastrux air freight booking comes with a comprehensive set of services at no hidden cost.</p>
      <div class="features-grid">
        <div class="feature-card">
          <div class="feature-icon"><iconify-icon icon="lucide:map-pin" style="font-size:24px"></iconify-icon></div>
          <h3>Real-Time Flight Tracking</h3>
          <p>Monitor your shipment at every stage from warehouse to aircraft to final delivery, updated live through our tracking portal.</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon"><iconify-icon icon="lucide:file-check" style="font-size:24px"></iconify-icon></div>
          <h3>Customs Clearance</h3>
          <p>Licensed customs brokers in every major destination country handle airway bills, import declarations, and duty management.</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon"><iconify-icon icon="lucide:shield-check" style="font-size:24px"></iconify-icon></div>
          <h3>Cargo Insurance</h3>
          <p>Comprehensive all-risk coverage from collection to delivery. Higher value limits available for charter and express shipments.</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon"><iconify-icon icon="lucide:thermometer" style="font-size:24px"></iconify-icon></div>
          <h3>Temperature Control</h3>
          <p>Validated cold-chain solutions for pharmaceuticals, food, and biologics with 2–8°C, 15–25°C, and deep-freeze options.</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon"><iconify-icon icon="lucide:alert-triangle" style="font-size:24px"></iconify-icon></div>
          <h3>Dangerous Goods (DGR)</h3>
          <p>IATA-certified dangerous goods handling for batteries, chemicals, and other regulated items on both passenger and cargo aircraft.</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon"><iconify-icon icon="lucide:headphones" style="font-size:24px"></iconify-icon></div>
          <h3>24/7 Support</h3>
          <p>Around-the-clock operations desk available by phone, email, or live chat — because urgent cargo doesn't follow business hours.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ── KEY ROUTES ── -->
  <section class="section section-alt">
    <div class="container">
      <h2 class="section-title">Key Air Routes</h2>
      <p class="section-subtitle">Direct and one-stop connections on the world's busiest air cargo corridors.</p>
      <div style="overflow-x:auto;">
        <table class="routes-table">
          <thead>
            <tr>
              <th>Origin</th>
              <th>Destination</th>
              <th>Standard</th>
              <th>Express</th>
              <th>Service</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>New York (JFK)</td>
              <td>London (LHR)</td>
              <td><span class="badge">3–4 days</span></td>
              <td><span class="badge badge-green">Next day</span></td>
              <td>Daily direct</td>
            </tr>
            <tr>
              <td>Los Angeles (LAX)</td>
              <td>Tokyo (NRT)</td>
              <td><span class="badge">3–5 days</span></td>
              <td><span class="badge badge-green">24–36 hrs</span></td>
              <td>Daily direct</td>
            </tr>
            <tr>
              <td>Shanghai (PVG)</td>
              <td>Frankfurt (FRA)</td>
              <td><span class="badge">4–5 days</span></td>
              <td><span class="badge badge-green">24–48 hrs</span></td>
              <td>Daily direct</td>
            </tr>
            <tr>
              <td>Singapore (SIN)</td>
              <td>New York (JFK)</td>
              <td><span class="badge">4–6 days</span></td>
              <td><span class="badge badge-green">36–48 hrs</span></td>
              <td>Daily via hub</td>
            </tr>
            <tr>
              <td>Dubai (DXB)</td>
              <td>Chicago (ORD)</td>
              <td><span class="badge">3–5 days</span></td>
              <td><span class="badge badge-green">24–36 hrs</span></td>
              <td>Daily direct</td>
            </tr>
            <tr>
              <td>Sydney (SYD)</td>
              <td>London (LHR)</td>
              <td><span class="badge">4–6 days</span></td>
              <td><span class="badge badge-green">36–48 hrs</span></td>
              <td>Daily via hub</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </section>

  <!-- ── CTA ── -->
  <section class="section" style="text-align:center;">
    <div class="container" style="max-width:580px;">
      <h2 class="section-title">Need it there fast?</h2>
      <p class="section-subtitle" style="margin-bottom:32px;">Get a competitive air freight quote in under 60 seconds. We'll have your cargo in the air within hours.</p>
      <div style="display:flex;gap:16px;justify-content:center;flex-wrap:wrap;">
        <a class="btn btn-primary" href="quote" style="padding:14px 32px;font-size:16px;">Get a Free Quote</a>
        <a class="btn btn-outline" href="contact" style="padding:14px 32px;font-size:16px;">Talk to a Specialist</a>
      </div>
    </div>
  </section>

  <!-- ── OTHER SERVICES ── -->
  <section class="section section-alt">
    <div class="container">
      <h2 class="section-title">Explore Other Services</h2>
      <p class="section-subtitle">Need a different shipping mode? We cover every option.</p>
      <div class="other-services-grid">
        <a href="ocean-freight" class="other-service-link">
          <div class="other-service-icon"><iconify-icon icon="lucide:ship" style="font-size:24px"></iconify-icon></div>
          <div><h3>Ocean Freight</h3><p>Cost-effective maritime shipping for large volumes.</p></div>
        </a>
        <a href="ground-transport" class="other-service-link">
          <div class="other-service-icon"><iconify-icon icon="lucide:truck" style="font-size:24px"></iconify-icon></div>
          <div><h3>Ground Transport</h3><p>Regional and national road freight solutions.</p></div>
        </a>
        <a href="warehousing" class="other-service-link">
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
          <a href="/" class="logo">
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
            <div class="footer-contact-item"><iconify-icon icon="lucide:map-pin"></iconify-icon> 1008 Oak Chase way, Leander, TX 78641</div>
            <div class="footer-contact-item"><iconify-icon icon="lucide:phone"></iconify-icon><a href="tel:+2038896129">+203-889-6129</a></div>
            <div class="footer-contact-item"><iconify-icon icon="lucide:mail"></iconify-icon><a href="mailto:support@fastrux.com">support@fastrux.com</a></div>
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
  <script src="auth-nav.js"></script>
</body>
</html>