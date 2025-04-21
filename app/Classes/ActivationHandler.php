<?php
declare(strict_types=1);

namespace EHxDonate\Classes;

use EHxDonate\Database\DBMigrator;

if (!defined('ABSPATH')) {
    exit;
}

class ActivationHandler
{    
    /**
     * Plugin activation hook.
     *
     * This function is called when the plugin is activated. It performs the following tasks:
     * 1. Updates the rewrite rules.
     * 2. Creates the payment table if it doesn't exist.
     * 3. Sets default plugin options.
     *
     * @return void
     */
    public static function handle()
    {
        // Check minimum requirements
        self::checkRequirements();

        DBMigrator::run();

        self::setPluginInstallTime();

        self::capabilities();
    }
    
    /**
     * Set plugin install time
     *
     * @return void
     */
    public static function setPluginInstallTime()
    {
        // $data = get_option( 'wp_statuses', []);
        // if( !isset($data['installed_time']) ){
        //     $data['installed_time'] = strtotime("now") ;
        //     update_option('wp_statuses', $data, false);
        // }

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
        update_option(Settings::$option, $options);
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
    public static function capabilities($role_name = 'administrator', $type = 'add')
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
     * Check system requirements
     */
    private static function checkRequirements(): void
    {
        global $wp_version;
        
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            wp_die(
                sprintf(
                    __('EHx Donate requires PHP 7.4 or higher. Your server is running PHP %s.', 'ehx-donate'),
                    PHP_VERSION
                )
            );
        }

        if (version_compare($wp_version, EHXDO_MINIMUM_WP_VERSION, '<')) {
            wp_die(
                sprintf(
                    __('EHx Donate requires WordPress %s or higher. You are running WordPress %s.', 'ehx-donate'),
                    EHXDO_MINIMUM_WP_VERSION,
                    $wp_version
                )
            );
        }
    }
}