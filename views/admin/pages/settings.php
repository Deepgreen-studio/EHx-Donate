<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<div class="wrap edp-wrapper">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <nav class="nav-tab-wrapper edp-tab-wrapper">
        <?php foreach (EHXDo_Settings::$tabs as $key => $tab): ?>
            <a href="#<?php echo esc_attr($tab['slug']); ?>" class="nav-tab <?php echo $key == 0 ? 'nav-tab-active' : ''; ?>">
                <?php echo esc_html($tab['label']); ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="notice" id="edp-notice" style="display: none;"><p></p></div>

    <!-- Form for Settings -->
    <form id="edp_donate_form_submit" action="<?php echo esc_url(admin_url('admin-ajax.php')) ?>" method="post">

        <?php wp_nonce_field(EHXDo_Settings::NONCE_ACTION, EHXDo_Settings::NONCE_NAME); ?>

        <input type="hidden" name="action" value="ehxdo_save_settings">

        <div class="tab-content edp-main-tab-content">

            <div id="general" class="tab-panel tab-panel-active">

                <h2><?php esc_html_e('General Settings', 'ehx-donate'); ?></h2>
                <p><?php esc_html_e("Email notifications sent from Ehx Member are listed below. Click on an email to configure it. Emails should be sent from an email using your website's domain name. We highly recommend using a SMTP service email delivery. Please see this doc for more information.", 'ehx-donate') ?></p>
                
                <table class="form-table">
                    <?php
                        foreach (EHXDo_Settings::get_integration_fields('general') as $field) {
                            EHXDo_Helper::input_group($field);
                        }
                    ?>

                    <tr valign="top">
                        <th scope="row">
                            <?php esc_html_e('Donation Form Shortcode', 'ehx-donate'); ?>
                        </th>
                        <td>
                            <code>[ehxdo_donation_form /]</code>
                            <p class="description">
                                <?php esc_html_e('Insert this shortcode on any page or post to display the donation form. Simply copy and paste it into the content editor.', 'ehx-donate'); ?>
                            </p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">
                            <?php esc_html_e('Donation Table Shortcode', 'ehx-donate'); ?>
                        </th>
                        <td>
                            <code>[ehxdo_donation_table /]</code>
                            <p class="description">
                                <?php esc_html_e('Insert this shortcode on any page or post to display the donation table. Simply copy and paste it into the content editor.', 'ehx-donate'); ?>
                            </p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">
                            <?php esc_html_e('Campaign Lists Shortcode', 'ehx-donate'); ?>
                        </th>
                        <td>
                            <code>[ehxdo_campaign_lists /]</code>
                            <p class="description">
                                <?php esc_html_e('Insert this shortcode on any page or post to display the donation campaigns. Simply copy and paste it into the content editor.', 'ehx-donate'); ?>
                            </p>
                            <p class="description">
                                <?php esc_html_e('Available parameters:', 'ehx-donate'); ?>
                                <ul>
                                    <li><code>posts_per_page</code> - <?php esc_html_e('Number of campaigns to display (default: 6).', 'ehx-donate'); ?></li>
                                    <li><code>order</code> - <?php esc_html_e('Sorting order (ASC or DESC, default: DESC).', 'ehx-donate'); ?></li>
                                    <li><code>orderby</code> - <?php esc_html_e('Field to order by (e.g., date, title, default: date).', 'ehx-donate'); ?></li>
                                    <!-- <li><code>category</code> - <?php esc_html_e('Filter by category slug.', 'ehx-donate'); ?></li>
                                    <li><code>taxonomy</code> - <?php esc_html_e('Custom taxonomy to filter campaigns.', 'ehx-donate'); ?></li>
                                    <li><code>terms</code> - <?php esc_html_e('Comma-separated term slugs for taxonomy filtering.', 'ehx-donate'); ?></li>
                                    <li><code>meta_key</code> - <?php esc_html_e('Meta key for custom field filtering.', 'ehx-donate'); ?></li>
                                    <li><code>meta_value</code> - <?php esc_html_e('Meta value for custom field filtering.', 'ehx-donate'); ?></li>
                                    <li><code>meta_compare</code> - <?php esc_html_e('Comparison operator for meta query (default: =).', 'ehx-donate'); ?></li> -->
                                    <li><code>exclude</code> - <?php esc_html_e('Comma-separated post IDs to exclude.', 'ehx-donate'); ?></li>
                                    <li><code>include</code> - <?php esc_html_e('Comma-separated post IDs to include.', 'ehx-donate'); ?></li>
                                    <li><code>columns</code> - <?php esc_html_e('Number of columns for grid layout (default: 2).', 'ehx-donate'); ?></li>
                                    <!-- <li><code>layout</code> - <?php esc_html_e('Layout type (grid or list, default: grid).', 'ehx-donate'); ?></li> -->
                                    <li><code>image_size</code> - <?php esc_html_e('Image size for thumbnails (default: thumbnail).', 'ehx-donate'); ?></li>
                                    <li><code>show_excerpt</code> - <?php esc_html_e('Show excerpt (true or false, default: true).', 'ehx-donate'); ?></li>
                                    <li><code>excerpt_length</code> - <?php esc_html_e('Number of words in excerpt (default: 10).', 'ehx-donate'); ?></li>
                                    <li><code>show_button</code> - <?php esc_html_e('Show button (true or false, default: true).', 'ehx-donate'); ?></li>
                                    <li><code>button_text</code> - <?php esc_html_e('Text for button (default: Donate Now).', 'ehx-donate'); ?></li>
                                    <li><code>pagination</code> - <?php esc_html_e('Enable pagination (true or false, default: true).', 'ehx-donate'); ?></li>
                                </ul>
                            </p>
                        </td>
                    </tr>

                </table>
                
            </div>

            <div id="email" class="tab-panel">

                <h2><?php esc_html_e('Email notifications', 'ehx-donate'); ?></h2>
                <p><?php esc_html_e("Email notifications sent from Ehx Donate are listed below. Click on an email to configure it. Emails should be sent from an email using your website's domain name. We highly recommend using a SMTP service email delivery. Please see this doc for more information.", 'ehx-donate') ?></p>
                
                <table class="form-table">
                    <?php
                        foreach (EHXDo_Settings::get_integration_fields('email') as $field) {
                            EHXDo_Helper::input_group($field);
                        }
                    ?>
                </table>

                <h2><?php esc_html_e('Email sender options', 'ehx-donate'); ?></h2>
                <p><?php esc_html_e("How the sender appears in outgoing Ehx donate emails.", 'ehx-donate') ?></p>
                
                <table class="form-table">
                    <?php
                        foreach (EHXDo_Settings::get_integration_fields('email-options') as $field) {
                            EHXDo_Helper::input_group($field);
                        }
                    ?>
                </table>

                <h2><?php esc_html_e('Email template', 'ehx-donate'); ?></h2>
                <p><?php esc_html_e("Section to customize email templates settings.", 'ehx-donate') ?></p>
                
                <table class="form-table">
                    <?php
                        foreach (EHXDo_Settings::get_integration_fields('email-template') as $field) {
                            EHXDo_Helper::input_group($field);
                        }
                    ?>
                </table>

            </div>

            <div id="integration" class="tab-panel">

                <ul class="subsubsub edp-sub-tab-wrapper">
                    <?php EHXDo_Settings::get_sub_tabs('integration') ?>
                </ul>
                <div class="clear"></div>
                
                <?php EHXDo_Settings::get_tab_heading_description('integration') ?>
                
                <div class="tab-content edp-sub-tab-content">

                    <div id="stripe" class="tab-panel tab-panel-active">
                        <table class="form-table">
                            <?php
                                foreach (EHXDo_Settings::get_integration_fields('stripe') as $field) {
                                    EHXDo_Helper::input_group($field);
                                }
                            ?>
                        </table>
                    </div>

                    <div id="paypal" class="tab-panel">
                        <table class="form-table">
                            <?php
                                foreach (EHXDo_Settings::get_integration_fields('paypal') as $field) {
                                    EHXDo_Helper::input_group($field);
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
