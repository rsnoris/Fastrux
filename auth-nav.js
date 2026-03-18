/**
 * auth-nav.js — Fastrux auth-aware navigation
 * Updates the header and mobile menu based on the logged-in user's role stored in localStorage.
 * Include this script at the bottom of every public page that has the standard nav header.
 */
(function () {
  'use strict';

  var EMPLOYEE_ROLES  = ['driver', 'owner_operator', 'corporate_staff'];
  var SHIPPER_ROLES   = ['shipper', 'customer'];
  var ADMIN_ROLES     = ['admin', 'super_admin'];
  var COMPANY_ROLES   = ['insurance_company', 'trucking_company'];

  function isEmployee(role) {
    return EMPLOYEE_ROLES.indexOf(role) !== -1;
  }

  function isShipper(role) {
    return SHIPPER_ROLES.indexOf(role) !== -1;
  }

  function isAdmin(role) {
    return ADMIN_ROLES.indexOf(role) !== -1;
  }

  function isCompany(role) {
    return COMPANY_ROLES.indexOf(role) !== -1;
  }

  function companyDashHref(role) {
    return role === 'insurance_company' ? 'insurance-dashboard.php' : 'trucking-dashboard.php';
  }

  function formatRole(role) {
    var map = {
      customer:          'Shipper',
      shipper:           'Shipper',
      driver:            'Owner & Operator / Driver',
      owner_operator:    'Owner & Operator',
      corporate_staff:   'Corporate Staff',
      admin:             'Admin',
      super_admin:       'Super-Admin',
      insurance_company: 'Insurance Company',
      trucking_company:  'Trucking Company',
    };
    return map[role] || role;
  }

  function updateNav() {
    var user = null;
    try { user = JSON.parse(localStorage.getItem('fx_user')); } catch (e) {}
    if (!user || !user.id) return;

    var role = user.role || 'shipper';

    // ── Desktop header ──────────────────────────────────────────
    var headerActions = document.querySelector('.header-actions');
    if (headerActions) {
      // Replace Login link with My Account
      var loginLink = headerActions.querySelector('a[href="login.php"]');
      if (loginLink) {
        loginLink.href = 'account.php';
        loginLink.textContent = 'My Account';
        loginLink.classList.remove('active');
      }

      // For employees: change "Get a Quote" button to "Dashboard"
      if (isEmployee(role)) {
        var quoteBtn = headerActions.querySelector('a[href="quote.php"]');
        if (quoteBtn) {
          quoteBtn.href = role === 'corporate_staff' ? 'staff-dashboard.php' : 'driver-dashboard.php';
          quoteBtn.textContent = 'Dashboard';
        }
      }

      // For admins: change "Get a Quote" button to "Admin Dashboard"
      if (isAdmin(role)) {
        var quoteBtn2 = headerActions.querySelector('a[href="quote.php"]');
        if (quoteBtn2) {
          quoteBtn2.href = 'admin-dashboard.php';
          quoteBtn2.textContent = 'Admin Dashboard';
        }
      }

      // For company roles: change "Get a Quote" / "Join Marketplace" button to "Dashboard"
      if (isCompany(role)) {
        var dashHref = companyDashHref(role);
        var ctaBtn = headerActions.querySelector('a[href="quote.php"]') ||
                     headerActions.querySelector('a[href="register.php"]');
        if (ctaBtn) {
          ctaBtn.href = dashHref;
          ctaBtn.textContent = 'My Dashboard';
        }
      }
    }

    // ── Desktop nav links ───────────────────────────────────────
    var navLinks = document.querySelector('.nav-links');
    if (navLinks) {
      if (isAdmin(role)) {
        // Hide "Drive with Us" for admins
        var driveLink = navLinks.querySelector('a[href="driver-onboarding.php"]');
        if (driveLink) driveLink.style.display = 'none';

        // Add "Admin Dashboard" link if not already present
        if (!navLinks.querySelector('a[href="admin-dashboard.php"]')) {
          var adminDashLink = document.createElement('a');
          adminDashLink.className = 'nav-link';
          adminDashLink.href = 'admin-dashboard.php';
          adminDashLink.textContent = 'Admin Dashboard';
          navLinks.appendChild(adminDashLink);
        }
      } else if (isEmployee(role)) {
        // Hide "Drive with Us" for employees who are already registered
        var driveLink2 = navLinks.querySelector('a[href="driver-onboarding.php"]');
        if (driveLink2) driveLink2.style.display = 'none';

        // Add a "Dashboard" link if not already present
        var dashHref = role === 'corporate_staff' ? 'staff-dashboard.php' : 'driver-dashboard.php';
        if (!navLinks.querySelector('a[href="' + dashHref + '"]')) {
          var dashLink = document.createElement('a');
          dashLink.className = 'nav-link';
          dashLink.href = dashHref;
          dashLink.textContent = 'Dashboard';
          navLinks.appendChild(dashLink);
        }
      } else if (isShipper(role)) {
        // Add "My Dashboard" link for shippers if not already present
        if (!navLinks.querySelector('a[href="shipper-dashboard.php"]')) {
          var shipperDashLink = document.createElement('a');
          shipperDashLink.className = 'nav-link';
          shipperDashLink.href = 'shipper-dashboard.php';
          shipperDashLink.textContent = 'My Dashboard';
          navLinks.appendChild(shipperDashLink);
        }
      } else if (isCompany(role)) {
        // Hide "Drive with Us" for company users
        var driveLinkCo = navLinks.querySelector('a[href="driver-onboarding.php"]');
        if (driveLinkCo) driveLinkCo.style.display = 'none';

        // Add "My Dashboard" link for company accounts
        var coDashHref = companyDashHref(role);
        if (!navLinks.querySelector('a[href="' + coDashHref + '"]')) {
          var coDashLink = document.createElement('a');
          coDashLink.className = 'nav-link';
          coDashLink.href = coDashHref;
          coDashLink.textContent = 'My Dashboard';
          navLinks.appendChild(coDashLink);
        }
      }
    }

    // ── Mobile menu ─────────────────────────────────────────────
    var mobileMenu = document.getElementById('mobileMenu');
    if (mobileMenu) {
      // Replace any Login links in mobile menu
      mobileMenu.querySelectorAll('a[href="login.php"]').forEach(function (el) {
        el.href = 'account.php';
        el.textContent = 'My Account';
      });

      if (isAdmin(role)) {
        // Hide "Drive with Us" in mobile
        var mobileDriveLink = mobileMenu.querySelector('a[href="driver-onboarding.php"]');
        if (mobileDriveLink) mobileDriveLink.style.display = 'none';

        // Replace "Get a Quote" button with "Admin Dashboard"
        var mobileQuoteBtn = mobileMenu.querySelector('a[href="quote.php"].btn');
        if (mobileQuoteBtn) {
          mobileQuoteBtn.href = 'admin-dashboard.php';
          mobileQuoteBtn.textContent = 'Admin Dashboard';
        }
      } else if (isEmployee(role)) {
        // Hide "Drive with Us" in mobile
        var mobileDriveLink2 = mobileMenu.querySelector('a[href="driver-onboarding.php"]');
        if (mobileDriveLink2) mobileDriveLink2.style.display = 'none';

        // Replace "Get a Quote" button with "Dashboard"
        var mobileQuoteBtn2 = mobileMenu.querySelector('a[href="quote.php"].btn');
        if (mobileQuoteBtn2) {
          mobileQuoteBtn2.href = role === 'corporate_staff' ? 'staff-dashboard.php' : 'driver-dashboard.php';
          mobileQuoteBtn2.textContent = 'Dashboard';
        }
      } else if (isShipper(role)) {
        // Add "My Dashboard" link in mobile if not already present
        if (!mobileMenu.querySelector('a[href="shipper-dashboard.php"]')) {
          var mobileShipperDash = document.createElement('a');
          mobileShipperDash.className = 'nav-link';
          mobileShipperDash.href = 'shipper-dashboard.php';
          mobileShipperDash.textContent = 'My Dashboard';
          // Insert before the header-actions div
          var mobileActions = mobileMenu.querySelector('.header-actions');
          if (mobileActions) {
            mobileMenu.insertBefore(mobileShipperDash, mobileActions);
          } else {
            mobileMenu.appendChild(mobileShipperDash);
          }
        }
      } else if (isCompany(role)) {
        // Hide "Drive with Us" in mobile
        var mobileDriveLinkCo = mobileMenu.querySelector('a[href="driver-onboarding.php"]');
        if (mobileDriveLinkCo) mobileDriveLinkCo.style.display = 'none';

        // Replace CTA button with "My Dashboard"
        var mobileCoDashHref = companyDashHref(role);
        var mobileCoBtn = mobileMenu.querySelector('a[href="quote.php"].btn') ||
                          mobileMenu.querySelector('a[href="register.php"].btn');
        if (mobileCoBtn) {
          mobileCoBtn.href = mobileCoDashHref;
          mobileCoBtn.textContent = 'My Dashboard';
        }
      }
    }
  }

  // Run after DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', updateNav);
  } else {
    updateNav();
  }
})();
