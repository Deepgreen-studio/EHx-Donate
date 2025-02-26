<?php

if (!class_exists('EHX_Donate_Response')) {

    /**
     * EHX_Response
     * A helper class for handling responses in WordPress.
     */
    class EHX_Donate_Response
    {
        /**
         * Send a JSON error response.
         *
         * @param string $message The error message to include in the response.
         * @param int    $status_code The HTTP status code for the error (default: 500).
         * @param array  $data Optional additional data to include in the response.
         * @return void
         */
        public function error(string $message, int $status_code = 500, array $data = []): void
        {
            $response = $this->prepareResponse($message, $data);

            if (!has_action('wp_ajax_nopriv')) {
                status_header($status_code);
            }

            wp_send_json_error($response, $status_code);
            exit;
        }

        /**
         * Send a JSON success response.
         *
         * @param string $message The success message to include in the response.
         * @param array  $data Optional data to include in the response.
         * @return void
         */
        public function success(string $message = '', array $data = []): void
        {
            $response = $this->prepareResponse($message, $data);

            wp_send_json_success($response);
            exit;
        }

        /**
         * Prepare the response array.
         *
         * @param string $message The message to include in the response.
         * @param array  $data Optional additional data to include in the response.
         * @return array The prepared response array.
         */
        private function prepareResponse(string $message, array $data = []): array
        {
            $response = [];

            if (!empty($message)) {
                $response['message'] = esc_html($message);
            }

            // Merge additional data if provided
            if (!empty($data)) {
                $response = array_merge($response, $data);
            }

            return $response;
        }
    }
}