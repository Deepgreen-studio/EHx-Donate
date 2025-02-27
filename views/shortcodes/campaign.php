<style>
    .edp-card select[name="campaign"],
    .edp-card select[name="recurring"] {
        width: 50%;
    }
</style>
<div class="edp-card" id="edp-card-element">
    
    <form id="ehx_donate_form_submit" class="edp-form" method="POST">
        
        <input type="hidden" name="action" value="ehx_donate_from_submit">
        <input type="hidden" name="callback" value="<?php echo esc_html($callback); ?>">
        <?php wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME); ?>

        <input type="hidden" name="amount" id="amount" value="0" />

        <ul id="progressbar" class="edp-progressbar">
            <li class="edp-progress-active" id="donation" data-step="1"><strong>Donation</strong></li>
            <li class="<?php echo esc_attr(isset($payment_callback) && $payment_callback ? 'edp-progress-active':''); ?>" id="personal" data-step="2"><strong>Personal</strong></li>
            <li class="<?php echo esc_attr(isset($payment_callback) && $payment_callback ? 'edp-progress-active':''); ?>" id="confirm" data-step="3"><strong>Confirmation</strong></li>
        </ul>
        <br>

        <fieldset class="edp-fieldset" <?php if(isset($payment_callback) && $payment_callback): ?> style="display: none; position: relative; opacity: 0;" <?php endif ?>>
            <div class="edp-form-card">
                <div class="row">
                    <div class="col-7">
                        <h2 class="edp-fs-title">Donation Information:</h2>
                    </div>
                </div> 

                <?php
                    if (count($campaigns)) {
                        self::input_field(label: 'campaign', isType: 'select', placeholder: 'Select campaign', data: $campaigns);
                        echo '<p id="edp__donation__message" style="display: none;color:red;">'. esc_html__('Please select required fields.', 'ehx-donate') .'</p>';
                    }
                    else {
                        echo "<input type='hidden' name='campaign' id='campaign' value='{$post->post_name}' />";
                        echo '<p id="edp__donation__message" style="display: none;color:red;">'. esc_html__('Please select one option.', 'ehx-donate') .'</p>';
                    }

                    self::input_field(label: 'How often would you like to give?', for: 'recurring', isType: 'select', data: $recurring);
                ?>

                <div>
                    <label class="edp-field-labels">Now choose how much.</label>
                    <div class="edp-plan-lists">
                        <?php foreach([10,20,30,50,100] as $value): ?>
                            <div class="edp-plan-list" data-amount="<?php echo esc_html($value) ?>">
                                <div class="">
                                    <span class="edp-plan-list-currency">£</span>
                                    <span class="edp-plan-list-price"><?php echo esc_html($value) ?></span>
                                </div>

                                <span class="edp-plan-list-text">One-off</span>
                            </div>
                        <?php endforeach ?>

                        <div class="edp-plan-list edp-plan-list-custom-input">

                            <span class="edp-plan-list-currency" style="display: none;">£</span>

                            <div class="edp-plan-list-form-field">
                                <input type="text" name="" id="" area-label="Or enter how much" placeholder="Or enter how much" class="is-empty">
                            </div>

                            <span class="edp-plan-list-text">One-off</span>
                        </div>
                        
                    </div>
                </div>

                <div class="edp-donation-amounts" id="edp-donation-amounts" style="display: none;">
                    <div class="edp-donation-amount">
                        <div class="form-column">
                            <span class="form-row-title"><strong>Donation amount:</strong></span>
                        </div>
                        <div class="form-column">
                            <span class="form-row-value" id="edp_donation_amount"><strong></strong></span>
                        </div>
                    </div>
                    <div class="edp-donation-amount">
                        <div class="form-column">
                            <span class="form-row-title"><strong>Total amount to pay:</strong></span>
                        </div>
                        <div class="form-column">
                            <span class="form-row-value" id="edp_donation_pay"><strong></strong></span>
                        </div>
                    </div>
                </div>

            </div> 

            <div class="edp-step-buttons"></div>

            <input type="button" name="next" class="edp-next-btn edp-action-btn" value="Next" />

        </fieldset>

        <fieldset class="edp-fieldset" <?php if(isset($payment_callback) && $payment_callback): ?> style="display: none; position: relative; opacity: 0;" <?php endif ?>>
            <div class="edp-form-card">
                <div class="row">
                    <div class="col-7">
                        <h2 class="edp-fs-title">Personal Information:</h2>
                    </div>
                </div>

                <p id="edp__personal__message" style="display: none;color:red;"><?php esc_html_e('Please fill up required fields.', 'ehx-donate') ?></p>
                
                <div class="edp-input-fields">
                    <?php
                        self::input_field(label: 'title', isType: 'select', placeholder: 'Select title', data: ['Mr','Ms','Mrs','Miss','Dr'], column: 'edp-field-full');
                        self::input_field(label: 'first_name', placeholder: 'Enter First Name');
                        self::input_field(label: 'last_name', placeholder: 'Enter Last Name');
                        self::input_field(label: 'email_address', for: 'email', placeholder: 'Enter Email Address');
                        self::input_field(label: 'phone_number', for: 'phone', placeholder: 'Enter Phone Number');
                    ?>
                </div>
                

                <div style="margin-bottom: 24px;">
                    <input type="checkbox" name="gift_aid" id="gift_aid" /> 
                    <label for="gift_aid" class="edp-field-labels" style="display: inline-block;">
                        Gift Aid
                        <img src="<?php echo EHX_DONATE_PLUGIN_URL ?>assets/images/gift-aid.png" alt="" srcset="">
                    </label> 
                </div>

                <div id="gift_aid_fields" style="display: none;">   
                    <div class="edp-input-fields">
                        <?php
                            self::input_field(label: 'address_line_1', placeholder: 'Address line 1', required: false);
                            self::input_field(label: 'address_line_2', placeholder: 'Address line 2', required: false);
                            self::input_field(label: 'city', placeholder: 'Enter City', required: false);
                            self::input_field(label: 'state', placeholder: 'Enter State', required: false);
                            self::input_field(label: 'country', placeholder: 'Enter Country', required: false);
                            self::input_field(label: 'post_code', placeholder: 'Enter Post Code', required: false);
                        ?>
                    </div>
                </div>

                <div>
                    <?php if($enable_recaptcha): ?>
                        <div style="margin-top: 24px;">
                            <script src="https://www.google.com/recaptcha/api.js" async defer></script>
                            <div class="g-recaptcha mb-3" id="feedback-recaptcha" data-sitekey="<?php echo esc_attr(EHX_Member_Settings::extract_setting_value('google_recaptcha_site_key')) ?>"></div>
                        </div>
                    <?php endif ?>

                    <div class="edp-donation-amounts" id="edp-pay-amounts" style="display: none;">
                        <div class="edp-donation-amount">
                            <div class="form-column">
                                <span class="form-row-title"><strong>Total amount to pay:</strong></span>
                            </div>
                            <div class="form-column">
                                <span class="form-row-value"><strong id="edp_donation_payable_amount"></strong></span>
                            </div>
                        </div>
                        <div class="edp-donation-amount" id="edp-pay-gift-aid" style="display: none;">
                            <div class="form-column">
                                <span class="form-row-title"><strong>Total amount to pay:</strong> (25%)</span>
                            </div>
                            <div class="form-column">
                                <span class="form-row-value"><strong id="edp_donation_pay"></strong></span>
                            </div>
                        </div>
                    </div>
                </div>

            </div> 
            
            <div class="edp-step-buttons"></div>

            <input type="submit" name="next" class="edp-action-btn" value="Submit" /> 
            <input type="button" name="previous" class="edp-previous-btn edp-action-btn-previous" value="Previous" />

        </fieldset>
        
        <fieldset class="edp-fieldset" <?php if(isset($payment_callback) && $payment_callback): ?> style="display: block; opacity: 1;" <?php endif ?>>
            <div class="edp-form-card">
                <div class="row">
                    <div class="col-7">
                        <h2 class="edp-fs-title">Confirmation:</h2>
                    </div>
                </div> 
                <br><br>

                <h2 class="purple-text text-center"><strong><?php echo $status == 'success' ? __('SUCCESS !', 'ehx-donate') : __('CANCEL !', 'ehx-donate'); ?></strong></h2> 
                <br>

                <div class="row justify-content-center">
                    <div class="col-7 text-center">
                        <h5 class="purple-text text-center">
                            <?php echo $status == 'success' ? __('Thank you for your generous donation!', 'ehx-donate') : __('Your donation are cancelled.', 'ehx-donate'); ?>
                        </h5>
                    </div>
                </div>

            </div>
        </fieldset>

    </form>
</div>