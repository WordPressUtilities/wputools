#!/bin/bash

echo "# SAMPLE";

###################################
## Initial datas
###################################

_WPUSAMPLE_RAND=$(bashutilities_rand_string 6);
_WPUSAMPLE_FILE="sample-${_WPUSAMPLE_RAND}.php";
_WPUSAMPLE_PATH="${_CURRENT_DIR}${_WPUSAMPLE_FILE}";

###################################
## Copy file
###################################

cp "${_TOOLSDIR}sample.php" "${_WPUSAMPLE_PATH}";

# File will be deleted after use so lets ensure rights are ok.
chmod 0644 "${_WPUSAMPLE_PATH}";

###################################
## Information
###################################

# Call file
curl -ksL ${_EXTRA_CURL_ARGS} "${_HOME_URL}/${_WPUSAMPLE_FILE}";

# Delete
rm "${_WPUSAMPLE_PATH}";
