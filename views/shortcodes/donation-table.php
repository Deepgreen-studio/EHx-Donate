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
            <?php foreach($donations as $donation): ?>
                <tr>
                    <td><?php echo esc_html(wp_date('d F Y', strtotime($donation->created_at))) ?></td>
                    <td><?php echo esc_html(EHX_Helper::currencyFormat($donation->total_amount)) ?></td>
                    <td><?php echo esc_html($donation->post_title) ?></td>
                    <td><?php echo esc_html($donation->gift_aid ? 'True':'False') ?></td>
                    <td><?php echo esc_html($donation->recurring) ?></td>
                    <td><?php echo esc_html($donation->payment_status) ?></td>
                </tr>
            <?php endforeach ?>
        </tbody>
        
    </table>
</div>