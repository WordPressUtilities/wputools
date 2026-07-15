#!/bin/bash

wputools_echo_message "# DUPLICATE MENU";

###################################
## Initial datas
###################################

function wputools__duplicate_menu(){
    local _FILE=$(wputools_create_bootstrapped_file "duplicatemenu");

    # Detect multisite
    wputools_select_multisite "$@";

    # Call file
    wputools_call_url "${_HOME_URL}/${_FILE}?menu_id=${1}";

    # Delete
    rm "${_CURRENT_DIR}${_FILE}";

}
wputools__duplicate_menu "$@";
