#!/bin/bash

###################################
## Remove old functions
###################################

unset -f _WPCLICOMMAND;
unset -f _WPUTOOLS_IMPORT__RESET;
unset -f check_parameters;
unset -f commit_without_protect;
unset -f run_test_after;
unset -f run_test_after_regenerate;
unset -f run_test_before;
unset -f wputools__check_acf_pro_install;
unset -f wputools__debug_display_file;
unset -f wputools__delete_old_files;
unset -f wputools__duplicate_menu;
unset -f wputools__generate_menus;
unset -f wputools__generate_urls;
unset -f wputools__get_db_host;
unset -f wputools__get_db_name;
unset -f wputools__get_db_password;
unset -f wputools__get_db_prefix;
unset -f wputools__get_db_user;
unset -f wputools__get_wp_config_path;
unset -f wputools__nginx_convert;
unset -f wputools__plugin;
unset -f wputools__sample_wptest;
unset -f wputools__sandbox;
unset -f wputools__update_all_features;
unset -f wputools__update_all_plugins;
unset -f wputools__update_all_submodules;
unset -f wputools__update_all_themes;
unset -f wputools__update_core;
unset -f wputools__update_plugin;
unset -f wputools_add_files_to_excludes;
unset -f wputools_archive_logs;
unset -f wputools_anonymize_db;
unset -f wputools_backup_uploads_cleanup;
unset -f wputools_cache_warming;
unset -f wputools_call_route;
unset -f wputools_call_url;
unset -f wputools_check_update;
unset -f wputools_execute_file;
unset -f wputools_get_real_option;
unset -f wputools_get_siteurl;
unset -f wputools_go_folder_or_subfolder;
unset -f wputools_install_plugin_folder;
unset -f wputools_query;
unset -f wputools_query_select;
unset -f wputools_test_check_urls;
unset -f wputools_update_available_message;
unset -v _ADMIN_PROTECT_FILE;
unset -v _ADMIN_PROTECT_FLAG;
unset -v _ADMIN_PROTECT_FLAG_FILE;
unset -v _BACKUP_ARCHIVE;
unset -v _BACKUP_DIR;
unset -v _BACKUP_FILE;
unset -v _BACKUP_FILE_ITEM;
unset -v _BACKUP_FILE_ITEM_NAME;
unset -v _BACKUP_FILES;
unset -v _BACKUP_FOLDERS;
unset -v _BACKUP_FOLDER_ITEM;
unset -v _BACKUP_FOLDER_ITEM_NAME;
unset -v _BACKUP_NAME;
unset -v _BACKUP_PATH;
unset -v _BACKUP_RAND;
unset -v _BACKUP_UPLOADS;
unset -v _BD_FILE;
unset -v _BD_PATH;
unset -v _BD_RAND;
unset -v _cache_arg;
unset -v _cache_dir;
unset -v _CACHE_DIRS;
unset -v _cache_type;
unset -v _COMMIT_CHECK_GIT;
unset -v _dbimport_file;
unset -v _dbimport_file_tmp;
unset -v _DEBUGLOG_FILE;
unset -v _DEBUGLOG_FILE_SIZE;
unset -v _DEBUGLOG_FILE_SIZE_AFTER;
unset -v _EXTRA_CURL_ARGS;
unset -v _HOME_URL;
unset -v _latest_backup;
unset -v _CURRENT_WORDPRESS;
unset -v _LATEST_WORDPRESS;
unset -v _menu_id;
unset -v _menu_link;
unset -v _menu_links;
unset -v _menu_list;
unset -v _NOBACKUP_CRONTABS;
unset -v _PHP_VERSION;
unset -v _PHP_VERSION_OK;
unset -v _PLUGIN_ID;
unset -v _SITE_NAME_SLUG;
unset -v _STATIC_FILE;
unset -v _STATIC_PATH;
unset -v _STATIC_RAND;
unset -v _tmp_dump;
unset -v _tmp_folder;
unset -v _TMPLINE;
unset -v _WP_PROJECT_ENV;
unset -v _WPDB_BACKUP_LOCAL_DIR;
unset -v _WPDB_REPLACE_AFTER;
unset -v _WPDB_REPLACE_BEFORE;
unset -v _WPDB_REPLACE_BEFORE_TMP;
unset -v _WPDB_SSH_BACKUP_DIR;
unset -v _WPDB_SSH_USER_AT_HOST;
unset -v _WPDB_SSH_PORT;
unset -v _WPUADM_DB_HOST;
unset -v _WPUADM_DB_NAME;
unset -v _WPUADM_DB_PASS;
unset -v _WPUADM_DB_USER;
unset -v _WPUADM_FILE;
unset -v _WPUADM_PATH;
unset -v _WPUADM_RAND;
unset -v _WPUADM_URL;
unset -v _WPPLUGINSTMPLIST;
unset -v _WPPLUGINSTMPCOUNT;
unset -v _WPPLUGINSTMPLISTSIZE;
unset -v _WPUDHK_COMPARE_PLUG;
unset -v _WPUDHK_COMPARE_WP;
unset -v _WPUDHK_DIR;
unset -v _WPUDHK_FILE;
unset -v _WPUDHK_PATH;
unset -v _WPUDHK_RAND;
unset -v _WPULOG_FILE;
unset -v _WPULOG_PATH;
unset -v _WPULOG_RAND;
unset -v _WPUSAMPLE_FILE;
unset -v _WPUSAMPLE_PATH;
unset -v _WPUSAMPLE_RAND;
unset -v _WPUTESTFILE_FILE;
unset -v _WPUTESTFILE_PATH;
unset -v _WPUTESTFILE_RAND;
unset -v _WPUTOOLS_ACF_PRO_LICENSE;
unset -v _WPUTOOLS_BACKUP_DIR;
unset -v _WPUTOOLS_CORE_UPDATE_TYPE;
unset -v _wputools_last_check_age;
unset -v _WPUTOOLS_LATEST;
unset -v _WPUTOOLS_LOCAL_PATH;
unset -v _WPUTOOLS_LOCAL_PATH_ASK;
unset -v _WPUTOOLS_LOGIN_URL;
unset -v _WPUTOOLS_MAINTENANCE_FILE;
unset -v _WPUTOOLS_MAINTENANCE_FILE_PATH;
unset -v _WPUTOOLS_SETTINGS_EXTRA_FILE;
unset -v _WPUTOOLS_SETTINGS_EXTRA_FILE_NAME;
unset -v _WPUTOOLS_SETTINGS_INSTALL_EXTRA_FILE;
unset -v _wputools_test__file;
unset -v _wputools_test_after_content;
unset -v _wputools_test_after_length;
unset -v _wputools_test_before_content;
unset -v _wputools_test_before_length;
unset -v _wputools_need_login;
unset -v _WPUTOOLS_TEXT_MESSAGE;
unset -v _WPUTOOLS_URL_LOCAL_FILE;
unset -v _WPUTOOLS_URL_LOCAL_FILE_TMP;
unset -v _WPUWOO_ACTION_DIR;
unset -v backup_topdir;
unset -v backup_uploads;
unset -v uploads_dir;
unset -v use__wpdb_replace_before_tmp;
unset -v wputools_use_backup_dir;
unset -v wputools_use_home_url;
unset -v wputools_use_site_name;

. "${_TOOLSDIR}BashUtilities/modules/stop.sh";
