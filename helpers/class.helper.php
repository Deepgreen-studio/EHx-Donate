<?php

if (!class_exists('EHX_Donate_Helper')) {

    /**
     * EHX_Helper
     * A helper class
     */
    class EHX_Donate_Helper
    {
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

            echo '<pre>';
            var_dump($values);
            exit(1);
        }
        
        /**
         * Initializes the PHP session if it is not already started.
         *
         * This function checks if the PHP session has been started using the session_id() function.
         * If the session is not started, it calls the session_start() function to initiate the session.
         *
         * @return void The function does not return a value.
         */
        public static function session()
        {
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }            
        }

        /**
         * Sets a value in the PHP session.
         *
         * This function initializes the PHP session if it is not already started,
         * and then sets the specified key-value pair in the $_SESSION superglobal array.
         *
         * @param string $key   The key to store the value in the session.
         * @param mixed  $value The value to be stored in the session.
         *                     If not provided, the default value is null.
         *
         * @return void The function does not return a value.
         */
        public static function sessionSet($key, $value = null)
        {
            self::session();

            $_SESSION[$key] = $value;
        }

        public static function sessionForget($key)
        {
            self::session();

            if(array_key_exists($key, $_SESSION)) {
                unset($_SESSION[$key]);
            }
            return true;
        }

        /**
         * Retrieves a value from the PHP session.
         *
         * This function initializes the PHP session if it is not already started,
         * and then retrieves the specified key-value pair from the $_SESSION superglobal array.
         * If the key does not exist in the session, the function returns the provided default value.
         *
         * @param string $key     The key to retrieve the value from the session.
         * @param mixed  $default The default value to return if the key does not exist in the session.
         *                       If not provided, the default value is null.
         *
         * @return mixed The value associated with the specified key in the session, or the default value if the key does not exist.
         */
        public static function sessionGet($key, $default = null)
        {
            self::session();

            return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
        }
        
        /**
         * Sends an email to the specified recipient.
         *
         * @param string $to The email address of the recipient.
         * @param string $subject The subject line of the email.
         * @param string $message The content of the email.
         *
         * @return void
         */
        public static function send_email($to, $subject, $message) 
        {
            $headers = ['Content-Type: text/html; charset=UTF-8'];
        
            wp_mail($to, $subject, $message, $headers);
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
        public static function input_group($args, $option = 'ehx_donate_settings_options')
        {
                // Extract field properties
                $field_name = isset($args['field_name']) ? $args['field_name'] : '';
                $field_name = strtolower(str_replace(' ', '_', $field_name));
                $type       = $args['type'] ?? 'text';
                $input_type = $args['is_type'] ?? 'input';
                $value      = EHX_Donate_Settings::extract_setting_value($field_name);
                $input_name = esc_attr($option . "[$field_name]");
                $placeholder = isset($args['placeholder']) ? esc_attr($args['placeholder']) : '';
                $data       = $args['data'] ?? [];

                // Ensure a valid field name exists
                if (empty($field_name)) {
                    return;
                }
            ?>
                <tr valign="top">
                    <th scope="row">
                        <label for="<?php echo esc_attr($field_name); ?>"><?php echo esc_html(ucfirst(str_replace('_', ' ', $field_name))); ?></label>
                    </th>
                    <td>
                        <?php if ($input_type === 'input'): ?>
                            <input
                                type="<?php echo esc_attr($type); ?>"
                                id="<?php echo esc_attr($input_name); ?>"
                                name="<?php echo esc_attr($input_name); ?>"
                                class="regular-text"
                                placeholder="<?php echo esc_attr($placeholder); ?>"
                                value="<?php echo esc_attr($type == 'text' ? $value : '1'); ?>"
                                <?php
                                if ($type != 'text') {
                                    checked(1, $value, true);
                                }
                                ?>
                                aria-label="<?php echo esc_html(ucfirst(str_replace('_', ' ', $field_name))); ?>" 
                            />

                            <?php if ($type !== 'text' && !empty($placeholder)): ?>
                                <label for="<?php echo esc_attr($input_name); ?>">
                                    <?php echo esc_html($placeholder); ?>
                                </label>
                            <?php endif; ?>

                        <?php elseif ($input_type === 'select'): ?>
                            <select
                                name="<?php echo esc_attr($input_name); ?>"
                                id="<?php echo esc_attr($field_name); ?>"
                                class="regular-text"
                                aria-label="<?php echo esc_html(ucfirst(str_replace('_', ' ', $field_name))); ?>"
                                aria-value="<?php echo esc_html($value); ?>">
                                <option value=""><?php esc_html_e('Select an option', 'ehx-donate'); ?></option>
                                <?php foreach ($data as $option): ?>
                                    <option value="<?php echo esc_attr($option['key']); ?>" <?php selected($option['key'], $value); ?>>
                                        <?php echo esc_html($option['value']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                        <?php elseif ($input_type === 'textarea'): ?>
                            <textarea
                                name="<?php echo esc_attr($input_name); ?>"
                                id="<?php echo esc_attr($field_name); ?>"
                                class="regular-text"
                                placeholder="<?php echo esc_html($placeholder); ?>"
                                aria-label="<?php echo esc_html(ucfirst(str_replace('_', ' ', $field_name))); ?>"><?php echo esc_html($value); ?></textarea>
                        <?php endif; ?>
                        <small id="invalid_<?php echo esc_attr($field_name); ?>" class="invalid-feedback" style="display: block;"></small>

                        <?php if (isset($args['content'])): ?>
                            <small><?php echo esc_html($args['content']); ?></small>
                        <?php endif; ?>

                    </td>
                </tr>
            <?php
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
            $value   = EHX_Donate_Settings::extract_setting_value($htmlFor);

            ?>
                <input
                    type="<?php echo esc_attr($type); ?>"
                    id="<?php echo esc_attr($htmlFor); ?>"
                    name="<?php echo esc_attr($htmlFor); ?>"
                    class="regular-text"
                    placeholder="<?php echo esc_html($placeholder); ?>"
                    <?php if ($type === 'text'): ?>
                    value="<?php echo esc_attr($value); ?>"
                    <?php else:
                        checked("on", $value, true);
                    endif; ?>
                    aria-label="<?php echo esc_html(ucfirst(str_replace('_', ' ', $label))); ?>" />

                <?php if (in_array($type, ['checkbox', 'radio']) && !empty($placeholder)): ?>
                    <label for="<?php echo esc_attr($htmlFor); ?>">
                        <?php echo esc_html($placeholder); ?>
                    </label>
                <?php endif; ?>
            <?php
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
            get_template_part(404);
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
                "memberships" => (object) [
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
                ]
            ];
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
            return '£' . number_format($amount, 2);
        }

    }

}