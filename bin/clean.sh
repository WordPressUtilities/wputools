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

# Delete locks
wp db query "DELETE FROM $(wp db prefix)postmeta WHERE meta_key IN ('_edit_lock','_edit_last')";

# Delete empty terms
wp db query "DELETE FROM $(wp db prefix)terms WHERE term_id IN (SELECT term_id FROM $(wp db prefix)term_taxonomy WHERE count = 0 )";
wp db query "DELETE FROM $(wp db prefix)term_relationships WHERE term_taxonomy_id not IN (SELECT term_taxonomy_id FROM $(wp db prefix)term_taxonomy)";

# Delete orphaned post metas
wp db query "DELETE pm FROM $(wp db prefix)postmeta pm LEFT JOIN $(wp db prefix)posts wp ON wp.ID = pm.post_id WHERE wp.ID IS NULL";

# Delete orphaned term metas
wp db query "DELETE FROM $(wp db prefix)termmeta WHERE NOT EXISTS (SELECT * FROM $(wp db prefix)terms WHERE $(wp db prefix)termmeta.term_id = $(wp db prefix)terms.term_id)";

# Delete orphaned user metas
wp db query "DELETE FROM $(wp db prefix)usermeta WHERE NOT EXISTS (SELECT * FROM $(wp db prefix)users WHERE $(wp db prefix)usermeta.user_id = $(wp db prefix)users.ID)";

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
