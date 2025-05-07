<?php

namespace EHxDonate\Classes;

use EHxDonate\Shortcodes\DonationFormShortcode;

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

        delete_transient(DonationFormShortcode::TRANSIENT);
        delete_transient(Settings::TRANSIENT);
    }
}
