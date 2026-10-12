<?php
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

function appStartSession() {

    $host = strtolower($_SERVER['SERVER_NAME'] ?? '');
    $host = preg_replace('/:\d+$/', '', $host);

    $isLocal = in_array($host, ['localhost', '127.0.0.1', '[::1]']) || str_ends_with($host, '.localhost');
    $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

    // Forzar HTTPS en producción
    if (!$isHttps && !$isLocal && $host !== '') {
        header('Location: https://' . $host . ($_SERVER['REQUEST_URI'] ?? '/'), true, 301);
        exit;
    }

    // Headers de seguridad
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()');

    $csp = "default-src 'self'; "
         . "base-uri 'self'; "
         . "object-src 'none'; "
         . "frame-ancestors 'self'; "
         . "form-action 'self'; "
         . "img-src 'self' data: blob: https:; "
         . "font-src 'self' data: https://fonts.gstatic.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net; "
         . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://unpkg.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; "
         . "script-src 'self' 'unsafe-inline' https://unpkg.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; "
         . "connect-src 'self' https:; "
         . "upgrade-insecure-requests";

    header("Content-Security-Policy: $csp");

    if ($isHttps) {
        header('Strict-Transport-Security: max-age=31536000');
    }

    // Iniciar sesión
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start([
            'cookie_lifetime' => 0,
            'cookie_path'     => '/Appjoteca',
            'cookie_httponly' => true,
            'cookie_secure'   => true,
            'cookie_samesite' => 'Lax',
        ]);
    }
}

appStartSession();