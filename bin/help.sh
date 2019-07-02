#!/bin/bash

cat <<EOF
# HELP

## Clean WordPress path & files
wputools clean;

## Update your WordPress core and plugins.
wputools update;

## Update this tool
wputools self-update;
EOF

