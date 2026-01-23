# WordPress Plugins Directory

This directory contains WordPress plugins for experimentation and development. Each plugin is in its own folder and can be independently activated/deactivated through the WordPress admin panel or WP-CLI.

---

## Plugin Development Guide

### Adding a New Plugin

1. **Create a new folder** in this directory:

   ```bash
   mkdir my-new-plugin
   ```

2. **Create the main plugin file** `my-new-plugin.php`:

   ```php
   <?php
   /**
    * Plugin Name: My New Plugin
    * Plugin URI: https://example.com
    * Description: Brief description of what the plugin does
    * Version: 1.0.0
    * Author: Your Name
    * Author URI: https://example.com
    * License: GPL v2 or later
    * License URI: https://www.gnu.org/licenses/gpl-2.0.html
    * Domain: my-new-plugin
    */

   // Your plugin code here
   ```

3. **Organize plugin structure** (recommended):

   ```
   my-new-plugin/
   ├── admin/                          (Admin-side functionality)
   │   ├── class-my-plugin-admin.php
   │   ├── css/
   │   └── js/
   ├── includes/                       (Core plugin logic)
   │   ├── class-my-plugin.php        (Main plugin class)
   │   ├── class-my-plugin-activator.php
   │   ├── class-my-plugin-deactivator.php
   │   ├── class-my-plugin-loader.php
   │   └── index.php
   ├── public/                         (Frontend-side functionality)
   │   ├── class-my-plugin-public.php
   │   ├── css/
   │   └── js/
   ├── my-new-plugin.php              (Entry point)
   ├── README.md                       (Plugin documentation)
   ├── .gitignore                      (Git ignore rules)
   ├── index.php                       (Prevent direct access)
   └── license.txt                     (Plugin license)
   ```

4. **Create documentation** in `README.md` with:
   - Plugin description and features
   - Installation instructions
   - Configuration guide
   - Usage examples
   - File structure
   - Hooks and filters
   - Changelog

5. **Activate the plugin**:
   - Go to WordPress Admin → Plugins
   - Click "Activate" on your plugin

### Plugin Structure Best Practices

**Main Plugin Class (`class-my-plugin.php`):**

- Initialize the plugin
- Set up hooks and actions
- Manage plugin lifecycle

**Admin Class (`class-my-plugin-admin.php`):**

- Add admin menus
- Create settings pages
- Handle post metaboxes
- Enqueue admin styles/scripts

**Public Class (`class-my-plugin-public.php`):**

- Frontend functionality
- Enqueue frontend styles/scripts
- Hooks for frontend display

**Activator/Deactivator:**

- Run on plugin activation/deactivation
- Create database tables
- Set default options
- Schedule cron events

**Loader Class (`class-my-plugin-loader.php`):**

- Centralize all hook registration
- Manage action and filter priorities

## Plugin Management

### Activate Plugin

```bash
# Via WordPress Admin
Settings → Plugins → [Plugin Name] → Activate

# Via WP-CLI
wp plugin activate plugin-folder-name
```

### Deactivate Plugin

```bash
# Via WordPress Admin
Settings → Plugins → [Plugin Name] → Deactivate

# Via WP-CLI
wp plugin deactivate plugin-folder-name
```

### Delete Plugin

```bash
# Via WordPress Admin
Settings → Plugins → [Plugin Name] → Delete

# Via file system
rm -rf plugin-folder-name/
```

### List All Plugins

```bash
# Via WP-CLI
wp plugin list
```

## Plugin Code Standards

### Security

- Always sanitize input: `sanitize_text_field()`, `sanitize_textarea_field()`
- Always escape output: `esc_html()`, `esc_attr()`, `wp_kses_post()`
- Verify nonces on form submissions: `wp_verify_nonce()`
- Check user capabilities: `current_user_can()`

### Documentation

- Use PHPDoc comments for classes and methods
- Document hooks (actions and filters)
- Include inline comments for complex logic
- Keep code clean and readable

### Naming Conventions

- Plugin folder: lowercase with hyphens (e.g., `my-new-plugin`)
- Classes: PascalCase (e.g., `My_New_Plugin_Admin`)
- Functions: snake_case (e.g., `my_plugin_function()`)
- Hooks: snake_case (e.g., `my_plugin_action_name`)
- Constants: UPPERCASE (e.g., `MY_PLUGIN_VERSION`)

## Useful WordPress Functions

### Options (Settings)

```php
get_option('option_name');
update_option('option_name', 'value');
add_option('option_name', 'default_value');
delete_option('option_name');
```

### Post Meta

```php
get_post_meta($post_id, 'meta_key', true);
update_post_meta($post_id, 'meta_key', 'value');
delete_post_meta($post_id, 'meta_key');
```

### Hooks (Actions)

```php
add_action('hook_name', 'callback_function', priority, args);
do_action('hook_name', $args);
remove_action('hook_name', 'callback_function');
```

### Hooks (Filters)

```php
add_filter('hook_name', 'callback_function', priority, args);
apply_filters('hook_name', $value);
remove_filter('hook_name', 'callback_function');
```

### Admin

```php
add_menu_page();           // Add top-level menu
add_submenu_page();        // Add submenu
add_options_page();        // Add settings page
add_meta_box();           // Add metabox to post
register_setting();       // Register a setting
```

## Testing Plugins

### Manual Testing

1. Activate plugin in WordPress Admin
2. Test all features in browser
3. Check browser console for JavaScript errors
4. Check WordPress debug log: `wp-content/debug.log`

### Enable Debug Mode

Add to `wp-config.php`:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

### WP-CLI Testing

```bash
wp plugin activate my-plugin
wp hook list --all
wp db query "SELECT * FROM wp_options WHERE option_name LIKE 'my_plugin%'"
```

## Common Issues

### Plugin Won't Activate

- Check for PHP syntax errors: `php -l plugin-file.php`
- Check WordPress error log
- Verify all required WordPress hooks are implemented
- Check file permissions

### Settings Not Saving

- Verify `register_setting()` is called with correct group name
- Check nonce is being verified
- Ensure form method is POST
- Verify `settings_fields()` has correct group name

### Plugin Conflicts

### WP-CLI Testing

```bash
wp plugin activate my-plugin
wp hook list --all
wp db query "SELECT * FROM wp_options WHERE option_name LIKE 'my_plugin%'"
```

## Common Issues

### Plugin Won't Activate

- Check for PHP syntax errors: `php -l plugin-file.php`
- Check WordPress error log
- Verify all required WordPress hooks are implemented
- Check file permissions

### Settings Not Saving

- Verify `register_setting()` is called with correct group name
- Check nonce is being verified
- Ensure form method is POST
- Verify `settings_fields()` has correct group name

### Plugin Conflicts

- Deactivate all other plugins
- Reactivate plugins one by one to find conflict
- Check for duplicate hook names
- Check for naming conflicts in functions/classes

## Resources

- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/php/)
- [WordPress Hooks](https://developer.wordpress.org/plugins/hooks/)
- [WordPress Admin Pages](https://developer.wordpress.org/plugins/admin-menus/)
- [WordPress Security](https://developer.wordpress.org/plugins/security/)

## Notes

- Always backup before modifying plugins
- Test plugins thoroughly before deploying
- Keep plugins updated with WordPress releases
- Follow WordPress coding standards for consistency
- Document your code for future reference

---

**Last Updated:** January 23, 2026
