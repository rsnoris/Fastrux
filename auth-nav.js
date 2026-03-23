/**
 * auth-nav.js — Fastrux auth-aware navigation
 * Updates the header and mobile menu based on the logged-in user's role stored in localStorage.
 * Include this script at the bottom of every public page that has the standard nav header.
 */
(function () {
  'use strict';

  var EMPLOYEE_ROLES      = ['driver', 'owner_operator', 'corporate_staff'];
  var SHIPPER_ROLES       = ['shipper', 'customer'];
  var ADMIN_ROLES         = ['admin', 'super_admin'];
  var COMPANY_ROLES       = ['insurance_company', 'trucking_company', 'gas_station', 'hotel'];
  var NOTIF_POLL_INTERVAL = 60000; // ms — how often to refresh unread message count
  var INACTIVITY_TIMEOUT  = 60000; // ms — auto-logout after 1 minute of inactivity

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
    if (role === 'insurance_company') return 'insurance-dashboard.php';
    if (role === 'trucking_company')  return 'trucking-dashboard.php';
    if (role === 'gas_station')       return 'gas-station-dashboard.php';
    if (role === 'hotel')             return 'hotel-dashboard.php';
    return 'marketplace.php';
  }

  function formatRole(role) {
    var map = {
      customer:          'Shipper',
      shipper:           'Shipper',
      driver:            'Driver',
      owner_operator:    'Owner Operator',
      corporate_staff:   'Corporate Staff',
      admin:             'Admin',
      super_admin:       'Super-Admin',
      insurance_company: 'Insurance Company',
      trucking_company:  'Trucking Company',
      gas_station:       'Gas Station',
      hotel:             'Hotel',
    };
    return map[role] || role;
  }

  // ── Inactivity auto-logout ──────────────────────────────────────
  function startInactivityTimer() {
    var lastActivity = Date.now();
    var loggedOut = false;

    function resetTimer() {
      lastActivity = Date.now();
    }

    ['scroll', 'touchstart', 'wheel', 'mousemove'].forEach(function (evt) {
      document.addEventListener(evt, resetTimer, { passive: true });
    });
    ['keydown', 'mousedown', 'click'].forEach(function (evt) {
      document.addEventListener(evt, resetTimer);
    });

    var checker = setInterval(function () {
      if (loggedOut) { clearInterval(checker); return; }
      if (Date.now() - lastActivity >= INACTIVITY_TIMEOUT) {
        loggedOut = true;
        clearInterval(checker);
        alert('You have been logged out due to 1 minute of inactivity.');
        localStorage.removeItem('fx_user');
        window.location.href = 'login';
      }
    }, 10000);
  }

  // ── Notification bell ──────────────────────────────────────────
  function injectNotificationBadge(userId) {
    var headerActions = document.querySelector('.header-actions');
    if (headerActions && !headerActions.querySelector('.nav-notif-bell')) {
      var bell = document.createElement('a');
      bell.href = 'messages.php';
      bell.className = 'nav-notif-bell';
      bell.title = 'Messages & Notifications';
      bell.style.cssText = 'position:relative;display:inline-flex;align-items:center;' +
        'color:var(--foreground);text-decoration:none;font-size:20px;padding:2px;';
      bell.innerHTML = '<iconify-icon icon="lucide:bell"></iconify-icon>' +
        '<span class="nav-notif-count" style="display:none;position:absolute;top:-4px;right:-6px;' +
        'background:var(--destructive,#e02424);color:#fff;font-size:10px;font-weight:700;' +
        'padding:1px 5px;border-radius:20px;min-width:16px;text-align:center;line-height:16px;"></span>';
      var firstChild = headerActions.firstChild;
      headerActions.insertBefore(bell, firstChild);
    }

    function refreshCount() {
      fetch('messages_data.php?action=unread_count&user_id=' + encodeURIComponent(userId))
        .then(function (r) { return r.json(); })
        .then(function (data) {
          var cnt   = data.unread_count || 0;
          var bellEl = document.querySelector('.nav-notif-bell');
          if (!bellEl) return;
          var badge = bellEl.querySelector('.nav-notif-count');
          if (!badge) return;
          if (cnt > 0) {
            badge.textContent = cnt > 99 ? '99+' : cnt;
            badge.style.display = 'block';
          } else {
            badge.style.display = 'none';
          }
        })
        .catch(function () {});
    }

    refreshCount();
    setInterval(refreshCount, NOTIF_POLL_INTERVAL);
  }

  // ── Hide public-only nav links for logged-in users ───────────
  function hidePublicNavLinks(container) {
    ['a[href="index.php"]', 'a[href="#services"]', 'a[href="index.php#services"]',
     'a[href="contact.php"]', 'a[href="#contact"]'].forEach(function (sel) {
      var el = container.querySelector(sel);
      if (el) el.style.display = 'none';
    });
  }

  // ── Add Messages & Documents links for all logged-in users ────
  function addAuthNavLinks(container, isMobile) {
    if (!container.querySelector('a[href="messages.php"]')) {
      var msgLink = document.createElement('a');
      msgLink.className = 'nav-link';
      msgLink.href = 'messages.php';
      msgLink.textContent = 'Messages';
      if (isMobile) {
        var mobileActionsMsg = container.querySelector('.header-actions');
        if (mobileActionsMsg) {
          container.insertBefore(msgLink, mobileActionsMsg);
        } else {
          container.appendChild(msgLink);
        }
      } else {
        container.appendChild(msgLink);
      }
    }
    if (!container.querySelector('a[href="documents.php"]')) {
      var docLink = document.createElement('a');
      docLink.className = 'nav-link';
      docLink.href = 'documents.php';
      docLink.textContent = 'Documents';
      if (isMobile) {
        var mobileActionsDoc = container.querySelector('.header-actions');
        if (mobileActionsDoc) {
          container.insertBefore(docLink, mobileActionsDoc);
        } else {
          container.appendChild(docLink);
        }
      } else {
        container.appendChild(docLink);
      }
    }
  }

  function updateNav() {
    var user = null;
    try { user = JSON.parse(localStorage.getItem('fx_user')); } catch (e) {}
    if (!user || !user.id) return;

    var role   = user.role || 'shipper';
    var userId = user.id;

    // ── Desktop header ──────────────────────────────────────────
    var headerActions = document.querySelector('.header-actions');
    if (headerActions) {
      // Replace Login link with My Account
      var loginLink = headerActions.querySelector('a[href="login.php"], a[href="login"]');
      if (loginLink) {
        loginLink.href = 'account.php';
        loginLink.textContent = 'My Account';
        loginLink.classList.remove('active');
      }

      // For employees: change "Get a Quote" button to "Dashboard"
      if (isEmployee(role)) {
        var quoteBtn = headerActions.querySelector('a[href="quote.php"], a[href="quote"]');
        if (quoteBtn) {
          quoteBtn.href = role === 'corporate_staff' ? 'staff-dashboard.php' : 'driver-dashboard.php';
          quoteBtn.textContent = 'Dashboard';
        }
      }

      // For admins: change "Get a Quote" button to "Admin Dashboard"
      if (isAdmin(role)) {
        var quoteBtn2 = headerActions.querySelector('a[href="quote.php"], a[href="quote"]');
        if (quoteBtn2) {
          quoteBtn2.href = 'admin-dashboard.php';
          quoteBtn2.textContent = 'Admin Dashboard';
        }
      }

      // For company roles: change "Get a Quote" / "Join Marketplace" button to "Dashboard"
      if (isCompany(role)) {
        var dashHref = companyDashHref(role);
        var ctaBtn = headerActions.querySelector('a[href="quote.php"], a[href="quote"]') ||
                     headerActions.querySelector('a[href="register.php"], a[href="register"]');
        if (ctaBtn) {
          ctaBtn.href = dashHref;
          ctaBtn.textContent = 'My Dashboard';
        }
      }
    }

    // ── Desktop nav links ───────────────────────────────────────
    var navLinks = document.querySelector('.nav-links');
    if (navLinks) {
      // Hide public-only links for logged-in users
      hidePublicNavLinks(navLinks);

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

        // Add "Loadboard" link for drivers / owner-operators
        if ((role === 'driver' || role === 'owner_operator') && !navLinks.querySelector('a[href="loadboard.php"]')) {
          var loadboardLink = document.createElement('a');
          loadboardLink.className = 'nav-link';
          loadboardLink.href = 'loadboard.php';
          loadboardLink.textContent = 'Loadboard';
          navLinks.appendChild(loadboardLink);
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
      // Hide public-only links for logged-in users
      hidePublicNavLinks(mobileMenu);

      // Replace any Login links in mobile menu
      mobileMenu.querySelectorAll('a[href="login.php"], a[href="login"]').forEach(function (el) {
        el.href = 'account.php';
        el.textContent = 'My Account';
      });

      if (isAdmin(role)) {
        // Hide "Drive with Us" in mobile
        var mobileDriveLink = mobileMenu.querySelector('a[href="driver-onboarding.php"]');
        if (mobileDriveLink) mobileDriveLink.style.display = 'none';

        // Replace "Get a Quote" button with "Admin Dashboard"
        var mobileQuoteBtn = mobileMenu.querySelector('a[href="quote.php"].btn, a[href="quote"].btn');
        if (mobileQuoteBtn) {
          mobileQuoteBtn.href = 'admin-dashboard.php';
          mobileQuoteBtn.textContent = 'Admin Dashboard';
        }
      } else if (isEmployee(role)) {
        // Hide "Drive with Us" in mobile
        var mobileDriveLink2 = mobileMenu.querySelector('a[href="driver-onboarding.php"]');
        if (mobileDriveLink2) mobileDriveLink2.style.display = 'none';

        // Replace "Get a Quote" button with "Dashboard"
        var mobileQuoteBtn2 = mobileMenu.querySelector('a[href="quote.php"].btn, a[href="quote"].btn');
        if (mobileQuoteBtn2) {
          mobileQuoteBtn2.href = role === 'corporate_staff' ? 'staff-dashboard.php' : 'driver-dashboard.php';
          mobileQuoteBtn2.textContent = 'Dashboard';
        }

        // Add "Loadboard" link in mobile for drivers / owner-operators
        if ((role === 'driver' || role === 'owner_operator') && !mobileMenu.querySelector('a[href="loadboard.php"]')) {
          var mobileLoadboardLink = document.createElement('a');
          mobileLoadboardLink.className = 'nav-link';
          mobileLoadboardLink.href = 'loadboard.php';
          mobileLoadboardLink.textContent = 'Loadboard';
          var mobileActionsEl = mobileMenu.querySelector('.header-actions');
          if (mobileActionsEl) {
            mobileMenu.insertBefore(mobileLoadboardLink, mobileActionsEl);
          } else {
            mobileMenu.appendChild(mobileLoadboardLink);
          }
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
        var mobileCoBtn = mobileMenu.querySelector('a[href="quote.php"].btn, a[href="quote"].btn') ||
                          mobileMenu.querySelector('a[href="register.php"].btn, a[href="register"].btn');
        if (mobileCoBtn) {
          mobileCoBtn.href = mobileCoDashHref;
          mobileCoBtn.textContent = 'My Dashboard';
        }
      }

      // Add Messages & Documents for all logged-in users in mobile
      addAuthNavLinks(mobileMenu, true);
    }

    // Add Messages & Documents for all logged-in users in desktop nav
    if (navLinks) {
      addAuthNavLinks(navLinks, false);
    }

    // ── Notification bell ────────────────────────────────────────
    injectNotificationBadge(userId);

    // ── Inactivity auto-logout ────────────────────────────────────
    startInactivityTimer();
  }

  // Run after DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', updateNav);
  } else {
    updateNav();
  }
})();
