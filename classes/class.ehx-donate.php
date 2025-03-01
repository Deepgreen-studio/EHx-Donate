<?php

class EHX_Donate 
{
    public static $donation_table;
    public static $donation_items_table;
    public static $transaction_table;
    public static $subscription_table;

    /**
     * Constructor for the EHX_Donate class.
     *
     * Initializes the donation table, loads necessary dependencies, and sets up WordPress hooks.
     *
     * @return void
     */
    public function __construct() 
    {
        global $wpdb;

        // Define the table names for donations, donation items, transactions, and subscriptions.
        self::$donation_table = $wpdb->prefix . 'ehx_donations';
        self::$donation_items_table = $wpdb->prefix . 'ehx_donation_items';
        self::$transaction_table = $wpdb->prefix . 'ehx_transactions';
        self::$subscription_table = $wpdb->prefix . 'ehx_subscriptions';

        add_action('plugins_loaded', [$this, 'load_textdomain']);

        $this->include_dependencies();

        add_action('init', fn() => EHX_Donate_Helper::session(), 1); // Priority 1 ensures it runs early
    }

    /**
     * Includes necessary dependencies for the plugin.
     *
     * This function loads Composer dependencies, helper classes, main classes, and shortcodes.
     * It also initializes core components of the plugin.
     *
     * @return void
     */
    private function include_dependencies() 
    {
        // Autoload Composer dependencies
        // require_once __DIR__ . '/../vendor/autoload.php';

        // Load helper classes
        $helper_files = [
            'class.request.php',
            'class.response.php',
            'class.validator.php',
            'class.helper.php',
        ];
        array_map(fn($file) => require_once EHX_DONATE_PLUGIN_DIR . "helpers/{$file}", $helper_files);

        // Load main classes
        $class_files = [
            'class.ehx-donate-menu.php',
            'class.ehx-donate-actions.php',
            'class.ehx-donate-scripts.php',
            'class.ehx-donate-settings.php',
            'class.ehx-donate-donation.php',
            'class.ehx-donate-campaign.php',
            'class.ehx-donate-cron-job.php',
        ];
        array_map(fn($file) => require_once EHX_DONATE_PLUGIN_DIR . "classes/{$file}", $class_files);

        // Load shortcodes
        require_once EHX_DONATE_PLUGIN_DIR . 'shortcodes/class.campaign-shortcode.php';

        // Initialize core components
        new EHX_Donate_Menu();
        new EHX_Donate_Actions();
        new EHX_Donate_Register_Scripts();
        new EHX_Donate_Settings();
        new EHX_Donate_Campaign();
        new EHX_Donate_Cron_Job();

        new EHX_Donate_Campaign_Shortcode();
    }
    
    /**
     * Activates the EHX_Donate plugin.
     *
     * This function is called when the plugin is activated. It performs the following tasks:
     * 1. Flushes the rewrite rules to prevent 404 errors.
     * 2. Calls the self::create_table() method to create the necessary tables for the plugin.
     * 3. Calls the self::set_default_options() method to set default plugin options.
     *
     * @return void
     */
    public static function activate() 
    {
        // Flush rewrite rules to prevent 404 errors
        update_option('rewrite_rules', '');

        self::create_table();

        // Set default plugin options (if needed)
        self::set_default_options();
    }

    /**
     * Plugin deactivation hook.
     *
     * This function is called when the plugin is deactivated. It performs the following tasks:
     * 1. Flushes the rewrite rules to ensure they are updated when the plugin is deactivated.
     *
     * @return void
     */
    public static function deactivate() 
    {
        flush_rewrite_rules();
    }

    /**
     * Uninstalls the plugin and performs necessary cleanup tasks.
     *
     * This function drops the ehx donate plugin required tables from the database and deletes the plugin's options.
     *
     * @return void
     */
    public static function uninstall() 
    {
        self::destroy_table();

        // Delete the plugin's options from the database
        delete_option('ehx_DONATEs_settings_options');
    }
    
    /**
     * Loads the plugin's text domain for localization.
     *
     * This function uses the load_plugin_textdomain() function to load the translations for the plugin's text strings.
     * It sets the text domain to 'ehx-member', specifies that the translations should not be loaded from the main WordPress
     * installation, and specifies the directory where the translations are located.
     *
     * @return void
     */
    public function load_textdomain() 
    {
        load_plugin_textdomain(
            'ehx-member',
            false,
            dirname(plugin_basename(__FILE__)) . '/../languages'
        );
    }

