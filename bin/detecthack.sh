#!/bin/bash

echo "# DETECT HACK";

###################################
## Initial datas
###################################

_WPUDHK_RAND=$(bashutilities_rand_string 6);
_WPUDHK_FILE="detecthack-${_WPUDHK_RAND}.php";
_WPUDHK_PATH="${_CURRENT_DIR}${_WPUDHK_FILE}";

###################################
## Copy file
###################################

cp "${_TOOLSDIR}detecthack.php" "${_WPUDHK_PATH}";

# File will be deleted after use so lets ensure rights are ok.
chmod 0644 "${_WPUDHK_PATH}";

###################################
## Information
###################################

$_PHP_COMMAND "${_WPUDHK_FILE}";
rm "${_WPUDHK_FILE}";
