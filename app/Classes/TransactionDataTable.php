<?php
declare(strict_types=1);

namespace EHxDonate\Classes;

use EHxDonate\Helpers\Helper;
use EHxDonate\Models\Donation;
use EHxDonate\Models\DonationItem;
use EHxDonate\Models\Transaction;
use EHxDonate\Services\Request;
use WP_List_Table;

if (!defined('ABSPATH')) {
    exit;
}

class TransactionDataTable extends WP_List_Table 
{ 
    /**
     * Constructor for the EHXDo_Transaction_Data_Table class.
     *
     * Initializes the parent class and sets up the necessary properties.
     *
     * @param array $args Arguments for the WP_List_Table class constructor.
     */
    public function __construct() 
    {
        parent::__construct([
            'singular' => esc_html__('Transaction', 'ehx-donate'),
            'plural'   => esc_html__('Transactions', 'ehx-donate'),
            'ajax'     => false
        ]);
    }
        
    /**
     * Get the columns for the list table.
     *
     * @return array An associative array where the keys are the column slugs and the values are the column names.
     */
    public function get_columns(): array 
    {
        return [
            'cb' => '<input type="checkbox" />',
            'created_at'   => esc_html__('Date', 'ehx-donate'),
            'display_name' => esc_html__('Donor', 'ehx-donate'),
            'post_title' => esc_html__('Campaign', 'ehx-donate'),
            'amount' => esc_html__('Amount', 'ehx-donate'),
            'status' => esc_html__('Status', 'ehx-donate'),
            'type'   => esc_html__('Type', 'ehx-donate'),
        ];
    }
        
    /**
     * Get the sortable columns for the list table.
     *
     * @return array An associative array where the keys are the column slugs and the values are arrays containing the column name and a boolean indicating whether the column is sortable.
     *
     * @since 1.0.0
     */
    public function get_sortable_columns(): array 
    {
        return [
            'display_name' => ['display_name', false],
            'status'   => ['status', false],
            'type'     => ['type', false],
            'post_title' => ['post_title', false],
            'amount'   => ['amount', false],
        ];
    }
        
    /**
     * Generate the checkbox HTML for the list table.
     *
     * This function generates the HTML for a checkbox input element that will be used in the list table.
     * The checkbox is used for selecting multiple items for bulk actions.
     *
     * @param array $item An associative array representing a single row in the list table.
     *                    The array should contain the 'id' key, which corresponds to the transaction ID.
     *
     * @return string The HTML for the checkbox input element.
     */
    public function column_cb($item): string 
    {
        return sprintf('<input type="checkbox" name="transaction[]" value="%s" />', $item['id']);
    }
        
    /**
     * Handles the output for the default columns in the list table.
     *
     * This function is called when the default columns are being displayed in the list table.
     * It checks the column name and returns the appropriate value based on the column.
     *
     * @param array $item An associative array representing a single row in the list table.
     *                    The array should contain the column slugs as keys and their corresponding values.
     * @param string $column_name The name of the column being displayed.
     *
     * @return mixed The value to be displayed in the specified column.
     */
    public function column_default($item, $column_name): mixed 
    {
        switch ($column_name) {
            case 'created_at':
                $page = AdminMenuHandler::$pages['transaction'];
                $delete_link = admin_url("admin.php?page={$page}&action=ehx_transactions_delete&id={$item['id']}");
                // $view_link = admin_url("admin.php?page={$page}&id={$item['id']}");

                $actions = [
                    'delete' => '<a href="' . esc_url(wp_nonce_url($delete_link, 'transactions_delete_' . $item['id'])) . '" onclick="return confirm(\'Are you sure?\')">Delete</a>',
                    // 'view'   => '<a href="' . esc_url($view_link) . '">' . esc_html__('View', 'ehx-donate') . '</a>'
                ];
                return wp_date('d/m/Y', strtotime($item['created_at'])) . $this->row_actions($actions);
            case 'amount':
            case 'charge':
                return Helper::currencyFormat($item[$column_name] ?? 0);
            default:
                return $item[$column_name] ?? '';
        }
    }
    
