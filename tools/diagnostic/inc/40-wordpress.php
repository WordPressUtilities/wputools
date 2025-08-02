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

/* Load a site */
if ($wpudiag_site) {
    $site = get_site_by_path($wpudiag_site, '/');
    if ($site) {
        switch_to_blog($site->blog_id);
    } else {
        $wputools_errors[] = sprintf('The site "%s" does not exist.', $wpudiag_site);
        return;
    }
}

/* ----------------------------------------------------------
  Initial checks
---------------------------------------------------------- */

/* Loading
-------------------------- */

if (!function_exists('get_option')) {
    $wputools_errors[] = 'WordPress is not correctly loaded';
    return;
}

/* Database
-------------------------- */

global $wpdb;
if (!is_object($wpdb) || !$wpdb->check_connection()) {
    $wputools_errors[] = 'WordPress : The database is not available.';
    return;
}

/* Debug
-------------------------- */

if (!function_exists('wp_get_environment_type')) {
    function wp_get_environment_type() {
        return '';
    }
}
$env_type = wp_get_environment_type();
$debug_env_values = array('local', 'development', 'staging', 'preproduction');
$is_debug_env = in_array($env_type, $debug_env_values);

/* Branch name
-------------------------- */

if (isset($wpudiag_branch_name) && $wpudiag_branch_name) {
    if ($env_type == 'production' && in_array($wpudiag_branch_name, array('develop', 'staging', 'preprod'))) {
        $wputools_errors[] = 'WordPress : The branch name is "' . strip_tags($wpudiag_branch_name) . '" on a production environment.';
    }
    if ($is_debug_env && in_array($wpudiag_branch_name, array('master', 'main', 'production'))) {
        $wputools_errors[] = 'WordPress : The branch name is "' . strip_tags($wpudiag_branch_name) . '" on a non-production environment.';
    }
}

/* Path
-------------------------- */

$production_path_list = array('/prod/', '/production/');
$debug_path_list = array('/dev/', '/development/', '/staging/', '/preprod/');

if ($env_type == 'production') {
    foreach ($debug_path_list as $path_part) {
        if (strpos($wpudiag_path, $path_part) !== false) {
            $wputools_errors[] = 'WordPress : The path contains "' . strip_tags($path_part) . '" on a production environment.';
        }
    }
}
if ($is_debug_env) {
    foreach ($production_path_list as $path_part) {
        if (strpos($wpudiag_path, $path_part) !== false) {
            $wputools_errors[] = 'WordPress : The path contains "' . strip_tags($path_part) . '" on a non-production environment.';
        }
    }
}

/* Env type
-------------------------- */

if (!$is_debug_env && $env_type != 'production') {
    $wputools_errors[] = 'WordPress : Env type "' . $env_type . '" is not valid';
}

/* Invalid env type
-------------------------- */

