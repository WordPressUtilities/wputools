#!/bin/bash

echo "# LOGIN";

# Check Install
_wputools_is_wp_installed=$(wputools_is_wp_installed);
if [[ "${_wputools_is_wp_installed}" != '' ]];then
    echo "${_wputools_is_wp_installed}";
    return 0;
fi;

###################################
## Detect multisite
###################################

wputools_select_multisite;

###################################
## Initial datas
###################################

_WPULOG_FILE=$(wputools_create_random_file "login");

###################################
## Copy file
###################################

cat "${_TOOLSDIR}login.php" > "${_CURRENT_DIR}${_WPULOG_FILE}";

###################################
## Information
###################################

_WPUTOOLS_LOGIN_URL="${_HOME_URL}/${_WPULOG_FILE}"
if [[ "${1}" != "" && "${1}" != "--quiet" ]];then
    _WPUTOOLS_LOGIN_URL="${_WPUTOOLS_LOGIN_URL}?user_id=${1}";
fi;

# Open in a new window if it exists
_WPUTOOLS_TEXT_MESSAGE="Please follow the link below";
if wputools_has_browser_available;then
    _WPUTOOLS_TEXT_MESSAGE="${_WPUTOOLS_TEXT_MESSAGE} or go to the opened browser window";
    /usr/bin/open "${_WPUTOOLS_LOGIN_URL}";
fi

echo "${_WPUTOOLS_TEXT_MESSAGE} to login :";
echo "${_WPUTOOLS_LOGIN_URL}";

# Go back to initial dir
cd "${_SCRIPTSTARTDIR}";
