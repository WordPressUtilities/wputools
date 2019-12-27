#!/bin/bash

echo "# CLEAN";

_db_prefix=$(php "${_WPCLISRC}" db prefix);

###################################
## Ask for a backup
###################################

read -p "Do you need a backup ? [Y,n] : " wputools_clean_need_backup
if [[ "${wputools_clean_need_backup}" != "N" && "${wputools_clean_need_backup}" != "n" ]]; then
    php "${_WPCLISRC}" db export - | gzip > ./db-$(date +%Y-%m-%d-%H%M%S).sql.gz;
fi

###################################
## Database
###################################

## Various
###################################

# Delete SPAMs
php "${_WPCLISRC}" comment delete --force $(php "${_WPCLISRC}" comment list --status=spam --field=ID);

# Unapproved comments
php "${_WPCLISRC}" db query "DELETE from ${_db_prefix}comments WHERE comment_approved = '0';";

# Delete revisions
php "${_WPCLISRC}" post delete --force $(php "${_WPCLISRC}" post list --post_type='revision' --format=ids);

# Delete locks
php "${_WPCLISRC}" db query "DELETE FROM ${_db_prefix}postmeta WHERE meta_key IN ('_edit_lock','_edit_last')";

## Terms
###################################

# Delete empty terms
php "${_WPCLISRC}" db query "DELETE FROM ${_db_prefix}terms WHERE term_id IN (SELECT term_id FROM ${_db_prefix}term_taxonomy WHERE count = 0 )";
php "${_WPCLISRC}" db query "DELETE FROM ${_db_prefix}term_taxonomy WHERE term_id not IN (SELECT term_id FROM ${_db_prefix}terms);";
php "${_WPCLISRC}" db query "DELETE FROM ${_db_prefix}term_relationships WHERE term_taxonomy_id not IN (SELECT term_taxonomy_id FROM ${_db_prefix}term_taxonomy)";

## Metas
###################################

# Delete orphaned post metas
php "${_WPCLISRC}" db query "DELETE pm FROM ${_db_prefix}postmeta pm LEFT JOIN ${_db_prefix}posts wp ON wp.ID = pm.post_id WHERE wp.ID IS NULL";

# Delete orphaned term metas
php "${_WPCLISRC}" db query "DELETE FROM ${_db_prefix}termmeta WHERE NOT EXISTS (SELECT * FROM ${_db_prefix}terms WHERE ${_db_prefix}termmeta.term_id = ${_db_prefix}terms.term_id)";

# Delete orphaned user metas
php "${_WPCLISRC}" db query "DELETE FROM ${_db_prefix}usermeta WHERE NOT EXISTS (SELECT * FROM ${_db_prefix}users WHERE ${_db_prefix}usermeta.user_id = ${_db_prefix}users.ID)";

## Clean
###################################

php "${_WPCLISRC}" db check;
php "${_WPCLISRC}" db repair;
php "${_WPCLISRC}" db optimize;

###################################
## Files
###################################

find . -name '.DS_Store' -type f -delete;
find . -name 'Thumbs.db' -type f -delete;
find . -name 'error_log' -type f -delete;

###################################
## Git
###################################

git gc;
