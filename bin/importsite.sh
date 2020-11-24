#!/bin/bash

echo "# IMPORT SITE";

_WPUTOOLS_IMPORT_MODEL=$(cat <<EOF
#!/bin/bash

_WPUTOOLS_IMPORT_HOST="";
_WPUTOOLS_IMPORT_USER="";
_WPUTOOLS_IMPORT_PASS="";
_WPUTOOLS_IMPORT_PATH="~/www/";

# Database ( Can be extracted from wp-config.php )
_WPUTOOLS_IMPORT_DB_NAME="";
_WPUTOOLS_IMPORT_DB_USER="";
_WPUTOOLS_IMPORT_DB_PASSWORD="";
_WPUTOOLS_IMPORT_DB_HOST="";
_WPUTOOLS_IMPORT_DB_PREFIX="";

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
    unset _WPUTOOLS_IMPORT_DB_PREFIX;
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
## Generate repository
###################################

_TMP_GITIGNORE=$(cat <<TXT
wp-content/uploads
wp-content/cache
wp-content/upgrade
wp-config.php*
TXT
);

if [[ ! -d "htdocs/.git" ]];then
    _generate_git_repo=$(bashutilities_get_yn "- Generate a git repository ?" 'y');
    if [[ "${_generate_git_repo}" == 'y' ]];then
        echo "# Generate repository";
        echo "${_TMP_GITIGNORE}" > "htdocs/.gitignore";
        $(cd htdocs && git init && git add .);
    fi;
fi;

###################################
## Extract from wp-config
###################################

_config_file="htdocs/wp-config.php";
if [[ -f "${_config_file}" ]];then
    _TMP_DB_NAME=$(bashutilities_search_extract_file__php_constant "DB_NAME" "${_config_file}");
    _TMP_DB_USER=$(bashutilities_search_extract_file__php_constant "DB_USER" "${_config_file}");
    _TMP_DB_PASSWORD=$(bashutilities_search_extract_file__php_constant "DB_PASSWORD" "${_config_file}");
    _TMP_DB_HOST=$(bashutilities_search_extract_file__php_constant "DB_HOST" "${_config_file}");
    _TMP_DB_PREFIX=$(bashutilities_search_extract_file "\$table_prefix =" "';" "${_config_file}");
    _TMP_DB_PREFIX=${_TMP_DB_PREFIX/\'/};
    if [[ -n "${_TMP_DB_NAME}" && -n "${_TMP_DB_USER}" && -n "${_TMP_DB_PASSWORD}" && -n "${_TMP_DB_HOST}" && -n "${_TMP_DB_PREFIX}" ]];then
        echo "NAME : ${_TMP_DB_NAME}";
        echo "USER : ${_TMP_DB_USER}";
        echo "PASSWORD : ${_TMP_DB_PASSWORD}";
        echo "HOST : ${_TMP_DB_HOST}";
        echo "PREFIX : ${_TMP_DB_PREFIX}";
        _use_tmp_values=$(bashutilities_get_yn "- Use temporary MySQL values?" 'y');
        if [[ "${_use_tmp_values}" == 'y' ]];then
            _WPUTOOLS_IMPORT_DB_NAME="${_TMP_DB_NAME}";
            _WPUTOOLS_IMPORT_DB_USER="${_TMP_DB_USER}";
            _WPUTOOLS_IMPORT_DB_PASSWORD="${_TMP_DB_PASSWORD}";
            _WPUTOOLS_IMPORT_DB_HOST="${_TMP_DB_HOST}";
            _WPUTOOLS_IMPORT_DB_PREFIX="${_TMP_DB_PREFIX}";
        fi;
    fi;
fi;

###################################
## Database infos
###################################

_dump_filename="dblocal.sql.gz";
if [[ ! -z "${_WPUTOOLS_IMPORT_HOST}" && ! -z "${_WPUTOOLS_IMPORT_USER}" && ! -z "${_WPUTOOLS_IMPORT_PASS}" && ! -z "${_WPUTOOLS_IMPORT_DB_NAME}" ]]; then
    # Import Database
    if [[ ! -f "${_dump_filename}" ]];then
        echo "# Importing database";
        ssh "${_WPUTOOLS_IMPORT_USER}"@"${_WPUTOOLS_IMPORT_HOST}" \
            "mysqldump -h ${_WPUTOOLS_IMPORT_DB_HOST} -u ${_WPUTOOLS_IMPORT_DB_USER} -p${_WPUTOOLS_IMPORT_DB_PASSWORD} ${_WPUTOOLS_IMPORT_DB_NAME} | gzip -9" > "${_dump_filename}";
    else
        echo "# Dump already exists";
    fi;
fi;

###################################
## Actions
###################################

cd htdocs;

## WP Config
_generate_wp_config=$(bashutilities_get_yn "- Generate a WP Config ?" 'y');
if [[ "${_generate_wp_config}" == 'y' ]];then
    if [[ -f "wp-config.php" ]];then
        _myrand=$(bashutilities_rand_string 6);
        mv "wp-config.php" "wp-config.php.${_myrand}";
    fi;
    . "${_SOURCEDIR}bin/wpconfig.sh";
fi;

## DB Import
_extract_dump=$(bashutilities_get_yn "- Extract the SQL Dump ?" 'y');
if [[ "${_extract_dump}" == 'y' ]];then
    . "${_SOURCEDIR}bin/dbimport.sh" "../${_dump_filename}";
fi;

cd ..;

###################################
## Reset
###################################

_WPUTOOLS_IMPORT__RESET;
