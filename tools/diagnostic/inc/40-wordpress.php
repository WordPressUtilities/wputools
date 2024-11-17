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

if (!is_readable($bootstrap)) {
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
$env_type = wp_get_environment_type();
$debug_env_values = array('local', 'development', 'staging');
$is_debug_env = in_array($env_type, $debug_env_values);

/* Env type
-------------------------- */

if (!$is_debug_env && $env_type != 'production') {
    $wputools_errors[] = 'WordPress : Env type "' . $env_type . '" is not valid';
}

/* Invalid env type
-------------------------- */

function wputools_diagnostic_check_is_preproduction($domainName) {
    // Check for localhost
    if ($domainName === 'localhost') {
        return true;
    }

    // Check for IP address pattern (IPv4)
    if (filter_var($domainName, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        return true;
    }

    // Check if the domain contains a port number
    if (strpos($domainName, ':') !== false) {
        return true;
    }

    // Check for specific subdomains indicating a preproduction environment
    $preproductionSubdomains = ['preview', 'preprod', 'staging'];
    foreach ($preproductionSubdomains as $subdomain) {
        if (strpos($domainName, $subdomain . '.') !== false) {
            return true;
        }
    }

    // Check for specific top-level domains (TLDs) indicating a local or development environment
    $developmentTlds = ['.test', '.local', '.dev'];
    foreach ($developmentTlds as $tld) {
        if (substr($domainName, -strlen($tld)) === $tld) {
            return true;
        }
    }

    // Assume it's a production domain if none of the above criteria are met
    return false;
}

$urlparts = wp_parse_url(home_url());
$looks_like_preproduction = wputools_diagnostic_check_is_preproduction($urlparts['host']);
if (!$is_debug_env && $looks_like_preproduction) {
    $wputools_errors[] = 'WordPress : Domain "' . $domain . '" does not looks like a production domain.';
}
if ($is_debug_env && !$looks_like_preproduction) {
    $wputools_errors[] = 'WordPress : Domain "' . $domain . '" does not looks like a preproduction domain.';
}

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
  Check debug.log settings
---------------------------------------------------------- */

if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
    if (strlen(WP_DEBUG_LOG) < 6) {
        $wputools_errors[] = 'WordPress : WP_DEBUG_LOG should not be a boolean, but a dynamic file path.';
    }
    if (strlen(WP_DEBUG_LOG) > 6 && is_dir(dirname(WP_DEBUG_LOG))) {

        /* Check that logs are correctly written */
        $log_file = WP_DEBUG_LOG;
        $log_test_value = 'wputools_test_log_' . time();
        error_log($log_test_value);
        if (!file_exists(WP_DEBUG_LOG)) {
            $wputools_errors[] = 'WordPress : The log file is not created.';
        } else {
            $log_content = file_get_contents($log_file);
            if (strpos($log_content, $log_test_value) === false) {
                $wputools_errors[] = 'WordPress : The log file is not correctly written.';
            }
        }

        /* Check debug log weight */
        $logs = glob(dirname(WP_DEBUG_LOG) . '/*.log');
        $logs_count = 0;
        $logs_weight = 0;
        if (is_array($logs)) {
            foreach ($logs as $log) {
                $logs_count++;
                $logs_weight += filesize($log);
            }
        }
        $max_weight = 30 * 1024 * 1024;
        if ($logs_weight > $max_weight) {
            $wputools_errors[] = sprintf('WordPress : The log folder size is bigger than %sMB.', round($max_weight / 1024 / 1024));
        }
        $max_count = 30;
        if ($logs_count > $max_count) {
            $wputools_errors[] = sprintf('WordPress : There is more than %s files in the logs folder.', $max_count);
        }
    }
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
  check RAM
---------------------------------------------------------- */

$ram_vars = array('WP_MEMORY_LIMIT', 'WP_MAX_MEMORY_LIMIT');
foreach ($ram_vars as $ram_var) {
    if (!defined($ram_var)) {
        $wputools_errors[] = 'WordPress : ' . $ram_var . ' should be defined.';
        continue;
    }
    $ram_var_value = intval(str_replace('M', '', constant($ram_var)), 10);
    if ($ram_var_value < 128) {
        $wputools_errors[] = 'WordPress : ' . $ram_var . ' value should be higher than 128MB.';
    }
    if ($ram_var_value > 512) {
        $wputools_errors[] = 'WordPress : ' . $ram_var . ' value should be lower than 512MB.';
    }
}

/* ----------------------------------------------------------
  Check for enabled auto file modification
---------------------------------------------------------- */

if (!$is_debug_env) {
    if (!defined('AUTOMATIC_UPDATER_DISABLED') || !AUTOMATIC_UPDATER_DISABLED) {
        $wputools_errors[] = 'WordPress : AUTOMATIC_UPDATER_DISABLED should be set to TRUE on a non-debug environment.';
    }
    if (!defined('WP_AUTO_UPDATE_CORE') || WP_AUTO_UPDATE_CORE) {
        $wputools_errors[] = 'WordPress : WP_AUTO_UPDATE_CORE should be set to FALSE on a non-debug environment.';
    }
    if (!defined('DISALLOW_FILE_EDIT') || !DISALLOW_FILE_EDIT) {
        $wputools_errors[] = 'WordPress : DISALLOW_FILE_EDIT should be set to TRUE on a non-debug environment.';
    }
    if (!defined('DISALLOW_FILE_MODS') || !DISALLOW_FILE_MODS) {
        $wputools_errors[] = 'WordPress : DISALLOW_FILE_MODS should be set to TRUE on a non-debug environment.';
    }
}

/* ----------------------------------------------------------
  Check commits hooks
---------------------------------------------------------- */

if ($is_debug_env && is_readable('.git/config') && !is_readable('.git/hooks/pre-commit')) {
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
  Check local overrides
---------------------------------------------------------- */

$local_override_file = WPMU_PLUGIN_DIR . '/wpu_local_overrides.php';
if ($env_type == 'local' && !is_file($local_override_file)) {
    $wputools_errors[] = 'WordPress : You should have a local overrides file.';
}

/* ----------------------------------------------------------
  Some URLs should not be publicly accessible
---------------------------------------------------------- */

$uris = array(
    '/.git/config',
    '/wp-json/wp/v2/users',
    '/wp-json/wp/v2/pages',
    '/wp-admin/index.php',
    '/wp-includes/wlwmanifest.xml',
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
  Some URLs should exist
---------------------------------------------------------- */

$uris = array(
    '/favicon.ico',
    '/robots.txt',
    '/sitemap.xml',
    '/wp-sitemap.xml'
);
foreach ($uris as $uri) {
    $response_code = wp_remote_retrieve_response_code(wp_remote_head(site_url() . $uri));
    if (!in_array($response_code, array(200, 301, 302))) {
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
  Check home page speed
---------------------------------------------------------- */

$info = false;
$ch = curl_init($site_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
if (curl_exec($ch)) {
    $info = curl_getinfo($ch);
}
curl_close($ch);
if (is_array($info) && isset($info['total_time']) && $info['total_time'] > 0.2) {
    $wputools_errors[] = sprintf('Homepage took %s seconds to load.', $info['total_time']);
}

/* ----------------------------------------------------------
  Check RSS
---------------------------------------------------------- */

function wputools_test_rss_feed($rss_url) {
    global $wputools_errors;

    libxml_use_internal_errors(true);

    if (empty($rss_url)) {
        $wputools_errors[] = __('The RSS URL is empty.');
        return;
    }

    $rss_content = @simplexml_load_file($rss_url);
    if ($rss_content === false) {
        $error_text = '';
        foreach (libxml_get_errors() as $error) {
            if ($error->message) {
                $error_text .= 'line ' . $error->line . ': ' . $error->message;
            }
        }
        $wputools_errors[] = sprintf(__("Failed to parse RSS feed :\n%s"), $error_text);
        return;
    }

    $items = $rss_content->channel->item;
    if (empty($items)) {
        $wputools_errors[] = __('No articles found in the RSS feed.');
        return;
    }
}

wputools_test_rss_feed(site_url() . '/feed');

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
    $top_options = $wpdb->get_results("SELECT option_name, LENGTH(option_value) AS opt_length FROM $wpdb->options WHERE autoload='yes' ORDER BY opt_length DESC LIMIT 0,15;");
    $top_options_text = '';
    foreach ($top_options as $top_option) {
        $top_options_text .= "\n  - " . $top_option->option_name . ": " . round($top_option->opt_length / 1024) . 'kb';
    }
    $wputools_errors[] = sprintf('The autoloaded options may be too heavy (%skb). It could be slowing down your website. Biggest lines: %s', round($autoloaded_weight / 1024), $top_options_text);
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

/* ----------------------------------------------------------
  Check admins
---------------------------------------------------------- */

$admins = get_users(array(
    'role' => 'administrator'
));

/* Check 2FA */
if (!$is_debug_env) {
    if (!in_array('two-factor/two-factor.php', get_option('active_plugins'))) {
        $wputools_errors[] = 'You should have an active 2FA plugin.';
    } else {
        foreach ($admins as $user) {
            $two_fa_user = get_user_meta($user->ID, '_two_factor_enabled_providers', true);
            if (!$two_fa_user) {
                $wputools_errors[] = sprintf('You should have 2FA enabled for the user “%s”.', $user->user_nicename);
            }
        }
    }
}

/* Check user name */
if (count($admins) > 1) {
    $admins = array_map(function ($c) {
        return $c->user_nicename;
    }, $admins);
    $wputools_errors[] = sprintf('You should not have more than one administrator. List of admins: “%s”.', implode('”, “', $admins));
}

/* ----------------------------------------------------------
  Check public setting
---------------------------------------------------------- */

$search_engines_blocked = (get_option('blog_public') == '0');
/* Do not block search engines on local & production */
if ((!$is_debug_env || $env_type == 'local') && $search_engines_blocked) {
    $wputools_errors[] = sprintf('WordPress : Search engines are blocked while the environment is defined as “%s”.', wp_get_environment_type());
}
/* Block search engines on debug env */
if (($is_debug_env && $env_type != 'local') && !$search_engines_blocked) {
    $wputools_errors[] = sprintf('WordPress : Search engines are not blocked while the environment is defined as “%s”.', wp_get_environment_type());
}
/* ----------------------------------------------------------
  Check posts
---------------------------------------------------------- */

$post_types = get_post_types();
if (isset($post_types['nav_menu_item'])) {
    unset($post_types['nav_menu_item']);
}
$all_posts = get_posts(array(
    'posts_per_page' => 100,
    'post_status' => 'publish',
    'post_type' => $post_types
));

$lorem_ipsum_strings = array(
    'lorem ipsum',
    'needs dreamers and the world'
);

$empty_pages = array();
$lorem_pages = array();
foreach ($all_posts as $p) {
    if (empty($p->post_content) && empty($p->post_excerpt)) {
        $empty_pages[] = get_permalink($p) . ' (' . $p->post_type . ')';
    }
    if (!empty($p->post_content)) {
        foreach ($lorem_ipsum_strings as $lorem_ipsum_string) {
            if (strpos($p->post_content, $lorem_ipsum_string) !== false) {
                $lorem_pages[] = get_permalink($p) . ' (' . $p->post_type . ')';
            }
        }
    }
}

if (!empty($empty_pages)) {
    $wputools_errors[] = sprintf("The following posts don't have any content: \n-- %s", implode("\n-- ", $empty_pages));
}

if (!empty($lorem_pages)) {
    $wputools_errors[] = sprintf("The following posts contains some lorem ipsum: \n-- %s", implode("\n-- ", $lorem_pages));
}

/* ----------------------------------------------------------
  Check image sizes
---------------------------------------------------------- */

$all_image_sizes = get_intermediate_image_sizes();
$max_nb_image_sizes = 7;
$nb_all_image_sizes = count($all_image_sizes);
if ($nb_all_image_sizes > $max_nb_image_sizes) {
    $additional_wp_sizes = wp_get_additional_image_sizes();
    $image_sizes_text = '';
    foreach ($all_image_sizes as $size) {
        $size_values = array(
            'width' => get_option($size . '_size_w'),
            'height' => get_option($size . '_size_h')
        );
        if (is_numeric($size_values['width']) && $size_values['width'] < 1) {
            $size_values['width'] = 'auto';
        }
        if (is_numeric($size_values['height']) && $size_values['height'] < 1) {
            $size_values['height'] = 'auto';
        }
        if (!$size_values['width'] && isset($additional_wp_sizes[$size]['width'])) {
            $size_values['width'] = $additional_wp_sizes[$size]['width'];
        }
        if (!$size_values['height'] && isset($additional_wp_sizes[$size]['height'])) {
            $size_values['height'] = $additional_wp_sizes[$size]['height'];
        }
        $image_sizes_text .= "\n-- " . $size . ': ' . $size_values['width'] . '×' . $size_values['height'];
    }

    $wputools_errors[] = sprintf('There are %d images sizes, please check if they are useful : %s', $nb_all_image_sizes, $image_sizes_text);
}
