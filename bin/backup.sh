#!/bin/bash

###################################
## Backup
###################################

_BACKUP_YESS=0;
if [[ "${1}" == '-y' || "${1}" == 'y' ]];then
    _BACKUP_YESS=1;
fi;

echo "# BACKUP";

# Vars
_BACKUP_RAND=$(openssl rand -hex 4);
_BACKUP_NAME="$(date +%Y-%m-%d-%H%M%S)-${_BACKUP_RAND}";
_BACKUP_PATH="./${_BACKUP_NAME}/";
_BACKUP_FILE="${_BACKUP_PATH}db-${_BACKUP_NAME}.sql";

# Create TMP DIR
mkdir "${_BACKUP_NAME}";

# Backup DATABASE
php "${_WPCLISRC}" db export - > "${_BACKUP_FILE}";
cp "wp-config.php" "${_BACKUP_PATH}wp-config.php";
cp ".htaccess" "${_BACKUP_PATH}htaccess.txt";

# Backup UPLOADS
if [[ "${_BACKUP_YESS}" == '1' ]];then
    backup_uploads='y';
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