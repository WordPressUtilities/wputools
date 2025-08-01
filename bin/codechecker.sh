#!/bin/bash

echo "# CODE CHECKER";

###################################
## Theme
###################################

function _wputools_code_checker__dir_contains(){
    local _test_lorem=$(grep -Ril --exclude-dir=node_modules "${1}" "${2}" | xargs ls);
    echo "-> Searching files containing “${1}”";
    if [[ "${_test_lorem}" != "" ]];then
        printf "%b" "\e[1;31mThese files contains ${1} :\n${_test_lorem}\e[0m\n";
    fi;
}

# Detect common errors
function _wputools_code_checker_common_tests(){
    echo "# Checking '${1}'";

    if [[ ! -d "${1}" ]];then
        echo "Directory not found: ${1}";
        return 0;
    fi;

    # Not Clean
    _wputools_code_checker__dir_contains "><?php" "${1}";
    _wputools_code_checker__dir_contains "lorem" "${1}";

    # Bad translation
    _wputools_code_checker__dir_contains "'wputh'" "${1}";

    # Unprotected content
    _wputools_code_checker__dir_contains "echo get_post_meta" "${1}";
    _wputools_code_checker__dir_contains "echo get_option" "${1}";
    _wputools_code_checker__dir_contains "echo get_sub_field" "${1}";
    _wputools_code_checker__dir_contains "echo get_field" "${1}";
    _wputools_code_checker__dir_contains "the_field" "${1}";

    # Should not be called
    _wputools_code_checker__dir_contains "wp_footer(" "${1}";

    # Prevent SQL Injection
    _wputools_code_checker__dir_contains "\$wpdb->get_var(\"" "${1}";
    _wputools_code_checker__dir_contains "\$wpdb->get_col(\"" "${1}";
    _wputools_code_checker__dir_contains "\$wpdb->get_row(\"" "${1}";
    _wputools_code_checker__dir_contains "\$wpdb->get_results(\"" "${1}";

    # Bad practices
    # - replace by is_readable
    _wputools_code_checker__dir_contains "file_exists(" "${1}";
    # - replace by ctype_digit
    _wputools_code_checker__dir_contains "is_numeric(" "${1}";
}

function _wputools_code_checker_theme(){
    local _theme_path=$(_WPCLICOMMAND theme path);
    local _theme_name=$(_WPCLICOMMAND option get stylesheet);
    local _theme_dir="${_theme_path}/${_theme_name}";
    _theme_dir="${_theme_dir/"$(pwd)"/.}";
    _wputools_code_checker_common_tests "${_theme_dir}";
}

function _wputools_code_checker_muplugins(){
    local _theme_name=$(_WPCLICOMMAND option get stylesheet);
    local _muplugins_dir="./wp-content/mu-plugins/${_theme_name}";
    _wputools_code_checker_common_tests "${_muplugins_dir}";
}

_wputools_code_checker_theme;
_wputools_code_checker_muplugins;

