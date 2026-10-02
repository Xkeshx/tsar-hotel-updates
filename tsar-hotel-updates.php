<?php
/**
 * Plugin Name: TSAR HOTEL Website Updates
 * Description: Reversible navigation repairs, Android/mobile layout fixes, empty social-link cleanup, configurable enquiry panels, and optional homepage SEO for TSAR HOTEL.
 * Version: 1.2.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: TSAR HOTEL project
 * License: GPL-2.0-or-later
 * Plugin URI: https://github.com/Xkeshx/tsar-hotel-updates
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * GitHub Plugin URI: https://github.com/Xkeshx/tsar-hotel-updates
 * Primary Branch: main
 * Text Domain: tsar-hotel-updates
 */

namespace TSARHotel\WebsiteUpdates;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Plugin {
	const VERSION = '1.2.0';
	const OPTION = 'tsar_hotel_updates_settings';
	const GROUP = 'tsar_hotel_updates_group';
	const PAGE = 'tsar-hotel-updates';

	private $panel_added = false;
	private $excluded_ids = null;

	public static function defaults() {
		return array(
			'menu_repair' => 1,
			'hide_empty_socials' => 1,
			'mobile_layout_fixes' => 1,
			'khotel_design' => 1,
			'contact_panel' => 0,
			'home_seo' => 0,
			'demo_noindex' => 0,
			'brand_heading' => 0,
			'language' => 'en',
			'panel_position' => 'top',
			'phone' => '',
			'whatsapp' => '',
			'email' => '',
			'map_url' => '',
			'facebook_url' => '',
			'instagram_url' => '',
			'tiktok_url' => '',
			'youtube_url' => '',
			'linkedin_url' => '',
			'google_reviews_url' => '',
			'home_title' => 'TSAR HOTEL Yaoundé | Rooms, Studio Suites & Dining',
			'home_description' => 'Discover TSAR HOTEL in Yaoundé, with rooms, studio suites, a restaurant and TSAR Snack Lounge. Contact our team for rates, availability and reservations.',
		);
	}

	public static function activate() {
		if ( false === get_option( self::OPTION, false ) ) {
			add_option( self::OPTION, self::defaults(), '', false );
		}
	}

	public function __construct() {
		add_filter( 'nav_menu_link_attributes', array( $this, 'repair_menu' ), 50, 4 );
		add_filter( 'body_class', array( $this, 'body_classes' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_filter( 'the_content', array( $this, 'inject_khotel_sections' ), 20 );
		add_filter( 'the_content', array( $this, 'add_contact_panel' ), 25 );
		add_action( 'wp_footer', array( $this, 'render_khotel_footer_js' ), 40 );
		add_shortcode( 'tsar_contact_actions', array( $this, 'contact_shortcode' ) );
		add_filter( 'the_title', array( $this, 'homepage_heading' ), 30, 2 );
		add_filter( 'surerank_set_meta', array( $this, 'surerank_home_meta' ), 50 );
		add_filter( 'pre_get_document_title', array( $this, 'homepage_document_title' ), 99 );
		add_action( 'wp_head', array( $this, 'core_home_description' ), 3 );
		add_filter( 'wp_robots', array( $this, 'core_robots' ), 50 );
		add_filter( 'surerank_robots_meta_array', array( $this, 'surerank_robots' ), 50 );
		add_filter( 'surerank_exclude_posts_from_sitemap', array( $this, 'exclude_sitemap_ids' ), 50 );
		add_filter( 'wp_sitemaps_posts_query_args', array( $this, 'core_sitemap_args' ), 50, 2 );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'settings_link' ) );
	}

	public function options() {
		$saved = get_option( self::OPTION, array() );
		return wp_parse_args( is_array( $saved ) ? $saved : array(), self::defaults() );
	}

	private function frontend_request() {
		return ! is_admin() && ! wp_doing_ajax() && ! ( defined( 'REST_REQUEST' ) && REST_REQUEST ) && ! is_feed();
	}

	public function repair_menu( $attributes, $item = null, $args = null, $depth = 0 ) {
		$options = $this->options();
		if ( ! $this->frontend_request() || empty( $options['menu_repair'] ) || ! is_array( $attributes ) ) {
			return $attributes;
		}
		$href = isset( $attributes['href'] ) ? $attributes['href'] : '';
		if ( in_array( $href, array( '#about', '#amenities', '#gallery', '#packages' ), true ) ) {
			$attributes['href'] = home_url( '/' ) . $href;
		}
		return $attributes;
	}

	public function body_classes( $classes ) {
		$options = $this->options();
		if ( ! empty( $options['hide_empty_socials'] ) ) {
			$classes[] = 'tsar-updates-clean-socials';
		}
		if ( ! empty( $options['mobile_layout_fixes'] ) ) {
			$classes[] = 'tsar-updates-mobile-fixes';
		}
		if ( ! empty( $options['khotel_design'] ) ) {
			$classes[] = 'tsar-khotel-design';
		}
		return $classes;
	}

