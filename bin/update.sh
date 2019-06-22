#!/bin/bash

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
php "${_WPCLISRC}" core update;

echo '# Updating WordPress core translations';
php "${_WPCLISRC}" language core update;

echo '# Updating WordPress plugins';
php "${_WPCLISRC}" plugin update --all;

echo '# Updating WordPress plugins translations';
php "${_WPCLISRC}" language plugin update --all;

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
        echo "Debug log seems to have changed since the update. Please look at it ?";
        tail -3 "${_DEBUGLOG_FILE}";
    fi;
fi;
