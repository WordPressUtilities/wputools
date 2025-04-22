<?php

// Load WordPress.
if (function_exists('set_time_limit')) {
    set_time_limit(0);
}
ini_set('memory_limit', '512M');

/* Disable maintenance */
define('WPUMAINTENANCE_DISABLED', true);

/* ----------------------------------------------------------
  Settings
---------------------------------------------------------- */
define('WP_USE_THEMES', false);
require 'wp-load.php';
wp();

$_hasImport = false;

/* ----------------------------------------------------------
  Images
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

switch ($_posttype) {
case 'comments':
    $_posttype = 'comment';
    break;
case 'users':
    $_posttype = 'user';
    break;
case 'pages':
    $_posttype = 'page';
    break;
case 'posts':
    $_posttype = 'post';
    break;
default:

}

/* ----------------------------------------------------------
  Attachments
---------------------------------------------------------- */

if ($_posttype == 'all' || $_posttype == 'attachments' || $_posttype == 'attachment') {

    $orientations = array('landscape', 'portrait', 'squarish');
    $nb_orientations = count($orientations);
    $images_list = array();
    if ($_GET['unsplash_api_key']) {
        for ($i = 0; $i < $_samples_nb; $i++) {
            $orientation = $orientations[$i % $nb_orientations];
            $image = json_decode(wp_remote_retrieve_body(wp_remote_get('https://api.unsplash.com/photos/random?orientation=' . $orientation . '&client_id=' . $_GET['unsplash_api_key'])), true);
            if (isset($image['urls'])) {
                $images_list[] = $image['urls']['regular'];
            }
        }
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    echo "Samples for attachments\n";
    foreach ($images_list as $url) {
        $tmp_file = download_url($url);
        $file_array = array(
            'name' => substr(sanitize_title(basename($url)), 0, 50) . '.jpg',
            'tmp_name' => $tmp_file
        );
        if (is_wp_error($tmp_file)) {
            @unlink($file_array['tmp_name']);
        } else {
            $id = media_handle_sideload($file_array, 0);
            if (is_numeric($id)) {
                $_hasImport = true;
                echo "Success : #" . $id . "\n";
            }
        }
        @flush();
        @ob_flush();
    }
    if (!$images_list) {
        echo "You need an Unsplash API KEY to import images. Please add a _WPUTOOLS_UNSPLASH_API_KEY in your wputools-local.sh file.\n";
    }
}

/* Random contents */
$raw_contents = array(
    'Sometimes when you innovate, you make <strong>mistakes</strong>. It is best to admit them <em>quickly</em>, and get on with improving your other innovations.',
    'We don’t get a chance to do that many things, and every one should be really <em>excellent</em>. Because this is our life. Life is brief, and then you die, you know? And we’ve all chosen to do this with our lives. So it better be damn good. It better be worth it.',
    'I think if you do <strong>something</strong> and it turns out pretty good, then you <em>should</em> go do something else wonderful, not dwell on it for too long. Just figure out what’s next.',
    'HTML test <strong>bold</strong> <em>italic</em> <a href="#">link</a> <img src="https://placehold.co/600x400" alt="image" /><br /> <ul><li>list</li></ul> <hr /> <blockquote>quote</blockquote> <pre>code</pre> <table><tr><td>table</td></tr></table>'
);
$nb_raw_contents = count($raw_contents);

$raw_titles = array(
    'A really long title that will test limits, maybe broke your content, and make you cry if your theme is not ready for it, but it will be a good test',
    'A short title',
    '10 Inspirational Graphics About coding',
    '10 Things Steve Jobs Can Teach Us About coding',
    'Why It’s Easier to Succeed With coding Than You Might Think',
    'What I Wish I Knew a Year Ago About coding a really long title, but it will be a good test to see if your theme is ready for it',
    'What’s the Current Job Market for coding Professionals Like?',
    '7 Things About coding Your Boss Wants to Know',
    'The coding Case Study You’ll Never Forget',
    '3 Reasons Your coding Is Broken (And How to Fix It)',
    'The Most Common Complaints About coding, and Why They’re Bunk'
);
$nb_raw_titles = count($raw_titles);

/* ----------------------------------------------------------
  Post types
---------------------------------------------------------- */

$post_types = get_post_types(array(
    'public' => true
), 'objects');

foreach ($post_types as $pt => $post_type) {
    $post_id = false;
    if (in_array($pt, array('attachment'))) {
        continue;
    }
    if ($_posttype != 'all' && $_posttype != $pt) {
        continue;
    }

    $is_multilingual = function_exists('pll_get_post_translations');
    $languages = array();
    if ($is_multilingual) {
        $languages_raw = get_terms('term_language', ['hide_empty' => false]);
        foreach ($languages_raw as $lang) {
            $languages[str_replace('pll_', '', $lang->slug)] = $lang->name;
        }
    }

    /* Create post */
    $random_images = get_posts(array(
        'post_type' => 'attachment',
        'post_mime_type' => 'image/jpeg',
        'fields' => 'ids',
        'post_status' => 'inherit',
        'posts_per_page' => 20,
        'orderby' => 'rand'
    ));

    $label = $post_type->labels->singular_name;
    echo "Samples for post type : " . $label . "\n";
    $taxonomies = get_object_taxonomies($pt);
    for ($i = 1; $i <= $_samples_nb; $i++) {
        /* Add images */
        $post_title = $raw_titles[mt_rand(0, $nb_raw_titles - 1)] . ' ' . $label . ' #' . $i;

        $post_infos = array(
            'post_title' => $post_title,
            'post_content' => $raw_contents[mt_rand(0, $nb_raw_contents - 1)],
            'post_type' => $pt,
            'post_status' => 'publish',
            'post_author' => 1
        );

        $post_id = wp_insert_post($post_infos);
        $_hasImport = true;

        /* Taxonomies */
        $thumbnail_id = false;
        if ($post_id && !empty($random_images)) {
            $thumbnail_id = $random_images[array_rand($random_images)];
            set_post_thumbnail($post_id, $thumbnail_id);
        }

        if ($is_multilingual && $languages) {
            $first_code = array_keys($languages)[0];
            $translations = array($post_id);
            foreach ($languages as $lang_code => $lang_name) {
                if ($lang_code === $first_code) {
                    continue;
                }
                $post_infos['post_title'] = $post_title . ' (' . $lang_name . ')';
                $translated_post_id = wp_insert_post($post_infos);
                if ($translated_post_id) {
                    pll_set_post_language($translated_post_id, $lang_code);
                    pll_save_post_translations(array_merge(pll_get_post_translations($post_id), [$lang_code => $translated_post_id]));
                }
                if($thumbnail_id){
                    set_post_thumbnail($translated_post_id, $thumbnail_id);
                }
            }
        }

        foreach ($taxonomies as $tax_name) {
            if (in_array($tax_name, array('post_format', 'post_translations', 'language'))) {
                continue;
            }
            $nb_tax = mt_rand(2, min($_samples_nb, 10));
            $nb_start = mt_rand(1, $nb_tax);
            for ($y = $nb_start; $y <= $nb_tax; $y++) {
                wp_set_object_terms($post_id, $tax_name . ' ' . $y, $tax_name, true);
            }
        }
        echo "Success : #" . $post_id . "\n";
    }

    if ($_posttype == 'post' && $post_id) {
        $sticky_posts = get_option('sticky_posts');
        if (!is_array($sticky_posts)) {
            $sticky_posts = array();
        }
        $sticky_posts[] = $post_id;
        update_option('sticky_posts', $sticky_posts);
    }
}

/* ----------------------------------------------------------
  Users
---------------------------------------------------------- */

$users_values = array(
    'first_name' => array(
        'Sheldon',
        'Donald',
        'Steve',
        'Tim',
        'Bill',
        'Anne',
        'Maya',
        'Jane',
        'Rosa',
        'Marie'
    ),
    'last_name' => array(
        'Cooper',
        'Duck',
        'Jobs',
        'Cook',
        'Gates',
        'Frank',
        'Angelou',
        'Austen',
        'Parks',
        'Curie'
    )
);

if ($_posttype == 'user') {
    if (!function_exists('get_editable_roles')) {
        require_once ABSPATH . '/wp-admin/includes/user.php';
    }
    $editable_roles = array_keys(get_editable_roles());
    echo "Samples for users\n";
    for ($i = 1; $i <= $_samples_nb; $i++) {
        $first_name = $users_values['first_name'][array_rand($users_values['first_name'])];
        $last_name = $users_values['last_name'][array_rand($users_values['last_name'])];
        $user_id = strtolower(sanitize_user($first_name . '_' . $last_name)) . '_' . uniqid();
        $user_id = wp_insert_user(array(
            'first_name' => $first_name,
            'last_name' => $last_name,
            'user_login' => $user_id,
            'user_email' => $user_id . '@yopmail.com',
            'user_pass' => 'password',
            'role' => $editable_roles[array_rand($editable_roles, 1)]
        ));
        $_hasImport = true;
        echo "Success : #" . $i . " - ID : " . $user_id . "\n";
    }
}

/* ----------------------------------------------------------
  Comments
---------------------------------------------------------- */

if ($_posttype == 'comment') {
    $post_id = isset($_GET['sample_extra']) && is_numeric($_GET['sample_extra']) ? $_GET['sample_extra'] : false;
    if (!$post_id) {
        echo "You need to provide a post ID to create sample comments. \n";
        echo "Example: wputools sample comments 5 12345\n";
        exit;
    }

    echo "Samples for comments\n";
    $comment_id = 0;
    for ($i = 1; $i <= $_samples_nb; $i++) {
        $first_name = $users_values['first_name'][array_rand($users_values['first_name'])];
        $last_name = $users_values['last_name'][array_rand($users_values['last_name'])];
        $user_id = strtolower(sanitize_user($first_name . '_' . $last_name)) . '_' . uniqid();
        $comment_id = wp_insert_comment(array(
            'comment_author' => $first_name . ' ' . $last_name,
            'comment_parent' => (rand(0, 1) == 1) ? $comment_id : 0,
            'comment_post_ID' => $post_id,
            'comment_content' => $raw_contents[mt_rand(0, $nb_raw_contents - 1)]
        ));
        if ($comment_id && function_exists('wpu_comments_rating__get_rating')) {
            update_comment_meta($comment_id, 'wpu_comment_rating', rand(1, 5));
        }
        $_hasImport = true;
        echo "Success : #" . $i . "\n";
    }
}

if (!$_hasImport) {
    echo "Nothing was imported\n";
}
