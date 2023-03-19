#!/bin/bash

echo "# DUPLICATE A MENU";

###################################
## Initial datas
###################################

function wputools__duplicate_menu(){

    local _RAND=$(bashutilities_rand_string 6);
    local _FILE="duplicatemenu-${_RAND}.php";
    local _PATH="${_CURRENT_DIR}${_FILE}";

    ###################################
    ## Copy file
    ###################################

    cp "${_TOOLSDIR}duplicatemenu.php" "${_PATH}";

    # File will be deleted after use so lets ensure rights are ok.
    chmod 0644 "${_PATH}";

    # Call file
    wputools_call_url "${_HOME_URL}/${_FILE}?menu_id=${1}";

    # Delete
    rm "${_PATH}";

}
wputools__duplicate_menu "${1}";
