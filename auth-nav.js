/**
 * auth-nav.js — Fastrux auth-aware navigation
 * Updates the header and mobile menu based on the logged-in user's role stored in localStorage.
 * Include this script at the bottom of every public page that has the standard nav header.
 */
(function () {
  'use strict';

  var EMPLOYEE_ROLES = ['driver', 'owner_operator', 'corporate_staff'];

  function isEmployee(role) {
    return EMPLOYEE_ROLES.indexOf(role) !== -1;
  }

  function formatRole(role) {
    var map = {
      customer:        'Shipper',
      shipper:         'Shipper',
      driver:          'Owner & Operator / Driver',
      owner_operator:  'Owner & Operator',
      corporate_staff: 'Corporate Staff',
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
          quoteBtn.href = 'driver-dashboard.php';
          quoteBtn.textContent = 'Dashboard';
        }
      }
    }

    // ── Desktop nav links ───────────────────────────────────────
    var navLinks = document.querySelector('.nav-links');
    if (navLinks) {
      if (isEmployee(role)) {
        // Hide "Drive with Us" for employees who are already registered
        var driveLink = navLinks.querySelector('a[href="driver-onboarding.php"]');
        if (driveLink) driveLink.style.display = 'none';

        // Add a "Dashboard" link if not already present
        if (!navLinks.querySelector('a[href="driver-dashboard.php"]')) {
          var dashLink = document.createElement('a');
          dashLink.className = 'nav-link';
          dashLink.href = 'driver-dashboard.php';
          dashLink.textContent = 'Dashboard';
          navLinks.appendChild(dashLink);
        }
      } else {
        // For shippers: add "My Quotes" if not present
        if (!navLinks.querySelector('a[href="quote-dashboard.php"]')) {
          var quoteDashLink = document.createElement('a');
          quoteDashLink.className = 'nav-link';
          quoteDashLink.href = 'quote-dashboard.php';
          quoteDashLink.textContent = 'My Quotes';
          navLinks.appendChild(quoteDashLink);
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

      if (isEmployee(role)) {
        // Hide "Drive with Us" in mobile
        var mobileDriveLink = mobileMenu.querySelector('a[href="driver-onboarding.php"]');
        if (mobileDriveLink) mobileDriveLink.style.display = 'none';

        // Replace "Get a Quote" button with "Dashboard"
        var mobileQuoteBtn = mobileMenu.querySelector('a[href="quote.php"].btn');
        if (mobileQuoteBtn) {
          mobileQuoteBtn.href = 'driver-dashboard.php';
          mobileQuoteBtn.textContent = 'Dashboard';
        }
      } else {
        // For shippers: add My Quotes link in mobile menu if not present
        if (!mobileMenu.querySelector('a[href="quote-dashboard.php"]')) {
          var mobileQuoteDash = document.createElement('a');
          mobileQuoteDash.className = 'nav-link';
          mobileQuoteDash.href = 'quote-dashboard.php';
          mobileQuoteDash.textContent = 'My Quotes';
          // Insert before the header-actions div
          var mobileActions = mobileMenu.querySelector('.header-actions');
          if (mobileActions) {
            mobileMenu.insertBefore(mobileQuoteDash, mobileActions);
          } else {
            mobileMenu.appendChild(mobileQuoteDash);
          }
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
