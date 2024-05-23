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

global $wpdb;

# Get all users with admin level
$admins = get_users(array(
    'role__in' => array(
        'administrator',
        'super_editor',
        'editor',
        'author',
        'contributor'
    ),
    'fields' => 'ids'
));

$admins = implode(',', $admins);

###################################
## Users table
###################################

$wpdb->query("
    UPDATE $wpdb->users
    SET
        user_email = CONCAT('user', ID, '@example.com'),
        user_nicename = CONCAT('user', ID),
        user_login = CONCAT('user', ID),
        display_name = CONCAT('User ', ID), user_url = ''
    WHERE ID NOT IN (" . $admins . ");
");

###################################
## Usermeta table
###################################

# Nickname
$wpdb->query("
    UPDATE $wpdb->usermeta SET
    meta_value = CONCAT('user', user_id)
    WHERE meta_key = 'nickname'
    AND user_id NOT IN (" . $admins . ");
");

# Email
$wpdb->query("
    UPDATE $wpdb->usermeta SET
    meta_value = CONCAT('user', user_id, '@example.com')
    WHERE meta_key = 'user_email'
    AND user_id NOT IN (" . $admins . ");
");

# First name and last name
$wpdb->query("
    UPDATE $wpdb->usermeta SET
    meta_value = CONCAT('User', user_id)
    WHERE (meta_key = 'first_name' OR meta_key = 'last_name')
    AND user_id NOT IN (" . $admins . ");
");
