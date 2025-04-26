<?php
declare(strict_types=1);

namespace EHxDonate\Shortcodes;

use EHxDonate\Classes\Settings;
use EHxDonate\Classes\View;
use EHxDonate\Helpers\Helper;
use EHxDonate\Models\Donation;
use EHxDonate\Models\DonationItem;
use EHxDonate\Models\Transaction;
use EHxDonate\Services\Request;
use EHxDonate\Services\Response;
use EHxDonate\Services\Validator;
use EHxRecurringDonation\Helpers\Helper as RecurringDonationHelper;

if (!defined('ABSPATH')) {
    exit;
}

class DonationFormShortcode
{
    public Response $response;
    
    const NONCE_ACTION = 'ehxdo_form_submit';

    const TRANSIENT = 'ehxdo_form_session';
    const TOKEN_EXPIRY = 1800; // 30 min in seconds

    protected object|bool $transient;

    public $form_id;

    /**
     * Constructor for the EHXDo_Donate class.
     *
     * Initializes the response object and adds the shortcode for the donate form.
     * Also sets up the AJAX actions for handling form submissions.
     */
    public function __construct()
    {
        // Initialize the response object
        $this->response = new Response();

        // Add the shortcode for the donate form
        add_shortcode('ehxdo_donation_form', [$this, 'addShortcode']);

        // Set up the AJAX actions for handling form submissions
        add_action('wp_ajax_ehxdo_form_submit', [$this, 'handleFormSubmit']); // When user is logged in
        add_action('wp_ajax_nopriv_ehxdo_form_submit', [$this, 'handleFormSubmit']); // When user is logged out
    }
    
    /**
     * Adds a shortcode for displaying a donation form for a specific campaign.
     *
     * The function retrieves the current campaign ID, retrieves the associated post and custom fields,
     * fetches all published 'ehxdo-campaign' posts, and generates a donation form with the necessary fields.
     * It also handles the payment callback and displays the payment form.
     *
     * @return string The rendered donation form.
     */
    public function addShortcode()
    {
        $campaign_id = get_the_ID();

        [$post, $ehx_campaign] = self::getField($campaign_id);

        $campaigns = [];
        if ($post == null || ($post != null && $post->post_type != 'ehxdo-campaign')) {
            $args = [
                'post_type' => 'ehxdo-campaign',
                'posts_per_page' => -1, // Get all posts
                'post_status'    => 'publish'
            ];

            foreach (get_posts($args) as $post) {
                $campaigns[$post->post_name] = $post->post_title;
            }
        }

        $status = Request::getInput('status');
        $txid = Request::getInput('txid');
        if (!empty($status) && !empty($txid)) {

            $this->transient = get_transient(self::TRANSIENT);

            $payment_callback = !empty($status) && !empty($txid) && $this->transient !== false;
            if($payment_callback) {
                $this->paymentCallback($status, $txid);
            }
        }

        $enable_recaptcha = defined('EHXRC_VERSION') && (bool) Settings::extractSettingValue('google_recaptcha_enable', false);
        $enable_gift_aid = defined('EHXGA_VERSION') && (bool) Settings::extractSettingValue('enable_gift_aid', false);

        if ($enable_recaptcha) {
            wp_enqueue_script('ehxrc-recaptcha');
        }
        
        global $wp;

        $content = View::render('shortcodes/donation-form', [
            'post' => $post,
            'ehx_campaign' => $ehx_campaign,
            'campaigns' => $campaigns,
            'status' => $status,
            'txid' => $txid,
            'callback' => home_url($wp->request),
            'payment_callback' => $payment_callback ?? false,
            'enable_recaptcha' => $enable_recaptcha,
            'enable_gift_aid' => $enable_gift_aid,
        ], true);

        return $content;
    }
    
