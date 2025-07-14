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

if (!defined('ABSPATH')) {
    exit; // Prevent direct access
}


class Appetiser_Common_Comments_Public {

    public function __construct() {
        add_action('init', array($this, 'handle_comments_enabled'));
    }

    public function handle_comments_enabled(){

        if (get_option('app_comments_enable') !== '1') {
            return; 
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($_SERVER['REQUEST_URI'], 'wp-comments-post.php') !== false) {
            wp_die('Comments are closed.', 'Comments Closed', ['response' => 403]);
        }

    }

}
