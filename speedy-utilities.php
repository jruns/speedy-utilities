<?php

/**
 *
 * @link              https://github.com/jruns
 * @since             0.1
 * @package           SpeedyUtilities
 *
 * @wordpress-plugin
 * Plugin Name:       Speedy Performance Utilities 
 * Plugin URI:        https://github.com/jruns/speedy-utilities
 * Description:       Utilities to improve the performance of your WordPress site.
 * Version:           1.1.0
 * Author:            jruns
 * Author URI:        https://github.com/jruns
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       speedy-utilities
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

define( 'SPEEDY_VERSION', '1.1.0' );
define( 'SPEEDY_BASE_NAME', plugin_basename( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-activator.php
 */
function activate_speedy_utilities() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-activator.php';
	SpeedyUtilities_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-deactivator.php
 */
function deactivate_speedy_utilities() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-deactivator.php';
	SpeedyUtilities_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_speedy_utilities' );
register_deactivation_hook( __FILE__, 'deactivate_speedy_utilities' );

/**
 * The core plugin class that is used to load active utilities,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-speedyutilities.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    0.1.0
 */
function run_speedy_utilities() {

	$plugin = new SpeedyUtilities();
	$plugin->run();

}
run_speedy_utilities();
