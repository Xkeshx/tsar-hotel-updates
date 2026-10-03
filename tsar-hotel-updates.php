<?php
/**
 * Plugin Name: TSAR HOTEL Website Updates
 * Description: Reversible navigation repairs, Android/mobile layout fixes, empty social-link cleanup, configurable enquiry panels, and optional homepage SEO for TSAR HOTEL.
 * Version: 1.4.0
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
	const VERSION = '1.4.0';
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
		return '/* Scoped, local-only styles for TSAR HOTEL Website Updates (v1.3.0 - 1:1 K-Hotel Douala Design) */
body.tsar-updates-clean-socials #colophon a.ast-builder-social-element[href=""],
body.tsar-updates-clean-socials #colophon a.ast-builder-social-element[href="#"],
body.tsar-updates-clean-socials #colophon a.ast-builder-social-element:not([href]) {
	display: none !important;
}

/* ==========================================================================
   1:1 K-HOTEL DOUALA LUXURY DESIGN & STRUCTURE (body.tsar-khotel-design)
   ========================================================================== */
body.tsar-khotel-design {
	font-family: "Lato", "Helvetica Neue", Arial, sans-serif !important;
	font-weight: 300 !important;
	letter-spacing: 0.04em;
	color: #111111;
	background-color: #ffffff !important;
	margin: 0;
	padding: 0;
	overflow-x: hidden;
}

/* Hide intrusive duplicate cookie modals on mobile/desktop so they don\'t block the layout */
body.tsar-khotel-design .cookieadmin_law_container,
body.tsar-khotel-design .cookieadmin_cookie_modal,
body.tsar-khotel-design #cookieadmin_consent_box,
body.tsar-khotel-design .surecookie-banner,
body.tsar-khotel-design #surecookie-consent-banner {
	display: none !important;
}

/* Hide Astra\'s bulky 2-row header & broken 1200px footer when K-Hotel design is active */
body.tsar-khotel-design #masthead,
body.tsar-khotel-design #colophon,
body.tsar-khotel-design .entry-header {
	display: none !important;
}

/* Full-bleed container overrides on homepage */
body.home.tsar-khotel-design #content,
body.home.tsar-khotel-design .ast-container,
body.home.tsar-khotel-design #primary,
body.home.tsar-khotel-design #main,
body.home.tsar-khotel-design article.page,
body.home.tsar-khotel-design .entry-content,
body.home.tsar-khotel-design .entry-content > * {
	max-width: 100% !important;
	width: 100% !important;
	padding: 0 !important;
	margin: 0 !important;
}

/* 1. K-HOTEL SINGLE-ROW SLIM STICKY HEADER (#tsar-kh-header) */
#tsar-kh-header {
	position: sticky;
	top: 0;
	left: 0;
	right: 0;
	width: 100%;
	background-color: #040707;
	z-index: 9995;
	transition: all 0.3s ease;
	border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}
.tsar-kh-header__inner {
	max-width: 1320px;
	margin: 0 auto;
	height: 84px;
	padding: 0 28px;
	display: flex;
	align-items: center;
	justify-content: space-between;
	box-sizing: border-box;
	transition: height 0.3s ease;
}
body.tsar-kh-scrolled .tsar-kh-header__inner {
	height: 68px;
}
.tsar-kh-logo {
	display: flex;
	align-items: center;
	gap: 12px;
	text-decoration: none !important;
}
.tsar-kh-logo img {
	height: 56px !important;
	width: auto !important;
	display: block;
	transition: height 0.3s ease;
}
body.tsar-kh-scrolled .tsar-kh-logo img {
	height: 44px !important;
}
.tsar-kh-logo__text {
	color: #ffffff;
	font-size: 20px;
	font-weight: 300;
	letter-spacing: 0.22em;
	text-transform: uppercase;
}
.tsar-kh-logo__text strong {
	color: #e6ac98;
	font-weight: 700;
}
.tsar-kh-nav-wrap {
	display: flex;
	align-items: center;
	gap: 0;
}
.tsar-kh-nav {
	display: flex;
	align-items: center;
	list-style: none !important;
	margin: 0 !important;
	padding: 0 !important;
}
.tsar-kh-nav li {
	list-style: none !important;
	margin: 0 !important;
	padding: 0 !important;
}
.tsar-kh-nav a {
	display: inline-block;
	color: #ffffff !important;
	font-size: 12.5px;
	font-weight: 400;
	letter-spacing: 0.16em;
	text-transform: uppercase;
	text-decoration: none !important;
	padding: 6px 18px;
	border-right: 1px solid rgba(255, 255, 255, 0.22);
	transition: color 0.25s ease;
}
.tsar-kh-nav li:last-child a {
	border-right: none;
}
.tsar-kh-nav a:hover {
	color: #e6ac98 !important;
}
.tsar-kh-book-btn {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	background-color: #e6ac98;
	color: #ffffff !important;
	font-size: 12.5px;
	font-weight: 400;
	letter-spacing: 0.22em;
	text-transform: uppercase;
	text-decoration: none !important;
	height: 44px;
	padding: 0 22px;
	margin-left: 14px;
	transition: background-color 0.25s ease;
}
.tsar-kh-book-btn:hover {
	background-color: #d39680;
	color: #ffffff !important;
}
.tsar-kh-burger {
	display: inline-flex;
	flex-direction: column;
	justify-content: center;
	align-items: center;
	gap: 6px;
	width: 48px !important;
	height: 44px !important;
	background: #1c1f1f !important;
	border: none !important;
	padding: 0 !important;
	margin-left: 10px;
	cursor: pointer;
	border-radius: 0 !important;
}
.tsar-kh-burger span {
	display: block;
	width: 26px;
	height: 2px;
	background: #e6ac98;
	transition: all 0.25s ease;
}

/* K-Hotel Slide-Out Full Drawer Menu (.mega-menu) */
#tsar-kh-drawer {
	position: fixed;
	top: 0;
	right: -420px;
	width: 360px;
	max-width: 88vw;
	height: 100vh;
	background: #040707;
	color: #ffffff;
	z-index: 9999;
	padding: 36px 32px;
	box-sizing: border-box;
	transition: right 0.35s ease;
	overflow-y: auto;
	box-shadow: -10px 0 30px rgba(0, 0, 0, 0.5);
}
#tsar-kh-drawer.is-open {
	right: 0;
}
.tsar-kh-drawer__close {
	background: transparent !important;
	border: 1px solid rgba(230, 172, 152, 0.4) !important;
	color: #e6ac98 !important;
	width: 40px !important;
	height: 40px !important;
	padding: 0 !important;
	font-size: 22px;
	cursor: pointer;
	float: right;
}
.tsar-kh-drawer__section {
	margin-top: 28px;
	clear: both;
}
.tsar-kh-drawer__heading {
	display: block;
	color: #e6ac98 !important;
	font-size: 14px;
	font-weight: 700;
	letter-spacing: 0.18em;
	text-transform: uppercase;
	text-decoration: none !important;
	margin-bottom: 10px;
	padding-bottom: 6px;
	border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}
.tsar-kh-drawer__list {
	list-style: none !important;
	margin: 0 !important;
	padding: 0 !important;
}
.tsar-kh-drawer__list li {
	margin: 7px 0 !important;
}
.tsar-kh-drawer__list a {
	color: rgba(255, 255, 255, 0.82) !important;
	font-size: 13.5px;
	text-decoration: none !important;
	letter-spacing: 0.06em;
}
.tsar-kh-drawer__list a:hover {
	color: #e6ac98 !important;
}