function wputools_diagnostic_check_domain_is_preproduction($domainName) {
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
$domain = $urlparts['host'];
$looks_like_preproduction = wputools_diagnostic_check_domain_is_preproduction($domain);
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
  Htaccess : test file
---------------------------------------------------------- */

$htaccess_file = ABSPATH . '.htaccess';
if (!is_file($htaccess_file)) {
    $wputools_errors[] = 'The root .htaccess file is missing !';
} else {
    if (!is_writable($htaccess_file)) {
        $wputools_errors[] = 'The root .htaccess file is not writable !';
    }
}

/* ----------------------------------------------------------
  Test if there is a case sensitive config
---------------------------------------------------------- */

$wputools_temp_file = wp_upload_dir()['basedir'] . '/wputools_TEMP_file_' . uniqid() . '.txt';
$wputools_temp_file_lower = strtolower($wputools_temp_file);

try {
    file_put_contents($wputools_temp_file, 'file');
} catch (Exception $e) {
    $wputools_errors[] = 'Failed to create case sensitive test file.';
}

if (!file_exists($wputools_temp_file)) {
    $wputools_errors[] = 'Failed to create case sensitive test file.';
}

if (is_readable($wputools_temp_file_lower)) {
    $wputools_notices[] = 'The file ' . basename($wputools_temp_file_lower) . ' is readable, which means the filesystem is case insensitive.';
} else {
    $wputools_errors[] = 'The file ' . basename($wputools_temp_file_lower) . ' is not readable, which means the filesystem is case sensitive.';
}

/* Clean up the temporary file */
if (file_exists($wputools_temp_file)) {
    unlink($wputools_temp_file);
}

/* ----------------------------------------------------------
  Htaccess : test directory protection
---------------------------------------------------------- */

$temp_dir_name = 'wputools_temp_dir_' . uniqid();
$wputools_temp_dir = wp_upload_dir()['basedir'] . '/' . $temp_dir_name;
$wputools_temp_url = wp_upload_dir()['baseurl'] . '/' . $temp_dir_name;
try {
    mkdir($wputools_temp_dir, 0755, true);
} catch (Exception $e) {
    $wputools_errors[] = sprintf('The temporary directory %s could not be created', $wputools_temp_dir);
}

if (!is_dir($wputools_temp_dir)) {
    $wputools_errors[] = sprintf('The temporary directory %s could not be created', $wputools_temp_dir);
} else {
    /* Create temp files */
    $temp_files = array(
        '.htaccess',
        'test.txt'
    );

    foreach ($temp_files as $temp_file) {
        file_put_contents($wputools_temp_dir . '/' . $temp_file, 'deny from all');
    }

    /* Test if the temporary directory is correctly blocked */
    $response = wp_remote_get($wputools_temp_url . '/test.txt');
    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 403) {
        $wputools_errors[] = 'The temporary directory test did not return a 403 error as expected.';
    }

    /* Clean up the temporary directory */
    foreach ($temp_files as $temp_file) {
        unlink($wputools_temp_dir . '/' . $temp_file);
    }

    /* Test if the temporary directory does not show an Apache index */
    $response = wp_remote_get($wputools_temp_url . '/');
    if (!is_wp_error($response)) {
        $body = wp_remote_retrieve_body($response);
        if (strpos($body, '<title>Index of') !== false) {
            $wputools_errors[] = 'The temporary directory is publicly accessible and shows an Apache index.';
        }
    }

    rmdir($wputools_temp_dir);
}

/* ----------------------------------------------------------
  Mail
---------------------------------------------------------- */

$wputools_test_address = 'test@example.com';
if (defined('WPUTOOLS_TEST_ADDRESS')) {
    $wputools_test_address = WPUTOOLS_TEST_ADDRESS;
}
if (function_exists('mail')) {
    $sentmail = mail($wputools_test_address, 'subject', 'message');
    if (!$sentmail) {
        $error_string = 'PHP mail function to "%s" doesn’t seem to work !';
        if (defined('WPUTOOLS_TEST_ADDRESS')) {
            $error_string .= ' (defined in WPUTOOLS_TEST_ADDRESS)';
        } else {
            $error_string .= ' Please try a custom test address WPUTOOLS_TEST_ADDRESS in your wp-config.php';
        }
        $wputools_errors[] = sprintf($error_string, $wputools_test_address);
    }
} else {
    $wputools_errors[] = 'PHP mail function is not available !';
}

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
        $debug_log_dir = realpath(dirname(WP_DEBUG_LOG));

        /* Check that log path is not in wp-content/debug.log */
        if (strpos($debug_log_dir, ABSPATH) !== false) {
            $wputools_errors[] = 'WordPress : WP_DEBUG_LOG should be located outside the project directory.' . $debug_log_dir;
        }

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
        $logs = glob($debug_log_dir . '/*.log');
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
  Check if a MySQL table named *logs* exists and contains more than 1000 rows
---------------------------------------------------------- */

