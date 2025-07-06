#!/bin/bash

function wputools_anonymize_db(){
    wputools_select_multisite;

    local _FILE=$(wputools_create_random_file "adminer");
    cat "${_TOOLSDIR}anonymizedb.php" > "${_CURRENT_DIR}${_FILE}";

    # Call file
    wputools_call_url "${_HOME_URL}/${_FILE}";

    # Delete
    rm "${_CURRENT_DIR}${_FILE}";

}
wputools_anonymize_db;
