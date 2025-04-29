<?php
declare(strict_types=1);

namespace EHxDonate\Classes;

use EHxDonate\Helpers\Helper;
use EHxDonate\Models\Donation;
use EHxDonate\Models\Model;
use EHxDonate\Services\Request;
use WP_List_Table;

if (!defined('ABSPATH')) {
    exit;
}

class DonorDataTable extends WP_List_Table 
{   
    /**
     * Constructor for the DonorDataTable class.
     *
     * Initializes the parent class and sets up the necessary properties.
     *
     * @param array $args Arguments for the WP_List_Table class constructor.
     */
    public function __construct() 
    {
        parent::__construct([
            'singular' => esc_html__('Donor', 'ehx-donate'),
            'plural'   => esc_html__('Donors', 'ehx-donate'),
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
            'user_login' => esc_html__('Username', 'ehx-donate'),
            'display_name'     => esc_html__('Name', 'ehx-donate'),
            'user_email'    => esc_html__('Email', 'ehx-donate'),
            'user_registered'    => esc_html__('Registered', 'ehx-donate'),
            'user_status' => esc_html__('Status', 'ehx-donate'),
            'total_donated' => esc_html__('Total Amount', 'ehx-donate'),
            'donation_count' => esc_html__('Success Donation', 'ehx-donate'),
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
            'user_login'     => ['user_login', false],
            'user_email'     => ['user_email', false],
            'display_name'   => ['display_name', false],
            'user_registered'    => ['user_registered', false],
            'total_donated'  => ['total_donated', false],
            'donation_count' => ['donation_count', false],
        ];
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
    public function column_default($item, $column_name) 
    {
        switch ($column_name) {
            case 'user_login':
                $donor = AdminMenuHandler::$pages['donor'];
                $donor_view = AdminMenuHandler::$pages['donor_view'];

                $avatar = get_avatar($item['ID'], 32); // Get user avatar (size: 32px)
                $edit_link = get_edit_user_link($item['ID']);
                $delete_link = admin_url("admin.php?page={$donor}&action=ehx_donor_delete&id={$item['ID']}");

                $view_link = admin_url("admin.php?page={$donor_view}&user_id={$item['ID']}");

                $actions = [
                    'edit'   => '<a href="' . esc_url($edit_link) . '">Edit</a>',
                    'delete' => '<a href="' . esc_url(wp_nonce_url($delete_link, 'donor_delete_' . $item['ID'])) . '" onclick="return confirm(\'Are you sure?\')">Delete</a>',
                    'view'   => '<a href="' . esc_url($view_link) . '">' . esc_html__('View', 'ehx-donate') . '</a>'
                ];

                return '<div style="display:flex; align-items:center; gap:10px;">' . 
                            $avatar . 
                            '<strong>' . esc_html($item['user_login']) . '</strong>' . 
                    '</div>' . 
                    $this->row_actions($actions);
            case 'user_status': 
                $current_status = $item['user_status'] ? esc_html__('Approve', 'ehx-donate') : esc_html__('Deny', 'ehx-donate');
                return esc_html($current_status);
            case 'total_donated':
                return Helper::currencyFormat($item[$column_name] ?? 0);
            case 'user_registered':
                return wp_date('d F Y', strtotime($item[$column_name]));
            default:
                return $item[$column_name];
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
            ?>
                <div class="alignleft actions">
                    <input type="hidden" name="page" value="<?php echo esc_html(AdminMenuHandler::$pages['donor']) ?>">
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
        // Initialize model with users table
        $model = (new Model('users'))
            ->table('users', 'u')
            ->select(['u.*', 'COUNT(d.id) as donation_count', 'SUM(d.total_amount) as total_donated'])
            ->join(Donation::$table, 'u.ID', '=', 'd.user_id', 'd')
            ->where('d.user_id', '!=', 'NULL')
            ->where('d.payment_status', 'Success')
            ->groupBy('u.ID');

        // Handle search filter
        if ($filter_search = Request::getInput('s')) {
            $search_term = '%' . $filter_search . '%';
            $model->where('u.user_login', 'LIKE', $search_term)
                ->orWhere('u.user_email', 'LIKE', $search_term)
                ->orWhere('u.display_name', 'LIKE', $search_term)
                ->orWhere('u.ID', 'LIKE', $search_term);
        }

        // Handle sorting
        $valid_orderby = [
            'id' => 'u.ID',
            'user_login' => 'u.user_login',
            'user_email' => 'u.user_email',
            'display_name' => 'u.display_name',
            'user_registered' => 'u.user_registered',
            'total_donated' => 'SUM(d.total_amount)',
            'donation_count' => 'COUNT(d.id)',
        ];

        $orderby = $valid_orderby[Request::getInput('orderby', 'id')] ?? 'u.ID';
        $order = strtoupper(Request::getInput('order', 'DESC'));
        $order = in_array($order, ['ASC', 'DESC']) ? $order : 'DESC';
        
        $model->orderBy($orderby, $order);

        // Get total count
        $count_model = clone $model;
        $total_items = $count_model->getCount();

        // Handle pagination
        $per_page = Request::getInput('per_page', 10);
        $current_page = $this->get_pagenum();
        
        if ($per_page !== -1) {
            $model->limit($per_page)
                ->offset(($current_page - 1) * $per_page);
        }

        // Execute query
        $data = $model->get(ARRAY_A);

        return [$data, $per_page, $total_items];
    }

}