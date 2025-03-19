<?php

class EHX_Donate 
{
    public static $donation_table;
    public static $donation_items_table;
    public static $transaction_table;
    public static $subscription_table;
    public static $booking_table;

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
        self::$booking_table = $wpdb->prefix . 'ehx_bookings';

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
        // Check if Composer autoloader is already loaded
        if (!class_exists('ComposerAutoloaderInitfb011bd338c520415f6fc4c876f2eedd')) {
            require_once __DIR__ . '/../vendor/autoload.php';
        }

        // Load classes
        $class_files = [
            // Load helper classes
            'helpers/class.request.php',
            'helpers/class.response.php',
            'helpers/class.validator.php',
            'helpers/class.helper.php',

            // Load main classes
            'classes/class.ehx-donate-menu.php',
            'classes/class.ehx-donate-actions.php',
            'classes/class.ehx-donate-scripts.php',
            'classes/class.ehx-donate-settings.php',
            'classes/class.ehx-donate-donation.php',
            'classes/class.ehx-donate-giftaid.php',
            // 'classes/class.ehx-donate-cron-job.php',
            'classes/class.ehx-donate-transaction.php',

            // Load post types classes
            'post-types/class.ehx-donate-campaign.php',

            // Load shortcodes
            'shortcodes/class.donation-form-shortcode.php',
            'shortcodes/class.donation-table-shortcode.php',
            'shortcodes/class.campaign-list-shortcode.php',
        ];

        array_map(fn($file) => require_once EHX_DONATE_PLUGIN_DIR . $file, $class_files);

        // Initialize core components
        new EHX_Donate_Menu();
        new EHX_Donate_Actions();
        new EHX_Donate_Register_Scripts();
        new EHX_Donate_Settings();
        // new EHX_Donate_Cron_Job();

        new EHX_Donate_Campaign();

        new EHX_Donate_Donation_Form_Shortcode();
        new EHX_Donate_Donation_Table_Shortcode();
        new EHX_Donate_Campaign_List_Shortcode();
    }
    
    /**
     * Activates the EHX Donate plugin.
     *
     * This function is called when the plugin is activated. It performs the following tasks:
     * 1. Flushes the rewrite rules to prevent 404 errors.
     * 2. Creates the required tables for the plugin in the WordPress database.
     * 3. Adds custom capabilities to the specified WordPress role.
     * 4. Sets default plugin options (if needed).
     *
     * @return void
     */
    public static function activate() 
    {
        // Flush rewrite rules to prevent 404 errors
        update_option('rewrite_rules', '');

        self::create_table();

        self::ehx_capabilities();

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
     * Uninstalls the EHX Donate plugin.
     *
     * This function is called when the plugin is uninstalled. It performs the following tasks:
     * 1. Destroys the required tables for the plugin in the WordPress database.
     * 2. Removes custom capabilities from the specified WordPress role.
     * 3. Deletes the plugin's options from the database.
     *
     * @return void
     */
    public static function uninstall() 
    {
        self::destroy_table();

        self::ehx_capabilities(type: 'remove');

        // Delete the plugin's options from the database
        delete_option('ehx_donate_settings_options');
    }

    
    /**
     * Loads the plugin's text domain for localization.
     *
     * This function uses the load_plugin_textdomain() function to load the translations for the plugin's text strings.
     * It sets the text domain to 'ehx-donate', specifies that the translations should not be loaded from the main WordPress
     * installation, and specifies the directory where the translations are located.
     *
     * @return void
     */
    public function load_textdomain() 
    {
        load_plugin_textdomain(
            'ehx-donate',
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
            ",
            self::$booking_table => "
                id INT(11) NOT NULL AUTO_INCREMENT,
                user_id BIGINT UNSIGNED DEFAULT NULL,
                event_id BIGINT UNSIGNED DEFAULT NULL,
                ticket_price DECIMAL(8,2) NOT NULL,
                quantity INT(11) NOT NULL,
                discount DECIMAL(8,2) NOT NULL,
                subtotal DECIMAL(8,2) NOT NULL,
                order_note TEXT DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY user_id (user_id),
                KEY event_id (event_id)
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
            $wpdb->query("DROP TABLE IF EXISTS $table");
        }
    }

    /**
     * Adds or removes custom capabilities for a specified WordPress role.
     *
     * This function retrieves the specified WordPress role and adds or removes custom capabilities
     * related to donations, gift aid, and transactions. The custom capabilities are defined in the
     * $capabilities array. The function uses the $type parameter to determine whether to add or
     * remove the capabilities.
     *
     * @param string $role_name The name of the WordPress role to which the capabilities will be added or removed.
     *                          Default is 'administrator'.
     * @param string $type      The type of operation to be performed. It can be either 'add' or 'remove'.
     *                          Default is 'add'.
     *
     * @return void
     */
    public static function ehx_capabilities($role_name = 'administrator', $type = 'add')
    {
        // Retrieve the specified WordPress role
        $role = get_role($role_name);

        // Define the custom capabilities to be added/removed
        $capabilities = ['manage_donations', 'manage_gift_aid', 'manage_transactions'];

        $method = "{$type}_cap";

        // Add/Remove the custom capabilities to the specified role
        array_map(fn($capability) => $role->{$method}($capability), $capabilities);
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
            'admin_email_address' => 'example@eh.studio',
            'mail_appears_from' => 'EHx Studio',
            'mail_appears_from_address' => 'example@eh.studio',
            'enable_gift_aid' => true,
            'stripe_enable' => true,
            'stripe_client_key' => 'pk_test_51R3tRbCo429twQWUFnIVnK8K0tH9Z1enVNk5Pggn3cABcgqctnO01kj60811kPBVLuSERJXphpfSzabb4CUWdrlb00ynOqC7Ot',
            'stripe_client_secret' => 'sk_test_51R3tRbCo429twQWUYCwaeYwTJFPGj2VPaaGDdawemLCojNAvttxquBmhbUGbFNuALznNhw4KdZ11MdatryMjZVSQ00hCKZNEiK',
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
