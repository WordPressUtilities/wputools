#!/bin/bash

echo "# Going to ...";

case "$1" in
    "current_theme" | "active_theme" )
        _CURRENT_THEME=$($_PHP_COMMAND $_WPCLISRC option get stylesheet --quiet --skip-plugins --skip-themes --skip-packages);
        echo "... current theme : ${_CURRENT_THEME}";
        cd "${_CURRENT_DIR}/wp-content/themes/${_CURRENT_THEME}";
    ;;
    "mu-plugins" | "mu-plugin" | "mu")
        wputools_go_folder_or_subfolder "mu-plugins" "${2}";
    ;;
    "plugins" | "plugin" )
        wputools_go_folder_or_subfolder "plugins" "${2}";
    ;;
    "themes" | "theme" )
        wputools_go_folder_or_subfolder "themes" "${2}";
    ;;
    "uploads" | "upload" )
        wputools_go_folder_or_subfolder "uploads" "${2}";
    ;;
esac
