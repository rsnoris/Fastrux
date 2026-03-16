<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <link rel="icon" href="favicon.svg" type="image/svg+xml" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>My Account — Fastrux Logistics</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="shared.css" />
  <script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>
  <style>
    body { background: var(--muted); }

    /* ── Page layout ── */
    .account-layout {
      display: grid;
      grid-template-columns: 260px 1fr;
      gap: 28px;
      padding-top: 32px;
      padding-bottom: 48px;
    }

    /* ── Sidebar ── */
    .account-sidebar {
      position: sticky;
      top: 88px;
      height: fit-content;
    }
    .sidebar-card {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius-xl);
      padding: 24px;
      margin-bottom: 16px;
    }
    .user-avatar {
      width: 64px; height: 64px;
      background: var(--secondary);
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 26px;
      font-weight: 700;
      color: var(--primary);
      margin-bottom: 12px;
    }
    .user-name { font-size: 16px; font-weight: 700; margin-bottom: 2px; }
    .user-email { font-size: 13px; color: var(--muted-foreground); }
    .user-role-badge {
      display: inline-flex; align-items: center; gap: 5px;
      padding: 4px 10px;
      border-radius: 999px;
      font-size: 12px;
      font-weight: 600;
      background: var(--secondary);
      color: var(--primary);
      margin-top: 10px;
    }

    .kyc-progress {
      margin-top: 16px;
      padding-top: 16px;
      border-top: 1px solid var(--border);
    }
    .kyc-progress-label {
      display: flex; justify-content: space-between;
      font-size: 13px; font-weight: 500; margin-bottom: 8px;
    }
    .progress-bar {
      height: 6px;
      background: var(--border);
      border-radius: 999px;
      overflow: hidden;
    }
    .progress-fill {
      height: 100%;
      background: var(--primary);
      border-radius: 999px;
      transition: width .5s ease;
    }

    .sidebar-nav { display: flex; flex-direction: column; gap: 4px; }
    .sidebar-nav-item {
      display: flex; align-items: center; gap: 10px;
      padding: 10px 14px;
      border-radius: var(--radius-md);
      font-size: 14px; font-weight: 500;
      color: var(--foreground);
      text-decoration: none;
      cursor: pointer;
      transition: background .15s, color .15s;
      border: none; background: none; width: 100%; text-align: left;
    }
    .sidebar-nav-item:hover { background: var(--muted); }
    .sidebar-nav-item.active { background: var(--secondary); color: var(--primary); font-weight: 600; }

    /* ── Content cards ── */
    .account-content { display: flex; flex-direction: column; gap: 24px; }

    .content-card {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius-xl);
      padding: 32px;
    }
    .content-card-header {
      display: flex; justify-content: space-between; align-items: flex-start;
      margin-bottom: 24px;
      padding-bottom: 20px;
      border-bottom: 1px solid var(--border);
    }
    .content-card-title { font-size: 18px; font-weight: 700; margin-bottom: 4px; }
    .content-card-subtitle { font-size: 13px; color: var(--muted-foreground); }

    /* KYC tabs */
    .kyc-tabs { display: none; }
    .kyc-tabs.active { display: block; }

    /* KYC status pill */
    .kyc-status {
      display: inline-flex; align-items: center; gap: 6px;
      padding: 6px 14px;
      border-radius: 999px;
      font-size: 13px;
      font-weight: 600;
    }
    .kyc-status.verified   { background: #e6f9ee; color: var(--success); }
    .kyc-status.pending    { background: #fff7e6; color: #d97706; }
    .kyc-status.incomplete { background: #fef2f2; color: var(--destructive); }

    /* Form layout overrides */
    .form-row-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
    .form-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .span-2 { grid-column: span 2; }
    .span-3 { grid-column: span 3; }

    /* Feedback */
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
    .form-feedback.success { background:#e6f9ee; border:1px solid #a7f0c4; color:var(--success); display:flex; }
    .form-feedback.error   { background:#fef2f2; border:1px solid #fecaca; color:var(--destructive); display:flex; }

    /* Section dividers */
    .form-section-title {
      font-size: 13px; font-weight: 600; text-transform: uppercase;
      letter-spacing: .5px; color: var(--muted-foreground);
      margin-top: 28px; margin-bottom: 16px;
      padding-bottom: 8px;
      border-bottom: 1px solid var(--border);
    }

    /* Upload areas */
    .upload-area {
      border: 2px dashed var(--border);
      border-radius: var(--radius-lg);
      padding: 32px;
      text-align: center;
      cursor: pointer;
      transition: border-color .2s, background .2s;
    }
    .upload-area:hover { border-color: var(--primary); background: var(--secondary); }
    .upload-area iconify-icon { font-size: 32px; color: var(--muted-foreground); margin-bottom: 8px; display: block; }
    .upload-area p { font-size: 14px; color: var(--muted-foreground); }
    .upload-area input[type="file"] { display: none; }

    /* Not-logged-in overlay */
    #loginPrompt {
      text-align: center;
      padding: 64px 32px;
    }
    #loginPrompt iconify-icon { font-size: 48px; color: var(--muted-foreground); display: block; margin: 0 auto 16px; }

    @media (max-width: 1024px) {
      .account-layout { grid-template-columns: 1fr; }
      .account-sidebar { position: static; }
      .form-row-3 { grid-template-columns: 1fr 1fr; }
      .span-3 { grid-column: span 2; }
    }
    @media (max-width: 640px) {
      .account-layout { gap: 16px; padding-top: 20px; }
      .content-card { padding: 20px; }
      .form-row-3, .form-row-2 { grid-template-columns: 1fr; }
      .span-2, .span-3 { grid-column: span 1; }
    }
  </style>
</head>
<body>

  <!-- ── HEADER ── -->
  <header class="header">
    <div class="container header-content">
      <a href="index.html" class="logo">
        <iconify-icon icon="lucide:truck" style="font-size:28px;color:var(--primary)"></iconify-icon>
        Fastrux
      </a>
      <nav class="nav-links">
        <a class="nav-link" href="index.html">Home</a>
        <a class="nav-link" href="index.html#services">Services</a>
        <a class="nav-link" href="track.html">Tracking</a>
        <a class="nav-link" href="about.html">About Us</a>
        <a class="nav-link" href="contact.html">Contact</a>
        <a class="nav-link" href="driver-onboarding.html">Drive with Us</a>
      </nav>
      <div class="header-actions">
        <a class="nav-link active" href="account.php" id="navAccountLink">My Account</a>
        <a class="btn btn-primary" href="quote.html">Get a Quote</a>
      </div>
      <button class="hamburger" id="hamburger" aria-label="Toggle menu">
        <span></span><span></span><span></span>
      </button>
    </div>
  </header>
  <nav class="mobile-menu" id="mobileMenu">
    <a class="nav-link" href="index.html">Home</a>
    <a class="nav-link" href="index.html#services">Services</a>
    <a class="nav-link" href="track.html">Tracking</a>
    <a class="nav-link" href="about.html">About Us</a>
    <a class="nav-link" href="contact.html">Contact</a>
    <a class="nav-link" href="driver-onboarding.html">Drive with Us</a>
    <div class="header-actions" style="margin-top:8px;">
      <a class="btn btn-outline" href="account.php">My Account</a>
      <a class="btn btn-primary" href="quote.html">Get a Quote</a>
    </div>
  </nav>

  <!-- ── PAGE HERO ── -->
  <div class="page-hero">
    <div class="container">
      <h1>My Account</h1>
      <p>Manage your profile, complete KYC verification, and keep your information up to date.</p>
    </div>
  </div>

  <!-- ── CONTENT ── -->
  <div class="container account-layout" id="accountLayout" style="display:none;">

    <!-- Sidebar -->
    <aside class="account-sidebar">
      <div class="sidebar-card">
        <div class="user-avatar" id="userAvatar">?</div>
        <div class="user-name" id="userName">Loading…</div>
        <div class="user-email" id="userEmail"></div>
        <div class="user-role-badge" id="userRoleBadge">
          <iconify-icon icon="lucide:user" style="font-size:12px"></iconify-icon>
          <span id="userRoleLabel">User</span>
        </div>
        <div class="kyc-progress">
          <div class="kyc-progress-label">
            <span>KYC Completion</span>
            <span id="kycPercent">0%</span>
          </div>
          <div class="progress-bar"><div class="progress-fill" id="kycProgressFill" style="width:0%"></div></div>
        </div>
      </div>
      <div class="sidebar-card" style="padding:8px;">
        <nav class="sidebar-nav">
          <button class="sidebar-nav-item active" onclick="showTab('profile', this)">
            <iconify-icon icon="lucide:user-circle" style="font-size:18px"></iconify-icon>Profile
          </button>
          <button class="sidebar-nav-item" onclick="showTab('kyc', this)">
            <iconify-icon icon="lucide:shield-check" style="font-size:18px"></iconify-icon>KYC Verification
          </button>
          <button class="sidebar-nav-item" onclick="showTab('documents', this)">
            <iconify-icon icon="lucide:file-check" style="font-size:18px"></iconify-icon>Documents
          </button>
          <button class="sidebar-nav-item" onclick="showTab('security', this)">
            <iconify-icon icon="lucide:lock" style="font-size:18px"></iconify-icon>Security
          </button>
          <hr style="border:none;border-top:1px solid var(--border);margin:8px 0;">
          <button class="sidebar-nav-item" onclick="logout()" style="color:var(--destructive);">
            <iconify-icon icon="lucide:log-out" style="font-size:18px"></iconify-icon>Sign Out
          </button>
        </nav>
      </div>
    </aside>

    <!-- Main content -->
    <div class="account-content">

      <!-- ── PROFILE TAB ── -->
      <div class="kyc-tabs active" id="tab-profile">
        <div class="content-card">
          <div class="content-card-header">
            <div>
              <div class="content-card-title">Profile Information</div>
              <div class="content-card-subtitle">Update your basic personal and company details.</div>
            </div>
          </div>
          <div class="form-feedback" id="profileFeedback"></div>
          <form id="profileForm" novalidate>
            <input type="hidden" name="form_type" value="kyc_update" />
            <input type="hidden" name="section" value="profile" />
            <div class="form-row-2">
              <div class="form-group">
                <label for="p_first_name">First Name *</label>
                <input class="form-control" type="text" id="p_first_name" name="first_name" required />
              </div>
              <div class="form-group">
                <label for="p_last_name">Last Name *</label>
                <input class="form-control" type="text" id="p_last_name" name="last_name" required />
              </div>
            </div>
            <div class="form-group">
              <label for="p_email">Email Address *</label>
              <input class="form-control" type="email" id="p_email" name="email" required />
            </div>
            <div class="form-row-2">
              <div class="form-group">
                <label for="p_phone">Phone Number</label>
                <input class="form-control" type="tel" id="p_phone" name="phone" placeholder="+1 (555) 000-0000" />
              </div>
              <div class="form-group">
                <label for="p_dob">Date of Birth</label>
                <input class="form-control" type="date" id="p_dob" name="dob" />
              </div>
            </div>
            <div class="form-group">
              <label for="p_address">Home Address</label>
              <input class="form-control" type="text" id="p_address" name="address" placeholder="Street address, city, state, ZIP" />
            </div>
            <div class="form-row-2">
              <div class="form-group">
                <label for="p_company">Company Name</label>
                <input class="form-control" type="text" id="p_company" name="company" placeholder="Optional" />
              </div>
              <div class="form-group">
                <label for="p_role">Account Role *</label>
                <select class="form-control" id="p_role" name="role" onchange="handleRoleChange(this.value)">
                  <option value="customer">Customer / Shipper</option>
                  <option value="driver">Driver</option>
                  <option value="owner_operator">Owner &amp; Operator</option>
                </select>
              </div>
            </div>
            <div style="display:flex;justify-content:flex-end;margin-top:8px;">
              <button type="submit" class="btn btn-primary" id="profileSaveBtn">
                <iconify-icon icon="lucide:save" style="font-size:15px;margin-right:6px"></iconify-icon>Save Profile
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- ── KYC TAB ── -->
      <div class="kyc-tabs" id="tab-kyc">

        <!-- KYC overview -->
        <div class="content-card" style="margin-bottom:0;">
          <div class="content-card-header">
            <div>
              <div class="content-card-title">KYC Verification</div>
              <div class="content-card-subtitle">Provide the required information to verify your identity and unlock full account features.</div>
            </div>
            <div class="kyc-status incomplete" id="kycStatusPill">
              <iconify-icon icon="lucide:alert-circle" style="font-size:14px"></iconify-icon>
              Incomplete
            </div>
          </div>

          <!-- Personal Identity -->
          <div class="form-section-title">Personal Identity</div>
          <div class="form-feedback" id="kycFeedback"></div>
          <form id="kycForm" novalidate>
            <input type="hidden" name="form_type" value="kyc_update" />
            <input type="hidden" name="section" value="kyc" />
            <div class="form-row-2">
              <div class="form-group">
                <label for="k_national_id">National ID / Passport Number *</label>
                <input class="form-control" type="text" id="k_national_id" name="national_id" placeholder="e.g. P12345678" required />
              </div>
              <div class="form-group">
                <label for="k_id_expiry">ID Expiry Date *</label>
                <input class="form-control" type="date" id="k_id_expiry" name="id_expiry" required />
              </div>
            </div>
            <div class="form-row-2">
              <div class="form-group">
                <label for="k_nationality">Nationality *</label>
                <input class="form-control" type="text" id="k_nationality" name="nationality" placeholder="e.g. United States" required />
              </div>
              <div class="form-group">
                <label for="k_ssn_last4">SSN Last 4 Digits</label>
                <input class="form-control" type="text" id="k_ssn_last4" name="ssn_last4" maxlength="4" placeholder="1234" />
              </div>
            </div>

            <!-- Role-specific KYC fields -->

            <!-- Customer fields -->
            <div id="kyc-customer" class="kyc-role-section">
              <div class="form-section-title">Business / Shipping Details</div>
              <div class="form-row-2">
                <div class="form-group">
                  <label for="k_business_type">Business Type</label>
                  <select class="form-control" id="k_business_type" name="business_type">
                    <option value="">Select type…</option>
                    <option value="individual">Individual</option>
                    <option value="llc">LLC</option>
                    <option value="corporation">Corporation</option>
                    <option value="partnership">Partnership</option>
                    <option value="non_profit">Non-Profit</option>
                    <option value="other">Other</option>
                  </select>
                </div>
                <div class="form-group">
                  <label for="k_tax_id">Tax ID / EIN</label>
                  <input class="form-control" type="text" id="k_tax_id" name="tax_id" placeholder="XX-XXXXXXX" />
                </div>
              </div>
              <div class="form-group">
                <label for="k_billing_address">Billing Address</label>
                <input class="form-control" type="text" id="k_billing_address" name="billing_address" placeholder="Street, city, state, ZIP, country" />
              </div>
              <div class="form-row-2">
                <div class="form-group">
                  <label for="k_annual_shipments">Estimated Annual Shipments</label>
                  <select class="form-control" id="k_annual_shipments" name="annual_shipments">
                    <option value="">Select range…</option>
                    <option value="1-10">1–10</option>
                    <option value="11-50">11–50</option>
                    <option value="51-200">51–200</option>
                    <option value="200+">200+</option>
                  </select>
                </div>
                <div class="form-group">
                  <label for="k_primary_service">Primary Service Needed</label>
                  <select class="form-control" id="k_primary_service" name="primary_service">
                    <option value="">Select service…</option>
                    <option value="ocean">Ocean Freight</option>
                    <option value="air">Air Freight</option>
                    <option value="ground">Ground Transport</option>
                    <option value="warehousing">Warehousing</option>
                  </select>
                </div>
              </div>
            </div>

            <!-- Driver fields -->
            <div id="kyc-driver" class="kyc-role-section" style="display:none;">
              <div class="form-section-title">Driver Details</div>
              <div class="form-row-2">
                <div class="form-group">
                  <label for="k_license_number">CDL / Driver Licence Number *</label>
                  <input class="form-control" type="text" id="k_license_number" name="license_number" placeholder="e.g. D123456789" />
                </div>
                <div class="form-group">
                  <label for="k_license_expiry">Licence Expiry Date</label>
                  <input class="form-control" type="date" id="k_license_expiry" name="license_expiry" />
                </div>
              </div>
              <div class="form-row-3">
                <div class="form-group">
                  <label for="k_van_make">Vehicle Make</label>
                  <input class="form-control" type="text" id="k_van_make" name="van_make" placeholder="e.g. Ford" />
                </div>
                <div class="form-group">
                  <label for="k_van_model">Vehicle Model</label>
                  <input class="form-control" type="text" id="k_van_model" name="van_model" placeholder="e.g. Transit" />
                </div>
                <div class="form-group">
                  <label for="k_van_reg">Registration Plate</label>
                  <input class="form-control" type="text" id="k_van_reg" name="van_reg" placeholder="e.g. ABC-1234" />
                </div>
              </div>
              <div class="form-row-2">
                <div class="form-group">
                  <label for="k_insurance_expiry">Insurance Expiry</label>
                  <input class="form-control" type="date" id="k_insurance_expiry" name="insurance_expiry" />
                </div>
                <div class="form-group">
                  <label for="k_years_exp">Years of Experience</label>
                  <input class="form-control" type="number" id="k_years_exp" name="years_experience" min="0" max="50" placeholder="0" />
                </div>
              </div>
              <div class="form-group">
                <label for="k_operating_areas">Operating Areas</label>
                <input class="form-control" type="text" id="k_operating_areas" name="operating_areas" placeholder="e.g. Texas, Oklahoma, Louisiana" />
              </div>
            </div>

            <!-- Owner & Operator fields -->
            <div id="kyc-owner_operator" class="kyc-role-section" style="display:none;">
              <div class="form-section-title">Owner &amp; Operator Details</div>
              <div class="form-row-2">
                <div class="form-group">
                  <label for="k_business_name">Business / DBA Name *</label>
                  <input class="form-control" type="text" id="k_business_name" name="business_name" placeholder="Your business name" />
                </div>
                <div class="form-group">
                  <label for="k_mc_number">MC / DOT Number</label>
                  <input class="form-control" type="text" id="k_mc_number" name="mc_number" placeholder="MC-XXXXXX" />
                </div>
              </div>
              <div class="form-row-2">
                <div class="form-group">
                  <label for="k_fleet_size">Fleet Size</label>
                  <select class="form-control" id="k_fleet_size" name="fleet_size">
                    <option value="">Select…</option>
                    <option value="1">1 vehicle</option>
                    <option value="2-5">2–5 vehicles</option>
                    <option value="6-15">6–15 vehicles</option>
                    <option value="16-50">16–50 vehicles</option>
                    <option value="50+">50+ vehicles</option>
                  </select>
                </div>
                <div class="form-group">
                  <label for="k_oo_tax_id">Business Tax ID / EIN</label>
                  <input class="form-control" type="text" id="k_oo_tax_id" name="oo_tax_id" placeholder="XX-XXXXXXX" />
                </div>
              </div>
              <div class="form-row-2">
                <div class="form-group">
                  <label for="k_oo_license_number">Owner CDL Number</label>
                  <input class="form-control" type="text" id="k_oo_license_number" name="oo_license_number" placeholder="e.g. D123456789" />
                </div>
                <div class="form-group">
                  <label for="k_oo_insurance_expiry">Fleet Insurance Expiry</label>
                  <input class="form-control" type="date" id="k_oo_insurance_expiry" name="oo_insurance_expiry" />
                </div>
              </div>
              <div class="form-group">
                <label for="k_oo_operating_areas">Operating Regions</label>
                <input class="form-control" type="text" id="k_oo_operating_areas" name="oo_operating_areas" placeholder="e.g. Southeast US, Nationwide" />
              </div>
            </div>

            <div style="display:flex;justify-content:flex-end;margin-top:8px;">
              <button type="submit" class="btn btn-primary" id="kycSaveBtn">
                <iconify-icon icon="lucide:shield-check" style="font-size:15px;margin-right:6px"></iconify-icon>Save KYC Information
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- ── DOCUMENTS TAB ── -->
      <div class="kyc-tabs" id="tab-documents">
        <div class="content-card">
          <div class="content-card-header">
            <div>
              <div class="content-card-title">Document Upload</div>
              <div class="content-card-subtitle">Upload supporting documents for identity and compliance verification.</div>
            </div>
          </div>
          <div class="form-feedback" id="docFeedback"></div>
          <form id="docForm" enctype="multipart/form-data" novalidate>
            <input type="hidden" name="form_type" value="kyc_update" />
            <input type="hidden" name="section" value="documents" />

            <div class="form-section-title">Identity Documents</div>
            <div class="form-row-2">
              <div class="form-group">
                <label>Government-Issued ID (front)</label>
                <div class="upload-area" onclick="this.querySelector('input').click()">
                  <iconify-icon icon="lucide:id-card"></iconify-icon>
                  <p id="doc_id_front_label">Click to upload or drag & drop<br><small>JPG, PNG or PDF · max 10 MB</small></p>
                  <input type="file" name="doc_id_front" accept=".jpg,.jpeg,.png,.pdf" onchange="setFileLabel(this,'doc_id_front_label')" />
                </div>
              </div>
              <div class="form-group">
                <label>Government-Issued ID (back)</label>
                <div class="upload-area" onclick="this.querySelector('input').click()">
                  <iconify-icon icon="lucide:id-card"></iconify-icon>
                  <p id="doc_id_back_label">Click to upload or drag & drop<br><small>JPG, PNG or PDF · max 10 MB</small></p>
                  <input type="file" name="doc_id_back" accept=".jpg,.jpeg,.png,.pdf" onchange="setFileLabel(this,'doc_id_back_label')" />
                </div>
              </div>
            </div>

            <div class="form-section-title">Proof of Address</div>
            <div class="form-row-2">
              <div class="form-group">
                <label>Utility Bill / Bank Statement</label>
                <div class="upload-area" onclick="this.querySelector('input').click()">
                  <iconify-icon icon="lucide:file-text"></iconify-icon>
                  <p id="doc_address_proof_label">Click to upload or drag & drop<br><small>JPG, PNG or PDF · max 10 MB</small></p>
                  <input type="file" name="doc_address_proof" accept=".jpg,.jpeg,.png,.pdf" onchange="setFileLabel(this,'doc_address_proof_label')" />
                </div>
              </div>
              <div id="docDriverExtra" style="display:none;">
                <div class="form-group">
                  <label>Driver Licence Document</label>
                  <div class="upload-area" onclick="this.querySelector('input').click()">
                    <iconify-icon icon="lucide:badge-check"></iconify-icon>
                    <p id="doc_licence_label">Click to upload or drag & drop<br><small>JPG, PNG or PDF · max 10 MB</small></p>
                    <input type="file" name="doc_licence" accept=".jpg,.jpeg,.png,.pdf" onchange="setFileLabel(this,'doc_licence_label')" />
                  </div>
                </div>
              </div>
            </div>

            <div style="display:flex;justify-content:flex-end;margin-top:8px;">
              <button type="submit" class="btn btn-primary" id="docSaveBtn">
                <iconify-icon icon="lucide:upload-cloud" style="font-size:15px;margin-right:6px"></iconify-icon>Upload Documents
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- ── SECURITY TAB ── -->
      <div class="kyc-tabs" id="tab-security">
        <div class="content-card">
          <div class="content-card-header">
            <div>
              <div class="content-card-title">Security Settings</div>
              <div class="content-card-subtitle">Update your password to keep your account secure.</div>
            </div>
          </div>
          <div class="form-feedback" id="securityFeedback"></div>
          <form id="securityForm" novalidate>
            <input type="hidden" name="form_type" value="kyc_update" />
            <input type="hidden" name="section" value="security" />
            <div class="form-group">
              <label for="s_current_password">Current Password *</label>
              <input class="form-control" type="password" id="s_current_password" name="current_password" autocomplete="current-password" required />
            </div>
            <div class="form-row-2">
              <div class="form-group">
                <label for="s_new_password">New Password *</label>
                <input class="form-control" type="password" id="s_new_password" name="new_password" autocomplete="new-password" minlength="8" required />
              </div>
              <div class="form-group">
                <label for="s_confirm_password">Confirm New Password *</label>
                <input class="form-control" type="password" id="s_confirm_password" name="confirm_password" autocomplete="new-password" required />
              </div>
            </div>
            <div style="display:flex;justify-content:flex-end;margin-top:8px;">
              <button type="submit" class="btn btn-primary" id="securitySaveBtn">
                <iconify-icon icon="lucide:lock" style="font-size:15px;margin-right:6px"></iconify-icon>Update Password
              </button>
            </div>
          </form>
        </div>
      </div>

    </div><!-- /account-content -->
  </div><!-- /account-layout -->

  <!-- Not logged in prompt -->
  <div class="container" id="loginPrompt" style="display:none;">
    <div class="content-card" style="max-width:480px;margin:0 auto;">
      <div id="loginPromptInner">
        <iconify-icon icon="lucide:lock-keyhole"></iconify-icon>
        <p style="font-size:18px;font-weight:700;margin-bottom:8px;">Sign in to access your account</p>
        <p style="font-size:14px;color:var(--muted-foreground);margin-bottom:24px;">You need to be logged in to view your account details and complete KYC verification.</p>
        <div style="display:flex;gap:12px;justify-content:center;">
          <a href="login.html?redirect=account.php" class="btn btn-primary">Sign In</a>
          <a href="register.html?redirect=account.php" class="btn btn-outline">Create Account</a>
        </div>
      </div>
    </div>
  </div>

  <!-- ── FOOTER ── -->
  <footer class="footer">
    <div class="container">
      <div class="footer-grid">
        <div class="footer-brand">
          <a href="index.html" class="logo">
            <iconify-icon icon="lucide:truck" style="font-size:24px;color:var(--primary)"></iconify-icon>
            Fastrux
          </a>
          <p>Delivering excellence in logistics and supply chain management worldwide. Your trusted partner for seamless transportation.</p>
        </div>
        <div>
          <h4 class="footer-heading">Services</h4>
          <div class="footer-links">
            <a href="ocean-freight.html">Ocean Freight</a>
            <a href="air-freight.html">Air Freight</a>
            <a href="ground-transport.html">Ground Transport</a>
            <a href="warehousing.html">Warehousing</a>
          </div>
        </div>
        <div>
          <h4 class="footer-heading">Company</h4>
          <div class="footer-links">
            <a href="about.html">About Us</a>
            <a href="careers.html">Careers</a>
            <a href="driver-onboarding.html">Drive with Us</a>
            <a href="news.html">News &amp; Media</a>
            <a href="contact.html">Contact</a>
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
    // ── Mobile menu ─────────────────────────────────────────────
    const ham = document.getElementById('hamburger');
    const mob = document.getElementById('mobileMenu');
    ham.addEventListener('click', () => { ham.classList.toggle('open'); mob.classList.toggle('open'); });

    // ── Auth check ──────────────────────────────────────────────
    let currentUser = null;
    try { currentUser = JSON.parse(localStorage.getItem('fx_user')); } catch(e) {}

    const layout      = document.getElementById('accountLayout');
    const loginPrompt = document.getElementById('loginPrompt');

    if (!currentUser || !currentUser.id) {
      loginPrompt.style.display = 'block';
    } else {
      layout.style.display = 'grid';
      populateSidebar(currentUser);
      loadUserData(currentUser.id);
    }

    function populateSidebar(user) {
      const initials = ((user.first_name || '?')[0] + (user.last_name || '')[0]).toUpperCase();
      document.getElementById('userAvatar').textContent      = initials;
      document.getElementById('userName').textContent        = (user.first_name || '') + ' ' + (user.last_name || '');
      document.getElementById('userEmail').textContent       = user.email || '';
      document.getElementById('userRoleLabel').textContent   = formatRole(user.role || 'customer');

      // Pre-fill profile form
      document.getElementById('p_first_name').value = user.first_name || '';
      document.getElementById('p_last_name').value  = user.last_name  || '';
      document.getElementById('p_email').value      = user.email      || '';
      if (user.role) document.getElementById('p_role').value = user.role;
      handleRoleChange(user.role || 'customer');
    }

    function formatRole(role) {
      const map = { customer: 'Customer / Shipper', driver: 'Driver', owner_operator: 'Owner & Operator' };
      return map[role] || role;
    }

    // ── Load saved user data ────────────────────────────────────
    function loadUserData(userId) {
      fetch('process_form.php', {
        method: 'POST',
        body: new URLSearchParams({ form_type: 'kyc_load', user_id: userId })
      })
      .then(r => r.json())
      .then(data => {
        if (data.success && data.kyc) {
          fillFormFromData(data.kyc);
          updateKycProgress(data.kyc);
        }
      })
      .catch(() => {/* silently ignore if no saved data */});
    }

    function fillFormFromData(kyc) {
      const fields = [
        'phone','dob','address','company','role',
        'national_id','id_expiry','nationality','ssn_last4',
        'business_type','tax_id','billing_address','annual_shipments','primary_service',
        'license_number','license_expiry','van_make','van_model','van_reg',
        'insurance_expiry','years_experience','operating_areas',
        'business_name','mc_number','fleet_size','oo_tax_id',
        'oo_license_number','oo_insurance_expiry','oo_operating_areas',
      ];
      fields.forEach(f => {
        const el = document.getElementById('p_' + f) || document.getElementById('k_' + f);
        if (el && kyc[f] !== undefined) el.value = kyc[f];
      });
      if (kyc.role) {
        document.getElementById('p_role').value = kyc.role;
        handleRoleChange(kyc.role);
      }
      // Update KYC status pill
      if (kyc.kyc_status) {
        const pill = document.getElementById('kycStatusPill');
        const icons = { verified: 'lucide:shield-check', pending: 'lucide:clock', incomplete: 'lucide:alert-circle' };
        const labels = { verified: 'Verified', pending: 'Pending Review', incomplete: 'Incomplete' };
        pill.className = 'kyc-status ' + kyc.kyc_status;
        pill.innerHTML = `<iconify-icon icon="${icons[kyc.kyc_status]||icons.incomplete}" style="font-size:14px"></iconify-icon>${labels[kyc.kyc_status]||'Incomplete'}`;
      }
    }

    function updateKycProgress(kyc) {
      const coreFields = ['national_id','id_expiry','nationality','phone','dob','address'];
      const filled = coreFields.filter(f => kyc[f] && kyc[f].toString().trim() !== '');
      const pct = Math.round((filled.length / coreFields.length) * 100);
      document.getElementById('kycPercent').textContent    = pct + '%';
      document.getElementById('kycProgressFill').style.width = pct + '%';
    }

    // ── Tab navigation ──────────────────────────────────────────
    function showTab(name, btn) {
      document.querySelectorAll('.kyc-tabs').forEach(t => t.classList.remove('active'));
      document.querySelectorAll('.sidebar-nav-item').forEach(b => b.classList.remove('active'));
      document.getElementById('tab-' + name).classList.add('active');
      if (btn) btn.classList.add('active');
    }

    // ── Role-specific KYC fields ────────────────────────────────
    function handleRoleChange(role) {
      document.querySelectorAll('.kyc-role-section').forEach(s => s.style.display = 'none');
      const section = document.getElementById('kyc-' + role);
      if (section) section.style.display = 'block';

      // Show driver licence upload for driver/owner_operator
      const driverExtra = document.getElementById('docDriverExtra');
      if (driverExtra) {
        driverExtra.style.display = (role === 'driver' || role === 'owner_operator') ? 'block' : 'none';
      }

      // Update role badge
      document.getElementById('userRoleLabel').textContent = formatRole(role);

      // Update localStorage
      if (currentUser) {
        currentUser.role = role;
        localStorage.setItem('fx_user', JSON.stringify(currentUser));
      }
    }

    // ── File upload labels ───────────────────────────────────────
    function setFileLabel(input, labelId) {
      const label = document.getElementById(labelId);
      if (!label) return;
      if (input.files && input.files[0]) {
        label.innerHTML = `<strong>${input.files[0].name}</strong><br><small>${(input.files[0].size/1024/1024).toFixed(2)} MB</small>`;
      }
    }

    // ── Generic form submit ──────────────────────────────────────
    function submitForm(formId, feedbackId, btnId, onSuccess) {
      const form     = document.getElementById(formId);
      const feedback = document.getElementById(feedbackId);
      const btn      = document.getElementById(btnId);

      form.addEventListener('submit', async (e) => {
        e.preventDefault();
        feedback.className = 'form-feedback';
        feedback.style.display = 'none';
        btn.disabled = true;
        const origHtml = btn.innerHTML;
        btn.innerHTML = '<iconify-icon icon="lucide:loader" style="font-size:15px;margin-right:6px;animation:spin 1s linear infinite"></iconify-icon>Saving…';

        try {
          const fd = new FormData(form);
          if (currentUser) {
            fd.append('user_id', currentUser.id);
            fd.append('user_email', currentUser.email || '');
            fd.append('user_role', document.getElementById('p_role')?.value || currentUser.role || 'customer');
          }
          const res  = await fetch('process_form.php', { method: 'POST', body: fd });
          const data = await res.json();
          feedback.className   = 'form-feedback ' + (data.success ? 'success' : 'error');
          feedback.innerHTML   = `<iconify-icon icon="${data.success?'lucide:check-circle':'lucide:x-circle'}" style="font-size:18px"></iconify-icon>${data.message}`;
          feedback.style.display = 'flex';
          if (data.success && onSuccess) onSuccess(data);
        } catch(err) {
          feedback.className   = 'form-feedback error';
          feedback.innerHTML   = '<iconify-icon icon="lucide:x-circle" style="font-size:18px"></iconify-icon>Network error. Please try again.';
          feedback.style.display = 'flex';
        } finally {
          btn.disabled  = false;
          btn.innerHTML = origHtml;
          feedback.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
      });
    }

    submitForm('profileForm', 'profileFeedback', 'profileSaveBtn', (data) => {
      if (currentUser) {
        currentUser.first_name = document.getElementById('p_first_name').value;
        currentUser.last_name  = document.getElementById('p_last_name').value;
        currentUser.email      = document.getElementById('p_email').value;
        currentUser.role       = document.getElementById('p_role').value;
        localStorage.setItem('fx_user', JSON.stringify(currentUser));
        populateSidebar(currentUser);
      }
    });

    submitForm('kycForm', 'kycFeedback', 'kycSaveBtn', (data) => {
      if (data.kyc) updateKycProgress(data.kyc);
      const pill = document.getElementById('kycStatusPill');
      pill.className = 'kyc-status pending';
      pill.innerHTML = '<iconify-icon icon="lucide:clock" style="font-size:14px"></iconify-icon>Pending Review';
    });

    submitForm('docForm', 'docFeedback', 'docSaveBtn');
    submitForm('securityForm', 'securityFeedback', 'securitySaveBtn');

    // ── Sign out ─────────────────────────────────────────────────
    function logout() {
      localStorage.removeItem('fx_user');
      window.location.href = 'login.html';
    }

    // Spin animation for loader icon
    document.head.insertAdjacentHTML('beforeend', '<style>@keyframes spin{to{transform:rotate(360deg)}}</style>');
  </script>
</body>
</html>
