# TSAR HOTEL Website Updates — Administrator Handover

**Package:** `tsar-hotel-updates-v1.0.0.zip`  
**Version:** 1.0.0  
**Target:** the existing TSAR HOTEL WordPress website, not a replacement website.  
**Status:** prepared for administrator review and staging installation; not installed on tsarhotel.com.

## 1. Read this first

This is a small, original WordPress plugin. It applies reversible front-end filters rather than rewriting the hotel's theme, menus, pages or booking data. The hotel administrator should review the custom code and verify compatibility on the actual staging site before production activation.

**It does not complete every item in the website audit.** In particular, cookie consent, real hotel photography, rates/inventory reconciliation and end-to-end enquiry/booking testing remain administrator/management tasks.

No domain transfer, hosting change, password sharing, paid subscription or new external account is required by this package. It contains no live reception number, guest information, credentials, API keys or real reservation data.

## 2. What activation does automatically

On first activation, only these two features are on:

1. **Repair section-menu links.** WordPress menu links with the exact destinations `#about`, `#amenities`, `#gallery` and `#packages` are rendered as homepage-qualified URLs. This fixes the observed navigation issue from the reservations page. The stored menu records are not changed. It targets conventional WordPress menus used by Astra, not arbitrary manually written HTML links.
2. **Hide empty Astra footer social icons.** Empty, missing and `#` placeholder URLs on `.ast-builder-social-element` links inside `#colophon` are hidden. Real non-empty profile links are left alone. This does not create or connect social accounts.

Everything below is off until reviewed and explicitly enabled.

## 3. Optional features your administrator can approve

### Reception enquiry panel

Enter the **approved** reception telephone number, WhatsApp Business number and email. The WhatsApp number is a separate field; it is not assumed to be the same as the telephone number. Use full international numbers including their country codes.

Enable the enquiry panel to display working contact actions on:

- Homepage.
- `/reservations/`.
- `/accommodations/`.
- MotoPress accommodation-type pages.

The panel is in the normal document flow, not a floating overlay. It can be placed before or after page content. Alternatively, leave automatic placement off and add `[tsar_contact_actions]` in a WordPress Shortcode block exactly where it is wanted. Use automatic or manual placement rather than duplicating it.

English and French panel labels are available. This does **not** translate the entire website.

Optional confirmed map and official social/review URLs appear in the panel. Unfilled fields produce no placeholder links. The panel states clearly that an enquiry is not a confirmed reservation. Opening a WhatsApp link only opens a prefilled chat; the visitor still chooses whether to send the message.

The panel adds `data-tsar-action` labels to contact links for later, appropriately consented analytics integration. It does not install analytics, track clicks itself or send information to a third party.

### Homepage brand heading

An opt-in filter replaces only the visible main-loop homepage title `Home` with **TSAR HOTEL — WHERE AFRICA MEETS**. The stored page title and navigation labels are not rewritten. Check its position and size in the actual theme before keeping it enabled.

### Homepage SEO text

The editable default title is:

> TSAR HOTEL Yaoundé | Rooms, Studio Suites & Dining

The editable default description is:

> Discover TSAR HOTEL in Yaoundé, with rooms, studio suites, a restaurant and TSAR Snack Lounge. Contact our team for rates, availability and reservations.

Review the wording with hotel management before enabling it. It does not claim an opening date or specific room rate.

The module supports WordPress core title/description output and **SureRank**, the SEO plugin identified in the public audit. With SureRank, it aligns the homepage's search and social-sharing title/description through existing filters rather than outputting a second description tag. Existing images, canonicals and other pages' SEO remain unchanged.

When this module is enabled, these package settings take precedence over the homepage title/description configured in SureRank. If future maintenance should be done entirely inside SureRank, turn this module off.

If a recognised different SEO plugin is active, the package suspends its SEO/search-cleanup modules rather than trying to override it. For other or unknown SEO configurations, keep these modules off and use the site's existing SEO tool. Re-test if SureRank, the theme or another SEO plugin changes.

### Limit indexing of demo / booking utility pages

This opt-in module adds `noindex` and excludes matched post IDs from supported sitemaps for these specific paths:

- `/sample-page/`.
- `/hello-world/` (the default post).
- `/search-results/`.
- `/my-account/`.
- `/booking-cancellation/`.
- `/booking-confirmation/` and its `booking-confirmed`, `booking-canceled`, `reservation-received` and `transaction-failed` child pages.

It does not delete pages, remove booking shortcodes or prevent visitors from using them. The homepage, reservations, room catalogue and room detail pages are not on this exclusion list.

**Cached sitemaps need regeneration after enabling or disabling this module.** In SureRank 1.10.1, the source explicitly notes that changes to the exclusion filter need a forced cache rebuild. Where WP-CLI is available, the administrator can use:

```sh
wp surerank generate_cache --force
```

Otherwise use the installed SEO plugin's supported sitemap-regeneration workflow, or leave this optional module off until regeneration can be verified. Also clear relevant page/CDN caches. Existing indexed demo URLs may take time to disappear from search; no instant removal is promised.

