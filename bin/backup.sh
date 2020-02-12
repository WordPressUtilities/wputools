#!/bin/bash

###################################
## Backup
###################################

_BACKUP_YESS=0;
if [[ "${1}" == '-y' || "${1}" == 'y' ]];then
    _BACKUP_YESS=1;
fi;
_BACKUP_NOOO=0;
if [[ "${1}" == '-n' || "${1}" == 'n' ]];then
    _BACKUP_NOOO=1;
fi;

echo "# BACKUP";

# Vars
_BACKUP_RAND=$(bashutilities_rand_string 6);
_BACKUP_NAME="$(date +%Y-%m-%d-%H%M%S)-${_BACKUP_RAND}";
_BACKUP_PATH="./${_BACKUP_NAME}/";
_BACKUP_FILE="${_BACKUP_PATH}db-${_BACKUP_NAME}.sql";

# Create TMP DIR
mkdir "${_BACKUP_NAME}";

# Backup DATABASE
_WPCLICOMMAND db export - > "${_BACKUP_FILE}";

# Backup htaccess
cp ".htaccess" "${_BACKUP_PATH}htaccess.txt";

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
if [[ "${_BACKUP_YESS}" == '1' ]];then
    backup_uploads='y';
elif [[ "${_BACKUP_NOOO}" == '1' ]];then
    backup_uploads='n';
else
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
tar -zcvf "backup-${_BACKUP_NAME}.tar.gz" "${_BACKUP_NAME}";

# Delete TMP DIR
rm -rf "${_BACKUP_NAME}";

echo "# BACKUP IS OVER !";
