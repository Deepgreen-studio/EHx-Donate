=== EHx Donate - Easy Donation for WordPress - Charity Donation, Fundraising Donation, Nonprofit Donation, & More ===
Contributors: ehstudio, iamsujitsarkar
Tags: donation, fundraising, charity, nonprofit, campaigns
Requires at least: 5.8
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

EHx Donate – WordPress Donation Plugin. A feature-rich donation management plugin.

== Description ==

The EHx Donate plugin is designed to enhance donation management on WordPress websites. It offers seamless integration with WordPress’s built-in user system, AJAX-based forms for a smooth experience, and custom role assignments for better user management.

**Key Features:**
- **AJAX-Based Submissions** – Users can donate without page reloads.
- **OOP Architecture** – Fully object-oriented structure for better scalability and maintenance.
- **Addon Support System** – Built-in mechanism to download, install, and activate both free and premium addons.
- **Google reCAPTCHA Addon** – Adds spam protection to your donation forms.
- **Multilingual Support** – Fully translatable with the text domain `ehx-donate`.
- **Performance-Optimized** – Lightweight and efficient for fast page loading.
- **Custom Post Type for Donations** – Organize donations separately.
- **Easy Integration** – Works with any WordPress theme.
- **Secure and Scalable** – Follows WordPress coding standards.

== Installation ==

1. Upload the `ehx-donate` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the ‘Plugins’ menu in WordPress.
3. Use the **[ehxdo_donation_form]** shortcode to display the donation form.
3. Use the **[ehxdo_campaign_lists]** shortcode to display the campaigns list.
3. Use the **[ehxdo_donation_table]** shortcode to display the donation table.

== Frequently Asked Questions ==

= How do I use the plugin? =
Use the shortcode `[ehxdo_donation_form]` on any page or post to display the donation form.

= Can I customize the donation form? =
Yes! You can modify the form layout and styling using CSS or template overrides.

= Does this plugin support multiple payment methods? =
The default version supports Stripe payments. Future versions will integrate **PayPal and WooCommerce**.

= Is this plugin compatible with my theme? =
Yes! EHx Donate works with any WordPress theme.

= How do I use addons? =
Addons can be installed from the plugin settings panel. Once downloaded, they are automatically activated and ready to use.

== Screenshots ==

1. Dashboard Donation List.
2. All settings.
3. All Campaigns.
4. All transactions.
5. Donation form step one.
6. Donation form step two.

== External Services ==

This plugin integrates with the following third-party services:

1. **Stripe PHP Library**
   - Purpose: Server-side payment processing for donations
   - Data Sent: Payment tokens, transaction amounts, customer metadata
   - When: During donation processing and payment verification
   - Links:
     - [Terms of Service](https://stripe.com/legal)
     - [Privacy Policy](https://stripe.com/privacy)
     - [GitHub Repository](https://github.com/stripe/stripe-php)

2. **Google reCAPTCHA** (via addon)
   - Purpose: Spam protection for donation forms
   - Data Sent: Form interaction details
   - When: On form submission to validate human interaction
   - Links:
     - [Terms of Service](https://policies.google.com/terms)
     - [Privacy Policy](https://policies.google.com/privacy)

== Data Handling ==

All communication with external services is done securely via HTTPS. The plugin implements:

1. **Payment Processing**:
   - Uses the official Stripe PHP library (stripe/stripe-php) for server-side operations
   - Sensitive payment details are processed directly by Stripe's systems
   - Our servers only receive and store payment tokens for transaction verification

2. **Spam Protection** (via reCAPTCHA addon):
   - Validates form submissions using Google reCAPTCHA

== User Consent ==

By using this plugin, you acknowledge that:
- Payment processing is handled by Stripe's secure systems
- The stripe-php library is used under MIT license
- Google reCAPTCHA is used under its respective terms when enabled via addon

== Changelog ==

= 1.1.0 =
- Refactored plugin structure to OOP
- Introduced dynamic addon management (install, activate, deactivate, delete).
- Protect donation forms from spam submissions via addon.
- Introduced Eloquent-inspired model structure for interacting with donations, campaigns, and related post meta.
- Added Laravel-style model and database join functionality

- Refactored entire plugin to follow Object-Oriented Programming (OOP) structure.
- Introduced support for downloading, installing activating and deleting plugin addons (free & premium).
- Added reCAPTCHA addon integration for spam prevention.
- Introduced Laravel-style models and database relationship support for easier queries.
- Added new Elementor Widget: **Campaign List Widget** to visually showcase campaigns.
- Improved shortcode flexibility and filtering options.
- Minor bug fixes and performance improvements.

= 1.0.0 =
- Initial release.
- AJAX donation form added.
- Custom post type for donations.
- Basic role-based access control.

== Upgrade Notice ==

= 1.1.0 =
Major update with OOP rewrite and new addon support. Make sure to back up your site before upgrading.

== License & Credits ==

This plugin is licensed under the **GPLv2 or later**.
Credits to Stripe (MIT Licensed) and Google for their APIs.
