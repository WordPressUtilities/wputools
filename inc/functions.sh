#!/bin/bash

###################################
## TEST URLS
###################################

_wputools_test__file="";
if [[ -f "${_CURRENT_DIR}../wputools-urls.txt" ]];then
    _wputools_test__file="${_CURRENT_DIR}../wputools-urls.txt";
fi;
if [[ -f "${_CURRENT_DIR}wputools-urls.txt" ]];then
    _wputools_test__file="${_CURRENT_DIR}wputools-urls.txt";
fi;

_wputools_test_before_content='';
_wputools_test_after_content='';
_wputools_test_before_length=0;
_wputools_test_after_length=0;

function wputools_call_route(){
    . "${_SOURCEDIR}bin/${1}.sh" "${2}" "${3}" "${4}" "${5}";
}

function wputools_test_check_urls(){
    wputools_call_route cache > /dev/null;
    while read line; do
        if [[ "${line}" != "" ]];then
            echo "";
            echo "####################";
            echo "## Test : ${line}";
            echo "####################";
            echo "";
            wget -qO- "${line}";
        fi;
    done < "${_wputools_test__file}"
}

function run_test_after_regenerate(){
    echo '# RUNNING TESTS : AFTER ACTION';
    local _wputools_test_simple_content=$(wputools_test_check_urls);
    echo "${_wputools_test_simple_content}" > diff-after.txt;
    if [[ -f "/usr/bin/opendiff" ]];then
        opendiff diff-before.txt diff-after.txt;
    fi;
}

function run_test_instant(){
    if [[ -n "${_wputools_test__file}" && -f "${_wputools_test__file}" ]];then
        echo '# RUNNING TESTS : INSTANT TEST';
        _wputools_test_instant_content=$(wputools_test_check_urls);
        _current_timestamp=$(date +%Y%m%d-%H%M%S)
        echo "${_wputools_test_instant_content}" > "diff-instant-${_current_timestamp}.txt";
    fi;
}

