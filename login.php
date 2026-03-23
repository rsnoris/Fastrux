<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <link rel="icon" href="favicon.svg" type="image/svg+xml" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login — Fastrux Logistics</title>
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
    .forgot-link { float: right; font-size: 13px; color: var(--primary); font-weight: 500; }
    @keyframes spin { to { transform: rotate(360deg); } }
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
        <a class="nav-link" href="marketplace">Marketplace</a>
        <a class="nav-link" href="loadboard">Loadboard</a>
        <a class="nav-link" href="driver-onboarding">Drive with Us</a>
        <a class="nav-link" href="contact">Contact</a>
      </nav>
      <div class="header-actions">
        <a class="nav-link active" href="login">Login</a>
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
    <a class="nav-link" href="track">Tracking</a>
        <a class="nav-link" href="marketplace">Marketplace</a>
        <a class="nav-link" href="loadboard">Loadboard</a>
        <a class="nav-link" href="driver-onboarding">Drive with Us</a>
    <a class="nav-link" href="contact">Contact</a>
    <div class="header-actions" style="margin-top:8px;">
      <a class="btn btn-primary" href="quote">Get a Quote</a>
    </div>
  </nav>

  <div class="auth-wrapper">
    <div class="auth-card">
      <div class="auth-logo">
        <iconify-icon icon="lucide:truck" style="font-size:24px"></iconify-icon>
        Fastrux
      </div>
      <h1 class="auth-title">Welcome back</h1>
      <p class="auth-subtitle">Sign in to manage your shipments and account.</p>
      <div class="form-feedback" id="loginFeedback"></div>
      <form id="loginForm" novalidate>
        <input type="hidden" name="form_type" value="login" />
        <div class="form-group">
          <label for="email">Email address</label>
          <input class="form-control" type="email" id="email" name="email"
                 placeholder="you@example.com" required autocomplete="email" />
        </div>
        <div class="form-group">
          <label for="password">
            Password
            <a class="forgot-link" href="forgot-password">Forgot password?</a>
          </label>
          <div class="password-wrapper">
            <input class="form-control" type="password" id="password" name="password"
                   placeholder="••••••••" required autocomplete="current-password" />
            <button type="button" class="password-toggle" id="togglePwd" aria-label="Show password">
              <iconify-icon icon="lucide:eye" id="eyeIcon" style="font-size:18px"></iconify-icon>
            </button>
          </div>
        </div>
        <button type="submit" class="btn btn-primary" id="loginBtn"
                style="width:100%;padding:14px;font-size:16px;margin-top:8px;">
          Sign In
        </button>
      </form>

      <!-- Social sign-in -->
      <div class="social-auth-divider">or continue with</div>
      <div class="social-auth-buttons">
        <a class="btn-social" id="googleSignIn" href="#">
          <svg width="18" height="18" viewBox="0 0 48 48" aria-hidden="true">
            <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
            <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
            <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
            <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.18 1.48-4.97 2.31-8.16 2.31-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
          </svg>
          Continue with Google
        </a>
        <a class="btn-social" id="linkedinSignIn" href="#">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="#0A66C2" aria-hidden="true">
            <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
          </svg>
          Continue with LinkedIn
        </a>
      </div>

      <p class="auth-footer-text">Don't have an account? <a href="register">Create one free</a></p>
    </div>
  </div>

  <footer class="footer">
    <div class="container">
      <div class="footer-bottom" style="border-top:none;padding-top:0;">
        <div>© 2026 Fastrux Logistics. All rights reserved.</div>
        <div>
          <a href="privacy" style="color:var(--muted-foreground);margin-right:16px;">Privacy</a>
          <a href="terms"   style="color:var(--muted-foreground);">Terms</a>
        </div>
      </div>
    </div>
  </footer>

  <script>
    const ham = document.getElementById('hamburger');
    const mob = document.getElementById('mobileMenu');
    ham.addEventListener('click', () => { ham.classList.toggle('open'); mob.classList.toggle('open'); });

    const pwd = document.getElementById('password');
    const eye = document.getElementById('eyeIcon');
    document.getElementById('togglePwd').addEventListener('click', () => {
      const show = pwd.type === 'password';
      pwd.type = show ? 'text' : 'password';
      eye.setAttribute('icon', show ? 'lucide:eye-off' : 'lucide:eye');
    });

    document.getElementById('loginForm').addEventListener('submit', async function(e) {
      e.preventDefault();
      const btn      = document.getElementById('loginBtn');
      const feedback = document.getElementById('loginFeedback');
      const origHTML = btn.innerHTML;
      btn.disabled   = true;
      btn.innerHTML  = '<iconify-icon icon="lucide:loader-circle" style="font-size:18px;margin-right:8px;animation:spin 1s linear infinite"></iconify-icon>Signing in…';
      feedback.style.display = 'none';
      try {
        const res  = await fetch('process_form.php', { method: 'POST', body: new FormData(this) });
        const data = await res.json();
        if (data.success) {
          feedback.className   = 'form-feedback success';
          feedback.textContent = '✓ ' + data.message;
          feedback.style.display = 'flex';
          // Store user session in localStorage
          localStorage.setItem('fx_user', JSON.stringify({
            id:         data.user?.id         || '',
            first_name: data.user?.first_name || '',
            last_name:  data.user?.last_name  || '',
            email:      data.user?.email      || document.getElementById('email').value.trim(),
            role:       data.user?.role       || 'shipper',
          }));
          // Redirect to intended page or role-specific dashboard after short delay
          const params = new URLSearchParams(window.location.search);
          const role = data.user?.role || 'shipper';
          const dashboardMap = {
            shipper:           'shipper-dashboard.php',
            customer:          'shipper-dashboard.php',
            driver:            'driver-dashboard.php',
            owner_operator:    'driver-dashboard.php',
            corporate_staff:   'staff-dashboard.php',
            admin:             'admin-dashboard.php',
            super_admin:       'admin-dashboard.php',
            insurance_company: 'insurance-dashboard.php',
            trucking_company:  'trucking-dashboard.php',
            gas_station:       'gas-station-dashboard.php',
            hotel:             'hotel-dashboard.php',
          };
          const defaultDash = dashboardMap[role] || 'index.php';
          const redirect = params.get('redirect') || defaultDash;
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

    // Social sign-in handlers
    function buildOAuthUrl(provider) {
      return 'oauth_handler.php?provider=' + provider + '&action=redirect&origin=login';
    }
    document.getElementById('googleSignIn').addEventListener('click', function(e) {
      e.preventDefault();
      window.location.href = buildOAuthUrl('google');
    });
    document.getElementById('linkedinSignIn').addEventListener('click', function(e) {
      e.preventDefault();
      window.location.href = buildOAuthUrl('linkedin');
    });
  </script>
</body>
</html>