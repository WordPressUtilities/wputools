#!/bin/bash

echo "# INSTALL MU-PLUGIN";

_MUPLUGIN_ID="${1}";

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
