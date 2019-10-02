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

        /* Try to extract datas */
        $datas = $this->get_datas_from_csv($argv[1]);
        if (!is_array($datas)) {
            $this->print_message("Invalid valid file provided");
            return;
        }

        /* Parse datas */
        $nb_posts = count($datas);
        foreach ($datas as $i => $data) {
            $ii = $i + 1;
            $total = "${ii} / {$nb_posts} - ";

            /* Create post */
            $post_id = $this->create_post_from_data($data);
            if (is_numeric($post_id)) {
                $this->print_message($total . "Post ID #${post_id} created !");
            } else {
                $this->print_message($total . "Post could not be created");
            }
        }
    }
}

new wputools_import();
