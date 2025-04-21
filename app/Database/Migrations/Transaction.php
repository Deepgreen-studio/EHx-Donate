<?php
declare(strict_types=1);

namespace EHxDonate\Database\Migrations;

use EHxDonate\Database\DBMigrator;
use EHxDonate\Models\Transaction as ModelsTransaction;

if (!defined('ABSPATH')) {
    exit;
}

class Transaction
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

        $table_name = $wpdb->prefix . ModelsTransaction::$table;

        $sql = "CREATE TABLE $table_name (
            id INT(11) NOT NULL AUTO_INCREMENT,
            donation_id BIGINT UNSIGNED DEFAULT NULL,
            amount DECIMAL(8,2) NOT NULL,
            balance DECIMAL(8,2) NOT NULL,
            note TEXT DEFAULT NULL,
            date DATE DEFAULT NULL,
            status ENUM('Paid','Unpaid') DEFAULT 'Paid',
            type ENUM('Credit','Debit') DEFAULT 'Credit',
            is_match TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP NULL DEFAULT NULL,
            updated_at TIMESTAMP NULL DEFAULT NULL,
            PRIMARY KEY (id),
            KEY donation_id (donation_id)
        ) $charsetCollate;";

        return DBMigrator::runSQL($sql, $force ? $table_name : null);
    }
}