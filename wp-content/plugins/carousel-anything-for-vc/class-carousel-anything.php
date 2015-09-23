<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists('GambitCarouselAnything') ) {
	class GambitCarouselAnything {
		
		private static $id = 0;

		/**
		 * Hook into WordPress
		 *
		 * @return	void
		 * @since	1.0
		 */		
		function __construct() {
			
			// Initializes VC shortcode
			add_filter( 'init', array( $this, 'createCAShortcodes' ), 999 );
			
			// Render shortcode for the plugin
			add_shortcode( 'carousel_anything', array( $this, 'renderCAShortcode' ) );
			
		}
		
		
		/**
		 * Creates the carousel element inside VC
		 *
		 * @return	void
		 * @since	1.0
		 */
		public function createCAShortcodes() {
			
			if ( ! is_admin() ) {
				return;
			}
			if ( ! function_exists( 'vc_map' ) ) {
				return;
			}
			
			$defaultContent = '[vc_row_inner][vc_column_inner width="1/1"][/vc_column_inner][/vc_row_inner]
[vc_row_inner][vc_column_inner width="1/1"][/vc_column_inner][/vc_row_inner]
				[vc_row_inner][vc_column_inner width="1/1"][/vc_column_inner][/vc_row_inner]';
			
			if ( vc_is_frontend_editor() ) {
				$defaultContent = '';
			}

			// Loads fixes that makes Carousel Anything possible.
			CARowFixes();

			vc_map( array(
				"name" => __( 'Carousel Anything', GAMBIT_CAROUSEL_ANYTHING ),
				"base" => "carousel_anything",
				"icon" => plugins_url( 'carousel-anything/images/vc-icon.png', GAMBIT_CAROUSEL_ANYTHING_FILE ),
				"description" => __( 'A modern and responsive content carousel system.', GAMBIT_CAROUSEL_ANYTHING ),
				"as_parent" => array( 'only' => 'vc_row,vc_row_inner' ),
				"js_view" => 'VcColumnView',
				"content_element" => true,
				'is_container' => true,
				'container_not_allowed' => false,
				'default_content' => $defaultContent,
				'js_view' => 'VcRowView',
				"params" => array(
					array(
						"type" => "textfield",
						"heading" => __( 'Items to display on screen', GAMBIT_CAROUSEL_ANYTHING ),
						"param_name" => "items",
						"value" => '3',
						"group" => __( 'General Options', GAMBIT_CAROUSEL_ANYTHING ),
						"description" => __( 'Maximum items to display at a time.', GAMBIT_CAROUSEL_ANYTHING ),
					),
					array(
						"type" => "textfield",
						"heading" => __( 'Items to display on small desktops', GAMBIT_CAROUSEL_ANYTHING ),
						"param_name" => "items_desktop_small",
						"value" => '2',
						"group" => __( 'General Options', GAMBIT_CAROUSEL_ANYTHING ),
						"description" => __( 'Maximum items to display at a time for smaller screened desktops.', GAMBIT_CAROUSEL_ANYTHING ),
					),
					array(
						"type" => "textfield",
						"heading" => __( 'Items to display on tablets', GAMBIT_CAROUSEL_ANYTHING ),
						"param_name" => "items_tablet",
						"value" => '2',
						"group" => __( 'General Options', GAMBIT_CAROUSEL_ANYTHING ),
						"description" => __( 'Maximum items to display at a time for tablet devices.', GAMBIT_CAROUSEL_ANYTHING ),
					),
					array(
						"type" => "textfield",
						"heading" => __( 'Items to display on mobile phones', GAMBIT_CAROUSEL_ANYTHING ),
						"param_name" => "items_mobile",
						"value" => '1',
						"group" => __( 'General Options', GAMBIT_CAROUSEL_ANYTHING ),
						"description" => __( 'Maximum items to display at a time for mobile devices.', GAMBIT_CAROUSEL_ANYTHING ),
					),
					array(
						"type" => "dropdown",
						"heading" => __( 'Navigation Thumbnails', GAMBIT_CAROUSEL_ANYTHING ),
						"param_name" => "thumbnails",
						"value" => array(
							__( 'Circle', GAMBIT_CAROUSEL_ANYTHING ) => 'circle',
							__( 'Square', GAMBIT_CAROUSEL_ANYTHING ) => 'square',
							__( 'Arrows', GAMBIT_CAROUSEL_ANYTHING ) => 'arrows',
							__( 'None', GAMBIT_CAROUSEL_ANYTHING ) => 'none',
						),
						'description' => __( 'Select whether to display thumbnails below your carousel for navigation.<br>Selecting Arrows will display navigation arrows at each side.', GAMBIT_CAROUSEL_ANYTHING ),
						"group" => __( 'Thumbnails', GAMBIT_CAROUSEL_ANYTHING ),
					),
					array(
						"type" => "colorpicker",
						"heading" => __( 'Thumbnail Default Color', GAMBIT_CAROUSEL_ANYTHING ),
						"param_name" => "thumbnail_color",
						"value" => '#c3cbc8',
						"description" => __( 'The color of the non-active thumbnail. Not applicable to Arrows type of navigation.', GAMBIT_CAROUSEL_ANYTHING ),
						"dependency" => array(
							"element" => "thumbnails",
							"value" => array( "circle", "square" ),
						),
						"group" => __( 'Thumbnails', GAMBIT_CAROUSEL_ANYTHING ),
					),
					array(
						"type" => "colorpicker",
						"heading" => __( 'Thumbnail Active Color', GAMBIT_CAROUSEL_ANYTHING ),
						"param_name" => "thumbnail_active_color",
						"value" => '#869791',
						"description" => __( 'The color of the active / current thumbnail. Not applicable to Arrows type of navigation.', GAMBIT_CAROUSEL_ANYTHING ),
						"dependency" => array(
							"element" => "thumbnails",
							"value" => array( "circle", "square" ),
						),
						"group" => __( 'Thumbnails', GAMBIT_CAROUSEL_ANYTHING ),
					),
					array(
						"type" => "checkbox",
						"heading" => '',
						"param_name" => "thumbnail_numbers",
						"value" => array( __( 'Check to display page numbers inside the thumbnails. Not applicable to Arrows type of navigation.', GAMBIT_CAROUSEL_ANYTHING ) => 'true' ),
						"description" => '',
						"dependency" => array(
							"element" => "thumbnails",
							"value" => array( "circle", "square" ),
						),
						"group" => __( 'Thumbnails', GAMBIT_CAROUSEL_ANYTHING ),
					),
					array(
						"type" => "colorpicker",
						"heading" => __( 'Thumbnail Default Page Number Color', GAMBIT_CAROUSEL_ANYTHING ),
						"param_name" => "thumbnail_number_color",
						"value" => '#ffffff',
						"description" => __( 'The color of the page numbers inside non-active thumbnails', GAMBIT_CAROUSEL_ANYTHING ),
						"dependency" => array(
							"element" => "thumbnail_numbers",
							"value" => array( "true" ),
						),
						"group" => __( 'Thumbnails', GAMBIT_CAROUSEL_ANYTHING ),
					),
					array(
						"type" => "colorpicker",
						"heading" => __( 'Thumbnail Active Page Number Color', GAMBIT_CAROUSEL_ANYTHING ),
						"param_name" => "thumbnail_number_active_color",
						"value" => '#ffffff',
						"description" => __( 'The color of the page numbers inside active / current thumbnails', GAMBIT_CAROUSEL_ANYTHING ),
						"dependency" => array(
							"element" => "thumbnail_numbers",
							"value" => array( "true" ),
						),
						"group" => __( 'Thumbnails', GAMBIT_CAROUSEL_ANYTHING ),
					),
					array(
						"type" => "textfield",
						"heading" => __( 'Autoplay', GAMBIT_CAROUSEL_ANYTHING ),
						"param_name" => "autoplay",
						"value" => '5000',
						"description" => __( 'Enter an amount in milliseconds for the carousel to move. Leave blank to disable autoplay', GAMBIT_CAROUSEL_ANYTHING ),
						"group" => __( 'Advanced', GAMBIT_CAROUSEL_ANYTHING ),
					),
					array(
						"type" => "checkbox",
						"heading" => '',
						"param_name" => "stop_on_hover",
						"value" => array( __( 'Pause the carousel when the mouse is hovered onto it.', GAMBIT_CAROUSEL_ANYTHING ) => 'true' ),
						"description" => '',
						"dependency" => array(
							"element" => "autoplay",
							"not_empty" => true,
						),
						"group" => __( 'Advanced', GAMBIT_CAROUSEL_ANYTHING ),
					),
					array(
						"type" => "textfield",
						"heading" => __( 'Scroll Speed', GAMBIT_CAROUSEL_ANYTHING ),
						"param_name" => "speed_scroll",
						"value" => '800',
						"description" => __( 'The speed the carousel scrolls in milliseconds', GAMBIT_CAROUSEL_ANYTHING ),
						"group" => __( 'Advanced', GAMBIT_CAROUSEL_ANYTHING ),
					),
					array(
						"type" => "textfield",
						"heading" => __( 'Rewind Speed', GAMBIT_CAROUSEL_ANYTHING ),
						"param_name" => "speed_rewind",
						"value" => '1000',
						"description" => __( 'The speed the carousel scrolls back to the beginning after it reaches the end in milliseconds', GAMBIT_CAROUSEL_ANYTHING ),
						"group" => __( 'Advanced', GAMBIT_CAROUSEL_ANYTHING ),
					),
					array(
						"type" => "checkbox",
						"heading" => '',
						"param_name" => "touchdrag",
						"value" => array( __( 'Check this box to disable touch dragging of the carousel. (Normally enabled by default)', GAMBIT_CAROUSEL_ANYTHING ) => 'true' ),
						"description" => '',
						"group" => __( 'Advanced', GAMBIT_CAROUSEL_ANYTHING ),
					),
					array(
						"type" => "dropdown",
						"heading" => __( 'Keyboard Navigation', GAMBIT_CAROUSEL_ANYTHING ),
						"param_name" => "keyboard",
						"value" => array(
							__( 'Disabled', GAMBIT_CAROUSEL_ANYTHING ) => 'false',
							__( 'Cursor keys', GAMBIT_CAROUSEL_ANYTHING ) => 'cursor',
							__( 'A and D keys', GAMBIT_CAROUSEL_ANYTHING ) => 'fps',
						),
						'description' => __( 'Select whether to enable carousel manipulation through cursor keys. Enabling this on a page with multiple carousels may give unpredictable results! Use it on a page with a single Carousel Anything element, or when there are no other scripts binding cursor or other keys present that may conflict.', GAMBIT_CAROUSEL_ANYTHING ),
						"group" => __( 'Advanced', GAMBIT_CAROUSEL_ANYTHING ),
					),
					array(
						"type" => "textfield",
						"heading" => __( 'Custom Class', GAMBIT_CAROUSEL_ANYTHING ),
						"param_name" => "class",
						"value" => '',
						"description" => __( 'Add a custom class name for the carousel here.', GAMBIT_CAROUSEL_ANYTHING ),
						"group" => __( 'Advanced', GAMBIT_CAROUSEL_ANYTHING ),
					),
				),
			) );
		}
		
		
		/**
		 * Shortcode logic
		 *
		 * @return	string The rendered html
		 * @since	1.0
		 */
		public function renderCAShortcode( $atts, $content = null ) {
			$defaults = array(
				'items' => '3',
				'items_desktop_small' => '2',
				'items_tablet' => '2',
				'items_mobile' => '1',
				'autoplay' => '5000',
				'stop_on_hover' => false,
				'scroll_per_page' => false,
				'speed_scroll' => '800',
				'speed_rewind' => '1000',
				'thumbnails' => 'circle',
				'thumbnail_color' => '#c3cbc8',
				'thumbnail_active_color' => '#869791',
				'thumbnail_numbers' => false,
				'thumbnail_number_color' => '#ffffff',
				'thumbnail_number_active_color' => '#ffffff',
				'touchdrag' => 'false',
				'keyboard' => 'false',		
				'class' => '',				
			);
			if ( empty( $atts ) ) {
				$atts = array();
			}
			$atts = array_merge( $defaults, $atts );

			wp_enqueue_style( 'carousel-anything-owl', plugins_url( 'carousel-anything/css/owl.carousel.theme.style.css', __FILE__ ), array(), VERSION_GAMBIT_CAROUSEL_ANYTHING );
			wp_enqueue_script( 'carousel-anything-owl', plugins_url( 'carousel-anything/js/min/owl.carousel-min.js', __FILE__ ), array( 'jquery' ), '1.3.3', true );
			wp_enqueue_script( 'carousel-anything', plugins_url( 'carousel-anything/js/min/script-min.js', __FILE__ ), array( 'jquery', 'carousel-anything-owl' ), VERSION_GAMBIT_CAROUSEL_ANYTHING, true );

			self::$id++;
			$id = 'carousel-anything-' . esc_attr( self::$id );

			$ret = "";

			// Thumbnail styles
			$styles = "";
			$carousel_class = "";
			$navigation_buttons = false;
			if ( ! empty( $atts['thumbnails'] ) ) {
				if ( $atts['thumbnails'] == 'square' ) {
					$styles .= "#{$id}.owl-theme .owl-controls .owl-page span { border-radius: 0 }";
				}
				if ( $atts['thumbnails'] == 'arrows' ) {
					$navigation_buttons = true;
					$carousel_class = " has-arrows";
				}
				if ( $atts['thumbnails'] != 'none' && $atts['thumbnails'] != 'arrows') {
					$styles .= "#{$id}.owl-theme .owl-controls .owl-page span { opacity: 1; background: " . esc_attr( $atts['thumbnail_color'] ) . " }"
							 . "#{$id}.owl-theme .owl-controls .owl-page.active span { background: " . esc_attr( $atts['thumbnail_active_color'] ) . " }";
				}
				if ( $atts['thumbnail_numbers'] != false && $atts['thumbnails'] != 'arrows') {
					$styles .= "#{$id}.owl-theme .owl-controls .owl-page span.owl-numbers { color: " . esc_attr( $atts['thumbnail_number_color'] ) . " }"
							 . "#{$id}.owl-theme .owl-controls .owl-page.active span.owl-numbers { color: " . esc_attr( $atts['thumbnail_number_active_color'] ) . " }";
				}
			}
			if ( ! empty( $atts['class'] ) ) {
				$carousel_class .= ' ' . esc_attr( $atts['class'] );
			}
			if ( $styles ) {
				$ret .= "<style>{$styles}</style>";
			}

			if ( $navigation_buttons ) {
				wp_enqueue_style( 'dashicons' );
				wp_enqueue_style( 'carousel-anything', plugins_url( '/carousel-anything/css/style.css', __FILE__ ), array(), VERSION_GAMBIT_CAROUSEL_ANYTHING );
			}

			// Carousel html
			// $ret .= '<div class="customNavigation"><a class="btn prev">Previous</a><a class="btn next">Next</a></div>';
			$ret .= '<div id="' . esc_attr( $id ) . '" class="carousel-anything-container owl-carousel' . $carousel_class . '" data-items="' . esc_attr( $atts['items'] ) . '" data-scroll_per_page="' . esc_attr( $atts['scroll_per_page'] ) . '" data-autoplay="' . esc_attr( empty( $atts['autoplay'] ) || $atts['autoplay'] == '0' ? 'false' : $atts['autoplay'] ) . '" data-items-small="' . esc_attr( $atts['items_desktop_small'] ) . '" data-items-tablet="' . esc_attr( $atts['items_tablet'] ) . '" data-items-mobile="' . esc_attr( $atts['items_mobile'] ) . '" data-stop-on-hover="' . esc_attr( $atts['stop_on_hover'] ) . '" data-speed-scroll="' . esc_attr( $atts['speed_scroll'] ) . '" data-speed-rewind="' . esc_attr( $atts['speed_rewind'] ) . '" data-thumbnails="' . esc_attr( $atts['thumbnails'] ) . '" data-thumbnail-numbers="' . esc_attr( $atts['thumbnail_numbers'] ) . '" data-navigation="' . esc_attr( $navigation_buttons ? 'true' : 'false' ) . '" data-touchdrag="' . esc_attr( $atts['touchdrag'] ) . '" data-keyboard="' . esc_attr( $atts['keyboard'] ) . '">';
			$ret .= do_shortcode( $content ) . '</div>';

			return $ret;
		}
		
	}
	new GambitCarouselAnything();
}


