<?php
/* Bootstrap injected from _bootstrap.php at copy time — see inc/functions.sh */
/* --- WPU_BOOTSTRAP_END --- */

if (empty($_GET) || !isset($_GET['s'])) {
    http_response_code(400);
    die('Bad Request: Missing search parameters.');
}

global $wpdb;
$search = sanitize_text_field(trim($_GET['s']));
$search_q = "'%" . $wpdb->esc_like($search) . "%'";
$post_results = array();
$term_results = array();

/* System taxonomies excluded from term search */
$excluded_taxonomies = array('nav_menu', 'post_format', 'link_category');
$excluded_taxonomies_q = "'" . implode("','", array_map(array($wpdb, 'esc_like'), $excluded_taxonomies)) . "'";

/* ----------------------------------------------------------
  Search in post
---------------------------------------------------------- */

$results = $wpdb->get_results(
    "SELECT ID, post_title, post_content, post_type
    FROM {$wpdb->posts}
    WHERE (post_title LIKE $search_q OR post_content LIKE $search_q)
    AND post_status = 'publish'"
);

if (!empty($results)) {
    foreach ($results as $post) {
        if (!isset($post_results[$post->ID])) {
            $post_results[$post->ID] = array();
        }
        if (stripos($post->post_content, $search) !== false) {
            $post_results[$post->ID]['post_content'] = true;
        }
        if (stripos($post->post_title, $search) !== false) {
            $post_results[$post->ID]['post_title'] = true;
        }
    }
}

/* ----------------------------------------------------------
  Search in post meta
---------------------------------------------------------- */

$meta_results = $wpdb->get_results(
    "SELECT post_id, meta_key, meta_value
    FROM {$wpdb->postmeta}
    WHERE meta_value LIKE $search_q
    AND post_id IN (
        SELECT ID FROM {$wpdb->posts}
        WHERE post_status = 'publish'
    )",
);

if (!empty($meta_results)) {
    foreach ($meta_results as $meta) {
        if (!isset($post_results[$meta->post_id])) {
            $post_results[$meta->post_id] = array();
        }
        if (stripos($meta->meta_value, $search) !== false) {
            $post_results[$meta->post_id]['post_meta'] = true;
        }
    }
}

/* ----------------------------------------------------------
  Search in terms
---------------------------------------------------------- */

$ensure_term_result = function ($row) use (&$term_results) {
    if (!isset($term_results[$row->term_taxonomy_id])) {
        $term_results[$row->term_taxonomy_id] = array(
            'term_id' => $row->term_id,
            'taxonomy' => $row->taxonomy,
            'name' => $row->name,
            'fields' => array()
        );
    }
};

$term_rows = $wpdb->get_results(
    "SELECT tt.term_taxonomy_id, tt.term_id, tt.taxonomy, tt.description, t.name, t.slug
    FROM {$wpdb->term_taxonomy} tt
    INNER JOIN {$wpdb->terms} t ON t.term_id = tt.term_id
    WHERE (t.name LIKE $search_q OR t.slug LIKE $search_q OR tt.description LIKE $search_q)
    AND tt.taxonomy NOT IN ($excluded_taxonomies_q)"
);

if (!empty($term_rows)) {
    foreach ($term_rows as $row) {
        $ensure_term_result($row);
        if (stripos($row->name, $search) !== false) {
            $term_results[$row->term_taxonomy_id]['fields']['name'] = true;
        }
        if (stripos($row->slug, $search) !== false) {
            $term_results[$row->term_taxonomy_id]['fields']['slug'] = true;
        }
        if (stripos((string) $row->description, $search) !== false) {
            $term_results[$row->term_taxonomy_id]['fields']['description'] = true;
        }
    }
}

/* ----------------------------------------------------------
  Search in term meta
---------------------------------------------------------- */

$term_meta_rows = $wpdb->get_results(
    "SELECT tt.term_taxonomy_id, tt.term_id, tt.taxonomy, t.name, tm.meta_value
    FROM {$wpdb->termmeta} tm
    INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_id = tm.term_id
    INNER JOIN {$wpdb->terms} t ON t.term_id = tt.term_id
    WHERE tm.meta_value LIKE $search_q
    AND tt.taxonomy NOT IN ($excluded_taxonomies_q)"
);

if (!empty($term_meta_rows)) {
    foreach ($term_meta_rows as $row) {
        if (stripos($row->meta_value, $search) === false) {
            continue;
        }
        $ensure_term_result($row);
        $term_results[$row->term_taxonomy_id]['fields']['meta'] = true;
    }
}

/* ----------------------------------------------------------
  Display post results
---------------------------------------------------------- */

echo '# Found ' . count($post_results) . " post(s)\n";

foreach ($post_results as $post_id => $result) {
    $result_string = array();
    if (isset($result['post_title'])) {
        $result_string[] = "Title";
    }
    if (isset($result['post_content'])) {
        $result_string[] = "Content";
    }
    if (isset($result['post_meta'])) {
        $result_string[] = "Meta";
    }
    echo "\n";
    echo get_the_title($post_id) . " (Post ID: {$post_id})\n";
    echo "-> Found in: " . implode(', ', $result_string) . "\n";
    echo "Edit: " . add_query_arg(array(
        'post' => $post_id,
        'action' => 'edit'
    ), admin_url('post.php')) . "\n";
    $post_type_object = get_post_type_object(get_post_type($post_id));
    if ($post_type_object && $post_type_object->public) {
        echo "View: " . get_permalink($post_id) . "\n";
    }
}

/* ----------------------------------------------------------
  Display term results
---------------------------------------------------------- */

echo "\n";
echo '# Found ' . count($term_results) . " term(s)\n";

foreach ($term_results as $term_taxonomy_id => $result) {
    $result_string = array();
    if (isset($result['fields']['name'])) {
        $result_string[] = "Name";
    }
    if (isset($result['fields']['slug'])) {
        $result_string[] = "Slug";
    }
    if (isset($result['fields']['description'])) {
        $result_string[] = "Description";
    }
    if (isset($result['fields']['meta'])) {
        $result_string[] = "Meta";
    }
    echo "\n";
    echo $result['name'] . " (Term ID: {$result['term_id']}, Taxonomy: {$result['taxonomy']})\n";
    echo "-> Found in: " . implode(', ', $result_string) . "\n";
    echo "Edit: " . add_query_arg(array(
        'taxonomy' => $result['taxonomy'],
        'tag_ID' => $result['term_id']
    ), admin_url('term.php')) . "\n";
    $term_link = get_term_link((int) $result['term_id'], $result['taxonomy']);
    if (!is_wp_error($term_link)) {
        echo "View: " . $term_link . "\n";
    }
}
