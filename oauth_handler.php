<?php
/**
 * Fastrux — OAuth 2.0 Handler
 *
 * Handles social sign-in / sign-up via Google and LinkedIn.
 *
 * Routes:
 *   GET ?provider=google&action=redirect[&origin=login|register]
 *       → Start OAuth flow: generate state, redirect to provider
 *
 *   GET ?provider=google&code=...&state=...
 *       → OAuth callback: validate state, exchange code, find/create user,
 *         render success page that sets localStorage and redirects to dashboard
 *
 *   GET ?provider=google&error=...
 *       → Provider returned an error; redirect to oauth-error.php
 */

session_start();

define('OAUTH_DATA_DIR', __DIR__ . '/data/');

require_once __DIR__ . '/audit_helper.php';
require_once __DIR__ . '/oauth_config.php';

// ── Validate provider ──────────────────────────────────────
$provider = $_GET['provider'] ?? '';
if (!in_array($provider, ['google', 'linkedin'], true)) {
    oauthError('Invalid or missing OAuth provider.');
}

$config = getOAuthConfig($provider);

if (empty($config['client_id']) || empty($config['client_secret'])) {
    oauthError(
        ucfirst($provider) . ' sign-in is not yet configured. '
        . 'Please sign in with email and password or contact the site administrator.'
    );
}

// ── Route ──────────────────────────────────────────────────
if (isset($_GET['action']) && $_GET['action'] === 'redirect') {
    startOAuthFlow($provider, $config);
} elseif (isset($_GET['code'])) {
    handleOAuthCallback($provider, $config);
} elseif (isset($_GET['error'])) {
    $errDesc = htmlspecialchars($_GET['error_description'] ?? $_GET['error'], ENT_QUOTES, 'UTF-8');
    oauthError("The authorization was denied: {$errDesc}. Please try again.");
} else {
    oauthError('Invalid request. Please return to the login page and try again.');
}

// ════════════════════════════════════════════════════════════
//  FUNCTIONS
// ════════════════════════════════════════════════════════════

/**
 * Step 1 — Redirect the user to the provider's authorization page.
 */
function startOAuthFlow(string $provider, array $config): void
{
    $state = bin2hex(random_bytes(16));

    $_SESSION['oauth_state']    = $state;
    $_SESSION['oauth_provider'] = $provider;
    $_SESSION['oauth_origin']   = in_array($_GET['origin'] ?? '', ['login', 'register'], true)
                                  ? $_GET['origin'] : 'login';

    $params = [
        'client_id'     => $config['client_id'],
        'redirect_uri'  => $config['redirect_uri'],
        'response_type' => 'code',
        'scope'         => $config['scope'],
        'state'         => $state,
    ];

    // Google-specific: request fresh consent each time for reliability
    if ($provider === 'google') {
        $params['access_type'] = 'online';
    }

    header('Location: ' . $config['auth_url'] . '?' . http_build_query($params));
    exit;
}

/**
 * Step 2 — Handle the provider callback (exchange code, find/create user).
 */
function handleOAuthCallback(string $provider, array $config): void
{
    // CSRF validation
    $returnedState = $_GET['state'] ?? '';
    $expectedState = $_SESSION['oauth_state'] ?? '';

    if (!$returnedState || !hash_equals($expectedState, $returnedState)) {
        oauthError('Security check failed (invalid state). Please return to the login page and try again.');
    }

    unset($_SESSION['oauth_state']);

    $code = $_GET['code'] ?? '';
    if (!$code) {
        oauthError('Authorization code missing. Please try again.');
    }

    // Exchange authorization code for access token
    $token = exchangeCodeForToken($code, $config);
    if (!$token || empty($token['access_token'])) {
        $tokenError = $token['error_description'] ?? ($token['error'] ?? 'unknown error');
        oauthError("Could not obtain an access token ({$tokenError}). Please try again.");
    }

    // Fetch user profile from provider
    $profile = fetchUserProfile($provider, $token['access_token'], $config);
    if (!$profile || empty($profile['email'])) {
        oauthError(
            'Could not retrieve your email address from ' . ucfirst($provider) . '. '
            . 'Please make sure your email is visible on your account and try again.'
        );
    }

    // Find existing user or create a new one
    $user = findOrCreateUser($provider, $profile);
    if (!$user) {
        oauthError('Failed to create your account. Please try again or sign up with email and password.');
    }

    // Block accounts that are pending admin approval
    if (($user['status'] ?? 'active') === 'pending_approval') {
        oauthError('Your account is pending admin approval. You will be notified once it is activated.');
    }

    if (($user['status'] ?? 'active') === 'rejected') {
        oauthError('Your account application was not approved. Please contact support for more information.');
    }

    renderSuccessPage($user);
}

