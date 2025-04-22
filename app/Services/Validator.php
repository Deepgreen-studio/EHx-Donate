<?php

declare(strict_types=1);

namespace EHxDonate\Services;

use EHxDonate\Classes\Settings;
use SimplePie\Exception;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Validator
 * A helper class for handling Form Data Validation in WordPress.
 */
class Validator
{
    private array $validatedData = [];
    private Response $response;
    private array $errors = [];

    public function __construct()
    {
        $this->response = new Response();
    }

    /**
     * Validate the request data against the provided rules.
     *
     * @param array $rules Validation rules.
     * @param bool $sanitize Whether to sanitize input values.
     * @return bool True if validation passes, false otherwise.
     * @throws Exception If an unsupported validation rule is encountered.
     */
    public function validate(array $rules): bool
    {
        $request = new Request();

        foreach ($rules as $field => $fieldRules) {
            $value = $request->input(key: $field);
            $fieldRulesArray = $this->parseRules($fieldRules);

            // Skip validation if the field is nullable and empty
            if ($this->isNullable($fieldRulesArray) && $this->isEmpty($value)) {
                $this->validatedData[$field] = null;
                continue;
            }

            // Validate each rule
            foreach ($fieldRulesArray as $rule) {
                $this->applyRule($field, $value, $rule);
            }

            // Store validated value
            $this->validatedData[$field] = $value;
        }

        // Return validation result
        if (!empty($this->errors)) {
            wp_send_json_error([
                'errors' => $this->errors(),
                'message' => esc_html__('Please fill up all fields correctly.', 'ehx-donate'),
            ], 422);
        }

        return empty($this->errors);
    }

    /**
     * Parse validation rules into an array.
     *
     * @param string $rules The rules string (e.g., "required|min:3|max:10").
     * @return array Array of rules.
     */
    private function parseRules(string $rules): array
    {
        return explode('|', $rules);
    }

    /**
     * Check if the rules include "nullable".
     *
     * @param array $rules Array of rules.
     * @return bool True if nullable, false otherwise.
     */
    private function isNullable(array $rules): bool
    {
        return in_array('nullable', $rules, true);
    }

    /**
     * Check if a value is empty.
     *
     * @param mixed $value The value to check.
     * @return bool True if empty, false otherwise.
     */
    private function isEmpty($value): bool
    {
        return is_null($value) || $value === '';
    }

    /**
     * Apply a validation rule to a field.
     *
     * @param string $field The field name.
     * @param mixed $value The field value.
     * @param string $rule The validation rule.
     * @throws Exception If the rule is not supported.
     */
    private function applyRule(string $field, $value, string $rule): void
    {
        $ruleName = $rule;
        $param = null;

        // Handle rules with parameters (e.g., "min:3")
        if (strpos($rule, ':') !== false) {
            [$ruleName, $param] = explode(':', $rule, 2);
        }

        $ruleName = esc_attr($ruleName);

        // Call the validation method
        $method = "validate_{$ruleName}";
        if (method_exists($this, $method)) {
            $this->$method($field, $value, $param);
        } else {
            /* translators: %s is the name of the validation rule (e.g., "min", "max") */
            throw new \Exception(sprintf(esc_html__('Validation rule "%s" is not supported.', 'ehx-donate'), esc_html($ruleName)));
        }
    }

    /**
     * Get all validation errors.
     *
     * @return array List of errors.
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Get validated data.
     *
     * @return array Validated data.
     */
    public function validated(): array
    {
        return $this->validatedData;
    }

    /**
     * Validate that a field is required.
     *
     * @param string $field The field name.
     * @param mixed $value The field value.
     */
    private function validate_required(string $field, $value): void
    {
        if ($this->isEmpty($value)) {
            /* translators: %s is the name of the field that is required */
            $this->addError($field, sprintf(esc_html__('The %s field is required.', 'ehx-donate'), esc_html($field)));
        }
    }


