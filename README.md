# TSAR HOTEL Website Updates

A reversible WordPress plugin for targeted TSAR HOTEL site repairs and an administrator-controlled homepage refresh. Existing WordPress page content, booking records, rates, payments and guest records are not rewritten.

## Default behavior

On a fresh install only two repairs are enabled:

- Qualify the four audited Astra section-menu destinations (`#about`, `#amenities`, `#gallery`, `#packages`) with the homepage URL.
- Hide empty/placeholder Astra footer social links.

The redesigned homepage is **off by default**. An administrator can preview and enable it from **Settings → TSAR HOTEL Updates** after installing on a backed-up staging site.

## Optional homepage refresh (v1.3.1)

When explicitly enabled, the plugin:

- Replaces the rendered homepage body while leaving its saved WordPress content untouched. Turning the setting off restores the saved content.
- Replaces the Astra header sitewide with TSAR branding, the same six destinations in desktop and mobile navigation—**Rooms & Suites, Restaurant, Snack Lounge, Events, Gallery, Contact**—and a floating booking link to the existing `/reservations/` page.
- Displays the website redesign in **English only**. The redesign has no language switcher and the enquiry panel is English-only. Common Polylang, WPML, TranslatePress and GTranslate switcher controls are hidden while the redesign is active. This package does not disable a separate multilingual plugin or remove its language routes; an administrator must disable French in that plugin to make the entire WordPress site English-only.
- Builds the room cards only from published MotoPress `mphb_room_type` posts. The public catalogue checked for this release lists **Studio Suite** and **Standard Room**; no extra room categories are hard-coded. Each card links to its published MotoPress detail page, where rates and availability are managed. The redesign does not invent prices.
- Uses TSAR images found in the saved homepage content and reuses its existing SureForms form when the rendered form is available.
- Adds WordPress dashboard content editors for **Special Offers** and **Events**. Only published, English entries appear. Offers link to reservations; event enquiries lead to the Contact section/form. No offer or event examples are prefilled.
- Features **Online booking, Special offers, Events and Guest reviews**. The review area stays unpopulated until an official TSAR Google review URL is provided; when configured, it displays a link only, not an invented rating, count or quotation.
- Does not add an airport option, spa, La Falaise photos, or La Falaise copy, prices, amenities, ratings or claims.

The design follows the reference site's visitor flow with TSAR-owned content and details. It does not copy the reference website's assets or factual claims.

## Build and checks

```sh
python3 tests/check_release.py
./build-zip.sh
```

The uploadable archive is generated at `dist/tsar-hotel-updates-v1.3.1.zip` and is intentionally not committed. The build script checks source consistency and packages the PHP include, CSS and JavaScript. Use a backed-up WordPress staging site for runtime validation; no live WordPress admin access or production deployment occurred in this session.

## Staging and rollback

Read [ADMIN-HANDOVER.md](ADMIN-HANDOVER.md), [REDESIGN-PLAN.md](REDESIGN-PLAN.md) and [TEST-REPORT.md](TEST-REPORT.md). Back up the site before installing. Keep the redesign option off until the staging review is complete. Turning it off restores the saved page content and Astra header; deactivate the plugin to roll back its other filters. The schema migration keeps the redesign off and forces the contact panel language setting to English.