	public static function inline_css() {
		return '/* Scoped, local-only styles. No fonts, scripts, cookies or external assets. */
body.tsar-updates-clean-socials #colophon a.ast-builder-social-element[href=""],
body.tsar-updates-clean-socials #colophon a.ast-builder-social-element[href="#"],
body.tsar-updates-clean-socials #colophon a.ast-builder-social-element:not([href]) {
  display: none !important;
}

.tsar-updates-contact {
  --tsar-ink: #192b37;
  --tsar-gold: #806019;
  --tsar-border: #decba2;
  box-sizing: border-box;
  display: block;
  position: static;
  width: 100%;
  max-width: 100%;
  margin: 1rem 0 1.5rem;
  padding: clamp(1rem, 2.5vw, 1.65rem);
  color: var(--tsar-ink);
  background: #fffdf8;
  border: 1px solid var(--tsar-border);
  border-top: 3px solid #b08935;
  border-radius: 12px;
  font: inherit;
  overflow-wrap: anywhere;
}
.tsar-updates-contact *,
.tsar-updates-contact *::before,
.tsar-updates-contact *::after {
  box-sizing: border-box;
}
.tsar-updates-contact .tsar-updates-contact__brand {
  margin: 0 0 .45rem;
  color: var(--tsar-gold);
  font-size: .75rem;
  font-weight: 700;
  letter-spacing: .065em;
  line-height: 1.5;
}
.tsar-updates-contact h2 {
  margin: 0 0 .45rem;
  color: var(--tsar-ink);
  font-family: inherit;
  font-size: clamp(1.25rem, 2.5vw, 1.6rem);
  font-weight: 700;
  line-height: 1.25;
}
.tsar-updates-contact .tsar-updates-contact__note {
  margin: 0;
  max-width: 75ch;
  color: #445460;
  font-size: .925rem;
  line-height: 1.65;
}
.tsar-updates-contact__actions {
  display: flex;
  flex-wrap: wrap;
  align-items: stretch;
  gap: .65rem;
  margin-top: 1rem;
}
.tsar-updates-contact a.tsar-updates-action {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 46px;
  max-width: 100%;
  padding: .7rem 1rem;
  border: 1px solid #192b37;
  border-radius: 7px;
  background: #192b37;
  color: #fff;
  font-family: inherit;
  font-size: .925rem;
  font-weight: 650;
  line-height: 1.35;
  text-align: center;
  text-decoration: none;
  white-space: normal;
  box-shadow: none;
}
.tsar-updates-contact a.tsar-updates-action--whatsapp {
  background: #126946;
  border-color: #126946;
}
.tsar-updates-contact a.tsar-updates-action--email {
  background: #fffdf8;
  color: #192b37;
}
.tsar-updates-contact a.tsar-updates-action:hover {
  filter: brightness(.92);
  text-decoration: none;
}
.tsar-updates-contact a:focus-visible {
  outline: 3px solid #936508;
  outline-offset: 4px;
}
.tsar-updates-contact__profiles {
  display: flex;
  flex-wrap: wrap;
  gap: .6rem 1.1rem;
  margin-top: 1rem;
  padding-top: .9rem;
  border-top: 1px solid #e7ddc8;
}
.tsar-updates-contact__profiles a {
  display: inline-flex;
  align-items: center;
  min-height: 44px;
  color: #415260;
  font-size: .875rem;
  font-weight: 600;
  line-height: 1.4;
  text-decoration: underline;
  text-underline-offset: .18em;
}
@media (max-width: 520px) {
  .tsar-updates-contact__actions {
    display: grid;
    grid-template-columns: minmax(0, 1fr);
  }
  .tsar-updates-contact a.tsar-updates-action {
    width: 100%;
    min-height: 48px;
  }
}

/* Reversible Android / mobile layout fixes (toggleable in Settings -> TSAR HOTEL Updates) */
body.tsar-updates-mobile-fixes #colophon #media_image-1 h2.widget-title,
body.tsar-updates-mobile-fixes #colophon h2.wp-block-heading,
body.tsar-updates-mobile-fixes .site-footer h2.wp-block-heading {
  color: #f3e5ab !important;
}

@media (max-width: 782px) {
  /* 1. Prevent "Home" page title from colliding with the Astra Transparent Header logo on Android */
  body.home.tsar-updates-mobile-fixes .entry-header {
    display: none !important;
  }
  body.home.ast-theme-transparent-header.tsar-updates-mobile-fixes #masthead {
    position: relative !important;
    background-color: #162232 !important;
  }
  body.home.ast-theme-transparent-header.tsar-updates-mobile-fixes #ast-mobile-header .ast-main-header-wrap {
    background-color: #162232 !important;
    padding-top: 8px !important;
    padding-bottom: 8px !important;
  }

  /* 2. Restore the collapsed About Us photo on narrow Android viewports (<= 480px) */
  body.tsar-updates-mobile-fixes #about .wp-block-spectra-container,
  body.tsar-updates-mobile-fixes #about figure.wp-block-image,
  body.tsar-updates-mobile-fixes #about figure.wp-block-image img {
    width: 100% !important;
    max-width: 100% !important;
    height: auto !important;
  }

  /* 3. Unhide the 3 mobile-hidden Gallery photos and stack cleanly as full-width cards on Android */
  body.tsar-updates-mobile-fixes #gallery .wp-block-spectra-container.is-horizontal {
    flex-direction: column !important;
    flex-wrap: wrap !important;
    align-items: stretch !important;
    gap: 16px !important;
  }
  body.tsar-updates-mobile-fixes #gallery figure.wp-block-image,
  body.tsar-updates-mobile-fixes #gallery figure.spectra-hide-mobile,
  body.tsar-updates-mobile-fixes #gallery figure.spectra-hide-tablet {
    display: block !important;
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 !important;
  }
  body.tsar-updates-mobile-fixes #gallery figure.wp-block-image img {
    width: 100% !important;
    height: 240px !important;
    object-fit: cover !important;
  }
}

@media print {
  .tsar-updates-contact {
    break-inside: avoid;
  }
}

/* ==========================================================================
   K-HOTEL DOUALA INSPIRED LUXURY DESIGN & SLIDERS (body.tsar-khotel-design)
   ========================================================================== */
body.tsar-khotel-design {
  font-family: "Lato", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
  font-weight: 300;
  letter-spacing: 0.03em;
  color: #111;
  background-color: #fff;
}

/* 1. Top utility bar & sleek dark K-Hotel header */
.tsar-kh-topbar {
  background: #0b1117;
  color: #cbd5e1;
  font-size: 12px;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  padding: 8px 20px;
  border-bottom: 1px solid rgba(230, 172, 152, 0.2);
}
.tsar-kh-topbar__inner {
  max-width: 1240px;
  margin: 0 auto;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 8px;
}
.tsar-kh-topbar a {
  color: #e6ac98;
  text-decoration: none;
  font-weight: 600;
}

body.tsar-khotel-design #masthead {
  position: sticky !important;
  top: 0 !important;
  z-index: 9990 !important;
  background-color: #040707 !important;
  transition: all 0.3s ease !important;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.28);
}
body.tsar-khotel-design #masthead .ast-main-header-wrap,
body.tsar-khotel-design #masthead .ast-primary-header-bar,
body.tsar-khotel-design #masthead .ast-below-header-bar,
body.tsar-khotel-design #ast-mobile-header .ast-main-header-wrap {
  background-color: #040707 !important;
  border-color: rgba(255, 255, 255, 0.08) !important;
  transition: padding 0.3s ease !important;
}
body.tsar-khotel-design.tsar-kh-scrolled #masthead .custom-logo {
  max-width: 135px !important;
  transition: max-width 0.3s ease;
}
body.tsar-khotel-design #masthead .menu-link {
  color: #ffffff !important;
  text-transform: uppercase !important;
  font-size: 13px !important;
  letter-spacing: 0.14em !important;
  font-weight: 400 !important;
}
body.tsar-khotel-design #masthead .menu-link:hover {
  color: #e6ac98 !important;
}

/* Header BOOK button (K-Hotel .booknow_btn) */
.tsar-kh-header-book {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background-color: #e6ac98;
  color: #040707 !important;
  font-size: 12px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 2.5px;
  padding: 11px 20px;
  text-decoration: none !important;
  transition: all 0.25s ease;
  margin-left: 14px;
}
.tsar-kh-header-book:hover {
  background-color: #d4957f;
  color: #ffffff !important;
}

/* Hide redundant default H1 Home title */
body.home.tsar-khotel-design .entry-header {
  display: none !important;
}

/* 2. K-Hotel Editorial Intro Block (.fp-slider-room-text) */
.tsar-kh-intro {
  text-align: center;
  padding: 56px 24px 44px;
  max-width: 980px;
  margin: 0 auto;
}
.tsar-kh-intro__subtitle {
  color: #777;
  font-weight: 300;
  letter-spacing: 0.22em;
  font-size: 14px;
  text-transform: uppercase;
  margin-bottom: 8px;
}
.tsar-kh-intro__title {
  font-size: clamp(30px, 4.5vw, 52px) !important;
  font-weight: 300 !important;
  letter-spacing: 0.06em !important;
  color: #111 !important;
  text-transform: uppercase;
  margin: 6px 0 18px !important;
  line-height: 1.2 !important;
}
.tsar-kh-intro__hr {
  width: 140px;
  height: 1px;
  border: 0;
  background: #e6ac98;
  margin: 0 auto 24px;
}
.tsar-kh-intro__lead {
  font-size: 16px;
  font-weight: 300;
  line-height: 1.85;
  color: #333;
  letter-spacing: 0.03em;
  margin: 0 auto 26px;
}
.tsar-kh-intro__actions {
  display: flex;
  justify-content: center;
  gap: 14px;
  flex-wrap: wrap;
}
.tsar-kh-btn {
  display: inline-block;
  background-color: #e6ac98;
  color: #111 !important;
  font-size: 13px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 2px;
  padding: 13px 28px;
  text-decoration: none !important;
  transition: all 0.25s ease;
  border: 1px solid #e6ac98;
}
.tsar-kh-btn:hover {
  background-color: #040707;
  color: #fff !important;
  border-color: #040707;
}
.tsar-kh-btn--outline {
  background-color: transparent;
  color: #111 !important;
  border: 1px solid #111;
}
.tsar-kh-btn--outline:hover {
  background-color: #111;
  color: #fff !important;
}

