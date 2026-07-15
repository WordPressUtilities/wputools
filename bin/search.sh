#!/bin/bash

echo "# SEARCH";

###################################
## Initial datas
###################################

function wputools__search(){

    local _ARGS=$(wputools_convert_args_to_url "$@");

    # Use first argument as search term if no other arguments are provided
    if [[ -z "${_ARGS}" && -n "${1}" ]]; then
        _ARGS="s=${*}";
        _ARGS=${_ARGS// /+};  # Replace spaces with plus signs
    fi;

    # Stop if no arguments provided
    if [[ -z "${_ARGS}" ]]; then
        echo "No arguments provided. Ex: wputools search mystring.";
        return 0;
    fi;

    # Create file with shared bootstrap injected
    local _FILE=$(wputools_create_bootstrapped_file "search");

    # Detect multisite
    wputools_select_multisite "$@";

    # Call file
    wputools_call_url "${_HOME_URL}/${_FILE}?${_ARGS}";

    # Delete
    rm "${_CURRENT_DIR}${_FILE}";

}
wputools__search "$@";
