<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Ag_Employee_Profiles
 * @subpackage Ag_Employee_Profiles/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Ag_Employee_Profiles
 * @subpackage Ag_Employee_Profiles/includes
 * @author     Alessandro Giuzio <mail@example.com>
 */
class Ag_Employee_Profiles_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		flush_rewrite_rules();
	}

}
