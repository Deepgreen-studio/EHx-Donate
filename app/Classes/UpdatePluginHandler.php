<?php
declare(strict_types=1);

namespace EHxDonate\Classes;

use EHxDonate\Database\DBMigrator;
use EHxDonate\Models\Currency;

if (!defined('ABSPATH')) {
    exit;
}

class UpdatePluginHandler
{  
    const PLUGIN_VERSION_KEY = 'ehxdo_plugin_version';
    
    /**
     * Handle Plugin Update Features.
     *
     * @return void
     */
    public function __construct()
    {
        $stored_version = get_option(self::PLUGIN_VERSION_KEY);

        if ($stored_version === false) {
            $this->runUpdate();
        }
        else {
            if (version_compare($stored_version, EHXDO_VERSION, '<')) {
                $this->runUpdate();
            }
        }
    }
    
    /**
     * Run Update
     *
     * @return void
     */
    protected function runUpdate()
    {
        DBMigrator::run();

        (new Currency)->seed();

        $options = array_merge(Settings::$options, [
            'currency' => (new Currency())->where('code', 'GBP')->first()?->id ?? 2,
            'currency_position' => 'before',
        ]);

        update_option(Settings::$option, $options);

        update_option(self::PLUGIN_VERSION_KEY, EHXDO_VERSION);
    }
}