#!/bin/bash

echo "# DEBUG FILE";

function wputools__debug_display_file() {
    _WPCLICOMMAND eval "error_log('eval_start');";
    local _debug_file=$(_WPCLICOMMAND config get "WP_DEBUG_LOG");
    if [[ ! -f "${_debug_file}" ]];then

        # check if log directory exists
        local _debug_dir=$(dirname "${_debug_file}");
        if [[ ! -d "${_debug_dir}" ]];then
            echo "-> Log directory does not exists : ${_debug_dir}";
            return;
        fi;

        echo "-> Debug file does not exists : ${_debug_file}";

        return;
    fi;
    bashutilities_message 'Start displaying debug file log' 'success';
    tail -f "${_debug_file}";
}
wputools__debug_display_file;
