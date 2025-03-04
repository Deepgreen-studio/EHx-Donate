<?php


class EHX_Donate_Cron_Job
{
    /**
     * Initializes the EHX Donate Cron Job class.
     *
     * This class handles the scheduling and execution of recurring donations.
     * It adds two actions to the WordPress action hook: 'wp' and 'ehx_donate_cron_event'.
     * The 'wp' action triggers the 'ehx_custom_cron_job' method, which schedules the 'ehx_donate_cron_event' action to run hourly.
     * The 'ehx_donate_cron_event' action triggers the 'ehx_donate_cron_task' method, which processes recurring donations.
     */
    public function __construct() 
    {
        add_action('wp', [$this, 'ehx_custom_cron_job']);

        add_action('ehx_donate_cron_event', [$this, 'ehx_donate_cron_task']);
    }

    /**
     * Schedules the 'ehx_donate_cron_event' action to run hourly if it's not already scheduled.
     *
     * This function checks if the 'ehx_donate_cron_event' action is scheduled. If it's not, it schedules the action to run hourly using the WordPress wp_schedule_event function.
     *
     * @return void
     */
    public function ehx_custom_cron_job() 
    {
        if (!wp_next_scheduled('ehx_donate_cron_event')) {
            wp_schedule_event(time(), 'hourly', 'ehx_donate_cron_event');
        }
    }

    /**
    * Processes recurring donations and updates the subscription next payment date.
    *
    * This function retrieves subscriptions due for payment today, processes each subscription,
    * and updates the subscription's next payment date. It also logs any exceptions that occur during the process.
    *
    * @global wpdb $wpdb WordPress database object.
    *
    * @return void
    */
    public function ehx_donate_cron_task() 
    {
        global $wpdb;

        $donation_table = EHX_Donate::$donation_table;
        $donation_items_table = EHX_Donate::$donation_items_table;
        $subscription_table = EHX_Donate::$subscription_table;

        $query = "SELECT s.*, di.subscription_id, di.processing_fee, di.campaign_id, d.id as donation_id, d.total_amount, o.gift_aid 
            FROM $subscription_table s
            LEFT JOIN $donation_items_table di ON s.id = di.subscription_id
            LEFT JOIN $donation_table d ON di.donation_id = d.id
            WHERE DATE(s.next_payment_date) = CURDATE()
        ";

        $subscriptions = $wpdb->get_results($query);

        foreach ($subscriptions as $subscription) {
            try {
                // Extract subscription name and details
                // $subscription_name = explode(' -> ', $subscription->subscription_name);

                // Get campaign
                // $campaign = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}campaigns WHERE title = %s LIMIT 1", $type));

                // if ($campaign) {
                //     $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}campaigns SET amount_raised = amount_raised + %f WHERE id = %d", $subscription->amount, $campaign->id));
                // }

                // Insert new donation item record
                $wpdb->insert(EHX_Donate::$donation_items_table, [
                    'donation_id' => $subscription->donation_id,
                    'campaign_id' => $subscription->campaign_id,
                    'subscription_id' => $subscription->id,
                    'amount'  => $subscription->total_amount,
                    'gift_aid' => $subscription->gift_aid,
                    'recurring' => $subscription->recurring,
                    'status' => 1,
                    'created_at' => wp_date('Y-m-d H:i:s'),
                ]);

                // Determine next payment date
                $next_payment_date = match($subscription->recurring) {
                    'weekly' => date('Y-m-d H:i:s', strtotime('+1 week')),
                    'quarterly' => date('Y-m-d H:i:s', strtotime('+3 months')),
                    'yearly' => date('Y-m-d H:i:s', strtotime('+1 year')),
                    default => date('Y-m-d H:i:s', strtotime('+1 month')),
                };

                // Update subscription next payment date
                $wpdb->update($subscription_table, ['next_payment_date' => $next_payment_date], ['id' => $subscription->id]);

            } catch (Exception $e) {
                error_log('Exception: ' . $e->getMessage());
            }
        }

        error_log("EHX Cron Job Executed at: " . current_time('mysql'));
    }
}
