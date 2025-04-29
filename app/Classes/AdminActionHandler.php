<?php
declare(strict_types=1);

namespace EHxDonate\Classes;

use EHxDonate\Models\Donation;
use EHxDonate\Models\DonationItem;
use EHxDonate\Models\Transaction;
use EHxDonate\Services\Request;

if (!defined('ABSPATH')) {
    exit;
}

class AdminActionHandler
{
    /**
     * Constructor for AdminActionHandler class.
     *
     * Initializes the AdminActionHandler class and sets up necessary actions and properties.
     *
     * @return void
     */
    public function __construct() 
    {
        add_action('admin_menu', [$this, 'addMenu'], 999);

        // Hook CSV export into WordPress before any output starts
        add_action('admin_init', [$this, 'exportCSV']);

        add_action('admin_init', [$this, 'deleteTableRow']);
    }

    /**
     * Adds a new menu page to the WordPress admin dashboard.
     *
     * This function creates a new menu page with the title 'User Details' and the slug 'ehx_member_user_view'.
     * The page is associated with the 'render_user_view_page' method of the current class.
     * The menu item is visible to users with the 'manage_options' capability.
     * The menu icon is provided by the 'dashicons-admin-users' Dashicon.
     * After adding the menu page, it removes the existing 'ehx_member_user_view' menu page.
     *
     * @return void
     */
    public function addMenu()
    {
        add_submenu_page(
            AdminMenuHandler::$pages['admin'],
            esc_html__('Donor Details', 'ehx-donate'),
            esc_html__('Donor Details', 'ehx-donate'),
            'manage_options',
            AdminMenuHandler::$pages['donor_view'],
            [$this, 'renderDonorViewPage'],
        );
    }

    /**
         * Renders the donor view page.
         *
         * This function retrieves the donor ID from the request, validates it, retrieves the donor data,
         * and then includes the donor details view template. If the donor ID is invalid or the donor is not found,
         * appropriate error messages are displayed.
         * 
         * @return void
         */
        public function renderDonorViewPage() 
        {
            $user_id = Request::getInput('user_id');

            if (!$user_id) {
                echo '<div class="notice notice-error"><p>' . esc_html__('Invalid donor ID.', 'ehx-donate') . '</p></div>';
                return;
            }

            $user = get_userdata($user_id);

            if (!$user) {
                echo '<div class="notice notice-error"><p>' . esc_html__('Donor not found.', 'ehx-donate') . '</p></div>';
                return;
            }

            $userData = [
                ['label' => esc_html__('Username', 'ehx-donate'), 'value' => $user->user_login],
                ['label' => esc_html__('Email', 'ehx-donate'), 'value' => $user->user_email],
                ['label' => esc_html__('Full name', 'ehx-donate'), 'value' => $user->display_name],
                ['label' => esc_html__('Status', 'ehx-donate'), 'value' => $user->user_status ? esc_html__('Approve', 'ehx-donate') : esc_html__('Deny', 'ehx-donate')],
            ];

            $donations = (new DonationDataTable())->getData($user_id);

            require EHXDO_PLUGIN_DIR . 'views/admin/pages/donor-details.php';
        }

    /**
     * Exports donation data to CSV or Excel file.
     *
     * This function handles the export of donation data to a CSV or Excel file based on the provided parameters.
     * It checks the export parameter and the page parameter to determine the type of data to export.
     * It then sets the appropriate headers for the download, opens an output stream, adds column headers,
     * and iterates through the data to create CSV rows.
     *
     * @param string $export The export parameter passed from the admin page.
     * @param string $page The page parameter passed from the admin page.
     *
     * @return void
     */
    public function exportCSV()
    {
        $export = Request::getInput('export');

        if ($export === 'edp-csv') {

            if (!current_user_can('manage_donations')) {
                wp_die(esc_html__('Permission denied', 'ehx-donate'));
            }

            $page  = Request::getInput('page');

            if($page == AdminMenuHandler::$pages['transaction']) {
                $data = (new Transaction())->table(Transaction::$table, 't')
                    ->select(['t.*', 'd.gift_aid','u.display_name','di.recurring','p.post_title'])
                    ->leftJoin(Donation::$table, 'd.id', '=', 't.donation_id', 'd')
                    ->leftJoin('users', 'u.ID', '=', 'd.user_id', 'u')
                    ->leftJoin(DonationItem::$table, 'd.id', '=', 'di.donation_id', 'di')
                    ->leftJoin('posts', 'p.ID', '=', 'di.campaign_id', 'p')
                    ->get();

                $header_fields = [
                    'Date', 
                    'Donor', 
                    'Campaign',
                    'Amount',
                    'Status', 
                    'Type',
                ];

                $filename = 'transactions.csv';
            } 
            else {
                $data = DonationDataTable::getData(false);

                $header_fields = [
                    'Date', 
                    'Donor', 
                    'Amount',
                    'Campaign',
                    'Gift Aid', 
                    'Recurring', 
                    'Payment',
                ];

                $filename = 'donations.csv';
            }

            if (empty($data)) {
                wp_die(esc_html__('No data found to export.', 'ehx-donate'));
            }

            // Set headers for CSV download
            header('Content-Type: text/csv; charset=utf-8');
            header("Content-Disposition: attachment; filename={$filename}");
            header('Pragma: no-cache');
            header('Expires: 0');

            // // Open output stream
            $output = fopen('php://output', 'w');

            // // Add CSV column headers
            fputcsv($output, $header_fields);

            // Add data rows
            foreach ($data as $row) {
                if($page == AdminMenuHandler::$pages['transaction']) {
                    $field = [
                        wp_date('d F Y', strtotime($row->created_at)),
                        $row->display_name,
                        $row->post_title,
                        $row->amount,
                        $row->status,
                        $row->type,
                    ];
                } 
                else {
                    $field = [
                        wp_date('d F Y', strtotime($row->created_at)),
                        $row->display_name,
                        $row->total_amount,
                        $row->post_title,
                        $row->gift_aid ? 'True' : 'False',
                        $row->recurring,
                        $row->payment_status
                    ];
                }

                fputcsv($output,  $field);
            }

            fclose($output);
            exit;
        }
    }

    /**
     * Deletes a donation or transaction record from the database.
     *
     * This function handles the deletion of donation or transaction records based on the provided action and ID.
     * It checks user capabilities, verifies the action, and performs the deletion using the WordPress database API.
     *
     * @param int $id The ID of the record to be deleted.
     * @param string $action The action to be performed (ehx_donations_delete or ehx_transactions_delete).
     *
     * @return void
     */
    public function deleteTableRow()
    {
        $id = Request::getInput('id');
        $action  = Request::getInput('action');

        if(!empty($id) && !empty($action)) {
            if (!current_user_can('manage_donations', $id) || !current_user_can('manage_transactions', $id)) {
                wp_die(esc_html__('Permission denied', 'ehx-donate'));
            }
        }

        if ($action === 'ehx_donations_delete') {
            check_admin_referer("donations_delete_{$id}");

            $model = new Donation();
        }

        if ($action === 'ehx_transactions_delete') {
            check_admin_referer("transactions_delete_{$id}");

            $model = new Transaction();
        }

        if ($action === 'ehx_donor_delete') {

            check_admin_referer("donor_delete_{$id}");

            wp_delete_user($id);

            $deleted = true;
        }

        if(isset($model)) {
            $model->where('id', $id)->delete();
        }

        if(isset($deleted)) {
            $page  = Request::getInput('page');
            wp_redirect(admin_url("admin.php?page={$page}&deleted=1"));
            exit;
        }
    }
}