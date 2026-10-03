# TSAR HOTEL Website Updates — Administrator Handover

**Package:** `dist/tsar-hotel-updates-v1.3.1.zip` (build with `./build-zip.sh`)
**Version:** 1.3.1
**Target:** the existing TSAR HOTEL WordPress website, not a replacement website.
**Status:** source-only update prepared for administrator review; no changes were deployed through this session. Public pages were inspected read-only, but the live WordPress admin/runtime was not accessed. See `TEST-REPORT.md` for findings and QA limits.

## 1. Read this first

This plugin contains conservative front-end repairs plus a separately controlled TSAR homepage refresh. The refresh is off by default. It applies only after an administrator enables it and can be reverted by turning the setting off; saved homepage content is never overwritten. Review the code and test on the actual staging site before production activation.

**The v1.3.1 refresh is English-only and its room cards use published MotoPress room types only.** The public catalogue checked for this release contains Studio Suite and Standard Room. Do not expect hard-coded categories, sample prices or rates invented by this plugin.

This plugin does not deactivate a separate multilingual plugin or disable French routes. It removes the redesign's language switcher, renders its own UI in English and hides common third-party switcher controls while the redesign is active. If the whole site must be English-only, an authorised administrator must separately configure or disable French in the site's multilingual plugin and test direct French URLs. The active plugin/configuration could not be identified from public pages.

**It does not complete every item in the website audit.** Cookie consent, real hotel photography, rates/inventory reconciliation and end-to-end enquiry/booking testing remain administrator/management tasks.

No domain transfer, hosting change, password sharing, paid subscription or new external account is required by this package. It contains no live reception number, guest information, credentials, API keys or real reservation data.

## 2. What activation does automatically

On a fresh install, only these two features are on:

1. **Repair section-menu links.** WordPress menu links with the exact destinations `#about`, `#amenities`, `#gallery` and `#packages` are rendered as homepage-qualified URLs. This fixes the observed navigation issue from the reservations page. The stored menu records are not changed. It targets conventional WordPress menus used by Astra, not arbitrary manually written HTML links.
2. **Hide empty Astra footer social icons.** Empty, missing and `#` placeholder URLs on `.ast-builder-social-element` links inside `#colophon` are hidden. Real non-empty profile links are left alone. This does not create or connect social accounts.

All optional features below are off until reviewed and explicitly enabled. On upgrade, a one-time migration sets the contact-panel language to English and turns off the previously default-on mobile layout changes and homepage refresh. Existing approved contact and SEO fields are retained; the administrator must re-enable the redesign after reviewing this version.

## 3. Optional features your administrator can approve

### Full homepage refresh and navigation

The option **Enable the new TSAR homepage and navigation** is off by default. When enabled:

- A TSAR-branded header replaces the Astra header sitewide. Desktop and four-line mobile navigation use the same six labels: **Rooms & Suites, Restaurant, Snack Lounge, Events, Gallery, Contact**. The floating booking action leads to the existing `/reservations/` page.
- The front page becomes a full-width, image-led TSAR landing page. Images are reused from the saved homepage content; no La Falaise photos or remote assets are included. The old WordPress page content remains stored and is restored when the option is turned off.
- The room cards are generated from published English MotoPress `mphb_room_type` records. The public catalogue checked for this release contains **Studio Suite** and **Standard Room**. Each card links directly to its published MotoPress detail page. The redesign does not hard-code four categories, add rates, or claim availability; rates and booking availability remain part of the MotoPress flow.
- Restaurant and Snack Lounge are separate sections. Service cards feature **Online booking, Special offers, Events and Guest reviews**.
- **Special Offers** and **Events** appear as dashboard editing menus. Staff create approved entries and publish them; drafts are not shown. An empty offers list shows a generic enquiry CTA, not a fabricated discount. Event entries use the featured image, title and excerpt supplied by staff.
- The Events section includes a request-information action and reuses the existing homepage SureForms form when the rendered form can be extracted. It does not create a second form-processing service. Test form rendering, submissions and email delivery with reception on staging.
- The Guest Reviews area contains no ratings, counts or quotations. It remains unpopulated while the Google Reviews URL is blank. Once the official URL is configured, the output adds a link to Google only.
- The redesign copy and contact panel are English-only. There is no language switcher. Common Polylang, WPML, TranslatePress and GTranslate switcher controls are hidden while the redesign is active. This package cannot disable a separate translation plugin's French routes; configure that plugin separately if French pages must not be available.
- No airport option, spa or wellness area is added.

The public `/events/` and `/contact/` pages returned 404 during review, so the custom-header links currently point to homepage sections. Restaurant, Snack Lounge, Events, Gallery and Contact also target homepage sections when clicked from inner pages. Update the destinations if TSAR creates dedicated pages.

When the plugin is deactivated, the new Custom Post Type editor menus disappear but offer/event records remain in the database. Keep the plugin active to edit and display them.

### Targeted mobile layout repairs

This option is **off by default**. It applies CSS to the homepage title/header, the About Us section, gallery figures and one footer heading. The selectors depend on the installed Astra/Spectra markup and can change how content is displayed. Enable only after checking the current theme and block-plugin output on staging at phone and tablet widths. Turn it back off if any section is hidden, duplicated or misaligned. It does not add content or images.

