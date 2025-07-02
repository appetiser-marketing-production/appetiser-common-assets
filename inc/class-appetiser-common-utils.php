<?php


 if (!defined('ABSPATH')) {
    exit; // Prevent direct access
}


class Appetiser_Common_Utils {

    public static function is_live_env() {
        $site_url = site_url();

        if (strpos($site_url, 'dev') !== false || strpos($site_url, 'local') !== false) {
            return false;
        }

        return true;
    }

}

?>