#!/bin/bash

echo "# CACHE";

###################################
## Default vars
###################################

if [ -z "${_EXTRA_CURL_ARGS}" ];then
    _EXTRA_CURL_ARGS='';
fi;

_cache_type='all';
if [[ "${1}" != "" ]];then
    _cache_type="${1}";
fi;

_cache_arg='';
if [[ "${2}" != "" ]];then
    _cache_arg="${2}";
fi;

###################################
## Purge WPUTOOLS vars
###################################

if [[ "${_cache_type}" == 'purge-wputools' ]];then
    echo '# Purging WPUTOOLS cache';
    if [[ -n "${_WPUTOOLS_CACHE_DIR}" && -d "${_WPUTOOLS_CACHE_DIR}" ]]; then
        echo "# Deleting all WPUTOOLS cache files";
        rm -rf "${_WPUTOOLS_CACHE_DIR}/"*;
    else
        echo "# Directory not found: ${_WPUTOOLS_CACHE_DIR}";
    fi;
    return 0;
fi;

###################################
## Select multisite
###################################

_wputools_multisite_urls=$(wputools_get_multisite_urls);
for arg in "$@"; do
    # If an argument named url exists, use it
    if [[ "$arg" == *-url=* ]]; then
        local _tmp_home_url=$(wputools_find_url_in_multisite "${arg#*-url=}");
        if [[ -n "${_tmp_home_url}" ]]; then
            _wputools_multisite_urls=("${_tmp_home_url}");
            echo "# Multisite URL selected: ${_tmp_home_url}";
            if [[ "${1}" == "$arg" ]];then
                _cache_type='all';
            fi;
        fi;
    fi;
done;

###################################
## Flush Rewrite rules
###################################

if ! wputools_is_multisite; then
    echo '# Flushing Rewrite rules';
    _WPCLICOMMAND rewrite flush --hard;
else
    # Loop through each multisite URL
    for _wputools_multisite_url in ${_wputools_multisite_urls}; do
        echo "# Flushing rewrite rules for ${_wputools_multisite_url}";
        _WPCLICOMMAND --url="${_wputools_multisite_url}" rewrite flush;
    done
fi;


###################################
## Clearing cache directories
###################################

_CACHE_DIRS=(cache/min cache/critical-css cache/busting);
for _cache_dir in "${_CACHE_DIRS[@]}"; do
    if [[ -d "${_CURRENT_DIR}wp-content/${_cache_dir}/" ]];then
        echo "# Clearing cache dir “${_cache_dir}”";
        rm -rf "${_CURRENT_DIR}wp-content/${_cache_dir}/";
    fi;
done;

###################################
## Performant translation cache
###################################

if [[ -f "${_CURRENT_DIR}.git/config" ]];then
    echo '# Clearing performant translation cache';
    find "${_CURRENT_DIR}wp-content" -type f -name "*.mo.php" -exec rm -f {} +;
fi;

###################################
## Extra directories
###################################

if [ -z "${_EXTRA_CACHE_DIRS}" ];then
    _EXTRA_CACHE_DIRS='';
fi;

for _cache_dir in "${_EXTRA_CACHE_DIRS[@]}"; do
    if [[ "${_cache_dir}" != '' && -d "${_CURRENT_DIR}${_cache_dir}/" ]];then
        echo "# Clearing extra cache dir “${_cache_dir}”";
        rm -rf "${_CURRENT_DIR}${_cache_dir}/";
    fi;
done;

_EXTRA_CACHE_DIRS='';

###################################
## Clearing Static Cache
###################################

# Initial datas
_STATIC_FILE=$(wputools_create_random_file "cache");

# Copy file
cat "${_TOOLSDIR}cache.php" > "${_CURRENT_DIR}${_STATIC_FILE}";

# Calling url
echo '# Clearing static cache';
if [[ "${_cache_type}" == 'purge-cli' ]];then
    _cache_type='all';
    echo '## Launching purge via CLI';
    $_PHP_COMMAND "${_STATIC_FILE}";
else
    for _wputools_multisite_url in ${_wputools_multisite_urls}; do
        echo "## Clearing static cache for ${_wputools_multisite_url}";
        wputools_call_url "${_wputools_multisite_url}/${_STATIC_FILE}?cache_type=${_cache_type}&cache_arg=${_cache_arg}";
    done;
fi;
rm "${_CURRENT_DIR}${_STATIC_FILE}";

###################################
## CACHE WARMER
###################################

echo '# Cache warming';

for _wputools_multisite_url in ${_wputools_multisite_urls}; do
    echo "## Cache warming for ${_wputools_multisite_url}";
    wputools_call_url "${_wputools_multisite_url}" > /dev/null;
done;

# After all
wputools_execute_file "wputools-cache-after-purge.sh";
