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
        if (isset($options['select_cpt'])) {
            add_rewrite_rule('^' . $options['select_cpt'] . '/([^/]+)/([^/]+)/?$', 'index.php?ft_cpt=' . $options['select_cpt'] . '&ft_name=$matches[2]', 'top');
            add_rewrite_rule('^' . $options['select_cpt'] . '/([^/]+)/?$', 'index.php?ft_category_name=$matches[1]', 'top');
        }
    }

    add_filter('query_vars', 'ft_add_query_vars');
    function ft_add_query_vars($vars) {
        $vars[] = 'ft_cpt';
        $vars[] = 'ft_name';
        $vars[] = 'ft_category_name';
        return $vars;
    }

    add_action('pre_get_posts', 'ft_modify_query_based_on_vars');
    function ft_modify_query_based_on_vars($query) {
        if (!is_admin() && $query->is_main_query()) {
            if (get_query_var('ft_cpt') && get_query_var('ft_name')) {
                $query->set('post_type', get_query_var('ft_cpt'));
                $query->set('name', get_query_var('ft_name'));
            } elseif (get_query_var('ft_category_name')) {
                $query->set('category_name', get_query_var('ft_category_name'));
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


    add_action('pre_get_posts', 'ft_modify_category_query');
    function ft_modify_category_query($query) {
        $options = get_option('propfirm_ftplugin_settings');
        if (!is_admin() && $query->is_category() && $query->is_main_query() && isset($options['select_cpt'])) {
            $query->set('post_type', $options['select_cpt']);
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
