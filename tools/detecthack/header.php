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
  Extract arguments
---------------------------------------------------------- */

$tmp_wp_dir = false;
foreach ($argv as $arg) {
    if (substr($arg, 0, 6) == '--dir=') {
        $arg_dir = str_replace('--dir=', '', $arg);
        if (is_dir($arg_dir)) {
            $tmp_wp_dir = $arg_dir;
        }
    }
}

/* ----------------------------------------------------------
  Testing current directory
---------------------------------------------------------- */

if (!$tmp_wp_dir) {
    wputh_echo('Missing temporary WordPress version. Cannot continue');
    return;
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

$invalid_compared_files = array();
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

$most_suspect_files = array();
function add_to_suspect_files($f) {
    global $most_suspect_files;
    if (!isset($most_suspect_files[$f])) {
        $most_suspect_files[$f] = 0;
    }
    $most_suspect_files[$f]++;
}

/* ----------------------------------------------------------
  Parse files
---------------------------------------------------------- */

foreach ($files as $f) {
    # Ignore current file
    if ($f == $current_file) {
        continue;
    }
    # Ignore tmp directory
    if (strpos($f, $tmp_wp_dir) !== false) {
        continue;
    }
    # If test file exists : compare to it
    $tmp_f = $tmp_wp_dir . '/' . $f;
    if (file_exists($tmp_f)) {
        # Ignore file
        if (hash_file('md5', $tmp_f) == hash_file('md5', $f)) {
            continue;
        }
        # Mark as invalid WP
        else {
            $invalid_compared_files[] = $f;
            add_to_suspect_files($f);
        }
    }
    $iterator_object = wpudhk_readfile($f);
    foreach ($iterator_object as $file_line) {
        foreach ($suspect_results as $str => $func) {
            foreach ($func['tests'] as $test_string) {
                if (strpos($file_line, $test_string) !== false) {
                    $suspect_results[$str]['values'][] = $f;
                    add_to_suspect_files($f);
                }
            }
        }
    }
    line_display_clear($f);
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

if (!empty($invalid_compared_files)) {
    wputh_echo("\n" . '# These files have a different content than the original version. Maybe look at it ?');
    foreach ($invalid_compared_files as $val) {
        wputh_echo(' - ' . $val);
    }
}

/* ----------------------------------------------------------
  Most suspect files
---------------------------------------------------------- */

wputh_echo("\n" . '# These files contains the most red flags :');
natsort($most_suspect_files);
$most_suspect_files = array_reverse($most_suspect_files);
$i = 0;
foreach ($most_suspect_files as $file => $nb_flags) {
    $i++;
    wputh_echo(' - ' . $file . ' : ' . $nb_flags . ' flags.');
    if ($i > 20) {
        break;
    }
}
