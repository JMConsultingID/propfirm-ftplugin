<?php
// Menambahkan menu di admin
add_action('admin_menu', 'propfirm_ftplugin_menu');

function propfirm_ftplugin_menu() {
    add_menu_page('FT Plugin', 'FT Plugin', 'manage_options', 'propfirm-ftplugin', 'propfirm_ftplugin_settings_page', 'dashicons-admin-generic', 22);
    add_submenu_page('propfirm-ftplugin','FT Permalink','FT Permalink','manage_options','propfirm-ftplugin-permalink','propfirm_ftplugin_permalink_settings_page'
    );
}

// Fungsi untuk menampilkan halaman settings
function propfirm_ftplugin_settings_page() {
    $settings_link = admin_url('admin.php?page=propfirm-ftplugin-permalink');
    ?>
    <div class="wrap">
        <h2>FT Plugin - Funded Trading</h2>
        <p>This plugin is to support Funded Trading Website Technology, Enjoy Using this Plugin.</p>
        <p>Setup Plugin :
        <ol>
            <li><a href="<?php echo esc_url($settings_link); ?>">Go to FT Plugin Permalink Settings</a></li>
        </ol>
    </div>
    <?php
}

// Fungsi untuk menampilkan halaman settings
function propfirm_ftplugin_permalink_settings_page() {
    ?>
    <div class="wrap">
        <h2>FT Permalink Settings</h2>
        <p>This is the settings page for the FT Permalink plugin. You can change the permalink structure for custom post types here.<br/> 
        After making changes in the FT Permalink Plugin, do the Following:
        <ol>
            <li>Make sure to reactivate your plugin so that the rewrite rules are updated.</li>
            <li>You may need to flush the rewrite rules in WordPress. You can do this by going back to <strong>"Settings" > "Permalinks"</strong> page in your WordPress dashboard and click <strong>"Save Changes"</strong> to ensure the rewrite rules are updated.</li>
            <li>Enjoy Using this Plugin.</li>
        </ol>
        </p> <!-- Deskripsi yang ditambahkan -->
        <form method="post" action="options.php">
            <?php
            settings_fields('propfirm_ftplugin_settings_group');
            do_settings_sections('propfirm-ftplugin');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Mendaftarkan setting dan field
add_action('admin_init', 'propfirm_ftplugin_settings_init');

function propfirm_ftplugin_settings_init() {
    register_setting('propfirm_ftplugin_settings_group', 'propfirm_ftplugin_settings');
    add_settings_section('propfirm_ftplugin_general_section', 'General Settings', null, 'propfirm-ftplugin');
    add_settings_field('ft_enable_plugin', 'Enable Plugin', 'ft_enable_plugin_callback', 'propfirm-ftplugin', 'propfirm_ftplugin_general_section');
    add_settings_field('ft_select_cpt', 'Select Custom Post Type', 'ft_select_cpt_callback', 'propfirm-ftplugin', 'propfirm_ftplugin_general_section');
    add_settings_field('ft_select_taxonomy', 'Select Taxonomy', 'ft_select_taxonomy_callback', 'propfirm-ftplugin', 'propfirm_ftplugin_general_section');
    add_settings_field('ft_archive_enable', 'Post Type Archive', 'ft_post_type_archive_callback', 'propfirm-ftplugin', 'propfirm_ftplugin_general_section');
    add_settings_field('ft_select_redirect_old_url', 'Redirect old URL', 'ft_select_redirect_callback', 'propfirm-ftplugin', 'propfirm_ftplugin_general_section');
    add_settings_field('ft_flush_rewrite', 'Flush Rewrite Rules', 'ft_flush_rewrite_rules_callback', 'propfirm-ftplugin', 'propfirm_ftplugin_general_section');
    //add_settings_field('ft_reset_settings', 'Reset Plugin Settings', 'ft_reset_settings_callback', 'propfirm-ftplugin', 'propfirm_ftplugin_general_section');
}

function ft_enable_plugin_callback() {
    $options = get_option('propfirm_ftplugin_settings');
    $value = isset($options['enable_plugin']) ? $options['enable_plugin'] : 'disable';
    echo '<select name="propfirm_ftplugin_settings[enable_plugin]">
            <option value="enable" '.selected($value, 'enable', false).'>Enable</option>
            <option value="disable" '.selected($value, 'disable', false).'>Disable</option>
          </select>';
}

function ft_select_cpt_callback() {
    $options = get_option('propfirm_ftplugin_settings');
    $value = isset($options['select_cpt']) ? $options['select_cpt'] : '';
    $post_types = get_post_types(array('public' => true), 'objects');
    echo '<select name="propfirm_ftplugin_settings[select_cpt]">';
    foreach ($post_types as $post_type) {
        echo '<option value="'.$post_type->name.'" '.selected($value, $post_type->name, false).'>'.$post_type->label.'</option>';
    }
    echo '</select>';
}

function ft_select_taxonomy_callback() {
    $options = get_option('propfirm_ftplugin_settings');
    $selected_taxonomy = isset($options['select_taxonomy']) ? $options['select_taxonomy'] : '';

    $taxonomies = get_taxonomies(array('public' => true), 'objects');

    echo '<select name="propfirm_ftplugin_settings[select_taxonomy]">';
    foreach ($taxonomies as $taxonomy) {
        echo '<option value="' . esc_attr($taxonomy->name) . '" ' . selected($selected_taxonomy, $taxonomy->name, false) . '>' . esc_attr($taxonomy->name) . '-' . esc_html($taxonomy->labels->name) . '</option>';
    }
    echo '</select>';
}

function ft_post_type_archive_callback() {
    $options = get_option('propfirm_ftplugin_settings');
    $value = isset($options['archive_enable']) ? $options['archive_enable'] : 'disable';
    echo '<select name="propfirm_ftplugin_settings[archive_enable]">
            <option value="enable" '.selected($value, 'enable', false).'>Enable</option>
            <option value="disable" '.selected($value, 'disable', false).'>Disable</option>
          </select>';
}


function ft_select_redirect_callback() {
    $options = get_option('propfirm_ftplugin_settings');
    $value = isset($options['select_redirect']) ? $options['select_redirect'] : 'disable';
    echo '<select name="propfirm_ftplugin_settings[select_redirect]">
            <option value="enable" '.selected($value, 'enable', false).'>Enable</option>
            <option value="disable" '.selected($value, 'disable', false).'>Disable</option>
          </select>';
}

function ft_flush_rewrite_rules_callback() {
    echo '<input type="submit" name="ft_flush_rewrite" value="Flush Rewrite Rules" class="button">';
}

if (isset($_POST['ft_flush_rewrite'])) {
    flush_rewrite_rules();
    // Anda bisa menambahkan pesan admin jika Anda mau
    add_action('admin_notices', 'ft_flush_rewrite_notice');
}

function ft_flush_rewrite_notice() {
    echo '<div class="updated"><p>Rewrite rules have been flushed.</p></div>';
}


function ft_reset_settings_callback() {
    echo '<input type="submit" name="ft_reset_settings" value="Reset Settings" class="button">';
}

add_action('admin_init', 'ft_handle_reset_settings');
function ft_handle_reset_settings() {
    if (isset($_POST['ft_reset_settings'])) {
        delete_option('propfirm_ftplugin_settings');
        add_action('admin_notices', 'ft_settings_reset_notice');
    }
}

function ft_settings_reset_notice() {
    echo '<div class="updated"><p>Plugin settings have been reset.</p></div>';
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