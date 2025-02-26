<div class="ehx-alert-element d-none">
    <div class="ehx-alert ehx-alert-primary text-center rounded-0" role="alert" id="ehx-alert-message">
        <i>&quest;</i>
        <span><?php esc_html_e('Item added to cart successfully', 'ehx-member'); ?></span>
    </div>
    <div class="ehx-alert-close">&#10006;</div>
</div>

<div class="ehx-container">
    <!-- Title section -->
    <!-- <div class="title"><?php echo esc_html($post->post_title); ?></div> -->
    <div class="ehx-content">

        <?php if($ehx_mode == 'profile'): ?>
            <div>
                <ul class="ehx-tabs">
                    <li><a href="#" class="ehx-tab-link" id="ehx-tab-link" data-tab="subscription"><?php esc_html_e('Subscriptions', 'ehx-member'); ?></a></li>
                    <li><a href="#" class="ehx-tab-link active" id="ehx-tab-link" data-tab="form"><?php esc_html_e('Edit Profile', 'ehx-member'); ?></a></li>
                    <li><a href="#" class="ehx-tab-link" id="ehx-tab-link" data-tab="payment"><?php esc_html_e('Payments', 'ehx-member'); ?></a></li>
                    <li><a href="<?php echo esc_url(wp_logout_url( home_url())); ?>" class="ehx-tab-link"><?php esc_html_e('Logout', 'ehx-member'); ?></a></li>
                </ul>
            </div>
        <?php endif ?>
        
        <div class="ehx-tab-contents">

            <div class="ehx-tab-content" id="tab-subscription">
                <div class="">
                    <p><?php esc_html_e('You do not have any subscriptions attached to your account.', 'ehx-member') ?></p>
                </div>
            </div>

            <div class="ehx-tab-content active" id="tab-form">
                <!-- Registration form -->
                <form action="#" method="POST" class="ehx-form" id="ehx_member_form_submit" <?php if($load_stripe): ?> enctype="application/x-www-form-urlencoded" <?php else: ?>  enctype="multipart/form-data" <?php endif ?> >

                    <input type="hidden" name="action" value="ehx_members_from_submit">
                    <?php wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME); ?>

                    <div class="ehx-user-details">
                        <?php
                            if (gettype($ehx_custom_fields) == 'array') {
                                foreach ($ehx_custom_fields as $index => $field) {
                                    EHX_Form_Shortcode::input_field($field);

                                    if ($field['_type'] == 'password' && isset($field['_force_confirm_pass']) && $field['_force_confirm_pass']) {
                                        EHX_Form_Shortcode::input_field(array_merge($field, ['_label' => $field['_label_confirm_pass'], '_metakey' => 'confirm_password']));
                                    }
                                }
                            }
                        ?>

                        <?php if($load_stripe): ?>
                            <div class="ehx-input-box ehx-input-box-full">
                                <label class="ehx-details" for="payment"><?php esc_html_e('Payment', 'ehx-member') ?> <?php echo esc_html("(£{$ehx_form['ehx_amount']})") ?> </label>
                                <div style="border: 1px solid #b2b4b7; padding: 12px 8px;">
                                    <div 
                                        id="card_element"
                                        data-key="<?php echo esc_attr(EHX_Member_Settings::extract_setting_value('stripe_client_key')) ?>"
                                    ></div>
                                </div>
                            </div>
                        <?php endif ?>

                        <input type="hidden" name="form_id" value="<?php echo esc_html($form_id); ?>">

                        <?php if($enable_recaptcha): ?>
                            <script src="https://www.google.com/recaptcha/api.js" async defer></script>
                            <div class="g-recaptcha mb-3" id="feedback-recaptcha" data-sitekey="<?php echo esc_attr(EHX_Member_Settings::extract_setting_value('google_recaptcha_site_key')) ?>"></div>
                        <?php endif ?>

                    </div>

                    <?php if (isset($ehx_form['register_use_gdpr']) && $ehx_form['register_use_gdpr']): ?>
                        <div class="ehx-input-box ehx-input-box-full">
                            <div class="ehx-gender-details">
                                <div class="ehx-category">
                                    <div>
                                        <input type="checkbox" id="privacy_policy" required>
                                        <label for="privacy_policy" style="align-items: inherit;">
                                            <span class="ehx-dot ehx-dot-checkbox" style="margin-top: 7px;width: 30px;height:20px;"></span>
                                            <span class="ehx-gender"><?php echo esc_html($ehx_form['register_use_gdpr_agreement']) ?></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif ?>

                    <!-- Submit button -->
                    <div>
                        <button type="submit" class="ehx-button"><?php echo !empty($ehx_form['primary_btn_word']) ? esc_html($ehx_form['primary_btn_word']) : esc_html__('Submit', 'ehx-member') ?></button>
                    </div>
                </form>
            </div>

            <div class="ehx-tab-content" id="tab-payment">
                <div class="">
                    <p><?php esc_html_e('Payments data.', 'ehx-member') ?></p>
                </div>
            </div>

        </div>

    </div>
</div>