<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Ag_Employee_Profiles
 * @subpackage Ag_Employee_Profiles/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Ag_Employee_Profiles
 * @subpackage Ag_Employee_Profiles/admin
 * @author     Alessandro Giuzio <mail@example.com>
 */
class Ag_Employee_Profiles_Admin {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/ag-employee-profiles-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/ag-employee-profiles-admin.js', array( 'jquery' ), $this->version, false );

	}
	// Register Custom Post Type for Employee Profiles
public function register_employee_profile_cpt() {
    $args = array(
        'public'       => true,
        'label'        => 'Employee Profiles',
        'has_archive'  => false,
        'rewrite'      => false,
        'show_in_rest' => true,
        'menu_icon'    => 'dashicons-businessman',
        'supports'     => array( 'title', 'editor', 'thumbnail' ),
    );
    register_post_type( 'employee_profile', $args );
}

// Add Metaboxes to Employee Profile CPT
    public function add_employee_profile_metaboxes($post_type, $post)
    {
        if ($post_type !== 'employee_profile') {
            return;
        }

        add_meta_box(
            'employee_profile_details',
            'Employee Profile Details',
            array($this, 'render_employee_profile_details_metabox'),
            'employee_profile',
            'normal',
            'default'
        );
    }

public function render_employee_profile_details_metabox( $post ) {
    // used to prevent CSRF attacks.
    wp_nonce_field( 'employee_profile_details_nonce', 'employee_profile_details_nonce' );

    // Get existing values
    $email      = get_post_meta( $post->ID, '_employee_email', true );
    $phone      = get_post_meta( $post->ID, '_employee_phone', true );
    $job_title  = get_post_meta( $post->ID, '_employee_job_title', true );
    $department = get_post_meta( $post->ID, '_employee_department', true );
    $company    = get_post_meta( $post->ID, '_employee_company', true );
    $website    = get_post_meta( $post->ID, '_employee_website', true );

    $street   = get_post_meta( $post->ID, '_employee_street', true );
    $postcode = get_post_meta( $post->ID, '_employee_postcode', true );
    $city     = get_post_meta( $post->ID, '_employee_city', true );
    $country  = get_post_meta( $post->ID, '_employee_country', true );

    $facebook = get_post_meta( $post->ID, '_employee_facebook', true );
    $twitter  = get_post_meta( $post->ID, '_employee_twitter', true );
    $profile  = get_post_meta( $post->ID, '_employee_profile', true );
    ?>

    <p>
        <label for="employee_job_title"><strong>Job Title</strong></label><br>
        <input type="text" id="employee_job_title" name="employee_job_title" value="<?php echo esc_attr( $job_title ); ?>" class="regular-text">
    </p>

    <p>
        <label for="employee_department"><strong>Department</strong></label><br>
        <input type="text" id="employee_department" name="employee_department" value="<?php echo esc_attr( $department ); ?>" class="regular-text">
    </p>

    <p>
        <label for="employee_company"><strong>Company</strong></label><br>
        <input type="text" id="employee_company" name="employee_company" value="<?php echo esc_attr( $company ); ?>" class="regular-text">
    </p>

    <p>
        <label for="employee_email"><strong>Email</strong></label><br>
        <input type="email" id="employee_email" name="employee_email" value="<?php echo esc_attr( $email ); ?>" class="regular-text">
    </p>

    <p>
        <label for="employee_phone"><strong>Phone</strong></label><br>
        <input type="text" id="employee_phone" name="employee_phone" value="<?php echo esc_attr( $phone ); ?>" class="regular-text">
    </p>

    <p>
        <label for="employee_website"><strong>Website</strong></label><br>
        <input type="url" id="employee_website" name="employee_website" value="<?php echo esc_attr( $website ); ?>" class="regular-text">
    </p>

    <hr>

    <p>
        <label for="employee_street"><strong>Street</strong></label><br>
        <input type="text" id="employee_street" name="employee_street" value="<?php echo esc_attr( $street ); ?>" class="regular-text">
    </p>

    <p>
        <label for="employee_postcode"><strong>Postal Code</strong></label><br>
        <input type="text" id="employee_postcode" name="employee_postcode" value="<?php echo esc_attr( $postcode ); ?>" class="regular-text">
    </p>

    <p>
        <label for="employee_city"><strong>City</strong></label><br>
        <input type="text" id="employee_city" name="employee_city" value="<?php echo esc_attr( $city ); ?>" class="regular-text">
    </p>

    <p>
        <label for="employee_country"><strong>Country</strong></label><br>
        <input type="text" id="employee_country" name="employee_country" value="<?php echo esc_attr( $country ); ?>" class="regular-text">
    </p>

    <hr>

    <p>
        <label for="employee_facebook"><strong>Facebook URL</strong></label><br>
        <input type="url" id="employee_facebook" name="employee_facebook" value="<?php echo esc_attr( $facebook ); ?>" class="regular-text">
    </p>

    <p>
        <label for="employee_twitter"><strong>X (Twitter) URL</strong></label><br>
        <input type="url" id="employee_twitter" name="employee_twitter" value="<?php echo esc_attr( $twitter ); ?>" class="regular-text">
    </p>

    <p>
        <label for="employee_profile"><strong>Other Profile URL</strong></label><br>
        <input type="url" id="employee_profile" name="employee_profile" value="<?php echo esc_attr( $profile ); ?>" class="regular-text">
    </p>
    <?php
}

public function save_employee_profile_metaboxes($post_id) {
        // 1) Check nonce
        if (!isset($_POST['employee_profile_details_nonce'])) {
            return;
        }
        if (!wp_verify_nonce($_POST['employee_profile_details_nonce'], 'employee_profile_details_nonce')) {
            return;
        }

        // 2) Stop autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // 3) Check user permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // 4) Save fields
        $fields = array(
            'employee_job_title' => '_employee_job_title',
            'employee_department' => '_employee_department',
            'employee_company' => '_employee_company',
            'employee_email' => '_employee_email',
            'employee_phone' => '_employee_phone',
            'employee_website' => '_employee_website',
            'employee_street' => '_employee_street',
            'employee_postcode' => '_employee_postcode',
            'employee_city' => '_employee_city',
            'employee_country' => '_employee_country',
            'employee_facebook' => '_employee_facebook',
            'employee_twitter' => '_employee_twitter',
            'employee_profile' => '_employee_profile',
        );

        foreach ($fields as $field => $meta_key) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, $meta_key, sanitize_text_field($_POST[$field]));
            }
        }
}
}
