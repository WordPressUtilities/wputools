#!/bin/bash

# Arguments for CURL if the site is password protected
#_EXTRA_CURL_ARGS="-u user:password";

# Static home URL if needed
#_HOME_URL="http://example.com";

# Site name if needed
#_SITE_NAME="MYSITENAME";

# URL replace format to trigger a search-replace after dbimport
#_WPDB_REPLACE_BEFORE="http://example-before.com";
#_WPDB_REPLACE_AFTER="http://example.com";

# Distant server settings to retrieve latest backup
#_WPDB_SSH_USER_AT_HOST="user@host";
#_WPDB_SSH_BACKUP_DIR="~/backups/";

# BACKUP DIRECTORY
#_BACKUP_DIR="MYBACKUPDIR";

# DISABLE BACKUP FOR UPLOADS
#_BACKUP_UPLOADS="n";

# IGNORE LOCALOVERRIDES WHEN IMPORTING
#_WPUTOOLS_DBIMPORT_IGNORE_LOCALOVERRIDES='1';

# DISABLE BACKUP FOR CRONTAB
#_NOBACKUP_CRONTABS="1";

# CORE UPDATE TYPE
#_WPUTOOLS_CORE_UPDATE_TYPE="major";

# EXTRA CACHE DIRECTORIES
#_EXTRA_CACHE_DIRS=(wp-content/uploads/wpufilecache)

# Override WP-CLI Version or PHP binary
#_PHP_COMMAND='/Applications/MAMP/bin/php/php5.4.45/bin/php';
#_WPCLICOMMAND(){
#    $_PHP_COMMAND $_WPCLISRC $@;
#}

# Clean some folders before backup with uploads
#function wputools_backup_uploads_cleanup(){
#    rm -rf "${1}/wpufilecache";
#}
