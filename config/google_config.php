<?php
// config/google_oauth.php

// ⚠️ Prefer loading these from environment variables or a non-public config file
define('GOOGLE_CLIENT_ID', getenv('GOOGLE_CLIENT_ID') ?: 'YOUR_CLIENT_ID');
define('GOOGLE_CLIENT_SECRET', getenv('GOOGLE_CLIENT_SECRET') ?: 'YOUR_CLIENT_SECRET');
define('GOOGLE_REDIRECT_URI', getenv('GOOGLE_REDIRECT_URI') ?: 'http://localhost/fitfuel_sia/google_callback.php');

// Endpoints
define('GOOGLE_AUTH_URL', 'https://accounts.google.com/o/oauth2/v2/auth');
define('GOOGLE_TOKEN_URL', 'https://oauth2.googleapis.com/token');
// OIDC-compliant userinfo endpoint
define('GOOGLE_USER_INFO_URL', 'https://openidconnect.googleapis.com/v1/userinfo');

// Scopes
define('GOOGLE_SCOPES', 'openid email profile');

/**
 * Generate a cryptographically-random state and return the auth URL.
 * Also stores state in session for CSRF checking.
 */
function getGoogleAuthUrl(): string {
    if (session_status() === PHP_SESSION_NONE) session_start();

    $state = bin2hex(random_bytes(16));
    $_SESSION['google_oauth_state'] = $state;

    $params = [
        'client_id' => GOOGLE_CLIENT_ID,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'scope' => GOOGLE_SCOPES,
        'response_type' => 'code',
        'access_type' => 'offline',     // refresh token (web apps may get it only on first consent)
        'prompt' => 'consent',          // or 'select_account consent'
        'state' => $state,
    ];

    return GOOGLE_AUTH_URL . '?' . http_build_query($params);
}

/**
 * Exchange authorization code for tokens
 */
function getGoogleAccessToken(string $code): array {
    $data = [
        'client_id' => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'grant_type' => 'authorization_code',
        'code' => $code,
    ];

    $ch = curl_init(GOOGLE_TOKEN_URL);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($data),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        CURLOPT_TIMEOUT => 15,
    ]);

    $response = curl_exec($ch);
    if ($response === false) {
        $err = curl_error($ch);
        curl_close($ch);
        throw new Exception('cURL error exchanging code for token: ' . $err);
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $json = json_decode($response, true);
    if ($httpCode !== 200) {
        $msg = $json['error_description'] ?? $json['error'] ?? 'Unknown error';
        throw new Exception("Failed to get access token (HTTP $httpCode): $msg");
    }

    // Returns: access_token, expires_in, refresh_token (maybe), id_token, scope, token_type
    return $json;
}

/**
 * Get user info using the access token
 */
function getGoogleUserInfo(string $accessToken): array {
    $ch = curl_init(GOOGLE_USER_INFO_URL);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $accessToken
        ],
        CURLOPT_TIMEOUT => 15,
    ]);

    $response = curl_exec($ch);
    if ($response === false) {
        $err = curl_error($ch);
        curl_close($ch);
        throw new Exception('cURL error requesting userinfo: ' . $err);
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $json = json_decode($response, true);
    if ($httpCode !== 200) {
        $msg = $json['error_description'] ?? $json['error'] ?? 'Unknown error';
        throw new Exception("Failed to get user info (HTTP $httpCode): $msg");
    }

    // Typical fields: sub (user id), email, email_verified, name, given_name, family_name, picture
    return $json;
}
