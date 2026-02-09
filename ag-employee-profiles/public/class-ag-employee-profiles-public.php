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

	// AG: vCArd handler for employee profiles
	public function download_employee_vcard(){

	// Bssic UUID format check (matches routing regex)
		$uuid = isset($_GET['uuid']) ? sanitize_text_field($_GET['uuid']) : '';
		// Basic UUID format check (matches routing regex)
		if (empty($uuid) || !preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/', $uuid)) {
			status_header(400);
			echo 'Invalid UUID';
			exit;
		}
	// Find the profile by UUID meta
	$ids = get_posts([
		'post_type' => 'employee_profile',
		'post_status' => 'publish',
		'fields' => 'ids',
		'posts_per_page' => 1,
		'meta_key' => '_ag_employee_uuid',
		'meta_value' => $uuid,
	]);
	if (empty($ids)) {
		status_header(404);
		echo 'Profile not found';
		exit;
	}
	$post_id = (int) $ids[0];
	// Collect data (title + meta)
	  $full_name = get_the_title($post_id);
	  $job_title = get_post_meta($post_id, '_ag_employee_job_title', true);
	  $company = get_post_meta($post_id, '_ag_employee_company', true);
	  $email = get_post_meta($post_id, '_ag_employee_email', true);
	  $phone = get_post_meta($post_id, '_ag_employee_phone', true);
	  $website = get_post_meta($post_id, '_ag_employee_website', true);

	  $street = get_post_meta($post_id, '_ag_employee_street', true);
	  $city = get_post_meta($post_id, '_ag_employee_city', true);
	  $state = get_post_meta($post_id, '_ag_employee_state', true);
	  $postal_code = get_post_meta($post_id, '_ag_employee_postal_code', true);
	  $country = get_post_meta($post_id, '_ag_employee_country', true);

	// Split name into first/last for vCard
		$parts = preg_split('/\s+/', trim($full_name));
		$last = $parts ? array_pop($parts) : '';
		$first = $parts ? implode(' ', $parts) : $full_name;
	// Build vCard
		$lines = [];
		$lines[] = 'BEGIN:VCARD';
		$lines[] = 'VERSION:3.0';
		$lines[] = 'N:' . $this->vcard_escape($last) . ';' . $this->vcard_escape($first) . ';;;';
		$lines[] = 'FN:' . $this->vcard_escape($full_name);

		if (!empty($company))
			$lines[] = 'ORG:' . $this->vcard_escape($company);
		if (!empty($job_title))
			$lines[] = 'TITLE:' . $this->vcard_escape($job_title);
		if (!empty($email))
			$lines[] = 'EMAIL;TYPE=INTERNET:' . $this->vcard_escape($email);
		if (!empty($phone))
			$lines[] = 'TEL;TYPE=CELL:' . $this->vcard_escape($phone);
		if (!empty($website))
			$lines[] = 'URL:' . $this->vcard_escape($website);
	// ADR: Street;City;State;PostalCode;Country
		if (!empty($street) || !empty($city) || !empty($state) || !empty($postal_code) || !empty($country)) {
			$adr = 'ADR;TYPE=WORK:;;' .
				$this->vcard_escape($street) . ';' .
				$this->vcard_escape($city) . ';' .
				$this->vcard_escape($state) . ';' .
				$this->vcard_escape($postal_code) . ';' .
				$this->vcard_escape($country);
			$lines[] = $adr;
		}

		$lines[] = 'END:VCARD';
		$vcard = implode("\r\n", $lines);
	// Dowload headers
		$filename = sanitize_file_name(strtolower(str_replace(' ', '-', $full_name))) ?: 'employee-profile';
		$filename .= '.vcf';

		nocache_headers();
		header('Content-Type: text/vcard; charset=utf-8');
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		header('Content-Length: ' . strlen($vcard));

		echo $vcard;
		exit;

	}
	// Helper: vCard escaping
	private function vcard_escape($value) {
	$value = (string) $value;
	$value = str_replace(["\r\n", "\r", "\n"], '\\n', $value);
	$value = str_replace([';', ','], ['\;', '\,'], $value);
	return $value;}


}


// Register the AJAX actions (public + logged-in)
// Add the Dowload button in the remplate
// Test
