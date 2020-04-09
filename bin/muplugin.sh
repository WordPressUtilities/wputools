#!/bin/bash

echo "# INSTALL MU-PLUGIN";

_MUPLUGIN_ID="${1}";
_PLUGIN_ID="${1}";
_PLUGIN_LIST=($(cat ${_WPUTOOLS_MUPLUGIN_LIST} | tr "\n" " " ));
_IS_WPU='0';
for plugin_item in "${_PLUGIN_LIST[@]}"; do
    if [[ "${_PLUGIN_ID}" == "${plugin_item}" ]];then
        echo "${plugin_item} present in the array";
        _IS_WPU="1";
    fi;
done

if [[ "${_IS_WPU}" != '1' ]];then
    echo $(bashutilities_message "The mu-plugin \"${_MUPLUGIN_ID}\" is not a WordPressUtilities mu-plugin." 'error');
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
