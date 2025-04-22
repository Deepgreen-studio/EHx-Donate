<?php
declare(strict_types=1);

namespace EHxDonate\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use WP_Query;

if (!defined('ABSPATH')) {
    exit;
}

class CampaignListWidget extends Widget_Base
{
    /**
     * Get widget name
     */
    public function get_name() 
    {
        return 'ehxdo_campaigns_list';
    }

    /**
     * Get widget title
     */
    public function get_title() 
    {
        return __('Campaigns List', 'ehx-donate');
    }

    /**
     * Get widget icon
     */
    public function get_icon() 
    {
        return 'eicon-wallet';
    }

    /**
     * Get widget categories
     */
    public function get_categories() 
    {
        return ['general'];
    }

    /**
     * Register widget controls
     */
    protected function _register_controls() 
    {
        $this->registerContentControls();

        $this->registerStyleControls();
    }

    /**
     * Render widget output
     */
    protected function render() 
    {
        $settings = $this->get_settings_for_display();

        // Build query args from widget settings
        $atts = [
            'posts_per_page' => $settings['posts_per_page'],
            'order'          => $settings['order'],
            'orderby'        => $settings['orderby'],
            'exclude'        => $settings['exclude'],
            'include'        => $settings['include'],
            'columns'        => $settings['columns'],
            'layout'         => $settings['layout'],
            'image_size'     => $settings['image_size'],
            'show_excerpt'   => $settings['show_excerpt'],
            'excerpt_length' => $settings['excerpt_length'],
            'show_button'    => $settings['show_button'],
            'button_text'    => $settings['button_text'],
            'pagination'     => $settings['pagination'],
        ];

        $args = array(
            'post_type'      => 'ehxdo-campaign',
            'posts_per_page' => intval($atts['posts_per_page']),
            'order'          => $atts['order'],
            'orderby'        => $atts['orderby'],
            'post__not_in'   => !empty($atts['exclude']) ? explode(',', $atts['exclude']) : [],
            'post__in'       => !empty($atts['include']) ? explode(',', $atts['include']) : [],
        );

        $query = new WP_Query($args);

        include EHXDO_PLUGIN_DIR . 'views/shortcodes/campaign-lists.php';

        wp_reset_postdata();
    }

    /**
     * Register content controls
     */
    protected function registerContentControls()
    {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'ehx-donate'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'posts_per_page',
            [
                'label' => __('Posts Per Page', 'ehx-donate'),
                'type' => Controls_Manager::NUMBER,
                'default' => 6,
            ]
        );

        $this->add_control(
            'order',
            [
                'label' => __('Order', 'ehx-donate'),
                'type' => Controls_Manager::SELECT,
                'default' => 'DESC',
                'options' => [
                    'DESC' => __('Descending', 'ehx-donate'),
                    'ASC' => __('Ascending', 'ehx-donate'),
                ],
            ]
        );

        $this->add_control(
            'orderby',
            [
                'label' => __('Order By', 'ehx-donate'),
                'type' => Controls_Manager::SELECT,
                'default' => 'date',
                'options' => [
                    'date' => __('Date', 'ehx-donate'),
                    'title' => __('Title', 'ehx-donate'),
                    'rand' => __('Random', 'ehx-donate'),
                ],
            ]
        );

        $this->add_control(
            'exclude',
            [
                'label' => __('Exclude', 'ehx-donate'),
                'type' => Controls_Manager::TEXT,
                'description' => 'Exclude post IDs. use , as separator.',
                'default' => '',
            ]
        );

        
        $this->add_control(
            'include',
            [
                'label' => __('Include', 'ehx-donate'),
                'type' => Controls_Manager::TEXT,
                'description' => 'include post IDs. use , as separator.',
                'default' => '',
            ]
        );

        $this->add_control(
            'columns',
            [
                'label' => __('Columns', 'ehx-donate'),
                'type' => Controls_Manager::NUMBER,
                'default' => 2,
            ]
        );

        $this->add_control(
            'layout',
            [
                'label' => __('Layout', 'ehx-donate'),
                'type' => Controls_Manager::SELECT,
                'default' => 'grid',
                'options' => [
                    'grid' => __('Grid', 'ehx-donate'),
                    'list' => __('List', 'ehx-donate'),
                ],
            ]
        );

        $this->add_control(
            'image_size',
            [
                'label' => __('Image Size', 'ehx-donate'),
                'type' => Controls_Manager::SELECT,
                'default' => 'thumbnail',
                'options' => [
                    'thumbnail' => __('thumbnail', 'ehx-donate'),
                    'medium' => __('Medium', 'ehx-donate'),
                    'medium_large' => __('Medium Large', 'ehx-donate'),
                    'large' => __('Large', 'ehx-donate'),
                    'full' => __('Full', 'ehx-donate'),
                ],
            ]
        );