/* 2. FULL-BLEED HERO BANNER SLIDESHOW (.tsar-kh-hero) */
.tsar-kh-hero {
	position: relative;
	width: 100%;
	height: 76vh;
	min-height: 480px;
	max-height: 720px;
	overflow: hidden;
	background: #040707;
}
.tsar-kh-hero__slide {
	position: absolute;
	inset: 0;
	opacity: 0;
	transition: opacity 1s ease-in-out;
}
.tsar-kh-hero__slide.is-active {
	opacity: 1;
	z-index: 2;
}
.tsar-kh-hero__slide img {
	width: 100% !important;
	height: 100% !important;
	object-fit: cover !important;
	object-position: center !important;
	display: block;
}
.tsar-kh-hero__dots {
	position: absolute;
	bottom: 22px;
	left: 50%;
	transform: translateX(-50%);
	z-index: 10;
	display: flex;
	gap: 10px;
}
.tsar-kh-hero__dot {
	width: 10px !important;
	height: 10px !important;
	padding: 0 !important;
	border-radius: 50% !important;
	border: 1px solid #ffffff !important;
	background: rgba(255, 255, 255, 0.35) !important;
	cursor: pointer;
}
.tsar-kh-hero__dot.is-active {
	background: #e6ac98 !important;
	border-color: #e6ac98 !important;
}

/* 3. SECTION 1: K-HOTEL EDITORIAL INTRO (.fp-slider-room-text) */
body.home.tsar-khotel-design .entry-content > .tsar-kh-intro {
	max-width: 980px !important;
}
body.home.tsar-khotel-design .entry-content > .tsar-kh-slider-section {
	max-width: 1420px !important;
}
.tsar-kh-intro {
	text-align: center;
	padding: 68px 24px 54px;
	max-width: 980px;
	margin: 0 auto;
	background: #ffffff;
}
.tsar-kh-intro__subtitle {
	color: #777777;
	font-weight: 300;
	letter-spacing: 0.18em;
	font-size: 13px;
	text-transform: uppercase;
	margin-bottom: 10px;
}
.tsar-kh-intro__title {
	font-family: "Lato", sans-serif !important;
	font-size: clamp(28px, 4.2vw, 50px) !important;
	font-weight: 300 !important;
	letter-spacing: 0.08em !important;
	color: #111111 !important;
	text-transform: uppercase;
	margin: 0 0 22px !important;
	line-height: 1.2 !important;
}
.tsar-kh-intro__hr {
	width: 160px;
	height: 1px;
	border: 0;
	background: #e5e5e5;
	margin: 0 auto 28px;
}
.tsar-kh-intro__p {
	font-size: 15.5px;
	font-weight: 300;
	line-height: 1.85;
	color: #333333;
	letter-spacing: 0.03em;
	margin: 0 auto 22px;
	max-width: 880px;
}
.tsar-kh-intro__welcome {
	font-size: 15.5px;
	font-weight: 400;
	color: #222222;
	margin: 12px 0 0;
}

/* 4. SECTION 2: K-HOTEL 3-CARD CAROUSEL WITH OVERLAPPING DARK CAPTION BOX */
.tsar-kh-slider-section {
	position: relative;
	width: 100%;
	max-width: 1420px;
	margin: 0 auto 70px;
	padding: 0 12px;
	box-sizing: border-box;
	background: #ffffff;
}
.tsar-kh-slider-grid {
	display: grid;
	grid-template-columns: 1fr 1fr 1fr;
	gap: 24px;
	align-items: start;
	position: relative;
}
.tsar-kh-slider-slot {
	position: relative;
}
.tsar-kh-slider-img-wrap {
	width: 100%;
	height: 360px;
	overflow: hidden;
	background: #111;
	position: relative;
}
.tsar-kh-slider-img-wrap img {
	width: 100% !important;
	height: 100% !important;
	object-fit: cover !important;
	display: block;
	transition: transform 0.6s ease;
}
.tsar-kh-slider-slot:hover .tsar-kh-slider-img-wrap img {
	transform: scale(1.04);
}
/* Overlapping dark box on the center card (exact match to K-Hotel .archive-show-content-bottom) */
.tsar-kh-center-box {
	background: #222222;
	color: #ffffff;
	width: 88%;
	margin: -88px auto 0;
	padding: 34px 28px 36px;
	position: relative;
	z-index: 5;
	box-sizing: border-box;
	min-height: 165px;
	display: flex;
	flex-direction: column;
	justify-content: center;
	text-decoration: none !important;
	box-shadow: 0 12px 30px rgba(0, 0, 0, 0.18);
}
.tsar-kh-center-box h3 {
	font-family: "Lato", sans-serif !important;
	font-size: 19px !important;
	font-weight: 300 !important;
	letter-spacing: 0.08em !important;
	text-transform: uppercase;
	color: #ffffff !important;
	margin: 0 0 10px !important;
}
.tsar-kh-center-box p {
	font-size: 13.5px;
	font-weight: 300;
	line-height: 1.65;
	color: rgba(255, 255, 255, 0.88);
	margin: 0;
}
/* Square K-Hotel < and > buttons on the edges of the center card */
.tsar-kh-sq-arrow {
	position: absolute !important;
	top: 154px !important;
	width: 54px !important;
	height: 54px !important;
	padding: 0 !important;
	margin: 0 !important;
	border: none !important;
	border-radius: 0 !important;
	display: flex !important;
	align-items: center !important;
	justify-content: center !important;
	font-size: 24px !important;
	font-weight: 700 !important;
	cursor: pointer !important;
	z-index: 15 !important;
	box-shadow: 0 4px 14px rgba(0, 0, 0, 0.18);
	transition: opacity 0.25s ease;
}
.tsar-kh-sq-arrow:hover {
	opacity: 0.9;
}
.tsar-kh-sq-arrow--prev {
	background-color: #ffffff !important;
	color: #e6ac98 !important;
	left: calc(33.333% - 70px) !important;
}
.tsar-kh-sq-arrow--next {
	background-color: #e6ac98 !important;
	color: #ffffff !important;
	right: calc(33.333% - 70px) !important;
}

