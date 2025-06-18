<?php 
/**
 * Plugin Name: Appetiser Common Assets
 * Plugin URI:  https://appetiser.com.au
 * Description: Shared resources and functions used by other Appetiser plugins for Dashboard UI.
 * Version: 1.0.0
 * Author: Landing page team
 * Author URI: https://appetiser.com.au
 * License: GPL v3
*  License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */

 
 /**
 * Registers the "Appetiser Tools" top-level admin menu.
 *
 * This menu serves as a container for all Appetiser-related submenu tools.
 * The main page does not render any content directly.
 *
 * @hook admin_menu
 * @return void
 */
 add_action('admin_menu', function() {
    add_menu_page(
        'Appetiser Tools',           // Page title
        'Appetiser Tools',           // Menu title
        'manage_options',            // Capability
        'appetiser-tools',           // Menu slug
        '__return_null',             // Callback (null, since submenus will handle content)
        'dashicons-admin-generic',  // Icon
        30                           // Position
    );
});

/**
 * Removes the default submenu link to the top-level "Appetiser Tools" page.
 *
 * This prevents WordPress from auto-adding a redundant first submenu item 
 * that links to the same slug as the parent. Intended to clean up the UI 
 * when only custom submenus are used.
 *
 * @hook admin_menu
 * @priority 999 To ensure it runs after the menu is registered
 * @return void
 */
add_action('admin_menu', function() {
    remove_submenu_page('appetiser-tools', 'appetiser-tools');
}, 999); 



 /**
 * Registers activation hook to create the `appetiser-settings` folder in the uploads directory.
 *
 * @hook register_activation_hook
 */
register_activation_hook(__FILE__, 'appetiser_common_assets_create_settings_folder');


/**
 * Creates the `appetiser-settings` directory inside wp-content/uploads on plugin activation.
 *
 * If the folder does not exist, it is created using `wp_mkdir_p()`.
 * A `.htaccess` file is added to deny direct access to any `.json` files within the directory.
 *
 * @return void
 */
function appetiser_common_assets_create_settings_folder() {
    $upload_dir = wp_upload_dir();
    $target_dir = $upload_dir['basedir'] . '/appetiser-settings';

    if (!file_exists($target_dir)) {
        wp_mkdir_p($target_dir);

        // Create .htaccess to protect JSON files
        $htaccess_content = "<FilesMatch \"\\.json$\">\nOrder allow,deny\nDeny from all\n</FilesMatch>";
        file_put_contents($target_dir . '/.htaccess', $htaccess_content);
    }
}
