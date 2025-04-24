<?php
declare(strict_types=1);

namespace EHxDonate\Shortcodes;

use EHxDonate\Classes\View;
use WP_Query;

if (!defined('ABSPATH')) {
    exit;
}

class CampaignListShortcode
{
    public function __construct()
    {
        add_shortcode('ehxdo_campaign_lists', [$this, 'renderShortcode']);
    }

    /**
     * Render the [ehxdo_campaign_lists] shortcode.
     *
     * @param array $atts Shortcode attributes.
     * @return string HTML output of campaign list.
     */
    public function renderShortcode($atts): string
    {
        $atts = shortcode_atts([
            'posts_per_page' => 6,
            'order'          => 'DESC',
            'orderby'        => 'date',
            'category'       => '',
            'taxonomy'       => '',
            'terms'          => '',
            'meta_key'       => '',
            'meta_value'     => '',
            'meta_compare'   => '=',
            'exclude'        => '',
            'include'        => '',
            'columns'        => 2,
            'layout'         => 'grid',
            'image_size'     => 'thumbnail',
            'show_excerpt'   => 'true',
            'excerpt_length' => 10,
            'show_button'    => 'true',
            'button_text'    => esc_html__('Donate Now', 'ehx-donate'),
            'pagination'     => 'true',
        ], array_change_key_case((array) $atts));

        $args = [
            'post_type'      => 'ehxdo-campaign',
            'posts_per_page' => (int) $atts['posts_per_page'],
            'order'          => strtoupper($atts['order']) === 'ASC' ? 'ASC' : 'DESC',
            'orderby'        => sanitize_key($atts['orderby']),
        ];

        // Post include/exclude
        if (!empty($atts['exclude'])) {
            $args['post__not_in'] = array_map('absint', explode(',', $atts['exclude']));
        }

        if (!empty($atts['include'])) {
            $args['post__in'] = array_map('absint', explode(',', $atts['include']));
        }

        // Taxonomy filter
        if (!empty($atts['taxonomy']) && !empty($atts['terms'])) {
            $args['tax_query'] = [
                [
                    'taxonomy' => sanitize_key($atts['taxonomy']),
                    'field'    => 'slug',
                    'terms'    => array_map('sanitize_text_field', explode(',', $atts['terms'])),
                ],
            ];
        }

        // Meta filter
        if (!empty($atts['meta_key']) && !empty($atts['meta_value'])) {
            $args['meta_query'] = [
                [
                    'key'     => sanitize_key($atts['meta_key']),
                    'value'   => sanitize_text_field($atts['meta_value']),
                    'compare' => in_array($atts['meta_compare'], ['=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE'], true)
                        ? $atts['meta_compare']
                        : '=',
                ],
            ];
        }

        // Query posts
        $query = new WP_Query($args);

        wp_reset_postdata();

        $content = View::render('shortcodes/campaign-lists', ['query' => $query, 'atts' => $atts], true);

        return $content;
    }
}
