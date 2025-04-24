<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php use EHxDonate\Helpers\Helper; ?>

<div class="edp-admin-metabox">
	
	<input type="hidden" name="<?php echo esc_html(Helper::NONCE_NAME) ?>" value="<?php echo esc_html(wp_create_nonce(Helper::NONCE_NAME)) ?>">
    
	<table class="form-table edp-form-table edp-form-register-gdpr edp-top-label">
        <tbody>
			<?php
				$ehx_campaign = get_post_meta($post->ID, '_ehx_campaign', true);
			?>
            <?php foreach($fields->campaigns as $fields): ?>
				<tr class="edp-forms-line">
					<?php foreach($fields as $field): ?>
						<td style="padding:16px;display: table-cell;">
							<label for="<?php echo esc_attr($field->id); ?>">
								<?php echo esc_html($field->label); ?>
								<?php if(isset($field->tooltip)): ?>
									<span class="ehx_tooltip dashicons dashicons-editor-help" title="<?php echo esc_html($field->tooltip); ?>"></span>
								<?php endif ?>
							</label>
							
							<?php if($field->type === 'select'): ?>
								<select id="<?php echo esc_attr($field->id); ?>" name="_ehx_campaign[<?php echo esc_attr($field->id); ?>]" class="edp-forms-field edp-long-field">
									<?php if(gettype($field->options) === 'string'): ?>
										<?php foreach(get_pages() as $page): ?>
											<option value="<?php echo esc_html($page->ID); ?>" <?php echo selected($page->ID, $ehx_campaign[$field->id] ?? null) ?>><?php echo esc_html($page->post_title); ?></option>
										<?php endforeach ?>
									<?php else: ?>
										<?php foreach($field->options as $value): ?>
											<option value="<?php echo esc_attr($value); ?>" <?php echo selected($value, $ehx_campaign[$field->id] ?? null) ?>><?php echo esc_html($value); ?></option>
										<?php endforeach ?>
									<?php endif ?>
								</select>
							<?php else: ?>
								<input type="<?php echo esc_attr($field->type); ?>" id="<?php echo esc_attr($field->id); ?>" name="_ehx_campaign[<?php echo esc_attr($field->id); ?>]" placeholder="<?php echo esc_attr($field->placeholder ?? ''); ?>" class="edp-forms-field edp-long-field" value="<?php echo esc_html($ehx_campaign[$field->id] ?? $field->value) ?>">
							<?php endif ?>
						</td>
					<?php endforeach ?>
				</tr>
			<?php endforeach ?>
        </tbody>
    </table>

    <div class="clear"></div>
</div>