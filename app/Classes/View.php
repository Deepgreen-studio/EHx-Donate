<?php
declare(strict_types=1);

namespace EHxDonate\Classes;

if (!defined('ABSPATH')) {
    exit;
}

class View
{    
    /**
     * make
     *
     * @param  mixed $path
     * @param  mixed $data
     * @return bool|string
     */
    public static function make($path, $data = []): bool|string
    {
        $path = str_replace('.', DIRECTORY_SEPARATOR, $path);

        $file = EHXDO_PLUGIN_DIR . 'views/'.$path.'.php';

        ob_start();
        extract($data);

        include $file;

        return ob_get_clean();
    }
        
    /**
     * render the html view
     *
     * @param  mixed $path
     * @param  mixed $data
     * @param  mixed $return
     */
    public static function render($path, $data = [], $return = false)
    {
        // $content = wp_kses(static::make($path, $data), \EHxMember\Helpers\Helper::allowedHTMLTags());
        $content = static::make($path, $data);

        if($return) {
            return $content;
        }
        echo wp_kses_post($content);
    }
}
