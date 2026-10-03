<?php
/**
 * Optional, reversible TSAR HOTEL homepage experience.
 *
 * @package TSARHotel\WebsiteUpdates
 */

namespace TSARHotel\WebsiteUpdates;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders a TSAR-branded home experience without changing saved page content.
 *
 * The redesign is explicitly opt-in. It uses existing page imagery, the
 * hotel's published room-category names, the saved enquiry form when available,
 * and published offer/event entries managed in WordPress.
 */
final class Homepage_Redesign {
	const OPTION = 'tsar_hotel_updates_settings';
	const VERSION = '1.3.1';

	private $header_rendered = false;

	public function __construct() {
		add_action( 'init', array( $this, 'register_content_types' ) );
		add_action( 'wp_body_open', array( $this, 'render_site_header' ), 5 );
		add_action( 'wp_footer', array( $this, 'render_floating_booking' ), 5 );
		add_filter( 'body_class', array( $this, 'body_classes' ) );
		add_filter( 'the_content', array( $this, 'replace_homepage_content' ), 99 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ), 30 );
	}

	private function options() {
		$options = get_option( self::OPTION, array() );
		return wp_parse_args( is_array( $options ) ? $options : array(), Plugin::defaults() );
	}

	private function enabled() {
		return ! is_admin() && ! wp_doing_ajax() && ! ( defined( 'REST_REQUEST' ) && REST_REQUEST ) && ! empty( $this->options()['redesign_enabled'] );
	}

	private function language() {
		return 'en';
	}

	private function t( $english ) {
		return $english;
	}

	private function is_front_content() {
		return $this->enabled() && is_front_page() && in_the_loop() && is_main_query() && ! post_password_required();
	}

	public function register_content_types() {
		$shared = array(
			'public'              => true,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_in_rest'        => true,
			'has_archive'         => false,
			'rewrite'             => false,
			'show_in_nav_menus'   => false,
			'menu_position'       => 22,
			'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ),
		);

		register_post_type(
			'tsar_hotel_offer',
			array_merge(
				$shared,
				array(
					'labels'     => array(
						'name'                  => 'Special Offers',
						'singular_name'         => 'Special Offer',
						'menu_name'             => 'Special Offers',
						'add_new_item'          => 'Add Special Offer',
						'edit_item'             => 'Edit Special Offer',
						'new_item'              => 'New Special Offer',
						'view_item'             => 'View Special Offer',
						'search_items'          => 'Search Special Offers',
						'not_found'             => 'No special offers found',
						'not_found_in_trash'    => 'No special offers found in Trash',
						'all_items'             => 'All Special Offers',
						'featured_image'        => 'Offer image',
						'set_featured_image'    => 'Set offer image',
						'remove_featured_image' => 'Remove offer image',
					),
					'menu_icon'  => 'dashicons-tag',
				)
			)
		);

		register_post_type(
			'tsar_hotel_event',
			array_merge(
				$shared,
				array(
					'labels'     => array(
						'name'                  => 'Events',
					'singular_name'         => 'Event',
					'menu_name'             => 'Events',
					'add_new_item'          => 'Add Event',
					'edit_item'             => 'Edit Event',
					'new_item'              => 'New Event',
					'view_item'             => 'View Event',
					'search_items'          => 'Search Events',
					'not_found'             => 'No events found',
					'not_found_in_trash'    => 'No events found in Trash',
					'all_items'             => 'All Events',
					'featured_image'        => 'Event image',
					'set_featured_image'    => 'Set event image',
					'remove_featured_image' => 'Remove event image',
				),
					'menu_icon'  => 'dashicons-calendar-alt',
				)
			)
		);
	}

	public function body_classes( $classes ) {
		if ( $this->enabled() ) {
			$classes[] = 'tsar-redesign-active';
		}
		return $classes;
	}

	public function enqueue_assets() {
		if ( ! $this->enabled() ) {
			return;
		}
		$css_path = dirname( __DIR__ ) . '/assets/redesign.css';
		$js_path  = dirname( __DIR__ ) . '/assets/redesign.js';
		$css_ver  = is_file( $css_path ) ? self::VERSION . '.' . filemtime( $css_path ) : self::VERSION;
		$js_ver   = is_file( $js_path ) ? self::VERSION . '.' . filemtime( $js_path ) : self::VERSION;
		$plugin_file = dirname( __DIR__ ) . '/tsar-hotel-updates.php';
		wp_enqueue_style( 'tsar-hotel-redesign', plugins_url( 'assets/redesign.css', $plugin_file ), array(), $css_ver );
		wp_enqueue_script( 'tsar-hotel-redesign', plugins_url( 'assets/redesign.js', $plugin_file ), array(), $js_ver, true );
	}

	public function render_site_header() {
		if ( ! $this->enabled() || $this->header_rendered ) {
			return;
		}
		$this->header_rendered = true;
		$links        = $this->navigation_links();
		$booking_url  = $this->page_url( 'reservations', '/reservations/' );
		$logo         = function_exists( 'get_custom_logo' ) ? get_custom_logo() : '';
		$brand        = $logo ? $logo : '<a class="tsar-redesign__brand-name" href="' . esc_url( $this->home_url() ) . '" rel="home"><span>TSAR</span><small>HOTEL</small></a>';

		echo '<a class="tsar-redesign__skip" href="#tsar-main-content">' . esc_html( $this->t( 'Skip to content') ) . '</a>';
		echo '<header class="tsar-redesign__header" id="tsar-site-header">';
		echo '<div class="tsar-redesign__header-inner">';
		echo '<div class="tsar-redesign__brand">' . $brand . '</div>';
		echo '<nav class="tsar-redesign__desktop-nav" aria-label="' . esc_attr( $this->t( 'Main navigation') ) . '"><ul>';
		foreach ( $links as $link ) {
			echo '<li><a href="' . esc_url( $link['url'] ) . '">' . esc_html( $link['label'] ) . '</a></li>';
		}
		echo '</ul></nav>';
		echo '<div class="tsar-redesign__header-actions">';
		echo '<a class="tsar-redesign__book-link" href="' . esc_url( $booking_url ) . '">' . esc_html( $this->t( 'Book your stay') ) . '</a>';
		echo '</div>';
		echo '<button class="tsar-redesign__menu-toggle" type="button" aria-expanded="false" aria-controls="tsar-mobile-nav" aria-label="' . esc_attr( $this->t( 'Open menu') ) . '" data-label-open="' . esc_attr( $this->t( 'Open menu') ) . '" data-label-close="' . esc_attr( $this->t( 'Close menu') ) . '"><span></span><span></span><span></span><span></span></button>';
		echo '</div>';
		echo '<nav class="tsar-redesign__mobile-nav" id="tsar-mobile-nav" aria-label="' . esc_attr( $this->t( 'Mobile navigation') ) . '" hidden><ul>';
		foreach ( $links as $link ) {
			echo '<li><a href="' . esc_url( $link['url'] ) . '">' . esc_html( $link['label'] ) . '</a></li>';
		}
			echo '</ul><div class="tsar-redesign__mobile-actions"><a class="tsar-redesign__book-link" href="' . esc_url( $booking_url ) . '">' . esc_html( $this->t( 'Book your stay' ) ) . '</a></div></nav>';
		echo '</header>';
	}

	private function navigation_links() {
		return array(
			array( 'label' => $this->t( 'Rooms & Suites'), 'url' => $this->page_url( 'accommodations', '/accommodations/' ) ),
			array( 'label' => $this->t( 'Restaurant'), 'url' => $this->anchor_url( 'restaurant' ) ),
			array( 'label' => $this->t( 'Snack Lounge'), 'url' => $this->anchor_url( 'snack-lounge' ) ),
			array( 'label' => $this->t( 'Events'), 'url' => $this->anchor_url( 'events' ) ),
			array( 'label' => $this->t( 'Gallery'), 'url' => $this->anchor_url( 'gallery' ) ),
			array( 'label' => $this->t( 'Contact'), 'url' => $this->anchor_url( 'contact' ) ),
		);
	}

	private function home_url() {
		$page_id = (int) get_option( 'page_on_front' );
		return $page_id ? get_permalink( $page_id ) : home_url( '/' );
	}

	private function page_url( $slug, $fallback ) {
		$page = get_page_by_path( $slug, OBJECT, 'page' );
		return $page ? get_permalink( $page ) : home_url( $fallback );
	}

	private function anchor_url( $anchor ) {
		return trailingslashit( $this->home_url() ) . '#' . rawurlencode( $anchor );
	}

	public function replace_homepage_content( $content ) {
		if ( ! $this->is_front_content() ) {
			return $content;
		}
		$images = $this->extract_images( $content );
		$form   = $this->extract_sureforms_form( $content );
		return $this->render_homepage( $images, $form );
	}

	private function extract_images( $content ) {
		$images = array();
		if ( ! is_string( $content ) || ! preg_match_all( '/<img\b[^>]*>/i', $content, $matches ) ) {
			$page_id = (int) get_queried_object_id();
			$featured = $page_id ? get_the_post_thumbnail_url( $page_id, 'full' ) : '';
			if ( $featured ) {
				$images[] = array( 'src' => $featured, 'alt' => '', 'id' => (int) get_post_thumbnail_id( $page_id ) );
			}
			return $images;
		}
		$site_host = strtolower( (string) wp_parse_url( home_url( '/' ), PHP_URL_HOST ) );
		foreach ( $matches[0] as $tag ) {
			if ( ! preg_match( '/\bsrc\s*=\s*(["\'])(.*?)\1/i', $tag, $src_match ) ) {
				continue;
			}
			$src = esc_url_raw( html_entity_decode( $src_match[2], ENT_QUOTES, 'UTF-8' ) );
			if ( '' === $src || ! preg_match( '/\.(?:jpe?g|png|webp|avif)(?:[?#].*)?$/i', $src ) ) {
				continue;
			}
			$host = strtolower( (string) wp_parse_url( $src, PHP_URL_HOST ) );
			if ( $site_host && $host && $site_host !== $host && ! preg_match( '/\.' . preg_quote( $site_host, '/' ) . '$/i', $host ) ) {
				continue;
			}
			if ( preg_match( '/\balt\s*=\s*(["\'])(.*?)\1/i', $tag, $alt_match ) ) {
				$alt = sanitize_text_field( html_entity_decode( $alt_match[2], ENT_QUOTES, 'UTF-8' ) );
			} else {
				$alt = '';
			}
			$id = 0;
			if ( preg_match( '/\bwp-image-(\d+)\b/', $tag, $id_match ) ) {
				$id = (int) $id_match[1];
			}
			$duplicate = false;
			foreach ( $images as $image ) {
				if ( $image['src'] === $src || ( $id && ! empty( $image['id'] ) && (int) $image['id'] === $id ) ) {
					$duplicate = true;
					break;
				}
			}
			if ( ! $duplicate ) {
				$images[] = array( 'src' => $src, 'alt' => $alt, 'id' => $id );
			}
			if ( count( $images ) >= 12 ) {
				break;
			}
		}
		if ( ! $images ) {
			$page_id = (int) get_queried_object_id();
			$featured = $page_id ? get_the_post_thumbnail_url( $page_id, 'full' ) : '';
			if ( $featured ) {
				$images[] = array( 'src' => $featured, 'alt' => '', 'id' => (int) get_post_thumbnail_id( $page_id ) );
			}
		}
		return $images;
	}

	private function image_html( $image, $class, $priority = false ) {
		if ( ! is_array( $image ) || empty( $image['src'] ) ) {
			return '';
		}
		$attributes = array(
			'class'    => $class,
			'alt'      => isset( $image['alt'] ) ? $image['alt'] : '',
			'loading'  => $priority ? 'eager' : 'lazy',
			'decoding' => 'async',
		);
		if ( $priority ) {
			$attributes['fetchpriority'] = 'high';
		}
		if ( ! empty( $image['id'] ) && wp_attachment_is_image( $image['id'] ) ) {
			return wp_get_attachment_image( $image['id'], 'large', false, $attributes );
		}
		return '<img src="' . esc_url( $image['src'] ) . '" class="' . esc_attr( $class ) . '" alt="' . esc_attr( isset( $image['alt'] ) ? $image['alt'] : '' ) . '" loading="' . esc_attr( $priority ? 'eager' : 'lazy' ) . '" decoding="async"' . ( $priority ? ' fetchpriority="high"' : '' ) . '>';
	}

	private function extract_sureforms_form( $content ) {
		if ( ! is_string( $content ) || false === stripos( $content, 'srfm-form-container' ) || ! class_exists( 'DOMDocument' ) ) {
			return '';
		}
		$previous = libxml_use_internal_errors( true );
		$document = new \DOMDocument( '1.0', 'UTF-8' );
		$loaded   = $document->loadHTML( '<?xml encoding="utf-8" ?><!DOCTYPE html><html><body><div id="tsar-redesign-content-root">' . $content . '</div></body></html>', LIBXML_NONET | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
		$form     = '';
		if ( $loaded ) {
			$xpath = new \DOMXPath( $document );
			$nodes = $xpath->query( '//*[contains(@class, "srfm-form-container-")]' );
			if ( $nodes && $nodes->length ) {
				$form = $document->saveHTML( $nodes->item( 0 ) );
			} else {
				$nodes = $xpath->query( '//*[@id="srfm-form-1107"]' );
				if ( $nodes && $nodes->length ) {
					$form = $document->saveHTML( $nodes->item( 0 ) );
				}
			}
		}
		libxml_clear_errors();
		libxml_use_internal_errors( $previous );
		return is_string( $form ) ? $form : '';
	}

	private function room_cards() {
		$room_types = get_posts(
			array(
				'post_type'              => 'mphb_room_type',
				'post_status'            => 'publish',
				'posts_per_page'         => -1,
				'orderby'                => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
				'lang'                   => 'en',
				'suppress_filters'       => false,
				'no_found_rows'          => true,
				'update_post_meta_cache' => true,
				'update_post_term_cache' => false,
			)
		);
		if ( ! $room_types ) {
			return '<p class="tsar-redesign__empty-state">' . esc_html( $this->t( 'No published room types are available in the MotoPress catalogue yet.' ) ) . ' <a class="tsar-redesign__text-link" href="' . esc_url( $this->page_url( 'accommodations', '/accommodations/' ) ) . '">' . esc_html( $this->t( 'View accommodations' ) ) . '</a></p>';
		}
		$output = '';
		foreach ( $room_types as $index => $room_type ) {
			$title   = get_the_title( $room_type );
			$excerpt = get_the_excerpt( $room_type );
			if ( '' === trim( (string) $excerpt ) && ! empty( $room_type->post_content ) ) {
				$excerpt = wp_trim_words( wp_strip_all_tags( strip_shortcodes( $room_type->post_content ) ), 24 );
			}
			$image = get_the_post_thumbnail( $room_type, 'large', array( 'class' => 'tsar-redesign__room-image', 'loading' => 'lazy', 'decoding' => 'async' ) );
			$url   = get_permalink( $room_type );
			$output .= '<article class="tsar-redesign__room-card">';
			if ( $image ) {
				$output .= '<div class="tsar-redesign__room-photo">' . $image . '</div>';
			}
			$output .= '<span class="tsar-redesign__room-number">' . esc_html( sprintf( '%02d', $index + 1 ) ) . '</span><h3>' . esc_html( $title ) . '</h3>';
			if ( $excerpt ) {
				$output .= '<p>' . esc_html( wp_trim_words( wp_strip_all_tags( $excerpt ), 24 ) ) . '</p>';
			}
			$output .= '<a class="tsar-redesign__text-link" href="' . esc_url( $url ) . '">' . esc_html( $this->t( 'View room details' ) ) . '<span aria-hidden="true"> ↗</span></a></article>';
		}
		return $output;
	}

	private function published_cards( $post_type, $limit, $button_label, $button_url ) {
		$posts = get_posts(
			array(
				'post_type'              => $post_type,
				'post_status'            => 'publish',
				'posts_per_page'         => $limit,
				'orderby'                => 'date',
				'order'                  => 'DESC',
				'lang'                   => 'en',
				'suppress_filters'       => false,
				'no_found_rows'          => true,
				'update_post_meta_cache' => true,
				'update_post_term_cache' => false,
			)
		);
		if ( ! $posts ) {
			return '';
		}
		$html = '';
		foreach ( $posts as $post ) {
			$title   = get_the_title( $post );
			$excerpt = get_the_excerpt( $post );
			$image   = get_the_post_thumbnail( $post, 'large', array( 'class' => 'tsar-redesign__card-image', 'loading' => 'lazy', 'decoding' => 'async' ) );
			$html   .= '<article class="tsar-redesign__editorial-card">';
			if ( $image ) {
				$html .= '<div class="tsar-redesign__editorial-image">' . $image . '</div>';
			}
			$html .= '<div class="tsar-redesign__editorial-copy"><h3>' . esc_html( $title ) . '</h3>';
			if ( $excerpt ) {
				$html .= '<p>' . esc_html( wp_trim_words( wp_strip_all_tags( $excerpt ), 30 ) ) . '</p>';
			}
			$html .= '<a class="tsar-redesign__text-link" href="' . esc_url( $button_url ) . '">' . esc_html( $button_label ) . '<span aria-hidden="true"> ↗</span></a></div></article>';
		}
		return $html;
	}

	private function render_homepage( $images, $contact_form ) {
		$options     = $this->options();
		$home_url    = $this->home_url();
		$booking_url = $this->page_url( 'reservations', '/reservations/' );
		$review_url  = isset( $options['google_reviews_url'] ) ? Plugin::https_url( $options['google_reviews_url'] ) : '';
		$hero_image  = isset( $images[0] ) ? $images[0] : array();
		$story_image = isset( $images[1] ) ? $images[1] : array();
		$gallery     = array_slice( $images, 2, 6 );
		$offers      = $this->published_cards( 'tsar_hotel_offer', 3, $this->t( 'Book this offer'), $booking_url );
		$events      = $this->published_cards( 'tsar_hotel_event', 3, $this->t( 'Enquire about this event'), $this->anchor_url( 'contact' ) );
		$phone       = Plugin::normalise_phone( isset( $options['phone'] ) ? $options['phone'] : '' );
		$email       = isset( $options['email'] ) && is_email( $options['email'] ) ? $options['email'] : '';

		$html  = '<div id="tsar-main-content" class="tsar-redesign-home" lang="' . esc_attr( $this->language() ) . '">';
		$html .= '<section class="tsar-redesign__hero" aria-labelledby="tsar-hero-title">';
		if ( $hero_image ) {
			$html .= '<div class="tsar-redesign__hero-media">' . $this->image_html( $hero_image, 'tsar-redesign__hero-image', true ) . '</div>';
		}
		$html .= '<div class="tsar-redesign__hero-content"><p class="tsar-redesign__eyebrow">TSAR HOTEL · YAOUNDÉ</p><h1 id="tsar-hero-title">' . esc_html( $this->t( 'Experience elegant hospitality in the heart of Yaoundé') ) . '</h1><p class="tsar-redesign__hero-tagline">' . esc_html( $this->t( 'Where Africa Meets') ) . '</p><div class="tsar-redesign__hero-actions"><a class="tsar-redesign__button tsar-redesign__button--light" href="' . esc_url( $booking_url ) . '">' . esc_html( $this->t( 'Book your stay') ) . '</a><a class="tsar-redesign__hero-secondary" href="' . esc_url( $this->anchor_url( 'rooms' ) ) . '">' . esc_html( $this->t( 'Discover our rooms') ) . '</a></div></div><a class="tsar-redesign__scroll" href="' . esc_url( $this->anchor_url( 'about' ) ) . '">' . esc_html( $this->t( 'Scroll to discover') ) . '<span aria-hidden="true"> ↓</span></a></section>';

		$html .= '<section class="tsar-redesign__booking-bar" aria-label="' . esc_attr( $this->t( 'Online booking') ) . '"><div><span class="tsar-redesign__booking-label">' . esc_html( $this->t( 'Your stay at TSAR HOTEL') ) . '</span><span>' . esc_html( $this->t( 'Check dates and availability directly with our booking system.') ) . '</span></div><a class="tsar-redesign__button" href="' . esc_url( $booking_url ) . '">' . esc_html( $this->t( 'Online booking') ) . '<span aria-hidden="true"> ↗</span></a></section>';

		$html .= '<section class="tsar-redesign__section tsar-redesign__story" id="about"><div class="tsar-redesign__story-copy"><p class="tsar-redesign__eyebrow">' . esc_html( $this->t( 'A Yaoundé welcome') ) . '</p><h2>' . esc_html( $this->t( 'Where Africa Meets') ) . '</h2><p>' . esc_html( $this->t( 'At TSAR HOTEL, enjoy comfortable accommodation, dining and a warm welcome in Yaoundé—whether you are travelling for business or leisure.') ) . '</p><a class="tsar-redesign__text-link" href="' . esc_url( $this->page_url( 'accommodations', '/accommodations/' ) ) . '">' . esc_html( $this->t( 'Explore rooms and suites') ) . '<span aria-hidden="true"> ↗</span></a></div>';
		if ( $story_image ) {
			$html .= '<figure class="tsar-redesign__story-image">' . $this->image_html( $story_image, 'tsar-redesign__image', false ) . '</figure>';
		}
		$html .= '</section>';

		$html .= '<section class="tsar-redesign__section tsar-redesign__rooms" id="rooms"><div class="tsar-redesign__section-heading"><div><p class="tsar-redesign__eyebrow">' . esc_html( $this->t( 'Stay your way') ) . '</p><h2>' . esc_html( $this->t( 'Rooms & Suites') ) . '</h2></div><a class="tsar-redesign__text-link" href="' . esc_url( $this->page_url( 'accommodations', '/accommodations/' ) ) . '">' . esc_html( $this->t( 'View all accommodations') ) . '<span aria-hidden="true"> ↗</span></a></div><div class="tsar-redesign__room-grid">' . $this->room_cards() . '</div></section>';

		$html .= '<section class="tsar-redesign__dining" aria-label="' . esc_attr( $this->t( 'Dining at TSAR HOTEL') ) . '"><article class="tsar-redesign__dining-panel" id="restaurant"><p class="tsar-redesign__eyebrow">' . esc_html( $this->t( 'At the table') ) . '</p><h2>' . esc_html( $this->t( 'Restaurant') ) . '</h2><p>' . esc_html( $this->t( 'Discover dining at TSAR HOTEL. Contact our team for current menus and service information.') ) . '</p><a class="tsar-redesign__text-link" href="' . esc_url( $this->anchor_url( 'contact' ) ) . '">' . esc_html( $this->t( 'Contact the team') ) . '<span aria-hidden="true"> ↗</span></a></article><article class="tsar-redesign__dining-panel tsar-redesign__dining-panel--accent" id="snack-lounge"><p class="tsar-redesign__eyebrow">' . esc_html( $this->t( 'A relaxed pause') ) . '</p><h2>' . esc_html( $this->t( 'Snack Lounge') ) . '</h2><p>' . esc_html( $this->t( 'Visit the TSAR Snack Lounge. Ask our team for current menu and opening information.') ) . '</p><a class="tsar-redesign__text-link" href="' . esc_url( $this->anchor_url( 'contact' ) ) . '">' . esc_html( $this->t( 'Contact the team') ) . '<span aria-hidden="true"> ↗</span></a></article></section>';

		$html .= '<section class="tsar-redesign__section tsar-redesign__services"><div class="tsar-redesign__section-heading"><div><p class="tsar-redesign__eyebrow">' . esc_html( $this->t( 'Plan your visit') ) . '</p><h2>' . esc_html( $this->t( 'Our services') ) . '</h2></div></div><div class="tsar-redesign__service-grid">';
		$html .= $this->service_card( '01', $this->t( 'Online booking'), $this->t( 'Check dates and availability through the hotel booking system.'), $booking_url, '↗' );
		$html .= $this->service_card( '02', $this->t( 'Special offers'), $this->t( 'Ask the team about current offers and booking options.'), $this->anchor_url( 'offers' ), '↘' );
		$html .= $this->service_card( '03', $this->t( 'Events'), $this->t( 'Tell us what you are planning and enquire with our team.'), $this->anchor_url( 'events' ), '↘' );
		$html .= $this->service_card( '04', $this->t( 'Guest reviews'), '', $review_url, $review_url ? '↗' : '', ! $review_url );
		$html .= '</div></section>';

		$html .= '<section class="tsar-redesign__section tsar-redesign__offers" id="offers"><div class="tsar-redesign__section-heading"><div><p class="tsar-redesign__eyebrow">' . esc_html( $this->t( 'Make the most of your stay') ) . '</p><h2>' . esc_html( $this->t( 'Special offers') ) . '</h2></div></div>';
		if ( $offers ) {
			$html .= '<div class="tsar-redesign__editorial-grid">' . $offers . '</div>';
		} else {
			$html .= '<div class="tsar-redesign__empty-state"><p>' . esc_html( $this->t( 'Ask our team about current offers and booking options.') ) . '</p><a class="tsar-redesign__text-link" href="' . esc_url( $this->anchor_url( 'contact' ) ) . '">' . esc_html( $this->t( 'Enquire with the team') ) . '<span aria-hidden="true"> ↗</span></a></div>';
		}
		$html .= '</section>';

		$html .= '<section class="tsar-redesign__events" id="events"><div class="tsar-redesign__events-inner"><div class="tsar-redesign__section-heading"><div><p class="tsar-redesign__eyebrow">' . esc_html( $this->t( 'Gather at TSAR HOTEL') ) . '</p><h2>' . esc_html( $this->t( 'Events') ) . '</h2></div></div><p class="tsar-redesign__events-intro">' . esc_html( $this->t( 'Planning an event? Share your requirements with our team and enquire about possible arrangements.') ) . '</p>';
		if ( $events ) {
			$html .= '<div class="tsar-redesign__editorial-grid">' . $events . '</div>';
		}
		$html .= '<a class="tsar-redesign__button" href="' . esc_url( $this->anchor_url( 'contact' ) ) . '">' . esc_html( $this->t( 'Request event information') ) . '<span aria-hidden="true"> ↗</span></a></div></section>';

		$html .= '<section class="tsar-redesign__section tsar-redesign__gallery" id="gallery"><div class="tsar-redesign__section-heading"><div><p class="tsar-redesign__eyebrow">' . esc_html( $this->t( 'A look around') ) . '</p><h2>' . esc_html( $this->t( 'Gallery') ) . '</h2></div></div>';
		if ( $gallery ) {
			$html .= '<div class="tsar-redesign__gallery-grid">';
			foreach ( $gallery as $image ) {
				$html .= '<figure class="tsar-redesign__gallery-item">' . $this->image_html( $image, 'tsar-redesign__gallery-image', false ) . '</figure>';
			}
			$html .= '</div>';
		} else {
			$html .= '<p class="tsar-redesign__empty-state">' . esc_html( $this->t( 'Add approved TSAR HOTEL photographs to the homepage media gallery to display them here.') ) . '</p>';
		}
		$html .= '</section>';

		$html .= '<section class="tsar-redesign__reviews" id="guest-reviews" aria-label="' . esc_attr( $this->t( 'Guest reviews') ) . '">';
		if ( $review_url ) {
			$html .= '<div class="tsar-redesign__section-heading"><div><p class="tsar-redesign__eyebrow">' . esc_html( $this->t( 'Guest feedback') ) . '</p><h2>' . esc_html( $this->t( 'Guest reviews') ) . '</h2></div></div><a class="tsar-redesign__text-link" href="' . esc_url( $review_url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $this->t( 'Read reviews on Google') ) . '<span aria-hidden="true"> ↗</span></a>';
		}
		$html .= '</section>';

		$html .= '<section class="tsar-redesign__contact" id="contact"><div class="tsar-redesign__contact-copy"><p class="tsar-redesign__eyebrow">' . esc_html( $this->t( 'We are here to help') ) . '</p><h2>' . esc_html( $this->t( 'Contact our team') ) . '</h2><p>' . esc_html( $this->t( 'Ask about rooms, dining, events or your stay at TSAR HOTEL.') ) . '</p>';
		if ( $phone ) {
			$html .= '<a class="tsar-redesign__contact-link" href="tel:' . esc_attr( $phone ) . '">' . esc_html( $this->t( 'Call the hotel') ) . '</a>';
		}
		if ( $email ) {
			$html .= '<a class="tsar-redesign__contact-link" href="mailto:' . esc_attr( $email ) . '">' . esc_html( $this->t( 'Email the hotel') ) . '</a>';
		}
		$html .= '<a class="tsar-redesign__contact-link" href="' . esc_url( $booking_url ) . '">' . esc_html( $this->t( 'Online booking') ) . '<span aria-hidden="true"> ↗</span></a></div><div class="tsar-redesign__contact-form">';
		if ( $contact_form ) {
			$html .= $contact_form;
		} else {
			$html .= '<p>' . esc_html( $this->t( 'Use the booking page for room availability and reservations. Contact details can be added by the hotel administrator in TSAR HOTEL Updates.') ) . '</p>';
		}
		$html .= '</div></section>';
		$html .= '</div>';
		return $html;
	}

	private function service_card( $number, $title, $description, $url, $arrow, $unpopulated = false ) {
		$class = 'tsar-redesign__service-card' . ( $unpopulated ? ' tsar-redesign__service-card--unpopulated' : '' );
		$tag   = $unpopulated || '' === $url ? 'div' : 'a';
		$href  = 'a' === $tag ? ' href="' . esc_url( $url ) . '"' : ' aria-label="' . esc_attr( $title ) . '"';
		$html  = '<' . $tag . ' class="' . esc_attr( $class ) . '"' . $href . '><span class="tsar-redesign__service-number">' . esc_html( $number ) . '</span><h3>' . esc_html( $title ) . '</h3>';
		if ( '' !== $description ) {
			$html .= '<p>' . esc_html( $description ) . '</p>';
		}
		if ( '' !== $arrow ) {
			$html .= '<span class="tsar-redesign__service-arrow" aria-hidden="true">' . esc_html( $arrow ) . '</span>';
		}
		$html .= '</' . $tag . '>';
		return $html;
	}

	public function render_floating_booking() {
		if ( ! $this->enabled() ) {
			return;
		}
		$url = $this->page_url( 'reservations', '/reservations/' );
		echo '<a class="tsar-redesign__floating-book" href="' . esc_url( $url ) . '"><span aria-hidden="true">✦</span><span>' . esc_html( $this->t( 'Book now') ) . '</span></a>';
	}
}
