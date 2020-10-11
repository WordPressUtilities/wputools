#!/bin/bash

###################################
## Get parameters
###################################

backup_uploads="";
backup_topdir="";
function check_parameters {
    OPTIND=1;
    while getopts "u:t:" option; do
        case "${option}" in
            u)
                backup_uploads=${OPTARG}
            ;;
            t)
                backup_topdir=${OPTARG}
            ;;
        esac
    done
}
check_parameters "$@"

###################################
## Check MySQL
###################################

if [[ ! $(_WPCLICOMMAND db check) ]];then
    echo $(bashutilities_message 'MySQL is not available' 'error');
    return 0;
fi;

###################################
## Backup
###################################

echo "# BACKUP";

# Vars
_SITE_NAME_SLUG=$(bashutilities_string_to_slug "${_SITE_NAME}");
_SITE_NAME_SLUG="${_SITE_NAME_SLUG:0:10}";
_BACKUP_RAND=$(bashutilities_rand_string 6);
_BACKUP_NAME="${_SITE_NAME_SLUG}-$(date +%Y-%m-%d-%H%M%S)-${_BACKUP_RAND}";
_BACKUP_PATH="./${_BACKUP_NAME}/";
_BACKUP_FILE="${_BACKUP_PATH}db-${_BACKUP_NAME}.sql";

# Create TMP DIR
mkdir "${_BACKUP_NAME}";

# Backup DATABASE
_WPCLICOMMAND db export - > "${_BACKUP_FILE}";

# Backup htaccess
cp ".htaccess" "${_BACKUP_PATH}htaccess.txt";

# Backup local overrides
if [[ -f "wp-content/mu-plugins/wpu_local_overrides.php" ]];then
    cp "wp-content/mu-plugins/wpu_local_overrides.php" "${_BACKUP_PATH}wpu_local_overrides.php";
fi;

# Backup wp-config.php
_wp_config_file="";
if [[ -f "wp-config.php" ]];then
    _wp_config_file="wp-config.php";
fi;
if [[ -f "../wp-config.php" ]];then
    _wp_config_file="../wp-config.php";
fi;
if [[ -n "${_wp_config_file}" ]];then
    cp "${_wp_config_file}" "${_BACKUP_PATH}wp-config.php";
fi;

# Backup UPLOADS
if [[ "${backup_uploads}" == '' ]];then
    backup_uploads=$(bashutilities_get_yn "- Backup uploads?" 'n');
fi;
if [[ "${backup_uploads}" == 'y' ]]; then
    # Copy uploads
    cp -La "wp-content/uploads" "${_BACKUP_PATH}uploads";
    # Delete tmp files
    find "${_BACKUP_PATH}uploads" -name '.DS_Store' -type f -delete;
    # Delete logs
    find "${_BACKUP_PATH}uploads" -name '*.log' -type f -delete;
fi;

# Zip TMP DIR
_BACKUP_ARCHIVE="backup-${_BACKUP_NAME}.tar.gz"
if [[ "${backup_topdir}" == 'y' ]];then
    _BACKUP_ARCHIVE="../${_BACKUP_ARCHIVE}";
fi
_BACKUP_ARCHIVE="${_BACKUP_DIR}${_BACKUP_ARCHIVE}";
tar -zcvf "${_BACKUP_ARCHIVE}" "${_BACKUP_NAME}";

# Delete TMP DIR
rm -rf "${_BACKUP_NAME}";

echo "# BACKUP IS OVER !";
