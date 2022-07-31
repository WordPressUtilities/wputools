#!/bin/bash

echo "# HELP";

cat <<EOF
wputools adminer;      # Open an adminer session.
wputools backup;       # Backup database + wp-config + htaccess + uploads (on demand).
wputools bduser;       # Install SecuPress-Backdoor-User.
wputools login;        # Login as the first available administrator.
wputools cache;        # Clear WordPress cache.
wputools cachewarm;    # Preload WordPress urls.
wputools clean;        # Clean WordPress path & files.
wputools codechecker;  # Check your code.
wputools detecthack;   # Try to detect hacked files.
wputools cleanhack;    # Try to clean hacked files.
wputools diagnostic;   # Checks that your installation can work properly.
wputools dbexport;     # Export an SQL dump and replace URLs.
wputools dbimport;     # Import an SQL dump.
wputools go;           # Quickly go to a specific folder.
wputools importsite;   # Import a distant website.
wputools muplugin;     # Install a WordPressUtilities mu-plugin.
wputools plugin;       # Install a WordPressUtilities plugin.
wputools sample;       # Insert sample posts & images.
wputools self-update;  # Update this tool.
wputools settings;     # Create an override settings file.
wputools src;          # Go to this tool source.
wputools update;       # Update your WordPress core and plugins.
wputools wp;           # Execute a WP-CLI Task.
wputools wpconfig;     # Quick Wizard to generate a wp-config.php file.
wputools wpuwoo;       # Execute a WPU Woo Import Export Task.
EOF

