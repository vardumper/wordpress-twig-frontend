<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://example.com
 * @since             1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:       WordpPress Twig Frontend
 * Plugin URI:        https://erikpoehler.com/wordpress-twig-frontend/
 * Description:       The fastest WordPress frontend on the planet.
 * Version:           1.1.0
 * Author:            Erik Pöhler
 * Author URI:        https://erikpoehler.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wordpress-twig-frontend
 * Domain Path:       /languages
 */

namespace Teuton\WordpressTwigFrontend;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

die(WPFRONTROOT);

error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED ^ E_WARNING ^ E_USER_ERROR ^ E_USER_NOTICE ^ E_USER_WARNING);

define('EP_FRONT_ROOT', dirname(__FILE__));

// We load Composer's autoload file
require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-plugin-name-activator.php
 */
function activate_plugin_name() {
	Plugin\Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-plugin-name-deactivator.php
 */
function deactivate_plugin_name() {
	Plugin\Deactivator::deactivate();
}

register_activation_hook( __FILE__, '\Teuton\WordpressTwigFrontend\activate_plugin_name' );
register_deactivation_hook( __FILE__, '\Teuton\WordpressTwigFrontend\deactivate_plugin_name' );

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since 1.0.0
 */
function run() {
	$plugin = new Main();
	$plugin->run();
}
run();
