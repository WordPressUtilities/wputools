#!/bin/bash

echo "# CLEAN HACK";

## TODO
# - Reinstall same language
# - Clean PHP root files (wp-config.php or non WordPress)
# - Clean mu-plugins dir
# - Clean plugins dir
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
## Delete invalid PHP Files
###################################

echo '# Delete invalid PHP Files';
grep -Ril "<?php" wp-content/{uploads,languages} | xargs rm

###################################
## Reinstall WordPress Core
###################################

echo '# Reinstall WordPress Core';
rm -rf wp-admin wp-includes;
_WPCLICOMMAND core download --force --skip-content --version="${_CURRENT_WORDPRESS}";
