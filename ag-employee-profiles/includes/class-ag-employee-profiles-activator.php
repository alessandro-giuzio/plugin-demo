<?php

/**
 * Fired during plugin activation
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Ag_Employee_Profiles
 * @subpackage Ag_Employee_Profiles/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Ag_Employee_Profiles
 * @subpackage Ag_Employee_Profiles/includes
 * @author     Alessandro Giuzio <mail@example.com>
 */
class Ag_Employee_Profiles_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		$plugin_name = 'ag-employee-profiles';
		$version = defined( 'AG_EMPLOYEE_PROFILES_VERSION' ) ? AG_EMPLOYEE_PROFILES_VERSION : '1.0.0';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-ag-employee-profiles-admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-ag-employee-profiles-public.php';

		$plugin_admin = new Ag_Employee_Profiles_Admin( $plugin_name, $version );
		$plugin_admin->register_employee_profile_cpt();

		$plugin_public = new Ag_Employee_Profiles_Public( $plugin_name, $version );
		$plugin_public->add_employee_profile_rewrite_rule();

		flush_rewrite_rules();
	}

}
