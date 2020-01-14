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

if [[ ! -f "${_UPDATE_CONTROL_FILE}" ]];then
    wputools_check_update;
    return 0;
fi;

_last_check_age=$(( $(date +%s) - $(stat -f%c "${_UPDATE_CONTROL_FILE}") ))
_max_check_age=80;

if [[ "${_last_check_age}" -gt "${_max_check_age}" ]];then
    echo "# Checking for WPUTools Updates";
    wputools_check_update;
    return 0;
fi;


wputools_update_available_message




