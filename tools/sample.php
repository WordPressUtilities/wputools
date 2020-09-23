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
  Content
---------------------------------------------------------- */

$samples_nb = 5;
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
    $label = $post_type->labels->singular_name;
    echo "Samples for post type : " . $label . "\n";
    for ($i = 1; $i <= $samples_nb; $i++) {
        $post_id = wp_insert_post(array(
            'post_title' => $label . ' #' . $i,
            'post_content' => $raw_contents[mt_rand(0, $nb_raw_contents - 1)],
            'post_type' => $pt,
            'post_status' => 'publish',
            'post_author' => 1
        ));
        echo "Success : #" . $post_id . "\n";
    }
}

/* ----------------------------------------------------------
  Images
---------------------------------------------------------- */

$images = array(
    'https://source.unsplash.com/random/1280x720?t=' . time(),
    'https://source.unsplash.com/random/720x1280?t=' . time(),
    'https://source.unsplash.com/random/1280x1280?t=' . time()
);

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

echo "Samples for : attachments\n";
foreach ($images as $url) {
    $file_array = array(
        'name' => sanitize_title(basename($url)) . '.jpg',
        'tmp_name' => download_url($url)
    );
    if (is_wp_error($tmp)) {
        @unlink($file_array['tmp_name']);
    } else {
        $id = media_handle_sideload($file_array, 0);
        if (is_numeric($id)) {
            echo "Success : #" . $id . "\n";
        }
    }
}
