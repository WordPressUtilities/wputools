#!/bin/bash

echo "# DEBUG FILE";

function wputools_debug_display_file() {
    _WPCLICOMMAND eval "error_log('eval_start');";
    local _debug_file=$(_WPCLICOMMAND config get "WP_DEBUG_LOG");
    if [[ ! -f "${_debug_file}" ]];then
        echo "No debug file";
        return;
    fi;
    bashutilities_message 'Start displaying debug file log' 'success';
    tail -f "${_debug_file}";
}
wputools_debug_display_file;
