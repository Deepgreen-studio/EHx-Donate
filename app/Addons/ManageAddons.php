<?php
declare(strict_types=1);

namespace EHxDonate\Addons;

use Automatic_Upgrader_Skin;
use EHxDonate\Services\Request;
use EHxDonate\Services\Response;
use Plugin_Upgrader;

if (!defined('ABSPATH')) {
    exit;
}

class ManageAddons
{    
    private Response $response;

    public function __construct()
    {
        $this->response = new Response();

        add_action('wp_ajax_ehxdo_install_addon', [$this, 'installAddon']);
        add_action('wp_ajax_ehxdo_activate_addon', [$this, 'activateAddon']);
        add_action('wp_ajax_ehxdo_deactivate_addon', [$this, 'deactivateAddon']);
    }
    
    /**
     * Install Addon
     *
     * @param  mixed $addon_url
     */
    public function installAddon() 
    {
        $request = new Request();

        $upgrader = new Plugin_Upgrader(new Automatic_Upgrader_Skin());

        $addon_url = $request->input('addon_url');
        
        $result = $upgrader->install($addon_url);
    
        if (is_wp_error($result)) {
            return $this->response->error(esc_html($result->get_error_message()));
        }
        
        return $this->response->success(esc_html__('Add-on installed successfully!', 'ehx-donate'));
    } 
    
    /**
     * Activate Addon
     *
     * @return void
     */
    public function activateAddon() 
    {
        $request = new Request();
        
        if (!current_user_can('activate_plugins')) {
            return $this->response->error(esc_html__('You do not have sufficient permissions.', 'ehx-donate'));
        }

        $plugin_file = $request->input('plugin_file');

        $result = activate_plugin($plugin_file);

        if (is_wp_error($result)) {
            return $this->response->error(esc_html($result->get_error_message()));
        }

        return $this->response->success(esc_html__('Add-on activated successfully!', 'ehx-donate'));
    }

    /**
     * Deactivate Addon
     *
     * @return void
     */
    public function deactivateAddon() 
    {
        $request = new Request();
        
        if (!current_user_can('activate_plugins')) {
            return $this->response->error(esc_html__('You do not have sufficient permissions.', 'ehx-donate'));
        }

        $plugin_file = $request->input('plugin_file');

        deactivate_plugins($plugin_file, true);

        return $this->response->success(esc_html__('Add-on deactivated successfully!', 'ehx-donate'));
    }

    /**
     * Get Available Addons
     *
     * @return array
     */
    public static function getAvailableAddons(): array 
    {
        // Retrieve from your API or local cache
        return [
            'ehx-recaptcha/ehx-recaptcha.php' => [
                'name' => esc_html__('reCaptcha', 'ehx-donate'),
                'version' => '1.0.0',
                'icon' => EHXDO_PLUGIN_URL . 'assets/images/recaptcha.png',
                'description' => esc_html__(' Adds Google reCAPTCHA functionality to EHx plugins for enhanced form security.'),
                'premium' => false,
                'updated' => '2023-11-10',
                'url' => EHXDO_PLUGIN_URL . 'addons/advanced-forms.zip'
            ],
            'ehx-recurring-donation/ehx-recurring-donation.php' => [
                'name' => esc_html__('Recurring Donations', 'ehx-donate'),
                'version' => '1.0.0',
                'icon' => EHXDO_PLUGIN_URL . 'assets/images/donation.jpg',
                'description' => esc_html__('Recurring Donations - A powerful WordPress plugin that enables automated recurring donations, seamlessly integrating with EHx Donate for a smooth and efficient donor experience.'),
                'premium' => true,
                'updated' => '2023-11-15',
                'url' => 'https://store.eh.studio/addons/recurring-donations'
            ]
        ];
    }
    
    /**
     * Get Installed Addons
     *
     * @return array
     */
    public static function getInstalledAddons(): array 
    {
        // Check installed plugins
        $all_plugins = get_plugins();

        $addons = [
            'ehx-recaptcha/ehx-recaptcha.php',
            'ehx-recurring-donation/ehx-recurring-donation.php',
        ];
        $our_addons = [];
        
        foreach ($all_plugins as $path => $plugin) {
            if (in_array($path, $addons)) {
                $our_addons[$path] = [
                    'active' => is_plugin_active($path),
                    'version' => $plugin['Version']
                ];
            }
        }

        return $our_addons;
    }
}