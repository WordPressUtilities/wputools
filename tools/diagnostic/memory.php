<?php

/* ----------------------------------------------------------
  Helpers
---------------------------------------------------------- */

function wputools__mem_mb($bytes) {
    return sprintf('%0.2f', round($bytes / 1024 / 1024, 2));
}

$wputools_memory_prev = null;

function wputools__snapshot($label) {
    if (php_sapi_name() !== 'cli') {
        return;
    }

    global $wputools_memory_prev;

    $usage = memory_get_usage(true);
    $peak = memory_get_peak_usage(true);
    $delta = $wputools_memory_prev === null ? 0 : $usage - $wputools_memory_prev;

    $parts = array(
        'label' => substr(str_pad($label, 60), 0, 60),
        'usage' => 'Usage : ' . str_pad(wputools__mem_mb($usage), 6, ' ', STR_PAD_LEFT) . ' MB',
        'delta' => 'Delta : '.($delta ? (str_pad(wputools__mem_mb($delta), 6, ' ', STR_PAD_LEFT) . ' MB') : str_pad('', 9)),
        'peak' => 'Peak : ' . str_pad(wputools__mem_mb($peak), 6, ' ', STR_PAD_LEFT) . ' MB'
    );

    echo implode(' | ', $parts) . "\n";

    $wputools_memory_prev = $usage;
}

function wputools_do_action_snapshot($label, $arg = null) {
    if (is_string($arg) && file_exists($arg)) {
        $label .= " // " . basename($arg);
    }
    wputools__snapshot("Before - " . $label);
    do_action($label);
    wputools__snapshot("After  - " . $label);
}

/* ----------------------------------------------------------
  Create a temporary wp-settings.php that wraps do_action() calls
---------------------------------------------------------- */

$wp_settings = dirname(__FILE__) . '/wp-settings.php';
$wp_settings_original_content = file_get_contents($wp_settings);

/* Replace
-------------------------- */

$wp_settings_content = $wp_settings_original_content;

/* Snapshot on main hooks */
$wp_settings_content = str_replace('do_action', 'wputools_do_action_snapshot', $wp_settings_content);

/* Handle later hooks */
$hook_insert = "\$GLOBALS['wp_roles']";
$wp_settings_content = str_replace($hook_insert, "
\$wputools_memory_hooks = array(
    'parse_request',
    'send_headers',
    'parse_query',
    'pre_get_posts',
    'wp',
    'template_redirect',
    'wp_enqueue_scripts',
    'wp_head',
    'wp_footer',
    'shutdown'
);
foreach(\$wputools_memory_hooks as \$hook) {
    add_action(\$hook, function() use (\$hook) {
        wputools__snapshot('Before - ' . \$hook);
    }, -9999);
    add_action(\$hook, function() use (\$hook) {
        wputools__snapshot('After  - ' . \$hook);
    }, 9999);
}
".$hook_insert, $wp_settings_content);

/* Ensure live queries won't break */
$wp_settings_content = str_replace('<?php', "<?php
if(!function_exists('wputools_do_action_snapshot')){
    function wputools_do_action_snapshot(\$label, \$arg = null) {
        do_action(\$label, \$arg);
    }
}
if(!function_exists('wputools__snapshot')){
    function wputools__snapshot(\$label) {}
}
", $wp_settings_content);

/* Insert modified wp-settings.php */
file_put_contents($wp_settings, $wp_settings_content);

/* Create a tmp file in case of early shutdown */
$tmp_wp_settings = dirname(__FILE__) . '/wp-settings-tmp.php';
file_put_contents($tmp_wp_settings, $wp_settings_original_content);

/* ----------------------------------------------------------
  Load WordPress
---------------------------------------------------------- */

require_once 'wp-load.php';

/* ----------------------------------------------------------
  Cleanup
---------------------------------------------------------- */

file_put_contents($wp_settings, $wp_settings_original_content);
unlink($tmp_wp_settings);
