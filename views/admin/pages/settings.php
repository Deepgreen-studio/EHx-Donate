<div class="wrap edp-wrapper">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <nav class="nav-tab-wrapper edp-tab-wrapper">
        <?php foreach (EHX_Donate_Settings::$tabs as $key => $tab): ?>
            <a href="#<?php echo esc_attr($tab['slug']); ?>" class="nav-tab <?php echo $key == 0 ? 'nav-tab-active' : ''; ?>">
                <?php echo esc_html($tab['label']); ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="notice" id="edp-notice" style="display: none;"><p></p></div>

    <!-- Form for Settings -->
    <form id="edp_member_form_submit" action="<?php echo esc_url(admin_url('admin-ajax.php')) ?>" method="post">

        <?php wp_nonce_field(EHX_Donate_Settings::NONCE_ACTION, EHX_Donate_Settings::NONCE_NAME); ?>

        <input type="hidden" name="action" value="ehx_save_settings">

        <div class="tab-content edp-main-tab-content">

            <div id="general" class="tab-panel tab-panel-active">

                <h2><?php esc_html_e('General Settings', 'ehx-donate'); ?></h2>
                <p><?php esc_html_e("Email notifications sent from Ehx Member are listed below. Click on an email to configure it. Emails should be sent from an email using your website's domain name. We highly recommend using a SMTP service email delivery. Please see this doc for more information.", 'ehx-donate') ?></p>
                
                <table class="form-table">
                    <?php
                        foreach (EHX_Donate_Settings::get_integration_fields('general') as $field) {
                            EHX_Donate_Helper::input_group($field);
                        }
                    ?>

                    <tr valign="top">
                        <th scope="row">
                            <?php esc_html_e('Campaign Shortcode', 'ehx-donate'); ?>
                        </th>
                        <td>
                            <code>[ehx_campaigns /]</code>
                            <p class="description">
                                <?php esc_html_e('Use this shortcode to display your donation campaigns on any page or post. Simply copy and paste it into the content editor.', 'ehx-donate'); ?>
                            </p>
                            <!-- <p class="description">
                                <?php esc_html_e('You can customize the shortcode with attributes like `id`, `category`, or `style` to filter or style the campaigns. Example: `[ehx_campaigns category="education" style="grid"]`.', 'ehx-donate'); ?>
                            </p> -->
                        </td>
                    </tr>
                </table>
                
            </div>

            <div id="email" class="tab-panel">

                <h2><?php esc_html_e('Email notifications', 'ehx-donate'); ?></h2>
                <p><?php esc_html_e("Email notifications sent from Ehx Member are listed below. Click on an email to configure it. Emails should be sent from an email using your website's domain name. We highly recommend using a SMTP service email delivery. Please see this doc for more information.", 'ehx-donate') ?></p>
                
                <table class="form-table">
                    <?php
                        foreach (EHX_Donate_Settings::get_integration_fields('email') as $field) {
                            EHX_Donate_Helper::input_group($field);
                        }
                    ?>
                </table>

                <h2><?php esc_html_e('Email sender options', 'ehx-donate'); ?></h2>
                <p><?php esc_html_e("How the sender appears in outgoing Ehx Member emails.", 'ehx-donate') ?></p>
                
                <table class="form-table">
                    <?php
                        foreach (EHX_Donate_Settings::get_integration_fields('email-options') as $field) {
                            EHX_Donate_Helper::input_group($field);
                        }
                    ?>
                </table>

                <h2><?php esc_html_e('Email template', 'ehx-donate'); ?></h2>
                <p><?php esc_html_e("Section to customize email templates settings.", 'ehx-donate') ?></p>
                
                <table class="form-table">
                    <?php
                        foreach (EHX_Donate_Settings::get_integration_fields('email-template') as $field) {
                            EHX_Donate_Helper::input_group($field);
                        }
                    ?>
                </table>

            </div>

            <div id="integration" class="tab-panel">

                <ul class="subsubsub edp-sub-tab-wrapper">
                    <?php EHX_Donate_Settings::get_sub_tabs('integration') ?>
                </ul>
                <div class="clear"></div>
                
                <?php EHX_Donate_Settings::get_tab_heading_description('integration') ?>
                
                <div class="tab-content edp-sub-tab-content">

                    <div id="stripe" class="tab-panel tab-panel-active">
                        <table class="form-table">
                            <?php
                                foreach (EHX_Donate_Settings::get_integration_fields('stripe') as $field) {
                                    EHX_Donate_Helper::input_group($field);
                                }
                            ?>
                        </table>
                    </div>

                    <div id="paypal" class="tab-panel">
                        <table class="form-table">
                            <?php
                                foreach (EHX_Donate_Settings::get_integration_fields('paypal') as $field) {
                                    EHX_Donate_Helper::input_group($field);
                                }
                            ?>
                        </table>
                    </div>

                    <div id="google_recaptcha" class="tab-panel">
                        <table class="form-table">
                            <?php
                                foreach (EHX_Donate_Settings::get_integration_fields('recaptcha') as $field) {
                                    EHX_Donate_Helper::input_group($field);
                                }
                            ?>
                        </table>
                    </div>

                    <div id="google_map" class="tab-panel">
                        <table class="form-table">
                            <?php
                                foreach (EHX_Donate_Settings::get_integration_fields('map') as $field) {
                                    EHX_Donate_Helper::input_group($field);
                                }
                            ?>
                        </table>
                    </div>

                </div>
                
            </div>

        </div>

        <?php submit_button(__('Save Settings', 'ehx-donate')); ?>
    </form>
</div>
