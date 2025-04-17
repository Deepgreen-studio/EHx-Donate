<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('EHXDo_Request')) {

    /**
     * EHXDo_Request
     * A helper class for handling Form Request Data in WordPress.
     */
    class EHXDo_Request
    {
        protected array $data;

        public function __construct()
        {
            if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
                $nonce  = EHXDo_Helper::getInput('_ehxdo_nonce');
                $action = EHXDo_Helper::getInput('action');
                if ($action !== 'editpost' && $nonce && !wp_verify_nonce($nonce, $action)) {
                    $error = new WP_Error(
                        'invalid_nonce',
                        esc_html__('Unauthorized action!', 'ehx-donate'),
                        [
                            'status' => 403,
                            'title' => esc_html__('Plugin Error', 'ehx-donate'),
                            'back_link' => true
                        ]
                    );
                    if (wp_doing_ajax()) {
                        wp_die(
                            json_encode(
                                array(
                                    'success' => false,
                                    'message' => esc_html($error->get_error_message())
                                )
                            ),
                            esc_html__('Plugin Error', 'ehx-donate'),
                            array(
                                'response' => 403,
                                'exit'     => true
                            )
                        );
                    }
                    else {
                        wp_die(esc_html($error->get_error_message()), esc_html__('Plugin Error', 'ehx-donate'), ['response' => 403]);
                    }
                }
            }

            // Initialize request data from global variables

            $this->data = [
                'post'   => wp_unslash($_POST),
                'get'    => wp_unslash($_GET),
                'files'  => $_FILES,
                'cookie' => wp_unslash($_COOKIE),
                'server' => $_SERVER,
            ];
        }

        /**
         * Get a sanitized value from the request data.
         *
         * @param string $key The key to retrieve.
         * @param mixed $default The default value if the key does not exist.
         * @return mixed The sanitized value or default.
         */
        public function input($key, $default = null, $sanitize = true)
        {
            foreach ($this->data as $values) {
                if (isset($values[$key])) {
                    return $this->sanitize($values[$key], $sanitize);
                }
            }
            return $default;
        }

        /**
         * Get all request data of a specific type (e.g., POST, GET).
         *
         * @param string $type The request type (e.g., 'post', 'get').
         * @return array The sanitized array of request data.
         */
        public function all($type = 'post')
        {
            $data = [];
            if (isset($this->data[$type])) {
                foreach ($this->data[$type] as $key => $value) {
                    $data[$key] = $this->sanitize($value);
                }
            }
            return $data;
        }

        /**
         * Check if a key exists in the request data.
         *
         * @param string $key The key to check.
         * @return bool True if the key exists, false otherwise.
         */
        public function has($key)
        {
            foreach ($this->data as $values) {
                if (isset($values[$key])) {
                    return true;
                }
            }
            return false;
        }

        /**
         * Check if a key exists and is not empty in the request data.
         *
         * @param string $key The key to check.
         * @return bool True if the key exists and is not empty, false otherwise.
         */
        public function filled($key)
        {
            return !empty($this->input($key));
        }

        /**
         * Determine if a key's value is equivalent to a boolean "true".
         *
         * @param string $key The key to check.
         * @return bool True if the value represents a boolean "true", false otherwise.
         */
        public function integer($key, $default = null)
        {
            return (int) $this->input($key, $default);
        }

        /**
         * Determine if a key's value is equivalent to a boolean "true".
         *
         * @param string $key The key to check.
         * @return bool True if the value represents a boolean "true", false otherwise.
         */
        public function boolean($key)
        {
            return in_array($this->input($key), ['on', 1, 'yes', true], true);
        }

        /**
         * Get a file from the request.
         *
         * @param string $key The key for the file input.
         * @return array|null The file data if it exists, null otherwise.
         */
        public function file($key)
        {
            return $this->data['files'][$key] ?? null;
        }

        /**
         * Check if a file exists in the request.
         *
         * @param string $key The key for the file input.
         * @return bool True if the file exists, false otherwise.
         */
        public function hasFile($key)
        {
            return isset($this->data['files'][$key]);
        }

        /**
         * Sanitize a value or array of values.
         *
         * @param mixed $value The value to sanitize.
         * @param bool $sanitize The value to sanitize.
         * @return mixed The sanitized value or array.
         */
        public function sanitize($value, $sanitize = true)
        {
            if (is_array($value)) {
                return array_map([$this, 'sanitize'], $value); // Recursive sanitization for arrays
            }
            
            $value = wp_unslash($value);
            if ($sanitize) {
                $value = sanitize_text_field($value);
            }
            return $value;
        }
    }
}