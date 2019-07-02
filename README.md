# WPU tools

A set of bash commands to power your WordPress.

Uses the amazing WP-CLI !

## How to install

Go to your favorite tools folder :

```git clone https://github.com/WordPressUtilities/wputools```

```cd wputools;_DIR_WPUTOOLS=$(pwd);echo "alias wputools=\". ${_DIR_WPUTOOLS}/wputools.sh\"" >> ~/.bash_profile;```

## How to use

## Update your WordPress core and plugins.

`wputools update;`

## Update this tool

`wputools self-update;`

## Clean WordPress path & files

`wputools clean;
