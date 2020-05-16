<?php

/* Disable default environment */
define('WP_USE_THEMES', false);
define('WP_ADMIN', true);

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

/* Start WP */
wp();
$users = get_users(array(
    'role' => 'administrator',
    'fields' => 'id'
));

if (empty($users) || !isset($users[0]) || !is_numeric($users[0])) {
    die('Could not find an admin user!');
}

$user = get_user_by('id', $users[0]);
if (!$user) {
    die('Could not load the user!');
}

/* Logout */
wp_logout();

/* Login as user */
wp_set_current_user($users[0], $user->user_login);
wp_set_auth_cookie($users[0]);
do_action('wp_login', $user->user_login, $user);

/* Redirect to admin home */
wp_redirect('/wp-admin/');

/* Delete this file */
unlink(__FILE__);
