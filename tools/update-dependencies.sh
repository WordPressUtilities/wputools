#!/bin/bash

function wputools__update_dependencies(){
    local _SOURCE_DIR;
    local LATEST_RELEASE;

    _SOURCE_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && cd .. && pwd )";
    cd "${_SOURCE_DIR}";

    echo "# Updating submodules";
    git submodule foreach 'git fetch; git checkout master; git checkout main; git pull origin';

    echo "# Updating some scripts";
    LATEST_RELEASE=$(curl -s https://api.github.com/repos/vrana/adminer/releases/latest | grep "tag_name" | awk -F '"' '{print $4}');
    if [ -n "$LATEST_RELEASE" ]; then
        echo "# Downloading latest Adminer version: $LATEST_RELEASE";
        local DOWNLOAD_URL="https://github.com/vrana/adminer/releases/download/${LATEST_RELEASE}/adminer-${LATEST_RELEASE#v}-en.php";
        wget -q -O "${_SOURCE_DIR}/tools/adminer/adminer.php" "$DOWNLOAD_URL";
    fi

}
wputools__update_dependencies;
