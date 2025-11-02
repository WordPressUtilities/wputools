#!/bin/bash

###################################
## Check existing files
###################################

if [[ "${_HAS_WPUTOOLS_LOCAL}" == '1' ]];then
    bashutilities_message "A wputools-local.sh file already exists." 'warning';
    bashutilities_message "Loaded from: ${_WPUTOOLS_LOCAL_LOADED[*]}" 'info';
fi;
if [[ "${_wputools_test__file}" != '' ]];then
    bashutilities_message "A wputools-urls.txt file already exists." 'warning';
fi;
if [[ "${_HAS_WPUTOOLS_LOCAL}" == '1' && "${_wputools_test__file}" != '' ]];then
    bashutilities_message "Main files already exists." 'error';
    if [[ $(bashutilities_get_yn "Continue anyway ?" 'y') == 'y' ]];then
        bashutilities_message "Restarting the setup..." 'info';
    else
        return 0;
    fi;
fi;
if [[ "${_HOME_URL}" == '' || "${_SITE_NAME}" == '' ]];then
    bashutilities_message "The WordPress install is not ready." 'error';
    return 0;
fi;

###################################
## Asks
###################################

_WPUTOOLS_LOCAL_PATH='-';
_WPUTOOLS_LOCAL_PATH_ASK=$(bashutilities_get_yn "- Create the config files one level over the root of your WordPress install ?" 'y');
if [[ "${_WPUTOOLS_LOCAL_PATH_ASK}" == 'y' ]];then
    _WPUTOOLS_LOCAL_PATH="${_CURRENT_DIR}../";
else
    _WPUTOOLS_LOCAL_PATH_ASK=$(bashutilities_get_yn "- Create the config files at the root of your WordPress install ?" 'y');
    if [[ "${_WPUTOOLS_LOCAL_PATH_ASK}" == 'y' ]];then
        _WPUTOOLS_LOCAL_PATH="${_CURRENT_DIR}";
    fi;
fi;

_WPUTOOLS_BACKUP_DIR="${_CURRENT_DIR}../backups/";
_WPUTOOLS_LOGS_DIR="${_CURRENT_DIR}../logs/";

###################################
## Check again
###################################

if [[ "${_WPUTOOLS_LOCAL_PATH}" == '-' ]];then
    bashutilities_message "You did not choose an install path." 'error';
    return;
fi

###################################
## Create
###################################

if [[ "${_HAS_WPUTOOLS_LOCAL}" != '1' ]];then
    wputools_use_home_url=$(bashutilities_get_yn "- Use “${_HOME_URL}” as home_url?" 'y');
    wputools_use_site_name=$(bashutilities_get_yn "- Use “${_SITE_NAME}” as site name?" 'y');
fi;

# Backup dir
if [[ -d "${_WPUTOOLS_BACKUP_DIR}" ]];then
    wputools_use_backup_dir='n';
else
    wputools_use_backup_dir=$(bashutilities_get_yn "- Create the backups folder in the parent folder ?" 'y');
fi;

# Logs dir
if [[ -d "${_WPUTOOLS_LOGS_DIR}" ]];then
    wputools_use_logs_dir='n';
else
    wputools_use_logs_dir=$(bashutilities_get_yn "- Create the logs folder in the parent folder ?" 'y');
fi;

