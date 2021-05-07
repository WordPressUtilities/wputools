#!/bin/bash

###################################
## Autocomplete commands
###################################

# Thanks : https://stackoverflow.com/a/5303225
_wputools_complete() {
    local cur prev prev2

    COMPREPLY=()
    cur=${COMP_WORDS[COMP_CWORD]}
    prev=${COMP_WORDS[COMP_CWORD-1]}

    if [ $COMP_CWORD -eq 1 ]; then
        COMPREPLY=( $(compgen -W "adminer backup bduser cache cachewarm clean dbexport dbimport importsite login muplugin plugin sample settings self-update src update wp wpconfig wpuwoo" -- $cur) )
    elif [ $COMP_CWORD -eq 2 ]; then
        case "$prev" in
            "cache")
                COMPREPLY=( $(compgen -W "all opcache wprocket w3tc object url" -- $cur) )
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
                COMPREPLY=( $(compgen -W "$(cat "${_WPUTOOLS_PLUGIN_LIST}")" -- $cur) )
            ;;
            "update")
                _reply=$(ls -1 "${_CURRENT_DIR}wp-content/plugins" | awk -F'/' '{print $NF}');
                COMPREPLY=( $(compgen -W "${_reply}" -- $cur) );
            ;;
            "wpuwoo")
                _reply=$(ls -1 "${_WPUWOO_ACTION_DIR}tasks/"*.php | awk -F'/' '{print $NF}');
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

    fi

    return 0
}

complete -F _wputools_complete wputools

