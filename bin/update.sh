#!/bin/bash

echo "# UPDATE";

_ADMIN_PROTECT_FILE=$(find . -mount -name 'wputh_admin_protect.php');
_DEBUGLOG_FILE=$(find . -mount -name 'debug.log');

###################################
## Initial checks
###################################

if [ -f "${_DEBUGLOG_FILE}" ]; then
    _DEBUGLOG_FILE_SIZE=$(wc -c "${_DEBUGLOG_FILE}");
fi;

if [ -f "${_ADMIN_PROTECT_FILE}" ]; then
    echo "# Disabling Admin Protect";
    mv "${_ADMIN_PROTECT_FILE}" "${_ADMIN_PROTECT_FILE}.txt";
fi;

function commit_without_protect(){
    git reset;
    git add -A;
    git restore --staged "${_ADMIN_PROTECT_FILE}" "${_ADMIN_PROTECT_FILE}.txt";
    git commit --no-verify -m "${1}";
}

###################################
## Update
###################################

_PLUGIN_ID="$1";
if [[ ! -z "$_PLUGIN_ID" ]];then
    _PLUGIN_DIR="${_CURRENT_DIR}wp-content/plugins/${_PLUGIN_ID}/";
    _PLUGIN_LANG="${_CURRENT_DIR}wp-content/languages/plugins/${_PLUGIN_ID}*";

    # Check if plugin dir exists
    if [[ ! -d "${_PLUGIN_DIR}" ]];then
        echo $(bashutilities_message "The plugin \"${_PLUGIN_ID}\" does not exists" 'error');
    else
        # Reset git status
        git reset;
        # Update
        if [[ -d "${_PLUGIN_DIR}.git" || -f "${_PLUGIN_DIR}.git" ]];then
            # If plugin uses git : update from git
            echo '# Update from git';
            (cd "${_PLUGIN_DIR}"; git checkout master; git checkout main; git pull origin);
        else
            # Update plugin with WP-CLI
            echo '# Update from WP-CLI';
            _WPCLICOMMAND plugin update "${_PLUGIN_ID}";
            _WPCLICOMMAND language plugin update "${_PLUGIN_ID}";
        fi;
        # Commit plugin update
        _PLUGIN_VERSION=$(_WPCLICOMMAND plugin get "${_PLUGIN_ID}" --field=version);
        _PLUGIN_TITLE=$(_WPCLICOMMAND plugin get "${_PLUGIN_ID}" --field=title);
        git add "${_PLUGIN_DIR}";
        git add "${_PLUGIN_LANG}";
        git commit -m "Plugin Update : ${_PLUGIN_TITLE} v${_PLUGIN_VERSION}";
    fi;
else
    run_test_before;

    ###################################
    ## CORE
    ###################################

    echo '# Updating WordPress core';
    _WPCLICOMMAND core check-update;
    _WPCLICOMMAND core update;
    rm -f "${_CURRENT_DIR}wp-content/languages/themes/twenty*";

    echo '# Updating WordPress core translations';
    _WPCLICOMMAND language core update;

    _LATEST_WORDPRESS=$(_WPCLICOMMAND core version);

    commit_without_protect "Update WordPress to ${_LATEST_WORDPRESS}";

    ###################################
    ## Plugins
    ###################################

    echo '# Updating WordPress plugins';
    _WPCLICOMMAND plugin update --all;

    echo '# Updating WordPress plugins translations';
    _WPCLICOMMAND language plugin update --all;

    # Update
    commit_without_protect "Update plugins";

    ###################################
    ## Submodules
    ###################################

    echo '# Updating submodules';
    git submodule foreach 'git checkout master; git checkout main; git pull origin';

    # Update
    commit_without_protect "Update submodules";

    run_test_after;
fi;

###################################
## Closing checks
###################################

if [ -f "${_ADMIN_PROTECT_FILE}.txt" ]; then
    echo "# Re-enabling Admin Protect";
    mv "${_ADMIN_PROTECT_FILE}.txt" "${_ADMIN_PROTECT_FILE}";
fi;

echo "# Update is over !";

if [ -f "${_DEBUGLOG_FILE}" ]; then
    _DEBUGLOG_FILE_SIZE_AFTER=$(wc -c "${_DEBUGLOG_FILE}");
    if [ "${_DEBUGLOG_FILE_SIZE}" != "${_DEBUGLOG_FILE_SIZE_AFTER}" ]; then
        echo $(bashutilities_message "Debug log seems to have changed since the update. Please look at it ?" 'warning');
        tail -3 "${_DEBUGLOG_FILE}";
    fi;
fi;

# Clear cache
WPUTools cache;
