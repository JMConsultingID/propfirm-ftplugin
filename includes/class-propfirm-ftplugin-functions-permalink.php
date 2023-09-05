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
        add_rewrite_rule('^' . $options['select_cpt'] . '/([^/]+)/?$', 'index.php?category_name=$matches[1]', 'top');
    }
}

add_action('pre_get_posts', 'ft_modify_category_query');
function ft_modify_category_query($query) {
    $options = get_option('propfirm_ftplugin_settings');
    if (!is_admin() && $query->is_category() && $query->is_main_query() && isset($options['select_cpt'])) {
        $query->set('post_type', $options['select_cpt']);
    }
}


add_action('init', 'ft_add_old_cpt_rewrite_rule');
function ft_add_old_cpt_rewrite_rule() {
    $options = get_option('propfirm_ftplugin_settings');
    if (isset($options['select_cpt'])) {
        // Tambahkan rewrite rule untuk format URL lama
        add_rewrite_rule('^' . $options['select_cpt'] . '/([^/]+)/?$', 'index.php?post_type=' . $options['select_cpt'] . '&name=$matches[1]', 'top');
    }
}


add_action('template_redirect', 'ft_redirect_old_cpt_urls');
function ft_redirect_old_cpt_urls() {
    global $post;

    $options = get_option('propfirm_ftplugin_settings');
    if (is_singular($options['select_cpt'])) {
        // Dapatkan kategori pertama dari post
        $categories = get_the_terms($post->ID, 'category');
        if ($categories && !is_wp_error($categories)) {
            $category = array_shift($categories);
            $category_slug = $category->slug;

            // Membuat URL baru
            $new_url = home_url($options['select_cpt'] . '/' . $category_slug . '/' . $post->post_name . '/');
            
            // Jika URL saat ini tidak sama dengan URL baru, arahkan ulang
            if (get_permalink($post->ID) !== $new_url) {
                wp_redirect($new_url, 301); // 301 adalah kode status untuk pengalihan permanen
                exit;
            }
        }
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