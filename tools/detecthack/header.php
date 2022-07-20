<?php

$nbcols_cli = 0;
if (function_exists('exec')) {
    $nbcols_cli = exec('tput cols');
}
if (!is_numeric($nbcols_cli)) {
    $nbcols_cli = 99;
}

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

function wpudhk_is_php($path) {
    $handle = fopen($path, "r");
    $return = strpos(trim(fgets($handle)), '<?php');
    fclose($handle);
    return $return !== false;
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
    global $old_line, $nbcols_cli;
    if (strlen($line) > $nbcols_cli) {
        $line = substr($line, 0 - $nbcols_cli, $nbcols_cli);
    }
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
if (isset($detecthack_file)) {
    $current_file = $detecthack_file;
}
$files = wpudhk_rglob('*');
$suspect_strings = array(
    array('flags' => 20, 'string' => 'str_split(rawurldecode(str_rot13'),
    array('flags' => 20, 'string' => 'array_slice(str_split(str_repeat'),
    array('flags' => 20, 'string' => 'base64_decode("'),
    array('flags' => 20, 'string' => 'base64_decode(chr('),
    array('flags' => 20, 'string' => '${"\x'),
    array('flags' => 20, 'string' => '${"\\'),
    array('flags' => 10, 'string' => '@copy($_FILES'),
    array('flags' => 10, 'string' => 'if(copy($_FILES[\''),
    array('flags' => 10, 'string' => '\x29\\'),
    array('flags' => 10, 'string' => '"]()'),
    array('flags' => 10, 'string' => '["\x'),
    array('flags' => 10, 'string' => '\x41\x42\x43'),
    array('flags' => 10, 'string' => 'window[_'),
    array('flags' => 10, 'string' => ');@'),
    array('flags' => 10, 'string' => '@include "\\'),
    array('flags' => 10, 'string' => '@include($'),
    array('flags' => 10, 'string' => '{ goto'),
    array('flags' => 10, 'string' => 'str_rot13(@pack'),
    array('flags' => 10, 'string' => 'file_put_contents($_SERVER'),
    array('flags' => 10, 'string' => 'foreach ($_POST '),
    array('flags' => 10, 'string' => 'round(0+'),
    array('flags' => 20, 'string' => '<?php' . str_repeat(' ', 100)),
    array('flags' => 20, 'string' => '$_COOKIE, $_POST'),
    array('flags' => 20, 'string' => 'CURLOPT_TIMEOUT,round'),
    array('flags' => 20, 'string' => '@$'),
    array('flags' => 20, 'string' => 'unlink($_SERVER[\'SCRIPT_FILENAME\'])'),
    array('flags' => 50, 'string' => 'wp_create_user(\''),
    array('flags' => 50, 'string' => 'return @$'),
    array('flags' => 50, 'string' => '@eval'),
    array('flags' => 50, 'string' => 'eval ($'),
    array('flags' => 50, 'string' => 'eval(\'?>'),
    array('flags' => 50, 'string' => 'eval("?'),
    array('flags' => 50, 'string' => 'eval(str_rot13'),
    array('flags' => 50, 'string' => 'eval/*')
);

$suspect_functions = array(
    array('flags' => 1, 'string' => 'str_rot13'),
    array('flags' => 1, 'string' => 'pack'),
    array('flags' => 1, 'string' => 'gzinflate'),
    array('flags' => 1, 'string' => 'eval'),
    array('flags' => 1, 'string' => 'base64_decode')
);

/* ----------------------------------------------------------
  Build results
---------------------------------------------------------- */

$global_tests = array(
    'invalid_compared_files' => array(
        'info' => '# These files have a different content than the original version.',
        'values' => array()
    ),
    'suspect_directories_files' => array(
        'info' => '# These files are in a suspect directory.',
        'values' => array()
    ),
    'suspect_recent_files' => array(
        'info' => '# These files have been edited recently.',
        'values' => array()
    ),
    'invalid_extension' => array(
        'info' => '# These files contains PHP but have the wrong extension.',
        'values' => array()
    )
);

$start_time = time();
$compare_time = 86400 * 2;

$suspect_results = array();
foreach ($suspect_strings as $str) {
    $suspect_results[$str['string']] = array(
        'tests' => array($str['string']),
        'flags' => $str['flags'],
        'values' => array()
    );
}

foreach ($suspect_functions as $str_item) {
    $tests = array();
    $before = array(
        "",
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
            $tests[] = $before_val . $str_item['string'] . $after_val;
        }
    }
    $suspect_results[$str_item['string']] = array(
        'tests' => $tests,
        'flags' => $str_item['flags'],
        'values' => array()
    );
}

