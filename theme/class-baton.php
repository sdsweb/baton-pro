<?php
/**
 * Baton - This class manages all functionality with our Baton theme.
 *
 * @class Baton
 * @author Slocum Studio
 * @version 1.1.1
 * @since 1.0.0
 */

// Bail if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

if ( ! class_exists( 'Baton' ) ) {
	class Baton {
		/**
		 * @var string, Current version number
		 */
		public $version = '1.1.1';

		/**
		 * @var string, Slug for Slocum Theme support
		 */
		public $theme_support_slug = 'slocum-theme';

		/**
		 * @var array, Array of Slocum Theme support
		 */
		public $theme_support = array();

		/**
		 * @var string
		 */
		public $note_sidebar_prefix = 'baton';

		/**
		 * @var array
		 */
		public $note_sidebar_ids = array();

		/**
		 * @var array, Organized by Note Sidebar location
		 */
		public $note_sidebar_ids_by_location = array();

		/**
		 * @var string
		 */
		public $note_post_id = '';

		/**
		 * @var array
		 */
		public $note_post_ids = array();

		/**
		 * @var array
		 */
		public $note_registered_sidebar_ids = array();

		/**
		 * @var array, Organized by Note Sidebar location
		 */
		public $note_post_ids_by_sidebar_id = array();

		/**
		 * @var string
		 */
		public $current_note_sidebar_id = false;

		/**
		 * @var string
         */
		public $page_template = null;

		/**
		 * @var Baton, Instance of the class
		 */
		private static $instance;

		/**
		 * @var EDD_SL_Theme_Updater, Instance of the EDD Software Licensing Theme Updater class
		 */
		protected $_updater;

		/**
		 * Function used to create instance of class.
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) )
				self::$instance = new self();

			return self::$instance;
		}


		/*
		 * This function sets up all of the actions and filters on instantiation.
		 */
		public function __construct() {
			add_action( 'after_setup_theme', array( $this, 'after_setup_theme' ), 20 ); // Register image sizes
			add_filter( 'theme_page_templates', array( $this, 'theme_page_templates' ) ); // Theme Page Templates
			add_action( 'after_switch_theme', array( $this, 'after_switch_theme' ), 1, 2 ); // Early
			add_action( 'init', array( $this, 'init' ), 20 ); // Adjust SDS Core Theme Options
			add_action( 'wp', array( $this, 'wp' ) ); // WordPress
			add_action( 'admin_init', array( $this, 'baton_admin_init' ), 20 ); // Adjust SDS Core Theme Options
			add_action( 'widgets_init', array( $this, 'widgets_init' ), 20 ); // Register sidebars
			add_action( 'customize_register', array( $this, 'customize_register' ) ); // Customizer Register
			add_action( 'customize_controls_enqueue_scripts', array( $this, 'customize_controls_enqueue_scripts' ), 25 ); // Customizer Controls Enqueue Scripts (after Note)
			add_action( 'customize_preview_init', array( $this, 'customize_preview_init' ) ); // Customizer Preview Initialization
			add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) ); // Used to enqueue editor styles based on post type
			add_action( 'do_meta_boxes', array( $this, 'do_meta_boxes' ), 1 ); // Do Meta Boxes (Early)
			add_action( 'wp_head', array( $this, 'wp_head' ), 1 ); // Add <meta> tags to <head> section
			add_action( 'tiny_mce_before_init', array( $this, 'tiny_mce_before_init' ), 10, 2 ); // Output TinyMCE Setup function
			add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) ); // Enqueue all stylesheets (Main Stylesheet, Fonts, etc...)
			add_filter( 'post_class', array( $this, 'post_class' ), 10, 3 ); // Post Class
			add_filter( 'the_content_more_link', array( $this, 'the_content_more_link' ) ); // Adjust default more link
			add_filter( 'dynamic_sidebar_params', array( $this, 'dynamic_sidebar_params' ) ); // Dynamic sidebar parameters (Note/Conductor Widgets)
			add_filter( 'edit_post_link', array( $this, 'edit_post_link' ) ); // Adjust CSS class on post edit links
			add_action( 'wp_footer', array( $this, 'wp_footer' ) ); // Responsive navigation functionality

			// Theme Options

			// Theme Updates
			add_action( 'admin_init', array( $this, 'admin_init' ) ); // Check and handle updates for theme

			// Note
			add_filter( 'note_tinymce_editor_types', array( $this, 'note_tinymce_editor_types' ) ); // Note Widget Editor Types
			add_filter( 'note_tinymce_editor_settings', array( $this, 'note_tinymce_editor_settings' ), 10, 2 ); // Note Widget TinyMCE Editor Settings
			add_filter( 'note_widget_template_types', array( $this, 'note_widget_template_types' ) ); // Note Widget Template Types
			add_filter( 'note_widget_templates', array( $this, 'note_widget_templates' ), 10, 2 ); // Note Widget Templates
			add_filter( 'note_widget_css_classes', array( $this, 'note_widget_css_classes' ), 10, 3 ); // Note Widget CSS Classes
			add_action( 'note_widget_title_before', array( $this, 'note_widget_title_before' ), 10, 3 ); // Note Title Before
			add_action( 'note_widget_template_before', array( $this, 'note_widget_template_before' ), 10, 7 ); // Note Template Before
			add_action( 'note_widget_template_after', array( $this, 'note_widget_template_after' ), 10, 7 ); // Note Template After

			// Note Sidebars
			add_filter( 'note_options_defaults', array( $this, 'note_options_defaults' ) ); // Note Option Defaults
			add_filter( 'note_sidebar_locations', array( $this, 'note_sidebar_locations' ) ); // Note Sidebar Locations
			add_filter( 'note_customizer_localize_sidebar_args', array( $this, 'note_customizer_localize_sidebar_args' ) ); // Note Customizer Localize Sidebar Arguments
			add_filter( 'note_customizer_sidebar_args_post_id', array( $this, 'note_customizer_sidebar_args_post_id' ), 10, 3 ); // Note Customizer Sidebar Arguments Post ID
			add_filter( 'note_sidebar_args', array( $this, 'note_sidebar_args' ), 10, 3 ); // Note Sidebar Arguments
			add_action( 'baton_content_wrapper_before', array( $this, 'baton_content_wrapper_before' ) ); // Baton Content Wrapper Before
			add_action( 'baton_content_wrapper_after', array( $this, 'baton_content_wrapper_after' ) ); // Baton Content Wrapper After

			// Conductor
			add_filter( 'conductor_content_layouts', array( $this, 'conductor_content_layouts' ) ); // Adjust Conductor content layouts
			add_filter( 'conductor_sidebar_args', array( $this, 'conductor_sidebar_args' ), 1, 4 ); // Adjust Conductor sidebar parameters (early)
			add_filter( 'conductor_widget_defaults', array( $this, 'conductor_widget_defaults' ), 10, 2 ); // Adjust Conductor widget defaults
			add_filter( 'conductor_widget_displays', array( $this, 'conductor_widget_displays' ), 10, 3 ); // Adjust Conductor Widget displays
			add_filter( 'conductor_widget_instance', array( $this, 'conductor_widget_instance' ), 20, 3 ); // Adjust callback functions upon Conductor Widget display
			add_action( 'conductor_widget_display_content', array( $this, 'conductor_widget_display_content' ), 10, 4 ); // Adjust content wrapper element position on Conductor Widgets
			add_filter( 'conductor_widget_wrapper_css_classes', array( $this, 'conductor_widget_wrapper_css_classes' ), 20, 5 ); // Adjust the CSS classes for the widget wrapper HTML element on Conductor Widgets (after Conductor)
			add_filter( 'conductor_widget_content_wrapper_html_element', array( $this, 'conductor_widget_content_wrapper_html_element' ) ); // Adjust the content wrapper HTML element on Conductor Widgets
			add_filter( 'conductor_widget_content_wrapper_css_classes', array( $this, 'conductor_widget_content_wrapper_css_classes' ), 10, 5 ); // Adjust the CSS classes for the content wrapper HTML element on Conductor Widgets
			add_filter( 'conductor_widget_before_widget_css_classes', array( $this, 'conductor_widget_before_widget_css_classes' ), 10, 5 ); // Adjust CSS classes on the before_widget wrapper element on Conductor Widgets
			add_filter( 'conductor_widget_featured_image_size', array( $this, 'conductor_widget_featured_image_size' ), 10, 2 ); // Adjust featured image size
			add_filter( 'conductor_content_wrapper_element_before', array( $this, 'conductor_content_wrapper_element_before' ) ); // Adjust Conductor opening wrapper element
			add_filter( 'conductor_content_wrapper_element_after', array( $this, 'conductor_content_wrapper_element_after' ) ); // Adjust Conductor closing wrapper element
			add_filter( 'conductor_content_element_before', array( $this, 'conductor_content_element_before' ) ); // Adjust Conductor content opening wrapper element
			add_filter( 'conductor_content_element_after', array( $this, 'conductor_content_element_after' ) ); // Adjust Conductor content closing wrapper element
			add_filter( 'conductor_primary_sidebar_element_before', array( $this, 'conductor_primary_sidebar_element_before' ) ); // Adjust Conductor primary sidebar opening wrapper element
			add_filter( 'conductor_primary_sidebar_element_after', array( $this, 'conductor_primary_sidebar_element_after' ) ); // Adjust Conductor primary sidebar closing wrapper element
			add_filter( 'conductor_secondary_sidebar_element_before', array( $this, 'conductor_secondary_sidebar_element_before' ) ); // Adjust Conductor secondary sidebar opening wrapper element
			add_filter( 'conductor_secondary_sidebar_element_after', array( $this, 'conductor_secondary_sidebar_element_after' ) ); // Adjust Conductor secondary sidebar closing wrapper element
			add_action( 'conductor_widget_pagination_before', array( $this, 'conductor_widget_pagination_before' ) ); // Output a wrapper element around Conductor Widget pagination
			add_action( 'conductor_widget_pagination_after', array( $this, 'conductor_widget_pagination_after' ) ); // Output a wrapper element around Conductor Widget pagination

			// Gravity Forms
			add_filter( 'gform_field_input', array( $this, 'gform_field_input' ), 10, 5 ); // Add placholder to newsletter form
			add_filter( 'gform_confirmation', array( $this, 'gform_confirmation' ), 10, 4 ); // Change confirmation message on newsletter form

			// WooCommerce
			add_filter( 'woocommerce_product_settings', array( $this, 'woocommerce_product_settings' ) ); // WooCommerce - Product Settings
			add_filter( 'woocommerce_get_image_size_shop_catalog', array( $this, 'woocommerce_get_image_size_shop_catalog' ) ); // WooCommerce - Get Image Size - Shop Catelog
			add_filter( 'woocommerce_get_image_size_shop_single', array( $this, 'woocommerce_get_image_size_shop_single' ) ); // WooCommerce - Get Image Size - Shop Single
			add_filter( 'woocommerce_get_image_size_shop_thumbnail', array( $this, 'woocommerce_get_image_size_shop_thumbnail' ) ); // WooCommerce - Get Image Size - Shop Thumbnail
			remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper' ); // Remove WooCommerce - Before Main Content
			add_action( 'woocommerce_before_main_content', array( $this, 'woocommerce_before_main_content' ) ); // WooCommerce - Before Main Content
			add_action( 'woocommerce_before_main_content', array( $this, 'woocommerce_before_main_content_article_content_wrapper_before' ), 30 ); // WooCommerce - Before Main Content (Article Content Wrapper Before)
			add_filter( 'loop_shop_per_page', array( $this, 'loop_shop_per_page' ) ); // WooCommerce - Loop Shop Per Page
			add_filter( 'loop_shop_columns', array( $this, 'loop_shop_columns' ) ); // WooCommerce - Loop Shop Columns
			add_action( 'woocommerce_before_shop_loop_item_title', array( $this, 'woocommerce_before_shop_loop_item_title_thumbnail_wrapper_before' ), 5 ); // WooCommerce - Before Shop Loop Item Title (Thumbnail Wrapper Before)
			add_action( 'woocommerce_before_shop_loop_item_title', array( $this, 'woocommerce_before_shop_loop_item_title_thumbnail_wrapper_after' ), 15 ); // WooCommerce - Before Shop Loop Item Title (Thumbnail Wrapper After)
			add_action( 'woocommerce_shop_loop_item_title', array( $this, 'woocommerce_shop_loop_item_title' ), 5 ); // WooCommerce - Shop Loop Item Title
			add_action( 'woocommerce_after_shop_loop_item_title', array( $this, 'woocommerce_after_shop_loop_item_title' ), 15 ); // WooCommerce - After Shop Loop Item Title
			add_action( 'woocommerce_after_shop_loop_item', array( $this, 'woocommerce_after_shop_loop_item_article_content_wrapper_before' ), 8 ); // WooCommerce - After Shop Loop Item (Article Content Wrapper Before)
			add_action( 'woocommerce_after_shop_loop_item', array( $this, 'woocommerce_after_shop_loop_item_article_content_wrapper_after' ), 15 ); // WooCommerce - After Shop Loop Item (Article Content Wrapper After)
			add_action( 'woocommerce_before_single_product_summary', array( $this, 'woocommerce_before_single_product_summary' ), 5 ); // WooCommerce - Before Single Product Summary
			add_filter( 'woocommerce_product_thumbnails_columns', array( $this, 'woocommerce_product_thumbnails_columns' ) ); // WooCommerce - Single Product Thumbnails Columns
			add_action( 'woocommerce_before_single_product_summary', array( $this, 'woocommerce_before_single_product_summary_baton_col_wrappers' ), 30 ); // WooCommerce - Before Single Product Summary (Baton Column Wrapper Before)
			add_action( 'woocommerce_single_product_summary', array( $this, 'woocommerce_single_product_summary' ), 2 ); // WooCommerce - Single Product Summary
			add_action( 'woocommerce_single_product_summary', array( $this, 'woocommerce_single_product_summary_article_content_wrapper_after' ), 70 ); // WooCommerce - Single Product Summary (Article Content Wrapper After)
			add_action( 'woocommerce_after_single_product_summary', array( $this, 'woocommerce_after_single_product_summary' ), 5 ); // WooCommerce - After Single Product Summary
			add_filter( 'woocommerce_output_related_products_args', array( $this, 'woocommerce_output_related_products_args' ) ); // WooCommerce - Related Products Arguments
			remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 ); // Remove WooCommerce - Related Products
			add_action( 'woocommerce_after_single_product', 'woocommerce_output_related_products' ); // WooCommerce - Related Products
			add_filter( 'single_product_archive_thumbnail_size', array( $this, 'single_product_archive_thumbnail_size' ) ); // WooCommerce - Single Product Archive Thumbnail Size
			remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 ); // Remove WooCommerce - Sidebar
			remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end' ); // Remove WooCommerce - After Main Content
			add_action( 'woocommerce_after_main_content', array( $this, 'woocommerce_after_main_content' ) ); // WooCommerce - After Main Content
		}


		/************************************************************************************
		 *    Functions to correspond with actions above (attempting to keep same order)    *
		 ************************************************************************************/

		/**
		 * This function adds images sizes to WordPress.
		 */
		public function after_setup_theme() {
			global $content_width, $sds_theme_options;

			// Grab the Baton Customizer instance
			$baton_customizer = Baton_Customizer_Instance();

			// Set the Content Width for embedded items
			if ( ! isset( $content_width ) )
				$content_width = 1272;

			// Determine if the max width theme mod is set
			if ( ( $max_width = $baton_customizer->get_theme_mod( 'baton_max_width' ) ) && $max_width !== $content_width )
				$content_width = $max_width;

			// Change default core markup for search form, comment form, and comments, etc... to HTML5
			add_theme_support( 'html5', array(
				'search-form',
				'comment-form',
				'comment-list'
			) );

			// Custom Background (color/image)
			add_theme_support( 'custom-background', array(
				'default-color' => $baton_customizer->get_current_color_scheme_default( 'background_color', '#f1f5f9' )
			) );

			// Yoast WordPress SEO Breadcrumbs (automatically enables breadcrumbs)
			//add_theme_support( 'yoast-seo-breadcrumbs', true );

			// Add Woocommerce Support
			add_theme_support( 'woocommerce' );

			// Theme textdomain
			load_theme_textdomain( 'baton-pro', get_template_directory() . '/languages' );

			add_image_size( 'baton-600x400', 600, 400, true ); // Portfolio Archive Page Featured Image Size
			add_image_size( 'baton-1200x9999', 1200, 9999, false ); // Single Post/Page Featured Image Size
			add_image_size( 'baton-conductor-small', 375, 9999, false ); // Conductor Small Widgets
			add_image_size( 'baton-conductor-small-cropped', 375, 250, true ); // Conductor Small Widgets (cropped)
			add_image_size( 'baton-conductor-medium', 760, 9999, false ); // Conductor Medium Widgets
			add_image_size( 'baton-conductor-medium-cropped', 760, 500, true ); // Conductor Medium Widgets (cropped)
			add_image_size( 'baton-conductor-large', 1200, 9999, false ); // Conductor Large Widgets
			add_image_size( 'baton-conductor-large-cropped', 1200, 1200, false ); // Conductor Large Widgets (cropped)

			// Register menus which are used in Baton
			register_nav_menus( array(
				'secondary_nav' => __( 'Secondary Navigation', 'baton-pro' ),
			) );

			// Unregister menus which are registered in SDS Core
			unregister_nav_menu( 'top_nav' );

			// Slocum Theme Extender Support
			add_theme_support( $this->theme_support_slug, apply_filters( 'baton_slocum_theme_support', array(
				// Fonts (adjustable font elements and properties)
				'fonts' => array(
					// Site Title
					'site_title' => array(
						// Font Size
						'font_size' => array(
							'default' => 24, // Default font size in px
							'min' => 18, // Minimum font size in px
							'max' => 72, // Maximum font size in px
							// CSS Properties
							'css' => array(
								// Other CSS properties that should be adjusted with the font size and their unit vale
								'properties' => array(
									// Property => unit (px, em, etc...)
									'line-height' => 'px'
								)
							)
						),
						// Font Family
						'font_family' => array( 'default' => 'Martel Sans' ),
						// Letter Spacing
						'letter_spacing' => array(
							'default' => 4, // Default letter spacing in px
							'min' => 0, // Minimum letter spacing in px
							'max' => 10, // Maximum letter spacing in px
						),
						// CSS Properties
						'css' => array(
							// CSS Selectors (array of selectors to match this element)
							'selector' => array( '#title' ),
							// Ignore using the default CSS selector for this element
							'ignore_default_selector' => true
						)
					),
					// Tagline
					'tagline' => array(
						// Font Size
						'font_size' => array(
							'default' => 12,
							'min' => 10,
							'css' => array(
								'properties' => array( 'line-height' => 'px' )
							)
						),
						// Font Family
						'font_family' => array( 'default' => 'Martel Sans' ),
						// Letter Spacing
						'letter_spacing' => array(
							'default' => 1, // Default letter spacing in px
							'min' => 0, // Minimum letter spacing in px
							'max' => 10, // Maximum letter spacing in px
						),
						// CSS Properties
						'css' => array(
							'selector' => array( '#slogan' ),
							'ignore_default_selector' => true
						)
					),
					// Navigation
					'navigation' => array(
						// Primary Navigation
						'primary_nav' => array(
							// Font Size
							'font_size' => array(
								'default' => 14
							),
							// Font Family
							'font_family' => array( 'default' => 'Martel Sans' ),
							// CSS Properties
							'css' => array(
								// Ignore using the default CSS selector for this element
								'ignore_default_selector' => true,
								// CSS Selectors (array of selectors to match this element)
								'selector' => array( 'nav .primary-nav' )
							)
						),
						// Secondary Navigation
						'secondary_nav' => array(
							// Font Size
							'font_size' => array(
								'default' => 12
							),
							// Font Family
							'font_family' => array( 'default' => 'Martel Sans' ),
							// CSS Properties
							'css' => array(
								'ignore_default_selector' => true,
								'selector' => array( '#secondary-nav' )
							)
						)
					),
					// Headings
					'headings' => array(
						// Heading 1
						'h1' => array(
							// Font Size
							'font_size' => array(
								'default' => 40,
								'min' => 24,
								'max' => 72
							),
							// Font Family
							'font_family' => array( 'default' => 'Lato' )
						),
						// Heading 2
						'h2' => array(
							// Font Size
							'font_size' => array(
								'default' => 34,
								'min' => 22,
								'max' => 64
							),
							// Font Family
							'font_family' => array( 'default' => 'Lato' )
						),
						// Heading 3
						'h3' => array(
							// Font Size
							'font_size' => array(
								'default' => 31,
								'min' => 18,
								'max' => 56
							),
							// Font Family
							'font_family' => array( 'default' => 'Lato' )
						),
						// Heading 4
						'h4' => array(
							// Font Size
							'font_size' => array(
								'default' => 26,
								'min' => 16,
								'max' => 48
							),
							// Font Family
							'font_family' => array( 'default' => 'Lato' )
						),
						// Heading 5
						'h5' => array(
							// Font Size
							'font_size' => array(
								'default' => 22,
								'min' => 12,
								'max' => 36
							),
							// Font Family
							'font_family' => array( 'default' => 'Lato' )
						),
						// Heading 6
						'h6' => array(
							// Font Size
							'font_size' => array(
								'default' => 18,
								'min' => 10,
								'max' => 32
							),
							// Font Family
							'font_family' => array( 'default' => 'Lato' )
						),
					),
					// Body (content)
					'body' => array(
						// Font Size
						'font_size' => array(
							'default' => 18
						),
						// Line Height
						'line_height' => array(
							'default' => 32
						),
						// Font Family
						'font_family' => array(
							'default' => 'Lato'
						),
						// CSS Properties
						'css' => array(
							'ignore_default_selector' => true,
							'selector' => array( '.content-container' )
						)
					),
					// Widget
					'widget' => array(
						// Font Size
						'font_size' => array(
							'default' => 15
						),
						// Font Family
						'font_family' => array( 'default' => 'Lato' ),
						// Widget Title
						'title' => array(
							// Font Size
							'font_size' => array(
								'default' => 14
							),
							// Font Family
							'font_family' => array( 'default' => 'Lato' )
						)
					),
					// Conductor
					// TODO
					/*'conductor' => array(
						// Large Widget Display
						'large' => array(
							// Labels
							'labels' => array(
								// Customizer Section
								'section' => __( 'Conductor: Large Widget Display', 'baton-pro' )
							),
							// Title (Post Title)
							'title' => array(
								// Labels
								'labels' => array(
									// Customizer Control (prefix)
									'control' => __( 'Title', 'baton-pro' )
								),
								// Font Size
								'font_size' => array(
									'default' => 60,
									'min' => 24,
									'max' => 96
								),
								// Font Family
								'font_family' => array( 'default' => 'Open Sans' ),
								// CSS Properties
								'css' => array(
									'selector' => array( '.conductor-widget .conductor-widget-large .post-title', '.conductor-widget .conductor-widget-single-large .post-title' )
								)
							),
							// Author Byline
							'author_byline' => array(
								// Labels
								'labels' => array(
									// Customizer Control (prefix)
									'control' => __( 'Author Byline', 'baton-pro' )
								),
								// Font Size
								'font_size' => array(
									'default' => 15,
									'min' => 10,
									'max' => 36
								),
								// Font Family
								'font_family' => array( 'default' => 'Open Sans' ),
								// CSS Properties
								'css' => array(
									'selector' => array( '.conductor-widget .conductor-widget-large .post-content .post-author', '.conductor-widget .conductor-widget-single-large .post-content .post-author' )
								)
							),
							// Content
							'content' => array(
								// Labels
								'labels' => array(
									// Customizer Control (prefix)
									'control' => __( 'Content', 'baton-pro' )
								),
								// Font Size
								'font_size' => array(
									'default' => 18,
									'min' => 12,
									'max' => 42
								),
								// Font Family
								'font_family' => array( 'default' => 'Open Sans' ),
								// CSS Properties
								'css' => array(
									'selector' => array( '.conductor-widget .conductor-widget-large .post-content p', '.conductor-widget .conductor-widget-single-large .post-content p' )
								)
							),
							// Read More
							'read_more' => array(
								// Labels
								'labels' => array(
									// Customizer Control (prefix)
									'control' => __( 'Read More', 'baton-pro' )
								),
								// Font Size
								'font_size' => array(
									'default' => 18,
									'min' => 12,
									'max' => 42
								),
								// Font Family
								'font_family' => array( 'default' => 'Open Sans' ),
								// CSS Properties
								'css' => array(
									'selector' => array( '.conductor-widget .conductor-widget-large .more-link', '.conductor-widget .conductor-widget-single-large .more-link' )
								)
							)
						),
						// Medium Widget Display
						'medium' => array(
							// Labels
							'labels' => array(
								// Customizer Section
								'section' => __( 'Conductor: Medium Widget Display', 'baton-pro' )
							),
							// Title (Post Title)
							'title' => array(
								// Labels
								'labels' => array(
									// Customizer Control (prefix)
									'control' => __( 'Title', 'baton-pro' )
								),
								// Font Size
								'font_size' => array(
									'default' => 48,
									'min' => 22,
									'max' => 72
								),
								// Font Family
								'font_family' => array( 'default' => 'Open Sans' ),
								// CSS Properties
								'css' => array(
									'selector' => array( '.conductor-widget .conductor-widget-medium .post-title', '.conductor-widget .conductor-widget-single-medium .post-title' )
								)
							),
							// Author Byline
							'author_byline' => array(
								// Labels
								'labels' => array(
									// Customizer Control (prefix)
									'control' => __( 'Author Byline', 'baton-pro' )
								),
								// Font Size
								'font_size' => array(
									'default' => 15,
									'min' => 10,
									'max' => 36
								),
								// Font Family
								'font_family' => array( 'default' => 'Open Sans' ),
								// CSS Properties
								'css' => array(
									'selector' => array( '.conductor-widget .conductor-widget-medium .post-content .post-author', '.conductor-widget .conductor-widget-single-medium .post-content .post-author' )
								)
							),
							// Content
							'content' => array(
								// Labels
								'labels' => array(
									// Customizer Control (prefix)
									'control' => __( 'Content', 'baton-pro' )
								),
								// Font Size
								'font_size' => array(
									'default' => 18,
									'min' => 12,
									'max' => 42
								),
								// Font Family
								'font_family' => array( 'default' => 'Open Sans' ),
								// CSS Properties
								'css' => array(
									'selector' => array( '.conductor-widget .conductor-widget-medium .post-content p', '.conductor-widget .conductor-widget-single-medium .post-content p' )
								)
							),
							// Read More
							'read_more' => array(
								// Labels
								'labels' => array(
									// Customizer Control (prefix)
									'control' => __( 'Read More', 'baton-pro' )
								),
								// Font Size
								'font_size' => array(
									'default' => 18,
									'min' => 12,
									'max' => 42
								),
								// Font Family
								'font_family' => array( 'default' => 'Open Sans' ),
								// CSS Properties
								'css' => array(
									'selector' => array( '.conductor-widget .conductor-widget-medium .more-link', '.conductor-widget .conductor-widget-single-medium .more-link' )
								)
							)
						),
						// Small Widget Display
						'small' => array(
							// Labels
							'labels' => array(
								// Customizer Section
								'section' => __( 'Conductor: Small Widget Display', 'baton-pro' )
							),
							// Title (Post Title)
							'title' => array(
								// Labels
								'labels' => array(
									// Customizer Control (prefix)
									'control' => __( 'Title', 'baton-pro' )
								),
								// Font Size
								'font_size' => array(
									'default' => 38,
									'min' => 18,
									'max' => 64
								),
								// Font Family
								'font_family' => array( 'default' => 'Open Sans' ),
								// CSS Properties
								'css' => array(
									'selector' => array( '.conductor-widget .conductor-widget-small .post-title', '.conductor-widget .conductor-widget-single-small .post-title' )
								)
							),
							// Author Byline
							'author_byline' => array(
								// Labels
								'labels' => array(
									// Customizer Control (prefix)
									'control' => __( 'Author Byline', 'baton-pro' )
								),
								// Font Size
								'font_size' => array(
									'default' => 15,
									'min' => 10,
									'max' => 36
								),
								// Font Family
								'font_family' => array( 'default' => 'Open Sans' ),
								// CSS Properties
								'css' => array(
									'selector' => array( '.conductor-widget .conductor-widget-small .post-content .post-author', '.conductor-widget .conductor-widget-single-small .post-content .post-author' )
								)
							),
							// Content
							'content' => array(
								// Labels
								'labels' => array(
									// Customizer Control (prefix)
									'control' => __( 'Content', 'baton-pro' )
								),
								// Font Size
								'font_size' => array(
									'default' => 18,
									'min' => 12,
									'max' => 42
								),
								// Font Family
								'font_family' => array( 'default' => 'Open Sans' ),
								// CSS Properties
								'css' => array(
									'selector' => array( '.conductor-widget .conductor-widget-small .post-content p', '.conductor-widget .conductor-widget-single-small .post-content p' )
								)
							),
							// Read More
							'read_more' => array(
								// Labels
								'labels' => array(
									// Customizer Control (prefix)
									'control' => __( 'Read More', 'baton-pro' )
								),
								// Font Size
								'font_size' => array(
									'default' => 18,
									'min' => 12,
									'max' => 42
								),
								// Font Family
								'font_family' => array( 'default' => 'Open Sans' ),
								// CSS Properties
								'css' => array(
									'selector' => array( '.conductor-widget .conductor-widget-small .more-link', '.conductor-widget .conductor-widget-single-small .more-link' )
								)
							)
						),
						// List Widget Display
						'list' => array(
							// Labels
							'labels' => array(
								// Customizer Section
								'section' => __( 'Conductor: List Widget Display', 'baton-pro' )
							),
							// Title (Post Title)
							'title' => array(
								// Labels
								'labels' => array(
									// Customizer Control (prefix)
									'control' => __( 'Title', 'baton-pro' )
								),
								// Font Size
								'font_size' => array(
									'default' => 48,
									'min' => 22,
									'max' => 72
								),
								// Font Family
								'font_family' => array( 'default' => 'Open Sans' ),
								// CSS Properties
								'css' => array(
									'selector' => array( '.conductor-widget.conductor-list-item .post-title', '.conductor-widget.conductor-list-item .content > .post-title' )
								)
							),
							// Author Byline
							'author_byline' => array(
								// Labels
								'labels' => array(
									// Customizer Control (prefix)
									'control' => __( 'Author Byline', 'baton-pro' )
								),
								// Font Size
								'font_size' => array(
									'default' => 15,
									'min' => 10,
									'max' => 36
								),
								// Font Family
								'font_family' => array( 'default' => 'Open Sans' ),
								// CSS Properties
								'css' => array(
									'selector' => array( '.conductor-widget.conductor-list-item .post-content .post-author' )
								)
							),
							// Content
							'content' => array(
								// Labels
								'labels' => array(
									// Customizer Control (prefix)
									'control' => __( 'Content', 'baton-pro' )
								),
								// Font Size
								'font_size' => array(
									'default' => 18,
									'min' => 12,
									'max' => 42
								),
								// Font Family
								'font_family' => array( 'default' => 'Open Sans' ),
								// CSS Properties
								'css' => array(
									'selector' => array( '.conductor-widget.conductor-list-item .post-content p' )
								)
							),
							// Read More
							'read_more' => array(
								// Labels
								'labels' => array(
									// Customizer Control (prefix)
									'control' => __( 'Read More', 'baton-pro' )
								),
								// Font Size
								'font_size' => array(
									'default' => 18,
									'min' => 12,
									'max' => 42
								),
								// Font Family
								'font_family' => array( 'default' => 'Open Sans' ),
								// CSS Properties
								'css' => array(
									'selector' => array( '.conductor-widget.conductor-list-item .more-link' )
								)
							)
						),
						// Table Widget Display
						'table' => array(
							// Labels
							'labels' => array(
								// Customizer Section
								'section' => __( 'Conductor: Table Widget Display', 'baton-pro' )
							),
							// Title (Post Title)
							'title' => array(
								// Labels
								'labels' => array(
									// Customizer Control (prefix)
									'control' => __( 'Title', 'baton-pro' )
								),
								// Font Size
								'font_size' => array(
									'default' => 38,
									'min' => 18,
									'max' => 64
								),
								// Font Family
								'font_family' => array( 'default' => 'Open Sans' ),
								// CSS Properties
								'css' => array(
									'selector' => array( '.conductor-widget .conductor-table .post-title' )
								)
							),
							// Author Byline
							'author_byline' => array(
								// Labels
								'labels' => array(
									// Customizer Control (prefix)
									'control' => __( 'Author Byline', 'baton-pro' )
								),
								// Font Size
								'font_size' => array(
									'default' => 15,
									'min' => 10,
									'max' => 36
								),
								// Font Family
								'font_family' => array( 'default' => 'Open Sans' ),
								// CSS Properties
								'css' => array(
									'selector' => array( '.conductor-widget .conductor-table .post-author' )
								)
							),
							// Content
							'content' => array(
								// Labels
								'labels' => array(
									// Customizer Control (prefix)
									'control' => __( 'Content', 'baton-pro' )
								),
								// Font Size
								'font_size' => array(
									'default' => 18,
									'min' => 12,
									'max' => 42
								),
								// Font Family
								'font_family' => array( 'default' => 'Open Sans' ),
								// CSS Properties
								'css' => array(
									'selector' => array( '.conductor-widget .conductor-table .post-content p' )
								)
							),
							// Read More
							'read_more' => array(
								// Labels
								'labels' => array(
									// Customizer Control (prefix)
									'control' => __( 'Read More', 'baton-pro' )
								),
								// Font Size
								'font_size' => array(
									'default' => 18,
									'min' => 12,
									'max' => 42
								),
								// Font Family
								'font_family' => array( 'default' => 'Open Sans' ),
								// CSS Properties
								'css' => array(
									'selector' => array( '.conductor-widget .conductor-table .more-link' )
								)
							)
						),
						// Grid Widget Display
						'grid' => array(
							// Labels
							'labels' => array(
								// Customizer Section
								'section' => __( 'Conductor: Grid Widget Display', 'baton-pro' )
							),
							// Title (Post Title)
							'title' => array(
								// Labels
								'labels' => array(
									// Customizer Control (prefix)
									'control' => __( 'Title', 'baton-pro' )
								),
								// Font Size
								'font_size' => array(
									'default' => 38,
									'min' => 18,
									'max' => 64
								),
								// Font Family
								'font_family' => array( 'default' => 'Open Sans' ),
								// CSS Properties
								'css' => array(
									'selector' => array( '.conductor-widget.conductor-grid-item .post-title' )
								)
							),
							// Author Byline
							'author_byline' => array(
								// Labels
								'labels' => array(
									// Customizer Control (prefix)
									'control' => __( 'Author Byline', 'baton-pro' )
								),
								// Font Size
								'font_size' => array(
									'default' => 15,
									'min' => 10,
									'max' => 36
								),
								// Font Family
								'font_family' => array( 'default' => 'Open Sans' ),
								// CSS Properties
								'css' => array(
									'selector' => array( '.conductor-widget.conductor-grid-item .post-content .post-author' )
								)
							),
							// Content
							'content' => array(
								// Labels
								'labels' => array(
									// Customizer Control (prefix)
									'control' => __( 'Content', 'baton-pro' )
								),
								// Font Size
								'font_size' => array(
									'default' => 18,
									'min' => 12,
									'max' => 42
								),
								// Font Family
								'font_family' => array( 'default' => 'Open Sans' ),
								// CSS Properties
								'css' => array(
									'selector' => array( '.conductor-widget.conductor-grid-item .post-content p' )
								)
							),
							// Read More
							'read_more' => array(
								// Labels
								'labels' => array(
									// Customizer Control (prefix)
									'control' => __( 'Read More', 'baton-pro' )
								),
								// Font Size
								'font_size' => array(
									'default' => 18,
									'min' => 12,
									'max' => 42
								),
								// Font Family
								'font_family' => array( 'default' => 'Open Sans' ),
								// CSS Properties
								'css' => array(
									'selector' => array( '.conductor-widget.conductor-grid-item .more-link' )
								)
							)
						),
						// Portfolio Widget Display
						'portfolio' => array(
							// Labels
							'labels' => array(
								// Customizer Section
								'section' => __( 'Conductor: Portfolio Widget Display', 'baton-pro' )
							),
							// Title (Post Title)
							'title' => array(
								// Labels
								'labels' => array(
									// Customizer Control (prefix)
									'control' => __( 'Title', 'baton-pro' )
								),
								// Font Size
								'font_size' => array(
									'default' => 16,
									'min' => 12,
									'max' => 64
								),
								// Font Family
								'font_family' => array( 'default' => 'Open Sans' ),
								// CSS Properties
								'css' => array(
									'selector' => array( '.conductor-widget .conductor-widget-portfolio .post-title, .conductor-widget .conductor-widget-portfolio-single .post-title' )
								)
							),
							// Author Byline
							'author_byline' => array(
								// Labels
								'labels' => array(
									// Customizer Control (prefix)
									'control' => __( 'Author Byline', 'baton-pro' )
								),
								// Font Size
								'font_size' => array(
									'default' => 14,
									'min' => 10,
									'max' => 36
								),
								// Font Family
								'font_family' => array( 'default' => 'Open Sans' ),
								// CSS Properties
								'css' => array(
									'selector' => array( '.conductor-widget .conductor-widget-portfolio .post-content .post-author, .conductor-widget .conductor-widget-portfolio-single .post-content .post-author' )
								)
							),
							// Content
							'content' => array(
								// Labels
								'labels' => array(
									// Customizer Control (prefix)
									'control' => __( 'Content', 'baton-pro' )
								),
								// Font Size
								'font_size' => array(
									'default' => 15,
									'min' => 12,
									'max' => 42
								),
								// Font Family
								'font_family' => array( 'default' => 'Open Sans' ),
								// CSS Properties
								'css' => array(
									'selector' => array( '.conductor-widget .conductor-widget-portfolio .post-content p, .conductor-widget .conductor-widget-portfolio-single .post-content p' )
								)
							),
							// Read More
							'read_more' => array(
								// Labels
								'labels' => array(
									// Customizer Control (prefix)
									'control' => __( 'Read More', 'baton-pro' )
								),
								// Font Size
								'font_size' => array(
									'default' => 15,
									'min' => 12,
									'max' => 42
								),
								// Font Family
								'font_family' => array( 'default' => 'Open Sans' ),
								// CSS Properties
								'css' => array(
									'selector' => array( '.conductor-widget .conductor-widget-portfolio .more-link, .conductor-widget .conductor-widget-portfolio-single .more-link' )
								)
							)
						)
					)*/
				)
			) ) );

			// Store theme support in class
			$this->theme_support = get_theme_support( $this->theme_support_slug );
			$this->theme_support = $this->theme_support[0]; // Remove the 0 index
		}

		/**
		 * This function adjusts the theme page templates.
		 */
		public function theme_page_templates( $page_templates ) {
			// Bail if Beaver Builder exists or we should enable [allow] the Beaver Builder template [to remain in the list of page templates]
			if ( class_exists( 'FLBuilderLoader' ) || apply_filters( 'baton_enable_beaver_builder_page_template', false ) )
				return $page_templates;

			// Remove the Beaver Builder template
			if ( array_key_exists( 'template-beaver-builder.php', $page_templates ) )
				unset( $page_templates['template-beaver-builder.php'] );

			return $page_templates;
		}

		/**
		 * This function adds an admin notice upon theme switch/activation.
		 */
		public function after_switch_theme( $old_theme_name, $old_theme = false ) {
			// Admin notices
			add_action( 'admin_notices', array( $this, 'admin_notices' ) );

			/*
			 * Conductor
			 */

			// Update Conductor Widgets
			$this->update_conductor_widgets();
		}

		/**
		 * This function outputs admin notices.
		 */
		public function admin_notices() {
			printf( __( '<div class="updated"><p>Welcome to Baton Pro! Get started by visiting the <a href="%1$s">Customizer</a>!</p></div>', 'baton-pro' ), esc_url( wp_customize_url() ) );
		}

		/**
		 * This function sets up properties on this class and allows other plugins and themes
		 * to adjust those properties by filtering.
		 */
		public function init() {
			// Update Conductor Widgets
			$this->update_conductor_widgets();
		}

		/**
		 * This function runs after the WordPress object it setup.
		 */
		public function wp() {
			// Store the current page template on the class
			$this->page_template = get_post_meta( get_queried_object_id(), '_wp_page_template', true );

			// Hook into get_post_metadata
			add_filter( 'get_post_metadata', array( $this, 'get_post_metadata' ), 10, 4 );
		}

		/**
		 * This function adjusts SDS Core Theme Options. There isn't a function in the Settings API
		 * to remove setting sections/fields so we have to manually unset them.
		 */
		public function baton_admin_init() {
			global $wp_settings_sections, $wp_settings_fields;

			// Remove the featured image size section
			unset( $wp_settings_sections['sds-theme-options[general]']['sds_theme_options_featured_image_size_section'] );

			// Remove the featured image size field
			unset( $wp_settings_fields['sds-theme-options[general]']['sds_theme_options_featured_image_size_section']['sds_theme_options_featured_image_size_field'] );

			// Hook into update
			add_filter( 'update_post_metadata', array( $this, 'update_post_metadata' ), 10, 4 );
		}

		/**
		 * This function registers/unregisters extra sidebars that are not used in this theme.
		 */
		public function widgets_init() {
			global $wp_registered_sidebars;

			// Unregister unused sidebars which are registered in SDS Core
			unregister_sidebar( 'front-page-slider-sidebar' );
			unregister_sidebar( 'header-call-to-action-sidebar' );
			unregister_sidebar( 'secondary-sidebar' );

			/*
			 * Adjust before_widget and after_widget wrapper elements for sidebars which are
			 * registered in SDS Core (changing from <section> elements to <div> elements).
			 */

			// If we have registered sidebars
			if ( ! empty( $wp_registered_sidebars ) ) {
				// SDS Core Sidebar IDs
				$sds_core_sidebar_ids = array(
					'primary-sidebar',
					'secondary-sidebar',
					'front-page-sidebar',
					'after-posts-sidebar',
					'footer-sidebar',
					'copyright-area-sidebar'
				);

				// Loop through registered sidebars
				foreach ( $wp_registered_sidebars as $sidebar_id => &$sidebar )
					// Make sure this is a sidebar registered in SDS Core
					if ( in_array( $sidebar_id, $sds_core_sidebar_ids ) ) {
						// before_widget
						$sidebar['before_widget'] = str_replace( 'section', 'div', $sidebar['before_widget'] );

						// after_widget
						$sidebar['after_widget'] = str_replace( 'section', 'div', $sidebar['after_widget'] );

						// Front Page Sidebar
						if ( $sidebar_id === 'front-page-sidebar' ) {
							// description
							$sidebar['description'] = __( '*This widget area is only displayed if a Front Page is selected via Settings &gt; Reading in the Dashboard.* This widget area is displayed on the Front Page and will replace the Front Page content.', 'baton-pro' );

							// before_widget
							$sidebar['before_widget'] .= '<div class="in front-page-widget-in cf">';

							// after_widget
							$sidebar['after_widget'] = '</div>' . $sidebar['after_widget'];
						}

						// Footer Sidebar (adjust for flexbox display)
						if ( $sidebar_id === 'footer-sidebar' )
							// before_widget
							$sidebar['before_widget'] = str_replace( 'class="', 'class="baton-col baton-col-footer-widget ', $sidebar['before_widget'] );
					}
			}
		}

		/**
		 * This function registers components for use in the Customizer.
		 */
		public function customize_register( $wp_customize ) {
			// Bail if Note doesn't exist
			if ( ! class_exists( 'Note' ) )
				return;

			/**
			 * Note
			 */

			// Grab the Note options defaults
			$note_option_defaults = Note_Options::get_option_defaults();

			// Grab the Note Sidebars instance
			$note_sidebars = Note_Sidebars();


			/*
			 * Note Baton
			 */

			// Setting (data is sanitized upon update_option() call using the sanitize function in Note_Admin_Options)
			$wp_customize->add_setting(
				new WP_Customize_Setting( $wp_customize,
					'note[baton]', // IDs can have nested array keys
					array(
						'default' => $note_option_defaults['baton'],
						'type' => 'option',
						'sanitize_callback' => array( $this, 'note_baton_sanitize_callback' ),
						'sanitize_js_callback' => array( $note_sidebars, 'sanitize_js_callback' ) // Note Sidebars json_encode()
					)
				)
			);

			// Control
			$wp_customize->add_control(
				new WP_Customize_Control(
					$wp_customize,
					'note_baton',
					array(
						'label' => __( 'Note Baton', 'baton-pro' ),
						'section' => 'note_sidebars',
						'settings' => 'note[baton]',
						'priority' => 20, // After Note Sidebars
						'input_attrs' => array(
							'class' => 'note-baton note-hidden'
						),
						'active_callback' => '__return_false' // Hide this control by default
					)
				)
			);
		}

		/**
		 * This function enqueues scripts within the Customizer.
		 */
		public function customize_controls_enqueue_scripts() {
			// Bail if Note doesn't exist
			if ( ! class_exists( 'Note' ) )
				return;

			// Note Baton Customizer
			wp_enqueue_script( 'note-baton-customizer', get_template_directory_uri() . '/customizer/js/note-baton-customizer.js', array( 'note-customizer' ), $this->version, true );
			wp_localize_script( 'note-baton-customizer', 'note_baton', array() ); // Empty for now
		}

		/**
		 * This function fires on the initialization of the Customizer. We add actions that pertain to the
		 * Customizer preview window here. The actions added here are fired only in the Customizer preview.
		 */
		public function customize_preview_init() {
			// Bail if Note doesn't exist
			if ( ! class_exists( 'Note' ) )
				return;

			add_action( 'wp_enqueue_scripts', array( $this, 'customize_preview_wp_enqueue_scripts' ) ); // Previewer Scripts/Styles
		}

		/**
		 * This function enqueues all styles and scripts (Main Stylesheet, Fonts, etc...). Stylesheets can be conditionally included if needed.
		 */
		public function customize_preview_wp_enqueue_scripts() {
			// Bail if Note doesn't exist
			if ( ! class_exists( 'Note' ) )
				return;

			// Grab the queried object
			$queried_object = get_queried_object();

			// Setup localize data
			$note_baton_localize = array(
				'queried_object' => $queried_object
			);

			// Taxonomy (term)
			if ( is_tax() ) {
				// Set a flag
				$note_baton_localize['is_tax'] = true;

				// Store a reference to the taxonomy
				$note_baton_localize['taxonomy'] = get_taxonomy( $queried_object->taxonomy );
			}
			// Post Type Archive
			else if ( is_post_type_archive() )
				// Set a flag
				$note_baton_localize['is_post_type_archive'] = true;

			// Note Baton
			wp_enqueue_script( 'note-baton', get_template_directory_uri() . '/customizer/js/note-baton.js', array( 'note' ), $this->version, true );
			wp_localize_script( 'note-baton', 'note_baton', $note_baton_localize );
		}

		/**
		 * This function adjusts post meta data.
		 */
		public function get_post_metadata( $value, $post_id, $meta_key, $single ) {
			// Bail if WooCommerce doesn't exist, we already have a check value, this isn't a single request, or the meta key doesn't match "_wp_page_template"
			if ( ! class_exists( 'WooCommerce' ) || $value !== null || ! $single || $meta_key !== '_wp_page_template' )
				return $value;

			// Bail if this isn't the admin and we're not on the WooCommerce cart and the WooCommerce checkout page
			if ( ! is_admin() && ! is_cart() && ! is_checkout() )
				return $value;

			// Bail if this is the admin and we're not on the WooCommerce cart and the WooCommerce checkout page
			if ( is_admin() && $post_id !== wc_get_page_id( 'cart' ) && $post_id !== wc_get_page_id( 'checkout' ) )
				return $value;

			// If we don't have a page template
			if ( $this->page_template === '' )
				// Set the page template to the full width page template
				$value = 'template-full-width.php';

			return $value;
		}

		/**
		 * This function adjusts post meta data while updating.
		 */
		public function update_post_metadata( $value, $post_id, $meta_key, $single ) {
			// Bail if WooCommerce doesn't exist, we already have a check value, this isn't a single request, or the meta key doesn't match "_wp_page_template"
			if ( ! class_exists( 'WooCommerce' ) || $value !== null || ! $single || $meta_key !== '_wp_page_template' )
				return $value;

			// Bail if this is the admin and we're not on the WooCommerce cart and the WooCommerce checkout page
			if ( is_admin() && $post_id !== wc_get_page_id( 'cart' ) && $post_id !== wc_get_page_id( 'checkout' ) )
				return $value;

			// Remove the get_post_metadata adjustment
			remove_filter( 'get_post_metadata', array( $this, 'get_post_metadata' ) );

			return $value;
		}

		/**
		 * This function adds editor styles based on post type, before TinyMCE is initialized.
		 * It will also enqueue the correct color scheme stylesheet to better match front-end display.
		 */
		public function pre_get_posts() {
			global $sds_theme_options, $post;

			// Determine the correct protocol
			$protocol = is_ssl() ? 'https' : 'http';

			// Grab the Baton Theme Helper instance
			$baton_theme_helper = Baton_Theme_Helper();

			// Core editor styles
			add_editor_style( 'css/editor-style.css' );

			// Add color scheme if selected
			if ( function_exists( 'sds_color_schemes' ) && ( $sds_color_scheme = sds_get_color_scheme() ) )
				add_editor_style( $sds_color_scheme['stylesheet'] );

			// Google Web Fonts (include only if a web font is not selected in Theme Options)
			if ( ! function_exists( 'sds_web_fonts' ) || empty( $sds_theme_options['web_font'] ) ) {
				// Lato and Martel Sans
				$google_web_fonts = 'Lato:400,700,900|Martel+Sans:400,600';

				// If the Baton_Customizer_Fonts() function exists
				if ( function_exists( 'Baton_Customizer_Fonts' ) ) {
					// Grab the Baton Customizer Fonts instance
					$baton_customizer_fonts = Baton_Customizer_Fonts();

					// If we have Google Web Font support and have Google Web Fonts selected
					if ( $baton_theme_helper->has_google_web_font_support() && ! $baton_customizer_fonts->has_default_font_families( true ) )
						$google_web_fonts .= '|' . $baton_customizer_fonts->get_google_web_font_stylesheet_families();
				}

				add_editor_style( str_replace( ',', '%2C', $protocol . '://fonts.googleapis.com/css?family=' . $google_web_fonts ) ); // Google Web Fonts
			}

			// Fetch page template if any on Pages only
			if ( ! empty( $post ) && $post->post_type === 'page' )
				$wp_page_template = get_post_meta( $post->ID,'_wp_page_template', true );

			// If we have a post using our full page or landing page templates
			if ( ! empty( $post ) && ( isset( $wp_page_template ) && ( $wp_page_template === 'template-full-width.php' || $wp_page_template === 'template-landing-page.php' ) ) )
				add_editor_style( 'css/editor-style-full-width.css' );

			// FontAwesome
			add_editor_style( SDS_Theme_Options::sds_core_dir( true ) . '/css/font-awesome.min.css' );
		}

		/**
		 * This function runs when meta boxes are loaded.
		 */
		public function do_meta_boxes() {
			global $post;

			// If we have a post
			if ( $post ) {
				// Store the current page template on the class
				$this->page_template = get_post_meta( get_post_field( 'ID', $post ), '_wp_page_template', true );

				// Hook into get_post_metadata
				add_filter( 'get_post_metadata', array( $this, 'get_post_metadata' ), 10, 4 );
			}
		}

		/**
		 * This function adds <meta> tags to the <head> element.
		 */
		public function wp_head() {
		?>
			<meta charset="<?php bloginfo( 'charset' ); ?>" />
			<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
		<?php
		}

		/**
		 * This function prints scripts after TinyMCE has been initialized for dynamic CSS in the
		 * content editor based on page template dropdown selection.
		 */
		public function tiny_mce_before_init( $mceInit, $editor_id ) {
			// Grab the Baton Customizer instance
			$baton_customizer = Baton_Customizer_Instance();

			// Grb the Baton Customizer Typography instance
			$baton_customizer_typography = Baton_Customizer_Typography();

			// Grab the Baton Customizer CSS
			$customizer_css = $baton_customizer->get_customizer_css(); // Baton Customizer CSS
			$customizer_css .= $baton_customizer_typography->get_customizer_css(); // Baton Customizer Typography CSS

			// Process customizer CSS
			$customizer_css = preg_replace( '/<style.+?>/', '', $customizer_css ); // Remove opening <style> tag
			$customizer_css = preg_replace( '/\/\*.+?\*\//', '', $customizer_css ); // Remove comments
			$customizer_css = preg_replace( '/\r?\n/', ' ', $customizer_css ); // Replace newlines with spaces
			$customizer_css = preg_replace( '/box-shadow:\s?(.+)?;/', '', $customizer_css ); // Remove box shadow styles
			$customizer_css = str_replace( '</style>', '', $customizer_css ); // Remove closing </style> tag
			$customizer_css = str_replace( ';', ' !important;', $customizer_css ); // Ensure all styles take precedence
			$customizer_css = str_replace( '"','\'', $customizer_css ); // Replace all quotation marks with single quotes
			$customizer_css = str_replace( '.content-container', 'body', $customizer_css ); // Replace all .content-container references (selectors) with body

			// Default maximum width
			$max_width = 1272;

			// Determine if the max width theme mod is set
			if ( ( $max_content_width = $baton_customizer->get_theme_mod( 'baton_max_width' ) ) && $max_content_width !== $max_width )
				$max_width = $max_content_width;

			// Adjust max width for padding (22.5%)
			$max_width -= round( $max_width * 0.225 );

			// Only on the admin 'content' editor
			if ( is_admin() && ! isset( $mceInit['setup'] ) && $editor_id === 'content' ) {
				// TinyMCE Setup
				$mceInit['setup'] = 'function( editor ) {
					// Editor init
					editor.on( "init", function( e ) {
						// Only on the "content" editor (other editors can inherit the setup function on init)
						if( editor.id === "content" ) {
							var $page_template = jQuery( "#page_template" ),
								full_width_templates = ["template-full-width.php", "template-landing-page.php"],
								$content_editor_head = jQuery( editor.getDoc() ).find( "head" );

							// If the page template dropdown exists
							if ( $page_template.length ) {
								// When the page template dropdown changes
								$page_template.on( "change", function() {
									// Is this a full width template?
									if ( full_width_templates.indexOf( $page_template.val() ) !== -1 ) {
										// Add dynamic CSS
										if( $content_editor_head.find( "#' . get_template() . '-editor-css" ).length === 0 ) {
											$content_editor_head.append( "<style type=\'text/css\' id=\'' . get_template() . '-editor-css\'> body, body.wp-autoresize { max-width: ' . $max_width . 'px; } </style>" );
										}
									}
									else {
										// Remove dynamic CSS
										$content_editor_head.find( "#' . get_template() . '-editor-css" ).remove();

										// If the full width style was added on TinyMCE Init, remove it
										$content_editor_head.find( "link[href^=\'' . get_template_directory_uri() . '/css/editor-style-full-width.css\']" ).remove();
									}
								} );
							}
						}
					} );
				}';

				// TinyMCE Content Style
				if ( ! empty( $customizer_css ) )
					$mceInit['content_style'] = $customizer_css;
			}

			return $mceInit;
		}

		/**
		 * This function enqueues all styles and scripts (Main Stylesheet, Fonts, etc...). Stylesheets can be conditionally included if needed.
		 */
		public function wp_enqueue_scripts() {
			// Determine current protocol
			$protocol = is_ssl() ? 'https' : 'http';

			// Baton (main stylesheet)
			wp_enqueue_style( 'baton', get_template_directory_uri() . '/style.css', false, $this->version );

			// Enqueue the child theme stylesheet only if a child theme is active
			if ( is_child_theme() )
				wp_enqueue_style( 'baton-child', get_stylesheet_uri(), array( 'baton' ), $this->version );

			// Google Web Fonts - Lato & Martel Sans
			wp_enqueue_style( 'baton-google-web-fonts', $protocol . '://fonts.googleapis.com/css?family=Lato:400,700,900|Martel+Sans:400,600', false, $this->version );

			// Ensure jQuery is loaded on the front end for our footer script (@see wp_footer() below)
			wp_enqueue_script( 'jquery' );

			// Fitvids
			wp_enqueue_script( 'fitvids', get_template_directory_uri() . '/js/fitvids.js', array( 'jquery' ), $this->version );

			// FontAwesome
			wp_enqueue_style( 'font-awesome-css-min', get_template_directory_uri() . '/includes/css/font-awesome.min.css' );
		}

		/**
		 * This function adjusts the post class.
		 */
		public function post_class( $classes, $class, $post_id ) {
			// Bail if WooCommerce doesn't exist, this isn't WooCommerce, we're in the cart, or we're checking out
			if ( ! class_exists( 'WooCommerce' ) || ! is_woocommerce() || is_cart() || is_checkout() )
				return $classes;

			// Grab the post
			$post = get_post( $post_id );

			// If this isn't a single WooCommerce Product and the post type is a WooCommerce Product (i.e. archive) or this is a single WooCommerce Product and the queried object ID doesn't match the post ID
			if ( ( ! is_product() && get_post_type( $post ) === 'product' ) || ( get_queried_object_id() !== get_post_field( 'ID', $post ) ) ) {
				// Add the Baton content CSS class
				$classes[] = esc_attr( 'content' );
				$classes[] = esc_attr( 'content-woocommerce' );
			}

			// If this isn't a single WooCommerce Product
			if ( ! is_product() ) {
				// Add the Baton column CSS classes
				$classes[] = esc_attr( 'baton-col' );
				$classes[] = esc_attr( 'baton-col-woocommerce-product' );
				$classes[] = esc_attr( 'baton-col-woocommerce-product-' . $post_id );
			}
			// Otherwise if this is a single WooCommerce Product and the queried object ID matches the post ID
			else if ( get_queried_object_id() === get_post_field( 'ID', $post ) ) {
				$classes[] = 'baton-flex';
				$classes[] = 'baton-flex-2-columns';
			}

			return $classes;
		}

		/**
		 * This function adds a clearing element before the more link in the_content(). It
		 * also adds a "button" CSS class to the link.
		 */
		public function the_content_more_link( $link ) {
			return '<div class="clear"></div>' . str_replace( 'class="', 'class="button ', $link );
		}

		/**
		 * This function adjusts the sidebar parameters for widgets.
		 */
		public function dynamic_sidebar_params( $params ) {
			// Bail if we're not on the front-end
			if ( is_admin() )
				return $params;

			// Baton Landing Page - Conductor
			if ( baton_is_conductor_baton_landing_page() ) {
				// If this is not a Conductor Widget and we're on the Conductor content sidebar
				if ( ! $this->conductor_widget_has_flexbox_support( $params ) && $params[0]['id'] === Conductor::get_conductor_content_layout_sidebar_id( 'content' ) ) {
					// Adjust the before_widget parameter (add "in" element)
					$params[0]['before_widget'] .= '<div class="in baton-conductor-widget-in baton-landing-page-conductor-widget-in cf">';

					// Adjust the after_widget parameter (close "in" element)
					$params[0]['after_widget'] = '</div>' . $params[0]['after_widget'];
				}
			}

			// If Note exists
			if ( function_exists( 'Note_Widget' ) ) {
				// Grab the Note Widget instance
				$note_widget = Note_Widget();
	
				// Only on Note Widgets
				if ( _get_widget_id_base( $params[0]['widget_id'] ) === $note_widget->id_base ) {
					// Store a reference to the widget settings (all Note Widgets)
					$note_widget_settings = $note_widget->get_settings();
	
					// Determine if this is a valid Note widget
					if ( array_key_exists( $params[1]['number'], $note_widget_settings ) ) {
						// Grab widget settings
						$instance = $note_widget_settings[$params[1]['number']];
	
						// If we have a template
						if ( property_exists( $note_widget, 'templates' ) && isset( $instance['template'] ) && ! empty( $instance['template'] ) && array_key_exists( $instance['template'], $note_widget->templates ) ) {
							// Grab the template details for this widget
							$template = $note_widget->templates[$instance['template']];
	
							// CSS Classes
							$css_classes = array();
	
							// Check the template type first
							if ( isset( $template['type'] ) )
								$css_classes[] = sanitize_html_class( $template['type'] . '-widget' );
							// Then check the template
							if ( empty( $css_classes ) && isset( $template['template'] ) )
								$css_classes[] = sanitize_html_class( $template['template'] . '-widget' );
							// Otherwise fallback to the name
							if ( empty( $css_classes ) )
								$css_classes[] = sanitize_html_class( $instance['template'] . '-widget' );
	
							// Adjust the before_widget parameter (only replacing once to ensure only the outer most wrapper element gets the CSS class adjustment)
							$params[0]['before_widget'] = preg_replace( '/class="/', 'class="' . esc_attr( implode( ' ', $css_classes ) ) . ' ', $params[0]['before_widget'], 1 );
						}
					}
				}

				// If this is a Baton Note Sidebar
				if ( in_array( $params[0]['id'], $this->note_registered_sidebar_ids ) || ( ! empty( $this->current_note_sidebar_id ) && $params[0]['id'] === $this->current_note_sidebar_id ) ) {
					// Adjust the before_widget parameter (add "widget" CSS class to wrapper)
					$params[0]['before_widget'] = str_replace( 'class="', 'class="widget ', $params[0]['before_widget'] );

					// If this is not a Conductor Widget
					if ( ! $this->conductor_widget_has_flexbox_support( $params ) ) {
						// Adjust the before_widget parameter (add "in" element)
						$params[0]['before_widget'] .= '<div class="in baton-note-sidebar-widget-in baton-' . sanitize_html_class( $params[0]['id'] ) . '-in cf">';

						// Adjust the after_widget parameter (close "in" element)
						$params[0]['after_widget'] = '</div>' . $params[0]['after_widget'];
					}
				}
			}

			// If this is a Conductor Widget with a flexbox display in the Front Page Sidebar
			if ( $this->conductor_widget_has_flexbox_support( $params ) && $params[0]['id'] === 'front-page-sidebar' ) {
				// Adjust the before_widget parameter (only replacing once to ensure only the outer most wrapper element gets the CSS class adjustment)
				$params[0]['before_widget'] = preg_replace( '/<div class="in front-page-widget-in cf">/', '', $params[0]['before_widget'], 1 );

				// Adjust the after_widget parameter (only replacing once to ensure only the outer most wrapper element gets the CSS class adjustment)
				$params[0]['after_widget'] = preg_replace( '/<\/div>/', '', $params[0]['after_widget'], 1 );
			}

			return $params;
		}

		/**
		 * This function adds a "button" CSS class on "edit post" links.
		 */
		public function edit_post_link( $link ) {
			return str_replace( '<a class="', '<a class="button ', $link );
		}

		/**
		 * This function outputs the necessary javascript for the responsive menus.
		 */
		public function wp_footer() {
		?>
			<script type="text/javascript">
				// <![CDATA[
				jQuery( function( $ ) {
					var $primary_nav_and_button = $( '.primary-nav-button, .primary-nav-mobile' ),
						$primary_nav_items = $primary_nav_and_button.find( 'li' ),
						$secondary_nav_and_button = $( '.secondary-nav-button, .secondary-nav' ),
						$secondary_nav_items = $secondary_nav_and_button.find( 'li' );

					// Primary Nav
					$primary_nav_and_button.on( 'click', function ( e ) {
						<?php
							// If we're not in the Customizer, stop propagation and default on click
							if ( ! is_customize_preview() ) :
						?>
							// Prevent Propagation (bubbling) to other elements and default
							e.stopPropagation();
							e.preventDefault();
						<?php
							endif;
						?>

						// Open
						if ( ! $primary_nav_and_button.hasClass( 'open' ) ) {
							$primary_nav_and_button.addClass( 'open' );

							// 500ms delay to account for CSS transition (if any)
							setTimeout( function() {
								$primary_nav_and_button.addClass( 'opened' );
							}, 500 );
						}
						// Close
						else {
							$primary_nav_and_button.removeClass( 'open opened' );
						}
					} );

					// Secondary Nav
					$secondary_nav_and_button.on( 'click', function ( e ) {
						<?php
							// If we're not in the Customizer, stop propagation and default on click
							if ( ! is_customize_preview() ) :
						?>
							// Prevent Propagation (bubbling) to other elements and default
							e.stopPropagation();
							e.preventDefault();
						<?php
							endif;
						?>

						// Open
						if ( ! $secondary_nav_and_button.hasClass( 'open' ) ) {
							$secondary_nav_and_button.addClass( 'open' );

							// 500ms delay to account for CSS transition (if any)
							setTimeout( function() {
								$secondary_nav_and_button.addClass( 'opened' );
							}, 500 );
						}
						// Close
						else {
							$secondary_nav_and_button.removeClass( 'open opened' );
						}
					} );

					// Primary Nav/Secondary Items
					$primary_nav_items.add( $secondary_nav_items ).each( function() {
						var $this = $( this );

						// Child elements
						if ( $this.hasClass( 'menu-item-has-children' ) || $this.hasClass( 'page_item_has_children' ) ) {
							$this.addClass( 'closed' ).append( '<span class="fa fa-chevron-down child-menu-button"></span>' );

							// Child menu buttons
							$this.find( '.child-menu-button' ).on( 'click', function( e ) {
								var $child_button = $( this );

								$this.toggleClass( 'closed open opened' );

								$child_button.toggleClass( 'fa-chevron-up fa-chevron-down' );
							} );
						}
					} );

					<?php
						// If we're not in the Customizer, stop propagation on click
						if ( ! is_customize_preview() ) :
					?>
						// Primary Nav/Secondary Nav Items Click
						$primary_nav_items.add( $secondary_nav_items ).on( 'click', function( e ) {
							// Prevent Propagation (bubbling) to other elements
							e.stopPropagation();
						} );
					<?php
						endif;
					?>

					// Document
					$( document ).on( 'click', function() {
						// Close Primary Nav
						$primary_nav_and_button.removeClass( 'open opened' );

						// Close Secondary Nav
						$secondary_nav_and_button.removeClass( 'open opened' );
					} );

					// Fitvids
					$( 'article.content, .widget' ).fitVids();
				} );
				// ]]>
			</script>
		<?php
		}


		/*****************
		 * Theme Options *
		 *****************/


		/*****************
		 * Theme Updates *
		 *****************/

		/**
		 * This function handles theme updates from the ThemeUpdateChecker library.
		 */
		public function admin_init() {
			// Bail if the EDD Theme Updater class doesn't exist
			if ( ! class_exists( 'EDD_SL_Theme_Updater' ) )
				return;

			// Fetch current theme details
			$sds_theme_options_instance = SDS_Theme_Options_Instance();

			// Fetch the license
			$sds_theme_options = SDS_Theme_Options::get_sds_theme_options();

			// Create the updater
			$this->_updater = new EDD_SL_Theme_Updater( array(
				'remote_api_url' => SDS_Theme_Options::$update_url,
				'version' 	=> $this->version,
				'license' 	=> $sds_theme_options['license']['key'],
				'item_name' => $sds_theme_options_instance->theme->get( 'Name' ),
				'author' 	=> __( 'Slocum Studio', 'baton-pro' )
			) );

			// Ensure theme updates "work" on multisite (@see function descriptions below)
			if ( is_multisite() )
				$this->multisite_theme_update_check();
		}

		/**
		 * This function ensures that multisite updates "work".
		 * It first checks to make sure the _maybe_update_themes() function exists (@see WP_INC/update.php as of 3.5.2).
		 * If the function does exist, use it to check for theme updates.
		 * If the function does not exist, use the one we've provided which is identical as of 3.5.2.
		 * "work" is in quotation marks because it requires the site that has this theme active to at least make one admin page request (i.e. loading the dashboard).
		 */
		public function multisite_theme_update_check() {
			/* delete_site_transient('update_themes'); // Used for DEBUG */

			if ( function_exists( '_maybe_update_themes' ) )
				_maybe_update_themes();
			else
				$this->maybe_update_themes();
		}

		/**
		 * This function is identicle to the one provided in WP 3.5.2 and will check to see if WP should check for theme updates.
		 * It is used as a fallback in multisite_theme_update_check above.
		 */
		public function maybe_update_themes() {
			$current = get_site_transient( 'update_themes' );

			if ( ! defined( 'HOUR_IN_SECONDS' ) )
				define( HOUR_IN_SECONDS, 60 );

			if ( isset( $current->last_checked ) && 12 * HOUR_IN_SECONDS > ( time() - $current->last_checked ) )
				return;

			wp_update_themes();
		}


		/********
		 * Note *
		 ********/

		/**
		 * This function adds Baton Note Widget editor types to Note.
		 */
		public function note_tinymce_editor_types( $types ) {
			// Baton Hero
			if ( ! in_array( 'baton-hero', $types ) )
				$types[] = 'baton-hero';

			return $types;
		}

		/**
		 * This function adjusts Note Widget TinyMCE editor settings based on editor type.
		 */
		public function note_tinymce_editor_settings( $settings, $editor_type ) {
			// Switch based on editor type
			switch ( $editor_type ) {
				// Hero
				case 'baton-hero':
					// Make plugins an array
					$settings['plugins'] = explode( ' ', $settings['plugins'] );

					// Search for the 'note_image' TinyMCE plugin in existing settings
					$note_image = array_search( 'note_image', $settings['plugins'] );

					// If we have an index for the the 'note_image' TinyMCE plugin
					if ( $note_image !== false ) {
						// Remove the 'note_image' TinyMCE plugin
						unset( $settings['plugins'][$note_image] );

						// Reset array keys to ensure JavaScript logic receives an array
						$settings['plugins'] = array_values( $settings['plugins'] );
					}

					// Make plugins a string again
					$settings['plugins'] = implode( ' ', $settings['plugins'] );


					// Search for the 'wp_image' TinyMCE block in existing settings
					$wp_image = array_search( 'wp_image', $settings['blocks'] );

					// If we have an index for the the 'wp_image' TinyMCE block
					if ( $wp_image !== false ) {
						// Remove the 'wp_image' TinyMCE block
						unset( $settings['blocks'][$wp_image] );

						// Reset array keys to ensure JavaScript logic receives an array
						$settings['blocks'] = array_values( $settings['blocks'] );
					}
				break;

				// Default, all displays
				default:
					// Adjust the style formats
					$settings['style_formats'][] = array(
						'title' => __( 'Button', 'baton-pro' ),
						'selector' => 'a',
						'attributes' => array(
							'class' => 'button'
						)
					);
					$settings['style_formats'][] = array(
						'title' => __( 'Button Alternate', 'baton-pro' ),
						'selector' => 'a',
						'attributes' => array(
							'class' => 'button-alt'
						)
					);
				break;
			}

			return $settings;
		}

		/**
		 * This function adds Baton Note Widget template types to Note.
		 */
		public function note_widget_template_types( $types ) {
			// Hero type
			if ( ! isset( $types['baton-hero'] ) )
				$types['baton-hero'] = __( 'Baton Hero', 'baton-pro' );

			// Features type
			if ( ! isset( $types['baton-features'] ) )
				$types['baton-features'] = __( 'Baton Features', 'baton-pro' );

			return $types;
		}

		/**
		 * This function adds Hero and Features templates to Note Widgets.
		 *
		 * @see Note for configuration details
		 */
		public function note_widget_templates( $templates, $widget ) {
			global $sds_theme_options, $wp_customize;

			// Customizer Previewer only
			if ( is_customize_preview() && ! is_admin() ) {
				// SDS Theme Options defaults
				$sds_theme_options_instance = SDS_Theme_Options_Instance();
				$sds_theme_options_defaults = $sds_theme_options_instance->get_sds_theme_option_defaults();

				/*
				 * Setting - We have to add a setting here in order to enable preview filters before the wp_loaded action (core).
				 */
				$setting = new WP_Customize_Setting( $wp_customize,
					'sds_theme_options[color_scheme]', // IDs can have nested array keys
					array(
						'default' => $sds_theme_options_defaults['color_scheme'],
						'type' => 'option',
						// Data is also sanitized upon update_option() call using the sanitize function in $sds_theme_options_instance
						'sanitize_callback' => 'sanitize_html_class'
					)
				);
				$wp_customize->add_setting( $setting );

				// Call the preview() function to enable Previewer filters
				$setting->preview();

				// SDS Theme Options (store reference to new options)
				$sds_theme_options = SDS_Theme_Options::get_sds_theme_options();
			}

			// Baton Hero 1
			if ( ! isset( $templates['baton-hero-1'] ) )
				$templates['baton-hero-1'] = array(
					// Label
					'label' => __( 'Baton Hero 1', 'baton-pro' ),
					// Placeholder Content
					'placeholder' => sprintf( '<h2>%1$s</h2>
							<p data-note-placeholder="false"><strong data-note-placeholder="false"><span style="font-size: 24px;">%2$s</span></strong></p>
							<p data-note-placeholder="false"><br data-note-placeholder="false" /></p>
							<p data-note-placeholder="false"><a href="#" class="button" data-note-placeholder="false">%3$s</a></p>',
						__( 'Hero 1', 'baton-pro' ),
						__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed eros tortor, molestie eget tortor sit amet, feugiat semper ante. Aliquam a pellentesque purus, quis vulputate lacus.', 'baton-pro' ),
						__( 'Button', 'baton-pro' ) ),
					// Type
					'type' => 'baton-hero',
					// Template
					'template' => 'baton-hero',
					// Customizer Previewer Configuration
					'config' => array(
						// Allow for the customization of the following
						'customize' => array(
							'note_background' => true // Note Background
						),
						// Type of editor
						'type' => 'baton-hero', // Hero
						// Plugins, Additional elements and features that this editor supports (optional)
						'plugins' => array(
							'note_background' // Allow for addition of a background image
						),
						// Blocks, Additional blocks to be added to the "insert" toolbar
						'blocks' => array(
							'note_background' // Allow for addition of a background image
						)
					)
				);

			// Baton Hero 2
			if ( ! isset( $templates['baton-hero-2'] ) )
				$templates['baton-hero-2'] = array(
					// Label
					'label' => __( 'Baton Hero 2', 'baton-pro' ),
					// Placeholder Content
					'placeholder' => sprintf( '<h2 style="text-align: center;">%1$s</h2>
							<p style="text-align: center;">%2$s</p>
							<p style="text-align: center;" data-note-placeholder="false"><a href="#" class="button" data-note-placeholder="false">%3$s</a></p>',
						__( 'Hero 2', 'baton-pro' ),
						__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed eros tortor, molestie eget tortor sit amet, feugiat semper ante. Aliquam a pellentesque purus, quis vulputate lacus.', 'baton-pro' ),
						__( 'Button', 'baton-pro' ) ),
					// Type
					'type' => 'baton-hero',
					// Template
					'template' => 'baton-hero',
					// Customizer Previewer Configuration
					'config' => array(
						// Allow for the customization of the following
						'customize' => array(
							'note_background' => true // Note Background
						),
						// Type of editor
						'type' => 'baton-hero', // Hero
						// Plugins, Additional elements and features that this editor supports (optional)
						'plugins' => array(
							'note_background' // Allow for addition of a background image
						),
						// Blocks, Additional blocks to be added to the "insert" toolbar
						'blocks' => array(
							'note_background' // Allow for addition of a background image
						)
					)
				);

			// Baton Hero 3 (Content Left)
			if ( ! isset( $templates['baton-hero-3'] ) )
				$templates['baton-hero-3'] = array(
					// Label
					'label' => __( 'Baton Hero 3 (Content Left)', 'baton-pro' ),
					// Placeholder Content
					'placeholder' => sprintf( '<h3>%1$s</h3>
								<p>%2$s</p>',
						__( 'Baton Hero 3 (Content Left)', 'baton-pro' ),
						__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed eros tortor, molestie eget tortor sit amet, feugiat semper ante. Aliquam a pellentesque purus, quis vulputate lacus.', 'baton-pro' ) ),
					// Type
					'type' => 'baton-hero',
					// Template
					'template' => 'baton-hero', // Hero
					// Customizer Previewer Configuration
					'config' => array(
						// Allow for the customization of the following
						'customize' => array(
							'note_background' => true // Note Background
						),
						// Type of editor
						'type' => 'baton-hero', // Baton Hero
						// Plugins, Additional elements and features that this editor supports (optional)
						'plugins' => array(
							'note_background' // Allow for addition of a background image
						),
						// Blocks, Additional blocks to be added to the "insert" toolbar
						'blocks' => array(
							'note_background' // Allow for addition of a background image
						)
					)
				);

			// Baton Hero 3 (Content Right)
			if ( ! isset( $templates['baton-hero-3-r'] ) )
				$templates['baton-hero-3-r'] = array(
					// Label
					'label' => 'Baton Hero 3 (Content Right)',
					// Placeholder Content
					'placeholder' => sprintf( '<h3 text-align: right;">%1$s</h3>
								<p text-align: right;">%2$s</p>',
						 __( 'Baton Hero 3 (Content Right)', 'baton-pro' ),
						 __( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed eros tortor, molestie eget tortor sit amet, feugiat semper ante. Aliquam a pellentesque purus, quis vulputate lacus.', 'baton-pro' ) ),
					// Type
					'type' => 'baton-hero',
					// Template
					'template' => 'baton-hero', // Hero
					// Customizer Previewer Configuration
					'config' => array(
						// Allow for the customization of the following
						'customize' => array(
							'note_background' => true // Note Background
						),
						// Type of editor
						'type' => 'baton-hero', // Baton Hero
						// Plugins, Additional elements and features that this editor supports (optional)
						'plugins' => array(
							'note_background' // Allow for addition of a background image
						),
						// Blocks, Additional blocks to be added to the "insert" toolbar
						'blocks' => array(
							'note_background' // Allow for addition of a background image
						)
					)
				);

			// Baton Features 1
			if ( ! isset( $templates['baton-features-1'] ) )
				$templates['baton-features-1'] = array(
					// Label
					'label' => __( 'Baton Features 1', 'baton-pro' ),
					// Placeholder Content
					'placeholder' => sprintf( '<h2 style="text-align: center;">%1$s</h2>
							<p style="text-align: center;">%2$s</p>',
						__( 'Features', 'baton-pro' ),
						__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed eros tortor, molestie eget tortor sit amet, feugiat semper ante. Aliquam a pellentesque purus, quis vulputate lacus.', 'baton-pro' ) ),
					// Type
					'type' => 'baton-features',
					// Template
					'template' => 'baton-features',
					// Customizer Previewer Configuration
					'config' => array(
						// Allow for the customization of the following
						'customize' => array(
							'columns' => true, // Columns
							'rows' => true // Rows
						),
						// Placeholder (Columns; used in place for "extra" columns that aren't found in configuration below)
						'placeholder' => sprintf( '<h6 style="text-align: center;">%1$s</h6>
								<p style="text-align: center;" data-note-placeholder="false"><span style="font-size: 16px;">%2$s</span></p>
								<p style="text-align: center;" data-note-placeholder="false"><strong data-note-placeholder="false"><span style="font-size: 16px;">%3$s</span></strong></p>',
							__( 'Feature', 'baton-pro' ),
							__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed eros tortor, molestie eget tortor sit amet, feugiat semper ante. Aliquam a pellentesque purus, quis vulputate lacus.', 'baton-pro' ),
							__( 'Read More', 'baton-pro' ) ),
						// Column configuration
						'columns' => array(
							// Column 1
							1 => array(
								// Placeholder (Column)
								'placeholder' => sprintf( '<h6 style="text-align: center;">%1$s</h6>
										<p style="text-align: center;" data-note-placeholder="false"><span style="font-size: 16px;">%2$s</span></p>
										<p style="text-align: center;" data-note-placeholder="false"><strong data-note-placeholder="false"><span style="font-size: 16px;">%3$s</span></strong></p>',
									__( 'Feature', 'baton-pro' ),
									__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed eros tortor, molestie eget tortor sit amet, feugiat semper ante. Aliquam a pellentesque purus, quis vulputate lacus.', 'baton-pro' ),
									__( 'Read More', 'baton-pro' ) ),
							),
							// Column 2
							2 => array(
								// Placeholder (Column)
								'placeholder' => sprintf( '<h6 style="text-align: center;">%1$s</h6>
										<p style="text-align: center;" data-note-placeholder="false"><span style="font-size: 16px;">%2$s</span></p>
										<p style="text-align: center;" data-note-placeholder="false"><strong data-note-placeholder="false"><span style="font-size: 16px;">%3$s</span></strong></p>',
									__( 'Feature', 'baton-pro' ),
									__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed eros tortor, molestie eget tortor sit amet, feugiat semper ante. Aliquam a pellentesque purus, quis vulputate lacus.', 'baton-pro' ),
									__( 'Read More', 'baton-pro' ) ),
							),
							// Column 2
							3 => array(
								// Placeholder (Column)
								'placeholder' => sprintf( '<h6 style="text-align: center;">%1$s</h6>
										<p style="text-align: center;" data-note-placeholder="false"><span style="font-size: 16px;">%2$s</span></p>
										<p style="text-align: center;" data-note-placeholder="false"><strong data-note-placeholder="false"><span style="font-size: 16px;">%3$s</span></strong></p>',
									__( 'Feature', 'baton-pro' ),
									__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed eros tortor, molestie eget tortor sit amet, feugiat semper ante. Aliquam a pellentesque purus, quis vulputate lacus.', 'baton-pro' ),
									__( 'Read More', 'baton-pro' ) ),
							),
							// Column 4
							4 => array(
								// Placeholder (Column)
								'placeholder' => sprintf( '<h6 style="text-align: center;">%1$s</h6>
										<p style="text-align: center;" data-note-placeholder="false"><span style="font-size: 16px;">%2$s</span></p>
										<p style="text-align: center;" data-note-placeholder="false"><strong data-note-placeholder="false"><span style="font-size: 16px;">%3$s</span></strong></p>',
									__( 'Feature', 'baton-pro' ),
									__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed eros tortor, molestie eget tortor sit amet, feugiat semper ante. Aliquam a pellentesque purus, quis vulputate lacus.', 'baton-pro' ),
									__( 'Read More', 'baton-pro' ) ),
							)
						)
					)
				);

			// Baton Features 2
			if ( ! isset( $templates['baton-features-2'] ) ) {
				$templates['baton-features-2'] = array(
					// Label
					'label' => __( 'Baton Features 2', 'baton-pro' ),
					// Placeholder Content
					'placeholder' => sprintf( '<h2 style="text-align: center;">%1$s</h2>
							<p style="text-align: center;"><br /></p>',
						__( 'Features', 'baton-pro' ) ),
					// Type
					'type' => 'baton-features',
					// Template
					'template' => 'baton-features',
					// Customizer Previewer Configuration
					'config' => array(
						// Allow for the customization of the following
						'customize' => array(
							'columns' => true, // Columns
							'rows' => true // Rows
						),
						// Placeholder (Columns; used in place for "extra" columns that aren't found in configuration below)
						'placeholder' => sprintf( '<h5 data-note-placeholder="false"><span style="color: #fff;">%1$s</span></h5>
								<p data-note-placeholder="false"><span style="color: #fff; font-size: 16px;">%2$s</span></p>',
							__( 'Feature', 'baton-pro' ),
							__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed eros tortor, molestie eget tortor sit amet, feugiat semper ante. Aliquam a pellentesque purus, quis vulputate lacus.', 'baton-pro' ) ),
						// Column configuration
						'columns' => array(
							// Column 1
							1 => array(
								// Placeholder (Column)
								'placeholder' => sprintf( '<h5 data-note-placeholder="false"><span style="color: #fff;">%1$s</span></h5>
										<p data-note-placeholder="false"><span style="color: #fff; font-size: 16px;">%2$s</span></p>',
									__( 'Feature', 'baton-pro' ),
									__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed eros tortor, molestie eget tortor sit amet, feugiat semper ante. Aliquam a pellentesque purus, quis vulputate lacus.', 'baton-pro' ) ),
							),
							// Column 2
							2 => array(
								// Placeholder (Column)
								'placeholder' => sprintf( '<h5 data-note-placeholder="false"><span style="color: #fff;">%1$s</span></h5>
										<p data-note-placeholder="false"><span style="color: #fff; font-size: 16px;">%2$s</span></p>',
									__( 'Feature', 'baton-pro' ),
									__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed eros tortor, molestie eget tortor sit amet, feugiat semper ante. Aliquam a pellentesque purus, quis vulputate lacus.', 'baton-pro' ) ),
							),
							// Column 2
							3 => array(
								// Placeholder (Column)
								'placeholder' => sprintf( '<h5 data-note-placeholder="false"><span style="color: #fff;">%1$s</span></h5>
										<p data-note-placeholder="false"><span style="color: #fff; font-size: 16px;">%2$s</span></p>',
									__( 'Feature', 'baton-pro' ),
									__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed eros tortor, molestie eget tortor sit amet, feugiat semper ante. Aliquam a pellentesque purus, quis vulputate lacus.', 'baton-pro' ) ),
							),
							// Column 4
							4 => array(
								// Placeholder (Column)
								'placeholder' => sprintf( '<h5 data-note-placeholder="false"><span style="color: #fff;">%1$s</span></h5>
										<p data-note-placeholder="false"><span style="color: #fff; font-size: 16px;">%2$s</span></p>',
									__( 'Feature', 'baton-pro' ),
									__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed eros tortor, molestie eget tortor sit amet, feugiat semper ante. Aliquam a pellentesque purus, quis vulputate lacus.', 'baton-pro' ) ),
							)
						)
					)
				);
				
				// Color scheme specific settings (Customizer Previewer only)
				if ( is_customize_preview() && ! is_admin() && isset( $sds_theme_options['color_scheme'] ) ) {
					// Switch based on color scheme
					switch ( $sds_theme_options['color_scheme'] ) {
						// Blue (remove white color from placeholder elements)
						case 'blue':
							// Placeholder (Columns; used in place for "extra" columns that aren't found in configuration below)
							$templates['baton-features-2']['config']['placeholder'] = str_replace( array( 'color: #fff;' ), '', $templates['baton-features-2']['config']['placeholder'] );

							// Loop through column configuration
							foreach ( $templates['baton-features-2']['config']['columns'] as $column_id => &$column )
								// Adjust the placeholder on even columns
								if ( $column_id % 2 === 0 )
									$column['placeholder'] = str_replace( array( 'color: #fff;' ), '', $column['placeholder'] );

							// Unset $column reference (at this point it references the last column in the initial configuration)
							unset( $column );

							// Store a reference to the current columns configuration
							$baton_features_2_columns = $templates['baton-features-2']['config']['columns'];

							/*
							 * Offset each even row column placeholder (switch even and odd due to theme alternating colors)
							 */

							// Loop through maximum rows (skip row 1)
							for ( $i = 2; $i <= $widget->max_rows; $i++ )
								// Loop through column configurations
								foreach ( $baton_features_2_columns as $column_id => $column ) {
									$template_columns = count( $baton_features_2_columns );
									$column_num = ( int ) ( $column_id + ( $template_columns * ( $i - 1 ) ) );

									// Even Row
									if ( $i % 2 === 0 )
										// If this is an even column, replace the placeholder with the odd column and vise-verse
										$templates['baton-features-2']['config']['columns'][$column_num] = ( $column_num % 2 === 0 ) ? $baton_features_2_columns[1] : $baton_features_2_columns[2];
								}
						break;
					}
				}
			}

			return $templates;
		}

		/**
		 * This function adjusts Note Widget CSS classes based on the widget instance settings.
		 */
		public function note_widget_css_classes( $classes, $instance, $widget ) {
			// If we are displaying a template
			if ( property_exists( $widget, 'templates' ) && isset( $instance['template'] ) && ! empty( $instance['template'] ) && array_key_exists( $instance['template'], $widget->templates ) ) {
				// Grab the template details for this widget
				$template = $widget->templates[$instance['template']];

				// Background Image Attachment ID
				if ( ( isset( $template['type'] ) && $template['type'] === 'baton-hero' ) && ( isset( $instance['extras'] ) && ( ! isset( $instance['extras']['background_image_attachment_id'] ) || ! $instance['extras']['background_image_attachment_id'] ) ) )
					$classes[] = 'has-default-baton-hero-image';
			}

			return $classes;
		}

		/**
		 * This function outputs an opening "in" wrapper element before the widget title on Note Widgets that
		 * have a Hero display selected and are set to display the widget title.
		 */
		public function note_widget_title_before( $instance, $args, $widget ) {
			// If we are displaying the widget title on a Hero display
			if ( isset( $instance['hide_title'] ) && ! $instance['hide_title'] && isset( $instance['template'] ) && array_key_exists( $instance['template'], $widget->templates ) ) :
				// Grab the template details for this widget
				$template = $widget->templates[$instance['template']];

				if ( isset( $template['type'] ) && $template['type'] === 'baton-hero' ) :
			?>
				<div class="in note-widget-in note-hero-widget-in note-baton-hero-in note-baton-hero-widget-in cf">
			<?php
				endif;
			endif;
		}

		/**
		 * This function outputs an opening "in" wrapper element before the widget template content
		 * on Note Widgets that have a Hero display selected and are not set to display the widget title.
		 */
		public function note_widget_template_before( $template_name, $template, $data, $number, $instance, $args, $widget ) {
			// If we are not displaying the widget title on a Hero display
			if ( isset( $instance['hide_title'] ) && $instance['hide_title'] && isset( $instance['template'] ) && array_key_exists( $instance['template'], $widget->templates ) ) :
				// Grab the template details for this widget
				$widget_template = $widget->templates[$instance['template']];

				if ( isset( $widget_template['type'] ) && $widget_template['type'] === 'baton-hero' ) :
			?>
				<div class="in note-widget-in note-hero-widget-in note-baton-hero-in note-baton-hero-widget-in cf">
			<?php
				endif;
			endif;
		}

		/**
		 * This function outputs a closing "in" wrapper element after the widget template content
		 * on Note Widgets that have a Hero display selected.
		 */
		public function note_widget_template_after( $template_name, $template, $data, $number, $instance, $args, $widget ) {
			// If we have a Hero display
			if ( isset( $instance['template'] ) && array_key_exists( $instance['template'], $widget->templates ) ) :
				// Grab the template details for this widget
				$widget_template = $widget->templates[$instance['template']];

				if ( isset( $widget_template['type'] ) && $widget_template['type'] === 'baton-hero' ) :
			?>
				</div>
			<?php
				endif;
			endif;
		}


		/*****************
		 * Note Sidebars *
		 *****************/

		/**
		 * This function sanitizes Note Baton data from the Customizer.
		 */
		public function note_baton_sanitize_callback( $input ) {
			// Decode JSON data to an associative array if necessary
			$input = ( ! is_array( $input ) ) ? json_decode( $input, true ) : $input;

			// Reference for sanitized data
			$sanitized_input = array();

			// Note Baton
			if ( ! empty( $input ) && is_array( $input ) ) {
				// Loop through Note "post_id" data
				foreach ( $input as $post_id => $data )
					// Loop through data
					foreach( $data as $key => $value ) {
						$the_post_id = sanitize_text_field( $post_id );
						$the_data_key = sanitize_text_field( $key );

						// If this "post_id" doesn't exist in sanitized data yet
						if ( ! isset( $sanitized_input[$the_post_id] ) )
							$sanitized_input[$the_post_id] = array();

						// Store sanitized data
						$sanitized_input[$the_post_id][$the_data_key] = sanitize_text_field( $value );
					}
			}

			$input = $sanitized_input;

			return $input;
		}

		/**
		 * This function adjusts the defaults for Note options.
		 */
		public function note_options_defaults( $defaults ) {
			// Note Baton
			if ( ! isset( $defaults['baton'] ) )
				$defaults['baton'] = array();

			return $defaults;
		}

		/**
		 * This function registers Note Sidebar locations within Baton.
		 */
		public function note_sidebar_locations( $sidebar_locations ) {
			// Baton Content Wrapper
			if ( ! in_array( 'baton-content-wrapper', $sidebar_locations ) ) {
				$sidebar_locations['baton-content-wrapper'] = array(
					'before' => 'baton-content-wrapper-before', // Baton Content Wrapper Before
					'after' => 'baton-content-wrapper-after' // Baton Content Wrapper After
				);

				// Store references to the sidebar IDs on this class
				$this->note_sidebar_ids_by_location['baton-content-wrapper'] = $sidebar_locations['baton-content-wrapper'];
				$this->note_sidebar_ids[] = $sidebar_locations['baton-content-wrapper']['before'];
				$this->note_sidebar_ids[] = $sidebar_locations['baton-content-wrapper']['after'];
			}

			return $sidebar_locations;
		}

		/**
		 * This function determines whether or not Note should localize Note Sidebar arguments
		 * in the Customizer. For Baton, this is always true as we need to allow sidebars on any content piece.
		 * 
		 * Core Note Sidebar logic will continue to function only on single content pieces even though
		 * this value is true.
		 */
		public function note_customizer_localize_sidebar_args( $localize ) {
			/*
			 * Note is currently setup to only add sidebars to singular content. Baton allows Note Sidebars
			 * on any content piece so we're setting this value to true to ensure Note Sidebar arguments
			 * are localized properly across all content pieces.
			 */
			if ( ! $localize )
				$localize = true;

			return $localize;
		}

		/**
		 * This function determines the "post_id" for Note Sidebar arguments. The "post_id" value is expected
		 * to be unique for each content piece so we're using the queried object (front-end) to generate a value.
		 */
		public function note_customizer_sidebar_args_post_id( $post_id, $previewer, $note_sidebars ) {
			global $post, $wp_query;

			/*
			 * Note is currently setup to only add sidebars to singular pieces of content. We're creating
			 * a unique "post_id" value based on the current queried object and type of content currently being
			 * viewed. Each "post_id" value contains a prefix and then a "type" followed by the unique portion
			 * of the queried object.
			 *
			 * This function also stores the unique value on this class in multiple locations.
			 */

			// Grab the queried object
			$queried_object = get_queried_object();

			// If we're on an archive (including the blog) or a singular content piece and we don't have a "post_id" or the "post_id" value matches the global post
			if ( ( is_archive() || is_home() || is_singular() ) && ( ! $post_id || ( is_int( $post_id ) && $post_id === $post->ID ) ) ) {
				// Category, Tag, or Taxonomy (WP_Term)
				if ( is_category() || is_tag() || is_tax() ) {
					// Category
					if ( is_category() )
						$post_id = $this->note_post_id = $this->note_post_ids[] = $this->note_sidebar_prefix . '-cat-' . $queried_object->term_id;
					// Tag
					else if ( is_tag() )
						$post_id = $this->note_post_id = $this->note_post_ids[] = $this->note_sidebar_prefix . '-tag-' . $queried_object->term_id;
					// Taxonomy
					else if ( is_tax() )
						$post_id = $this->note_post_id = $this->note_post_ids[] = $this->note_sidebar_prefix . '-tax-' . $queried_object->term_id; // TODO: Technically taxonomies can contain hyphens in taxonomy name (this could affect our parsing logic)
				}
				// Post Type Archive
				else if ( is_post_type_archive() )
					$post_id = $this->note_post_id = $this->note_post_ids[] = $this->note_sidebar_prefix . '-post_type-' . $queried_object->name; // Underscore to allow for parsing during note_sidebar_arguments; TODO: Technically post types can contain hyphens in post type name (this could affect our parsing logic)
				// Home (Blog) Archive (WP_Post)
				else if ( is_home() ) {
					// If this is the front page (posts are set to display on the front page)
					if ( is_front_page() && get_option( 'show_on_front' ) === 'posts' )
						$post_id = $this->note_post_id = $this->note_post_ids[] = $this->note_sidebar_prefix . '-blog-front_page';
					// Otherwise this is just a blog page (separate page from the front page)
					else
						$post_id = $this->note_post_id = $this->note_post_ids[] = $this->note_sidebar_prefix . '-blog-' . $queried_object->ID;
				}
				// Author (WP_User)
				else if ( is_author() )
					$post_id = $this->note_post_id = $this->note_post_ids[] = $this->note_sidebar_prefix . '-author-' . $queried_object->ID;
				// Singular (no "post_id")
				else if ( ! $post_id && is_singular() )
					$post_id = $post->ID;
			}

			return $post_id;
		}

		/**
		 * This function adjusts Note Sidebar arguments.
		 *
		 * Note builds the sidebar name based off the single post data, but since we're adding Note Sidebars
		 * to all content pieces, we need to build this off of the queried object data instead.
		 *
		 * In the admin we use data stored in Note Options to determine arguments for certain content pieces.
		 */
		public function note_sidebar_args( $sidebar_args, $sidebar_id, $post_id ) {
			global $post;

			// Grab Note Options
			$note_options = Note_Options::get_options();

			// If we're in the admin
			if ( is_admin() ) {
				// If this is a Baton Note Sidebar (not stored)
				if ( in_array( $sidebar_id, $this->note_sidebar_ids ) ) {
					// Store the current "post_id" value on this class to allow for the logic below to run
					$this->note_post_id = $post_id;

					// Parse the "post_id" to determine content type and ID
					$baton_post_id = explode( '-', $post_id );

					// Create an associative array to reference data more easily
					$baton_post_id = array(
						'type' => ( isset( $baton_post_id[1] ) ) ? $baton_post_id[1] : false, // [1] is the type of content piece the sidebar was created for
						'ID' => ( isset( $baton_post_id[2] ) ) ? $baton_post_id[2] : false // [2] is the unique ID for the sidebar
					);
				}
			}

			// Grab the queried object
			$queried_object = get_queried_object();

			// If the sidebar ID matches one of the registered locations and the "post_id" is from Baton or we're on a singular piece of content and have a post ID as the "post_id" value
			if ( in_array( $sidebar_id, $this->note_sidebar_ids ) && ( $post_id === $this->note_post_id || ( is_singular() && is_int( $post_id ) ) ) ) {
				// If we're in the admin we have to define these conditional variables based on the type of sidebar
				if ( is_admin() ) {
					$is_category = ( $baton_post_id['type'] === 'cat' );
					$is_tag = ( $baton_post_id['type'] === 'tag' );
					$is_tax = ( $baton_post_id['type'] === 'tax' );
					$is_post_type_archive = ( $baton_post_id['type'] === 'post_type' );
					$is_home = ( $baton_post_id['type'] === 'blog' );
					$is_author = ( $baton_post_id['type'] === 'author' );
				}

				// Category, Tag, or Taxonomy (WP_Term)
				if ( is_category() || is_tag() || is_tax() || ( is_admin() && ( $is_category || $is_tag || $is_tax ) ) ) {
					// Category
					if ( is_category() || ( is_admin() && $is_category ) ) {
						// If we're in the admin
						if ( is_admin() )
							// Mimic the correct "queried object"
							$queried_object = get_category( $baton_post_id['ID'] );

						// Adjust the sidebar name
						$sidebar_args['name'] = sprintf( __( 'Category - %1$s - %2$s', 'baton-pro' ), $queried_object->name, $sidebar_args['name'] );
					}
					// Tag
					else if ( is_tag() || ( is_admin() && $is_tag ) ) {
						// If we're in the admin
						if ( is_admin() )
							// Mimic the correct "queried object"
							$queried_object = get_tag( $baton_post_id['ID'] );

						// Adjust the sidebar name
						$sidebar_args['name'] = sprintf( __( 'Tag - %1$s - %2$s', 'baton-pro' ), $queried_object->name, $sidebar_args['name'] );
					}
					// Taxonomy
					else if ( is_tax() || ( is_admin() && $is_tax ) ) {
						// Grab the taxonomy
						$taxonomy = ( $queried_object && ! is_wp_error( $queried_object ) ) ? get_taxonomy( $queried_object->taxonomy ) : false;

						// If we don't have a queried object or a taxonomy yet, check if we have data stored in Note Options
						if ( ( ! $queried_object || is_wp_error( $queried_object ) || ! $taxonomy || is_wp_error( $taxonomy ) ) && isset( $note_options['baton'][$post_id] ) )
							// Adjust the sidebar name
							$sidebar_args['name'] = sprintf( __( '%1$s - %2$s - %3$s', 'baton-pro' ), $note_options['baton'][$post_id]['tax_label'], $note_options['baton'][$post_id]['term_label'], $sidebar_args['name'] ); // TODO: _x()
						// Otherwise use the queried object
						else
							// Adjust the sidebar name
							$sidebar_args['name'] = sprintf( __( '%1$s - %2$s - %3$s', 'baton-pro' ), $taxonomy->labels->name, $queried_object->name, $sidebar_args['name'] ); // TODO: _x()
					}
				}
				// Post Type Archive
				else if ( is_post_type_archive() || ( is_admin() && $is_post_type_archive ) ) {
					// If we're in the admin
					if ( is_admin() )
						// Mimic the correct "queried object"
						$queried_object = get_post_type_object( $baton_post_id['ID'] );

					// If we don't have a queried object yet, check if we have data stored in Note Options
					if ( ( ! $queried_object || is_wp_error( $queried_object ) ) && isset( $note_options['baton'][$post_id] ) )
						// Adjust the sidebar name
						$sidebar_args['name'] = sprintf( __( 'Post Type Archive - %1$s - %2$s', 'baton-pro' ), $note_options['baton'][$post_id]['label'], $sidebar_args['name'] );
					// Otherwise use the queried object
					else
						// Adjust the sidebar name
						$sidebar_args['name'] = sprintf( __( 'Post Type Archive - %1$s - %2$s', 'baton-pro' ), $queried_object->labels->name, $sidebar_args['name'] );
				}
				// Home (Blog) Archive (WP_Post)
				else if ( is_home() || ( is_admin() && $is_home ) ) {
					// If this is the front page (posts are set to display on the front page)
					if ( ( is_front_page() && get_option( 'show_on_front' ) === 'posts' ) || ( is_admin() && $baton_post_id['ID'] === 'front_page' ) )
						// Adjust the sidebar name
						$sidebar_args['name'] = sprintf( __( 'Blog (Front Page) - %1$s', 'baton-pro' ), $sidebar_args['name'] );
					// Otherwise this is just a blog page (separate page from the front page)
					else
						// Adjust the sidebar name
						$sidebar_args['name'] = sprintf( __( 'Blog - %1$s', 'baton-pro' ), $sidebar_args['name'] );
				}
				// Author (WP_User)
				else if ( is_author() || ( is_admin() && $is_author ) ) {
					// If we're in the admin
					if ( is_admin() )
						// Mimic the correct "queried object"
						$queried_object = get_user_by( 'id', $baton_post_id['ID'] );

					// Adjust the sidebar name
					$sidebar_args['name'] = sprintf( __( 'Author Archive - %1$s - %2$s', 'baton-pro' ), $queried_object->data->display_name, $sidebar_args['name'] );
				}

				// Adjust the sidebar description (use updated sidebar name instead of original generic name; Note uses "post_id" to grab the title of the current singular content piece)
				$sidebar_args['description'] = sprintf( __( 'This is the %1$s widget area.', 'baton-pro' ), $sidebar_args['name'] );

				// Store a reference to the "post_id" on this class
				$this->note_post_ids_by_sidebar_id[$sidebar_id] = $post_id;

				// Store a reference to the Note Sidebar ID on this class (unique)
				if ( ! in_array( $sidebar_args['id'], $this->note_registered_sidebar_ids ) )
					$this->note_registered_sidebar_ids[] = $sidebar_args['id'];
			}

			return $sidebar_args;
		}

		/**
		 * This function outputs Baton Note Sidebar markup before the Baton content wrapper.
		 */
		public function baton_content_wrapper_before() {
			global $post, $wp_registered_sidebars;

			// Bail if Note doesn't exist
			if ( ! class_exists( 'Note' ) )
				return;

			// If we're on an archive (including the blog) or a singular piece of content
			if ( is_archive() || is_home() || is_singular() ) :
				// Grab the Note Sidebars reference
				$note_sidebars = Note_Sidebars();

				// Grab the Baton content wrapper sidebar ID and "post_id"
				$baton_content_wrapper_sidebar_ids = $this->note_sidebar_ids_by_location['baton-content-wrapper'];
				$sidebar_id = $baton_content_wrapper_sidebar_ids['before'];
				$post_id = ( isset( $this->note_post_ids_by_sidebar_id[$sidebar_id] ) ) ? $this->note_post_ids_by_sidebar_id[$sidebar_id] : false;

				// If we don't have a valid "post_id", try to generate one
				if ( ! $post_id || ( is_singular() && is_int( $post_id ) && $post_id !== $post->ID ) )
					// Grab the correct "post_id" for this content piece
					$post_id = $this->note_customizer_sidebar_args_post_id( '', false, $note_sidebars );


				// If we have a valid "post_id" (if the sidebar is registered; on pages where the sidebar is not registered, the sidebar ID value is set to a generic value)
				if ( $post_id ) :
					// Grab the Note Sidebar ID (store a reference on this class)
					$note_sidebar_id = $this->current_note_sidebar_id = Note_Sidebars::get_sidebar_id( $sidebar_id, $post_id );
					$is_registered_sidebar = ( function_exists( 'is_registered_sidebar' ) ) ? is_registered_sidebar( $note_sidebar_id ) : isset( $wp_registered_sidebars[$note_sidebar_id] );
			?>
					<div class="baton-note-sidebar baton-content-wrapper-before baton-content-wrapper-note-sidebar-wrap <?php echo ( $is_registered_sidebar ) ? 'registered' : 'placeholder in baton-note-sidebar-in baton-note-sidebar-placeholder-in' ; ?> <?php echo ( is_active_sidebar( $note_sidebar_id ) ) ? 'widgets' : 'no-widgets in baton-note-sidebar-in baton-note-sidebar-inactive-in'; ?>">
						<?php echo Note_Sidebars::sidebar( $sidebar_id, $post_id ); // Note Sidebar (Baton Content Wrapper Before) ?>
					</div>
			<?php
					// Reset stored Note "post_id" reference
					$this->note_post_id = false;

					// Reset stored Note Sidebar ID reference
					$this->current_note_sidebar_id = false;
				endif;
			endif;
		}

		/**
		 * This function outputs Baton Note Sidebar markup after the Baton content wrapper.
		 */
		public function baton_content_wrapper_after() {
			global $post, $wp_registered_sidebars;

			// Bail if Note doesn't exist
			if ( ! class_exists( 'Note' ) )
				return;

			// If we're on an archive (including the blog) or a singular piece of content
			if ( is_archive() || is_home() || is_singular() ) :
				// Grab the Note Sidebars reference
				$note_sidebars = Note_Sidebars();

				// Grab the Baton content wrapper sidebar ID and "post_id"
				$baton_content_wrapper_sidebar_ids = $this->note_sidebar_ids_by_location['baton-content-wrapper'];
				$sidebar_id = $baton_content_wrapper_sidebar_ids['after'];
				$post_id = ( isset( $this->note_post_ids_by_sidebar_id[$sidebar_id] ) ) ? $this->note_post_ids_by_sidebar_id[$sidebar_id] : false;

				// If we don't have a valid "post_id", try to generate one
				if ( ! $post_id || ( is_singular() && is_int( $post_id ) && $post_id !== $post->ID ) )
					// Grab the correct "post_id" for this content piece
					$post_id = $this->note_customizer_sidebar_args_post_id( '', false, $note_sidebars );


				// If we have a valid "post_id" (if the sidebar is registered; on pages where the sidebar is not registered, the sidebar ID value is set to a generic value)
				if ( $post_id ) :
					// Store a reference to the "post_id" on this class
					$this->note_post_id = $post_id;

					// Grab the Note Sidebar ID (store a reference on this class)
					$note_sidebar_id = $this->current_note_sidebar_id = Note_Sidebars::get_sidebar_id( $sidebar_id, $post_id );
					$is_registered_sidebar = ( function_exists( 'is_registered_sidebar' ) ) ? is_registered_sidebar( $note_sidebar_id ) : isset( $wp_registered_sidebars[ $note_sidebar_id ] );
			?>
					<div class="baton-note-sidebar baton-content-wrapper-after baton-content-wrapper-note-sidebar-wrap <?php echo ( $is_registered_sidebar ) ? 'registered' : 'placeholder in baton-note-sidebar-in baton-note-sidebar-placeholder-in' ; ?> <?php echo ( is_active_sidebar( $note_sidebar_id ) ) ? 'widgets' : 'no-widgets in baton-note-sidebar-in baton-note-sidebar-inactive-in'; ?>">
						<?php echo Note_Sidebars::sidebar( $sidebar_id, $post_id ); // Note Sidebar (Baton Content Wrapper After) ?>
					</div>
			<?php
					// Reset stored Note "post_id" reference
					$this->note_post_id = false;

					// Reset stored Note Sidebar ID reference
					$this->current_note_sidebar_id = false;
				endif;
			endif;
		}


		/*************
		 * Conductor *
		 *************/

		/**
		 * This function adjusts Conductor content layouts.
		 */
		public function conductor_content_layouts( $content_layouts ) {
			// Baton Landing Page
			if ( ! isset( $content_layouts['baton-landing-page'] ) )
				$content_layouts['baton-landing-page'] = array(
					// Label for this content layout
					'label' => __( 'Baton Landing Page', 'baton-pro' ),
					// Preview HTML for this content layout(required; %1$s is replaced with values below on options panel if specified)
					'preview' => '<div class="cols cols-1">
						<div class="col col-content" title="%1$s" style="font-size: 12px;">
							<span class="label">%1$s</span>
						</div>
					</div>',
					// Preview values for this content layout(values that will be applied to the layout preview above)
					'preview_values' => array( __( 'Baton Landing Page', 'baton-pro' ) ),
					// Body classes appended when this layout is selected
					'body_class' => array(
						'conductor-cols-1', // Conductor
						'baton-conductor',
						'baton-conductor-landing-page'
					)
				);

			return $content_layouts;
		}

		/**
		 * This function adjusts sidebar parameters of Conductor sidebars.
		 */
		public function conductor_sidebar_args( $sidebar_args, $conductor_sidebar_id, $content_layout, $content_layouts ) {
			// before_widget
			$sidebar_args['before_widget'] = str_replace( 'section', 'div', $sidebar_args['before_widget'] );

			// after_widget
			$sidebar_args['after_widget'] = str_replace( 'section', 'div', $sidebar_args['after_widget'] );

			return $sidebar_args;
		}

		/**
		 * This function adjusts the default setting values for Conductor widgets.
		 */
		public function conductor_widget_defaults( $defaults, $widget ) {
			// If Conductor has flexbox display
			if ( $this->conductor_has_flexbox_display( $widget ) )
				// Adjust default widget size (display) to flexbox
				$defaults['widget_size'] = 'flexbox';

			$author_byline = array();

			// Loop through output elements
			foreach ( $defaults['output'] as $priority => $output ) {
				// Read More
				if ( $output['id'] === 'read_more' ) {
					// Adjust default label to match Baton more link label
					$defaults['output'][$priority]['label'] = baton_more_link_label();
				}

				// Author Byline (store reference to priority and configuration)
				if ( $output['id'] === 'author_byline' ) {
					$author_byline = $output;

					// Remove author byline
					unset( $defaults['output'][$priority] );
				}
			}

			// Read More output features (Baton deault)
			$defaults['output_features']['read_more']['edit_label']['default'] = _x( 'Continue Reading', '"read more" label for Conductor widgets', 'baton-pro' );

			/*
			 * Author Byline (move to bottom of default output elements)
			 */
			$output_elements = array();
			$default_priority_gap = 10;
			$count = 0;

			// Loop through the passed in widget settings
			foreach ( $defaults['output'] as $output ) {
				// Increase count
				$count++;

				// Add this element to the output elements
				$output_elements[( $default_priority_gap * $count )] = $output;
			}

			// Author Byline (increase count before multiplying)
			$output_elements[( $default_priority_gap * ++$count )] = $author_byline;

			// Set the default output
			$defaults['output'] = $output_elements;

			return $defaults;
		}

		/**
		 * This function depreciates legacy display options from Conductor Widgets.
		 */
		public function conductor_widget_displays( $conductor_widget_displays, $instance, $widget ) {
			// Only if the flexbox display exists
			if ( isset( $conductor_widget_displays['flexbox'] ) ) {
				// Remove Small legacy display
				if ( isset( $conductor_widget_displays['small'] ) )
					unset( $conductor_widget_displays['small'] );

				// Remove Medium legacy display
				if ( isset( $conductor_widget_displays['medium'] ) )
					unset( $conductor_widget_displays['medium'] );

				// Remove Large legacy display
				if ( isset( $conductor_widget_displays['large'] ) )
					unset( $conductor_widget_displays['large'] );
			}

			return $conductor_widget_displays;
		}

		/**
		 * This function adjusts the callback functions for various output elements.
		 */
		public function conductor_widget_instance( $instance, $args, $widget ) {
			// If we have output elements (i.e. this isn't a brand new Conductor Widget)
			if ( isset( $instance['output'] ) && ! empty( $instance['output'] ) )
				// Adjust the callback output elements
				foreach ( $instance['output'] as $priority => &$element ) {
					// Featured Image
					if ( $element['id'] === 'featured_image' )
						$element['callback'] = array( $this, 'conductor_widget_featured_image' );

					// Post Title
					if ( $element['id'] === 'post_title' )
						$element['callback'] = array( $this, 'conductor_widget_post_title' );

					// Post Content
					if ( $element['id'] === 'post_content' )
						$element['callback'] = array( $this, 'conductor_widget_post_content' );

					// Read More
					if ( $element['id'] === 'read_more' )
						$element['callback'] = array( $this, 'conductor_widget_read_more' );

					// Author Byline
					if ( $element['id'] === 'author_byline' )
						$element['callback'] = array( $this, 'conductor_widget_author_byline' );
				}

			return $instance;
		}

		/**
		 * This function moves the opening Conductor content wrapper opening element on Conductor Widgets.
		 */
		public function conductor_widget_display_content( $post, $instance, $widget, $conductor_widget_query ) {
			// Bail if this isn't a flexbox display
			if ( $instance['widget_size'] !== 'flexbox' )
				return;

			$output_elements_before_featured_image = 0;
			$featured_image_priority = 0;
			$featured_image_only = true; // Flag to determine if the featured image is the only visible output element

			// If we have hooks
			if ( ! empty( $conductor_widget_query->hooks ) && isset( $conductor_widget_query->hooks['conductor_widget_display_content_' . $widget->number] ) ) {
				// Store a reference to the list of hooks for this widget
				$hooks = &$conductor_widget_query->hooks['conductor_widget_display_content_' . $widget->number];

				// Loop through hooks to find the featured image priority
				foreach ( $hooks as $priority => $callback )
					// conductor_widget_featured_image; $callback[1] is the function name
					if ( is_array( $callback ) && $callback[1] === 'conductor_widget_featured_image' ) {
						$featured_image_priority = $priority;
						break;
					}

				// If we have a featured image priority
				if ( $featured_image_priority ) {
					// Determine if only the featured image is visible
					foreach ( $instance['output'] as $priority => $output ) {
						if ( $output['id'] !== 'featured_image' && $output['visible'] === true )
							$featured_image_only = false;

						// Increase the count of elements before the featured image
						if ( $output['id'] !== 'featured_image' && $priority < $featured_image_priority )
							$output_elements_before_featured_image++;
					}

					// If we have more than a featured image to output
					if ( ! $featured_image_only )
						// Loop through hooks again
						foreach ( $hooks as $priority => $callback )
							// conductor_widget_content_wrapper; $callback[1] is the function name
							if ( is_array( $callback ) && $callback[1] === 'conductor_widget_content_wrapper' && $priority < $featured_image_priority ) {
								// Determine new priority for content wrapper opening element
								$new_priority = ( $priority + $featured_image_priority );

								// Remove the default action (if there are no output elements before the featured image)
								if ( ! $output_elements_before_featured_image ) {
									remove_action( 'conductor_widget_display_content_' . $widget->number, array( $conductor_widget_query, $callback[1] ), $priority );

									// Adjust the "hooks" property
									unset( $hooks[$priority] );
								}
								// Otherwise we have elements before the featured image, ensure the default wrapper is closed
								else {
									// Determine new priority for content wrapper closing element
									$closing_wrapper_priority = ( $featured_image_priority - $priority );

									// Add the action before the featured image
									add_action( 'conductor_widget_display_content_' . $widget->number, array( $conductor_widget_query, 'conductor_widget_content_wrapper_close' ), $closing_wrapper_priority, $conductor_widget_query->display_content_args_count );

									// Adjust the "hooks" property
									$hooks += array( $closing_wrapper_priority => array( get_class( $conductor_widget_query ), 'conductor_widget_content_wrapper_close' ) ); // Static callback
								}

								// Add the action after the featured image element
								add_action( 'conductor_widget_display_content_' . $widget->number, array( $conductor_widget_query, $callback[1] ), $new_priority, $conductor_widget_query->display_content_args_count );

								// Adjust the "hooks" property
								$hooks += array( $new_priority => array( get_class( $conductor_widget_query ), $callback[1] ) ); // Static callback
								ksort( $hooks ); // Sort the hooks by key
							}
				}
			}
		}

		/**
		 * This function adjusts the featured image size on Conductor widgets only if a size has not been selected by the user.
		 */
		public function conductor_widget_featured_image_size( $size, $instance ) {
			// Only adjust the size if a user has not selected one
			if ( isset( $instance['post_thumbnails_size'] ) && ! empty( $instance['post_thumbnails_size'] ) )
				return $instance['post_thumbnails_size'];

			// If we have a widget size
			if ( isset( $instance['widget_size'] ) ) {
				// Switch based on widget size
				switch ( $instance['widget_size'] ) {
					// Flexbox
					case 'flexbox':
						// Switch based on number of flexbox columns
						switch ( $instance['flexbox']['columns'] ) {
							// "Large"
							case 1:
								$size = 'baton-conductor-large';
							break;

							// "Medium"
							case 2:
							case 3:
								$size = 'baton-conductor-medium';
							break;

							// "Small"
							case 4:
							case 5:
								$size = 'baton-conductor-small';
							break;
							case 6:
								$size = 'thumbnail';
							break;
						}
					break;
				}
			}

			return $size;
		}


		/*********************
		 * Conductor Display *
		 *********************/

		/**
		 * This function adjusts the CSS classes on the widget wrapper element.
		 */
		public function conductor_widget_wrapper_css_classes( $css_classes, $post, $instance, $widget, $query ) {
			// Bail if this isn't a flexbox display
			if ( $instance['widget_size'] !== 'flexbox' )
				return $css_classes;

			// If we have hooks
			if ( ! empty( $query->hooks ) && isset( $query->hooks['conductor_widget_display_content_' . $widget->number] ) ) {
				$content_wrapper_elements = 0;

				// Store a reference to the list of hooks for this widget
				$hooks = &$query->hooks['conductor_widget_display_content_' . $widget->number];

				// Loop through hooks to find the featured image priority
				foreach ( $hooks as $priority => $callback )
					// conductor_widget_content_wrapper; $callback[1] is the function name
					if ( is_array( $callback ) && $callback[1] === 'conductor_widget_content_wrapper' )
						$content_wrapper_elements++;

				// If there are multiple content wrapper elements
				if ( $content_wrapper_elements > 1 ) {
					// Explode the CSS classes
					$css_classes = explode( ' ', $css_classes );

					// Add CSS classes
					$css_classes[] = 'multiple-content-wrapper-elements';
					$css_classes[] = 'conductor-multiple-content-wrapper-elements';
					$css_classes[] = 'baton-multiple-content-wrapper-elements';

					// Ensure CSS classes are a string
					$css_classes = implode( ' ', $css_classes );
				}
			}

			return $css_classes;
		}

		/**
		 * This function adjusts the HTML element used for content wrapper elements.
		 */
		public function conductor_widget_content_wrapper_html_element( $element ) {
			return 'article';
		}

		/**
		 * This function adjusts the CSS classes on the content wrapper element.
		 */
		public function conductor_widget_content_wrapper_css_classes( $css_classes, $post, $instance, $widget, $query ) {
			// List widget size (display) only
			if ( $instance['widget_size'] === 'list' )
				// Remove the "content" CSS class (only replacing once)
				$css_classes = preg_replace( '/content /', '', $css_classes, 1 );

			// If we have output elements
			if ( isset( $instance['output'] ) ) {
				// Keep track of the post_title and read_more output element priority
				$featured_image_priority = $post_title_priority = $read_more_priority = $author_byline_priority = -1;

				// Loop through output elements
				foreach ( $instance['output'] as $priority => $output ) {
					// Post Content (if hidden)
					if ( $output['id'] === 'post_content' && $output['visible'] === false )
						$css_classes .= ' no-content no-post-content';

					// Featured Image
					if ( $output['id'] === 'featured_image' )
						$featured_image_priority = $priority;

					// Post Title
					if ( $output['id'] === 'post_title' )
						$post_title_priority = $priority;

					// Read More
					if ( $output['id'] === 'read_more' )
						$read_more_priority = $priority;

					// Author Byine
					if ( $output['id'] === 'author_byline' )
						$author_byline_priority = $priority;
				}

				// If post_title output element appears right before the read_more element (10 is the default priority padding)
				if ( ( $post_title_priority + 10 ) === $read_more_priority )
					$css_classes .= ' post-title-before-read-more';

				// If featured_image output element appears right before the author_byline element (10 is the default priority padding)
				if ( ( $featured_image_priority + 10 ) === $author_byline_priority )
					$css_classes .= ' featured-image-before-author-byline';

				// If featured_image output element appears right after the author_byline element (10 is the default priority padding)
				if ( ( $author_byline_priority + 10 ) === $featured_image_priority )
					$css_classes .= ' featured-image-after-author-byline';
			}

			return $css_classes;
		}

		/**
		 * This function adjusts the CSS classes on the before_widget wrapper element on Conductor Widgets
		 * with flexbox displays on the Front Page sidebar only.
		 */
		public function conductor_widget_before_widget_css_classes( $css_classes, $params, $instance, $conductor_widget_settings, $widget ) {
			// Front Page Sidebar only
			if ( $params[0]['id'] === 'front-page-sidebar' ) {
				$css_classes[] = 'in';
				$css_classes[] = 'front-page-widget-in';
				$css_classes[] = 'cf';
			}

			return $css_classes;
		}

		/**
		 * This function outputs the featured image for Conductor Widgets.
		 */
		public function conductor_widget_featured_image( $post, $instance, $widget, $query ) {
			// Find the featured image output element data
			$priority = $instance['output_elements']['featured_image'];
			$output = $instance['output'][$priority];

			if ( has_post_thumbnail( $post->ID ) ) :
				do_action( 'conductor_widget_featured_image_before', $post, $instance );

				// Output desired featured image size
				if ( ! empty( $instance['post_thumbnails_size'] ) )
					$conductor_thumbnail_size = $instance['post_thumbnails_size'];
				else
					$conductor_thumbnail_size = ( $instance['widget_size'] !== 'small' ) ? $instance['widget_size'] : 'thumbnail';

				$conductor_thumbnail_size = apply_filters( 'conductor_widget_featured_image_size', $conductor_thumbnail_size, $instance, $post );
		?>
				<!-- Post Thumbnail/Featured Image -->
				<div class="article-thumbnail-wrap article-featured-image-wrap post-thumbnail-wrap featured-image-wrap cf">
					<?php sds_featured_image( ( bool ) $output['link'], $conductor_thumbnail_size ); ?>
				</div>
				<!-- End Post Thumbnail/Featured Image -->
		<?php
				do_action( 'conductor_widget_featured_image_after', $post, $instance );
			endif;
		}

		/**
		 * This function outputs the post title for Conductor Widgets.
		 */
		public function conductor_widget_post_title( $post, $instance, $widget, $query ) {
			// Find the post title output element data
			$priority = $instance['output_elements']['post_title'];
			$output = $instance['output'][$priority];

			do_action( 'conductor_widget_post_title_before', $post, $instance );
		?>
			<!-- Article Header -->
			<header class="article-title-wrap">
				<?php baton_categories_tags( true ) ?>

				<?php if ( strlen( get_the_title() ) > 0 ) : ?>
					<h1 class="article-title">
						<?php
							// Link post title to post
							if ( $output['link'] ) :
						?>
							<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
						<?php
							// Just output the post title
							else:
								the_title();
							endif;
						?>
					</h1>
				<?php endif; ?>
			</header>
			<!-- End Article Header -->
		<?php
			do_action( 'conductor_widget_post_title_after', $post, $instance );
		}

		/**
		 * This function outputs the post content for Conductor Widgets.
		 */
		public function conductor_widget_post_content( $post, $instance, $widget, $query ) {
			do_action( 'conductor_widget_post_content_before', $post, $instance );
		?>
			<!-- Article Content -->
			<div class="article-content cf">
		<?php
			// Determine which type of content to output
			switch ( $instance['content_display_type'] ) {
				// Excerpt - the_excerpt()
				case 'excerpt':
					echo $query->get_excerpt_by_id( $post, $instance['excerpt_length'] );
				break;

				// the_content()
				case 'content':
				default:
					echo $query->get_content_by_id( $post );
				break;
			}
		?>
			</div>
			<!-- End Article Content -->
		<?php
			do_action( 'conductor_widget_post_content_after', $post, $instance );
		}

		/**
		 * This function outputs the read more link for Conductor Widgets.
		 */
		public function conductor_widget_read_more( $post, $instance, $widget, $query ) {
			// Find the read more output element data
			$priority = $instance['output_elements']['read_more'];
			$output = $instance['output'][$priority];

			do_action( 'conductor_widget_read_more_before', $post, $instance );
		?>
			<!-- Article Content -->
			<div class="article-content article-content-more-link cf">
				<p>
		<?php
			// Link read more to post
			if ( $output['link'] ) :
		?>
				<a class="more read-more more-link button" href="<?php echo get_permalink( $post->ID ); ?>">
					<?php echo $output['label']; ?>
				</a>
		<?php
			// Just output the read more
			else:
				echo $output['label'];
			endif;
		?>
				</p>
			</div>
			<!-- End Article Content -->
		<?php
			do_action( 'conductor_widget_read_more_after', $post, $instance );
		}

		/**
		 * This function outputs the author byline for Conductor Widgets.
		 */
		public function conductor_widget_author_byline( $post, $instance, $widget, $query ) {
			do_action( 'conductor_widget_author_byline_before', $post, $instance );

			// Force the post meta to be displayed, regardless of SDS Theme Options settings or post type
			sds_post_meta( true, true );

			do_action( 'conductor_widget_author_byline_after', $post, $instance );
		}

		/**
		 * This function adjusts the opening content wrapper on Conductor layouts.
		 */
		public function conductor_content_wrapper_element_before( $wrapper ) {
			$wrapper = '<!-- Main --><main role="main" class="content-wrap content-wrap-conductor baton-flex ';
			$wrapper .= ( baton_is_yoast_breadcrumbs_active() ) ? 'has-breadcrumbs' : 'no-breadcrumbs';
			$wrapper .= '">';

			return $wrapper;
		}

		/**
		 * This function adjusts the closing content wrapper on Conductor layouts.
		 */
		public function conductor_content_wrapper_element_after( $wrapper ) {
			$wrapper = '<div class="clear"></div></main><!-- End Main -->';

			return $wrapper;
		}

		/**
		 * This function adjusts the content opening wrapper on Conductor layouts.
		 */
		public function conductor_content_element_before( $wrapper ) {
			$wrapper = '<!-- Home/Blog Content --><div class="conductor-content baton-col baton-col-content baton-col-conductor-content ' . Conductor::get_conductor_content_layout_sidebar_id( 'content' ) . '" data-sidebar-id="' . Conductor::get_conductor_content_layout_sidebar_id( 'content' ) . '">';
			$wrapper .= '<section class="content-container content-conductor-container">';
			$wrapper .= '<div class="conductor-inner conductor-cf">';

			return $wrapper;
		}

		/**
		 * This function adjusts the content closing wrapper on Conductor layouts.
		 */
		public function conductor_content_element_after( $wrapper ) {
			$wrapper = '</div></section></div><!-- End Home/Blog Content -->';

			return $wrapper;
		}

		/**
		 * This function adjusts the primary sidebar opening wrapper on Conductor layouts.
		 */
		public function conductor_primary_sidebar_element_before( $wrapper ) {
			$wrapper = '<!-- Primary Sidebar --><div class="conductor-sidebar conductor-primary-sidebar baton-col baton-col-sidebar baton-col-conductor-sidebar baton-col-conductor-primary-sidebar ' . Conductor::get_conductor_content_layout_sidebar_id( 'primary' ) . '" data-sidebar-id="' . Conductor::get_conductor_content_layout_sidebar_id( 'primary' ) . '">';
			$wrapper .= '<section class="sidebar-container sidebar-conductor-container sidebar-conductor-primary-sidebar-container">';
			$wrapper .= '<aside class="sidebar sidebar-conductor-primary">';
			$wrapper .= '<div class="conductor-inner conductor-cf">';

			return $wrapper;
		}

		/**
		 * This function adjusts the primary sidebar closing wrapper on Conductor layouts.
		 */
		public function conductor_primary_sidebar_element_after( $wrapper ) {
			$wrapper = '</div></aside></section></div><!-- End Primary Sidebar -->';

			return $wrapper;
		}

		/**
		 * This function adjusts the secondary sidebar opening wrapper on Conductor layouts.
		 */
		public function conductor_secondary_sidebar_element_before( $wrapper ) {
			$wrapper = '<!-- Secondary Sidebar --><div class="conductor-sidebar conductor-secondary-sidebar baton-col baton-col-sidebar baton-col-sidebar-secondary baton-col-conductor-sidebar baton-col-conductor-secondary-sidebar ' . Conductor::get_conductor_content_layout_sidebar_id( 'secondary' ) . '" data-sidebar-id="' . Conductor::get_conductor_content_layout_sidebar_id( 'secondary' ) . '">';
			$wrapper .= '<section class="sidebar-container sidebar-conductor-container sidebar-conductor-secondary-sidebar-container">';
			$wrapper .= '<aside class="sidebar sidebar-conductor-secondary">';
			$wrapper .= '<div class="conductor-inner conductor-cf">';

			return $wrapper;
		}

		/**
		 * This function adjusts the secondary sidebar closing wrapper on Conductor layouts.
		 */
		public function conductor_secondary_sidebar_element_after( $wrapper ) {
			$wrapper = '</div></aside></section></div><!-- End Secondary Sidebar -->';

			return $wrapper;
		}

		/**
		 * This function outputs a wrapper element before pagination on Conductor Widgets.
		 */
		public function conductor_widget_pagination_before() {
		?>
			<footer class="pagination">
		<?php
		}

		/**
		 * This function outputs a wrapper element after pagination on Conductor Widgets.
		 */
		public function conductor_widget_pagination_after() {
		?>
			</footer>
		<?php
		}


		/*****************
		 * Gravity Forms *
		 *****************/

		/**
		 * This function adds the HTML5 placeholder attribute to forms with a CSS class of the following:
		 * .mc-gravity, .mc_gravity, .mc-newsletter, .mc_newsletter classes
		 */
		public function gform_field_input( $input, $field, $value, $lead_id, $form_id ) {
			$form_meta = RGFormsModel::get_form_meta( $form_id ); // Get form meta

			// Ensure we have at least one CSS class
			if ( isset( $form_meta['cssClass'] ) ) {
				$form_css_classes = explode( ' ', $form_meta['cssClass'] );

				// Ensure the current form has one of our supported classes and alter the field accordingly if we're not on admin
				if ( ! is_admin() && array_intersect( $form_css_classes, array( 'mc-gravity', 'mc_gravity', 'mc-newsletter', 'mc_newsletter' ) ) )
					$input = '<div class="ginput_container"><input name="input_' . $field['id'] . '" id="input_' . $form_id . '_' . $field['id'] . '" type="text" value="" class="large" placeholder="' . $field['label'] . '" /></div>';
			}

			return $input;
		}

		/**
		 * This function alters the confirmation message on forms with a CSS class of the following:
		 * .mc-gravity, .mc_gravity, .mc-newsletter, .mc_newsletter classes
		 */
		public function gform_confirmation( $confirmation, $form, $lead, $ajax ) {
			// Ensure we have at least one CSS class
			if ( isset( $form['cssClass'] ) ) {
				$form_css_classes = explode( ' ', $form['cssClass'] );

				// Confirmation message is set and form has one of our supported classes (alter the confirmation accordingly)
				if ( $form['confirmation']['type'] === 'message' && array_intersect( $form_css_classes, array( 'mc-gravity', 'mc_gravity', 'mc-newsletter', 'mc_newsletter' ) ) )
					$confirmation = '<div class="mc-gravity-confirmation mc_gravity-confirmation mc-newsletter-confirmation mc_newsletter-confirmation">' . $confirmation . '</div>';
			}

			return $confirmation;
		}


		/***************
		 * WooCommerce *
		 ***************/

		/**
		 * This function adjusts the default WooCommerce Product settings.
		 */
		public function woocommerce_product_settings( $settings ) {
			// Loop through the WooCommerce product settings
			foreach ( $settings as &$setting ) {
				// Adjust the shop catalog image size
				if ( $setting['id'] === 'shop_catalog_image_size' ) {
					$setting['default']['width'] = $setting['default']['height'] = 550;

					$setting['default']['crop'] = 0;
				}

				// Adjust the shop single image size
				if ( $setting['id'] === 'shop_single_image_size' ) {
					$setting['default']['width'] = $setting['default']['height'] = 850;

					$setting['default']['crop'] = 0;
				}

				// Adjust the shop thumbnail image size
				if ( $setting['id'] === 'shop_thumbnail_image_size' ) {
					$setting['default']['width'] = $setting['default']['height'] = 175;

					$setting['default']['crop'] = 0;
				}
			}

			return $settings;
		}

		/**
		 * This function adjusts the WooCommerce shop catalog image size
		 */
		public function woocommerce_get_image_size_shop_catalog( $size ) {
			$size['width'] = $size['height'] = '550';

			return $size;
		}

		/**
		 * This function adjusts the WooCommerce shop single image size
		 */
		public function woocommerce_get_image_size_shop_single( $size ) {
			$size['width'] = $size['height'] = '850';

			return $size;
		}

		/**
		 * This function adjusts the WooCommerce shop thumbnail image size
		 */
		public function woocommerce_get_image_size_shop_thumbnail( $size ) {
			$size['width'] = $size['height'] = '175';

			return $size;
		}

		/**
		 * This function outputs a content wrapper element before WooCommerce output.
		 */
		public function woocommerce_before_main_content() {
		?>
			<!-- Main -->
			<main role="main" class="content-wrap content-wrap-page content-wrap-woocommerce content-wrap-full-width-page baton-flex baton-flex-1-columns">
				<!-- Page Content -->
				<div class="baton-col baton-col-content">
					<section class="content-container content-page-container content-woocommerce-container">
		<?php
		}

		/**
		 * This function outputs the opening article content wrapper element before the main WooCommerce content.
		 */
		public function woocommerce_before_main_content_article_content_wrapper_before() {
			// If we're not displaying a singular piece of content
			if ( ! is_singular() ) :
		?>
						<!-- Article -->
						<article id="post-<?php the_ID(); ?>" class="content-woocommerce cf">
		<?php
			endif;
		}

		/**
		 * This function adjusts the number of products per page on WooCommerce pages.
		 */
		public function loop_shop_per_page() {
			// Set the posts per page to 12
			return 12;
		}

		/**
		 * This function adjusts the number of columns per page on WooCommerce pages.
		 */
		public function loop_shop_columns() {
			// Set the columns to 3
			return 3;
		}

		/**
		 * This function outputs the opening thumbnail wrapper element.
		 */
		public function woocommerce_before_shop_loop_item_title_thumbnail_wrapper_before() {
		?>
			<!-- Post Thumbnail/Featured Image -->
			<div class="article-thumbnail-wrap article-featured-image-wrap post-thumbnail-wrap featured-image-wrap cf">
		<?php
		}

		/**
		 * This function outputs the closing thumbnail wrapper element.
		 */
		public function woocommerce_before_shop_loop_item_title_thumbnail_wrapper_after() {
		?>
			</div>
			<!-- End Post Thumbnail/Featured Image -->
		<?php
		}

		/**
		 * This function outputs the opening article content wrapper element before the product title.
		 */
		public function woocommerce_shop_loop_item_title() {
		?>
			<!-- Article Content -->
			<div class="article-content cf">
		<?php
		}

		/**
		 * This function outputs the closing article content wrapper element after the product title.
		 */
		public function woocommerce_after_shop_loop_item_title() {
		?>
				<div class="clear"></div>
			</div>
			<!-- End Article Content -->
		<?php
		}

		/**
		 * This function outputs the opening article content wrapper element before the add to cart link.
		 */
		public function woocommerce_after_shop_loop_item_article_content_wrapper_before() {
		?>
			<!-- Article Content -->
			<div class="article-content cf">
		<?php
		}

		/**
		 * This function outputs the closing article content wrapper element after the add to cart link.
		 */
		public function woocommerce_after_shop_loop_item_article_content_wrapper_after() {
		?>
				<div class="clear"></div>
			</div>
			<!-- End Article Content -->
		<?php
		}

		/**
		 * This function outputs the opening baton column wrapper element before the single product images.
		 */
		public function woocommerce_before_single_product_summary() {
		?>
			<div class="baton-col baton-col-woocommerce-product baton-col-woocommerce-product-images">
		<?php
		}

		/**
		 * This function adjusts the single product thumbnails columns.
		 */
		public function woocommerce_product_thumbnails_columns( $columns ) {
			// Bail if the columns are already set to three
			if ( $columns === 3 )
				return $columns;

			// Set the columns to three
			$columns = 3;

			return $columns;
		}

		/**
		 * This function outputs the closing and opening baton column wrapper element before
         * the single product summary.
		 */
		public function woocommerce_before_single_product_summary_baton_col_wrappers() {
		?>
			</div>

			<div class="baton-col baton-col-woocommerce-product baton-col-woocommerce-product-summary">
				<!-- Article -->
				<article class="content content-woocommerce-product cf">
		<?php
		}

		/**
		 * This function outputs the opening article content wrapper element before the product summary.
		 */
		public function woocommerce_single_product_summary() {
		?>
			<!-- Article Content -->
			<div class="article-content cf">
		<?php
		}

		/**
		 * This function outputs the closing article content wrapper element after the product summary.
		 */
		public function woocommerce_single_product_summary_article_content_wrapper_after() {
		?>
				<div class="clear"></div>
			</div>
			<!-- End Article Content -->
		<?php
		}

		/**
		 * This function outputs the closing baton column wrapper element before the single product summary.
		 */
		public function woocommerce_after_single_product_summary() {
		?>
					<div class="clear"></div>
				</article>
				<!-- End Article -->
			</div>
		<?php
		}

		/**
		 * This function adjusts the WooCommerce related products arguments.
		 */
		public function woocommerce_output_related_products_args( $args ) {
			// Bail if the posts per page and columns are already set to three
			if ( $args['posts_per_page'] === 3 && $args['columns'] === 3 )
				return $args;

			// Set the posts per page and columns arguments to three
			$args['posts_per_page'] = $args['columns'] = 3;

			return $args;
		}

		/**
		 * This function adjusts the WooCommerce single product archive thumbnail size.
         */
		public function single_product_archive_thumbnail_size( $size ) {
			// Bail if we're not on the cart and the size isn't set to the shop catalog
			if ( ! is_cart() || $size !== 'shop_catalog' )
				return $size;

			// Set the size to shop single
			$size = 'shop_single';

			return $size;
		}

		/**
		 * This function outputs a closing content wrapper element after WooCommerce output.
		 */
		public function woocommerce_after_main_content() {
			// If we're not displaying a singular piece of content
			if ( ! is_singular() ) :
		?>
							<div class="clear"></div>
						</article>
						<!-- End Article -->
		<?php
			endif;
		?>

						<div class="clear"></div>
					</section>

					<div class="clear"></div>
				</div>
				<!-- End Page Content -->

				<div class="clear"></div>
			</main>
			<!-- End Main -->
		<?php
		}


		/**********************
		 * Internal Functions *
		 **********************/

		/**
		 * This function inserts a value into an array before or after a specified key.
		 */
		public function array_insert( $type, $value, $action, $key, $original = array() ) {
			// Switch based on type
			switch ( $type ) {
				// Sidebar
				case 'sidebar':
					global $wp_registered_sidebars;

					// Where should we look (in global or passed original data)
					$where = ( ! empty( $original ) ) ? $original: $wp_registered_sidebars;

					// Check to see if the array key exists in the current array
					if ( array_key_exists( $key, $where ) ) {
						$new = array();

						foreach ( $where as $k => $v ) {
							// Before
							if ( $k === $key && $action === 'before' )
								$new[$value['id']] = $value;

							// Current
							$new[$k] = $v;

							// After
							if ( $k === $key && $action === 'after' )
								$new[$value['id']] = $value;
						}

						return $new;
					}

					// No key found, return the original array
					return $where;
				break;
				// Settings Section
				case 'settings-section':
					global $wp_settings_sections;

					// Where should we look (in global or passed original data)
					$where = ( ! empty( $original ) ) ? $original: $wp_settings_sections;

					// Check to see if the array key exists in the current array
					if ( array_key_exists( $key, $where ) ) {
						$new = array();
						$settings_section = $value;
						unset( $settings_section['page'] );

						foreach ( $where as $k => $v ) {
							// Before
							if ( $k === $key && $action === 'before' )
								$new[$value['id']] = $settings_section;

							// Current
							$new[$k] = $v;

							// After
							if ( $k === $key && $action === 'after' )
								$new[$value['id']] = $settings_section;
						}

						return $new;
					}

					// No key found, return the original array
					return $where;
				break;
			}

			return array();
		}

		/**
		 * This function updates legacy Conductor Widgets to ensure legacy widget displays/sizes are
		 * switched to the new custom (flexbox) display. This function ensures the current version of
		 * Conductor is at least 1.3.0.
		 *
		 * It also updates the order of the output elements to ensure that the author byline output
		 * element is at the bottom.
		 */
		public function update_conductor_widgets( $after_switch_theme = false ) {
			global $sds_theme_options;

			// Grab SDS Theme Options
			$sds_theme_options = SDS_Theme_Options::get_sds_theme_options();

			// If Conductor Widget exists and we haven't already updated legacy widget sizes
			if ( function_exists( 'Conduct_Widget' ) && ( ! isset( $sds_theme_options['baton_conductor_widgets_updated'] ) || ! $sds_theme_options['baton_conductor_widgets_updated'] || $after_switch_theme ) ) {
				// Grab the Conductor Widget instance
				$conductor_widget = Conduct_Widget();

				// Grab all Conductor Widget instances
				$all_instances = $conductor_widget->get_settings();

				// If Conductor is greater than 1.2.9 or Conductor Widget instance has the "displays" property, we can check to see if the custom display exists
				if ( $this->conductor_has_flexbox_display( $conductor_widget ) ) {
					// Loop through instances (passing by reference)
					foreach ( $all_instances as $number => &$instance ) {
						// Only if this instance isn't empty
						if ( ! empty( $instance ) ) {
							// Legacy display
							if ( in_array( $instance['widget_size'], array( 'small', 'medium', 'large' ) ) ) {
								// Switch based on widget size
								switch ( $instance['widget_size'] ) {
									case 'small':
										// Flexbox Columns (4 columns)
										$instance['flexbox']['columns'] = 4;
										$instance['flexbox_columns'] = 4;
									break;

									// Medium
									case 'medium':
										// Flexbox Columns (2 columns)
										$instance['flexbox']['columns'] = 2;
										$instance['flexbox_columns'] = 2;
									break;

									// Large
									case 'large':
										// Flexbox Columns (1 column)
										$instance['flexbox']['columns'] = 1;
										$instance['flexbox_columns'] = 1;
									break;
								}

								// Widget Size (display)
								$instance['widget_size'] = 'flexbox'; // Custom (Flexbox)
							}
						}
					}

					// Set the update flag
					$sds_theme_options['baton_conductor_widgets_updated'] = true;
					update_option( SDS_Theme_Options::get_option_name(), $sds_theme_options );
				}

				// Only on after_switch_theme
				if ( $after_switch_theme ) {
					/*
					 * Conductor Output Elements
					 */
					$author_byline = array();

					// Remove the reference to the $instance
					unset( $instance );

					// Conductor output elements, Loop through instances (passing by reference)
					foreach ( $all_instances as $number => &$instance ) {
						// Only if this instance isn't empty
						if ( ! empty( $instance ) && isset( $instance['output'] ) ) {
							// Loop through output elements
							foreach ( $instance['output'] as $priority => $output )
								// Author Byline (store reference to priority and configuration)
								if ( $output['id'] === 'author_byline' ) {
									$author_byline = $output;

									// Remove author byline
									unset( $instance['output'][$priority] );
								}

							/*
							 * Author Byline (move to bottom of default output elements)
							 */
							$output_elements = array();
							$default_priority_gap = 10;
							$count = 0;

							// Loop through the passed in widget settings
							foreach ( $instance['output'] as $output ) {
								// Increase count
								$count++;

								// Add this element to the output elements
								$output_elements[( $default_priority_gap * $count )] = $output;
							}

							// Author Byline (increase count before multiplying)
							$output_elements[( $default_priority_gap * ++$count )] = $author_byline;

							// Set the default output
							$instance['output'] = $output_elements;
						}
					}
				}

				// Update the database
				$conductor_widget->save_settings( $all_instances );
			}
		}

		/**
		 * This function checks to see if Conductor has the new flexbox display.
		 */
		public function conductor_has_flexbox_display( $conductor_widget = false ) {
			// Bail if Conductor doesn't exist
			if ( ! class_exists( 'Conductor' ) || ! function_exists( 'Conduct_Widget' ) )
				return false;

			// If we don't have a Conductor Widget reference, grab one now
			$conductor_widget = ( ! $conductor_widget ) ? Conduct_Widget() : $conductor_widget;

			// If Conductor is greater than 1.2.9 or Conductor Widget instance has the "displays" property, we can check to see if the custom display exists
			return ( ( version_compare( Conductor::$version, '1.2.9', '>' ) || property_exists( $conductor_widget, 'displays' ) ) && isset( $conductor_widget->displays['flexbox'] ) );
		}

		/**
		 * This function determines if the current widget is a Conductor Widget which has a display
		 * that supports flexbox.
		 */
		public function conductor_widget_has_flexbox_support( $params ) {
			// Bail if the Conductor Widget doesn't exist
			if ( ! function_exists( 'Conduct_Widget' ) )
				return false;

			// Grab the Conductor Widget instance
			$conductor_widget = Conduct_Widget();

			// Only on Conductor Widgets
			if ( _get_widget_id_base( $params[0]['widget_id'] ) === $conductor_widget->id_base ) {
				// Store a reference to the widget settings (all Conductor Widgets)
				$conductor_widget_settings = $conductor_widget->get_settings();

				// Determine if this is a valid Conductor widget
				if ( array_key_exists( $params[1]['number'], $conductor_widget_settings ) ) {
					// Grab widget settings
					$instance = $conductor_widget_settings[$params[1]['number']];

					// If Conductor supports flexbox
					if ( $this->conductor_has_flexbox_display() ) {
						// Grab this widget's display configuration
						$widget_display_config = ( isset( $instance['widget_size'] ) && isset( $conductor_widget->displays[$instance['widget_size']] ) ) ? $conductor_widget->displays[$instance['widget_size']] : false;

						// Verify that the widget size supports columns
						if ( ! empty( $widget_display_config ) && $conductor_widget->widget_display_supports_customize_property( $widget_display_config, 'columns' ) )
							return true;
					}
				}
			}

			// At this point we don't have a Conductor Widget or the widget display doesn't support flexbox
			return false;
		}
	}


	function Baton_Instance() {
		return Baton::instance();
	}

	// Starts Baton
	Baton_Instance();
}