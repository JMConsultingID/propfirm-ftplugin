<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://fundedtrading.com
 * @since             1.0.0
 * @package           Propfirm_Ftplugin
 *
 * @wordpress-plugin
 * Plugin Name:       FT Plugin - Funded Trading
 * Plugin URI:        https://fundedtrading.com
 * Description:       This plugin is to support Funded Trading Website Technology
 * Version:           1.0.0
 * Author:            Ardika JM Consulting
 * Author URI:        https://fundedtrading.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       propfirm-ftplugin
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PROPFIRM_FTPLUGIN_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-propfirm-ftplugin-activator.php
 */
function activate_propfirm_ftplugin() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-propfirm-ftplugin-activator.php';
	Propfirm_Ftplugin_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-propfirm-ftplugin-deactivator.php
 */
function deactivate_propfirm_ftplugin() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-propfirm-ftplugin-deactivator.php';
	Propfirm_Ftplugin_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_propfirm_ftplugin' );
register_deactivation_hook( __FILE__, 'deactivate_propfirm_ftplugin' );

function filter_action_propfirm_ftplugin_links( $links ) {
     $links['settings'] = '<a href="' . admin_url( 'admin.php?page=propfirm-ftplugin' ) . '">' . __( 'Settings', 'propfirm-ftplugin' ) . '</a>';
     $links['support'] = '<a href="' . admin_url( 'admin.php?page=propfirm-ftplugin' ) . '">' . __( 'Doc', 'propfirm-ftplugin' ) . '</a>';
     return $links;
}
add_filter( 'plugin_action_links_propfirm-ftplugin/propfirm-ftplugin.php', 'filter_action_propfirm_ftplugin_links', 10, 1 );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-propfirm-ftplugin.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-propfirm-ftplugin-functions.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-propfirm-ftplugin-functions-permalink.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_propfirm_ftplugin() {

	$plugin = new Propfirm_Ftplugin();
	$plugin->run();

}
run_propfirm_ftplugin();