## 4. Installation — one authorised administrator

1. **Confirm a full files-and-database backup and a usable restore method.** Retain it outside the plugin folder. Never install first and assume a restore is available.
2. Use the actual site's **staging environment**. Check WordPress and PHP versions and existing plugin compatibility. Headers require WordPress 6.0+ and PHP 7.4+; use a currently maintained PHP release supported by the host, not an outdated release merely because it meets the minimum. The included test report specifies what was actually tested, not every possible version combination.
3. Go to **Plugins → Add New → Upload Plugin**.
4. Upload `tsar-hotel-updates-v1.0.0.zip`, choose **Install Now**, then **Activate**. Do not upload a parent project archive or a folder of unrelated files.
5. Open **Settings → TSAR HOTEL Updates**.
6. Verify the two default repairs. Enter only management-approved contacts/profile links and opt into the modules wanted. Choose **Save approved settings**.
7. Clear relevant caches and complete the checks below.
8. Once staging is approved, repeat on production during an agreed maintenance window, using a current production backup. Re-enter approved settings or reproduce them carefully; do not overwrite the production database with an old staging copy.

If upload/install controls are absent, the signed-in account may not have administrator permissions or the host may restrict uploads. Ask the existing authorised hotel administrator to install it. Do not share their password in chat. Multisite/network deployment has not been certified; this handover targets the hotel's single-site setup.

## 5. Acceptance checklist

- [ ] The homepage and reservations page load normally over HTTPS.
- [ ] From reservations and room pages, About Us/Amenities/Gallery/Packages lead to the intended homepage sections.
- [ ] Empty footer social placeholders are hidden; existing working links remain available.
- [ ] If enabled, the contact panel fits a small phone screen and can be used by keyboard.
- [ ] Telephone and email actions open the correct intended destination.
- [ ] The WhatsApp action opens the approved hotel account. Reception confirms this; the package cannot verify account ownership from a number alone.
- [ ] Every map/profile/review URL is the actual official destination.
- [ ] Homepage search/social metadata is accurate, with no duplicate description tags.
- [ ] When search cleanup is enabled, only the intended pages are noindexed and the regenerated sitemap excludes them.
- [ ] With permission, a labelled test enquiry is received by reception and the guest acknowledgement works.
- [ ] With permission, a controlled booking-flow test verifies rates, availability and notifications. Avoid real charges; remove test records appropriately.
- [ ] The cookie-consent consolidation below is completed and consent choices work.
- [ ] Management approves the room-category/rate information and real photographs separately.

## 6. Cookie banners — deliberately not automated

The audit found **SureCookie and CookieAdmin** banners appearing together. This package does not conceal the banners or deactivate consent plugins because the correct decision depends on the actual configuration, analytics and privacy requirements.

On staging, the administrator should:

1. Record each active consent plugin, its settings and any dependent add-on.
2. Choose one solution to retain and verify its script/cookie controls.
3. Deactivate the redundant solution and its dependent add-on where appropriate. Do not deactivate both and do not deactivate unrelated forms/booking plugins.
4. Clear caches. Test a fresh/private-browser visit, accept/reject behaviour, saved preferences and any consent-dependent analytics.
5. Confirm one usable consent interface appears on desktop and mobile before deploying the same reviewed change.

No compliance certification is provided by this package.

## 7. What remains outside this update

- Approved prices, room categories and booking inventory matching the hotel's 21 rooms, including 3 studio suites.
- Real, licensed photographs for each room category, restaurant and TSAR Snack Lounge.
- A full homepage copy/layout redesign and maintained multilingual content.
- Actual Google Business Profile/social account creation, verification and ownership.
- Email deliverability, reservation/payment configuration and reception staffing.
- Hotel-specific structured data, performance tuning and broader SEO after confirmed business details.
- Analytics accounts, event attribution, reporting dashboards and advertising.
- Deleting confirmed demo content after management/dependency review; the optional module only controls indexing.

## 8. Rollback

1. Deactivate **TSAR HOTEL Website Updates**.
2. Clear page, server and CDN caches.
3. If the search-cleanup module was enabled, regenerate the SEO sitemap cache after deactivation.
4. Verify the original rendered navigation/content/metadata have returned. The plugin did not rewrite stored pages, menus or reservations.
5. Restore the full backup if unrelated changes were made outside this plugin.

Deleting the plugin removes only its own saved option for the current site. Deactivation keeps its settings so it can be re-enabled. Removing the plugin will also remove the shortcode output; remove any inserted shortcode block if permanently uninstalling it.

## 9. Maintenance and privacy

The code has no automatic update service; the hotel/developer should retain the source and review it when changing the theme, booking plugin or SEO plugin. No customer records, analytics identifiers or remote credentials are required. Do not put secrets into contact/profile fields.

The package makes no outgoing API requests, sets no cookies and does not contact a developer server. Visitor-initiated contact links navigate to the selected service. Other existing WordPress components may still make their own requests; this plugin does not govern them.
