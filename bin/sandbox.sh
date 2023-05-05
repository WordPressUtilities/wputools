#!/bin/bash

echo "# SANDBOX";

function wputools__sandbox(){
    # Default values
    local _RAND=$((RANDOM%900+100))
    local _URL="localhost:8${_RAND}"

    # Temp dir
    local _DIR=${_URL/:/_};
    mkdir "${_DIR}";
    cd "${_DIR}";

    # Download WordPress
    wp core download;

    # Download and Install SQLite integration
    git clone https://github.com/aaemnnosttv/wp-sqlite-db.git;
    mv wp-sqlite-db/src/db.php wp-content/db.php;
    rm -rf wp-sqlite-db;

    # Install site
    wp core config \
        --skip-check \
        --dbname=foo \
        --dbuser=bar \
        --dbpass=none;
    wp core install \
        --title="WPUTools Sample Website" \
        --admin_name=admin \
        --admin_email="admin@example.com" \
        --admin_password=admin \
        --url="http://${_URL}/"

    # Create init file
    cat <<EOT >> "init-server.sh"
#!/bin/bash
php -S ${_URL};
EOT

    # Init server
    php -S "${_URL}";
}

wputools__sandbox;
