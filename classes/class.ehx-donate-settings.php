<?php

if (!class_exists('EHX_Donate_Settings')) {

    class EHX_Donate_Settings 
    {
        public static array $pages;
        public static $options;
        public static array $tabs = [];

        const NONCE_ACTION = 'ehx_donate_settings_form_action';
        const NONCE_NAME = 'ehx_donate_settings_form_nonce';

        /**
         * Constructor for the EHX_Donate_Settings class.
         *
         * Initializes the plugin's settings, defines tabs, retrieves pages, and sets up admin actions.
         *
         * @return void
         */
        public function __construct() 
        {
            // Retrieve options from the database
            self::$options = get_option('ehx_donate_settings_options');

            // Define tabs
            self::$tabs = [
                ['label' => __('General', 'ehx-member'), 'slug' => 'general', 'subtab' => 'pages'],
                ['label' => __('Access', 'ehx-member'), 'slug' => 'access', 'subtab' => 'restriction_content'],
                ['label' => __('Email', 'ehx-member'), 'slug' => 'email', 'subtab' => null],
                ['label' => __('Integration', 'ehx-member'), 'slug' => 'integration', 'subtab' => 'stripe'],
                // ['label' => __('Appearance', 'ehx-member'), 'slug' => 'appearance', 'subtab' => 'profile'],
            ];

            $pages = get_pages();
            foreach ($pages as $page) {
                self::$pages[] = [
                    'key'   => $page->ID,
                    'value' => $page->post_title,
                ];
            }

            add_action('admin_init', [$this, 'admin_init']);

            add_action('wp_ajax_ehx_save_settings', [$this, 'ehx_save_settings']);
        }
        
        
        /**
         * Extracts a specific setting value from the plugin's options.
         *
         * This function retrieves the value of a specific setting from the plugin's options array.
         * If the setting is not found, it returns a default value.
         *
         * @param string $field     The name of the setting to extract.
         * @param mixed  $default   The default value to return if the setting is not found.
         *
         * @return mixed The value of the setting or the default value if not found.
         */
        public static function extract_setting_value($field, $default = '')
        {
            $value = isset(self::$options[$field]) ? self::$options[$field] : $default;
            return $value;
        }
        
        /**
         * Registers the plugin's settings for the WordPress admin area.
         *
         * This function uses the `register_setting` function to register the plugin's settings
         * under the 'ehx_donate_settings_group' group. The settings are stored in the
         * 'ehx_donate_settings_options' option in the WordPress database.
         *
         * @return void
         */
        public function admin_init()
        {
            register_setting('ehx_donate_settings_group', 'ehx_donate_settings_options');
        }

        /**
         * Returns an array of fields for different integration pages.
         *
         * @param string $page The page for which fields are required. Default is 'pages'.
         * @param array $data Additional data required for some fields.
         *
         * @return array An array of fields for the specified page.
         */
        public static function get_integration_fields($page = 'pages', $data = []): array 
        {
            return match($page) {
                'general' => [
                    ['field_name' => 'Default Donation Amounts', 'description' => 'If enabled, the text entered below will replace the title of the post/page/CPT for users who do not have permission to view the restricted content. Please see this doc for more information on this.', 'option' => $page],
                    ['field_name' => 'enable_gift_aid', 'title' => 'Enabled', 'type' => 'checkbox', 'placeholder' => 'Enable Gift Aid', 'option' => $page],
                ],
                'restriction_content' => [
                    ['field_name' => 'paypal_enable', 'title' => 'Enabled', 'type' => 'checkbox', 'placeholder' => 'Enable PayPal as a payment option on the platform.', 'option' => $page],
                    ['field_name' => 'Restricted Access Post Title', 'description' => 'If enabled, the text entered below will replace the title of the post/page/CPT for users who do not have permission to view the restricted content. Please see this doc for more information on this.', 'option' => $page],
                    ['field_name' => 'Restricted Access Message', 'type' => 'textarea', 'option' => $page],
                    ['field_name' => 'Restricted Gutenberg Blocks', 'type' => 'checkbox', 'placeholder' => 'Enable the "Content Restriction" settings for the Gutenberg Blocks', 'option' => $page],
                    ['field_name' => 'Restricted Access Block Message', 'type' => 'textarea', 'option' => $page],
                ],
                'paypal' => [
                    ['field_name' => 'paypal_enable', 'title' => 'Enabled', 'type' => 'checkbox', 'placeholder' => 'Enable PayPal as a payment option on the platform.', 'option' => $page],
                    ['field_name' => 'paypal_client_id', 'title' => 'Client id', 'placeholder' => 'PayPal client id', 'option' => $page],
                    ['field_name' => 'paypal_client_secret', 'title' => 'Client secret', 'placeholder' => 'PayPal client secret', 'option' => $page],
                    // ['field_name' => 'paypal_callback_url', 'title' => 'Callback URL', 'placeholder' => 'PayPal callback URL', 'option' => $page],
                ],
                'stripe' => [
                    ['field_name' => 'stripe_enable', 'title' => 'Enabled', 'type' => 'checkbox', 'placeholder' => 'Enable Stripe as a payment option on the platform.', 'option' => $page],
                    ['field_name' => 'stripe_client_key', 'title' => 'Client key', 'placeholder' => 'Stripe client key', 'option' => $page],
                    ['field_name' => 'stripe_client_secret', 'title' => 'Client secret', 'placeholder' => 'Stripe client secret', 'option' => $page],
                    // ['field_name' => 'stripe_callback_url', 'title' => 'Callback URL', 'placeholder' => 'Stripe callback URL', 'option' => $page],
                ],
                'recaptcha' => [
                    ['field_name' => 'google_recaptcha_enable', 'title' => 'Enabled', 'type' => 'checkbox', 'placeholder' => 'Enable Google reCAPTCHA to protect your forms from spam and abuse.', 'option' => $page],
                    ['field_name' => 'google_recaptcha_site_key', 'title' => 'Site key', 'placeholder' => 'Google Recaptcha Site key', 'option' => $page],
                    ['field_name' => 'google_recaptcha_secret_key', 'title' => 'Secret key', 'placeholder' => 'Google Recaptcha Secret key', 'option' => $page],
                ],
                'map' => [
                    ['field_name' => 'google_map_enable', 'title' => 'Enabled', 'type' => 'checkbox', 'placeholder' => 'Enable Google Maps to display interactive maps on your platform.', 'option' => $page],
                    ['field_name' => 'google_map_api_key', 'title' => 'API key', 'placeholder' => 'Google map API key', 'option' => $page],
                ],
                'email' => [
                    
                ],
                'email-options' => [
                    ['field_name' => 'admin_email_address', 'title' => 'Admin Email Address', 'placeholder' => 'e.g. admin@companyname.com.'],
                    ['field_name' => 'mail_appears_from', 'title' => 'Mail appears from', 'placeholder' => 'e.g. Site Name.'],
                    ['field_name' => 'mail_appears_from_address', 'title' => 'Mail appears from address', 'placeholder' => 'e.g. admin@companyname.com.'],
                ],
                'email-template' => [
                    ['field_name' => 'content_type', 'title' => 'Content type', 'type' => 'checkbox', 'placeholder' => 'Enable HTML for Emails', 'content' => 'If you plan use emails with HTML, please make sure that this option is enabled. Otherwise, HTML will be displayed as plain text.'],
                ],
                default => [
                    // ['field_name' => 'User page', 'is_type' => 'select', 'placeholder' => 'Stripe callback URL', 'option' => $page],
                    // ['field_name' => 'Login page', 'is_type' => 'select', 'placeholder' => 'Stripe callback URL', 'option' => $page],
                    // ['field_name' => 'Register page', 'is_type' => 'select', 'placeholder' => 'Stripe callback URL', 'option' => $page],
                    // ['field_name' => 'donate page', 'is_type' => 'select', 'placeholder' => 'Stripe callback URL', 'option' => $page],
                    // ['field_name' => 'Logout page', 'is_type' => 'select', 'placeholder' => 'Stripe callback URL', 'option' => $page],
                    // ['field_name' => 'Account page', 'is_type' => 'select', 'placeholder' => 'Stripe callback URL', 'option' => $page],
                    // ['field_name' => 'Password Reset page', 'is_type' => 'select', 'placeholder' => 'Stripe callback URL', 'option' => $page],
                    ['field_name' => 'Login Redirect', 'is_type' => 'select', 'placeholder' => 'Stripe callback URL', 'data' => $data, 'option' => $page],
                    ['field_name' => 'Registration Redirect', 'is_type' => 'select', 'placeholder' => 'Stripe callback URL', 'data' => $data, 'option' => $page],
                ],
            };
        }

        /**
         * Retrieves sub tabs based on the provided tab and returns them as HTML or as an array.
         *
         * @param string $tab The main tab to retrieve sub tabs for. Default is 'general'.
         * @param bool $onlyData If true, only returns the sub tabs data as an array. If false, returns the sub tabs as HTML.
         *
         */
        public static function get_sub_tabs($tab = 'general', $onlyData = false) 
        {
            $tabs = match($tab) {
                'general' => [
                    [
                        'label' => 'General',
                        'slug'  => 'general',
                        'description' => "Provides settings for controlling access to your site",
                    ],
                    [
                        'label' => 'Other',
                        'slug'  => 'other',
                        'description' => 'Settings to manage user roles, permissions, and related functionality.',
                    ],
                ],
                'access' => [
                    [
                        'label' => 'Restriction Content',
                        'slug'  => 'restriction_content',
                        'description' => "Provides settings for controlling access to your site",
                    ],
                    [
                        'label' => 'Other',
                        'slug'  => 'other',
                        'description' => 'Settings to manage user roles, permissions, and related functionality.',
                    ],
                ],
                'integration' => [
                    [
                        'label' => 'Stripe',
                        'slug'  => 'stripe',
                        'description' => 'Configuration for Stripe payment gateway integration.',
                    ],
                    // [
                    //     'label' => 'Paypal',
                    //     'slug'  => 'paypal',
                    //     'description' => 'Configuration for PayPal payment gateway integration.',
                    // ],
                    [
                        'label' => 'Google Recaptcha',
                        'slug'  => 'google_recaptcha',
                        'description' => 'Settings for integrating Google reCAPTCHA to prevent spam and abuse.',
                    ],
                    [
                        'label' => 'Google Map',
                        'slug'  => 'google_map',
                        'description' => 'Settings for integrating Google Maps to display interactive maps on your platform.',
                    ], 
                ],
                'appearance' => [
                    [
                        'label' => 'Profile',
                        'slug'  => 'profile',
                        'description' => 'Appearance customization options for user profiles.',
                    ],
                    [
                        'label' => 'Login Form',
                        'slug'  => 'login_form',
                        'description' => 'Appearance and behavior settings for the login form.',
                    ],
                    [
                        'label' => 'Registration Form',
                        'slug'  => 'registration_form',
                        'description' => 'Appearance and behavior settings for the registration form.'
                    ],
                ],
                default => [
                    [
                        'label' => 'Pages',
                        'slug'  => 'pages',
                        'description' => "This section enables you to assign a page to one of the core elements necessary for the plugin's proper function. The plugin automatically creates and configures the required pages upon installation.You only need to use this tab if you accidentally deleted pages that were automatically created during the initial plugin activation. <a href='#'>Learn more about manually creating pages.</a>",
                    ],
                    [
                        'label' => 'Users',
                        'slug'  => 'users',
                        'description' => 'Settings to manage user roles, permissions, and related functionality.',
                    ],
                    [
                        'label' => 'Account',
                        'slug'  => 'account',
                        'description' => 'Settings for user account preferences and customization.',
                    ],
                ],
            };

            if($onlyData) return $tabs;

            foreach ($tabs as $key => $tab): ?>
                <li>
                    <a href="#<?php echo esc_attr($tab['slug']); ?>" class="nav-sub-tab <?php echo $key == 0 ? 'current' : ''; ?>" data-description="<?php echo esc_html($tab['description']); ?>">
                        <?php echo esc_html($tab['label']); ?>
                    </a> |
                </li>
            <?php endforeach;
        }

        /**
         * Retrieves and displays the heading and description for a specific sub tab.
         *
         * This function retrieves the sub tabs data for the given main tab using the `get_sub_tabs` method.
         * It then displays the heading and description for the specified sub tab using the provided key.
         *
         * @param string $tab The main tab to retrieve sub tabs for. Default is 'pages'.
         * @param int $key The index of the sub tab to display. Default is 0.
         *
         * @return void This function outputs HTML directly.
         */
        public static function get_tab_heading_description($tab = 'pages', $key = 0) 
        {
            $sub_tab = self::get_sub_tabs($tab, true);
            ?>
                <h2><?php echo esc_html($sub_tab[$key]['label']); ?></h2>
                <p><?php echo esc_html($sub_tab[$key]['description']); ?></p>
            <?php
        }
        
        /**
         * Processes and saves the plugin's settings.
         *
         * This function handles the submission of the plugin's settings form. It retrieves the submitted data,
         * validates the nonce, and then saves the settings using the `update_option` function.
         *
         * @return void This function does not return any value. It outputs a success response using the EHX_Response class.
         */
        public function ehx_save_settings() 
        {
            $response  = new EHX_Donate_Response();
            $request   = new EHX_Donate_Request();
            $validator = new EHX_Donate_Validator();

            $validator->validate_nonce(self::NONCE_NAME, self::NONCE_ACTION);
            
            // Get the submitted data
            $inputs = $request->input('ehx_donate_settings_options');
        
            // // Save the setting (you can use update_option or your custom logic)
            update_option('ehx_donate_settings_options', $inputs);
        
            // // Return success response
            return $response->success(esc_html__('Settings saved successfully.', 'ehx-member'));
        }        
    }

}