    /**
     * Handles the form submission for donations.
     *
     * Validates form input, processes payments using Stripe API,
     * and saves donation records to the database.
     *
     * @return void Returns true on successful payment, error message on failure.
     */
    public function handleFormSubmit()
    {
        $request = new Request();

        // Initialize validator
        $validator = new Validator();

        // Validate nonce to prevent CSRF
        $validator->validate_nonce(Helper::NONCE_NAME, self::NONCE_ACTION);

        // Validate reCaptcha
        $response = $validator->validateRecaptcha($request->input('g-recaptcha-response'));
        
        $enable_gift_aid = $request->boolean('gift_aid');
        $required = $enable_gift_aid ? 'required' : 'nullable';

        // Validate input data
        $validator->validate([
            'campaign' => 'required|string|max:255',
            'recurring' => defined('EHXRD_VERSION') ? 'required' : 'nullable' . '|string|max:255',
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
            'post_code' => $required . '|string|max:50'
        ]);

        // Calculate total amount with service charge
        $amount = (float) $request->input('amount');
        $service_charge = $amount * 1.4 / 100;
        $total_amount = $amount + $service_charge;
        $browser_session = uniqid();

        // Handle Stripe payment if enabled
        $stripe_enable = (bool) Settings::extractSettingValue('stripe_enable', false);
        if ($stripe_enable && $total_amount > 0) {
            $campaign = get_page_by_path(page_path: $request->input('campaign'), post_type: 'ehxdo-campaign');

            $response = $this->handlePayment($total_amount, $browser_session, $campaign->post_title, $request->input('callback'), $request->input('recurring'));
            if (!$response || !isset($response->url)) {
                return $this->response->error(__('Payment processing failed. Please try again.', 'ehx-donate'));
            }
        }

        // Create or update user
        $user_id = $this->createORUpdateUser($request);
        if (is_wp_error($user_id)) {
            return $this->response->error(__('Failed to create user. Please try again.', 'ehx-donate'));
        }

        // Save donation record
        $donation_id = (new Donation)->insert([
            'user_id' => $user_id,
            'invoice' => 'stripe',
            'processing_fee' => $service_charge,
            'gift_aid' => $request->boolean('gift_aid'),
            'amount' => $amount,
            'total_amount' => $total_amount,
            'charge' => 0,
            'payment_method' => 'stripe',
            'payment_status' => 'pending',
            'browser_session' => $browser_session
        ]);

        if (!$donation_id) {
            return $this->response->error(__('Failed to save donation record. Please try again.', 'ehx-donate'));
        }

        // Save user meta data
        $this->saveUserMeta($user_id, $request);

        // Store validated input and browser session in session
        set_transient(
            self::TRANSIENT, 
            (object) [
                'input' => $validator->validated(), 
                'browser_session' => $browser_session, 
                'campaign' => $campaign ?? null
            ], 
            self::TOKEN_EXPIRY
        );

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
    private function createORUpdateUser(Request $request)
    {
        $display_name = $request->input('first_name');
        if ($request->filled('last_name')) {
            $display_name .= ' ' . $request->input('last_name');
        }

        $user_email = $request->input('email');

        $user = get_user_by('email', $user_email);
        if($user) {
            return $user->ID;
        }

        $user_login = str_replace(' ', '-', strtolower($display_name));
        $user = get_user_by('login', $user_login);

        if($user) {
            $user_login .= wp_rand();
        }

        return wp_insert_user([
            'user_login' => $user_login,
            'user_pass' => wp_generate_password(),
            'user_email' => $user_email,
            'display_name' => $display_name,
        ]);
    }

    /**
     * Save user meta data based on form input.
     *
     * @param int $user_id
     */
    private function saveUserMeta($user_id, Request $request)
    {
        add_user_meta($user_id, 'title', $request->input('title'));
        add_user_meta($user_id, 'first_name', $request->input('first_name'));
        add_user_meta($user_id, 'last_name', $request->input('last_name'));
        add_user_meta($user_id, 'phone', $request->input('phone'));

        if ($request->boolean('gift_aid')) {
            $address = [
                'address_line_1' => $request->input('address_line_1'),
                'address_line_2' => $request->input('address_line_2'),
                'city' => $request->input('city'),
                'state' => $request->input('state'),
                'country' => $request->input('country'), 
                'post_code' => $request->input('post_code'),
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
    private function handlePayment($total_amount, $browser_session, $post_title = null, $callback = null, $recurring = null)
    {
        try {
            $mode = 'payment';

            $priceData = [
                'currency' => 'gbp',
                'unit_amount' => round($total_amount, 2) * 100,
                'product_data' => [
                    'name' => $post_title ?? esc_html__('Quick Donation', 'ehx-donate'),
                    // 'description' => substr(strip_tags($campaign->post_content), 20),
                ],
            ];

            if (defined('EHXRD_VERSION') && $recurring !== RecurringDonationHelper::RECURRING_ONEOFF) {
                $interval = match ($recurring) {
                    RecurringDonationHelper::RECURRING_WEEKLY => ['interval' => 'week'],
                    RecurringDonationHelper::RECURRING_QUARTERLY => ['interval' => 'day', 'interval_count' => 15],
                    RecurringDonationHelper::RECURRING_YEARLY => ['interval' => 'year'],
                    default => ['interval' => 'month']
                };
                $priceData['recurring'] = $interval;

                $mode = 'subscription';
            }

            $items[] = [
                'price_data' => $priceData,
                'quantity' => 1,
            ];

            \Stripe\Stripe::setApiKey(esc_html(Settings::extractSettingValue('stripe_client_secret')));

            $payment_method_types = ['card'];

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

        } catch (\Exception $e) {
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
    private function paymentCallback($status, $txid): bool|string
    {
        if ($this->transient?->browser_session != $txid) {
            wp_safe_redirect(home_url());
            exit;
        }

        $donation = (new Donation())->where('browser_session', $this->transient?->browser_session)->where('payment_status', 'pending')->first();
        if($donation) {
            $recurring = $input['recurring'] ?? esc_html('One-off');

            (new DonationItem)->insert([
                'donation_id' => $donation->id,
                'campaign_id' => $this->transient?->campaign?->ID ?? null,
                'amount'  => $donation->total_amount,
                'gift_aid' => $donation->gift_aid,
                'recurring' => $recurring,
                'status' => 1,
            ]);

            if ($status == 'success') {
                (new Transaction)->insert([
                    'donation_id' => $donation->id,
                    'amount'  => $donation->total_amount,
                    'balance'  => $donation->total_amount,
                ]);
                
                $this->sendConfirmationMail($donation->total_amount, $donation->browser_session);
            }

            (new Donation())->where('browser_session', $this->transient?->browser_session)->update(['payment_status' => $status]);
        }

        delete_transient(self::TRANSIENT);
        $this->transient = false;

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
    public static function inputField($label = '', $for = '', $isType = 'input', $placeholder = '', $data = [], $required = true, $column = 'edp-field-50')
    {
        $htmlFor = $for != null ? $for : $label;
        $isRequired = $required ? 'required' : '';

        include EHXDO_PLUGIN_DIR . 'views/frontend/components/input-field.php';
    }

    /**
     * Retrieves the post and associated custom fields for a given form ID.
     *
     * @param int $form_id The ID of the form.
     *
     * @return array An array containing the post object, custom fields, and form data.
     */
    private static function getField($form_id): array
    {
        $post = get_post($form_id);
        $ehx_campaign = get_post_meta($post->ID, '_ehx_campaign', true) ?? [];
        
        return [$post, $ehx_campaign];
    }

    /**
     * Sends a confirmation email to the donor after a successful donation.
     *
     * @param array $input The input data containing the donor's details.
     * @param float $total_amount The total amount donated.
     * @param string $trx The unique transaction identifier.
     *
     * @return bool Whether the email was sent successfully.
     */
    private function sendConfirmationMail($total_amount, $trx)
    {
        $fromName  = Settings::extractSettingValue('mail_appears_from', get_bloginfo('name'));

        $subject = esc_html__('Thank You for Your Generous Donation!', 'ehx-donate');
        $name = $this->transient?->input['first_name'] . ' ' . $this->transient?->input['last_name'];
        $total_amount = Helper::currencyFormat($total_amount);

        $home_url = home_url();

        $content = View::render('mail/donation-confirmation', [
            'fromName' => $fromName,
            'subject' => $subject,
            'name' => $name,
            'total_amount' => $total_amount,
            'home_url' => $home_url,
            'trx' => $trx,
        ], true);

        Helper::sendEmail($this->transient?->input['email'], $subject, $content);

        return true;
    }

}