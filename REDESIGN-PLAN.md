# TSAR HOTEL redesign — reference review and implementation map

**Reviewed:** 3 October 2026
**Implementation:** v1.3.1 source is staged in the plugin as an opt-in refresh.
**Deployment:** No live WordPress admin access or production deployment occurred.

## Objective and reference findings

The structure and visual rhythm of [La Falaise Bonapriso](https://www.lafalaisebonapriso.com/en) are adapted for TSAR HOTEL—not its name, copy, prices, photographs, amenities, reviews or reputation. The reference review covered the home, rooms, restaurant, coffee/bar, events, gallery and contact pages via read-only text extraction. Exact colors, spacing, responsive behavior and animation were not browser-verified.

The useful reference pattern is a compact hotel header, prominent booking action, image-led hero, room cards, separated food/drink areas, offer cards, an event enquiry flow, gallery, reviews and clear contact/booking links. La Falaise's Events page uses event cards/services and an enquiry flow; offers lead guests toward booking. TSAR's version follows that visitor workflow using only TSAR-owned content and existing booking/contact mechanisms.

## User-confirmed decisions

1. The redesigned website UI is **English only**. Remove the redesign's language switcher and French interface copy. Do not claim separate French routes are disabled unless confirmed in WordPress settings.
2. Room cards must come only from **published MotoPress room types**. The public catalogue checked for this release contains **Studio Suite** and **Standard Room**; do not add fixed/invented room categories.
3. Keep the six exact navigation labels in the desktop header and hamburger menu: **Rooms & Suites, Restaurant, Snack Lounge, Events, Gallery, Contact**.
4. Keep the floating booking action linked to TSAR's existing reservation flow.
5. Feature **Online booking, Special offers, Events and Guest reviews**.
6. Use the La Falaise offers/events visitor workflow with TSAR-owned details. Do not add an airport option or spa.
7. Leave reviews unpopulated until TSAR supplies a verified official link/details.

## Implemented structure in the source

### Header and booking

- Custom TSAR header replaces the Astra masthead only when the new setting is explicitly enabled.
- Desktop and four-line mobile menus use the same six destinations: **Rooms & Suites, Restaurant, Snack Lounge, Events, Gallery, Contact**.
- **Book your stay** and the floating booking action link to TSAR's existing `/reservations/` page.
- Restaurant, Snack Lounge, Events, Gallery and Contact point to homepage anchors while dedicated pages do not exist; Rooms & Suites links to `/accommodations/`.
- The redesign has English-only copy, no language switcher and no French contact-panel text. Common Polylang, WPML, TranslatePress and GTranslate switcher controls are hidden while the redesign is active.
- This plugin does not deactivate a separate multilingual plugin or block its French routes. To make the entire WordPress site English-only, an authorised administrator must also disable French in that plugin. The plugin and route configuration could not be identified or verified without admin access.
- No airport option, spa or wellness section is added.

### Landing-page sections

1. Image-led TSAR hero and direct booking CTA. The first available TSAR image in saved homepage output is reused; no competitor/remote image assets are included.
2. Short TSAR introduction.
3. Room cards queried from published `mphb_room_type` posts in English. Cards use the MotoPress title, excerpt, featured image and detail link. The redesign does not invent category names, rates, availability or room facts.
4. Separate Restaurant and Snack Lounge sections.
5. Service cards for **Online booking, Special offers, Events and Guest reviews**.
6. **Special Offers** cards are created by hotel staff in a dashboard content menu and lead to the reservation flow. With no published offers, the page shows a generic enquiry CTA, not an invented discount.
7. **Events** cards are created in a separate dashboard menu. Their enquiry actions lead to Contact; the Events section reuses the current homepage SureForms form when it is present and extractable. No event price, capacity, venue claim or example event is prefilled.
8. Gallery uses image tags in the saved TSAR homepage content.
9. Guest Reviews is blank with no rating/count/quote while the Google URL is unset. After a verified URL is entered, the output adds a Google link only.
10. Contact section reuses the existing homepage form when available, plus only administrator-approved contact details.

## Live TSAR facts to keep in view

- Existing booking destination: `/reservations/`; accommodations page: `/accommodations/`.
- The public MotoPress catalogue checked for this release exposes **Studio Suite** and **Standard Room**. The redesign displays all published English MotoPress room types only; this is not a hard-coded list. Rates/availability remain in the MotoPress detail/booking flow.
- The current homepage has its own static room cards and public rates. The new room section does not copy those hard-coded categories or rates.
- `/events/` and `/contact/` returned 404 during review. The redesigned header uses homepage sections until dedicated pages are created.
- The public WordPress page listing did not show French counterparts. The currently active multilingual plugin could not be identified without WordPress admin access. No claim is made that French routes have been disabled.
- No official TSAR Google Business Profile/review URL was verified. The review content stays blank.

## Staging and acceptance steps

1. Back up the files/database and install the v1.3.1 ZIP on TSAR's staging environment.
2. Confirm the main site and redesign are English-only. If a separate multilingual plugin is active, disable French/routes there and test a direct attempt to open any old French URL.
3. Enable the redesign option only on staging. Check the custom header, four-line menu, page title layout and each destination at phone/tablet/desktop sizes.
4. Confirm the homepage images and accessible alt text. Confirm the existing SureForms form remains functional after being reused in the Contact section.
5. Add draft and published offer/event entries, verify only published entries display, and test enquiry and reservation flows with hotel staff.
6. Confirm each room card matches a published MotoPress room type and its detail page shows current management-approved rates/availability. The home template does not create or edit inventory.
7. Leave Guest Reviews blank until TSAR supplies and verifies its official Google link. Then test the link; do not add an unverified rating, count or quotation.
8. Keep the new experience disabled on production until hotel management approves the staging review. Turning the option off restores the original saved homepage content and Astra header.

Implementation details and release checks are also recorded in `ADMIN-HANDOVER.md` and `TEST-REPORT.md`.
