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

    echo "# Chosen mode : ${_MODE}";

    # Temp dir
    local _DIR=${_URL/:/_};
    mkdir "${_DIR}";
    cd "${_DIR}";
    local _CURRENT_DIR_SANDBOX=$(pwd);

    # Download WordPress
    _WPCLICOMMAND core download;

    if [[ "${_MODE}" == 'sqlite' ]];then
        # Download and Install SQLite integration
        git clone https://github.com/aaemnnosttv/wp-sqlite-db.git;
        mv wp-sqlite-db/src/db.php wp-content/db.php;
        rm -rf wp-sqlite-db;
    fi;

    # Install site
    _WPCLICOMMAND core config \
        --skip-check \
        --dbname="${_DIR}" \
        --dbuser=root \
        --dbpass=root;

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
