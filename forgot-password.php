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
        <a class="nav-link active" href="login.php">Login</a>
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
      <a class="btn btn-primary" href="quote.php">Get a Quote</a>
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
      <p class="auth-footer-text"><a href="login.php">← Back to Sign In</a></p>
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
      <a href="login.php" class="btn btn-primary"
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

    document.getElementById('resetForm').addEventListener('submit', function(e) {
      e.preventDefault();
      const email = document.getElementById('email').value.trim();
      document.getElementById('sentTo').textContent = email;
      document.getElementById('requestCard').style.display = 'none';
      document.getElementById('confirmCard').style.display  = 'block';
    });
  </script>
</body>
</html>
