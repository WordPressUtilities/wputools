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

$files = array(
    array('wp-config.php', '../wp-config.php'),
    '.htaccess'
);
foreach ($files as $file) {
    if (!is_array($file)) {
        $file = array($file, $file);
    }
    if (!file_exists($file[0]) && !file_exists($file[1])) {
        $wputools_errors[] = sprintf('The %s file should exist !', $file[0]);
        continue;
    }
    if (!is_writable($file[0]) && !is_writable($file[1])) {
        $wputools_errors[] = sprintf('The file %s should be writable !', $file[0]);
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
    '/\.maintenance/',
    '/\.disable_wpu_admin_protect/',
    '/license\.txt/',
    '/README\.md/',
    '/readme\.html/',
    '/wp-links-opml\.php/',
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

/* ----------------------------------------------------------
  Find files which should not be in a Uploads directory
---------------------------------------------------------- */

if (!function_exists('glob_recursive')) {
    function glob_recursive($pattern, $flags = 0) {
        $files = glob($pattern, $flags);
        foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir) {
            $files = array_merge($files, glob_recursive($dir . '/' . basename($pattern), $flags));
        }
        return $files;
    }
}

$files = glob_recursive('wp-content/uploads/*.php', GLOB_BRACE);
$allowlist_files = array(
    'wp-content/uploads/wpucontactforms/index.php',
    'wp-content/uploads/wp-personal-data-exports/index.php'
);
foreach ($files as $file) {
    if (!is_file($file)) {
        continue;
    }
    if (in_array($file, $allowlist_files)) {
        continue;
    }
    $wputools_errors[] = sprintf('The file %s should not be present in the uploads directory !', $file);
}
