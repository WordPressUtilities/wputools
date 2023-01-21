#!/bin/bash

echo "# UPDATE";

_ADMIN_PROTECT_FILE=$(find . -mount -name 'wputh_admin_protect.php');
_ADMIN_PROTECT_FLAG=".disable_wpu_admin_protect";
_ADMIN_PROTECT_FLAG_FILE="${_CURRENT_DIR}${_ADMIN_PROTECT_FLAG}";
_WPUTOOLS_MAINTENANCE_FILE=".maintenance";
_WPUTOOLS_MAINTENANCE_FILE_PATH="${_CURRENT_DIR}${_WPUTOOLS_MAINTENANCE_FILE}";
_DEBUGLOG_FILE=$(find . -mount -name 'debug.log');

if [[ "${_WPUTOOLS_CORE_UPDATE_TYPE}" == '' ]];then
    _WPUTOOLS_CORE_UPDATE_TYPE='major';
fi;

wputools_add_files_to_excludes "${_ADMIN_PROTECT_FLAG}";

# Allow a simple launch of the page test
if [[ "${1}" == 'instant-test' ]];then
    run_test_instant;
    return;
fi;

# Allow a relaunch of the page test
if [[ "${1}" == 'relaunch-test' ]];then
    run_test_after_regenerate;
    return;
fi;

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
        echo "define('ACF_PRO_LICENSE','${_WPUTOOLS_ACF_PRO_LICENSE}');";
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
    local _CURRENT_WORDPRESS=$(_WPCLICOMMAND core version);
    _WPCLICOMMAND core check-update;
    if [[ "${_WPUTOOLS_CORE_UPDATE_TYPE}" == 'major' ]];then
        _WPCLICOMMAND core update --skip-themes;
    else
        _WPCLICOMMAND core update --minor;
    fi;
    rm -f "${_CURRENT_DIR}wp-content/languages/themes/twenty*";
    _LATEST_WORDPRESS=$(_WPCLICOMMAND core version);

    local _WP_UPDATE_TEXT="Update WordPress from ${_CURRENT_WORDPRESS} to ${_LATEST_WORDPRESS}";
    if [[ "${_CURRENT_WORDPRESS}" == "${_LATEST_WORDPRESS}" ]];then
        local _WP_UPDATE_TEXT="Update WordPress";
    fi;
    commit_without_protect "${_WP_UPDATE_TEXT}";

    echo '# Updating WordPress core translations';
    _WPCLICOMMAND language core update;

    commit_without_protect "Update WordPress core languages";
}

function wputools__update_plugin() {
    local _PLUGIN_ID="${1}";
    local _PLUGIN_DIR="${_CURRENT_DIR}wp-content/plugins/${_PLUGIN_ID}/";
    local _PLUGIN_LANG="${_CURRENT_DIR}wp-content/languages/plugins/${_PLUGIN_ID}*";

    # Check if plugin dir exists
    if [[ ! -d "${_PLUGIN_DIR}" ]];then
        bashutilities_message "The plugin \"${_PLUGIN_ID}\" does not exists" 'error';
    else
        local _PLUGIN_VERSION_OLD=$(_WPCLICOMMAND plugin get "${_PLUGIN_ID}" --field=version);
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
        local _PLUGIN_VERSION=$(_WPCLICOMMAND plugin get "${_PLUGIN_ID}" --field=version);
        local _PLUGIN_TITLE=$(_WPCLICOMMAND plugin get "${_PLUGIN_ID}" --field=title);
        local _PLUGIN_COMMIT_TEXT="Update Plugin ${_PLUGIN_TITLE} from v${_PLUGIN_VERSION_OLD} to v${_PLUGIN_VERSION}";
        if [[ "${_PLUGIN_VERSION_OLD}" == "${_PLUGIN_VERSION}" ]];then
            local _PLUGIN_COMMIT_TEXT="Update Plugin ${_PLUGIN_TITLE}";
        fi;
        git add "${_PLUGIN_LANG}";
        if [[ -d "${_PLUGIN_LANG}" ]];then
            git add "${_PLUGIN_LANG}";
        fi;
        commit_without_protect "${_PLUGIN_COMMIT_TEXT}";
    fi;
}

function wputools__update_all_plugins() {
    echo '# Updating WordPress plugins';
    local _plugin_id;
    for _plugin_id in $(_WPCLICOMMAND plugin list --field=name); do
       wputools__update_plugin "${_plugin_id}";
    done
}

function wputools__update_all_submodules() {
   echo '# Updating submodules';
   git submodule foreach 'git checkout master; git checkout main; git pull origin';

   # Update
   commit_without_protect "Update submodules";

   # Fix sub-sub-modules behavior.
   git submodule update --init --recursive;
}

###################################
## Update
###################################

_PLUGIN_ID="$1";
if [[ "${1}" == 'core' ]];then
    wputools__update_core;
elif [[ "${1}" == 'all-plugins' ]];then
    wputools__check_acf_pro_install;
    wputools__update_all_plugins;
elif [[ "${1}" == 'all-submodules' ]];then
    wputools__update_all_submodules;
elif [[ ! -z "${_PLUGIN_ID}" ]];then
    wputools__update_plugin "${_PLUGIN_ID}"
else

    wputools__check_acf_pro_install;

    ###################################
    ## CORE
    ###################################

    wputools__update_core;

    ###################################
    ## Plugins
    ###################################

    wputools__update_all_plugins;

    ###################################
    ## Submodules
    ###################################

    wputools__update_all_submodules;

    ###################################
    ## Fixes
    ###################################

    # Disable object cache for redis-cache if updated
    if [[ -f "${_CURRENT_DIR}wp-content/plugins/redis-cache/includes/object-cache.php" && -f "${_CURRENT_DIR}wp-content/object-cache.php" ]];then
        rm "${_CURRENT_DIR}wp-content/object-cache.php";
    fi;

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