/**
 * Loads the fixes that makes Carousel Anything work.
 *
 * @return	void
 * @since	1.5
 */
function CARowFixes() {
	/**
	 * We need to define this so that VC will show our nesting container correctly
	 */
	if ( class_exists( 'WPBakeryShortCodesContainer' ) && ! class_exists( 'WPBakeryShortCode_Carousel_Anything' ) ) {
		class WPBakeryShortCode_Carousel_Anything extends WPBakeryShortCodesContainer {
		}
	} else {
		// If we can't detech the classes it means that VC is embeded in a theme,
		global $composer_settings;

		// The class WPBakeryShortCodesContainer is defined in VC's shortcodes.php, include it so we can define our container
		if ( ! empty( $composer_settings ) ) {
			if ( array_key_exists( 'COMPOSER_LIB', $composer_settings ) ) {
				$lib_dir = $composer_settings['COMPOSER_LIB'];
				if ( file_exists( $lib_dir . 'shortcodes.php' ) ) {
					require_once( $lib_dir . 'shortcodes.php' );
				}
			}
		}

		// We need to define this so that VC will show our nesting container correctly
		if ( class_exists( 'WPBakeryShortCodesContainer' ) && ! class_exists( 'WPBakeryShortCode_Carousel_Anything' ) ) {
			class WPBakeryShortCode_Carousel_Anything extends WPBakeryShortCodesContainer {
			}
		}
	}
}