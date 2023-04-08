<?php

/* Disable default environment */
define('WP_USE_THEMES', false);
define('WP_ADMIN', true);

/* Fool login check */
$_SERVER['PHP_SELF'] = '/wp-admin/index.php';

/* Disable maintenance */
define('WPUMAINTENANCE_DISABLED', true);

/* Thanks to http://boiteaweb.fr/wordpress-bootstraps-ou-comment-bien-charger-wordpress-6717.html */
chdir(dirname(__FILE__));
$bootstrap = 'wp-load.php';
while (!is_file($bootstrap)) {
    if (is_dir('..') && getcwd() != '/') {
        chdir('..');
    } else {
        die('Could not find WordPress!');
    }
}
require_once $bootstrap;

/* Require some functions if W3TC is installed */
$admin_path = str_replace(get_bloginfo('url') . '/', ABSPATH, get_admin_url());
require_once $admin_path . '/includes/screen.php';

/* Start WP */
wp();

$user = false;
if (isset($_GET['user_id'])) {
    $user = get_user_by(is_numeric($_GET['user_id']) ? 'id' : 'slug', $_GET['user_id']);
} else {
    $users = get_users(array(
        'role' => 'administrator',
        'fields' => 'id'
    ));

    if (empty($users) || !isset($users[0]) || !is_numeric($users[0])) {
        die('Could not find an admin user!');
    }
    $user = get_user_by('id', $users[0]);
}

if (!$user) {
    die('Could not load the user!');
}

/* Avoid unwanted redirects or else */
remove_all_actions('wp_logout');

/* Logout */
wp_logout();

/* Login as user */
wp_set_current_user($user->ID, $user->user_login);
wp_set_auth_cookie($user->ID);
do_action('wp_login', $user->user_login, $user);

/* Redirect to admin home */
wp_redirect(get_admin_url());

/* Delete this file */
unlink(__FILE__);
