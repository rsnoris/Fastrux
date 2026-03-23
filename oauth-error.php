<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <link rel="icon" href="favicon.svg" type="image/svg+xml" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sign-In Error — Fastrux Logistics</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="shared.css" />
  <script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>
  <style>
    .error-icon-wrap {
      width: 72px; height: 72px; border-radius: 50%;
      background: linear-gradient(135deg, #ef4444, #dc2626);
      display: flex; align-items: center; justify-content: center;
      margin: 0 auto 24px; font-size: 36px; color: #fff;
    }
    .auth-actions { display: flex; flex-direction: column; gap: 12px; margin-top: 28px; }
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
    <a class="nav-link" href="contact">Contact</a>
    <div class="header-actions" style="margin-top:8px;">
      <a class="btn btn-outline" href="login">Login</a>
      <a class="btn btn-primary" href="quote">Get a Quote</a>
    </div>
  </nav>

  <div class="auth-wrapper">
    <div class="auth-card" style="text-align:center;">
      <div class="error-icon-wrap">
        <iconify-icon icon="lucide:x" style="font-size:36px;color:#fff;"></iconify-icon>
      </div>
      <h1 class="auth-title" style="margin-bottom:12px;">Sign-In Failed</h1>
      <p id="errorMsg" class="auth-subtitle" style="margin-bottom:0;color:var(--muted-foreground);"></p>

      <div class="auth-actions">
        <a class="btn btn-primary" href="login" style="width:100%;padding:13px;font-size:15px;justify-content:center;">
          <iconify-icon icon="lucide:arrow-left" style="font-size:17px;margin-right:8px;"></iconify-icon>
          Return to Login
        </a>
        <a class="btn btn-outline" href="register" style="width:100%;padding:13px;font-size:15px;justify-content:center;">
          <iconify-icon icon="lucide:user-plus" style="font-size:17px;margin-right:8px;"></iconify-icon>
          Create Account with Email
        </a>
      </div>

      <p class="auth-footer-text" style="margin-top:20px;font-size:13px;">
        Need help?
        <a href="contact">Contact support</a>
      </p>
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

    // Safely display the error message from the query string
    const params = new URLSearchParams(window.location.search);
    const msg    = params.get('msg') || 'An unexpected error occurred during sign-in. Please try again.';
    document.getElementById('errorMsg').textContent = msg;
  </script>
</body>
</html>
