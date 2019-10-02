#!/bin/bash

echo "# IMPORT";

###################################
## Calling script
###################################

_IMPORT_FILE="${_CURRENT_DIR}/wputools-import.php";

cp "${_SOURCEDIR}/tools/import.php" "${_IMPORT_FILE}";
bashutilities_sed "s#SCRIPT_DIR#${_SOURCEDIR}tools/#g" "${_IMPORT_FILE}";

cd "${_CURRENT_DIR}";
php "${_IMPORT_FILE}" "${1}";

rm "${_IMPORT_FILE}";
