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

wputools_execute_file "wputools-dbimport-before-search-replace.sh";

# Try to extract siteurl if not specified
if [[ ! -n "${_WPDB_REPLACE_BEFORE}" ]];then
    _WPDB_REPLACE_BEFORE_TMP=$(wputools_get_siteurl);
    if [[ "${_WPDB_REPLACE_BEFORE_TMP}" != '' ]];then
        use__wpdb_replace_before_tmp=$(bashutilities_get_yn "- Use '${_WPDB_REPLACE_BEFORE_TMP}' as the URL to replace ?" 'y');
        if [[ "${use__wpdb_replace_before_tmp}" == 'y' ]];then
            _WPDB_REPLACE_BEFORE=$(_WPDB_REPLACE_BEFORE_TMP);
        fi;
    fi;
fi;

if [[ -n "${_WPDB_REPLACE_BEFORE}" && -n "${_WPDB_REPLACE_AFTER}" ]];then
    # Search replace
    _WPCLICOMMAND search-replace "${_WPDB_REPLACE_BEFORE}" "${_WPDB_REPLACE_AFTER}";
fi;

wputools_execute_file "wputools-dbimport-after-search-replace.sh";

# Check if uploads can be retrieved
uploads_dir='-';
dbimport_uploads='n';
dbimport_wpulocaloverrides='n';
overrides_file='-';
if [[ -d "${_tmp_folder}" ]];then
    # Check if uploads are available aside backup
    dirname_file=$(dirname "${_dbimport_file}");
    uploads_dir="${dirname_file}/uploads";
    overrides_file="${dirname_file}/wpu_local_overrides.php";
    # Ask to import uploads
    if [[ -d "${uploads_dir}" ]];then
        dbimport_uploads=$(bashutilities_get_yn "- Import uploads ?" 'y');
    fi
    # Ask to import override
    if [[ -f "${overrides_file}" ]];then
        dbimport_wpulocaloverrides=$(bashutilities_get_yn "- Import wpu_local_overrides ?" 'y');
    fi;
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

if [[ "${dbimport_wpulocaloverrides}" == 'y' ]];then
    dbimport_muplugins_dir="${_CURRENT_DIR}wp-content/mu-plugins";
    dbimport_wpulocaloverrides_file="${dbimport_muplugins_dir}/wpu_local_overrides.php";
    # Ensure dir is available
    if [[ ! -d "${dbimport_muplugins_dir}" ]];then
        mkdir -p "${dbimport_muplugins_dir}";
    fi;

    # Ensure file does not exists
    dbimport_wpulocaloverrides_file_delete='n';
    if [[ -f "${dbimport_wpulocaloverrides_file}" ]];then
        dbimport_wpulocaloverrides_file_delete=$(bashutilities_get_yn "- Delete old wpu_local_overrides file ?" 'y');
    fi;
    if [[ "${dbimport_wpulocaloverrides_file_delete}" == 'y' ]];then
        rm "${dbimport_wpulocaloverrides_file}";
        echo "# Old wpu_local_overrides file deleted";
    fi;

    mv "${overrides_file}" "${dbimport_wpulocaloverrides_file}";
    echo "# wpu_local_overrides imported"

fi;

wputools_execute_file "wputools-dbimport-after.sh";

# Disable maintenance mode
_WPCLICOMMAND maintenance-mode deactivate;

if [[ -d "${_tmp_folder}" ]];then
    rm -rf "${_tmp_folder}";
fi;

# Clear cache
WPUTools cache;
