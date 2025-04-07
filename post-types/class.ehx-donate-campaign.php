<?php

if (!class_exists('EHXDo_Campaign')) {

    class EHXDo_Campaign
    {
        public EHXDo_Response $response;
        public EHXDo_Request $request;
        public EHXDo_Validator $validator;

        const NONCE_ACTION = 'ehxdo_nonce';
        const NONCE_NAME = 'ehxdo_custom_field_form_nonce';

        public function __construct() 
        {
            // Initialize dependencies
            $this->response  = new EHXDo_Response();
            $this->request   = new EHXDo_Request();
            $this->validator = new EHXDo_Validator();

            // Register custom post type
            add_action('init', [$this, 'create_post_type']);

            // Handle post saving
            add_action('save_post', [$this, 'ehxdo_save_post'], 10, 2);

            // Customize admin columns
            $this->customize_admin_columns();

            // Disable Gutenberg and Classic Editor for 'ehxdo-campaign'
            $this->disable_editors();

            // // Remove unnecessary meta boxes
            $this->remove_meta_boxes();

            // // Customize "Add New" post title and placeholder
            $this->customize_post_ui();

            add_filter('template_include', [$this, 'customize_campaign_details']);
        }
        
        
        /**
         * Registers the 'ehxdo-campaign' custom post type.
         *
         * @return void
         */
        public function create_post_type()
        {
            register_post_type('ehxdo-campaign', [
                'label' => esc_html__("Campaigns", 'ehx-donate'),
                'description' => esc_html__("Campaigns", 'ehx-donate'),
                'labels' => [
                    'label' => esc_html__('Campaigns', 'ehx-donate'),
                    'singular_name' => esc_html__('Form', 'ehx-donate'),
                    'menu_name'     => esc_html__('EHx Campaigns', 'ehx-donate'),
                    'all_items'     => esc_html__('All Campaigns', 'ehx-donate'),
                    'add_new'       => esc_html__('Add New', 'ehx-donate'),
                    'add_new_item'  => esc_html__('Add New Campaign', 'ehx-donate'),
                    'edit_item'     => esc_html__('Edit Campaign', 'ehx-donate'),
                    'new_item'      => esc_html__('New Campaign', 'ehx-donate'),
                    'view_item'     => esc_html__('View Campaign', 'ehx-donate'),
                    'search_items'  => esc_html__('Search Campaigns', 'ehx-donate'),
                    'not_found'     => esc_html__('No campaigns found', 'ehx-donate'),
                    'not_found_in_trash' => esc_html__('No campaigns found in Trash', 'ehx-donate')
                ],
                'public' => true,
                'supports' => ['title', 'editor', 'thumbnail'],
                'hierarchical' => false,
                'show_ui' => true,
                'show_in_menu' => false,
                'menu_position' => 5,
                'menu_in_admin_bar' => true,
                'menu_in_nav_menus' => true,
                'can_export' => false,
                'has_archive' => false,
                'exclude_from_search' => false,
                'publicly_queryable' => true,
                'show_in_rest' => true,
                'menu_icon' => 'dashicons-images-alt2',
                'taxonomies'  => ['category', 'post_tag'],
                'register_meta_box_cb' => [$this, 'add_meta_boxes'],
            ]);
        }
        
        /**
         * Customizes the display of custom columns for the 'ehxdo-campaign' post type.
         *
         * @param array $columns An associative array of column names and their display names.
         *
         * @return array The modified associative array of column names and their display names.
         *
         * The function adds three new custom columns to the 'ehxdo-campaign' post type:
         * - 'goal_amount': Displays the goal amount of the campaign.
         * - 'recurring': Indicates whether the campaign is recurring or not.
         * - 'start_and_end_date': Shows the start and end dates of the campaign.
         */
        public function ehx_campaign_cpt_columns($columns)
        {
            $new_columns = array(
                'goal_amount' => __('Goal Amount', 'ehx-donate'),
                'recurring' => __('Recurring', 'ehx-donate'),
                'progress' => __('Progress', 'ehx-donate'),
                'start_and_end_date' => __('Start & End Date', 'ehx-donate'),
            );
            return array_merge($columns, $new_columns);
        }

        /**
         * Customizes the display of custom columns for the 'ehxdo-campaign' post type.
         *
         * @param string $column The name of the column being displayed.
         * @param int $post_id The ID of the current post.
         *
         * @return void
         */
        public function ehx_campaign_custom_columns($column, $post_id)
        {
            $ehx_campaign = get_post_meta($post_id, '_ehx_campaign', true);

            switch ($column) {
                case 'goal_amount':
                    echo esc_html('£' . $ehx_campaign['goal_amount'] ?? '');
                    break;
                case 'progress':
                    global $wpdb;

                    // Get the donation table name
                    $donation_table = EHX_Donate::$donation_items_table;

                    // Query to calculate the total donations for the campaign
                    $sum_total_amount = $wpdb->get_var($wpdb->prepare("SELECT SUM(amount) FROM $donation_table WHERE campaign_id = %d", $post_id));

                    // Ensure $sum_total_amount is a valid number
                    $sum_total_amount = floatval($sum_total_amount);

                    // Get the goal amount
                    $goal_amount = isset($ehx_campaign['goal_amount']) ? floatval($ehx_campaign['goal_amount']) : 0;

                    // Calculate progress percentage
                    $progress = $goal_amount > 0 ? ($sum_total_amount / $goal_amount) * 100 : 0;

                    // Ensure progress does not exceed 100%
                    $progress = min($progress, 100);

                    // Display progress bar
                    echo '<div class="edp-progress-container"><div class="edp-progress html" style="width: ' . esc_attr($progress) . '%;"></div></div>';
                    echo '<p>'. esc_html(EHXDo_Helper::currencyFormat($sum_total_amount)) .' ('.esc_html(round($progress, 2) . '%').')</p>';
                    break;
                case'start_and_end_date':
                    echo esc_html($ehx_campaign['start_date']. '-'. $ehx_campaign['end_date']);
                    break;
                default:
                    echo esc_html($ehx_campaign[$column] ?? '');
                    break;
            }
        }

        /**
         * Customizes the sortable columns for the 'ehxdo-campaign' post type.
         *
         * @param array $columns An associative array of column names and their display names.
         *
         * @return array The modified associative array of column names and their display names.
         */
        public function ehx_campaign_sortable_columns($columns)
        {
            // Add custom sortable columns for 'ehxdo-campaign' post type
            $columns['_ehx_campaign']['goal_amount'] = 'goal_amount';
            $columns['_ehx_campaign']['recurring'] = 'recurring';
            $columns['_ehx_campaign']['start_and_end_date'] = 'start_and_end_date';

            return $columns;
        }

        /**
         * Customize admin columns for 'ehxdo-campaign' post type.
         */
        protected function customize_admin_columns()
        {
            add_filter('manage_ehxdo-campaign_posts_columns', [$this, 'ehx_campaign_cpt_columns']);
            add_action('manage_ehxdo-campaign_posts_custom_column', [$this, 'ehx_campaign_custom_columns'], 10, 2);
            add_filter('manage_edit-ehxdo-campaign_sortable_columns', [$this, 'ehx_campaign_sortable_columns']);
        }

        /**
         * Disable Gutenberg and Classic Editor for 'ehxdo-campaign' post type.
         */
        protected function disable_editors()
        {
            // Disable Gutenberg editor for 'ehxdo-campaign' post type.
            add_filter('use_block_editor_for_post_type', fn($enabled, $post_type) => $post_type === 'ehxdo-campaign' ? false : $enabled, 10, 2);

            // Remove Classic Editor support
            // add_action('admin_head', [$this, 'remove_classic_editor_support']);
            // add_action('add_meta_boxes', [$this, 'remove_editor_support_on_screen'], 100);
        }

        /**
         * Remove unnecessary meta boxes for 'ehxdo-campaign' post type.
         */
        protected function remove_meta_boxes()
        {
            add_action('do_meta_boxes', [$this, 'remove_unnecessary_meta_boxes']);
        }

        /**
         * Customize "Add New" post title and placeholder for 'ehxdo-campaign' post type.
         */
        protected function customize_post_ui()
        {
            add_filter('gettext', [$this, 'customize_add_new_post_title'], 10, 3);
            add_filter('enter_title_here', [$this, 'customize_title_placeholder'], 10, 2);
        }

        /**
         * Remove Classic Editor support for 'ehxdo-campaign' post type.
         */
        public function remove_classic_editor_support()
        {
            if ($this->is_ehx_form_screen()) {
                remove_post_type_support('ehxdo-campaign', 'editor');
            }
        }

        /**
         * Remove editor support on the 'ehxdo-campaign' screen.
         */
        public function remove_editor_support_on_screen()
        {
            if ($screen = get_current_screen()) {
                if ($screen->id === 'ehxdo-campaign') {
                    remove_post_type_support($screen->id, 'editor');
                }
            }
        }

        /**
         * Remove unnecessary meta boxes for 'ehxdo-campaign' post type.
         */
        public function remove_unnecessary_meta_boxes()
        {
            $meta_boxes_to_remove = [
                'generate_layout_options_meta_box' => 'side',
                // 'categorydiv' => 'side',
                // 'tagsdiv-post_tag' => 'side',
                // 'postimagediv' => 'side',
                // 'submitdiv' => 'side',
                // 'slugdiv' => 'normal',
            ];

            foreach ($meta_boxes_to_remove as $meta_box => $context) {
                remove_meta_box($meta_box, 'ehxdo-campaign', $context);
            }
        }

        /**
         * Customize "Add New" post title for 'ehxdo-campaign' post type.
         */
        public function customize_add_new_post_title($translated_text, $text, $domain)
        {
            if (is_admin() && get_post_type() === 'ehxdo-campaign' && $text === 'Add New Post') {
                return esc_html__('Add New Campaign', 'ehx-donate');
            }
            return $translated_text;
        }

        /**
         * Customize placeholder for title field in 'ehxdo-campaign' post type.
         */
        public function customize_title_placeholder($placeholder, $post)
        {
            return $post->post_type === 'ehxdo-campaign' ? esc_html__('Enter Campaign Title', 'ehx-donate') : $placeholder;
        }

        /**
         * Register meta boxes for the 'ehxdo-campaign' post type.
         */
        public function add_meta_boxes()
        {
            $meta_boxes = [
                [
                    'id' => 'ehx_campaign_banner_image_meta_box',
                    'title' => esc_html__('Banner image', 'ehx-donate'),
                    'context' => 'side',
                    'priority' => 'low',
                    'view' => 'admin/inc/banner-image.php',
                ],
                [
                    'id' => 'ehx_campaign_field_meta_box',
                    'title' => esc_html__('Options', 'ehx-donate'),
                    'context' => 'advanced',
                    'priority' => 'low',
                    'view' => 'admin/inc/campaign-options.php',
                    'fields' => true,
                ],
            ];

            foreach ($meta_boxes as $meta_box) {
                add_meta_box(
                    $meta_box['id'],
                    $meta_box['title'],
                    [$this, 'ehx_meta_box'],
                    'ehxdo-campaign',
                    $meta_box['context'],
                    $meta_box['priority'],
                    array_filter([
                        'view' => $meta_box['view'],
                        'fields' => $meta_box['fields'] ?? false,
                    ])
                );
            }
        }

        /**
         * add inner meta boxes
         */
        public function ehx_meta_box($post, $callback_args)
        {
            $args = array_map('esc_html', $callback_args['args']);

            $fields = isset($args['fields']) && $args['fields']  ? EHXDo_Helper::customize() : null;

            require_once EHXDO_PLUGIN_DIR . "views/{$args['view']}";
        }

        /**
         * Save post metadata for 'ehxdo-campaign' post type.
         *
         * @param int|string $post_id Post ID.
         */
        public function ehxdo_save_post($post_id)
        {
            // Verify nonce for security
            $nonce = $this->request->input(self::NONCE_ACTION);
            if (!isset($nonce) || !wp_verify_nonce($nonce, self::NONCE_ACTION)) {
                return;
            }

            // Prevent auto-saves
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return;
            }

            // Ensure the request is for an 'ehxdo-campaign' post type
            if ($this->request->input('post_type') !== 'ehxdo-campaign') {
                return;
            }

            // Ensure the user has permission to edit the post
            if (!current_user_can('edit_post', $post_id)) {
                return;
            }

            // Only proceed if it's an edit post action
            if ($this->request->input('action') !== 'editpost') {
                return;
            }

            $new_value = $this->request->input('_ehx_campaign');
            $old_value = get_post_meta($post_id, '_ehx_campaign', true);

            // Update only if the value has changed
            if ($new_value !== $old_value) {
                update_post_meta($post_id, '_ehx_campaign', $new_value);
            }
        }
        
        /**
         * check is ehx form screen or not
         *
         * @return bool
         */
        private function is_ehx_form_screen(): bool
        {
            $screen = get_current_screen();
            return $screen && $screen->post_type === 'ehxdo-campaign';
        }

        /**
         * Customizes the template for the single 'ehxdo-campaign' post type.
         *
         * This function checks if the current page is a singular 'ehxdo-campaign' post type.
         * If it is, it returns the custom template located at 'EHXDO_PLUGIN_DIR/views/frontend/campaign-details.php'.
         * If it's not a singular 'ehxdo-campaign' post type, it returns the original template.
         *
         * @param string $template The original template file.
         *
         * @return string The modified template file.
         */
        public function customize_campaign_details($template)
        {
            // EHXDo_Helper::dd($template);
            if (is_singular('ehxdo-campaign')) {
                return EHXDO_PLUGIN_DIR . 'views/frontend/campaign-details.php';
            }
            return $template;
        }

    }
}