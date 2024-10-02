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
    echo '- Launching purge via CLI';
    $_PHP_COMMAND "${_STATIC_FILE}";
else
    wputools_call_url "${_HOME_URL}/${_STATIC_FILE}?cache_type=${_cache_type}&cache_arg=${_cache_arg}";
fi;
rm "${_CURRENT_DIR}${_STATIC_FILE}";

###################################
## CACHE WARMER
###################################

echo '# Cache warming';
wputools_call_url "${_HOME_URL}" > /dev/null;

# After all
wputools_execute_file "wputools-cache-after-purge.sh";
