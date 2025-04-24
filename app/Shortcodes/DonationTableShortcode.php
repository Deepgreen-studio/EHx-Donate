<?php
declare(strict_types=1);

namespace EHxDonate\Shortcodes;

use EHxDonate\Classes\DonationDataTable;
use EHxDonate\Classes\View;

if (!defined('ABSPATH')) {
    exit;
}

class DonationTableShortcode
{
    /**
     * Initializes the donation table shortcode.
     *
     * This method adds the 'ehxdo_donation_table' shortcode to WordPress,
     * which triggers the 'add_shortcode' method when used in content.
     */
    public function __construct()
    {
        add_shortcode('ehxdo_donation_table', [$this, 'addShortcode']);
    }

    /**
     * Adds the donation table shortcode.
     *
     * This function retrieves donation data from the database, includes the donation table view,
     * enqueues necessary CSS and JavaScript files, and returns the rendered HTML.
     *
     * @return string The rendered HTML of the donation table.
     */
    public function addShortcode()
    {
        $donations = DonationDataTable::getData();

        wp_enqueue_style('ehxdo-datatable');
        wp_enqueue_script('ehxdo-datatable');

        $content = View::render('shortcodes/donation-table', ['donations' => $donations], true);

        return $content;
    }

}