/* 3. K-Hotel 3-Card Interactive Experience Carousel (.fp-feature-room-section) */
.tsar-kh-carousel-sec {
  position: relative;
  width: 100%;
  max-width: 1440px;
  margin: 10px auto 56px;
  padding: 0 16px;
  box-sizing: border-box;
}
.tsar-kh-carousel-viewport {
  overflow: hidden;
  width: 100%;
  position: relative;
}
.tsar-kh-carousel-track {
  display: flex;
  transition: transform 0.55s cubic-bezier(0.25, 0.8, 0.25, 1);
  will-change: transform;
}
.tsar-kh-slide {
  flex: 0 0 33.3333%;
  max-width: 33.3333%;
  padding: 0 10px;
  box-sizing: border-box;
}
.tsar-kh-card {
  position: relative;
  overflow: hidden;
  background: #040707;
  height: 430px;
  display: block;
  text-decoration: none !important;
}
.tsar-kh-card img {
  width: 100% !important;
  height: 100% !important;
  object-fit: cover !important;
  transition: transform 0.65s ease, opacity 0.4s ease;
  opacity: 0.88;
}
.tsar-kh-card:hover img {
  transform: scale(1.06);
  opacity: 0.72;
}
.tsar-kh-card__overlay {
  position: absolute;
  left: 0;
  right: 0;
  bottom: 0;
  padding: 28px 24px 24px;
  background: linear-gradient(to top, rgba(4, 7, 7, 0.92) 0%, rgba(4, 7, 7, 0.55) 65%, rgba(4, 7, 7, 0) 100%);
  color: #fff;
}
.tsar-kh-card__tag {
  display: inline-block;
  font-size: 11px;
  letter-spacing: 0.2em;
  text-transform: uppercase;
  color: #e6ac98;
  margin-bottom: 6px;
  font-weight: 700;
}
.tsar-kh-card__title {
  font-size: 20px;
  font-weight: 400;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: #fff;
  margin: 0 0 8px;
}
.tsar-kh-card__desc {
  font-size: 14px;
  line-height: 1.55;
  color: rgba(255, 255, 255, 0.88);
  margin: 0;
  font-weight: 300;
}
.tsar-kh-arrow {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  width: 46px;
  height: 46px;
  background: rgba(4, 7, 7, 0.78);
  color: #fff;
  border: 1px solid rgba(230, 172, 152, 0.55);
  cursor: pointer;
  z-index: 20;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 22px;
  transition: all 0.25s ease;
}
.tsar-kh-arrow:hover {
  background: #e6ac98;
  color: #040707;
}
.tsar-kh-arrow--prev { left: 26px; }
.tsar-kh-arrow--next { right: 26px; }
.tsar-kh-dots {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 8px;
  margin-top: 18px;
}
.tsar-kh-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: #cbd5e1;
  border: 0;
  padding: 0;
  cursor: pointer;
  transition: all 0.25s ease;
}
.tsar-kh-dot.is-active {
  background: #e6ac98;
  transform: scale(1.25);
}

/* 4. K-Hotel "Discover Yaounde" Split Section (.fp-section-3) */
.tsar-kh-discover {
  padding: 64px 20px;
  background: #f8f9ef;
  margin: 48px 0 0;
}
.tsar-kh-discover__inner {
  max-width: 1200px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: 1fr 1fr 1.6fr;
  gap: 24px;
  align-items: stretch;
}
.tsar-kh-discover__col {
  display: flex;
  flex-direction: column;
  gap: 20px;
}
.tsar-kh-feature-box {
  background: #292929;
  color: #fff;
  padding: 38px 22px;
  text-align: center;
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
}
.tsar-kh-feature-box--light {
  background: #ffffff;
  color: #111;
  border: 1px solid #e5e7eb;
}
.tsar-kh-feature-box h3 {
  font-size: 20px !important;
  text-transform: uppercase;
  letter-spacing: 0.08em !important;
  font-weight: 300 !important;
  margin: 0 0 10px !important;
  color: inherit !important;
}
.tsar-kh-feature-box p {
  font-size: 14px;
  line-height: 1.65;
  margin: 0;
  opacity: 0.9;
}
.tsar-kh-discover__img {
  width: 100%;
  height: 220px;
  object-fit: cover;
  display: block;
}
.tsar-kh-discover__text {
  padding: 20px 16px 20px 28px;
  display: flex;
  flex-direction: column;
  justify-content: center;
}
.tsar-kh-discover__kicker {
  font-size: 13px;
  letter-spacing: 0.2em;
  text-transform: uppercase;
  color: #777;
  margin-bottom: 6px;
}
.tsar-kh-discover__text h2 {
  font-size: clamp(28px, 3.8vw, 42px) !important;
  font-weight: 300 !important;
  text-transform: uppercase;
  letter-spacing: 0.06em !important;
  color: #111 !important;
  margin: 0 0 18px !important;
}
.tsar-kh-discover__text p {
  font-size: 15.5px;
  line-height: 1.8;
  color: #333;
  margin: 0 0 14px;
}

/* 5. Room Package card booking buttons & hover polish */
body.tsar-khotel-design .tsar-kh-room-btn {
  display: inline-block;
  margin-top: 14px;
  padding: 10px 20px;
  background: #040707;
  color: #e6ac98 !important;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 2px;
  text-transform: uppercase;
  text-decoration: none !important;
  transition: all 0.25s ease;
}
body.tsar-khotel-design .tsar-kh-room-btn:hover {
  background: #e6ac98;
  color: #040707 !important;
}

/* 6. Scroll-reveal animation & Floating Scroll-to-Top button */
.tsar-kh-reveal {
  opacity: 0;
  transform: translateY(24px);
  transition: opacity 0.65s ease, transform 0.65s ease;
}
.tsar-kh-reveal.is-visible {
  opacity: 1;
  transform: translateY(0);
}
#tsar-scroll-top {
  position: fixed;
  bottom: 26px;
  right: 22px;
  width: 44px;
  height: 44px;
  background: #040707;
  color: #e6ac98;
  border: 1px solid #e6ac98;
  border-radius: 50%;
  display: none;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  cursor: pointer;
  z-index: 9980;
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.25);
}
#tsar-scroll-top.is-shown {
  display: flex;
}

