<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://josh.cusens.au
 * @since             1.0.0
 * @package           Search_Logger
 *
 * @wordpress-plugin
 * Plugin Name:       WP Search Logger
 * Plugin URI:        https://github.com/josh-cusens?tab=repositories
 * Description:       Logs search requests made by a user in searches using the default WP search to a CSV file and displays them in a filterable HTML table.
 * Version:           1.0.0
 * Author:            Josh Cusens
 * Author URI:        https://josh.cusens.au/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       search-logger
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
define( 'SEARCH_LOGGER_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-search-logger-activator.php
 */
function activate_search_logger() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-search-logger-activator.php';
    Search_Logger_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-search-logger-deactivator.php
 */
function deactivate_search_logger() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-search-logger-deactivator.php';
    Search_Logger_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_search_logger' );
register_deactivation_hook( __FILE__, 'deactivate_search_logger' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-search-logger.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_search_logger() {
    $plugin = new Search_Logger\Search_Logger();
    $plugin->run();
}
run_search_logger();

?>
