#!/bin/bash

_SOURCEDIR="$(dirname "${BASH_SOURCE[0]}" )/../";
_SCRIPTDIR=$(cd "${_SOURCEDIR}";pwd);
_SCRIPTNAME="${_SCRIPTDIR}/wputools.sh";

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

if [[ "${_FILEINSTALL}" == '' ]];then
    echo "WPUTools could not be installed : no bash config file found."
    return 0;
fi;

###################################
## Install
###################################

echo '- Loading dependencies';
$(cd "${_SOURCEDIR}";git submodule update --init --recursive);
echo '- Creating alias';
echo "alias wputools=\". ${_SCRIPTNAME}\"" >> "${_FILEINSTALL}";

echo "WPUTools is installed !";
