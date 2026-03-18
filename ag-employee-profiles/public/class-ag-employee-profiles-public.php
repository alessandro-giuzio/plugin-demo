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
	 * Current routing mode (slug or uuid).
	 *
	 * @var string
	 */
	private $route_mode = 'slug';

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
		$this->route_mode = $this->determine_route_mode();

	}

	private function determine_route_mode() {
		$mode = get_option('ag_employee_route_mode', 'slug');
		return in_array($mode, ['slug', 'uuid'], true) ? $mode : 'slug';

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
    // Localize script for AJAX
		wp_localize_script(
			$this->plugin_name,
			'agEmployeeAjax',
			[
				'url' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('ag_employee_filter'),
			]
		);


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

		$this->mark_employee_profile_single($query, $post_id);
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
 // AJAX handler for employee profile filtering
	public function filter_employee_profiles() {
    check_ajax_referer( 'ag_employee_filter', 'nonce' );

    $search     = sanitize_text_field( $_POST['search']     ?? '' );
    $department = sanitize_text_field( $_POST['department'] ?? '' );
		$company = sanitize_text_field($_POST['company'] ?? '');
		$website = sanitize_text_field($_POST['website'] ?? '');


		$base_args = [
        'post_type'      => 'employee_profile',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
    ];

    $args = [
        'post_type'      => 'employee_profile',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
    ];

    // Department filter — narrows results to one department
		$meta_filters = [];

		if (!empty($department)) {
			$meta_filters[] = ['key' => '_employee_department', 'value' => $department, 'compare' => '='];
		}

		if (!empty($company)) {
			$meta_filters[] = ['key' => '_employee_company', 'value' => $company, 'compare' => '='];
		}

		if (!empty($website)) {
			$meta_filters[] = ['key' => '_employee_website', 'value' => $website, 'compare' => 'LIKE'];
		}

		if (!empty($meta_filters)) {
			$meta_filters['relation'] = 'AND';
			$args['meta_query'] = $meta_filters;
		}


		// Search: OR across post title + job title meta
    if ( ! empty( $search ) ) {
        $title_query = new WP_Query( array_merge( $base_args, [ 's' => $search ] ) );
        $title_ids   = $title_query->posts;

        $meta_query_result = new WP_Query( array_merge( $base_args, [
            'meta_query' => [
                [ 'key' => '_employee_job_title', 'value' => $search, 'compare' => 'LIKE' ],
            ],
        ] ) );
        $meta_ids = $meta_query_result->posts;

        $merged_ids = array_unique( array_merge( $title_ids, $meta_ids ) );

        if ( empty( $merged_ids ) ) {
            wp_send_json_success( [ 'html' => '<p>No employees found.</p>', 'count' => 0 ] );
        }

        $args['post__in'] = $merged_ids;
    }

    $query = new WP_Query( $args );

    ob_start();
    foreach ( $query->posts as $profile ) {
        $url = $this->get_employee_profile_url( $profile->ID );
        ?>
        <div class="ag-employee-profile" data-name="<?php echo esc_attr( strtolower( get_the_title( $profile->ID ) ) ); ?>">
            <h3><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( get_the_title( $profile->ID ) ); ?></a></h3>
            <p><?php echo esc_html( get_post_meta( $profile->ID, '_employee_job_title', true ) ); ?></p>
        </div>
        <?php
    }
    $html = ob_get_clean();

    if ( empty( $html ) ) {
        $html = '<p>No employees found.</p>';
    }

    wp_send_json_success( [ 'html' => $html, 'count' => $query->found_posts ] );
}

	// Helper: vCard escaping
	private function vcard_escape($value) {
	$value = (string) $value;
	$value = str_replace(["\r\n", "\r", "\n"], '\\n', $value);
	$value = str_replace([';', ','], ['\\;', '\\,'], $value);
	return $value;}

	private function mark_employee_profile_single($query, $post_id) {
		$query->is_home = false;
		$query->is_archive = false;
		$query->is_post_type_archive = false;
		$query->is_singular = true;
		$query->is_single = true;
		$query->queried_object = get_post( $post_id );
		$query->queried_object_id = $post_id;
	}

	private function get_employee_profile_url($post_id) {
		if ($this->route_mode === 'uuid') {
			$uuid = get_post_meta($post_id, '_ag_employee_uuid', true);
			if (!empty($uuid)) {
				return home_url('/team/' . $uuid);
			}
		}

		return get_permalink($post_id);
	}

	public function enforce_canonical_employee_profile_url() {
		if (!is_singular('employee_profile')) {
			return;
		}

		global $post;
		if (!$post instanceof WP_Post) {
			return;
		}

		$canonical = $this->get_employee_profile_url($post->ID);
		if (empty($canonical)) {
			return;
		}

		$canonical_path = trim((string) wp_parse_url($canonical, PHP_URL_PATH), '/');
		$current_path = $this->get_current_request_path();

		if ($canonical_path && $current_path && $canonical_path !== $current_path) {
			wp_safe_redirect($canonical, 301);
			exit;
		}
	}

	private function get_current_request_path() {
		global $wp;
		$request = isset($wp->request) ? (string) $wp->request : '';
		return trim($request, '/');
	}


	// employee shortcode handler to show a list of employee profiles + search
public function register_shortcodes() {
	add_shortcode('employee_profiles', [$this, 'employee_profiles']);
}
	public function employee_profiles()
	{
		// Fetch distinct department values for the dropdown
		global $wpdb;
		$departments = $wpdb->get_col(
			"SELECT DISTINCT meta_value
         FROM {$wpdb->postmeta}
         INNER JOIN {$wpdb->posts} ON {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id
         WHERE meta_key = '_employee_department'
           AND meta_value != ''
           AND post_status = 'publish'
           AND post_type = 'employee_profile'
         ORDER BY meta_value ASC"
		);

		// Initial server-side render (works without JS)
		$profiles = get_posts([
			'post_type' => 'employee_profile',
			'post_status' => 'publish',
			'numberposts' => -1,
			'orderby' => 'title',
			'order' => 'ASC',
		]);
		$companies = $wpdb->get_col(
			"SELECT DISTINCT meta_value
     FROM {$wpdb->postmeta}
     INNER JOIN {$wpdb->posts} ON {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id
     WHERE meta_key = '_employee_company'
       AND meta_value != ''
       AND post_status = 'publish'
       AND post_type = 'employee_profile'
     ORDER BY meta_value ASC"
		);


		ob_start();
		?>
			<p>
					<input
							type="text"
							id="ag-employee-search"
							placeholder="Search employees..."
							style="width: 100%; max-width: 400px; padding: 8px; margin-bottom: 10px;"
					>
			</p>
			<?php if (!empty($departments)): ?>
				<p>
						<select id="ag-employee-department" style="padding: 8px; margin-bottom: 20px;">
								<option value="">All Departments</option>
								<?php foreach ($departments as $dept): ?>
											<option value="<?php echo esc_attr($dept); ?>"><?php echo esc_html($dept); ?></option>
								<?php endforeach; ?>
						</select>
				</p>
			<?php endif; ?>
			<?php if (!empty($companies)): ?>
				<p>
					<select id="ag-employee-company" style="padding: 8px; margin-bottom: 20px;">
						<option value="">All Companies</option>
						<?php foreach ($companies as $company): ?>
							<option value="<?php echo esc_attr($company); ?>">
								<?php echo esc_html($company); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</p>
			<?php endif; ?>

			<div id="ag-employee-list">
			<?php foreach ($profiles as $profile):
				$url = $this->get_employee_profile_url($profile->ID);
				?>
						<div class="ag-employee-profile" data-name="<?php echo esc_attr(strtolower(get_the_title($profile->ID))); ?>">
								<h3><a href="<?php echo esc_url($url); ?>"><?php echo esc_html(get_the_title($profile->ID)); ?></a></h3>
								<p><?php echo esc_html(get_post_meta($profile->ID, '_employee_job_title', true)); ?></p>
						</div>
			<?php endforeach; ?>
			</div>
			<p>
    <input
        type="text"
        id="ag-employee-website"
        placeholder="Filter by website..."
        style="width: 100%; max-width: 400px; padding: 8px; margin-bottom: 10px;"
    >
</p>

			<?php
			return ob_get_clean();
	}

}