/* 5. SECTION 3: K-HOTEL ROOMS & SUITES SHOWCASE (#packages) */
.tsar-kh-rooms-sec {
	padding: 64px 24px;
	background: #fafafa;
	border-top: 1px solid #f0f0f0;
}
.tsar-kh-rooms-sec__inner {
	max-width: 1240px;
	margin: 0 auto;
}
.tsar-kh-sec-head {
	text-align: center;
	margin-bottom: 44px;
}
.tsar-kh-sec-head span {
	display: block;
	color: #777777;
	font-size: 12.5px;
	letter-spacing: 0.2em;
	text-transform: uppercase;
	margin-bottom: 8px;
}
.tsar-kh-sec-head h2 {
	font-family: "Lato", sans-serif !important;
	font-size: clamp(26px, 3.5vw, 40px) !important;
	font-weight: 300 !important;
	letter-spacing: 0.08em !important;
	text-transform: uppercase;
	color: #111111 !important;
	margin: 0 !important;
}
.tsar-kh-rooms-grid {
	display: grid;
	grid-template-columns: repeat(4, 1fr);
	gap: 24px;
}
.tsar-kh-room-card {
	background: #ffffff;
	border: 1px solid #eaeaea;
	display: flex;
	flex-direction: column;
	transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.tsar-kh-room-card:hover {
	transform: translateY(-4px);
	box-shadow: 0 12px 28px rgba(0, 0, 0, 0.08);
}
.tsar-kh-room-card__img {
	position: relative;
	height: 220px;
	overflow: hidden;
	background: #111;
}
.tsar-kh-room-card__img img {
	width: 100% !important;
	height: 100% !important;
	object-fit: cover !important;
	display: block;
}
.tsar-kh-room-card__rate {
	position: absolute;
	bottom: 0;
	left: 0;
	right: 0;
	background: rgba(4, 7, 7, 0.86);
	color: #e6ac98;
	font-size: 12.5px;
	font-weight: 400;
	letter-spacing: 0.12em;
	text-transform: uppercase;
	padding: 9px 16px;
}
.tsar-kh-room-card__body {
	padding: 24px 20px;
	flex: 1;
	display: flex;
	flex-direction: column;
	justify-content: space-between;
}
.tsar-kh-room-card__body h3 {
	font-family: "Lato", sans-serif !important;
	font-size: 18px !important;
	font-weight: 400 !important;
	letter-spacing: 0.08em !important;
	text-transform: uppercase;
	color: #111111 !important;
	margin: 0 0 10px !important;
}
.tsar-kh-room-card__body p {
	font-size: 14px;
	line-height: 1.65;
	color: #555555;
	margin: 0 0 18px;
}
.tsar-kh-room-card__cta {
	display: inline-block;
	text-align: center;
	background: #040707;
	color: #ffffff !important;
	font-size: 11.5px;
	letter-spacing: 0.2em;
	text-transform: uppercase;
	text-decoration: none !important;
	padding: 12px 16px;
	transition: background 0.25s ease;
}
.tsar-kh-room-card__cta:hover {
	background: #e6ac98;
	color: #040707 !important;
}

/* 6. SECTION 4: K-HOTEL "DISCOVER YAOUNDE" STAGGERED SECTION (.fp-section-3) */
.tsar-kh-discover {
	padding: 76px 24px;
	background: #f8f9ef;
}
.tsar-kh-discover__inner {
	max-width: 1200px;
	margin: 0 auto;
	display: grid;
	grid-template-columns: 1fr 1fr 1.65fr;
	gap: 26px;
	align-items: stretch;
}
.tsar-kh-discover__col {
	display: flex;
	flex-direction: column;
	gap: 24px;
}
.tsar-kh-fbox {
	background: #292929;
	color: #ffffff;
	padding: 44px 24px;
	text-align: center;
	flex: 1;
	display: flex;
	flex-direction: column;
	justify-content: center;
}
.tsar-kh-fbox--white {
	background: #ffffff;
	color: #111111;
}
.tsar-kh-fbox h3 {
	font-family: "Lato", sans-serif !important;
	font-size: 20px !important;
	font-weight: 300 !important;
	letter-spacing: 0.08em !important;
	text-transform: uppercase;
	color: inherit !important;
	margin: 0 0 12px !important;
}
.tsar-kh-fbox p {
	font-size: 14px;
	line-height: 1.65;
	color: inherit;
	opacity: 0.88;
	margin: 0;
}
.tsar-kh-discover__img {
	width: 100% !important;
	height: 230px !important;
	object-fit: cover !important;
	display: block;
}
.tsar-kh-discover__editorial {
	padding: 16px 12px 16px 32px;
	display: flex;
	flex-direction: column;
	justify-content: center;
}
.tsar-kh-discover__editorial h2 {
	font-family: "Lato", sans-serif !important;
	font-size: clamp(28px, 3.6vw, 44px) !important;
	font-weight: 300 !important;
	letter-spacing: 0.06em !important;
	text-transform: uppercase;
	color: #111111 !important;
	margin: 0 0 22px !important;
}
.tsar-kh-discover__editorial p {
	font-size: 15px;
	line-height: 1.85;
	color: #333333;
	margin: 0 0 16px;
}
.tsar-kh-discover__btn {
	display: inline-block;
	align-self: flex-start;
	background: #e6ac98;
	color: #ffffff !important;
	font-size: 12px;
	letter-spacing: 0.2em;
	text-transform: uppercase;
	text-decoration: none !important;
	padding: 14px 28px;
	margin-top: 8px;
}
.tsar-kh-discover__btn:hover {
	background: #040707;
}

/* 7. SECTION 5: K-HOTEL 4-COLUMN ARCHITECTURAL FOOTER (#tsar-kh-footer) */
#tsar-kh-footer {
	background-color: #fbfbf9;
	background-image: radial-gradient(#e5e0d8 0.75px, transparent 0.75px);
	background-size: 22px 22px;
	color: #222222;
	padding: 72px 24px 0;
	border-top: 1px solid #eceae4;
}
.tsar-kh-footer__grid {
	max-width: 1200px;
	margin: 0 auto 56px;
	display: grid;
	grid-template-columns: 1.3fr 1fr 1fr 1.3fr;
	gap: 36px;
}
.tsar-kh-footer__about p {
	font-size: 14px;
	line-height: 1.8;
	color: #333333;
	margin: 0 0 20px;
}
.tsar-kh-footer__badge {
	display: inline-flex;
	align-items: center;
	gap: 8px;
	font-size: 13px;
	font-weight: 700;
	letter-spacing: 0.1em;
	color: #040707;
	text-transform: uppercase;
}
.tsar-kh-footer__links {
	list-style: none !important;
	margin: 0 !important;
	padding: 0 !important;
}
.tsar-kh-footer__links li {
	margin: 0 0 14px !important;
}
.tsar-kh-footer__links a {
	color: #222222 !important;
	font-size: 14px;
	font-weight: 300;
	text-decoration: none !important;
	transition: color 0.2s ease;
}
.tsar-kh-footer__links a:hover {
	color: #e6ac98 !important;
}
.tsar-kh-footer__contact-item {
	display: flex;
	align-items: flex-start;
	gap: 14px;
	margin-bottom: 18px;
	padding-bottom: 16px;
	border-bottom: 1px solid rgba(0, 0, 0, 0.06);
}
.tsar-kh-footer__icon {
	width: 34px;
	height: 34px;
	border-radius: 50%;
	border: 1px solid #e6ac98;
	color: #e6ac98;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 15px;
	flex-shrink: 0;
	margin-top: 2px;
}
.tsar-kh-footer__contact-item small {
	display: block;
	font-size: 12px;
	color: #777777;
	margin-bottom: 3px;
}
.tsar-kh-footer__contact-item a,
.tsar-kh-footer__contact-item span {
	color: #111111 !important;
	font-size: 14px;
	font-weight: 400;
	text-decoration: none !important;
}
.tsar-kh-footer__map-btn {
	display: inline-block;
	background: #e6ac98;
	color: #ffffff !important;
	font-size: 11.5px;
	letter-spacing: 0.18em;
	text-transform: uppercase;
	text-decoration: none !important;
	padding: 13px 22px;
	margin-top: 4px;
}
.tsar-kh-footer__bar {
	max-width: 1200px;
	margin: 0 auto;
	background: #040707;
	color: rgba(255, 255, 255, 0.8);
	padding: 18px 28px;
	font-size: 12.5px;
	letter-spacing: 0.06em;
	display: flex;
	justify-content: space-between;
	align-items: center;
	flex-wrap: wrap;
	gap: 10px;
}

/* Floating Scroll-to-Top button */
#tsar-scroll-top {
	position: fixed !important;
	bottom: 24px !important;
	right: 24px !important;
	width: 44px !important;
	height: 44px !important;
	padding: 0 !important;
	line-height: 44px !important;
	border-radius: 50% !important;
	background: #040707 !important;
	color: #e6ac98 !important;
	border: 1px solid #e6ac98 !important;
	display: none;
	align-items: center;
	justify-content: center;
	font-size: 18px !important;
	cursor: pointer;
	z-index: 9980;
}
#tsar-scroll-top.is-shown {
	display: flex !important;
}

