<?php
    /*
        * Plugin Name: EHx Donate
        * Plugin URI:  https://wordpress.org/plugins/ehx-donate
        * Short Description: EHx Donate – WordPress Donation Plugin. A feature-rich donation management plugin with AJAX forms, custom roles, and seamless integration with WordPress.
        * Description: The **EHx Donate** plugin is designed to enhance donation management on WordPress websites. It offers **seamless integration** with WordPress’s built-in user system, **AJAX-based forms** for a smooth experience, and **custom role assignments** for better user management.
        * 
        * Key Features:
        * - AJAX-Based Submissions – Ensures a smooth, no-refresh experience for users when submitting forms.
        * - Multilingual Support – Fully translatable via the text domain (ehx-donate).
        * - Performance-Optimized – Lightweight and efficient for fast page loading.
        * - Easy Integration – Works with any WordPress theme.
        * 
        * Version:           1.0.0
        * Requires at least: 5.8
        * Requires PHP:      7.4
        * Author:            EH Studio
        * Author URI:        https://eh.studio
        * License:           GPLv3 License
        * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
        * Text Domain:       ehx-donate
        * Domain Path:       /languages
    */

if (!defined('ABSPATH')) {
    die('Hi there! I\'m just a plugin, not much I can do when called directly.');
}

define('EHX_DONATE_VERSION', '1.0.0');
define('EHX_DONATE_MINIMUM_WP_VERSION', '5.8');
define('EHX_DONATE_PLUGIN_DIR', plugin_dir_path( __FILE__));
define('EHX_DONATE_PLUGIN_URL', plugin_dir_url( __FILE__));
define('EHX_DONATE_DELETE_LIMIT', 10000);

require_once EHX_DONATE_PLUGIN_DIR . 'classes/class.ehx-donate.php';

new EHX_Donate();

register_activation_hook(__FILE__, ['EHX_Donate', 'activate']);
register_deactivation_hook(__FILE__, ['EHX_Donate', 'deactivate']);
register_uninstall_hook(__FILE__, ['EHX_Donate', 'uninstall']);