<?php

/* ----------------------------------------------------------
  Helpers
---------------------------------------------------------- */

define('WPUTOOLS_RAM_COL_WIDTH', 10);
define('WPUTOOLS_TEXT_COL_WIDTH', 60);

function wputools_cli_table_thead($columns, $separator = ' | ') {
    $line_width = array_sum($columns) + (count($columns) - 1) * strlen($separator);
    $parts = array();
    foreach ($columns as $col_name => $col_width) {
        $parts[] = substr(str_pad($col_name, $col_width, ' ', $col_width > 15 ? STR_PAD_RIGHT : STR_PAD_BOTH), 0, $col_width);
    }
    echo str_repeat('-', $line_width) . "\n";
    echo implode($separator, $parts) . "\n";
    echo str_repeat('-', $line_width) . "\n";
}

function wputools_cli_table_tr($values, $separator = ' | ') {
    echo implode($separator, $values) . "\n";
}

/* Values
-------------------------- */

function wputools__mem_mb($bytes) {
    return sprintf('%0.2f', round($bytes / 1024 / 1024, 2));
}

function wputools_mem_pad($value) {
    $suffix = ' MB';
    $suffix_length = strlen($suffix);
    return $value ? str_pad(wputools__mem_mb($value), WPUTOOLS_RAM_COL_WIDTH - $suffix_length, ' ', STR_PAD_LEFT) . ' MB' : str_pad('', WPUTOOLS_RAM_COL_WIDTH);
}

function wputools_text_pad($value) {
    return substr(str_pad($value, WPUTOOLS_TEXT_COL_WIDTH), 0, WPUTOOLS_TEXT_COL_WIDTH);
}

/* Snapshot
-------------------------- */

$wputools_memory_prev = null;
$wputools_microtime_start = microtime(true);
function wputools__snapshot($label, $empty = false) {
    if (php_sapi_name() !== 'cli') {
        return;
    }

    if ($empty) {
        echo wputools_cli_table_thead(array(
            'Hook' => WPUTOOLS_TEXT_COL_WIDTH,
            'Time' => WPUTOOLS_RAM_COL_WIDTH,
            'Usage' => WPUTOOLS_RAM_COL_WIDTH,
            'Delta' => WPUTOOLS_RAM_COL_WIDTH,
            'Peak' => WPUTOOLS_RAM_COL_WIDTH,
        ));
        return;
    }

    global $wputools_memory_prev, $wputools_microtime_start;

    $usage = memory_get_usage(true);
    $delta = $wputools_memory_prev === null ? 0 : $usage - $wputools_memory_prev;

    echo wputools_cli_table_tr(array(
        'label' => wputools_text_pad($label),
        'time' => str_pad(sprintf('%0.2f sec', microtime(true) - $wputools_microtime_start), WPUTOOLS_RAM_COL_WIDTH, ' ', STR_PAD_RIGHT),
        'usage' => wputools_mem_pad($usage),
        'delta' => wputools_mem_pad($delta),
        'peak' => wputools_mem_pad(memory_get_peak_usage(true)),
    ));

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
wputools__snapshot('Start',true);
", $wp_settings_content);

/* Additional snapshots */
$strings_to_find = array(
    'wp_check_php_mysql_versions()',
    'wp_initial_constants()',
    'wp_debug_mode()',
    'wp_start_object_cache()',
    'wp_not_installed()',
    'wp_plugin_directory_constants()',
);

foreach ($strings_to_find as $string_to_find) {
    $wp_settings_content = str_replace($string_to_find, "
wputools__snapshot('Check : " . trim($string_to_find) . "');
" . $string_to_find, $wp_settings_content);
}

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
add_action('shutdown', function() {
    wputools__snapshot('End', true);
}, 10000);

" . $hook_insert, $wp_settings_content);

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
