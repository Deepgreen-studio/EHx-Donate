<?php if($query->have_posts()): ?>
    <div class="edp-campaign-list edp-layout-<?php echo esc_attr($atts['layout']) ?> edp-columns-<?php echo esc_attr($atts['columns']) ?>">
        <?php while ($query->have_posts()): ?>
            <?php $query->the_post(); ?>
            <div class="edp-campaign-item">
                <a href="<?php echo esc_url(get_permalink()); ?>" class="edp-campaign-card">
                    <?php
                        if (has_post_thumbnail()) {
                            echo '<div class="edp-campaign-thumbnail"><div>';
                            the_post_thumbnail($atts['image_size']);
                            echo '</div></div>';
                        }
                    ?>
                    <div class="edp-campaign-content">
                        <h3 class="edp-content-title"><?php echo esc_html(get_the_title()) ?></h3>

                        <p class="edp-content-text"><?php echo wp_trim_words(get_the_excerpt(), intval($atts['excerpt_length']), '...') ?></p>
                    </div>
                    <div class="edp-campaign-content">
                        
                    </div>
                </a>
            </div>
        <?php endwhile ?>
    </div>

    <?php
        // Pagination
        if ($atts['pagination'] == 'true') {
            echo '<div class="pagination">';
            echo paginate_links(array(
                'total' => $query->max_num_pages,
            ));
            echo '</div>';
        }
    ?>

<?php else: ?>
    <p>No posts found.</p>
<?php endif ?>