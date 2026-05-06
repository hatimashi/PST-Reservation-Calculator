=== PST Reservation Calculator ===
Contributors: hatimashi
Tags: rental, price calculator, vehicle rental, reservation, booking
Requires at least: 5.5
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

PST Reservation Calculator is a tool for vehicle rentals, letting users select dates, see seasonal pricing, and send booking inquiries instantly.

== Description ==

PST Reservation Calculator is a tool for vehicle rentals, letting users select dates, see seasonal pricing, and send booking inquiries instantly.

= Key features =

* **Seasonal pricing** — define as many season periods as you need per vehicle type (e.g. low / mid / high season), each with its own daily rate and date range.
* **Cross-season bookings** — if a rental spans two seasons the price is automatically split and summed proportionally.
* **Three vehicle types** — motorhome (`kamper`), trailer (`przyczepa`), car (`samochod`). Each type has independent season definitions.
* **Configurable fees** — set the service fee (net + gross) and refundable deposit per vehicle type from the admin panel.
* **Configurable VAT** — set your VAT rate once in the admin panel; the gross price is always calculated from that value.
* **Reservation e-mail** — when a customer submits the contact form, a formatted HTML e-mail is sent to a configurable address.
* **Simple shortcode** — embed the calculator anywhere with `[pst_reservation type="kamper"]`.

= Shortcode usage =

`[pst_reservation type="kamper"]`
`[pst_reservation type="przyczepa"]`
`[pst_reservation type="samochod"]`

Place one shortcode per page/post for each vehicle type you want to feature.

= Admin panel =

The plugin adds a **PST Reservation** menu item in the WordPress admin area with three tabs:

* **Sezony** — edit the start date, end date and daily rate for every season period, separately for each vehicle type. Only the month and day of the stored dates matter; the year is ignored at runtime so the seasons repeat automatically every year.
* **Ustawienia** — set the VAT percentage and the service fee / deposit amounts per vehicle type.
* **Kontakt** — set the e-mail address that receives reservation inquiries. Defaults to the site administrator's address.

= Privacy =

This plugin does not collect, store, or transmit any personal data on its own. Reservation inquiry data (name, phone, e-mail, dates, price) is sent directly to the site owner's e-mail address and is not stored in the database.

== Installation ==

1. Upload the `pst-reservation-calculator` folder to the `/wp-content/plugins/` directory, or install the plugin through the **Plugins > Add New** screen in WordPress.
2. Activate the plugin through the **Plugins** screen.
3. Go to **PST Reservation** in the admin menu to configure seasons, fees, VAT and the notification e-mail.
4. Add the shortcode `[pst_reservation type="kamper"]` (or `przyczepa` / `samochod`) to any page or post where you want the calculator to appear.

== Frequently Asked Questions ==

= How do I add the calculator to a page? =

Use the shortcode `[pst_reservation type="kamper"]` in the page content or in a shortcode block. Replace `kamper` with `przyczepa` or `samochod` for other vehicle types.

= Can I show calculators for multiple vehicle types on the same site? =

Yes. Each vehicle type has its own independent season definitions and fees. You can create separate pages for each type and place the appropriate shortcode on each.

= What happens if a rental period spans two seasons? =

The plugin automatically splits the booking at the season boundary and calculates the price for each part at the correct daily rate. The totals are then summed.

= Does the year stored in season dates matter? =

No. Only the month and day from each season record are used. The year is determined dynamically from the rental dates, so seasons renew automatically each year without any manual updates.

= Where do reservation inquiries go? =

To the e-mail address set in the **Kontakt** tab of the admin panel. If no address has been set, WordPress's site administrator e-mail is used as a fallback.

= How do I configure SMTP for outgoing mail? =

This plugin uses the standard WordPress `wp_mail()` function. Configure your SMTP settings in `wp-config.php` or via a dedicated SMTP plugin (e.g. WP Mail SMTP).

= What PHP version is required? =

PHP 7.4 or higher.

== Screenshots ==

1. Front-end calculator — date picker with instant price calculation and reservation button.
2. Admin panel — season editor with editable prices and date ranges per vehicle type.
3. Admin panel — cars editor.
4. Admin panel — settings tab for VAT and service fees.
5. Admin panel — discount editor with discount name, type and VAT. 
6. Admin panel — contact tab for the notification e-mail address.
7. Reservation popup — contact form prefilled with dates and calculated price.

== Changelog ==

= 1.2.0 =
* Form rewrited using tailwind
* Cleanup js's and HTML

= 1.1.0 =
* Plugin Update feature

= 1.0.0 =
* Initial public release.
* Seasonal pricing engine with cross-season support.
* Configurable VAT, service fees and deposits per vehicle type.
* Season date/price editor in the admin panel.
* Configurable notification e-mail address.
* Full input sanitization, output escaping and nonce verification on all AJAX endpoints.

== Upgrade Notice ==

= 1.0.0 =
Initial release. No upgrade required.
