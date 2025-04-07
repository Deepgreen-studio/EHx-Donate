<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<div class="edp-admin-metabox">
    <?php
        $ehx_campaign = get_post_meta($post->ID, '_ehx_campaign', true);
        $banner_image = isset($ehx_campaign['banner_image']) ? $ehx_campaign['banner_image'] : null;
    ?>
    <img src="<?php echo esc_url(wp_get_attachment_image_url($banner_image, 'medium')); ?>" style="max-width:100%; display:<?php echo !empty($banner_image) ? 'block' : 'none'; ?>;">
    <br>

    <button type="button" class="edp-metabox-thickbox" id="upload-image" data-title="<?php esc_html_e('Upload Banner Image', 'ehx-donate'); ?>" data-button="<?php esc_html_e('Use this image', 'ehx-donate'); ?>"><?php esc_html_e('Set Banner Image', 'ehx-donate'); ?></button>
    <button type="button" class="edp-metabox-thickbox" id="remove-image" style="display:<?php echo $banner_image ? 'inline-block' : 'none'; ?>;color: #b32d2e;"><?php esc_html_e('Remove', 'ehx-donate'); ?></button>

    <input type="hidden" id="banner_image" name="_ehx_campaign[banner_image]" value="<?php echo esc_attr($banner_image); ?>">
</div>