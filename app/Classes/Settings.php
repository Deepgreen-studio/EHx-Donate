<?php
declare(strict_types=1);

namespace EHxDonate\Classes;

use EHxDonate\Helpers\Helper;
use EHxDonate\Models\Currency;
use EHxDonate\Services\Request;
use EHxDonate\Services\Response;
use EHxDonate\Services\Validator;

if (!defined('ABSPATH')) {
    exit;
}

// Load recaptcha integration if available
if (defined('EHXRC_VERSION')) {
    require_once WP_PLUGIN_DIR . '/ehx-recaptcha/autoloader.php';
}

class Settings 
{
    const TRANSIENT = 'ehxdo_session';
    const TOKEN_EXPIRY = 3600; // 30 min in seconds

    public static string $option = 'ehxdo_settings';
    public static array $pages;
    public static $options;
    public static array $tabs = [];

    const NONCE_ACTION = 'ehxdo_save_settings';

    /**
     * Constructor for the Settings class.
     *
     * Initializes the plugin's settings, defines tabs, retrieves pages, and sets up admin actions.
     *
     * @return void
     */
    public function __construct() 
    {
        // Retrieve options from the database
        self::$options = get_option(self::$option);

        // Define tabs
        self::$tabs = [
            ['label' => __('General', 'ehx-donate'), 'slug' => 'general', 'subtab' => 'pages'],
            ['label' => __('Email', 'ehx-donate'), 'slug' => 'email', 'subtab' => null],
            ['label' => __('Integration', 'ehx-donate'), 'slug' => 'integration', 'subtab' => 'stripe']
        ];

        $pages = get_pages();
        foreach ($pages as $page) {
            self::$pages[] = [
                'key'   => $page->ID,
                'value' => $page->post_title,
            ];
        }
        
        add_action('init', [$this, 'setupSession']);

        add_action('wp_ajax_ehxdo_save_settings', [$this, 'saveSetting']);
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
    public static function extractSettingValue($field, $default = '')
    {
        $value = isset(self::$options[$field]) ? self::$options[$field] : $default;
        return $value;
    }

    /**
     * Returns an array of fields for different integration pages.
     *
     * @param string $page The page for which fields are required. Default is 'pages'.
     * @param array $data Additional data required for some fields.
     *
     * @return array An array of fields for the specified page.
     */
    public static function getIntegrationFields($page = 'pages', $data = []): array 
    {
        return match($page) {
            'general' => [
                ['field_name' => 'default_donation_amounts', 'title' => esc_html__('Default Donation Amounts', 'ehx-donate'), 'option' => $page],
                ['field_name' => 'currency', 'title' => esc_html__('Currency', 'ehx-donate'), 'is_type' => 'select', 'option' => $page, 'data' => $data],
                ['field_name' => 'currency_position', 'title' => esc_html__('Currency Position', 'ehx-donate'), 'is_type' => 'select', 'option' => $page, 'data' => [['key' => 'before', 'value' => esc_html__('Before', 'ehx-donate')], ['key' => 'after', 'value' => esc_html__('After', 'ehx-donate')]]],
            ],
            'restriction_content' => [
                ['field_name' => 'paypal_enable', 'title' => esc_html__('Paypal Enable', 'ehx-donate'), 'type' => 'checkbox', 'placeholder' => 'Enable PayPal as a payment option on the platform.', 'option' => $page],
                ['field_name' => 'restricted_access_post_title', 'title' => esc_html__('Restricted Access Post Title', 'ehx-donate'), 'description' => esc_html__('If enabled, the text entered below will replace the title of the post/page/CPT for users who do not have permission to view the restricted content. Please see this doc for more information on this.', 'ehx-donate'), 'option' => $page],
                ['field_name' => 'restricted_access_message', 'title' => esc_html__('Restricted Access Message', 'ehx-donate'), 'type' => 'textarea', 'option' => $page],
                ['field_name' => 'restricted_gutenberg_blocks', 'title' => esc_html__('Restricted Gutenberg Blocks', 'ehx-donate'), 'type' => 'checkbox', 'placeholder' => esc_html__('Enable the "Content Restriction" settings for the Gutenberg Blocks', 'ehx-donate'), 'option' => $page],
                ['field_name' => 'restricted_access_block_message', 'title' => esc_html__('Restricted Access Block Message', 'ehx-donate'), 'type' => 'textarea', 'option' => $page],
            ],
            'paypal' => [
                ['field_name' => 'paypal_enable', 'title' => esc_html__('Enabled', 'ehx-donate'), 'type' => 'checkbox', 'placeholder' => esc_html__('Enable PayPal as a payment option on the platform.', 'ehx-donate'), 'option' => $page],
                ['field_name' => 'paypal_client_id', 'title' => 'Client id', 'placeholder' => esc_html__('PayPal client id', 'ehx-donate'), 'option' => $page],
                ['field_name' => 'paypal_client_secret', 'title' => 'Client secret', 'placeholder' => esc_html__('PayPal client secret', 'ehx-donate'), 'option' => $page],
            ],
            'stripe' => [
                ['field_name' => 'stripe_test_mode_enable', 'title' => esc_html__('Enabled', 'ehx-donate'), 'type' => 'switch', 'option' => $page],
                ['field_name' => 'stripe_enable', 'title' => esc_html__('Enabled', 'ehx-donate'), 'type' => 'checkbox', 'placeholder' => esc_html__('Enable Stripe as a payment option on the platform.', 'ehx-donate'), 'option' => $page],
                ['field_name' => 'stripe_client_key', 'title' => esc_html__('Client key', 'ehx-donate'), 'placeholder' => esc_html__('Stripe client key', 'ehx-donate'), 'option' => $page, 'depend_field' => 'stripe_test_mode_enable', 'depend_value' => 1],
                ['field_name' => 'stripe_client_secret', 'title' => esc_html__('Client secret', 'ehx-donate'), 'placeholder' => esc_html__('Stripe client secret', 'ehx-donate'), 'option' => $page, 'depend_field' => 'stripe_test_mode_enable', 'depend_value' => 1],
            ],
            'map' => [
                ['field_name' => 'google_map_enable', 'title' => esc_html__('Enabled', 'ehx-donate'), 'type' => 'checkbox', 'placeholder' => esc_html__('Enable Google Maps to display interactive maps on your platform.', 'ehx-donate'), 'option' => $page],
                ['field_name' => 'google_map_api_key', 'title' => 'API key', 'placeholder' => 'Google map API key', 'option' => $page],
            ],
            'email' => [
                
            ],
            'email-options' => [
                ['field_name' => 'admin_email_address', 'title' => esc_html__('Admin Email Address', 'ehx-donate'), 'placeholder' => esc_html__('e.g. admin@companyname.com.', 'ehx-donate')],
                ['field_name' => 'mail_appears_from', 'title' => esc_html__('Mail appears from', 'ehx-donate'), 'placeholder' => esc_html__('e.g. Site Name.', 'ehx-donate')],
                ['field_name' => 'mail_appears_from_address', 'title' => esc_html__('Mail appears from address', 'ehx-donate'), 'placeholder' => esc_html__('e.g. admin@companyname.com.', 'ehx-donate')],
            ],
            'email-template' => [
                ['field_name' => 'content_type', 'title' => esc_html__('Content type', 'ehx-donate'), 'type' => 'checkbox', 'placeholder' => esc_html__('Enable HTML for Emails', 'ehx-donate'), 'content' => esc_html__('If you plan use emails with HTML, please make sure that this option is enabled. Otherwise, HTML will be displayed as plain text.', 'ehx-donate')],
            ],
            'gift-aid' => [
                ['field_name' => 'enable_gift_aid', 'title' => esc_html__('Enable Gift Aid', 'ehx-donate'), 'type' => 'checkbox', 'placeholder' => esc_html__('Allow Gift Aid Donation', 'ehx-donate')],
            ],
            default => [
                ['field_name' => 'login_redirect', 'title' => esc_html__('Login Redirect', 'ehx-donate'), 'is_type' => 'select', 'placeholder' => esc_html__('Stripe callback URL', 'ehx-donate'), 'data' => $data, 'option' => $page],
                ['field_name' => 'registration_redirect', 'title' => esc_html__('Registration Redirect', 'ehx-donate'), 'is_type' => 'select', 'placeholder' => esc_html__('Stripe callback URL', 'ehx-donate'), 'data' => $data, 'option' => $page],
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
    public static function getSubTabs($tab = 'general', $onlyData = false) 
    {
        $tabs = match($tab) {
            'integration' => [
                [
                    'label' => esc_html__('Stripe', 'ehx-donate'),
                    'slug'  => 'stripe',
                    'description' => esc_html__('Configuration for Stripe payment gateway integration.', 'ehx-donate'),
                ],
            ],
            default => [
                [
                    'label' => esc_html__('General', 'ehx-donate'),
                    'slug'  => 'general',
                    'description' => esc_html__("Provides settings for controlling access to your site", 'ehx-donate'),
                ],
                [
                    'label' => esc_html__('Other', 'ehx-donate'),
                    'slug'  => 'other',
                    'description' => esc_html__('Settings to manage user roles, permissions, and related functionality.', 'ehx-donate'),
                ],
            ],
        };

        if($tab = 'integration' && defined('EHXRC_VERSION')) {
            $tabs = [
                ...$tabs,
                \EHxRecaptcha\Classes\HandleSetting::getSubTabData()
            ];
        }

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
     * This function retrieves the sub tabs data for the given main tab using the `getSubTabs` method.
     * It then displays the heading and description for the specified sub tab using the provided key.
     *
     * @param string $tab The main tab to retrieve sub tabs for. Default is 'pages'.
     * @param int $key The index of the sub tab to display. Default is 0.
     *
     * @return void This function outputs HTML directly.
     */
    public static function getTabHeadingDescription($tab = 'pages', $key = 0) 
    {
        $sub_tab = self::getSubTabs($tab, true);
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
     * @return void This function does not return any value. It outputs a success response using the Response class.
     */
    public function saveSetting() 
    {
        $response  = new Response();
        $request   = new Request();
        $validator = new Validator();

        $validator->validate_nonce(Helper::NONCE_NAME, self::NONCE_ACTION);
        
        // Get the submitted data
        $inputs = $request->input(self::$option);

        if(isset($inputs['stripe_test_mode_enable'])) {
            $inputs['stripe_client_key'] = ActivationHandler::STRIPE_CLIENT_KEY;
            $inputs['stripe_client_secret'] = ActivationHandler::STRIPE_SECRET_KEY;
        }
    
        // Save the setting (you can use update_option or your custom logic)
        update_option(self::$option, $inputs);
    
        // // Return success response
        return $response->success(esc_html__('Settings saved successfully.', 'ehx-donate'));
    }
        
    /**
     * Setup Session Data
     *
     * @return void
     */
    public function setupSession()
    {
        $transient = get_transient(self::TRANSIENT);
        
        if($transient === false) {
            $currencyId = Settings::extractSettingValue('currency');
            $currencyId = in_array(gettype($currencyId), ['array', 'object']) ? 2 : $currencyId;
            $currency = (new Currency())->where('id', $currencyId)->first();
            $symbol_position = Settings::extractSettingValue('symbol_position', 'before');

            set_transient(
                self::TRANSIENT, 
                (object) [
                    'currency' => $currency,
                    'symbol_position' => $symbol_position,
                ], 
                self::TOKEN_EXPIRY
            );
        }
    }
}