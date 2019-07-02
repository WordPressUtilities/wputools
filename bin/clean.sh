#!/bin/bash

###################################
## Database
###################################

## Delete useless fields
###################################

# Delete SPAMs
wp comment delete --force $(wp comment list --status=spam --field=ID);

# Delete revisions
wp post delete --force $(wp post list --post_type='revision' --format=ids);

## Clean
###################################

wp db check;
wp db repair;
wp db optimize;

###################################
## Files
###################################

find . -name '.DS_Store' -type f -delete;
find . -name 'Thumbs.db' -type f -delete;

###################################
## Git
###################################

git gc;
