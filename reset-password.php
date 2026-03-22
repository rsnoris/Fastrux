<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <link rel="icon" href="favicon.svg" type="image/svg+xml" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Reset Password — Fastrux Logistics</title>
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
        <a class="nav-link" href="contact">Contact</a>
        <a class="nav-link" href="driver-onboarding">Drive with Us</a>
        <a class="nav-link" href="loadboard">Loadboard</a>
        <a class="nav-link" href="marketplace">Marketplace</a>
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
    <a class="nav-link" href="contact">Contact</a>
    <a class="nav-link" href="driver-onboarding">Drive with Us</a>
    <a class="nav-link" href="loadboard">Loadboard</a>
    <a class="nav-link" href="marketplace">Marketplace</a>
    <div class="header-actions" style="margin-top:8px;">
      <a class="btn btn-primary" href="quote">Get a Quote</a>
    </div>
  </nav>

  <div class="auth-wrapper">

    <!-- Invalid / missing token card -->
    <div class="auth-card" id="invalidCard" style="display:none;">
      <div class="auth-logo">
        <iconify-icon icon="lucide:alert-circle" style="font-size:24px;color:var(--destructive)"></iconify-icon>
      </div>
      <h1 class="auth-title">Invalid Reset Link</h1>
      <p class="auth-subtitle">This password reset link is invalid or has expired. Reset links are valid for 1 hour.</p>
      <a href="forgot-password" class="btn btn-primary"
         style="display:block;width:100%;padding:14px;font-size:16px;text-align:center;margin-top:8px;">
        Request a New Link
      </a>
      <p class="auth-footer-text"><a href="login">← Back to Sign In</a></p>
    </div>

    <!-- Reset form card -->
    <div class="auth-card" id="resetCard">
      <div class="auth-logo">
        <iconify-icon icon="lucide:lock" style="font-size:24px"></iconify-icon>
      </div>
      <h1 class="auth-title">Set new password</h1>
      <p class="auth-subtitle">Choose a strong password for your account.</p>
      <div class="form-feedback" id="resetFeedback"></div>
      <form id="resetForm" novalidate>
        <input type="hidden" name="form_type" value="reset_password" />
        <input type="hidden" name="token"     id="tokenField"  value="" />
        <div class="form-group">
          <label for="new_password">New password</label>
          <div class="password-wrapper">
            <input class="form-control" type="password" id="new_password" name="new_password"
                   placeholder="Min. 8 characters" required autocomplete="new-password" />
            <button type="button" class="password-toggle" id="togglePwd1" aria-label="Show password">
              <iconify-icon icon="lucide:eye" id="eyeIcon1" style="font-size:18px"></iconify-icon>
            </button>
          </div>
        </div>
        <div class="form-group">
          <label for="confirm_password">Confirm new password</label>
          <div class="password-wrapper">
            <input class="form-control" type="password" id="confirm_password" name="confirm_password"
                   placeholder="Repeat password" required autocomplete="new-password" />
            <button type="button" class="password-toggle" id="togglePwd2" aria-label="Show password">
              <iconify-icon icon="lucide:eye" id="eyeIcon2" style="font-size:18px"></iconify-icon>
            </button>
          </div>
        </div>
        <button type="submit" class="btn btn-primary" id="resetBtn"
                style="width:100%;padding:14px;font-size:16px;margin-top:8px;">
          Update Password
        </button>
      </form>
      <p class="auth-footer-text"><a href="login">← Back to Sign In</a></p>
    </div>

    <!-- Success card -->
    <div class="auth-card" id="successCard" style="display:none;">
      <div class="auth-logo">
        <iconify-icon icon="lucide:check-circle" style="font-size:24px;color:var(--success,#16a34a)"></iconify-icon>
      </div>
      <h1 class="auth-title">Password updated!</h1>
      <p class="auth-subtitle">Your password has been changed successfully. You can now sign in with your new password.</p>
      <a href="login" class="btn btn-primary"
         style="display:block;width:100%;padding:14px;font-size:16px;text-align:center;margin-top:8px;">
        Sign In
      </a>
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

    // Password toggle helpers
    function setupToggle(btnId, iconId, inputId) {
      document.getElementById(btnId).addEventListener('click', () => {
        const inp  = document.getElementById(inputId);
        const show = inp.type === 'password';
        inp.type   = show ? 'text' : 'password';
        document.getElementById(iconId).setAttribute('icon', show ? 'lucide:eye-off' : 'lucide:eye');
      });
    }
    setupToggle('togglePwd1', 'eyeIcon1', 'new_password');
    setupToggle('togglePwd2', 'eyeIcon2', 'confirm_password');

    // Read token from URL
    const params  = new URLSearchParams(window.location.search);
    const token   = params.get('token')   || '';
    const userId  = params.get('user_id') || '';

    if (!token) {
      document.getElementById('resetCard').style.display   = 'none';
      document.getElementById('invalidCard').style.display = 'block';
    } else {
      document.getElementById('tokenField').value = token;
    }

    document.getElementById('resetForm').addEventListener('submit', async function(e) {
      e.preventDefault();
      const btn      = document.getElementById('resetBtn');
      const feedback = document.getElementById('resetFeedback');
      const origHTML = btn.innerHTML;
      btn.disabled   = true;
      btn.innerHTML  = '<iconify-icon icon="lucide:loader-circle" style="font-size:18px;margin-right:8px;animation:spin 1s linear infinite"></iconify-icon>Updating…';
      feedback.style.display = 'none';

      try {
        const res  = await fetch('process_form.php', { method: 'POST', body: new FormData(this) });
        const data = await res.json();

        if (data.success) {
          document.getElementById('resetCard').style.display   = 'none';
          document.getElementById('successCard').style.display = 'block';
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
