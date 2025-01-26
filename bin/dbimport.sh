#!/bin/bash

echo "# DB Import";

_dbimport_file="${1}";
_dbimport_file_tmp="";
_dbimport_file_isdownloaded="0";
_tmp_folder="";
_latest_backup="";

# Check Install
_wputools_is_wp_installed=$(wputools_is_wp_installed);
if [[ "${_wputools_is_wp_installed}" != '' ]];then
    echo "${_wputools_is_wp_installed}";
    return 0;
fi;

# Use a secondary env for backups
if [[ "${_dbimport_file}" == 'secondary' ]];then
    echo "Using secondary source as distant backups dir";
    _dbimport_file='latest';
    if [[ -n "${_WPDB_SSH_PORT_SECONDARY}" ]];then
        _WPDB_SSH_PORT="${_WPDB_SSH_PORT_SECONDARY}";
    fi;
    if [[ -n "${_WPDB_SSH_USER_AT_HOST_SECONDARY}" ]];then
        _WPDB_SSH_USER_AT_HOST="${_WPDB_SSH_USER_AT_HOST_SECONDARY}";
    fi;
    if [[ -n "${_WPDB_SSH_BACKUP_DIR_SECONDARY}" ]];then
        _WPDB_SSH_BACKUP_DIR="${_WPDB_SSH_BACKUP_DIR_SECONDARY}";
    fi;
fi;

wputools_execute_file "wputools-dbimport-before-all.sh" "${1}";

# Try to find the latest backup on server
if [[ "${_dbimport_file}" == 'latest' ]];then
    if [[ -z "${_WPDB_SSH_BACKUP_DIR}" || -z "${_WPDB_SSH_USER_AT_HOST}" ]]; then
        bashutilities_message 'The distant host or backup directory is not defined' 'error';
        return 0;
    fi

    if [[ "${_WPDB_SSH_PORT}" == "" ]];then
        _WPDB_SSH_PORT=22;
    fi;

    if [[ "$(wputools_is_online)" == '0' ]];then
        bashutilities_message 'You need to be online to import a distant backup' 'error';
        return 0;
    fi;

    # Find latest backup on server and copy it
    _latest_backup=$(ssh -p "${_WPDB_SSH_PORT}" "${_WPDB_SSH_USER_AT_HOST}" "ls ${_WPDB_SSH_BACKUP_DIR}* -t1 | head -n 1");

    # Copy latest file to current folder
    _dbimport_file=$(basename "${_latest_backup}");
    _dbimport_file="../${_dbimport_file}";
    _dbimport_file_tmp="${_dbimport_file}";
    if [[ -n "${_dbimport_file}" && ! -f "${_dbimport_file}" ]];then
        _dbimport_file_isdownloaded="1";
        scp -P "${_WPDB_SSH_PORT}" "${_WPDB_SSH_USER_AT_HOST}":"${_latest_backup}" "${_dbimport_file}";
    fi;

fi;

# Try to find the latest backup on a local directory
if [[ "${_dbimport_file}" == 'latestlocal' && -n "${_WPDB_BACKUP_LOCAL_DIR}" ]];then

    # Find latest backup
    _latest_backup=$(ls -t ${_WPDB_BACKUP_LOCAL_DIR}*  | head -n 1);

    # Use it if it's ok
    if [[ -f "${_latest_backup}" ]];then
        _dbimport_file="${_latest_backup}";
    fi;

fi;

# Check DB file
if [[ ! -f "${_dbimport_file}" ]]; then
    bashutilities_message 'The file does not exists' 'error';
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
    bashutilities_message 'The file should be an SQL dump' 'error';
    return 0;
fi;

# Force maintenance mode
_WPCLICOMMAND maintenance-mode activate;

# Purge DB
_WPCLICOMMAND db clean --yes;

# Import DB File
echo '- Importing db file';
_WPCLICOMMAND db import "${_dbimport_file}";

wputools_execute_file "wputools-dbimport-before-search-replace.sh";

