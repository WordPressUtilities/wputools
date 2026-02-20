#!/bin/bash

echo "# MULTISITE-CONVERT";

function wputools_multisite_convert(){

    if wputools_is_multisite; then
        echo "# This is already a multisite installation";
        return;
    fi

    local _home_url=$(_WPCLICOMMAND option get home);

    local _continue=$(bashutilities_get_yn "- Are you sure you want to convert this single site to a multisite network ?" 'n');
    if [[ $_continue != "y" ]]; then
        bashutilities_message "Operation cancelled.";
        return;
    fi

    # Disable all plugins before conversion to avoid issues
    local active_plugins=$(_WPCLICOMMAND plugin list --status=active --field=name);
    if [[ -n "$active_plugins" ]]; then
        echo "# Deactivating all plugins before conversion...";
        _WPCLICOMMAND plugin deactivate --all;
    fi

    # Add multisite constants to wp-config.php
    _WPCLICOMMAND config set WP_ALLOW_MULTISITE true --raw;

    echo "# Multisite conversion completed.";
    echo "Please follow the instructions in the WordPress dashboard to complete the network setup.";
    echo "${_home_url}/wp-admin/network.php";

}

wputools_multisite_convert;
