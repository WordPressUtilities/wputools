#!/bin/bash

echo "# UPDATE";

_ADMIN_PROTECT_FILE=$(find . -mount -name 'wputh_admin_protect.php');
_DEBUGLOG_FILE=$(find . -mount -name 'debug.log');

###################################
## Initial checks
###################################

if [ -f "${_DEBUGLOG_FILE}" ]; then
    _DEBUGLOG_FILE_SIZE=$(wc -c "${_DEBUGLOG_FILE}");
fi;

if [ -f "${_ADMIN_PROTECT_FILE}" ]; then
    echo "# Disabling Admin Protect";
    mv "${_ADMIN_PROTECT_FILE}" "${_ADMIN_PROTECT_FILE}.txt";
fi;

###################################
## Update
###################################

_PLUGIN_ID="$1";
if [[ ! -z "$_PLUGIN_ID" ]];then
    _PLUGIN_DIR="${_CURRENT_DIR}wp-content/plugins/${_PLUGIN_ID}/";

    # Check if plugin dir exists
    if [[ ! -d "${_PLUGIN_DIR}" ]];then
        echo $(bashutilities_message "The plugin \"${_PLUGIN_ID}\" does not exists" 'error');
    else
        # Reset git status
        git reset;
        # If plugin uses git : update from git
        if [[ -d "${_PLUGIN_DIR}.git" || -f "${_PLUGIN_DIR}.git" ]];then
            (cd "${_PLUGIN_DIR}" && git pull origin master);
        # Update plugin with WP-CLI
        else
            _WPCLICOMMAND plugin update "${_PLUGIN_ID}";
            _WPCLICOMMAND language plugin update "${_PLUGIN_ID}";
        fi;
        # Commit plugin update
        git add "${_PLUGIN_DIR}";
        git commit -m "Plugin update : ${_PLUGIN_ID}";
    fi;
else
    echo '# Updating WordPress core';
    _WPCLICOMMAND core update;

    echo '# Updating WordPress core translations';
    _WPCLICOMMAND language core update;

    echo '# Updating WordPress plugins';
    _WPCLICOMMAND plugin update --all;

    echo '# Updating WordPress plugins translations';
    _WPCLICOMMAND language plugin update --all;

    echo '# Updating submodules';
    git submodule foreach git pull origin master;
fi;

###################################
## Closing checks
###################################

if [ -f "${_ADMIN_PROTECT_FILE}.txt" ]; then
    echo "# Re-enabling Admin Protect";
    mv "${_ADMIN_PROTECT_FILE}.txt" "${_ADMIN_PROTECT_FILE}";
fi;

echo "# Update is over !";

if [ -f "${_DEBUGLOG_FILE}" ]; then
    _DEBUGLOG_FILE_SIZE_AFTER=$(wc -c "${_DEBUGLOG_FILE}");
    if [ "${_DEBUGLOG_FILE_SIZE}" != "${_DEBUGLOG_FILE_SIZE_AFTER}" ]; then
        echo $(bashutilities_message "Debug log seems to have changed since the update. Please look at it ?" 'warning');
        tail -3 "${_DEBUGLOG_FILE}";
    fi;
fi;

# Clear cache
WPUTools cache;
