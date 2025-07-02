<?php

class Appetiser_Common_Admin {

    public function __construct() {
        add_action( 'admin_menu',  array( $this, 'add_plugin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    public function enqueue_styles( $hook ) {
        if (!isset($_GET['page']) || $_GET['page'] !== 'appetiser-common-admin') {
            return;
        }
        
       wp_enqueue_style('dashicons');
       wp_enqueue_style( 'appetiser-dashboard-style', plugin_dir_url( __FILE__ ) . 'css/appetiser-dashboard.css', array(), '1.0.0', 'all' );
       wp_enqueue_style( 'appetiser-general-settings', plugin_dir_url( __FILE__ ) . 'css/appetiser-general-settings.css', array(), '1.0.0', 'all' );
    }

    public function enqueue_scripts( $hook ) {
        if (!isset($_GET['page']) || $_GET['page'] !== 'appetiser-common-admin') {
            return;
        }

        wp_enqueue_script( 'appetiser-dashboard-script', plugin_dir_url( __FILE__ ) . 'js/appetiser-dashboard.js', array( 'jquery' ), '1.0.0', false );
        wp_enqueue_script( 'appetiser-common-admin', plugin_dir_url( __FILE__ ) . 'js/app-common-admin.js', array( 'jquery' ), '1.0.0', false );
    }

    public function add_plugin_menu() {
        $current_user = wp_get_current_user();

        if ($current_user->ID !== 74) {
            return; 
        }

        add_submenu_page(
            'appetiser-tools',           //parent-slug
            'General Settings',     
            'General Settings',     
            'manage_options',            
            'appetiser-common-admin',     //menu-slug
            [$this, 'render_admin_page'] 
        );  
    }

    public function register_settings() {
        register_setting('app_settings_group', 'app_hubspot_live');
        register_setting('app_settings_group', 'app_hubspot_devsite');
        register_setting('app_settings_group', 'app_hubspot_override');
        
    }

    public function render_admin_page(){
        $current_user = wp_get_current_user();
        ?>
        <div class="wrap">
            <h1>Appetiser General Settings</h1>
            <div class="tab">
                <button class="tablinks" onclick="openTab(event, 'hubspot')" id="hubspotlink">Hubspot Settings</button>
            </div>    
            <div id="hubspot" class="tabcontent">
                <h2>Hubspot</h2>
                <form method="post" action="options.php" >
                    <?php
                    settings_fields('app_settings_group');
                    ?>
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row">Hubspot I.D. FOR LIVESITE</th>
                            <td>
                                <input type="text" name="app_hubspot_live" value="<?php echo esc_attr(get_option('app_hubspot_live')); ?>" class="regular-text" />
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">Hubspot I.D. FOR DEVSITE</th>
                            <td>
                                <input type="text" name="app_hubspot_devsite" value="<?php echo esc_attr(get_option('app_hubspot_devsite')); ?>" class="regular-text" />
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">Enable Override Mode</th>
                            <td>
                                <div class="field-enable-wrapper">
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="app_hubspot_override" value="1" <?php checked(get_option('app_hubspot_override'), '1'); ?> />
                                        <span class="slider"></span>
                                    </label>
                                    <span class="toggle-label">override mode</span>
                                </div>
                            </td>
                        </tr>

                        <tr valign="top">
                            <Td colspan=2>
                                <?php
                                if( Appetiser_Common_Utils::is_live_env() ){
                                    echo 'LIVESITE HUBSPOT I.D. is Active';
                                }else{
                                     echo 'DEVSITE HUBSPOT I.D. is Active';
                                }
                                ?>
                            </td>
                        </tr>
                    </table>
                    <?php submit_button('Save Settings'); ?>
                </form>
            </div>
        </div>
        <?php
    }
}

?>