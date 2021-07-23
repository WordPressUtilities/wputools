#!/bin/bash

###################################
## Remove old functions
###################################

unset -f _WPCLICOMMAND;
unset -f check_parameters;
unset -f commit_without_protect;
unset -f run_test_after;
unset -f run_test_before;
unset -f wputools__get_db_host;
unset -f wputools__get_db_name;
unset -f wputools__get_db_password;
unset -f wputools__get_db_prefix;
unset -f wputools__get_db_user;
unset -f wputools_cache_warming;
unset -f wputools_backup_uploads_cleanup;
unset -f wputools_call_url;
unset -f wputools_check_update;
unset -f wputools_execute_file;
unset -f wputools_get_real_option;
unset -f wputools_get_siteurl;
unset -f wputools_query;
unset -f wputools_query_select;
unset -f wputools_test_check_urls;
unset -f wputools_update_available_message;
unset -v _BACKUP_DIR;
unset -v _BACKUP_UPLOADS;
unset -v _EXTRA_CURL_ARGS;
unset -v _HOME_URL;
unset -v _WPDB_REPLACE_AFTER;
unset -v _WPDB_REPLACE_BEFORE;
unset -v _WPDB_SSH_BACKUP_DIR;
unset -v _WPDB_SSH_USER_AT_HOST;
