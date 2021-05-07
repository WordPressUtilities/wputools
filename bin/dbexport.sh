#!/bin/bash

echo "# DB Export";

_dbexport_new_url="${1}";

###################################
## Check MySQL
###################################

if [[ ! $(_WPCLICOMMAND db check) ]];then
    bashutilities_message 'MySQL is not available' 'error';
    return 0;
fi;

if [[ "${_dbexport_new_url}" == '' ]];then
    bashutilities_message 'Missing export URL' 'error';
    return 0;
fi;

###################################
## Datas
###################################

_SITE_NAME_SLUG=$(bashutilities_string_to_slug "${_SITE_NAME}");
_SITE_NAME_SLUG="${_SITE_NAME_SLUG:0:10}";
_BACKUP_RAND=$(bashutilities_rand_string 6);
_BACKUP_NAME="${_SITE_NAME_SLUG}-$(date +%Y-%m-%d-%H%M%S)-${_BACKUP_RAND}.sql";
_BACKUP_ARCHIVE="export-${_BACKUP_NAME}.tar.gz"
_WPDB_REPLACE_BEFORE_TMP=$(wputools_get_siteurl);

###################################
## Backup
###################################

_WPCLICOMMAND search-replace "${_WPDB_REPLACE_BEFORE_TMP}" "${_dbexport_new_url}" --export="${_BACKUP_NAME}";

###################################
## Zip
###################################

tar -zcvf "${_BACKUP_ARCHIVE}" "${_BACKUP_NAME}";
rm -rf "${_BACKUP_NAME}";
