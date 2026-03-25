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
    ['a[href="index.php"]', 'a[href="index"]', 'a[href="#services"]',
     'a[href="index.php#services"]', 'a[href="index#services"]',
     'a[href="contact.php"]', 'a[href="contact"]', 'a[href="#contact"]'].forEach(function (sel) {
      var el = container.querySelector(sel);
      if (el) el.style.display = 'none';
    });
  }

  // ── Add Maps link for logged-in users (replaces Contact) ─────
  function addMapsLink(container, beforeEl) {
    if (container.querySelector('a[href="maps.php"], a[href="maps"]')) return;
    var mapsLink = document.createElement('a');
    mapsLink.className = 'nav-link';
    mapsLink.href = 'maps.php';
    mapsLink.textContent = 'Maps';
    if (beforeEl) {
      container.insertBefore(mapsLink, beforeEl);
    } else {
      container.appendChild(mapsLink);
    }
  }

  // ── Inject dropdown CSS for the My Dashboard nav item ────────
  function injectDropdownStyles() {
    if (document.getElementById('nav-dash-dropdown-styles')) return;
    var style = document.createElement('style');
    style.id = 'nav-dash-dropdown-styles';
    style.textContent =
      '.nav-dash-dropdown{position:relative;display:inline-flex;align-items:center;}' +
      '.nav-dash-dropdown-toggle::after{content:" ▾";font-size:10px;margin-left:2px;}' +
      '.nav-dash-dropdown-menu{display:none;position:absolute;top:calc(100% + 6px);left:0;' +
      'background:var(--card,#fff);border:1px solid var(--border,#e5e7eb);' +
      'box-shadow:0 4px 16px rgba(0,0,0,0.10);min-width:180px;border-radius:10px;' +
      'z-index:9999;flex-direction:column;padding:4px 0;}' +
      '.nav-dash-dropdown:hover .nav-dash-dropdown-menu,' +
      '.nav-dash-dropdown:focus-within .nav-dash-dropdown-menu{display:flex;}' +
      '.nav-dash-dropdown-item{padding:9px 16px;text-decoration:none;' +
      'color:var(--foreground,#0b2545);font-size:14px;display:flex;align-items:center;' +
      'gap:8px;white-space:nowrap;}' +
      '.nav-dash-dropdown-item:hover{background:var(--muted,#f1f5f9);color:var(--primary,#0b6fff);}';
    document.head.appendChild(style);
  }

  // ── Create a dropdown wrapper for a dashboard nav link ────────
  function createDashDropdown(dashHref, dashLabel) {
    injectDropdownStyles();
    var wrapper = document.createElement('div');
    wrapper.className = 'nav-dash-dropdown';

    var toggle = document.createElement('a');
    toggle.className = 'nav-link nav-dash-dropdown-toggle';
    toggle.href = dashHref;
    toggle.textContent = dashLabel;

    var menu = document.createElement('div');
    menu.className = 'nav-dash-dropdown-menu';

    [{ href: dashHref, text: 'Dashboard Overview' },
     { href: 'messages.php', text: 'Messages' },
     { href: 'documents.php', text: 'Documents' }
    ].forEach(function (item) {
      var a = document.createElement('a');
      a.className = 'nav-dash-dropdown-item';
      a.href = item.href;
      a.textContent = item.text;
      menu.appendChild(a);
    });

    wrapper.appendChild(toggle);
    wrapper.appendChild(menu);
    return wrapper;
  }

  // ── Add Messages & Documents links in the mobile menu ────────
  function addMobileDashLinks(container) {
    var mobileActionsEl = container.querySelector('.header-actions');
    function insertLink(href, text) {
      if (container.querySelector('a[href="' + href + '"]')) return;
      var link = document.createElement('a');
      link.className = 'nav-link';
      link.href = href;
      link.textContent = text;
      if (mobileActionsEl) {
        container.insertBefore(link, mobileActionsEl);
      } else {
        container.appendChild(link);
      }
    }
    insertLink('messages.php', 'Messages');
    insertLink('documents.php', 'Documents');
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

      // Add Maps link for logged-in users who can access the Maps page
      // (not shown for company roles: insurance_company, trucking_company, gas_station, hotel)
      if (!isCompany(role)) {
        addMapsLink(navLinks);
      }

      if (isAdmin(role)) {
        // Hide "Drive with Us" for admins
        var driveLink = navLinks.querySelector('a[href="driver-onboarding.php"]');
        if (driveLink) driveLink.style.display = 'none';

        // Add "Admin Dashboard" dropdown if not already present
        if (!navLinks.querySelector('a[href="admin-dashboard.php"]')) {
          navLinks.appendChild(createDashDropdown('admin-dashboard.php', 'Admin Dashboard'));
        }
      } else if (isEmployee(role)) {
        // Hide "Drive with Us" for employees who are already registered
        var driveLink2 = navLinks.querySelector('a[href="driver-onboarding.php"]');
        if (driveLink2) driveLink2.style.display = 'none';

        // Add a "My Dashboard" dropdown if not already present
        var dashHref = role === 'corporate_staff' ? 'staff-dashboard.php' : 'driver-dashboard.php';
        if (!navLinks.querySelector('a[href="' + dashHref + '"]')) {
          navLinks.appendChild(createDashDropdown(dashHref, 'My Dashboard'));
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
        // Add "My Dashboard" dropdown for shippers if not already present
        if (!navLinks.querySelector('a[href="shipper-dashboard.php"]')) {
          navLinks.appendChild(createDashDropdown('shipper-dashboard.php', 'My Dashboard'));
        }
      } else if (isCompany(role)) {
        // Hide "Drive with Us" for company users
        var driveLinkCo = navLinks.querySelector('a[href="driver-onboarding.php"]');
        if (driveLinkCo) driveLinkCo.style.display = 'none';

        // Add "My Dashboard" dropdown for company accounts
        var coDashHref = companyDashHref(role);
        if (!navLinks.querySelector('a[href="' + coDashHref + '"]')) {
          navLinks.appendChild(createDashDropdown(coDashHref, 'My Dashboard'));
        }
      }
    }

    // ── Mobile menu ─────────────────────────────────────────────
    var mobileMenu = document.getElementById('mobileMenu');
    if (mobileMenu) {
      // Hide public-only links for logged-in users
      hidePublicNavLinks(mobileMenu);

      // Add Maps link for logged-in users who can access the Maps page
      // (not shown for company roles: insurance_company, trucking_company, gas_station, hotel)
      var mobileMenuActions = mobileMenu.querySelector('.header-actions');
      if (!isCompany(role)) {
        addMapsLink(mobileMenu, mobileMenuActions || null);
      }

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
      addMobileDashLinks(mobileMenu);
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
