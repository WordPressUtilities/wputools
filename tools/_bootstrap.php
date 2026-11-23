<?php

/* File expiration */
if (filemtime(__FILE__) < time() - 60) {
    http_response_code(410);
    die('This file has expired.');
}

/* Load WordPress. */
if (function_exists('set_time_limit')) {
    set_time_limit(0);
}
ini_set('memory_limit', '512M');

/* Disable maintenance */
define('WPUMAINTENANCE_DISABLED', true);

/* CLI : target site URL as first argument (needed to load the right multisite blog) */
if (PHP_SAPI === 'cli' && !empty($argv[1])) {
    $wpu_cli_url = parse_url($argv[1]);
    if (!empty($wpu_cli_url['host'])) {
        $_SERVER['HTTP_HOST'] = $wpu_cli_url['host'] . (isset($wpu_cli_url['port']) ? ':' . $wpu_cli_url['port'] : '');
        $_SERVER['SERVER_NAME'] = $wpu_cli_url['host'];
        $_SERVER['REQUEST_URI'] = isset($wpu_cli_url['path']) ? $wpu_cli_url['path'] : '/';
        if (isset($wpu_cli_url['scheme']) && $wpu_cli_url['scheme'] === 'https') {
            $_SERVER['HTTPS'] = 'on';
        }
    }
}

/* Loads the WordPress Environment and Template */
define('WP_USE_THEMES', false);
require 'wp-load.php';
wp();
