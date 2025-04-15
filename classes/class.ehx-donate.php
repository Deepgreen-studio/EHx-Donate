<?php

if (!defined('ABSPATH')) {
    exit;
}

class EHXDo_Donate 
{
    public static $donation_table;
    public static $donation_items_table;
    public static $transaction_table;
    public static $booking_table;

    /**
     * Constructor for the EHXDo_Donate class.
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
        self::$booking_table = $wpdb->prefix . 'ehx_bookings';

        $this->include_dependencies();
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
            'classes/class.ehx-donate-transaction.php',

            // Load post types classes
            'post-types/class.ehx-donate-campaign.php',

            // Load shortcodes
            'shortcodes/class.donation-form-shortcode.php',
            'shortcodes/class.donation-table-shortcode.php',
            'shortcodes/class.campaign-list-shortcode.php',
        ];

        array_map(fn($file) => require_once EHXDO_PLUGIN_DIR . $file, $class_files);

        // Initialize core components
        new EHXDo_Menu();
        new EHXDo_Actions();
        new EHXDo_Register_Scripts();
        new EHXDo_Settings();

        new EHXDo_Campaign();

        new EHXDo_Donation_Form_Shortcode();
        new EHXDo_Donation_Table_Shortcode();
        new EHXDo_Campaign_List_Shortcode();
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
        delete_option(EHXDo_Settings::$option);
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
            if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($table_name))) !== $table_name) {
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
        ];

        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS " . esc_sql($table));
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
        $capabilities = ['manage_donations', 'manage_transactions'];

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
            'stripe_test_mode_enable' => true,
            'stripe_enable' => true,
            'stripe_client_key' => 'pk_test_51R3tRbCo429twQWUFnIVnK8K0tH9Z1enVNk5Pggn3cABcgqctnO01kj60811kPBVLuSERJXphpfSzabb4CUWdrlb00ynOqC7Ot',
            'stripe_client_secret' => 'sk_test_51R3tRbCo429twQWUYCwaeYwTJFPGj2VPaaGDdawemLCojNAvttxquBmhbUGbFNuALznNhw4KdZ11MdatryMjZVSQ00hCKZNEiK',
        ];

        // Update the plugin's options with the default settings
        update_option(EHXDo_Settings::$option, $options);
    }
}
