#!/bin/bash

echo "# WPU WOO";

_WPUWOO_ACTION="${1}";
_WPUWOO_ACTION_DIR="${_SOURCEDIR}tools/wpuwooimportexport/";
_WPUWOO_ACTION_FILE="${_WPUWOO_ACTION_DIR}tasks/${_WPUWOO_ACTION}.php";

if [[ ! -f "${_WPUWOO_ACTION_FILE}" ]]; then
    echo "/!\\ The action \"${_WPUWOO_ACTION}\" is not available. Sorry ! /!\\";
    echo "Available commands :";
    for file in $(find "${_WPUWOO_ACTION_DIR}tasks/" -type f -maxdepth 1)
    do
        if [[ -f "${file}" ]]; then
            _file=$(basename "${file}");
            _file=${_file%.*};
            echo "- wputools wpuwoo ${_file}";
        fi
    done
    return;
fi;

###################################
## Calling script
###################################

_PROJECT_FILE="${_CURRENT_DIR}/wputools--${_WPUWOO_ACTION}.php";
cp "${_WPUWOO_ACTION_FILE}" "${_PROJECT_FILE}";
bashutilities_sed "s#include#require_once 'wp-load.php';wp();include#" "${_PROJECT_FILE}";
bashutilities_sed "s#dirname(__FILE__) . '/../#'${_WPUWOO_ACTION_DIR}#g" "${_PROJECT_FILE}";

cd "${_CURRENT_DIR}";
php "${_PROJECT_FILE}" "${2}" "${3}";

rm "${_PROJECT_FILE}";
