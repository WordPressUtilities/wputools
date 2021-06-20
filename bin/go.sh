#!/bin/bash

echo "# Going to ...";

case "$1" in
    "current_theme" | "active_theme" )
        _CURRENT_THEME=$($_PHP_COMMAND $_WPCLISRC option get stylesheet --quiet --skip-plugins --skip-themes --skip-packages);
        echo "... current theme : ${_CURRENT_THEME}";
        cd "${_CURRENT_DIR}/wp-content/themes/${_CURRENT_THEME}";
    ;;
    "mu-plugins" | "mu-plugin" | "mu")
        echo "... mu-plugins";
        cd "${_CURRENT_DIR}/wp-content/mu-plugins";
    ;;
    "plugins" | "plugin" )
        echo "... plugins";
        cd "${_CURRENT_DIR}/wp-content/plugins";
    ;;
    "themes" | "theme" )
        echo "... themes";
        cd "${_CURRENT_DIR}/wp-content/themes";
    ;;
    "uploads" | "upload" )
        echo "... uploads";
        cd "${_CURRENT_DIR}/wp-content/uploads";
    ;;
esac
