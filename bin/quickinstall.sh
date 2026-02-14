#!/bin/bash

echo "# QUICK INSTALL";

# Check if wp-config.php file exists
if [[ ! -f "${_CURRENT_DIR}wp-config.php" && ! -f "${_CURRENT_DIR}/../wp-config.php" ]]; then
    bashutilities_message 'wp-config.php file not found' 'error';
    return 0;
fi

# WordPress is already configured
if [[ "${_HOME_URL}" != '' && "${_SITE_NAME}" != '' ]];then
    bashutilities_message "WordPress is already installed." 'error';
    return 0;
fi;

_HOME_URL=$(bashutilities_get_user_var "What is the home URL ? (Example: http://example.test)");

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
    --title="${_HOME_URL#*://}"\
    --url="${_HOME_URL}" \
    --skip-email;

_WPCLICOMMAND user create \
    admin_example \
    admin@example.com\
    --user_pass=admin_example \
    --role=administrator \
    --url="${_HOME_URL}";
