#!/bin/bash

if [ ! -f "${_WPCLISRC}" ]; then
    echo '# WP-CLI : Installation in progress';
    curl https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar --output "${_WPCLISRC}";
    chmod +x "${_WPCLISRC}";
fi;
if [[ ! -d ~/.wp-cli && -w ~/ ]];then
    echo '# WP-CLI : creating config folder.';
    mkdir ~/.wp-cli;
fi;
if [[ -d ~/.wp-cli && ! -f ~/.wp-cli/config.yml ]];then
    echo '# WP-CLI : creating config file.';
    echo -e "apache_modules:\n  - mod_rewrite" >> ~/.wp-cli/config.yml
fi;
