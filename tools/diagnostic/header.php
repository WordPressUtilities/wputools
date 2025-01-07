<?php

/* ----------------------------------------------------------
  Vars
---------------------------------------------------------- */

$wputools_user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
$wputools_is_cli = php_sapi_name() == 'cli';
$wputools_is_public = $wputools_is_cli || isset($_GET['from_cli']) || (strpos($wputools_user_agent, 'curl') !== false);
$wputools_errors = array();
$wputools_notices = array();

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
if (empty($wputools_errors) && empty($wputools_notices)) {
    echo "No errors !";
} else {
    foreach ($wputools_errors as $error) {
        echo "\033[31m- " . $error . "\033[0m\n"; // Red color for errors
    }
    foreach ($wputools_notices as $notice) {
        echo "\033[33m- " . $notice . "\033[0m\n"; // Yellow color for notices
    }
}
if (!$wputools_is_public) {
    echo "</pre>";
}
echo "\n";
if (!$wputools_is_public) {
    echo "Dont forget to delete this file :\nrm " . $wpudiag_file;
    echo "\n";
}
