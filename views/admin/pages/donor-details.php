<?php
    use EHxDonate\Helpers\Helper;
    use EHxDonate\Classes\AdminMenuHandler;

    $donorPage = AdminMenuHandler::$pages['donor'];

    $headings = [
        esc_html__('Date', 'ehx-donate'),
        esc_html__('Amount', 'ehx-donate'),
        esc_html__('Campaign', 'ehx-donate'),
        esc_html__('Gift Aid Enabled', 'ehx-donate'),
        esc_html__('Recurring', 'ehx-donate'),
        esc_html__('Payment', 'ehx-donate'),
    ];
?>
<div class="wrap">
    <div style="display: flex;"></div>
    <h1><?php esc_html_e('Donor Details', 'ehx-donate'); ?></h1>

    <div class="user-details">

        <div class="user-details-line">
            <?php echo get_avatar($user->ID); ?>
        </div>

        <?php foreach($userData as $key => $data): ?>
            <div class="user-details-line">
                <p><?php echo esc_html($data['label']); ?>:</p>
                <p><?php echo esc_html($data['value']); ?></p>
            </div>
        <?php endforeach ?>

    </div>
    
    <div style="display: flex;"></div>
    <h1><?php esc_html_e('Donations', 'ehx-donate'); ?></h1>

    <div style="margin: 24px 0;">
        <table class="wp-list-table widefat fixed striped table-view-list">
            <thead>
                <tr>
                    <?php foreach($headings as $heading): ?>
                        <th><?php echo esc_html($heading) ?></th>
                    <?php endforeach ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach($donations as $donation): ?>
                    <tr>
                        <td><?php echo esc_html(wp_date('d F Y', strtotime($donation->created_at))) ?></td>
                        <td><?php echo esc_html(Helper::currencyFormat($donation->total_amount)) ?></td>
                        <td><?php echo esc_html($donation->post_title ?? esc_html__('Quick Donation', 'ehx-donate')) ?></td>
                        <td><?php echo esc_html($donation->gift_aid ? 'Yes' : 'No') ?></td>
                        <td><?php echo esc_html($donation->recurring) ?></td>
                        <td>
                            <?php 
                                $status_classes = [
                                    'Pending' => 'background: orange; color: white; padding: 4px 8px; border-radius: 4px;',
                                    'Success' => 'background: green; color: white; padding: 4px 8px; border-radius: 4px;',
                                    'Cancel'  => 'background: red; color: white; padding: 4px 8px; border-radius: 4px;',
                                ];
                                echo sprintf('<span style="%s">%s</span>', $status_classes[$donation->payment_status], ucfirst($donation->payment_status));
                            ?>
                        </td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>

    <a href="<?php echo esc_url(admin_url("admin.php?page={$donorPage}")); ?>" class="button button-secondary">
        <?php esc_html_e('Back to Donor', 'ehx-donate'); ?>
    </a>
</div>