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
    .role-notice {
      border-radius: 8px;
      padding: 12px 16px;
      font-size: 13px;
      margin-bottom: 4px;
      display: none;
    }
    .role-notice.info   { background:#eff6ff;border:1px solid #93c5fd;color:#1e40af; }
    .role-notice.warn   { background:#fffbeb;border:1px solid #fbbf24;color:#92400e; }
    .coverage-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 8px;
      margin-top: 6px;
    }
    .coverage-grid label {
      display: flex; align-items: center; gap: 8px;
      font-size: 14px; font-weight: 400; cursor: pointer;
    }
    .section-divider {
      border: none; border-top: 1px solid var(--border);
      margin: 20px 0 16px;
    }
    .section-heading {
      font-size: 13px; font-weight: 600; color: var(--muted-foreground);
      text-transform: uppercase; letter-spacing: .05em;
      margin-bottom: 12px;
    }
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
        <a class="nav-link" href="marketplace.php">Marketplace</a>
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
    <a class="nav-link" href="marketplace.php">Marketplace</a>
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

        <!-- ── Account type ── -->
        <div class="form-group">
          <label for="role">Account type *</label>
          <select class="form-control" id="role" name="role" required onchange="onRoleChange()">
            <optgroup label="Shippers &amp; Carriers">
              <option value="shipper">Shipper — I need to ship goods</option>
              <option value="driver">Owner Operator &amp; Driver</option>
            </optgroup>
            <optgroup label="Marketplace Partners">
              <option value="insurance_company">Insurance Company — Offer spot insurance</option>
              <option value="trucking_company">Trucking Company — List trucks for lease / sale</option>
            </optgroup>
            <optgroup label="Fastrux Team">
              <option value="corporate_staff">Corporate Staff — Fastrux team member</option>
            </optgroup>
          </select>
        </div>

        <!-- Staff pending notice -->
        <div id="staffPendingNotice" class="role-notice warn" style="display:none;">
          <iconify-icon icon="lucide:info" style="font-size:15px;margin-right:6px;vertical-align:middle;"></iconify-icon>
          <strong>Corporate Staff accounts require admin approval.</strong> After registering, an administrator will review and activate your account. You will not be able to log in until approved.
        </div>

        <!-- Insurance / Trucking company info notice -->
        <div id="companyNotice" class="role-notice info" style="display:none;">
          <iconify-icon icon="lucide:store" style="font-size:15px;margin-right:6px;vertical-align:middle;"></iconify-icon>
          <span id="companyNoticeText"></span>
        </div>

        <!-- ── Base fields ── -->
        <div class="name-row" style="margin-top:8px;">
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
        <div class="form-group" id="companyField">
          <label for="company">Company name <span id="companyRequired" style="color:var(--muted-foreground);font-weight:400;">(optional)</span></label>
          <input class="form-control" type="text" id="company" name="company"
                 placeholder="Acme Corp" autocomplete="organization" />
        </div>

        <!-- ── Insurance company specific fields ── -->
        <div id="insuranceFields" style="display:none;">
          <hr class="section-divider" />
          <p class="section-heading"><iconify-icon icon="lucide:shield-check" style="margin-right:4px;vertical-align:middle;"></iconify-icon>Insurance Company Details</p>
          <div class="name-row">
            <div class="form-group">
              <label for="insurance_license">License / Registration No.</label>
              <input class="form-control" type="text" id="insurance_license" name="insurance_license"
                     placeholder="e.g. INS-1234567" autocomplete="off" />
            </div>
            <div class="form-group">
              <label for="state_of_incorporation">State of Incorporation</label>
              <input class="form-control" type="text" id="state_of_incorporation" name="state_of_incorporation"
                     placeholder="e.g. Texas" autocomplete="off" />
            </div>
          </div>
          <div class="name-row">
            <div class="form-group">
              <label for="years_in_business_ins">Years in Business <span style="color:var(--muted-foreground);font-weight:400;">(0 = new)</span></label>
              <input class="form-control" type="number" id="years_in_business_ins" name="years_in_business"
                     placeholder="e.g. 10" min="0" />
            </div>
            <div class="form-group">
              <label for="contact_phone_ins">Phone Number</label>
              <input class="form-control" type="tel" id="contact_phone_ins" name="contact_phone"
                     placeholder="+1 (555) 000-0000" autocomplete="tel" />
            </div>
          </div>
          <div class="form-group">
            <label for="website_ins">Website <span style="color:var(--muted-foreground);font-weight:400;">(optional)</span></label>
            <input class="form-control" type="url" id="website_ins" name="website"
                   placeholder="https://example.com" autocomplete="url" />
          </div>
          <div class="form-group">
            <label>Types of Coverage Offered</label>
            <div class="coverage-grid">
              <label><input type="checkbox" name="coverage_types[]" value="cargo" style="accent-color:var(--primary);"> Cargo Insurance</label>
              <label><input type="checkbox" name="coverage_types[]" value="liability" style="accent-color:var(--primary);"> Liability</label>
              <label><input type="checkbox" name="coverage_types[]" value="physical_damage" style="accent-color:var(--primary);"> Physical Damage</label>
              <label><input type="checkbox" name="coverage_types[]" value="workers_comp" style="accent-color:var(--primary);"> Workers' Comp</label>
              <label><input type="checkbox" name="coverage_types[]" value="general_liability" style="accent-color:var(--primary);"> General Liability</label>
              <label><input type="checkbox" name="coverage_types[]" value="occupational_accident" style="accent-color:var(--primary);"> Occupational Accident</label>
              <label><input type="checkbox" name="coverage_types[]" value="bobtail" style="accent-color:var(--primary);"> Bobtail</label>
              <label><input type="checkbox" name="coverage_types[]" value="non_trucking" style="accent-color:var(--primary);"> Non-Trucking Liability</label>
            </div>
          </div>
        </div>

        <!-- ── Trucking company specific fields ── -->
        <div id="truckingFields" style="display:none;">
          <hr class="section-divider" />
          <p class="section-heading"><iconify-icon icon="lucide:truck" style="margin-right:4px;vertical-align:middle;"></iconify-icon>Trucking Company Details</p>
          <div class="name-row">
            <div class="form-group">
              <label for="dot_number">DOT Number</label>
              <input class="form-control" type="text" id="dot_number" name="dot_number"
                     placeholder="e.g. 1234567" autocomplete="off" />
            </div>
            <div class="form-group">
              <label for="mc_number">MC Number <span style="color:var(--muted-foreground);font-weight:400;">(optional)</span></label>
              <input class="form-control" type="text" id="mc_number" name="mc_number"
                     placeholder="e.g. MC-123456" autocomplete="off" />
            </div>
          </div>
          <div class="name-row">
            <div class="form-group">
              <label for="fleet_size">Fleet Size</label>
              <input class="form-control" type="number" id="fleet_size" name="fleet_size"
                     placeholder="e.g. 25" min="1" />
            </div>
            <div class="form-group">
              <label for="contact_phone_trk">Phone Number</label>
              <input class="form-control" type="tel" id="contact_phone_trk" name="contact_phone"
                     placeholder="+1 (555) 000-0000" autocomplete="tel" />
            </div>
          </div>
          <div class="form-group">
            <label for="truck_types">Types of Trucks in Fleet</label>
            <input class="form-control" type="text" id="truck_types" name="truck_types"
                   placeholder="e.g. Semi, Flatbed, Reefer" autocomplete="off" />
          </div>
          <div class="form-group">
            <label for="service_area">Primary Service Area</label>
            <input class="form-control" type="text" id="service_area" name="service_area"
                   placeholder="e.g. Southeast US, Nationwide" autocomplete="off" />
          </div>
          <div class="form-group">
            <label for="website_trk">Website <span style="color:var(--muted-foreground);font-weight:400;">(optional)</span></label>
            <input class="form-control" type="url" id="website_trk" name="website"
                   placeholder="https://example.com" autocomplete="url" />
          </div>
        </div>

        <!-- ── Password ── -->
        <div class="form-group" style="margin-top:8px;">
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
      <p class="auth-footer-text" style="margin-top:6px;font-size:13px;color:var(--muted-foreground);">
        Insurance company? <a href="insurance-login.php" style="color:var(--primary);">Insurance portal</a> &nbsp;·&nbsp;
        Trucking company? <a href="trucking-login.php" style="color:var(--primary);">Trucking portal</a>
      </p>
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
      const role             = document.getElementById('role').value;
      const staffNotice      = document.getElementById('staffPendingNotice');
      const companyNotice    = document.getElementById('companyNotice');
      const companyNoticeText= document.getElementById('companyNoticeText');
      const insuranceFields  = document.getElementById('insuranceFields');
      const truckingFields   = document.getElementById('truckingFields');
      const companyRequired  = document.getElementById('companyRequired');
      const companyInput     = document.getElementById('company');

      // Reset all conditional sections
      staffNotice.style.display    = 'none';
      companyNotice.style.display  = 'none';
      insuranceFields.style.display= 'none';
      truckingFields.style.display = 'none';
      companyRequired.textContent  = '(optional)';
      companyInput.required        = false;

      if (role === 'corporate_staff') {
        staffNotice.style.display = 'block';
      } else if (role === 'insurance_company') {
        companyNoticeText.textContent = 'You will be able to post spot insurance offerings in the Fastrux Marketplace after registration.';
        companyNotice.style.display   = 'block';
        insuranceFields.style.display = 'block';
        companyRequired.textContent   = '(required)';
        companyInput.required         = true;
      } else if (role === 'trucking_company') {
        companyNoticeText.textContent = 'You will be able to list trucks for lease or sale in the Fastrux Marketplace after registration.';
        companyNotice.style.display   = 'block';
        truckingFields.style.display  = 'block';
        companyRequired.textContent   = '(required)';
        companyInput.required         = true;
      }
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
          onRoleChange(); // update notice visibility after form reset

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

          // Redirect based on role
          const params   = new URLSearchParams(window.location.search);
          let redirect   = params.get('redirect');
          if (!redirect) {
            const dashMap = {
              insurance_company: 'insurance-dashboard.php',
              trucking_company:  'trucking-dashboard.php',
              driver:            'driver-dashboard.php',
              owner_operator:    'driver-dashboard.php',
              shipper:           'shipper-dashboard.php',
            };
            redirect = dashMap[data.role || role] || 'account.php';
          }
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
