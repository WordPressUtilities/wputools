#!/bin/bash

echo "# GENERATE MENUS";

###################################
## Initial datas
###################################

function wputools__generate_menus(){

    local _ARGS=$(wputools_convert_args_to_url "$@");

    # Create file
    local _FILE=$(wputools_create_random_file "generatemenus");
    cat "${_TOOLSDIR}generatemenus.php" > "${_CURRENT_DIR}${_FILE}";

    # Detect multisite
    wputools_select_multisite "$@";

    # Call file
    wputools_call_url "${_HOME_URL}/${_FILE}?${_ARGS}";

    # Delete
    rm "${_CURRENT_DIR}${_FILE}";

}
wputools__generate_menus "$@";
