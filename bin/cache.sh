#!/bin/bash

echo "# CACHE";

###################################
## Flush Rewrite rules
###################################

echo '# Flushing Rewrite rules';
wp rewrite flush --hard;

###################################
## Clearing Static Cache
###################################

# Initial datas
_STATIC_RAND=$(openssl rand -hex 4);
_STATIC_FILE="cache-${_STATIC_RAND}.php";
_STATIC_PATH="${_CURRENT_DIR}${_STATIC_FILE}";
_HOME_URL=$(php "${_WPCLISRC}" option get home --quiet --skip-plugins --skip-themes --skip-packages);

# Copy file
cp "${_SOURCEDIR}tools/cache.php" "${_STATIC_PATH}";

# File will be deleted after use so lets ensure rights are ok.
chmod 0777 "${_STATIC_PATH}";

# Calling url
echo '# Clearing static cache';
curl -ko - "${_HOME_URL}/${_STATIC_FILE}" > /dev/null;
rm "${_STATIC_PATH}";

###################################
## CACHE WARMER
###################################

echo '# Cache warming';
curl -ks - "${_HOME_URL}" > /dev/null;
