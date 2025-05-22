# Changelog

All notable changes to the **EHx Donate** plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.3] – 2025-05-11

### Added
- Mailchimp Addon for syncing donor emails to Mailchimp lists.

## [1.1.2] – 2025-04-30

### Added
- Currency support with formatting

### Improved
- Currency conversion system based on selected site currency

### Fixed
- Minor UI and logic bugs in campaign listing and donation form

## [1.1.1] – 2025-04-29

### Added
- Premium addon compatibility
- Security vulnerability in form submissions
- Documentation for premium features

### Improved
- Rebuilt model query system
- Database interactions for large datasets
- Template handling with override support.

### Fixed
- Donation recording edge cases

## [1.1.0] – 2025-04-15

### Added
- **Object-Oriented Structure**: Refactored entire plugin using modern OOP principles.
- **Addon System**: Introduced dynamic addon management (install, activate, deactivate, delete).
- **Google reCAPTCHA Addon**: Protect donation forms from spam submissions via addon.
- **Laravel-style Models**: Introduced Eloquent-inspired model structure for interacting with donations, campaigns, and related post meta.
- **Database Joins Support**: Added internal helpers for complex WP_Query-like joins between CPTs, taxonomies, and metadata.
- **Campaign List Elementor Widget**: Added layout and styling options.

### Improved
- Shortcode rendering performance and reliability.
- Code readability and maintainability.
- Template handling with override support.
- Compatibility with newer WordPress and PHP versions.

### Fixed
- Minor layout bugs in campaign grid/list views.
- Issues with post inclusion/exclusion in campaign shortcode.

## [1.0.0] – 2025-04-01

### Added
- Initial release of EHx Donate.
- AJAX-powered donation form with real-time Stripe payment.
- Custom post type for `ehxdo-campaign` and campaign listings.
- Role-based access control for donation management.
- `[ehxdo_donation_form]` shortcode.
- `[ehxdo_campaign_lists]` shortcode for displaying campaign archives.
- `[ehxdo_donation_table]` shortcode to display a customizable list of donations.