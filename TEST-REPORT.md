# TSAR HOTEL Website Updates — Validation Report

**Version:** 1.3.1
**Date:** 3 October 2026
**Status:** Source update prepared for review. The optional redesign remains off by default; no production changes were deployed.

## Checks for this source

| Check | Result |
|---|---|
| `python3 tests/check_release.py` — release metadata, defaults, migration, MotoPress-only rooms, English-only UI and hashes | Passed (24 checks) |
| `./build-zip.sh` — archive root, required source/assets and package contents | Passed; generated `dist/tsar-hotel-updates-v1.3.1.zip` |
| `unzip -t dist/tsar-hotel-updates-v1.3.1.zip` plus archive-content assertions | Passed |
| `node --check assets/redesign.js` | Passed |
| `git diff --check` | Passed |
| PHP syntax parser (`php-parser` 3.7.0) | Passed for plugin, redesign include and uninstall file; parser-only, not PHP runtime |
| Native PHP syntax lint (`php -l`) | Not available: PHP CLI is unavailable in this environment |
| WordPress activation/admin rendering, MotoPress/SureForms runtime integration, multilingual-plugin configuration and real browser layout | Not run: live WordPress admin/runtime is not available |

Source checks are not a production certification. Runtime behavior and visual layout must be reviewed on a backed-up staging site before enabling the redesign. The ZIP is generated in ignored `dist/`; run `./build-zip.sh` to recreate it.

## Public TSAR findings used for this revision

Read-only public page text and the WordPress REST API were checked on 3 October 2026. The MotoPress room-type listing exposed **Studio Suite** and **Standard Room**. The redesign now queries published `mphb_room_type` posts rather than carrying a hard-coded category list. A card links to the MotoPress room detail page; rates and availability remain managed by MotoPress.

The public page listing did not show French counterparts. The active multilingual plugin and its route configuration could not be identified without WordPress admin access. The redesign UI contains English copy only and has no language switcher. Common language-switcher widgets are hidden while the redesign is active, but this plugin does not deactivate a separate translation plugin or disable its French routes. An authorised administrator must do that separately if French routes exist and the whole website is to be English-only.

Other prior public findings remain relevant: `/reservations/` and `/accommodations/` exist; `/events/` and `/contact/` returned 404 during review; the homepage contains a SureForms form; no official TSAR Google review URL was verified. Form extraction/submission, room detail rendering and booking behavior still need staging verification.

## v1.3.1 implementation scope

- The redesign is an explicit settings opt-in; migration keeps it disabled and sets the contact panel language to English. Disabling the redesign restores the saved homepage content and Astra header.
- When enabled, the custom desktop/mobile navigation contains exactly **Rooms & Suites, Restaurant, Snack Lounge, Events, Gallery, Contact**. The floating booking CTA points to TSAR's existing `/reservations/` flow.
- The redesign and contact panel are English-only and contain no EN/FR switcher. Common language-switcher controls are hidden while the redesign is active. This does not disable French routes supplied by a separate plugin.
- The homepage displays only published English MotoPress room type records (`mphb_room_type`); it does not hard-code four categories or add rates/availability.
- It reuses images from the saved TSAR homepage, separates Restaurant from Snack Lounge and reuses the existing homepage form when extractable.
- It adds dashboard-managed Special Offers and Events entries. Offers link to booking; event enquiries lead to Contact and reuse the existing homepage form when extractable.
- Guest reviews remain unpopulated without the verified Google URL; when configured, only the outbound link appears—not a generated rating, count or quotation.
- No airport option, spa or La Falaise photos, copy, prices, amenities, ratings or claims are added.

## Limitations / not certified

- No WordPress runtime activation, settings persistence, Astra/Spectra visual output or live-plugin compatibility was tested.
- The public catalogue indicated two room types at review time; production room records and all detail links must be verified on staging.
- No active multilingual plugin was identified. This package does not configure other plugins or block French URLs; site-wide English-only routing remains an administrator task.
- The existing form's extraction/rendering, submissions and email delivery need an end-to-end staging test. The plugin does not create a new event-email service.
- Real image selection, alt text, booking availability, rates/payments, contact delivery, consent behavior, mobile accessibility, performance, legal compliance and production deployment were not verified.

## Source integrity

SHA-256 hashes for the current runtime source/assets:

```text
a236ffec79d7e098fb1a3a74298514ebf73016785769e570453348ad83cd944c  tsar-hotel-updates.php
1dbce2d0fd9881cbeab33a3b7c2c5c81a216e3f671bdc677e5edf94cb5321af6  uninstall.php
c30bd6aaeda77013bb2111243fe4ae3bbf928abe8758b0f9ad843fb89930756e  assets/front.css
ea0ba62eada62df90deaec3c1fb5b129e24ca25d75f6fe2a98ef07b9cc51bf78  includes/class-homepage-redesign.php
906887927b1009ac3770cc45f2e4c7e3f095c66949372a548655e7e0029c86b6  assets/redesign.css
6ad77c0a9f0684dda3da09c856c5ed9e2444a8dc4c2eda1c5a42f5ee1ef7293a  assets/redesign.js
```
