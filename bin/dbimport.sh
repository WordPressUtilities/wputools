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
_WPCLICOMMAND maintenance-mode activate;

# Purge DB
_WPCLICOMMAND db reset --yes;

# Import DB File
_WPCLICOMMAND db import "${_dbimport_file}";

if [[ -n "${_WPDB_REPLACE_BEFORE}" && -n "${_WPDB_REPLACE_AFTER}" ]];then
    # Search replace
    _WPCLICOMMAND search-replace "${_WPDB_REPLACE_BEFORE}" "${_WPDB_REPLACE_AFTER}";
fi;

# Check if uploads can be retrieved
uploads_dir='-';
dbimport_uploads='n';
if [[ -d "${_tmp_folder}" ]];then
    # Check if uploads are available aside backup
    dirname_file=$(dirname "${_dbimport_file}");
    uploads_dir="${dirname_file}/uploads";
    # Ask to import uploads
    if [[ "${uploads_dir}" != '-' && -d "${uploads_dir}" ]];then
        dbimport_uploads=$(bashutilities_get_yn "- Import uploads ?" 'y');
    fi
fi;

if [[ "${dbimport_uploads}" == 'y' ]];then
    # Move away current uploads
    dbimport_uploads_current="${_CURRENT_DIR}wp-content/uploads";
    dbimport_uploads_current_rand=$(bashutilities_rand_string 6);
    dbimport_uploads_current_new="${dbimport_uploads_current}-${dbimport_uploads_current_rand}";
    if [[ -d "${dbimport_uploads_current}" ]];then
        mv "${dbimport_uploads_current}" "${dbimport_uploads_current_new}";
    fi;

    # Move uploads dir to current upload path
    mv "${uploads_dir}" "${dbimport_uploads_current}";
    echo "# Uploads imported"

    # Ask to delete old uploads
    dbimport_uploads_delete_current=$(bashutilities_get_yn "- Delete old uploads ?" 'y');
    if [[ "${dbimport_uploads_delete_current}" == 'y' ]];then
        rm -rf "${dbimport_uploads_current_new}";
        echo "# Old Uploads deleted";
    fi;
fi;

# Disable maintenance mode
_WPCLICOMMAND maintenance-mode deactivate;

if [[ -d "${_tmp_folder}" ]];then
    rm -rf "${_tmp_folder}";
fi;

# Clear cache
WPUTools cache;
