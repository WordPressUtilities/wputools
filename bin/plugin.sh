#!/bin/bash

echo "# INSTALL PLUGIN";

_PLUGIN_ID="${1}";

# Install plugin
cd "${_CURRENT_DIR}wp-content/plugins/";
git submodule add --force "https://github.com/WordPressUtilities/${_PLUGIN_ID}.git";

# Activate and get infos
_WPCLICOMMAND plugin activate "${1}";
_PLUGIN_VERSION=$(_WPCLICOMMAND plugin get "${_PLUGIN_ID}" --field=version);
_PLUGIN_TITLE=$(_WPCLICOMMAND plugin get "${_PLUGIN_ID}" --field=title);

# Commit items
cd "${_CURRENT_DIR}";
git reset;
git add "${_CURRENT_DIR}wp-content/plugins/${_PLUGIN_ID}/";
git add .gitmodules;

# Commit
git commit -m "Add Plugin : ${_PLUGIN_TITLE} v ${_PLUGIN_VERSION}";
