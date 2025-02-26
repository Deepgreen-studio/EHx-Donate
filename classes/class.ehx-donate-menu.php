<?php

if (!class_exists('EHX_Donate_Menu')) {

    class EHX_Donate_Menu 
    {
        private EHX_Donate_Request $request;

        /**
         * Constructor for EHX_Member_Menu class.
         *
         * Initializes the EHX_Request object and adds the admin menu.
         */
        public function __construct() 
        {
            $this->request = new EHX_Donate_Request();

            // Add admin menu
            add_action('admin_menu', [$this, 'add_menu']);
        }

        /**
         * Adds the admin menu and submenus.
         *
         * This function retrieves the menu structure from the get_menu_structure method,
         * iterates through each menu and submenu, and adds them to the WordPress admin menu.
         * It also removes a duplicate submenu item.
         *
         * @return void
         */
        public function add_menu() 
        {
            // Define the main menu and submenus
            $menus = $this->get_menu_structure();

            // Add the main menu and submenus
            foreach ($menus as $menu) {
                $this->add_main_menu($menu);

                foreach ($menu['submenus'] as $submenu) {
                    $this->add_submenu($menu['menu_slug'], $submenu);
                }
            }

            // Remove the duplicate submenu item
            remove_submenu_page('ehx_donate_admin', 'ehx_donate_admin');
        }

        /**
         * Retrieves the menu structure for the admin dashboard.
         *
         * @return array An array of menu items, each containing the following keys:
         * - page_title: The title of the page.
         * - menu_title: The title displayed in the admin menu.
         * - menu_slug: The slug for the menu.
         * - callback: The callback function to be executed when the menu is clicked.
         * - icon_url: The URL of the icon to be displayed in the admin menu.
         * - submenus: An array of submenus, each containing the same keys as the main menu items.
         */
        private function get_menu_structure(): array
        {
            return [
                [
                    'page_title' => esc_html__('EHx Donate', 'ehx-member'),
                    'menu_title' => esc_html__('EHx Donates', 'ehx-member'),
                    'menu_slug'  => 'ehx_donate_admin',
                    'callback'   => [$this, 'ehx_donate_settings_page'],
                    'icon_url'   => 'dashicons-admin-users',
                    'submenus'  => [
                        [
                            'page_title' => esc_html__('Settings', 'ehx-member'),
                            'menu_title' => esc_html__('Settings', 'ehx-member'),
                            'menu_slug'  => 'ehx_donate_admin_settings',
                            'callback'   => [$this, 'ehx_donate_settings_page'],
                        ],
                        [
                            'page_title' => esc_html__('Donations', 'ehx-member'),
                            'menu_title' => esc_html__('Donations', 'ehx-member'),
                            'menu_slug'  => 'ehx_donate_admin_donations',
                            'callback'   => [$this, 'ehx_donate_donations_page'],
                        ],
                        [
                            'page_title' => esc_html__('Campaigns', 'ehx-member'),
                            'menu_title' => esc_html__('Campaigns', 'ehx-member'),
                            'menu_slug'  => 'edit.php?post_type=ehx-campaign',
                            'callback'   => null,
                        ],
                        [
                            'page_title' => esc_html__('Gift Aid Transaction', 'ehx-member'),
                            'menu_title' => esc_html__('Gift Aid Transaction', 'ehx-member'),
                            'menu_slug'  => 'ehx_donate_transactions',
                            'callback'   => [$this, 'ehx_donate_transactions_page'],
                        ],
                    ],
                    // [
                    //     'page_title' => esc_html__('User Details', 'ehx-member'),
                    //     'menu_title' => esc_html__('User Details', 'ehx-member'),
                    //     'menu_slug'  => 'ehx_member_user_view',
                    //     'callback'   => [$this, 'render_user_view_page'],
                    //     'icon_url'   => 'dashicons-admin-users'
                    // ]
                ],
            ];
        }

        /**
         * Adds a main menu to the WordPress admin dashboard.
         *
         * @param array $menu An associative array containing the menu details.
         * The array should have the following keys:
         * - page_title: The title of the page.
         * - menu_title: The title displayed in the admin menu.
         * - menu_slug: The slug for the menu.
         * - callback: The callback function to be executed when the menu is clicked.
         * - icon_url: The URL of the icon to be displayed in the admin menu.
         *
         * @return void
         */
        private function add_main_menu(array $menu): void
        {
            add_menu_page(
                $menu['page_title'],
                $menu['menu_title'],
                'manage_options',
                $menu['menu_slug'],
                $menu['callback'],
                $menu['icon_url']
            );
        }

        /**
         * Adds a submenu to the WordPress admin dashboard.
         *
         * @param string $parent_slug The slug of the parent menu.
         * @param array $submenu An associative array containing the submenu details.
         * The array should have the following keys:
         * - page_title: The title of the page.
         * - menu_title: The title displayed in the admin menu.
         * - menu_slug: The slug for the submenu.
         * - callback: The callback function to be executed when the submenu is clicked.
         *
         * @return void
         */
        private function add_submenu(string $parent_slug, array $submenu): void
        {
            add_submenu_page(
                $parent_slug,
                $submenu['page_title'],
                $submenu['menu_title'],
                'manage_options',
                $submenu['menu_slug'],
                $submenu['callback']
            );
        }

        /**
         * Callback function for the settings page.
         *
         * This function checks if the current user has the necessary capabilities to access the settings page.
         * If the user has the required capabilities, it processes any form submissions and displays a success message.
         * Then, it renders the settings page using the provided view file.
         *
         * @return void
         */
        public function ehx_donate_settings_page() 
        {
            if (!current_user_can('manage_options')) {
                return;
            }

            $request = new EHX_Donate_Request();
            if ($request->filled('settings-updated')) {
                EHX_Donate_Helper::display_notice(esc_html__('Setting Updated Successfully.', 'ehx-member'));
            }

            // Render the settings page
            require EHX_DONATE_PLUGIN_DIR . 'views/admin/pages/settings.php';
        }

        /**
         * Callback function for the payments page.
         *
         * This function checks if the current user has the necessary capabilities to access the payments page.
         * If the user has the required capabilities, it initializes and displays the payments table.
         *
         * @return void
         */
        public function ehx_donate_admin_donations() 
        {
            if (!current_user_can('manage_options')) {
                return;
            }

            // Initialize and display the payments table
            $this->render_table_page('Payment_Data_Table', 'payments');
        }

        /**
         * Callback function for the members page.
         *
         * This function checks if the current user has the necessary capabilities to access the members page.
         * If the user has the required capabilities, it processes any form submissions related to member deletion and status update.
         * Then, it initializes and displays the members table.
         *
         * @return void
         */
        public function ehx_donate_transactions_page() 
        {
            if (!current_user_can('manage_options')) {
                return;
            }

            if ($this->request->filled('user_deleted')) {
                EHX_Donate_Helper::display_notice(esc_html__('Member Deleted Successfully.', 'ehx-member'));
            }

            if ($this->request->filled('status_updated')) {
                EHX_Donate_Helper::display_notice(esc_html__('Status Updated Successfully.', 'ehx-member'));
            }

            // Initialize and display the members table
            $this->render_table_page('Member_Data_Table', 'members');
        }

        /**
         * Renders the table page for the admin dashboard.
         *
         * This function initializes a custom table class based on the provided table class name,
         * prepares the table items, and includes the corresponding view file to display the table.
         *
         * @param string $table_class The name of the custom table class to be instantiated.
         * @param string $view_name The name of the view file to be included.
         *
         * @return void
         */
        private function render_table_page(string $table_class, string $view_name): void
        {
            $custom_table = new $table_class();
            $custom_table->prepare_items();
            require EHX_DONATE_PLUGIN_DIR . "views/admin/pages/{$view_name}.php";
        }
    }

}