<?php

namespace EHxDonate\Classes;

if (!defined('ABSPATH')) {
    exit;
}

class DeactivationHandler
{    
    /**
     * handle
     *
     * @return void
     */
    public static function handle()
    {
        flush_rewrite_rules();
    }
}
