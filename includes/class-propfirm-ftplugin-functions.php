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