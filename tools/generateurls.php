<?php

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

$links = array(site_url());

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
file_put_contents($_GET['file'], implode("\n", $links));
