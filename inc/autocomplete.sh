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
        COMPREPLY=( $(compgen -W "backup bduser cache clean dbimport src self-update update wp wpuwoo" -- $cur) )
    elif [ $COMP_CWORD -eq 2 ]; then
        case "$prev" in
            "cache")
                COMPREPLY=( $(compgen -W "all opcache wprocket w3tc object url" -- $cur) )
            ;;
            "dbimport")
                COMPREPLY=( $( compgen -o plusdirs  -f -X '!*.sql' -- $cur ) )
                COMPREPLY+=( $( compgen -o plusdirs  -f -X '!*.tar.gz' -- $cur ) )
            ;;
            *)
            ;;
        esac
    fi

    return 0
}

complete -F _wputools_complete wputools
