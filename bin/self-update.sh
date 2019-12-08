#!/bin/bash

echo "# SELF-UPDATE";

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

# Reload autocomplete
. "${_SOURCEDIR}inc/autocomplete.sh";

# Back to the current dir
cd "${_CURRENT_DIR}";
