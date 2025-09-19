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

/* ----------------------------------------------------------
  Build lang list
---------------------------------------------------------- */

$langs = array(
    'default' => array(
        'suffix' => ''
    )
);

$args = wp_parse_args($_GET, array(
    'force_add' => 0,
    'depth' => 2,
    'num_items' => 2,
    'menu_id' => 'all',
    'generate_type' => 'default'
));

/* Clean args */
$args['force_add'] = (int) $args['force_add'];
if ($args['menu_id'] != 'all') {
    $args['menu_id'] = (int) $args['menu_id'];
}

/* Polylang
-------------------------- */

$has_polylang = false;
$polylang_options = array();
if (function_exists('pll_the_languages')) {
    $polylang_options = get_option('polylang');
    if (!is_array($polylang_options)) {
        $polylang_options = array();
    }
    if (!isset($polylang_options['nav_menus']) || !is_array($polylang_options['nav_menus'])) {
        $polylang_options['nav_menus'] = array();
    }
    if (!isset($polylang_options['nav_menus'][get_stylesheet()])) {
        $polylang_options['nav_menus'][get_stylesheet()] = array();
    }
    $langs_pll = pll_the_languages(array(
        'echo' => 0,
        'raw' => 1
    ));
    if (!empty($langs_pll)) {
        $langs = array();
        foreach ($langs_pll as $lang) {
            $langs[$lang['slug']] = array(
                'suffix' => $lang['name']
            );
        }
    }
}

/* ----------------------------------------------------------
  Default item
---------------------------------------------------------- */

/* Random Item
-------------------------- */

function generatemenus_get_random_item_name($type = 'default') {
    if ($type == 'default') {
        return 'Random Item';
    }
    $item_names = array(
        'Random Item',
        'The world needs dreamers and the world needs doers. But above all, the world needs dreamers who do — Sarah Ban Breathnach.',
        'thisisaverylongitemnamethatshouldnotbeusedinproductionthisisaverylongitemnamethatshouldnotbeusedinproductionthisisaverylongitemnamethatshouldnotbeusedinproduction',
        'This <strong>contains</strong> <em>HTML</em> and an 😂'
    );
    return $item_names[rand(0, count($item_names) - 1)];
}

function generatemenus_get_random_item($args = array()) {
    if (!is_array($args)) {
        $args = array();
    }
    $args = wp_parse_args($args, array(
        'force_home' => false
    ));

    $menu_item = array(
        'menu-item-db-id' => 0,
        'menu-item-type' => 'custom',
        'menu-item-status' => 'publish'
    );
    if ($args['force_home']) {
        $menu_item['menu-item-title'] = 'Home';
        $menu_item['menu-item-url'] = get_site_url();
    } else {
        $p = get_posts(array(
            'post_type' => 'any',
            'posts_per_page' => 1,
            'orderby' => 'rand'
        ));
        $menu_item['menu-item-title'] = $p ? $p[0]->post_title : generatemenus_get_random_item_name();
        $menu_item['menu-item-url'] = $p ? get_permalink($p[0]->ID) : get_site_url();
    }
    return $menu_item;
}

/* ----------------------------------------------------------
  Generate menus
---------------------------------------------------------- */

$locations = get_theme_mod('nav_menu_locations');
if (!is_array($locations)) {
    $locations = array();
}
$has_modification = false;

$nav_menus = get_registered_nav_menus();
foreach ($nav_menus as $menu_slug => $menu_name) {
    foreach ($langs as $lang_id => $lang_details) {
        $menu_name_lang = $menu_name . ($lang_details['suffix'] ? ' - ' . $lang_details['suffix'] : '');
        $menu_item = wp_get_nav_menu_object($menu_name_lang);

        /* Stop if this menu exists */
        if ($menu_item) {
            $menu_id = $menu_item->term_id;
            echo "- Menu {$menu_name_lang} already exists.\n";
            if (!$args['force_add'] || $args['menu_id'] != 'all' && $args['menu_id'] != $menu_id) {
                continue;
            }

            echo "- Adding random items to menu {$menu_name_lang}.\n";
            // Add random items to the menu
            for ($i = 1; $i <= $args['num_items']; $i++) {
                wputools_generatemenus($menu_id, $args);
            }

            continue;
        }

        /* Create menu */
        $menu_id = wp_create_nav_menu($menu_name_lang);

        /* Generate alternate lang menus */
        $has_lang = false;
        $page_item = generatemenus_get_random_item(array(
            'force_home' => true
        ));
        if (isset($polylang_options['nav_menus'])) {
            $has_lang = true;
            $page_item['menu-item-url'] = pll_home_url($lang_id);
            if (!isset($polylang_options['nav_menus'][get_stylesheet()][$menu_slug])) {
                $polylang_options['nav_menus'][get_stylesheet()][$menu_slug] = array();
            }
            $polylang_options['nav_menus'][get_stylesheet()][$menu_slug][$lang_id] = $menu_id;
        }
        $locations[$menu_slug] = $menu_id;

        if (!$args['force_add']) {
            /* Set up default menu item */
            wp_update_nav_menu_item($menu_id, 0, $page_item);
        } else {
            /* Add random items */
            for ($i = 1; $i <= $args['num_items']; $i++) {
                wputools_generatemenus($menu_id, $args);
            }
        }

        $has_modification = true;
        /* Success message */
        echo "- Created {$menu_name_lang}\n";
    }
}

function wputools_generatemenus($menu_id = 0, $args = array()) {
    $random_item = generatemenus_get_random_item($args);
    $parent_item_id = wp_update_nav_menu_item($menu_id, 0, $random_item);
    $number_of_children = rand(3, 6);
    if ($args['depth'] > 1) {
        for ($j = 1; $j <= $number_of_children; $j++) {
            $sub_item = generatemenus_get_random_item();
            $sub_item['menu-item-parent-id'] = $parent_item_id;
            wp_update_nav_menu_item($menu_id, 0, $sub_item);
        }
    }
}

/* ----------------------------------------------------------
  Save mods
---------------------------------------------------------- */

if ($has_modification) {
    set_theme_mod('nav_menu_locations', $locations);
    if (!empty($polylang_options)) {
        update_option('polylang', $polylang_options);
    }
}
