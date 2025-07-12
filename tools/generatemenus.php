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

$page_item = array(
    'menu-item-db-id' => 0,
    'menu-item-type' => 'custom',
    'menu-item-title' => 'Home',
    'menu-item-url' => get_site_url(),
    'menu-item-parent-id' => 0,
    'menu-item-status' => 'publish'
);

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
            for ($i = 1; $i <= 2; $i++) {
                $random_item = $page_item;
                $random_item['menu-item-title'] = generatemenus_get_random_item_name($args['generate_type']) . $i;
                $random_item['menu-item-url'] = get_site_url();
                $parent_item_id = wp_update_nav_menu_item($menu_id, 0, $random_item);
                $number_of_children = rand(3, 6);
                if ($args['depth'] > 1) {
                    for ($j = 1; $j <= $number_of_children; $j++) {
                        $sub_item = array(
                            'menu-item-db-id' => 0,
                            'menu-item-type' => 'custom',
                            'menu-item-title' => generatemenus_get_random_item_name($args['generate_type']) . $j,
                            'menu-item-url' => get_site_url(),
                            'menu-item-parent-id' => $parent_item_id,
                            'menu-item-status' => 'publish'
                        );
                        wp_update_nav_menu_item($menu_id, 0, $sub_item);
                    }
                }
            }

            continue;
        }

        /* Create menu */
        $menu_id = wp_create_nav_menu($menu_name_lang);

        /* Generate alternate lang menus */
        if (isset($polylang_options['nav_menus'])) {
            $page_item['menu-item-url'] = pll_home_url($lang_id);
            if (!isset($polylang_options['nav_menus'][get_stylesheet()][$menu_slug])) {
                $polylang_options['nav_menus'][get_stylesheet()][$menu_slug] = array();
            }
            $polylang_options['nav_menus'][get_stylesheet()][$menu_slug][$lang_id] = $menu_id;
        }
        $locations[$menu_slug] = $menu_id;

        /* Set up default menu items */
        wp_update_nav_menu_item($menu_id, 0, $page_item);
        $has_modification = true;
        /* Success message */
        echo "- Created {$menu_name_lang}\n";
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
