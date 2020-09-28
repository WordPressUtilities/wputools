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

/* ----------------------------------------------------------
  Settings
---------------------------------------------------------- */

$_samples_nb = 5;
$_posttype = 'all';
if (isset($_GET['sample_num']) && is_numeric($_GET['sample_num'])) {
    $_samples_nb = (int) $_GET['sample_num'];
}
if (isset($_GET['sample_posttype']) && $_GET['sample_posttype']) {
    if (is_numeric($_GET['sample_posttype'])) {
        $_samples_nb = (int) $_GET['sample_posttype'];
    } else {
        $_posttype = esc_html($_GET['sample_posttype']);
    }
}

/* ----------------------------------------------------------
  Content
---------------------------------------------------------- */

$raw_contents = array(
    'Sometimes when you innovate, you make mistakes. It is best to admit them quickly, and get on with improving your other innovations.',
    'We don’t get a chance to do that many things, and every one should be really excellent. Because this is our life. Life is brief, and then you die, you know? And we’ve all chosen to do this with our lives. So it better be damn good. It better be worth it.',
    'I think if you do something and it turns out pretty good, then you should go do something else wonderful, not dwell on it for too long. Just figure out what’s next.'
);
$nb_raw_contents = count($raw_contents);

$post_types = get_post_types(array(
    'public' => true
), 'objects');

foreach ($post_types as $pt => $post_type) {
    if (in_array($pt, array('attachment'))) {
        continue;
    }
    if ($_posttype != 'all' && $_posttype != $pt) {
        continue;
    }

    $label = $post_type->labels->singular_name;
    echo "Samples for post type : " . $label . "\n";
    $taxonomies = get_object_taxonomies($pt);
    for ($i = 1; $i <= $_samples_nb; $i++) {
        $post_id = wp_insert_post(array(
            'post_title' => $label . ' #' . $i,
            'post_content' => $raw_contents[mt_rand(0, $nb_raw_contents - 1)],
            'post_type' => $pt,
            'post_status' => 'publish',
            'post_author' => 1
        ));
        foreach ($taxonomies as $tax_name) {
            if (in_array($tax_name, array('post_format'))) {
                continue;
            }
            $nb_tax = mt_rand(2, $_samples_nb);
            $nb_start = mt_rand(1, $nb_tax);
            for ($y = $nb_start; $y <= $nb_tax; $y++) {
                wp_set_object_terms($post_id, $tax_name . ' ' . $y, $tax_name, true);
            }
        }
        echo "Success : #" . $post_id . "\n";
    }
}

/* ----------------------------------------------------------
  Images
---------------------------------------------------------- */

$images = array(
    'http://source.unsplash.com/random/1280x720',
    'http://source.unsplash.com/random/720x1280',
    'http://source.unsplash.com/random/1280x1280'
);
$images_nb = count($images);
$images_list = array();
for ($i = 0; $i < $_samples_nb; $i++) {
    $images_list[] = $images[$i % $images_nb] . '?t=' . microtime(true);
}

if ($_posttype == 'all' || $_posttype == 'attachments' || $_posttype == 'attachment') {
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    echo "Samples for attachments\n";
    foreach ($images as $url) {
        $tmp_file = download_url($url);
        $file_array = array(
            'name' => sanitize_title(basename($url)) . '.jpg',
            'tmp_name' => $tmp_file
        );
        if (is_wp_error($tmp_file)) {
            @unlink($file_array['tmp_name']);
        } else {
            $id = media_handle_sideload($file_array, 0);
            if (is_numeric($id)) {
                echo "Success : #" . $id . "\n";
            }
        }
        @flush();
        @ob_flush();
    }
}
