<?php
declare(strict_types=1);

namespace EHxDonate\PostTypes;

use EHxDonate\Helpers\Helper;
use EHxDonate\Models\DonationItem;
use EHxDonate\Services\Request;
use EHxDonate\Services\Response;
use EHxDonate\Services\Validator;

if (!defined('ABSPATH')) {
    exit;
}

class CampaignPostType
{
    public Response $response;
    public Validator $validator;

    public function __construct() 
    {
        // Initialize dependencies
        $this->response  = new Response();
        $this->validator = new Validator();

        // Register custom post type
        add_action('init', [$this, 'createPostType']);

        // Handle post saving
        add_action('save_post', [$this, 'savePost'], 10, 2);

        // Customize admin columns
        $this->customizeAdminColumns();

        // Disable Gutenberg and Classic Editor for 'ehxdo-campaign'
        $this->disableEditors();

        // // Remove unnecessary meta boxes
        $this->removeMetaBoxes();

        // // Customize "Add New" post title and placeholder
        $this->customizePostUI();

        add_filter('template_include', [$this, 'customizeCampaignDetails']);
    }
    
    
    /**
     * Registers the 'ehxdo-campaign' custom post type.
     *
     * @return void
     */
    public function createPostType()
    {
        register_post_type('ehxdo-campaign', [
            'label' => esc_html__("Campaigns", 'ehx-donate'),
            'description' => esc_html__("Campaigns", 'ehx-donate'),
            'labels' => [
                'label' => esc_html__('Campaigns', 'ehx-donate'),
                'singular_name' => esc_html__('Form', 'ehx-donate'),
                'menu_name'     => esc_html__('EHx Campaigns', 'ehx-donate'),
                'all_items'     => esc_html__('All Campaigns', 'ehx-donate'),
                'add_new'       => esc_html__('Add New', 'ehx-donate'),
                'add_new_item'  => esc_html__('Add New Campaign', 'ehx-donate'),
                'edit_item'     => esc_html__('Edit Campaign', 'ehx-donate'),
                'new_item'      => esc_html__('New Campaign', 'ehx-donate'),
                'view_item'     => esc_html__('View Campaign', 'ehx-donate'),
                'search_items'  => esc_html__('Search Campaigns', 'ehx-donate'),
                'not_found'     => esc_html__('No campaigns found', 'ehx-donate'),
                'not_found_in_trash' => esc_html__('No campaigns found in Trash', 'ehx-donate')
            ],
            'public' => true,
            'supports' => ['title', 'editor', 'thumbnail'],
            'hierarchical' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'menu_position' => 5,
            'menu_in_admin_bar' => true,
            'menu_in_nav_menus' => true,
            'can_export' => false,
            'has_archive' => false,
            'exclude_from_search' => false,
            'publicly_queryable' => true,
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-images-alt2',
            'taxonomies'  => ['category', 'post_tag'],
            'register_meta_box_cb' => [$this, 'addMetaBoxes'],
        ]);
    }
    
    /**
     * Customizes the display of custom columns for the 'ehxdo-campaign' post type.
     *
     * @param array $columns An associative array of column names and their display names.
     *
     * @return array The modified associative array of column names and their display names.
     *
     * The function adds three new custom columns to the 'ehxdo-campaign' post type:
     * - 'goal_amount': Displays the goal amount of the campaign.
     * - 'recurring': Indicates whether the campaign is recurring or not.
     * - 'start_and_end_date': Shows the start and end dates of the campaign.
     */
    public function cptColumns($columns)
    {
        $new_columns = array(
            'goal_amount' => __('Goal Amount', 'ehx-donate'),
            'recurring' => __('Recurring', 'ehx-donate'),
            'progress' => __('Progress', 'ehx-donate'),
            'start_and_end_date' => __('Start & End Date', 'ehx-donate'),
        );
        return array_merge($columns, $new_columns);
    }

