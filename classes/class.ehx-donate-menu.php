<?php

if (!class_exists('EHXDo_Menu')) {

    class EHXDo_Menu 
    {
        private EHXDo_Request $request;

        public static array $pages = [
            'admin'       => 'ehxdo_admin',
            'setting'     => 'ehxdo_admin_settings',
            'donation'    => 'ehxdo_admin_donations',
            'transaction' => 'ehxdo_admin_transactions',
        ];

        /**
         * Constructor for EHXDo_Menu class.
         *
         * Initializes the EHX_Request object and adds the admin menu.
         */
        public function __construct() 
        {
            $this->request = new EHXDo_Request();

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
            remove_submenu_page(self::$pages['admin'], self::$pages['admin']);
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
                    'page_title' => esc_html__('EHx Donate', 'ehx-donate'),
                    'menu_title' => esc_html__('EHx Donate', 'ehx-donate'),
                    'menu_slug'  => self::$pages['admin'],
                    'callback'   => [$this, 'EHXDo_Settings_page'],
                    'icon_url'   => 'dashicons-admin-users',
                    'submenus'  => [
                        [
                            'page_title' => esc_html__('Settings', 'ehx-donate'),
                            'menu_title' => esc_html__('Settings', 'ehx-donate'),
                            'menu_slug'  => self::$pages['setting'],
                            'callback'   => [$this, 'EHXDo_Settings_page'],
                        ],
                        [
                            'page_title' => esc_html__('Donations', 'ehx-donate'),
                            'menu_title' => esc_html__('Donations', 'ehx-donate'),
                            'menu_slug'  => self::$pages['donation'],
                            'callback'   => [$this, 'ehxdo_donations_page'],
                        ],
                        [
                            'page_title' => esc_html__('Campaigns', 'ehx-donate'),
                            'menu_title' => esc_html__('Campaigns', 'ehx-donate'),
                            'menu_slug'  => 'edit.php?post_type=ehxdo-campaign',
                            'callback'   => null,
                        ],
                        [
                            'page_title' => esc_html__('Transactions', 'ehx-donate'),
                            'menu_title' => esc_html__('Transactions', 'ehx-donate'),
                            'menu_slug'  => self::$pages['transaction'],
                            'callback'   => [$this, 'ehxdo_transactions_page'],
                        ]
                    ]
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
        public function EHXDo_Settings_page() 
        {
            if (!current_user_can('manage_options')) {
                return;
            }

            $request = new EHXDo_Request();
            if ($request->filled('settings-updated')) {
                EHXDo_Helper::display_notice(esc_html__('Setting Updated Successfully.', 'ehx-donate'));
            }

            // Render the settings page
            require EHXDO_PLUGIN_DIR . 'views/admin/pages/settings.php';
        }

        /**
         * Callback function for the donations page.
         *
         * This function checks if the current user has the necessary capabilities to access the donations page.
         * If the user has the required capabilities, it initializes and displays the donations table.
         *
         * @return void
         */
        public function ehxdo_donations_page() 
        {
            if (!current_user_can('manage_donations')) {
                return;
            }

            if ($this->request->filled('deleted')) {
                EHXDo_Helper::display_notice(esc_html__('Donation Deleted Successfully.', 'ehx-donate'));
            }

            // Initialize and display the payments table
            $this->render_table_page('EHXDo_Donation_Data_Table', 'donations');
        }

        /**
         * Callback function for the donations page.
         *
         * This function checks if the current user has the necessary capabilities to access the donations page.
         * If the user has the required capabilities, it initializes and displays the donations table.
         *
         * @return void
         */
        public function ehxdo_gift_aid_page() 
        {
            if (!current_user_can('manage_gift_aid')) {
                return;
            }

            if ($this->request->filled('deleted')) {
                EHXDo_Helper::display_notice(esc_html__('Gift Aid Deleted Successfully.', 'ehx-donate'));
            }

            // Initialize and display the payments table
            $this->render_table_page('ehxdo_GiftAid_Data_Table', 'giftaid');
        }

        /**
         * Callback function for the donates page.
         *
         * This function checks if the current user has the necessary capabilities to access the donates page.
         * If the user has the required capabilities, it processes any form submissions related to donate deletion and status update.
         * Then, it initializes and displays the donates table.
         *
         * @return void
         */
        public function ehxdo_transactions_page() 
        {
            if (!current_user_can('manage_transactions')) {
                return;
            }

            if ($this->request->filled('deleted')) {
                EHXDo_Helper::display_notice(esc_html__('Transaction Deleted Successfully.', 'ehx-donate'));
            }

            // Initialize and display the donates table
            $this->render_table_page('EHXDo_Transaction_Data_Table', 'transactions');
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
            require EHXDO_PLUGIN_DIR . "views/admin/pages/{$view_name}.php";
        }
    }

}