# Try to extract siteurl if not specified
if [[ ! -n "${_WPDB_REPLACE_BEFORE}" ]];then
    _WPDB_REPLACE_BEFORE_TMP=$(wputools_get_siteurl);
    if [[ "${_WPDB_REPLACE_BEFORE_TMP}" != '' &&  "${_WPDB_REPLACE_BEFORE_TMP}" != "${_HOME_URL}" ]];then
        use__wpdb_replace_before_tmp=$(bashutilities_get_yn "- Use '${_WPDB_REPLACE_BEFORE_TMP}' as the URL to replace by '${_HOME_URL}' ?" 'y');
        if [[ "${use__wpdb_replace_before_tmp}" == 'y' ]];then
            _WPDB_REPLACE_BEFORE=${_WPDB_REPLACE_BEFORE_TMP};
            _WPDB_REPLACE_AFTER=${_HOME_URL};
        fi;
    fi;
fi;

if [[ -n "${_WPDB_REPLACE_BEFORE}" && -n "${_WPDB_REPLACE_AFTER}" ]];then
    # Search replace
    _WPCLICOMMAND search-replace "${_WPDB_REPLACE_BEFORE}" "${_WPDB_REPLACE_AFTER}"  \
        --all-tables-with-prefix \
        --skip-columns=autoload,comment_status,display_name,meta_key,option_name,ping_status,pinged,post_mime_type,post_name,post_password,post_status,post_title,post_type,to_ping,user_email,user_login \
        --skip-tables=*_actionscheduler*,*_redirection_404,*_redirection_logs,*_wpmailsmtp*,*_matomo*,*_wpunewsletter*,*_comments,*_commentmeta,*_links,*_terms,*_term_taxonomy,*_users,*_usermeta,*_log,*_wc_product*,*_wc_tax*,*_woocommerce_tax*,*_woocommerce_payment_tokenmeta;
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
    if [[ -f "${overrides_file}" && "${_WPUTOOLS_DBIMPORT_IGNORE_LOCALOVERRIDES}" == '0' ]];then
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
    dbimport_wpulocaloverrides_can_import='n';
    dbimport_wpulocaloverrides_file_delete='n';
    if [[ -f "${dbimport_wpulocaloverrides_file}" ]];then
        dbimport_wpulocaloverrides_file_delete=$(bashutilities_get_yn "- An old wpu_local_overrides file exists: override it ?" 'n');
    else
        dbimport_wpulocaloverrides_can_import='y';
    fi;
    if [[ "${dbimport_wpulocaloverrides_file_delete}" == 'y' ]];then
        rm "${dbimport_wpulocaloverrides_file}";
        echo "# Old wpu_local_overrides file deleted";
        dbimport_wpulocaloverrides_can_import='y';
    fi;

    # If import is allowed
    if [[ "${dbimport_wpulocaloverrides_can_import}" == 'y' ]];then
        mv "${overrides_file}" "${dbimport_wpulocaloverrides_file}";
        echo "# wpu_local_overrides imported"
    fi;
fi;

wputools_execute_file "wputools-dbimport-after.sh";

# Disable maintenance mode
_WPCLICOMMAND maintenance-mode deactivate;

# Delete temp folder
if [[ -d "${_tmp_folder}" ]];then
    rm -rf "${_tmp_folder}";
fi;

# Delete latest downloaded backup
if [[ "${_dbimport_file_tmp}" != '' && -f "${_dbimport_file_tmp}" ]];then
    _delete_dbimport_file_default='y';
    if [[ "${_dbimport_file_isdownloaded}" == '0' ]];then
        _delete_dbimport_file_default='n';
    fi;
    _delete_dbimport_file=$(bashutilities_get_yn "- Delete imported backup file (${_dbimport_file_tmp}) ?" "${_delete_dbimport_file_default}");
    if [[ "${_delete_dbimport_file}" == 'y' ]];then
        rm "${_dbimport_file_tmp}";
    fi;
fi;

# Clear cache
echo '- Purging cache';
wputools_call_route cache > /dev/null;

# Ask for login
_wputools_need_login=$(bashutilities_get_yn "- Do you want to login ?" "y");
if [[ "${_wputools_need_login}" == 'y' ]];then
    wputools_call_route login;
fi;
