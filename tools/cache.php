<?php

// Load WordPress.
set_time_limit(0);
ini_set('memory_limit', '512M');

/* Disable maintenance */
define('WPUMAINTENANCE_DISABLED', true);

/** Loads the WordPress Environment and Template */
define('WP_USE_THEMES', false);
require 'wp-load.php';
wp();

$cache_type = isset($_GET['cache_type']) ? $_GET['cache_type'] : 'all';
$cache_arg = isset($_GET['cache_arg']) ? $_GET['cache_arg'] : '';

echo "# Cache type : " . htmlentities($cache_type) . "\n";

// Opcache
if (function_exists('opcache_reset') && ($cache_type == 'all' || $cache_type == 'opcache' || $cache_type == 'opcode')) {
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

// FVM
if (function_exists('fvm_purge_all') && ($cache_type == 'all' || $cache_type == 'fvm')) {
    echo '# Clearing FVM cache' . "\n";
    fvm_purge_all();
    fvm_purge_others();
}

// Object Cache
if (function_exists('wp_cache_flush') && ($cache_type == 'all' || $cache_type == 'object')) {
    echo '# Clearing object cache' . "\n";
    wp_cache_flush();
}

// Object Cache
if ($cache_type == 'all' || $cache_type == 'cloudflare') {
    if (function_exists('rocket_purge_cloudflare')) {
        echo '# Purging Cloudflare via WP Rocket' . "\n";
        rocket_purge_cloudflare();
    } elseif (function_exists('wpucloudflare_purge_everything')) {
        echo '# Purging Cloudflare via WPU Cloudflare' . "\n";
        wpucloudflare_purge_everything();
    }
}

// URL
$cached_url = false;
if (filter_var($cache_arg, FILTER_VALIDATE_URL) !== false) {
    $cached_url = $cache_arg;
}

if ($cache_type == 'url' && $cached_url) {
    echo '# Purging cache for URL "' . $cached_url . '"' . "\n";

    /* Purge W3TC page */
    if (function_exists('rocket_clean_files')) {
        echo '# - Purging cache for URL in WP Rocket' . "\n";
        rocket_clean_files(array($cached_url));
    }

    /* Purge WP Rocket page */
    if (function_exists('w3tc_flush_url')) {
        echo '# - Purging cache for URL in W3TC' . "\n";
        w3tc_flush_url($cached_url);
    }

    /* Purge cloudflare cache */
    if (function_exists('wpucloudflare_purge_urls')) {
        echo '# - Purging cache for URL in WPU Cloudflare' . "\n";
        wpucloudflare_purge_urls(array($cached_url));
    }
}
