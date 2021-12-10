<?php

$is_cli = php_sapi_name() == 'cli';
$errors = array();

/* ----------------------------------------------------------
  Test folders
---------------------------------------------------------- */

$folders = array('.', 'wp-content', 'wp-content/uploads');
foreach ($folders as $folder) {
    if (!is_dir($folder)) {
        $errors[] = sprintf('The %s folder should exist !', $folder);
        continue;
    }
    $tmp_file = $folder . '/tmp-' . uniqid();
    $file_creation = file_put_contents($tmp_file, '1');
    unlink($tmp_file);
    if (!$file_creation) {
        $errors[] = sprintf('The folder %s should be writable !', $folder);
        continue;
    }
}

/* ----------------------------------------------------------
  Test files
---------------------------------------------------------- */

$files = array('wp-config.php', '.htaccess');
foreach ($files as $file) {
    if (!file_exists($file)) {
        $errors[] = sprintf('The %s file should exist !', $file);
        continue;
    }
    if (!is_writable($file)) {
        $errors[] = sprintf('The file %s should be writable !', $file);
        continue;
    }
}

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
echo "Dont forget to delete this file :\nrm " . basename(__FILE__);
echo "\n";
