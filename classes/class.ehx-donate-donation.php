<?php

if (!class_exists('classes/EHXDo_Donation_Data_Table')) {

    if (!class_exists('WP_List_Table')) {
        require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
    }
    
    class EHXDo_Donation_Data_Table extends WP_List_Table 
    {   
        private EHXDo_Request $request;

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
                'singular' => esc_html__('Donation', 'ehx-donate'),
                'plural'   => esc_html__('Donations', 'ehx-donate'),
                'ajax'     => false
            ]);

            $this->request = new EHXDo_Request();
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
                'created_at'   => esc_html__('Date', 'ehx-donate'),
                'display_name' => esc_html__('Donor', 'ehx-donate'),
                'amount' => esc_html__('Amount', 'ehx-donate'),
                'post_title' => esc_html__('Campaign', 'ehx-donate'),
                'gift_aid' => esc_html__('Gift Aid Enabled', 'ehx-donate'),
                'recurring' => esc_html__('Recurring', 'ehx-donate'),
                'payment_status' => esc_html__('Payment', 'ehx-donate'),
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
                    $page = EHXDo_Menu::$pages['donation'];
                    $delete_link = admin_url("admin.php?page={$page}&action=ehx_donations_delete&id={$item['id']}");
                    // $view_link = admin_url("admin.php?page={$page}&id={$item['id']}");

                    $actions = [
                        'delete' => '<a href="' . esc_url(wp_nonce_url($delete_link, 'donations_delete_' . $item['id'])) . '" onclick="return confirm(\'Are you sure?\')">Delete</a>',
                        // 'view'   => '<a href="' . esc_url($view_link) . '">' . esc_html__('View', 'ehx-donate') . '</a>'
                    ];

                    return wp_date('d F Y', strtotime($item['created_at'])) . $this->row_actions($actions);
                case 'gift_aid':
                    return esc_html($item[$column_name] ? 'True' : 'False');
                case 'display_name':
                    return esc_html($item[$column_name]) . '<br/>' . esc_html($item['user_email']);
                case 'post_title':
                    return !empty($item[$column_name]) ? esc_html($item[$column_name]) : esc_html__('Quick Donation', 'ehx-donate');
                case 'payment_status':
                    $status_classes = [
                        'Pending' => 'background: orange; color: white; padding: 4px 8px; border-radius: 4px;',
                        'Success' => 'background: green; color: white; padding: 4px 8px; border-radius: 4px;',
                        'Cancel'  => 'background: red; color: white; padding: 4px 8px; border-radius: 4px;',
                    ];
                    return sprintf('<span style="%s">%s</span>', $status_classes[$item['payment_status']], ucfirst($item['payment_status']));
                case 'amount':
                case 'charge':
                    return EHXDo_Helper::currencyFormat($item[$column_name] ?? 0);
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
                    <input type="hidden" name="page" value="<?php echo esc_html(EHXDo_Menu::$pages['donation']) ?>">
                    
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
                    <a href="?page=<?php echo esc_html(EHXDo_Menu::$pages['donation']) ?>&per_page=-1&export=edp-csv" class="button action"><?php esc_html_e('Export', 'ehx-donate') ?></a> 
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
            // Get query results and pagination parameters
            [$data, $per_page, $total_items] = $this->get_query_results();

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
            $orderby = $valid_orderby[$orderby] ?? 'd.id';
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
            $query = "SELECT d.*, u.display_name, u.user_email, di.recurring, p.post_title 
                FROM $donation_table d 
                LEFT JOIN $users_table u ON d.user_id = u.ID 
                LEFT JOIN $donation_items_table di ON d.id = di.donation_id 
                LEFT JOIN $posts_table p ON di.campaign_id = p.id 
                WHERE $where 
                ORDER BY $orderby $order";

            $total_items = count($wpdb->get_col($query));

            // Pagination setup
            $per_page = esc_sql($this->request->input('per_page', 10));
            if ($per_page != -1) {
                $current_page = $this->get_pagenum();
                $offset = ($current_page - 1) * $per_page;

                $query .= $wpdb->prepare(" LIMIT %d OFFSET %d", $per_page, $offset);
            }

            $data = $wpdb->get_results($query, ARRAY_A);

            return [$data, $per_page, $total_items];
        }

        /**
         * Retrieves donation data for the current user.
         *
         * This function connects to the WordPress database, retrieves donation data for the current user,
         * and returns the results as an associative array. The retrieved data includes donation details,
         * campaign information, and whether the donation is recurring.
         *
         * @global wpdb $wpdb The WordPress database object.
         *
         * @return array An associative array containing the donation data for the current user.
         *               If no data is found, an empty array is returned.
         */
        public static function get_data()
        {
            global $wpdb;

            // Define table names
            $donation_table = esc_sql(EHX_Donate::$donation_table);
            $donation_items_table = esc_sql(EHX_Donate::$donation_items_table);
            $posts_table = esc_sql($wpdb->posts);

            // Get the current user ID
            $user_id = get_current_user_id();

            // Prepare the SQL query
            $query = $wpdb->prepare(
                "SELECT d.*, di.recurring, p.post_title 
                 FROM $donation_table d 
                 LEFT JOIN $donation_items_table di ON d.id = di.donation_id 
                 LEFT JOIN $posts_table p ON di.campaign_id = p.id 
                 WHERE user_id = %d",
                $user_id
            );

            // Execute the query and retrieve the results
            $data = $wpdb->get_results($query);

            // Return the data or an empty array if no data is found
            return $data ?? [];
        }

    }
}