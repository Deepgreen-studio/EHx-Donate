<div class="wrap">
    <div style="display: flex;"></div>
    <h1><?php esc_html_e('Member Details', 'ehx-member'); ?></h1>

    <div class="user-details">

        <div class="user-details-line">
            <?php
                if (isset($user_meta['profile_picture']) && !empty($user_meta['profile_picture'])) {
                    echo wp_get_attachment_image($user_meta['profile_picture']);
                } 
                else {
                    echo get_avatar($user->ID); 
                }
            ?>
        </div>

        <?php foreach($userData as $key => $data): ?>
            <div class="user-details-line">
                <p><?php echo esc_html($data['label']); ?>:</p>
                <p><?php echo esc_html($data['value']); ?></p>
            </div>
        <?php endforeach ?>

        <?php if(!empty($user_meta)): ?>
            <?php foreach($user_meta as $key => $value): ?>
                <?php if(!in_array($key, ['profile_picture', 'form_id'])): ?>
                    <div class="user-details-line">
                        <p>
                            <?php
                                if(isset($user_meta['form_id'])) {
                                    echo esc_html(EHX_Helper::getDynamicFieldLabel($user_meta['form_id'], $key));
                                }
                                else {
                                    echo esc_html(ucfirst(str_replace('_', ' ', $key)));
                                }
                            ?>
                        </p>
                        
                        <p>
                            <?php
                                if (gettype($value) == 'array') {
                                    echo implode(', ', $value);
                                } 
                                else {
                                    echo esc_html($value);
                                }
                            ?>
                        </p>
                    </div>
                <?php endif ?>
            <?php endforeach ?>
        <?php endif ?>

    </div>

    <a href="<?php echo esc_url(admin_url('admin.php?page=ehx_member_members')); ?>" class="button button-secondary">
        <?php esc_html_e('Back to Members', 'ehx-member'); ?>
    </a>
</div>