<?php

if (!class_exists('EHX_Donate_Actions')) {

    class EHX_Donate_Actions
    {
        private EHX_Donate_Request $request;

        /**
         * Constructor for EHX_Donate_Actions class.
         *
         * Initializes the EHX_Donate_Actions class and sets up necessary actions and properties.
         *
         * @return void
         */
        public function __construct() 
        {
            $this->request = new EHX_Donate_Request();

            // Hook CSV export into WordPress before any output starts
            add_action('admin_init', [$this, 'export_csv']);

            add_action('admin_init', [$this, 'ehx_donate_donation_delete']);
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
            $export = $this->request->input('export');

            if ($export === 'csv') {
                $page  = $this->request->input('page');

                if($page == 'ehx_donate_admin_gift_aid') {
                    [$data] = (new EHX_Donate_GiftAid_Data_Table)->get_query_results();

                    $header_fields = [
                        'Title', 
                        'First Name', 
                        'Last Name', 
                        'Address', 
                        'Postcode', 
                        'Aggregated Donations', 
                        'Sponsored Event', 
                        'Donation Date', 
                        'Amount'
                    ];

                    $filename = 'gift_aid.xlsx';
                }
                else {
                    [$data] = (new EHX_Donate_Donation_Data_Table)->get_query_results();

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
                    wp_die('No data found to export.');
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
                    if($page == 'ehx_donate_admin_gift_aid') {
                        $address = !empty($row['address']) ? unserialize($row['address']) : [];

                        $address_line = $address['address_line_1'] ?? null;
                        $address_line .= $address['city'] ?? null;
                        $address_line .= $address['state'] ?? null;
                        $address_line .= $address['country'] ?? null;

                        $field = [
                            $row['title'],
                            $row['first_name'],
                            $row['last_name'],
                            $address_line,
                            $address['post_code'] ?? null,
                            $row['recurring'] .' Gift Aid donations',
                            '',
                            date('d/m/Y', strtotime($row['created_at'])),
                            $row['total_amount']
                        ];
                    }
                    else {
                        $field = [
                            date('d F Y', strtotime($row['created_at'])),
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
         * Deletes a donation record from the database.
         *
         * This function handles the deletion of a donation record from the WordPress database.
         * It checks if the current user has the necessary permissions and performs a database query to delete the record.
         *
         * @param int $id The ID of the donation record to be deleted.
         * @param string $action The action parameter passed from the admin page.
         * @param string $page The page parameter passed from the admin page.
         *
         * @return void
         */
        public function ehx_donate_donation_delete()
        {
            $id = $this->request->integer('id');
            $action  = $this->request->input('action');

            if ($action === 'ehx_donations_delete') {
                $page  = $this->request->input('page');
                if (!current_user_can('delete_users', $id)) {
                    wp_die('Permission denied');
                }

                check_admin_referer("donations_delete_{$id}");

                global $wpdb;

                $donation_table = esc_sql(EHX_Donate::$donation_table);

                $wpdb->query($wpdb->prepare("DELETE FROM $donation_table WHERE id = %d", $id));

                wp_redirect(admin_url("admin.php?page={$page}&deleted=1"));

                exit;
            }
        }
    }

}