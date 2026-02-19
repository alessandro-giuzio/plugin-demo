<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Ag_Employee_Profiles
 * @subpackage Ag_Employee_Profiles/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Ag_Employee_Profiles
 * @subpackage Ag_Employee_Profiles/includes
 * @author     Alessandro Giuzio <mail@example.com>
 */
class Ag_Employee_Profiles {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Ag_Employee_Profiles_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'AG_EMPLOYEE_PROFILES_VERSION' ) ) {
			$this->version = AG_EMPLOYEE_PROFILES_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'ag-employee-profiles';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Ag_Employee_Profiles_Loader. Orchestrates the hooks of the plugin.
	 * - Ag_Employee_Profiles_i18n. Defines internationalization functionality.
	 * - Ag_Employee_Profiles_Admin. Defines all hooks for the admin area.
	 * - Ag_Employee_Profiles_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ag-employee-profiles-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ag-employee-profiles-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-ag-employee-profiles-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-ag-employee-profiles-public.php';

		$this->loader = new Ag_Employee_Profiles_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Ag_Employee_Profiles_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Ag_Employee_Profiles_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Ag_Employee_Profiles_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action('init', $plugin_admin, 'register_employee_profile_cpt',0 );
		$this->loader->add_action('save_post', $plugin_admin, 'save_employee_profile_metaboxes', 10, 1);
		$this->loader->add_action('add_meta_boxes', $plugin_admin, 'add_employee_profile_metaboxes', 10, 2);
		$this->loader->add_filter('use_block_editor_for_post_type', $plugin_admin, 'disable_gutenberg_for_employee_profiles', 10, 2);
		$this->loader->add_action('admin_menu', $plugin_admin, 'add_employee_profiles_admin_menu');




	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Ag_Employee_Profiles_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_filter('template_include', $plugin_public, 'load_employee_profile_template');
		$this->loader->add_action('init', $plugin_public, 'add_employee_profile_rewrite_rule');
		$this->loader->add_filter('query_vars', $plugin_public, 'add_employee_uuid_query_var');
		$this->loader->add_filter('request', $plugin_public, 'capture_employee_uuid_request');
		$this->loader->add_action('pre_get_posts', $plugin_public, 'maybe_load_employee_profile_by_uuid', 1);
		$this->loader->add_action('wp_ajax_ag_employee_vcard', $plugin_public, 'download_employee_vcard');
		$this->loader->add_action('wp_ajax_nopriv_ag_employee_vcard', $plugin_public, 'download_employee_vcard');




	}

	/**
	 * Fires after WordPress has finished loading but before any headers are sent.
	 *
	 */
	public function action_init() : void {
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Ag_Employee_Profiles_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
