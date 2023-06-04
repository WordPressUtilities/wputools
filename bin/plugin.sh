#!/bin/bash

echo "# INSTALL PLUGIN";

function wputools__plugin(){

    local inside_git_repo="$(git rev-parse --is-inside-work-tree 2>/dev/null)"

    local _PLUGIN_ID="${1}";
    if [[ "${_PLUGIN_ID}" == '' ]];then
        bashutilities_message "No plugin specified." 'error';
        return 0;
    fi

    local _PLUGIN_LIST=($(cat ${_WPUTOOLS_PLUGIN_LIST} | tr "\n" " " ));
    local _IS_WPU='0';
    for plugin_item in "${_PLUGIN_LIST[@]}"; do
        if [[ "${_PLUGIN_ID}" == "${plugin_item}" ]];then
            _IS_WPU="1";
        fi;
    done

    # Base vars
    local _PLUGIN_DIR="${_CURRENT_DIR}wp-content/plugins/${_PLUGIN_ID}/";


    local _force_submodule='y';
    if [[ ! "${inside_git_repo}" ]];then
        _force_submodule='n';
    fi;

    # Install plugin and activate
    if [[ "${_IS_WPU}" == '1' ]];then
        cd "${_CURRENT_DIR}wp-content/plugins/";
        bashutilities_submodule_or_install "https://github.com/WordPressUtilities/${_PLUGIN_ID}.git" "${_force_submodule}";
        cd "${_CURRENT_DIR}";
        _WPCLICOMMAND plugin activate "${1}";
    else
        _WPCLICOMMAND plugin install --activate "${1}";
    fi

    if [[ ! -d "${_PLUGIN_DIR}" ]];then
        bashutilities_message 'Plugin could not be installed' 'error';
        return 0;
    fi;

    # Add lang
    local _PLUGIN_LANG=$(_WPCLICOMMAND language core list --field=language --status=active);
    _WPCLICOMMAND language plugin install "${_PLUGIN_ID}" "${_PLUGIN_LANG}";

    if [[ "${inside_git_repo}" ]];then

        # Get infos
        local _PLUGIN_VERSION=$(_WPCLICOMMAND plugin get "${_PLUGIN_ID}" --field=version);
        local _PLUGIN_TITLE=$(_WPCLICOMMAND plugin get "${_PLUGIN_ID}" --field=title);
        local _PLUGIN_LANG_DIR="${_CURRENT_DIR}wp-content/languages/plugins/";

        # Commit items
        git reset;
        git add "${_PLUGIN_DIR}";
        if [[ -d "${_PLUGIN_LANG_DIR}" ]];then
            git add "${_PLUGIN_LANG_DIR}*";
        fi;
        if [[ -f "${_CURRENT_DIR}.gitmodules" ]];then
            git add "${_CURRENT_DIR}.gitmodules";
        fi;

        # Commit
        git commit --no-verify -m "Add Plugin : \"${_PLUGIN_TITLE}\" v ${_PLUGIN_VERSION}";
    fi;
}

wputools__plugin "$@";


