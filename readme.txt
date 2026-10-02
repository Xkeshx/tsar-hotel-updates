=== TSAR HOTEL Website Updates ===
Contributors: tsar-hotel-project
Requires at least: 6.0
Requires PHP: 7.4
Tested up to: 7.1
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A private, reversible technical update for the existing TSAR HOTEL WordPress site.

== Description ==

First activation enables homepage-qualified section-menu links and hides empty Astra footer social icons. Approved reception contacts, an enquiry panel, homepage brand wording, SureRank-compatible homepage SEO and limited demo/utility-page indexing changes are optional.

No rates, rooms, bookings, payments, guest data, domain settings or other plugins are changed. Consent consolidation still requires administrator review. No external assets, analytics, cookies or update service are added.

Read ADMIN-HANDOVER.md and TEST-REPORT.md before installing. Back up files and the database, review the code and test on the actual staging site before production deployment.

== Installation ==

1. An authorised administrator uploads the plugin ZIP at Plugins > Add New > Upload Plugin.
2. Activate on staging first.
3. Open Settings > TSAR HOTEL Updates.
4. Verify the two default repairs; approve and configure optional modules.
5. Clear caches and complete the acceptance checklist before production installation.

== Frequently Asked Questions ==

= Does activation fix the entire hotel website? =
No. It automates defined technical changes only. Actual photos, room/rate reconciliation, consent settings and enquiry/booking tests remain separate tasks.

= Will my reservation data be modified? =
No. The plugin stores only its own settings and filters front-end output.

= How do I undo it? =
Deactivate the plugin and clear caches. If optional indexing cleanup was enabled, rebuild the sitemap cache too. Original pages and menu records are not rewritten.

= Does it replace SureRank? =
No. Its optional homepage SEO module integrates with SureRank filters. Other recognised SEO plugins cause those modules to be suspended.

== Changelog ==

= 1.0.0 =
* Initial administrator-review package based on the public website audit of 30 September 2026.
