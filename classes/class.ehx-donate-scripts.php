<?php

/**
 * Class EHX_Donate_Register_Scripts
 *
 * This class is responsible for registering and enqueuing scripts and styles for both the admin and frontend of the WordPress plugin.
 */
class EHX_Donate_Register_Scripts 
{
    /**
     * Constructor for the EHX_Donate_Register_Scripts class.
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
     * This method enqueues admin-specific CSS and JS files. It also localizes the 'ehx-donate-admin-js' script with necessary data.
     */
    public function register_admin_scripts() 
    {
        wp_enqueue_style(
            handle: 'ehx-donate-admin-css',
            src: EHX_DONATE_PLUGIN_URL . 'assets/css/admin.css',
            ver: EHX_DONATE_VERSION
        );

        wp_enqueue_style(
            handle: 'ehx-donate-fonticons-css',
            src: EHX_DONATE_PLUGIN_URL . 'assets/libs/legacy/fonticons/fonticons-fa.css',
            ver: EHX_DONATE_VERSION
        );

        wp_enqueue_style(
            handle: 'ehx-donate-admin-jquery-ui-css',
            src: EHX_DONATE_PLUGIN_URL . 'assets/libs/jquery-ui/jquery-ui.min.css',
            ver: EHX_DONATE_VERSION
        );

        wp_enqueue_script(
            handle: 'ehx-donate-admin-sortable-js',
            src: EHX_DONATE_PLUGIN_URL . 'assets/libs/sortablejs/sortable.min.js',
            deps: ['jquery'],
            ver: EHX_DONATE_VERSION,
            args: false
        );

        // wp_enqueue_script(handle: 'jquery-ui-widget', deps: ['jquery']);
        // wp_enqueue_script(handle: 'jquery-ui-mouse', deps: ['jquery']);
        // wp_enqueue_script(handle: 'jquery-ui-accordion', deps: ['jquery']);
        // wp_enqueue_script(handle: 'jquery-ui-autocomplete', deps: ['jquery']);
        wp_enqueue_script(handle: 'jquery-ui-tooltip', deps: ['jquery']);
        
        wp_enqueue_script(
            handle: 'ehx-donate-admin-helper-js',
            src: EHX_DONATE_PLUGIN_URL . 'assets/js/helper.js',
            deps: ['jquery'],
            ver: EHX_DONATE_VERSION,
            args: true
        );
        wp_enqueue_script(
            handle: 'ehx-donate-admin-js',
            src: EHX_DONATE_PLUGIN_URL . 'assets/js/admin.js',
            deps: ['jquery'],
            ver: EHX_DONATE_VERSION,
            args: true
        );

        wp_localize_script('ehx-donate-admin-js', 'ehx_donate_object', [
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
            handle: 'ehx-donate-main-css',
            src: EHX_DONATE_PLUGIN_URL . 'assets/css/style.css',
            ver: EHX_DONATE_VERSION
        );

        wp_register_style(
            handle: 'ehx-donate-datatable',
            src: EHX_DONATE_PLUGIN_URL . 'assets/libs/datatables/datatable.css',
            ver: EHX_DONATE_VERSION,
        );

        // $google_map_enable = (bool) EHX_Donate_Settings::extract_setting_value('google_map_enable', false);
        // if ($google_map_enable) {
        //     $google_map_api_key = EHX_Donate_Settings::extract_setting_value('google_map_api_key');
        //     wp_register_script(
        //         handle: 'ehx-donate-google-map',
        //         src: EHX_DONATE_PLUGIN_URL . 'assets/js/google-map.js',
        //         deps: ['jquery'],
        //         ver: EHX_DONATE_VERSION,
        //         args: true
        //     );
    
        //     wp_register_script(
        //         handle: 'ehx-donate-google-map-init',
        //         src: "https://maps.googleapis.com/maps/api/js?key={$google_map_api_key}&libraries=places&callback=cities",
        //         deps: ['jquery'],
        //         ver: EHX_DONATE_VERSION,
        //         args: true
        //     );
        // }

        $stripe_enable = (bool) EHX_Donate_Settings::extract_setting_value('stripe_enable', false);
        if ($stripe_enable) {
            wp_register_script(
                handle: 'ehx-donate-stripe',
                src: 'https://js.stripe.com/v3/',
                deps: [],
                ver: EHX_DONATE_VERSION,
                args: true
            );
        }

        wp_register_script(
            handle: 'ehx-donate-datatable',
            src: EHX_DONATE_PLUGIN_URL . 'assets/libs/datatables/datatable.js',
            deps: ['jquery'],
            ver: EHX_DONATE_VERSION,
            args: true
        );

        wp_enqueue_script(
            handle: 'ehx-donate-helper-js',
            src: EHX_DONATE_PLUGIN_URL . 'assets/js/helper.js',
            deps: ['jquery'],
            ver: EHX_DONATE_VERSION,
            args: true
        );
        wp_enqueue_script(
            handle: 'ehx-donate-main-js',
            src: EHX_DONATE_PLUGIN_URL . 'assets/js/main.js',
            deps: ['jquery', 'ehx-donate-datatable'],
            ver: EHX_DONATE_VERSION,
            args: true
        );
        wp_localize_script('ehx-donate-main-js', 'ehx_donate_object', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'currency' => '£',
        ]);
    }
}
