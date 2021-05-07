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

### Import a distant website.

`wputools importsite conf.sh;`

### Export an SQL dump and replace URLs.

`wputools dbexport https://github.com;`

### Import an SQL dump.

`wputools dbimport dump.sql;`

### Import posts from a CSV File.

`wputools wpuwoo import-csv file.csv`

### Export post #10.

`wputools wpuwoo import-export-post export 10`

### Import post #10.

`wputools wpuwoo import-export-post import 10`

### Install a WordPressUtilities mu-plugin

`wputools mu-plugin wpu_file_cache;`

### Install a WordPressUtilities plugin

`wputools plugin wpuloginas;`

### Create an override settings file.

`wputools settings;`

### Insert sample posts & images

`wputools sample;`

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

# DISABLE BACKUP FOR CRONTAB
_NOBACKUP_CRONTABS="1";

# Override WP-CLI Version or PHP binary
_PHP_COMMAND='/Applications/MAMP/bin/php/php5.4.45/bin/php';
_WPCLICOMMAND(){
    $_PHP_COMMAND $_WPCLISRC $@;
}

```

## Thanks

* To @JulioPotier for https://github.com/JulioPotier/SecuPress-Backdoor-User
