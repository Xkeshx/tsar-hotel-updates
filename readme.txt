=== TSAR HOTEL Website Updates ===
Contributors: tsar-hotel-project
Requires at least: 6.0
Requires PHP: 7.4
Stable tag: 1.3.1
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Reversible WordPress fixes plus an optional, administrator-controlled TSAR HOTEL homepage refresh.

== Description ==

Fresh activation enables only homepage-qualified section-menu links and hiding of empty Astra footer social icons. The redesigned homepage and custom navigation remain off until an authorised administrator explicitly enables them on staging.

The optional refresh uses TSAR's existing homepage media/form, adds the six requested destinations, a four-line mobile menu, published MotoPress room types only, events/offers sections, and a floating link to the existing reservation page. The public catalogue checked for this release contains Studio Suite and Standard Room; the room cards are generated from published MotoPress records, with no hard-coded extra categories or rates. The design includes no airport option, spa, copied La Falaise photos/copy/prices/amenities/ratings/claims, sample reviews, invented ratings, or fake special offers/events. WordPress content entries for offers and events are published by hotel staff. Guest reviews remain unpopulated until an official TSAR Google review URL is supplied.

The redesign and enquiry panel are English-only; there is no language switcher. Common third-party language controls are hidden while the redesign is active. This plugin does not disable a separate multilingual plugin or prevent its French routes from loading. Configure any separate multilingual plugin for English-only operation if that is required for the entire website.

The saved homepage blocks are not rewritten. Disabling the redesign restores the saved content and Astra header. The package does not install a multilingual plugin or auto-translate existing pages.

== Installation ==

1. From the source repository, run `./build-zip.sh` to create `dist/tsar-hotel-updates-v1.3.1.zip`.
2. An authorised administrator uploads that ZIP at Plugins > Add New > Upload Plugin.
3. Activate on a backed-up staging site first.
4. Open Settings > TSAR HOTEL Updates, review the options and enable the homepage refresh only after staging approval.
5. Add only verified contact and profile links. Add offers and events from their dashboard menus when approved content is ready.
6. Clear relevant caches and complete the acceptance checklist in ADMIN-HANDOVER.md before production.

== Frequently Asked Questions ==

= Does activation redesign the website? =
No. The redesign is disabled by default. It must be explicitly enabled in the plugin settings.

= Does the redesign change stored page content or booking records? =
No. It filters the homepage output only. The original blocks remain stored and appear again if the redesign is disabled. It does not change MotoPress inventory, availability, rates, bookings, payments or guest data.

= Which rooms appear in the redesign? =
Only published MotoPress room-type records appear. The public catalogue checked for this release contains Studio Suite and Standard Room. The cards link to those records' detail pages; the redesign does not create room categories or rates.

= Is the whole WordPress installation forced to English? =
The redesign and its enquiry panel render in English and contain no language switcher. Common switch controls are hidden while the redesign is active. A separate multilingual plugin may still serve French URLs or translated pages, so an administrator must disable French in that plugin for a site-wide English-only setup.

= How do Special Offers and Events get content? =
Hotel staff create and publish entries in the Special Offers and Events dashboard menus. Only published entries are shown. Event enquiry links lead to the existing homepage contact section/form when available.

= Are Google ratings or review quotes included? =
No. The review section stays blank until an official TSAR Google review URL is set. When configured, the page displays a link only; no rating/count/quote is synthesized.

= How do I undo it? =
Turn off the homepage refresh option and clear caches to restore the saved home content and Astra header. Deactivate the plugin to remove the other runtime filters. Offer/event records are not deleted, but their edit screens require the plugin to be active.

== Changelog ==

= 1.3.1 =
* Made the redesign and enquiry panel English-only and removed multilingual switcher integration.
* Replaced the four hard-coded room cards with cards generated only from published MotoPress room types.
* Added an upgrade migration that sets the panel language to English and keeps the redesign disabled.
* Updated release documentation and checks to reflect the English-only, MotoPress-driven scope.

= 1.3.0 =
* Added the opt-in, reversible homepage and six-link custom navigation, four-line mobile menu and floating booking action.
* Added editor-managed Special Offers and Events content types and the existing-form event enquiry path.
* Kept Google reviews empty until an official URL is configured.
* Added a migration that keeps the new redesign disabled on upgrade.

= 1.2.3 =
* Removed the unreviewed homepage redesign, slider and hard-coded unverified hotel details.
* Made mobile layout repairs opt-in and added a migration to disable prior default-on changes.
* Switched to a single cacheable local stylesheet and aligned package documentation.

= 1.2.2 =
* Previous release. Its runtime was not covered by the historic 1.0.0 QA report.

= 1.0.0 =
* Initial administrator-review package based on the public website audit of 30 September 2026.
