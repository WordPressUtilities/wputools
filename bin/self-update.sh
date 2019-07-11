#!/bin/bash

cd "${_SOURCEDIR}";
git pull;
git submodule update --init --recursive;
cd "${_CURRENT_DIR}";
