=== EHx Donate ===
Contributors: EH Studio
Short Description: The EHx Donate plugin is a feature-rich tool designed to enhance donation management on your WordPress website
Tags: user registration, frontend forms, AJAX, validation, custom fields, membership, roles
Requires at least: WordPress 6.7
Tested up to: WordPress 6.7
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv3 License
License URI: https://www.gnu.org/licenses/gpl-3.0.html

== Description ==

The EHx Donate plugin is a feature-rich tool designed to enhance donation management on your WordPress website. With a focus on user-friendly forms, AJAX submissions, and custom role assignments, this plugin makes it easy to handle donations and memberships while seamlessly integrating with WordPress’s built-in user system.. 

== Key Features ==

- **Fully customizable frontend registration forms**: Create forms tailored to your needs.
- **AJAX-based form submission**: Provides a seamless user experience without page reloads.
- **Create and assign WordPress roles automatically**: Assign roles to new users during registration.
- **Support for custom fields**: Collect additional user information like phone, address, and membership type.
- **Multilingual ready**: Supports translation via the `ehx-donate` text domain.
- **Lightweight and optimized for performance**: Built for speed and efficiency.
- **Easy to integrate**: Works seamlessly with any WordPress theme.

== Installation ==

1. Download the plugin ZIP file.
2. Go to your WordPress admin dashboard.
3. Navigate to **Plugins > Add New > Upload Plugin**.
4. Upload the ZIP file and click **Install Now**.
5. Once installed, click **Activate Plugin**.

Alternatively, you can install the plugin manually:
1. Extract the ZIP file.
2. Upload the `ehx-donate` folder to the `/wp-content/plugins/` directory.
3. Go to **Plugins** in your WordPress admin dashboard and activate the **EHx Donate** plugin.

== Configuration ==

After activating the plugin, you can start using it immediately. No additional configuration is required. However, you can customize the registration forms and validation rules by editing the plugin's code or using hooks and filters.

== Usage ==

= Creating Registration Forms =
1. Use the provided shortcode `[ehx_member_form]` to display the registration form on any page or post.
2. Customize the form fields and validation rules in the plugin's code.

= AJAX Form Submission =
The plugin uses AJAX for form submissions, ensuring a smooth user experience without page reloads.

= Custom Fields =
Add custom fields to the registration form by modifying the plugin's code. Supported field types include text, email, phone, and more.

= Multilingual Support =
The plugin is translation-ready. Use the `ehx-donate` text domain to translate the plugin into your preferred language.

== Frequently Asked Questions (FAQ) ==

= How do I customize the registration form? =
You can customize the form fields and validation rules by editing the plugin's code. Look for the `EHX_Member` class and modify the form logic as needed.

= Can I use this plugin for membership websites? =
Yes, the plugin is ideal for membership websites. It supports custom fields, role assignment, and robust validation.

= Is the plugin compatible with multilingual sites? =
Yes, the plugin is translation-ready. Use the `ehx-donate` text domain to translate the plugin into your preferred language.

= How do I handle errors during form submission? =
The plugin includes built-in error handling. If validation fails, error messages will be displayed to the user.

== Changelog ==

= 1.0.0 =
- Initial release of the Ehx Donates plugin.
- Added support for customizable frontend registration forms.
- Implemented AJAX-based form submission.
- Integrated role assignment for new users.
- Added support for custom fields.
- Made the plugin translation-ready.

== License ==

This plugin is licensed under the **GPLv3 License**. See the [LICENSE](https://www.gnu.org/licenses/gpl-3.0.html) file for more details.

== Support ==

For support, feature requests, or bug reports, please visit the [plugin's support page](https://wordpress.org/plugins/ehx-donate) or contact the developer at [EH Studio](https://eh.studio).

---

Thank you for using **Ehx Donates**! We hope this plugin simplifies your user registration process and enhances your WordPress site.