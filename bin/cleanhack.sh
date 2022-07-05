#!/bin/bash

echo "# CLEAN HACK";

## TODO
# - Reinstall same language
# - Clean PHP root files (wp-config.php or non WordPress)
# - Clean mu-plugins dir
# - Clean plugins dir
# - Clean uploads dir (remove all non medias files)
# - Clean themes dir

###################################
## Initial checks
###################################

# Extract version
_CURRENT_WORDPRESS=$(_WPCLICOMMAND core version);

###################################
## Purge cache dir
###################################

echo '# Purge cache dir';
rm -rf wp-content/cache/*;

###################################
## Reinstall WordPress Core
###################################

echo '# Reinstall WordPress Core';
rm -rf wp-admin wp-includes;
_WPCLICOMMAND core download --force --skip-content --version="${_CURRENT_WORDPRESS}";
