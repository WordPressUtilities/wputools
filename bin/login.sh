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

wputools_select_multisite "$@";

###################################
## Initial datas
###################################

_WPULOG_FILE=$(wputools_create_random_file "login");

###################################
## Information
###################################


_WPUTOOLS_LOGIN_ID="";
_WPUTOOLS_LOGIN_URL="${_HOME_URL}/${_WPULOG_FILE}"

# Get from config
if [[ -n "${_WPUTOOLS_LOGIN_USER_ID}" ]];then
    _WPUTOOLS_LOGIN_ID="${_WPUTOOLS_LOGIN_USER_ID}";
fi;

# Get from param
if [[ "${1:0:1}" =~ [0-9] ]]; then
    _WPUTOOLS_LOGIN_ID="${1}";
fi;

# If ID is set, add it to URL
if [[ -n "${_WPUTOOLS_LOGIN_ID}" ]]; then
    if wp user get "${_WPUTOOLS_LOGIN_ID}" --field=ID >/dev/null 2>&1; then
        _WPUTOOLS_LOGIN_URL="${_WPUTOOLS_LOGIN_URL}?user_id=${_WPUTOOLS_LOGIN_ID}";
    else
        echo "User ${_WPUTOOLS_LOGIN_ID} does not exist.";
        return 0;
    fi
fi;

###################################
## Copy file
###################################

cat "${_TOOLSDIR}login.php" > "${_CURRENT_DIR}${_WPULOG_FILE}";

###################################
## Launch
###################################

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
