<?php

// Load WordPress.
set_time_limit(0);
ini_set('memory_limit', '512M');

/** Loads the WordPress Environment and Template */
define('WP_USE_THEMES', false);
require 'wp-load.php';
wp();


// Clear cache.
if (function_exists('rocket_clean_domain')) {
    rocket_clean_domain();
}

if (function_exists('w3tc_flush_all')) {
    w3tc_flush_all();
}

if (function_exists('opcache_reset')) {
    opcache_reset();
}
