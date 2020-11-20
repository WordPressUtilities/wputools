#!/bin/bash

echo "# IMPORT SITE";

_WPUTOOLS_IMPORT_MODEL=$(cat <<EOF
#!/bin/bash

_WPUTOOLS_IMPORT_HOST="";
_WPUTOOLS_IMPORT_USER="";
_WPUTOOLS_IMPORT_PASS="";
_WPUTOOLS_IMPORT_PATH="~/www/";

# Database
_WPUTOOLS_IMPORT_DB_NAME="";
_WPUTOOLS_IMPORT_DB_USER="";
_WPUTOOLS_IMPORT_DB_PASSWORD="";
_WPUTOOLS_IMPORT_DB_HOST="";

EOF
);

function _WPUTOOLS_IMPORT__RESET(){
    unset _WPUTOOLS_IMPORT_HOST;
    unset _WPUTOOLS_IMPORT_USER;
    unset _WPUTOOLS_IMPORT_PASS;
    unset _WPUTOOLS_IMPORT_PATH;
    unset _WPUTOOLS_IMPORT_DB_NAME;
    unset _WPUTOOLS_IMPORT_DB_USER;
    unset _WPUTOOLS_IMPORT_DB_PASSWORD;
    unset _WPUTOOLS_IMPORT_DB_HOST;
}

###################################
## Test Access
###################################

if [[ ! -f "${2}" ]];then
    echo 'Usage : wputools importsite conf.sh';
    echo "${_WPUTOOLS_IMPORT_MODEL}";
    _WPUTOOLS_IMPORT__RESET;
    return 0;
fi;

if [[ ! -f "${2}" ]];then
    echo 'Please create a config file';
    echo "${_WPUTOOLS_IMPORT_MODEL}";
    _WPUTOOLS_IMPORT__RESET;
    return 0;
fi;

. "${2}";

if [[ -z "${_WPUTOOLS_IMPORT_HOST}" ]];then
    echo "Config file is invalid (HOST)";
    echo "${_WPUTOOLS_IMPORT_MODEL}";
    _WPUTOOLS_IMPORT__RESET;
    return 0;
fi;

if [[ -z "${_WPUTOOLS_IMPORT_USER}" ]];then
    echo "Config file is invalid (USER)";
    echo "${_WPUTOOLS_IMPORT_MODEL}";
    _WPUTOOLS_IMPORT__RESET;
    return 0;
fi;

if [[ -z "${_WPUTOOLS_IMPORT_PASS}" ]];then
    echo "Config file is invalid (PASS)";
    echo "${_WPUTOOLS_IMPORT_MODEL}";
    _WPUTOOLS_IMPORT__RESET;
    return 0;
fi;

if [[ -z "${_WPUTOOLS_IMPORT_PATH}" ]];then
    echo "Config file is invalid (PATH)";
    echo "${_WPUTOOLS_IMPORT_MODEL}";
    _WPUTOOLS_IMPORT__RESET;
    return 0;
fi;

###################################
## Copy site
###################################

echo '# Copying Site';

rsync -ruv \
    --exclude '.git' \
    --exclude 'wp-content/cache' \
    --exclude 'wp-content/uploads' \
    --exclude 'wp-content/upgrade' \
    "${_WPUTOOLS_IMPORT_USER}@${_WPUTOOLS_IMPORT_HOST}:${_WPUTOOLS_IMPORT_PATH}" htdocs/;

if [[ ! -d "htdocs/wp-content/uploads" ]];then
    echo '# Creating upload dir';
    mkdir htdocs/wp-content/uploads;
fi;

###################################
## Import database
###################################

if [[ ! -z "${_WPUTOOLS_IMPORT_HOST}" && ! -z "${_WPUTOOLS_IMPORT_USER}" && ! -z "${_WPUTOOLS_IMPORT_PASS}" && ! -z "${_WPUTOOLS_IMPORT_PATH}" ]]; then
    if [[ ! -f "dblocal.sql.gz" ]];then
        echo "# Importing database"
        ssh "${_WPUTOOLS_IMPORT_USER}"@"${_WPUTOOLS_IMPORT_HOST}" \
            "mysqldump -h ${_WPUTOOLS_IMPORT_DB_HOST} -u ${_WPUTOOLS_IMPORT_DB_USER} -p${_WPUTOOLS_IMPORT_DB_PASSWORD} ${_WPUTOOLS_IMPORT_DB_NAME} | gzip -9" > dblocal.sql.gz
    else
        echo "# Dump already exists";
    fi;
fi;


