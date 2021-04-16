#!/bin/bash

echo "# Adminer";

###################################
## Initial datas
###################################

_WPUADM_RAND=$(bashutilities_rand_string 6);
_WPUADM_FILE="adminer-${_WPUADM_RAND}.php";
_WPUADM_PATH="${_CURRENT_DIR}${_WPUADM_FILE}";

###################################
## Copy file
###################################

cp "${_TOOLSDIR}adminer/adminer.php" "${_WPUADM_PATH}";

# File will be deleted after use so lets ensure rights are ok.
chmod 0644 "${_WPUADM_PATH}";

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
_TEXT_MESSAGE="Please follow the link below";
if [[ -f "/usr/bin/open" ]];then
    _TEXT_MESSAGE="${_TEXT_MESSAGE} or go to the opened browser window";
    /usr/bin/open "${_WPUADM_URL}";
fi

echo "${_TEXT_MESSAGE} to login :";
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
