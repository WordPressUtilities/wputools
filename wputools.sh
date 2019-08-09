#!/bin/bash

cat <<EOF

###################################
## WPU Tools v 0.5.0
###################################

EOF

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

###################################
## Dependencies
###################################

. "${_SOURCEDIR}/tools/BashUtilities/modules/files.sh";

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

_WORDPRESS_FOUND='n';
_SCRIPTSTARTDIR="$( pwd )/";
_CURRENT_DIR="$( pwd )/";
for (( c=1; c<=10; c++ )); do
    if [ -d "wp-content" ]; then
        _WORDPRESS_FOUND='y';
        break;
    else
        cd ..;
        _CURRENT_DIR="$( pwd )/";
    fi;
done


if [ "${_WORDPRESS_FOUND}" == 'n' ]; then
    cd "${_SCRIPTSTARTDIR}";
    echo "/!\ The script could not find a WordPress root dir /!\\";
    return 0;
fi;

###################################
## Router
###################################

case "$1" in
    "clean")
        . "${_SOURCEDIR}bin/clean.sh";
    ;;
    "bduser" | "backdoor-user")
        . "${_SOURCEDIR}bin/bduser.sh";
    ;;
    "update")
        . "${_SOURCEDIR}bin/update.sh";
    ;;
    "cache")
        . "${_SOURCEDIR}bin/cache.sh";
    ;;
    "help" | "*" | "")
        . "${_SOURCEDIR}bin/help.sh";
    ;;
esac
