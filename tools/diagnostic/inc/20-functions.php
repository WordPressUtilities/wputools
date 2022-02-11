<?php

/* ----------------------------------------------------------
  Test functions
---------------------------------------------------------- */

$functions = array('curl_init', 'mb_strtoupper');
foreach ($functions as $function) {
    if (!function_exists($function)) {
        $wputools_errors[] = sprintf('The function %s should be available !', $function);
    }
}