    /**
     * Handles the output for the extra navigation bar at the top of the list table.
     *
     * This function generates the HTML for the extra navigation bar at the top of the list table.
     * It includes dropdown filters for users and payment statuses.
     *
     * @param string $which The context for the extra table navigation.
     *                      It can be either 'top' or 'bottom'.
     *
     * @return void
     */
    public function extra_tablenav($which): void 
    {
        if ($which === 'top') {
            $users = get_users([
                'fields'  => ['ID', 'display_name'],
                'orderby' => 'display_name',
                'order'   => 'ASC'
            ]);
        
            $selected_user = Request::getInput('filter_user');
            ?>
            <div class="alignleft actions">
                <input type="hidden" name="page" value="<?php echo esc_html(AdminMenuHandler::$pages['transaction']) ?>">
                
                <!-- User Filter -->
                <select name="filter_user">
                    <option value=""><?php esc_html_e('All Users', 'ehx-donate') ?></option>
                    <?php foreach ($users as $user) : ?>
                        <option value="<?php echo esc_attr($user->ID); ?>" <?php selected($selected_user, $user->ID); ?>>
                            <?php echo esc_html($user->display_name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <input type="submit" class="button" value="Filter">
                <a href="?page=<?php echo esc_html(AdminMenuHandler::$pages['transaction']) ?>&per_page=-1&export=edp-csv" class="button action"><?php esc_html_e('Export', 'ehx-donate') ?></a> 
            </div>
            <?php
        }
    }
    
    /**
     * Prepares the items to be displayed in the list table.
     *
     * This function retrieves the necessary data from the database, applies filters and sorting,
     * and sets up pagination for the list table. It then populates the items property with the
     * retrieved data.
     *
     * @return void
     */
    public function prepare_items(): void 
    {
        // Get query results and pagination parameters
        [$data, $per_page, $total_items] = $this->get_query_results();

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items / $per_page),
        ]);

        $this->_column_headers = [$this->get_columns(), [], $this->get_sortable_columns()];
        $this->items = $data;
    }

    /**
     * Retrieves and prepares the data for the list table.
     *
     * This function constructs a SQL query to retrieve donation data from the database,
     * applies sorting and filtering, and sets up pagination. It then executes the query
     * and returns the results along with pagination parameters.
     *
     * @return array An array containing the retrieved data, the number of items per page, and the WHERE conditions for the query.
     */
    public function get_query_results()
    {
        $transaction = new Transaction();
        $transaction->table(Transaction::$table, 't')
            ->select(['t.*', 'd.gift_aid','u.display_name','di.recurring','p.post_title'])
            ->leftJoin(Donation::$table, 'd.id', '=', 't.donation_id', 'd')
            ->leftJoin('users', 'u.ID', '=', 'd.user_id', 'u')
            ->leftJoin(DonationItem::$table, 'd.id', '=', 'di.donation_id', 'di')
            ->leftJoin('posts', 'p.ID', '=', 'di.campaign_id', 'p');

        // Apply filters
        $filter_user = Request::getInput('filter_user');
        $filter_status = Request::getInput('filter_status');

        if ($filter_user) {
            $transaction->where('d.user_id', (int) $filter_user);
        }

        if ($filter_status) {
            $transaction->where('t.payment_status', $filter_status);
        }

        // Handle sorting
        $valid_orderby = [
            'id' => 't.id',
            'amount' => 't.amount',
            'created_at' => 't.created_at',
            'display_name' => 'u.display_name',
        ];
        
        $orderby = $valid_orderby[Request::getInput('orderby', 'id')] ?? 't.id';

        $order_input = strtoupper(Request::getInput('order', 'DESC'));
        $order = in_array($order_input, ['ASC', 'DESC']) ? $order_input : 'DESC';

        $transaction->orderBy($orderby, $order);

        // Get total count before pagination
        $total_items = $transaction->getCount();

        // Handle pagination
        $per_page = Request::getInput('per_page', 10);
        $current_page = $this->get_pagenum();
        $offset = ($current_page - 1) * $per_page;

        if ($per_page !== -1) {
            $transaction->limit($per_page)->offset($offset);
        }

        // Execute query and format results
        $data = $transaction->get(ARRAY_A);

        return [
            $data,
            $per_page,
            $total_items,
            $current_page
        ];
    }

}