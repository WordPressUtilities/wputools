#!/bin/bash

echo "# SANDBOX";

function wputools__sandbox(){
    # Default values
    local _RAND=$((RANDOM%900+100))
    local _URL="localhost:8${_RAND}"
    local _MODE="sqlite";
    if [[ "${1}" == 'mysql' ]];then
        _MODE="mysql";
    fi;

    local _INSTALL_TYPE="blank";
    local _REPOSITORY="";
    # if command contains an argument containing .git, we consider it as a git url
    if [[ "${1}" == *".git"* ]];then
        _INSTALL_TYPE="git";
        _REPOSITORY="${1}";
    fi;

    echo "# Chosen mode : ${_MODE}";

    # Temp dir
    local _DIR=${_URL/:/_};
    if [[ "${_INSTALL_TYPE}" == 'git' ]];then
        git clone "${_REPOSITORY}" "${_DIR}";
        cd "${_DIR}";
        git submodule update --init --recursive;
    else
        mkdir "${_DIR}";
        cd "${_DIR}";
    fi;
    local _CURRENT_DIR_SANDBOX=$(pwd);

    # Download WordPress
    if [[ "${_MODE}" == 'sqlite' ]];then

        # Download WordPress
        if [[ "${_INSTALL_TYPE}" == 'blank' ]];then
            _WPCLICOMMAND core download ;
        fi;

        # Download and Install SQLite integration
        git clone https://github.com/aaemnnosttv/wp-sqlite-db.git;
        mv wp-sqlite-db/src/db.php wp-content/db.php;
        rm -rf wp-sqlite-db;
    else
        _WPCLICOMMAND core download;
    fi;

    # Install site
    _WPCLICOMMAND config create \
        --skip-check \
        --dbname="${_DIR}" \
        --dbuser=root \
        --dbpass=root \
        --extra-php <<PHP
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
PHP

    if [[ "${_INSTALL_TYPE}" == 'blank' ]];then
        # Create an empty plugins folder
        rm -r "wp-content/plugins/";
        mkdir "wp-content/plugins/";
        # Create a child theme
        mkdir "wp-content/themes/${_DIR}";
        cp -a "${_TOOLSDIR}/sandbox-theme/." "wp-content/themes/${_DIR}/"
    fi;

    if [[ "${_MODE}" == 'mysql' ]];then
        # Database
        _WPCLICOMMAND db create;
    fi;

    # Install
    _WPCLICOMMAND core install \
        --title="WPUToolsSampleWebsite" \
        --admin_name=admin \
        --admin_email="admin@example.com" \
        --admin_password=admin \
        --url="http://${_URL}/"

    # Activate theme
    if [[ "${_INSTALL_TYPE}" == 'blank' ]];then
        _WPCLICOMMAND theme activate "${_DIR}";
    fi;

    # Create init file
    cat <<EOT >> "init-server.sh";
#!/bin/bash

function init_server_sandbox(){
    cd "${_CURRENT_DIR_SANDBOX}";
    php -S ${_URL} &
    open "http://${_URL}";
}
init_server_sandbox;

EOT
    chmod +x "init-server.sh";

    # Init server
    . "init-server.sh";
}

wputools__sandbox "${2}";
