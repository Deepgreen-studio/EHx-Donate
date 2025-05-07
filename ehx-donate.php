<?php
/**
 * Plugin Name: EHx Donate
 * Plugin URI: https://wordpress.org/plugins/ehx-donate
 * Description: A feature-rich donation management plugin with AJAX forms, multilingual support, and seamless WordPress integration.
 * Version: 1.1.2
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Author: EH Studio
 * Author URI: https://eh.studio
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ehx-donate
 * Domain Path: /languages
 */


if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define plugin constants
define('EHXDO_VERSION', '1.1.2');
define('EHXDO_MINIMUM_WP_VERSION', '5.8');
define('EHXDO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('EHXDO_PLUGIN_URL', plugin_dir_url(__FILE__));
define('EHXDO_DELETE_LIMIT', 10000);
define('EHXDO_TABLE_PREFIX', 'ehxdo_');

class EHxDonate 
{
    /**
     * Plugin instance
     * 
     * @var EHxDonate
     */
    private static $instance;

    /**
     * Get plugin instance
     * 
     * @return EHxDonate
     */
    public static function getInstance(): EHxDonate
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize the plugin
     */
    private function __construct()
    {
        $this->registerAutoload();

        add_action('plugins_loaded', [$this, 'initPlugin']);
    }

    /**
     * Initialize plugin components
     */
    public function initPlugin(): void
    {
        // Initialize core components
        new \EHxDonate\Classes\Settings();
        new \EHxDonate\Classes\RegisterScripts();
        new \EHxDonate\PostTypes\CampaignPostType();
        new \EHxDonate\Classes\RegisterElementorWidget();
        
        // Frontend components
        new \EHxDonate\Shortcodes\DonationFormShortcode();
        new \EHxDonate\Shortcodes\DonationTableShortcode();
        new \EHxDonate\Shortcodes\CampaignListShortcode();
        new \EHxDonate\Addons\ManageAddons();

        // Initialize admin components
        if (is_admin()) {
            new \EHxDonate\Classes\AdminMenuHandler();
            new \EHxDonate\Classes\AdminActionHandler();
            new \EHxDonate\Addons\ManageAddons();
            new \EHxDonate\Classes\ActivationHandler();
            new \EHxDonate\Classes\UpdatePluginHandler();
        }
    }
    
    /**
     * Register Autoload
     *
     * @return void
     */
    protected function registerAutoload()
    {
        // Load required dependencies
        require_once EHXDO_PLUGIN_DIR . 'vendor/autoload.php';
        
        // Load recurring donation integration if available
        if (defined('EHXRD_VERSION')) {
            require_once WP_PLUGIN_DIR . '/ehx-recurring-donation/autoloader.php';
        }
    }
}

// Initialize the plugin
EHxDonate::getInstance();

register_activation_hook(__FILE__, [\EHxDonate\Classes\ActivationHandler::class, 'handle']);
register_deactivation_hook(__FILE__, [\EHxDonate\Classes\DeactivationHandler::class, 'handle']);
register_uninstall_hook(__FILE__, [\EHxDonate\Classes\UninstallHandler::class, 'handle']);