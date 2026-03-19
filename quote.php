<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <link rel="icon" href="favicon.svg" type="image/svg+xml" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Get a Quote — Fastrux Logistics</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="shared.css" />
  <script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>
  <style>
    .quote-grid {
      display: grid; grid-template-columns: 1fr 1.6fr;
      gap: 64px; align-items: start;
    }
    .quote-info h2 { font-size: 32px; font-weight: 700; margin-bottom: 16px; }
    .quote-info p  { color: var(--muted-foreground); margin-bottom: 32px; font-size: 16px; }
    .quote-feature { display: flex; gap: 16px; align-items: flex-start; margin-bottom: 24px; }
    .quote-feature-icon {
      width: 44px; height: 44px; min-width: 44px;
      background: var(--secondary); border-radius: var(--radius-md);
      display: flex; align-items: center; justify-content: center; color: var(--primary);
    }
    .quote-feature h4 { font-size: 16px; font-weight: 600; margin-bottom: 4px; }
    .quote-feature p  { font-size: 14px; color: var(--muted-foreground); margin: 0; }
    @media (max-width: 1024px) { .quote-grid { grid-template-columns: 1fr; gap: 40px; } }
  </style>
</head>
<body>

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
        <a class="nav-link" href="about.php">About Us</a>
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
    <a class="nav-link" href="about.php">About Us</a>
    <a class="nav-link" href="contact.php">Contact</a>
        <a class="nav-link" href="driver-onboarding.php">Drive with Us</a>
        <a class="nav-link" href="loadboard.php">Loadboard</a>
        <a class="nav-link" href="marketplace.php">Marketplace</a>
    <div class="header-actions" style="margin-top:8px;">
      <a class="btn btn-outline" href="login.php">Login</a>
      <a class="btn btn-primary" href="quote.php">Get a Quote</a>
    </div>
  </nav>

  <div class="page-hero">
    <div class="container">
      <h1>Get a Free Quote</h1>
      <p>Tell us about your shipment and we'll respond with a competitive rate within 24 hours.</p>
    </div>
  </div>

  <section class="section">
    <div class="container quote-grid">
      <div class="quote-info">
        <h2>Why ship with Fastrux?</h2>
        <p>We combine global reach with local expertise to deliver the most competitive rates and reliable service.</p>
        <div class="quote-feature">
          <div class="quote-feature-icon"><iconify-icon icon="lucide:clock" style="font-size:22px"></iconify-icon></div>
          <div><h4>24-hour Response</h4><p>Our team reviews every quote request within one business day.</p></div>
        </div>
        <div class="quote-feature">
          <div class="quote-feature-icon"><iconify-icon icon="lucide:shield-check" style="font-size:22px"></iconify-icon></div>
          <div><h4>Cargo Insurance</h4><p>All shipments come with full insurance coverage options.</p></div>
        </div>
        <div class="quote-feature">
          <div class="quote-feature-icon"><iconify-icon icon="lucide:globe" style="font-size:22px"></iconify-icon></div>
          <div><h4>180+ Countries</h4><p>Our network spans every major trade lane across the globe.</p></div>
        </div>
        <div class="quote-feature">
          <div class="quote-feature-icon"><iconify-icon icon="lucide:headphones" style="font-size:22px"></iconify-icon></div>
          <div><h4>Dedicated Support</h4><p>A dedicated account manager handles your shipment end-to-end.</p></div>
        </div>
      </div>

      <div class="card" style="padding:40px;">
        <div class="form-feedback" id="quoteFeedback"></div>
        <form id="quoteForm" novalidate>
          <input type="hidden" name="form_type" value="quote" />
          <input type="hidden" name="user_id" id="quoteUserId" value="" />
          <div class="form-row">
            <div class="form-group">
              <label>First Name *</label>
              <input class="form-control" type="text" name="first_name" placeholder="John" required />
            </div>
            <div class="form-group">
              <label>Last Name *</label>
              <input class="form-control" type="text" name="last_name" placeholder="Doe" required />
            </div>
          </div>
          <div class="form-group">
            <label>Company Name</label>
            <input class="form-control" type="text" name="company" placeholder="Acme Corp" />
          </div>
          <div class="form-group">
            <label>Email Address *</label>
            <input class="form-control" type="email" name="email" placeholder="you@company.com" required />
          </div>
          <div class="form-group">
            <label>Service Type *</label>
            <select class="form-control" name="service" required>
              <option value="">Select a service…</option>
              <option>Ocean Freight</option>
              <option>Air Freight</option>
              <option>Ground Transport</option>
              <option>Warehousing</option>
            </select>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Origin *</label>
              <input class="form-control" type="text" name="origin" placeholder="City, Country" required />
            </div>
            <div class="form-group">
              <label>Destination *</label>
              <input class="form-control" type="text" name="destination" placeholder="City, Country" required />
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Cargo Weight (kg)</label>
              <input class="form-control" type="number" name="weight" placeholder="e.g. 500" min="0" />
            </div>
            <div class="form-group">
              <label>Estimated Volume (m³)</label>
              <input class="form-control" type="number" name="volume" placeholder="e.g. 2.5" min="0" step="0.1" />
            </div>
          </div>
          <div class="form-group">
            <label>Additional Notes</label>
            <textarea class="form-control" name="notes" rows="3"
                      placeholder="Hazardous goods, special handling, preferred dates…"></textarea>
          </div>
          <button type="submit" class="btn btn-primary" id="quoteBtn"
                  style="width:100%;padding:14px;font-size:16px;">
            Submit Quote Request
          </button>
        </form>
      </div>
    </div>
  </section>

  <footer class="footer">
    <div class="container">
      <div class="footer-grid">
        <div class="footer-brand">
          <a href="index.php" class="logo">
            <iconify-icon icon="lucide:truck" style="font-size:24px;color:var(--primary)"></iconify-icon>
            Fastrux
          </a>
          <p>Delivering excellence in logistics and supply chain management worldwide.</p>
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
            <a class="nav-link" href="loadboard.php">Loadboard</a>
            <a class="nav-link" href="marketplace.php">Marketplace</a>
            <a href="news.php">News &amp; Media</a>
            <a href="contact.php">Contact</a>
          </div>
        </div>
        <div>
          <h4 class="footer-heading">Contact</h4>
          <div class="footer-links">
            <div class="footer-contact-item"><iconify-icon icon="lucide:map-pin"></iconify-icon>1008 Oak Chase way, Leander, TX 78641</div>
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

    // Helper: get logged-in user from localStorage
    function getLoggedInUser() {
      try { return JSON.parse(localStorage.getItem('fx_user')); } catch (e) { return null; }
    }

    // Pre-fill form for logged-in shippers
    (function () {
      var user = getLoggedInUser();
      if (!user || !user.id) return;
      var shipperRoles = ['shipper', 'customer'];
      if (shipperRoles.indexOf(user.role || 'shipper') === -1) return;
      document.getElementById('quoteUserId').value = user.id || '';
      var f = document.getElementById('quoteForm');
      if (user.first_name) { var fn = f.querySelector('[name="first_name"]'); if (fn) fn.value = user.first_name; }
      if (user.last_name)  { var ln = f.querySelector('[name="last_name"]');  if (ln) ln.value = user.last_name;  }
      if (user.email)      { var em = f.querySelector('[name="email"]');      if (em) em.value = user.email;      }
    })();

    document.getElementById('quoteForm').addEventListener('submit', async function(e) {
      e.preventDefault();

      const btn      = document.getElementById('quoteBtn');
      const feedback = document.getElementById('quoteFeedback');
      const origHTML = btn.innerHTML;

      btn.disabled  = true;
      btn.innerHTML = '<iconify-icon icon="lucide:loader-circle" style="font-size:18px;margin-right:8px;animation:spin 1s linear infinite"></iconify-icon> Submitting…';

      try {
        const res  = await fetch('process_form.php', { method: 'POST', body: new FormData(this) });
        const data = await res.json();
        showFeedback(feedback, data.success, data.success
          ? `✓ ${data.message} (Ref: ${data.reference})`
          : `✗ ${data.message}`);
        if (data.success) {
          this.reset();
          // Re-inject user_id after reset if logged in
          var user = getLoggedInUser();
          if (user && user.id) document.getElementById('quoteUserId').value = user.id;
        }
      } catch (err) {
        showFeedback(feedback, false, '✗ Network error — please try again or email us directly.');
      }

      btn.disabled  = false;
      btn.innerHTML = origHTML;
      feedback.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });

    function showFeedback(el, success, msg) {
      el.className     = 'form-feedback ' + (success ? 'success' : 'error');
      el.textContent   = msg;
      el.style.display = 'flex';
    }
  </script>
  <script src="auth-nav.js"></script>
</body>
</html>