    /**
     * Validates that a given field can be null or empty.
     * If the field is null or empty, no error is added.
     *
     * @param string $field The name of the field being validated.
     * @param mixed  $value The value of the field to validate.
     * @return bool True if the field is null or empty, false otherwise.
     */
    private function validate_nullable(string $field, $value): bool
    {
        if (is_null($value) || $value === '') {
            // Field is nullable and has no value; no need to add an error.
            return true;
        }
        return false;
    }


    /**
     * Validate that a field is an array.
     *
     * @param string $field The field name.
     * @param mixed $value The field value.
     */
    private function validate_array(string $field, $value): void
    {
        if (!is_array($value)) {
            /* translators: %s field must be an array */
            $this->addError($field, sprintf(esc_html__('The %s field must be an array.', 'ehx-donate'), esc_html($field)));
        }
    }

    /**
     * Validate that a field is a valid email address.
     *
     * @param string $field The field name.
     * @param mixed $value The field value.
     */
    private function validate_email(string $field, $value): void
    {
        if (!is_email($value)) {
            /* translators: %s field must be a valid email address */
            $this->addError($field, sprintf(esc_html__('The %s field must be a valid email address.', 'ehx-donate'), esc_html($field)));
        }
    }

    /**
     * Validate that a field is a valid email address.
     *
     * @param string $field The field name.
     * @param mixed $value The field value.
     */
    private function validate_unique(string $field, $value): void
    {
        if (email_exists($value)) {
            /* translators: %s has already been taken */
            $this->addError($field, sprintf(esc_html__('The %s has already been taken.', 'ehx-donate'), esc_html($field)));
        }
    }

    /**
     * Validate that a field meets a minimum length.
     *
     * @param string $field The field name.
     * @param mixed $value The field value.
     * @param string $param The minimum length.
     */
    private function validate_min(string $field, $value, string $param): void
    {
        if (strlen($value) < (int)$param) {
            /* translators: %1$s is the field name, %2$d is the minimum length */
            $this->addError(
                $field,
                sprintf(
                    /* translators: %1$s is the field name, %2$d is the minimum length */
                    esc_html__('The %1$s field must be at least %2$d characters long.', 'ehx-donate'),
                    esc_html($field),
                    (int)$param
                )
            );
        }
    }


    /**
     * Validate that a field does not exceed a maximum length.
     *
     * @param string $field The field name.
     * @param mixed $value The field value.
     * @param string $param The maximum length.
     */
    private function validate_max(string $field, $value, string $param): void
    {
        if (strlen($value) > (int)$param) {
            $this->addError(
                $field,
                sprintf(
                    /* translators: %1$s is the field name, %2$d is the maximum length */
                    esc_html__('The %1$s field must not exceed %2$d characters.', 'ehx-donate'),
                    esc_html($field),
                    (int)$param
                )
            );
        }
    }

    /**
     * Validate that a field is a string.
     *
     * @param string $field The field name.
     * @param mixed $value The field value.
     */
    private function validate_string(string $field, $value): void
    {
        if (!is_string($value)) {
            /* translators: %s field must be a string */
            $this->addError($field, sprintf(esc_html__('The %s field must be a string.', 'ehx-donate'), esc_html($field)));
        }
    }

    /**
     * Validate that a field is numeric.
     *
     * @param string $field The field name.
     * @param mixed $value The field value.
     */
    private function validate_numeric(string $field, $value): void
    {
        if (!is_numeric($value)) {
            /* translators: %s field must be a number */
            $this->addError($field, sprintf(esc_html__('The %s field must be a number.', 'ehx-donate'), esc_html($field)));
        }
    }

    /**
     * Validate that a field matches another field.
     *
     * @param string $field The field name.
     * @param mixed $value The field value.
     * @param string $param The field to match.
     */
    private function validate_same(string $field, $value, string $param): void
    {
        $request = new Request();

        $otherValue = $request->input($param);

        if ($value !== $otherValue) {
            $this->addError(
                $field,
                sprintf(
                    /* translators: %1$s is the first field name, %2$s is the second field name */
                    esc_html__('The %1$s field must match the %2$s field.', 'ehx-donate'),
                    esc_html($field),
                    esc_html($param)
                )
            );
        }
    }

