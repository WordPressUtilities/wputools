#!/bin/bash

echo "# CODE CHECKER";

###################################
## Theme
###################################

function _wputools_code_checker__dir_contains(){
    local _test_lorem=$(grep -Ril --exclude-dir=node_modules "${1}" "${2}" | xargs ls);
    echo "-> Searching files containing “${1}”";
    if [[ "${_test_lorem}" != "" ]];then
        printf "These files contains ${1} :\n${_test_lorem}\n";
    fi;
}

function _wputools_code_checker_theme(){
    local _theme_path=$(_WPCLICOMMAND theme path);
    local _theme_name=$(_WPCLICOMMAND option get stylesheet);
    local _theme_dir="${_theme_path}/${_theme_name}";
    _theme_dir="${_theme_dir/"$(pwd)"/.}";

    # Detect common errors
    _wputools_code_checker__dir_contains "lorem" "${_theme_dir}";
    _wputools_code_checker__dir_contains "'wputh'" "${_theme_dir}";
    _wputools_code_checker__dir_contains "echo get_post_meta" "${_theme_dir}";
    _wputools_code_checker__dir_contains "echo get_option" "${_theme_dir}";
    _wputools_code_checker__dir_contains "echo get_field" "${_theme_dir}";
    _wputools_code_checker__dir_contains "wp_footer(" "${_theme_dir}";

}
_wputools_code_checker_theme;

