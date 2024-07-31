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

    # Detect a git repository
    if [[ -d .git ]]; then
        _INSTALL_TYPE="local"
        # Asks the user wants to continue
        local continue_git_detected=$(bashutilities_get_yn "- A git repository has been detected in this folder, do you want to continue and use it ?" 'n')
        if [[ "${continue_git_detected}" == 'n' ]]; then
            return 0;
        fi;
        # Stop if a wp-config file is detected
        if [[ -f wp-config.php || -f ../wp-config.php ]]; then
            bashutilities_message "A wp-config.php file has been detected in this folder or the parent, please remove it before continuing" 'error';
            return 0;
        fi;
    fi;

    echo "# Chosen mode : ${_MODE}";

    # Temp dir
    local _DIR=${_URL/:/_};
    if [[ "${_INSTALL_TYPE}" == 'git' ]];then
        git clone "${_REPOSITORY}" "${_DIR}";
        cd "${_DIR}";
        git submodule update --init --recursive;
    fi;
    if [[ "${_INSTALL_TYPE}" == 'blank' ]];then
        mkdir "${_DIR}";
        cd "${_DIR}";
    fi;
    local _CURRENT_DIR_SANDBOX=$(pwd);

    # Download WordPress
    if [[ "${_INSTALL_TYPE}" == 'blank' ]];then
        _WPCLICOMMAND core download ;
    fi;

    # Download and Install SQLite integration
    if [[ "${_MODE}" == 'sqlite' ]];then
        git clone https://github.com/aaemnnosttv/wp-sqlite-db.git;
        mv wp-sqlite-db/src/db.php wp-content/db.php;
        rm -rf wp-sqlite-db;
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
    else
        # Install first available theme excluding twenty* and wputhemes
        local _THEME=$(wp theme list --status=inactive --field=name | grep -v 'twenty' | grep -v 'WPUT' | head -n 1);
        _WPCLICOMMAND theme activate "${_THEME}";
    fi;

    # Create an admin user
    _WPCLICOMMAND user create admin2 admin2@example.com --user_pass=admin2 --role=administrator;

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

    if [[ "${_INSTALL_TYPE}" == 'local' ]];then
    cat <<EOT >> "clean-server.sh";
#!/bin/bash

function clean_server_sandbox(){
    rm -r "${_CURRENT_DIR_SANDBOX}/wp-content/database";
    rm -r "${_CURRENT_DIR_SANDBOX}/wp-config.php";
    rm -r "${_CURRENT_DIR_SANDBOX}/clean-server.sh";
    rm -r "${_CURRENT_DIR_SANDBOX}/init-server.sh";
    rm -r "${_CURRENT_DIR_SANDBOX}/wp-content/db.php";
}
clean_server_sandbox;

EOT
    chmod +x "clean-server.sh";
    fi;

    # Init server
    . "init-server.sh";
}

wputools__sandbox "${2}";
