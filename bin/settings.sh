#!/bin/bash

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

wputools_use_home_url=$(bashutilities_get_yn "- Use “${_HOME_URL}” as home_url?" 'y');
wputools_use_site_name=$(bashutilities_get_yn "- Use “${_SITE_NAME}” as site name?" 'y');

if [[ -d "${_WPUTOOLS_BACKUP_DIR}" ]];then
    wputools_use_backup_dir='n';
else
    wputools_use_backup_dir=$(bashutilities_get_yn "- Create the backups folder in the parent folder ?" 'y');
fi;

if [[ "${_HAS_WPUTOOLS_LOCAL}" == '1' ]];then
    bashutilities_message "A wputools-local.sh file already exists" 'warning';
else
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
    if [[ "${wputools_use_backup_dir}" == 'y' ]];then
        bashutilities_sed "s#MYBACKUPDIR#${_WPUTOOLS_BACKUP_DIR}#g" "${_WPUTOOLS_LOCAL_FILE}";
        bashutilities_sed "s/#_BACKUP_DIR/_BACKUP_DIR/g" "${_WPUTOOLS_LOCAL_FILE}";
        mkdir "${_WPUTOOLS_BACKUP_DIR}";
    fi
fi

if [[ "${_wputools_test__file}" != '' ]];then
    bashutilities_message "A wputools-urls.txt file already exists" 'warning';
else
    _WPUTOOLS_URL_LOCAL_FILE="${_WPUTOOLS_LOCAL_PATH}wputools-urls.txt";
    touch "${_WPUTOOLS_URL_LOCAL_FILE}";
    if [[ "${wputools_use_home_url}" == 'y' ]];then
        echo "${_HOME_URL}" > "${_WPUTOOLS_URL_LOCAL_FILE}";
    fi
fi
