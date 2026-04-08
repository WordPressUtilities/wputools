#!/bin/bash

_INFOS_FORMAT="${1:-text}";

if [[ "${_INFOS_FORMAT}" != 'text' && "${_INFOS_FORMAT}" != 'csv' ]];then
    bashutilities_message "Invalid format: ${_INFOS_FORMAT}. Use 'text' or 'csv'." 'error';
    return 0;
fi;

# Get WordPress core version
_WP_CORE_VERSION=$(_WPCLICOMMAND core version --skip-plugins --skip-themes --skip-packages);

# Get plugin list with versions, sorted by name, excluding empty versions
_PLUGINS_LIST=$(_WPCLICOMMAND plugin list --fields=name,version --format=csv --skip-themes --skip-packages | tail -n +2 | awk -F',' '$2 != ""' | sort -t',' -k1,1);

_PLUGINS_DIR="${_CURRENT_DIR}wp-content/plugins";

# Build rows: slug,version,git
_INFOS_ROWS=();
_INFOS_ROWS+=("core_version,wordpress,${_WP_CORE_VERSION},no");
while IFS=',' read -r _slug _version; do
    _git="no";
    if [[ -d "${_PLUGINS_DIR}/${_slug}/.git" || -f "${_PLUGINS_DIR}/${_slug}/.git" ]];then
        _git="yes";
    fi;
    _INFOS_ROWS+=("plugin_version,${_slug},${_version},${_git}");
done <<< "${_PLUGINS_LIST}";

# Output
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
        printf "%-20s %-40s %-12s %s\n" "TYPE" "SLUG" "VERSION" "GIT";
        printf "%-20s %-40s %-12s %s\n" "----" "----" "-------" "---";
        for _row in "${_INFOS_ROWS[@]}"; do
            IFS=',' read -r _type _slug _version _git <<< "${_row}";
            printf "%-20s %-40s %-12s %s\n" "${_type}" "${_slug}" "${_version}" "${_git}";
        done;
    ;;
esac;
