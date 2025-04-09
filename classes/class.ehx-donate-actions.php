<?php

if (!class_exists('EHXDo_Actions')) {

    class EHXDo_Actions
    {
        private EHXDo_Helper $helper;

        /**
         * Constructor for EHXDo_Actions class.
         *
         * Initializes the EHXDo_Actions class and sets up necessary actions and properties.
         *
         * @return void
         */
        public function __construct() 
        {
            $this->helper = new EHXDo_Helper();

            // Hook CSV export into WordPress before any output starts
            add_action('admin_init', [$this, 'export_csv']);

            add_action('admin_init', [$this, 'ehxdo_table_row_delete']);
        }

        /**
         * Exports donation data to CSV or Excel file.
         *
         * This function handles the export of donation data to a CSV or Excel file based on the provided parameters.
         * It checks the export parameter and the page parameter to determine the type of data to export.
         * It then sets the appropriate headers for the download, opens an output stream, adds column headers,
         * and iterates through the data to create CSV rows.
         *
         * @param string $export The export parameter passed from the admin page.
         * @param string $page The page parameter passed from the admin page.
         *
         * @return void
         */
        public function export_csv()
        {
            $export = $this->helper->getInput('export');

            if ($export === 'edp-csv') {

                if (!current_user_can('manage_donations')) {
                    wp_die(esc_html__('Permission denied', 'ehx-donate'));
                }

                $page  = $this->helper->getInput('page');

                if($page == EHXDo_Menu::$pages['transaction']) {
                    [$data] = (new EHXDo_Transaction_Data_Table)->get_query_results();

                    $header_fields = [
                        'Date', 
                        'Donor', 
                        'Campaign',
                        'Amount',
                        'Status', 
                        'Type',
                    ];

                    $filename = 'transactions.csv';
                } 
                else {
                    [$data] = (new EHXDo_Donation_Data_Table)->get_query_results();

                    $header_fields = [
                        'Date', 
                        'Donor', 
                        'Amount',
                        'Campaign',
                        'Gift Aid', 
                        'Recurring', 
                        'Payment',
                    ];

                    $filename = 'donations.csv';
                }

                if (empty($data)) {
                    wp_die(esc_html__('No data found to export.', 'ehx-donate'));
                }

                // Set headers for CSV download
                header('Content-Type: text/csv; charset=utf-8');
                header("Content-Disposition: attachment; filename={$filename}");
                header('Pragma: no-cache');
                header('Expires: 0');

                // // Open output stream
                $output = fopen('php://output', 'w');

                // // Add CSV column headers
                fputcsv($output, $header_fields);

                // Add data rows
                foreach ($data as $row) {
                    if($page == EHXDo_Menu::$pages['transaction']) {
                        $field = [
                            wp_date('d F Y', strtotime($row['created_at'])),
                            $row['display_name'],
                            $row['post_title'],
                            $row['amount'],
                            $row['status'],
                            $row['type'],
                        ];
                    } 
                    else {
                        $field = [
                            wp_date('d F Y', strtotime($row['created_at'])),
                            $row['display_name'],
                            $row['total_amount'],
                            $row['post_title'],
                            $row['gift_aid'] ? 'True' : 'False',
                            $row['recurring'],
                            $row['payment_status']
                        ];
                    }

                    fputcsv($output,  $field);
                }

                fclose($output);
                exit;
            }
        }

        /**
         * Deletes a donation or transaction record from the database.
         *
         * This function handles the deletion of donation or transaction records based on the provided action and ID.
         * It checks user capabilities, verifies the action, and performs the deletion using the WordPress database API.
         *
         * @param int $id The ID of the record to be deleted.
         * @param string $action The action to be performed (ehx_donations_delete or ehx_transactions_delete).
         *
         * @return void
         */
        public function ehxdo_table_row_delete()
        {
            $id = $this->helper->getInput('id');
            $action  = $this->helper->getInput('action');

            if(!empty($id) && !empty($action)) {
                if (!current_user_can('manage_donations', $id) || !current_user_can('manage_transactions', $id)) {
                    wp_die(esc_html__('Permission denied', 'ehx-donate'));
                }
            }

            if ($action === 'ehx_donations_delete') {
                check_admin_referer("donations_delete_{$id}");

                $table = esc_sql(EHX_Donate::$donation_table);
            }

            if ($action === 'ehx_transactions_delete') {

                check_admin_referer("transactions_delete_{$id}");

                $table = esc_sql(EHX_Donate::$transaction_table);
            }

            if(isset($table)) {
                $page  = $this->helper->getInput('page');

                global $wpdb;

                $wpdb->query($wpdb->prepare("DELETE FROM %i WHERE id = %d", $table, $id));

                wp_redirect(admin_url("admin.php?page={$page}&deleted=1"));

                exit;
            }
        }
    }

}