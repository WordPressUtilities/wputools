#!/bin/bash

echo "# CACHE";

###################################
## Clearing opcache
###################################

# Initial datas
_OP_RAND=$(openssl rand -hex 4);
_OP_FILE="opcache-${_OP_RAND}.php";
_OP_PATH="${_CURRENT_DIR}${_OP_FILE}";
_HOME_URL=$(php "${_WPCLISRC}" option get home --quiet --skip-plugins --skip-themes --skip-packages);

# Copy file
cp "${_SOURCEDIR}tools/opcache.php" "${_OP_PATH}";

# File will be deleted after use so lets ensure rights are ok.
chmod 0777 "${_OP_PATH}";

# Calling url
echo '# Clearing opcache';
curl -s "${_HOME_URL}/${_OP_FILE}" > /dev/null;
rm "${_OP_PATH}";

###################################
## Clearing WordPress object cache
###################################

echo '# Clearing WordPress object cache';
wp cache flush;

###################################
## Flush Rewrite rules
###################################

echo '# Flushing Rewrite rules';
wp rewrite flush --hard;

###################################
## CACHE WARMER
###################################

echo '# Cache warming';
curl -s "${_HOME_URL}" > /dev/null;
