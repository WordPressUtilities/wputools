#!/bin/bash

###################################
## Autocomplete commands
###################################

_WPUTOOLS_AUTOCOMPLETE_WPUWOO_ACTION_DIR="${_WPUWOO_ACTION_DIR}";

# Thanks : https://stackoverflow.com/a/5303225
_wputools_complete() {
    local cur prev prev2

    local _base_wp_dir=$(awk -F '/wp-content'  '{print $1}'  <<<  "${PWD}");
    local _base_wp_dir_content="${_base_wp_dir}/wp-content/";
    local _base_wp_dir_plugins="${_base_wp_dir_content}plugins";

    COMPREPLY=()
    cur=${COMP_WORDS[COMP_CWORD]}
    prev=${COMP_WORDS[COMP_CWORD-1]}

    if [ $COMP_CWORD -eq 1 ]; then
        COMPREPLY=( $(compgen -W "adminer backup bduser cache cachewarm clean cleanhack codechecker dbexport dbimport detecthack diagnostic duplicatemenu generatemenus go importsite login muplugin plugin sample sandbox settings self-update src update wp wpconfig wpuwoo" -- $cur) )
    elif [ $COMP_CWORD -eq 2 ]; then
        case "$prev" in
            "cache")
                COMPREPLY=( $(compgen -W "all opcache wprocket w3tc transient fvm object url purge-cli" -- $cur) )
            ;;
            "diagnostic")
                COMPREPLY=( $(compgen -W "now view" -- $cur) )
            ;;
            "go")
                COMPREPLY=( $(compgen -W "current_theme mu-plugins plugins themes uploads" -- $cur) )
            ;;
            "dbimport")
                COMPREPLY=( $( compgen -o plusdirs  -f -X '!*.sql' -- $cur ) )
                COMPREPLY+=( $( compgen -o plusdirs  -f -X '!*.tar.gz' -- $cur ) )
                COMPREPLY+=( $( compgen -o plusdirs  -f -X '!*.sql.gz' -- $cur ) )
            ;;
            "muplugin")
                COMPREPLY=( $(compgen -W "$(cat "${_WPUTOOLS_MUPLUGIN_LIST}")" -- $cur) )
            ;;
            "plugin")
                COMPREPLY=( $(compgen -W "$(cat "${_WPUTOOLS_PLUGIN_LIST}" "${_WPUTOOLS_PLUGIN_FAV_LIST}")" -- $cur) )
            ;;
            "sample")
                COMPREPLY=( $(compgen -W "all attachment comment help page post user wptest" -- $cur) )
            ;;
            "update")
                if [[ -d "${_base_wp_dir_plugins}" ]];then
                    _reply=$(ls -1 "${_base_wp_dir_plugins}" | awk -F'/' '{print $NF}');
                    COMPREPLY=( $(compgen -W "${_reply}" -- $cur) );
                fi;
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

        if [[ "$prev2" == 'wpuwoo' && "$prev" == 'import-csv' ]];then
            COMPREPLY=( $( compgen -o plusdirs  -f -X '!*.csv' -- $cur ) )
        fi;

        if [[ "$prev2" == 'go' ]];then
            # Go to a plugin
            if [[ "$prev" == 'plugins' || "$prev" == 'themes' || "$prev" == 'uploads' || "$prev" == 'mu-plugins' ]];then
                if [[ -d "${_base_wp_dir_content}${prev}" ]];then
                    _reply=$(ls -1 "${_base_wp_dir_content}${prev}" | awk -F'/' '{print $NF}');
                    COMPREPLY=( $(compgen -W "${_reply}" -- $cur) );
                fi;
            fi;
        fi;

    fi

    return 0
}

complete -F _wputools_complete wputools

