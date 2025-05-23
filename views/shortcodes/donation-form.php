<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<?php
    use EHxDonate\Classes\Settings;
    use EHxDonate\Helpers\Helper;
    use EHxDonate\Shortcodes\DonationFormShortcode;

    $transient = get_transient(Settings::TRANSIENT);
?>

<?php include EHXDO_PLUGIN_DIR . 'views/frontend/components/toast-alert.php'; ?>

<div class="edp-card" id="edp-card-element">
    
    <form id="ehxdo_form_submit" class="edp-form" method="POST">

        <input type="hidden" name="callback" value="<?php echo esc_html($callback); ?>">

        <input type="hidden" name="action" value="<?php echo esc_html(DonationFormShortcode::NONCE_ACTION); ?>">
        <?php wp_nonce_field(DonationFormShortcode::NONCE_ACTION, Helper::NONCE_NAME); ?>

        <input type="hidden" name="amount" id="amount" value="0" />

        <ul id="progressbar" class="edp-progressbar">
            <li class="edp-progress-active" id="donation" data-step="<?php esc_html_e('1', 'ehx-donate') ?>"><strong><?php esc_html_e('Donation', 'ehx-donate') ?></strong></li>
            <li id="personal" data-step="<?php esc_html_e('2', 'ehx-donate') ?>"><strong><?php esc_html_e('Personal', 'ehx-donate') ?></strong></li>
        </ul>
        <br>

        <fieldset class="edp-fieldset">
            <div class="edp-form-card">
                <div class="row">
                    <div class="col-7">
                        <h2 class="edp-fs-title"><?php esc_html_e('Donation Information', 'ehx-donate') ?>:</h2>
                    </div>
                </div> 

                <div class="edp-input-fields" style="margin-bottom: 40px;">
                    <?php
                        if (count($campaigns)) {
                            DonationFormShortcode::inputField(label: 'campaign', isType: 'select', placeholder: esc_html__('Select campaign', 'ehx-donate'), data: $campaigns, column: 'edp-field-full');
                            echo '<p id="edp__donation__message" style="display: none;color:red;">'. esc_html__('Please select required fields.', 'ehx-donate') .'</p>';
                        }
                        else {
                            echo "<input type='hidden' name='campaign' id='campaign' value='".esc_html($post->post_name)."' />";
                            echo '<p id="edp__donation__message" style="display: none;color:red;">'. esc_html__('Please select one option.', 'ehx-donate') .'</p>';
                        }

                        if(defined('EHXRD_VERSION')) {
                            $recurring = \EHxRecurringDonation\Helpers\Helper::getRecurring();
                            DonationFormShortcode::inputField(label: esc_html__('How often would you like to give?', 'ehx-donate'), for: 'recurring', isType: 'select', data: $recurring, column: 'edp-field-full');
                        }
                    ?>
                </div>

                <div>
                    <label class="edp-field-labels"><?php esc_html_e('Now choose how much', 'ehx-donate') ?>.</label>
                    <div class="edp-plan-lists">
                        <?php foreach([10,20,30,50,100] as $value): ?>
                            <div class="edp-plan-list" data-amount="<?php echo esc_html($value) ?>">
                                <div class="">
                                    <span class="edp-plan-list-currency"><?php echo esc_html($transient->currency?->symbol) ?></span>
                                    <span class="edp-plan-list-price"><?php echo esc_html($value) ?></span>
                                </div>

                                <span class="edp-plan-list-text"><?php esc_html_e('One-off', 'ehx-donate') ?></span>
                            </div>
                        <?php endforeach ?>

                        <div class="edp-plan-list edp-plan-list-custom-input">

                            <span class="edp-plan-list-currency" style="display: none;"><?php echo esc_html($transient->currency?->symbol) ?></span>

                            <div class="edp-plan-list-form-field">
                                <input type="text" area-label="<?php esc_html_e('Custom', 'ehx-donate') ?>" placeholder="<?php esc_html_e('Custom', 'ehx-donate') ?>" class="is-empty">
                            </div>

                            <span class="edp-plan-list-text"><?php esc_html_e('One-off', 'ehx-donate') ?></span>
                        </div>
                        
                    </div>
                </div>

                <div class="edp-donation-amounts" id="edp-donation-amounts" style="display: none;">
                    <div class="edp-donation-amount">
                        <div class="form-column">
                            <span class="form-row-title"><strong><?php esc_html_e('Total Payable Amount', 'ehx-donate') ?>:</strong></span>
                        </div>
                        <div class="form-column">
                            <span class="form-row-value" id="edp_donation_amount"><strong></strong></span>
                        </div>
                    </div>
                    <div class="edp-donation-amount">
                        <div class="form-column">
                            <span class="form-row-title"><strong><?php esc_html_e('Final Payable with Fee', 'ehx-donate') ?>:</strong></span>
                        </div>
                        <div class="form-column">
                            <span class="form-row-value" id="edp_donation_pay"><strong></strong></span>
                        </div>
                    </div>
                </div>

            </div> 

            <div class="edp-step-buttons"></div>
            <input type="button" name="next" class="edp-next-btn edp-action-btn" value="<?php esc_attr_e('Next') ?>" />

        </fieldset>

        <fieldset class="edp-fieldset">
            <div class="edp-form-card">
                <div class="row">
                    <div class="col-7">
                        <h2 class="edp-fs-title"><?php esc_html_e('Personal Information', 'ehx-donate') ?>:</h2>
                    </div>
                </div>

                <p id="edp__personal__message" style="display: none;color:red;"><?php esc_html_e('Please fill up required fields.', 'ehx-donate') ?></p>
                
                <div class="edp-input-fields">
                    <?php
                        DonationFormShortcode::inputField(label: __('Title', 'ehx-donate'), for: 'title', isType: 'select', placeholder: __('Select title', 'ehx-donate'), data: [__('Mr', 'ehx-donate'), __('Ms', 'ehx-donate'), __('Mrs', 'ehx-donate'), __('Miss', 'ehx-donate'), __('Dr', 'ehx-donate')], column: 'edp-field-full');
                        DonationFormShortcode::inputField(label: __('First Name', 'ehx-donate'), for: 'first_name', placeholder: __('Enter First Name', 'ehx-donate'));
                        DonationFormShortcode::inputField(label: __('Last Name', 'ehx-donate'), for: 'last_name', placeholder: __('Enter Last Name', 'ehx-donate'));
                        DonationFormShortcode::inputField(label: __('Email Address', 'ehx-donate'), for: 'email', placeholder: __('Enter Email Address', 'ehx-donate'));
                        DonationFormShortcode::inputField(label: __('Phone Number', 'ehx-donate'), for: 'phone', placeholder: __('Enter Phone Number', 'ehx-donate'));
                    ?>

                    <?php if($enable_gift_aid): ?>
                        <div class="edp-field-100 edp-input-checkbox">
                            <input type="checkbox" name="gift_aid" id="gift_aid" /> 
                            <label for="gift_aid" class="edp-field-labels" style="display: inline-block;">
                                <?php esc_html_e('Gift Aid', 'ehx-donate') ?>
                                <img src="<?php echo esc_url(EHXDO_PLUGIN_URL .'assets/images/gift-aid.png') ?>" style="width: 70px;">
                            </label> 
                        </div>
                    <?php endif ?>
                </div>

                <?php if($enable_gift_aid): ?>
                    <div id="gift_aid_fields" style="display: none;">   
                        <div class="edp-input-fields">
                            <?php
                                DonationFormShortcode::inputField(label: __('Address line 1', 'ehx-donate'), for: 'address_line_1', placeholder: __('Address line 1', 'ehx-donate'), required: false);
                                DonationFormShortcode::inputField(label: __('Address line 2', 'ehx-donate'), for: 'address_line_2', placeholder: __('Address line 2', 'ehx-donate'), required: false);
                                DonationFormShortcode::inputField(label: __('City', 'ehx-donate'), for: 'city', placeholder: __('Enter City', 'ehx-donate'), required: false);
                                DonationFormShortcode::inputField(label: __('State', 'ehx-donate'), for: 'state', placeholder: __('Enter State', 'ehx-donate'), required: false);
                                DonationFormShortcode::inputField(label: __('Country', 'ehx-donate'), for: 'country', placeholder: __('Enter Country', 'ehx-donate'), required: false);
                                DonationFormShortcode::inputField(label: __('Post Code', 'ehx-donate'), for: 'post_code', placeholder: __('Enter Post Code', 'ehx-donate'), required: false);
                            ?>
                        </div>
                    </div>
                <?php endif ?>

                <div>

                    <?php if($enable_recaptcha): ?>
                        <div style="margin-top: 24px;">
                            <div class="g-recaptcha mb-3" id="feedback-recaptcha" data-sitekey="<?php echo esc_attr(Settings::extractSettingValue('google_recaptcha_site_key')) ?>"></div>
                        </div>
                    <?php endif ?>

                    <div class="edp-donation-amounts" id="edp-pay-amounts" style="display: none;">
                        <div class="edp-donation-amount">
                            <div class="form-column">
                                <span class="form-row-title"><strong><?php esc_html_e('Final Payable with Fee', 'ehx-donate') ?>:</strong></span>
                            </div>
                            <div class="form-column">
                                <span class="form-row-value"><strong id="edp_donation_payable_amount"></strong></span>
                            </div>
                        </div>
                        <div class="edp-donation-amount" id="edp-pay-gift-aid" style="display: none;">
                            <div class="form-column">
                                <span class="form-row-title"><strong><?php esc_html_e('Your Contribution with Gift Aid', 'ehx-donate') ?>:</strong></span>
                            </div>
                            <div class="form-column">
                                <span class="form-row-value"><strong id="edp_donation_pay"></strong></span>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if(defined('EHXMC_VERSION')): ?>
                    <div class="edp-input-fields">
                        <div class="edp-field-100 edp-input-checkbox">
                            <input type="checkbox" name="subscribe_newsletter" id="subscribe_newsletter" /> 
                            <label for="subscribe_newsletter" class="edp-field-labels" style="display: inline-block;">
                                <?php esc_html_e('Get updates, promotions, and exclusive content via email. We respect your privacy.', 'ehx-donate') ?>
                            </label> 
                        </div>
                    </div>
                <?php endif ?>

            </div> 
            
            <div class="edp-step-buttons"></div>
            
            <button type="submit" class="edp-action-btn" data-submit="<?php esc_html_e('Please Wait...', 'ehx-donate') ?>">
                <div class="ehx-btn-loader" id="ehx-loader"></div>
                <span id="ehx-btn-text"><?php esc_html_e('Submit', 'ehx-donate') ?></span>
            </button>
            <input type="button" name="previous" class="edp-previous-btn edp-action-btn-previous" value="<?php esc_attr_e('Previous') ?>" />

        </fieldset>

    </form>

    <?php if(isset($payment_callback) && $payment_callback): ?>
        <div id="edp-callback-modal" class="edp-modal-window edp-modal-active edp-callback-modal">
            <div class="edp-modal-dialog edp-modal-sm">
                <div class="edp-modal-content">

                    <div class="edp-modal-header">
                        <a href="#" title="Close" class="edp-modal-close" onclick="event.preventDefault();"><?php esc_html_e('Close', 'ehx-donate') ?></a>
                    </div>
                    
                    <div class="edp-modal-body">
                        <div class="edp-modal-icon">
                            <?php if($status == 'success'): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                </svg>
                            <?php else: ?>
                                <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x-circle">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="15" y1="9" x2="9" y2="15"></line>
                                    <line x1="9" y1="9" x2="15" y2="15"></line>
                                </svg>
                            <?php endif ?>
                        </div>

                        <h3><?php echo $status == 'success' ? esc_html__('SUCCESS !', 'ehx-donate') : esc_html__('CANCEL !', 'ehx-donate'); ?></h3>
                        <div>
                            <?php echo $status == 'success' ? esc_html__('Thank you for your generous donation!', 'ehx-donate') : esc_html__('Your donation are cancelled.', 'ehx-donate'); ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    <?php endif ?>
</div>