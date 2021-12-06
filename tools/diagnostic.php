<?php

$is_cli = php_sapi_name() == 'cli';
$errors = array();

/* ----------------------------------------------------------
  Test functions
---------------------------------------------------------- */

$functions = array('curl_init', 'mb_strtoupper');
foreach ($functions as $function) {
    if (!function_exists($function)) {
        $errors[] = sprintf('The function %s should be available !', $function);
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
        $errors[] = sprintf('The PHP extension %s should be enabled !', $ext);
    }
}

if (!in_array('gd', $extensions) && !in_array('imagick', $extensions)) {
    $errors[] = sprintf('One of the following PHP extensions should be enabled : %s or %s', 'gd', 'imagick');
}

/* ----------------------------------------------------------
  Display success or errors
---------------------------------------------------------- */

if (!$is_cli) {
    echo "<pre>";
}
if (empty($errors)) {
    echo "No errors !";
} else {
    echo implode("\n", $errors);
}
if (!$is_cli) {
    echo "</pre>";
}
echo "\n";
