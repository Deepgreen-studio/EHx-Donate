<?php

namespace EHxDonate\Classes;

if (!defined('ABSPATH')) {
    exit;
}

class UninstallHandler
{    
    /**
     * Uninstalls the plugin and performs necessary cleanup tasks.
     *
     * This function drops the payment table from the database and deletes the plugin's options.
     *
     * @return void
     */
    public static function handle()
    {
        ActivationHandler::capabilities('administrator', 'remove');

        // Delete the plugin's options from the database
        delete_option(Settings::$option);
    }
}
