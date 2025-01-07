<?php

/* ----------------------------------------------------------
  Env
---------------------------------------------------------- */

/* Apache
-------------------------- */

if (function_exists('apache_get_modules')) {
    $apache_modules = apache_get_modules();
    $apache_modules_needed = array(
        'mod_deflate',
        'mod_expires',
        'mod_headers',
        'mod_mime',
        'mod_rewrite',
    );
    foreach ($apache_modules_needed as $module) {
        if (!in_array($module, $apache_modules)) {
            $wputools_errors[] = sprintf('The apache module “%s” is needed', $module);
        }
    }
}

/* PHP
-------------------------- */

$phpversion = phpversion();
if (version_compare($phpversion, '7.4.33', '<')) {
    $wputools_errors[] = sprintf('PHP version %s is too old !', $phpversion);
}
else if (version_compare($phpversion, '8.0', '<')) {
    $wputools_notices[] = sprintf('PHP version %s is getting old.', $phpversion);
}
