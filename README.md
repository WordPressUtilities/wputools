# WPU tools

A set of bash commands to power your WordPress.

Uses the amazing WP-CLI !

## How to install

Go to your favorite tools folder :

```git clone https://github.com/WordPressUtilities/wputools```

```cd wputools;git submodule update --init --recursive;_DIR_WPUTOOLS=$(pwd);echo "alias wputools=\". ${_DIR_WPUTOOLS}/wputools.sh\"" >> ~/.bash_profile;```

## How to use

### Install SecuPress-Backdoor-User.

`wputools bduser;`

### Clean WordPress path & files.

`wputools clean;`

### Go to this tool source.

`wputools src;`

### Update this tool.

`wputools self-update;`

### Update your WordPress core and plugins.

`wputools update;`

## Thanks

* To @boiteaweb for https://github.com/BoiteAWeb/SecuPress-Backdoor-User
