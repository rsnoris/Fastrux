<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <link rel="icon" href="favicon.svg" type="image/svg+xml" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Careers — Fastrux Logistics</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="shared.css" />
  <script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>
  <style>
    /* Filter tabs */
    .dept-filters {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      margin-bottom: 40px;
    }
    .dept-btn {
      padding: 8px 18px;
      border-radius: 999px;
      border: 1.5px solid var(--border);
      background: var(--card);
      font-family: var(--font-family-body);
      font-size: 14px;
      font-weight: 500;
      cursor: pointer;
      color: var(--foreground);
      transition: all .2s;
    }
    .dept-btn.active,
    .dept-btn:hover {
      background: var(--primary);
      border-color: var(--primary);
      color: #fff;
    }

    /* Job listings */
    .jobs-list { display: flex; flex-direction: column; gap: 16px; }
    .job-card {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      padding: 28px 32px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 24px;
      transition: box-shadow .2s, transform .2s;
    }
    .job-card:hover { box-shadow: 0 6px 24px rgba(11,111,255,.1); transform: translateY(-1px); }
    .job-title  { font-size: 17px; font-weight: 600; margin-bottom: 6px; }
    .job-meta   { display: flex; gap: 16px; flex-wrap: wrap; }
    .job-tag    {
      display: inline-flex; align-items: center; gap: 5px;
      font-size: 13px; color: var(--muted-foreground);
    }
    .job-badge  {
      display: inline-block;
      padding: 3px 10px;
      border-radius: 999px;
      font-size: 12px;
      font-weight: 600;
      background: var(--secondary);
      color: var(--primary);
    }
    .job-card .btn { white-space: nowrap; }
    .no-jobs { text-align: center; padding: 48px 0; color: var(--muted-foreground); }

    /* State + role filters */
    .job-filters { display: flex; gap: 16px; flex-wrap: wrap; align-items: center; margin-bottom: 40px; }
    .state-select {
      padding: 8px 18px;
      border-radius: 999px;
      border: 1.5px solid var(--border);
      background: var(--card);
      font-family: var(--font-family-body);
      font-size: 14px;
      font-weight: 500;
      cursor: pointer;
      color: var(--foreground);
      outline: none;
      transition: border-color .2s;
      appearance: none;
      -webkit-appearance: none;
      padding-right: 32px;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 12px center;
    }
    .state-select:focus { border-color: var(--primary); }
    .dept-filters { margin-bottom: 0; }

    @media (max-width: 768px) {
      .job-card { flex-direction: column; align-items: flex-start; }
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
        <a class="nav-link" href="index.php#services">Services</a>
        <a class="nav-link" href="track.php">Tracking</a>
        <a class="nav-link" href="contact.php">Contact</a>
        <a class="nav-link" href="driver-onboarding.php">Drive with Us</a>
        <a class="nav-link" href="loadboard.php">Loadboard</a>
        <a class="nav-link" href="marketplace.php">Marketplace</a>
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
    <a class="nav-link" href="index.php#services">Services</a>
    <a class="nav-link" href="track.php">Tracking</a>
    <a class="nav-link" href="contact.php">Contact</a>
        <a class="nav-link" href="driver-onboarding.php">Drive with Us</a>
        <a class="nav-link" href="loadboard.php">Loadboard</a>
        <a class="nav-link" href="marketplace.php">Marketplace</a>
    <div class="header-actions" style="margin-top:8px;">
      <a class="btn btn-outline" href="login.php">Login</a>
      <a class="btn btn-primary" href="quote.php">Get a Quote</a>
    </div>
  </nav>

  <!-- ── HERO ── -->
  <div class="page-hero">
    <div class="container">
      <h1>Careers at Fastrux</h1>
      <p>Join 3,400+ logistics professionals moving the world's cargo. Build your career where it matters.</p>
    </div>
  </div>

  <!-- ── JOB LISTINGS ── -->
  <section class="section section-alt">
    <div class="container">
      <h2 class="section-title">Driver, Owner &amp; Operator Positions</h2>
      <p class="section-subtitle">We operate in all 50 states. Find a driving or owner-operator opportunity near you.</p>

      <div class="job-filters">
        <select id="stateFilter" class="state-select" aria-label="Filter by state">
          <option value="all">All States</option>
          <option value="alabama">Alabama</option>
          <option value="alaska">Alaska</option>
          <option value="arizona">Arizona</option>
          <option value="arkansas">Arkansas</option>
          <option value="california">California</option>
          <option value="colorado">Colorado</option>
          <option value="connecticut">Connecticut</option>
          <option value="delaware">Delaware</option>
          <option value="florida">Florida</option>
          <option value="georgia">Georgia</option>
          <option value="hawaii">Hawaii</option>
          <option value="idaho">Idaho</option>
          <option value="illinois">Illinois</option>
          <option value="indiana">Indiana</option>
          <option value="iowa">Iowa</option>
          <option value="kansas">Kansas</option>
          <option value="kentucky">Kentucky</option>
          <option value="louisiana">Louisiana</option>
          <option value="maine">Maine</option>
          <option value="maryland">Maryland</option>
          <option value="massachusetts">Massachusetts</option>
          <option value="michigan">Michigan</option>
          <option value="minnesota">Minnesota</option>
          <option value="mississippi">Mississippi</option>
          <option value="missouri">Missouri</option>
          <option value="montana">Montana</option>
          <option value="nebraska">Nebraska</option>
          <option value="nevada">Nevada</option>
          <option value="new-hampshire">New Hampshire</option>
          <option value="new-jersey">New Jersey</option>
          <option value="new-mexico">New Mexico</option>
          <option value="new-york">New York</option>
          <option value="north-carolina">North Carolina</option>
          <option value="north-dakota">North Dakota</option>
          <option value="ohio">Ohio</option>
          <option value="oklahoma">Oklahoma</option>
          <option value="oregon">Oregon</option>
          <option value="pennsylvania">Pennsylvania</option>
          <option value="rhode-island">Rhode Island</option>
          <option value="south-carolina">South Carolina</option>
          <option value="south-dakota">South Dakota</option>
          <option value="tennessee">Tennessee</option>
          <option value="texas">Texas</option>
          <option value="utah">Utah</option>
          <option value="vermont">Vermont</option>
          <option value="virginia">Virginia</option>
          <option value="washington">Washington</option>
          <option value="west-virginia">West Virginia</option>
          <option value="wisconsin">Wisconsin</option>
          <option value="wyoming">Wyoming</option>
        </select>
        <div class="dept-filters" id="roleFilters">
          <button class="dept-btn active" data-role="all">All Roles</button>
          <button class="dept-btn" data-role="Driver">Driver</button>
          <button class="dept-btn" data-role="Owner &amp; Operator">Owner &amp; Operator</button>
        </div>
      </div>

      <div class="jobs-list" id="jobsList"></div>
    </div>
  </section>

  <!-- ── CTA ── -->
  <section class="section" style="text-align:center;">
    <div class="container" style="max-width:560px;">
      <h2 class="section-title">Don't see a fit?</h2>
      <p class="section-subtitle" style="margin-bottom:32px;">We're always on the lookout for great people. Send us your CV and we'll keep you in mind for future openings.</p>
      <a class="btn btn-primary" href="mailto:careers@fastrux.com" style="padding:14px 32px;font-size:16px;">
        Send Open Application
        <iconify-icon icon="lucide:send" style="font-size:16px;margin-left:8px"></iconify-icon>
      </a>
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
            <a href="careers.php">Careers</a>
            <a href="driver-onboarding.php">Drive with Us</a>
            <a class="nav-link" href="loadboard.php">Loadboard</a>
            <a class="nav-link" href="marketplace.php">Marketplace</a>
            <a href="news.php">News &amp; Media</a>
            <a href="contact.php">Contact</a>
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

    const STATES = [
      { key: 'alabama',       name: 'Alabama' },
      { key: 'alaska',        name: 'Alaska' },
      { key: 'arizona',       name: 'Arizona' },
      { key: 'arkansas',      name: 'Arkansas' },
      { key: 'california',    name: 'California' },
      { key: 'colorado',      name: 'Colorado' },
      { key: 'connecticut',   name: 'Connecticut' },
      { key: 'delaware',      name: 'Delaware' },
      { key: 'florida',       name: 'Florida' },
      { key: 'georgia',       name: 'Georgia' },
      { key: 'hawaii',        name: 'Hawaii' },
      { key: 'idaho',         name: 'Idaho' },
      { key: 'illinois',      name: 'Illinois' },
      { key: 'indiana',       name: 'Indiana' },
      { key: 'iowa',          name: 'Iowa' },
      { key: 'kansas',        name: 'Kansas' },
      { key: 'kentucky',      name: 'Kentucky' },
      { key: 'louisiana',     name: 'Louisiana' },
      { key: 'maine',         name: 'Maine' },
      { key: 'maryland',      name: 'Maryland' },
      { key: 'massachusetts', name: 'Massachusetts' },
      { key: 'michigan',      name: 'Michigan' },
      { key: 'minnesota',     name: 'Minnesota' },
      { key: 'mississippi',   name: 'Mississippi' },
      { key: 'missouri',      name: 'Missouri' },
      { key: 'montana',       name: 'Montana' },
      { key: 'nebraska',      name: 'Nebraska' },
      { key: 'nevada',        name: 'Nevada' },
      { key: 'new-hampshire', name: 'New Hampshire' },
      { key: 'new-jersey',    name: 'New Jersey' },
      { key: 'new-mexico',    name: 'New Mexico' },
      { key: 'new-york',      name: 'New York' },
      { key: 'north-carolina', name: 'North Carolina' },
      { key: 'north-dakota',  name: 'North Dakota' },
      { key: 'ohio',          name: 'Ohio' },
      { key: 'oklahoma',      name: 'Oklahoma' },
      { key: 'oregon',        name: 'Oregon' },
      { key: 'pennsylvania',  name: 'Pennsylvania' },
      { key: 'rhode-island',  name: 'Rhode Island' },
      { key: 'south-carolina', name: 'South Carolina' },
      { key: 'south-dakota',  name: 'South Dakota' },
      { key: 'tennessee',     name: 'Tennessee' },
      { key: 'texas',         name: 'Texas' },
      { key: 'utah',          name: 'Utah' },
      { key: 'vermont',       name: 'Vermont' },
      { key: 'virginia',      name: 'Virginia' },
      { key: 'washington',    name: 'Washington' },
      { key: 'west-virginia', name: 'West Virginia' },
      { key: 'wisconsin',     name: 'Wisconsin' },
      { key: 'wyoming',       name: 'Wyoming' },
    ];

    const JOBS = STATES.flatMap(s => [
      { title: 'Driver',             state: s.key, location: s.name, type: 'Full-time',              role: 'Driver' },
      { title: 'Owner & Operator',   state: s.key, location: s.name, type: 'Independent Contractor', role: 'Owner & Operator' },
    ]);

    let currentState = 'all';
    let currentRole  = 'all';

    function renderJobs() {
      const list = document.getElementById('jobsList');
      let filtered = JOBS;
      if (currentState !== 'all') filtered = filtered.filter(j => j.state === currentState);
      if (currentRole  !== 'all') filtered = filtered.filter(j => j.role  === currentRole);
      if (!filtered.length) {
        list.innerHTML = '<div class="no-jobs"><iconify-icon icon="lucide:search-x" style="font-size:40px;margin-bottom:12px;display:block;margin-left:auto;margin-right:auto"></iconify-icon>No open roles matching your selection right now.</div>';
        return;
      }
      list.innerHTML = filtered.map(j => `
        <div class="job-card">
          <div>
            <div class="job-title">${j.title} — ${j.location}</div>
            <div class="job-meta">
              <span class="job-tag"><iconify-icon icon="lucide:map-pin" style="font-size:14px"></iconify-icon>${j.location}</span>
              <span class="job-tag"><iconify-icon icon="lucide:briefcase" style="font-size:14px"></iconify-icon>${j.type}</span>
              <span class="job-badge">${j.role}</span>
            </div>
          </div>
          <a class="btn btn-outline" href="driver-onboarding.php">Apply Now</a>
        </div>`).join('');
    }

    document.getElementById('stateFilter').addEventListener('change', e => {
      currentState = e.target.value;
      renderJobs();
    });

    document.getElementById('roleFilters').addEventListener('click', e => {
      const btn = e.target.closest('.dept-btn');
      if (!btn) return;
      document.querySelectorAll('#roleFilters .dept-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      currentRole = btn.dataset.role;
      renderJobs();
    });

    renderJobs();
  </script>
  <script src="auth-nav.js"></script>
</body>
</html>