/* ==========================================================================
   RESPONSIVE TABLET & ANDROID RULES (Matches khotel_mobile_1.jpg & 2.jpg)
   ========================================================================== */
@media (max-width: 991px) {
	.tsar-kh-nav {
		display: none !important;
	}
	.tsar-kh-rooms-grid {
		grid-template-columns: repeat(2, 1fr);
	}
	.tsar-kh-discover__inner {
		grid-template-columns: 1fr 1fr;
	}
	.tsar-kh-discover__editorial {
		grid-column: 1 / -1;
		padding: 16px 4px;
	}
	.tsar-kh-footer__grid {
		grid-template-columns: 1fr 1fr;
	}
}

@media (max-width: 767px) {
	.tsar-kh-header__inner {
		height: 70px;
		padding: 0 16px;
	}
	.tsar-kh-logo img {
		height: 44px !important;
	}
	.tsar-kh-logo__text {
		font-size: 16px;
		letter-spacing: 0.16em;
	}
	.tsar-kh-book-btn {
		height: 38px;
		padding: 0 14px;
		font-size: 11px;
		margin-left: 6px;
	}
	.tsar-kh-hero {
		height: 52vh;
		min-height: 320px;
	}
	.tsar-kh-intro {
		padding: 44px 18px 36px;
	}
	/* On mobile, show only the active center slide + overlapping dark box + < > arrows */
	.tsar-kh-slider-grid {
		grid-template-columns: 1fr;
	}
	.tsar-kh-slider-slot--left,
	.tsar-kh-slider-slot--right {
		display: none !important;
	}
	.tsar-kh-sq-arrow--prev {
		left: 12px !important;
		top: 90px !important;
		background-color: #e6ac98 !important;
		color: #ffffff !important;
	}
	.tsar-kh-sq-arrow--next {
		right: 12px !important;
		top: 90px !important;
	}
	.tsar-kh-rooms-grid {
		grid-template-columns: 1fr;
	}
	.tsar-kh-discover__inner {
		grid-template-columns: 1fr;
	}
	.tsar-kh-footer__grid {
		grid-template-columns: 1fr;
		gap: 28px;
	}
}

/* ==========================================================================
   VERSION 1.4.0: WORDPRESS ADMIN BAR FIX + INNER PAGES LUXURY STYLING
   (/accommodations/, /reservations/, /accommodation/studio-suite/, etc.)
   ========================================================================== */
body.admin-bar #tsar-kh-header {
	top: 32px !important;
}
@media (max-width: 782px) {
	body.admin-bar #tsar-kh-header {
		top: 46px !important;
	}
}

/* Fix invisible white-on-white links inside MotoPress / Astra content */
body.tsar-khotel-design .mphb-room-type-title,
body.tsar-khotel-design .mphb-room-type-title a,
body.tsar-khotel-design .mphb-loop-room-type-attributes a,
body.tsar-khotel-design .mphb-single-room-type-attributes a {
	color: #111111 !important;
	text-decoration: none !important;
}
body.tsar-khotel-design .mphb-loop-room-type-attributes a:hover,
body.tsar-khotel-design .mphb-single-room-type-attributes a:hover {
	color: #e6ac98 !important;
}

/* Show & style .entry-header on inner pages (keep hidden on homepage) */
body.tsar-khotel-design:not(.home) .entry-header {
	display: block !important;
	background: #0b1117;
	color: #ffffff;
	text-align: center;
	padding: 54px 24px 48px !important;
	margin: 0 0 44px !important;
	border-bottom: 2px solid #e6ac98;
}
body.tsar-khotel-design:not(.home) .entry-header .entry-title {
	font-family: "Lato", sans-serif !important;
	font-size: clamp(28px, 4vw, 44px) !important;
	font-weight: 300 !important;
	letter-spacing: 0.12em !important;
	text-transform: uppercase !important;
	color: #ffffff !important;
	margin: 0 !important;
}

/* Hide blog author/date/comment meta & comment form on hotel room pages */
body.tsar-khotel-design .entry-meta,
body.tsar-khotel-design #comments,
body.tsar-khotel-design .comments-area,
body.tsar-khotel-design .post-navigation {
	display: none !important;
}

/* Inner page container width & breathing room */
body.tsar-khotel-design:not(.home) #content .ast-container {
	max-width: 1180px !important;
	margin: 0 auto !important;
	padding: 0 20px 64px !important;
	box-sizing: border-box !important;
}
body.tsar-khotel-design:not(.home) #primary {
	width: 100% !important;
	max-width: 100% !important;
	margin: 0 !important;
	padding: 0 !important;
}

/* Style /accommodations/ Room Cards in a 2-Column K-Hotel Luxury Grid */
body.tsar-khotel-design .mphb_sc_rooms-wrapper.mphb-room-types {
	display: grid;
	grid-template-columns: repeat(2, 1fr);
	gap: 32px;
	margin-top: 10px;
}
body.tsar-khotel-design .mphb_sc_rooms-wrapper .mphb-room-type {
	background: #ffffff;
	border: 1px solid #e5e7eb;
	padding: 0 0 28px !important;
	margin: 0 !important;
	box-shadow: 0 10px 28px rgba(0, 0, 0, 0.05);
	display: flex;
	flex-direction: column;
	overflow: hidden;
}
.tsar-kh-injected-room-banner {
	width: 100%;
	height: 260px;
	overflow: hidden;
	background: #111;
	margin-bottom: 22px;
	position: relative;
}
.tsar-kh-injected-room-banner img {
	width: 100% !important;
	height: 100% !important;
	object-fit: cover !important;
	display: block;
}
body.tsar-khotel-design .mphb_sc_rooms-wrapper .mphb-room-type > *:not(.tsar-kh-injected-room-banner) {
	padding-left: 26px !important;
	padding-right: 26px !important;
}
body.tsar-khotel-design .mphb-room-type-title {
	font-family: "Lato", sans-serif !important;
	font-size: 24px !important;
	font-weight: 300 !important;
	letter-spacing: 0.08em !important;
	text-transform: uppercase;
	margin: 0 0 12px !important;
}
body.tsar-khotel-design .mphb-loop-room-type-attributes,
body.tsar-khotel-design .mphb-single-room-type-attributes {
	list-style: none !important;
	margin: 12px 0 18px !important;
	padding: 14px 18px !important;
	background: #f8f9ef;
	border-left: 3px solid #e6ac98;
}
body.tsar-khotel-design .mphb-loop-room-type-attributes li,
body.tsar-khotel-design .mphb-single-room-type-attributes li {
	margin: 6px 0 !important;
	font-size: 14px;
	color: #222 !important;
}
body.tsar-khotel-design .mphb-regular-price {
	font-size: 16px !important;
	margin: 12px 0 20px !important;
	color: #111 !important;
}
body.tsar-khotel-design .mphb-price {
	color: #040707 !important;
	font-weight: 700 !important;
	font-size: 20px !important;
}

