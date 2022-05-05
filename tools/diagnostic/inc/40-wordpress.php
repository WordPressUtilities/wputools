<?php

/* ----------------------------------------------------------
  Bootstrap WP
---------------------------------------------------------- */

$bootstrap = 'wp-load.php';
while (!is_file($bootstrap)) {
    if (is_dir('..') && getcwd() != '/') {
        chdir('..');
    }
}

if (!file_exists($bootstrap)) {
    return;
}

/* Disable default environment */
define('WP_USE_THEMES', false);

/* Fix for qtranslate and other plugins */
define('WP_ADMIN', true);
$_SERVER['PHP_SELF'] = '/wp-admin/index.php';

/* Include wp load */
require_once $bootstrap;

/* ----------------------------------------------------------
  Initial checks
---------------------------------------------------------- */

/* Debug
-------------------------- */

if (!function_exists('wp_get_environment_type')) {
    function wp_get_environment_type() {
        return '';
    }
}
$is_debug_env = in_array(wp_get_environment_type(), array('local', 'development'));

/* Parts
-------------------------- */

$site_url = get_site_url();
$url_parts = parse_url($site_url);
$ignored_extensions = array('test', 'local', 'dev', 'localhost');
$is_https = isset($url_parts['scheme']) && $url_parts['scheme'] == 'https';
$host_extension = '';
if (isset($url_parts['host'])) {
    $url_parts_host = explode(".", $url_parts['host']);
    $host_extension = end($url_parts_host);
}
$is_test_extension = $host_extension && in_array($host_extension, $ignored_extensions);

/* ----------------------------------------------------------
  Check SAVEQUERIES
---------------------------------------------------------- */

if ($wputools_is_cli && defined('SAVEQUERIES') && SAVEQUERIES) {
    $wputools_errors[] = 'WordPress : SAVEQUERIES should not be enabled on CLI, because it can induce some memory leaks.';
}

/* ----------------------------------------------------------
  Check Debug env
---------------------------------------------------------- */

if ($host_extension && ($is_test_extension xor $is_debug_env)) {
    $wputools_errors[] = sprintf('WordPress : The environment is defined as %s, which is not normal for a website with an extension in .%s.', wp_get_environment_type(), $host_extension);
}

/* ----------------------------------------------------------
  Check SCRIPT_DEBUG
---------------------------------------------------------- */

if (!$is_debug_env && defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) {
    $wputools_errors[] = 'WordPress : SCRIPT_DEBUG should only be enabled on a local or dev environment.';
}

/* ----------------------------------------------------------
  Check commits hooks
---------------------------------------------------------- */

if ($is_debug_env && file_exists('.git/config') && !file_exists('.git/hooks/pre-commit')) {
    $wputools_errors[] = 'Git : You should have a pre-commit hook installed.';
}

/* ----------------------------------------------------------
  Check MySQL version
---------------------------------------------------------- */

global $wpdb;
if (method_exists($wpdb, 'db_server_info')) {
    $mysqlVersion = $wpdb->db_server_info();
    if (strpos($mysqlVersion, 'MariaDB') === false && version_compare($mysqlVersion, '8.0', '<')) {
        $wputools_errors[] = sprintf('MySQL version %s is too old !', $mysqlVersion);
    }
}

/* ----------------------------------------------------------
  Check some constants
---------------------------------------------------------- */

$php_constants = array('WP_CACHE_KEY_SALT');
foreach ($php_constants as $constant) {
    if (!defined($constant)) {
        $wputools_errors[] = sprintf('WordPress : the constant %s should be defined.', $constant);
    }
}

/* ----------------------------------------------------------
  Some URLs should not be publicly accessible
---------------------------------------------------------- */

$uris = array(
    '/.git/config',
    '/wp-json/wp/v2/users',
    '/wp-admin/index.php',
    '/wp-includes/version.php'
);
if (!$is_debug_env) {
    $uris[] = '/wp-login.php';
}
$all_themes = wp_get_themes();
foreach ($all_themes as $theme_id => $theme_values) {
    $uris[] = '/wp-content/themes/' . $theme_id . '/index.php';
    $uris[] = '/wp-content/themes/' . $theme_id . '/functions.php';
}
foreach ($uris as $uri) {
    $response_code = wp_remote_retrieve_response_code(wp_remote_head(site_url() . $uri));
    if (in_array($response_code, array(200, 500, 503))) {
        $wputools_errors[] = sprintf('WordPress : the URL %s should not return a code %s.', site_url() . $uri, $response_code);
    }
}

/* ----------------------------------------------------------
  Check https
---------------------------------------------------------- */

if (!$is_https && !$is_test_extension) {
    $wputools_errors[] = sprintf('The %s site url should use https !', $site_url);
}

/* ----------------------------------------------------------
  Compare WP Version
---------------------------------------------------------- */

global $wp_version;
$request = wp_remote_get('https://api.wordpress.org/core/stable-check/1.0/');
if (!is_wp_error($request)) {
    $data = json_decode(wp_remote_retrieve_body($request), true);
    if (is_array($data) && isset($data[$wp_version]) && $data[$wp_version] != 'latest') {
        $latest_version = '';
        foreach ($data as $version_key => $status) {
            if ($status == 'latest') {
                $latest_version = $version_key;
            }
        }
        $wputools_errors[] = sprintf('Your WordPress v%s is %s ! The latest version is %s.', $wp_version, $data[$wp_version], $latest_version);
    }
}

/* ----------------------------------------------------------
  Check autoloaded options weight
---------------------------------------------------------- */

global $wpdb;
$autoloaded_weight = intval($wpdb->get_var("SELECT SUM(LENGTH(option_value)) as autoload_weight FROM $wpdb->options WHERE autoload='yes';"));
if ($autoloaded_weight > 1024 * 200) {
    $wputools_errors[] = sprintf('The autoloaded options may be too heavy (%skb). It could be slowing down your website.', round($autoloaded_weight / 1024));
}

/* ----------------------------------------------------------
  Check forbidden user slugs
---------------------------------------------------------- */

$forbidden_slugs = array('admin', 'editor', get_stylesheet());
foreach ($forbidden_slugs as $user_slug) {
    $user_admin = get_user_by('slug', $user_slug);
    if (!$user_admin) {
        continue;
    }
    $wputools_errors[] = sprintf('You should not have an user with the ID “%s”.', $user_slug);
}
