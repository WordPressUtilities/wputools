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
## Vars
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
_BACKUP_FOLDERS=("wp-content/w3tc-config");

###################################
## Clean
###################################

if [[ "${1}" == 'clean' ]];then
    # Create a tmp directory in _BACKUP_DIR
    _WPUTOOLS_TMP_DIR="${_BACKUP_DIR}tmp${_BACKUP_RAND}/";
    if [[ -d "${_WPUTOOLS_TMP_DIR}" ]];then
        bashutilities_message 'The temporary directory already exists' 'error';
        return 0;
    fi;
    mkdir "${_WPUTOOLS_TMP_DIR}";

    # Build an array of year-month, starting 3 years ago and finishing one month ago
    _current_date=$(date +%Y-%m)
    if [[ "$OSTYPE" == "darwin"* ]]; then
        _start_date=$(date -v-3y +%Y-%m)
    else
        _start_date=$(date --date='-3 years' +%Y-%m)
    fi
    _year_months=()
    while [[ "$_start_date" < "$_current_date" ]]; do
        _year_months+=("$_start_date")
        if [[ "$OSTYPE" == "darwin"* ]]; then
            _start_date=$(date -v+1m -jf "%Y-%m" "$_start_date" +%Y-%m)
        else
            _start_date=$(date --date="$_start_date-01 +1 month" +%Y-%m)
        fi
    done

    # For each year-month, find all files and move them all except the first one to _WPUTOOLS_TMP_DIR
    _WPUTOOLS_FILES_MOVED=0;
    for ym in "${_year_months[@]}"; do
        _BACKUP_FILES=($(find "${_BACKUP_DIR}" -type f -name "*${ym}*" | sort));

        for ((i=1; i<${#_BACKUP_FILES[@]}; i++)); do
            mv "${_BACKUP_FILES[$i]}" "${_WPUTOOLS_TMP_DIR}";
            _WPUTOOLS_FILES_MOVED=$((_WPUTOOLS_FILES_MOVED + 1))
        done
    done;

    # Display a message with the number of files moved
    if [[ "${_WPUTOOLS_FILES_MOVED}" -gt 0 ]];then
        bashutilities_message "${_WPUTOOLS_FILES_MOVED} file(s) moved to ${_WPUTOOLS_TMP_DIR}";
    else
        bashutilities_message "No files moved";
    fi;

    return 0;
fi;


###################################
## Code
###################################

if [[ "${1}" == 'code' ]];then
    if [[ -f "${_CURRENT_DIR}/.git/config" ]];then

        _BACKUP_NAME="${_BACKUP_NAME}-code";

        # Cloning repo with submodules
        git clone "$(git config --get remote.origin.url)" "${_BACKUP_NAME}";
        cd "${_BACKUP_NAME}";
        git submodule update --init --recursive;

        # Removing code
        find  . -name '.git*' -exec rm -rf {} \;

        # Compress
        cd "${_CURRENT_DIR}";
        tar -zcvf "${_BACKUP_NAME}.tar.gz" "${_BACKUP_NAME}";
        rm -rf "${_BACKUP_NAME}";

        return;
    fi;
fi;

###################################
## Backup
###################################

# Create TMP DIR
mkdir "${_BACKUP_NAME}";

# Protect temp dir
echo 'deny from all' > "${_BACKUP_PATH}.htaccess";

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

# Backup folders
for ix in ${!_BACKUP_FOLDERS[*]}
do
    _BACKUP_FOLDER_ITEM="${_CURRENT_DIR}${_BACKUP_FOLDERS[$ix]}";
    if [[ -d "${_BACKUP_FOLDER_ITEM}" ]];then
        _BACKUP_FOLDER_ITEM_NAME=$(basename "${_BACKUP_FOLDER_ITEM}");
        cp -r "${_BACKUP_FOLDER_ITEM}" "${_BACKUP_PATH}${_BACKUP_FOLDER_ITEM_NAME}";
    fi;
done

# Backup DATABASE
_WPCLICOMMAND db export - > "${_BACKUP_FILE}";

# Hook
wputools_execute_file "wputools-backup-after-db-export.sh" "${_BACKUP_FILE}";

# Check dump filesize
_WPUTOOLS_DUMP_FILESIZE=$(wc -c "${_BACKUP_FILE}" | awk '{print $1}')
if [ "${_WPUTOOLS_DUMP_FILESIZE}" -lt "5000" ]; then
    bashutilities_message 'The MySQL dump looks corrupted' 'error';
fi

# Backup crontab
if [ -x "$(command -v crontab)" ]; then
    _WPUTOOLS_HAS_CRONTAB='1';
    # Empty crontab
    if [ $(crontab -l | wc -c) -eq 0 ]; then
        _WPUTOOLS_HAS_CRONTAB='0';
    fi
    # No need to backup
    if [[ ! -z "${_NOBACKUP_CRONTABS}" && "${_NOBACKUP_CRONTABS}" == '1' ]];then
        _WPUTOOLS_HAS_CRONTAB='0';
    fi;
    if [[ "${_WPUTOOLS_HAS_CRONTAB}" == '0' ]];then
        echo '- crontab ignored';
    else
        crontab -l > "${_BACKUP_PATH}crontab.txt";
    fi;
fi

# Backup UPLOADS
if [[ ! -z "${_BACKUP_UPLOADS}" ]];then
    backup_uploads="${_BACKUP_UPLOADS}";
fi;
if [[ -d "wp-content/uploads" ]];then
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
else
    echo "- No uploads folder";
fi;

# Zip TMP DIR
_BACKUP_ARCHIVE="backup-${_BACKUP_NAME}.tar.gz"
if [[ "${backup_topdir}" == 'y' ]];then
    _BACKUP_ARCHIVE="../${_BACKUP_ARCHIVE}";
fi
_BACKUP_ARCHIVE="${_BACKUP_DIR}${_BACKUP_ARCHIVE}";
tar --exclude=".htaccess" -zcvf "${_BACKUP_ARCHIVE}" "${_BACKUP_NAME}";

# Delete TMP DIR
rm -rf "${_BACKUP_NAME}";

echo "# BACKUP IS OVER !";