/* Replace yellow Astra buttons on inner pages with K-Hotel Obsidian & Rose-Gold buttons */
body.tsar-khotel-design .mphb-view-details-button,
body.tsar-khotel-design .mphb-book-button,
body.tsar-khotel-design .mphb_sc_search-submit-button-wrapper input[type="submit"],
body.tsar-khotel-design .mphb-reserve-btn {
	background-color: #040707 !important;
	color: #ffffff !important;
	border: 1px solid #040707 !important;
	border-radius: 0 !important;
	font-family: "Lato", sans-serif !important;
	font-size: 12px !important;
	font-weight: 400 !important;
	letter-spacing: 0.2em !important;
	text-transform: uppercase !important;
	padding: 13px 26px !important;
	text-decoration: none !important;
	display: inline-block !important;
	cursor: pointer !important;
	transition: all 0.25s ease !important;
}
body.tsar-khotel-design .mphb-book-button,
body.tsar-khotel-design .mphb_sc_search-submit-button-wrapper input[type="submit"],
body.tsar-khotel-design .mphb-reserve-btn {
	background-color: #e6ac98 !important;
	color: #040707 !important;
	border-color: #e6ac98 !important;
	font-weight: 700 !important;
}
body.tsar-khotel-design .mphb-view-details-button:hover,
body.tsar-khotel-design .mphb-book-button:hover,
body.tsar-khotel-design .mphb_sc_search-submit-button-wrapper input[type="submit"]:hover,
body.tsar-khotel-design .mphb-reserve-btn:hover {
	background-color: #292929 !important;
	color: #ffffff !important;
	border-color: #292929 !important;
}

/* Style /reservations/ & Room Reservation Form into a K-Hotel Luxury Card */
body.tsar-khotel-design .mphb_sc_search-wrapper,
body.tsar-khotel-design .mphb-booking-form {
	background: #f8f9ef;
	border: 1px solid #e5e0d8;
	padding: 36px 32px !important;
	max-width: 880px;
	margin: 0 auto 36px !important;
	box-sizing: border-box;
	box-shadow: 0 12px 30px rgba(0, 0, 0, 0.05);
}
body.tsar-khotel-design .mphb_sc_search-form input[type="text"],
body.tsar-khotel-design .mphb_sc_search-form select,
body.tsar-khotel-design .mphb-booking-form input[type="text"],
body.tsar-khotel-design .mphb-booking-form select {
	width: 100% !important;
	max-width: 100% !important;
	height: 46px !important;
	padding: 0 14px !important;
	border: 1px solid #d1d5db !important;
	border-radius: 0 !important;
	background: #ffffff !important;
	font-size: 14px !important;
	box-sizing: border-box !important;
}

/* Fix MotoPress 2-month Availability Calendar horizontal overflow on Android (431px -> 100%) */
body.tsar-khotel-design .mphb-calendar,
body.tsar-khotel-design .datepick-multi,
body.tsar-khotel-design .datepick {
	max-width: 100% !important;
	width: 100% !important;
	box-sizing: border-box !important;
	overflow-x: auto !important;
}
body.tsar-khotel-design .datepick-month {
	max-width: 100% !important;
	box-sizing: border-box !important;
}

