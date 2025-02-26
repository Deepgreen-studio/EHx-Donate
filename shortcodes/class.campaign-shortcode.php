<?php

if (!class_exists('EHX_Donate_Campaign_Shortcode')) {

    class EHX_Donate_Campaign_Shortcode
    {
        public EHX_Donate_Response $response;
        public EHX_Donate_Request $request;
        const NONCE_ACTION = 'ehx_form_action';
        const NONCE_NAME = 'ehx_form_nonce';

        public $form_id;

        /**
         * Constructor for the EHX_Member class.
         *
         * Initializes the response object and adds the shortcode for the member form.
         * Also sets up the AJAX actions for handling form submissions.
         */
        public function __construct()
        {
            // Initialize the response object
            $this->response = new EHX_Donate_Response();
            $this->request = new EHX_Donate_Request();

            // Add the shortcode for the member form
            add_shortcode('ehx_campaigns', [$this, 'add_shortcode']);

            // Set up the AJAX actions for handling form submissions
            add_action('wp_ajax_ehx_members_from_submit', [$this, 'handle_form']); // When user is logged in
            add_action('wp_ajax_nopriv_ehx_members_from_submit', [$this, 'handle_form']); // When user is logged out
        }
        
        /**
         * Register the shortcode to display the form.
         *
         * @param array $attr Shortcode attributes.
         * @return string HTML output of the form.
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

            $enable_recaptcha = (bool) EHX_Member_Settings::extract_setting_value('google_recaptcha_enable', false);

            ob_start();

            $recurring = [
                esc_html__('One-off', 'ehx-donate'),
                esc_html__('Weekly', 'ehx-donate'),
                esc_html__('Monthly', 'ehx-donate'),
                esc_html__('Quarterly', 'ehx-donate'),
                esc_html__('Yearly', 'ehx-donate'),
            ];

            require EHX_DONATE_PLUGIN_DIR . 'views/shortcodes/campaign.php';

            // require EHX_MEMBER_PLUGIN_DIR . ($ehx_mode == 'profile' ? 'views/profile-form.php' : 'views/form.php');

            // if (isset($ehx_form['_ehx_mode']) && $ehx_form['_ehx_mode'] == 'register') {
            //     wp_enqueue_script('ehx-members-google-map');
            //     wp_enqueue_script('ehx-members-google-map-init');
            // }
            // else {
            //     wp_enqueue_style('ehx-members-profile-css');
            //     wp_enqueue_script('ehx-members-bootstrap-cdn');
            // }

            if ($load_stripe) {
                wp_enqueue_script('ehx-donate-stripe');
            }

            if ($this->request->filled('status') && $this->request->filled('txid') && EHX_Donate_Helper::sessionGet('browser_session') != null) {
                $this->paymentCallback();
            }

            return ob_get_clean();
        }
        
        
        /**
         * Handle user registration, login, and profile updates.
         *
         * @return mixed Success response with message and optional redirect URL, error response with error message.
         */
        public function handle_form()
        {
            $validator = new EHX_Donate_Validator();

            // Validate nonce to prevent CSRF
            $validator->validate_nonce(self::NONCE_NAME, self::NONCE_ACTION);

            $validator->validate([
                'campaign' => 'required|string|max:255',
                'recurring' => 'required|string|max:255',
                'amount' => 'required|numeric',
                'first_name' => 'required|string|min:2|max:30',
                'last_name' => 'required|string|min:2|max:30',
                'email' => 'required|string|email|max:255',
                'phone' => 'required|string|min:9|max:15',
                'address_line_1' => 'nullable|string|max:50',
                'address_line_2' => 'nullable|string|max:50',
                'city' => 'nullable|string|max:50',
                'state' => 'nullable|string|max:50',
                'country' => 'nullable|string|max:50',
                'post_code' => 'nullable|string|max:50',
            ]);

            $stripe_enable = (bool) EHX_Donate_Settings::extract_setting_value('stripe_enable', false);
            
            $amount = $this->request->input('amount');
            $service_charge = $amount * 1.4 / 100;
            $total_amount = $amount + $service_charge;
            $browser_session = uniqid();

            if($stripe_enable && $total_amount > 0) {
                try {
                    $mode = 'payment';

                    $campaign = get_page_by_path($this->request->input('campaign'), OBJECT, 'ehx-campaign'); 

                    $priceData = [
                        'currency' => 'gbp',
                        'unit_amount' => round($total_amount, 2) * 100,
                        'product_data' => [
                            'name' => $campaign->post_title,
                            'description' => $campaign->post_content,
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

                    global $wp;

                    $callback = home_url($wp->request);
                    $cancel_url = "$callback?status=cancel&txid=$browser_session";
                    $success_url = "$callback?status=success&txid=$browser_session";
        
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

            $display_name = $this->request->input('first_name');
            if ($this->request->filled('last_name')) {
                $display_name .= ' ' . $this->request->input('last_name');
            }
            
            global $wpdb;

            $user_id = wp_insert_user([
                'user_login' => str_replace(' ', '-', strtolower($display_name)),
                'user_pass' => rand(),
                'user_email' => $this->request->input('email'),
                'display_name' => $display_name,
            ]);

            $wpdb->insert(EHX_Donate::$donation_table, [
                'user_id' => $user_id,
                'invoice' => 'stripe',
                'processing_fee' => $service_charge,
                'gift_aid' => $this->request->boolean('gift_aid'),
                'total_amount' => $total_amount,
                'charge'  => 0,
                'payment_method' => 'stripe',
                'payment_status' => 'pending',
                'browser_session' => $browser_session,
                'created_at' => gmdate('Y-m-d H:i:s'),
            ]);

            EHX_Donate_Helper::sessionSet('browser_session', $browser_session);
            
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
                        <select name="<?php echo esc_attr($htmlFor) ?>" id="<?php echo esc_attr($htmlFor) ?>" class="edp-input-field" <?php echo $isRequired; ?>>
                            <?php if(!empty($placeholder)): ?>
                                <option value=""><?php echo esc_html($placeholder) ?></option>
                            <?php endif ?>
                            
                            <?php foreach($data as $key => $value): ?>
                                <option value="<?php echo esc_html(gettype($key) == 'string' ? $key : $value); ?>"><?php echo esc_html($value) ?></option>
                            <?php endforeach ?>
                        </select>

                    <?php elseif($isType == 'textarea'): ?>
                        <textarea name="<?php echo esc_attr($htmlFor) ?>" id="<?php echo esc_attr($htmlFor) ?>" placeholder="<?php echo esc_attr($placeholder) ?>" class="edp-input-field" <?php echo $isRequired; ?>>

                        </textarea>
                    <?php else: ?>
                        <input type="text" name="<?php echo esc_attr($htmlFor) ?>" id="<?php echo esc_attr($htmlFor) ?>" placeholder="<?php echo esc_attr($placeholder) ?>" class="edp-input-field"  <?php echo $isRequired; ?> /> 
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
        
        private function paymentCallback()
        {
            $txid = $this->request->input('txid');

            $browser_session = EHX_Donate_Helper::sessionGet('browser_session');
            if ($browser_session == null || $browser_session != $txid) {
                return home_url('/');
            }

            $status = $this->request->input('status');
            
            $donation_table = EHX_Donate::$donation_table;

            global $wpdb;

            $donation = $wpdb->get_row($wpdb->prepare("SELECT * FROM $donation_table WHERE `browser_session` = %s", $browser_session));
            if($donation != null) {
                $wpdb->insert(EHX_Donate::$donation_items_table, [
                    'donation_id' => $donation->id,
                    'campaign_id' => 1,
                    'subscription_id' => $this->request->boolean('gift_aid'),
                    'amount'  => $donation->total_amount,
                    'gift_aid' => $donation->gift_aid,
                    'recurring' => $donation->recurring,
                    'status' => 1,
                    'created_at' => gmdate('Y-m-d H:i:s'),
                ]);
            }
        }

    }
}