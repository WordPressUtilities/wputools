#!/bin/bash

echo "# DB Import";

_dbimport_file="${1}";

# Check DB file
if [[ ! -f "${_dbimport_file}" ]]; then
    echo $(bashutilities_message 'The file does not exists' 'error');
    return 0;
fi;

# Check DB format
if [[ "${_dbimport_file}" != *.sql ]]; then
    echo $(bashutilities_message 'The file should be an SQL dump' 'error');
    return 0;
fi;

# Purge DB
php "${_WPCLISRC}" db reset --yes;

# Import DB File
php "${_WPCLISRC}" db import "${_dbimport_file}";

# Clear cache
WPUTools cache;
