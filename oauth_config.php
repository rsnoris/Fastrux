<?php
/**
 * Fastrux — OAuth 2.0 Provider Configuration
 *
 * Set credentials via environment variables (recommended for production)
 * or supply them through your hosting environment.
 *
 * Required environment variables:
 *   GOOGLE_CLIENT_ID       — Google OAuth client ID
 *   GOOGLE_CLIENT_SECRET   — Google OAuth client secret
 *   LINKEDIN_CLIENT_ID     — LinkedIn OAuth client ID
 *   LINKEDIN_CLIENT_SECRET — LinkedIn OAuth client secret
 *
 * Optional overrides (auto-detected if not set):
 *   GOOGLE_REDIRECT_URI    — Full callback URL for Google
 *   LINKEDIN_REDIRECT_URI  — Full callback URL for LinkedIn
 */

/**
 * Return the OAuth configuration array for the given provider.
 */
function getOAuthConfig(string $provider): array
{
    $isHttps  = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
             || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    $scheme   = $isHttps ? 'https' : 'http';
    $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $dir      = rtrim(dirname($_SERVER['PHP_SELF'] ?? ''), '/\\');
    $baseUri  = "{$scheme}://{$host}{$dir}";

    $configs = [
        'google' => [
            'client_id'     => (string)(getenv('GOOGLE_CLIENT_ID')     ?: ''),
            'client_secret' => (string)(getenv('GOOGLE_CLIENT_SECRET') ?: ''),
            'redirect_uri'  => (string)(getenv('GOOGLE_REDIRECT_URI')
                                ?: "{$baseUri}/oauth_handler.php?provider=google"),
            'scope'         => 'openid email profile',
            'auth_url'      => 'https://accounts.google.com/o/oauth2/v2/auth',
            'token_url'     => 'https://oauth2.googleapis.com/token',
            'userinfo_url'  => 'https://www.googleapis.com/oauth2/v3/userinfo',
        ],
        'linkedin' => [
            'client_id'     => (string)(getenv('LINKEDIN_CLIENT_ID')     ?: ''),
            'client_secret' => (string)(getenv('LINKEDIN_CLIENT_SECRET') ?: ''),
            'redirect_uri'  => (string)(getenv('LINKEDIN_REDIRECT_URI')
                                ?: "{$baseUri}/oauth_handler.php?provider=linkedin"),
            'scope'         => 'openid email profile',
            'auth_url'      => 'https://www.linkedin.com/oauth/v2/authorization',
            'token_url'     => 'https://www.linkedin.com/oauth/v2/accessToken',
            'userinfo_url'  => 'https://api.linkedin.com/v2/userinfo',
        ],
    ];

    return $configs[$provider] ?? [];
}
