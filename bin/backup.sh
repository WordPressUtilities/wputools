#!/bin/bash

###################################
## Backup
###################################

echo "# BACKUP";

# Vars
_BACKUP_NAME="$(date +%Y-%m-%d-%H%M%S)";
_BACKUP_PATH="./${_BACKUP_NAME}/";
_BACKUP_FILE="${_BACKUP_PATH}db-${_BACKUP_NAME}.sql";

# Create TMP DIR
mkdir "${_BACKUP_NAME}";

# Backup DATABASE
wp db export - > "${_BACKUP_FILE}";
cp "wp-config.php" "${_BACKUP_PATH}wp-config.php";
cp ".htaccess" "${_BACKUP_PATH}htaccess.txt";

# Backup UPLOADS
backup_uploads=$(bashutilities_get_yn "- Backup uploads?" 'n');
if [[ "${backup_uploads}" == 'y' ]]; then
    cp -a "wp-content/uploads" "${_BACKUP_PATH}uploads";
fi;

# Zip TMP DIR
tar -zcvf "backup-${_BACKUP_NAME}.tar.gz" "${_BACKUP_NAME}";

# Delete TMP DIR
rm -rf "${_BACKUP_NAME}";

echo "# BACKUP IS OVER !";
