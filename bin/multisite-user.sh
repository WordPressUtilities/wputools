#!/bin/bash

echo "# MULTISITE-USER";

###################################
## Help
###################################

if [[ -z "${1}" || "${1}" == 'help' ]];then
    echo "Usage: wputools multisite-user <command> [<args>]";
    echo "";
    echo "Commands:";
    echo "  create <user-login> <user-email>        Create a new user on the main site of the multisite";
    echo "  addto <user-login> <role> [all]         Add an existing user to all or selected sites of the multisite with the specified role";
    echo "";
    return 0;
fi;

###################################
## Create
###################################

if [[ "${1}" == 'create' ]];then

    # Check args
    if [[ -z "${2}" || -z "${3}" ]];then
        echo "Usage: wputools multisite-user create <user-login> <user-email>";
        return 1;
    fi;

    # Check if user exists
    if [[ $(_WPCLICOMMAND  user get "${2}" --field=ID 2>/dev/null) ]];then
        echo "# User '${2}' already exists.";
        return 0;
    fi;

    # Check if email is used
    if _WPCLICOMMAND user list --search="${3}" --search-columns=user_email --field=ID | grep -q .; then
        echo "# Email '${3}' is already used by another user.";
        return 1;
    fi

    # Select multisite if needed
    echo "# Creating user on site : ${_HOME_URL}";

    _WPCLICOMMAND --url="${_HOME_URL}" user create "${2}" "${3}";
fi;


###################################
## Parse all websites and ask if user should be added
###################################

if [[ "${1}" == 'addto' ]]; then

    local _wputools_addto_all="";
    if [[ "${4}" == 'all' ]]; then
        _wputools_addto_all="true";
    fi;

    # Check if user is specified
    if [[ -z "${2}" ]]; then
        echo "Usage: wputools multisite-user addto <user-login> editor [all]";
        return 1;
    fi;

    # Check if role is specified
    if [[ -z "${3}" ]]; then
        echo "Usage: wputools multisite-user addto <user-login> <role> [all]";
        return 1;
    fi;

    # Check if user exists globally
    if [[ ! $(_WPCLICOMMAND  user get "${2}" --field=ID 2>/dev/null) ]];then
        echo "# User '${2}' does not exist.";
        return 1;
    fi;

    _wputools_user_global_id=$(_WPCLICOMMAND  user get "${2}" --field=ID);

    _wputools_site_ids=($(_WPCLICOMMAND site list --field=blog_id));
    for _wputools_site_id in "${_wputools_site_ids[@]}"; do
        _wputools_multisite_url=$(_WPCLICOMMAND site list --blog_id="${_wputools_site_id}" --field=url);

        _wputools_confirm="n";
        if [[ "${_wputools_addto_all}" == "true" && $_wputools_site_id != "1" ]]; then
            _wputools_confirm="y";
        fi;
        if [[ "${_wputools_confirm}" == "n" ]]; then
            read -p "Add user '${2}' to site ${_wputools_multisite_url}? [Y/n]: " _wputools_confirm
        fi;
        if [[ ! "${_wputools_confirm}" =~ ^[Nn]$ ]]; then
            _WPCLICOMMAND --url="${_wputools_multisite_url}" user set-role "${2}" ${3};
            echo "# User '${2}' added to ${_wputools_multisite_url}"
        else
            _WPCLICOMMAND --url="${_wputools_multisite_url}" user remove-role "${2}";
        fi;

    done;

fi;
