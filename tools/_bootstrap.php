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

/* Loads the WordPress Environment and Template */
define('WP_USE_THEMES', false);
require 'wp-load.php';
wp();
