<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('EHXDo_Donation_Table_Shortcode')) {

    class EHXDo_Donation_Table_Shortcode
    {
        /**
         * Initializes the donation table shortcode.
         *
         * This method adds the 'ehxdo_donation_table' shortcode to WordPress,
         * which triggers the 'add_shortcode' method when used in content.
         */
        public function __construct()
        {
            add_shortcode('ehxdo_donation_table', [$this, 'add_shortcode']);
        }

        /**
         * Adds the donation table shortcode.
         *
         * This function retrieves donation data from the database, includes the donation table view,
         * enqueues necessary CSS and JavaScript files, and returns the rendered HTML.
         *
         * @return string The rendered HTML of the donation table.
         */
        public function add_shortcode()
        {
            $donations = EHXDo_Donation_Data_Table::get_data();

            require EHXDO_PLUGIN_DIR . 'views/shortcodes/donation-table.php';

            wp_enqueue_style('ehxdo-datatable');
            wp_enqueue_script('ehxdo-datatable');

            return ob_get_clean();
        }

    }
}