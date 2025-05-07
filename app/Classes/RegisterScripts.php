<?php
declare(strict_types=1);

namespace EHxDonate\Classes;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class RegisterScripts
 *
 * This class is responsible for registering and enqueuing scripts and styles for both the admin and frontend of the WordPress plugin.
 */
class RegisterScripts 
{
    /**
     * Constructor for the RegisterScripts class.
     *
     * Initializes the sets up WordPress hooks.
     *
     * @return void
     */
    public function __construct() 
    {
        // Register admin scripts
        add_action('admin_enqueue_scripts', [$this, 'registerAdminScripts']);

        // Register frontend scripts
        add_action('wp_enqueue_scripts', [$this, 'registerScripts']);

        add_filter('safe_style_css', function ($styles) {
            $styles[] = 'display'; // allow the 'display' property
            return $styles;
        });
        
    }

    /**
     * Register admin scripts and styles.
     *
     * This method enqueues admin-specific CSS and JS files. It also localizes the 'ehxdo-admin-js' script with necessary data.
     */
    public function registerAdminScripts() 
    {
        wp_enqueue_style(
            handle: 'ehxdo-admin-css',
            src: EHXDO_PLUGIN_URL . 'assets/css/admin.css',
            ver: EHXDO_VERSION
        );

        wp_enqueue_script(handle: 'jquery-ui-tooltip', deps: ['jquery']);
        
        wp_enqueue_script(
            handle: 'ehxdo-admin-helper-js',
            src: EHXDO_PLUGIN_URL . 'assets/js/helper.js',
            deps: ['jquery'],
            ver: EHXDO_VERSION,
            args: true
        );
        wp_enqueue_script(
            handle: 'ehxdo-admin-js',
            src: EHXDO_PLUGIN_URL . 'assets/js/admin.js',
            deps: ['jquery'],
            ver: EHXDO_VERSION,
            args: true
        );

        wp_localize_script('ehxdo-admin-js', 'ehxdo_object', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ehx_ajax_nonce'),
        ]);
    }

    /**
     * Register frontend scripts and styles.
     *
     * This method enqueues frontend-specific CSS and JS files. It also registers and enqueues Google Map and Stripe scripts if enabled in the plugin settings.
     */
    public function registerScripts() 
    {
        wp_enqueue_style(
            handle: 'ehxdo-main-css',
            src: EHXDO_PLUGIN_URL . 'assets/css/style.css',
            ver: EHXDO_VERSION
        );

        wp_register_style(
            handle: 'ehxdo-datatable',
            src: EHXDO_PLUGIN_URL . 'assets/libs/datatables/datatable.css',
            ver: EHXDO_VERSION,
        );

        wp_register_script(
            handle: 'ehxdo-datatable',
            src: EHXDO_PLUGIN_URL . 'assets/libs/datatables/datatable.js',
            deps: ['jquery'],
            ver: EHXDO_VERSION,
            args: true
        );

        wp_enqueue_script(
            handle: 'ehxdo-helper-js',
            src: EHXDO_PLUGIN_URL . 'assets/js/helper.js',
            deps: ['jquery'],
            ver: EHXDO_VERSION,
            args: true
        );
        wp_enqueue_script(
            handle: 'ehxdo-main-js',
            src: EHXDO_PLUGIN_URL . 'assets/js/main.js',
            deps: ['jquery', 'ehxdo-datatable'],
            ver: EHXDO_VERSION,
            args: true
        );
        wp_localize_script('ehxdo-main-js', 'ehxdo_object', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'transient' => get_transient(Settings::TRANSIENT),
        ]);
    }
}
