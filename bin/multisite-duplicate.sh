#!/bin/bash

echo "# MULTISITE-DUPLICATE";

function wputools_multisite_duplicate(){

    ## VARS
    ###################################

    local source_site_id=$(bashutilities_get_user_var "- What is the source site ID?" "1");
    local target_site_url=$(bashutilities_get_user_var "- What is the target site url?" "http://example.test");
    local source_site_url=$(_WPCLICOMMAND site list --field=url --site__in="$source_site_id");
    local source_site_admin=$(_WPCLICOMMAND option get admin_email --url="$source_site_url");
    local rand_site_id=$(bashutilities_rand_string 6);
    local tmp_new_site_id="site$rand_site_id"

    ## CONFIRM
    ###################################

    echo "# Source Site ID: $source_site_id";
    echo "# Source Admin Email: $source_site_admin";
    echo "# Target Site URL: $target_site_url";
    _continue=$(bashutilities_get_yn "- Continue with these settings ?" 'y');
    if [[ $_continue != "y" ]]; then
        return;
    fi


    ## TEMPORARY SITE CREATION
    ###################################

    local new_site_id=$(_WPCLICOMMAND site create \
        --slug="$tmp_new_site_id" \
        --title="$tmp_new_site_id" \
        --email="$source_site_admin" \
        --porcelain);

    echo "# New site created with ID: $new_site_id";

    ## QUICK FIX FOR URLS ON NEW SITE
    ###################################

    if [ "$(echo "$source_site_url" | awk '{print substr($0,length($0),1)}')" = "/" ]; then
        source_site_url="${source_site_url%/}"
    fi
    local new_site_url=$(_WPCLICOMMAND site list --field=url --site__in="$new_site_id");
    if [ "$(echo "$new_site_url" | awk '{print substr($0,length($0),1)}')" = "/" ]; then
        new_site_url="${new_site_url%/}"
    fi
    local new_site_domain=$(echo "$new_site_url" | awk -F[/:] '{print $4}');
    local target_site_domain=$(echo "$target_site_url" | awk -F[/:] '{print $4}');
    _WPCLICOMMAND search-replace "$new_site_url" "$target_site_url" --network --all-tables-with-prefix --quiet;
    _WPCLICOMMAND search-replace "$new_site_domain" "$target_site_domain" --network --all-tables-with-prefix --quiet;
    echo "# Temporary URLs are replaced by target URLs";

    ## DUPLICATE
    ###################################

    # Export database
    local _DB_FILE="source-$rand_site_id.sql"
    local _DBPREFIX=$(_WPCLICOMMAND db prefix);
    local _TABPREFIX_SRC="${_DBPREFIX}${source_site_id}_";
    local _TABPREFIX_NEW="${_DBPREFIX}${new_site_id}_";
    local _TABLES=$(_WPCLICOMMAND db tables --all-tables-with-prefix | grep "${_TABPREFIX_SRC}" | tr '\n' ',');
    _WPCLICOMMAND db export "${_DB_FILE}" --tables="$_TABLES";
    echo "# Database exported to a temp file";

    # Modify table prefixes in SQL file
    bashutilities_sed "s#${_TABPREFIX_SRC}#${_TABPREFIX_NEW}#g" "${_DB_FILE}";

    # Reimport database and replace URLs
    _WPCLICOMMAND db import "${_DB_FILE}";
    _WPCLICOMMAND search-replace "$source_site_url" "$target_site_url" --url="$target_site_url" --all-tables-with-prefix --quiet;
    echo "# Database imported and URLs replaced";

    ## CLEANUP
    ###################################

    # Delete DB file
    rm "$_DB_FILE";

    # Copy uploads
    rsync -ruv "wp-content/uploads/sites/${source_site_id}/" "wp-content/uploads/sites/${new_site_id}/"

    ## Success
    ###################################
    echo "# Done! New site is ready at: $target_site_url (ID: $new_site_id)";

}


wputools_multisite_duplicate;
