<?php
declare(strict_types=1);

namespace EHxDonate\Shortcodes;

use WP_Query;

if (!defined('ABSPATH')) {
    exit;
}

class CampaignListShortcode
{
    /**
     * Initializes the donation table shortcode.
     *
     * This method adds the 'CampaignListShortcode' shortcode to WordPress,
     * which triggers the 'add_shortcode' method when used in content.
     */
    public function __construct()
    {
        add_shortcode('ehxdo_campaign_lists', [$this, 'addShortcode']);
    }

    /**
     * Processes the ehxdo_campaign_lists shortcode and generates a list of campaigns.
     *
     * @param array $atts An array of shortcode attributes.
     *
     * @return string The generated HTML for the campaign list.
     */
    public function addShortcode($atts)
    {
        // Set default attributes
        $atts = shortcode_atts(array(
            'posts_per_page' => 6,          // Number of posts to display
            'order'          => 'DESC',       // Order of posts (DESC or ASC)
            'orderby'        => 'date',       // Field to order posts by
            'category'       => '',           // Category filter (if taxonomy applies)
            'taxonomy'       => '',           // Custom taxonomy
            'terms'          => '',           // Term slug for taxonomy filter
            'meta_key'       => '',           // Meta key
            'meta_value'     => '',           // Meta value
            'meta_compare'   => '=',          // Meta compare operator
            'exclude'        => '',           // Exclude post IDs
            'include'        => '',           // Include post IDs
            'columns'        => 2,            // Number of columns in the layout
            'layout'         => 'grid',       // Layout type (grid or list)
            'image_size'     => 'thumbnail',  // Image size for campaign thumbnails
            'show_excerpt'   => 'true',       // Show excerpt for campaigns
            'excerpt_length' => 10,           // Length of the excerpt
            'show_button'    => 'true',       // Show donate now button for campaigns
            'button_text'    => esc_html__('Donate Now', 'ehx-donate'), // Text for the donate now button
            'pagination'     => 'true',       // Enable pagination for the campaign list
        ), array_change_key_case((array) $atts));

        // Query arguments
        $args = array(
            'post_type'      => 'ehxdo-campaign',
            'posts_per_page' => intval($atts['posts_per_page']),
            'order'          => $atts['order'],
            'orderby'        => $atts['orderby'],
            'post__not_in'   => !empty($atts['exclude']) ? explode(',', $atts['exclude']) : [],
            'post__in'       => !empty($atts['include']) ? explode(',', $atts['include']) : [],
        );

        // Taxonomy filter
        if (!empty($atts['taxonomy']) && !empty($atts['terms'])) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => $atts['taxonomy'],
                    'field'    => 'slug',
                    'terms'    => explode(',', $atts['terms']),
                ),
            );
        }

        // Meta query
        if (!empty($atts['meta_key']) && !empty($atts['meta_value'])) {
            $args['meta_query'] = array(
                array(
                    'key'     => $atts['meta_key'],
                    'value'   => $atts['meta_value'],
                    'compare' => $atts['meta_compare'],
                ),
            );
        }

        // Query posts
        $query = new WP_Query($args);
        
        // Include the view file to generate the campaign list HTML
        require EHXDO_PLUGIN_DIR . 'views/shortcodes/campaign-lists.php';

        // Reset the post data after the query
        wp_reset_postdata();

        // Return the generated HTML
        return ob_get_clean();
    }

}