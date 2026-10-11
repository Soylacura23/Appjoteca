<?php

if (!function_exists('appStartSession')) {
    function appStartSession(): void
    {
        if (PHP_SAPI !== 'cli') {
            $host = strtolower((string) ($_SERVER['SERVER_NAME'] ?? ''));
            if (!str_starts_with($host, '[')) {
                $host = preg_replace('/:\d+$/', '', $host);
            }
            $isLocal = in_array($host, ['localhost', '127.0.0.1', '[::1]'], true)
                || str_ends_with($host, '.localhost');
            $isHttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== '' && $_SERVER['HTTPS'] !== 'off';

            if (!$isHttps && !$isLocal) {
                $host = preg_replace('/[^a-z0-9.\-:\[\]]/i', '', $host);
                if ($host !== '') {
                    header('Location: https://' . $host . ($_SERVER['REQUEST_URI'] ?? '/'), true, 301);
                    exit;
                }
            }

            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: SAMEORIGIN');
            header('Referrer-Policy: strict-origin-when-cross-origin');
            header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()');
            header("Content-Security-Policy: default-src 'self'; base-uri 'self'; object-src 'none'; frame-ancestors 'self'; form-action 'self'; img-src 'self' data: blob: https:; font-src 'self' data: https://fonts.gstatic.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://unpkg.com https://cdn.jsdelivr.net; script-src 'self' 'unsafe-inline' https://unpkg.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; connect-src 'self' https:; upgrade-insecure-requests");

            if ($isHttps) {
                header('Strict-Transport-Security: max-age=31536000');
            }
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start([
                'cookie_lifetime' => 0,
                'cookie_path' => '/Appjoteca',
                'cookie_httponly' => true,
                'cookie_secure' => true,
                'cookie_samesite' => 'Lax',
            ]);
        }
    }
}

appStartSession();
