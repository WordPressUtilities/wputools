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

_wputools_test_before_length=0;
_wputools_test_after_length=0;

function check_urls(){
    WPUTools cache > /dev/null;
    for line in $(cat "${_wputools_test__file}")
    do
        wget -qO- "${line}";
    done
}

function run_test_before(){
    if [[ -f "${_wputools_test__file}" ]];then
        echo '# RUNNING TESTS : BEFORE ACTION';
        _check_url_before=$(check_urls);
        _wputools_test_before_length=${#_check_url_before};
    fi;
}

function run_test_after(){
    if [[ -f "${_wputools_test__file}" ]];then
        echo '# RUNNING TESTS : AFTER ACTION';
        _check_url_after=$(check_urls);
        _wputools_test_after_length=${#_check_url_after};
        if [[ "${_wputools_test_before_length}" != "${_wputools_test_after_length}" ]];then
            echo "Content size has changed on tested URLs :";
            echo "- Before: ${_wputools_test_before_length}";
            echo "- After: ${_wputools_test_after_length}";
        fi
    fi;
}
