# WPU tools

A set of bash commands to power your WordPress.

Uses the amazing WP-CLI !

## How to install

Go to your favorite tools folder :

```git clone https://github.com/WordPressUtilities/wputools```

```cd wputools;_DIR_WPUTOOLS=$(pwd);echo "alias wputools=\". ${_DIR_WPUTOOLS}/wputools.sh\"" >> ~/.bash_profile;```

## How to use

### Backup database + wp-config + htaccess + uploads (on demand).

`wputools backup;`

### Install SecuPress-Backdoor-User.

`wputools bduser;`

### Clear WordPress cache.

`wputools cache;`

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