        $this->add_control(
            'show_excerpt',
            [
                'label' => __('Show Excerpt', 'ehx-donate'),
                'type' => Controls_Manager::SELECT,
                'default' => 'true',
                'options' => [
                    'true' => __('Yes', 'ehx-donate'),
                    'false' => __('No', 'ehx-donate'),
                ],
            ]
        );

        $this->add_control(
            'excerpt_length',
            [
                'label' => __('Excerpt Length', 'ehx-donate'),
                'type' => Controls_Manager::NUMBER,
                'default' => 10,
            ]
        );

        $this->add_control(
            'show_button',
            [
                'label' => __('Show Button', 'ehx-donate'),
                'type' => Controls_Manager::SELECT,
                'default' => 'true',
                'options' => [
                    'true' => __('Yes', 'ehx-donate'),
                    'false' => __('No', 'ehx-donate'),
                ],
            ]
        );

        $this->add_control(
            'button_text',
            [
                'label' => __('Button Text', 'ehx-donate'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Reserve a spot', 'ehx-donate'),
            ]
        );

        $this->add_control(
            'pagination',
            [
                'label' => __('Pagination', 'ehx-donate'),
                'type' => Controls_Manager::SELECT,
                'default' => 'true',
                'options' => [
                    'true' => __('Yes', 'ehx-donate'),
                    'false' => __('No', 'ehx-donate'),
                ],
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Register style controls
     */
    protected function registerStyleControls()
    {
        $this->registerCardStyleControls();
        
        $this->registerImageStyleControls();
        $this->registerButtonStyleControls();
    }
    
    /**
     * Register card style controls
     */
    protected function registerCardStyleControls()
    {
        // Style Tab
        $this->start_controls_section(
            'style_section',
            [
                'label' => __('Style', 'ehx-donate'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        // Card Typography
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'card_typography',
                'selector' => '{{WRAPPER}} .edp-campaign-item',
            ]
        );

        $this->start_controls_tabs('card_style_tabs');

        // Normal State
        $this->start_controls_tab(
            'card_normal_tab',
            [
                'label' => __('Normal', 'ehx-donate'),
            ]
        );

        $this->add_control(
            'card_bg_color',
            [
                'label' => __('Background Color', 'ehx-donate'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .edp-campaign-item' => 'background-color: {{VALUE}} !important',
                ],
            ]
        );

        $this->add_control(
            'card_title_color',
            [
                'label' => __('Title Color', 'ehx-donate'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .edp-content-title' => 'color: {{VALUE}} !important',
                ],
            ]
        );

        $this->add_control(
            'card_description_color',
            [
                'label' => __('Description Color', 'ehx-donate'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .edp-content-text' => 'color: {{VALUE}} !important',
                ],
            ]
        );

        $this->add_control(
            'card_border_width',
            [
                'label' => __('Border Width', 'ehx-donate'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 20,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .ehx-donate-item' => 'border-width: {{SIZE}}{{UNIT}} !important',
                ],
            ]
        );

        $this->add_control(
            'card_border_color',
            [
                'label' => __('Border Color', 'ehx-donate'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .edp-campaign-item' => 'border-color: {{VALUE}} !important',
                ],
            ]
        );
    
        $this->add_control(
            'card_border_radius',
            [
                'label' => __('Border Radius', 'ehx-donate'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .edp-campaign-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'card_box_shadow',
                'selector' => '{{WRAPPER}} .edp-campaign-item',
            ]
        );

        $this->end_controls_tab();

        // End Normal State

        // Hover State
        $this->start_controls_tab(
            'card_hover_tab',
            [
                'label' => __('Hover', 'ehx-donate'),
            ]
        );

        $this->add_control(
            'card_bg_color_hover',
            [
                'label' => __('Background Color', 'ehx-donate'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .edp-campaign-item:hover' => 'background-color: {{VALUE}} !important',
                ],
            ]
        );

        $this->add_control(
            'card_title_color_hover',
            [
                'label' => __('Title Color', 'ehx-donate'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .edp-content-title:hover' => 'color: {{VALUE}} !important',
                ],
            ]
        );

        $this->add_control(
            'card_description_color_hover',
            [
                'label' => __('Description Color', 'ehx-donate'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .edp-content-text:hover' => 'color: {{VALUE}} !important',
                ],
            ]
        );

        $this->add_control(
            'card_border_width_hover',
            [
                'label' => __('Border Width', 'ehx-donate'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 20,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .ehx-donate-item' => 'border-width: {{SIZE}}{{UNIT}} !important',
                ],
            ]
        );

        $this->add_control(
            'card_border_color_hover',
            [
                'label' => __('Border Color', 'ehx-donate'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .edp-campaign-item:hover' => 'border-color: {{VALUE}} !important',
                ],
            ]
        );
    
        $this->add_control(
            'card_border_radius_hover',
            [
                'label' => __('Border Radius', 'ehx-donate'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .edp-campaign-item:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'card_box_shadow_hover',
                'selector' => '{{WRAPPER}} .edp-campaign-item:hover',
            ]
        );

        $this->end_controls_tab();

        // End hover state

        $this->end_controls_tabs();

        $this->end_controls_section();
    }

    /**
     * Register image style controls
     */
    protected function registerImageStyleControls()
    {
        // Add Style Section
        $this->start_controls_section(
            'image_style_section',
            [
                'label' => __('Image Style', 'ehx-donate'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );
    
        // Image Typography
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'image_typography',
                'selector' => '{{WRAPPER}} .edp-campaign-thumbnail-img',
            ]
        );
    
        // Image Colors
        $this->start_controls_tabs('image_style_tabs');
    
        // Start Normal State
        $this->start_controls_tab(
            'image_normal_tab',
            [
                'label' => __('Normal', 'ehx-donate'),
            ]
        );
    
        $this->add_control(
            'image_border_radius',
            [
                'label' => __('Border Radius', 'ehx-donate'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .edp-campaign-thumbnail-img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important',
                ],
            ]
        );
    
        $this->end_controls_tab();
        // End Normal State

        // Start Hover State
        $this->start_controls_tab(
            'image_hover_tab',
            [
                'label' => __('Hover', 'ehx-donate'),
            ]
        );
    
        $this->add_control(
            'image_border_radius_hover',
            [
                'label' => __('Border Radius', 'ehx-donate'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .edp-campaign-thumbnail-img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important',
                ],
            ]
        );
        $this->end_controls_tab();
        // End hover state

        $this->end_controls_tabs();
    
        $this->end_controls_section();
    }

    /**
     * Register button style controls
     */
    protected function registerButtonStyleControls()
    {
        $this->start_controls_section(
            'button_style_section',
            [
                'label' => __('Button Style', 'ehx-donate'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'button_typography',
                'selector' => '{{WRAPPER}} .edp-campaign-btn',
            ]
        );

        $this->start_controls_tabs('button_style_tabs');

        // Normal State
        $this->start_controls_tab(
            'button_normal_tab',
            ['label' => __('Normal', 'ehx-donate')]
        );

        $this->add_control(
            'button_text_color',
            [
                'label' => __('Text Color', 'ehx-donate'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .edp-campaign-btn' => 'color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_control(
            'button_bg_color',
            [
                'label' => __('Background Color', 'ehx-donate'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .edp-campaign-btn' => 'background-color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'button_border',
                'selector' => '{{WRAPPER}} .edp-campaign-btn',
                'fields_options' => [
                    'color' => [
                        'selectors' => [
                            '{{WRAPPER}} .edp-campaign-btn' => 'border-color: {{VALUE}} !important;'
                        ]
                    ],
                    'width' => [
                        'selectors' => [
                            '{{WRAPPER}} .edp-campaign-btn' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;'
                        ]
                    ]
                ]
            ]
        );

        $this->add_control(
            'button_border_radius',
            [
                'label' => __('Border Radius', 'ehx-donate'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .edp-campaign-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                ],
            ]
        );

        $this->add_control(
            'button_padding',
            [
                'label' => __('Padding', 'ehx-donate'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .edp-campaign-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'button_box_shadow',
                'selector' => '{{WRAPPER}} .edp-campaign-btn',
            ]
        );

        $this->end_controls_tab();

        // Hover State
        $this->start_controls_tab(
            'button_hover_tab',
            ['label' => __('Hover', 'ehx-donate')]
        );

        $this->add_control(
            'button_text_color_hover',
            [
                'label' => __('Text Color', 'ehx-donate'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .edp-campaign-btn:hover' => 'color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_control(
            'button_bg_color_hover',
            [
                'label' => __('Background Color', 'ehx-donate'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .edp-campaign-btn:hover' => 'background-color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_control(
            'button_border_color_hover',
            [
                'label' => __('Border Color', 'ehx-donate'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .edp-campaign-btn:hover' => 'border-color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->end_controls_tab();
        $this->end_controls_tabs();

        $this->end_controls_section();
    }
}