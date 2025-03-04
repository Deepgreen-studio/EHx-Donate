<style>
    button.thickbox {
        background: transparent;
        border: transparent;
        color: #2271b1;
        cursor: pointer;
    }
</style>
<div class="edp-admin-metabox">
    <?php
        $ehx_campaign = get_post_meta($post->ID, '_ehx_campaign', true);
        $banner_image = isset($ehx_campaign['banner_image']) ? $ehx_campaign['banner_image'] : null;
    ?>
    <img src="<?php echo wp_get_attachment_image_url($banner_image, 'medium'); ?>" style="max-width:100%; display:<?php echo !empty($banner_image) ? 'block' : 'none'; ?>;">
    <br>

    <button type="button" class="thickbox" id="upload-image" data-title="<?php _e('Upload Banner Image', 'edp-donate'); ?>"><?php _e('Set Banner Image', 'exh-donate'); ?></button>
    <button type="button" class="thickbox" id="remove-image" style="display:<?php echo $banner_image ? 'inline-block' : 'none'; ?>;color: #b32d2e;"><?php _e('Remove', 'exh-donate'); ?></button>

    <input type="hidden" id="banner_image" name="_ehx_campaign[banner_image]" value="<?php echo esc_attr($banner_image); ?>">
</div>

<script>
    jQuery(document).ready(function($) {
		var frame;
		$('.edp-admin-metabox').on('click', '#upload-image', function(e) {
			e.preventDefault();

			let btn = $(this);
			let title = btn.data('title');

			if (frame) {
				frame.open();
				return;
			}
			
			frame = wp.media({
				title: title,
				button: { text: '<?php _e('Use this image', 'edp-donate'); ?>' },
				multiple: false
			});
			frame.on('select', function() {
				var attachment = frame.state().get('selection').first().toJSON();
				
				btn.siblings('input').val(attachment.id);
				btn.siblings('img').attr('src', attachment.url).show();
				btn.siblings('#remove-image').show();
			});
			frame.open();
		});

		$('#remove-image').click(function() {
			$(this).siblings('input').val('');
			$(this).siblings('img').hide();
			$(this).hide();
		});
	});
</script>