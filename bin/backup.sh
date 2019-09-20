#!/bin/bash

###################################
## Backup
###################################

echo "# BACKUP";

# Vars
_BACKUP_NAME="$(date +%Y-%m-%d-%H%M%S)";
_BACKUP_FILE="./${_BACKUP_NAME}/db-${_BACKUP_NAME}.sql";

# Create TMP DIR
mkdir "${_BACKUP_NAME}";

# Backup DATABASE
wp db export - > "${_BACKUP_FILE}";
cp "wp-config.php" "./${_BACKUP_NAME}/wp-config.php";
cp ".htaccess" "./${_BACKUP_NAME}/htaccess.txt";

# Zip TMP DIR
tar -zcvf "backup-${_BACKUP_NAME}.tar.gz" "${_BACKUP_NAME}";

# Delete TMP DIR
rm -rf "${_BACKUP_NAME}";
