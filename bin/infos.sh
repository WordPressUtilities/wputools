#!/bin/bash

###################################
## Format
###################################

_INFOS_FORMAT="${1:-text}";

if [[ "${_INFOS_FORMAT}" != 'text' && "${_INFOS_FORMAT}" != 'csv' ]];then
    bashutilities_message "Invalid format: ${_INFOS_FORMAT}. Use 'text' or 'csv'." 'error';
    return 0;
fi;

###################################
## Common rows
###################################

_INFOS_ROWS=();
_INFOS_ROWS+=("core_version,php,${_PHP_VERSION_FULL},");

###################################
## WordPress version
###################################

# Get WordPress core version
_WP_CORE_VERSION=$(_WPCLICOMMAND core version --skip-plugins --skip-themes --skip-packages);
_INFOS_ROWS+=("core_version,wordpress,${_WP_CORE_VERSION},");


###################################
## Last commit
###################################

if [[ -d "${_CURRENT_DIR}.git" ]];then
    _LAST_COMMIT_DATE=$(git -C "${_CURRENT_DIR}" log -1 --format="%ci" 2>/dev/null | cut -c1-16);
    if [[ -n "${_LAST_COMMIT_DATE}" ]];then
        _INFOS_ROWS+=("last_commit,repo,${_LAST_COMMIT_DATE},");
    fi;
fi;


###################################
## Plugins
###################################

# Get plugin list with versions, sorted by name, excluding empty versions
_PLUGINS_LIST=$(_WPCLICOMMAND plugin list --fields=name,version --format=csv --skip-themes --skip-packages | tail -n +2 | awk -F',' '$2 != ""' | sort -t',' -k1,1);
_PLUGINS_DIR="${_CURRENT_DIR}wp-content/plugins";
while IFS=',' read -r _slug _version; do
    _git="no";
    if [[ -d "${_PLUGINS_DIR}/${_slug}/.git" || -f "${_PLUGINS_DIR}/${_slug}/.git" ]];then
        _git="yes";
    fi;
    _INFOS_ROWS+=("plugin_version,${_slug},${_version},${_git}");
done <<< "${_PLUGINS_LIST}";

###################################
## MU-Plugins
###################################

# Git submodules in mu-plugins
_MUPLUGINS_DIR="${_CURRENT_DIR}wp-content/mu-plugins";
if [[ -d "${_MUPLUGINS_DIR}" ]];then
    while IFS= read -r _git_path; do
        _muplugin_dir=$(dirname "${_git_path}");
        _slug=$(basename "${_muplugin_dir}");
        _version="";
        for _php_file in "${_muplugin_dir}"/*.php; do
            if [[ -f "${_php_file}" ]];then
                _version=$(grep -m 1 -i "^[ *]*Version:" "${_php_file}" | sed 's/.*Version:[[:space:]]*//i');
                if [[ -n "${_version}" ]];then
                    break;
                fi;
            fi;
        done;
        _INFOS_ROWS+=("muplugin_version,${_slug},${_version},yes");
    done < <(find "${_MUPLUGINS_DIR}" -maxdepth 3 -name ".git" | sort);
fi;

###################################
## Output
###################################

_TEXT_COL_FORMAT="%-20s %-40s %-20s %s\n";
case "${_INFOS_FORMAT}" in
    "csv")
        echo "type,slug,version,git";
        for _row in "${_INFOS_ROWS[@]}"; do
            echo "${_row}";
        done;
    ;;
    *)
        wputools_echo_message "# INFOS";
        echo "";
        printf "${_TEXT_COL_FORMAT}" "TYPE" "SLUG" "VERSION" "GIT";
        printf "${_TEXT_COL_FORMAT}" "----" "----" "-------" "---";
        for _row in "${_INFOS_ROWS[@]}"; do
            IFS=',' read -r _type _slug _version _git <<< "${_row}";
            printf "${_TEXT_COL_FORMAT}" "${_type}" "${_slug}" "${_version}" "${_git}";
        done;
    ;;
esac;
