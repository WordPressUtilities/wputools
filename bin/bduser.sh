#!/bin/bash

echo "# BACKDOOR-USER";

###################################
## Initial datas
###################################

_BD_RAND=$(bashutilities_rand_string 6);
_BD_FILE="bduser-${_BD_RAND}.php";
_BD_PATH="${_CURRENT_DIR}${_BD_FILE}";
if [ -z "${_HOME_URL}" ];then
    _HOME_URL=$($_PHP_COMMAND $_WPCLISRC option get home --quiet --skip-plugins --skip-themes --skip-packages);
fi;

###################################
## Copy file
###################################

cp "${_TOOLSDIR}SecuPress-Backdoor-User/secupress-backdoor-user.php" "${_BD_PATH}";

# Only load administrators
bashutilities_sed "s/get_users()/get_users(array('role'=>'administrator'))/g" "${_BD_PATH}";

# Default menu is login
bashutilities_sed "s/'dash';/'read';/g" "${_BD_PATH}";

# File will be deleted after use so lets ensure rights are ok.
chmod 0777 "${_BD_PATH}";

###################################
## Information
###################################

echo "Please follow the link below to use secupress-backdoor-user";
echo "${_HOME_URL}/${_BD_FILE}";

# Open in a new window if it exists
if [[ -f "/usr/bin/open" ]];then
    /usr/bin/open "${_HOME_URL}/${_BD_FILE}";
fi

unset _HOME_URL;
