<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class EHXDo_Register_Scripts
 *
 * This class is responsible for registering and enqueuing scripts and styles for both the admin and frontend of the WordPress plugin.
 */
class EHXDo_Register_Scripts 
{
    /**
     * Constructor for the EHXDo_Register_Scripts class.
     *
     * Initializes the sets up WordPress hooks.
     *
     * @return void
     */
    public function __construct() 
    {
        // Register admin scripts
        add_action('admin_enqueue_scripts', [$this, 'register_admin_scripts']);

        // Register frontend scripts
        add_action('wp_enqueue_scripts', [$this, 'register_scripts']);
    }

    /**
     * Register admin scripts and styles.
     *
     * This method enqueues admin-specific CSS and JS files. It also localizes the 'ehxdo-admin-js' script with necessary data.
     */
    public function register_admin_scripts() 
    {
        wp_enqueue_style(
            handle: 'ehxdo-admin-css',
            src: EHXDO_PLUGIN_URL . 'assets/css/admin.css',
            ver: EHXDO_VERSION
        );

        wp_enqueue_style(
            handle: 'ehxdo-fonticons-css',
            src: EHXDO_PLUGIN_URL . 'assets/libs/legacy/fonticons/fonticons-fa.css',
            ver: EHXDO_VERSION
        );

        wp_enqueue_style(
            handle: 'ehxdo-admin-jquery-ui-css',
            src: EHXDO_PLUGIN_URL . 'assets/libs/jquery-ui/jquery-ui.min.css',
            ver: EHXDO_VERSION
        );

        wp_enqueue_script(
            handle: 'ehxdo-admin-sortable-js',
            src: EHXDO_PLUGIN_URL . 'assets/libs/sortablejs/sortable.min.js',
            deps: ['jquery'],
            ver: EHXDO_VERSION,
            args: false
        );

        // wp_enqueue_script(handle: 'jquery-ui-widget', deps: ['jquery']);
        // wp_enqueue_script(handle: 'jquery-ui-mouse', deps: ['jquery']);
        // wp_enqueue_script(handle: 'jquery-ui-accordion', deps: ['jquery']);
        // wp_enqueue_script(handle: 'jquery-ui-autocomplete', deps: ['jquery']);
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
    public function register_scripts() 
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
            'currency' => '£',
        ]);
    }
}
