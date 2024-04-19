#!/bin/bash

_SOURCEDIR="$(dirname "${BASH_SOURCE[0]}" )/../";
_SCRIPTDIR=$(cd "${_SOURCEDIR}";pwd);
_SCRIPTNAME="${_SCRIPTDIR}/wputools.sh";
_CURRENT_DIR="${PWD}/";

###################################
## Check if WPUTools is installed
###################################

_check=$(type wputools);
if [[ $_check == *"wputools.sh"* ]]; then
    echo "WPUTools is already installed!";
    return 0;
fi;

###################################
## Find best file to install
###################################

_FILEINSTALL="";

if [[ -f ~/.bash_aliases ]]; then
    _FILEINSTALL=~/.bash_aliases;
fi

if [[ "${_FILEINSTALL}" == '' && -f ~/.bash_profile ]]; then
    _FILEINSTALL=~/.bash_profile;
fi

if [[ "${_FILEINSTALL}" == '' && -f ~/.bashrc ]]; then
    _FILEINSTALL=~/.bashrc;
fi

###################################
## Install
###################################

echo '- Loading dependencies';
cd "${_SOURCEDIR}";
git submodule update --init --recursive;
cd "${_CURRENT_DIR}";

alias wputools=". ${_SCRIPTNAME}";
if [[ "${_FILEINSTALL}" == '' ]];then
    echo "WPUTools can't be fully installed : no bash config file found."
    echo "Please create manually an alias for WPUTools in your bash config file."
    echo "alias wputools=\". ${_SCRIPTNAME}\"";
else
    echo '- Creating alias';
    echo "alias wputools=\". ${_SCRIPTNAME}\"" >> "${_FILEINSTALL}";
    echo "WPUTools is installed !";
fi;
