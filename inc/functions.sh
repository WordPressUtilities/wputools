#!/bin/bash

###################################
## TEST URLS
###################################

_wputools_test__file="";
if [[ -f "${_CURRENT_DIR}../wputools-urls.txt" ]];then
    _wputools_test__file="${_CURRENT_DIR}../wputools-urls.txt";
fi;
if [[ -f "${_CURRENT_DIR}wputools-urls.txt" ]];then
    _wputools_test__file="${_CURRENT_DIR}wputools-urls.txt";
fi;

_wputools_test_before_content='';
_wputools_test_after_content='';
_wputools_test_before_length=0;
_wputools_test_after_length=0;

function wputools_test_check_urls(){
    WPUTools cache > /dev/null;
    while read line; do
        echo "";
        echo "####################";
        echo "## Test : ${line}";
        echo "####################";
        echo "";
        wget -qO- "${line}";
    done < "${_wputools_test__file}"
}

function run_test_before(){
    if [[ -f "${_wputools_test__file}" ]];then
        echo '# RUNNING TESTS : BEFORE ACTION';
        _wputools_test_before_content=$(wputools_test_check_urls);
        _wputools_test_before_length=${#_wputools_test_before_content};
    fi;
}

function run_test_after(){
    if [[ -f "${_wputools_test__file}" ]];then
        echo '# RUNNING TESTS : AFTER ACTION';
        _wputools_test_after_content=$(wputools_test_check_urls);
        _wputools_test_after_length=${#_wputools_test_after_content};
        if [[ "${_wputools_test_before_length}" != "${_wputools_test_after_length}" ]];then
            echo "Content size has changed on tested URLs :";
            echo "- Before: ${_wputools_test_before_length}";
            echo "- After: ${_wputools_test_after_length}";
            echo "${_wputools_test_before_content}" > diff-before.txt;
            echo "${_wputools_test_after_content}" > diff-after.txt;
            if [[ -f "/usr/bin/opendiff" ]];then
                opendiff diff-before.txt diff-after.txt;
            fi;
        fi
    fi;
}

###################################
## Execute if file exists
###################################

function wputools_execute_file(){
    local _STR_FOUND="“${1}” found. Executing ...";
    if [[ -f "${1}" ]];then
        echo "${_STR_FOUND}";
        . "${1}";
        return 1;
    fi;
    if [[ -f "${_CURRENT_DIR}../${1}" ]];then
        echo "${_STR_FOUND}";
        . "${_CURRENT_DIR}../${1}";
        return 1;
    fi;
    if [[ -f "${_CURRENT_DIR}../${1}" ]];then
        echo "${_STR_FOUND}";
        . "${_CURRENT_DIR}../${1}";
        return 1;
    fi;
}

###################################
## Call URL
###################################

function wputools_call_url(){
    curl -ksL ${_EXTRA_CURL_ARGS} "${1}";
}

###################################
## WPUTools - Getters
###################################

wputools__get_db_prefix(){
    local _TMP_DB_PREFIX=$(bashutilities_search_extract_file "\$table_prefix" "';" "wp-config.php");
    local _TMP_DB_PREFIX=${_TMP_DB_PREFIX/\'/};
    local _TMP_DB_PREFIX=${_TMP_DB_PREFIX/=/};
    local _TMP_DB_PREFIX=${_TMP_DB_PREFIX/ /};
    echo "${_TMP_DB_PREFIX}";
}

###################################
## WPUTools replace old URL
###################################

function wputools_get_siteurl(){
    local _TMP_DB_NAME=$(bashutilities_search_extract_file__php_constant "DB_NAME" "wp-config.php");
    local _TMP_DB_USER=$(bashutilities_search_extract_file__php_constant "DB_USER" "wp-config.php");
    local _TMP_DB_PASSWORD=$(bashutilities_search_extract_file__php_constant "DB_PASSWORD" "wp-config.php");
    local _TMP_DB_HOST=$(bashutilities_search_extract_file__php_constant "DB_HOST" "wp-config.php");
    local _TMP_DB_PREFIX=$(wputools__get_db_prefix);
    local _OLD_URL=$( mysql --skip-column-names -u "${_TMP_DB_USER}" -p"${_TMP_DB_PASSWORD}" -h "${_TMP_DB_HOST}" -se "USE ${_TMP_DB_NAME};SELECT option_value FROM ${_TMP_DB_PREFIX}options WHERE option_name='siteurl'");
    echo "${_OLD_URL}";
}
