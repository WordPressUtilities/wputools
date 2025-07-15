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

if (empty($_GET) || !isset($_GET['s'])) {
    http_response_code(400);
    die('Bad Request: Missing search parameters.');
}

global $wpdb;
$search = sanitize_text_field($_GET['s']);
$search_q = "'%" . $wpdb->esc_like($search) . "%'";
$post_results = array();

/* ----------------------------------------------------------
  Search in post
---------------------------------------------------------- */

$results = $wpdb->get_results(
    "SELECT ID, post_title, post_content, post_type
    FROM {$wpdb->posts}
    WHERE (post_title LIKE $search_q OR post_content LIKE $search_q)
    AND post_status = 'publish'"
);

if (!empty($results)) {
    foreach ($results as $post) {
        if (!isset($post_results[$post->ID])) {
            $post_results[$post->ID] = array();
        }
        if (stripos($post->post_content, $search) !== false) {
            $post_results[$post->ID]['post_content'] = true;
        }
        if (stripos($post->post_title, $search) !== false) {
            $post_results[$post->ID]['post_title'] = true;
        }
    }
}

/* ----------------------------------------------------------
  Search in post meta
---------------------------------------------------------- */

$meta_results = $wpdb->get_results(
    "SELECT post_id, meta_key, meta_value
    FROM {$wpdb->postmeta}
    WHERE meta_value LIKE $search_q
    AND post_id IN (
        SELECT ID FROM {$wpdb->posts}
        WHERE post_status = 'publish'
    )",
);

if (!empty($meta_results)) {
    foreach ($meta_results as $meta) {
        if (!isset($post_results[$meta->post_id])) {
            $post_results[$meta->post_id] = array();
        }
        if (stripos($meta->meta_value, $search) !== false) {
            $post_results[$meta->post_id]['post_meta'] = true;
        }
    }
}

/* ----------------------------------------------------------
  Display results
---------------------------------------------------------- */

echo '# Found ' . count($post_results) . " result(s)\n";

foreach ($post_results as $post_id => $result) {
    $result_string = array();
    if (isset($result['post_title'])) {
        $result_string[] = "Title";
    }
    if (isset($result['post_content'])) {
        $result_string[] = "Content";
    }
    if (isset($result['post_meta'])) {
        $result_string[] = "Meta";
    }
    echo "\n";
    echo get_the_title($post_id) . " (Post ID: {$post_id})\n";
    echo "-> Found in: " . implode(', ', $result_string);
    echo "\n";
    echo add_query_arg(array(
        'post' => $post_id,
        'action' => 'edit'
    ), admin_url('post.php'));
    echo "\n";
}