/* Responsive Tablet & Android rules for K-Hotel components */
@media (max-width: 991px) {
  .tsar-kh-slide {
    flex: 0 0 50%;
    max-width: 50%;
  }
  .tsar-kh-discover__inner {
    grid-template-columns: 1fr 1fr;
  }
  .tsar-kh-discover__text {
    grid-column: 1 / -1;
    padding: 16px 4px;
  }
}
@media (max-width: 767px) {
  .tsar-kh-topbar__inner {
    justify-content: center;
    text-align: center;
    font-size: 11px;
  }
  .tsar-kh-slide {
    flex: 0 0 100%;
    max-width: 100%;
    padding: 0 4px;
  }
  .tsar-kh-card {
    height: 360px;
  }
  .tsar-kh-discover__inner {
    grid-template-columns: 1fr;
  }
  .tsar-kh-intro {
    padding: 36px 16px 28px;
  }
}
';
	}

	public function enqueue_styles() {
		$options = $this->options();
		if ( ! empty( $options['hide_empty_socials'] ) || ! empty( $options['mobile_layout_fixes'] ) || ! empty( $options['khotel_design'] ) || ! empty( $options['contact_panel'] ) || is_singular() ) {
			wp_register_style( 'tsar-hotel-updates', false, array(), self::VERSION );
			wp_enqueue_style( 'tsar-hotel-updates' );
			wp_add_inline_style( 'tsar-hotel-updates', self::inline_css() );
		}
	}

	public static function normalise_phone( $value ) {
		if ( ! is_scalar( $value ) || preg_match( '/[^0-9+().\s-]/', (string) $value ) ) {
			return '';
		}
		$value = preg_replace( '/[().\s-]/', '', (string) $value );
		return preg_match( '/^\+?([1-9][0-9]{6,14})$/D', $value, $matches ) ? '+' . $matches[1] : '';
	}

	public static function https_url( $value ) {
		if ( ! is_scalar( $value ) ) {
			return '';
		}
		$value = trim( (string) $value );
		if ( '' === $value ) {
			return '';
		}
		$parts = wp_parse_url( $value );
		if ( ! is_array( $parts ) || empty( $parts['host'] ) || 'https' !== strtolower( isset( $parts['scheme'] ) ? $parts['scheme'] : '' ) || isset( $parts['user'] ) || isset( $parts['pass'] ) ) {
			return '';
		}
		return esc_url_raw( $value, array( 'https' ) );
	}

	public function sanitize( $input ) {
		$input = is_array( $input ) ? $input : array();
		$output = self::defaults();
		foreach ( array( 'menu_repair', 'hide_empty_socials', 'mobile_layout_fixes', 'khotel_design', 'contact_panel', 'home_seo', 'demo_noindex', 'brand_heading' ) as $key ) {
			$output[ $key ] = isset( $input[ $key ] ) && is_scalar( $input[ $key ] ) && '1' === (string) $input[ $key ] ? 1 : 0;
		}
		$output['language'] = isset( $input['language'] ) && 'fr' === $input['language'] ? 'fr' : 'en';
		$output['panel_position'] = isset( $input['panel_position'] ) && 'bottom' === $input['panel_position'] ? 'bottom' : 'top';
		foreach ( array( 'phone', 'whatsapp' ) as $key ) {
			$output[ $key ] = self::normalise_phone( isset( $input[ $key ] ) ? $input[ $key ] : '' );
			if ( ! empty( $input[ $key ] ) && '' === $output[ $key ] ) {
				add_settings_error( self::OPTION, 'invalid_' . $key, 'Enter a valid international ' . $key . ' number, including its country code.', 'warning' );
			}
		}
		$email = isset( $input['email'] ) && is_scalar( $input['email'] ) ? sanitize_email( (string) $input['email'] ) : '';
		$output['email'] = is_email( $email ) ? $email : '';
		if ( ! empty( $input['email'] ) && '' === $output['email'] ) {
			add_settings_error( self::OPTION, 'invalid_email', 'The email address was not valid and was not saved.', 'warning' );
		}
		foreach ( array( 'map_url', 'facebook_url', 'instagram_url', 'tiktok_url', 'youtube_url', 'linkedin_url', 'google_reviews_url' ) as $key ) {
			$output[ $key ] = self::https_url( isset( $input[ $key ] ) ? $input[ $key ] : '' );
			if ( ! empty( $input[ $key ] ) && '' === $output[ $key ] ) {
				add_settings_error( self::OPTION, 'invalid_' . $key, 'Only complete HTTPS links without embedded login details are accepted for ' . $key . '.', 'warning' );
			}
		}
		foreach ( array( 'home_title' => 160, 'home_description' => 320 ) as $key => $limit ) {
			$value = isset( $input[ $key ] ) && is_scalar( $input[ $key ] ) ? sanitize_text_field( (string) $input[ $key ] ) : $output[ $key ];
			$output[ $key ] = function_exists( 'mb_substr' ) ? mb_substr( $value, 0, $limit ) : substr( $value, 0, $limit );
		}
		if ( $output['contact_panel'] && ! $output['phone'] && ! $output['whatsapp'] && ! $output['email'] ) {
			$output['contact_panel'] = 0;
			add_settings_error( self::OPTION, 'contacts_required', 'The automatic contact panel stays off until at least one approved telephone number, WhatsApp number or email address is entered.', 'warning' );
		}
		return $output;
	}

	public function contact_shortcode() {
		return $this->frontend_request() ? $this->render_contact_panel() : '';
	}

	public function add_contact_panel( $content ) {
		$options = $this->options();
		if ( ! $this->frontend_request() || empty( $options['contact_panel'] ) || $this->panel_added || ! in_the_loop() || ! is_main_query() || post_password_required() ) {
			return $content;
		}
		if ( ! ( is_front_page() || is_page( array( 'reservations', 'accommodations' ) ) || is_singular( 'mphb_room_type' ) ) ) {
			return $content;
		}
		if ( false !== strpos( $content, 'tsar-updates-contact' ) ) {
			return $content;
		}
		$panel = $this->render_contact_panel();
		if ( '' === $panel ) {
			return $content;
		}
		$this->panel_added = true;
		return 'bottom' === $options['panel_position'] ? $content . $panel : $panel . $content;
	}

	public function render_contact_panel() {
		$options = $this->options();
		$fr = 'fr' === $options['language'];
		$phone = self::normalise_phone( $options['phone'] );
		$whatsapp = self::normalise_phone( $options['whatsapp'] );
		$email = is_email( $options['email'] ) ? $options['email'] : '';
		$actions = array();
		if ( $whatsapp ) {
			$message = $fr ? 'Bonjour TSAR HOTEL, je souhaite me renseigner sur les disponibilités et les réservations.' : 'Hello TSAR HOTEL, I would like to enquire about availability and reservations.';
			$actions[] = array( 'href' => 'https://wa.me/' . ltrim( $whatsapp, '+' ) . '?text=' . rawurlencode( $message ), 'label' => $fr ? 'Contacter sur WhatsApp' : 'Enquire on WhatsApp', 'kind' => 'whatsapp', 'external' => true );
		}
		if ( $phone ) {
			$actions[] = array( 'href' => 'tel:' . $phone, 'label' => $fr ? 'Appeler la réception' : 'Call reception', 'kind' => 'phone', 'external' => false );
		}
		if ( $email ) {
			$actions[] = array( 'href' => 'mailto:' . $email, 'label' => $fr ? 'Envoyer un e-mail' : 'Email reception', 'kind' => 'email', 'external' => false );
		}
		if ( ! $actions ) {
			return '';
		}
		$heading = $fr ? 'Contactez TSAR HOTEL' : 'Enquire at TSAR HOTEL';
		$note = $fr ? 'Contactez notre équipe pour les tarifs et les disponibilités. Une demande ne constitue pas une réservation confirmée.' : 'Contact our team for rates and availability. An enquiry is not a confirmed reservation.';
		$html = '<section class="tsar-updates-contact" lang="' . esc_attr( $fr ? 'fr' : 'en' ) . '" aria-label="' . esc_attr( $heading ) . '">';
		$html .= '<div class="tsar-updates-contact__intro"><p class="tsar-updates-contact__brand">TSAR HOTEL — WHERE AFRICA MEETS</p><h2>' . esc_html( $heading ) . '</h2><p class="tsar-updates-contact__note">' . esc_html( $note ) . '</p></div>';
		$html .= '<div class="tsar-updates-contact__actions">';
		foreach ( $actions as $action ) {
			$external = $action['external'] ? ' target="_blank" rel="noopener noreferrer"' : '';
			$html .= '<a class="tsar-updates-action tsar-updates-action--' . esc_attr( $action['kind'] ) . '" data-tsar-action="' . esc_attr( $action['kind'] ) . '" href="' . esc_url( $action['href'] ) . '"' . $external . '>' . esc_html( $action['label'] ) . '</a>';
		}
		$html .= '</div>';
		$links = array( 'map_url' => $fr ? 'Itinéraire' : 'Directions', 'facebook_url' => 'Facebook', 'instagram_url' => 'Instagram', 'tiktok_url' => 'TikTok', 'youtube_url' => 'YouTube', 'linkedin_url' => 'LinkedIn', 'google_reviews_url' => $fr ? 'Avis Google' : 'Google Reviews' );
		$profiles = '';
		foreach ( $links as $key => $label ) {
			$url = self::https_url( $options[ $key ] );
			if ( $url ) {
				$profiles .= '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $label ) . '</a>';
			}
		}
		if ( $profiles ) {
			$html .= '<nav class="tsar-updates-contact__profiles" aria-label="' . esc_attr( $fr ? 'Liens officiels TSAR HOTEL' : 'Official TSAR HOTEL links' ) . '">' . $profiles . '</nav>';
		}
		return $html . '</section>';
	}

		public function inject_khotel_sections( $content ) {
		$options = $this->options();
		if ( ! $this->frontend_request() || empty( $options['khotel_design'] ) || ! is_front_page() || ! in_the_loop() || ! is_main_query() || post_password_required() ) {
			return $content;
		}
		if ( false !== strpos( $content, 'tsar-kh-carousel-sec' ) ) {
			return $content;
		}
		$res_url = esc_url( home_url( '/reservations/' ) );
		$acc_url = esc_url( home_url( '/accommodations/' ) );
		$suite_url = esc_url( home_url( '/accommodation/studio-suite/' ) );
		$std_url = esc_url( home_url( '/accommodation/standard-room/' ) );
		$u = esc_url( home_url( '/wp-content/uploads/' ) );

		$intro_and_slider = '
		<section class="tsar-kh-intro tsar-kh-reveal" aria-label="Welcome to TSAR HOTEL">
			<div class="tsar-kh-intro__subtitle">WORK. PLAY. STAY.</div>
			<h2 class="tsar-kh-intro__title">Where Africa Meets.</h2>
			<hr class="tsar-kh-intro__hr" />
			<p class="tsar-kh-intro__lead">
				Be inspired by TSAR HOTEL&#8217;s refined interiors, signature dining and welcoming atmosphere in Nsimeyong, Yaound&eacute;.
				Stay inspired by warm Cameroonian hospitality across our 21 well-appointed rooms, including 3 spacious Studio Suites,
				our all-day Restaurant and the vibrant <strong>TSAR Snack Lounge</strong>.
			</p>
			<div class="tsar-kh-intro__actions">
				<a class="tsar-kh-btn" href="' . $res_url . '">Book Your Stay</a>
				<a class="tsar-kh-btn tsar-kh-btn--outline" href="' . $acc_url . '">Explore Rooms &amp; Suites</a>
			</div>
		</section>

		<section class="tsar-kh-carousel-sec tsar-kh-reveal" aria-label="TSAR HOTEL Highlights">
			<button type="button" class="tsar-kh-arrow tsar-kh-arrow--prev" aria-label="Previous slide">&#10094;</button>
			<div class="tsar-kh-carousel-viewport">
				<div class="tsar-kh-carousel-track" id="tsar-kh-track">
					<div class="tsar-kh-slide">
						<a class="tsar-kh-card" href="' . $suite_url . '">
							<img src="' . $u . '2026/08/1000039276-1024x683.png" alt="TSAR HOTEL Studio Suites in Yaounde" loading="lazy" />
							<div class="tsar-kh-card__overlay">
								<span class="tsar-kh-card__tag">From XAF 35,000 / Night</span>
								<h3 class="tsar-kh-card__title">Studio Suites</h3>
								<p class="tsar-kh-card__desc">Three spacious Studio Suites crafted for business travellers, couples and extended stays in Yaound&eacute;.</p>
							</div>
						</a>
					</div>
					<div class="tsar-kh-slide">
						<a class="tsar-kh-card" href="' . $std_url . '">
							<img src="' . $u . '2020/08/slide1-free-img.jpg" alt="TSAR HOTEL Standard and Double Rooms" loading="lazy" />
							<div class="tsar-kh-card__overlay">
								<span class="tsar-kh-card__tag">From XAF 20,000 / Night</span>
								<h3 class="tsar-kh-card__title">Premium Accommodation</h3>
								<p class="tsar-kh-card__desc">Retreat to the comfort of our 21 stylish en-suite rooms with air conditioning, Wi-Fi and 24/7 reception.</p>
							</div>
						</a>
					</div>
					<div class="tsar-kh-slide">
						<a class="tsar-kh-card" href="#amenities">
							<img src="' . $u . '2026/08/file_00000000046c81f48e62fe3ec1fd9d54.png" alt="TSAR Snack Lounge Yaounde" loading="lazy" />
							<div class="tsar-kh-card__overlay">
								<span class="tsar-kh-card__tag">Evening Ambience</span>
								<h3 class="tsar-kh-card__title">TSAR Snack Lounge</h3>
								<p class="tsar-kh-card__desc">Unwind with refreshing drinks, music and warm social hospitality at TSAR Snack Lounge.</p>
							</div>
						</a>
					</div>
					<div class="tsar-kh-slide">
						<a class="tsar-kh-card" href="#amenities">
							<img src="' . $u . '2020/08/hotel-cooking.jpg" alt="Fine Dining at TSAR HOTEL Restaurant" loading="lazy" />
							<div class="tsar-kh-card__overlay">
								<span class="tsar-kh-card__tag">All-Day Dining</span>
								<h3 class="tsar-kh-card__title">Fine Dining Restaurant</h3>
								<p class="tsar-kh-card__desc">Indulge in freshly prepared Cameroonian and international dishes from breakfast through dinner.</p>
							</div>
						</a>
					</div>
					<div class="tsar-kh-slide">
						<a class="tsar-kh-card" href="' . $res_url . '">
							<img src="' . $u . '2020/06/hotel-linens.jpg" alt="Direct Reservations at TSAR HOTEL" loading="lazy" />
							<div class="tsar-kh-card__overlay">
								<span class="tsar-kh-card__tag">24/7 Reception</span>
								<h3 class="tsar-kh-card__title">Seamless Reservations</h3>
								<p class="tsar-kh-card__desc">Check availability online or contact our reception team directly for personalised group and stay arrangements.</p>
							</div>
						</a>
					</div>
				</div>
			</div>
			<button type="button" class="tsar-kh-arrow tsar-kh-arrow--next" aria-label="Next slide">&#10095;</button>
			<div class="tsar-kh-dots" id="tsar-kh-dots"></div>
		</section>';

		$discover_section = '
		<section class="tsar-kh-discover tsar-kh-reveal" aria-label="Discover Yaounde">
			<div class="tsar-kh-discover__inner">
				<div class="tsar-kh-discover__col">
					<div class="tsar-kh-feature-box">
						<h3>Heart of Nsimeyong</h3>
						<p>Ideally located in Nsimeyong, Yaound&eacute;, offering peaceful comfort with convenient access to the city&#8217;s main districts.</p>
					</div>
					<img class="tsar-kh-discover__img" src="' . $u . '2026/08/1000039276-1024x683.png" alt="TSAR HOTEL Nsimeyong Yaounde" loading="lazy" />
				</div>
				<div class="tsar-kh-discover__col">
					<img class="tsar-kh-discover__img" src="' . $u . '2026/08/file_00000000046c81f48e62fe3ec1fd9d54.png" alt="TSAR Snack Lounge and Dining" loading="lazy" />
					<div class="tsar-kh-feature-box tsar-kh-feature-box--light">
						<h3>Where Africa Meets</h3>
						<p>21 rooms including 3 Studio Suites, an on-site Restaurant and TSAR Snack Lounge tailored to both visitors and Yaound&eacute; residents.</p>
					</div>
				</div>
				<div class="tsar-kh-discover__text">
					<div class="tsar-kh-discover__kicker">THE CAPITAL OF SEVEN HILLS</div>
					<h2>Discover Yaound&eacute;</h2>
					<p>As Cameroon&#8217;s political and diplomatic capital, Yaound&eacute; blends hillside views, vibrant cultural landmarks, museums and bustling neighbourhoods.</p>
					<p>Whether you are visiting for business meetings, family celebrations, or a relaxing city retreat, TSAR HOTEL places warm Cameroonian hospitality, dining and restful comfort at the centre of your stay.</p>
					<div><a class="tsar-kh-btn" href="' . $res_url . '">Check Availability &rarr;</a></div>
				</div>
			</div>
		</section>';

		// Insert intro + 3-card slider right after the opening hero <figure>, or at top of content
		$pos = strpos( $content, '</figure>' );
		if ( false !== $pos ) {
			$insert_at = $pos + strlen( '</figure>' );
			$content = substr( $content, 0, $insert_at ) . $intro_and_slider . substr( $content, $insert_at );
		} else {
			$content = $intro_and_slider . $content;
		}
		return $content . $discover_section;
	}

	public function render_khotel_footer_js() {
		$options = $this->options();
		if ( ! $this->frontend_request() || empty( $options['khotel_design'] ) ) {
			return;
		}
		$res_url = esc_url( home_url( '/reservations/' ) );
		?>
		<button type="button" id="tsar-scroll-top" aria-label="Scroll to top">&#8593;</button>
		<script>
		(function(){
			var resUrl = <?php echo wp_json_encode( $res_url ); ?>;
			/* 1. Inject K-Hotel Top Utility Bar & Header BOOK button */
			var masthead = document.getElementById('masthead');
			if (masthead && !document.querySelector('.tsar-kh-topbar')) {
				var topbar = document.createElement('div');
				topbar.className = 'tsar-kh-topbar';
				topbar.innerHTML = '<div class="tsar-kh-topbar__inner"><span>We’ll make you feel at home — Where Africa Meets</span><span>Nsimeyong, Yaoundé &nbsp;|&nbsp; <a href="tel:+237683628079">+237 6 83 62 80 79</a> &nbsp;|&nbsp; <a href="' + resUrl + '">Book Online</a></span></div>';
				masthead.parentNode.insertBefore(topbar, masthead);

				var navWrap = masthead.querySelector('.ast-below-header-bar .ast-builder-grid-row, #ast-mobile-header .ast-builder-grid-row');
				if (navWrap && !masthead.querySelector('.tsar-kh-header-book')) {
					var bookBtn = document.createElement('a');
					bookBtn.className = 'tsar-kh-header-book';
					bookBtn.href = resUrl;
					bookBtn.textContent = 'Book';
					navWrap.appendChild(bookBtn);
				}
			}

			/* 2. Sticky Header Shrink + Scroll-to-Top visibility on scroll */
			var scrollBtn = document.getElementById('tsar-scroll-top');
			window.addEventListener('scroll', function(){
				if (window.scrollY > 30) {
					document.body.classList.add('tsar-kh-scrolled');
				} else {
					document.body.classList.remove('tsar-kh-scrolled');
				}
				if (scrollBtn) {
					if (window.scrollY > 220) {
						scrollBtn.classList.add('is-shown');
					} else {
						scrollBtn.classList.remove('is-shown');
					}
				}
			}, {passive: true});

			if (scrollBtn) {
				scrollBtn.addEventListener('click', function(){
					window.scrollTo({top: 0, behavior: 'smooth'});
				});
			}

			/* 3. Interactive K-Hotel 3-Card Carousel (Autoplay + Arrows + Dots + Touch Swipe) */
			var track = document.getElementById('tsar-kh-track');
			var dotsWrap = document.getElementById('tsar-kh-dots');
			if (track && dotsWrap) {
				var slides = track.querySelectorAll('.tsar-kh-slide');
				var current = 0;
				var timer = null;

				function perView() {
					if (window.innerWidth <= 767) return 1;
					if (window.innerWidth <= 991) return 2;
					return 3;
				}
				function maxIndex() {
					return Math.max(0, slides.length - perView());
				}
				function buildDots() {
					dotsWrap.innerHTML = '';
					var total = maxIndex() + 1;
					for (var i = 0; i < total; i++) {
						(function(idx){
							var b = document.createElement('button');
							b.type = 'button';
							b.className = 'tsar-kh-dot' + (idx === current ? ' is-active' : '');
							b.setAttribute('aria-label', 'Go to slide ' + (idx + 1));
							b.addEventListener('click', function(){ goTo(idx); resetTimer(); });
							dotsWrap.appendChild(b);
						})(i);
					}
				}
				function goTo(idx) {
					var max = maxIndex();
					if (idx > max) idx = 0;
					if (idx < 0) idx = max;
					current = idx;
					var pct = (100 / perView()) * current;
					track.style.transform = 'translateX(-' + pct + '%)';
					var dots = dotsWrap.querySelectorAll('.tsar-kh-dot');
					for (var i = 0; i < dots.length; i++) {
						dots[i].classList.toggle('is-active', i === current);
					}
				}
				function resetTimer() {
					if (timer) clearInterval(timer);
					timer = setInterval(function(){ goTo(current + 1); }, 4200);
				}
				var prevBtn = document.querySelector('.tsar-kh-arrow--prev');
				var nextBtn = document.querySelector('.tsar-kh-arrow--next');
				if (prevBtn) prevBtn.addEventListener('click', function(){ goTo(current - 1); resetTimer(); });
				if (nextBtn) nextBtn.addEventListener('click', function(){ goTo(current + 1); resetTimer(); });

				var startX = 0;
				track.addEventListener('touchstart', function(e){ startX = e.touches[0].clientX; }, {passive: true});
				track.addEventListener('touchend', function(e){
					var diff = startX - e.changedTouches[0].clientX;
					if (Math.abs(diff) > 40) {
						goTo(diff > 0 ? current + 1 : current - 1);
						resetTimer();
					}
				}, {passive: true});

				window.addEventListener('resize', function(){ buildDots(); goTo(current); });
				buildDots();
				goTo(0);
				resetTimer();
			}

			/* 4. Add K-Hotel "BOOK ROOM ->" buttons to the 4 Homepage Room Package Cards */
			var pkgHeadings = document.querySelectorAll('h4');
			for (var i = 0; i < pkgHeadings.length; i++) {
				var h4 = pkgHeadings[i];
				var txt = (h4.textContent || '').trim().toLowerCase();
				if (txt.indexOf('studio') !== -1 || txt.indexOf('double') !== -1 || txt.indexOf('executive') !== -1 || txt.indexOf('single') !== -1) {
					var cardCol = h4.parentElement;
					if (cardCol && !cardCol.querySelector('.tsar-kh-room-btn')) {
						var btn = document.createElement('a');
						btn.className = 'tsar-kh-room-btn';
						btn.href = txt.indexOf('studio') !== -1 ? <?php echo wp_json_encode( esc_url( home_url( '/accommodation/studio-suite/' ) ) ); ?> : resUrl;
						btn.textContent = 'Book Room →';
						cardCol.appendChild(btn);
					}
				}
			}

			/* 5. Make plain-text footer phone & email clickable */
			var footerTitles = document.querySelectorAll('#colophon h2.widget-title');
			for (var j = 0; j < footerTitles.length; j++) {
				var el = footerTitles[j];
				var t = (el.textContent || '').trim();
				if (t.indexOf('+237') !== -1 && !el.querySelector('a')) {
					el.innerHTML = '<a href="tel:' + t.replace(/\s+/g, '') + '" style="color:inherit;text-decoration:none;">' + t + '</a>';
				} else if (t.indexOf('@') !== -1 && !el.querySelector('a')) {
					el.innerHTML = '<a href="mailto:' + t + '" style="color:inherit;text-decoration:none;">' + t + '</a>';
				}
			}

			/* 6. Smooth scroll-reveal animations */
			var reveals = document.querySelectorAll('.tsar-kh-reveal');
			if ('IntersectionObserver' in window) {
				var io = new IntersectionObserver(function(entries){
					entries.forEach(function(entry){
						if (entry.isIntersecting) {
							entry.target.classList.add('is-visible');
						}
					});
				}, {threshold: 0.12});
				for (var k = 0; k < reveals.length; k++) io.observe(reveals[k]);
			} else {
				for (var k = 0; k < reveals.length; k++) reveals[k].classList.add('is-visible');
			}
		})();
		</script>
		<?php
	}

	public function homepage_heading( $title, $post_id = 0 ) {
		if ( ! $this->frontend_request() || empty( $this->options()['brand_heading'] ) || ! is_front_page() || ! in_the_loop() || ! is_main_query() || (int) $post_id !== (int) get_option( 'page_on_front' ) || 'Home' !== trim( wp_strip_all_tags( $title ) ) ) {
			return $title;
		}
		return 'TSAR HOTEL — WHERE AFRICA MEETS';
	}

	public function has_surerank() {
		return defined( 'SURERANK_VERSION' );
	}

	public function other_seo_plugins() {
		$active = (array) get_option( 'active_plugins', array() );
		if ( is_multisite() ) {
			$active = array_merge( $active, array_keys( (array) get_site_option( 'active_sitewide_plugins', array() ) ) );
		}
		$known = array( 'wordpress-seo/', 'wordpress-seo-premium/', 'seo-by-rank-math/', 'seo-by-rank-math-pro/', 'all-in-one-seo-pack/', 'all-in-one-seo-pack-pro/', 'wp-seopress/', 'autodescription/', 'smartcrawl-seo/', 'seo-press/' );
		$found = array();
		foreach ( $active as $plugin ) {
			foreach ( $known as $prefix ) {
				if ( 0 === strpos( $plugin, $prefix ) ) {
					$found[] = $plugin;
				}
			}
		}
		return array_unique( $found );
	}

	private function home_seo_enabled() {
		return $this->frontend_request() && ! empty( $this->options()['home_seo'] ) && is_front_page() && ! is_paged() && ! $this->other_seo_plugins();
	}

	public function surerank_home_meta( $meta ) {
		if ( ! $this->home_seo_enabled() || ! is_array( $meta ) ) {
			return $meta;
		}
		$options = $this->options();
		if ( $options['home_title'] ) {
			foreach ( array( 'page_title', 'facebook_title', 'twitter_title' ) as $key ) {
				$meta[ $key ] = $options['home_title'];
			}
		}
		if ( $options['home_description'] ) {
			foreach ( array( 'page_description', 'facebook_description', 'twitter_description' ) as $key ) {
				$meta[ $key ] = $options['home_description'];
			}
		}
		return $meta;
	}

	public function homepage_document_title( $title ) {
		if ( $this->home_seo_enabled() && $this->options()['home_title'] ) {
			return esc_html( $this->options()['home_title'] );
		}
		return $title;
	}

	public function core_home_description() {
		if ( $this->home_seo_enabled() && ! $this->has_surerank() && $this->options()['home_description'] ) {
			echo '<meta name="description" content="' . esc_attr( $this->options()['home_description'] ) . '">' . "\n";
		}
	}

	private function excluded_page_paths() {
		return array( 'sample-page', 'search-results', 'my-account', 'booking-cancellation', 'booking-confirmation', 'booking-confirmation/booking-confirmed', 'booking-confirmation/booking-canceled', 'booking-confirmation/reservation-received', 'booking-confirmation/transaction-failed' );
	}

	private function restricted_page() {
		if ( ! $this->frontend_request() || empty( $this->options()['demo_noindex'] ) || ! is_singular() || $this->other_seo_plugins() ) {
			return false;
		}
		$post = get_queried_object();
		if ( ! ( $post instanceof \WP_Post ) ) {
			return false;
		}
		return ( 'page' === $post->post_type && in_array( get_page_uri( $post->ID ), $this->excluded_page_paths(), true ) ) || ( 'post' === $post->post_type && 'hello-world' === $post->post_name );
	}

	public function core_robots( $robots ) {
		if ( $this->restricted_page() ) {
			unset( $robots['index'] );
			$robots['noindex'] = true;
		}
		return $robots;
	}

	public function surerank_robots( $robots ) {
		if ( $this->restricted_page() && is_array( $robots ) ) {
			$robots = array_values( array_diff( $robots, array( 'index' ) ) );
			$robots[] = 'noindex';
			$robots = array_values( array_unique( $robots ) );
		}
		return $robots;
	}

	private function find_excluded_ids() {
		if ( empty( $this->options()['demo_noindex'] ) || $this->other_seo_plugins() ) {
			return array();
		}
		if ( null !== $this->excluded_ids ) {
			return $this->excluded_ids;
		}
		$this->excluded_ids = array();
		foreach ( $this->excluded_page_paths() as $path ) {
			$post = get_page_by_path( $path, OBJECT, 'page' );
			if ( $post ) {
				$this->excluded_ids[] = (int) $post->ID;
			}
		}
		$post = get_page_by_path( 'hello-world', OBJECT, 'post' );
		if ( $post ) {
			$this->excluded_ids[] = (int) $post->ID;
		}
		return array_values( array_unique( $this->excluded_ids ) );
	}

	public function exclude_sitemap_ids( $ids ) {
		return array_values( array_unique( array_merge( wp_parse_id_list( $ids ), $this->find_excluded_ids() ) ) );
	}

	public function core_sitemap_args( $args, $post_type = '' ) {
		$excluded = $this->find_excluded_ids();
		if ( $excluded ) {
			$args['post__not_in'] = array_values( array_unique( array_merge( isset( $args['post__not_in'] ) ? wp_parse_id_list( $args['post__not_in'] ) : array(), $excluded ) ) );
		}
		return $args;
	}

	public function admin_menu() {
		add_options_page( 'TSAR HOTEL Updates', 'TSAR HOTEL Updates', 'manage_options', self::PAGE, array( $this, 'admin_page' ) );
	}

	public function register_settings() {
		register_setting( self::GROUP, self::OPTION, array( 'type' => 'array', 'sanitize_callback' => array( $this, 'sanitize' ), 'default' => self::defaults(), 'show_in_rest' => false ) );
	}

	public function settings_link( $links ) {
		array_unshift( $links, '<a href="' . esc_url( admin_url( 'options-general.php?page=' . self::PAGE ) ) . '">Settings</a>' );
		return $links;
	}

	private function checkbox( $key, $label, $description ) {
		$options = $this->options();
		echo '<tr><th scope="row">' . esc_html( $label ) . '</th><td><label><input type="checkbox" name="' . esc_attr( self::OPTION . '[' . $key . ']' ) . '" value="1" ' . checked( ! empty( $options[ $key ] ), true, false ) . '> Enable</label><p class="description">' . esc_html( $description ) . '</p></td></tr>';
	}

	private function text_field( $key, $label, $description, $type = 'text' ) {
		$options = $this->options();
		echo '<tr><th scope="row"><label for="tsar-' . esc_attr( $key ) . '">' . esc_html( $label ) . '</label></th><td><input class="regular-text" id="tsar-' . esc_attr( $key ) . '" name="' . esc_attr( self::OPTION . '[' . $key . ']' ) . '" type="' . esc_attr( $type ) . '" value="' . esc_attr( $options[ $key ] ) . '"><p class="description">' . esc_html( $description ) . '</p></td></tr>';
	}

	public function admin_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to manage these settings.', 'tsar-hotel-updates' ) );
		}
		$options = $this->options();
		echo '<div class="wrap"><h1>TSAR HOTEL Website Updates</h1><p>Version ' . esc_html( self::VERSION ) . ' — controlled, reversible front-end adjustments.</p>';
		echo '<div class="notice notice-warning inline"><p><strong>Back up and test on staging first.</strong> This plugin does not change room rates, availability, bookings, payments, forms, account passwords or guest records. It does not disable either cookie-consent tool. The existing overlapping banners still require an administrator to retain and configure one consent solution.</p></div>';
		if ( $this->other_seo_plugins() ) {
			echo '<div class="notice notice-warning inline"><p>Another SEO plugin was detected. Homepage SEO and search-cleanup changes are suspended to avoid conflicts. Configure the existing SEO plugin instead.</p></div>';
		}
		echo '<p>On first activation, only the menu repair and hiding of empty Astra footer social links are enabled. Other changes require your review and explicit opt-in. No contact number or profile URL is prefilled.</p>';
		settings_errors( self::OPTION );
		echo '<form method="post" action="options.php">';
		settings_fields( self::GROUP );
		echo '<h2>1. Verified technical repairs</h2><table class="form-table" role="presentation">';
		$this->checkbox( 'menu_repair', 'Repair section-menu links', 'Changes only #about, #amenities, #gallery and #packages menu destinations to the homepage. Stored menus are not rewritten.' );
		$this->checkbox( 'hide_empty_socials', 'Hide empty social icons', 'Hides only empty or placeholder Astra footer social links. Working links remain visible.' );
		$this->checkbox( 'mobile_layout_fixes', 'Apply Android / mobile layout repairs', 'Fixes the mobile homepage header/title overlap, restores the collapsed About Us image on phones (<= 480px), unhides the 3 mobile-hidden Gallery images, and improves dark footer heading contrast.' );
		$this->checkbox( 'khotel_design', 'Enable K-Hotel luxury design, slideshows & interactive JS', 'Adds the K-Hotel Douala inspired sticky dark header with BOOK button, WORK. PLAY. STAY. editorial intro, 3-card autoplay/touch-swipeable Experience Slider, room-card booking buttons, Discover Yaounde split section, scroll-reveal animations and scroll-to-top button.' );
		echo '</table><h2>2. Approved reception contacts</h2><p>Enter only contacts the hotel owns, monitors and approves. Do not enter passwords or API keys.</p><table class="form-table" role="presentation">';
		$this->text_field( 'phone', 'Reception telephone', 'Use the full international number including its country code. Leave blank until confirmed.', 'tel' );
		$this->text_field( 'whatsapp', 'WhatsApp Business number', 'Enter separately; a published telephone number is not assumed to be active on WhatsApp.', 'tel' );
		$this->text_field( 'email', 'Reception email', 'Confirm the mailbox works and reception monitors it.', 'email' );
		$this->text_field( 'map_url', 'Directions link', 'Paste the confirmed hotel map/directions HTTPS link.', 'url' );
		$this->checkbox( 'contact_panel', 'Add the enquiry panel automatically', 'Adds a normal-flow, non-sticky panel to the homepage, reservations, accommodations and MotoPress room pages. At least one approved contact is required.' );
		echo '<tr><th scope="row"><label for="tsar-language">Panel language</label></th><td><select id="tsar-language" name="' . esc_attr( self::OPTION ) . '[language]"><option value="en" ' . selected( $options['language'], 'en', false ) . '>English</option><option value="fr" ' . selected( $options['language'], 'fr', false ) . '>Français</option></select><p class="description">Changes this panel only; it does not translate the website.</p></td></tr>';
		echo '<tr><th scope="row"><label for="tsar-position">Automatic panel position</label></th><td><select id="tsar-position" name="' . esc_attr( self::OPTION ) . '[panel_position]"><option value="top" ' . selected( $options['panel_position'], 'top', false ) . '>Before page content</option><option value="bottom" ' . selected( $options['panel_position'], 'bottom', false ) . '>After page content</option></select><p class="description">For precise placement, leave automatic placement off and insert the shortcode [tsar_contact_actions] in a Shortcode block.</p></td></tr></table>';
		echo '<h2>3. Official profile links</h2><p>Optional links appear in the enquiry panel. They do not create accounts or overwrite theme settings. Empty links are not displayed.</p><table class="form-table" role="presentation">';
		foreach ( array( 'facebook_url' => 'Facebook', 'instagram_url' => 'Instagram', 'tiktok_url' => 'TikTok', 'youtube_url' => 'YouTube', 'linkedin_url' => 'LinkedIn', 'google_reviews_url' => 'Google Reviews' ) as $key => $label ) {
			$this->text_field( $key, $label, 'Paste the confirmed official HTTPS URL; otherwise leave blank.', 'url' );
		}
		echo '</table><h2>4. Optional brand and SEO adjustments</h2><table class="form-table" role="presentation">';
		$this->checkbox( 'brand_heading', 'Replace the generic homepage heading', 'Replaces only the visible main-loop homepage title Home with TSAR HOTEL — WHERE AFRICA MEETS. It does not change the stored page title or navigation labels.' );
		$this->checkbox( 'home_seo', 'Use the homepage SEO copy below', 'Supports SureRank and core WordPress. SureRank social-sharing titles/descriptions are aligned without emitting a second description tag. Review all text before enabling.' );
		$this->text_field( 'home_title', 'Homepage SEO title', 'This changes the browser/search title, not every visible heading.' );
		echo '<tr><th scope="row"><label for="tsar-description">Homepage description</label></th><td><textarea class="large-text" rows="3" id="tsar-description" name="' . esc_attr( self::OPTION ) . '[home_description]">' . esc_textarea( $options['home_description'] ) . '</textarea><p class="description">Use accurate, management-approved wording. Do not claim unconfirmed opening dates, rates or services.</p></td></tr>';
		$this->checkbox( 'demo_noindex', 'Limit indexing of demo / booking utility pages', 'Adds noindex to the specifically documented demo and transaction pages, and excludes their IDs from core/SureRank sitemaps. It never deletes them or blocks booking access. Regenerate any cached sitemap after enabling or disabling.' );
		echo '</table>';
		submit_button( 'Save approved settings' );
		echo '</form><h2>After saving</h2><ol><li>Clear the relevant website/page caches. If search cleanup changed, regenerate the cached sitemap using the SEO plugin’s supported tools.</li><li>Test mobile and desktop pages, all menu destinations and the contact buttons.</li><li>With reception approval, test enquiry delivery and reservation behaviour separately; this plugin does not prove those work.</li><li>Keep exactly one reviewed consent solution; do not disable both or remove consent just to hide banners.</li></ol><p><strong>Rollback:</strong> deactivate TSAR HOTEL Website Updates and clear caches. The original stored content and menus were not rewritten. Use the backup if other changes were made outside this plugin.</p></div>';
	}
}

register_activation_hook( __FILE__, array( __NAMESPACE__ . '\\Plugin', 'activate' ) );
new Plugin();
