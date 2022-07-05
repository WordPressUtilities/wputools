#!/bin/bash

echo "# DETECT HACK";

###################################
## Initial datas
###################################

_WPUDHK_RAND=$(bashutilities_rand_string 6);
_WPUDHK_FILE="detecthack-${_WPUDHK_RAND}.php";
_WPUDHK_PATH="${_CURRENT_DIR}${_WPUDHK_FILE}";
_WPUDHK_DIR="tmpwp${_WPUDHK_RAND}";

###################################
## Questions
###################################

_WPUDHK_COMPARE_WP=$(bashutilities_get_yn "- Compare core code to a fresh WordPress install?" 'y');
_WPUDHK_COMPARE_PLUG=$(bashutilities_get_yn "- Compare each plugin code to a fresh plugin install?" "${_WPUDHK_COMPARE_WP}");

###################################
## Loading WordPress
###################################

mkdir "${_WPUDHK_DIR}";

# Copy wp-config to allow plugin install to work
cp wp-config.php "${_WPUDHK_DIR}";

# Extract version
_CURRENT_WORDPRESS=$(_WPCLICOMMAND core version);

# Downloading test version
if [[ "${_WPUDHK_COMPARE_WP}" == 'y' ]];then
echo "# Downloading core WordPress to have a clean base to compare";
_WPCLICOMMAND core download \
    --quiet \
    --skip-plugins \
    --skip-themes \
    --path="${_WPUDHK_DIR}" \
    --version="${_CURRENT_WORDPRESS}";
fi;

if [[ "${_WPUDHK_COMPARE_PLUG}" == 'y' ]];then
echo "# Downloading plugins to have a clean base to compare";
_WPPLUGINSTMPCOUNT=0;
_WPPLUGINSTMPLIST=$(_WPCLICOMMAND plugin list --format=csv --fields=name,version);
_WPPLUGINSTMPLISTSIZE=$(_WPCLICOMMAND plugin list --format=csv | grep -c '^');
for line in ${_WPPLUGINSTMPLIST}; do
    _WPPLUGINSTMPCOUNT=$((++_WPPLUGINSTMPCOUNT));
    _TMPLINE=(${line//,/ })
    if [[ ${_TMPLINE[1]} != '' && ${_TMPLINE[1]} != 'version' ]];then
        echo "-- Line ${_WPPLUGINSTMPCOUNT}/${_WPPLUGINSTMPLISTSIZE} : Downloading ${_TMPLINE[0]} v ${_TMPLINE[1]}";
        _WPCLICOMMAND plugin install "${_TMPLINE[0]}" \
            --version="${_TMPLINE[1]}" \
            --force \
            --path="${_WPUDHK_DIR}"\
            --quiet;
    fi;
done
fi;

###################################
## Prepare file
###################################

# Copy file
echo "<?php \$detecthack_file='${_WPUDHK_FILE}';include '${_TOOLSDIR}detecthack/header.php'; " > "${_WPUDHK_PATH}";

# File will be deleted after use so lets ensure rights are ok.
chmod 0644 "${_WPUDHK_PATH}";

###################################
## Trigger detection
###################################

$_PHP_COMMAND "${_WPUDHK_FILE}" --dir="${_WPUDHK_DIR}";

###################################
## Clean
###################################

# Detection script
rm "${_WPUDHK_FILE}";

# TMP WordPress
rm -r "${_WPUDHK_DIR}";

