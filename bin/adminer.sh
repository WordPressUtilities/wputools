#!/bin/bash

echo "# Adminer";

###################################
## Initial datas
###################################

_WPUADM_FILE=$(wputools_create_random_file "adminer")
cat "${_TOOLSDIR}adminer/adminer.php" > "${_CURRENT_DIR}${_WPUADM_FILE}";

###################################
## Information
###################################

# Customize URL
_WPUADM_URL="${_HOME_URL}/${_WPUADM_FILE}?wputools=1";

_WPUADM_DB_NAME=$(wputools__get_db_name);
if [[ -n "${_WPUADM_DB_NAME}" ]];then
    _WPUADM_URL="${_WPUADM_URL}&db=${_WPUADM_DB_NAME}";
fi;
_WPUADM_DB_USER=$(wputools__get_db_user);
if [[ -n "${_WPUADM_DB_USER}" ]];then
    _WPUADM_URL="${_WPUADM_URL}&username=${_WPUADM_DB_USER}";
fi;
_WPUADM_DB_HOST=$(wputools__get_db_host);
if [[ -n "${_WPUADM_DB_HOST}" ]];then
    _WPUADM_URL="${_WPUADM_URL}&server=${_WPUADM_DB_HOST}";
fi;

# Open in a new window if it exists
_WPUTOOLS_TEXT_MESSAGE="Please follow the link below";

if [[ -f "/usr/bin/open" ]];then
    if [[ "${_WPUADM_URL}" == http:* ]]; then
        bashutilities_message "Adminer is being served over HTTP. Automatic opening is disabled." 'warning';
    else
        _WPUTOOLS_TEXT_MESSAGE="${_WPUTOOLS_TEXT_MESSAGE} or go to the opened browser window";
        /usr/bin/open "${_WPUADM_URL}";
    fi
fi


echo "${_WPUTOOLS_TEXT_MESSAGE} to login :";
echo "${_WPUADM_URL}";

# Display password
_WPUADM_DB_PASS=$(wputools__get_db_password);
if [[ -n "${_WPUADM_DB_PASS}" ]];then
    echo "DB Password: ${_WPUADM_DB_PASS}";
fi;

echo "";
echo "###################################";
echo "## WARNING"
echo "###################################";
echo "Remember to delete the adminer file.";
echo "rm ${_WPUADM_FILE}";
echo "rm adminer-*";
