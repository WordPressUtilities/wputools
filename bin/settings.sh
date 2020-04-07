#!/bin/bash

###################################
## Check
###################################

if [[ "${_HAS_WPUTOOLS_LOCAL}" == '1' ]];then
    echo $(bashutilities_message "A wputools-local.sh file already exists" 'warning');
    return;
fi

###################################
## Asks
###################################

_WPUTOOLS_LOCAL_PATH='-';
_WPUTOOLS_LOCAL_PATH_ASK=$(bashutilities_get_yn "- Create the wputools-local.sh file one level over the root of your WordPress install ?" 'y');
if [[ "${_WPUTOOLS_LOCAL_PATH_ASK}" == 'y' ]];then
    _WPUTOOLS_LOCAL_PATH="${_CURRENT_DIR}../wputools-local.sh";
else
    _WPUTOOLS_LOCAL_PATH_ASK=$(bashutilities_get_yn "- Create the wputools-local.sh file at the root of your WordPress install ?" 'y');
    if [[ "${_WPUTOOLS_LOCAL_PATH_ASK}" == 'y' ]];then
        _WPUTOOLS_LOCAL_PATH="${_CURRENT_DIR}wputools-local.sh";
    fi;
fi;

###################################
## Check again
###################################

if [[ "${_WPUTOOLS_LOCAL_PATH}" == '-' ]];then
    echo $(bashutilities_message "You did not choose an install path." 'error');
    return;
fi

###################################
## Create
###################################

cp "${_TOOLSDIR}wputools-local.sh" "${_WPUTOOLS_LOCAL_PATH}";

wputools_use_home_url=$(bashutilities_get_yn "- Use “${_HOME_URL}” as home_url?" 'y');
if [[ "${wputools_use_home_url}" == 'y' ]];then
    bashutilities_sed "s#http://example.com#${_HOME_URL}#g" "${_WPUTOOLS_LOCAL_PATH}";
    bashutilities_sed "s/#_HOME_URL/_HOME_URL/g" "${_WPUTOOLS_LOCAL_PATH}";
fi