$most_suspect_files = array();
function add_to_suspect_files($f, $score_plus = 1) {
    global $most_suspect_files;
    if (!isset($most_suspect_files[$f])) {
        $most_suspect_files[$f] = 0;
    }
    $most_suspect_files[$f] += $score_plus;
}

/* ----------------------------------------------------------
  Parse files
---------------------------------------------------------- */

$authorized_file_types = array(
    'text/x-php',
    'text/plain'
);

$ignored_file_types = array(
    'directory',
    'text/troff',
    'text/html',
    'text/xml'
);

$ignored_file_main_types = array(
    'application',
    'image',
    'video',
    'font'
);

$ignored_extensions = array(
    'css',
    'js'
);

$clean_files = array();
foreach ($files as $f) {
    # Ignore current file
    if ($f == $current_file) {
        continue;
    }
    # Ignore tmp & unsupported directories
    if (strpos($f, $tmp_wp_dir) !== false || strpos($f, 'node_modules/') !== false) {
        continue;
    }
    /* Ignore some extensions */
    $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
    if (in_array($ext, $ignored_extensions)) {
        continue;
    }
    # Types detection
    $file_type = mime_content_type($f);
    $file_type_parts = explode('/', $file_type);
    # Ignore some unsupported file types
    if (in_array($file_type, $ignored_file_types) || (isset($file_type_parts[0]) && in_array($file_type_parts[0], $ignored_file_main_types))) {
        continue;
    }
    # Ignore bad mime types
    if (substr($file_type, 0, 7) == 'text/x-' && !in_array($file_type, $authorized_file_types)) {
        continue;
    }
    # Invalid extension
    $is_php = wpudhk_is_php($f);
    if ($ext != 'php' && $is_php) {
        $global_tests['invalid_extension']['values'][] = $f;
        add_to_suspect_files($f, 5);
    }
    # If PHP detected in a suspect directory
    if (strpos($f, 'wp-content/uploads') !== false && $is_php) {
        $global_tests['suspect_directories_files']['values'][] = $f;
        add_to_suspect_files($f, 10);
    }
    # If recently edited
    if ($start_time - filemtime($f) < $compare_time) {
        $global_tests['suspect_recent_files']['values'][] = $f;
        add_to_suspect_files($f, 1);
    }
    # If test file exists : compare to it
    $tmp_f = $tmp_wp_dir . '/' . $f;
    if (file_exists($tmp_f)) {
        # Ignore file
        if (hash_file('md5', $tmp_f) == hash_file('md5', $f)) {
            $clean_files[] = $f;
            continue;
        }
        # Mark as invalid WP
        else {
            $global_tests['invalid_compared_files']['values'][] = $f;
            add_to_suspect_files($f, 10);
        }
    }
    $iterator_object = wpudhk_readfile($f);
    foreach ($iterator_object as $file_line) {
        foreach ($suspect_results as $str => $func) {
            foreach ($func['tests'] as $test_string) {
                if (strpos($file_line, $test_string) !== false) {
                    $suspect_results[$str]['values'][] = $f;
                    add_to_suspect_files($f, $func['flags']);
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

foreach ($global_tests as $test_id => $var_test) {
    if (empty($var_test['values'])) {
        continue;
    }
    wputh_echo("\n" . $var_test['info']);
    foreach ($var_test['values'] as $val) {
        if (in_array($val, $clean_files)) {
            continue;
        }
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
    if (in_array($file, $clean_files)) {
        continue;
    }
    $i++;
    wputh_echo(' - ' . $file . ' : ' . $nb_flags . ' red flags.');
    if ($i > 100 && $nb_flags <= 1) {
        break;
    }
}