function run_test_before(){
    if [[ -n "${_wputools_test__file}" && -f "${_wputools_test__file}" ]];then
        echo '# RUNNING TESTS : BEFORE ACTION';
        _wputools_test_before_content=$(wputools_test_check_urls);
        _wputools_test_before_length=${#_wputools_test_before_content};
    fi;
}

function run_test_after(){
    if [[ -n "${_wputools_test__file}" && -f "${_wputools_test__file}" ]];then
        echo '# RUNNING TESTS : AFTER ACTION';
        _wputools_test_after_content=$(wputools_test_check_urls);
        _wputools_test_after_length=${#_wputools_test_after_content};
        if [[ "${_wputools_test_before_length}" != "${_wputools_test_after_length}" ]];then
            echo "Content size has changed on tested URLs :";
            echo "- Before: ${_wputools_test_before_length}";
            echo "- After: ${_wputools_test_after_length}";
            echo "${_wputools_test_before_content}" > diff-before.txt;
            echo "${_wputools_test_after_content}" > diff-after.txt;
            if [[ -f "/usr/bin/opendiff" ]];then
                opendiff diff-before.txt diff-after.txt;
            fi;
        fi
    fi;
}

function wputools_add_files_to_excludes(){
    local _excluded_git_file="${_CURRENT_DIR}/.git/info/exclude";
    local _excluded_git_file_content=$(cat "${_excluded_git_file}");
    local _excluded_content=$(cat "${_TOOLSDIR}/git-excluded-files.txt");
    local _tmpf=$(mktemp);

    # Load arg
    if [[ "${1}" != '' ]];then
        _excluded_content="${_excluded_content}
${1}";
    fi;
    _excluded_content="${_excluded_git_file_content}
${_excluded_content}";

    # Deduplicate lines in excludes
    echo "${_excluded_content}" > "${_excluded_git_file}";
    sort "${_excluded_git_file}" | uniq > "${_tmpf}";
    mv "${_tmpf}" "${_excluded_git_file}";
}

###################################
## Execute if file exists
###################################

function wputools_execute_file(){
    local _STR_FOUND="“${1}” found. Executing ...";
    local _test_files=( "${1}" "${_CURRENT_DIR}../${1}" "${_SOURCEDIR}${1}" )
    for i in "${_test_files[@]}"; do
        if [[ -f "${i}" ]];then
            echo "${_STR_FOUND}";
            . "${i}" "${2}";
            return 1;
        fi;
    done
}

###################################
## Call URL
###################################

function wputools_call_url(){
    curl --connect-timeout "${_WPUTOOLS_CONNECT_TIMEOUT}" -ksL ${_EXTRA_CURL_ARGS} "${1}";
}

###################################
## WPUTools - Getters
###################################

function wputools__get_wp_config_path(){
    local _wpconfigpath="wp-config.php";
    if [[ -f "../wp-config.php" ]];then
        _wpconfigpath="../wp-config.php";
    fi;
    echo "${_wpconfigpath}";
}

function wputools__get_db_prefix(){
    local _TMP_DB_PREFIX;
    _TMP_DB_PREFIX=$(bashutilities_search_extract_file "\$table_prefix" "';" "$(wputools__get_wp_config_path)" );
    _TMP_DB_PREFIX=${_TMP_DB_PREFIX/\'/};
    _TMP_DB_PREFIX=${_TMP_DB_PREFIX/=/};
    echo "${_TMP_DB_PREFIX/ /}";
}

function wputools__get_db_user(){
    bashutilities_search_extract_file__php_constant "DB_USER" "$(wputools__get_wp_config_path)";
}

function wputools__get_db_password(){
    bashutilities_search_extract_file__php_constant "DB_PASSWORD" "$(wputools__get_wp_config_path)";
}

function wputools__get_db_host(){
    bashutilities_search_extract_file__php_constant "DB_HOST" "$(wputools__get_wp_config_path)";
}

function wputools__get_db_name(){
    bashutilities_search_extract_file__php_constant "DB_NAME" "$(wputools__get_wp_config_path)";
}

###################################
## WPUTools - Query
###################################

function wputools_query(){
        # Create temp my.cnf
    local rand_cnf=$(bashutilities_rand_string 6);
    local cnf_file="my-${rand_cnf}-";
    local cnf_file_content=$(cat <<EOF
    [client]
user=$(wputools__get_db_user)
password=$(wputools__get_db_password)
host="$(wputools__get_db_host)"
EOF
);
    echo "${cnf_file_content}" > "${cnf_file}";

    # Do query
    case "${1}" in
        "select")
            mysql --defaults-extra-file="${cnf_file}" --skip-column-names -se "${2}";
        ;;
    esac

    # Remove temp my.cnf
    rm "${cnf_file}";
}

###################################
## WPUTools - Query Select
###################################

function wputools_query_select(){
    wputools_query "select" "USE $(wputools__get_db_name);${1}";
}

###################################
## WPUTools - Option
###################################

function wputools_get_real_option(){
    wputools_query_select "SELECT option_value FROM $(wputools__get_db_prefix)options WHERE option_name='${1}' LIMIT 0,1";
}

###################################
## WPUTools get old URL
###################################

function wputools_get_siteurl(){
    wputools_get_real_option 'siteurl';
}

###################################
## Actions on backup dir
###################################

function wputools_backup_uploads_cleanup(){
    echo '- No cleanup';
}

###################################
## Go with subfolder
###################################

function wputools_go_folder_or_subfolder(){
    local _TMPLINE="... ${1}";
    local _tmp_folder="${_CURRENT_DIR}/wp-content/${1}";
    if [[ "${2}" != '' ]];then
        _tmp_folder="${_tmp_folder}/${2}";
        _TMPLINE="${_TMPLINE}/${2}";
    fi;
    echo "${_TMPLINE}";
    if [[ ! -d "${_tmp_folder}" ]];then
        echo 'This folder does not exists.';
        return 0;
    fi;
    cd "${_tmp_folder}";
}

###################################
## Install plugin
###################################

function wputools_install_plugin_folder(){
    if [[ ! -d "${_PLUGINSMANUALDIR}${1}" ]];then
        echo "The ${1} folder is not available in plugins/";
        return 0;
    fi;
    if [[ -d "${_CURRENT_DIR}wp-content/plugins/${1}" ]];then
        echo "The ${1} plugin is already installed";
    else
        cp -r "${_PLUGINSMANUALDIR}${1}"  "${_CURRENT_DIR}wp-content/plugins/${1}";
    fi;
    _WPCLICOMMAND plugin activate "${1}";
}

###################################
## Copy file
###################################

function wputools_create_random_file(){
    local _RANDFILE_RAND=$(bashutilities_rand_string 6);
    local _RANDFILE_FILE="${1}-${_RANDFILE_RAND}.php";
    local _RANDFILE_PATH="${_CURRENT_DIR}${_RANDFILE_FILE}";
    touch "${_RANDFILE_PATH}";
    chmod 0777 "${_RANDFILE_PATH}";
    echo "${_RANDFILE_FILE}";
}

###################################
## Echo a message if not quiet mode
###################################

function wputools_echo_message(){
    if [[ "${_IS_QUIET_MODE}" == '1' ]];then
        return 0;
    fi;
    echo "${1}";
}

###################################
## Is online
###################################

function wputools_is_online(){
    if [[ "${_WPUTOOLS_IS_ONLINE}" == '1' ]];then
        echo "1";
        return;
    fi;

    # Successful ping
    if ping -q -c 1 1.1.1.1 &>/dev/null; then
        echo "1";
        return;
    fi

    # Github is reachable
    if curl -Is https://raw.githubusercontent.com/ >/dev/null 2>&1; then
        echo "1";
        return;
    fi

    echo "0";

}

###################################
## Convert script arguments to URL arguments
###################################

function wputools_convert_args_to_url(){
    local _ARGS="";
    for arg in "$@"; do
        if [[ $arg == --* ]]; then
            _ARGS="${_ARGS}&${arg:2}";
        fi
    done
    _ARGS="${_ARGS#&}";
    echo "${_ARGS}";
}

###################################
## Check if WordPress is installed
###################################

function wputools_is_wp_installed(){
    local wputools_wp_config_path=$(wputools__get_wp_config_path);
    if [[ ! -f "${wputools_wp_config_path}" ]]; then
        bashutilities_message 'wp-config.php file not found' 'error';
        return 0;
    fi

    # WordPress is not configured
    if [[ "${_HOME_URL}" == '' || "${_SITE_NAME}" == '' ]];then
        bashutilities_message "The WordPress install is not ready." 'error';
        return 0;
    fi;
}

###################################
## Check if it is a multisite
###################################

function wputools_is_multisite(){
    _WPCLICOMMAND site list --field=url >/dev/null 2>&1
}

function wputools_select_multisite(){
    if ! wputools_is_multisite; then
        return;
    fi
    # List all sites home URLs
    echo "Multiple sites detected. Please choose one site to continue:"
    local _wputools_sites=($(wputools_get_multisite_urls))
    select _wputools_site in "${_wputools_sites[@]}"; do
        if [[ -n "$_wputools_site" ]]; then
            _HOME_URL="$_wputools_site"
            break
        else
            echo "Invalid site. Please try again.";
        fi
    done
}

function wputools_get_multisite_urls(){
    if ! wputools_is_multisite; then
        echo "${_HOME_URL}";
        return;
    fi
    _wputools_multisite_urls=$(_WPCLICOMMAND site list --fields=url,archived --format=csv | grep ',0$');
    _wputools_multisite_urls=$(echo "${_wputools_multisite_urls}" | sed 's/,0$//' | tr -d '\r' | tr '\n' ' ');
    echo "${_wputools_multisite_urls}";
}

function wputools_has_browser_available(){
    if [[ ! -f "/usr/bin/open" ]];then
        return 1;
    fi;

    # Check for GUI session
    if [ -n "$DISPLAY" ] || [ -n "$WAYLAND_DISPLAY" ] || [[ "$OSTYPE" == "darwin"* ]]; then
        return 0;
    fi

    return 1;
}

function wputools_is_website_id_valid(){
    if ! wputools_is_multisite; then
        return 1;
    fi
    local _wputools_site_ids=($(_WPCLICOMMAND site list --field=blog_id))
    for id in "${_wputools_site_ids[@]}"; do
        if [[ "$id" == "$1" ]]; then
            return 0;
        fi
    done
    return 1;
}
