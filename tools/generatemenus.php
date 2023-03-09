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

/* ----------------------------------------------------------
  Build lang list
---------------------------------------------------------- */

$langs = array(
    'default' => array(
        'suffix' => ''
    )
);

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

        /* Stop if this menu exists */
        if (wp_get_nav_menu_object($menu_name_lang)) {
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
