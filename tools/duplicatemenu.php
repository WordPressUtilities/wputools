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
  Helpers
---------------------------------------------------------- */

function wputools_display_menus() {
    echo "Please select a menu and retry:\n";
    $menus = get_terms('nav_menu', array('hide_empty' => true));
    foreach ($menus as $menu) {
        echo "#{$menu->term_id} - {$menu->name}\n";
    }
}

/* ----------------------------------------------------------
  Checks
---------------------------------------------------------- */

if (!isset($_GET['menu_id']) || !is_numeric($_GET['menu_id'])) {
    echo "Menu ID is invalid.\n";
    wputools_display_menus();
    return;
}

$id = intval($_GET['menu_id'], 10);
$old_menu = wp_get_nav_menu_object($id);

if (!$old_menu) {
    echo "This menu does not exists.\n";
    wputools_display_menus();
    return;
}

/* ----------------------------------------------------------
  Create new menu
---------------------------------------------------------- */

$new_menu_name = $old_menu->name . ' - ' . date('Y-m-d H:i:s');
$new_menu_id = wp_create_nav_menu($new_menu_name);

/* ----------------------------------------------------------
  Duplicate items
---------------------------------------------------------- */

$matches_ids = array();
$old_menu_items = wp_get_nav_menu_items($id);
foreach ($old_menu_items as $i => $item) {
    $args = array(
        'menu-item-attr-title' => $item->attr_title,
        'menu-item-classes' => implode(' ', $item->classes),
        'menu-item-description' => $item->description,
        'menu-item-object' => $item->object,
        'menu-item-object-id' => $item->object_id,
        'menu-item-position' => $i,
        'menu-item-status' => $item->post_status,
        'menu-item-target' => $item->target,
        'menu-item-title' => $item->title,
        'menu-item-type' => $item->type,
        'menu-item-url' => $item->url,
        'menu-item-xfn' => $item->xfn
    );
    $old_metas = get_post_meta($item->ID);
    $new_metas = array();
    foreach ($old_metas as $meta_key => $meta_value) {
        if ($meta_key[0] == '_') {
            continue;
        }
        $new_metas[$meta_key] = $meta_value[0];
    }

    $new_item_id = wp_update_nav_menu_item($new_menu_id, 0, $args);
    foreach ($new_metas as $meta_key => $meta_value) {
        update_post_meta($new_item_id, $meta_key, $meta_value);
    }

    $matches_ids[$item->db_id] = array(
        'id' => $new_item_id,
        'parent' => $item->menu_item_parent,
        'args' => $args
    );
}

/* ----------------------------------------------------------
  Match parent menu
---------------------------------------------------------- */

foreach ($matches_ids as $new_item) {
    if ($new_item['parent']) {
        $new_item['args']['menu-item-parent-id'] = $matches_ids[$new_item['parent']]['id'];
        wp_update_nav_menu_item($new_menu_id, $new_item['id'], $new_item['args']);
    }
}

/* ----------------------------------------------------------
  Success
---------------------------------------------------------- */

echo "A copy of this menu has been created : \"{$new_menu_name}\"\n";
