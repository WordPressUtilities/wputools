#!/bin/bash

echo "# NGINX CONVERT";

function wputools__nginx_convert() {
    local first_line;
    local dir_path;
    local htaccess_file;
    local nginx_file_name;
    local nginx_file;
    local _rand;

    # Initialize nginx.conf file
    nginx_file_name="nginx-$(bashutilities_rand_string 6).conf";
    nginx_file="${_CURRENT_DIR}/${nginx_file_name}";
    echo "" > "${nginx_file}";

    # Find all .htaccess files containing "deny from all" on the first line
    find "${_CURRENT_DIR}" -type f -name ".htaccess" | while read htaccess_file; do
        first_line=$(head -n 1 "$htaccess_file")
        if [[ "$first_line" == "deny from all" ]]; then
            # Extract directory path
            dir_path=$(dirname "$htaccess_file")
            dir_path=${dir_path/$_CURRENT_DIR/}

            # Append nginx configuration for denying access to this directory
    cat <<EOT >> "${nginx_file}";
location = ${dir_path}/ {
    deny all;
    return 404;
}
EOT
        fi
    done

    echo "- ${nginx_file_name} has been created.";
}
wputools__nginx_convert;