/**
 * Exchange the authorization code for an access token.
 */
function exchangeCodeForToken(string $code, array $config): ?array
{
    if (!function_exists('curl_init')) {
        oauthError('cURL is required for OAuth but is not available on this server.');
    }

    $params = http_build_query([
        'client_id'     => $config['client_id'],
        'client_secret' => $config['client_secret'],
        'code'          => $code,
        'redirect_uri'  => $config['redirect_uri'],
        'grant_type'    => 'authorization_code',
    ]);

    $ch = curl_init($config['token_url']);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $params,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: application/json',
        ],
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);

    $response = curl_exec($ch);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($curlErr || !$response) {
        return null;
    }

    return json_decode($response, true);
}

/**
 * Fetch the user's profile from the provider's userinfo endpoint.
 */
function fetchUserProfile(string $provider, string $accessToken, array $config): ?array
{
    $ch = curl_init($config['userinfo_url']);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $accessToken,
            'Accept: application/json',
        ],
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);

    $response = curl_exec($ch);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($curlErr || !$response) {
        return null;
    }

    $data = json_decode($response, true);

    return normaliseProfile($provider, $data ?? []);
}

/**
 * Normalise provider-specific profile payloads into a common structure.
 */
function normaliseProfile(string $provider, array $data): array
{
    $profile = [
        'provider'    => $provider,
        'provider_id' => '',
        'email'       => '',
        'first_name'  => '',
        'last_name'   => '',
        'avatar'      => '',
    ];

    if ($provider === 'google') {
        // Google OIDC userinfo (https://www.googleapis.com/oauth2/v3/userinfo)
        $profile['email']       = $data['email']       ?? '';
        $profile['first_name']  = $data['given_name']  ?? '';
        $profile['last_name']   = $data['family_name'] ?? '';
        $profile['avatar']      = $data['picture']     ?? '';
        $profile['provider_id'] = $data['sub']         ?? '';
    } elseif ($provider === 'linkedin') {
        // LinkedIn OIDC userinfo (https://api.linkedin.com/v2/userinfo)
        $profile['email']       = $data['email']       ?? '';
        $profile['first_name']  = $data['given_name']  ?? '';
        $profile['last_name']   = $data['family_name'] ?? '';
        $profile['avatar']      = $data['picture']     ?? '';
        $profile['provider_id'] = $data['sub']         ?? '';
    }

    return $profile;
}

/**
 * Look up an existing user by email, or create a new shipper account.
 */
function findOrCreateUser(string $provider, array $profile): ?array
{
    $usersPath = OAUTH_DATA_DIR . 'registered_users.json';
    $users     = [];

    if (file_exists($usersPath)) {
        $users = json_decode(file_get_contents($usersPath), true) ?? [];
    }

    $email = strtolower(trim($profile['email']));

    // Look for an existing account with this email
    foreach ($users as &$u) {
        if (isset($u['email']) && strtolower($u['email']) === $email) {
            // Link the OAuth provider ID if not already stored for this provider.
            // auth_provider records the first OAuth provider ever linked; existing
            // email-only accounts keep auth_provider unset until an OAuth provider
            // is linked for the first time.
            $oauthKey = "oauth_{$provider}_id";
            if (empty($u[$oauthKey]) && !empty($profile['provider_id'])) {
                $u[$oauthKey]       = $profile['provider_id'];
                if (empty($u['auth_provider'])) {
                    $u['auth_provider'] = $provider;
                }
                file_put_contents(
                    $usersPath,
                    json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                );
            }
            auditLog(
                'user.oauth_login',
                $u['id'] ?? '',
                'user',
                $u['id'] ?? '',
                "OAuth sign-in ({$provider}): {$email} (role: " . ($u['role'] ?? 'shipper') . ')'
            );
            return $u;
        }
    }
    unset($u);

    // No matching account — create a new one with default shipper role
    $id        = 'USR-' . strtoupper(bin2hex(random_bytes(4)));
    $timestamp = date('Y-m-d H:i:s');
    $firstName = !empty($profile['first_name'])
                 ? $profile['first_name']
                 : ucfirst(explode('@', $email)[0]);
    $lastName  = $profile['last_name'] ?? '';

    $entry = [
        'id'                        => $id,
        'timestamp'                 => $timestamp,
        'first_name'                => $firstName,
        'last_name'                 => $lastName,
        'email'                     => $profile['email'],
        'company'                   => '',
        'role'                      => 'shipper',
        'status'                    => 'active',
        'password_hash'             => '',
        'auth_provider'             => $provider,
        "oauth_{$provider}_id"      => $profile['provider_id'] ?? '',
    ];

    $users[] = $entry;

    if (file_put_contents(
            $usersPath,
            json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        ) === false) {
        return null;
    }

    auditLog(
        'user.oauth_registered',
        $id,
        'user',
        $id,
        "New account via OAuth ({$provider}): {$profile['email']} (role: shipper)"
    );

    return $entry;
}

