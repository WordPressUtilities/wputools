#!/bin/bash

WPUTools(){

local _WPUTOOLS_VERSION='0.21.1';
local _PHP_VERSIONS=(7.0 7.1 7.2 7.3 7.4)
local _PHP_VERSIONS_OBSOLETES=(7.0 7.1)
local _CURRENT_DIR="$( pwd )/";
cat <<EOF

###################################
## WPU Tools v ${_WPUTOOLS_VERSION}
###################################

EOF

local _SOURCEDIR="$( dirname "${BASH_SOURCE[0]}" )/";
local _WPCLISRC="${_SOURCEDIR}wp-cli.phar";
local _TOOLSDIR="${_SOURCEDIR}tools/";
local _UPDATE_CONTROL_FILE="${_SOURCEDIR}control.txt";
local _UPDATE_CHECK_EVERY_SEC=86400;
local _HOME_URL='';
local _SITE_NAME='';
local _BACKUP_DIR='';
local _PHP_COMMAND='php';
local _HAS_WPUTOOLS_LOCAL='0';
local _WPUTOOLS_NO_UPDATE='0';
_WPUWOO_ACTION_DIR="${_TOOLSDIR}wpuwooimportexport/";
_WPUTOOLS_PLUGIN_LIST="${_TOOLSDIR}plugins.txt";
_WPUTOOLS_MUPLUGIN_LIST="${_TOOLSDIR}muplugins.txt";

if [[ -f "${_SOURCEDIR}wputools-local.sh" ]];then
    . "${_SOURCEDIR}wputools-local.sh";
fi;

_WPCLICOMMAND(){
    $_PHP_COMMAND $_WPCLISRC $@;
}

typeset -fx _WPCLICOMMAND;

###################################
## Test submodules
###################################

if [[ ! -f "${_TOOLSDIR}BashUtilities/README.md" || ! -f "${_TOOLSDIR}SecuPress-Backdoor-User/readme.txt" || ! -f "${_TOOLSDIR}wpuwooimportexport/README.md" ]]; then
    cd "${_SOURCEDIR}";
    git submodule update --init --recursive;
    cd "${_CURRENT_DIR}";
fi;

###################################
## Dependencies
###################################

. "${_TOOLSDIR}BashUtilities/modules/files.sh";
. "${_TOOLSDIR}BashUtilities/modules/messages.sh";
. "${_TOOLSDIR}BashUtilities/modules/texttransform.sh";
. "${_TOOLSDIR}BashUtilities/modules/values.sh";

###################################
## Test WP Cli
###################################

. "${_SOURCEDIR}inc/install-wpcli.sh";

###################################
## Test PHP
###################################

# Thanks to https://stackoverflow.com/a/53231244
_PHP_VERSION=$("${_PHP_COMMAND}" -v | head -n 1 | cut -d " " -f 2 | cut -f1-2 -d".");
_PHP_VERSION_OK='n';
case "${_PHP_VERSIONS[@]}" in  *"${_PHP_VERSION}"*)
    _PHP_VERSION_OK='y';
esac

if [ "${_PHP_VERSION_OK}" != 'y' ]; then
    echo $(bashutilities_message "Wrong PHP Version : ${_PHP_VERSION}" 'error');
    return 0;
fi;

case "${_PHP_VERSIONS_OBSOLETES[@]}" in  *"${_PHP_VERSION}"*)
    echo $(bashutilities_message "Your PHP Version is obsolete : ${_PHP_VERSION}" 'warning');
esac

###################################
## Autocomplete
###################################

if [[ "${_WPUTOOLS_NO_UPDATE}" != '1' ]];then
    _WPUTOOLS_NO_UPDATE='0';
fi

. "${_SOURCEDIR}inc/autocomplete.sh";
if [[ "$1" != "self-update" && "${_WPUTOOLS_NO_UPDATE}" != '1' ]];then
    . "${_SOURCEDIR}inc/check-update.sh";
fi;

###################################
## Router before
###################################

case "$1" in
    "src")
        . "${_SOURCEDIR}bin/src.sh";
        return 0;
    ;;
    "self-update")
        . "${_SOURCEDIR}bin/self-update.sh";
        return 0;
    ;;
esac

###################################
## Going to the WordPress root dir
###################################

local _WORDPRESS_FOUND='n';
local _SCRIPTSTARTDIR="$( pwd )/";

for (( c=1; c<=10; c++ )); do
    if [[ -d "wp-content" && -d "wp-includes" ]]; then
        _WORDPRESS_FOUND='y';
        break;
    else
        cd ..;
        _CURRENT_DIR="$( pwd )/";
    fi;
done

if [ "${_WORDPRESS_FOUND}" == 'n' ]; then
    cd "${_SCRIPTSTARTDIR}";
    echo $(bashutilities_message 'The script could not find a WordPress root dir' 'error');
    . "${_SOURCEDIR}inc/stop.sh";
    return 0;
fi;

if [[ -f "${_CURRENT_DIR}../wputools-local.sh" ]];then
    . "${_CURRENT_DIR}../wputools-local.sh";
    _HAS_WPUTOOLS_LOCAL='1';
fi;
if [[ -f "${_CURRENT_DIR}wputools-local.sh" ]];then
    . "${_CURRENT_DIR}wputools-local.sh";
    _HAS_WPUTOOLS_LOCAL='1';
fi;

###################################
## Not sure if WordPress is installed
###################################

case "$1" in
    "wpconfig")
        . "${_SOURCEDIR}bin/wpconfig.sh";
        return 0;
    ;;
esac

###################################
## Getting vars
###################################

if [[ -z "${_HOME_URL}" || "${_HOME_URL}" == '' ]];then
    _HOME_URL=$($_PHP_COMMAND $_WPCLISRC option get home --quiet --skip-plugins --skip-themes --skip-packages);
fi;
if [[ -z "${_SITE_NAME}" || "${_SITE_NAME}" == '' ]];then
    _SITE_NAME=$($_PHP_COMMAND $_WPCLISRC option get blogname --quiet --skip-plugins --skip-themes --skip-packages);
fi;

# Load functions
. "${_SOURCEDIR}inc/functions.sh";

###################################
## Router
###################################

case "$1" in
    "import")
        . "${_SOURCEDIR}bin/wpuwoo.sh" "import-csv" "${2}";
    ;;
    "backup" | "bduser" | "login" | "clean" | "update" | "cache" | "dbimport" | "muplugin" | "plugin" | "settings" | "wpuwoo")
        . "${_SOURCEDIR}bin/${1}.sh" "${2}" "${3}" "${4}" "${5}";
    ;;
    "wp")
        _WPCLICOMMAND "${@:2}";
    ;;
    "help" | "" | * )
        . "${_SOURCEDIR}bin/help.sh";
    ;;
esac


. "${_SOURCEDIR}inc/stop.sh";

}

WPUTools "$@";
