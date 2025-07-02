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


class Appetiser_Common_Hubspot_Public {

    private $original_handler;

    public function __construct() {
        add_action('init', array($this, 'handle_hubspot_shortcode'));
    }

    public function handle_hubspot_shortcode() {

        if (get_option('app_hubspot_override') !== '1') {
            return; 
        }

        global $shortcode_tags;

        // Save the original handler (if it exists)
        if (isset($shortcode_tags['hubspot'])) {
            $this->original_handler = $shortcode_tags['hubspot'];
            unset($shortcode_tags['hubspot']);
        }

        // Register new shortcode handler
        add_shortcode('hubspot', array($this, 'custom_hubspot_shortcode_handler'));
    }

    public function custom_hubspot_shortcode_handler($atts) {
        $atts = shortcode_atts([
            'type'   => '',
            'portal' => '',
            'id'     => '',
        ], $atts);

        
        if ($atts['type'] === 'form') {
            if( Appetiser_Common_Utils::is_live_env() ){
                $active_portalid = get_option( 'app_hubspot_live' );
            }else{
                $active_portalid = get_option( 'app_hubspot_devsite' );
            }
            
            return 'portalId: '.$active_portalid;
            /*
            <script charset="utf-8" type="text/javascript" src="//js.hsforms.net/forms/embed/v2.js"></script>
            <script>
            hbspt.forms.create({
                portalId: "5769657",
                formId: "9c826994-93d4-44fc-af2f-30fb488da514",
                region: "na1"
            });
            </script>
            */
            /*    
            return '<script>
                hbspt.forms.create({
                    portalId: "' . esc_js($atts['portal']) . '",
                    formId: "' . esc_js($atts['id']) . '",
                    target: "#custom-hbspt-form"
                });
            </script><div id="custom-hbspt-form"></div>';
            */
        }
        
        if (is_callable($this->original_handler)) {
            return call_user_func($this->original_handler, $atts);
        }

        return '';
    }
}
