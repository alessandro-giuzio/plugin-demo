<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Ag_Employee_Profiles
 * @subpackage Ag_Employee_Profiles/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Ag_Employee_Profiles
 * @subpackage Ag_Employee_Profiles/public
 * @author     Alessandro Giuzio <mail@example.com>
 */
class Ag_Employee_Profiles_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Ag_Employee_Profiles_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Ag_Employee_Profiles_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/ag-employee-profiles-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Ag_Employee_Profiles_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Ag_Employee_Profiles_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/ag-employee-profiles-public.js', array( 'jquery' ), $this->version, false );

	}
	// register a rewrite rule on init: Match/team/<uuid>/ internally route to:index.php?post_type=employee_profile&employee_uuid=<captured_uuid>
	public function add_employee_profile_rewrite_rule()
	{
		add_rewrite_tag( '%employee_uuid%', '([0-9a-fA-F-]{36})' );
		add_rewrite_rule(
			'^team/([0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12})/?$',
			'index.php?post_type=employee_profile&employee_uuid=$matches[1]',
			'top'
		);
	}
  // add 'employee_uuid' to the list of recognized query vars
	public function add_employee_uuid_query_var($vars)
	{
		$vars[] = 'employee_uuid';
		return $vars;
	}

	// Ensure UUID requests are captured even if rewrite rules are bypassed.
	public function capture_employee_uuid_request( $vars )
	{
		if ( is_admin() ) {
			return $vars;
		}

		if ( ! empty( $vars['employee_uuid'] ) ) {
			return $vars;
		}

		$uri = isset( $_SERVER['REQUEST_URI'] ) ? (string) $_SERVER['REQUEST_URI'] : '';
		$path = trim( (string) wp_parse_url( $uri, PHP_URL_PATH ), '/' );
		if ( preg_match( '#^team/([0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12})/?$#', $path, $matches ) ) {
			$vars['employee_uuid'] = $matches[1];
			$vars['post_type'] = 'employee_profile';
		}

		return $vars;
	}


 // modify the main query to load employee_profile by UUID if 'employee_uuid' query var is present
	public function maybe_load_employee_profile_by_uuid($query)
	{
		// AG: Only run on the frontend main query
		if (is_admin() || !$query->is_main_query()) {
			return;
		}

		$uuid = $query->get('employee_uuid');
		if (empty($uuid)) {
			$name = $query->get('name');
			if (!empty($name) && preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/', $name)) {
				$uuid = $name;
			}
		}
		if (empty($uuid)) {
			return;
		}

		// AG: Find the employee_profile post ID by UUID meta
		$posts = get_posts([
			'post_type' => 'employee_profile',
			'post_status' => 'publish',
			'fields' => 'ids',
			'posts_per_page' => 1,
			'meta_key' => '_ag_employee_uuid',
			'meta_value' => $uuid,
		]);

		$post_id = !empty($posts) ? (int) $posts[0] : 0;

		// AG: If no match, let WP handle as 404 naturally
		if (!$post_id) {
			$query->set_404();
			$query->is_404 = true;
			return;
		}

		// AG: Convert the main query into a real single post query by ID
		$query->set('post_type', 'employee_profile');
		$query->set('p', $post_id);
		$query->set('pagename', '');
		$query->set('page_id', 0);

		// AG: Clear anything that could force archive-ish behavior
		$query->set('name', '');
		$query->set('employee_uuid', '');
		$query->set('posts_per_page', 1);

		// AG: Force single state so template selection doesn't fall back to archive
		$query->is_home = false;
		$query->is_archive = false;
		$query->is_post_type_archive = false;
		$query->is_singular = true;
		$query->is_single = true;
		$query->queried_object = get_post( $post_id );
		$query->queried_object_id = $post_id;
	}






	// Load custom template for single employee profile
	public function load_employee_profile_template($template)
	{
		if (is_singular('employee_profile')) {
			$plugin_template = plugin_dir_path(dirname(__FILE__)) . 'templates/single-employee_profile.php';
			if (file_exists($plugin_template)) {
				return $plugin_template;
			}
		}
		return $template;
	}


}
