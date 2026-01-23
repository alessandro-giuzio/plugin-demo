<?php

/**
 * Fired during plugin activation
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Expiration_Manager
 * @subpackage Expiration_Manager/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Expiration_Manager
 * @subpackage Expiration_Manager/includes
 * @author     Alessandro <test@mail.com>
 */
class Expiration_Manager_Activator {

	public static function activate()
	{

		// 1) Global default text (only add the first time)
		if (false === get_option('expiration_manager_default_notice')) {
			add_option('expiration_manager_default_notice', 'This content is outdated.');
		}

		// 2) Schedule our cron check (runs hourly)
		if (!wp_next_scheduled('expiration_manager_cron_check')) {
			wp_schedule_event(time(), 'daily', 'expiration_manager_cron_check');
		}
	}

}