/**
 * Render the post-OAuth success page.
 * Sets localStorage and redirects to the appropriate dashboard.
 */
function renderSuccessPage(array $user): void
{
    $dashboardMap = [
        'shipper'           => 'shipper-dashboard.php',
        'customer'          => 'shipper-dashboard.php',
        'driver'            => 'driver-dashboard.php',
        'owner_operator'    => 'driver-dashboard.php',
        'corporate_staff'   => 'staff-dashboard.php',
        'admin'             => 'admin-dashboard.php',
        'super_admin'       => 'admin-dashboard.php',
        'insurance_company' => 'insurance-dashboard.php',
        'trucking_company'  => 'trucking-dashboard.php',
        'gas_station'       => 'gas-station-dashboard.php',
        'hotel'             => 'hotel-dashboard.php',
    ];

    $role      = $user['role'] ?? 'shipper';
    $dashboard = $dashboardMap[$role] ?? 'index.php';

    $userJson = json_encode([
        'id'         => $user['id']         ?? '',
        'first_name' => $user['first_name'] ?? '',
        'last_name'  => $user['last_name']  ?? '',
        'email'      => $user['email']      ?? '',
        'role'       => $role,
    ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

    $name      = htmlspecialchars($user['first_name'] ?? 'there', ENT_QUOTES, 'UTF-8');
    $dashboard = htmlspecialchars($dashboard, ENT_QUOTES, 'UTF-8');

    // Output an inline HTML page; no header() calls after this point
    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Signing in… — Fastrux</title>
  <link rel="icon" href="favicon.svg" type="image/svg+xml" />
  <link rel="stylesheet" href="shared.css" />
  <style>
    body {
      display: flex; align-items: center; justify-content: center;
      min-height: 100vh;
      background: linear-gradient(160deg, var(--secondary) 0%, var(--background) 60%);
    }
    .signin-card {
      background: var(--card); border: 1px solid var(--border);
      border-radius: var(--radius-xl); padding: 48px;
      text-align: center; max-width: 380px; width: 100%;
      box-shadow: 0 16px 48px rgba(11,111,255,.08);
    }
    .check-circle {
      width: 72px; height: 72px; border-radius: 50%;
      background: linear-gradient(135deg, #22c55e, #16a34a);
      display: flex; align-items: center; justify-content: center;
      margin: 0 auto 24px; font-size: 36px; color: #fff;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
    .spinner {
      display: inline-block; width: 20px; height: 20px;
      border: 2.5px solid var(--border); border-top-color: var(--primary);
      border-radius: 50%; animation: spin .8s linear infinite;
      vertical-align: middle; margin-right: 8px;
    }
  </style>
</head>
<body>
  <div class="signin-card">
    <div class="check-circle">✓</div>
    <h2 style="font-size:22px;font-weight:700;margin-bottom:8px;color:var(--foreground);">
      Welcome, {$name}!
    </h2>
    <p style="color:var(--muted-foreground);margin-bottom:0;">
      <span class="spinner"></span>Redirecting to your dashboard…
    </p>
  </div>
  <script>
    try {
      localStorage.setItem('fx_user', JSON.stringify({$userJson}));
    } catch (e) {}
    setTimeout(function () {
      window.location.href = '{$dashboard}';
    }, 1000);
  </script>
</body>
</html>
HTML;
    exit;
}

/**
 * Redirect to the OAuth error page with a human-readable message.
 */
function oauthError(string $message): void
{
    $encoded = urlencode($message);
    header("Location: oauth-error.php?msg={$encoded}");
    exit;
}
