#!/bin/bash

echo "# INSTALL PLUGIN";

_PLUGIN_ID="${1}";
if [[ "${_PLUGIN_ID}" == '' ]];then
    bashutilities_message "No plugin specified." 'error';
    return 0;
fi

_PLUGIN_LIST=($(cat ${_WPUTOOLS_PLUGIN_LIST} | tr "\n" " " ));
_IS_WPU='0';
for plugin_item in "${_PLUGIN_LIST[@]}"; do
    if [[ "${_PLUGIN_ID}" == "${plugin_item}" ]];then
        _IS_WPU="1";
    fi;
done

# Install plugin and activate
if [[ "${_IS_WPU}" == '1' ]];then
    cd "${_CURRENT_DIR}wp-content/plugins/";
    git submodule add --force "https://github.com/WordPressUtilities/${_PLUGIN_ID}.git";
    cd "${_CURRENT_DIR}";
    _WPCLICOMMAND plugin activate "${1}";
else
    _WPCLICOMMAND plugin install --activate "${1}";
fi

_PLUGIN_LANG=$(_WPCLICOMMAND language core list --field=language --status=active);
_WPCLICOMMAND language plugin install "${_PLUGIN_ID}" "${_PLUGIN_LANG}";

# Get infos
_PLUGIN_VERSION=$(_WPCLICOMMAND plugin get "${_PLUGIN_ID}" --field=version);
_PLUGIN_TITLE=$(_WPCLICOMMAND plugin get "${_PLUGIN_ID}" --field=title);

# Commit items
git reset;
git add "${_CURRENT_DIR}wp-content/plugins/${_PLUGIN_ID}/";
git add "${_CURRENT_DIR}wp-content/languages/plugins/${_PLUGIN_ID}*";
git add .gitmodules;

# Commit
git commit --no-verify -m "Add Plugin : \"${_PLUGIN_TITLE}\" v ${_PLUGIN_VERSION}";
