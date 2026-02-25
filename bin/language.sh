#!/bin/bash

echo "# LANGUAGE";

# Stay in start directory
cd "${_SCRIPTSTARTDIR}";

###################################
## Help
###################################

if [[ -z "${1}" || "${1}" == 'help' ]];then
    echo "Usage: wputools language <command> [<args>]";
    echo "";
    echo "Commands:";
    echo "  add-string <string>        Add a string to all .po files in the current directory";
    echo "";
    return 0;
fi;

###################################
## Add a string to all language files
###################################

if [ "$1" == "add-string" ]; then
    if [ -z "$2" ]; then
        echo "Usage: wputools add-string <string>"
        return 0;
    fi

    if [ -z "$(ls ./*.po 2>/dev/null)" ]; then
        echo "No .po files found in current directory"
        return 0;
    fi

    for file in *.po; do
        if ! grep -q "msgid \"$2\"" "$file"; then
            echo "Adding string '$2' to $file"
            echo -e "\nmsgid \"$2\"\nmsgstr \"\"" >> "$file"
        else
            echo "String '$2' already exists in $file"
        fi
    done
fi
