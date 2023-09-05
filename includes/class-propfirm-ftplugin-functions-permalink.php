<?php
function is_propfirm_ftplugin_enabled() {
    $options = get_option('propfirm_ftplugin_settings');
    return isset($options['enable_plugin']) && $options['enable_plugin'] == 'enable';
}

//enable plugin
if (is_propfirm_ftplugin_enabled()) {

    // Mengubah link post
    add_filter('post_type_link', 'ft_custom_permalink_structure', 10, 2);
    function ft_custom_permalink_structure($post_link, $post) {
        $options = get_option('propfirm_ftplugin_settings');
        if (isset($options['select_cpt']) && $post->post_type == $options['select_cpt']) {
            $terms = get_the_terms($post->ID, 'category');
            if ($terms) {
                return home_url($terms[0]->slug . '/' . $post->post_name . '/');
            }
        }
        return $post_link;
    }

    // Menambahkan rewrite rules
    add_action('init', 'ft_add_rewrite_rules');
    function ft_add_rewrite_rules() {
        $options = get_option('propfirm_ftplugin_settings');
        global $post;
        // Jika ini adalah halaman atau post biasa, keluar dari fungsi
        if (is_page() || is_singular('post')) {
            return;
        }
        if (isset($options['select_cpt'])) {
            $categories = get_categories(array('hide_empty' => 0));
            foreach ($categories as $category) {
                // Untuk setiap kategori, tambahkan rewrite rule yang spesifik
                add_rewrite_rule('^' . $category->slug . '/([^/]+)/?$', 'index.php?post_type=' . $options['select_cpt'] . '&name=$matches[1]', 'top');
            }
        }
    }


    // Mengubah link kategori
    add_filter('term_link', 'ft_custom_category_permalink', 10, 3);
    function ft_custom_category_permalink($url, $term, $taxonomy) {
        $options = get_option('propfirm_ftplugin_settings');
        if ($taxonomy == 'category' && isset($options['select_cpt'])) {
            return home_url($term->slug . '/');
        }
        return $url;
    }

    // Menambahkan rewrite rules untuk kategori
    add_action('init', 'ft_add_category_rewrite_rules');
    function ft_add_category_rewrite_rules() {
        $options = get_option('propfirm_ftplugin_settings');

        global $post;
        // Jika ini adalah halaman atau post biasa, keluar dari fungsi
        if (is_page() || is_singular('post')) {
            return;
        }

        if (isset($options['select_cpt'])) {
            $categories = get_categories(array('hide_empty' => 0));
            foreach ($categories as $category) {
                add_rewrite_rule('^' . $category->slug . '/?$', 'index.php?category_name=' . $category->slug, 'top');
            }
        }
    }

    add_action('template_redirect', 'ft_redirect_old_cpt_urls_to_new');
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
            $categories = get_the_terms($post->ID, 'category');
            if ($categories && !is_wp_error($categories)) {
                $category_slug = $categories[0]->slug;
                $expected_url = home_url("/{$category_slug}/{$post->post_name}/");
                if ($_SERVER['REQUEST_URI'] != parse_url($expected_url, PHP_URL_PATH)) {
                    wp_redirect($expected_url, 301);
                    exit;
                }
            }
        }
    }

    add_filter('request', 'ft_custom_request_filter');
    function ft_custom_request_filter($query_vars) {
        $options = get_option('propfirm_ftplugin_settings');

        if (isset($options['select_cpt'])) {
            // Jika ini adalah permintaan untuk single post dari custom post type
            if (isset($query_vars['name']) && isset($query_vars['post_type']) && $query_vars['post_type'] == $options['select_cpt']) {
                return $query_vars;
            }

            // Jika ini adalah permintaan untuk kategori
            if (isset($query_vars['category_name'])) {
                $query_vars['post_type'] = $options['select_cpt'];
                return $query_vars;
            }
        }

        return $query_vars;
    }

}

// Flush rewrite rules saat plugin diaktifkan
register_activation_hook(__FILE__, 'ft_flush_rewrite_rules');
function ft_flush_rewrite_rules() {
    ft_add_rewrite_rules();
    flush_rewrite_rules();
}

// Flush rewrite rules saat plugin dinonaktifkan
register_deactivation_hook(__FILE__, 'ft_flush_rewrite_rules_deactivate');
function ft_flush_rewrite_rules_deactivate() {
    flush_rewrite_rules();
}