### Reception enquiry panel

Enter the **approved** reception telephone number, WhatsApp Business number and email. The WhatsApp number is a separate field; it is not assumed to be the same as the telephone number. Use full international numbers including their country codes.

Enable the enquiry panel to display working contact actions on:

- Homepage.
- `/reservations/`.
- `/accommodations/`.
- MotoPress accommodation-type pages.

The panel is in the normal document flow, not a floating overlay. It can be placed before or after page content. Alternatively, leave automatic placement off and add `[tsar_contact_actions]` in a WordPress Shortcode block exactly where it is wanted. Use automatic or manual placement rather than duplicating it.

The panel is English-only. Optional confirmed map and official social/review URLs appear in the panel. Unfilled fields produce no placeholder links. The panel states clearly that an enquiry is not a confirmed reservation. Opening a WhatsApp link only opens a prefilled chat; the visitor still chooses whether to send the message.

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
2. Use the actual site's **staging environment**. Check WordPress and PHP versions and existing plugin compatibility. Headers require WordPress 6.0+ and PHP 7.4+; use a currently maintained PHP release supported by the host. The included test report specifies what was actually tested, not every possible version combination.
3. From the repository root, run `./build-zip.sh`. It creates `dist/tsar-hotel-updates-v1.3.1.zip`; the ZIP is generated locally and is not committed to the repository.
4. Go to **Plugins → Add New → Upload Plugin**.
5. Upload that ZIP, choose **Install Now**, then **Activate**. Do not upload the parent repository archive.
6. Open **Settings → TSAR HOTEL Updates**.
7. Verify the two default repairs. Enter only management-approved contacts/profile links and opt into the modules wanted. Choose **Save approved settings**.
8. Clear relevant caches and complete the checks below.
9. Once staging is approved, repeat on production during an agreed maintenance window, using a current production backup. Re-enter approved settings or reproduce them carefully; do not overwrite the production database with an old staging copy.

If upload/install controls are absent, the signed-in account may not have administrator permissions or the host may restrict uploads. Ask the existing authorised hotel administrator to install it. Do not share their password in chat. Multisite/network deployment has not been certified; this handover targets the hotel's single-site setup.

## 5. Acceptance checklist

- [ ] The homepage and reservations page load normally over HTTPS.
- [ ] From reservations and room pages, About Us/Amenities/Gallery/Packages lead to the intended homepage sections.
- [ ] Empty footer social placeholders are hidden; existing working links remain available.
- [ ] With the redesign option off, the original saved homepage and Astra header render normally. If enabled on staging, the new TSAR header and homepage appear once, all published MotoPress room types appear, and the six menu labels and booking buttons go to valid destinations.
- [ ] The website and redesign show English content only. If a separate multilingual plugin is active, disable French/routes there and test a direct attempt to open a French URL; the plugin package alone does not block those routes.
- [ ] The homepage enquiry form shows no validation errors on a fresh load and a permitted test enquiry reaches reception.
- [ ] Consent controls are checked visually in a fresh/private browser; determine whether repeated text is one banner plus its preferences dialog or two active consent plugins before changing settings.
- [ ] If enabled, the custom header, four-line mobile menu, floating booking CTA, section layout and existing Astra footer are checked at phone, tablet and desktop widths.
- [ ] If enabled, the contact panel fits a small phone screen and can be used by keyboard.
- [ ] Telephone and email actions open the correct intended destination.
- [ ] The WhatsApp action opens the approved hotel account. Reception confirms this; the package cannot verify account ownership from a number alone.
- [ ] Every map/profile/review URL is the actual official destination.
- [ ] Each room card comes from a published MotoPress room type and opens its correct detail page; rates and availability are managed in MotoPress, not hard-coded here.
- [ ] Published Special Offers/Events entries appear with approved text and images; drafts do not appear. No placeholder price, offer, capacity or review claim is shown.
- [ ] The review section is blank until the official URL is configured; then it shows only the link and no synthetic score or quote.
- [ ] Homepage search/social metadata is accurate, with no duplicate description tags.
- [ ] When search cleanup is enabled, only the intended pages are noindexed and the regenerated sitemap excludes them.
- [ ] With permission, a labelled test enquiry is received by reception and the guest acknowledgement works.
- [ ] With permission, a controlled booking-flow test verifies rates, availability and notifications. Avoid real charges; remove test records appropriately.
- [ ] The cookie-consent consolidation below is completed and consent choices work.
- [ ] Management approves the room information and real photographs separately.

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

- Administrator verification that no separate multilingual plugin continues to expose French routes; this package changes only its own UI/switcher output.
- Approved MotoPress inventory, rates and booking availability.
- Approved photography and descriptive alt text for each room, restaurant, Snack Lounge and gallery image.
- Verification and ownership of the Google Business Profile; the reviews module remains unpopulated until the hotel supplies its official URL.
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

The code has no automatic update service; the hotel/developer should retain the source and review it when changing the theme, block editor plugins, booking plugin or SEO plugin. No customer records, analytics identifiers or remote credentials are required. Do not put secrets into contact/profile fields.

The package makes no outgoing API requests, sets no cookies and does not contact a developer server. Visitor-initiated contact links navigate to the selected service. Other existing WordPress components may still make their own requests; this plugin does not govern them.
