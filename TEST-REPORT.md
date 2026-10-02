# TSAR HOTEL Website Updates — Test Report

**Version:** 1.0.0  
**Date:** 30 September 2026  
**Result:** 117 / 117 checks passed in a disposable local QA environment.  
**Deployment status:** Not installed or tested inside the live tsarhotel.com administration environment.

## Environment

- WordPress 7.1.2; PHP 8.4.26; WP-CLI 2.12.0.
- Astra 4.14.0, matching the theme version identified in the public site source.
- SureRank 1.10.1, matching the SEO plugin identified in the public site source.
- SQLite Database Integration for the disposable database; the production database/host configuration was not copied.
- External HTTP was deliberately blocked in the QA WordPress configuration. The native installer emitted WordPress.org update-check warnings for that reason; local ZIP installation and activation succeeded. These were core update-check warnings, not a plugin runtime failure.
- Chromium/Playwright component rendering at 320, 390, 768 and 1440 pixels.

## Test summary

| Suite | Passed | Total |
|---|---:|---:|
| WordPress core integration | 66 | 66 |
| Actual SureRank frontend integration | 18 | 18 |
| Isolated responsive contact component | 21 | 21 |
| Frontend rollback | 7 | 7 |
| Uninstall isolation | 3 | 3 |
| Fresh activation defaults | 1 | 1 |
| Actual ZIP installation / activation | 1 | 1 |
| **Total** | **117** | **117** |

## What was verified

- Conventional WordPress menus render the four audited fragment links as homepage-qualified links; unrelated URLs are preserved.
- Stored menu destinations and fixture page/form content remain unchanged.
- Contact fields start blank. Optional modules start off; only the two documented repairs start on.
- International-number formatting, HTTPS-only profile links, script-stripping, input bounds and non-scalar/unknown input handling.
- Contact actions, reservation disclaimer, optional official links and English/French panel labels.
- Normal-flow contact-panel placement, duplicate avoidance and preservation of a reservation-form fixture.
- WordPress/SureRank homepage titles and descriptions, including sharing metadata, without a duplicate description tag in the tested setup.
- Noindex scoping and SureRank sitemap regeneration; homepage, reservations and accommodation catalogue IDs are not excluded.
- A recognised alternative SEO provider suspends the package SEO/search-cleanup changes.
- Administrator-only settings display, native WordPress nonce fields and non-exposure of settings through REST.
- Component layout without horizontal overflow, contact-target heights, keyboard access and scoped empty-icon hiding.
- Deactivation restores original rendering. A forced sitemap rebuild restores the previous sitemap entries.
- Uninstall removes only this plugin’s option, preserving an unrelated option; reactivation restores conservative defaults after uninstall.
- PHP syntax checks and source review for outgoing HTTP-request/cookie-setting calls.
- Installation and activation of the actual ZIP through the WordPress plugin installer. The installed runtime file hashes matched the tested source; the default menu fix worked and the reservation fixture remained present.

## Not verified / not certified

- The actual live hotel database, all installed plugins, host restrictions or production caching behaviour.
- End-to-end MotoPress/SureForms booking and email delivery, real availability/rates, payment handling, cancellation notifications or guest communications. The form-preservation checks used local fixtures, not real reservations.
- Consent-plugin consolidation, legal compliance, full-site accessibility, real-device/network performance, PageSpeed or Core Web Vitals.
- Other WordPress/PHP/theme/SEO version combinations or multisite deployment. Minimum-version headers are compatibility targets, not a claim that every combination was tested.
- A complete penetration test or third-party security certification. Native WordPress role/nonce facilities are used, but the administrator should still review the custom code.

**Production remains subject to an actual staging review, management-approved information and a verified backup/restore path.**

## Source integrity

SHA-256 hashes for the tested runtime files:

```text
d3a37e78200d91a994c282be32d396feec3fee90a4db60861ce08d7182da8d88  tsar-hotel-updates.php
1dbce2d0fd9881cbeab33a3b7c2c5c81a216e3f671bdc677e5edf94cb5321af6  uninstall.php
648a16af643849f7f823c1c65ecf9caa7ef309bf21421af030ec91144dd57e4a  assets/front.css
```

The plugin ZIP is packaged with one top-level `tsar-hotel-updates/` directory. Source and settings contain no live credentials or guest records.
