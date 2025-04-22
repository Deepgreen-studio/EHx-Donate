<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<input
    type="<?php echo esc_attr($type); ?>"
    id="<?php echo esc_attr($htmlFor); ?>"
    name="<?php echo esc_attr($htmlFor); ?>"
    class="regular-text"
    placeholder="<?php echo esc_html($placeholder); ?>"
    <?php if ($type === 'text'): ?>
    value="<?php echo esc_attr($value); ?>"
    <?php else:
        checked("on", $value, true);
    endif; ?>
    aria-label="<?php echo esc_html(ucfirst(str_replace('_', ' ', $label))); ?>" />

<?php if (in_array($type, ['checkbox', 'radio']) && !empty($placeholder)): ?>
    <label for="<?php echo esc_attr($htmlFor); ?>">
        <?php echo esc_html($placeholder); ?>
    </label>
<?php endif; ?>