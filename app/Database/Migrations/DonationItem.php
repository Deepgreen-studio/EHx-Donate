<?php
declare(strict_types=1);

namespace EHxDonate\Database\Migrations;

use EHxDonate\Database\DBMigrator;
use EHxDonate\Models\DonationItem as ModelsDonationItem;

if (!defined('ABSPATH')) {
    exit;
}

class DonationItem
{
    /**
     * Migrate the table.
     *
     * @return bool
     */
    public static function migrate($force = false): bool
    {
        global $wpdb;

        $charsetCollate = $wpdb->get_charset_collate();

        $table_name = $wpdb->prefix . ModelsDonationItem::$table;

        $sql = "CREATE TABLE $table_name (
            id INT(11) NOT NULL AUTO_INCREMENT,
            donation_id BIGINT UNSIGNED DEFAULT NULL,
            campaign_id BIGINT UNSIGNED DEFAULT NULL,
            subscription_id BIGINT UNSIGNED DEFAULT NULL,
            is_zakat TINYINT(1) DEFAULT 0,
            amount DECIMAL(8,2) DEFAULT NULL,
            gift_aid TINYINT(1) DEFAULT 0,
            recurring ENUM('One-off','Weekly','Monthly','Quarterly','Yearly') DEFAULT 'One-off',
            status TINYINT(1) DEFAULT 0,
            type VARCHAR(50) DEFAULT NULL,
            `option` VARCHAR(50) DEFAULT NULL,
            created_at TIMESTAMP NULL DEFAULT NULL,
            updated_at TIMESTAMP NULL DEFAULT NULL,
            PRIMARY KEY (id),
            KEY donation_id (donation_id),
            KEY campaign_id (campaign_id),
            KEY subscription_id (subscription_id)
        ) $charsetCollate;";

        return DBMigrator::runSQL($sql, $force ? $table_name : null);
    }
}