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
        add_action('wp_enqueue_scripts', array( $this, 'register_intl_tel_input_assets') );
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

    public function get_dev_form_id_by_live_id($live_id) {
        $forms = get_option('app_hubspot_forms', []);

        if (!is_array($forms) || empty($forms)) {
            return false;
        }

        foreach ($forms as $form) {
            if (
                isset($form['live'], $form['dev'], $form['enabled']) &&
                $form['live'] === $live_id
            ) {
                return $form['enabled'] == '1' ? $form['dev'] : false;
            }
        }

        return false;
    }

    public function get_form_config_by_live_id($live_id) {
        $forms = get_option('app_hubspot_forms', []);

        if (!is_array($forms) || empty($forms)) {
            return false;
        }

        foreach ($forms as $form) {
            if (isset($form['live']) && $form['live'] === $live_id) {
                return $form; 
            }
        }

        return false;
    }

    public function custom_hubspot_shortcode_handler($atts) {
    $atts = shortcode_atts([
        'type'   => '',
        'portal' => '',
        'id'     => '',
    ], $atts);

    if ($atts['type'] !== 'form' || empty($atts['id'])) {
        return '';
    }

    $is_live = Appetiser_Common_Utils::is_live_env();
    $form_config = $this->get_form_config_by_live_id($atts['id']);

    if (!$is_live) {
        if (
            !$form_config ||
            empty($form_config['enabled']) ||
            $form_config['enabled'] != '1'
        ) {
            return ''; 
        }
    }
    
    $active_portalid = $is_live
        ? get_option('app_hubspot_live')
        : get_option('app_hubspot_devsite');

    $active_formid = $is_live
        ? $atts['id']
        : ($form_config['dev'] ?? '');

    $enable_validation = !empty($form_config['phone_validate']) && $form_config['phone_validate'] == '1';

    $sandbox_notice = !$is_live
        ? '<div style="color: orange; font-weight: bold; margin-bottom:10px;">[Sandbox Mode]<br />Portal ID: [' . esc_html($active_portalid) . ']<br />Form ID: [' . esc_html($active_formid) . ']</div>'
        : '';

    if ($enable_validation) {
        wp_enqueue_style('hbspt-form-style');
        wp_enqueue_style('intltel-inputcss');
        wp_enqueue_script('intltel-input');
        wp_enqueue_script('intltel-init');
    }


    return $sandbox_notice . '
    <script charset="utf-8" type="text/javascript" src="//js.hsforms.net/forms/embed/v2.js"></script>
    <script>
    hbspt.forms.create({
        portalId: "' . esc_js($active_portalid) . '",
        formId: "' . esc_js($active_formid) . '",
        region: "na1"
    });
    </script>';
}

    function register_intl_tel_input_assets() {
        $base_uri = plugin_dir_url(__FILE__) . 'inttelinput';

        wp_register_style(
            'hbspt-form-style',
            $base_uri . '/hbspt-form-style.css',
            array(),
            '1.0'
        );

        wp_register_style(
            'intltel-inputcss',
            $base_uri . '/intlTelInput.min.css',
            array(),
            '17.0.8'
        );

        wp_register_script(
            'intltel-input',
            $base_uri . '/intlTelInput.min.js',
            array('jquery'),
            '17.0.8',
            true
        );

        wp_register_script(
            'intltel-init',
            $base_uri . '/intl-tel-init.js',
            array('intltel-input'),
            '17.0.8',
            true
        );
    }
}
