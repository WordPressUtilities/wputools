#!/bin/bash

echo "# QUICK INSTALL";

# Check if wp-config.php file exists
if [[ ! -f "${_CURRENT_DIR}wp-config.php" && ! -f "${_CURRENT_DIR}/../wp-config.php" ]]; then
    bashutilities_message 'wp-config.php file not found' 'error';
    return 0;
fi

# Check MySQL
if [[ ! $(_WPCLICOMMAND db check) ]];then
    bashutilities_message 'MySQL is not available' 'error';
    return 0;
fi;

# WordPress is already configured
if [[ "${_HOME_URL}" != '' && "${_SITE_NAME}" != '' ]];then
    bashutilities_message "WordPress is already installed." 'error';
    return 0;
fi;

_WPUTOOLS_DEFAULT_INSTALL_DOMAIN=$(wputools_get_default_domain);
_HOME_URL=$(bashutilities_get_user_var "What is the home URL ?" "http://${_WPUTOOLS_DEFAULT_INSTALL_DOMAIN}.test");

if [[ -z "${_HOME_URL}" || "${_HOME_URL}" == '' ]];then
    bashutilities_message "The home URL is required." 'error';
    return 0;
fi;
_HOME_URL=$(wputools_format_home_url "${_HOME_URL}");

# Remove trailing slash from _HOME_URL if it exists
_HOME_URL="${_HOME_URL%/}"

_WPCLICOMMAND core install \
    --admin_user=admin_example \
    --admin_password=admin_example \
    --admin_email=admin@example.com\
    --title="${_WPUTOOLS_DEFAULT_INSTALL_DOMAIN}"\
    --url="${_HOME_URL}" \
    --skip-email;

# Clear cache after install
wputools_call_route cache > /dev/null;

# Login to the new install
wputools_call_route login;
