#!/bin/bash

###################################
## Autocomplete commands
###################################

_WPUTOOLS_AUTOCOMPLETE_WPUWOO_ACTION_DIR="${_WPUWOO_ACTION_DIR}";

# Thanks : https://stackoverflow.com/a/5303225
_wputools_complete() {
    local cur prev prev2 dir ext _reply;

    local _base_wp_dir=$(awk -F '/wp-content'  '{print $1}'  <<<  "${PWD}");
    local _base_wp_dir_content="${_base_wp_dir}/wp-content/";
    local _base_wp_dir_plugins="${_base_wp_dir_content}plugins";
    local _list_backup_dir=( "../backups/" "../" "../local-backups/" );
    local _backup_extensions=( ".tar.gz" ".sql" ".sql.gz" );
    local _WPUTOOLS_ROOT_PATH="$( dirname "${BASH_SOURCE[0]}" )/../";
    local _WPCLIPATH="${_WPUTOOLS_ROOT_PATH}wp-cli.phar";

    COMPREPLY=()
    cur=${COMP_WORDS[COMP_CWORD]}
    prev=${COMP_WORDS[COMP_CWORD-1]}

    if [ $COMP_CWORD -eq 1 ]; then
        _reply="";
        # Load extensions
        for dir in "${_WPUTOOLS_ROOT_PATH}extensions"/*/; do
            if [[ -d "$dir" ]]; then
                _reply+="extension-$(basename "$dir") "
            fi
        done
        COMPREPLY=( $(compgen -W "adminer anonymizedb archivelogs backup bduser cache cachewarm clean cleanhack codechecker dbexport dbimport debugfile detecthack diagnostic duplicatemenu generatemenus go importsite login muplugin nginx-convert optimizeimage plugin quickinstall sample sandbox search settings self-update src update wp wpconfig wpuwoo ${_reply}" -- $cur) )
    elif [ $COMP_CWORD -eq 2 ]; then
        case "$prev" in
            "archivelogs")
                COMPREPLY=( $(compgen -W "all" -- $cur) )
            ;;
            "backup")
                COMPREPLY=( $(compgen -W "all clean code" -- $cur) )
            ;;
            "bduser")
                COMPREPLY=( $(compgen -W "admins all-users" -- $cur) )
            ;;
            "cache")
                COMPREPLY=( $(compgen -W "all opcache wprocket w3tc transient fvm object url purge-cli" -- $cur) )
            ;;
            "diagnostic")
                COMPREPLY=( $(compgen -W "code-profiler cli now view web" -- $cur) )
            ;;
            "go")
                COMPREPLY=( $(compgen -W "current_theme mu-plugins plugins themes uploads" -- $cur) )
            ;;
            "generatemenus")
                COMPREPLY=( $(compgen -W "force_add" -- $cur) )
            ;;
            "dbimport")
                COMPREPLY=( $(compgen -W "latest" -- $cur) )
                for dir in "${_list_backup_dir[@]}"; do
                    if [[ -d "${_base_wp_dir}/$dir" ]]; then
                        for ext in "${_backup_extensions[@]}"; do
                            _reply=$(ls -1 "${_base_wp_dir}/$dir" | awk -F'/' '{print "'$dir'"$NF}' | grep "$ext$");
                            COMPREPLY+=( $(compgen -W "${_reply}" -- $cur) );
                        done
                    fi;
                done
            ;;
            "muplugin")
                COMPREPLY=( $(compgen -W "$(cat "${_WPUTOOLS_MUPLUGIN_LIST}" "${_WPUTOOLS_PLUGIN_LIST}" )" -- $cur) )
            ;;
            "optimizeimage")
                _reply=$(ls -1 . 2>/dev/null | grep -E '\.(jpg|jpeg|png|gif|webp)$')
                COMPREPLY=( $(compgen -W "${_reply}" -- $cur) )
            ;;
            "plugin")
                COMPREPLY=( $(compgen -W "$(cat "${_WPUTOOLS_PLUGIN_LIST}" "${_WPUTOOLS_PLUGIN_FAV_LIST}")" -- $cur) )
            ;;
            "sample")
                _reply=$("${_WPCLIPATH}" post-type list --public=1 --fields=name --format=csv 2>/dev/null | tail -n +2);
                COMPREPLY=( $(compgen -W "all comment help user wptest ${_reply}" -- $cur) );
            ;;
            "update")
                COMPREPLY=( $(compgen -W "all-features all-plugins all-submodules core instant-test relaunch-test" -- $cur) )
                if [[ -d "${_base_wp_dir_plugins}" ]];then
                    _reply=$(ls -1 "${_base_wp_dir_plugins}" | awk -F'/' '{print $NF}');
                    COMPREPLY+=( $(compgen -W "${_reply}" -- $cur) );
                fi;
            ;;
            "wp")
                # Thanks to https://github.com/wp-cli/wp-cli/issues/6012
                _reply=$(echo 'foreach (WP_CLI::get_root_command()->get_subcommands() as $name => $details) { echo $name. " "; }' | "${_WPCLIPATH}" shell);
                COMPREPLY=( $(compgen -W "${_reply}" -- $cur) );
            ;;
            "wpuwoo")
                _reply=$(ls -1 "${_WPUTOOLS_AUTOCOMPLETE_WPUWOO_ACTION_DIR}tasks/"*.php | awk -F'/' '{print $NF}');
                _reply=${_reply//\.php/};
                COMPREPLY=( $(compgen -W "${_reply}" -- $cur) );
            ;;
            *)
            ;;
        esac
    elif [ $COMP_CWORD -eq 3 ]; then
        prev2=${COMP_WORDS[COMP_CWORD-2]}

        if [[ "$prev2" == 'wp' ]];then
            # Load all subcommands
            local _tmp_reply='foreach (WP_CLI::get_root_command()->get_subcommands() as $name => $details) {if($name == "';
            _tmp_reply+=$prev;
            _tmp_reply+='") {foreach ($details->get_subcommands() as $subname => $subdetails) { echo $subname. " "; } }} ';
            _reply=$(echo "${_tmp_reply}" | "${_WPCLIPATH}" shell);
            COMPREPLY=( $(compgen -W "${_reply}" -- $cur) );
        fi;

        if [[ "$prev2" == 'wpuwoo' && "$prev" == 'import-csv' ]];then
            COMPREPLY=( $( compgen -o plusdirs  -f -X '!*.csv' -- $cur ) )
        fi;

        if [[ "$prev2" == 'go' ]];then
            # Go to a plugin
            if [[ "$prev" == 'plugins' || "$prev" == 'themes' || "$prev" == 'uploads' || "$prev" == 'mu-plugins' ]];then
                if [[ -d "${_base_wp_dir_content}${prev}" ]];then
                    _reply=$(ls -1d "${_base_wp_dir_content}${prev}"/*/ 2>/dev/null | awk -F'/' '{print $(NF-1)}');
                    COMPREPLY=( $(compgen -W "${_reply}" -- $cur) );
                fi;
            fi;
        fi;

    fi

    return 0
}

complete -F _wputools_complete wputools

