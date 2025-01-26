#!/bin/bash

wputools_echo_message "# SAMPLE";

# Check Install
_wputools_is_wp_installed=$(wputools_is_wp_installed);
if [[ "${_wputools_is_wp_installed}" != '' ]];then
    echo "${_wputools_is_wp_installed}";
    return 0;
fi;

###################################
## Initial datas
###################################

_WPUSAMPLE_FILE=$(wputools_create_random_file "sample");

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

cat "${_TOOLSDIR}sample.php" > "${_CURRENT_DIR}${_WPUSAMPLE_FILE}";

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

_WPUSAMPLE_EXTRA='';
if [[ "${3}" != "" ]];then
    _WPUSAMPLE_EXTRA="${3}";
fi;

if [[ -z "${_WPUTOOLS_UNSPLASH_API_KEY}" ]];then
    _WPUTOOLS_UNSPLASH_API_KEY="";
fi;

# Call file
wputools_call_url "${_HOME_URL}/${_WPUSAMPLE_FILE}?sample_posttype=${_WPUSAMPLE_TYPE}&sample_num=${_WPUSAMPLE_NUM}&sample_extra=${_WPUSAMPLE_EXTRA}&unsplash_api_key=${_WPUTOOLS_UNSPLASH_API_KEY}";

# Delete
rm "${_CURRENT_DIR}${_WPUSAMPLE_FILE}";
