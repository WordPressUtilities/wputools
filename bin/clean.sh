#!/bin/bash

echo "# CLEAN";

_db_prefix=$(_WPCLICOMMAND db prefix);

###################################
## Ask for a backup
###################################

wputools_clean_need_backup=$(bashutilities_get_yn "- Do you need a backup?" 'y');
if [[ "${wputools_clean_need_backup}" == "y" ]]; then
    . "${_SOURCEDIR}bin/backup.sh" -u=n;
fi

###################################
## Database
###################################

## Various
###################################

# Delete SPAMs
_WPCLICOMMAND comment delete --force $(_WPCLICOMMAND comment list --status=spam --field=ID);

# Unapproved comments
_WPCLICOMMAND db query "DELETE from ${_db_prefix}comments WHERE comment_approved = '0';";

# Delete revisions
_WPCLICOMMAND post delete --force $(_WPCLICOMMAND post list --post_type='revision' --format=ids);

# Delete locks
_WPCLICOMMAND db query "DELETE FROM ${_db_prefix}postmeta WHERE meta_key IN ('_edit_lock','_edit_last')";

## Terms
###################################

# Delete empty terms
_WPCLICOMMAND db query "DELETE FROM ${_db_prefix}terms WHERE term_id IN (SELECT term_id FROM ${_db_prefix}term_taxonomy WHERE count = 0 )";
_WPCLICOMMAND db query "DELETE FROM ${_db_prefix}term_taxonomy WHERE term_id not IN (SELECT term_id FROM ${_db_prefix}terms);";
_WPCLICOMMAND db query "DELETE FROM ${_db_prefix}term_relationships WHERE term_taxonomy_id not IN (SELECT term_taxonomy_id FROM ${_db_prefix}term_taxonomy)";

## Metas
###################################

# Delete orphaned post metas
_WPCLICOMMAND db query "DELETE pm FROM ${_db_prefix}postmeta pm LEFT JOIN ${_db_prefix}posts wp ON wp.ID = pm.post_id WHERE wp.ID IS NULL";

# Delete orphaned term metas
_WPCLICOMMAND db query "DELETE FROM ${_db_prefix}termmeta WHERE NOT EXISTS (SELECT * FROM ${_db_prefix}terms WHERE ${_db_prefix}termmeta.term_id = ${_db_prefix}terms.term_id)";

# Delete orphaned user metas
_WPCLICOMMAND db query "DELETE FROM ${_db_prefix}usermeta WHERE NOT EXISTS (SELECT * FROM ${_db_prefix}users WHERE ${_db_prefix}usermeta.user_id = ${_db_prefix}users.ID)";

# Delete orphaned comment metas
_WPCLICOMMAND db query "DELETE FROM ${_db_prefix}commentmeta WHERE NOT EXISTS (SELECT * FROM ${_db_prefix}comments WHERE ${_db_prefix}commentmeta.comment_id = ${_db_prefix}comments.comment_ID)";

## Clean
###################################

_WPCLICOMMAND db check;
_WPCLICOMMAND db repair;
_WPCLICOMMAND db optimize;

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
