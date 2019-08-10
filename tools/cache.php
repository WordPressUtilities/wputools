<?php

// Load WordPress.
require 'wp-load.php';

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
