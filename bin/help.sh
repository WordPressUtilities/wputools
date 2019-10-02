#!/bin/bash

echo "# HELP";

cat <<EOF
wputools backup;       # Backup database + wp-config + htaccess + uploads (on demand).
wputools bduser;       # Install SecuPress-Backdoor-User.
wputools cache;        # Clear WordPress cache.
wputools clean;        # Clean WordPress path & files.
wputools import;       # Import posts from a CSV File.
wputools src;          # Go to this tool source.
wputools self-update;  # Update this tool.
wputools update;       # Update your WordPress core and plugins.
EOF