@media (max-width: 767px) {
	body.tsar-khotel-design:not(.home) .entry-header {
		padding: 36px 14px 32px !important;
	}
	body.tsar-khotel-design:not(.home) .entry-header .entry-title {
		font-size: 21px !important;
		letter-spacing: 0.08em !important;
		word-break: normal !important;
	}
	body.tsar-khotel-design .mphb_sc_rooms-wrapper.mphb-room-types {
		grid-template-columns: 1fr;
	}
	body.tsar-khotel-design .datepick-month {
		width: 100% !important;
		float: none !important;
	}
	body.tsar-khotel-design .mphb_sc_search-wrapper,
	body.tsar-khotel-design .mphb-booking-form {
		padding: 24px 18px !important;
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
		$res_url   = esc_url( home_url( '/reservations/' ) );
		$acc_url   = esc_url( home_url( '/accommodations/' ) );
		$suite_url = esc_url( home_url( '/accommodation/studio-suite/' ) );
		$std_url   = esc_url( home_url( '/accommodation/standard-room/' ) );
		$u         = esc_url( home_url( '/wp-content/uploads/' ) );

		$khotel_home = '
		<!-- 1. FULL-BLEED K-HOTEL HERO SLIDESHOW -->
		<section class="tsar-kh-hero" aria-label="TSAR HOTEL Hero Slideshow">
			<div class="tsar-kh-hero__slide is-active">
				<img src="' . $u . '2026/08/1000039276-1024x683.png" alt="TSAR HOTEL Yaounde Exterior" />
			</div>
			<div class="tsar-kh-hero__slide">
				<img src="' . $u . '2026/08/file_00000000046c81f48e62fe3ec1fd9d54-1024x683.png" alt="TSAR Snack Lounge Yaounde" />
			</div>
			<div class="tsar-kh-hero__slide">
				<img src="' . $u . '2020/08/slide1-free-img.jpg" alt="TSAR HOTEL Luxury Rooms and Suites" />
			</div>
			<div class="tsar-kh-hero__dots" id="tsar-kh-hero-dots">
				<button type="button" class="tsar-kh-hero__dot is-active" data-hero-idx="0" aria-label="Hero Slide 1"></button>
				<button type="button" class="tsar-kh-hero__dot" data-hero-idx="1" aria-label="Hero Slide 2"></button>
				<button type="button" class="tsar-kh-hero__dot" data-hero-idx="2" aria-label="Hero Slide 3"></button>
			</div>
		</section>

		<!-- 2. K-HOTEL CENTERED EDITORIAL INTRO (#about) -->
		<section id="about" class="tsar-kh-intro" aria-label="About TSAR HOTEL">
			<div class="tsar-kh-intro__subtitle">WORK. PLAY. STAY.</div>
			<h1 class="tsar-kh-intro__title">BE INSPIRED. STAY INSPIRED.</h1>
			<hr class="tsar-kh-intro__hr" />
			<p class="tsar-kh-intro__p">
				Be inspired by TSAR HOTEL&#8217;s luxurious interiors, signature dining and exclusive amenities. Stay inspired by warm Cameroonian hospitality and service excellence &mdash; <strong>Where Africa Meets</strong>.
			</p>
			<p class="tsar-kh-intro__p">
				Located in Nsimeyong, Yaound&eacute;, the hotel is ideally situated for business travellers, diplomats, couples and leisure guests. Featuring 21 well-appointed rooms including 3 spacious Studio Suites, our all-day Restaurant and the vibrant TSAR Snack Lounge, we strive to exceed your every expectation. It&#8217;s more than a place; it&#8217;s an experience.
			</p>
			<p class="tsar-kh-intro__welcome">We look forward to welcoming you.</p>
		</section>

		<!-- 3. K-HOTEL 3-CARD CAROUSEL WITH OVERLAPPING DARK BOX (#amenities) -->
		<section id="amenities" class="tsar-kh-slider-section" aria-label="TSAR HOTEL Highlights">
			<button type="button" class="tsar-kh-sq-arrow tsar-kh-sq-arrow--prev" id="tsar-kh-prev" aria-label="Previous highlight">&#10094;</button>
			<div class="tsar-kh-slider-grid" id="tsar-kh-3grid">
				<div class="tsar-kh-slider-slot tsar-kh-slider-slot--left">
					<div class="tsar-kh-slider-img-wrap">
						<img id="tsar-kh-img-left" src="' . $u . '2026/08/file_00000000046c81f48e62fe3ec1fd9d54-1024x683.png" alt="TSAR Snack Lounge" />
					</div>
				</div>
				<div class="tsar-kh-slider-slot tsar-kh-slider-slot--center">
					<div class="tsar-kh-slider-img-wrap">
						<img id="tsar-kh-img-center" src="' . $u . '2020/08/slide1-free-img.jpg" alt="Premium Accommodation" />
					</div>
					<a class="tsar-kh-center-box" id="tsar-kh-center-link" href="' . $acc_url . '">
						<h3 id="tsar-kh-center-title">PREMIUM ACCOMMODATION</h3>
						<p id="tsar-kh-center-desc">Retreat to the comfort of our 21 well-appointed, stylish en-suite rooms or 3 spacious Studio Suites in Yaound&eacute;.</p>
					</a>
				</div>
				<div class="tsar-kh-slider-slot tsar-kh-slider-slot--right">
					<div class="tsar-kh-slider-img-wrap">
						<img id="tsar-kh-img-right" src="' . $u . '2020/08/hotel-cooking.jpg" alt="Fine Dining Restaurant" />
					</div>
				</div>
			</div>
			<button type="button" class="tsar-kh-sq-arrow tsar-kh-sq-arrow--next" id="tsar-kh-next" aria-label="Next highlight">&#10095;</button>
		</section>

		<!-- 4. K-HOTEL ROOMS & SUITES SHOWCASE (#packages & #gallery) -->
		<section id="packages" class="tsar-kh-rooms-sec" aria-label="Rooms and Suites">
			<div id="gallery" class="tsar-kh-rooms-sec__inner">
				<div class="tsar-kh-sec-head">
					<span>ACCOMMODATION &amp; RATES</span>
					<h2>STAY WITH US</h2>
				</div>
				<div class="tsar-kh-rooms-grid">
					<div class="tsar-kh-room-card">
						<div class="tsar-kh-room-card__img">
							<img src="' . $u . '2020/08/slide1-free-img.jpg" alt="Studio Suite at TSAR HOTEL" loading="lazy" />
							<div class="tsar-kh-room-card__rate">XAF 35,000 / Night</div>
						</div>
						<div class="tsar-kh-room-card__body">
							<div>
								<h3>Studio Suite</h3>
								<p>Three spacious Studio Suites crafted for business travellers, couples and extended stays with dedicated living space.</p>
							</div>
							<a class="tsar-kh-room-card__cta" href="' . $suite_url . '">Book Studio Suite</a>
						</div>
					</div>
					<div class="tsar-kh-room-card">
						<div class="tsar-kh-room-card__img">
							<img src="' . $u . '2020/08/executive-suite-free-img.jpg" alt="Executive Suite at TSAR HOTEL" loading="lazy" />
							<div class="tsar-kh-room-card__rate">XAF 30,000 / Night</div>
						</div>
						<div class="tsar-kh-room-card__body">
							<div>
								<h3>Executive Suite</h3>
								<p>Enjoy modern elegance, air-conditioned comfort, high-speed Wi-Fi and 24/7 room service in our Executive Suite.</p>
							</div>
							<a class="tsar-kh-room-card__cta" href="' . $res_url . '">Book Executive Suite</a>
						</div>
					</div>
					<div class="tsar-kh-room-card">
						<div class="tsar-kh-room-card__img">
							<img src="' . $u . '2020/08/double-room-free-img.jpg" alt="Double Room at TSAR HOTEL" loading="lazy" />
							<div class="tsar-kh-room-card__rate">XAF 25,000 / Night</div>
						</div>
						<div class="tsar-kh-room-card__body">
							<div>
								<h3>Double Room</h3>
								<p>A refined, welcoming room with a comfortable double bed, en-suite bathroom and work desk for one or two guests.</p>
							</div>
							<a class="tsar-kh-room-card__cta" href="' . $std_url . '">Book Double Room</a>
						</div>
					</div>
					<div class="tsar-kh-room-card">
						<div class="tsar-kh-room-card__img">
							<img src="' . $u . '2020/08/hotel-single-room.jpg" alt="Single Room at TSAR HOTEL" loading="lazy" />
							<div class="tsar-kh-room-card__rate">XAF 20,000 / Night</div>
						</div>
						<div class="tsar-kh-room-card__body">
							<div>
								<h3>Single Room</h3>
								<p>Cozy, quiet and thoughtfully equipped for solo travellers seeking quality accommodation in Yaound&eacute;.</p>
							</div>
							<a class="tsar-kh-room-card__cta" href="' . $res_url . '">Book Single Room</a>
						</div>
					</div>
				</div>
			</div>
		</section>

		<!-- 5. K-HOTEL DISCOVER YAOUNDE STAGGERED SECTION -->
		<section class="tsar-kh-discover" aria-label="Discover Yaounde">
			<div class="tsar-kh-discover__inner">
				<div class="tsar-kh-discover__col">
					<div class="tsar-kh-fbox">
						<h3>HEART OF NSIMEYONG</h3>
						<p>Ideally located in Nsimeyong, Yaound&eacute;, offering peaceful comfort with convenient access to corporate offices, embassies and city life.</p>
					</div>
					<img class="tsar-kh-discover__img" src="' . $u . '2026/08/1000039276-1024x683.png" alt="TSAR HOTEL Building in Yaounde" loading="lazy" />
				</div>
				<div class="tsar-kh-discover__col">
					<img class="tsar-kh-discover__img" src="' . $u . '2026/08/file_00000000046c81f48e62fe3ec1fd9d54-1024x683.png" alt="TSAR Snack Lounge Yaounde" loading="lazy" />
					<div class="tsar-kh-fbox tsar-kh-fbox--white">
						<h3>WHERE AFRICA MEETS</h3>
						<p>21 rooms including 3 Studio Suites, our all-day Restaurant and the vibrant TSAR Snack Lounge.</p>
					</div>
				</div>
				<div class="tsar-kh-discover__editorial">
					<h2>DISCOVER YAOUND&Eacute;</h2>
					<p>Known as the Capital of Seven Hills, Yaound&eacute; is Cameroon&#8217;s political and diplomatic heart, blending lush hillside vistas, cultural monuments and vibrant nightlife.</p>
					<p>From business meetings and diplomatic visits to weekend dining at TSAR Snack Lounge, TSAR HOTEL places authentic Cameroonian warmth and modern luxury at the centre of your stay.</p>
					<p>Our 24/7 reception team is always ready to assist with room reservations, airport or city car rental, and dining arrangements.</p>
					<a class="tsar-kh-discover__btn" href="' . $res_url . '">Book Your Stay</a>
				</div>
			</div>
		</section>';

		return $khotel_home;
	}

	public function render_khotel_footer_js() {
		$options = $this->options();
		if ( ! $this->frontend_request() || empty( $options['khotel_design'] ) ) {
			return;
		}
		$home_url  = esc_url( home_url( '/' ) );
		$res_url   = esc_url( home_url( '/reservations/' ) );
		$acc_url   = esc_url( home_url( '/accommodations/' ) );
		$suite_url = esc_url( home_url( '/accommodation/studio-suite/' ) );
		$std_url   = esc_url( home_url( '/accommodation/standard-room/' ) );
		$u         = esc_url( home_url( '/wp-content/uploads/' ) );
		?>
		<button type="button" id="tsar-scroll-top" aria-label="Scroll to top">&#8593;</button>
		<script>
		(function(){
			var homeUrl  = <?php echo wp_json_encode( $home_url ); ?>;
			var resUrl   = <?php echo wp_json_encode( $res_url ); ?>;
			var accUrl   = <?php echo wp_json_encode( $acc_url ); ?>;
			var suiteUrl = <?php echo wp_json_encode( $suite_url ); ?>;
			var stdUrl   = <?php echo wp_json_encode( $std_url ); ?>;
			var upUrl    = <?php echo wp_json_encode( $u ); ?>;

			/* 1. Inject 1:1 K-Hotel Single-Row Header + Slide-out Mega Drawer at top of body */
			if (!document.getElementById('tsar-kh-header')) {
				var headerHtml = '' +
				'<header id="tsar-kh-header">' +
					'<div class="tsar-kh-header__inner">' +
						'<a class="tsar-kh-logo" href="' + homeUrl + '">' +
							'<img src="' + upUrl + '2026/08/1000035937-200x133.png" alt="TSAR HOTEL Logo" />' +
							'<span class="tsar-kh-logo__text"><strong>TSAR</strong> HOTEL</span>' +
						'</a>' +
						'<div class="tsar-kh-nav-wrap">' +
							'<ul class="tsar-kh-nav">' +
								'<li><a href="' + homeUrl + '#packages">Stay</a></li>' +
								'<li><a href="' + homeUrl + '#amenities">Dine</a></li>' +
								'<li><a href="' + homeUrl + '#amenities">Lounge</a></li>' +
								'<li><a href="' + homeUrl + '#about">About</a></li>' +
								'<li><a href="#tsar-kh-footer">Contact</a></li>' +
							'</ul>' +
							'<a class="tsar-kh-book-btn" href="' + resUrl + '">Book</a>' +
							'<button type="button" class="tsar-kh-burger" id="tsar-kh-burger-btn" aria-label="Open Menu">' +
								'<span></span><span></span><span></span>' +
							'</button>' +
						'</div>' +
					'</div>' +
				'</header>' +
				'<aside id="tsar-kh-drawer" aria-label="Full Menu">' +
					'<button type="button" class="tsar-kh-drawer__close" id="tsar-kh-drawer-close" aria-label="Close Menu">&times;</button>' +
					'<div class="tsar-kh-drawer__section">' +
						'<a class="tsar-kh-drawer__heading" href="' + resUrl + '">Book Stay</a>' +
					'</div>' +
					'<div class="tsar-kh-drawer__section">' +
						'<a class="tsar-kh-drawer__heading" href="' + accUrl + '">Stay (21 Rooms &amp; Suites)</a>' +
						'<ul class="tsar-kh-drawer__list">' +
							'<li><a href="' + suiteUrl + '">Studio Suites (XAF 35,000)</a></li>' +
							'<li><a href="' + resUrl + '">Executive Suite (XAF 30,000)</a></li>' +
							'<li><a href="' + stdUrl + '">Double Room (XAF 25,000)</a></li>' +
							'<li><a href="' + resUrl + '">Single Room (XAF 20,000)</a></li>' +
						'</ul>' +
					'</div>' +
					'<div class="tsar-kh-drawer__section">' +
						'<a class="tsar-kh-drawer__heading" href="' + homeUrl + '#amenities">Dine &amp; Lounge</a>' +
						'<ul class="tsar-kh-drawer__list">' +
							'<li><a href="' + homeUrl + '#amenities">TSAR Restaurant</a></li>' +
							'<li><a href="' + homeUrl + '#amenities">TSAR Snack Lounge</a></li>' +
						'</ul>' +
					'</div>' +
					'<div class="tsar-kh-drawer__section">' +
						'<a class="tsar-kh-drawer__heading" href="#tsar-kh-footer">Contact Us</a>' +
						'<ul class="tsar-kh-drawer__list">' +
							'<li><a href="tel:+237683628079">+237 6 83 62 80 79</a></li>' +
							'<li><a href="mailto:info@tsarhotel.com">info@tsarhotel.com</a></li>' +
						'</ul>' +
					'</div>' +
				'</aside>';
				document.body.insertAdjacentHTML('afterbegin', headerHtml);

				var burger = document.getElementById('tsar-kh-burger-btn');
				var drawer = document.getElementById('tsar-kh-drawer');
				var closeB = document.getElementById('tsar-kh-drawer-close');
				if (burger && drawer) {
					burger.addEventListener('click', function(){ drawer.classList.add('is-open'); });
				}
				if (closeB && drawer) {
					closeB.addEventListener('click', function(){ drawer.classList.remove('is-open'); });
				}
				if (drawer) {
					var dLinks = drawer.querySelectorAll('a');
					for (var d = 0; d < dLinks.length; d++) {
						dLinks[d].addEventListener('click', function(){ drawer.classList.remove('is-open'); });
					}
				}
			}

			/* 2. Inject 1:1 K-Hotel 4-Column Architectural Footer */
			if (!document.getElementById('tsar-kh-footer')) {
				var footerHtml = '' +
				'<footer id="tsar-kh-footer">' +
					'<div class="tsar-kh-footer__grid">' +
						'<div class="tsar-kh-footer__about">' +
							'<p>Be inspired by TSAR HOTEL’s luxurious interiors, signature dining and exclusive amenities. Stay inspired by warm Cameroonian hospitality and service excellence.</p>' +
							'<div class="tsar-kh-footer__badge">★ TSAR HOTEL — WHERE AFRICA MEETS</div>' +
						'</div>' +
						'<div>' +
							'<ul class="tsar-kh-footer__links">' +
								'<li><a href="' + accUrl + '">+ Accommodation</a></li>' +
								'<li><a href="' + suiteUrl + '">+ Studio Suites</a></li>' +
								'<li><a href="' + stdUrl + '">+ Standard &amp; Double Rooms</a></li>' +
								'<li><a href="' + homeUrl + '#amenities">+ TSAR Restaurant</a></li>' +
								'<li><a href="' + homeUrl + '#amenities">+ TSAR Snack Lounge</a></li>' +
							'</ul>' +
						'</div>' +
						'<div>' +
							'<ul class="tsar-kh-footer__links">' +
								'<li><a href="' + homeUrl + '#about">+ About TSAR Hotel</a></li>' +
								'<li><a href="' + homeUrl + '#packages">+ Rooms &amp; Rates</a></li>' +
								'<li><a href="' + resUrl + '">+ Online Reservations</a></li>' +
								'<li><a href="#tsar-kh-footer">+ Contact Us</a></li>' +
							'</ul>' +
						'</div>' +
						'<div>' +
							'<div class="tsar-kh-footer__contact-item">' +
								'<div class="tsar-kh-footer__icon">☎</div>' +
								'<div><small>Phone number</small><a href="tel:+237683628079">+237 6 83 62 80 79</a></div>' +
							'</div>' +
							'<div class="tsar-kh-footer__contact-item">' +
								'<div class="tsar-kh-footer__icon">✉</div>' +
								'<div><small>Email</small><a href="mailto:info@tsarhotel.com">info@tsarhotel.com</a></div>' +
							'</div>' +
							'<div class="tsar-kh-footer__contact-item" style="border-bottom:none;">' +
								'<div class="tsar-kh-footer__icon">⌂</div>' +
								'<div><small>TSAR Hotel Address</small><span>Nsimeyong, Yaoundé, Cameroon</span></div>' +
							'</div>' +
							'<a class="tsar-kh-footer__map-btn" href="https://www.google.com/maps/search/?api=1&query=TSAR+HOTEL+Nsimeyong+Yaounde" target="_blank" rel="noopener">View Google Map</a>' +
						'</div>' +
					'</div>' +
					'<div class="tsar-kh-footer__bar">' +
						'<span>Luxury Accommodation | © TSAR HOTEL Yaoundé - 2026</span>' +
						'<span>Where Africa Meets</span>' +
					'</div>' +
				'</footer>';
				document.body.insertAdjacentHTML('beforeend', footerHtml);
			}

			/* 3. Hero Banner Autoplay Crossfade Slideshow */
			var heroSlides = document.querySelectorAll('.tsar-kh-hero__slide');
			var heroDots   = document.querySelectorAll('.tsar-kh-hero__dot');
			if (heroSlides.length > 1) {
				var hIdx = 0;
				function showHero(n) {
					hIdx = (n + heroSlides.length) % heroSlides.length;
					for (var i = 0; i < heroSlides.length; i++) {
						heroSlides[i].classList.toggle('is-active', i === hIdx);
					}
					for (var j = 0; j < heroDots.length; j++) {
						heroDots[j].classList.toggle('is-active', j === hIdx);
					}
				}
				for (var d = 0; d < heroDots.length; d++) {
					(function(k){
						heroDots[k].addEventListener('click', function(){ showHero(k); });
					})(d);
				}
				setInterval(function(){ showHero(hIdx + 1); }, 5000);
			}

			/* 4. K-Hotel 3-Card Interactive Slider with Overlapping Dark Box */
			var items = [
				{
					img: upUrl + '2026/08/file_00000000046c81f48e62fe3ec1fd9d54-1024x683.png',
					title: 'TSAR SNACK LOUNGE',
					desc: 'Unwind with signature drinks, platters and warm evening ambience at TSAR Snack Lounge.',
					url: homeUrl + '#amenities'
				},
				{
					img: upUrl + '2020/08/slide1-free-img.jpg',
					title: 'PREMIUM ACCOMMODATION',
					desc: 'Retreat to the comfort of our 21 well-appointed, stylish en-suite rooms or 3 spacious Studio Suites in Yaoundé.',
					url: accUrl
				},
				{
					img: upUrl + '2020/08/hotel-cooking.jpg',
					title: 'FINE DINING RESTAURANT',
					desc: 'Indulge in freshly prepared Cameroonian and international cuisine from breakfast through dinner.',
					url: homeUrl + '#amenities'
				},
				{
					img: upUrl + '2026/08/1000039276-1024x683.png',
					title: 'STUDIO SUITES',
					desc: 'Three spacious Studio Suites crafted for business travellers, couples and extended stays — XAF 35,000 / Night.',
					url: suiteUrl
				},
				{
					img: upUrl + '2020/08/executive-suite-free-img.jpg',
					title: 'SEAMLESS RESERVATIONS',
					desc: '24/7 reception, room service, high-speed Wi-Fi and direct online booking for your stay in Yaoundé.',
					url: resUrl
				}
			];
			var imgLeft   = document.getElementById('tsar-kh-img-left');
			var imgCenter = document.getElementById('tsar-kh-img-center');
			var imgRight  = document.getElementById('tsar-kh-img-right');
			var cTitle    = document.getElementById('tsar-kh-center-title');
			var cDesc     = document.getElementById('tsar-kh-center-desc');
			var cLink     = document.getElementById('tsar-kh-center-link');
			var prevBtn   = document.getElementById('tsar-kh-prev');
			var nextBtn   = document.getElementById('tsar-kh-next');

			if (imgCenter && cTitle && cDesc) {
				var cIdx = 1;
				var cTimer = null;
				function renderCarousel(idx) {
					cIdx = (idx + items.length) % items.length;
					var leftIdx  = (cIdx - 1 + items.length) % items.length;
					var rightIdx = (cIdx + 1) % items.length;
					if (imgLeft)   { imgLeft.src   = items[leftIdx].img;  imgLeft.alt   = items[leftIdx].title; }
					if (imgCenter) { imgCenter.src = items[cIdx].img;     imgCenter.alt = items[cIdx].title; }
					if (imgRight)  { imgRight.src  = items[rightIdx].img; imgRight.alt  = items[rightIdx].title; }
					cTitle.textContent = items[cIdx].title;
					cDesc.textContent  = items[cIdx].desc;
					if (cLink) cLink.href = items[cIdx].url;
				}
				function restartCarouselTimer() {
					if (cTimer) clearInterval(cTimer);
					cTimer = setInterval(function(){ renderCarousel(cIdx + 1); }, 4200);
				}
				if (prevBtn) prevBtn.addEventListener('click', function(){ renderCarousel(cIdx - 1); restartCarouselTimer(); });
				if (nextBtn) nextBtn.addEventListener('click', function(){ renderCarousel(cIdx + 1); restartCarouselTimer(); });

				var gridEl = document.getElementById('tsar-kh-3grid');
				if (gridEl) {
					var sx = 0;
					gridEl.addEventListener('touchstart', function(e){ sx = e.touches[0].clientX; }, {passive: true});
					gridEl.addEventListener('touchend', function(e){
						var dx = sx - e.changedTouches[0].clientX;
						if (Math.abs(dx) > 40) {
							renderCarousel(dx > 0 ? cIdx + 1 : cIdx - 1);
							restartCarouselTimer();
						}
					}, {passive: true});
				}
				renderCarousel(1);
				restartCarouselTimer();
			}

						/* 6. Enhance Inner Pages (/accommodations/, /accommodation/*, /reservations/) with K-Hotel Room Photos */
			var roomCards = document.querySelectorAll('.mphb_sc_rooms-wrapper .mphb-room-type');
			for (var r = 0; r < roomCards.length; r++) {
				var rc = roomCards[r];
				if (!rc.querySelector('.tsar-kh-injected-room-banner')) {
					var titleEl = rc.querySelector('.mphb-room-type-title');
					var tText = titleEl ? (titleEl.textContent || '').toLowerCase() : '';
					var rImg = tText.indexOf('studio') !== -1 ? (upUrl + '2020/08/slide1-free-img.jpg') : (upUrl + '2020/08/double-room-free-img.jpg');
					var banner = document.createElement('div');
					banner.className = 'tsar-kh-injected-room-banner';
					banner.innerHTML = '<img src="' + rImg + '" alt="TSAR HOTEL Room" />';
					rc.insertBefore(banner, rc.firstChild);
				}
			}
			var singleRoom = document.querySelector('body.single-mphb_room_type .entry-content');
			if (singleRoom && !singleRoom.querySelector('.tsar-kh-injected-room-banner')) {
				var pageTitle = document.querySelector('.entry-header .entry-title');
				var pText = pageTitle ? (pageTitle.textContent || '').toLowerCase() : '';
				var sImg = pText.indexOf('studio') !== -1 ? (upUrl + '2020/08/slide1-free-img.jpg') : (upUrl + '2020/08/executive-suite-free-img.jpg');
				var sBanner = document.createElement('div');
				sBanner.className = 'tsar-kh-injected-room-banner';
				sBanner.style.height = '380px';
				sBanner.innerHTML = '<img src="' + sImg + '" alt="TSAR HOTEL Accommodation" />';
				singleRoom.insertBefore(sBanner, singleRoom.firstChild);
			}

			/* 5. Scroll Header Shrink + Scroll-to-Top button */
			var scrollBtn = document.getElementById('tsar-scroll-top');
			window.addEventListener('scroll', function(){
				if (window.scrollY > 30) {
					document.body.classList.add('tsar-kh-scrolled');
				} else {
					document.body.classList.remove('tsar-kh-scrolled');
				}
				if (scrollBtn) {
					scrollBtn.classList.toggle('is-shown', window.scrollY > 260);
				}
			}, {passive: true});
			if (scrollBtn) {
				scrollBtn.addEventListener('click', function(){
					window.scrollTo({top: 0, behavior: 'smooth'});
				});
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
