#!/bin/bash

echo "# WP Config";

if [[ -f "${_CURRENT_DIR}wp-config.php" || -f "${_CURRENT_DIR}../wp-config.php" ]];then
    bashutilities_message "There is already a wp-config.php file available" 'error';
    return 0;
fi;

###################################
## Get infos
###################################

wpconfig_site_id=$(bashutilities_get_user_var "- What is your project ID?" "project_id");
wpconfig_site_prefix=$(bashutilities_get_user_var "- What is your site prefix?" "wp_");

# Default values
mysql_host='localhost';
mysql_user='root';
mysql_password='root';
mysql_database="${wpconfig_site_id}";
project_raw_domain="${wpconfig_site_id}.test";

wpconfig_default_settings=$(bashutilities_get_yn "- Use default settings? (localhost/root/root)" 'y');
if [[ "${wpconfig_default_settings}" != 'y' ]];then
    mysql_host=$(bashutilities_get_user_var "- What is the MySQL host?" "localhost");
    mysql_user=$(bashutilities_get_user_var "- What is the MySQL user?" "root");
    mysql_password=$(bashutilities_get_user_var "- What is the MySQL password?" "root");
    mysql_database=$(bashutilities_get_user_var "- What is the MySQL database?" "${wpconfig_site_id}");
    project_raw_domain=$(bashutilities_get_user_var "- What is the project domain name?" "${wpconfig_site_id}.test");
fi;

###################################
## Create config
###################################

_WPCLICOMMAND core config \
    --skip-check \
    --dbhost="${mysql_host}" \
    --dbname="${mysql_database}" \
    --dbuser="${mysql_user}" \
    --dbpass="${mysql_password}" \
    --dbprefix="${wpconfig_site_prefix}" \
    --extra-php <<PHP

# URLs
if(!isset(\$_SERVER['HTTP_HOST']) || !\$_SERVER['HTTP_HOST']){
    \$_SERVER['HTTP_HOST'] = '${project_raw_domain}';
}
if(!isset(\$_SERVER['SERVER_PROTOCOL']) || !\$_SERVER['SERVER_PROTOCOL']){
    \$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.0';
}
define('WP_SITEURL', 'http://' . \$_SERVER['HTTP_HOST'] . '/');
define('WP_HOME', 'http://' . \$_SERVER['HTTP_HOST'] . '/');

# CRONs
define('DISABLE_WP_CRON', true);

# Environment
define('WP_ENVIRONMENT_TYPE', 'local');

# Config
define('EMPTY_TRASH_DAYS', 7);
define('WP_POST_REVISIONS', 6);

# Memory
define('WP_MEMORY_LIMIT', '128M');
define('WP_MAX_MEMORY_LIMIT', '256M');

# Updates
define('AUTOMATIC_UPDATER_DISABLED', true);
define('WP_AUTO_UPDATE_CORE', true);

# Block external access
#define('WP_HTTP_BLOCK_EXTERNAL', true);

# Block file edit
#define('DISALLOW_FILE_EDIT', true);
#define('DISALLOW_FILE_MODS', true);

# Debug
define('WP_DEBUG', true);
if (WP_DEBUG) {
    @ini_set('display_errors', 0);
    if (!defined('WP_DEBUG_DISPLAY')) {
        define('WP_DEBUG_DISPLAY', false);
    }
    if (!defined('WP_DEBUG_LOG')) {
        define('WP_DEBUG_LOG', dirname(__FILE__) . '/../logs/debug-' . date('Ymd') . '.log');
    }
    if (!defined('SCRIPT_DEBUG')) {
        define('SCRIPT_DEBUG', 1);
    }
    if (!defined('SAVEQUERIES')) {
        define('SAVEQUERIES', (php_sapi_name() !== 'cli'));
    }
}
PHP
