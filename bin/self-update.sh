#!/bin/bash

echo "# SELF-UPDATE";

_CURRENT_DIR="$( pwd )/";

# Update WPUTools
cd "${_SOURCEDIR}";
git pull;
git submodule update --init --recursive;
_WPUTOOLS_LATEST=$(git describe --tags $(git rev-list --tags --max-count=1));

if [[ "${_WPUTOOLS_VERSION}" == "${_WPUTOOLS_LATEST}" ]]; then
    echo "WPUTools was already at the latest version.";
else
    echo "Successful update from WPUTools v ${_WPUTOOLS_VERSION} to WPUTools v ${_WPUTOOLS_LATEST}";
fi;

# Update WP CLI
php "${_WPCLISRC}" cli update --yes;
if [[ -d ~/.wp-cli ]];then
    php "${_WPCLISRC}" cli cache prune --quiet;
fi;

# Reload autocomplete
. "${_SOURCEDIR}inc/autocomplete.sh";

# Mark update control file
touch "${_UPDATE_CONTROL_FILE}";

# Back to the current dir
cd "${_CURRENT_DIR}";
