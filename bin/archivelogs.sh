#!/bin/bash

###################################
## Archive logs
###################################

function wputools_archive_logs(){

    local _logs_folder;
    local _logs_archive;
    local _logs_files;
    local _log_file;
    local _log_file_ym;
    local _log_file_archive;
    local _EXCLUDED_YEARMONTHS;
    local _PREVIOUS_MONTH;

    # Find log folder
    _logs_folder="${_CURRENT_DIR}/../logs/";
    if [ ! -d "${_logs_folder}" ]; then
        echo 'No logs folder';
        return;
    fi
    _logs_archive="${_logs_folder}archive/";

    # Find if there are logs
    _logs_files=$(ls -1 "${_logs_folder}");
    if [ -z "${_logs_files}" ]; then
        echo 'No logs files';
        return;
    fi

    _EXCLUDED_YEARMONTHS="$(date +%Y)$(date +%m)";
    if ((BASH_VERSINFO[0] >= 4)); then
        _PREVIOUS_MONTH=$(date -d "1 month ago" +%Y%m);
    else
        _PREVIOUS_MONTH=$(date -v-1m +%Y%m);
    fi
    _EXCLUDED_YEARMONTHS="${_EXCLUDED_YEARMONTHS} ${_PREVIOUS_MONTH}";

    # Loop through log files and archive them by year and month contained in the log file name
    for _log_file in ${_logs_files}; do

        # Stop if the year and month are in the excluded list
        if [[ "${_EXCLUDED_YEARMONTHS}" == *"${_log_file}"* ]]; then
            continue;
        fi

        # Stop if the file is not a log file
        if [[ "${_log_file}" != *".log"* ]]; then
            continue;
        fi

        # Stop if the file name does not contain the prefix
        if [[ "${_log_file}" != "debug-"* ]]; then
            echo "Not a supported log file: ${_log_file}";
            continue;
        fi

        # Get the year and month from the log file name
        _log_file_ym="${_log_file/debug\-/}";
        _log_file_ym="${_log_file_ym/.log/}";
        _log_file_ym="${_log_file_ym:0:6}";

        # Check if the first chars are in the excluded list
        if [[ "${_EXCLUDED_YEARMONTHS}" == *"${_log_file_ym}"* ]]; then
            continue;
        fi

        # Create the folder if it does not exist
        if [ ! -d "${_logs_archive}${_log_file_ym}" ]; then
            mkdir -p "${_logs_archive}${_log_file_ym}";
        fi

        # Move the log file to the folder
        mv "${_logs_folder}/${_log_file}" "${_logs_archive}${_log_file_ym}/${_log_file}";

    done;

    # Zip all folders
    cd "${_logs_archive}";
    for _log_file_ym in *; do
        if [ -d "${_log_file_ym}" ]; then
            _log_file_archive="${_log_file_ym##*/}.tar.gz";
            tar -czf "${_logs_archive}${_log_file_archive}" "${_log_file_ym}";
            rm -rf "${_log_file_ym}";
        fi
    done;
    cd "${_CURRENT_DIR}";



}

wputools_archive_logs;
