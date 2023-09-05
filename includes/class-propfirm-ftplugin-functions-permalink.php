<?php
function is_propfirm_ftplugin_enabled() {
    $options = get_option('propfirm_ftplugin_settings');
    return isset($options['enable_plugin']) && $options['enable_plugin'] == 'enable';
}

//enable plugin
if (is_propfirm_ftplugin_enabled()) {

add_filter('register_post_type_args', 'ft_remove_cpt_slug', 10, 2);
function ft_remove_cpt_slug($args, $post_type) {
    $options = get_option('propfirm_ftplugin_settings');
    if (isset($options['select_cpt']) && $post_type == $options['select_cpt']) {
        $args['rewrite']['slug'] = '/';
        $args['has_archive'] = false; // Matikan arsip untuk custom post type ini
    }
    return $args;
}

add_action('init', 'ft_add_custom_rewrite_rule');
function ft_add_custom_rewrite_rule() {
    $options = get_option('propfirm_ftplugin_settings');
    if (isset($options['select_cpt'])) {
        add_rewrite_rule('([^/]+)/([^/]+)/?$', 'index.php?post_type=' . $options['select_cpt'] . '&name=$matches[2]', 'top');
    }
}

add_action('template_redirect', 'ft_redirect_to_new_url_structure');
function ft_redirect_to_new_url_structure() {
    global $post;

    $options = get_option('propfirm_ftplugin_settings');
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