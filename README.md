# WPU tools

A set of bash commands to power your WordPress.

Uses the amazing WP-CLI !

## How to install

Go to your favorite tools folder :

```
git clone https://github.com/WordPressUtilities/wputools
```

Add CLI shortcut :

```
. wputools/inc/installer.sh;
```

## How to use

### Open an adminer session.

`wputools adminer;`

### Archive old logs

`wputools archivelogs;`

### Anonymize the database.

`wputools anonymizedb;`

### Backup database + wp-config + htaccess + uploads (on demand).

`wputools backup;`

#### Backup database + wp-config + htaccess + uploads in top dir.

`wputools backup -t "y";`

#### Backup database + wp-config + htaccess + uploads without asking.

`wputools backup -u "y";`

### Install SecuPress-Backdoor-User.

`wputools bduser;`

### Login as the first available administrator.

`wputools login;`

### Quick Wizard to generate a wp-config.php file.

`wputools wpconfig;`

### Clear WordPress cache.

`wputools cache;`

### Preload WordPress urls.

`wputools cachewarm;`

### Clean WordPress path & files.

`wputools clean;`

### Check your code.

`wputools codechecker;`

### Import a distant website.

`wputools importsite conf.sh;`

### Display the debug file.

`wputools debugfile;`

### Checks that your installation can work properly.

`wputools diagnostic;`

### Try to detect hacked files.

`wputools detecthack;`

### Try to clean hacked files.

`wputools cleanhack;`

### Export an SQL dump and replace URLs.

`wputools dbexport https://github.com;`

### Import an SQL dump.

`wputools dbimport dump.sql;`

### Quickly go to a specific folder.

`wputools go mu-plugins;`

### Duplicate a menu.

`wputools duplicatemenu;`

### Generate default menus.

`wputools generatemenus;`

### Import posts from a CSV File.

`wputools wpuwoo import-csv file.csv`

### Export post #10.

`wputools wpuwoo import-export-post export 10`

### Import post #10.

`wputools wpuwoo import-export-post import 10`

### Install a WordPressUtilities mu-plugin

`wputools mu-plugin wpu_file_cache;`

### Convert some htaccess rules to nginx

`wputools nginx-convert;`

### Install a WordPressUtilities plugin

`wputools plugin wpuloginas;`

### Quick WordPress Install.

`wputools quickinstall;`

### Create an override settings file.

`wputools settings;`

### Insert sample posts & images

`wputools sample;`

### Create a WordPress sandbox.

`wputools sandbox;`

### Search content in the database.

`wputools search;`

### Go to this tool source.

`wputools src;`

### Update this tool.

`wputools self-update;`

### Update your WordPress core and plugins.

`wputools update;`

## Override

You can add a `wputools-local.sh` file at the root of your WordPress install or in the folder above.

```bash
#!/bin/bash

# Arguments for CURL if the site is password protected
_EXTRA_CURL_ARGS="-u user:password";

# Static home URL if needed
_HOME_URL="http://example.com";

# Site name if needed
_SITE_NAME="MYSITENAME";

# URL replace format to trigger a search-replace after dbimport
_WPDB_REPLACE_BEFORE="http://example-before.com";
_WPDB_REPLACE_AFTER="http://example-after.com";

# BACKUP DIRECTORY
_BACKUP_DIR="~/MYBACKUPDIR/";

# DISABLE BACKUP FOR UPLOADS
_BACKUP_UPLOADS="n";

# IGNORE LOCALOVERRIDES WHEN IMPORTING
_WPUTOOLS_DBIMPORT_IGNORE_LOCALOVERRIDES='1';

# DISABLE BACKUP FOR CRONTAB
_NOBACKUP_CRONTABS="1";

# CORE UPDATE TYPE
_WPUTOOLS_CORE_UPDATE_TYPE="major";

# EXTRA CACHE DIRECTORIES
_EXTRA_CACHE_DIRS=(wp-content/uploads/wpufilecache)

# Override WP-CLI Version or PHP binary
_PHP_COMMAND='/Applications/MAMP/bin/php/php5.4.45/bin/php';
_WPCLICOMMAND(){
    $_PHP_COMMAND $_WPCLISRC $@;
}

# Clean some folders before backup with uploads
function wputools_backup_uploads_cleanup(){
    rm -rf "${1}/wpufilecache";
}

```

## Thanks

* To @JulioPotier for https://github.com/JulioPotier/SecuPress-Backdoor-User
* To @vrana for https://github.com/vrana/adminer
