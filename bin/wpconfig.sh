#!/bin/bash


echo "# WP Config";

###################################
## Set values
###################################

function wputools__wpconfig_set_values(){
    _WPCLICOMMAND config set DISABLE_WP_CRON true --raw;
    _WPCLICOMMAND config set EMPTY_TRASH_DAYS 7 --raw;
    _WPCLICOMMAND config set WP_POST_REVISIONS 6 --raw;
    _WPCLICOMMAND config set WP_MEMORY_LIMIT 128M;
    _WPCLICOMMAND config set WP_MAX_MEMORY_LIMIT 256M;
    _WPCLICOMMAND config set AUTOMATIC_UPDATER_DISABLED true --raw;
    _WPCLICOMMAND config set WP_AUTO_UPDATE_CORE false --raw;
    _WPCLICOMMAND config set WP_ENVIRONMENT_TYPE 'local';
}

###################################
## Check config
###################################

if [[ -f "${_CURRENT_DIR}wp-config.php" || -f "${_CURRENT_DIR}../wp-config.php" ]];then
    bashutilities_message "There is already a wp-config.php file available" 'error';
    wputools_wpconfig_ask_overwrite="$(bashutilities_get_yn "- Do you want to force add some values?" 'n')";
    if [[ "${wputools_wpconfig_ask_overwrite}" == 'y' ]];then
        wputools__wpconfig_set_values;
    fi;
    return 0;
fi;

###################################
## Get infos
###################################

wpconfig_site_id=$(bashutilities_get_user_var "- What is your project ID?" "project_id");
wpconfig_site_prefix=$(bashutilities_get_user_var "- What is your site prefix?" "wp_");

# Default values
wputools__mysql_host='localhost';
wputools__mysql_user='root';
wputools__mysql_password='root';
wputools__mysql_database="${wpconfig_site_id}";
wputools__project_raw_domain="${wpconfig_site_id}.test";
wputools__use_https='n';
wputools__protocol='http';

wpconfig_default_settings=$(bashutilities_get_yn "- Use default settings? (localhost/root/root)" 'y');
if [[ "${wpconfig_default_settings}" != 'y' ]];then
    wputools__mysql_host=$(bashutilities_get_user_var "- What is the MySQL host?" "localhost");
    wputools__mysql_user=$(bashutilities_get_user_var "- What is the MySQL user?" "root");
    wputools__mysql_password=$(bashutilities_get_user_var "- What is the MySQL password?" "root");
    wputools__mysql_database=$(bashutilities_get_user_var "- What is the MySQL database?" "${wpconfig_site_id}");
    wputools__project_raw_domain=$(bashutilities_get_user_var "- What is the project domain name?" "${wpconfig_site_id}.test");
    wputools__use_https=$(bashutilities_get_yn "- Use HTTPS for site URL?" 'n');
fi;

if [[ "${wputools__use_https}" == 'y' ]];then
    wputools__protocol='https';
fi;

###################################
## Create config
###################################

_WPCLICOMMAND core config \
    --skip-check \
    --dbhost="${wputools__mysql_host}" \
    --dbname="${wputools__mysql_database}" \
    --dbuser="${wputools__mysql_user}" \
    --dbpass="${wputools__mysql_password}" \
    --dbprefix="${wpconfig_site_prefix}" \
    --extra-php <<PHP

# URLs
if(!isset(\$_SERVER['HTTP_HOST']) || !\$_SERVER['HTTP_HOST']){
    \$_SERVER['HTTP_HOST'] = '${wputools__project_raw_domain}';
}
if(!isset(\$_SERVER['SERVER_PROTOCOL']) || !\$_SERVER['SERVER_PROTOCOL']){
    \$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.0';
}
define('WP_SITEURL', '${wputools__protocol}://' . \$_SERVER['HTTP_HOST'] . '/');
define('WP_HOME', '${wputools__protocol}://' . \$_SERVER['HTTP_HOST'] . '/');

# Block external access
#define('WP_HTTP_BLOCK_EXTERNAL', true);

# Block file edit
#define('DISALLOW_FILE_EDIT', true);
#define('DISALLOW_FILE_MODS', true);

# Debug
if(!defined('WP_DEBUG')){
    define('WP_DEBUG', true);
}
if (WP_DEBUG) {
    @ini_set('display_errors', 0);
    if (!defined('WP_DEBUG_DISPLAY')) {
        define('WP_DEBUG_DISPLAY', false);
    }
    if (!defined('WP_DEBUG_LOG')) {
        define('WP_DEBUG_LOG', dirname(__FILE__) . '/../logs/debug-' . date('Ymd') . '.log');
    }
    if (!defined('SAVEQUERIES')) {
        define('SAVEQUERIES', (php_sapi_name() !== 'cli'));
    }
}
PHP


wputools__wpconfig_set_values;