    /**
     * Create the payment table if it doesn't exist.
     *
     * This function checks if the payment table exists in the WordPress database. If it doesn't,
     * it creates the table using the provided SQL query. The table structure includes fields for
     * payment ID, user ID, amount, charge, payment method, status, and creation timestamp.
     *
     * @global wpdb $wpdb WordPress database object.
     *
     * @return void
     */
    public static function create_table() 
    {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // Define the tables
        $tables = [
            self::$donation_table => "
                id INT(11) NOT NULL AUTO_INCREMENT,
                user_id BIGINT UNSIGNED NOT NULL,
                invoice VARCHAR(30) DEFAULT NULL,
                admin_fee DECIMAL(8,2) DEFAULT NULL,
                processing_fee_percentage DECIMAL(8,2) NOT NULL,
                processing_fee DECIMAL(8,2) NOT NULL,
                gift_aid TINYINT(1) DEFAULT 0,
                amount DECIMAL(8,2) NOT NULL,
                total_amount DECIMAL(8,2) NOT NULL,
                charge DECIMAL(8,2) DEFAULT 0,
                payment_method ENUM('Stripe','Paypal','Google Pay','Samsung Pay','Apple Pay','Skrill','Checkout','Blockchain','BTCPay') DEFAULT NULL,
                payment_status ENUM('Pending','Success','Cancel') DEFAULT 'Pending',
                browser_session VARCHAR(50) DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY user_id (user_id)
            ",
            self::$donation_items_table => "
                id INT(11) NOT NULL AUTO_INCREMENT,
                donation_id BIGINT UNSIGNED DEFAULT NULL,
                campaign_id BIGINT UNSIGNED DEFAULT NULL,
                subscription_id BIGINT UNSIGNED DEFAULT NULL,
                is_zakat TINYINT(1) DEFAULT 0,
                amount DECIMAL(8,2) DEFAULT NULL,
                gift_aid TINYINT(1) DEFAULT 0,
                recurring ENUM('One-off','Weekly','Monthly','Quarterly','Yearly') DEFAULT 'One-off',
                status TINYINT(1) DEFAULT 0,
                type VARCHAR(50) DEFAULT NULL,
                `option` VARCHAR(50) DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY donation_id (donation_id),
                KEY campaign_id (campaign_id),
                KEY subscription_id (subscription_id)
            ",
            self::$transaction_table => "
                id INT(11) NOT NULL AUTO_INCREMENT,
                donation_id BIGINT UNSIGNED DEFAULT NULL,
                amount DECIMAL(8,2) NOT NULL,
                balance DECIMAL(8,2) NOT NULL,
                note TEXT DEFAULT NULL,
                date DATE DEFAULT NULL,
                status ENUM('Paid','Unpaid') DEFAULT 'Paid',
                type ENUM('Credit','Debit') DEFAULT 'Credit',
                is_match TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY donation_id (donation_id)
            ",
            self::$subscription_table => "
                id INT(11) NOT NULL AUTO_INCREMENT,
                user_id BIGINT UNSIGNED DEFAULT NULL,
                title VARCHAR(255) NOT NULL,
                stripe_subscription_id VARCHAR(255) DEFAULT NULL,
                stripe_subscription_price_id VARCHAR(255) DEFAULT NULL,
                amount DECIMAL(8,2) NOT NULL,
                recurring ENUM('One-off','Weekly','Monthly','Quarterly','Yearly') DEFAULT NULL,
                next_payment_date DATE DEFAULT NULL,
                invoice_no VARCHAR(255) NOT NULL,
                status VARCHAR(255) DEFAULT NULL,
                payment_method ENUM('Stripe','Paypal','Google Pay','Samsung Pay','Apple Pay','Skrill','Checkout','Blockchain','BTCPay') DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY user_id (user_id)
            "
        ];

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // Create or update tables using dbDelta
        foreach ($tables as $table_name => $table_schema) {
            $query = $wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($table_name));

            if ($wpdb->get_var($query) !== $table_name) {
                $sql = "CREATE TABLE $table_name ($table_schema) $charset_collate;";
                dbDelta($sql);
            }
        }
    }

    /**
     * Destroys the ehx donate plugin required tables.
     *
     * This function drops the ehx donate plugin required tables from the WordPress database.
     * If the tables do not exist, no action is taken.
     *
     * @return void
     */
    public static function destroy_table()
    {   
        global $wpdb;

        // Prepare and execute a SQL query to drop the ehx donate plugin required tables if it exists
        $tables = [
            self::$donation_table,
            self::$donation_items_table,
            self::$transaction_table,
            self::$subscription_table,
        ];

        foreach ($tables as $table) {
            $query = $wpdb->prepare("DROP TABLE IF EXISTS %s", $table);
            $wpdb->query($query);
        }
    }

    /**
     * Sets default options for the plugin.
     *
     * This function adds custom roles, sets default payment gateway settings, and updates the plugin's options.
     *
     * @return void
     */
    public static function set_default_options() 
    {
        // Define default payment gateway settings
        $options = [
            'stripe_enable' => true,
            'stripe_client_key' => 'pk_test_51MiBHEKVOOxRoCcVTL0ZVEuPjbMxPARb7MmEPF37YwZyxnn5vvghF6f9Z6kCBuFzYWQqP8RXYXEQjpuzBdf4khDW004kzM6OS6',
            'stripe_client_secret' => 'sk_test_51MiBHEKVOOxRoCcVds9KeZ2B8YgkPm9XldAbegtt7OClqd9XMM5CChYSvI5g41fxQkURsUoLHcpVzSccQ99iOOi000rszEAiU5',
            'stripe_callback_url' => null,
            'google_recaptcha_enable' => true,
            'google_recaptcha_site_key' => '6LePvtQqAAAAABdeaktZv79QZCLUCqxVn5Wt64w8',
            'google_recaptcha_secret_key' => '6LePvtQqAAAAAFp7GxqThMl2ESIyRVJdshS-_YNy',
            'google_map_enable' => false,
            'google_map_api_key' => 'AIzaSyBiN747TEpdfi0TeFN47PvEjw9LrrY9d8w',
        ];

        // Update the plugin's options with the default settings
        update_option('ehx_donate_settings_options', $options);
    }
}
