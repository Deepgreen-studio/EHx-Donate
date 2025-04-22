<?php
declare(strict_types=1);

namespace EHxDonate\Classes;

use EHxDonate\Widgets\CampaignListWidget;

if (!defined('ABSPATH')) {
    exit;
}

class RegisterElementorWidget
{
    /**
     * Constructor for RegisterElementorWidget class.
     *
     * Initializes the RegisterElementorWidget class and sets up necessary actions and properties.
     *
     * @return void
     */
    public function __construct() 
    {
        add_action('elementor/init', [$this, 'registerCampaignListWidget']);
    }
            
    /**
     * Register Campaign List Widget
     *
     * @return void
     */
    public function registerCampaignListWidget()
    { 
        \Elementor\Plugin::instance()->widgets_manager->register(new CampaignListWidget());
    }

}