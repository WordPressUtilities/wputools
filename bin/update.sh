#!/bin/bash

echo "# UPDATE";

_ADMIN_PROTECT_FILE=$(find . -mount -name 'wputh_admin_protect.php');
_ADMIN_PROTECT_FLAG=".disable_wpu_admin_protect";
_ADMIN_PROTECT_FLAG_FILE="${_CURRENT_DIR}${_ADMIN_PROTECT_FLAG}";
_MAINTENANCE_FILE=".maintenance";
_MAINTENANCE_FILE_PATH="${_CURRENT_DIR}${_MAINTENANCE_FILE}";
_DEBUGLOG_FILE=$(find . -mount -name 'debug.log');

###################################
## Check if ACF Pro license is available
###################################

function wputools__check_acf_pro_install(){
    local _license_opt;
    local _license_wpc;
    local _version_info;

    # Check if ACF is installed
    if [[ ! -f "${_CURRENT_DIR}wp-content/plugins/advanced-custom-fields-pro/acf.php" ]];then
        return 0;
    fi;

    # Check if this is a version which does not support the license in constant.
    _version_info=$(_WPCLICOMMAND plugin get advanced-custom-fields-pro --fields=version --format=csv);
    _version_info=$(echo $_version_info | xargs);
    _version_info="${_version_info/Field,Value version,/}";

    local requiredver='5.11.0';
    local currentver="${_version_info}";
    local wpoption_needed;

    # https://unix.stackexchange.com/a/285928
    if [ "$(printf '%s\n' "$requiredver" "$currentver" | sort -V | head -n1)" = "$requiredver" ]; then
        wpoption_needed='0';
    else
        wpoption_needed='1';
    fi

    # Check if the license is defined
    _license_opt=$(_WPCLICOMMAND option --quiet get acf_pro_license 2> /dev/null);
    if [[ "${_license_opt}" != "" ]];then
        return 0;
    fi;
    _license_wpc=$(_WPCLICOMMAND config --quiet get ACF_PRO_LICENSE 2> /dev/null);
    if [[ "${wpoption_needed}" == "0" && "${_license_wpc}" != "" ]];then
        return 0;
    fi;

    # Not defined : pause
    bashutilities_message "You seem to have installed ACF Pro, but no license is defined.";
    if [[ "${wpoption_needed}" == '0' ]];then
        echo "Please add your license in the wp-config file.";
        echo "define('ACF_PRO_LICENSE','LICENSE').";
    else
        echo "Please add your license in the admin area.";
    fi;
    echo "If you ignore this, ACF Pro wont be updated.";
    read -p "Press enter to continue or ignore :";


    local _license_opt=$(_WPCLICOMMAND --quiet option get acf_pro_license 2> /dev/null);
    local _license_wpc=$(_WPCLICOMMAND --quiet config get ACF_PRO_LICENSE 2> /dev/null);

    if [[ "${_license_opt}" == "" && "${_license_wpc}" == "" ]];then
        bashutilities_message "ACF Pro wont be updated.";
    else
        bashutilities_message "ACF Pro will be updated." 'success';
    fi;
}

if [[ "$1" == "" ]];then
    wputools__check_acf_pro_install;
fi;

###################################
## Initial checks
###################################

if [ -f "${_DEBUGLOG_FILE}" ]; then
    _DEBUGLOG_FILE_SIZE=$(wc -c "${_DEBUGLOG_FILE}");
fi;

run_test_before;

git add -u . && git add .;
git stash;

touch "${_ADMIN_PROTECT_FLAG_FILE}";
if [ -f "${_ADMIN_PROTECT_FILE}" ]; then
    echo "# Disabling Admin Protect";
    mv "${_ADMIN_PROTECT_FILE}" "${_ADMIN_PROTECT_FILE}.txt";
fi;

_WPCLICOMMAND maintenance-mode activate;

