#!/bin/bash

echo "# DIAGNOSTIC";

###################################
## Code profiler
###################################

if [[ "${1}" == 'code-profiler' ]];then
    if [[ ! -d "${_TOOLSDIR}code-profiler-pro" ]];then
        echo "The code-profiler-pro folder is not available in tools/";
        return 0;
    fi;
    if [[ -d "${_CURRENT_DIR}wp-content/plugins/code-profiler-pro" ]];then
        echo "The code-profiler-pro plugin is already installed";
    else
        cp -r "${_TOOLSDIR}code-profiler-pro"  "${_CURRENT_DIR}wp-content/plugins/code-profiler-pro";
    fi;
    _WPCLICOMMAND plugin activate code-profiler-pro;
    echo "Success! please go to :";
    wp eval 'echo admin_url("admin.php?page=code-profiler-pro")."\n";';
    return 0;
fi;

###################################
## Initial datas
###################################

_WPUDIAG_RAND=$(bashutilities_rand_string 6);
_WPUDIAG_FILE="diagnostic-${_WPUDIAG_RAND}.php";
_WPUDIAG_PATH="${_CURRENT_DIR}${_WPUDIAG_FILE}";

###################################
## Copy file
###################################

echo "<?php \$wpudiag_file='${_WPUDIAG_FILE}';include '${_TOOLSDIR}diagnostic/header.php'; " > "${_WPUDIAG_PATH}";

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
    wputools_call_url "${_HOME_URL}/${_WPUDIAG_FILE}?from_cli=1";
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
