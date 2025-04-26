<?php
declare(strict_types=1);

namespace EHxDonate\Helpers;

use EHxDonate\Classes\Settings;

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
     * @return string The formatted currency string.
     */
    public static function currencyFormat($amount): string
    {
        return '£' . number_format((float) $amount, 2);
    }
}
