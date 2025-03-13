<?php

if (!class_exists('EHX_Donate_Donation_Form_Shortcode')) {

    class EHX_Donate_Donation_Form_Shortcode
    {
        public EHX_Donate_Response $response;
        public EHX_Donate_Request $request;
        const NONCE_ACTION = 'ehx_form_action';
        const NONCE_NAME = 'ehx_form_nonce';

        public $form_id;

        /**
         * Constructor for the EHX_Donate class.
         *
         * Initializes the response object and adds the shortcode for the donate form.
         * Also sets up the AJAX actions for handling form submissions.
         */
        public function __construct()
        {
            // Initialize the response object
            $this->response = new EHX_Donate_Response();
            $this->request = new EHX_Donate_Request();

            // Add the shortcode for the donate form
            add_shortcode('ehx_donate_donation_form', [$this, 'add_shortcode']);

            // Set up the AJAX actions for handling form submissions
            add_action('wp_ajax_ehx_donate_from_submit', [$this, 'handle_form']); // When user is logged in
            add_action('wp_ajax_nopriv_ehx_donate_from_submit', [$this, 'handle_form']); // When user is logged out
        }
        
        /**
         * Adds a shortcode for displaying a donation form for a specific campaign.
         *
         * The function retrieves the current campaign ID, retrieves the associated post and custom fields,
         * fetches all published 'ehx-campaign' posts, and generates a donation form with the necessary fields.
         * It also handles the payment callback and displays the payment form.
         *
         * @return string The rendered donation form.
         */
        public function add_shortcode()
        {
            $campaign_id = get_the_ID();

            [$post, $ehx_campaign] = self::get_field($campaign_id);

            $campaigns = [];
            if ($post == null || ($post != null && $post->post_type != 'ehx-campaign')) {
                $args = [
                    'post_type' => 'ehx-campaign',
                    'posts_per_page' => -1, // Get all posts
                    'post_status'    => 'publish'
                ];

                foreach (get_posts($args) as $post) {
                    $campaigns[$post->post_name] = $post->post_title;
                }
            }

            $enable_gift_aid = (bool) EHX_Donate_Settings::extract_setting_value('enable_gift_aid', false);
            $enable_recaptcha = (bool) EHX_Donate_Settings::extract_setting_value('google_recaptcha_enable', false);

            ob_start();

            $recurring = [
                esc_html__('One-off', 'ehx-donate'),
                esc_html__('Weekly', 'ehx-donate'),
                esc_html__('Monthly', 'ehx-donate'),
                esc_html__('Quarterly', 'ehx-donate'),
                esc_html__('Yearly', 'ehx-donate'),
            ];

            $status = $this->request->input('status');
            $txid = $this->request->input('txid');
            if (!empty($status) && !empty($txid)) {
                $browser_session = EHX_Donate_Helper::sessionGet('browser_session');
                $payment_callback = !empty($status) && !empty($txid) && !empty($browser_session);
                $this->paymentCallback($status, $txid, $browser_session);
            }

            global $wp;
            $callback = home_url($wp->request);

            require EHX_DONATE_PLUGIN_DIR . 'views/shortcodes/donation-form.php';

            return ob_get_clean();
        }
        
        /**
         * Handles the form submission for donations.
         *
         * Validates form input, processes payments using Stripe API,
         * and saves donation records to the database.
         *
         * @return void Returns true on successful payment, error message on failure.
         */
        public function handle_form()
        {
            // Initialize validator
            $validator = new EHX_Donate_Validator();

            // Validate nonce to prevent CSRF
            $validator->validate_nonce(self::NONCE_NAME, self::NONCE_ACTION);

            $enable_gift_aid = $this->request->boolean('gift_aid');
            $enable_recaptcha = (bool) EHX_Donate_Settings::extract_setting_value('google_recaptcha_enable', false);

            $required = $enable_gift_aid ? 'required' : 'nullable';

            // Validate input data
            $validator->validate([
                'campaign' => 'required|string|max:255',
                'recurring' => 'required|string|max:255',
                'amount' => 'required|numeric',
                'first_name' => 'required|string|min:2|max:30',
                'last_name' => 'required|string|min:2|max:30',
                'email' => 'required|string|email|max:255',
                'phone' => 'required|string|min:9|max:20',
                'address_line_1' => $required . '|string|max:50',
                'address_line_2' => 'nullable|string|max:50',
                'city' => $required . '|string|max:50',
                'state' => $required . '|string|max:50',
                'country' => $required . '|string|max:50',
                'post_code' => $required . '|string|max:50',
                // 'g-recaptcha-response' => $enable_recaptcha ? 'required' : 'nullable',
            ]);

            // Validate reCAPTCHA if enabled
            // if ($enable_recaptcha) {
            //     $validator->validate_recaptcha($this->request->input('g-recaptcha-response'));
            // }

            // Calculate total amount with service charge
            $amount = (float) $this->request->input('amount');
            $service_charge = $amount * 1.4 / 100;
            $total_amount = $amount + $service_charge;
            $browser_session = uniqid();

            // Handle Stripe payment if enabled
            $stripe_enable = (bool) EHX_Donate_Settings::extract_setting_value('stripe_enable', false);
            if ($stripe_enable && $total_amount > 0) {
                $response = $this->handlePayment($total_amount, $browser_session);
                if (!$response || !isset($response->url)) {
                    return $this->response->error(__('Payment processing failed. Please try again.', 'ehx-donate'));
                }
            }

            // Create or update user
            $user_id = $this->create_or_update_user();
            if (is_wp_error($user_id)) {
                return $this->response->error(__('Failed to create user. Please try again.', 'ehx-donate'));
            }

            // Save donation record
            $donation_id = $this->save_donation_record($user_id, $amount, $total_amount, $service_charge, $browser_session);
            if (!$donation_id) {
                return $this->response->error(__('Failed to save donation record. Please try again.', 'ehx-donate'));
            }

            // Save user meta data
            $this->save_user_meta($user_id);

            // Store validated input and browser session in session
            EHX_Donate_Helper::sessionSet('input', $validator->validated());
            EHX_Donate_Helper::sessionSet('browser_session', $browser_session);

            // Return success response
            return $this->response->success(
                __('Donation processed successfully, please complete payment.', 'ehx-donate'),
                ['redirect' => $response->url]
            );
        }

        /**
         * Create or update a user based on form input.
         *
         * @return int|WP_Error User ID on success, WP_Error on failure.
         */
        private function create_or_update_user()
        {
            $display_name = $this->request->input('first_name');
            if ($this->request->filled('last_name')) {
                $display_name .= ' ' . $this->request->input('last_name');
            }

            return wp_insert_user([
                'user_login' => str_replace(' ', '-', strtolower($display_name)),
                'user_pass' => wp_generate_password(),
                'user_email' => $this->request->input('email'),
                'display_name' => $display_name,
            ]);
        }

        /**
         * Save donation record to the database.
         *
         * @param int $user_id
         * @param float $total_amount
         * @param float $service_charge
         * @param string $browser_session
         * @return int|false The inserted donation ID or false on failure.
         */
        private function save_donation_record($user_id, $amount, $total_amount, $service_charge, $browser_session)
        {
            global $wpdb;

            return $wpdb->insert(EHX_Donate::$donation_table, [
                'user_id' => $user_id,
                'invoice' => 'stripe',
                'processing_fee' => $service_charge,
                'gift_aid' => $this->request->boolean('gift_aid'),
                'amount' => $amount,
                'total_amount' => $total_amount,
                'charge' => 0,
                'payment_method' => 'stripe',
                'payment_status' => 'pending',
                'browser_session' => $browser_session,
                'created_at' => gmdate('Y-m-d H:i:s'),
            ]) ? $wpdb->insert_id : false;
        }

        /**
         * Save user meta data based on form input.
         *
         * @param int $user_id
         */
        private function save_user_meta($user_id)
        {
            add_user_meta($user_id, 'title', $this->request->input('title'));
            add_user_meta($user_id, 'first_name', $this->request->input('first_name'));
            add_user_meta($user_id, 'last_name', $this->request->input('last_name'));
            add_user_meta($user_id, 'phone', $this->request->input('phone'));

            if ($this->request->boolean('gift_aid')) {
                $address = [
                    'address_line_1' => $this->request->input('address_line_1'),
                    'address_line_2' => $this->request->input('address_line_2'),
                    'city' => $this->request->input('city'),
                    'state' => $this->request->input('state'),
                    'country' => $this->request->input('country'),
                    'post_code' => $this->request->input('post_code'),
                ];
                add_user_meta($user_id, 'address', $address);
            }
        }

        /**
         * Handles the payment process using Stripe API.
         *
         * @param float $total_amount The total amount to be paid.
         * @param string $browser_session The unique browser session identifier.
         */
        private function handlePayment($total_amount, $browser_session)
        {
            try {
                $mode = 'payment';

                $campaign = get_page_by_path(page_path: $this->request->input('campaign'), post_type: 'ehx-campaign'); 

                EHX_Donate_Helper::sessionSet('campaign', $campaign);

                $priceData = [
                    'currency' => 'gbp',
                    'unit_amount' => round($total_amount, 2) * 100,
                    'product_data' => [
                        'name' => $campaign->post_title,
                        // 'description' => substr(strip_tags($campaign->post_content), 20),
                    ],
                ];

                $recurring = $this->request->input('recurring');
                if ($recurring !== 'One-off') {
                    $interval = match ($recurring) {
                        'weekly' => ['interval' => 'week'],
                        'quarterly' => ['interval' => 'day', 'interval_count' => 15],
                        'yearly' => ['interval' => 'year'],
                        default => ['interval' => 'month']
                    };
                    $priceData['recurring'] = $interval;

                    $mode = 'subscription';
                }

                $items[] = [
                    'price_data' => $priceData,
                    'quantity' => 1,
                ];

                \Stripe\Stripe::setApiKey(esc_html(EHX_Donate_Settings::extract_setting_value('stripe_client_secret')));

                $payment_method_types = match ($this->request->input('payment_method')) {
                    'paypal' => ['paypal'],
                    'applepay' => ['applepay'],
                    'googlepay' => ['googlepay'],
                    default => ['card'],
                };

                $callback = $this->request->input('callback');
                $cancel_url  = add_query_arg(array('status' => 'cancel', 'txid' => $browser_session), $callback);
                $success_url = add_query_arg(array('status' => 'success', 'txid' => $browser_session), $callback);

                $session = \Stripe\Checkout\Session::create([
                    'payment_method_types' => $payment_method_types,
                    'line_items' => $items,
                    'mode' => $mode,
                    'cancel_url' => $cancel_url,
                    'success_url' => $success_url,
                ]);

                return $session;

            } catch (Exception $e) {
                return $this->response->error($e->getMessage());
            }
        }
        
        /**
         * Handles the payment callback after a successful payment.
         *
         * This function updates the donation status, creates a subscription if recurring,
         * and inserts the donation details into the respective tables.
         *
         * @param string $status The payment status (e.g., 'success' or 'cancel').
         * @param string $txid The unique transaction identifier.
         * @param string $browser_session The unique browser identifier.
         *
         * @return bool|string
         */
        private function paymentCallback($status, $txid, $browser_session): bool|string
        {
            if ($browser_session != $txid) {
                return home_url('/');
            }

            // $browser_session = '67ca9c9a270dd';

            $campaign = EHX_Donate_Helper::sessionGet('campaign');
            $input = EHX_Donate_Helper::sessionGet('input');

            $donation_table = EHX_Donate::$donation_table;

            global $wpdb;

            $donation = $wpdb->get_row($wpdb->prepare("SELECT * FROM $donation_table WHERE browser_session = %s AND payment_status = 'pending'", $browser_session));

            if($donation != null) {
                $recurring = $input['recurring'];

                $wpdb->insert(EHX_Donate::$donation_items_table, [
                    'donation_id' => $donation->id,
                    'campaign_id' => $campaign->ID,
                    'amount'  => $donation->total_amount,
                    'gift_aid' => $donation->gift_aid,
                    'recurring' => $recurring,
                    'status' => 1,
                    'created_at' => gmdate('Y-m-d H:i:s'),
                ]);

                if ($status == 'success') {
                    if ($recurring !== 'One-off') {

                        $next_payment_date = match($recurring) {
                            'weekly' => gmdate('Y-m-d H:i:s', strtotime('+1 week')),
                            'quarterly' => gmdate('Y-m-d H:i:s', strtotime('+3 months')),
                            'yearly' => gmdate('Y-m-d H:i:s', strtotime('+1 year')),
                            default => gmdate('Y-m-d H:i:s', strtotime('+1 month')),
                        };

                        $subscriptionId = $wpdb->insert(EHX_Donate::$subscription_table, [
                            'user_id' => $donation->user_id,
                            'donation_id' => $donation->id,
                            'title' => $campaign->post_title,
                            'stripe_subscription_id' => wp_rand(),
                            'stripe_subscription_price_id' => null,
                            'amount' => $donation->total_amount,
                            'recurring' => $recurring,
                            'next_payment_date'  => $next_payment_date,
                            'invoice_no' => wp_rand(),
                            'status' => 'active',
                            'payment_method' => $donation->payment_method,
                            'created_at' => gmdate('Y-m-d H:i:s'),
                        ]);
                    }

                    $wpdb->insert(EHX_Donate::$transaction_table, [
                        'donation_id' => $donation->id,
                        'amount'  => $donation->total_amount,
                        'balance'  => $donation->total_amount,
                        'created_at' => gmdate('Y-m-d H:i:s'),
                    ]);

                    $subject = 'Donation Receipt for Your Generous Contribution.';
                    $message = "Your donation has been successfully received! With your kind contribution, we're one step closer to achieving our goals and making a positive change. Thank you for your generosity and support. We couldn't do it without you!";
                    
                    EHX_Donate_Helper::send_email($input['email'], $subject, $message);
                }

                $wpdb->query($wpdb->prepare("UPDATE $donation_table SET payment_status = %s WHERE browser_session = %s", $status, $browser_session));

            }

            EHX_Donate_Helper::sessionForget('campaign');
            EHX_Donate_Helper::sessionForget('input');
            EHX_Donate_Helper::sessionForget('browser_session');

            return true;
        }

        /**
         * Generates an HTML input field with label and validation message.
         *
         * @param string $label The label text for the input field.
         * @param string $for The 'for' attribute value for the label. If not provided, defaults to the $label.
         * @param string $isType The type of input field. Can be 'input', 'select', or 'textarea'. Defaults to 'input'.
         * @param string $placeholder The placeholder text for the input field.
         * @param bool $required Whether the input field is required. Defaults to true.
         *
         * @return void Prints the HTML for the input field with label and validation message.
         */
        public static function input_field($label = '', $for = '', $isType = 'input', $placeholder = '', $data = [], $required = true, $column = 'edp-field-50')
        {
            $htmlFor = $for != null ? $for : $label;
            $isRequired = $required ? 'required' : '';

            ?>
                <div class="<?php echo esc_attr($column) ?>">
                    <?php if(!empty($label)): ?>
                        <label for="<?php echo esc_attr($htmlFor) ?>" class="edp-field-labels">
                            <?php echo esc_html(ucfirst(str_replace('_', ' ', $label))) ?> 
                            <?php if($required): ?>
                                <span>*</span>
                            <?php endif ?>
                        </label>
                    <?php endif ?>

                    <?php if($isType == 'select'): ?>
                        <select name="<?php echo esc_attr($htmlFor) ?>" id="<?php echo esc_attr($htmlFor) ?>" class="edp-input-field" <?php echo esc_attr($isRequired); ?>>
                            <?php if(!empty($placeholder)): ?>
                                <option value=""><?php echo esc_html($placeholder) ?></option>
                            <?php endif ?>
                            
                            <?php foreach($data as $key => $value): ?>
                                <option value="<?php echo esc_html(gettype($key) == 'string' ? $key : $value); ?>"><?php echo esc_html($value) ?></option>
                            <?php endforeach ?>
                        </select>

                    <?php elseif($isType == 'textarea'): ?>
                        <textarea name="<?php echo esc_attr($htmlFor) ?>" id="<?php echo esc_attr($htmlFor) ?>" class="edp-input-field" <?php echo esc_attr($isRequired); ?>></textarea>
                    <?php else: ?>
                        <input type="text" name="<?php echo esc_attr($htmlFor) ?>" id="<?php echo esc_attr($htmlFor) ?>" class="edp-input-field"  <?php echo esc_attr($isRequired); ?> /> 
                    <?php endif ?>
                    <div id="invalid_<?php echo esc_html($htmlFor) ?>"></div>
                </div>
            <?php
        }

        /**
         * Retrieves the post and associated custom fields for a given form ID.
         *
         * @param int $form_id The ID of the form.
         *
         * @return array An array containing the post object, custom fields, and form data.
         */
        private static function get_field($form_id): array
        {
            $post = get_post($form_id);
            $ehx_campaign = get_post_meta($post->ID, '_ehx_campaign', true) ?? [];
            
            return [$post, $ehx_campaign];
        }
    }
}