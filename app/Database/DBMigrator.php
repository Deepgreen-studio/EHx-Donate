<?php
declare(strict_types=1);

namespace EHxDonate\Database;

use EHxDonate\Database\Migrations\Donation;
use EHxDonate\Database\Migrations\DonationItem;
use EHxDonate\Database\Migrations\Transaction;

class DBMigrator
{    
    /**
     * run
     *
     * @param  mixed $network_wide
     * @return void
     */
    public static function run()
    {
        self::migrate();
    }
    
    /**
     * migrate
     *
     * @return void
     */
    public static function migrate()
    {
        Donation::migrate();
        DonationItem::migrate();
        Transaction::migrate();
    }
    
    /**
     * Run SQL
     *
     * @param  mixed $sql
     * @param  mixed $tableName
     * @return bool
     */
    public static function runSQL($sql, $tableName = null): bool
    {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        if($tableName == null) {
            dbDelta($sql);
        }
        else {
            global $wpdb;
            if ($wpdb->prepare("SHOW TABLES LIKE %s", $tableName) != $tableName) {

                dbDelta($sql);
                return true;
            }
        }
        return true;
    }
}