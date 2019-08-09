#!/bin/bash

echo "# SELF-UPDATE";

cd "${_SOURCEDIR}";
git pull;
git submodule update --init --recursive;
php "${_WPCLISRC}" cli update --yes;
cd "${_CURRENT_DIR}";
