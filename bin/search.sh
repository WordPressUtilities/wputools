#!/bin/bash

echo "# SEARCH";

###################################
## Initial datas
###################################

function wputools__search(){

    local _ARGS=$(wputools_convert_args_to_url "$@");

    # Use first argument as search term if no other arguments are provided
    if [[ -z "${_ARGS}" && -n "${1}" ]]; then
        _ARGS="s=${1}";
    fi;

    # Stop if no arguments provided
    if [[ -z "${_ARGS}" ]]; then
        echo "No arguments provided. Ex: wputools search mystring.";
        return 0;
    fi;

    # Create file
    local _FILE=$(wputools_create_random_file "search");
    cat "${_TOOLSDIR}search.php" > "${_CURRENT_DIR}${_FILE}";

    # Detect multisite
    wputools_select_multisite;

    # Call file
    wputools_call_url "${_HOME_URL}/${_FILE}?${_ARGS}";

    # Delete
    rm "${_CURRENT_DIR}${_FILE}";

}
wputools__search "$@";
