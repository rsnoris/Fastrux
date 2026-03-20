<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <link rel="icon" href="favicon.svg" type="image/svg+xml" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Trucking Company Portal Login — Fastrux Logistics</title>
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

    .portal-badge {
      display: inline-flex; align-items: center; gap: 6px;
      background: #f0fdf4; border: 1px solid #86efac;
      color: #15803d; border-radius: 20px;
      padding: 4px 12px; font-size: 12px; font-weight: 600;
      margin-bottom: 12px; letter-spacing: .02em;
    }

    .features-list {
      display: grid; grid-template-columns: 1fr 1fr;
      gap: 12px; margin: 20px 0;
    }
    .feature-tile {
      background: var(--muted); border: 1px solid var(--border);
      border-radius: var(--radius-md);
      padding: 14px; display: flex; flex-direction: column; gap: 6px;
    }
    .feature-tile iconify-icon { font-size: 20px; color: var(--primary); }
    .feature-tile .ft-title { font-size: 13px; font-weight: 600; }
    .feature-tile .ft-desc  { font-size: 12px; color: var(--muted-foreground); line-height: 1.4; }
    @media(max-width:480px) { .features-list { grid-template-columns: 1fr; } }
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
        <a class="nav-link" href="marketplace.php">Marketplace</a>
        <a class="nav-link" href="loadboard.php">Loadboard</a>
        <a class="nav-link" href="contact.php">Contact</a>
      </nav>
      <div class="header-actions">
        <a class="nav-link" href="login.php">General Login</a>
        <a class="btn btn-primary" href="register.php?role=trucking_company">List Your Trucks</a>
      </div>
      <button class="hamburger" id="hamburger" aria-label="Toggle menu">
        <span></span><span></span><span></span>
      </button>
    </div>
  </header>
  <nav class="mobile-menu" id="mobileMenu">
    <a class="nav-link" href="index.php">Home</a>
    <a class="nav-link" href="marketplace.php">Marketplace</a>
    <a class="nav-link" href="loadboard.php">Loadboard</a>
    <a class="nav-link" href="contact.php">Contact</a>
    <div class="header-actions" style="margin-top:8px;">
      <a class="btn btn-outline" href="login.php">Sign In</a>
      <a class="btn btn-primary" href="register.php?role=trucking_company">List Your Trucks</a>
    </div>
  </nav>

  <div class="auth-wrapper" style="align-items:flex-start;padding-top:48px;">
    <div style="width:100%;max-width:460px;margin:0 auto;">

      <!-- Login card -->
      <div class="auth-card" style="margin-bottom:20px;">
        <div class="portal-badge">
          <iconify-icon icon="lucide:truck"></iconify-icon>
          Trucking Company Portal
        </div>
        <div class="auth-logo">
          <iconify-icon icon="lucide:truck" style="font-size:24px"></iconify-icon>
          Fastrux
        </div>
        <h1 class="auth-title">Trucking Partner Login</h1>
        <p class="auth-subtitle">Sign in to manage your truck listings for lease and sale on the Fastrux Marketplace.</p>

        <div class="form-feedback" id="loginFeedback"></div>
        <form id="loginForm" novalidate>
          <input type="hidden" name="form_type" value="login" />
          <div class="form-group">
            <label for="email">Company Email</label>
            <input class="form-control" type="email" id="email" name="email"
                   placeholder="fleet@company.com" required autocomplete="email" />
          </div>
          <div class="form-group">
            <label for="password">
              Password
              <a class="forgot-link" href="forgot-password.php">Forgot password?</a>
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
            Sign In to Trucking Portal
          </button>
        </form>
        <p class="auth-footer-text">Don't have an account? <a href="register.php">Register your trucking company</a></p>
        <p class="auth-footer-text" style="font-size:13px;color:var(--muted-foreground);">
          Not a trucking company? <a href="login.php">General login</a>
        </p>
      </div>

      <!-- Features card -->
      <div class="auth-card" style="padding:24px;">
        <h2 style="font-size:16px;font-weight:700;margin-bottom:4px;">What you can do with the Trucking Portal</h2>
        <p style="font-size:13px;color:var(--muted-foreground);margin-bottom:0;">Monetise your idle fleet by connecting with buyers and lessees across the Fastrux network.</p>
        <div class="features-list">
          <div class="feature-tile">
            <iconify-icon icon="lucide:truck"></iconify-icon>
            <span class="ft-title">List Trucks</span>
            <span class="ft-desc">Post semi-trucks, flatbeds, reefers, and more for lease or sale.</span>
          </div>
          <div class="feature-tile">
            <iconify-icon icon="lucide:search"></iconify-icon>
            <span class="ft-title">Qualified Buyers</span>
            <span class="ft-desc">Connect with verified carriers and owner-operators looking to lease or buy.</span>
          </div>
          <div class="feature-tile">
            <iconify-icon icon="lucide:edit-3"></iconify-icon>
            <span class="ft-title">Manage Listings</span>
            <span class="ft-desc">Update price, availability, and specs anytime — no delay.</span>
          </div>
          <div class="feature-tile">
            <iconify-icon icon="lucide:trending-up"></iconify-icon>
            <span class="ft-title">Grow Your Fleet</span>
            <span class="ft-desc">Lease in trucks as easily as you list yours — build a bigger operation.</span>
          </div>
        </div>
      </div>

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

    const params = new URLSearchParams(window.location.search);
    const emailParam = params.get('email');
    if (emailParam) document.getElementById('email').value = emailParam;

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
          const role = data.user?.role || '';
          if (role !== 'trucking_company') {
            feedback.className   = 'form-feedback error';
            feedback.textContent = '✗ This portal is for trucking companies only. Please use the general login page.';
            feedback.style.display = 'flex';
            btn.disabled  = false;
            btn.innerHTML = origHTML;
            return;
          }
          feedback.className   = 'form-feedback success';
          feedback.textContent = '✓ ' + data.message;
          feedback.style.display = 'flex';
          localStorage.setItem('fx_user', JSON.stringify({
            id:         data.user?.id         || '',
            first_name: data.user?.first_name || '',
            last_name:  data.user?.last_name  || '',
            email:      data.user?.email      || document.getElementById('email').value.trim(),
            role:       role,
          }));
          const redirect = params.get('redirect') || 'trucking-dashboard.php';
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
