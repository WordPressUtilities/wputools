#!/bin/bash

echo "# HELP";

cat <<EOF
# Commands
wputools adminer;               # Open an adminer session.
wputools anonymizedb;           # Anonymize the database.
wputools archivelogs;           # Archive old logs.
wputools backup;                # Backup database + wp-config + htaccess + uploads (on demand).
wputools bduser;                # Install SecuPress-Backdoor-User.
wputools login;                 # Login as the first available administrator.
wputools cache;                 # Clear WordPress cache.
wputools cachewarm;             # Preload WordPress urls.
wputools clean;                 # Clean WordPress path & files.
wputools codechecker;           # Check your code.
wputools debugfile;             # Display the debug file.
wputools detecthack;            # Try to detect hacked files.
wputools cleanhack;             # Try to clean hacked files.
wputools diagnostic;            # Checks that your installation can work properly.
wputools dbexport;              # Export an SQL dump and replace URLs.
wputools dbimport;              # Import an SQL dump.
wputools duplicatemenu;         # Duplicate a menu.
wputools go;                    # Quickly go to a specific folder.
wputools generatemenus;         # Generate default menus.
wputools importsite;            # Import a distant website.
wputools multisite-convert;     # Convert a single site to a multisite network.
wputools multisite-duplicate;   # Duplicate a WordPress site in a multisite network.
wputools multisite-user;        # Manage users in a multisite network.
wputools muplugin;              # Install a WordPressUtilities mu-plugin.
wputools nginx-convert;         # Convert some htaccess rules to nginx
wputools optimizeimage;         # Optimize an image using TinyPNG.
wputools plugin;                # Install a WordPressUtilities plugin.
wputools quickinstall;          # Quick WordPress Install.
wputools sample;                # Insert sample posts & images.
wputools sandbox;               # Create a WordPress sandbox.
wputools search;                # Search content in the database.
wputools self-update;           # Update this tool.
wputools settings;              # Create an override settings file.
wputools src;                   # Go to this tool source.
wputools update;                # Update your WordPress core and plugins.
wputools wp;                    # Execute a WP-CLI Task.
wputools wpconfig;              # Quick Wizard to generate a wp-config.php file.
wputools wpuwoo;                # Execute a WPU Woo Import Export Task.
EOF


if [ -d "${_SOURCEDIR}extensions" ] && [ "$(ls -A "${_SOURCEDIR}extensions")" ] && [ -n "$(find "${_SOURCEDIR}extensions" -mindepth 1 -type d -print -quit)" ] ; then
    echo "# Extensions"
    for dir in "${_SOURCEDIR}extensions"/*/; do
        if [[ -d "$dir" ]]; then
            echo "wputools extension-$(basename "$dir"); "
        fi
    done
fi