    /**
     * Customizes the display of custom columns for the 'ehxdo-campaign' post type.
     *
     * @param string $column The name of the column being displayed.
     * @param int $post_id The ID of the current post.
     *
     * @return void
     */
    public function customColumns($column, $post_id)
    {
        $ehx_campaign = get_post_meta($post_id, '_ehx_campaign', true);

        switch ($column) {
            case 'goal_amount':
                echo esc_html(Helper::currencyFormat($ehx_campaign['goal_amount'] ?? 0));
                break;
            case 'progress':

                $sum_total_amount = (new DonationItem())->where('campaign_id', $post_id)->getSum('amount');

                // Get the goal amount
                $goal_amount = isset($ehx_campaign['goal_amount']) ? floatval($ehx_campaign['goal_amount']) : 0;

                // Calculate progress percentage
                $progress = $goal_amount > 0 ? ($sum_total_amount / $goal_amount) * 100 : 0;

                // Ensure progress does not exceed 100%
                $progress = min($progress, 100);

                // Display progress bar
                echo '<div class="edp-progress-container"><div class="edp-progress html" style="width: ' . esc_attr($progress) . '%;"></div></div>';
                echo '<p>'. esc_html(Helper::currencyFormat($sum_total_amount)) .' ('.esc_html(round($progress, 2) . '%').')</p>';
                break;
            case'start_and_end_date':
                echo esc_html($ehx_campaign['start_date']. '-'. $ehx_campaign['end_date']);
                break;
            default:
                echo esc_html($ehx_campaign[$column] ?? '');
                break;
        }
    }

    /**
     * Customizes the sortable columns for the 'ehxdo-campaign' post type.
     *
     * @param array $columns An associative array of column names and their display names.
     *
     * @return array The modified associative array of column names and their display names.
     */
    public function sortableColumns($columns)
    {
        // Add custom sortable columns for 'ehxdo-campaign' post type
        $columns['_ehx_campaign']['goal_amount'] = 'goal_amount';
        $columns['_ehx_campaign']['recurring'] = 'recurring';
        $columns['_ehx_campaign']['start_and_end_date'] = 'start_and_end_date';

        return $columns;
    }

    /**
     * Customize admin columns for 'ehxdo-campaign' post type.
     */
    protected function customizeAdminColumns()
    {
        add_filter('manage_ehxdo-campaign_posts_columns', [$this, 'cptColumns']);
        add_action('manage_ehxdo-campaign_posts_custom_column', [$this, 'customColumns'], 10, 2);
        add_filter('manage_edit-ehxdo-campaign_sortable_columns', [$this, 'sortableColumns']);
    }

    /**
     * Disable Gutenberg and Classic Editor for 'ehxdo-campaign' post type.
     */
    protected function disableEditors()
    {
        // Disable Gutenberg editor for 'ehxdo-campaign' post type.
        add_filter('use_block_editor_for_post_type', fn($enabled, $post_type) => $post_type === 'ehxdo-campaign' ? false : $enabled, 10, 2);

        // Remove Classic Editor support
        // add_action('admin_head', [$this, 'removeClassicEditorSupport']);
        // add_action('addMetaBoxes', [$this, 'removeEditorSupportOnScreen'], 100);
    }

    /**
     * Remove unnecessary meta boxes for 'ehxdo-campaign' post type.
     */
    protected function removeMetaBoxes()
    {
        add_action('do_meta_boxes', [$this, 'removeUnnecessaryMetaBoxes']);
    }

    /**
     * Customize "Add New" post title and placeholder for 'ehxdo-campaign' post type.
     */
    protected function customizePostUI()
    {
        add_filter('gettext', [$this, 'customizeAddNewPostTitle'], 10, 3);
        add_filter('enter_title_here', [$this, 'customizeTitlePlaceholder'], 10, 2);
    }

    /**
     * Remove Classic Editor support for 'ehxdo-campaign' post type.
     */
    public function removeClassicEditorSupport()
    {
        if ($this->isEhxFormScreen()) {
            remove_post_type_support('ehxdo-campaign', 'editor');
        }
    }

    /**
     * Remove editor support on the 'ehxdo-campaign' screen.
     */
    public function removeEditorSupportOnScreen()
    {
        if ($screen = get_current_screen()) {
            if ($screen->id === 'ehxdo-campaign') {
                remove_post_type_support($screen->id, 'editor');
            }
        }
    }

    /**
     * Remove unnecessary meta boxes for 'ehxdo-campaign' post type.
     */
    public function removeUnnecessaryMetaBoxes()
    {
        $meta_boxes_to_remove = [
            'generate_layout_options_meta_box' => 'side',
            // 'categorydiv' => 'side',
            // 'tagsdiv-post_tag' => 'side',
            // 'postimagediv' => 'side',
            // 'submitdiv' => 'side',
            // 'slugdiv' => 'normal',
        ];

        foreach ($meta_boxes_to_remove as $meta_box => $context) {
            remove_meta_box($meta_box, 'ehxdo-campaign', $context);
        }
    }