# Extra files
for _WPUTOOLS_SETTINGS_EXTRA_FILE in "${_TOOLSDIR}dbimport-shell"/*.sh; do
    _WPUTOOLS_SETTINGS_EXTRA_FILE_NAME=$(basename "${_WPUTOOLS_SETTINGS_EXTRA_FILE}");
    if [[ ! -f "${_WPUTOOLS_LOCAL_PATH}${_WPUTOOLS_SETTINGS_EXTRA_FILE_NAME}" ]];then
        _WPUTOOLS_SETTINGS_INSTALL_EXTRA_FILE=$(bashutilities_get_yn "- Do you need ${_WPUTOOLS_SETTINGS_EXTRA_FILE_NAME} ?" 'n');
        if [[ "${_WPUTOOLS_SETTINGS_INSTALL_EXTRA_FILE}" == 'y' ]];then
            cp "${_WPUTOOLS_SETTINGS_EXTRA_FILE}" "${_WPUTOOLS_LOCAL_PATH}${_WPUTOOLS_SETTINGS_EXTRA_FILE_NAME}";
        fi;
    fi;
done

# Generate settings file
if [[ "${_HAS_WPUTOOLS_LOCAL}" != '1' ]];then
    _WPUTOOLS_LOCAL_FILE="${_WPUTOOLS_LOCAL_PATH}wputools-local.sh";
    cp "${_TOOLSDIR}wputools-local.sh" "${_WPUTOOLS_LOCAL_FILE}";
    if [[ "${wputools_use_home_url}" == 'y' ]];then
        bashutilities_sed "s#http://example.com#${_HOME_URL}#g" "${_WPUTOOLS_LOCAL_FILE}";
        bashutilities_sed "s/#_HOME_URL/_HOME_URL/g" "${_WPUTOOLS_LOCAL_FILE}";
    fi
    if [[ "${wputools_use_site_name}" == 'y' ]];then
        bashutilities_sed "s#MYSITENAME#${_SITE_NAME}#g" "${_WPUTOOLS_LOCAL_FILE}";
        bashutilities_sed "s/#_SITE_NAME/_SITE_NAME/g" "${_WPUTOOLS_LOCAL_FILE}";
    fi
    # Backup dir
    if [[ "${wputools_use_backup_dir}" == 'y' || -d "${_WPUTOOLS_BACKUP_DIR}" ]];then
        bashutilities_sed "s#MYBACKUPDIR#${_WPUTOOLS_BACKUP_DIR}#g" "${_WPUTOOLS_LOCAL_FILE}";
        bashutilities_sed "s/#_BACKUP_DIR/_BACKUP_DIR/g" "${_WPUTOOLS_LOCAL_FILE}";
        if [[ ! -d "${_WPUTOOLS_BACKUP_DIR}" ]];then
            mkdir "${_WPUTOOLS_BACKUP_DIR}";
        fi;
    fi
    if [[ "${wputools_use_logs_dir}" == 'y' && ! -d "${_WPUTOOLS_LOGS_DIR}" ]];then
        mkdir "${_WPUTOOLS_LOGS_DIR}";
    fi
    # Check for .htpasswd file
    _HTPASSWD_FILE='';
    if [[ -f "${_CURRENT_DIR}.htpasswd" ]]; then
        _HTPASSWD_FILE="${_CURRENT_DIR}.htpasswd";
    elif [[ -f "${_CURRENT_DIR}../.htpasswd" ]]; then
        _HTPASSWD_FILE="${_CURRENT_DIR}../.htpasswd";
    fi

    if [[ "${_HTPASSWD_FILE}" != '' ]]; then
        bashutilities_message "A .htpasswd file was found." 'warning';
        read -p "Enter username for .htpasswd: " _HTPASSWD_USER;
        read -p "Enter password for .htpasswd: " _HTPASSWD_PASS;
        bashutilities_sed "s/#_EXTRA_CURL_ARGS/_EXTRA_CURL_ARGS/g" "${_WPUTOOLS_LOCAL_FILE}";
        bashutilities_sed "s#user:password#${_HTPASSWD_USER}:${_HTPASSWD_PASS}#g" "${_WPUTOOLS_LOCAL_FILE}";
    fi
fi

# Generate URL file
function wputools__generate_urls(){

    local _FILE=$(wputools_create_random_file "generateurls");
    cat "${_TOOLSDIR}generateurls.php" > "${_CURRENT_DIR}${_FILE}";

    # Call file
    wputools_call_url "${_HOME_URL}/${_FILE}?file=${_WPUTOOLS_LOCAL_PATH}wputools-urls.txt";

    # Delete
    rm "${_CURRENT_DIR}${_FILE}";

}

if [[ "${_wputools_test__file}" == '' ]];then
    wputools__generate_urls;
fi;
