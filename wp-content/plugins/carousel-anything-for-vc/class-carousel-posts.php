<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists('GambitCarouselPosts') ) {
	class GambitCarouselPosts {

	    private static $id = 0;

		/**
		 * Hook into WordPress
		 *
		 * @return	void
		 * @since	1.0
		 */
		function __construct() {
			
			// Initializes VC shortcode
			add_filter( 'init', array( $this, 'createCPShortcodes' ), 999 );
			
			// Render shortcode for the plugin
			add_shortcode( 'carousel_posts', array( $this, 'renderCPShortcode' ) );
		}
	

		/**
		 * Pulls a list of options with dependencies
		 *
		 * @return	$posttypes
		 * @since	1.5
		 */	
		function generateOptions() {
			// Initialize option.
			$taxDependency = array();
			$termDependency = array();
			$theTerms = array();
			$taxonomyNames =array();
			$allTaxonomies = array();
			$taxonomyLabel = array();
			$allTheTerms = array();
			
			// First, pull all post types.
			$postTypes = $this->getPostTypes('array');
			
			// And make an output here. This generates an option in VC.
			$output[] = array(
				"type" => "dropdown",
				"heading" => __( 'Select Post Type we will be using', GAMBIT_CAROUSEL_ANYTHING ),
				"param_name" => "posttype",
				"value" => $this->getPostTypes(),
				'description' => __( 'Choose the Post Type to use to populate the Carousel.<br />If your post type has a taxonomy like Category, and its terms has posts associated with it, a new pulldown below will appear.', GAMBIT_CAROUSEL_ANYTHING ),
				"group" => __( 'Contents', GAMBIT_CAROUSEL_ANYTHING ),
			);
					
			// Now pull their taxonomies, using the array generated.
			foreach ( $postTypes['slug'] as $postType ) {
			
				// This does the dirty work of getting the taxonomies, it returns as an array object
				$taxonomyNames[$postType] = get_object_taxonomies( $postType );
				
				// No taxonomy found? Terminate.
				if ( ! is_array( $taxonomyNames[$postType] ) ) { 
					break; 
				}
				
				// Now parse the taxonomy contents.
				if (! empty( $taxonomyNames[$postType] ) ) {
					foreach ( $taxonomyNames[$postType] as $taxonomyName ) {
						$allTaxonomies[] = $taxonomyName;
						$tax = get_taxonomy( $taxonomyName );
						
						//Store the Taxonomy name so we have a human-readable name for the VC option later.
						$taxonomyLabel[$taxonomyName] = $tax->labels->name;
												
						// Populate dependency data.
						$taxDependency[$taxonomyName][] = $postType;
					}
				}
				
				// Iterate through the collected taxonomy and terms and now print them all				
				if ( count( $taxonomyNames[$postType] ) > 0 ) {
					// Initialize the array for collecting the terms selectable.
					$allTheTerms[$postType] = array();
					foreach ( $taxonomyNames[$postType] as $taxonomyName ) {
						// Does the heavy lifting of getting all the terms in a given taxonomy.
						$theTermNames[$postType] = $this->getTerms($taxonomyName);
						// The temporary placeholder for collected terms.
						$nowTheNames[$postType] = array();
						if ( count($theTermNames[$postType]) > 0 ) {
							// Apply the default selection ONLY if there's a term to print.
							$nowTheNames[$postType] = array( __( 'All Categories', GAMBIT_CAROUSEL_ANYTHING ) => 'all' );
							foreach( $theTermNames[$postType] as $key => $value ) {
							    $key .= ' (' . $taxonomyLabel[$taxonomyName] . ')';
								$value = $taxonomyName . '|' . $value;
								$nowTheNames[$postType][$key] = $value;
							}
							// This collects all the terms from separate taxonomies into a unified array identified by post type.
							$allTheTerms[$postType] = array_merge($allTheTerms[$postType], $nowTheNames[$postType]);
						}
					}
					
					// Makes sure no duplicate terms get printed.
					$allTheTerms[$postType] = array_unique($allTheTerms[$postType]);
					
					// Now print out the option ONLY if there's terms to use.
					if ( count( $allTheTerms[$postType] ) > 0 ) {
						
						$caption = __( 'Select Category / Taxonomy for %s', GAMBIT_CAROUSEL_ANYTHING );					
						$caption = str_replace('%s', $postTypes['name'][$postType], $caption );
					
						$output[] = array(
							"type" => "dropdown",
							"heading" => $caption,
							"param_name" => "taxonomy_" . $postType,
							"value" => $allTheTerms[$postType],
		                    'description' => __( 'Choose the category terms for this post type to use.', GAMBIT_CAROUSEL_ANYTHING ),
							"dependency" => array(
								"element" => "posttype",
								"value" => $taxDependency[$taxonomyName],
							),
							"group" => __( 'Contents', GAMBIT_CAROUSEL_ANYTHING ),
						);
					}
				}
			}		
			return $output;
		}


		/**
		 * Limits wordcount.
		 *
		 * @return  subtracted words
		 * @since	1.6
		 */	
		function limit_words( $string, $offset, $word_limit ) {
		    $words = explode( " ",$string );
		    $out = implode( " ", array_splice( $words, $offset, $word_limit ) );
			return $out;
		}


		/**
		 * Counts words.
		 *
		 * @return  number of words given.
		 * @since	1.6
		 */	
		function count_words( $string ) {
		    $words = explode( " ",$string );
		    return count( $words );
		}


		/**
		 * Pulls a list of terms with their Taxonomies
		 *
		 * @return	$posttypes
		 * @since	1.5
		 */		
		function getTerms( $taxonomy ) {
			$output = array();
			
			$terms = get_terms( $taxonomy, array( 
				'parent' => 0,
				'hide_empty' => false,
			) );
	
			if ( is_wp_error( $terms ) ) {
				return $output;
			}
			
			foreach( $terms as $term ) {
		
				$output[ $term->name ] = $term->slug;
				$term_children = get_term_children( $term->term_id, $taxonomy );
		
				if ( is_wp_error( $term_children ) ) {
					continue;
				}
				
				// If the term has a child, this disambiguates the entry.
				foreach( $term_children as $term_child_id ) {
			
					$term_child = get_term_by( 'id', $term_child_id, $taxonomy );
			
					if ( is_wp_error( $term_child ) ) {
						continue;
					}
			
					$output[ $term->name . ' - ' . $term_child->name ] = $term_child->slug;
				}
				
		
			}
			
			return $output;
		}


		/**
		 * Pulls a list of post types and their slugs
		 *
		 * @return	$posttypes
		 * @since	1.5
		 */		
		public function getPostTypes( $type='list' ) {
			if ($type == 'list') {
				$posttypes = array( 'Posts' => 'post', 'Pages' => 'page' );
			}
			else {
				$posttypes['slug'][] = 'post';
				$posttypes['slug'][] = 'page';				
				$posttypes['name']['post'] = 'Posts';
				$posttypes['name']['page'] = 'Pages';				
			}
			$args = array(
			   'public' => true,
			   '_builtin' => false
			);
			$post_types = get_post_types( $args, 'objects' );
			foreach ( $post_types  as $post_type ) {
				if ($type == 'list') {
					$posttypes[ $post_type->labels->name ] = $post_type->query_var;
				}
				else {
					
					$posttypes['slug'][] = $post_type->query_var;
					$posttypes['name'][ $post_type->query_var ] = $post_type->labels->name;
				}
			}
			
			return $posttypes;
		}


		/**
		 * Creates the carousel element inside VC, for posts
		 *
		 * @return	void
		 * @since	1.5
		 */
		public function createCPShortcodes() {
			if ( ! is_admin() ) {
				return;
			}
			if ( ! function_exists( 'vc_map' ) ) {
				return;
			}

			// Set up VC Element Array here since we use dynamically generated stuff.
			$vcElement = array(
			    "name" => __( 'Carousel Posts', GAMBIT_CAROUSEL_ANYTHING ),
			    "base" => "carousel_posts",
				"icon" => plugins_url( 'carousel-anything/images/vc-icon.png', GAMBIT_CAROUSEL_ANYTHING_FILE ),
				"description" => __( 'A modern and responsive posts carousel system.', GAMBIT_CAROUSEL_ANYTHING ),
				"as_parent" => array( 'only' => 'vc_row,vc_row_inner' ),
				"content_element" => true,
				'is_container' => true,
				'container_not_allowed' => false,						
			);

			// Make the options here.
			$vcElement['params'] = array( 
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
			);
			// Insert options generated by a function.
			$dynamicOptions = $this->generateOptions();
			foreach ( $dynamicOptions as $dynamicOption ) {
				$vcElement['params'][] = $dynamicOption;
			}
			
			// Continue with the rest of the options.
			$otherOptions = array( 
				array(
					"type" => "textfield",
					"heading" => __( 'Number of Total Posts', GAMBIT_CAROUSEL_ANYTHING ),
					"param_name" => "numofallposts",
					"value" => '9',
					"description" => __( 'Specify how many posts to pull all in all. Zero or blank values will pull all posts regardless of post types. When this amount is reached, all other posts will be ignored.', GAMBIT_CAROUSEL_ANYTHING ),
					"group" => __( 'Contents', GAMBIT_CAROUSEL_ANYTHING ),
				),
				array(
					"type" => "dropdown",
					"heading" => __( 'Post ordering', GAMBIT_CAROUSEL_ANYTHING ),
					"param_name" => "orderby",
					"value" => array(
                        __( 'By Date', GAMBIT_CAROUSEL_ANYTHING ) => 'date',							
                        __( 'By Post Title', GAMBIT_CAROUSEL_ANYTHING ) => 'title',
                        __( 'By Comment count', GAMBIT_CAROUSEL_ANYTHING ) => 'comment_count',
                        __( 'Random', GAMBIT_CAROUSEL_ANYTHING ) => 'rand',							
                    ),
                    'description' => __( 'Select the order of posting to pull.', GAMBIT_CAROUSEL_ANYTHING ),
					"group" => __( 'Contents', GAMBIT_CAROUSEL_ANYTHING ),
				),
				array(
					"type" => "dropdown",
					"heading" => __( 'Post direction', GAMBIT_CAROUSEL_ANYTHING ),
					"param_name" => "order_direction",
					"value" => array(
                        __( 'Descending', GAMBIT_CAROUSEL_ANYTHING ) => 'DESC',
                        __( 'Ascending', GAMBIT_CAROUSEL_ANYTHING ) => 'ASC',						
                    ),
                    'description' => __( 'Choose sorting order of the post.', GAMBIT_CAROUSEL_ANYTHING ),
					"group" => __( 'Contents', GAMBIT_CAROUSEL_ANYTHING ),
				),									
				array(
					"type" => "checkbox",
					"heading" => 'Post Details to Display',
					"param_name" => "show_details",
					"value" => array( 
						__( 'Featured Image', GAMBIT_CAROUSEL_ANYTHING ) => 'featured_image',	
						__( 'Title', GAMBIT_CAROUSEL_ANYTHING ) => 'title',														
						__( 'Author', GAMBIT_CAROUSEL_ANYTHING ) => 'author',
						__( 'Excerpt', GAMBIT_CAROUSEL_ANYTHING ) => 'excerpt',
					),
					"description" => '',
					"std" => 'featured_image,title,excerpt',
					"group" => __( 'Contents', GAMBIT_CAROUSEL_ANYTHING ),
				),
				array(
					"type" => "dropdown",
					"heading" => 'Post Design',
					"param_name" => "featured",
					"value" => array(
                        __( 'Plain image', GAMBIT_CAROUSEL_ANYTHING ) => 'image',						
                        __( 'Use as background image', GAMBIT_CAROUSEL_ANYTHING ) => 'bg',
                    ),
					"description" => __( 'The selection done here will affect all posts pulled. If a post does not have a Featured Image, it will not be rendered for that post.', GAMBIT_CAROUSEL_ANYTHING ),
					"group" => __( 'Design', GAMBIT_CAROUSEL_ANYTHING ),
				),
				array(
					"type" => "dropdown",
					"heading" => 'Alignment',
					"param_name" => "alignment",
					"value" => array(
                        __( 'No alignment', GAMBIT_CAROUSEL_ANYTHING ) => '',
                        __( 'Align left', GAMBIT_CAROUSEL_ANYTHING ) => ' gcp-alignleft',
                        __( 'Align center', GAMBIT_CAROUSEL_ANYTHING ) => ' gcp-aligncenter',	
                        __( 'Align right', GAMBIT_CAROUSEL_ANYTHING ) => ' gcp-alignright',
                    ),
					"description" => __( 'If desired, you can force content alignment of the particular pulled post.', GAMBIT_CAROUSEL_ANYTHING ),
					"group" => __( 'Design', GAMBIT_CAROUSEL_ANYTHING ),
				),
				array(
					"type" => "textfield",
					"heading" => __( 'Excerpt word count', GAMBIT_CAROUSEL_ANYTHING ),
					"param_name" => "excerpt_count",
					"value" => '25',
					"description" => __( 'If your post excerpt is too long, you can limit the amount of words printed here.', GAMBIT_CAROUSEL_ANYTHING ),
					"group" => __( 'Design', GAMBIT_CAROUSEL_ANYTHING ),
				),
				array(
					"type" => "checkbox",
					"heading" => 'Excerpt ellipsis',
					"param_name" => "ellipsis",
					"value" => array( 
						__( 'Check to add elipsis on the end part of the excerpt. If the wordcount is shorter than the limit, the ellipsis will not be added.', GAMBIT_CAROUSEL_ANYTHING ) => 'true',	
					),
					"description" => '',
					"std" => 'true',
					"group" => __( 'Design', GAMBIT_CAROUSEL_ANYTHING ),
				),				
				array(
					"type" => "textfield",
					"heading" => __( 'Image Height', GAMBIT_CAROUSEL_ANYTHING ),
					"param_name" => "image_height",
					"value" => '200',
					"description" => __( 'Specify the height of the image inside the carousel content for each post.', GAMBIT_CAROUSEL_ANYTHING ),
					"group" => __( 'Design', GAMBIT_CAROUSEL_ANYTHING ),
					"dependency" => array(
						"element" => "featured",
						"value" => "image",
					),
				),
				array(
					"type" => "textfield",
					"heading" => __( 'Content Height', GAMBIT_CAROUSEL_ANYTHING ),
					"param_name" => "content_height",
					"value" => '400',
					"description" => __( 'Specify the height of the carousel content for each post.', GAMBIT_CAROUSEL_ANYTHING ),
					"group" => __( 'Design', GAMBIT_CAROUSEL_ANYTHING ),
					"dependency" => array(
						"element" => "featured",
						"value" => "bg",
					),
				),
				array(
					"type" => "colorpicker",
					"heading" => __( 'Title Text Color', GAMBIT_CAROUSEL_ANYTHING ),
					"param_name" => "title_color",
					"value" => '#000',
					"description" => __( 'The color of the title for each pulled post.', GAMBIT_CAROUSEL_ANYTHING ),
					"group" => __( 'Design', GAMBIT_CAROUSEL_ANYTHING ),
					"dependency" => array(
						"element" => "featured",
						"value" => "bg",
					),
				),
				array(
					"type" => "colorpicker",
					"heading" => __( 'Author Text Color', GAMBIT_CAROUSEL_ANYTHING ),
					"param_name" => "author_color",
					"value" => '#000',
					"description" => __( 'The color of the text of the author of each pulled post.', GAMBIT_CAROUSEL_ANYTHING ),
					"group" => __( 'Design', GAMBIT_CAROUSEL_ANYTHING ),
					"dependency" => array(
						"element" => "featured",
						"value" => "bg",
					),
				),
				array(
					"type" => "colorpicker",
					"heading" => __( 'Body Text Color', GAMBIT_CAROUSEL_ANYTHING ),
					"param_name" => "body_color",
					"value" => '#000',
					"description" => __( 'The color of the body text for each pulled post.', GAMBIT_CAROUSEL_ANYTHING ),
					"group" => __( 'Design', GAMBIT_CAROUSEL_ANYTHING ),
					"dependency" => array(
						"element" => "featured",
						"value" => "bg",
					),
				),
				array(
					"type" => "colorpicker",
					"heading" => __( 'Body Background Color', GAMBIT_CAROUSEL_ANYTHING ),
					"param_name" => "body_bg_color",
					"value" => '',
					"description" => __( 'The background color of the pulled post.', GAMBIT_CAROUSEL_ANYTHING ),
					"group" => __( 'Design', GAMBIT_CAROUSEL_ANYTHING ),
					"dependency" => array(
						"element" => "featured",
						"value" => "bg",
					),					
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
                    'description' => __( 'Select whether to enable carousel manipulation through cursor keys. Enabling this on a page with multiple carousels may give unpredictable results! Use it on a page with a single Carousel Posts element, or when there are no other scripts binding cursor or other keys present that may conflict.', GAMBIT_CAROUSEL_ANYTHING ),
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
			);
			foreach ( $otherOptions as $otherOption ) {
				$vcElement['params'][] = $otherOption;
			}
			
			//Put everything together and make it a whole array of options.
			vc_map( $vcElement );
		}
		
		/**
		 * Shortcode logic
		 *
		 * @return	string The rendered html
		 * @since	1.0
		 */
		public function renderCPShortcode( $atts, $content = null ) {
	        $defaults = array(
				'posttype' => 'post',
				'taxonomy_posts' => 'category',	
				'numofallposts' => '9',
				'orderby' => 'date',
				'order_direction' => 'DESC',
				'items' => '3',
				'items_desktop_small' => '2',
				'items_tablet' => '2',
				'items_mobile' => '1',
				'autoplay' => '5000',
				'stop_on_hover' => false,
				'scroll_per_page' => false,
				'speed_scroll' => '800',
				'speed_rewind' => '1000',
				'show_details' => 'featured_image,title,excerpt',
				'featured' => 'image',
				'alignment'	=> '',
				'thumbnails' => 'circle',
				'thumbnail_color' => '#c3cbc8',
				'thumbnail_active_color' => '#869791',
				'thumbnail_numbers' => false,
				'thumbnail_number_color' => '#ffffff',
				'thumbnail_number_active_color' => '#ffffff',
				'title_color' => '#000',
				'author_color' => '#000',
				'body_color' => '#000',
				'body_bg_color' => '',
				'touchdrag' => 'false',
				'keyboard' => 'false',
				'image_height' => '200',
				'content_height' => '400',
				'class' => '',
				'excerpt_count'	=> '25',
				'ellipsis' => 'true',
	        );
			if ( empty( $atts ) ) {
				$atts = array();
			}
			$atts = array_merge( $defaults, $atts );

			self::$id++;
			$id = 'carousel-posts-' . esc_attr( self::$id );
			
			// Parse what to show.
			$postdata = explode( ',', $atts['show_details'] );

			// Initialize necessary arrays and defaults
			$titleStyles = array();
			$authorStyles = array();
			$contentStyles = array();
			$titleOtherStyles = array();
			$authorOtherStyles = array();
			$contentOtherStyles = array();			
			$styles = "";
			$carousel_class = "";
			$navigation_buttons = false;
	        $ret = "";
			$titleEntry = '';
			$authorEntry = '';
			$contentEntry = '';

			// Pull posts				
			$querypost = array(
				'posts_per_page' => $atts['numofallposts'],
				'orderby' => $atts['orderby'],
				'order' => $atts['order_direction'],
				'post_status' => 'publish',
				'post_type' => $atts['posttype'],
				'ignore_sticky_posts' => 1,
			);
			
			// Check if term entry exists, or if set to all.
			if (! empty( $atts['taxonomy_' . $atts['posttype'] ] ) ) {		
				$termOfPost	= $atts['taxonomy_' . $atts['posttype'] ];
				if ( $termOfPost != 'all') {
					$catIn = explode( '|', $termOfPost );
					if ( is_array($catIn) ) {
						$key = $catIn[0];
						if ( $key === 'category' ) {
							$key = 'category_name';
						}
						$querypost[ $key ] = $catIn[1];
					}
				}
			}
			
			$posts = query_posts( $querypost );
			$postentries = '';
			if( have_posts() ) : 

				// Thumbnail styles
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
			
				if ( $atts['featured'] == 'bg' ) {
					$titleStyles[] = "color: " . $atts['title_color'] . "; ";
					$authorStyles[] = "color: " . $atts['author_color'] . "; ";	
					$contentStyles[] = "color: " . $atts['body_color'] . "; ";
				}
			
				// Style initialization based on display preference.
				if ( in_array( 'featured_image', $postdata ) && in_array( 'title', $postdata ) && count( $postdata ) == 2 && $atts['featured'] == 'bg' ) {
					$titleOtherStyles[] = "top: 50%; position: absolute; padding: 20px;";
				}
				elseif ( in_array( 'featured_image', $postdata ) && in_array( 'author', $postdata ) && count( $postdata ) == 2 && $atts['featured'] == 'bg' ) {
					$authorOtherStyles[] = "top: 50%; position: absolute; padding: 20px;";
				}
				elseif ( in_array( 'featured_image', $postdata ) && in_array( 'excerpt', $postdata ) && count( $postdata ) == 2 && $atts['featured'] == 'bg' ) {
					$contentOtherStyles[] = "bottom: 0; position: absolute; padding: 20px; margin-bottom: 0;";
				}
				elseif ( count( $postdata ) == 4 && $atts['featured'] == 'bg' )	{
					$titleOtherStyles[] = "padding: 20px;";
				}
			
				// Explode all individual styles for Title into the main style.
				if ( count( $titleStyles ) > 0 ) {
					$styles .= ".gcp-post-title, .gcp-post-title a, .gcp-post-title a:link, .gcp-post-title a:visited, .gcp-post-title a:hover { " . implode( " ", $titleStyles ) . " }";
				}
				if ( count( $titleOtherStyles ) > 0 ) {			
					$styles .= ".gcp-post-title { " . implode( " ", $titleOtherStyles ) . " }";
				}
			
				// Explode all individual styles for Author into the main style.
				if ( count( $authorStyles ) > 0 ) {
					$styles .= ".gcp-post-author, .gcp-post-author a, .gcp-post-author a:link, .gcp-post-author a:visited, .gcp-post-author a:hover { " . implode( " ", $authorStyles ) . " }";
				}
				if ( count( $authorOtherStyles ) > 0 ) {			
					$styles .= ".gcp-post-author { " . implode( " ", $authorOtherStyles ) . " }";
				}
			
				// Explode all individual styles for Content Excerpt into the main style.
				if ( count( $contentStyles ) > 0 ) {
					$styles .= ".gcp-post-content, .gcp-post-content a, .gcp-post-content a:link, .gcp-post-content a:hover, .gcp-post-content a:visited { " . implode( " ", $contentStyles ) . " }";
				}
				if ( count( $contentOtherStyles ) > 0 ) {			
					$styles .= ".gcp-post-content { " . implode( " ", $contentOtherStyles ) . " }";
				}
			
				// Apply the classes to the main carousel div.
				if ( ! empty( $atts['class'] ) ) {
					$carousel_class .= ' ' . esc_attr( $atts['class'] );
				}
			
				// Print out an inline stylesheet.
				if ( $styles ) {
					$ret .= "<style>{$styles}</style>";
				}

				if ( $navigation_buttons ) {
					wp_enqueue_style( 'dashicons' );
				}
				wp_enqueue_style( 'gcp-owl-carousel-css', plugins_url( 'carousel-anything/css/style.css', __FILE__ ), array(), VERSION_GAMBIT_CAROUSEL_ANYTHING );
				wp_enqueue_style( 'carousel-anything-owl', plugins_url( 'carousel-anything/css/owl.carousel.theme.style.css', __FILE__ ), array(), VERSION_GAMBIT_CAROUSEL_ANYTHING );
				wp_enqueue_script( 'carousel-anything-owl', plugins_url( 'carousel-anything/js/min/owl.carousel-min.js', __FILE__ ), array( 'jquery' ), '1.3.3', true );
				wp_enqueue_script( 'carousel-anything', plugins_url( 'carousel-anything/js/min/script-min.js', __FILE__ ), array( 'jquery', 'carousel-anything-owl' ), VERSION_GAMBIT_CAROUSEL_ANYTHING, true );
				wp_enqueue_style( 'carousel-anything-single-post', plugins_url( 'carousel-anything/css/single-post.css', __FILE__ ), array(), VERSION_GAMBIT_CAROUSEL_ANYTHING );
				
				while( have_posts() ) : the_post();
				
					// Process the featured image
					$thumbnail = '';
					$css = array();
					if ( in_array( 'featured_image', $postdata ) ) {
						$post_thumbnail_id = get_post_thumbnail_id();
						// Jetpack issue, Photon is not giving us the image dimensions
						// This snippet gets the dimensions for us
						add_filter( 'jetpack_photon_override_image_downsize', '__return_true' );
						$imageInfo = wp_get_attachment_image_src( $post_thumbnail_id, 'full' );
						remove_filter( 'jetpack_photon_override_image_downsize', '__return_true' );

						$attachmentImage = wp_get_attachment_image_src( $post_thumbnail_id, 'full' );
						$bgImageWidth = $imageInfo[1];
						$bgImageHeight = $imageInfo[2];
						$bgImage = $attachmentImage[0];
			
						if ( $post_thumbnail_id ) {
							if ( $atts['featured'] == 'bg' ) {
								$css[] = 'height: ' . $atts['content_height'] . 'px; background-repeat: no-repeat; background-size: cover; background-position: center;  background-image: url(' . $attachmentImage[0] . ');';
							}
							elseif ( $atts['featured'] == 'image' ) {
								$css[] = 'height: ' . $atts['content_height'] . 'px;';
								$thumbnail = '<a href="' . get_permalink() . '"><div id="post-image" style="height: ' . $atts['image_height'] . 'px; background-repeat: no-repeat; background-size: cover; background-position: center;  background-image: url(' . $attachmentImage[0] . ');"></div></a>';
							}
						}
						elseif ( $atts['featured'] == 'bg' ) {
							$css[] = 'height: ' . $atts['content_height'] . 'px;';
						}
					}
					
					// Render background color image, if a color is defined. Do not add if none.
					if ( ! empty( $atts['body_bg_color'] ) && in_array( 'featured_image', $postdata ) && $atts['featured'] == 'bg') {
						$css[] = 'background-color: ' . $atts['body_bg_color'] . ';';
					}

					// Render the entries
					if ( in_array( 'title', $postdata ) ) {
						$titleEntry = '<h3 class="gcp-post-title"><a href="' . get_permalink() . '">' . get_the_title() . '</a></h2>';
					}
				
					if ( in_array( 'author', $postdata ) ) {
						$authorEntry = '<p class="gcp-post-author"><a href="' . get_author_posts_url( get_the_author_meta( 'ID' ) ) . '">' . get_the_author() . '</a></p>';
					}
					
					if ( in_array( 'excerpt', $postdata ) ) {
						$theExcerpt = $this->limit_words( get_the_excerpt(), 0, $atts['excerpt_count'] );
						$theExcerptCount = $this->count_words( $theExcerpt );
		
						if ( $theExcerpt != '' && $theExcerptCount <= $atts['excerpt_count'] && $atts['ellipsis'] == 'true' ) {
							$theExcerpt .= '...';
						}
						$contentEntry = '<p class="gcp-post-content">' . $theExcerpt . '</p>';
					}

					// Assemble all css parameters into a single array.
					$mainStyling = ' style="' . implode( " ", $css ) . ' "';

					// Assemble the container of each pulled post.
					$postentries .= '<div class="gcp-post ' . esc_attr( $atts['alignment'] ) . ' gcp-design-' . esc_attr( $atts['featured'] ) . ' "' . $mainStyling . '>';
				
					if ( $atts['featured'] == 'image' ) {
						$postentries .= $thumbnail;
					}
				
					if ( in_array( 'featured_image', $postdata ) && in_array( 'title', $postdata ) && in_array( 'author', $postdata ) && count( $postdata ) == 3 && $atts['featured'] == 'bg' ) {
						$postentries .= '<div class="gcp-title-and-author ' . esc_attr( $atts['alignment'] ) . '">';
						$postentries .= $titleEntry . $authorEntry;
						$postentries .= '</div>';
					}
					elseif ( in_array( 'featured_image', $postdata ) && in_array( 'title', $postdata ) && in_array( 'excerpt', $postdata ) && count( $postdata ) == 3 && $atts['featured'] == 'bg' ) {
						$postentries .= '<div class="gcp-title-and-excerpt ' . esc_attr( $atts['alignment'] ) . '">';
						$postentries .= $titleEntry . $contentEntry;
						$postentries .= '</div>';
					}
					elseif ( in_array( 'featured_image', $postdata ) && in_array( 'author', $postdata ) && in_array( 'excerpt', $postdata ) && count( $postdata ) == 3 && $atts['featured'] == 'bg' ) {
						$postentries .= '<div class="gcp-author-and-excerpt ' . esc_attr( $atts['alignment'] ) . '">';
						$postentries .= $authorEntry . $contentEntry;
						$postentries .= '</div>';
					}
					elseif ( count( $postdata ) == 4 && $atts['featured'] == 'bg' ) {
						$postentries .= $titleEntry;
						$postentries .= '<div class="gcp-author-and-excerpt ' . esc_attr( $atts['alignment'] ) . '">';
						$postentries .= $authorEntry . $contentEntry;
						$postentries .= '</div>';
					}						
					else {
						$postentries .= $titleEntry . $authorEntry . $contentEntry;
					}

					$postentries .= '</div>';

				endwhile; 
			
				// Carousel html
				// $ret .= '<div class="customNavigation"><a class="btn prev">Previous</a><a class="btn next">Next</a></div>';
				$ret .= '<div id="' . esc_attr( $id ) . '" class="carousel-anything-container owl-carousel' . $carousel_class . '" ' .
						'data-items="' . esc_attr( $atts['items'] ) . '" ' .
						'data-scroll_per_page="' . esc_attr( $atts['scroll_per_page'] ) . '" ' .
						'data-autoplay="' . esc_attr( empty( $atts['autoplay'] ) || $atts['autoplay'] == '0' ? 'false' : $atts['autoplay'] ) . '" ' .
						'data-items-small="' . esc_attr( $atts['items_desktop_small'] ) . '" ' .
						'data-items-tablet="' . esc_attr( $atts['items_tablet'] ) . '" ' .
						'data-items-mobile="' . esc_attr( $atts['items_mobile'] ) . '" ' .
						'data-stop-on-hover="' . esc_attr( $atts['stop_on_hover'] ) . '" ' .
						'data-speed-scroll="' . esc_attr( $atts['speed_scroll'] ) . '" ' .
						'data-speed-rewind="' . esc_attr( $atts['speed_rewind'] ) . '" ' .
						'data-thumbnails="' . esc_attr( $atts['thumbnails'] ) . '" ' .
						'data-thumbnail-numbers="' . esc_attr( $atts['thumbnail_numbers'] ) . '" ' .
						'data-navigation="' . esc_attr( $navigation_buttons ? 'true' : 'false' ) . '" ' .
						'data-touchdrag="' . esc_attr( $atts['touchdrag'] ) . '" ' .
						'data-keyboard="' . esc_attr( $atts['keyboard'] ) . '">';
				$ret .=  $postentries . '</div>';
				
				endif; wp_reset_query();

			return $ret;
		}
		
	}
	new GambitCarouselPosts();
}