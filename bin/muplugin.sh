#!/bin/bash

echo "# INSTALL MU-PLUGIN";

function wputools__muplugin(){
    local _MUPLUGIN_ID="${1}";
    local _PLUGIN_ROOT="${_CURRENT_DIR}wp-content/plugins/";
    local _MUPLUGIN_ROOT="${_CURRENT_DIR}wp-content/mu-plugins/";
    local _PLUGIN_DIR="${_PLUGIN_ROOT}${_MUPLUGIN_ID}/";
    local _MUPLUGIN_DIR="${_MUPLUGIN_ROOT}wpu/${_MUPLUGIN_ID}/";
    if [[ "${_MUPLUGIN_ID}" == '' ]];then
        bashutilities_message "No mu-plugin specified." 'error';
        return 0;
    fi

    if [[ ! -d "${_MUPLUGIN_ROOT}" ]];then
        mkdir -p "${_MUPLUGIN_ROOT}";
    fi
    if [[ ! -f "${_MUPLUGIN_ROOT}wpu_muplugin_autoloader.php" ]];then
        local _URL_AUTOLOADER="https://raw.githubusercontent.com/Darklg/WPUtilities/refs/heads/master/wp-content/mu-plugins/wpu_muplugin_autoloader.php";
        curl -o "${_MUPLUGIN_ROOT}wpu_muplugin_autoloader.php" "${_URL_AUTOLOADER}";
        git reset;
        git add "${_MUPLUGIN_ROOT}wpu_muplugin_autoloader.php";
        git commit -m "Add MU-Plugin : wpu_muplugin_autoloader";
    fi
    if [[ ! -d "${_MUPLUGIN_ROOT}wpu/" ]];then
        mkdir -p "${_MUPLUGIN_ROOT}wpu/";
    fi

    if [[ -d "${_MUPLUGIN_DIR}" || -d "${_PLUGIN_DIR}" ]];then
        bashutilities_message "The mu-plugin \"${_MUPLUGIN_ID}\" is already installed." 'error';
        return 0;
    fi

    local _MUPLUGIN_LIST=($(cat "${_WPUTOOLS_MUPLUGIN_LIST}" "${_WPUTOOLS_PLUGIN_LIST}" | tr "\n" " " ));
    local _IS_WPU='0';
    for muplugin_item in "${_MUPLUGIN_LIST[@]}"; do
        if [[ "${_MUPLUGIN_ID}" == "${muplugin_item}" ]];then
            _IS_WPU="1";
        fi;
    done

    if [[ "${_IS_WPU}" != '1' ]];then
        bashutilities_message "The mu-plugin \"${_MUPLUGIN_ID}\" is not a WordPressUtilities mu-plugin." 'error';
        return;
    fi;

    # Install plugin
    cd "${_CURRENT_DIR}wp-content/mu-plugins/wpu/";
    git submodule add --force "https://github.com/WordPressUtilities/${_MUPLUGIN_ID}.git";

    # Commit items
    cd "${_CURRENT_DIR}";
    git reset;
    git add "${_CURRENT_DIR}wp-content/mu-plugins/wpu/${_MUPLUGIN_ID}/";
    git add .gitmodules;

    # Commit
    git commit -m "Add MU-Plugin : ${_MUPLUGIN_ID}";
}

wputools__muplugin "$@";
