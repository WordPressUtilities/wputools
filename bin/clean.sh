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

# Delete orphaned post metas
wp db query "DELETE pm FROM $(wp db prefix)postmeta pm LEFT JOIN $(wp db prefix)posts wp ON wp.ID = pm.post_id WHERE wp.ID IS NULL";

# Delete locks
wp db query "DELETE FROM $(wp db prefix)postmeta WHERE meta_key IN ('_edit_lock','_edit_last')";

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
