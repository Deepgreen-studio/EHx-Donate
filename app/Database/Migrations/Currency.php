<?php
declare(strict_types=1);

namespace EHxDonate\Database\Migrations;

use EHxDonate\Database\DBMigrator;
use EHxDonate\Models\Currency as ModelsCurrency;

if (!defined('ABSPATH')) {
    exit;
}

class Currency
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

        $table_name = $wpdb->prefix . ModelsCurrency::$table;

        $sql = "CREATE TABLE $table_name (
            id INT(11) NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(50) NOT NULL,
            symbol VARCHAR(50) NOT NULL,
            exchange_rate VARCHAR(50) DEFAULT NULL,
            status TINYINT(1) DEFAULT 1,
            code VARCHAR(50) DEFAULT NULL,
            created_at TIMESTAMP NULL DEFAULT NULL,
            updated_at TIMESTAMP NULL DEFAULT NULL,
            PRIMARY KEY (id)
        ) $charsetCollate;";

        return DBMigrator::runSQL($sql, $force ? $table_name : null);
    }
}