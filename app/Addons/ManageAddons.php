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
    const NONCE_ACTION = 'ehxdo_handle_addon_form';
    private Response $response;

    public function __construct()
    {
        $this->response = new Response();

        add_action('wp_ajax_ehxdo_handle_addon_form', [$this, 'handleFormSubmit']);
    }

    public function handleFormSubmit()
    {
        $request = new Request();

        try {
            $slug = $request->input('slug');

            return match($request->input('type')) {
                'install' => $this->installAddon($slug),
                'activate' => $this->activateAddon($slug),
                'delete' => $this->destroyAddon($slug),
                default => $this->deactivateAddon($slug),
            };

        } catch (\Exception $e) {
            return $this->response->error(esc_html($e->getMessage()));
        }
    }
    
    /**
     * Install Addon
     *
     * @param  string $slug
     */
    protected function installAddon($slug) 
    {
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
    
        $upgrader = new Plugin_Upgrader(new Automatic_Upgrader_Skin());

        $addons = self::getAvailableAddons();

        $addon = $addons[$slug]['url'] ?? null;

        $result = $upgrader->install($addon);
    
        if (is_wp_error($result)) {
            return $this->response->error(esc_html($result->get_error_message()));
        }
        
        return $this->response->success(esc_html__('Add-on installed successfully!', 'ehx-donate'));

    } 
    
    /**
     * Activate Addon
     *
     * @param  string $slug
     */
    protected function activateAddon($slug) 
    {
        if (!current_user_can('activate_plugins')) {
            return $this->response->error(esc_html__('You do not have sufficient permissions.', 'ehx-donate'));
        }

        $result = activate_plugin($slug);

        if (is_wp_error($result)) {
            return $this->response->error(esc_html($result->get_error_message()));
        }

        return $this->response->success(esc_html__('Add-on activated successfully!', 'ehx-donate'));
    }
    
    /**
     * Deactivate Addon
     *
     * @param  string $slug
     */
    protected function deactivateAddon($slug) 
    {
        if (!current_user_can('activate_plugins')) {
            return $this->response->error(esc_html__('You do not have sufficient permissions.', 'ehx-donate'));
        }

        deactivate_plugins($slug, true);

        return $this->response->success(esc_html__('Add-on deactivated successfully!', 'ehx-donate'));
    }
    
    /**
     * Delete Addon
     *
     * @param  string $slug
     */
    protected function destroyAddon($slug) 
    {
        if (!current_user_can('activate_plugins')) {
            return $this->response->error(esc_html__('You do not have sufficient permissions.', 'ehx-donate'));
        }

        $result = delete_plugins([$slug]);
        if (is_wp_error($result)) {
            return $this->response->error(esc_html($result->get_error_message()));
        }

        return $this->response->success(esc_html__('Add-on deleted successfully!', 'ehx-donate'));
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
                'description' => esc_html__(' Adds Google reCAPTCHA functionality to EHx plugins for enhanced form security.', 'ehx-donate'),
                'premium' => false,
                'updated' => '2023-11-10',
                'url' => 'https://portal.immersivebrands.co.uk/storage/plugin/ehx-recaptcha.zip'
            ],
            'ehx-recurring-donation/ehx-recurring-donation.php' => [
                'name' => esc_html__('Recurring Donations', 'ehx-donate'),
                'version' => '1.0.0',
                'icon' => EHXDO_PLUGIN_URL . 'assets/images/donation.jpg',
                'description' => esc_html__('Recurring Donations - A powerful WordPress plugin that enables automated recurring donations, seamlessly integrating with EHx Donate for a smooth and efficient donor experience.', 'ehx-donate'),
                'premium' => true,
                'updated' => '2023-11-15',
                'url' => null
            ],
            'ehx-giftaid/ehx-giftaid.php' => [
                'name' => esc_html__('Gift Aid', 'ehx-donate'),
                'version' => '1.0.0',
                'icon' => EHXDO_PLUGIN_URL . 'assets/images/gift-aid.png',
                'description' => esc_html__('Gift Aid plugin extends the EHx Donate plugin to enable Gift Aid functionality, allowing donors to increase their donation amount without additional cost.', 'ehx-donate'),
                'premium' => true,
                'updated' => '2023-11-15',
                'url' => null
            ],
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
                    'version' => $plugin['Version'],
                ];
            }
        }

        return $our_addons;
    }
}