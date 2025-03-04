<?php

if (!class_exists('EHX_Donate_Event')) {

    class EHX_Donate_Event
    {
        public EHX_Donate_Response $response;
        public EHX_Donate_Request $request;
        public EHX_Donate_Validator $validator;

        const NONCE_ACTION = 'ehx_donate_nonce';
        const NONCE_NAME = 'ehx_custom_field_form_nonce';

        public function __construct() 
        {
            // Initialize dependencies
            $this->response  = new EHX_Donate_Response();
            $this->request   = new EHX_Donate_Request();
            $this->validator = new EHX_Donate_Validator();

            // Register custom post type
            add_action('init', [$this, 'create_post_type']);

            // Handle post saving
            add_action('save_post', [$this, 'save_post'], 10, 2);

            // AJAX handlers
            add_action('wp_ajax_ehx_add_field_modal', [$this, 'ehx_add_field_modal']);
            add_action('wp_ajax_ehx_custom_field_modal', [$this, 'ehx_custom_field_modal']);
            add_action('wp_ajax_ehx_render_input_field', [$this, 'ehx_render_input_field']);

            // Customize admin columns
            $this->customize_admin_columns();

            // Disable Gutenberg and Classic Editor for 'ehx-event'
            $this->disable_editors();

            // // Remove unnecessary meta boxes
            $this->remove_meta_boxes();

            // // Customize "Add New" post title and placeholder
            $this->customize_post_ui();
        }
        
        /**
         * Registers the 'ehx-event' custom post type.
         *
         * @return void
         */
        public function create_post_type()
        {
            register_post_type('ehx-event', [
                'label' => esc_html__("Events", 'ehx-donate'),
                'description' => esc_html__("Events", 'ehx-donate'),
                'labels' => [
                    'label' => esc_html__('Events', 'ehx-donate'),
                    'singular_name' => esc_html__('Form', 'ehx-donate'),
                    'menu_name'     => esc_html__('EHx Events', 'ehx-donate'),
                    'all_items'     => esc_html__('All Events', 'ehx-donate'),
                    'add_new'       => esc_html__('Add New', 'ehx-donate'),
                    'add_new_item'  => esc_html__('Add New Event', 'ehx-donate'),
                    'edit_item'     => esc_html__('Edit Event', 'ehx-donate'),
                    'new_item'      => esc_html__('New Event', 'ehx-donate'),
                    'view_item'     => esc_html__('View Event', 'ehx-donate'),
                    'search_items'  => esc_html__('Search Events', 'ehx-donate'),
                    'not_found'     => esc_html__('No events found', 'ehx-donate'),
                    'not_found_in_trash' => esc_html__('No events found in Trash', 'ehx-donate')
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
                'register_meta_box_cb' => [$this, 'add_meta_boxes'],
            ]);
        }
        
        /**
         * Customizes the display of custom columns for the 'ehx-event' post type.
         *
         * @param array $columns An associative array of column names and their display names.
         *
         * @return array The modified associative array of column names and their display names.
         *
         * The function adds three new custom columns to the 'ehx-event' post type:
         * - 'goal_amount': Displays the goal amount of the event.
         * - 'recurring': Indicates whether the event is recurring or not.
         * - 'start_and_end_date': Shows the start and end dates of the event.
         */
        public function ehx_event_cpt_columns($columns)
        {
            $new_columns = array(
                'location' => __('Location', 'ehx-donate'),
                'ticket_price' => __('Ticket Price', 'ehx-donate'),
                'date_and_time' => __('Date & Time', 'ehx-donate'),
            );
            return array_merge($columns, $new_columns);
        }

        /**
         * Customizes the display of custom columns for the 'ehx-event' post type.
         *
         * @param string $column The name of the column being displayed.
         * @param int $post_id The ID of the current post.
         *
         * @return void
         */
        public function ehx_event_custom_columns($column, $post_id)
        {
            $ehx_event = get_post_meta($post_id, '_ehx_event', true);

            echo match($column) {
                'ticket_price' => esc_html(EHX_Donate_Helper::currencyFormat($ehx_event['ticket_price'] ?? 0)),
                'date_and_time' => esc_html($ehx_event['date'] ?? '' . $ehx_event['start_time'] ?? '' . '-' . $ehx_event['end_time'] ?? ''),
                default => esc_html($ehx_event[$column] ?? ''),
            };
        }

        /**
         * Customizes the sortable columns for the 'ehx-event' post type.
         *
         * @param array $columns An associative array of column names and their display names.
         *
         * @return array The modified associative array of column names and their display names.
         */
        public function ehx_event_sortable_columns($columns)
        {
            // Add custom sortable columns for 'ehx-event' post type
            $columns['_ehx_event']['location'] = 'location';
            $columns['_ehx_event']['ticket_price'] = 'ticket_price';
            $columns['_ehx_event']['date_and_time'] = 'date_and_time';

            return $columns;
        }

        /**
         * Customize admin columns for 'ehx-event' post type.
         */
        protected function customize_admin_columns()
        {
            add_filter('manage_ehx-event_posts_columns', [$this, 'ehx_event_cpt_columns']);
            add_action('manage_ehx-event_posts_custom_column', [$this, 'ehx_event_custom_columns'], 10, 2);
            add_filter('manage_edit-ehx-event_sortable_columns', [$this, 'ehx_event_sortable_columns']);
        }

        /**
         * Disable Gutenberg and Classic Editor for 'ehx-event' post type.
         */
        protected function disable_editors()
        {
            // Disable Gutenberg editor for 'ehx-event' post type.
            add_filter('use_block_editor_for_post_type', fn($enabled, $post_type) => $post_type === 'ehx-event' ? false : $enabled, 10, 2);

            // Remove Classic Editor support
            // add_action('admin_head', [$this, 'remove_classic_editor_support']);
            // add_action('add_meta_boxes', [$this, 'remove_editor_support_on_screen'], 100);
        }

        /**
         * Remove unnecessary meta boxes for 'ehx-event' post type.
         */
        protected function remove_meta_boxes()
        {
            add_action('do_meta_boxes', [$this, 'remove_unnecessary_meta_boxes']);
        }

        /**
         * Customize "Add New" post title and placeholder for 'ehx-event' post type.
         */
        protected function customize_post_ui()
        {
            add_filter('gettext', [$this, 'customize_add_new_post_title'], 10, 3);
            add_filter('enter_title_here', [$this, 'customize_title_placeholder'], 10, 2);
        }

        /**
         * Remove Classic Editor support for 'ehx-event' post type.
         */
        public function remove_classic_editor_support()
        {
            if ($this->is_ehx_form_screen()) {
                remove_post_type_support('ehx-event', 'editor');
            }
        }

        /**
         * Remove editor support on the 'ehx-event' screen.
         */
        public function remove_editor_support_on_screen()
        {
            if ($screen = get_current_screen()) {
                if ($screen->id === 'ehx-event') {
                    remove_post_type_support($screen->id, 'editor');
                }
            }
        }

        /**
         * Remove unnecessary meta boxes for 'ehx-event' post type.
         */
        public function remove_unnecessary_meta_boxes()
        {
            $meta_boxes_to_remove = [
                'generate_layout_options_meta_box' => 'side',
                'categorydiv' => 'side',
                'tagsdiv-post_tag' => 'side',
                // 'postimagediv' => 'side',
                // 'submitdiv' => 'side',
                'slugdiv' => 'normal',
            ];

            foreach ($meta_boxes_to_remove as $meta_box => $context) {
                remove_meta_box($meta_box, 'ehx-event', $context);
            }
        }

        /**
         * Customize "Add New" post title for 'ehx-event' post type.
         */
        public function customize_add_new_post_title($translated_text, $text, $domain)
        {
            if (is_admin() && get_post_type() === 'ehx-event' && $text === 'Add New Post') {
                return esc_html__('Add New event', 'ehx-member');
            }
            return $translated_text;
        }

        /**
         * Customize placeholder for title field in 'ehx-event' post type.
         */
        public function customize_title_placeholder($placeholder, $post)
        {
            return $post->post_type === 'ehx-event' ? esc_html__('Enter event Title', 'ehx-member') : $placeholder;
        }

        /**
         * Register meta boxes for the 'ehx-event' post type.
         */
        public function add_meta_boxes()
        {
            $meta_boxes = [
                [
                    'id' => 'ehx_event_field_meta_box',
                    'title' => esc_html__('Options', 'ehx-member'),
                    'context' => 'advanced',
                    'priority' => 'low',
                    'view' => 'admin/inc/event-options.php',
                    'fields' => true,
                ],
            ];

            foreach ($meta_boxes as $meta_box) {
                add_meta_box(
                    $meta_box['id'],
                    $meta_box['title'],
                    [$this, 'ehx_meta_box'],
                    'ehx-event',
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

            $fields = isset($args['fields']) && $args['fields']  ? EHX_Donate_Helper::customize() : null;

            require_once EHX_DONATE_PLUGIN_DIR . "views/{$args['view']}";
        }

        /**
         * Save post metadata for 'ehx-event' post type.
         *
         * @param int|string $post_id Post ID.
         */
        public function save_post($post_id)
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

            // Ensure the request is for an 'ehx-event' post type
            if ($this->request->input('post_type') !== 'ehx-event') {
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

            $new_value = $this->request->input('_ehx_event');
            $old_value = get_post_meta($post_id, '_ehx_event', true);

            // Update only if the value has changed
            if ($new_value !== $old_value) {
                update_post_meta($post_id, '_ehx_event', $new_value);
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
            return $screen && $screen->post_type === 'ehx-event';
        }
    }
}