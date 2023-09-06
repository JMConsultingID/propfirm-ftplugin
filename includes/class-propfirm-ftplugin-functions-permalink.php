<?php
function is_propfirm_ftplugin_enabled() {
    $options = get_option('propfirm_ftplugin_settings');
    return isset($options['enable_plugin']) && $options['enable_plugin'] == 'enable';
}

function ft_required_options_set() {
    $options = get_option('propfirm_ftplugin_settings');
    return isset($options['select_cpt']) && !empty($options['select_cpt']) && isset($options['select_taxonomy']) && !empty($options['select_taxonomy']);
}


//enable plugin
if (is_propfirm_ftplugin_enabled()) {
    if (!ft_required_options_set()) {
        return; // Jika custom post type atau custom taxonomy belum diatur, keluar dari fungsi
    }
    // Mengubah link post
    add_filter('post_type_link', 'ft_custom_permalink_structure', 10, 2);
    // Menambahkan rewrite rules
    add_action('init', 'ft_add_rewrite_rules');
    // Mengubah link kategori
    add_filter('term_link', 'ft_custom_category_permalink', 10, 3);
    // Menambahkan rewrite rules untuk kategori
    add_action('init', 'ft_add_category_rewrite_rules');
    // Menambahkan fungsi redirect old permalink to new permalink
    add_action('template_redirect', 'ft_redirect_old_cpt_urls_to_new');
    // Menambahkan fungsi request_filter
    add_filter('request', 'ft_custom_request_filter');    
}

function ft_custom_permalink_structure($post_link, $post) {
    $options = get_option('propfirm_ftplugin_settings');
    if (isset($options['select_cpt']) && $post->post_type == $options['select_cpt']) {
        $terms = get_the_terms($post->ID, $options['select_taxonomy']);
        if ($terms) {
            return home_url($terms[0]->slug . '/' . $post->post_name . '/');
        }
    }
    return $post_link;
}

function ft_add_rewrite_rules() {
    $options = get_option('propfirm_ftplugin_settings');
    global $post;
    // Jika ini adalah halaman atau post biasa, keluar dari fungsi
    if (is_page() || is_singular('post')) {
        return;
    }
    if (isset($options['select_cpt'])) {
        $casino_categories = get_terms(array('taxonomy' => $options['select_taxonomy'], 'hide_empty' => 0));
        foreach ($casino_categories as $category) {
            add_rewrite_rule('^' . $category->slug . '/([^/]+)/?$', 'index.php?post_type=' . $options['select_cpt'] . '&name=$matches[1]', 'top');
        }
    }
}

function ft_custom_category_permalink($url, $term, $taxonomy) {
    $options = get_option('propfirm_ftplugin_settings');
    if ($taxonomy == $options['select_taxonomy'] && isset($options['select_cpt'])) {
        return home_url($term->slug . '/');
    }
    return $url;
}

function ft_add_category_rewrite_rules() {
    $options = get_option('propfirm_ftplugin_settings');

    global $post;
    // Jika ini adalah halaman atau post biasa, keluar dari fungsi
    if (is_page() || is_singular('post')) {
        return;
    }

    if (isset($options['select_cpt'])) {
        $casino_categories = get_terms(array('taxonomy' => $options['select_taxonomy'], 'hide_empty' => 0));
        foreach ($casino_categories as $category) {
            add_rewrite_rule('^' . $category->slug . '/?$', 'index.php?casino-category=' . $category->slug, 'top');
        }
    }
}

function ft_redirect_old_cpt_urls_to_new() {
    $options = get_option('propfirm_ftplugin_settings');

    // Memeriksa apakah select_redirect diaktifkan
    if (!isset($options['select_redirect']) || $options['select_redirect'] !== 'enable') {
        return; // Jika tidak diaktifkan, keluar dari fungsi
    }

    global $post;

    // Jika ini adalah halaman atau post biasa, keluar dari fungsi
    if (is_page() || is_singular('post')) {
        return;
    }

    if (isset($options['select_cpt']) && is_single() && $post->post_type == $options['select_cpt']) {
        $casino_categories = get_the_terms($post->ID, $options['select_taxonomy']);
        if ($casino_categories && !is_wp_error($casino_categories)) {
            $category_slug = $casino_categories[0]->slug;
            $expected_url = home_url("/{$category_slug}/{$post->post_name}/");
            if ($_SERVER['REQUEST_URI'] != parse_url($expected_url, PHP_URL_PATH)) {
                wp_redirect($expected_url, 301);
                exit;
            }
        }
    }
}

function ft_custom_request_filter($query_vars) {
    $options = get_option('propfirm_ftplugin_settings');
    if (isset($query_vars[$options['select_taxonomy']]) && isset($options['select_cpt'])) {
        $query_vars['post_type'] = $options['select_cpt'];
    }
    return $query_vars;
}