global $wpdb;
$q = "SHOW TABLES LIKE '%log%'";
$tables = $wpdb->get_results($q);
foreach ($tables as $table) {
    $logs_table = implode('', get_object_vars($table));
    $logs_count = $wpdb->get_var("SELECT COUNT(*) FROM $logs_table");
    if ($logs_count > 1000) {
        $wputools_errors[] = sprintf('WordPress : The table “%s” contains more than 1000 rows.', $logs_table);
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
  Check execution time
---------------------------------------------------------- */

if (!$wputools_is_cli) {
    $execution_time = ini_get('max_execution_time');
    if ($execution_time < 5 || $execution_time > 120) {
        $wputools_errors[] = sprintf('WordPress : max_execution_time should be set roughly to 30 seconds. Current value is %s seconds.', $execution_time);
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
        $wputools_errors[] = sprintf('WordPress : the constant %s should be defined : wputools wp config shuffle-salts WP_CACHE_KEY_SALT --force', $constant);
    }
}

/* ----------------------------------------------------------
  Check local overrides
---------------------------------------------------------- */

$local_override_file = WPMU_PLUGIN_DIR . '/wpu_local_overrides.php';
if ($env_type == 'local' && !is_file($local_override_file)) {
    $wputools_notices[] = 'WordPress : You should have a local overrides file.';
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
if (post_type_exists('post')) {
    $latest_post = get_posts(array(
        'posts_per_page' => 1,
        'post_type' => 'post',
        'orderby' => 'date',
        'order' => 'DESC'
    ));
    if (!empty($latest_post)) {
        $latest_post_year = date('Y', strtotime($latest_post[0]->post_date));
        $uris[] = '/' . $latest_post_year . '/';
    }
}
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

/* Check redirection
-------------------------- */

if ($env_type == 'production') {
    $site_has_www = strpos($site_url, '/www.') !== false;
    $site_before_redirection = $site_has_www ? str_replace('/www.', '/', $site_url) : str_replace('://', '://www.', $site_url);
    $response = wp_remote_get($site_before_redirection, array('redirection' => 0));
    $redirection_ok = false;
    if (!is_wp_error($response)) {
        $redirected_url = wp_remote_retrieve_header($response, 'location');
        if ($redirected_url) {
            if ($site_has_www && strpos($redirected_url, '/www.') !== false) {
                $redirection_ok = true;
            }
            if (!$site_has_www && strpos($redirected_url, '/www.') === false) {
                $redirection_ok = true;
            }
        } else {
            $wputools_errors[] = __('WWW Redirection could not be tested : The location header is missing in the response.');
        }
        if (!$redirection_ok) {
            $wputools_errors[] = sprintf('The URL "%s" should redirect to the %s version.', $site_before_redirection, $site_has_www ? 'www' : 'non-www');
        }
    }
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
    global $wputools_notices;

    if (!function_exists('libxml_use_internal_errors') || !function_exists('simplexml_load_string')) {
        $wputools_errors[] = __('The XML extension is not available.');
        return;
    }

    if (!filter_var(ini_get('allow_url_fopen'), FILTER_VALIDATE_BOOLEAN)) {
        $wputools_errors[] = __('The PHP setting allow_url_fopen is disabled. RSS fetching may fail.');
        return;
    }

    libxml_use_internal_errors(true);

    if (empty($rss_url)) {
        $wputools_errors[] = __('The RSS URL is empty.');
        return;
    }

    $rss_content_text = file_get_contents($rss_url);
    $rss_content = @simplexml_load_string($rss_content_text);
    if ($rss_content === false) {
        $rss_content_text = html_entity_decode($rss_content_text);
        $rss_content = @simplexml_load_string($rss_content_text);
        if ($rss_content) {
            $wputools_notices[] = __('The RSS Feed was parsed, but had an encoding problem.');
        }
    }

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
        $wputools_notices[] = __('No articles found in the RSS feed.');
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
    if (!in_array('two-factor/two-factor.php', get_option('active_plugins')) && !is_plugin_active_for_network('two-factor/two-factor.php')) {
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
  Templates
---------------------------------------------------------- */

/* If no attachment template, the link to an attachment should be redirected */
if (!is_readable(get_stylesheet_directory() . '/attachment.php')) {
    $attachments = get_posts(array(
        'post_type' => 'attachment',
        'posts_per_page' => 1,
        'post_status' => 'inherit'
    ));
    if (!empty($attachments)) {
        $attachment_url = get_attachment_link($attachments[0]->ID);
        if ($attachment_url) {
            $response = wp_remote_get($attachment_url, array('redirection' => 0));
            if (!is_wp_error($response)) {
                $redirected_url = wp_remote_retrieve_header($response, 'location');
                if (empty($redirected_url)) {
                    $wputools_errors[] = sprintf('Attachment URL %s is not redirected.', $attachment_url);
                }
            }
        }
    }
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

$post_types = get_post_types(array(
    'public' => true
));
if (isset($post_types['nav_menu_item'])) {
    unset($post_types['nav_menu_item']);
}
$all_posts = get_posts(array(
    'posts_per_page' => 100,
    'post_status' => 'publish',
    'lang' => '', // Get posts in all languages
    'post_type' => $post_types
));

$lorem_ipsum_strings = array(
    'lorem ipsum',
    'needs dreamers and the world'
);

$url_languages = array();
$default_language = false;
$default_home_url = false;
if (function_exists('pll_languages_list') && function_exists('pll_home_url') && function_exists('pll_default_language')) {
    $languages = pll_languages_list(array(
        'hide_empty' => false,
        'fields' => 'slug'
    ));

    $default_language = pll_default_language();
    foreach ($languages as $lang) {
        if ($lang == $default_language) {
            continue;
        }
        $url_languages[$lang] = pll_home_url($lang);
    }
    $default_home_url = pll_home_url($default_language);
}

$empty_pages = array();
$lorem_pages = array();
$empty_titles = array();
$invalid_slugs = array();
$invalid_languages = array();
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
    if (empty($p->post_title)) {
        $empty_titles[] = get_permalink($p) . ' (' . $p->post_type . ')';
    }
    if ($p->post_name == $p->ID || (is_numeric($p->post_name) && $p->post_title && !is_numeric($p->post_title))) {
        $invalid_slugs[] = get_permalink($p) . ' (' . $p->post_type . ')';
    }

    $post_lang = false;
    if (function_exists('pll_get_post_language') && !empty($url_languages)) {
        $post_lang = pll_get_post_language($p->ID);
    }

    if ($post_lang && isset($url_languages[$post_lang])) {
        if ($post_lang == $default_language) {
            /* Post lang has the default lang : check links containing another url*/
            foreach ($url_languages as $lang_code => $lang_url) {
                if (strpos($p->post_content, $lang_url) !== false) {
                    $invalid_languages[] = get_permalink($p) . ' (' . $p->post_type . ') links to ' . $lang_url;
                }
            }
        } else if ($post_lang != $default_language && strpos($p->post_content, $default_home_url) !== false && !strpos($p->post_content, $url_languages[$post_lang])) {
            /* Not the default lang : find all links containing the default home url but not the current lang */
            $urls_with_default_home = array();
            preg_match_all('/https?:\/\/[^\s"\']+/', $p->post_content, $matches);
            foreach ($matches[0] as $url) {
                /* Exclude invalid links */
                if (strpos($url, $default_home_url) === false || strpos($url, 'wp-') !== false) {
                    continue;
                }
                /* Links not containing the current language */
                if (strpos($url, $url_languages[$post_lang]) === false) {
                    $urls_with_default_home[] = $url;
                }
            }
            if (!empty($urls_with_default_home)) {
                $tab_url = "\n---- ";
                $invalid_languages[] = get_permalink($p) . ' (' . $p->post_type . ') contains links to posts to a wrong language: ' . $tab_url . implode($tab_url, $urls_with_default_home);
            }
        }
    }

}

if (!empty($empty_pages)) {
    $wputools_notices[] = sprintf("The following posts don't have any content: \n-- %s", implode("\n-- ", $empty_pages));
}

if (!empty($lorem_pages)) {
    $wputools_errors[] = sprintf("The following posts contains some lorem ipsum: \n-- %s", implode("\n-- ", $lorem_pages));
}

if (!empty($empty_titles)) {
    $wputools_errors[] = sprintf("The following posts don't have any title: \n-- %s", implode("\n-- ", $empty_titles));
}

if (!empty($invalid_slugs)) {
    $wputools_errors[] = sprintf("The following posts have an invalid slug: \n-- %s", implode("\n-- ", $invalid_slugs));
}

if (!empty($invalid_languages)) {
    $wputools_errors[] = sprintf("The following posts have a link to a post in another language: \n-- %s", implode("\n-- ", $invalid_languages));
}

/* ----------------------------------------------------------
  Check image sizes
---------------------------------------------------------- */

$all_image_sizes = get_intermediate_image_sizes();
$max_nb_image_sizes = (defined('WPUTOOLS_MAX_IMAGE_SIZES') && is_numeric(WPUTOOLS_MAX_IMAGE_SIZES)) ? WPUTOOLS_MAX_IMAGE_SIZES : 7;
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

    $wputools_notices[] = sprintf('There are %d images sizes, please check if they are useful : %s', $nb_all_image_sizes, $image_sizes_text);
}

/* ----------------------------------------------------------
  Check privacy policy
---------------------------------------------------------- */

$privacy_page_id = get_option('wp_page_for_privacy_policy');
if ($privacy_page_id) {
    $privacy_page = get_post($privacy_page_id);
    if ($privacy_page && $privacy_page->post_status != 'publish') {
        $wputools_errors[] = sprintf('The privacy policy page is not published.');
    }
} else {
    $wputools_errors[] = sprintf('The privacy policy page is not set.');
}
