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
                'cb' => '<input type="checkbox" />',
                'created_at'   => 'Date',
                'display_name' => 'Donor',
                'amount' => 'Amount',
                'post_title' => 'Campaign',
                'gift_aid' => 'Gift Aid Enabled',
                'recurring' => 'Recurring',
                'payment_status' => 'Payment',
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
                'created_at'   => ['created_at', false],
                'display_name' => ['display_name', false],
                'amount'       => ['total_amount', false],
                'post_title'   => ['post_title', false],
                'recurring'    => ['recurring', false]
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
                    $page = EHX_Donate_Menu::$pages['donation'];
                    $delete_link = admin_url("admin.php?page={$page}&action=ehx_donations_delete&id={$item['id']}");
                    // $view_link = admin_url("admin.php?page={$page}&id={$item['id']}");

                    $actions = [
                        'delete' => '<a href="' . esc_url(wp_nonce_url($delete_link, 'donations_delete_' . $item['id'])) . '" onclick="return confirm(\'Are you sure?\')">Delete</a>',
                        // 'view'   => '<a href="' . esc_url($view_link) . '">' . esc_html__('View', 'ehx-member') . '</a>'
                    ];

                    return wp_date('d F Y', strtotime($item['created_at']))
                    . $this->row_actions($actions);
                case 'gift_aid':
                    return esc_html($item['gift_aid'] ? 'True' : 'False');
                case 'payment_status':
                    $status_classes = [
                        'Pending' => 'background: orange; color: white; padding: 4px 8px; border-radius: 4px;',
                        'Success' => 'background: green; color: white; padding: 4px 8px; border-radius: 4px;',
                        'Cancel'  => 'background: red; color: white; padding: 4px 8px; border-radius: 4px;',
                    ];
                    return sprintf('<span style="%s">%s</span>', $status_classes[$item['payment_status']], ucfirst($item['payment_status']));
                case 'total_amount':
                case 'charge':
                    return EHX_Donate_Helper::currencyFormat($item[$column_name] ?? 0);
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
                    'order'   => 'ASC',
                ]);
                
                $all_status = [__('Pending', 'ehx-donate'), __('Success', 'ehx-donate'), __('Cancel', 'ehx-donate')];
                $selected_user = $this->request->input('filter_user');
                $selected_status = $this->request->input('filter_status');
                ?>
                <div class="alignleft actions">
                    <input type="hidden" name="page" value="<?php echo esc_html(EHX_Donate_Menu::$pages['donation']) ?>">
                    
                    <!-- User Filter -->
                    <select name="filter_user">
                        <option value=""><?php esc_html_e('All Users', 'ehx-donate') ?></option>
                        <?php foreach ($users as $user) : ?>
                            <option value="<?php echo esc_attr($user->ID); ?>" <?php selected($selected_user, $user->ID); ?>>
                                <?php echo esc_html($user->display_name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <!-- Status Filter -->
                    <select name="filter_status">
                        <option value=""><?php esc_html_e('All', 'ehx-donate') ?></option>
                        <?php foreach($all_status as $status): ?>
                            <option value="<?php echo esc_html($status) ?>" <?php selected($selected_status, $status); ?>><?php echo esc_html($status) ?></option>
                        <?php endforeach ?>
                    </select>

                    <input type="submit" class="button" value="Filter">
                    <a href="?page=<?php echo esc_html(EHX_Donate_Menu::$pages['donation']) ?>&per_page=-1&export=csv" class="button action"><?php esc_html_e('Export', 'ehx-donate') ?></a> 
                </div>
                <?php
            }
        }
        
        /**
         * Prepares the items to be displayed in the list table.
         *
         * This function retrieves the necessary data from the database, applies filters and sorting,
         * and sets up pagination for the list table. It then populates the items property with the
         * retrieved data.
         *
         * @return void
         */
        public function prepare_items(): void 
        {
            global $wpdb;
            $donation_table = esc_sql(EHX_Donate::$donation_table);
            
            // Get query results and pagination parameters
            [$data, $per_page, $where] = $this->get_query_results();

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

        /**
         * Retrieves and prepares the data for the list table.
         *
         * This function constructs a SQL query to retrieve donation data from the database,
         * applies sorting and filtering, and sets up pagination. It then executes the query
         * and returns the results along with pagination parameters.
         *
         * @return array An array containing the retrieved data, the number of items per page, and the WHERE conditions for the query.
         */
        public function get_query_results()
        {
            global $wpdb;
            $donation_table = esc_sql(EHX_Donate::$donation_table);
            $donation_items_table = esc_sql(EHX_Donate::$donation_items_table);
            $users_table = esc_sql($wpdb->users);
            $posts_table = esc_sql($wpdb->posts);

            // Sorting
            $valid_orderby = [
                'id' => 'd.id',
                'created_at' => 'd.created_at',
                'display_name' => 'u.display_name',
                'amount' => 'd.total_amount',
                'post_title' => 'p.post_title',
                'recurring' => 'di.recurring',
            ];

            $orderby = esc_sql($this->request->input('orderby', 'id'));
            $orderby = isset($valid_orderby[$orderby]) ? $valid_orderby[$orderby] : 'd.id';
            $order = esc_sql($this->request->input('order', 'DESC'));
            $order = ($order === 'ASC') ? 'ASC' : 'DESC';

            // Filtering
            $filter_user = $this->request->input('filter_user');
            $filter_status = $this->request->input('filter_status');

            // Build WHERE conditions
            $where = "1=1"; // Always true condition to append other filters easily

            if ($filter_user) {
                $where .= $wpdb->prepare(" AND user_id = %d", $filter_user);
            }

            if ($filter_status) {
                $where .= $wpdb->prepare(" AND payment_status = %s", $filter_status);
            }

            // Query: Join with wp_users and wp_usermeta
            $query = "SELECT d.*, u.display_name, di.recurring, p.post_title 
                FROM $donation_table d 
                LEFT JOIN $users_table u ON d.user_id = u.ID 
                LEFT JOIN $donation_items_table di ON d.id = di.donation_id 
                LEFT JOIN $posts_table p ON di.campaign_id = p.id 
                WHERE $where 
                ORDER BY $orderby $order";

            // Pagination setup
            $per_page = esc_sql($this->request->input('per_page', 10));
            if ($per_page != -1) {
                $current_page = $this->get_pagenum();
                $offset = ($current_page - 1) * $per_page;

                $query .= $wpdb->prepare(" LIMIT %d OFFSET %d", $per_page, $offset);
            }

            $data = $wpdb->get_results($query, ARRAY_A);

            return [$data, $per_page, $where];
        }

    }
}