function commit_without_protect(){
    git reset;
    git add -A;
    # Maintenance
    git reset -- "${_MAINTENANCE_FILE}";
    git restore --staged "${_MAINTENANCE_FILE_PATH}";
    # Admin protect
    git reset -- "${_ADMIN_PROTECT_FLAG}";
    if [[ -n "${_ADMIN_PROTECT_FILE}" ]];then
        git restore --staged "${_ADMIN_PROTECT_FILE}";
        git restore --staged "${_ADMIN_PROTECT_FILE}.txt";
    fi;
    git commit --no-verify -m "${1}";
}

function wputools__update_core(){
    echo '# Updating WordPress core';
    _WPCLICOMMAND core check-update;
    _WPCLICOMMAND core update;
    rm -f "${_CURRENT_DIR}wp-content/languages/themes/twenty*";
    _LATEST_WORDPRESS=$(_WPCLICOMMAND core version);
    commit_without_protect "Update WordPress to ${_LATEST_WORDPRESS}";

    echo '# Updating WordPress core translations';
    _WPCLICOMMAND language core update;

    commit_without_protect "Update WordPress core languages";
}

###################################
## Update
###################################

_PLUGIN_ID="$1";
if [[ "${1}" == 'core' ]];then
    wputools__update_core;
elif [[ ! -z "$_PLUGIN_ID" ]];then
    _PLUGIN_DIR="${_CURRENT_DIR}wp-content/plugins/${_PLUGIN_ID}/";
    _PLUGIN_LANG="${_CURRENT_DIR}wp-content/languages/plugins/${_PLUGIN_ID}*";

    # Check if plugin dir exists
    if [[ ! -d "${_PLUGIN_DIR}" ]];then
        bashutilities_message "The plugin \"${_PLUGIN_ID}\" does not exists" 'error';
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

    wputools__check_acf_pro_install;

    ###################################
    ## CORE
    ###################################

    wputools__update_core;

    ###################################
    ## Plugins
    ###################################

    echo '# Updating WordPress plugins';
    _WPCLICOMMAND plugin update --all;

    # Update
    commit_without_protect "Update plugins";

    echo '# Updating WordPress plugins translations';
    _WPCLICOMMAND language plugin update --all;

    # Update
    commit_without_protect "Update plugins translations";

    ###################################
    ## Submodules
    ###################################

    echo '# Updating submodules';
    git submodule foreach 'git checkout master; git checkout main; git pull origin';

    # Update
    commit_without_protect "Update submodules";

    # Fix sub-sub-modules behavior.
    git submodule update --init --recursive;

    ###################################
    ## Fixes
    ###################################

    # Disable object cache for redis-cache if updated
    if [[ -f "${_CURRENT_DIR}wp-content/plugins/redis-cache/includes/object-cache.php" && -f "${_CURRENT_DIR}wp-content/object-cache.php" ]];then
        rm "${_CURRENT_DIR}wp-content/object-cache.php";
    fi;

    ###################################
    ## Test
    ###################################


fi;

###################################
## Closing checks
###################################

rm "${_ADMIN_PROTECT_FLAG_FILE}";
if [ -f "${_ADMIN_PROTECT_FILE}.txt" ]; then
    echo "# Re-enabling Admin Protect";
    mv "${_ADMIN_PROTECT_FILE}.txt" "${_ADMIN_PROTECT_FILE}";
fi;

_WPCLICOMMAND maintenance-mode deactivate;
git stash apply;

run_test_after;

echo "# Update is over !";

if [ -f "${_DEBUGLOG_FILE}" ]; then
    _DEBUGLOG_FILE_SIZE_AFTER=$(wc -c "${_DEBUGLOG_FILE}");
    if [ "${_DEBUGLOG_FILE_SIZE}" != "${_DEBUGLOG_FILE_SIZE_AFTER}" ]; then
        bashutilities_message "Debug log seems to have changed since the update. Please look at it ?" 'warning';
        tail -3 "${_DEBUGLOG_FILE}";
    fi;
fi;

# Clear cache
wputools_call_route cache > /dev/null;
