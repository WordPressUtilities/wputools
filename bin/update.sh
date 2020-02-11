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
