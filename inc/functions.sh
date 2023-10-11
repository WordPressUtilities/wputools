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
        echo '# RUNNING TESTS : BEFORE ACTION';
        _wputools_test_instant_content=$(wputools_test_check_urls);
        echo "${_wputools_test_instant_content}" > diff-instant.txt;
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
    local _TMP_DB_PREFIX=$(bashutilities_search_extract_file "\$table_prefix" "';" $(wputools__get_wp_config_path) );
    _TMP_DB_PREFIX=${_TMP_DB_PREFIX/\'/};
    _TMP_DB_PREFIX=${_TMP_DB_PREFIX/=/};
    echo "${_TMP_DB_PREFIX/ /}";
}

function wputools__get_db_user(){
    bashutilities_search_extract_file__php_constant "DB_USER" $(wputools__get_wp_config_path) ;
}

function wputools__get_db_password(){
    bashutilities_search_extract_file__php_constant "DB_PASSWORD" $(wputools__get_wp_config_path) ;
}

function wputools__get_db_host(){
    bashutilities_search_extract_file__php_constant "DB_HOST" $(wputools__get_wp_config_path) ;
}

function wputools__get_db_name(){
    bashutilities_search_extract_file__php_constant "DB_NAME" $(wputools__get_wp_config_path) ;
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
    cd "${_tmp_folder}";
}
