#!/bin/bash

###################################
## WPU Tools v 0.1.0
###################################

_SOURCEDIR="$( dirname "${BASH_SOURCE[0]}" )/";
_WPCLISRC="${_SOURCEDIR}wp-cli.phar";

###################################
## Test WP Cli
###################################

if [ ! -f "${_WPCLISRC}" ]; then
    echo '# WP-CLI is missing';
    echo '# Installation in progress';
    curl https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar --output "${_WPCLISRC}";
    chmod +x "${_WPCLISRC}";
fi;

###################################
## Test PHP
###################################

# Thanks to https://stackoverflow.com/a/53231244
_PHP_VERSION=$(php -v | head -n 1 | cut -d " " -f 2 | cut -f1-2 -d".");
_PHP_VERSION_OK='n';
_PHP_VERSIONS=(7.0 7.1 7.2 7.3)
case "${_PHP_VERSIONS[@]}" in  *"${_PHP_VERSION}"*)
    _PHP_VERSION_OK='y';
esac

if [ "${_PHP_VERSION_OK}" != 'y' ]; then
    echo "# Wrong PHP Version : ${_PHP_VERSION}";
    return 0;
fi;


case "$1" in
    "update")
        . "${_SOURCEDIR}bin/update.sh";
    ;;
    "self-update")
        cd "${_SOURCEDIR}"; git pull;
    ;;
    "help" | "*" | "")
        . "${_SOURCEDIR}bin/help.sh";
    ;;
esac
