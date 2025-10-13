=== BrivonCare Admin Dashboard ===
Contributors: (your-username)
Requires at least: 5.0
Tested up to: 6.5
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A custom WordPress plugin to simplify the admin dashboard for BrivonCare management. It introduces custom admin pages for Carers, Jobs, Customers, and Messages, fetching all data from an external API.

== Description ==

The BrivonCare Admin Dashboard plugin is designed to replace or simplify key sections of the standard WordPress admin area by providing a unified, custom interface for core business management tasks.

Features include:
* Custom menu pages: Carers, Jobs, Customers, Messages.
* Data fetching from an external API (simulated via an API Helper class).
* Sample AJAX action for Carer verification.
* Security implemented with capability checks and nonces.

== Installation ==

1.  Upload the `brivoncare-admin-dashboard` folder to the `/wp-content/plugins/` directory.
2.  Activate the plugin through the 'Plugins' menu in WordPress.
3.  A new 'BrivonCare' menu item will appear in your admin sidebar.

== Changelog ==

= 1.0.0 =
* Initial release of the BrivonCare Admin Dashboard plugin.
* Implemented core file structure and admin menu creation.
* Added API Helper and AJAX handler.