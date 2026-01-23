<?php

/* ----------------------------------------------------------
  Helpers
---------------------------------------------------------- */

function wputools__mem_mb($bytes) {
    return round($bytes / 1024 / 1024, 2);
}

function wputools__snapshot($label) {
    if (php_sapi_name() !== 'cli') {
        return;
    }

    $prev = null;

    $usage = memory_get_usage(true);
    $peak = memory_get_peak_usage(true);
    $delta = $prev === null ? 0 : $usage - $prev;

    printf(
        "%-80s | usage: %7.2f MB | delta: %+6.2f MB | peak: %7.2f MB\n",
        $label,
        wputools__mem_mb($usage),
        wputools__mem_mb($delta),
        wputools__mem_mb($peak)
    );

    $prev = $usage;
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
$wp_settings_content = str_replace('do_action', 'wputools_do_action_snapshot', $wp_settings_content);
$wp_settings_content = str_replace('<?php', "<?php

if(!function_exists('wputools_do_action_snapshot')){
    function wputools_do_action_snapshot(\$label, \$arg = null) {
        do_action(\$label, \$arg);
    }
}

", $wp_settings_content);
file_put_contents($wp_settings, $wp_settings_content);

/* Create a tmp file in case of early shutdown
-------------------------- */

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
