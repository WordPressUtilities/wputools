<?php

$wputools_is_cli = php_sapi_name() == 'cli';
$wputools_is_public = $wputools_is_cli || isset($_GET['from_cli']);
$wputools_errors = array();

/* ----------------------------------------------------------
  Env
---------------------------------------------------------- */

/* PHP
-------------------------- */

$phpversion = phpversion();
if (version_compare($phpversion, '7.4.10', '<')) {
    $wputools_errors[] = sprintf('PHP version %s is too old !', $phpversion);
}

/* Mail
-------------------------- */

$sentmail = mail('test@example.com', 'subject', 'message');
if (!$sentmail) {
    $wputools_errors[] = sprintf('PHP mail function doesn’t seem to work !', $phpversion);
}

/* ----------------------------------------------------------
  Test folders
---------------------------------------------------------- */

$folders = array('.', 'wp-content', 'wp-content/uploads');
foreach ($folders as $folder) {
    if (!is_dir($folder)) {
        $wputools_errors[] = sprintf('The %s folder should exist !', $folder);
        continue;
    }
    $tmp_file = $folder . '/tmp-' . uniqid();
    $file_creation = file_put_contents($tmp_file, '1');
    unlink($tmp_file);
    if (!$file_creation) {
        $wputools_errors[] = sprintf('The folder %s should be writable !', $folder);
        continue;
    }
}

/* ----------------------------------------------------------
  Test disk space
---------------------------------------------------------- */

$free_space = disk_free_space('.') / 1024 / 1024 / 1024;
if ($free_space < 20) {
    $wputools_errors[] = sprintf('There is only %sgb of disk space left on the server !', round($free_space));
}

/* ----------------------------------------------------------
  Test files
---------------------------------------------------------- */

$files = array('wp-config.php', '.htaccess');
foreach ($files as $file) {
    if (!file_exists($file)) {
        $wputools_errors[] = sprintf('The %s file should exist !', $file);
        continue;
    }
    if (!is_writable($file)) {
        $wputools_errors[] = sprintf('The file %s should be writable !', $file);
        continue;
    }
}

/* ----------------------------------------------------------
  Test functions
---------------------------------------------------------- */

$functions = array('curl_init', 'mb_strtoupper');
foreach ($functions as $function) {
    if (!function_exists($function)) {
        $wputools_errors[] = sprintf('The function %s should be available !', $function);
    }
}

/* ----------------------------------------------------------
  Test extensions
---------------------------------------------------------- */

$extensions = array_map('strtolower', get_loaded_extensions());
$required_extensions = array(
    'curl',
    'dom',
    'exif',
    'fileinfo',
    'hash',
    'mbstring',
    'openssl',
    'pcre',
    'xml',
    'zip',
    'bcmath',
    'filter',
    'gd',
    'iconv',
    'intl',
    'simplexml',
    'sodium',
    'xmlreader',
    'zlib'
);

foreach ($required_extensions as $ext) {
    if (!in_array($ext, $extensions)) {
        $wputools_errors[] = sprintf('The "%s" PHP extension should be enabled !', $ext);
    }
}

if (!in_array('gd', $extensions) && !in_array('imagick', $extensions)) {
    $wputools_errors[] = sprintf('One of the following PHP extensions should be enabled : "%s" or "%s"', 'gd', 'imagick');
}

/* ----------------------------------------------------------
  Bootstrap WP
---------------------------------------------------------- */

$bootstrap = 'wp-load.php';
while (!is_file($bootstrap)) {
    if (is_dir('..') && getcwd() != '/') {
        chdir('..');
    }
}

if (file_exists($bootstrap)) {

    /* Disable default environment */
    define('WP_USE_THEMES', false);

    /* Fix for qtranslate and other plugins */
    define('WP_ADMIN', true);
    $_SERVER['PHP_SELF'] = '/wp-admin/index.php';

    /* Include wp load */
    require_once $bootstrap;

    /* Check SAVEQUERIES */
    if ($wputools_is_cli && defined('SAVEQUERIES') && SAVEQUERIES) {
        $wputools_errors[] = 'WordPress : SAVEQUERIES should not be enabled on CLI, because it can induce some memory leaks.';
    }

    /* Check some constants */
    $php_constants = array('WP_CACHE_KEY_SALT');
    foreach ($php_constants as $constant) {
        if (!defined($constant)) {
            $wputools_errors[] = sprintf('WordPress : the constant %s should be defined.', $constant);
        }
    }

    /* Some URLs should not be publicly accessible */
    $uris = array(
        '/.git/config',
        '/wp-admin/index.php',
    );
    foreach ($uris as $uri) {
        $response_code = wp_remote_retrieve_response_code(wp_remote_get(site_url() . $uri));
        if ($response_code == 200) {
            $wputools_errors[] = sprintf('WordPress : the URI %s should not return a code 200.', site_url() . $uri);
        }
    }

}

/* ----------------------------------------------------------
  Display success or errors
---------------------------------------------------------- */

if (!$wputools_is_public) {
    echo "<pre>";
}
if (empty($wputools_errors)) {
    echo "No errors !";
} else {
    $wputools_errors = array_map(function ($i) {
        return '- ' . $i;
    }, $wputools_errors);
    echo "Errors:\n" . implode("\n", $wputools_errors);
}
if (!$wputools_is_public) {
    echo "</pre>";
}
echo "\n";
if (!$wputools_is_public) {
    echo "Dont forget to delete this file :\nrm " . basename(__FILE__);
    echo "\n";
}
