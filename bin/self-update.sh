#!/bin/bash

echo "# SELF-UPDATE";

_CURRENT_DIR="${PWD}/";

# Update WPUTools
cd "${_SOURCEDIR}";
bashutilities_update_repo_to_latest_main "${_SOURCEDIR}";
_WPUTOOLS_LATEST=$(git describe --tags $(git rev-list --tags --max-count=1));

if [[ "${_WPUTOOLS_VERSION}" == "${_WPUTOOLS_LATEST}" ]]; then
    echo "WPUTools was already at the latest version.";
else
    echo "Successful update from WPUTools v ${_WPUTOOLS_VERSION} to WPUTools v ${_WPUTOOLS_LATEST}";
fi;

_WPCLIVERSION_BEFORE=$(_WPCLICOMMAND cli version);
# Update WP CLI
rm wp-cli.phar;
curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar;
chmod +x wp-cli.phar;
_WPCLIVERSION_AFTER=$(_WPCLICOMMAND cli version);

if [[ "${_WPCLIVERSION_BEFORE}" == "${_WPCLIVERSION_AFTER}" ]]; then
    echo "WP-CLI was already at the latest version.";
else
    echo "Successful update from ${_WPCLIVERSION_BEFORE} to ${_WPCLIVERSION_AFTER}";
fi;


# Prune cache
if [[ -d ~/.wp-cli ]];then
    _WPCLICOMMAND cli cache prune --quiet;
fi;

# Back to the current dir
cd "${_CURRENT_DIR}";

# Mark update control file
touch "${_UPDATE_CONTROL_FILE}";

# Reload autocomplete
. "${_SOURCEDIR}inc/autocomplete.sh";
