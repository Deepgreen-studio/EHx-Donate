<?php
declare(strict_types=1);

namespace EHxDonate\Database\Migrations;

use EHxDonate\Database\DBMigrator;
use EHxDonate\Models\Donation as ModelsDonation;

if (!defined('ABSPATH')) {
    exit;
}

class Donation
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

        $table_name = $wpdb->prefix . ModelsDonation::$table;

        $sql = "CREATE TABLE $table_name (
            id INT(11) NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NOT NULL,
            invoice VARCHAR(30) DEFAULT NULL,
            admin_fee DECIMAL(8,2) DEFAULT NULL,
            processing_fee_percentage DECIMAL(8,2) NOT NULL,
            processing_fee DECIMAL(8,2) NOT NULL,
            gift_aid TINYINT(1) DEFAULT 0,
            amount DECIMAL(8,2) NOT NULL,
            total_amount DECIMAL(8,2) NOT NULL,
            charge DECIMAL(8,2) DEFAULT 0,
            payment_method ENUM('Stripe','Paypal','Google Pay','Samsung Pay','Apple Pay','Skrill','Checkout','Blockchain','BTCPay') DEFAULT NULL,
            payment_status ENUM('Pending','Success','Cancel') DEFAULT 'Pending',
            browser_session VARCHAR(50) DEFAULT NULL,
            created_at TIMESTAMP NULL DEFAULT NULL,
            updated_at TIMESTAMP NULL DEFAULT NULL,
            PRIMARY KEY (id),
            KEY `user_id` (user_id)
        ) $charsetCollate;";

        return DBMigrator::runSQL($sql, $force ? $table_name : null);
    }
}