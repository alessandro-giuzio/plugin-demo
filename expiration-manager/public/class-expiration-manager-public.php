<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Expiration_Manager
 * @subpackage Expiration_Manager/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Expiration_Manager
 * @subpackage Expiration_Manager/public
 * @author     Alessandro <test@mail.com>
 */
class Expiration_Manager_Public {

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
		 * defined in Expiration_Manager_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Expiration_Manager_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/expiration-manager-public.css', array(), $this->version, 'all' );

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
		 * defined in Expiration_Manager_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Expiration_Manager_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/expiration-manager-public.js', array( 'jquery' ), $this->version, false );

	}
	/**
	 * Prepend an "expired" notice banner above the content when a post/page is expired
	 * and the selected action is "notice".
	 *
	 * @param string $content The post content.
	 * @return string
	 */
	public function maybe_prepend_expiration_notice($content)
	{

		// Only on single posts/pages (not archives)
		if (!is_singular(array('post', 'page'))) {
			return $content;
		}

		$post_id = get_the_ID();
		if (!$post_id) {
			return $content;
		}

		// 1) Get expiration timestamp
		$expiration_ts = (int) get_post_meta($post_id, '_em_expiration_date', true);
		if (!$expiration_ts) {
			return $content;
		}

		// 2) Not expired yet
		if (time() < $expiration_ts) {
			return $content;
		}

		// 3) Check action
		$action = get_post_meta($post_id, '_em_expiration_action', true);
		if ('notice' !== $action) {
			return $content;
		}

		// 4) Get banner message: per-post custom notice OR global default option
		$custom_notice = get_post_meta($post_id, '_em_expiration_notice', true);
		$default_notice = get_option('expiration_manager_default_notice', 'This content is outdated.');
		$message = $custom_notice ? $custom_notice : $default_notice;

		// 5) Build banner HTML (simple inline style for now)
		$banner = '<div class="em-expired-notice" role="status" aria-live="polite" style="padding:10px;border:1px solid #f0c36d;background:#fff7e6;margin:0 0 16px 0;">';
		$banner .= esc_html($message);
		$banner .= '</div>';

		return $banner . $content;
	}

}
