<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<div class="<?php echo esc_attr($column) ?>">
    <?php if(!empty($label)): ?>
        <label for="<?php echo esc_attr($htmlFor) ?>" class="edp-field-labels">
            <?php echo esc_html(ucfirst(str_replace('_', ' ', $label))) ?> 
            <?php if($required): ?>
                <span>*</span>
            <?php endif ?>
        </label>
    <?php endif ?>

    <?php if($isType == 'select'): ?>
        <select name="<?php echo esc_attr($htmlFor) ?>" id="<?php echo esc_attr($htmlFor) ?>" class="edp-input-field" <?php echo esc_attr($isRequired); ?>>
            <?php if(!empty($placeholder)): ?>
                <option value=""><?php echo esc_html($placeholder) ?></option>
            <?php endif ?>
            
            <?php foreach($data as $key => $value): ?>
                <option value="<?php echo esc_html(gettype($key) == 'string' ? $key : $value); ?>"><?php echo esc_html($value) ?></option>
            <?php endforeach ?>
        </select>

    <?php elseif($isType == 'textarea'): ?>
        <textarea name="<?php echo esc_attr($htmlFor) ?>" id="<?php echo esc_attr($htmlFor) ?>" class="edp-input-field" <?php echo esc_attr($isRequired); ?>></textarea>
    <?php else: ?>
        <input type="text" name="<?php echo esc_attr($htmlFor) ?>" id="<?php echo esc_attr($htmlFor) ?>" class="edp-input-field"  <?php echo esc_attr($isRequired); ?> /> 
    <?php endif ?>
    <div id="invalid_<?php echo esc_html($htmlFor) ?>"></div>
</div>