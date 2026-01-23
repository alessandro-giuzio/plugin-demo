<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Expiration_Manager
 * @subpackage Expiration_Manager/admin
 */

class Expiration_Manager_Admin
{

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
	 * @param    string $plugin_name The name of this plugin.
	 * @param    string $version     The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{
		wp_enqueue_style(
			$this->plugin_name,
			plugin_dir_url(__FILE__) . 'css/expiration-manager-admin.css',
			array(),
			$this->version,
			'all'
		);
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{
		wp_enqueue_script(
			$this->plugin_name,
			plugin_dir_url(__FILE__) . 'js/expiration-manager-admin.js',
			array('jquery'),
			$this->version,
			false
		);
	}

	/* -------------------------------------------------------------------------
	 * POINT 1: Data per post (Metabox)
	 * -------------------------------------------------------------------------
	 * Post meta keys we store:
	 * - _em_expiration_date   (timestamp int)
	 * - _em_expiration_action (notice | unpublish)
	 * - _em_expiration_notice (optional text)
	 */

	/**
	 * Add the metabox to posts and pages.
	 */
	public function add_expiration_metabox()
	{
		add_meta_box(
			'em_expiration',
			'Expiration',
			array($this, 'render_expiration_metabox'),
			array('post', 'page'),
			'side',
			'default'
		);
	}

	/**
	 * Metabox HTML.
	 */
	public function render_expiration_metabox($post)
	{
		wp_nonce_field('em_save_expiration', 'em_nonce');

		$date = get_post_meta($post->ID, '_em_expiration_date', true);
		$action = get_post_meta($post->ID, '_em_expiration_action', true);
		$notice = get_post_meta($post->ID, '_em_expiration_notice', true);

		// Timestamp -> YYYY-MM-DD for <input type="date">
		$date_value = $date ? date('Y-m-d', (int) $date) : '';

		if (empty($action)) {
			$action = 'notice';
		}
		?>
		<p>
			<label for="em_date">Expire on</label><br>
			<input id="em_date" type="date" name="em_date" value="<?php echo esc_attr($date_value); ?>" style="width:100%;" />
		</p>

		<p>
			<label for="em_action">When expired</label><br>
			<select id="em_action" name="em_action" style="width:100%;">
				<option value="notice" <?php selected($action, 'notice'); ?>>Show notice</option>
				<option value="unpublish" <?php selected($action, 'unpublish'); ?>>Unpublish</option>
			</select>
		</p>

		<p>
			<label for="em_notice">Custom notice (optional)</label><br>
			<textarea id="em_notice" name="em_notice" rows="3"
				style="width:100%;"><?php echo esc_textarea($notice); ?></textarea>
		</p>

		<p style="font-size:12px;opacity:.8;margin:0;">
			If empty, the plugin will use the global default notice text.
		</p>
		<?php
	}

	/**
	 * Save the metabox values.
	 */
	public function save_expiration_metabox($post_id)
	{

		// Security
		if (!isset($_POST['em_nonce'])) {
			return;
		}
		if (!wp_verify_nonce($_POST['em_nonce'], 'em_save_expiration')) {
			return;
		}
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}
		if (!current_user_can('edit_post', $post_id)) {
			return;
		}

		// 1) Expiration date -> store as end-of-day timestamp
		if (!empty($_POST['em_date'])) {
			$date = sanitize_text_field($_POST['em_date']); // YYYY-MM-DD
			$ts = strtotime($date . ' 23:59:59');
			update_post_meta($post_id, '_em_expiration_date', (int) $ts);
		} else {
			delete_post_meta($post_id, '_em_expiration_date');
		}

		// 2) Action
		$action = !empty($_POST['em_action']) ? sanitize_text_field($_POST['em_action']) : 'notice';
		update_post_meta($post_id, '_em_expiration_action', $action);

		// 3) Optional custom notice
		$notice = isset($_POST['em_notice']) ? sanitize_textarea_field($_POST['em_notice']) : '';
		if ('' === $notice) {
			delete_post_meta($post_id, '_em_expiration_notice');
		} else {
			update_post_meta($post_id, '_em_expiration_notice', $notice);
		}
	}
	/* -------------------------------------------------------------------------
	 * SETTINGS PAGE: Global default notice text
	 * Option name: expiration_manager_default_notice
	 * ------------------------------------------------------------------------- */

	public function add_settings_page()
	{
		add_options_page(
			'Expiration Manager',            // Page title
			'Expiration Manager',            // Menu title
			'manage_options',                // Capability
			'expiration-manager',            // Menu slug
			array($this, 'render_settings_page') // Callback
		);
	}

	public function register_settings()
	{
		//  Register a new setting to enable/disable the expiration feature globally
		register_setting(
			'expiration_manager_settings',        // Settings group
			'expiration_manager_enabled',         // Option name
			array(
				'type' => 'boolean',
				'sanitize_callback' => 'rest_sanitize_boolean',
				'default' => true,
			)
		);
		register_setting(
			'expiration_manager_settings',        // Settings group
			'expiration_manager_default_notice',  // Option name
			array(
				'type' => 'string',
				'sanitize_callback' => 'sanitize_textarea_field',
				'default' => 'This content is outdated.',
			)
		);

		add_settings_section(
			'expiration_manager_section_main',
			'Global Settings',
			'__return_false',
			'expiration-manager'
		);

		// Add a checkbox field to the settings page for enabling/disabling expiration
		add_settings_field(
			'expiration_manager_enabled_field',
			'Enable Expiration Feature',
			array($this, 'render_enabled_field'),
			'expiration-manager',
			'expiration_manager_section_main'
		);
		add_settings_field(
			'expiration_manager_default_notice_field',
			'Default expired notice text',
			array($this, 'render_default_notice_field'),
			'expiration-manager',
			'expiration_manager_section_main'
		);
	}

	// Render the checkbox for enabling/disabling the expiration feature
	public function render_enabled_field()
	{
		$checked = get_option('expiration_manager_enabled', true) ? 'checked' : '';
		echo '<input type="checkbox" name="expiration_manager_enabled" value="1" ' . $checked . ' /> Enable expiration for posts and pages';
		// Small help text for the admin
		echo '<p style="margin:6px 0 0; font-size:12px; opacity:.8;">Uncheck to disable all expiration features site-wide.</p>';
	}

	public function render_default_notice_field()
	{
		$value = get_option('expiration_manager_default_notice', 'This content is outdated.');
		echo '<textarea name="expiration_manager_default_notice" rows="4" style="width:100%;">' . esc_textarea($value) . '</textarea>';
		echo '<p style="margin:6px 0 0; font-size:12px; opacity:.8;">Used when a post has no custom notice.</p>';
	}

	public function render_settings_page()
	{
		?>
		<div class="wrap">
			<h1>Expiration Manager</h1>

			<form method="post" action="options.php">
				<?php
				settings_fields('expiration_manager_settings');
				do_settings_sections('expiration-manager');
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

}
