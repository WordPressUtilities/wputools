<?php

// File expiration
if (filemtime(__FILE__) < time() - 60) {
    http_response_code(410);
    die('This file has expired.');
}

// Load WordPress.
if (function_exists('set_time_limit')) {
    set_time_limit(0);
}
ini_set('memory_limit', '512M');

/* Disable maintenance */
define('WPUMAINTENANCE_DISABLED', true);

/* Delete menu if it exists */
define('WP_USE_THEMES', false);
require 'wp-load.php';
wp();

$links = array(site_url(), home_url());

/* ----------------------------------------------------------
  Extract all links from menus
---------------------------------------------------------- */

$site_url = site_url();
$locations = get_nav_menu_locations();
foreach ($locations as $location => $menu_id) {
    $menu_items = wp_get_nav_menu_items($menu_id);
    foreach ($menu_items as $item) {
        /* Exclude non urls */
        if (!filter_var($item->url, FILTER_VALIDATE_URL)) {
            continue;
        }
        /* Exclude site */
        if ($item->url == $site_url || $item->url == $site_url . '/') {
            continue;
        }
        /* Should contain site url */
        if (strpos($item->url, $site_url) === false) {
            continue;
        }
        $links[] = $item->url;
    }
}

/* ----------------------------------------------------------
  Extract 2 items from every post type
---------------------------------------------------------- */

$post_types = get_post_types(array(
    'public' => true
));
foreach ($post_types as $pt) {
    if ($pt == 'attachment') {
        continue;
    }
    $posts = get_posts(array(
        'post_type' => $pt,
        'posts_per_page' => 2
    ));
    foreach ($posts as $post) {
        $link = get_permalink($post);
        if ($link) {
            $links[] = $link;
        }
    }
    if (get_post_type_archive_link($pt)) {
        $links[] = get_post_type_archive_link($pt);
    }
}

/* ----------------------------------------------------------
  Extract 2 terms from every taxonomy
---------------------------------------------------------- */

$tax = get_taxonomies(array(
    'public' => true
));
foreach ($tax as $tax) {
    if ($tax == 'post_format') {
        continue;
    }
    $terms = get_terms(array(
        'taxonomy' => $tax,
        'hide_empty' => false,
        'number' => 2
    ));
    foreach ($terms as $term) {
        $link = get_term_link($term);
        if ($link) {
            $links[] = $link;
        }
    }
}

/* ----------------------------------------------------------
  Sort items and generate file
---------------------------------------------------------- */

$links = array_unique($links);

/* URLs file : warming cache or checking pages */
$wputools_urls_file_content =
    site_url() . '/robots.txt' . "\n" .
    site_url() . '/feed' . "\n" .
    site_url() . '/wp-sitemap.xml' . "\n";
foreach ($links as $link) {
    $wputools_urls_file_content .= $link . "\n";
}
file_put_contents($_GET['file'], $wputools_urls_file_content);

/* Test file : used to compare two instances of the same website */
$wputools_test_file_content = '';
foreach ($links as $link) {
    $link_prod = str_replace(site_url(), 'httpreplacebyproddomain', $link);
    $wputools_test_file_content .= $link . ';' . $link_prod . "\n";
}
$test_file = dirname($_GET['file']) . '/' . 'test-' . basename($_GET['file']);
file_put_contents($test_file, $wputools_test_file_content);
