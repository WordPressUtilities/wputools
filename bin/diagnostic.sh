#!/bin/bash

wputools_echo_message "# DIAGNOSTIC";

###################################
## Code profiler
###################################

if [[ "${1}" == 'code-profiler' ]];then
    wputools_install_plugin_folder "code-profiler-pro";
    wputools_add_files_to_excludes "wp-content/plugins/code-profiler-pro"
    wputools_add_files_to_excludes "wp-content/mu-plugins/0----code-profiler-pro.php"
    echo "Success! please go to :";
    $_PHP_COMMAND "$_WPCLISRC" eval 'echo admin_url("admin.php?page=code-profiler-pro")."\n";';
    return 0;
fi;

###################################
## Initial datas
###################################

_WPUDIAG_FILE=$(wputools_create_random_file "diagnostic");
echo "<?php \$wpudiag_file='${_WPUDIAG_FILE}';include '${_TOOLSDIR}diagnostic/header.php'; " > "${_CURRENT_DIR}${_WPUDIAG_FILE}";

###################################
## Launch
###################################

# Direct launch
if [[ "${1}" == 'now' || "${1}" == 'cli' || "${1}" == 'web' ]];then
    wputools_echo_message "";
    if [[ "${1}" == 'cli' || "${1}" == 'now' ]];then
        wputools_echo_message "### Diagnostic CLI";
        $_PHP_COMMAND "${_WPUDIAG_FILE}";
    fi;
    if [[ "${1}" == 'web' || "${1}" == 'now' ]];then
        wputools_echo_message "### Diagnostic WEB";
        wputools_call_url "${_HOME_URL}/${_WPUDIAG_FILE}";
    fi;
    wputools_echo_message "### END";
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
