#!/bin/bash

###################################
## Check existing iles
###################################

if [[ "${_HAS_WPUTOOLS_LOCAL}" == '1' ]];then
    bashutilities_message "A wputools-local.sh file already exists." 'warning';
fi;
if [[ "${_wputools_test__file}" != '' ]];then
    bashutilities_message "A wputools-urls.txt file already exists." 'warning';
fi;
if [[ "${_HAS_WPUTOOLS_LOCAL}" == '1' && "${_wputools_test__file}" != '' ]];then
    bashutilities_message "All files already exists." 'error';
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
    if [[ "${wputools_use_backup_dir}" == 'y' ]];then
        bashutilities_sed "s#MYBACKUPDIR#${_WPUTOOLS_BACKUP_DIR}#g" "${_WPUTOOLS_LOCAL_FILE}";
        bashutilities_sed "s/#_BACKUP_DIR/_BACKUP_DIR/g" "${_WPUTOOLS_LOCAL_FILE}";
        mkdir "${_WPUTOOLS_BACKUP_DIR}";
    fi
fi

# Generate URL file
if [[ "${_wputools_test__file}" == '' ]];then
    _WPUTOOLS_URL_LOCAL_FILE="${_WPUTOOLS_LOCAL_PATH}wputools-urls.txt";
    _WPUTOOLS_URL_LOCAL_FILE_TMP="${_WPUTOOLS_LOCAL_PATH}wputools-url-tmp.txt";
    touch "${_WPUTOOLS_URL_LOCAL_FILE_TMP}";
    touch "${_WPUTOOLS_URL_LOCAL_FILE}";
    if [[ "${wputools_use_home_url}" == 'y' ]];then
        echo "${_HOME_URL}" > "${_WPUTOOLS_URL_LOCAL_FILE_TMP}";
    fi

    # Extract all links from menus
    _menu_list=$(_WPCLICOMMAND menu list --fields=term_id --format=csv);
    for _menu_id in $_menu_list; do
        if [[ "${_menu_id}" == 'term_id' ]];then
            continue;
        fi;
        _menu_links=$(_WPCLICOMMAND menu item list "${_menu_id}" --fields=link --format=csv)
        for _menu_link in $_menu_links;do
            # Exclude invalid links
            if [[ "${_menu_link}" == 'link' ]];then
                continue;
            fi;
            # Exclude duplicate home URL
            if [[ "${wputools_use_home_url}" == 'y' ]];then
                if [[ "${_menu_link}" == "${_HOME_URL}" || "${_menu_link}" == "${_HOME_URL}/" ]];then
                    continue;
                fi;
            fi;
            # Exclude external links
            if [[ "${_menu_link}" != "${_HOME_URL}"* ]];then
                continue;
            fi;
            # Add link to file
            echo "${_menu_link}" >> "${_WPUTOOLS_URL_LOCAL_FILE_TMP}";
        done;
    done

    # Sort & deduplicate results
    # Thanks to https://unix.stackexchange.com/a/190055
    sort -u "${_WPUTOOLS_URL_LOCAL_FILE_TMP}" > "${_WPUTOOLS_URL_LOCAL_FILE}";
    rm "${_WPUTOOLS_URL_LOCAL_FILE_TMP}";
fi
