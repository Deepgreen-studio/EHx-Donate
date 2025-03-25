<?php

if (!class_exists('EHX_Donate_Donation_Table_Shortcode')) {

    class EHX_Donate_Donation_Table_Shortcode
    {
        /**
         * Initializes the donation table shortcode.
         *
         * This method adds the 'ehx_donate_donation_table' shortcode to WordPress,
         * which triggers the 'add_shortcode' method when used in content.
         */
        public function __construct()
        {
            add_shortcode('ehx_donate_donation_table', [$this, 'add_shortcode']);
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
            $donations = EHX_Donate_Donation_Data_Table::get_data();

            require EHX_DONATE_PLUGIN_DIR . 'views/shortcodes/donation-table.php';

            wp_enqueue_style('ehx-donate-datatable');
            wp_enqueue_script('ehx-donate-datatable');

            return ob_get_clean();
        }

    }
}