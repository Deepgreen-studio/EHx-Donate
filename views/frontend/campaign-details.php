<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<?php 
    get_header(); 
    $ehx_campaign = get_post_meta(get_the_ID(), '_ehx_campaign', true);
?>

<section class="edp-campaign-section">
    <div class="edp-campaign-container">
        <div class="edp-campaign-content">

            <div class="edp-campaign-contents">
                <?php if (!empty($ehx_campaign['banner_image'])): ?>
                    <div class="edp-campaign-cover">
                        <?php echo wp_get_attachment_image($ehx_campaign['banner_image']); ?>
                    </div>
                <?php endif; ?>

                <h1 class="edp-campaign-title"><?php the_title(); ?></h1>
                <div><?php the_content(); ?></div>

                <?php
                    echo do_shortcode( '[ehxdo_donation_form /]' );
                ?>
            </div>

        </div>
    </div>
</section>

<?php get_footer(); ?>
