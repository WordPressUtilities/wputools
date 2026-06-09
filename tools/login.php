<?php

/* ----------------------------------------------------------
  Avoid preview
---------------------------------------------------------- */

$file = basename(__FILE__);
if (!isset($_GET['wputools_login'])) {
    $url_args = array(
        'wputools_login' => 1
    );
    if (isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
        $url_args['user_id'] = $_GET['user_id'];
    }
    echo '<script>window.location.href="' . basename(__FILE__) . '?' . http_build_query($url_args) . '"</script>';
    return;
}

/* ----------------------------------------------------------
  File expiration
---------------------------------------------------------- */

if (filemtime(__FILE__) < time() - 60) {
    http_response_code(410);
    die('This file has expired.');
}

/* ----------------------------------------------------------
  Bootstrap
---------------------------------------------------------- */

/* Disable default environment */
define('WP_USE_THEMES', false);
define('WP_ADMIN', true);
define('DOING_AJAX', false);

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

/* ----------------------------------------------------------
  Correct URL
---------------------------------------------------------- */

define('WPUTOOLS_LOGIN_DOMAIN', '{{LOGIN_DOMAIN}}');
$blog_url = get_bloginfo('url');
$bloginfo_url_parts = parse_url($blog_url);
$bloginfo_url = $bloginfo_url_parts['scheme'] . "://" . $bloginfo_url_parts['host'];
if (WPUTOOLS_LOGIN_DOMAIN !== $bloginfo_url_parts['host']) {
    http_response_code(400);
    die('Bad Request: This file must be accessed from ' . WPUTOOLS_LOGIN_DOMAIN);
}

/* ----------------------------------------------------------
  Login
---------------------------------------------------------- */

/* Require some functions if W3TC is installed */

/* Require some functions if W3TC is installed */
$admin_path = str_replace(strtok($bloginfo_url, '?') . '/', ABSPATH, get_admin_url());
require_once $admin_path . '/includes/screen.php';

/* Start WP */
wp();

$user = false;
if (isset($_GET['user_id'])) {
    $user = get_user_by(is_numeric($_GET['user_id']) ? 'id' : 'slug', $_GET['user_id']);
} else {
    $users = get_users(array(
        'role' => 'administrator',
        'fields' => 'id',
        'orderby' => 'ID',
        'order' => 'ASC'
    ));

    if (empty($users) || !isset($users[0]) || !is_numeric($users[0])) {
        die('Could not find an admin user!');
    }
    $user = get_user_by('id', $users[0]);
}

if (!$user) {
    die('Could not load the user!');
}

/* Disable two-factor auth */

add_filter('get_user_metadata', function ($value, $user_id, $meta_key, $single) {
    if ($meta_key === '_two_factor_enabled_providers') {
        return array();
    }
    return $value;
}, 99, 4);
add_filter('two_factor_providers', '__return_empty_array', 10, 1);
add_filter('wp_2fa_user_enabled_methods', '__return_empty_array', 10, 1);

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
