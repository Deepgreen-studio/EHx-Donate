<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<tr valign="top" <?php if ($dependable): ?> class="edp-disabled-content" <?php endif ?> <?php if (isset($args['depend_field'])): ?> data-depend_field="<?php echo esc_html($option . '[' . $args['depend_field'] . ']') ?>" data-depend_value="<?php echo esc_html($args['depend_value']) ?>" <?php endif ?>>
    <th scope="row">
        <label for="<?php echo esc_attr($field_name); ?>"><?php echo esc_html(ucfirst(str_replace('_', ' ', $field_name))); ?></label>
    </th>
    <td>
        <?php if ($input_type === 'input'): ?>
            <?php if ($type !== 'switch'): ?>
                <input
                    type="<?php echo esc_attr($type); ?>"
                    id="<?php echo esc_attr($input_name); ?>"
                    name="<?php echo esc_attr($input_name); ?>"
                    class="regular-text"
                    placeholder="<?php echo esc_attr($placeholder); ?>"
                    <?php if (!$dependable): ?>
                    value="<?php echo esc_attr($type == 'text' ? $value : '1'); ?>"
                    <?php endif; ?>
                    <?php
                    if ($type != 'text') {
                        checked(1, $value, true);
                    }
                    ?>
                    aria-label="<?php echo esc_html(ucfirst(str_replace('_', ' ', $field_name))); ?>" />

                <?php if ($type !== 'text' && !empty($placeholder)): ?>
                    <label for="<?php echo esc_attr($input_name); ?>">
                        <?php echo esc_html($placeholder); ?>
                    </label>
                <?php endif; ?>
            <?php else: ?>
                <label class="edp-switch">
                    <input
                        type="checkbox"
                        class="edp-checkbox"
                        id="<?php echo esc_attr($input_name); ?>"
                        name="<?php echo esc_attr($input_name); ?>"
                        value="1"
                        <?php checked(1, $value, true); ?>
                        data-dependable>
                    <div class="edp-switch-slider"></div>
                </label>
            <?php endif; ?>

        <?php elseif ($input_type === 'select'): ?>
            <select
                name="<?php echo esc_attr($input_name); ?>"
                id="<?php echo esc_attr($field_name); ?>"
                class="regular-text"
                aria-label="<?php echo esc_html(ucfirst(str_replace('_', ' ', $field_name))); ?>"
                aria-value="<?php echo esc_html($value); ?>">
                <option value=""><?php esc_html_e('Select an option', 'ehx-donate'); ?></option>
                <?php foreach ($data as $option): ?>
                    <option value="<?php echo esc_attr($option['key']); ?>" <?php selected($option['key'], $value); ?>>
                        <?php echo esc_html($option['value']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

        <?php elseif ($input_type === 'textarea'): ?>
            <textarea
                name="<?php echo esc_attr($input_name); ?>"
                id="<?php echo esc_attr($field_name); ?>"
                class="regular-text"
                placeholder="<?php echo esc_html($placeholder); ?>"
                aria-label="<?php echo esc_html(ucfirst(str_replace('_', ' ', $field_name))); ?>"><?php echo esc_html($value); ?></textarea>
        <?php endif; ?>
        <small id="invalid_<?php echo esc_attr($field_name); ?>" class="invalid-feedback" style="display: block;"></small>

        <?php if (isset($args['content'])): ?>
            <small><?php echo esc_html($args['content']); ?></small>
        <?php endif; ?>

    </td>
</tr>