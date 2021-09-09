#!/bin/bash

echo "# LOGIN";

###################################
## Initial datas
###################################

_WPULOG_RAND=$(bashutilities_rand_string 6);
_WPULOG_FILE="login-${_WPULOG_RAND}.php";
_WPULOG_PATH="${_CURRENT_DIR}${_WPULOG_FILE}";

###################################
## Copy file
###################################

cp "${_TOOLSDIR}login.php" "${_WPULOG_PATH}";

# File will be deleted after use so lets ensure rights are ok.
chmod 0644 "${_WPULOG_PATH}";

###################################
## Information
###################################

# Open in a new window if it exists
_WPUTOOLS_TEXT_MESSAGE="Please follow the link below";
if [[ -f "/usr/bin/open" ]];then
    _WPUTOOLS_TEXT_MESSAGE="${_WPUTOOLS_TEXT_MESSAGE} or go to the opened browser window";
    /usr/bin/open "${_HOME_URL}/${_WPULOG_FILE}";
fi

echo "${_WPUTOOLS_TEXT_MESSAGE} to login :";
echo "${_HOME_URL}/${_WPULOG_FILE}";
