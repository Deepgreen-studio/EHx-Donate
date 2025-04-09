# EHx Donate

**Contributors:** ehstudio
**Tags:** donation, fundraising, membership, payment, charity  
**Requires at least:** 5.8  
**Tested up to:** 6.8  
**Requires PHP:** 7.4  
**Stable tag:** 1.0.0  
**License:** GPLv2 or later  
**License URI:** [https://www.gnu.org/licenses/gpl-2.0.html](https://www.gnu.org/licenses/gpl-2.0.html)  

EHx Donate – WordPress Donation Plugin. A feature-rich donation management plugin.

## Description

The **EHx Donate** plugin is designed to enhance donation management on WordPress websites. It offers **seamless integration** with WordPress’s built-in user system, **AJAX-based forms** for a smooth experience, and **custom role assignments** for better user management.

### Key Features:
- **AJAX-Based Submissions** – Users can donate without page reloads.
- **Multilingual Support** – Fully translatable with the text domain `ehx-donate`.
- **Performance-Optimized** – Lightweight and efficient for fast page loading.
- **Custom Post Type for Donations** – Organize donations separately.
- **Easy Integration** – Works with any WordPress theme.
- **Secure and Scalable** – Follows WordPress coding standards.

## Installation

1. Upload the `ehx-donate` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the ‘Plugins’ menu in WordPress.
3. Use the **[ehxdo_donation_form]** shortcode to display the donation form.

## Frequently Asked Questions

### How do I use the plugin?
Use the shortcode `[ehxdo_donation_form]` on any page or post to display the donation form.

### Can I customize the donation form?
Yes! You can modify the form layout and styling using CSS or template overrides.

### Does this plugin support multiple payment methods?
The default version supports Stripe payments. Future versions will integrate **PayPal, and WooCommerce**.

### Is this plugin compatible with my theme?
Yes! EHx Donate works with any WordPress theme.

## Screenshots

1. Donation form with AJAX submission.
2. Custom post type for managing donations.
3. Settings panel for customizing donations.

## External Services
This plugin integrates with the following third-party services:

1. **Stripe PHP Library**
   - Purpose: Server-side payment processing for donations
   - Data Sent: Payment tokens, transaction amounts, customer metadata
   - When: During donation processing and payment verification
   - Links:
     - [Terms of Service](https://stripe.com/legal)
     - [Privacy Policy](https://stripe.com/privacy)
     - [GitHub Repository](https://github.com/stripe/stripe-php)

== Data Handling ==

All communication with external services is done securely via HTTPS. The plugin implements:

1. **Payment Processing**:
   - Uses the official Stripe PHP library (stripe/stripe-php) for server-side operations
   - Sensitive payment details are processed directly by Stripe's systems
   - Our servers only receive and store payment tokens for transaction verification

== User Consent ==

By using this plugin, you acknowledge that:
- Payment processing is handled by Stripe's secure systems
- The stripe-php library is used under MIT license

You can disable individual services in the plugin settings if desired.

1. Donation form with AJAX submission.
2. Custom post type for managing donations.
3. Settings panel for customizing donations.

## Changelog

### 1.0.0
- Initial release.
- AJAX donation form added.
- Custom post type for donations.
- Basic role-based access control.

## Upgrade Notice

### 1.0.0
- First release, no upgrades necessary.

## License & Credits

This plugin is licensed under the **GPLv2 or later**.  
For contributions and bug reports, visit [GitHub Repository](https://github.com/ehstudio/ehx-donate).

