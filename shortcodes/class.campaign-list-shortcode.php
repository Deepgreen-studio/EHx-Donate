<?php

if (!class_exists('EHX_Donate_Campaign_List_Shortcode')) {

    class EHX_Donate_Campaign_List_Shortcode
    {
        /**
         * Initializes the donation table shortcode.
         *
         * This method adds the 'ehx_donate_campaign_lists' shortcode to WordPress,
         * which triggers the 'add_shortcode' method when used in content.
         */
        public function __construct()
        {
            add_shortcode('ehx_donate_campaign_lists', [$this, 'add_shortcode']);
        }

        public function add_shortcode($atts)
        {
            // Set default attributes
            $atts = shortcode_atts(array(
                'post_type'      => 'ehx-campaign',       // Custom post type
                'posts_per_page' => 6,          // Number of posts
                'order'          => 'DESC',       // Sorting order
                'orderby'        => 'date',       // Order by field
                'category'       => '',           // Category filter (if taxonomy applies)
                'taxonomy'       => '',           // Custom taxonomy
                'terms'          => '',           // Term slug for taxonomy filter
                'meta_key'       => '',           // Meta key
                'meta_value'     => '',           // Meta value
                'meta_compare'   => '=',          // Meta compare operator
                'exclude'        => '',           // Exclude post IDs
                'include'        => '',           // Include post IDs
                'columns'        => '2',          // Number of columns
                'layout'         => 'grid',       // Layout type (grid/list)
                'image_size'     => 'thumbnail',  // Image size
                'show_excerpt'   => 'true',       // Show excerpt
                'excerpt_length' => '20',         // Excerpt length
                'show_read_more' => 'true',       // Show read more button
                'read_more_text' => 'Read More',  // Read more button text
                'pagination'     => 'true',       // Enable pagination
            ), $atts);

            // Query arguments
            $args = array(
                'post_type'      => $atts['post_type'],
                'posts_per_page' => intval($atts['posts_per_page']),
                'order'          => $atts['order'],
                'orderby'        => $atts['orderby'],
                'post__not_in'   => !empty($atts['exclude']) ? explode(',', $atts['exclude']) : array(),
                'post__in'       => !empty($atts['include']) ? explode(',', $atts['include']) : array(),
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

            require EHX_DONATE_PLUGIN_DIR . 'views/shortcodes/campaign-lists.php';

            wp_reset_postdata();

            return ob_get_clean();
        }

    }
}