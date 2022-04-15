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
    bashutilities_message 'MySQL is not available' 'error';
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
_BACKUP_FILES=(".htaccess" "wp-content/mu-plugins/wpu_local_overrides.php" "wp-config.php" "../wp-config.php")

# Create TMP DIR
mkdir "${_BACKUP_NAME}";

# Backup DATABASE
_WPCLICOMMAND db export - > "${_BACKUP_FILE}";

# Backup files
for ix in ${!_BACKUP_FILES[*]}
do
    _BACKUP_FILE_ITEM="${_CURRENT_DIR}${_BACKUP_FILES[$ix]}";
    if [[ -f "${_BACKUP_FILE_ITEM}" ]];then
        _BACKUP_FILE_ITEM_NAME=$(basename "${_BACKUP_FILE_ITEM}");
        # Prevent invisible files
        if [[ ${_BACKUP_FILE_ITEM_NAME::1} == "." ]] ;then
            _BACKUP_FILE_ITEM_NAME="${_BACKUP_FILE_ITEM_NAME:1}.txt"
        fi;
        cp "${_BACKUP_FILE_ITEM}" "${_BACKUP_PATH}${_BACKUP_FILE_ITEM_NAME}";
    fi;
done

# Backup crontab
if [ -x "$(command -v crontab)" ]; then
    _HAS_CRONTAB='1';
    # Empty crontab
    if [ $(crontab -l | wc -c) -eq 0 ]; then
        _HAS_CRONTAB='0';
    fi
    # No need to backup
    if [[ ! -z "${_NOBACKUP_CRONTABS}" && "${_NOBACKUP_CRONTABS}" == '1' ]];then
        _HAS_CRONTAB='0';
    fi;
    if [[ "${_HAS_CRONTAB}" == '0' ]];then
        echo '- crontab ignored';
    else
        crontab -l > "${_BACKUP_PATH}crontab.txt";
    fi;
fi


# Backup UPLOADS
if [[ ! -z "${_BACKUP_UPLOADS}" ]];then
    backup_uploads="${_BACKUP_UPLOADS}";
fi;
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

    wputools_backup_uploads_cleanup "${_BACKUP_PATH}uploads";
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
