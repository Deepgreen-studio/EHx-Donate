<?php

if (!class_exists('classes/EHX_Donate_Donation_Data_Table')) {

    if (!class_exists('WP_List_Table')) {
        require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
    }
    
    class EHX_Donate_Donation_Data_Table extends WP_List_Table 
    {   
        private EHX_Donate_Request $request;

        /**
         * Constructor for the Payment_Data_Table class.
         *
         * Initializes the parent class and sets up the necessary properties.
         *
         * @param array $args Arguments for the WP_List_Table class constructor.
         */
        public function __construct() 
        {
            parent::__construct([
                'singular' => 'Payment',
                'plural'   => 'Payments',
                'ajax'     => false
            ]);

            $this->request = new EHX_Donate_Request();
        }
           
        /**
         * Get the columns for the list table.
         *
         * @return array An associative array where the keys are the column slugs and the values are the column names.
         */
        public function get_columns(): array 
        {
            return [
                'cb'     => '<input type="checkbox" />',
                'created_at'   => 'Date',
                'display_name'  => 'Donor',
                'amount' => 'Amount',
                'post_title'  => 'Cause',
                'gift_aid' => 'Gift Aid Enabled',
                'recurring'     => 'Recurring',
            ];
        }
         
        /**
         * Get the sortable columns for the list table.
         *
         * @return array An associative array where the keys are the column slugs and the values are arrays containing the column name and a boolean indicating whether the column is sortable.
         *
         * @since 1.0.0
         */
        public function get_sortable_columns(): array 
        {
            return [
                'date'  => ['created_at', false],
                'donor'  => ['donor', false],
                'amount'     => ['total_amount', false],
                'cause'     => ['cause', false],
                'recurring' => ['recurring', false]
            ];
        }
         
        /**
         * Generate the checkbox HTML for the list table.
         *
         * This function generates the HTML for a checkbox input element that will be used in the list table.
         * The checkbox is used for selecting multiple items for bulk actions.
         *
         * @param array $item An associative array representing a single row in the list table.
         *                    The array should contain the 'id' key, which corresponds to the transaction ID.
         *
         * @return string The HTML for the checkbox input element.
         */
        public function column_cb($item): string 
        {
            return sprintf('<input type="checkbox" name="transaction[]" value="%s" />', $item['id']);
        }
           
        /**
         * Handles the output for the default columns in the list table.
         *
         * This function is called when the default columns are being displayed in the list table.
         * It checks the column name and returns the appropriate value based on the column.
         *
         * @param array $item An associative array representing a single row in the list table.
         *                    The array should contain the column slugs as keys and their corresponding values.
         * @param string $column_name The name of the column being displayed.
         *
         * @return mixed The value to be displayed in the specified column.
         */
        public function column_default($item, $column_name): mixed 
        {
            switch ($column_name) {
                case 'created_at':
                    return $item['created_at'];
                case 'gift_aid':
                    return esc_html($item['gift_aid'] ? 'True' : 'False');
                case 'amount':
                case 'charge':
                    return '£' . number_format($item[$column_name] ?? 0, 2);
                default:
                    return $item[$column_name];
            }
        }
        
        /**
         * Handles the output for the extra navigation bar at the top of the list table.
         *
         * This function generates the HTML for the extra navigation bar at the top of the list table.
         * It includes dropdown filters for users and payment statuses.
         *
         * @param string $which The context for the extra table navigation.
         *                      It can be either 'top' or 'bottom'.
         *
         * @return void
         */
        public function extra_tablenav($which): void 
        {
            if ($which === 'top') {
                $users = get_users([
                    'fields'  => ['ID', 'display_name'],
                    'orderby' => 'display_name',
                    'order'   => 'ASC'
                ]);
                
                $selected_user = $this->request->input('filter_user');
                $selected_status = $this->request->input('filter_status');
                ?>
                <div class="alignleft actions">
                    <input type="hidden" name="page" value="ehx_donate_admin_donations">
                    
                    <!-- User Filter -->
                    <select name="filter_user">
                        <option value="">All Users</option>
                        <?php foreach ($users as $user) : ?>
                            <option value="<?php echo esc_attr($user->ID); ?>" <?php selected($selected_user, $user->ID); ?>>
                                <?php echo esc_html($user->display_name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <!-- Status Filter -->
                    <select name="filter_status">
                        <option value="">All Statuses</option>
                        <option value="pending" <?php selected($selected_status, 'pending'); ?>>Pending</option>
                        <option value="success" <?php selected($selected_status, 'success'); ?>>Success</option>
                        <option value="cancel" <?php selected($selected_status, 'cancel'); ?>>Cancel</option>
                    </select>

                    <input type="submit" class="button" value="Filter">
                    <a href="?page=ehx_donate_admin_donations&export=csv" class="button action">Export CSV</a> 
                </div>
                <?php
            }
        }
        
         
        /**
         * Prepares the items to be displayed in the list table.
         *
         * This function retrieves payment data from the database, applies pagination, sorting, and filtering,
         * and sets up the necessary properties for the list table.
         *
         * @return void
         */
        public function prepare_items(): void 
        {
            global $wpdb;
            $donation_table = esc_sql(EHX_Donate::$donation_table);
            $donation_items_table = esc_sql(EHX_Donate::$donation_items_table);
            $users_table = esc_sql($wpdb->users);
            $posts_table = esc_sql($wpdb->posts);
        
            // Pagination setup
            $per_page = 10;
            $current_page = $this->get_pagenum();
            $offset = ($current_page - 1) * $per_page;
        
            // Sorting
            $orderby = esc_sql($this->request->input('orderby', 'id'));
            $order = esc_sql($this->request->input('order', 'DESC'));
        
            // Filtering
            $filter_user = $this->request->input('filter_user');
            $filter_status = $this->request->input('filter_status');
        
            // Build WHERE conditions
            $where = "1=1"; // Always true condition to append other filters easily
        
            if ($filter_user) {
                $where .= $wpdb->prepare("AND p.user_id = %d", $filter_user);
            }
        
            if ($filter_status) {
                $where .= $wpdb->prepare("AND p.status = %s", $filter_status);
            }
        
            // Query: Join with wp_users and wp_usermeta
            $query = $wpdb->prepare("SELECT d.*, u.display_name, di.recurring, p.post_title FROM $donation_table d 
                LEFT JOIN $users_table u ON d.user_id = u.ID 
                LEFT JOIN $donation_items_table di ON d.id = di.donation_id 
                LEFT JOIN $posts_table p ON di.campaign_id = p.id 
                WHERE $where GROUP BY d.id ORDER BY $orderby $order LIMIT %d OFFSET %d
                ", 
                $per_page, $offset);
        
            $data = $wpdb->get_results($query, ARRAY_A);
        
            // Get total items for pagination
            $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $donation_table WHERE $where");
        
            $this->set_pagination_args([
                'total_items' => $total_items,
                'per_page'    => $per_page,
                'total_pages' => ceil($total_items / $per_page),
            ]);
        
            $this->_column_headers = [$this->get_columns(), [], $this->get_sortable_columns()];
            $this->items = $data;
        }
        
    }
}