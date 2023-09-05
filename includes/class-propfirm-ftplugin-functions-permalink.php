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

add_action('template_redirect', 'ft_redirect_old_post_urls');
function ft_redirect_old_post_urls() {
	error_log('template_redirect ft_redirect_old_post_urls hook is working');
    $options = get_option('propfirm_ftplugin_settings');

    // Jika opsi redirect tidak diaktifkan, keluar dari fungsi
    if (!isset($options['select_redirect']) || $options['select_redirect'] !== 'enable') {
        error_log('Redirect not enabled'); // Ini akan mencatat jika opsi redirect tidak diaktifkan
    	return;
	}

    global $post;

    // Pastikan kita berada di halaman single dari custom post type yang relevan dan 'select_cpt' telah diatur
    if (is_singular($options['select_cpt']) && isset($options['select_cpt'])) {
        // Dapatkan kategori dari postingan saat ini
        $categories = get_the_category($post->ID);

        // Jika postingan memiliki kategori
        if ($categories) {
            $category_slug = $categories[0]->slug; // Mengambil slug dari kategori pertama (Anda dapat memodifikasi ini jika diperlukan)

            // Membuat URL baru
            $new_url = home_url("/{$options['select_cpt']}/{$category_slug}/{$post->post_name}/");

            // Jika URL saat ini tidak sama dengan URL baru, lakukan pengalihan
            if ($_SERVER['REQUEST_URI'] !== parse_url($new_url, PHP_URL_PATH)) {
			    error_log('Redirecting to: ' . $new_url); // Ini akan mencatat URL tujuan sebelum pengalihan
			    wp_redirect($new_url, 301);
			    exit;
			}
        }
    }
}

add_action('template_redirect', 'ft_test_template_redirect');
function ft_test_template_redirect() {
    error_log('template_redirect hook is working');
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