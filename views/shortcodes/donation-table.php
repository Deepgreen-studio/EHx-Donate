<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<div>
    <p><?php esc_html_e('Donations.', 'ehx-donate') ?></p>

    <table class="edp-datatable">
        <thead>
            <tr>
                <th><?php esc_html_e('Date & Time', 'ehx-donate') ?></th>
                <th><?php esc_html_e('Amount', 'ehx-donate') ?></th>
                <th><?php esc_html_e('Campaign', 'ehx-donate') ?></th>
                <th><?php esc_html_e('Gift Aid', 'ehx-donate') ?></th>
                <th><?php esc_html_e('Recurring', 'ehx-donate') ?></th>
                <th><?php esc_html_e('Payment Status', 'ehx-donate') ?></th>
            </tr>
        </thead>

        <tbody>
            <?php if(count($donations) > 0): ?>
                <?php foreach($donations as $donation): ?>
                    <tr>
                        <td><?php echo esc_html(wp_date('d F Y', strtotime($donation->created_at))) ?></td>
                        <td><?php echo esc_html(EHxMember_Helper::currencyFormat($donation->total_amount)) ?></td>
                        <td><?php echo esc_html($donation->post_title) ?></td>
                        <td><?php echo esc_html($donation->gift_aid ? 'True':'False') ?></td>
                        <td><?php echo esc_html($donation->recurring) ?></td>
                        <td><?php echo esc_html($donation->payment_status) ?></td>
                    </tr>
                <?php endforeach ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center;"><?php esc_html_e('Data not found :)', 'ehx-donate') ?></td>
                </tr>
            <?php endif ?>
        </tbody>
        
    </table>
</div>