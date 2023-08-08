<?php

/* ----------------------------------------------------------
  Disable Gutemberg if needed
---------------------------------------------------------- */

//add_filter('use_block_editor_for_post', '__return_false', 10);
add_action('wp_enqueue_scripts', function () {
    // wp_dequeue_style('wp-block-library');
    // wp_dequeue_style('wp-block-library-theme');
    // wp_dequeue_style('wc-blocks-style');
    // wp_dequeue_style('global-styles');
}, 100);
