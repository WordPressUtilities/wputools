#!/bin/bash

echo "# SAMPLE";

###################################
## Initial datas
###################################

_WPUSAMPLE_RAND=$(bashutilities_rand_string 6);
_WPUSAMPLE_FILE="sample-${_WPUSAMPLE_RAND}.php";
_WPUSAMPLE_PATH="${_CURRENT_DIR}${_WPUSAMPLE_FILE}";

###################################
## Copy file
###################################

cp "${_TOOLSDIR}sample.php" "${_WPUSAMPLE_PATH}";

# File will be deleted after use so lets ensure rights are ok.
chmod 0644 "${_WPUSAMPLE_PATH}";

###################################
## Information
###################################

_WPUSAMPLE_TYPE='all';
if [[ "${1}" != "" ]];then
    _WPUSAMPLE_TYPE="${1}";
fi;

_WPUSAMPLE_NUM='5';
if [[ "${2}" != "" ]];then
    _WPUSAMPLE_NUM="${2}";
fi;

if [[ -z "${_WPUTOOLS_UNSPLASH_API_KEY}" ]];then
    _WPUTOOLS_UNSPLASH_API_KEY="";
fi;

# Call file
wputools_call_url "${_HOME_URL}/${_WPUSAMPLE_FILE}?sample_posttype=${_WPUSAMPLE_TYPE}&sample_num=${_WPUSAMPLE_NUM}&unsplash_api_key=${_WPUTOOLS_UNSPLASH_API_KEY}";

# Delete
rm "${_WPUSAMPLE_PATH}";
