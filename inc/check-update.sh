#!/bin/bash


wputools_update_available_message(){
    _WPUTOOLS_LATEST=$(cd "${_SOURCEDIR}" && git describe --tags $(git rev-list --tags --max-count=1));
    if [[ "${_WPUTOOLS_VERSION}" != "${_WPUTOOLS_LATEST}" ]]; then
        cat <<EOF
--
An update is available: ${_WPUTOOLS_LATEST}
You actually use: ${_WPUTOOLS_VERSION}
Just type in your terminal:
$ wputools self-update
--

EOF
    fi;
}

wputools_check_update() {
    echo "# Fetching latest updates";
    $(cd "${_SOURCEDIR}" && git fetch --tags);
    wputools_update_available_message
    touch "${_UPDATE_CONTROL_FILE}";
}

# Create control file if it dont exists : trigger update.
if [[ ! -f "${_UPDATE_CONTROL_FILE}" ]];then
    wputools_check_update;
    return 0;
fi;

# If the file is older than our control duration : trigger update.
_last_check_age=$(( $(date +%s) - $( date -r "${_UPDATE_CONTROL_FILE}" +%s) ))
if [[ "${_last_check_age}" -gt "${_UPDATE_CHECK_EVERY_SEC}" ]];then
    echo "# Checking for WPUTools updates";
    wputools_check_update;
    return 0;
fi;


wputools_update_available_message

# Remove old functions
unset -f wputools_check_update
unset -f wputools_update_available_message
