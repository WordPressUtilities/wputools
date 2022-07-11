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
## Purging cache dir
###################################

echo '# Purging cache dir';
rm -rf wp-content/cache/*;

###################################
## Deleting invalid PHP Files
###################################

echo '# Deleting invalid PHP Files';
grep -Ril "<?php" wp-content/{uploads,languages} | xargs rm

###################################
## Deleting non-PHP files containing PHP
###################################

echo '# Deleting non-PHP files containing PHP'
grep -Ril "<?php" --exclude=\*.{php,md,txt,rst,txt,js,phar,dist} | xargs rm

###################################
## Cleaning PHP files
###################################

echo '# Cleaning PHP files';
grep -Ril "<?php       " . | xargs sed -i "" "s/^<\?php         .*/<\?php/";
grep -Ril "wp_create_user(\'" . | xargs sed -i "" "s/wp_create_user('.*/false;/";

###################################
## Reinstalling WordPress Core
###################################

echo '# Reinstalling WordPress Core';
rm -rf wp-admin wp-includes;
_WPCLICOMMAND core download --force --version="${_CURRENT_WORDPRESS}";
