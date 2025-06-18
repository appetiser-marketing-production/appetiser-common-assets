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

add_action('admin_menu', function() {
    remove_submenu_page('appetiser-tools', 'appetiser-tools');
}, 999); // Priority must be after the main menu is added


register_activation_hook(__FILE__, 'appetiser_common_assets_create_settings_folder');

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
