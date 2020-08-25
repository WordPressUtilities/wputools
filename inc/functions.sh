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
