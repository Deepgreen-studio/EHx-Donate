<?php

namespace EHxDonate\Models;

if (!defined('ABSPATH')) {
    exit;
}

class User
{
    /** @var \wpdb $db WordPress database instance */
    protected $db;
    
    protected $table;

    public function __construct()
    {
        global $wpdb;

        $this->db = $wpdb;
        $this->table = $wpdb->users;
    }
    
    /**
     * create
     *
     * @param  mixed $data
     */
    public function create($data)
    {
        $user_id = wp_insert_user($data);

        return $user_id;
    }
    
    /**
     * update
     *
     * @param  mixed $user_id
     * @param  mixed $data
     * @return void
     */
    public function update($user_id, $data)
    {
        $this->db->update($this->table, $data, ['id' => $user_id]);
    }
    
    /**
     * updateMetaData
     *
     * @param  mixed $user_id
     * @param  mixed $key
     * @param  mixed $value
     * @return void
     */
    public function updateMetaData($user_id, $key, $value)
    {
        update_user_meta($user_id, $key, $value);
    }
}