    /**
     * Customize "Add New" post title for 'ehxdo-campaign' post type.
     */
    public function customizeAddNewPostTitle($translated_text, $text, $domain)
    {
        if (is_admin() && get_post_type() === 'ehxdo-campaign' && $text === 'Add New Post') {
            return esc_html__('Add New Campaign', 'ehx-donate');
        }
        return $translated_text;
    }

    /**
     * Customize placeholder for title field in 'ehxdo-campaign' post type.
     */
    public function customizeTitlePlaceholder($placeholder, $post)
    {
        return $post->post_type === 'ehxdo-campaign' ? esc_html__('Enter Campaign Title', 'ehx-donate') : $placeholder;
    }

    /**
     * Register meta boxes for the 'ehxdo-campaign' post type.
     */
    public function addMetaBoxes()
    {
        $meta_boxes = [
            [
                'id' => 'ehx_campaign_banner_image_meta_box',
                'title' => esc_html__('Banner image', 'ehx-donate'),
                'context' => 'side',
                'priority' => 'low',
                'view' => 'admin/inc/banner-image.php',
            ],
            [
                'id' => 'ehx_campaign_field_meta_box',
                'title' => esc_html__('Options', 'ehx-donate'),
                'context' => 'advanced',
                'priority' => 'low',
                'view' => 'admin/inc/campaign-options.php',
                'fields' => true,
            ],
        ];

        foreach ($meta_boxes as $meta_box) {
            add_meta_box(
                $meta_box['id'],
                $meta_box['title'],
                [$this, 'renderMetaBox'],
                'ehxdo-campaign',
                $meta_box['context'],
                $meta_box['priority'],
                array_filter([
                    'view' => $meta_box['view'],
                    'fields' => $meta_box['fields'] ?? false,
                ])
            );
        }
    }

    /**
     * add inner meta boxes
     */
    public function renderMetaBox($post, $callback_args)
    {
        $args = array_map('esc_html', $callback_args['args']);

        $fields = isset($args['fields']) && $args['fields']  ? Helper::customize() : null;

        require_once EHXDO_PLUGIN_DIR . "views/{$args['view']}";
    }
    
    /**
     * check is ehx form screen or not
     *
     * @return bool
     */
    private function isEhxFormScreen(): bool
    {
        $screen = get_current_screen();
        return $screen && $screen->post_type === 'ehxdo-campaign';
    }

    /**
     * Customizes the template for the single 'ehxdo-campaign' post type.
     *
     * This function checks if the current page is a singular 'ehxdo-campaign' post type.
     * If it is, it returns the custom template located at 'EHXDO_PLUGIN_DIR/views/frontend/campaign-details.php'.
     * If it's not a singular 'ehxdo-campaign' post type, it returns the original template.
     *
     * @param string $template The original template file.
     *
     * @return string The modified template file.
     */
    public function customizeCampaignDetails($template)
    {
        if (is_singular('ehxdo-campaign')) {
            return EHXDO_PLUGIN_DIR . 'views/frontend/campaign-details.php';
        }
        return $template;
    }

    /**
     * Save post metadata for 'ehxdo-campaign' post type.
     *
     * @param int|string $post_id Post ID.
     */
    public function savePost($post_id)
    {
        // Ensure the request is for an 'ehxdo-campaign' post type
        if ((isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] != 'POST') || Request::getInput('post_type') !== 'ehxdo-campaign') {
            return;
        }

        // Verify nonce for security
        if (!(new Validator())->validate_nonce(Helper::NONCE_NAME, Helper::NONCE_NAME)) {
            return;
        }

        $request = new Request();

        // Prevent auto-saves
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Ensure the user has permission to edit the post
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Only proceed if it's an edit post action
        if ($request->input('action') !== 'editpost') {
            return;
        }

        $new_value = $request->input('_ehx_campaign');
        $old_value = get_post_meta($post_id, '_ehx_campaign', true);

        // Update only if the value has changed
        if ($new_value !== $old_value) {
            update_post_meta($post_id, '_ehx_campaign', $new_value);
        }
    }

}