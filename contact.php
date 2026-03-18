<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <link rel="icon" href="favicon.svg" type="image/svg+xml" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Contact Us — Fastrux Logistics</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="shared.css" />
  <script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>
  <style>
    .contact-grid {
      display: grid;
      grid-template-columns: 1fr 1.6fr;
      gap: 64px;
      align-items: start;
    }
    .contact-info h2 { font-size: 32px; font-weight: 700; margin-bottom: 16px; }
    .contact-info p  { color: var(--muted-foreground); margin-bottom: 40px; font-size: 16px; }

    .contact-item {
      display: flex; gap: 16px; align-items: flex-start; margin-bottom: 28px;
    }
    .contact-item-icon {
      width: 48px; height: 48px; min-width: 48px;
      background: var(--secondary);
      border-radius: var(--radius-md);
      display: flex; align-items: center; justify-content: center;
      color: var(--primary);
    }
    .contact-item h4 { font-size: 15px; font-weight: 600; margin-bottom: 4px; }
    .contact-item p,
    .contact-item a  { font-size: 15px; color: var(--muted-foreground); text-decoration: none; line-height: 1.5; }
    .contact-item a:hover { color: var(--primary); }

    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

    .contact-form-card {
      background: var(--card); border: 1px solid var(--border);
      border-radius: var(--radius-xl); padding: 40px;
      box-shadow: 0 8px 32px rgba(11,111,255,.06);
    }
    .contact-form-card h3 { font-size: 22px; font-weight: 700; margin-bottom: 24px; color: var(--foreground); }

    .form-feedback {
      display: none;
      padding: 14px 18px;
      border-radius: var(--radius-md);
      font-size: 14px;
      font-weight: 500;
      margin-bottom: 20px;
      align-items: center;
      gap: 10px;
    }
    .form-feedback.success { background:#e6f9ee; border:1px solid #a7f0c4; color:var(--success); }
    .form-feedback.error   { background:#fef2f2; border:1px solid #fecaca; color:var(--destructive); }

    @keyframes spin { to { transform: rotate(360deg); } }

    @media (max-width: 1024px) {
      .contact-grid { grid-template-columns: 1fr; gap: 48px; }
    }
    @media (max-width: 480px) {
      .form-row    { grid-template-columns: 1fr; }
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
        <a class="nav-link" href="about.php">About Us</a>
        <a class="nav-link active" href="contact.php">Contact</a>
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
    <a class="nav-link" href="index.php#services">Services</a>
    <a class="nav-link" href="track.php">Tracking</a>
    <a class="nav-link" href="about.php">About Us</a>
    <a class="nav-link active" href="contact.php">Contact</a>
    <a class="nav-link" href="driver-onboarding.php">Drive with Us</a>
    <div class="header-actions" style="margin-top:8px;">
      <a class="btn btn-outline" href="login.php">Login</a>
      <a class="btn btn-primary" href="quote.php">Get a Quote</a>
    </div>
  </nav>

  <!-- ── HERO ── -->
  <div class="page-hero">
    <div class="container">
      <h1>Get In Touch</h1>
      <p>Have a question or need a custom logistics solution? Our team is ready to help.</p>
    </div>
  </div>

  <!-- ── CONTACT SECTION ── -->
  <section class="section">
    <div class="container contact-grid">

      <!-- Left: info -->
      <div class="contact-info">
        <h2>We'd love to hear from you</h2>
        <p>Whether you're looking for a freight quote, need help tracking a shipment, or want to explore a partnership — reach out any time.</p>

        <div class="contact-item">
          <div class="contact-item-icon"><iconify-icon icon="lucide:map-pin" style="font-size:22px"></iconify-icon></div>
          <div>
            <h4>Headquarters</h4>
            <p>1008 Oak Chase way, Leander, TX 78641</p>
          </div>
        </div>
        <div class="contact-item">
          <div class="contact-item-icon"><iconify-icon icon="lucide:phone" style="font-size:22px"></iconify-icon></div>
          <div>
            <h4>Phone</h4>
            <a href="tel:+2038896129">+203-889-6129</a>
            <p style="font-size:13px;margin-top:2px;">Mon–Fri, 8 AM – 8 PM EST</p>
          </div>
        </div>
        <div class="contact-item">
          <div class="contact-item-icon"><iconify-icon icon="lucide:mail" style="font-size:22px"></iconify-icon></div>
          <div>
            <h4>Email</h4>
            <a href="mailto:support@fastrux.com">support@fastrux.com</a><br>
            <a href="mailto:sales@fastrux.com">sales@fastrux.com</a>
          </div>
        </div>
        <div class="contact-item">
          <div class="contact-item-icon"><iconify-icon icon="lucide:clock" style="font-size:22px"></iconify-icon></div>
          <div>
            <h4>Business Hours</h4>
            <p>Monday – Friday: 8 AM – 8 PM EST<br>Saturday: 9 AM – 5 PM EST</p>
          </div>
        </div>

      </div>

      <!-- Right: contact form -->
      <div class="contact-form-card">
        <h3>Send Us a Message</h3>

        <div class="form-feedback" id="contactFeedback"></div>

        <form id="contactForm" novalidate>
          <input type="hidden" name="form_type" value="contact" />
          <div class="form-row">
            <div class="form-group">
              <label for="c_first_name">First Name *</label>
              <input class="form-control" type="text" id="c_first_name" name="first_name" placeholder="John" required />
            </div>
            <div class="form-group">
              <label for="c_last_name">Last Name *</label>
              <input class="form-control" type="text" id="c_last_name" name="last_name" placeholder="Doe" required />
            </div>
          </div>
          <div class="form-group">
            <label for="c_email">Email Address *</label>
            <input class="form-control" type="email" id="c_email" name="email" placeholder="you@company.com" required />
          </div>
          <div class="form-group">
            <label for="c_phone">Phone Number</label>
            <input class="form-control" type="tel" id="c_phone" name="phone" placeholder="+1 (555) 000-0000" />
          </div>
          <div class="form-group">
            <label for="c_subject">Subject *</label>
            <select class="form-control" id="c_subject" name="subject" required>
              <option value="">Select a topic…</option>
              <option>Shipment Tracking</option>
              <option>Request a Quote</option>
              <option>Billing &amp; Invoices</option>
              <option>Partnership Enquiry</option>
              <option>Other</option>
            </select>
          </div>
          <div class="form-group">
            <label for="c_message">Message *</label>
            <textarea class="form-control" id="c_message" name="message" rows="4"
                      placeholder="Tell us how we can help…" required style="resize:vertical;"></textarea>
          </div>
          <button type="submit" class="btn btn-primary" id="contactBtn"
                  style="width:100%;padding:14px;font-size:16px;">
            Send Message
            <iconify-icon icon="lucide:send" style="font-size:16px;margin-left:8px"></iconify-icon>
          </button>
        </form>
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
            <a href="architecture.php">Architecture</a>
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

    document.getElementById('contactForm').addEventListener('submit', async function (e) {
      e.preventDefault();

      const btn      = document.getElementById('contactBtn');
      const feedback = document.getElementById('contactFeedback');
      const origHTML = btn.innerHTML;

      btn.disabled  = true;
      btn.innerHTML = '<iconify-icon icon="lucide:loader-circle" style="font-size:18px;margin-right:8px;animation:spin 1s linear infinite"></iconify-icon> Sending…';

      try {
        const res  = await fetch('process_form.php', { method: 'POST', body: new FormData(this) });
        const data = await res.json();
        showFeedback(feedback, data.success, data.success
          ? `✓ ${data.message} (Ref: ${data.reference})`
          : `✗ ${data.message}`);
        if (data.success) { this.reset(); }
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