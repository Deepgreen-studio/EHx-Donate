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
        }

        public function export_csv()
        {
            $export = $this->request->input('export');

            if ($export === 'csv') {
                global $wpdb;
                $donation_table = esc_sql(EHX_Donate::$donation_table);
                $donation_items_table = esc_sql(EHX_Donate::$donation_items_table);
                $users_table = esc_sql($wpdb->users);
                $usermeta_table = esc_sql($wpdb->usermeta);
                $posts_table = esc_sql($wpdb->posts);

                // Ensure no extra output is sent
                if (ob_get_length()) {
                    ob_end_clean();
                }

                // Fetch data
                $query = "SELECT 
                            d.id, 
                            d.created_at, 
                            d.total_amount, 
                            d.user_id, 
                            d.gift_aid, 
                            di.recurring,
                            MAX(CASE WHEN um.meta_key = 'title' THEN um.meta_value END) AS title,
                            MAX(CASE WHEN um.meta_key = 'first_name' THEN um.meta_value END) AS first_name,
                            MAX(CASE WHEN um.meta_key = 'last_name' THEN um.meta_value END) AS last_name,
                            MAX(CASE WHEN um.meta_key = 'address' THEN um.meta_value END) AS address,
                            p.post_title AS cause
                        FROM $donation_table d 
                        LEFT JOIN $donation_items_table di ON d.id = di.donation_id
                        LEFT JOIN $usermeta_table um ON um.user_id = d.user_id 
                        LEFT JOIN $posts_table p ON di.campaign_id = p.id
                        GROUP BY d.id, d.created_at, d.amount, d.user_id, d.gift_aid, di.recurring, p.post_title";

                $results = $wpdb->get_results($query);


                if (empty($results)) {
                    wp_die('No data found to export.');
                }

                // Set headers for CSV download
                header('Content-Type: text/csv; charset=utf-8');
                header('Content-Disposition: attachment; filename=donations.csv');
                header('Pragma: no-cache');
                header('Expires: 0');

                // // Open output stream
                $output = fopen('php://output', 'w');
                
                // // Add CSV column headers
                fputcsv(
                    $output, 
                    [
                        'Title', 
                        'First Name', 
                        'Last Name', 
                        'Address', 
                        'Postcode', 
                        'Aggregated Donations', 
                        'Sponsored Event', 
                        'Donation Date', 
                        'Amount'
                    ]
                );

                // Add data rows
                foreach ($results as $row) {
                    $address = !empty($row->address) ? unserialize($row->address) : [];
                    
                    $address_line = $address['address_line_1'] ?? null;
                    $address_line .= $address['city'] ?? null;
                    $address_line .= $address['state'] ?? null;
                    $address_line .= $address['country'] ?? null;
                    
                    fputcsv(
                        $output, 
                        [
                            $row->title,
                            $row->first_name,
                            $row->last_name,
                            $address_line,
                            $address['post_code'] ?? null,
                            $row->recurring .' Gift Aid donations',
                            '',
                            $row->created_at,
                            $row->total_amount
                        ]
                    );
                }

                fclose($output);
                exit;
            }
        }
    }

}