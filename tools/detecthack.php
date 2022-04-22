<?php

/* ----------------------------------------------------------
  Helpers
---------------------------------------------------------- */

function wpudhk_readfile($path) {
    $handle = fopen($path, "r");
    while (!feof($handle)) {
        yield trim(fgets($handle));
    }
    fclose($handle);
}

/* https://stackoverflow.com/a/12109100 */
function wpudhk_rglob($pattern, $flags = 0) {
    $files = glob($pattern, $flags);
    foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir) {
        $files = array_merge($files, wpudhk_rglob($dir . '/' . basename($pattern), $flags));
    }
    return $files;
}

function wputh_echo($string) {
    echo $string . "\n";
}

$old_line = '';
function line_display_clear($line) {
    global $old_line;
    if ($old_line) {
        echo str_repeat(' ', strlen($old_line)) . "\r";
    }
    echo $line . "\r";
    $old_line = $line;
}

/* ----------------------------------------------------------
  Values
---------------------------------------------------------- */

$current_file = basename(__FILE__);
$files = wpudhk_rglob('*.php');
$suspect_strings = array(
    'x29'
);

$suspect_functions = array(
    'str_rot13',
    'pack',
    'gzinflate',
    'eval',
    'base64_decode'
);

/* ----------------------------------------------------------
  Build results
---------------------------------------------------------- */

$suspect_results = array();
foreach ($suspect_strings as $str) {
    $suspect_results[$str] = array(
        'tests' => array($str),
        'values' => array()
    );
}

foreach ($suspect_functions as $str) {
    $tests = array();
    $before = array(
        "\n",
        ' ',
        '('
    );
    $after = array(
        '(',
        ' ('
    );
    foreach ($before as $before_val) {
        foreach ($after as $after_val) {
            $tests[] = $before_val . $str . $after_val;
        }
    }
    $suspect_results[$str] = array(
        'tests' => $tests,
        'values' => array()
    );
}

/* ----------------------------------------------------------
  Parse files
---------------------------------------------------------- */

foreach ($files as $f) {
    line_display_clear($f);
    if ($f == $current_file) {
        continue;
    }
    $iterator_object = wpudhk_readfile($f);
    foreach ($iterator_object as $file_line) {
        foreach ($suspect_results as $str => $func) {
            foreach ($func['tests'] as $test_string) {
                if (strpos($file_line, $test_string) !== false) {
                    $suspect_results[$str]['values'][] = $f;
                }
            }
        }
    }
}

/* Clear after displaying all files */
line_display_clear('');

/* ----------------------------------------------------------
  Display results
---------------------------------------------------------- */

foreach ($suspect_results as $str => $files) {
    if (empty($files['values'])) {
        continue;
    }
    $files['values'] = array_unique($files['values']);
    wputh_echo("\n" . '# Detecting : "' . $str . '"');
    foreach ($files['values'] as $val) {
        wputh_echo(' - ' . $val);
    }
}
