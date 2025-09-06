#!/bin/bash

echo "# CODE CHECKER";

###################################
## Theme
###################################

function _wputools_code_checker__dir_contains(){
    local _test_lorem=$(grep -Ril \
        --include=*.php \
        --exclude-dir=node_modules \
        --exclude-dir=twentytwentythree \
        --exclude-dir=twentytwentyfour \
        --exclude-dir=twentytwentyfive \
        --exclude-dir=wpu \
        --exclude-dir=WPUTheme \
        --exclude-dir=vendor \
        "${1}" "${2}" | xargs ls);
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

    # Common errors
    _wputools_code_checker__dir_contains "echo the_" "${1}";

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


    local _METHODS=("GET" "POST");
    local _method;
    for _method in "${_METHODS[@]}"; do
        _wputools_code_checker__dir_contains "= \$_${_method}" "${1}";
        _wputools_code_checker__dir_contains "\. \$_${_method}" "${1}";
        _wputools_code_checker__dir_contains "\.\$_${_method}" "${1}";
        _wputools_code_checker__dir_contains "echo \$_${_method}" "${1}";
    done

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
    _theme_path="${_theme_path/"$(pwd)"/.}";
    _wputools_code_checker_common_tests "${_theme_path}";
}

function _wputools_code_checker_muplugins(){
    _wputools_code_checker_common_tests "./wp-content/mu-plugins";
}


if [[ "${1}" == 'theme' ]]; then
    _wputools_code_checker_theme;
elif [[ "${1}" == 'muplugins' ]]; then
    _wputools_code_checker_muplugins;
elif [[ "${1}" == 'current' ]]; then
    _wputools_code_checker_common_tests "${_SCRIPTSTARTDIR}";
    cd "${_SCRIPTSTARTDIR}";
else
    _wputools_code_checker_theme;
    _wputools_code_checker_muplugins;
fi;
