<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <link rel="icon" href="favicon.svg" type="image/svg+xml" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Create Account — Fastrux Logistics</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="shared.css" />
  <script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>
  <style>
    .password-wrapper { position: relative; }
    .password-toggle {
      position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
      background: none; border: none; cursor: pointer;
      color: var(--muted-foreground); display: flex; align-items: center;
    }
    .name-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
    }
    @media (max-width: 480px) {
      .name-row { grid-template-columns: 1fr; }
    }
    @keyframes spin { to { transform: rotate(360deg); } }
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
    <div class="header-actions" style="margin-top:8px;">
      <a class="btn btn-primary" href="quote.php">Get a Quote</a>
    </div>
  </nav>

  <div class="auth-wrapper">
    <div class="auth-card">
      <div class="auth-logo">
        <iconify-icon icon="lucide:truck" style="font-size:24px"></iconify-icon>
        Fastrux
      </div>
      <h1 class="auth-title">Create your account</h1>
      <p class="auth-subtitle">Start managing your shipments with Fastrux today.</p>
      <div class="form-feedback" id="registerFeedback"></div>
      <form id="registerForm" novalidate>
        <input type="hidden" name="form_type" value="register" />
        <div class="name-row">
          <div class="form-group">
            <label for="firstName">First name</label>
            <input class="form-control" type="text" id="firstName" name="firstName"
                   placeholder="Jane" required autocomplete="given-name" />
          </div>
          <div class="form-group">
            <label for="lastName">Last name</label>
            <input class="form-control" type="text" id="lastName" name="lastName"
                   placeholder="Smith" required autocomplete="family-name" />
          </div>
        </div>
        <div class="form-group">
          <label for="email">Email address</label>
          <input class="form-control" type="email" id="email" name="email"
                 placeholder="you@example.com" required autocomplete="email" />
        </div>
        <div class="form-group">
          <label for="company">Company name <span style="color:var(--muted-foreground);font-weight:400;">(optional)</span></label>
          <input class="form-control" type="text" id="company" name="company"
                 placeholder="Acme Corp" autocomplete="organization" />
        </div>
        <div class="form-group">
          <label for="role">I am a… *</label>
          <select class="form-control" id="role" name="role" required onchange="onRoleChange()">
            <option value="shipper">Shipper — I need to ship goods</option>
            <option value="driver">Owner Operator &amp; Driver</option>
            <option value="corporate_staff">Corporate Staff — Fastrux team member</option>
          </select>
        </div>
        <div id="staffPendingNotice" style="display:none;background:#fffbeb;border:1px solid #fbbf24;border-radius:8px;padding:12px 16px;font-size:13px;color:#92400e;margin-bottom:4px;">
          <iconify-icon icon="lucide:info" style="font-size:15px;margin-right:6px;vertical-align:middle;"></iconify-icon>
          <strong>Corporate Staff accounts require admin approval.</strong> After registering, an administrator will review and activate your account. You will not be able to log in until approved.
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <div class="password-wrapper">
            <input class="form-control" type="password" id="password" name="password"
                   placeholder="Min. 8 characters" required autocomplete="new-password" minlength="8" />
            <button type="button" class="password-toggle" id="togglePwd" aria-label="Show password">
              <iconify-icon icon="lucide:eye" id="eyeIcon" style="font-size:18px"></iconify-icon>
            </button>
          </div>
        </div>
        <div class="form-group" style="margin-bottom:0;">
          <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;font-weight:400;">
            <input type="checkbox" name="terms" id="termsCheck" required
                   style="margin-top:3px;accent-color:var(--primary);" />
            <span style="font-size:14px;color:var(--muted-foreground);">
              I agree to the
              <a href="terms.php" style="color:var(--primary);">Terms of Service</a>
              and
              <a href="privacy.php" style="color:var(--primary);">Privacy Policy</a>
            </span>
          </label>
        </div>
        <button type="submit" class="btn btn-primary" id="registerBtn"
                style="width:100%;padding:14px;font-size:16px;margin-top:24px;">
          Create Account
        </button>
      </form>
      <p class="auth-footer-text">Already have an account? <a href="login.php">Sign in</a></p>
    </div>
  </div>

  <footer class="footer">
    <div class="container">
      <div class="footer-bottom" style="border-top:none;padding-top:0;">
        <div>© 2026 Fastrux Logistics. All rights reserved.</div>
        <div>
          <a href="privacy.php" style="color:var(--muted-foreground);margin-right:16px;">Privacy</a>
          <a href="terms.php"   style="color:var(--muted-foreground);">Terms</a>
        </div>
      </div>
    </div>
  </footer>

  <script>
    const ham = document.getElementById('hamburger');
    const mob = document.getElementById('mobileMenu');
    ham.addEventListener('click', () => { ham.classList.toggle('open'); mob.classList.toggle('open'); });

    function onRoleChange() {
      const role   = document.getElementById('role').value;
      const notice = document.getElementById('staffPendingNotice');
      if (notice) notice.style.display = role === 'corporate_staff' ? 'block' : 'none';
    }

    const pwd = document.getElementById('password');
    const eye = document.getElementById('eyeIcon');
    document.getElementById('togglePwd').addEventListener('click', () => {
      const show = pwd.type === 'password';
      pwd.type = show ? 'text' : 'password';
      eye.setAttribute('icon', show ? 'lucide:eye-off' : 'lucide:eye');
    });

    document.getElementById('registerForm').addEventListener('submit', async function(e) {
      e.preventDefault();
      const btn      = document.getElementById('registerBtn');
      const feedback = document.getElementById('registerFeedback');
      const origHTML = btn.innerHTML;
      btn.disabled   = true;
      btn.innerHTML  = '<iconify-icon icon="lucide:loader-circle" style="font-size:18px;margin-right:8px;animation:spin 1s linear infinite"></iconify-icon>Creating account…';
      feedback.style.display = 'none';
      try {
        const res  = await fetch('process_form.php', { method: 'POST', body: new FormData(this) });
        const data = await res.json();
        if (data.success) {
          feedback.className   = 'form-feedback success';
          feedback.textContent = '✓ ' + data.message;
          feedback.style.display = 'flex';
          // Read form values BEFORE reset so they are available for localStorage
          const firstName = document.getElementById('firstName').value.trim();
          const lastName  = document.getElementById('lastName').value.trim();
          const email     = document.getElementById('email').value.trim();
          const role      = document.getElementById('role').value || 'shipper';
          this.reset();
          onRoleChange(); // reset notice visibility

          // For pending_approval accounts, don't store session or redirect to dashboard
          if (data.pending_approval) {
            btn.disabled  = false;
            btn.innerHTML = origHTML;
            return; // Stay on page — user must wait for admin approval
          }

          // Store user session in localStorage
          localStorage.setItem('fx_user', JSON.stringify({
            id:         data.reference || '',
            first_name: firstName,
            last_name:  lastName,
            email:      email,
            role:       data.role || role,
          }));
          // Redirect to account page (or intended page if specified)
          const params = new URLSearchParams(window.location.search);
          const redirect = params.get('redirect') || 'account.php';
          setTimeout(() => { window.location.href = redirect; }, 800);
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
    });
  </script>
</body>
</html>
