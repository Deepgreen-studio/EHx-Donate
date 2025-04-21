<?php

namespace EHxDonate\Classes;

use EHxDonate\Helpers\Helper;

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
