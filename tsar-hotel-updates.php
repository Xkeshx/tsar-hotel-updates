<?php
/**
 * Plugin Name: TSAR HOTEL Website Updates
 * Description: Reversible navigation repairs, empty social-link cleanup, configurable enquiry panels, and optional homepage SEO for TSAR HOTEL.
 * Version: 1.0.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: TSAR HOTEL project
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI: false
 * Text Domain: tsar-hotel-updates
 */

namespace TSARHotel\WebsiteUpdates;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Plugin {
	const VERSION = '1.0.0';
	const OPTION = 'tsar_hotel_updates_settings';
	const GROUP = 'tsar_hotel_updates_group';
	const PAGE = 'tsar-hotel-updates';

	private $panel_added = false;
	private $excluded_ids = null;

	public static function defaults() {
		return array(
			'menu_repair' => 1,
			'hide_empty_socials' => 1,
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
		add_filter( 'the_content', array( $this, 'add_contact_panel' ), 25 );
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
		if ( ! empty( $this->options()['hide_empty_socials'] ) ) {
			$classes[] = 'tsar-updates-clean-socials';
		}
		return $classes;
	}

	public function enqueue_styles() {
		$options = $this->options();
		if ( ! empty( $options['hide_empty_socials'] ) || ! empty( $options['contact_panel'] ) || is_singular() ) {
			wp_enqueue_style( 'tsar-hotel-updates', plugins_url( 'assets/front.css', __FILE__ ), array(), self::VERSION );
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
		foreach ( array( 'menu_repair', 'hide_empty_socials', 'contact_panel', 'home_seo', 'demo_noindex', 'brand_heading' ) as $key ) {
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
