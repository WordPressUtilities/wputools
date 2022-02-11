<?php

/* ----------------------------------------------------------
  Vars
---------------------------------------------------------- */

$wputools_is_cli = php_sapi_name() == 'cli';
$wputools_is_public = $wputools_is_cli || isset($_GET['from_cli']);
$wputools_errors = array();

/* ----------------------------------------------------------
  Load tests
---------------------------------------------------------- */

$included_files = glob(dirname(__FILE__) . '/inc/*.php');
natsort($included_files);
foreach ($included_files as $included_file) {
    include $included_file;
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
