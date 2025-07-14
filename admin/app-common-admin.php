<?php

class Appetiser_Common_Admin {
    public function __construct() {
        add_action( 'admin_menu',  array( $this, 'add_plugin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

        add_action( 'admin_init', array( $this, 'register_settings' ) );

        add_action( 'admin_post_app_export_comments', array( $this, 'app_export_comments_to_file' ) );
        add_action( 'admin_post_app_delete_all_comments', array( $this, 'app_delete_all_comments' ) );
        add_action( 'admin_post_app_restore_comments', array( $this, 'app_restore_comments' ) );

        add_action( 'wp_ajax_app_backup_comments_ajax', array( $this, 'ajax_backup_comments' ) );
        add_action( 'wp_ajax_app_restore_comments_ajax', array( $this, 'ajax_restore_comments' ) );
        add_action( 'wp_ajax_app_delete_all_comments_ajax', array( $this, 'ajax_delete_all_comments' ) );
    }

    public function enqueue_styles( $hook ) {
        if (!isset($_GET['page']) || $_GET['page'] !== 'appetiser-common-admin') return;

        wp_enqueue_style('dashicons');
        wp_enqueue_style( 'appetiser-dashboard-style', plugin_dir_url( __FILE__ ) . 'css/appetiser-dashboard.css', array(), '1.0.0', 'all' );
        wp_enqueue_style( 'appetiser-general-settings', plugin_dir_url( __FILE__ ) . 'css/appetiser-general-settings.css', array(), '1.0.0', 'all' );
    }

    public function enqueue_scripts( $hook ) {
        if (!isset($_GET['page']) || $_GET['page'] !== 'appetiser-common-admin') return;

        wp_enqueue_script( 'appetiser-dashboard-script', plugin_dir_url( __FILE__ ) . 'js/appetiser-dashboard.js', array( 'jquery' ), '1.0.0', false );
        wp_enqueue_script( 'appetiser-common-admin', plugin_dir_url( __FILE__ ) . 'js/app-common-admin.js', array( 'jquery' ), '1.0.0', true );
    }

    public function add_plugin_menu() {
        $current_user = wp_get_current_user();
        if ($current_user->ID !== 74) return;

        add_submenu_page(
            'appetiser-tools',
            'General Settings',
            'General Settings',
            'manage_options',
            'appetiser-common-admin',
            array($this, 'render_admin_page')
        );
    }

    public function ajax_backup_comments() {
        if ( ! current_user_can('manage_options') ) wp_send_json_error('Unauthorized');

        @set_time_limit(0);
        @ini_set('memory_limit', '512M');
        require_once ABSPATH . 'wp-admin/includes/export.php';

        $upload_dir = wp_upload_dir();
        $filename = 'app_comments_backup_' . date('mdY') . '.xml';
        $filepath = trailingslashit($upload_dir['basedir']) . $filename;

        $fh = fopen($filepath, 'w');
        if (!$fh) wp_send_json_error('Cannot write file');

        ob_start(function($buffer) use ($fh) {
            fwrite($fh, $buffer);
            return '';
        });
        export_wp(['content' => 'comment']);
        ob_end_flush();
        fclose($fh);

        wp_send_json_success(['filename' => $filename]);
    }
    
    public function ajax_restore_comments() {
        if ( ! current_user_can('manage_options') ) wp_send_json_error('Unauthorized');

        @set_time_limit(0);
        @ini_set('memory_limit', '512M');

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/import.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-importer.php';
        require_once WP_PLUGIN_DIR . '/wordpress-importer/parsers.php';

        if ( ! class_exists( 'WP_Import' ) ) {
            require_once WP_PLUGIN_DIR . '/wordpress-importer/wordpress-importer.php';
            require_once WP_PLUGIN_DIR . '/wordpress-importer/class-wp-import.php';
        }

        $upload_dir = wp_upload_dir();
        $pattern = $upload_dir['basedir'] . '/app_comments_backup_*.xml';
        $files = glob($pattern);
        rsort($files);

        if (empty($files)) wp_send_json_error('No backup file found.');

        $latest_file = $files[0];

        $importer = new WP_Import();
        $importer->fetch_attachments = false;

        ob_start();
        $importer->import($latest_file);
        ob_end_clean();

        wp_send_json_success(['filename' => basename($latest_file)]);
    }

    public function ajax_delete_all_comments() {
        if ( ! current_user_can('manage_options') ) wp_send_json_error('Unauthorized');

        global $wpdb;
        $wpdb->query("DELETE FROM $wpdb->comments");
        $wpdb->query("DELETE FROM $wpdb->commentmeta");

        wp_send_json_success('All comments deleted.');
    }

    public function app_export_comments_to_file() {
        if ( ! current_user_can('manage_options') ) {
            wp_die('Unauthorized');
        }

        @set_time_limit(0);
        @ini_set('memory_limit', '512M');

        require_once ABSPATH . 'wp-admin/includes/export.php';

        $upload_dir = wp_upload_dir();
        $filename = 'app_comments_backup_' . date('mdY') . '.xml';
        $filepath = trailingslashit($upload_dir['basedir']) . $filename;

        $fh = fopen($filepath, 'w');
        if (!$fh) {
            wp_die('Failed to open file for writing.');
        }

        // Use output buffering to stream directly to file
        ob_start(function($buffer) use ($fh) {
            fwrite($fh, $buffer);
            return ''; // clear buffer to avoid memory use
        });

        export_wp(['content' => 'comment']);

        ob_end_flush();
        fclose($fh);

        wp_redirect(admin_url('admin.php?page=appetiser-common-admin&export_success=1&filename=' . $filename));
        exit;
    }

    function app_delete_all_comments() {
        if ( ! current_user_can('moderate_comments') || ! check_admin_referer('app_delete_comments') ) {
            wp_die('Unauthorized');
        }

        global $wpdb;
        $wpdb->query("DELETE FROM $wpdb->comments");
        $wpdb->query("DELETE FROM $wpdb->commentmeta");

        wp_redirect(admin_url('admin.php?page=appetiser-common-admin&deleted_all_comments=1'));
        exit;
    }

    public function app_restore_comments() {
        if ( ! current_user_can('manage_options') ) {
            wp_die('Unauthorized');
        }

        @set_time_limit(0);
        @ini_set('memory_limit', '512M');

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/import.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-importer.php';
        require_once WP_PLUGIN_DIR . '/wordpress-importer/parsers.php';

        // Load the importer from the installed plugin
        if ( ! class_exists( 'WP_Import' ) ) {
            $importer_main  = WP_PLUGIN_DIR . '/wordpress-importer/wordpress-importer.php';
            $importer_class = WP_PLUGIN_DIR . '/wordpress-importer/class-wp-import.php';

            if ( file_exists( $importer_main ) && file_exists( $importer_class ) ) {
                require_once $importer_main;
                require_once $importer_class;
            } else {
                wp_die('The WordPress Importer plugin files could not be found.');
            }
        }

        $upload_dir = wp_upload_dir();
        $pattern = $upload_dir['basedir'] . '/app_comments_backup_*.xml';
        $files = glob($pattern);
        rsort($files); // latest file first

        if (empty($files)) {
            wp_die('No backup file found.');
        }

        $latest_file = $files[0];

        $importer = new WP_Import();
        $importer->fetch_attachments = false;

        ob_start();
        $importer->import($latest_file);
        ob_end_clean();

        wp_redirect(admin_url('admin.php?page=appetiser-common-admin&restored_comments=1'));
        exit;
    }

    public function register_settings() {
        register_setting('app_settings_group', 'app_hubspot_live');
        register_setting('app_settings_group', 'app_hubspot_devsite');
        register_setting('app_settings_group', 'app_hubspot_override');
        register_setting('app_settings_group', 'app_hubspot_forms');
        register_setting('app_settings_group', 'app_comments_enable');
    }

    public function render_admin_page(){
        $current_user = wp_get_current_user();
        ?>
        <div class="wrap">
            <h1>Appetiser General Settings</h1>

            <form method="post" action="options.php" >
            <div class="tab">
                <button class="tablinks" onclick="openTab(event, 'hubspot')" id="hubspotlink">Hubspot Settings</button>
                <button class="tablinks" onclick="openTab(event, 'comments')" id="commentlink">Comment Settings</button>
            </div>    
            <div id="hubspot" class="tabcontent">
                <h2>Hubspot</h2>
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
                        <tr valign="top">
                            <Td colspan=2>
                                <h2>Active Hubspot Forms</h2>
                                <div id="hubspot-form-groups">
                                    <!-- JS will populate form groups here -->
                                </div>
                                <p>
                                    <button type="button" id="add-hubspot-form-group" class="button" title="Add Form Group">
                                        <span class="dashicons dashicons-plus-alt2"></span> Add New Form
                                    </button>
                                </p>
                            </td>
                        </tr>
                        <script>
                            const savedHubspotForms = <?php echo json_encode(get_option('app_hubspot_forms', [])); ?>;
                        </script>
                    </table>
                
            </div>
            <div id="comments" class="tabcontent">
                <h2>Comment settings</h2>
                <div id="app-comments-progress" style="display:none; margin: 10px 0;">
                    <div style="background:#e5e5e5; height:20px; border-radius:4px;">
                        <div id="app-progress-bar" style="width:0%; background:#0073aa; color:#fff; height:20px; text-align:center; line-height:20px;">0%</div>
                    </div>
                    <p id="app-progress-status" style="margin-top:5px;"></p>
                </div>
                <table class="form-table">
                    <tr valign="top">
                        <td colspan="2">
                            <p>
                                <button id="app-backup-comments" class="button">Backup All Comments</button>
                                <button id="app-restore-comments" class="button">Restore Comments</button>
                                <button id="app-delete-comments" class="button button-danger">Delete All Comments</button>ss
                            </p>
                            <small style="display:block; margin-top:5px; color:#555;">
                                Note: Restoration may take some time. You can, however,continue navigating within the dashboard. Just do not leave the admin area during restore.
                            </small>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Block Comment Submissions</th>
                        <td>
                            <div class="field-enable-wrapper">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="app_comments_enable" value="1" <?php checked(get_option('app_comments_enable'), '1'); ?> />
                                    <span class="slider"></span>
                                </label>
                                <span class="toggle-label">"Enable Comment Lockdown</span>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="bottomtab">
                <?php submit_button('Save Settings'); ?>
            </div>
            </form>
        </div>
        <?php
    }
}

?>