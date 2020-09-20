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

_CACHE_DIRS=(cache/min cache/critical-css cache/busting uploads/cache/fvm);
for _cache_dir in "${_CACHE_DIRS[@]}"; do
    if [[ -d "${_CURRENT_DIR}wp-content/${_cache_dir}/" ]];then
        echo "# Clearing cache dir “${_cache_dir}”";
        rm -rf "${_CURRENT_DIR}wp-content/${_cache_dir}/";
    fi;
done;

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
chmod 0777 "${_STATIC_PATH}";

# Calling url
echo '# Clearing static cache';
curl -ksL ${_EXTRA_CURL_ARGS} "${_HOME_URL}/${_STATIC_FILE}?cache_type=${_cache_type}&cache_arg=${_cache_arg}";
rm "${_STATIC_PATH}";

###################################
## CACHE WARMER
###################################

echo '# Cache warming';
curl -ksL ${_EXTRA_CURL_ARGS} "${_HOME_URL}" > /dev/null;
