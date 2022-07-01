<?php

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
  Test files which should exist and be writable
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
  Test folders which should exist and be writable
---------------------------------------------------------- */

$folders = array('wp-content', 'wp-content/uploads', 'wp-content/uploads/' . date('Y'));
foreach ($folders as $folder) {
    if (!is_writable($folder)) {
        $wputools_errors[] = sprintf('The folder %s should be writable !', $folder);
        continue;
    }
}

/* ----------------------------------------------------------
  Test files which should not exist at source
---------------------------------------------------------- */

$files = array_diff(scandir('.'), array('.', '..'));
$matches = array(
    '/^adminer(.*).php/',
    '/^cache-(.*).php/',
    '/^detecthack-(.*).php/',
    '/^diagnostic-(.*).php/',
    '/^login-(.*).php/'
);
foreach ($files as $file) {
    if (!is_file($file)) {
        continue;
    }
    if ($file == $wpudiag_file) {
        continue;
    }
    foreach ($matches as $match) {
        if (!preg_match($match, $file)) {
            continue;
        }
        $wputools_errors[] = sprintf('The file %s should not be here !', $file);
    }
}

/* ----------------------------------------------------------
  Find files which should not be in a WordPress directory
---------------------------------------------------------- */

$files = glob('{*,wp-content/*,wp-content/uploads/*}.{log,sql,zip,sql.gz}', GLOB_BRACE);
foreach ($files as $file) {
    if (!is_file($file)) {
        continue;
    }
    $wputools_errors[] = sprintf('The file %s should not be present in the WordPress directory !', $file);
}
