<?php

/* Native boostrap to then include lib from another path */
require_once 'wp-load.php';
wp();

/* Load files */
require_once 'SCRIPT_DIR/wpuwooimportexport/inc/bootstrap.php';

class wputools_import extends WPUWooImportExport {
    public function __construct() {
        global $argv;

        /* Check file */
        if (!isset($argv[1]) || !file_exists($argv[1])) {
            $this->print_message("No valid file provided");
            return;
        }
        $this->sync_posts_from_csv($argv[1], array(
            'debug_type' => 'print'
        ));

    }

}

new wputools_import();
