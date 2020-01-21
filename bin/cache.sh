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
php "${_WPCLISRC}" rewrite flush --hard;

_cache_type='all';
if [[ "${1}" != "" ]];then
    _cache_type="${1}";
fi;

_cache_arg='';
if [[ "${2}" != "" ]];then
    _cache_arg="${2}";
fi;

###################################
## Clearing Static Cache
###################################

# Initial datas
_STATIC_RAND=$(openssl rand -hex 4);
_STATIC_FILE="cache-${_STATIC_RAND}.php";
_STATIC_PATH="${_CURRENT_DIR}${_STATIC_FILE}";
_HOME_URL=$(php "${_WPCLISRC}" option get home --quiet --skip-plugins --skip-themes --skip-packages);

# Copy file
cp "${_TOOLSDIR}cache.php" "${_STATIC_PATH}";

# File will be deleted after use so lets ensure rights are ok.
chmod 0777 "${_STATIC_PATH}";

# Calling url
echo '# Clearing static cache';
curl -ks "${_EXTRA_CURL_ARGS}" "${_HOME_URL}/${_STATIC_FILE}?cache_type=${_cache_type}&cache_arg=${_cache_arg}";
rm "${_STATIC_PATH}";

###################################
## CACHE WARMER
###################################

echo '# Cache warming';
curl -ks "${_EXTRA_CURL_ARGS}" "${_HOME_URL}" > /dev/null;
