#!/bin/bash

echo "# DB Import";

_dbimport_file="${1}";
_tmp_folder="";

# Check DB file
if [[ ! -f "${_dbimport_file}" ]]; then
    echo $(bashutilities_message 'The file does not exists' 'error');
    return 0;
fi;

if [[ "${_dbimport_file}" == *.tar.gz ]]; then
    # Unzip/untarr in a tmp folder
    _tmp_folder="${_dbimport_file/.tar.gz/}";
    mkdir "${_tmp_folder}";
    tar xvf "${_dbimport_file}" -C "${_tmp_folder}";

    # Find SQL Dump in tmp folder
    _tmp_dump=$(find "${_tmp_folder}" -name "*sql" -print -quit);
    if [[ -f "${_tmp_dump}" ]];then
        _dbimport_file="${_tmp_dump}";
    fi;
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

if [[ -d "${_tmp_folder}" ]];then
    rm -rf "${_tmp_folder}";
fi;

# Clear cache
WPUTools cache;
