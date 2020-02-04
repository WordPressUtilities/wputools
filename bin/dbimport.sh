#!/bin/bash

echo "# DB Import";

_dbimport_file="${1}";
_tmp_folder="";

# Check DB file
if [[ ! -f "${_dbimport_file}" ]]; then
    echo $(bashutilities_message 'The file does not exists' 'error');
    return 0;
fi;

# untar in a tmp folder
if [[ "${_dbimport_file}" == *.tar.gz ]]; then
    _tmp_folder="${_dbimport_file/.tar.gz/}";
    mkdir "${_tmp_folder}";
    tar xvf "${_dbimport_file}" -C "${_tmp_folder}";
fi;

# unzip in a tmp folder
if [[ "${_dbimport_file}" == *.sql.gz ]]; then
    _tmp_folder="${_dbimport_file/.sql.gz/}";
    mkdir "${_tmp_folder}";
    gunzip -c "${_dbimport_file}" > "${_tmp_folder}/file.sql"
fi;

# Find a SQL Dump in tmp folder
if [[ -n "${_tmp_folder}" && -d "${_tmp_folder}" ]];then
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

# Force maintenance mode
php "${_WPCLISRC}" maintenance-mode activate;

# Purge DB
php "${_WPCLISRC}" db reset --yes;

# Import DB File
php "${_WPCLISRC}" db import "${_dbimport_file}";

if [[ -n "${_WPDB_REPLACE_BEFORE}" && -n "${_WPDB_REPLACE_AFTER}" ]];then
    # Search replace
    php "${_WPCLISRC}" search-replace "${_WPDB_REPLACE_BEFORE}" "${_WPDB_REPLACE_AFTER}";
fi;

# Disable maintenance mode
php "${_WPCLISRC}" maintenance-mode deactivate;

if [[ -d "${_tmp_folder}" ]];then
    rm -rf "${_tmp_folder}";
fi;

# Clear cache
WPUTools cache;
