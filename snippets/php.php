<?php
// WattDrop — Vanilla PHP drop-in (router fallback)
// Place this block at the very top of your main index.php / router.

$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$icon_paths = ['/favicon.ico', '/favicon-16x16.png', '/favicon-32x32.png',
               '/apple-touch-icon.png', '/apple-touch-icon-precomposed.png'];

if (in_array($request_uri, $icon_paths)) {
    header('Content-Type: image/svg+xml');
    echo '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><rect width="100" height="100" rx="22" fill="#10b981"/><text x="50%" y="55%" font-size="46" text-anchor="middle" fill="#ffffff" font-family="system-ui, monospace" font-weight="bold">WD</text></svg>';
    exit; // Important: stop the script so no further code runs
}