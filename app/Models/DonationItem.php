<?php

namespace EHxDonate\Models;

if (!defined('ABSPATH')) {
    exit;
}

class DonationItem extends Model
{
    public static string $table = 'ehxdo_donation_items';

    public function __construct()
    {
        parent::__construct(self::$table);
    }

    public function getTableName()
    {
        return $this->db->prefix . self::$table;
    }

    public function create($data)
    {
        return $this->insert($data);
    }

}