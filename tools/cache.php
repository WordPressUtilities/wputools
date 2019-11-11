<?php

// Load WordPress.
set_time_limit(0);
ini_set('memory_limit', '512M');

/** Loads the WordPress Environment and Template */
define('WP_USE_THEMES', false);
require 'wp-load.php';
wp();

$cache_type = isset($_GET['cache_type']) ? $_GET['cache_type'] : 'all';

echo "# Cache type : " . htmlentities($cache_type) . "\n";

// Opcache
if (function_exists('opcache_reset') && ($cache_type == 'all' || $cache_type == 'opcache')) {
    echo '# Clearing opcache cache' . "\n";
    opcache_reset();
}

// WP Rocket
if (function_exists('rocket_clean_domain') && ($cache_type == 'all' || $cache_type == 'wprocket')) {
    echo '# Clearing WP Rocket cache' . "\n";
    rocket_clean_domain();
}

// W3TC
if (function_exists('w3tc_flush_all') && ($cache_type == 'all' || $cache_type == 'w3tc')) {
    echo '# Clearing W3TC cache' . "\n";
    w3tc_flush_all();
}

// Object Cache
if (function_exists('wp_cache_flush') && ($cache_type == 'all' || $cache_type == 'object')) {
    echo '# Clearing object cache' . "\n";
    wp_cache_flush();
}
