<?php
declare(strict_types=1);

namespace EHxDonate\Helpers;

use EHxDonate\Classes\Settings;
use EHxDonate\Models\Currency;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Helper
 * A helper class
 */
class Helper
{
    public const NONCE_NAME = '_ehxdo_nonce';

    /**
     * A function for debugging purposes. It outputs the provided value(s) and stops the script execution.
     *
     * @param mixed ...$values The value to be dumped.
     *
     * @return void The function does not return a value.
     */
    public static function dd(mixed ...$values)
    {
        if (!\in_array(\PHP_SAPI, ['cli', 'phpdbg', 'embed'], true) && !headers_sent()) {
            header('HTTP/1.1 500 Internal Server Error');
        }

        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
        $file = $backtrace['file'] ?? 'unknown file';
        $line = $backtrace['line'] ?? 'unknown line';

        echo '<pre>';
        echo "Called from: {$file} on line {$line}\n\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        foreach ($values as $value) {
            var_dump($value); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }
        echo '</pre>';

        exit(1);
    }

    /**
     * Sends an email using WordPress's wp_mail function.
     *
     * @param string|array $to          Email address or array of email addresses to send the email to.
     * @param string       $subject     The subject of the email.
     * @param string       $message     The body of the email. Can be HTML or plain text.
     * @param string|array $headers     Optional. Additional headers to send with the email.
     * @param string|array $attachments Optional. Files to attach to the email.
     * @param bool         $is_html     Optional. Whether the email is HTML. Default true.
     * @param string|array $cc          Optional. CC email address or array of CC email addresses.
     * @param string|array $bcc         Optional. BCC email address or array of BCC email addresses.
     * @param string       $reply_to    Optional. Reply-To email address.
     *
     * @return bool Whether the email was sent successfully.
     */
    public static function sendEmail($to, $subject, $message, $headers = [], $attachments = [], $is_html = true, $cc = [], $bcc = [], $reply_to = '')
    {
        // Validate email addresses
        if (!is_email($to)) {
            error_log('Invalid recipient email address: ' . $to);  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            return false;
        }

        $allowedHtml = (bool) Settings::extractSettingValue('content_type', false);
        // Set the default headers
        $default_headers = [
            "Content-Type: " . ($is_html && $allowedHtml ? "text/html" : "text/plain") . "; charset=UTF-8"
        ];

        // Set the default "From" name and email
        $fromName  = Settings::extractSettingValue('mail_appears_from', get_bloginfo('name'));

        if (!empty($fromName)) {
            $from = "From: {$fromName}";
            $fromEmail = Settings::extractSettingValue('mail_appears_from_address', get_option('admin_email'));
            if (is_email($fromEmail)) {
                $from .= " <{$fromEmail}>";
            }

            $default_headers[] = $from;
        }

        // Add Reply-To header if provided
        if (!empty($reply_to) && is_email($reply_to)) {
            $default_headers[] = "Reply-To: {$reply_to}";
        }

        // Add CC headers if provided
        if (!empty($cc)) {
            $cc = is_array($cc) ? $cc : [$cc];
            foreach ($cc as $cc_email) {
                if (is_email($cc_email)) {
                    $default_headers[] = "Cc: {$cc_email}";
                }
            }
        }

        // Add BCC headers if provided
        if (!empty($bcc)) {
            $bcc = is_array($bcc) ? $bcc : [$bcc];
            foreach ($bcc as $bcc_email) {
                if (is_email($bcc_email)) {
                    $default_headers[] = "Bcc: {$bcc_email}";
                }
            }
        }

        // Merge default headers with custom headers
        $headers = array_merge($default_headers, (array)$headers);

        // Allow filtering of headers
        $headers = apply_filters('ehxdo_email_headers', $headers, $to, $subject, $message);

        // Allow filtering of message
        $message = apply_filters('ehxdo_email_message', $message, $to, $subject, $headers);

        // Send the email
        $result = wp_mail($to, $subject, $message, $headers, $attachments);

        // Log errors if the email fails to send
        if (!$result) {
            error_log('Failed to send email to: ' . $to);  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }

        return $result;
    }

    /**
     * Displays a notice to the user.
     *
     * @param string $message The message to display to the user.
     * @param string $type    The type of notice. Default is 'success'.
     *                        Accepted values: 'success', 'error', 'warning', 'info'.
     *
     * @return void
     */
    public static function display_notice($message, $type = 'success'): void
    {
        add_settings_error(
            'ehx_donate_options',
            'ehx_donate_message',
            esc_html($message),
            $type
        );
        settings_errors('ehx_donate_options');
    }

    /**
     * Renders an input group field for WordPress settings pages.
     *
     * @param array  $args   Configuration for the input field.
     * @param string $option Option group name for WordPress settings.
     */
    public static function input_group($args, $option = null)
    {
        $option = $option ? $option : Settings::$option;

        // Extract field properties
        $label = isset($args['title']) ? $args['title'] : ($args['field_name'] ?? null);
        $field_name = isset($args['field_name']) ? $args['field_name'] : ($args['title'] ?? null);
        $field_name = strtolower(str_replace(' ', '_', $field_name));
        $type       = $args['type'] ?? 'text';
        $input_type = $args['is_type'] ?? 'input';
        $value      = Settings::extractSettingValue($field_name);
        $input_name = esc_attr($option . "[$field_name]");
        $placeholder = isset($args['placeholder']) ? esc_attr($args['placeholder']) : '';
        $data       = $args['data'] ?? [];

        $depend_field = isset($args['depend_field']) ? Settings::extractSettingValue($args['depend_field']) : '';
        $depend_value = $args['depend_value'] ?? null;
        $dependable = isset($args['depend_field']) && $depend_field == $depend_value;

        // Ensure a valid field name exists
        if (empty($field_name)) {
            return;
        }

        require EHXDO_PLUGIN_DIR . 'views/admin/components/input-group.php';
    }

    /**
     * Renders an input field for WordPress settings pages.
     *
     * @param string  $label
     * @param string  $for
     * @param string  $type
     * @param string  $placeholder
     */
    public static function input_field($label, $for = null, $type = 'text', $placeholder = '')
    {
        $htmlFor = $for != null ? $for : $label;
        $value   = Settings::extractSettingValue($htmlFor);

        require EHXDO_PLUGIN_DIR . 'views/admin/components/input-field.php';
    }

    /**
     * Displays a 404 page and terminates execution.
     *
     * This function sets the 404 status code, updates the global $wp_query to reflect a 404 status,
     * and then includes the 404 template part.
     *
     * @return void
     */
    public static function show_404()
    {
        global $wp_query;
        $wp_query->set_404();
        status_header(404);
        get_template_part('404');
        exit();
    }

    /**
     * Generate and return customization settings for forms.
     *
     * This method provides a structured object containing customization options for forms,
     * including form settings, privacy options, login redirection, and membership configurations.
     *
     * @return object An object containing all customization settings.
     */
    public static function customize()
    {
        return (object) [
            "campaigns" => (object) [
                (object) [
                    (object) [
                        "label" => esc_html__("Goal Amount", 'ehx-donate'),
                        "id" => "goal_amount",
                        "type" => "number",
                        "placeholder" => esc_html__("Enter Goal Amount", 'ehx-donate'),
                        "value" => 0,
                    ],
                    (object) [
                        "label" => esc_html__("Recurring", 'ehx-donate'),
                        "id" => "recurring",
                        "type" => "select",
                        "options" => [
                            esc_html__('One-off', 'ehx-donate'),
                            esc_html__('Weekly', 'ehx-donate'),
                            esc_html__('Monthly', 'ehx-donate'),
                            esc_html__('Quarterly', 'ehx-donate'),
                            esc_html__('Yearly', 'ehx-donate'),
                        ],
                        "value" => 0,
                    ]
                ],
                (object) [
                    (object) [
                        "label" => esc_html__("Start Date", 'ehx-donate'),
                        "id" => "start_date",
                        "type" => "date",
                        "placeholder" => esc_html__("Choice Start date", 'ehx-donate'),
                        "value" => 0,
                    ],
                    (object) [
                        "label" => esc_html__("End Date", 'ehx-donate'),
                        "id" => "end_date",
                        "type" => "date",
                        "placeholder" => esc_html__("Choice Start date", 'ehx-donate'),
                        "value" => 0,
                    ]
                ]
            ],
        ];
    }

    /**
     * Checks if a specific plugin is active.
     *
     * @param string $name The name of the plugin to check.
     *
     * @return string|bool Returns the name of the plugin if it is active, or false if it is not active.
     */
    public static function check_addons(string $name): string|bool
    {
        $active_plugins = get_option('active_plugins');

        return in_array($name, $active_plugins);
    }

    /**
     * Formats a monetary amount into a currency string with a pound sign and two decimal places.
     *
     * @param float $amount The monetary amount to be formatted.
     *
     * @return string|float The formatted currency string.
     */
    public static function currencyFormat($amount, $addSymbol = true): string|float
    {
        $transient = get_transient(Settings::TRANSIENT);

        // Converted & Format Amount
        $converted_amount = (float) number_format((float) $amount, 2);

        if(!$addSymbol) {
            return $converted_amount;
        }

        $symbol = $transient->currency?->symbol ?? '£';
        $position = $transient->currency->symbol_position ?? 'before'; // optional

        return $position === 'after' ? "{$converted_amount} {$symbol}" : "{$symbol} {$converted_amount}";
    }

    /**
     * Exchange a monetary amount into a currency string.
     *
     * @param float $amount The monetary amount to be exchange.
     *
     * @return float The formatted currency string.
     */
    public static function exchangeCurrency($amount): float
    {
        $transient = get_transient(Settings::TRANSIENT);

        $rate = $transient->currency?->exchange_rate ?? 1;

        // Convert
        $converted_amount = (float) $amount * (float) $rate;

        return (float) number_format($converted_amount, 2);
    }


    /**
     * Allowed HTML Tags
     *
     * @return array
     */
    public static function allowedHTMLTags(): array
    {
        // Base attributes allowed for most tags
        $base_attrs = [
            'id' => true,
            'class' => true,
            'style' => true,
            'title' => true,
            'href' => true,
            'role' => true,
            'aria-*' => true,
            'data-*' => true,
            'lang' => true,
            'dir' => ['ltr', 'rtl', 'auto'],
            'tabindex' => true,
            'contenteditable' => true,
            'hidden' => true,
            'draggable' => ['true', 'false'],
            'spellcheck' => ['true', 'false'],
        ];

        // SVG-specific tags and attributes
        $svg_tags = [
            'svg' => [
                'xmlns' => true,
                'width' => true,
                'height' => true,
                'viewbox' => true,
                'fill' => true,
                'stroke' => true,
                'stroke-width' => true,
                'stroke-linecap' => true,
                'stroke-linejoin' => true,
                'x' => true,
                'y' => true,
                'preserveAspectRatio' => true,
            ] + $base_attrs,
            'g' => [
                'fill' => true,
                'transform' => true,
            ] + $base_attrs,
            'title' => $base_attrs,
            'path' => [
                'd' => true,
                'fill' => true,
                'stroke' => true,
                'stroke-width' => true,
                'stroke-linecap' => true,
                'stroke-linejoin' => true,
            ] + $base_attrs,
            'circle' => [
                'cx' => true,
                'cy' => true,
                'r' => true,
                'fill' => true,
                'stroke' => true,
            ] + $base_attrs,
            'polyline' => [
                'cx' => true,
                'cy' => true,
                'r' => true,
                'fill' => true,
                'stroke' => true,
                'points' => true,
            ] + $base_attrs,
            'line' => [
                'cx' => true,
                'cy' => true,
                'r' => true,
                'fill' => true,
                'stroke' => true,
                'x1' => true,
                'x2' => true,
                'y1' => true,
                'y2' => true,
            ] + $base_attrs,
            'rect' => [
                'x' => true,
                'y' => true,
                'width' => true,
                'height' => true,
                'rx' => true,
                'ry' => true,
                'fill' => true,
                'stroke' => true,
            ] + $base_attrs,
            'text' => [
                'x' => true,
                'y' => true,
                'font-family' => true,
                'font-size' => true,
                'text-anchor' => true,
                'fill' => true,
            ] + $base_attrs,
            'use' => [
                'href' => true,
                'x' => true,
                'y' => true,
                'width' => true,
                'height' => true,
            ] + $base_attrs,
        ];

        // Form and input related tags
        $form_tags = [
            'form' => [
                'action' => true,
                'method' => ['get', 'post'],
                'enctype' => ['application/x-www-form-urlencoded', 'multipart/form-data', 'text/plain'],
                'novalidate' => true,
                'target' => ['_blank', '_self', '_parent', '_top'],
                'autocomplete' => ['on', 'off'],
                'accept-charset' => true,
                'name' => true,
            ] + $base_attrs,
            'input' => [
                'type' => ['text', 'password', 'email', 'number', 'tel', 'url', 'search', 'date', 'time', 'datetime-local', 'month', 'week', 'color', 'checkbox', 'radio', 'file', 'submit', 'image', 'reset', 'button', 'hidden', 'range'],
                'name' => true,
                'value' => true,
                'autocomplete' => ['on', 'off'],
                'placeholder' => true,
                'required' => true,
                'disabled' => true,
                'readonly' => true,
                'checked' => true,
                'min' => true,
                'max' => true,
                'step' => true,
                'minlength' => true,
                'maxlength' => true,
                'pattern' => true,
                'size' => true,
                'multiple' => true,
                'accept' => true,
                'src' => true,
                'alt' => true,
                'form' => true,
                'list' => true,
            ] + $base_attrs,
            'textarea' => [
                'name' => true,
                'placeholder' => true,
                'rows' => true,
                'cols' => true,
                'required' => true,
                'disabled' => true,
                'readonly' => true,
                'minlength' => true,
                'maxlength' => true,
                'wrap' => ['hard', 'soft'],
                'autocomplete' => ['on', 'off'],
                'form' => true,
            ] + $base_attrs,
            'select' => [
                'name' => true,
                'required' => true,
                'disabled' => true,
                'multiple' => true,
                'size' => true,
                'autocomplete' => ['on', 'off'],
                'form' => true,
            ] + $base_attrs,
            'option' => [
                'value' => true,
                'selected' => true,
                'disabled' => true,
                'label' => true,
            ] + $base_attrs,
            'optgroup' => [
                'label' => true,
                'disabled' => true,
            ] + $base_attrs,
            'button' => [
                'type' => ['button', 'submit', 'reset'],
                'name' => true,
                'value' => true,
                'disabled' => true,
                'form' => true,
                'formaction' => true,
                'formenctype' => true,
                'formmethod' => true,
                'formnovalidate' => true,
                'formtarget' => true,
                'autofocus' => true,
            ] + $base_attrs,
            'label' => [
                'for' => true,
                'form' => true,
            ] + $base_attrs,
            'fieldset' => [
                'disabled' => true,
                'form' => true,
                'name' => true,
            ] + $base_attrs,
            'legend' => $base_attrs,
            'datalist' => $base_attrs,
            'output' => [
                'for' => true,
                'form' => true,
                'name' => true,
            ] + $base_attrs,
            'progress' => [
                'value' => true,
                'max' => true,
            ] + $base_attrs,
            'meter' => [
                'value' => true,
                'min' => true,
                'max' => true,
                'low' => true,
                'high' => true,
                'optimum' => true,
            ] + $base_attrs,
        ];

        // Media tags
        $media_tags = [
            'img' => [
                'src' => true,
                'alt' => true,
                'srcset' => true,
                'sizes' => true,
                'decoding' => ['async', 'auto', 'sync'],
                'loading' => ['lazy', 'eager'],
                'width' => true,
                'height' => true,
                'crossorigin' => ['anonymous', 'use-credentials'],
                'usemap' => true,
                'ismap' => true,
                'longdesc' => true,
                'referrerpolicy' => ['no-referrer', 'no-referrer-when-downgrade', 'origin', 'origin-when-cross-origin', 'unsafe-url'],
            ] + $base_attrs,
            'picture' => $base_attrs,
            'source' => [
                'src' => true,
                'srcset' => true,
                'sizes' => true,
                'type' => true,
                'media' => true,
            ] + $base_attrs,
            'audio' => [
                'src' => true,
                'controls' => true,
                'autoplay' => true,
                'loop' => true,
                'muted' => true,
                'preload' => ['none', 'metadata', 'auto'],
                'crossorigin' => ['anonymous', 'use-credentials'],
            ] + $base_attrs,
            'video' => [
                'src' => true,
                'poster' => true,
                'width' => true,
                'height' => true,
                'controls' => true,
                'autoplay' => true,
                'loop' => true,
                'muted' => true,
                'preload' => ['none', 'metadata', 'auto'],
                'playsinline' => true,
                'crossorigin' => ['anonymous', 'use-credentials'],
            ] + $base_attrs,
            'track' => [
                'kind' => ['subtitles', 'captions', 'descriptions', 'chapters', 'metadata'],
                'src' => true,
                'srclang' => true,
                'label' => true,
                'default' => true,
            ] + $base_attrs,
            'embed' => [
                'src' => true,
                'type' => true,
                'width' => true,
                'height' => true,
            ] + $base_attrs,
            'object' => [
                'data' => true,
                'type' => true,
                'width' => true,
                'height' => true,
                'usemap' => true,
                'form' => true,
                'name' => true,
            ] + $base_attrs,
            'param' => [
                'name' => true,
                'value' => true,
            ] + $base_attrs,
            'iframe' => [
                'src' => true,
                'srcdoc' => true,
                'name' => true,
                'width' => true,
                'height' => true,
                'allow' => true,
                'allowfullscreen' => true,
                'referrerpolicy' => ['no-referrer', 'no-referrer-when-downgrade', 'origin', 'origin-when-cross-origin', 'unsafe-url'],
                'sandbox' => ['allow-forms', 'allow-modals', 'allow-orientation-lock', 'allow-pointer-lock', 'allow-popups', 'allow-popups-to-escape-sandbox', 'allow-presentation', 'allow-same-origin', 'allow-scripts', 'allow-top-navigation', 'allow-top-navigation-by-user-activation'],
                'loading' => ['lazy', 'eager'],
            ] + $base_attrs,
            'map' => [
                'name' => true,
            ] + $base_attrs,
            'area' => [
                'alt' => true,
                'coords' => true,
                'shape' => ['rect', 'circle', 'poly', 'default'],
                'href' => true,
                'target' => ['_blank', '_self', '_parent', '_top'],
                'download' => true,
                'rel' => true,
                'hreflang' => true,
                'type' => true,
                'referrerpolicy' => ['no-referrer', 'no-referrer-when-downgrade', 'origin', 'origin-when-cross-origin', 'unsafe-url'],
            ] + $base_attrs,
            'canvas' => [
                'width' => true,
                'height' => true,
            ] + $base_attrs,
        ];

        // Table tags
        $table_tags = [
            'table' => [
                'border' => true,
                'cellpadding' => true,
                'cellspacing' => true,
                'summary' => true,
                'width' => true,
            ] + $base_attrs,
            'caption' => $base_attrs,
            'colgroup' => [
                'span' => true,
            ] + $base_attrs,
            'col' => [
                'span' => true,
            ] + $base_attrs,
            'thead' => $base_attrs,
            'tbody' => $base_attrs,
            'tfoot' => $base_attrs,
            'tr' => $base_attrs,
            'td' => [
                'colspan' => true,
                'rowspan' => true,
                'headers' => true,
                'scope' => ['row', 'col', 'rowgroup', 'colgroup'],
                'abbr' => true,
                'align' => ['left', 'center', 'right', 'justify', 'char'],
                'valign' => ['top', 'middle', 'bottom', 'baseline'],
                'width' => true,
                'height' => true,
            ] + $base_attrs,
            'th' => [
                'colspan' => true,
                'rowspan' => true,
                'headers' => true,
                'scope' => ['row', 'col', 'rowgroup', 'colgroup'],
                'abbr' => true,
                'align' => ['left', 'center', 'right', 'justify', 'char'],
                'valign' => ['top', 'middle', 'bottom', 'baseline'],
                'width' => true,
                'height' => true,
            ] + $base_attrs,
        ];

        $a_tag = [
            'a' => [
                'href' => true,
                'target' => ['_blank', '_self', '_parent', '_top'],
                'rel' => true,
                'download' => true,
                'hreflang' => true,
                'type' => true,
                'referrerpolicy' => ['no-referrer', 'origin', 'unsafe-url'],
                'ping' => true,
                'onclick' => true,
            ] + $base_attrs,
        ];        

        // Text formatting and semantic tags
        $text_tags = [
            'a' => $base_attrs,
            'p' => $base_attrs,
            'h1' => $base_attrs,
            'h2' => $base_attrs,
            'h3' => $base_attrs,
            'h4' => $base_attrs,
            'h5' => $base_attrs,
            'h6' => $base_attrs,
            'div' => $base_attrs,
            'span' => $base_attrs,
            'br' => $base_attrs,
            'hr' => $base_attrs,
            'pre' => $base_attrs,
            'blockquote' => [
                'cite' => true,
            ] + $base_attrs,
            'ol' => [
                'reversed' => true,
                'start' => true,
                'type' => ['1', 'a', 'A', 'i', 'I'],
            ] + $base_attrs,
            'ul' => $base_attrs,
            'li' => [
                'value' => true,
            ] + $base_attrs,
            'dl' => $base_attrs,
            'dt' => $base_attrs,
            'dd' => $base_attrs,
            'figure' => $base_attrs,
            'figcaption' => $base_attrs,
            'main' => $base_attrs,
            'section' => $base_attrs,
            'article' => $base_attrs,
            'aside' => $base_attrs,
            'header' => $base_attrs,
            'footer' => $base_attrs,
            'nav' => $base_attrs,
            'address' => $base_attrs,
            'time' => [
                'datetime' => true,
            ] + $base_attrs,
            'mark' => $base_attrs,
            'ruby' => $base_attrs,
            'rt' => $base_attrs,
            'rp' => $base_attrs,
            'bdi' => $base_attrs,
            'bdo' => [
                'dir' => ['ltr', 'rtl'],
            ] + $base_attrs,
            'wbr' => $base_attrs,
            'ins' => [
                'cite' => true,
                'datetime' => true,
            ] + $base_attrs,
            'del' => [
                'cite' => true,
                'datetime' => true,
            ] + $base_attrs,
            'small' => $base_attrs,
            'strong' => $base_attrs,
            'em' => $base_attrs,
            'i' => $base_attrs,
            'b' => $base_attrs,
            'u' => $base_attrs,
            's' => $base_attrs,
            'cite' => $base_attrs,
            'q' => [
                'cite' => true,
            ] + $base_attrs,
            'dfn' => $base_attrs,
            'abbr' => [
                'title' => true,
            ] + $base_attrs,
            'code' => $base_attrs,
            'var' => $base_attrs,
            'samp' => $base_attrs,
            'kbd' => $base_attrs,
            'sub' => $base_attrs,
            'sup' => $base_attrs,
            'details' => [
                'open' => true,
            ] + $base_attrs,
            'summary' => $base_attrs,
            'dialog' => [
                'open' => true,
            ] + $base_attrs,
            'menu' => $base_attrs,
            'menuitem' => $base_attrs,
            'template' => $base_attrs,
            'slot' => $base_attrs,
        ];

        // Framework-specific tags (Vue, React, etc.)
        $framework_tags = [
            'router-view' => $base_attrs,
            'router-link' => [
                'to' => true,
                'exact' => true,
                'active-class' => true,
                'exact-active-class' => true,
                'replace' => true,
                'append' => true,
                'tag' => true,
                'event' => true,
            ] + $base_attrs,
            'transition' => [
                'name' => true,
                'appear' => true,
                'mode' => ['in-out', 'out-in'],
                'duration' => true,
            ] + $base_attrs,
            'transition-group' => [
                'name' => true,
                'tag' => true,
                'move-class' => true,
            ] + $base_attrs,
        ];

        // Combine all tags
        $allowed_tags = array_merge(
            $svg_tags,
            $a_tag,
            $form_tags,
            $media_tags,
            $table_tags,
            $text_tags,
            $framework_tags
        );

        return $allowed_tags;
    }
}
