<?php

// Load WordPress.
set_time_limit(0);
ini_set('memory_limit', '512M');

/** Loads the WordPress Environment and Template */
define('WP_USE_THEMES', false);
require 'wp-load.php';
wp();

// WP Rocket
if (function_exists('rocket_clean_domain')) {
    echo '# Clearing WP Rocket cache'."\n";
    rocket_clean_domain();
}

// W3TC
if (function_exists('w3tc_flush_all')) {
    echo '# Clearing W3TC cache'."\n";
    w3tc_flush_all();
}

// Opcache
if (function_exists('opcache_reset')) {
    echo '# Clearing opcache cache'."\n";
    opcache_reset();
}

// Object Cache
if (function_exists('wp_cache_flush')) {
    echo '# Clearing object cache'."\n";
    wp_cache_flush();
}
