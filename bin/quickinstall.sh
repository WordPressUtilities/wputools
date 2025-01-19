#!/bin/bash

echo "# QUICK INSTALL";

# Check if wp-config.php file exists
if [[ ! -f "${_CURRENT_DIR}wp-config.php" ]]; then
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

_WPCLICOMMAND core install \
    --title=Example \
    --admin_user=admin \
    --admin_password=admin \
    --admin_email=test@example.com \
    --url="${_HOME_URL}" \
    --skip-email;
