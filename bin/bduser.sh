#!/bin/bash

echo "# BACKDOOR-USER";

###################################
## Initial datas
###################################

_BD_FILE=$(wputools_create_random_file "bduser");

###################################
## Copy file
###################################

cat "${_TOOLSDIR}SecuPress-Backdoor-User/secupress-backdoor-user.php" > "${_CURRENT_DIR}${_BD_FILE}";

if [[ "${1}" != "all-users" ]];then
    # Only load administrators
    bashutilities_sed "s/get_users()/get_users(array('role'=>'administrator'))/g" "${_CURRENT_DIR}${_BD_FILE}";
fi;

# Default menu is login
bashutilities_sed "s/'dash';/'read';/g" "${_CURRENT_DIR}${_BD_FILE}";

###################################
## Information
###################################

# Open in a new window if it exists
_WPUTOOLS_TEXT_MESSAGE="Please follow the link below";
if [[ -f "/usr/bin/open" ]];then
    _WPUTOOLS_TEXT_MESSAGE="${_WPUTOOLS_TEXT_MESSAGE} or go to the opened browser window";
    /usr/bin/open "${_HOME_URL}/${_BD_FILE}";
fi

echo "${_WPUTOOLS_TEXT_MESSAGE} to use secupress-backdoor-user :";
echo "${_HOME_URL}/${_BD_FILE}";
