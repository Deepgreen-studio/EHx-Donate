<?php
/**
 * Plugin Name: EHx Donate
 * Plugin URI: https://wordpress.org/plugins/ehx-donate
 * Description: A feature-rich donation management plugin with AJAX forms, multilingual support, and seamless WordPress integration.
 * Version: 1.0.0
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
define('EHXDO_VERSION', '1.0.0');
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
        $this->load_dependencies();

        add_action('plugins_loaded', [$this, 'initPlugin']);
    }

    /**
     * Load required dependencies
     */
    private function load_dependencies(): void
    {
        // Composer autoload
        require_once EHXDO_PLUGIN_DIR . 'vendor/autoload.php';

        // Load recaptcha integration if available
        if (defined('EHXRC_VERSION')) {
            require_once WP_PLUGIN_DIR . '/ehx-recaptcha/includes/autoloader.php';
        }
    }

    /**
     * Initialize plugin components
     */
    public function initPlugin(): void
    {
        // Initialize components
        $this->initComponents();

        // Admin-only components
        if (is_admin()) {
            $this->initAdminComponents();
        }
    }

    /**
     * Initialize core components
     */
    private function initComponents(): void
    {
        new \EHxDonate\Classes\RegisterScripts();
        new \EHxDonate\Classes\Settings();
        new \EHxDonate\PostTypes\CampaignPostType();
        
        // Frontend components
        new \EHxDonate\Shortcodes\DonationFormShortcode();
        new \EHxDonate\Shortcodes\DonationTableShortcode();
        new \EHxDonate\Shortcodes\CampaignListShortcode();
    }

    /**
     * Initialize admin components
     */
    private function initAdminComponents(): void
    {
        new \EHxDonate\Classes\AdminMenuHandler();
        new \EHxDonate\Classes\AdminActionHandler();
    }
}

// Initialize the plugin
EHxDonate::getInstance();

register_activation_hook(__FILE__, [\EHxDonate\Classes\ActivationHandler::class, 'activate']);
register_deactivation_hook(__FILE__, [\EHxDonate\Classes\DeactivationHandler::class, 'deactivate']);
register_uninstall_hook(__FILE__, [\EHxDonate\Classes\UninstallHandler::class, 'handle']);