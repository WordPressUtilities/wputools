#!/bin/bash

echo "# CACHE WARMING";

if [[ "${_wputools_test__file}" == '' ]];then
    bashutilities_message "No wputools-urls.txt file found." 'error';
    return;
fi;

function wputools_cache_warming(){
    while read line; do
        echo "## Warming : ${line}";
        wputools_call_url "${line}" > /dev/null;
    done < "${_wputools_test__file}"
}
wputools_cache_warming;
