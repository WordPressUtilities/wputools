#!/bin/bash

echo "# GENERATE MENUS";

###################################
## Initial datas
###################################

function wputools__generate_menus(){

    local _RAND=$(bashutilities_rand_string 6);
    local _FILE="generatemenus-${_RAND}.php";
    local _PATH="${_CURRENT_DIR}${_FILE}";

    ###################################
    ## Copy file
    ###################################

    cp "${_TOOLSDIR}generatemenus.php" "${_PATH}";

    # File will be deleted after use so lets ensure rights are ok.
    chmod 0644 "${_PATH}";

    # Call file
    wputools_call_url "${_HOME_URL}/${_FILE}";

    # Delete
    rm "${_PATH}";

}
wputools__generate_menus;
