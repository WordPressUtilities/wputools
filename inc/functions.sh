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
    for line in $(cat "${_wputools_test__file}")
    do
        wget -qO- "${line}";
    done
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
        fi
    fi;
}
