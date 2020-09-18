#!/bin/bash

###################################
## Autocomplete commands
###################################

# Thanks : https://stackoverflow.com/a/5303225
_wputools_complete() {
    local cur prev

    COMPREPLY=()
    cur=${COMP_WORDS[COMP_CWORD]}
    prev=${COMP_WORDS[COMP_CWORD-1]}

    if [ $COMP_CWORD -eq 1 ]; then
        COMPREPLY=( $(compgen -W "backup bduser cache cachewarm clean dbimport login muplugin plugin settings self-update src update wp wpconfig wpuwoo" -- $cur) )
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
    fi

    return 0
}

complete -F _wputools_complete wputools

