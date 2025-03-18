<?php

if (!class_exists('classes/EHX_Donate_GiftAid_Data_Table')) {

    if (!class_exists('WP_List_Table')) {
        require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
    }
    
    class EHX_Donate_GiftAid_Data_Table extends WP_List_Table 
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
                'singular' => esc_html__('Gift Aid', 'ehx-donate'),
                'plural'   => esc_html__('Gift Aid', 'ehx-donate'),
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
                'title'   => esc_html__('Title', 'ehx-donate'),
                'first_name'   => esc_html__('First Name', 'ehx-donate'),
                'last_name'   => esc_html__('Last Name', 'ehx-donate'),
                'address'   => esc_html__('House name or number', 'ehx-donate'),
                'post_code'   => esc_html__('Postcode', 'ehx-donate'),
                'aggregated_donations'   => esc_html__('Aggregated Donations', 'ehx-donate'),
                'sponsored_event'   => esc_html__('Sponsored Event', 'ehx-donate'),
                'created_at'   => esc_html__('Donation Date', 'ehx-donate'),
                'total_amount' => esc_html__('Amount', 'ehx-donate'),
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
                'title'  => ['title', false],
                'first_name'   => ['first_name', false],
                'last_name'    => ['last_name', false],
                'created_at' => ['created_at', false],
                'total_amount' => ['total_amount', false],
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
                case 'title':
                    $page = EHX_Donate_Menu::$pages['gift_aid'];
                    $delete_link = admin_url("admin.php?page={$page}&action=ehx_donations_delete&id={$item['id']}");
                    // $view_link = admin_url("admin.php?page={$page}&id={$item['id']}");

                    $actions = [
                        'delete' => '<a href="' . esc_url(wp_nonce_url($delete_link, 'donations_delete_' . $item['id'])) . '" onclick="return confirm(\'Are you sure?\')">Delete</a>',
                        // 'view'   => '<a href="' . esc_url($view_link) . '">' . esc_html__('View', 'ehx-donate') . '</a>'
                    ];

                    return $item['title'] . $this->row_actions($actions);
                case 'created_at':
                    return wp_date('d/m/Y', strtotime($item['created_at']));
                case 'gift_aid':
                    return esc_html($item['gift_aid'] ? 'True' : 'False');
                case 'address':
                    $address = !empty($item['address']) ? unserialize($item['address']) : [];
                    $address_line = $address['address_line_1'] ?? null;
                    $address_line .= $address['city'] ?? null;
                    $address_line .= $address['state'] ?? null;
                    $address_line .= $address['country'] ?? null;
                    return esc_html($address_line);
                case 'post_code':
                    $address = !empty($item['address']) ? unserialize($item['address']) : [];
                    return esc_html($address['post_code'] ?? null);
                case 'total_amount':
                case 'charge':
                    return EHX_Donate_Helper::currencyFormat($item[$column_name] ?? 0);
                case 'aggregated_donations':
                    return $item['recurring'] .' '. esc_html__('Gift Aid donations', 'ehx-donate');
                default:
                    return $item[$column_name] ?? '';
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

                    <input type="submit" class="button" value="Filter">
                    <a href="?page=<?php echo esc_html(EHX_Donate_Menu::$pages['gift_aid']) ?>&per_page=-1&export=csv" class="button action"><?php esc_html_e('Export', 'ehx-donate') ?></a> 
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

            // Set pagination arguments
            $this->set_pagination_args([
                'total_items' => $total_items,
                'per_page'    => $per_page,
                'total_pages' => ceil($total_items / $per_page),
            ]);

            // Set column headers and sortable columns
            $this->_column_headers = [$this->get_columns(), [], $this->get_sortable_columns()];

            // Populate items property with retrieved data
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
            $usermeta_table = esc_sql($wpdb->usermeta);
            $posts_table = esc_sql($wpdb->posts);

            // Sorting
            $valid_orderby = ['id', 'created_at', 'total_amount']; // Allowed sorting columns
            $orderby = esc_sql($this->request->input('orderby', 'id'));
            $orderby = in_array($orderby, $valid_orderby) ? $orderby : 'id';
            $order = esc_sql($this->request->input('order', 'DESC'));
            $order = ($order === 'ASC') ? 'ASC' : 'DESC';

            // Filtering
            $filter_user = $this->request->input('filter_user');
            $filter_status = $this->request->input('filter_status');

            // Build WHERE conditions
            $where = "1=1"; // Always true condition to append other filters easily
            $where .= " AND d.gift_aid = 1 AND d.payment_status = 'Success'";
            $where .= " AND payment_status = 'Success'";

            if ($filter_user) {
                $where .= $wpdb->prepare(" AND d.user_id = %d", $filter_user);
            }

            if ($filter_status) {
                $where .= $wpdb->prepare(" AND d.status = %s", $filter_status);
            }

            // Query: Join with wp_users and wp_usermeta
            $query = "SELECT 
                d.*,
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
                WHERE $where 
                GROUP BY d.id 
                ORDER BY $orderby $order
            ";
            
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

    }
}