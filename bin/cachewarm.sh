#!/bin/bash

echo "# CACHE WARMING";

function wputools_cache_warming(){
    while read line; do
        echo "## Warming : ${line}";
        curl -ksL ${_EXTRA_CURL_ARGS} "${line}" > /dev/null;
    done < "${_wputools_test__file}"
}
wputools_cache_warming;
