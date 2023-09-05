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
    // Pastikan ini adalah custom post type yang Anda inginkan
    $options = get_option('propfirm_ftplugin_settings');
    if (isset($options['select_cpt']) && $post->post_type == $options['select_cpt']) {
        $terms = get_the_terms($post->ID, 'category');
        if ($terms) {
            return home_url($post->post_type . '/' . $terms[0]->slug . '/' . $post->post_name . '/');
        }
    }
    return $post_link;
}
// Menambahkan rewrite rules
add_action('init', 'ft_add_rewrite_rules');
function ft_add_rewrite_rules() {
    $options = get_option('propfirm_ftplugin_settings');
    if (isset($options['select_cpt'])) {
        add_rewrite_rule('^' . $options['select_cpt'] . '/([^/]+)/([^/]+)/?$', 'index.php?post_type=' . $options['select_cpt'] . '&name=$matches[2]', 'top');
    }
}

add_action('init', 'ft_modify_cpt_args');
function ft_modify_cpt_args() {
    $options = get_option('propfirm_ftplugin_settings');
    if (isset($options['select_cpt'])) {
        $post_type_object = get_post_type_object($options['select_cpt']);
        if ($post_type_object) {
            $post_type_object->has_archive = true;
            register_post_type($options['select_cpt'], $post_type_object);
        }
    }
}


// Mengubah link kategori
add_filter('term_link', 'ft_custom_category_permalink', 10, 3);
function ft_custom_category_permalink($url, $term, $taxonomy) {
    $options = get_option('propfirm_ftplugin_settings');
    if ($taxonomy == 'category' && isset($options['select_cpt'])) {
        return home_url($options['select_cpt'] . '/' . $term->slug . '/');
    }
    return $url;
}


// Menambahkan rewrite rules untuk kategori
add_action('init', 'ft_add_category_rewrite_rules');
function ft_add_category_rewrite_rules() {
    $options = get_option('propfirm_ftplugin_settings');
    if (isset($options['select_cpt'])) {
        // Hanya tambahkan rewrite rule jika permintaan bukan ke post
        if (!get_page_by_path(get_query_var('name'), OBJECT, $options['select_cpt'])) {
            add_rewrite_rule('^' . $options['select_cpt'] . '/([^/]+)/?$', 'index.php?category_name=$matches[1]', 'top');
        }
    }
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
    global $post;

    $options = get_option('propfirm_ftplugin_settings');
    if (isset($options['select_cpt']) && is_single() && $post->post_type == $options['select_cpt']) {
        $categories = get_the_terms($post->ID, 'category');
        if ($categories && !is_wp_error($categories)) {
            $category_slug = $categories[0]->slug; // Ambil kategori pertama jika ada beberapa kategori
            $expected_url = home_url("/{$options['select_cpt']}/{$category_slug}/{$post->post_name}/");
            if ($_SERVER['REQUEST_URI'] != parse_url($expected_url, PHP_URL_PATH)) {
                wp_redirect($expected_url, 301);
                exit;
            }
        }
    } elseif (isset($options['select_cpt']) && is_category()) {
        // Jika kita berada di halaman kategori, pastikan kita tidak mengarahkan ulang
        remove_action('template_redirect', 'ft_redirect_old_cpt_urls_to_new');
    }
}





}
// end enable plugin

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