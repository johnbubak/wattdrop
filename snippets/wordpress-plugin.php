<?php
/**
 * Plugin Name: WattDrop (Eco Favicon)
 * Description: Stops 404 log spam and saves massive server resources (CPU/DB) by intercepting missing icon requests before WordPress loads its core.
 * Version: 1.0.0
 * Author: Eco-Dev Community
 * License: MIT
 */

// Hook as early as possible into the WordPress load cycle
add_action('init', 'wattdrop_intercept_icon_requests');
function wattdrop_intercept_icon_requests() {
    // 1. Which URL was requested?
    $request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $icon_paths = [
        '/favicon.ico',
        '/favicon-16x16.png',
        '/favicon-32x32.png',
        '/apple-touch-icon.png',
        '/apple-touch-icon-precomposed.png',
    ];

    // 2. Is it a standard icon request?
    if (in_array($request_uri, $icon_paths)) {
        // 3. Safety check: maybe a real file exists on the server?
        if (file_exists(ABSPATH . ltrim($request_uri, '/'))) {
            return; // Real file wins — we don't interfere
        }
        // 4. No file? Serve the RAM-based SVG immediately
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><rect width="100" height="100" rx="22" fill="#10b981"/><text x="50%" y="55%" font-size="46" text-anchor="middle" fill="#ffffff" font-family="system-ui, monospace" font-weight="bold">WD</text></svg>';
        header('Content-Type: image/svg+xml');
        echo $svg;
        // 5. THE MOST IMPORTANT COMMAND: exit!
        // Stops WordPress immediately. No DB queries, no theme loading, no 404.
        exit;
    }
}