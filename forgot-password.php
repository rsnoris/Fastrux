<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <link rel="icon" href="favicon.svg" type="image/svg+xml" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Forgot Password — Fastrux Logistics</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="shared.css" />
  <script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>
  <style>
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
    <div class="auth-card" id="requestCard">
      <div class="auth-logo">
        <iconify-icon icon="lucide:truck" style="font-size:24px"></iconify-icon>
        Fastrux
      </div>
      <h1 class="auth-title">Reset your password</h1>
      <p class="auth-subtitle">Enter your email address and we'll send you a link to reset your password.</p>
      <form id="resetForm">
        <div class="form-group">
          <label for="email">Email address</label>
          <input class="form-control" type="email" id="email" name="email"
                 placeholder="you@example.com" required autocomplete="email" />
        </div>
        <button type="submit" class="btn btn-primary"
                style="width:100%;padding:14px;font-size:16px;margin-top:8px;">
          Send Reset Link
        </button>
      </form>
      <p class="auth-footer-text"><a href="login">← Back to Sign In</a></p>
    </div>

    <div class="auth-card" id="confirmCard" style="display:none;">
      <div class="auth-logo">
        <iconify-icon icon="lucide:mail-check" style="font-size:24px"></iconify-icon>
      </div>
      <h1 class="auth-title">Check your email</h1>
      <p class="auth-subtitle">
        We've sent a password reset link to <strong id="sentTo"></strong>.
        If it doesn't appear within a few minutes, check your spam folder.
      </p>
      <!-- Dev/staging: show the reset link directly when no email server is configured -->
      <div id="resetLinkWrap" style="display:none;margin:16px 0;padding:12px 16px;background:var(--muted);border:1px solid var(--border);border-radius:var(--radius-md);word-break:break-all;font-size:13px;">
        <strong>Reset link:</strong><br/>
        <a id="resetLink" href="#" style="color:var(--primary);">—</a>
      </div>
      <a href="login" class="btn btn-primary"
         style="display:block;width:100%;padding:14px;font-size:16px;text-align:center;margin-top:8px;">
        Back to Sign In
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

    document.getElementById('resetForm').addEventListener('submit', async function(e) {
      e.preventDefault();
      const btn      = this.querySelector('button[type="submit"]');
      const email    = document.getElementById('email').value.trim();
      const origHTML = btn.innerHTML;
      btn.disabled   = true;
      btn.innerHTML  = '<iconify-icon icon="lucide:loader-circle" style="font-size:18px;margin-right:8px;animation:spin 1s linear infinite"></iconify-icon>Sending…';

      try {
        const fd = new FormData();
        fd.append('form_type', 'forgot_password');
        fd.append('email', email);
        const res  = await fetch('process_form.php', { method: 'POST', body: fd });
        const data = await res.json();

        if (data.success) {
          document.getElementById('sentTo').textContent = email;
          document.getElementById('requestCard').style.display = 'none';

          // If the API returns a reset token (dev/staging mode), build the link directly.
          if (data.reset_token && data.user_id) {
            const resetUrl = 'reset-password.php?token=' + encodeURIComponent(data.reset_token) + '&user_id=' + encodeURIComponent(data.user_id);
            document.getElementById('resetLinkWrap').style.display = 'block';
            document.getElementById('resetLink').href        = resetUrl;
            document.getElementById('resetLink').textContent = resetUrl;
          }

          document.getElementById('confirmCard').style.display = 'block';
        } else {
          // Show inline error
          let fb = document.getElementById('resetFeedback');
          if (!fb) {
            fb = document.createElement('div');
            fb.id        = 'resetFeedback';
            fb.className = 'form-feedback error';
            this.prepend(fb);
          }
          fb.textContent     = '✗ ' + data.message;
          fb.style.display   = 'flex';
          btn.disabled  = false;
          btn.innerHTML = origHTML;
        }
      } catch {
        let fb = document.getElementById('resetFeedback');
        if (!fb) {
          fb = document.createElement('div');
          fb.id        = 'resetFeedback';
          fb.className = 'form-feedback error';
          this.prepend(fb);
        }
        fb.textContent   = '✗ Network error — please try again.';
        fb.style.display = 'flex';
        btn.disabled  = false;
        btn.innerHTML = origHTML;
      }
    });
  </script>
</body>
</html>
