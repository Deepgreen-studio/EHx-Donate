<?php
declare(strict_types=1);

namespace EHxDonate\Classes;

use EHxDonate\Helpers\Helper;

if (!defined('ABSPATH')) {
    exit;
}

class View
{
    /**
     * Generate the full file path for a given view.
     *
     * @param string $path Dot notation path (e.g., 'folder.view').
     * @return string
     */
    protected static function getFilePath(string $path): string
    {
        $path = str_replace('.', DIRECTORY_SEPARATOR, $path);
        return trailingslashit(EHXDO_PLUGIN_DIR . 'views') . $path . '.php';
    }

    /**
     * Load a view file and return its rendered content.
     *
     * @param string $path Dot notation path to the view.
     * @param array $data Data to extract into the view.
     * @return string Rendered HTML content.
     * @throws \RuntimeException If view file does not exist.
     */
    public static function make(string $path, array $data = []): string
    {
        $file = static::getFilePath($path);

        if (!file_exists($file)) {
            throw new \RuntimeException(sprintf('View file "%s" not found.', $file));
        }

        ob_start();

        try {
            extract($data, EXTR_SKIP);
            include $file;
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }

        return ob_get_clean() ?: '';
    }

    /**
     * Render a view with escaped HTML output.
     *
     * @param string $path Dot notation path to the view.
     * @param array $data Data to pass to the view.
     * @param bool $return Whether to return the output or echo it.
     * @return string|null
     */
    public static function render(string $path, array $data = [], bool $return = false): ?string
    {
        try {
            $content = wp_kses(
                static::make($path, $data),
                Helper::allowedHTMLTags()
            );

            if ($return) {
                return $content;
            }

            echo wp_kses_post($content);
        } catch (\Throwable $e) {
            if (WP_DEBUG) {
                error_log($e->getMessage());
            }
            if ($return) {
                return '';
            }
        }

        return null;
    }
}
