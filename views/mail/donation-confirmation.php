<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<?php
/**
 * Email Template for Donations
 * 
 * @var string $subject
 * @var string $name
 * @var string $fromName
 * @var string $total_amount
 * @var string $trx
 * @var string $home_url
 */
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title><?php echo esc_html($subject); ?></title>
</head>
<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.6; margin: 0; padding: 0;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background: #f9f9f9;">
        <h2 style="color: #0073aa; margin-top: 0;"><?php echo esc_html($subject); ?></h2>
        <p><?php 
            /* translators: %s: Donor's name */
            printf( esc_html__('Dear %s,', 'ehx-donate'), '<strong>' . esc_html($name) . '</strong>' ); 
        ?></p>

        <p><?php 
            /* translators: %s: Organization name */
            printf( esc_html__('We sincerely appreciate your generous donation to %s. Your support makes a difference.', 'ehx-donate'), 
            '<strong>' . esc_html($fromName) . '</strong>' ); 
        ?></p>

        <div style="background: #fff; padding: 15px; border-radius: 6px; margin: 15px 0;">
            <p><strong><?php esc_html_e('Donation Details:', 'ehx-donate'); ?></strong></p>
            <p><strong><?php esc_html_e('Amount:', 'ehx-donate'); ?></strong> <?php echo esc_html($total_amount); ?></p>
            <p><strong><?php esc_html_e('Date:', 'ehx-donate'); ?></strong> <?php echo esc_html(wp_date(get_option('date_format'))); ?></p>
            <p><strong><?php esc_html_e('Transaction ID:', 'ehx-donate'); ?></strong> <?php echo esc_html($trx); ?></p>
        </div>

        <p><?php esc_html_e('Your contribution helps provide essential resources to those in need.', 'ehx-donate'); ?></p>
        <p><?php esc_html_e('For tax receipts or questions, please contact us.', 'ehx-donate'); ?></p>
        <p><?php esc_html_e('Thank you for being part of our mission!', 'ehx-donate'); ?></p>

        <p><?php esc_html_e('Best regards,', 'ehx-donate'); ?><br>
        <strong><?php echo esc_html($fromName); ?></strong><br>
        <a href="<?php echo esc_url($home_url); ?>"><?php echo esc_url($home_url); ?></a></p>

        <p style="font-size: 12px; color: #666; margin-top: 20px;">
            <?php esc_html_e('This is an automated message. Please do not reply directly.', 'ehx-donate'); ?>
        </p>
    </div>
</body>
</html>