    /**
     * Validate that a field is url.
     *
     * @param string $field The field name.
     * @param mixed $value The field value.
     */
    private function validate_url(string $field, $value): void
    {
        if (filter_var($value, FILTER_VALIDATE_URL) === FALSE) {
            /* translators: %s field must be an valid url */
            $this->addError($field, sprintf(esc_html__('The %s field must be an valid url.', 'ehx-donate'), esc_html($field)));
        }
    }

    /**
     * Validate that a field is date.
     *
     * @param string $field The field name.
     * @param mixed $value The field value.
     */
    private function validate_date(string $field, $value): bool
    {
        // wp_checkdate();
        return true;
    }

    /**
     * Validate that a field is file.
     *
     * @param string $field The field name.
     * @param mixed $value The field value.
     */
    private function validate_file(string $field, $value): bool
    {
        return true; // File is valid
    }

    /**
     * Validate that a field is image.
     *
     * @param string $field The field name.
     * @param mixed $value The field value.
     */
    private function validate_image(string $field, $value): bool
    {
        return true; // File is valid
    }

    /**
     * Add an error message for a field.
     *
     * @param string $field The field name.
     * @param string $message The error message.
     * @param mixed ...$args Additional arguments for sprintf.
     */
    private function addError(string $field, string $message, ...$args): void
    {
        $fieldName = str_replace('_', ' ', $field);
        $args = str_replace('_', ' ', $args);
        $this->errors[$field][] = sprintf($message, $fieldName, ...$args);
    }

    /**
     * Validate a nonce field.
     *
     * @param string $field Nonce field name.
     * @param string $action Nonce action.
     * @return bool True if the nonce is valid.
     */
    public function validate_nonce(string $field, string $action): bool
    {
        $request = new Request();
        $nonce = $request->input($field);
        if (!wp_verify_nonce($nonce, $action)) {
            $this->response->error(esc_html__('Nonce verification failed. Please try again.', 'ehx-donate'), 419);
            return false;
        }
        return true;
    }

    /**
     * Validate an AJAX nonce field.
     *
     * @return bool True if the nonce is valid.
     */
    public function validate_ajax_nonce(): bool
    {
        if (!check_ajax_referer('ehx_ajax_nonce', 'security', false)) {
            $this->response->error(esc_html__('Nonce verification failed. Please try again.', 'ehx-donate'), 419);
            return false;
        }
        return true;
    }

    /**
     * Validate a Google reCAPTCHA.
     *
     * @param string $value The reCAPTCHA response token from the frontend.
     * @return WP_Error|void|bool Returns true if validation is successful, otherwise returns a WP_Error.
     */
    public function validateRecaptcha($value)
    {
        try {
            $enable_recaptcha = defined('EHXRC_VERSION') && (bool) Settings::extractSettingValue('google_recaptcha_enable', false);

            if (!$enable_recaptcha) {
                return true;
            }

            if(empty($value)) {
                throw new Exception(esc_html__('Please verify ReCaptcha.', 'ehx-donate'));
            }

            // Prepare request data
            $data = [
                'secret'   => Settings::extractSettingValue('google_recaptcha_secret_key'),
                'response' => $value
            ];

            // Send request to Google
            $response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
                'body'      => $data,
                'timeout'   => 10,
                'sslverify' => true,
            ]);

            // Check for WP_Error
            if (is_wp_error($response)) {
                throw new Exception(esc_html__('ReCaptcha verification failed.', 'ehx-donate'));
            }

            // Decode JSON response
            $body = json_decode(wp_remote_retrieve_body($response), true);

            // Validate reCAPTCHA success
            if (empty($body['success']) || !$body['success']) {
                throw new Exception(esc_html__('ReCaptcha verification failed.', 'ehx-donate'));
            }

            return true; // Validation successful
        } catch (Exception $e) {
            return $this->response->error(esc_html($e->getMessage()));
        }
    }
}
