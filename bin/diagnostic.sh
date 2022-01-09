#!/bin/bash

echo "# DIAGNOSTIC";

###################################
## Initial datas
###################################

_WPUDIAG_RAND=$(bashutilities_rand_string 6);
_WPUDIAG_FILE="diagnostic-${_WPUDIAG_RAND}.php";
_WPUDIAG_PATH="${_CURRENT_DIR}${_WPUDIAG_FILE}";

###################################
## Copy file
###################################

cp "${_TOOLSDIR}diagnostic.php" "${_WPUDIAG_PATH}";

# File will be deleted after use so lets ensure rights are ok.
chmod 0644 "${_WPUDIAG_PATH}";

###################################
## Launch
###################################

# Direct launch
if [[ "${1}" == 'now' ]];then
    echo "";
    echo "### Diagnostic CLI";
    $_PHP_COMMAND "${_WPUDIAG_FILE}";
    echo "### Diagnostic WEB";
    wget -qO- "${_HOME_URL}/${_WPUDIAG_FILE}";
    echo "### END";
    rm "${_WPUDIAG_FILE}";
else
    # Open in a new window if it exists
    _WPUTOOLS_TEXT_MESSAGE="Please follow the link below";
    echo "${_WPUTOOLS_TEXT_MESSAGE} :";
    echo "${_HOME_URL}/${_WPUDIAG_FILE}";
    echo "or open it via terminal :";
    echo "php ${_WPUDIAG_FILE}";
    echo "";
    echo "###################################";
    echo "## WARNING"
    echo "###################################";
    echo "Remember to delete the diagnostic file.";
    echo "rm ${_WPUDIAG_FILE}";
    echo "rm diagnostic-*";
fi;
