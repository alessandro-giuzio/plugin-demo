<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://example.com
 * @since             1.0.0
 * @package           Ag_Employee_Profiles
 *
 * @wordpress-plugin
 * Plugin Name:       AG Employee Profiles
 * Plugin URI:        https://example.com
 * Description:       Short description
 * Version:           1.0.0
 * Author:            Alessandro Giuzio
 * Author URI:        https://example.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       ag-employee-profiles
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
define( 'AG_EMPLOYEE_PROFILES_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-ag-employee-profiles-activator.php
 */
function activate_ag_employee_profiles() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-ag-employee-profiles-activator.php';
	Ag_Employee_Profiles_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-ag-employee-profiles-deactivator.php
 */
function deactivate_ag_employee_profiles() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-ag-employee-profiles-deactivator.php';
	Ag_Employee_Profiles_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_ag_employee_profiles' );
register_deactivation_hook( __FILE__, 'deactivate_ag_employee_profiles' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-ag-employee-profiles.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_ag_employee_profiles() {

	$plugin = new Ag_Employee_Profiles();
	$plugin->run();

}
run_ag_employee_profiles();
