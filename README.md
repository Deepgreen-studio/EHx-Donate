# EHx Donate

**Contributors:** [EH Studio](https://profiles.wordpress.org/ehstudio), [Sujit Sarkar](https://profiles.wordpress.org/iamsujitsarkar)
**Tags:** donation, fundraising, charity, nonprofit, campaigns
**Requires at least:** 5.8  
**Tested up to:** 6.8  
**Requires PHP:** 7.4  
**Stable tag:** 1.1.2
**License:** GPLv2 or later  
**License URI:** https://www.gnu.org/licenses/gpl-2.0.html  

**EHx Donate** – A feature-rich WordPress donation plugin with modern architecture and addon support.


## Description

The EHx Donate plugin enhances donation management in WordPress. Built with an object-oriented structure, it supports AJAX-based forms, secure payment integrations, and addon handling—all with optimal performance and flexibility.

### 🔑 Key Features

- ⚡ **AJAX-Based Submissions** – Smooth, no-reload donation experience.
- 🧱 **OOP Architecture** – Modular, scalable, and maintainable code.
- ➕ **Addon Support System** – Download, install, and activate addons from within the plugin.
- 🔐 **Google reCAPTCHA Addon** – Adds spam protection to donation forms.
- 🌐 **Multilingual Ready** – Fully translatable via `ehx-donate` text domain.
- 🚀 **Performance Optimized** – Lightweight and efficient.
- 🗃️ **Custom Post Type for Donations** – Keep donations organized.
- 🎨 **Theme Compatible** – Works with any modern WordPress theme.
- 🔒 **Secure & Scalable** – Built following WordPress best practices.

## Installation

1. Upload the `ehx-donate` folder to `/wp-content/plugins/`.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Use any of the shortcodes listed below to display plugin functionality.

## Usage

### Shortcodes

- `[ehxdo_donation_form]` – Display the donation form.
- `[ehxdo_campaign_lists]` – Show a customizable list of campaigns.
- `[ehxdo_donation_table]` – Display a table listing recent donations.

You can place these shortcodes in posts, pages, or widgets.


## FAQ

### How do I display the donation form?

Use the shortcode `[ehxdo_donation_form]` on any page or post.

### Can I customize the donation form?

Yes! You can override templates or apply custom styles via CSS.

### Does the plugin support multiple payment gateways?

Stripe is supported in the free version. Future updates will support **PayPal** and **WooCommerce**.

### Is EHx Donate compatible with my theme?

Yes! It works with all modern WordPress themes.

## Screenshots

1. Dashboard Donation List  
2. Plugin Settings Panel  
3. Campaign Listings  
4. Transactions Overview  
5. Donation Form – Step 1  
6. Donation Form – Step 2

## External Services

This plugin integrates with the following:

### 1. Stripe PHP Library
- **Used for**: Server-side payment processing  
- **Data**: Tokens, transaction amounts, customer metadata  
- **When**: During donation and verification  
- **Links**:
  - [Terms of Service](https://stripe.com/legal)  
  - [Privacy Policy](https://stripe.com/privacy)  
  - [GitHub](https://github.com/stripe/stripe-php)

### 2. Google reCAPTCHA (Addon)
- **Used for**: Spam protection  
- **Data**: Form interaction validation  
- **When**: On form submission  
- **Links**:
  - [Terms of Service](https://policies.google.com/terms)  
  - [Privacy Policy](https://policies.google.com/privacy)


## Data Handling

- Payment processing is securely done via Stripe servers.
- Stripe tokens are stored server-side for verification only.
- reCAPTCHA validates form submissions via Google's secure APIs.


## User Consent

By using this plugin, you agree that:

- Payment is handled by Stripe.
- Google reCAPTCHA is used for spam protection (if enabled).
- External libraries are used under their respective open-source licenses.


## Changelog

Detailed changelog available in [CHANGELOG.md](CHANGELOG.md).


## License & Credits

This plugin is licensed under the **GPLv2 or later** license.

**Thanks to:**
- [Stripe](https://github.com/stripe/stripe-php) (MIT License)
- [Google reCAPTCHA](https://www.google.com/recaptcha/)
