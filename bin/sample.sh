#!/bin/bash

echo "# SAMPLE";

###################################
## Initial datas
###################################

_WPUSAMPLE_RAND=$(bashutilities_rand_string 6);
_WPUSAMPLE_FILE="sample-${_WPUSAMPLE_RAND}.php";
_WPUSAMPLE_PATH="${_CURRENT_DIR}${_WPUSAMPLE_FILE}";

###################################
## Help
###################################

if [[ "${1}" == 'help' || "${1}" == '--quiet' ]];then
cat <<TXT
## Generate 5 posts
wputools sample post

## Generate 10 users
wputools sample user 10
TXT
return 0;
fi;

###################################
## WP Test
###################################

function wputools__sample_wptest(){
    # Check if plugin exists
    local _HAD_IMPORTER_PLUGIN="0";
    if [[ -d "${_CURRENT_DIR}wp-content/plugins/wordpress-importer" ]];then
        _HAD_IMPORTER_PLUGIN="1";
    fi;

    # Add plugin WordPress Importer
    _WPCLICOMMAND plugin install wordpress-importer --activate;

    # Load wptest file
    curl -OL https://raw.githubusercontent.com/poststatus/wptest/master/wptest.xml

    # Import the file, then delete it.
    _WPCLICOMMAND import wptest.xml --authors=create;
    rm wptest.xml

    # Delete plugin if added only for this command
    if [[ "${_HAD_IMPORTER_PLUGIN}" == '0' ]];then
        rm -rf "${_CURRENT_DIR}wp-content/plugins/wordpress-importer";
    fi;
}

if [[ "${1}" == "wptest" ]];then
    wputools__sample_wptest;
    return;
fi;

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
