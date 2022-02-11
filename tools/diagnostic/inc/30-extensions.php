<?php

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
