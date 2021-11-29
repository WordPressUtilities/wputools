#!/bin/bash

echo "# CACHE";

###################################
## Default vars
###################################

if [ -z "${_EXTRA_CURL_ARGS}" ];then
    _EXTRA_CURL_ARGS='';
fi;

###################################
## Flush Rewrite rules
###################################

echo '# Flushing Rewrite rules';
_WPCLICOMMAND rewrite flush --hard;

_cache_type='all';
if [[ "${1}" != "" ]];then
    _cache_type="${1}";
fi;

_cache_arg='';
if [[ "${2}" != "" ]];then
    _cache_arg="${2}";
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
## Extra directories
###################################

if [ -z "${_EXTRA_CACHE_DIRS}" ];then
    _EXTRA_CACHE_DIRS='';
fi;

for _cache_dir in "${_EXTRA_CACHE_DIRS[@]}"; do
    if [[ -d "${_CURRENT_DIR}${_cache_dir}/" ]];then
        echo "# Clearing extra cache dir “${_cache_dir}”";
        rm -rf "${_CURRENT_DIR}${_cache_dir}/";
    fi;
done;

_EXTRA_CACHE_DIRS='';

###################################
## Clearing Static Cache
###################################

# Initial datas
_STATIC_RAND=$(bashutilities_rand_string 6);
_STATIC_FILE="cache-${_STATIC_RAND}.php";
_STATIC_PATH="${_CURRENT_DIR}${_STATIC_FILE}";

# Copy file
cp "${_TOOLSDIR}cache.php" "${_STATIC_PATH}";

# File will be deleted after use so lets ensure rights are ok.
chmod 0644 "${_STATIC_PATH}";

# Calling url
echo '# Clearing static cache';
wputools_call_url "${_HOME_URL}/${_STATIC_FILE}?cache_type=${_cache_type}&cache_arg=${_cache_arg}";
rm "${_STATIC_PATH}";

###################################
## CACHE WARMER
###################################

echo '# Cache warming';
wputools_call_url "${_HOME_URL}" > /dev/null;
