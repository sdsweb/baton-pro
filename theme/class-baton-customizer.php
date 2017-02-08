<?php
/**
 * This class manages all Customizer functionality with our Baton theme.
 */

// Bail if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

if ( ! class_exists( 'Baton_Customizer' ) ) {
	class Baton_Customizer {
		/**
		 * @var string
		 */
		public $version = '1.0.4';

		/**
		 * @var string, Transient name
		 */
		public $transient_name = 'baton_customizer_';

		/**
		 * @var array, Transient data
		 */
		public $transient_data = array();

		/**
		 * @var array, selected Baton color scheme properties
		 */
		public $sds_color_scheme = array();

		/**
		 * @var array, color scheme control IDs
		 */
		public $sds_color_scheme_control_ids = array();

		/*
		 * var array, background image control IDs
		 */
		public $baton_background_image_control_ids = array();

		private static $instance; // Keep track of the instance

		/**
		 * Function used to create instance of class.
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) )
				self::$instance = new self();

			return self::$instance;
		}


		/*
		 * This function sets up all of the actions and filters on instance
		 */
		public function __construct() {
			global $sds_theme_options;

			// Includes
			$this->includes();

			// Set the current Baton color scheme
			$sds_color_schemes = sds_color_schemes();
			$this->sds_color_scheme = ( ! empty( $sds_theme_options['color_scheme'] ) && array_key_exists( $sds_theme_options['color_scheme'], $sds_color_schemes ) ) ? $sds_color_schemes[$sds_theme_options['color_scheme']] : $this->sds_color_scheme;

			add_action( 'after_setup_theme', array( $this, 'after_setup_theme' ), 9999 ); // After Setup Theme (late; load assets based on theme support)
			add_action( 'after_switch_theme', array( $this, 'reset_transient' ), 9999 ); // After Switch Theme (late)
			add_action( 'wp_loaded', array( $this, 'wp_loaded' ), 20 ); // After core Customizer preview filters have been added

			// Customizer
			add_filter( 'sds_color_scheme_customizer_color_controls', array( $this, 'sds_color_scheme_customizer_color_controls' ) ); // Adjust color controls in SDS Core
			add_action( 'customize_register', array( $this, 'customize_register' ), 25 ); // Add settings/sections/controls to Customizer
			add_action( 'customize_controls_enqueue_scripts', array( $this, 'customize_controls_enqueue_scripts' ), 20 );
			add_filter( 'default_option_sds_theme_options', array( $this, 'option_sds_theme_options' ) );
			add_filter( 'option_sds_theme_options', array( $this, 'option_sds_theme_options' ) );
			add_filter( 'sanitize_option_sds_theme_options', array( $this, 'sanitize_option_sds_theme_options' ), 20 ); // After SDS Core
			add_action( 'customize_save_after', array( $this, 'reset_transient' ) ); // Customize Save (reset transients)

			// Color Scheme
			add_filter( 'theme_mod_primary_color', array( $this, 'theme_mod_primary_color' ) ); // Set the default primary color
			add_filter( 'theme_mod_secondary_color', array( $this, 'theme_mod_secondary_color' ) ); // Set the default secondary color
			add_filter( 'theme_mod_content_color', array( $this, 'theme_mod_content_color' ) ); // Set the default content color
			add_filter( 'theme_mod_link_color', array( $this, 'theme_mod_link_color' ) ); // Set the default link color
			add_filter( 'theme_mod_baton_post_title_color', array( $this, 'theme_mod_baton_post_title_color' ) ); // Set the default post title color
			add_filter( 'theme_mod_baton_archive_title_color', array( $this, 'theme_mod_baton_archive_title_color' ) ); // Set the default archive title color
			add_filter( 'theme_mod_baton_button_text_color', array( $this, 'theme_mod_baton_button_text_color' ) ); // Set the default button text color
			add_filter( 'theme_mod_baton_button_hover_text_color', array( $this, 'theme_mod_baton_button_hover_text_color' ) ); // Set the default button text color
			add_filter( 'theme_mod_baton_button_background_color', array( $this, 'theme_mod_baton_button_background_color' ) ); // Set the default button background color
			add_filter( 'theme_mod_baton_button_hover_background_color', array( $this, 'theme_mod_baton_button_hover_background_color' ) ); // Set the default button hover background color
			add_filter( 'theme_mod_baton_content_background_color', array( $this, 'theme_mod_baton_content_background_color' ) ); // Set the default content background color

			// Widget Design
			add_filter( 'theme_mod_baton_widget_title_color', array( $this, 'theme_mod_baton_widget_title_color' ) ); // Set the default widget title color
			add_filter( 'theme_mod_baton_widget_color', array( $this, 'theme_mod_baton_widget_color' ) ); // Set the default widget title color
			add_filter( 'theme_mod_baton_widget_link_color', array( $this, 'theme_mod_baton_widget_link_color' ) ); // Set the default widget title color

			// Header
			//add_filter( 'theme_mod_header_textcolor', array( $this, 'theme_mod_header_textcolor' ) ); // Set the default header text color'
			add_filter( 'theme_mod_baton_site_title_color', array( $this, 'theme_mod_baton_site_title_color' ) ); // Set the default site title color
			add_filter( 'theme_mod_baton_tagline_color', array( $this, 'theme_mod_baton_tagline_color' ) ); // Set the default tagline color
			add_filter( 'theme_mod_baton_primary_hover_active_color', array( $this, 'theme_mod_baton_primary_hover_active_color' ) ); // Set the default primary hover/active color
			add_filter( 'theme_mod_baton_primary_sub_menu_color', array( $this, 'theme_mod_baton_primary_sub_menu_color' ) ); // Set the default primary navigation sub menu color
			add_filter( 'theme_mod_baton_primary_sub_menu_hover_color', array( $this, 'theme_mod_baton_primary_sub_menu_hover_color' ) ); // Set the default primary navigation sub menu hover color
			add_filter( 'theme_mod_baton_primary_sub_menu_background_color', array( $this, 'theme_mod_baton_primary_sub_menu_background_color' ) ); // Set the default primary navigation sub menu background color
			add_filter( 'theme_mod_baton_header_background_color', array( $this, 'theme_mod_baton_header_background_color' ) ); // Set the default header background color

			// Secondary Header
			add_filter( 'theme_mod_baton_secondary_hover_active_color', array( $this, 'theme_mod_baton_secondary_hover_active_color' ) ); // Set the default secondary hover/active color
			add_filter( 'theme_mod_baton_secondary_header_sub_menu_color', array( $this, 'theme_mod_baton_secondary_header_sub_menu_color' ) ); // Set the default secondary header navigation sub menu color
			add_filter( 'theme_mod_baton_secondary_header_sub_menu_hover_color', array( $this, 'theme_mod_baton_secondary_header_sub_menu_hover_color' ) ); // Set the default secondary navigation sub menu hover color
			add_filter( 'theme_mod_baton_secondary_header_sub_menu_background_color', array( $this, 'theme_mod_baton_secondary_header_sub_menu_background_color' ) ); // Set the default secondary header navigation sub menu background color
			add_filter( 'theme_mod_baton_secondary_header_background_color', array( $this, 'theme_mod_baton_secondary_header_background_color' ) ); // Set the default secondary header background color

			// Footer
			add_filter( 'theme_mod_baton_footer_text_color', array( $this, 'theme_mod_baton_footer_text_color' ) ); // Set the default footer text color
			add_filter( 'theme_mod_baton_footer_link_color', array( $this, 'theme_mod_baton_footer_link_color' ) ); // Set the default footer link color
			add_filter( 'theme_mod_baton_footer_heading_color', array( $this, 'theme_mod_baton_footer_heading_color' ) ); // Set the default footer widget title color
			add_filter( 'theme_mod_baton_footer_background_color', array( $this, 'theme_mod_baton_footer_background_color' ) ); // Set the default footer background color

			// More Link
			add_filter( 'theme_mod_baton_more_link_label', array( $this, 'theme_mod_baton_more_link_label' ) ); // Set the default more link button label

			// Front End
			add_filter( 'body_class', array( $this, 'body_class' ) );
			add_action( 'wp_head', array( $this, 'wp_head' ) );
		}

		/**
		 * Include required core files used in admin and on the front-end.
		 */
		private function includes() {
			// All
			include_once 'class-baton-customizer-theme-helper.php'; // Customizer Theme Helper Class
			include_once 'class-baton-customizer-typography.php'; // Customizer Typography Class
			include_once get_template_directory() . '/customizer/class-baton-customizer-reset-control.php'; // Baton Customizer Reset Control (used to reset theme mods to their "default" values)

			// Conductor
			//if ( class_exists( 'Conductor' ) )
			//	include_once 'class-baton-customizer-conductor-typography.php'; // Customizer Conductor Typography Class

			// Admin Only
			if ( is_admin() ) { }

			// Front-End Only
			if ( ! is_admin() ) { }
		}


		/************************************************************************************
		 *    Functions to correspond with actions above (attempting to keep same order)    *
		 ************************************************************************************/

		/**
		 * This function runs after the theme has been setup and determines which assets to load based on theme support.
		 */
		public function after_setup_theme() {
			// Load required assets
			$this->includes();

			$baton_theme_helper = Baton_Theme_Helper(); // Grab the Baton_Theme_Helper instance

			// Setup transient data
			$this->transient_name .= $baton_theme_helper->theme->get_template(); // Append theme name to transient name
			$this->transient_data = $this->get_transient();

			// If the theme has updated, let's update the transient data
			if ( ! isset( $this->transient_data['version'] ) || $this->transient_data['version'] !== $baton_theme_helper->theme->get( 'Version' ) )
				$this->reset_transient();
		}

		/**
		 * This function sets/resets the current color scheme for use in the Customizer.
		 */
		public function wp_loaded() {
			global $sds_theme_options;

			// Set the current Baton color scheme
			$sds_color_schemes = sds_color_schemes();
			$this->sds_color_scheme = ( ! empty( $sds_theme_options['color_scheme'] ) && array_key_exists( $sds_theme_options['color_scheme'], $sds_color_schemes ) ) ? $sds_color_schemes[$sds_theme_options['color_scheme']] : $this->sds_color_scheme;
		}


		/**************
		 * Customizer *
		 **************/

		/**
		 * This function adjusts the default color controls used in SDS Core to determine color
		 * defaults within a Customizer session (when a color scheme is adjusted).
		 */
		public function sds_color_scheme_customizer_color_controls( $color_controls ) {
			$color_controls = array_merge( $color_controls, array(
				'background_color', // Default background color,
				'baton_post_title_color', // Default post title color
				'baton_archive_title_color', // Default archive title color
				'baton_button_text_color', // Default button text color
				'baton_button_hover_text_color', // Default button hover text color
				'baton_button_background_color', // Default button background color
				'baton_button_hover_background_color', // Default button hover background color
				'baton_content_background_color', // Default content background color
				'baton_widget_title_color', // Default widget title color
				'baton_widget_color', // Default widget title color
				'baton_widget_link_color', // Default widget title color
				'baton_site_title_color', // Default site title color
				'baton_tagline_color', // Default tagline color
				'baton_primary_hover_active_color', // Default primary hover
				'baton_primary_sub_menu_color', // Default primary navigation sub menu color
				'baton_primary_sub_menu_hover_color', // Default primary navigation sub menu hover color
				'baton_primary_sub_menu_background_color', // Default primary navigation sub menu background color
				'baton_header_background_color', // Default header background color
				'baton_secondary_hover_active_color', // Default secondary hover
				'baton_secondary_header_sub_menu_color', // Default secondary header navigation sub menu color
				'baton_secondary_header_sub_menu_hover_color', // Default secondary navigation sub menu hover color
				'baton_secondary_header_sub_menu_background_color', // Default secondary header navigation sub menu background color
				'baton_secondary_header_background_color', // Default secondary header background color
				'baton_footer_text_color', // Default footer text color
				'baton_footer_link_color', // Default footer link color
				'baton_footer_heading_color', // Default footer widget title color
				'baton_footer_background_color' // Default footer background color
			) );

			return $color_controls;
		}

		/**
		 * This function registers various Customizer options for this theme.
		 */
		public function customize_register( $wp_customize ) {
			// Load custom Customizer API assets
			include_once get_template_directory() . '/customizer/class-baton-customizer-font-size-control.php'; // Baton Customizer Font Size Control

			/**
			 * General Settings
			 */

			/*
			 * General Settings Panel
			 */
			$wp_customize->add_panel( 'baton_general_settings', array(
				'priority' => 10, // Top
				'title' => __( 'General Settings', 'baton' )
			) );


			/**
			 * Logo/Site Title & Tagline Section
			 */
			if ( $title_tagline_section = $wp_customize->get_section( 'title_tagline' ) ) {// Get Section
				$title_tagline_section->panel = 'baton_general_settings'; // Add panel
				$title_tagline_section->priority = 10; // Adjust Priority
			}


			/**
			 * Static Front Page Section
			 */
			if ( $static_front_page_section = $wp_customize->get_section( 'static_front_page' ) ) { // Get Section
				$static_front_page_section->panel = 'baton_general_settings'; // Add panel
				$static_front_page_section->priority = 20; // Adjust Priority
			}


			/**
			 * Nav Section
			 */
			if ( $nav_section = $wp_customize->get_section( 'nav' ) ) { // Get Section
				$nav_section->panel = 'baton_general_settings'; // Add panel
				$nav_section->priority = 30; // Adjust Priority
			}
			else if ( $nav_menus_panel = $wp_customize->get_panel( 'nav_menus' ) ) { // Get Panel (WordPress 4.3+)
				$nav_menus_panel->panel = 'baton_general_settings'; // Add panel
				$nav_menus_panel->priority = 30; // Adjust Priority
			}


			/**
			 * Site Layout Section
			 */
			$wp_customize->add_section( 'baton_design_site_layout', array(
				'priority' => 40, // After Static Front Page
				'title' => __( 'Site Layout', 'baton' ),
				'panel' => 'baton_general_settings'
			) );


			/**
			 * Max Width
			 */
			// Setting
			$wp_customize->add_setting(
				'baton_max_width',
				array(
					'default' => apply_filters( 'baton_max_width', 1272, 1272 ), // Pass the default value as second parameter
					'sanitize_callback' => 'absint',
					'sanitize_js_callback' => 'absint'
				)
			);

			// Control
			$wp_customize->add_control(
				new Baton_Customizer_Font_Size_Control(
					$wp_customize,
					'baton_max_width',
					array(
						'label' => __( 'Maximum Width', 'baton' ),
						'description' => __( 'The default width is 1272px.', 'baton' ),
						'section' => 'baton_design_site_layout',
						'settings' => 'baton_max_width',
						'priority' => 10, // Top
						'type' => 'number',
						'input_attrs' => array(
							'min' => apply_filters( 'theme_mod_baton_max_width_min', 800, 800 ), // Pass the default value as second parameter
							'max' => apply_filters( 'theme_mod_baton_max_width_max', 1272, 1272 ), // Pass the default value as second parameter
							'placeholder' => apply_filters( 'theme_mod_baton_max_width', 1272, 1272 ), // Pass the default value as second parameter
							'style' => 'width: 70px',
							'step' => '10'
						),
						'units' => array(
							'title' => _x( 'pixels', 'title attribute for max width Customizer control', 'baton' )
						)
					)
				)
			);


			/**
			 * Content Layouts (SDS Core)
			 */
			if ( $sds_theme_options_content_layouts_section = $wp_customize->get_section( 'sds_theme_options_content_layouts' ) ) { // Get Section
				$sds_theme_options_content_layouts_section->panel = 'baton_general_settings'; // Adjust panel
				$sds_theme_options_content_layouts_section->priority = 50; // Adjust priority
			}


			/**
			 * Images Section
			 */
			/*if ( $images_section = $wp_customize->get_section( 'images' ) ) { // Get Section
				$images_section->panel = 'baton_general_settings'; // Add panel
				$images_section->priority = 50; // After Site Layout
			}*/

			/*
			 * Featured Image Size (SDS Core)
			 */
			$wp_customize->remove_setting( 'sds_theme_options[featured_image_size]' ); // Remove Setting
			$wp_customize->remove_control( 'sds_theme_options[featured_image_size]' ); // Remove Control

			/**
			 * Show/Hide Elements Section
			 */
			if ( $sds_theme_options_show_hide_section = $wp_customize->get_section( 'sds_theme_options_show_hide' ) ) { // Get Section
				$sds_theme_options_show_hide_section->panel = 'baton_general_settings'; // Adjust panel
				$sds_theme_options_show_hide_section->priority = 60; // After Images
			}

			/**
			 * Reset Colors, Background Images, and Fonts
			 */
			// Section
			$wp_customize->add_section( 'baton_reset_theme_mods', array(
				'priority' => 70, // After Show/Hide
				'title' => __( 'Reset Settings', 'baton' ),
				'panel' => 'baton_general_settings'
			) );

			/**
			 * Reset Colors
			 */
			// Setting
			$wp_customize->add_setting(
				'baton_reset_colors',
				array(
					'default' => false,
					'sanitize_callback' => 'baton_boolval',
					'sanitize_js_callback' => 'baton_boolval'
				)
			);

			// Control
			$wp_customize->add_control(
				new Baton_Customizer_Reset_Control(
					$wp_customize,
					'baton_reset_colors',
					array(
						'label' => __( 'Reset Colors', 'baton' ),
						'description' => __( 'Reset all individual color settings to their default values based on the selected color scheme.', 'baton' ),
						'section' => 'baton_reset_theme_mods',
						'settings' => 'baton_reset_colors',
						'priority' => 10,
						'button_label' => __( 'Reset Colors', 'baton' ),
						'reset_type' => 'colors'
					)
				)
			);

			/**
			 * Reset Background Images
			 */
			// Setting
			$wp_customize->add_setting(
				'baton_reset_background_images',
				array(
					'default' => false,
					'sanitize_callback' => 'baton_boolval',
					'sanitize_js_callback' => 'baton_boolval'
				)
			);

			// Control
			$wp_customize->add_control(
				new Baton_Customizer_Reset_Control(
					$wp_customize,
					'baton_reset_background_images',
					array(
						'label' => __( 'Reset Background Images', 'baton' ),
						'description' => __( 'Reset all individual background image settings back to their default values.', 'baton' ),
						'section' => 'baton_reset_theme_mods',
						'settings' => 'baton_reset_background_images',
						'priority' => 20, // After Reset Colors
						'button_label' => __( 'Reset Background Images', 'baton' ),
						'reset_type' => 'background_images'
					)
				)
			);

			/**
			 * Reset Font Properties
			 */
			// Setting
			$wp_customize->add_setting(
				'baton_reset_font_properties',
				array(
					'default' => false,
					'sanitize_callback' => 'baton_boolval',
					'sanitize_js_callback' => 'baton_boolval'
				)
			);

			// Control
			$wp_customize->add_control(
				new Baton_Customizer_Reset_Control(
					$wp_customize,
					'baton_reset_font_properties',
					array(
						'label' => __( 'Reset Font Attributes', 'baton' ),
						'description' => __( 'Reset all individual font size, letter spacing, and line height settings back to their default values.', 'baton' ),
						'section' => 'baton_reset_theme_mods',
						'settings' => 'baton_reset_font_properties',
						'priority' => 30, // After Reset Background Images
						'button_label' => __( 'Reset Font Attributes', 'baton' ),
						'reset_type' => 'font_properties'
					)
				)
			);

			/**
			 * Reset Font Families
			 */
			// Setting
			$wp_customize->add_setting(
				'baton_reset_font_families',
				array(
					'default' => false,
					'sanitize_callback' => 'baton_boolval',
					'sanitize_js_callback' => 'baton_boolval'
				)
			);

			// Control
			$wp_customize->add_control(
				new Baton_Customizer_Reset_Control(
					$wp_customize,
					'baton_reset_font_families',
					array(
						'label' => __( 'Reset Font Families', 'baton' ),
						'description' => __( 'Reset all individual font family settings back to their default values.', 'baton' ),
						'section' => 'baton_reset_theme_mods',
						'settings' => 'baton_reset_font_families',
						'priority' => 40, // After Reset Font Properties
						'button_label' => __( 'Reset Font Families', 'baton' ),
						'reset_type' => 'font_families'
					)
				)
			);

			/**
			 * Reset All (colors, background images, font families, and font properties)
			 */
			// Setting
			$wp_customize->add_setting(
				'baton_reset_all_theme_mods',
				array(
					'default' => false,
					'sanitize_callback' => 'baton_boolval',
					'sanitize_js_callback' => 'baton_boolval'
				)
			);

			// Control
			$wp_customize->add_control(
				new Baton_Customizer_Reset_Control(
					$wp_customize,
					'baton_reset_all_theme_mods',
					array(
						'label' => __( 'Reset All of the Above', 'baton' ),
						'description' => __( 'Reset ALL individual color, background image, font attribute, and font family settings back to their default values.', 'baton' ),
						'section' => 'baton_reset_theme_mods',
						'settings' => 'baton_reset_all_theme_mods',
						'priority' => 50, // After Reset Font Sizes
						'button_label' => __( 'Reset All', 'baton' ),
						'reset_type' => 'all'
					)
				)
			);


			/**
			 * Color Scheme (Colors)
			 */

			if ( $colors_section = $wp_customize->get_section( 'colors' ) ) { // Get Section
				$colors_section->title = __( 'Color Scheme', 'baton' ); // Adjust Label
				$colors_section->priority = 20; // Adjust priority (after General Settings)
			}

			if ( $sds_theme_options_color_scheme_control = $wp_customize->get_control( 'sds_theme_options[color_scheme]' ) ) { // Get Control
				$sds_theme_options_color_scheme_control->description = __( 'Select a color scheme to use on your site. Individual color settings exist throughout the Customizer for even more flexibility.', 'baton' ); // Adjust description

				// Store a reference of the color controls
				$this->sds_color_scheme_control_ids = $sds_theme_options_color_scheme_control->color_controls;
			}

			/**
			 * Background Color & Image Panel
			 */
			$wp_customize->add_panel( 'baton_background_color_image', array(
				'priority' => 25, // After Color Scheme Section
				'title' => __( 'Background Colors &amp; Images', 'baton' )
			) );

			/**
			 * Body Background Section
			 */
			$wp_customize->add_section( 'baton_background_body', array(
				'priority' => 10, // Top
				'title' => __( 'Body', 'baton' ),
				'panel' => 'baton_background_color_image'
			) );

			/**
			 * Background Color
			 */
			if ( $background_color_control = $wp_customize->get_control( 'background_color' ) ) { // Get Control
				$background_color_control->section = 'baton_background_body'; // Adjust Section
				$background_color_control->priority = 10; // Adjust Priority
			}

			if ( $background_color_setting = $wp_customize->get_setting( 'background_color' ) ) // Get Setting
				$background_color_setting->transport = 'refresh'; // Adjust Transport

			/**
			 * Background Image
			 */
			if ( $background_image_control = $wp_customize->get_control( 'background_image' ) ) { // Get Control
				$background_image_control->section = 'baton_background_body'; // Adjust Section
				$background_image_control->priority = 20; // Adjust Priority
				$wp_customize->remove_section( 'background_image' ); // Remove Section

				// Add this control ID to the list of background image control IDs
				$this->baton_background_image_control_ids[] = $background_image_control->id;
			}

			/**
			 * Background Preset
			 */
			if ( $background_preset_control = $wp_customize->get_control( 'background_preset' ) ) { // Get Control
				$background_preset_control->section = 'baton_background_body'; // Adjust Section
				$background_preset_control->priority = 30; // Adjust Priority
			}

			/**
			 * Background Position
			 */
			if ( $background_position_control = $wp_customize->get_control( 'background_position' ) ) { // Get Control
				$background_position_control->section = 'baton_background_body'; // Adjust Section
				$background_position_control->priority = 40; // Adjust Priority
			}

			/**
			 * Background Size
			 */
			if ( $background_size_control = $wp_customize->get_control( 'background_size' ) ) { // Get Control
				$background_size_control->section = 'baton_background_body'; // Adjust Section
				$background_size_control->priority = 50; // Adjust Priority
			}

			/**
			 * Background Repeat
			 */
			if ( $background_repeat_control = $wp_customize->get_control( 'background_repeat' ) ) { // Get Control
				$background_repeat_control->section = 'baton_background_body'; // Adjust Section
				$background_repeat_control->priority = 60; // Adjust Priority
			}

			/**
			 * Background Attachment
			 */
			if ( $background_attachment_control = $wp_customize->get_control( 'background_attachment' ) ) { // Get Control
				$background_attachment_control->section = 'baton_background_body'; // Adjust Section
				$background_attachment_control->priority = 70; // Adjust Priority
			}


			/**
			 * Content Background Section
			 */
			$wp_customize->add_section( 'baton_background_content', array(
				'priority' => 20, // After Body
				'title' => __( 'Content', 'baton' ),
				'panel' => 'baton_background_color_image'
			) );

			/**
			 * Content Background Color
			 */
			// Setting
			$wp_customize->add_setting(
				'baton_content_background_color',
				array(
					'default' => apply_filters( 'theme_mod_baton_content_background_color', '' ),
					'sanitize_callback' => 'sanitize_hex_color',
					'sanitize_js_callback' => 'maybe_hash_hex_color'
				)
			);

			// Control
			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					'baton_content_background_color',
					array(
						'label' => __( 'Content Background Color', 'baton' ),
						'section' => 'baton_background_content',
						'settings' => 'baton_content_background_color',
						'priority' => 10
					)
				)
			);

			/**
			 * Content Background Image
			 */
			// Setting
			$wp_customize->add_setting(
				'baton_content_background_image',
				array(
					'default' => apply_filters( 'theme_mod_baton_content_background_image', '' ),
					'sanitize_callback' => 'wp_unslash',
				)
			);

			// Control
			$wp_customize->add_control(
				new WP_Customize_Image_Control(
					$wp_customize,
					'baton_content_background_image',
					array(
						'label' => __( 'Content Background Image', 'baton' ),
						'section' => 'baton_background_content',
						'settings' => 'baton_content_background_image',
						'priority' => 20
					)
				)
			);

			// Add this control ID to the list of background image controls
			$this->baton_background_image_control_ids[] = 'baton_content_background_image';

			/**
			 * Content Background Image Repeat
			 */
			// Setting
			$wp_customize->add_setting(
				'baton_content_background_image_repeat',
				array(
					'default' => apply_filters( 'theme_mod_baton_content_background_image_repeat', 'repeat' ),
					'sanitize_callback' => 'sanitize_text_field',
				)
			);

			// Control
			$wp_customize->add_control(
				new WP_Customize_Control(
					$wp_customize,
					'baton_content_background_image_repeat',
					array(
						'label' => __( 'Background Repeat', 'baton' ),
						'section' => 'baton_background_content',
						'settings' => 'baton_content_background_image_repeat',
						'priority' => 30,
						'type' => 'radio',
						'choices' => array(
							'no-repeat' => __( 'No Repeat', 'baton' ),
							'repeat' => __( 'Tile', 'baton' ),
							'repeat-x' => __( 'Tile Horizontally', 'baton' ),
							'repeat-y' => __( 'Tile Vertically', 'baton' )
						)
					)
				)
			);

			/**
			 * Content Background Image Position X
			 */
			// Setting
			$wp_customize->add_setting(
				'baton_content_background_image_position_x',
				array(
					'default' => apply_filters( 'theme_mod_baton_content_background_image_position_x', 'left' ),
					'sanitize_callback' => 'sanitize_text_field',
				)
			);

			// Control
			$wp_customize->add_control(
				new WP_Customize_Control(
					$wp_customize,
					'baton_content_background_image_position_x',
					array(
						'label' => __( 'Background Position', 'baton' ),
						'section' => 'baton_background_content',
						'settings' => 'baton_content_background_image_position_x',
						'priority' => 40,
						'type' => 'radio',
						'choices' => array(
							'left' => __( 'Left', 'baton' ),
							'center' => __( 'Center', 'baton' ),
							'right' => __( 'Right', 'baton' )
						)
					)
				)
			);

			/**
			 * Fluid Width Background Image Attachment
			 */
			// Setting
			$wp_customize->add_setting(
				'baton_content_background_image_attachment',
				array(
					'default' => apply_filters( 'theme_mod_baton_content_background_image_attachment', 'scroll' ),
					'sanitize_callback' => 'sanitize_text_field',
				)
			);

			// Control
			$wp_customize->add_control(
				new WP_Customize_Control(
					$wp_customize,
					'baton_content_background_image_attachment',
					array(
						'label' => __( 'Background Attachment', 'baton' ),
						'section' => 'baton_background_content',
						'settings' => 'baton_content_background_image_attachment',
						'priority' => 50,
						'type' => 'radio',
						'choices' => array(
							'scroll' => __( 'Scroll', 'baton' ),
							'fixed' => __( 'Fixed', 'baton' )
						)
					)
				)
			);


			/**
			 * Main Header Panel
			 */
			$wp_customize->add_panel( 'baton_main_header', array(
				'priority' => 40,
				'title' => __( 'Header', 'baton' )
			) );

			/**
			 * Main Header Alignment Section
			 */
			$wp_customize->add_section( 'baton_main_header_alignment', array(
				'priority' => 10, // Top
				'title' => __( 'Alignment', 'baton' ),
				'panel' => 'baton_main_header'
			) );

			/**
			 * Main Header Alignment
			 */
			// Setting
			$wp_customize->add_setting(
				'baton_main_header_alignment',
				array(
					'default' => apply_filters( 'theme_mod_baton_main_header_alignment', 'traditional' ),
					'sanitize_callback' => 'sanitize_text_field',
				)
			);

			// Control
			$wp_customize->add_control(
				new WP_Customize_Control(
					$wp_customize,
					'baton_main_header_alignment',
					array(
						'label' => __( 'Alignment', 'baton' ),
						'section' => 'baton_main_header_alignment',
						'settings' => 'baton_main_header_alignment',
						'priority' => 10,
						'type' => 'select',
						'choices' => array(
							'' => __( '&mdash; Select &mdash;', 'baton' ),
							'traditional' => __( 'Default', 'baton' ),
							'centered' => __( 'Centered', 'baton' ),
							'flipped' => __( 'Flipped', 'baton' ),
							'nav-below' => __( 'Navigation Below', 'baton' )
						)
					)
				)
			);


			/**
			 * Site Title Section
			 */
			$wp_customize->add_section( 'baton_main_header_site_title', array(
				'priority' => 20, // After Main Header Alignment
				'title' => __( 'Site Title', 'baton' ),
				'panel' => 'baton_main_header'
			) );

			/**
			 * Site Title Color
			 */
			// Setting
			$wp_customize->add_setting(
				'baton_site_title_color',
				array(
					'default' => apply_filters( 'theme_mod_baton_site_title_color', '' ),
					'sanitize_callback' => 'sanitize_hex_color',
					'sanitize_js_callback' => 'maybe_hash_hex_color'
				)
			);

			// Control
			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					'baton_site_title_color',
					array(
						'label' => __( 'Site Title Color', 'baton' ),
						'section' => 'baton_main_header_site_title',
						'settings' => 'baton_site_title_color',
						'priority' => 10
					)
				)
			);

			/**
			 * Site Title Font Size
			 */
			if ( $site_title_font_size_control = $wp_customize->get_control( 'baton_site_title_font_size' ) ) { // Get Control
				$site_title_font_size_control->section = 'baton_main_header_site_title'; // Adjust Section
				$site_title_font_size_control->priority = 20; // Adjust Priority
			}


			/**
			 * Site Title Font Family
			 */
			if ( $site_title_font_family_control = $wp_customize->get_control( 'baton_site_title_font_family' ) ) { // Get Control
				$site_title_font_family_control->section = 'baton_main_header_site_title'; // Adjust Section
				$site_title_font_family_control->priority = 30; // Adjust Priority
			}

			/**
			 * Site Title Letter Spacing
			 */
			if ( $site_title_letter_spacing_control = $wp_customize->get_control( 'baton_site_title_letter_spacing' ) ) { // Get Control
				$site_title_letter_spacing_control->section = 'baton_main_header_site_title'; // Adjust Section
				$site_title_letter_spacing_control->priority = 40; // Adjust Priority
			}


			/**
			 * Tagline Section
			 */
			$wp_customize->add_section( 'baton_main_header_tagline', array(
				'priority' => 30, // After Site Title
				'title' => __( 'Tagline', 'baton' ),
				'panel' => 'baton_main_header'
			) );

			/**
			 * Tagline Color
			 */
			// Setting
			$wp_customize->add_setting(
				'baton_tagline_color',
				array(
					'default' => apply_filters( 'theme_mod_baton_tagline_color', '' ),
					'sanitize_callback' => 'sanitize_hex_color',
					'sanitize_js_callback' => 'maybe_hash_hex_color'
				)
			);

			// Control
			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					'baton_tagline_color',
					array(
						'label' => __( 'Tagline Color', 'baton' ),
						'section' => 'baton_main_header_tagline',
						'settings' => 'baton_tagline_color',
						'priority' => 10
					)
				)
			);

			/**
			 * Tagline Font Size
			 */
			if ( $tagline_font_size_control = $wp_customize->get_control( 'baton_tagline_font_size' ) ) { // Get Control
				$tagline_font_size_control->section = 'baton_main_header_tagline'; // Adjust Section
				$tagline_font_size_control->priority = 20; // Adjust Priority
			}

			/**
			 * Tagline Font Family
			 */
			if ( $tagline_font_family_control = $wp_customize->get_control( 'baton_tagline_font_family' ) ) { // Get Control
				$tagline_font_family_control->section = 'baton_main_header_tagline'; // Adjust Section
				$tagline_font_family_control->priority = 30; // Adjust Priority
			}

			/**
			 * Tagline Letter Spacing
			 */
			if ( $tagline_letter_spacing_control = $wp_customize->get_control( 'baton_tagline_letter_spacing' ) ) { // Get Control
				$tagline_letter_spacing_control->section = 'baton_main_header_tagline'; // Adjust Section
				$tagline_letter_spacing_control->priority = 40; // Adjust Priority
			}


			/**
			 * Main Header Navigation Section
			 */
			$wp_customize->add_section( 'baton_main_header_navigation', array(
				'priority' => 40, // After Tagline
				'title' => __( 'Navigation', 'baton' ),
				'panel' => 'baton_main_header'
			) );

			/**
			 * Primary Color (registered in SDS Core)
			 */
			if ( $primary_color_control = $wp_customize->get_control( 'primary_color' ) ) { // Get Control
				$primary_color_control->section = 'baton_main_header_navigation'; // Adjust Section
				$primary_color_control->priority = 10; // Adjust Priority
				$primary_color_control->label = _x( 'Color','primary navigation color label', 'baton' );
			}

			/**
			 * Primary Navigation Menu Hover Color
			 */
			// Setting
			$wp_customize->add_setting(
				'baton_primary_hover_active_color',
				array(
					'default' => apply_filters( 'theme_mod_baton_primary_hover_active_color', '' ),
					'sanitize_callback' => 'sanitize_hex_color',
					'sanitize_js_callback' => 'maybe_hash_hex_color'
				)
			);

			// Control
			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					'baton_primary_hover_active_color',
					array(
						'label' => __( 'Hover/Active Color', 'baton' ),
						'section' => 'baton_main_header_navigation',
						'settings' => 'baton_primary_hover_active_color',
						'priority' => 20
					)
				)
			);

			/**
			 * Primary Navigation Sub Menu Color
			 */
			// Setting
			$wp_customize->add_setting(
				'baton_primary_sub_menu_color',
				array(
					'default' => apply_filters( 'theme_mod_baton_primary_sub_menu_color', '' ),
					'sanitize_callback' => 'sanitize_hex_color',
					'sanitize_js_callback' => 'maybe_hash_hex_color'
				)
			);

			// Control
			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					'baton_primary_sub_menu_color',
					array(
						'label' => __( 'Sub Menu Color', 'baton' ),
						'section' => 'baton_main_header_navigation',
						'settings' => 'baton_primary_sub_menu_color',
						'priority' => 30
					)
				)
			);

			/**
			 * Primary Navigation Sub Menu Hover Color
			 */
			// Setting
			$wp_customize->add_setting(
				'baton_primary_sub_menu_hover_color',
				array(
					'default' => apply_filters( 'theme_mod_baton_primary_sub_menu_hover_color', '' ),
					'sanitize_callback' => 'sanitize_hex_color',
					'sanitize_js_callback' => 'maybe_hash_hex_color'
				)
			);

			// Control
			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					'baton_primary_sub_menu_hover_color',
					array(
						'label' => __( 'Sub Menu Hover Color', 'baton' ),
						'section' => 'baton_main_header_navigation',
						'settings' => 'baton_primary_sub_menu_hover_color',
						'priority' => 40
					)
				)
			);

			/**
			 * Primary Navigation Sub Menu Background Color
			 */
			// Setting
			$wp_customize->add_setting(
				'baton_primary_sub_menu_background_color',
				array(
					'default' => apply_filters( 'theme_mod_baton_primary_sub_menu_background_color', '' ),
					'sanitize_callback' => 'sanitize_hex_color',
					'sanitize_js_callback' => 'maybe_hash_hex_color'
				)
			);

			// Control
			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					'baton_primary_sub_menu_background_color',
					array(
						'label' => __( 'Sub Menu Background Color', 'baton' ),
						'section' => 'baton_main_header_navigation',
						'settings' => 'baton_primary_sub_menu_background_color',
						'priority' => 50
					)
				)
			);

			/**
			 * Primary Navigation Font Size
			 */
			if ( $primary_nav_font_size_control = $wp_customize->get_control( 'baton_navigation_primary_nav_font_size' ) ) { // Get Control
				$primary_nav_font_size_control->section = 'baton_main_header_navigation'; // Adjust Section
				$primary_nav_font_size_control->label = __( 'Font Size', 'baton' ); // Adjust Label
				$primary_nav_font_size_control->priority = 60; // Adjust Priority
			}

			/**
			 * Primary Navigation Font Family
			 */
			if ( $primary_nav_font_family_control = $wp_customize->get_control( 'baton_navigation_primary_nav_font_family' ) ) { // Get Control
				$primary_nav_font_family_control->section = 'baton_main_header_navigation'; // Adjust Section
				$primary_nav_font_family_control->label = __( 'Font Family', 'baton' ); // Adjust Label
				$primary_nav_font_family_control->priority = 70; // Adjust Priority
			}


			/**
			 * Main Header Background Section
			 */
			$wp_customize->add_section( 'baton_main_header_background', array(
				'priority' => 50, // After Navigation
				'title' => __( 'Background', 'baton' ),
				'panel' => 'baton_main_header'
			) );

			/**
			 * Header Background Color
			 */
			// Setting
			$wp_customize->add_setting(
				'baton_header_background_color',
				array(
					'default' => apply_filters( 'theme_mod_baton_header_background_color', '' ),
					'sanitize_callback' => 'sanitize_hex_color',
					'sanitize_js_callback' => 'maybe_hash_hex_color'
				)
			);

			// Control
			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					'baton_header_background_color',
					array(
						'label' => __( 'Background Color', 'baton' ),
						'section' => 'baton_main_header_background',
						'settings' => 'baton_header_background_color',
						'priority' => 10
					)
				)
			);

			/**
			 * Header Background Image
			 */
			// Setting
			$wp_customize->add_setting(
				'baton_header_background_image',
				array(
					'default' => apply_filters( 'theme_mod_baton_header_background_image', '' ),
					'sanitize_callback' => 'wp_unslash',
				)
			);

			// Control
			$wp_customize->add_control(
				new WP_Customize_Image_Control(
					$wp_customize,
					'baton_header_background_image',
					array(
						'label' => __( 'Background Image', 'baton' ),
						'section' => 'baton_main_header_background',
						'settings' => 'baton_header_background_image',
						'priority' => 20,
					)
				)
			);

			// Add this control ID to the list of background image controls
			$this->baton_background_image_control_ids[] = 'baton_header_background_image';

			/**
			 * Header Background Image Repeat
			 */
			// Setting
			$wp_customize->add_setting(
				'baton_header_background_image_repeat',
				array(
					'default' => apply_filters( 'theme_mod_baton_header_background_image_repeat', 'repeat' ),
					'sanitize_callback' => 'sanitize_text_field',
				)
			);

			// Control
			$wp_customize->add_control(
				new WP_Customize_Control(
					$wp_customize,
					'baton_header_background_image_repeat',
					array(
						'label' => __( 'Background Repeat', 'baton' ),
						'section' => 'baton_main_header_background',
						'settings' => 'baton_header_background_image_repeat',
						'priority' => 30,
						'type' => 'radio',
						'choices' => array(
							'no-repeat' => __( 'No Repeat', 'baton' ),
							'repeat' => __( 'Tile', 'baton' ),
							'repeat-x' => __( 'Tile Horizontally', 'baton' ),
							'repeat-y' => __( 'Tile Vertically', 'baton' )
						)
					)
				)
			);

			/**
			 * Header Background Image Position X
			 */
			// Setting
			$wp_customize->add_setting(
				'baton_header_background_image_position_x',
				array(
					'default' => apply_filters( 'theme_mod_baton_header_background_image_position_x', 'left' ),
					'sanitize_callback' => 'sanitize_text_field',
				)
			);

			// Control
			$wp_customize->add_control(
				new WP_Customize_Control(
					$wp_customize,
					'baton_header_background_image_position_x',
					array(
						'label' => __( 'Background Position', 'baton' ),
						'section' => 'baton_main_header_background',
						'settings' => 'baton_header_background_image_position_x',
						'priority' => 40,
						'type' => 'radio',
						'choices' => array(
							'left' => __( 'Left', 'baton' ),
							'center' => __( 'Center', 'baton' ),
							'right' => __( 'Right', 'baton' )
						)
					)
				)
			);

			/**
			 * Header Background Image Attachment
			 */
			// Setting
			$wp_customize->add_setting(
				'baton_header_background_image_attachment',
				array(
					'default' => apply_filters( 'theme_mod_baton_header_background_image_attachment', 'scroll' ),
					'sanitize_callback' => 'sanitize_text_field',
				)
			);

			// Control
			$wp_customize->add_control(
				new WP_Customize_Control(
					$wp_customize,
					'baton_header_background_image_attachment',
					array(
						'label' => __( 'Background Attachment', 'baton' ),
						'section' => 'baton_main_header_background',
						'settings' => 'baton_header_background_image_attachment',
						'priority' => 50,
						'type' => 'radio',
						'choices' => array(
							'scroll' => __( 'Scroll', 'baton' ),
							'fixed' => __( 'Fixed', 'baton' )
						)
					)
				)
			);

			/**
			 * Secondary Header Panel
			 */
			$wp_customize->add_panel( 'baton_secondary_header', array(
				'priority' => 50, // After Main Header
				'title' => __( 'Secondary Header', 'baton' )
			) );

			/**
			 * Secondary Header Alignment Section
			 */
			$wp_customize->add_section( 'baton_secondary_header_alignment', array(
				'priority' => 10, // Top
				'title' => __( 'Alignment', 'baton' ),
				'panel' => 'baton_secondary_header'
			) );

			/**
			 * Secondary Header Alignment
			 */
			// Setting
			$wp_customize->add_setting(
				'baton_secondary_header_alignment',
				array(
					'default' => apply_filters( 'theme_mod_baton_secondary_header_alignment', 'traditional' ),
					'sanitize_callback' => 'sanitize_text_field',
				)
			);

			// Control
			$wp_customize->add_control(
				new WP_Customize_Control(
					$wp_customize,
					'baton_secondary_header_alignment',
					array(
						'label' => __( 'Alignment', 'baton' ),
						'section' => 'baton_secondary_header_alignment',
						'settings' => 'baton_secondary_header_alignment',
						'priority' => 10,
						'type' => 'select',
						'choices' => array(
							'' => __( '&mdash; Select &mdash;', 'baton' ),
							'traditional' => __( 'Default', 'baton' ),
							'centered' => __( 'Centered', 'baton' ),
							'flipped' => __( 'Flipped', 'baton' )
						)
					)
				)
			);


			/**
			 * Secondary Header Navigation Section
			 */
			$wp_customize->add_section( 'baton_secondary_header_navigation', array(
				'priority' => 20, // After Secondary Header Alignment
				'title' => __( 'Navigation', 'baton' ),
				'description' => __( 'This section is displayed on the front-end when a "Secondary Navigation" menu is set under Appearance &gt; Menus or a widget is placed in the "Secondary Sidebar" under Appearance &gt; Widgets.', 'baton' ),
				'panel' => 'baton_secondary_header'
			) );


			/**
			 * Secondary Color (registered im SDS Core)
			 */
			if ( $secondary_color_control = $wp_customize->get_control( 'secondary_color' ) ) { // Get Control
				$secondary_color_control->section = 'baton_secondary_header_navigation'; // Add panel
				$secondary_color_control->priority = 10; // Adjust Priority
				$secondary_color_control->label = _x( 'Color', 'secondary navigation color label', 'baton' );
			}


			/**
			 * Secondary Navigation Menu Hover Color
			 */
			// Setting
			$wp_customize->add_setting(
				'baton_secondary_hover_active_color',
				array(
					'default' => apply_filters( 'theme_mod_baton_secondary_hover_active_color', '' ),
					'sanitize_callback' => 'sanitize_hex_color',
					'sanitize_js_callback' => 'maybe_hash_hex_color'
				)
			);

			// Control
			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					'baton_secondary_hover_active_color',
					array(
						'label' => __( 'Hover/Active Color', 'baton' ),
						'section' => 'baton_secondary_header_navigation',
						'settings' => 'baton_secondary_hover_active_color',
						'priority' => 20
					)
				)
			);


			/**
			 * Secondary Navigation Sub Menu Color
			 */
			// Setting
			$wp_customize->add_setting(
				'baton_secondary_header_sub_menu_color',
				array(
					'default' => apply_filters( 'theme_mod_baton_secondary_header_sub_menu_color', '' ),
					'sanitize_callback' => 'sanitize_hex_color',
					'sanitize_js_callback' => 'maybe_hash_hex_color'
				)
			);

			// Control
			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					'baton_secondary_header_sub_menu_color',
					array(
						'label' => __( 'Sub Menu Color', 'baton' ),
						'section' => 'baton_secondary_header_navigation',
						'settings' => 'baton_secondary_header_sub_menu_color',
						'priority' => 30
					)
				)
			);


			/**
			 * Secondary Navigation Sub Menu Hover Color
			 */
			// Setting
			$wp_customize->add_setting(
				'baton_secondary_header_sub_menu_hover_color',
				array(
					'default' => apply_filters( 'theme_mod_baton_secondary_header_sub_menu_hover_color', '' ),
					'sanitize_callback' => 'sanitize_hex_color',
					'sanitize_js_callback' => 'maybe_hash_hex_color'
				)
			);

			// Control
			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					'baton_secondary_header_sub_menu_hover_color',
					array(
						'label' => __( 'Sub Menu Hover Color', 'baton' ),
						'section' => 'baton_secondary_header_navigation',
						'settings' => 'baton_secondary_header_sub_menu_hover_color',
						'priority' => 40
					)
				)
			);


			/**
			 * Secondary Navigation Sub Menu Background Color
			 */
			// Setting
			$wp_customize->add_setting(
				'baton_secondary_header_sub_menu_background_color',
				array(
					'default' => apply_filters( 'theme_mod_baton_secondary_header_sub_menu_background_color', '' ),
					'sanitize_callback' => 'sanitize_hex_color',
					'sanitize_js_callback' => 'maybe_hash_hex_color'
				)
			);

			// Control
			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					'baton_secondary_header_sub_menu_background_color',
					array(
						'label' => __( 'Sub Menu Background Color', 'baton' ),
						'section' => 'baton_secondary_header_navigation',
						'settings' => 'baton_secondary_header_sub_menu_background_color',
						'priority' => 50
					)
				)
			);


			/**
			 * Secondary Navigation Font Size
			 */
			if ( $secondary_nav_font_size_control = $wp_customize->get_control( 'baton_navigation_secondary_nav_font_size' ) ) { // Get Control
				$secondary_nav_font_size_control->section = 'baton_secondary_header_navigation'; // Adjust Section
				$secondary_nav_font_size_control->label = __( 'Font Size', 'baton' ); // Adjust Label
				$secondary_nav_font_size_control->priority = 60; // Adjust Priority
			}


			/**
			 * Secondary Navigation Font Family
			 */
			if ( $secondary_nav_font_family_control = $wp_customize->get_control( 'baton_navigation_secondary_nav_font_family' ) ) { // Get Control
				$secondary_nav_font_family_control->section = 'baton_secondary_header_navigation'; // Adjust Section
				$secondary_nav_font_family_control->label = __( 'Font Family', 'baton' ); // Adjust Label
				$secondary_nav_font_family_control->priority = 70; // Adjust Priority
			}


			/**
			 * Secondary Header Background Section
			 */
			$wp_customize->add_section( 'baton_secondary_header_background', array(
				'priority' => 30, // After Secondary Header Color
				'title' => __( 'Background', 'baton' ),
				'panel' => 'baton_secondary_header'
			) );


			/**
			 * Secondary Header Background Color
			 */
			// Setting
			$wp_customize->add_setting(
				'baton_secondary_header_background_color',
				array(
					'default' => apply_filters( 'theme_mod_baton_secondary_header_background_color', '' ),
					'sanitize_callback' => 'sanitize_hex_color',
					'sanitize_js_callback' => 'maybe_hash_hex_color'
				)
			);

			// Control
			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					'baton_secondary_header_background_color',
					array(
						'label' => __( 'Background Color', 'baton' ),
						'section' => 'baton_secondary_header_background',
						'settings' => 'baton_secondary_header_background_color',
						'priority' => 10
					)
				)
			);


			/**
			 * Secondary Header Background Image
			 */
			// Setting
			$wp_customize->add_setting(
				'baton_secondary_header_background_image',
				array(
					'default' => apply_filters( 'theme_mod_baton_secondary_header_background_image', '' ),
					'sanitize_callback' => 'wp_unslash',
				)
			);

			// Control
			$wp_customize->add_control(
				new WP_Customize_Image_Control(
					$wp_customize,
					'baton_secondary_header_background_image',
					array(
						'label' => __( 'Background Image', 'baton' ),
						'section' => 'baton_secondary_header_background',
						'settings' => 'baton_secondary_header_background_image',
						'priority' => 20,
					)
				)
			);

			// Add this control ID to the list of background image controls
			$this->baton_background_image_control_ids[] = 'baton_secondary_header_background_image';

			/**
			 * Secondary Header Background Image Repeat
			 */
			// Setting
			$wp_customize->add_setting(
				'baton_secondary_header_background_image_repeat',
				array(
					'default' => apply_filters( 'theme_mod_baton_secondary_header_background_image_repeat', 'repeat' ),
					'sanitize_callback' => 'sanitize_text_field',
				)
			);

			// Control
			$wp_customize->add_control(
				new WP_Customize_Control(
					$wp_customize,
					'baton_secondary_header_background_image_repeat',
					array(
						'label' => __( 'Background Repeat', 'baton' ),
						'section' => 'baton_secondary_header_background',
						'settings' => 'baton_secondary_header_background_image_repeat',
						'priority' => 30,
						'type' => 'radio',
						'choices' => array(
							'no-repeat' => __( 'No Repeat', 'baton' ),
							'repeat' => __( 'Tile', 'baton' ),
							'repeat-x' => __( 'Tile Horizontally', 'baton' ),
							'repeat-y' => __( 'Tile Vertically', 'baton' )
						)
					)
				)
			);


			/**
			 * Secondary Header Background Image Position X
			 */
			// Setting
			$wp_customize->add_setting(
				'baton_secondary_header_background_image_position_x',
				array(
					'default' => apply_filters( 'theme_mod_baton_secondary_header_background_image_position_x', 'left' ),
					'sanitize_callback' => 'sanitize_text_field',
				)
			);

			// Control
			$wp_customize->add_control(
				new WP_Customize_Control(
					$wp_customize,
					'baton_secondary_header_background_image_position_x',
					array(
						'label' => __( 'Background Position', 'baton' ),
						'section' => 'baton_secondary_header_background',
						'settings' => 'baton_secondary_header_background_image_position_x',
						'priority' => 40,
						'type' => 'radio',
						'choices' => array(
							'left' => __( 'Left', 'baton' ),
							'center' => __( 'Center', 'baton' ),
							'right' => __( 'Right', 'baton' )
						)
					)
				)
			);


			/**
			 * Secondary Header Background Image Attachment
			 */
			// Setting
			$wp_customize->add_setting(
				'baton_secondary_header_background_image_attachment',
				array(
					'default' => apply_filters( 'theme_mod_baton_secondary_header_background_image_attachment', 'scroll' ),
					'sanitize_callback' => 'sanitize_text_field',
				)
			);

			// Control
			$wp_customize->add_control(
				new WP_Customize_Control(
					$wp_customize,
					'baton_secondary_header_background_image_attachment',
					array(
						'label' => __( 'Background Attachment', 'baton' ),
						'section' => 'baton_secondary_header_background',
						'settings' => 'baton_secondary_header_background_image_attachment',
						'priority' => 50,
						'type' => 'radio',
						'choices' => array(
							'scroll' => __( 'Scroll', 'baton' ),
							'fixed' => __( 'Fixed', 'baton' )
						)
					)
				)
			);


			/**
			 * Content Panel
			 */
			$wp_customize->add_panel( 'baton_content', array(
				'priority' => 60, // After Secondary Header
				'title' => __( 'Content', 'baton' )
			) );


			/**
			 * Color Section
			 */
			$wp_customize->add_section( 'baton_content_colors', array(
				'priority' => 10, // Top
				'title' => __( 'Colors', 'baton' ),
				'panel' => 'baton_content'
			) );


			/**
			 * Archive Title Color
			 */
			// Setting
			$wp_customize->add_setting(
				'baton_archive_title_color',
				array(
					'default' => apply_filters( 'theme_mod_baton_archive_title_color', '' ),
					'sanitize_callback' => 'sanitize_hex_color',
					'sanitize_js_callback' => 'maybe_hash_hex_color'
				)
			);

			// Control
			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					'baton_archive_title_color',
					array(
						'label' => __( 'Archive Title', 'baton' ),
						'section' => 'baton_content_colors',
						'settings' => 'baton_archive_title_color',
						'priority' => 10
					)
				)
			);


			/**
			 * Post Title Color
			 */
			// Setting
			$wp_customize->add_setting(
				'baton_post_title_color',
				array(
					'default' => apply_filters( 'theme_mod_baton_post_title_color', '' ),
					'sanitize_callback' => 'sanitize_hex_color',
					'sanitize_js_callback' => 'maybe_hash_hex_color'
				)
			);

			// Control
			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					'baton_post_title_color',
					array(
						'label' => __( 'Post Title', 'baton' ),
						'section' => 'baton_content_colors',
						'settings' => 'baton_post_title_color',
						'priority' => 20
					)
				)
			);


			/**
			 * Content Color (registered in SDS Core)
			 */
			if ( $content_color_control = $wp_customize->get_control( 'content_color' ) ) { // Get Control
				$content_color_control->section = 'baton_content_colors'; // Adjust Section
				$content_color_control->label = __( 'Content', 'baton' ); // Adjust Label
				$content_color_control->priority = 30; // Adjust Priority
			}


			/**
			 * Link Color (registered in SDS Core)
			 */
			if ( $link_color_control = $wp_customize->get_control( 'link_color' ) ) { // Get Control
				$link_color_control->section = 'baton_content_colors'; // Adjust Section
				$link_color_control->label = __( 'Link', 'baton' ); // Adjust Label
				$link_color_control->priority = 40; // Adjust Priority
			}


			/**
			 * Button Text Color
			 */
			// Setting
			$wp_customize->add_setting(
				'baton_button_text_color',
				array(
					'default' => apply_filters( 'theme_mod_baton_button_text_color', '' ),
					'sanitize_callback' => 'sanitize_hex_color',
					'sanitize_js_callback' => 'maybe_hash_hex_color'
				)
			);

			// Control
			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					'baton_button_text_color',
					array(
						'label' => __( 'Button Text', 'baton' ),
						'section' => 'baton_content_colors',
						'settings' => 'baton_button_text_color',
						'priority' => 50
					)
				)
			);


			/**
			 * Button Hover Text Color
			 */
			// Setting
			$wp_customize->add_setting(
				'baton_button_hover_text_color',
				array(
					'default' => apply_filters( 'theme_mod_baton_button_hover_text_color', '' ),
					'sanitize_callback' => 'sanitize_hex_color',
					'sanitize_js_callback' => 'maybe_hash_hex_color'
				)
			);

			// Control
			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					'baton_button_hover_text_color',
					array(
						'label' => __( 'Button Text Hover', 'baton' ),
						'section' => 'baton_content_colors',
						'settings' => 'baton_button_hover_text_color',
						'priority' => 60
					)
				)
			);


			/**
			 * Button Background Color
			 */
			// Setting
			$wp_customize->add_setting(
				'baton_button_background_color',
				array(
					'default' => apply_filters( 'theme_mod_baton_button_background_color', '' ),
					'sanitize_callback' => 'sanitize_hex_color',
					'sanitize_js_callback' => 'maybe_hash_hex_color'
				)
			);

			// Control
			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					'baton_button_background_color',
					array(
						'label' => __( 'Button Background', 'baton' ),
						'section' => 'baton_content_colors',
						'settings' => 'baton_button_background_color',
						'priority' => 70
					)
				)
			);


			/**
			 * Button Hover Background Color
			 */
			// Setting
			$wp_customize->add_setting(
				'baton_button_hover_background_color',
				array(
					'default' => apply_filters( 'theme_mod_baton_button_hover_background_color', '' ),
					'sanitize_callback' => 'sanitize_hex_color',
					'sanitize_js_callback' => 'maybe_hash_hex_color'
				)
			);

			// Control
			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					'baton_button_hover_background_color',
					array(
						'label' => __( 'Button Background Hover', 'baton' ),
						'section' => 'baton_content_colors',
						'settings' => 'baton_button_hover_background_color',
						'priority' => 80
					)
				)
			);


			/**
			 * Headings Section
			 */
			$wp_customize->add_section( 'baton_content_headings', array(
				'priority' => 20, // After Colors
				'title' => __( 'Headings', 'baton' ),
				'panel' => 'baton_content'
			) );


			/**
			 * Heading 1 Font Size
			 */
			if ( $heading_1_font_size_control = $wp_customize->get_control( 'baton_h1_font_size' ) ) // Get Control
				$heading_1_font_size_control->section = 'baton_content_headings'; // Adjust Section


			/**
			 * Heading 1 Font Family
			 */
			if ( $heading_1_font_family_control = $wp_customize->get_control( 'baton_h1_font_family' ) ) // Get Control
				$heading_1_font_family_control->section = 'baton_content_headings'; // Adjust Section


			/**
			 * Heading 2 Font Size
			 */
			if ( $heading_2_font_size_control = $wp_customize->get_control( 'baton_h2_font_size' ) ) // Get Control
				$heading_2_font_size_control->section = 'baton_content_headings'; // Adjust Section


			/**
			 * Heading 2 Font Family
			 */
			if ( $heading_2_font_family_control = $wp_customize->get_control( 'baton_h2_font_family' ) ) // Get Control
				$heading_2_font_family_control->section = 'baton_content_headings'; // Adjust Section

			/**
			 * Heading 3 Font Size
			 */
			if ( $heading_3_font_size_control = $wp_customize->get_control( 'baton_h3_font_size' ) ) // Get Control
				$heading_3_font_size_control->section = 'baton_content_headings'; // Adjust Section


			/**
			 * Heading 3 Font Family
			 */
			if ( $heading_3_font_family_control = $wp_customize->get_control( 'baton_h3_font_family' ) ) // Get Control
				$heading_3_font_family_control->section = 'baton_content_headings'; // Adjust Section


			/**
			 * Heading 4 Font Size
			 */
			if ( $heading_4_font_size_control = $wp_customize->get_control( 'baton_h4_font_size' ) ) // Get Control
				$heading_4_font_size_control->section = 'baton_content_headings'; // Adjust Section


			/**
			 * Heading 4 Font Family
			 */
			if ( $heading_4_font_family_control = $wp_customize->get_control( 'baton_h4_font_family' ) ) // Get Control
				$heading_4_font_family_control->section = 'baton_content_headings'; // Adjust Section


			/**
			 * Heading 5 Font Size
			 */
			if ( $heading_5_font_size_control = $wp_customize->get_control( 'baton_h5_font_size' ) ) // Get Control
				$heading_5_font_size_control->section = 'baton_content_headings'; // Adjust Section


			/**
			 * Heading 5 Font Family
			 */
			if ( $heading_5_font_family_control = $wp_customize->get_control( 'baton_h5_font_family' ) ) // Get Control
				$heading_5_font_family_control->section = 'baton_content_headings'; // Adjust Section


			/**
			 * Heading 6 Font Size
			 */
			if ( $heading_6_font_size_control = $wp_customize->get_control( 'baton_h6_font_size' ) ) // Get Control
				$heading_6_font_size_control->section = 'baton_content_headings'; // Adjust Section


			/**
			 * Heading 6 Font Family
			 */
			if ( $heading_6_font_family_control = $wp_customize->get_control( 'baton_h6_font_family' ) ) // Get Control
				$heading_6_font_family_control->section = 'baton_content_headings'; // Adjust Section


			/**
			 * Body (Content) Section
			 */
			$wp_customize->add_section( 'baton_content_body', array(
				'priority' => 30, // After Headings
				'title' => __( 'Body', 'baton' ),
				'panel' => 'baton_content'
			) );


			/**
			 * Body (Content) Font Size
			 */
			if ( $body_font_size_control = $wp_customize->get_control( 'baton_body_font_size' ) ) // Get Control
				$body_font_size_control->section = 'baton_content_body'; // Adjust Section


			/**
			 * Body (Content) Font Size
			 */
			if ( $body_line_height_control = $wp_customize->get_control( 'baton_body_line_height' ) ) // Get Control
				$body_line_height_control->section = 'baton_content_body'; // Adjust Section


			/**
			 * Body (Content) Font Family
			 */
			if ( $body_font_family_control = $wp_customize->get_control( 'baton_body_font_family' ) ) // Get Control
				$body_font_family_control->section = 'baton_content_body'; // Adjust Section


			/**
			 * More Link Section
			 */
			$wp_customize->add_section( 'baton_content_more_link', array(
				'priority' => 40, // After Body (Content)
				'title' => __( 'More Link', 'baton' ),
				'panel' => 'baton_content'
			) );


			/**
			 * More Link Button Label
			 */
			// Setting
			$wp_customize->add_setting(
				'baton_more_link_label',
				array(
					'default' => apply_filters( 'theme_mod_baton_more_link_label', '' ),
					'sanitize_callback' => 'sanitize_text_field'
				)
			);

			// Control
			$wp_customize->add_control(
				new WP_Customize_Control(
					$wp_customize,
					'baton_more_link_label',
					array(
						'label' => __( 'Button Label', 'baton' ),
						'section' => 'baton_content_more_link',
						'settings' => 'baton_more_link_label',
						'priority' => 10
					)
				)
			);


			/**
			 * Widget Design Panel
			 */
			$wp_customize->add_panel( 'baton_widget_design', array(
				'priority' => 70, // After Content
				'title' => __( 'Widget Design', 'baton' )
			) );


			/**
			 * Widget Color Section
			 */
			$wp_customize->add_section( 'baton_widget_colors', array(
				'priority' => 10, // Top
				'title' => _x( 'Colors', 'widget colors Customizer section label', 'baton' ),
				'panel' => 'baton_widget_design'
			) );


			/**
			 * Widget Title Color
			 */
			// Setting
			$wp_customize->add_setting(
				'baton_widget_title_color',
				array(
					'default' => apply_filters( 'theme_mod_baton_widget_title_color', '' ),
					'sanitize_callback' => 'sanitize_hex_color',
					'sanitize_js_callback' => 'maybe_hash_hex_color'
				)
			);

			// Control
			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					'baton_widget_title_color',
					array(
						'label' => __( 'Widget Title Color', 'baton' ),
						'section' => 'baton_widget_colors',
						'settings' => 'baton_widget_title_color',
						'priority' => 10
					)
				)
			);


			/**
			 * Widget Text Color
			 */
			// Setting
			$wp_customize->add_setting(
				'baton_widget_color',
				array(
					'default' => apply_filters( 'theme_mod_baton_widget_color', '' ),
					'sanitize_callback' => 'sanitize_hex_color',
					'sanitize_js_callback' => 'maybe_hash_hex_color'
				)
			);

			// Control
			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					'baton_widget_color',
					array(
						'label' => _x( 'Text Color', 'widget text color Customizer control label', 'baton' ),
						'section' => 'baton_widget_colors',
						'settings' => 'baton_widget_color',
						'priority' => 20
					)
				)
			);

			/**
			 * Widget Link Color
			 */
			// Setting
			$wp_customize->add_setting(
				'baton_widget_link_color',
				array(
					'default' => apply_filters( 'theme_mod_baton_widget_link_color', '' ),
					'sanitize_callback' => 'sanitize_hex_color',
					'sanitize_js_callback' => 'maybe_hash_hex_color'
				)
			);

			// Control
			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					'baton_widget_link_color',
					array(
						'label' => _x( 'Link Color', 'widget link color Customizer control label', 'baton' ),
						'section' => 'baton_widget_colors',
						'settings' => 'baton_widget_link_color',
						'priority' => 30
					)
				)
			);


			/**
			 * Footer Panel
			 */
			$wp_customize->add_panel( 'baton_footer', array(
				'priority' => 80, // After Content
				'title' => __( 'Footer', 'baton' )
			) );


			/**
			 * Colors Section
			 */
			$wp_customize->add_section( 'baton_footer_colors', array(
				'priority' => 10, // Top
				'title' => __( 'Colors', 'baton' ),
				'panel' => 'baton_footer'
			) );

			/**
			 * Footer Text Color
			 */
			// Setting
			$wp_customize->add_setting(
				'baton_footer_text_color',
				array(
					'default' => apply_filters( 'theme_mod_baton_footer_text_color', '' ),
					'sanitize_callback' => 'sanitize_hex_color',
					'sanitize_js_callback' => 'maybe_hash_hex_color'
				)
			);

			// Control
			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					'baton_footer_text_color',
					array(
						'label' => __( 'Text Color', 'baton' ),
						'section' => 'baton_footer_colors',
						'settings' => 'baton_footer_text_color',
						'priority' => 10
					)
				)
			);

			/**
			 * Footer Link Color
			 */
			// Setting
			$wp_customize->add_setting(
				'baton_footer_link_color',
				array(
					'default' => apply_filters( 'theme_mod_baton_footer_link_color', '' ),
					'sanitize_callback' => 'sanitize_hex_color',
					'sanitize_js_callback' => 'maybe_hash_hex_color'
				)
			);

			// Control
			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					'baton_footer_link_color',
					array(
						'label' => __( 'Link', 'baton' ),
						'section' => 'baton_footer_colors',
						'settings' => 'baton_footer_link_color',
						'priority' => 20
					)
				)
			);

			/**
			 * Footer Widget Title Color
			 */
			// Setting
			$wp_customize->add_setting(
				'baton_footer_heading_color',
				array(
					'default' => apply_filters( 'theme_mod_baton_footer_heading_color', '' ),
					'sanitize_callback' => 'sanitize_hex_color',
					'sanitize_js_callback' => 'maybe_hash_hex_color'
				)
			);

			// Control
			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					'baton_footer_heading_color',
					array(
						'label' => __( 'Headings', 'baton' ),
						'section' => 'baton_footer_colors',
						'settings' => 'baton_footer_heading_color',
						'priority' => 30
					)
				)
			);


			/**
			 * Copyright and Branding Section
			 */
			if ( $sds_footer_copyright_branding_section = $wp_customize->get_section( 'sds_footer_copyright_branding' ) ) // Get Section
				$sds_footer_copyright_branding_section->panel = 'baton_footer'; // Add Panel


			/**
			 * Background Section
			 */
			$wp_customize->add_section( 'baton_footer_background', array(
				'priority' => 30, // After Branding/Copyright
				'title' => __( 'Background', 'baton' ),
				'panel' => 'baton_footer'
			) );


			/**
			 * Footer Background Color
			 */
			// Setting
			$wp_customize->add_setting(
				'baton_footer_background_color',
				array(
					'default' => apply_filters( 'theme_mod_baton_footer_background_color', '' ),
					'sanitize_callback' => 'sanitize_hex_color',
					'sanitize_js_callback' => 'maybe_hash_hex_color'
				)
			);

			// Control
			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					'baton_footer_background_color',
					array(
						'label' => __( 'Background Color', 'baton' ),
						'section' => 'baton_footer_background',
						'settings' => 'baton_footer_background_color',
						'priority' => 10
					)
				)
			);


			/**
			 * Footer Background Image
			 */
			// Setting
			$wp_customize->add_setting(
				'baton_footer_background_image',
				array(
					'default' => apply_filters( 'theme_mod_baton_footer_background_image', '' ),
					'sanitize_callback' => 'wp_unslash',
				)
			);

			// Control
			$wp_customize->add_control(
				new WP_Customize_Image_Control(
					$wp_customize,
					'baton_footer_background_image',
					array(
						'label' => __( 'Background Image', 'baton' ),
						'section' => 'baton_footer_background',
						'settings' => 'baton_footer_background_image',
						'priority' => 20,
					)
				)
			);

			// Add this control ID to the list of background image controls
			$this->baton_background_image_control_ids[] = 'baton_footer_background_image';

			/**
			 * Footer Background Image Repeat
			 */
			// Setting
			$wp_customize->add_setting(
				'baton_footer_background_image_repeat',
				array(
					'default' => apply_filters( 'theme_mod_baton_footer_background_image_repeat', 'repeat' ),
					'sanitize_callback' => 'sanitize_text_field',
				)
			);

			// Control
			$wp_customize->add_control(
				new WP_Customize_Control(
					$wp_customize,
					'baton_footer_background_image_repeat',
					array(
						'label' => __( 'Background Repeat', 'baton' ),
						'section' => 'baton_footer_background',
						'settings' => 'baton_footer_background_image_repeat',
						'priority' => 30,
						'type' => 'radio',
						'choices' => array(
							'no-repeat' => __( 'No Repeat', 'baton' ),
							'repeat' => __( 'Tile', 'baton' ),
							'repeat-x' => __( 'Tile Horizontally', 'baton' ),
							'repeat-y' => __( 'Tile Vertically', 'baton' )
						)
					)
				)
			);


			/**
			 * Footer Background Image Position X
			 */
			// Setting
			$wp_customize->add_setting(
				'baton_footer_background_image_position_x',
				array(
					'default' => apply_filters( 'theme_mod_baton_footer_background_image_position_x', 'left' ),
					'sanitize_callback' => 'sanitize_text_field',
				)
			);

			// Control
			$wp_customize->add_control(
				new WP_Customize_Control(
					$wp_customize,
					'baton_footer_background_image_position_x',
					array(
						'label' => __( 'Background Position', 'baton' ),
						'section' => 'baton_footer_background',
						'settings' => 'baton_footer_background_image_position_x',
						'priority' => 40,
						'type' => 'radio',
						'choices' => array(
							'left' => __( 'Left', 'baton' ),
							'center' => __( 'Center', 'baton' ),
							'right' => __( 'Right', 'baton' )
						)
					)
				)
			);


			/**
			 * Footer Background Image Attachment
			 */
			// Setting
			$wp_customize->add_setting(
				'baton_footer_background_image_attachment',
				array(
					'default' => apply_filters( 'theme_mod_baton_footer_background_image_attachment', 'scroll' ),
					'sanitize_callback' => 'sanitize_text_field',
				)
			);

			// Control
			$wp_customize->add_control(
				new WP_Customize_Control(
					$wp_customize,
					'baton_footer_background_image_attachment',
					array(
						'label' => __( 'Background Attachment', 'baton' ),
						'section' => 'baton_footer_background',
						'settings' => 'baton_footer_background_image_attachment',
						'priority' => 50,
						'type' => 'radio',
						'choices' => array(
							'scroll' => __( 'Scroll', 'baton' ),
							'fixed' => __( 'Fixed', 'baton' )
						)
					)
				)
			);
		}

		/**
		 * This function enqueues scripts and styles on the Customizer.
		 */
		public function customize_controls_enqueue_scripts() {
			$baton_theme_helper = Baton_Theme_Helper(); // Grab the Baton_Theme_Helper instance

			// Baton Customize Controls
			wp_enqueue_script( 'baton-customize-controls', get_template_directory_uri() . '/customizer/js/baton-customize-controls.js', array( 'customize-controls' ), $this->version );

			// Setup initial localization data
			$localization_data = array(
				// TODO: Add other support features here (if necessary)
				'theme_support' => array(
					'fonts' => $baton_theme_helper->current_theme_supports( 'fonts' )
				),
				// WordPress 4.0
				'is_wp_4_0' => $this->version_compare( '4.1', '<' ),
				// Color Schemes
				'sds_color_schemes' => sds_color_schemes(),
				'sds_color_scheme_control_ids' => $this->sds_color_scheme_control_ids,
				'sds_color_scheme_setting_id' => 'sds_theme_options[color_scheme]',
				'baton_background_image_control_ids' => $this->baton_background_image_control_ids
			);

			// Baton Font Customizer localization data
			if ( $baton_theme_helper->current_theme_supports( 'fonts' ) ) {
				// Load Baton Customizer Fonts if necessary
				if ( ! function_exists( 'Baton_Customizer_Fonts' ) )
					include_once 'class-baton-customizer-fonts.php'; // Customizer Font Settings/Controls

				// Grab the Baton_Customizer_Fonts instance
				$baton_customizer_fonts = Baton_Customizer_Fonts();

				$localization_data['baton_font_families'] = $baton_customizer_fonts->get_google_fonts_choices( false, true );
				$localization_data['baton_font_family_regex'] = '^baton_.+_font_family$';

				$localization_data['baton_font_property_control_ids'] = $baton_customizer_fonts->baton_font_property_control_ids;
				$localization_data['baton_font_property_defaults'] = $baton_customizer_fonts->baton_font_property_defaults;
				$localization_data['baton_font_family_control_ids'] = $baton_customizer_fonts->baton_font_family_control_ids;
				$localization_data['baton_font_family_defaults'] = $baton_customizer_fonts->baton_font_family_defaults;
			}

			// Allow for filtering of localization data
			$localization_data = apply_filters( 'baton_customize_controls_localization', $localization_data, $this );

			// Localize the script data
			wp_localize_script( 'baton-customize-controls', 'baton_customize_controls', $localization_data );

			// Baton Customizer CSS
			wp_enqueue_style( 'baton-customizer', get_template_directory_uri() . '/customizer/css/baton-customizer.css', array( 'sds-theme-options' ) );

			// Select2
			wp_enqueue_script( 'select2', get_template_directory_uri() . '/customizer/js/select2/select2.min.js', array( 'jquery' ), $this->version );
			wp_enqueue_style( 'select2', get_template_directory_uri() . '/customizer/js/select2/select2.css' );
		}

		/**
		 * This function filters SDS Theme Options on the Customizer window only.
		 */
		public function option_sds_theme_options( $value ) {
			// Only on the Customizer
			if ( did_action( 'customize_controls_init' ) ) {
				// Footer Copyright
				if ( ! isset( $value['footer']['copyright'] ) || empty( $value['footer']['copyright'] ) )
					$value['footer']['copyright'] = sds_get_copyright();

				// Footer Branding
				if ( ! isset( $value['footer']['branding'] ) || empty( $value['footer']['branding'] ) )
					$value['footer']['branding'] = sds_get_copyright_branding();
			}

			return $value;
		}

		/**
		 * This function resets transient data upon SDS Theme Options save.
		 */
		function sanitize_option_sds_theme_options( $input ) {
			// Set the current Baton color scheme
			$sds_color_schemes = sds_color_schemes();
			$this->sds_color_scheme = ( ! empty( $input['color_scheme'] ) && array_key_exists( $input['color_scheme'], $sds_color_schemes ) ) ? $sds_color_schemes[$input['color_scheme']] : $this->sds_color_scheme;

			// Reset transient data
			$this->reset_transient();

			return $input;
		}

		/**
		 * This function sets the default primary color in the Customizer.
		 */
		public function theme_mod_primary_color( $color = false ) {
			// Return the current color if set
			if ( $color )
				return $color;

			// Return the default
			return $this->get_current_color_scheme_default( 'primary_color', '#ffffff' );
		}

		/**
		 * This function sets the default secondary color in the Customizer.
		 */
		public function theme_mod_secondary_color( $color = false ) {
			// Return the current color if set
			if ( $color )
				return $color;

			// Return the default
			return $this->get_current_color_scheme_default( 'secondary_color', '#ffffff' );
		}

		/**
		 * This function sets the default content color in the Customizer.
		 */
		public function theme_mod_content_color( $color = false ) {
			// Return the current color if set
			if ( $color )
				return $color;

			// Return the default
			return $this->get_current_color_scheme_default( 'content_color', '#4c5357' );
		}

		/**
		 * This function sets the default content link color in the Customizer.
		 */
		public function theme_mod_link_color( $color = false ) {
			// Return the current color if set
			if ( $color )
				return $color;

			// Return the default
			return $this->get_current_color_scheme_default( 'link_color', '#3ebbbb' );
		}

		/**
		 * This function sets the default post title color in the Customizer.
		 */
		public function theme_mod_baton_post_title_color( $color = false ) {
			// Return the current color if set
			if ( $color )
				return $color;

			// Return the default
			return $this->get_current_color_scheme_default( 'baton_post_title_color', '#363a42' );
		}

		/**
		 * This function sets the default archive title color in the Customizer.
		 */
		public function theme_mod_baton_archive_title_color( $color = false ) {
			// Return the current color if set
			if ( $color )
				return $color;

			// Return the default
			return $this->get_current_color_scheme_default( 'baton_archive_title_color', '#363a42' );
		}

		/**
		 * This function sets the default button text color in the Customizer.
		 */
		public function theme_mod_baton_button_text_color( $color = false ) {
			// Return the current color if set
			if ( $color )
				return $color;

			// Return the default
			return $this->get_current_color_scheme_default( 'baton_button_text_color', '#ffffff' );
		}

		/**
		 * This function sets the default hover button text color in the Customizer.
		 */
		public function theme_mod_baton_button_hover_text_color( $color = false ) {
			// Return the current color if set
			if ( $color )
				return $color;

			// Return the default
			return $this->get_current_color_scheme_default( 'baton_button_hover_text_color', '#ffffff' );
		}

		/**
		 * This function sets the default hover button background color in the Customizer.
		 */
		public function theme_mod_baton_button_background_color( $color = false ) {
			// Return the current color if set
			if ( $color )
				return $color;

			// Return the default
			return $this->get_current_color_scheme_default( 'baton_button_background_color', '#3ebbbb' );
		}

		/**
		 * This function sets the default hover button background color in the Customizer.
		 */
		public function theme_mod_baton_button_hover_background_color( $color = false ) {
			// Return the current color if set
			if ( $color )
				return $color;

			// Return the default
			return $this->get_current_color_scheme_default( 'baton_button_hover_background_color', '#363a42' );
		}

		/**
		 * This function sets the default content background color in the Customizer.
		 */
		public function theme_mod_baton_content_background_color( $color = false ) {
			// Return the current color if set
			if ( $color )
				return $color;

			// Return the default
			return $this->get_current_color_scheme_default( 'baton_content_background_color', '#ffffff' );
		}

		/**
		 * This function sets the default widget title color in the Customizer.
		 */
		public function theme_mod_baton_widget_title_color( $color = false ) {
			// Return the current color if set
			if ( $color )
				return $color;

			// Return the default
			return $this->get_current_color_scheme_default( 'baton_widget_title_color', '#363a42' );
		}

		/**
		 * This function sets the default widget color in the Customizer.
		 */
		public function theme_mod_baton_widget_color( $color = false ) {
			// Return the current color if set
			if ( $color )
				return $color;

			// Return the default
			return $this->get_current_color_scheme_default( 'baton_widget_color', '#4c5357' );
		}

		/**
		 * This function sets the default widget link color in the Customizer.
		 */
		public function theme_mod_baton_widget_link_color( $color = false ) {
			// Return the current color if set
			if ( $color )
				return $color;

			// Return the default
			return $this->get_current_color_scheme_default( 'baton_widget_link_color', '#3ebbbb' );
		}

		/**
		 * This function sets the default site title color in the Customizer.
		 */
		public function theme_mod_baton_site_title_color( $color = false ) {
			// Return the current color if set
			if ( $color )
				return $color;

			// Return the default
			return $this->get_current_color_scheme_default( 'baton_site_title_color', '#ffffff' );
		}

		/**
		 * This function sets the default tagline color in the Customizer.
		 */
		public function theme_mod_baton_tagline_color( $color = false ) {
			// Return the current color if set
			if ( $color )
				return $color;

			// Return the default
			return $this->get_current_color_scheme_default( 'baton_tagline_color', '#84919e' );
		}

		/**
		 * This function sets the default primary navigation hover color in the Customizer.
		 */
		public function theme_mod_baton_primary_hover_active_color( $color = false ) {
			// Return the current color if set
			if ( $color )
				return $color;

			// Return the default
			return $this->get_current_color_scheme_default( 'baton_primary_hover_active_color', '#3ebbbb' );
		}

		/**
		 * This function sets the default primary navigation sub menu color in the Customizer.
		 */
		public function theme_mod_baton_primary_sub_menu_color( $color = false ) {
			// Return the current color if set
			if ( $color )
				return $color;

			// Return the default
			return $this->get_current_color_scheme_default( 'baton_primary_sub_menu_color', '#84919e' );
		}

		/**
		 * This function sets the default primary navigation sub menu hover color in the Customizer.
		 */
		public function theme_mod_baton_primary_sub_menu_hover_color( $color = false ) {
			// Return the current color if set
			if ( $color )
				return $color;

			// Return the default
			return $this->get_current_color_scheme_default( 'baton_primary_sub_menu_hover_color', '#ffffff' );
		}

		/**
		 * This function sets the default primary navigation sub menu background color in the Customizer.
		 */
		public function theme_mod_baton_primary_sub_menu_background_color( $color = false ) {
			// Return the current color if set
			if ( $color )
				return $color;

			// Return the default
			return $this->get_current_color_scheme_default( 'baton_primary_sub_menu_background_color', '#363a42' );
		}

		/**
		 * This function sets the default header background color in the Customizer.
		 */
		public function theme_mod_baton_header_background_color( $color = false ) {
			// Return the current color if set
			if ( $color )
				return $color;

			// Return the default
			return $this->get_current_color_scheme_default( 'baton_header_background_color', '#363a42' );
		}

		/**
		 * This function sets the default secondary navigation hover color in the Customizer.
		 */
		public function theme_mod_baton_secondary_hover_active_color( $color = false ) {
			// Return the current color if set
			if ( $color )
				return $color;

			// Return the default
			return $this->get_current_color_scheme_default( 'baton_secondary_hover_active_color', '#ffffff' );
		}

		/**
		 * This function sets the default secondary navigation sub menu color in the Customizer.
		 */
		public function theme_mod_baton_secondary_header_sub_menu_color( $color = false ) {
			// Return the current color if set
			if ( $color )
				return $color;

			// Return the default
			return $this->get_current_color_scheme_default( 'baton_secondary_header_sub_menu_color', '#ccf4f4' );
		}

		/**
		 * This function sets the default secondary navigation sub menu hover color in the Customizer.
		 */
		public function theme_mod_baton_secondary_header_sub_menu_hover_color( $color = false ) {
			// Return the current color if set
			if ( $color )
				return $color;

			/*
			 * Backwards compatibility check for baton_secondary_sub_menu_hover_color.
			 *
			 * In previous versions of Baton Pro, the theme mod named baton_secondary_sub_menu_hover_color
			 * was used instead of baton_secondary_header_sub_menu_hover_color. If this value is present,
			 * use the stored value instead of grabbing the default value for the selected color scheme.
			 *
			 * We're checking to see if the current filter matches the name of this function. This function
			 * can be called to get the default value for this theme mod, and other logic will compare the
			 * value in the database versus the default. If they match, the CSS for this theme mod will not
			 * be output on the front-end. The current filter check helps us know when this function is being
			 * called to grab the default value instead of the stored value.
			 *
			 * If we have a value in baton_secondary_sub_menu_hover_color, remove that theme mod, and store
			 * the value in the baton_secondary_header_sub_menu_hover_color theme mod instead.
			 */
			if ( current_filter() === 'theme_mod_baton_secondary_header_sub_menu_hover_color' && ( $baton_secondary_sub_menu_hover_color = get_theme_mod( 'baton_secondary_sub_menu_hover_color' ) ) ) {
				// Remove the old theme mod
				remove_theme_mod( 'baton_secondary_sub_menu_hover_color' );

				// Set the new theme mod
				set_theme_mod( 'baton_secondary_header_sub_menu_hover_color', $baton_secondary_sub_menu_hover_color );

				return $baton_secondary_sub_menu_hover_color;
			}

			// Return the default
			return $this->get_current_color_scheme_default( 'baton_secondary_header_sub_menu_hover_color', '#ffffff' );
		}

		/**
		 * This function sets the default secondary navigation sub menu background color in the Customizer.
		 */
		public function theme_mod_baton_secondary_header_sub_menu_background_color( $color = false ) {
			// Return the current color if set
			if ( $color )
				return $color;

			// Return the default
			return $this->get_current_color_scheme_default( 'baton_secondary_header_sub_menu_background_color', '#3ebbbb' );
		}

		/**
		 * This function sets the default secondary header background color in the Customizer.
		 */
		public function theme_mod_baton_secondary_header_background_color( $color = false ) {
			// Return the current color if set
			if ( $color )
				return $color;

			// Return the default
			return $this->get_current_color_scheme_default( 'baton_secondary_header_background_color', '#3ebbbb' );
		}

		/**
		 * This function sets the default footer text color in the Customizer.
		 */
		public function theme_mod_baton_footer_text_color( $color = false ) {
			// Return the current color if set
			if ( $color )
				return $color;

			// Return the default
			return $this->get_current_color_scheme_default( 'baton_footer_text_color', '#acacac' );
		}

		/**
		 * This function sets the default footer link color in the Customizer.
		 */
		public function theme_mod_baton_footer_link_color( $color = false ) {
			// Return the current color if set
			if ( $color )
				return $color;

			// Return the default
			return $this->get_current_color_scheme_default( 'baton_footer_link_color', '#3ebbbb' );
		}

		/**
		 * This function sets the default widget title color in the Customizer.
		 */
		public function theme_mod_baton_footer_heading_color( $color = false ) {
			// Return the current color if set
			if ( $color )
				return $color;

			// Return the default
			return $this->get_current_color_scheme_default( 'baton_footer_heading_color', '#ffffff' );
		}

		/**
		 * This function sets the default footer background color in the Customizer.
		 */
		public function theme_mod_baton_footer_background_color( $color = false ) {
			// Return the current color if set
			if ( $color )
				return $color;

			// Return the default
			return $this->get_current_color_scheme_default( 'baton_footer_background_color', '#363a42' );
		}

		/**
		 * This function sets the default more link label in the Customizer.
		 */
		public function theme_mod_baton_more_link_label( $label = false ) {
			// Return the current color if set
			if ( $label )
				return $label;

			// Return the default
			return baton_more_link_label( true ) ;
		}

		/**
		 * This function adjusts the body classes based on theme mods.
		 */
		public function body_class( $classes ) {
			// Max Width
			if ( ( $theme_mod_baton_max_width = $this->get_theme_mod( 'baton_max_width', 1272 ) ) )
				$classes['baton_max_width'] = 'custom-max-width custom-max-width-' . $theme_mod_baton_max_width . ' max-width-' . $theme_mod_baton_max_width;

			// Main Header Alignment (ignore default value in $this->get_theme_mod() check)
			if ( $theme_mod_baton_main_header_alignment = $this->get_theme_mod( 'baton_main_header_alignment' ) )
				$classes['baton_main_header_alignment'] = 'header-' . $theme_mod_baton_main_header_alignment . ' main-header-' . $theme_mod_baton_main_header_alignment . ' header-alignment-' . $theme_mod_baton_main_header_alignment . ' main-header-alignment-' . $theme_mod_baton_main_header_alignment;
			else
				$classes['baton_main_header_alignment'] = 'header-traditional main-header-traditional header-alignment-traditional main-header-alignment-traditional';

			// Secondary Header Alignment (ignore default value in $this->get_theme_mod() check)
			if ( $theme_mod_baton_secondary_header_alignment = $this->get_theme_mod( 'baton_secondary_header_alignment' ) )
				$classes['baton_secondary_header_alignment'] = 'secondary-header-' . $theme_mod_baton_secondary_header_alignment . ' secondary-header-' . $theme_mod_baton_secondary_header_alignment . ' secondary-header-alignment-' . $theme_mod_baton_secondary_header_alignment;
			else
				$classes['baton_secondary_header_alignment'] = 'secondary-header-traditional header-alignment-traditional secondary-header-alignment-traditional';

			// Custom Header Alignment CSS class
			if ( isset( $theme_mod_baton_main_header_alignment ) || isset( $theme_mod_baton_secondary_header_alignment ) )
				$classes['baton_custom_header_alignment'] = 'custom-header-alignment';

			return $classes;
		}

		/**
		 * This function returns a CSS <style> block for Customizer theme mods.
		 */
		// TODO: Variable names might be too long
		// TODO: Tweak output so that each block of CSS is stored in an array, ordered by theme_mod name, with a selector and properties
		public function get_customizer_css() {
			// Check transient first (not in the Customizer)
			if ( ! $this->is_customize_preview() && ! empty( $this->transient_data ) && isset( $this->transient_data['customizer_css' ] ) )
				return $this->transient_data['customizer_css'];
			// Otherwise return data
			else {
				// Make sure Customizer functions are available
				if ( is_admin() && ! class_exists( 'WP_Customize_Manager' ) )
					include_once ABSPATH . WPINC . '/class-wp-customize-manager.php';

				// Grab the SDS Theme Options Instance
				$sds_theme_options_instance = SDS_Theme_Options_Instance();

				// Open <style>
				$r = '<style type="text/css" id="' . $sds_theme_options_instance->get_parent_theme()->get_template() . '-customizer">';

				// If we have a max width set by the user
				if ( ( $theme_mod_baton_max_width = $this->get_theme_mod( 'baton_max_width', 1272 ) ) ) {
					$r .= '/* Maximum Width */' . "\n";
					$r .= '.in,' . "\n";
					$r .= '.front-page-widgets .conductor-widget-title, .front-page-widgets .conductor-widget,' . "\n";
					$r .= '.front-page-widgets .widget.conductor-widget, .conductor-widget .front-page-widget-in,' . "\n";
					$r .= '.widget.conductor-widget .front-page-widget-in,' . "\n";
					$r .= '.conductor-slider-testimonials-slider, .conductor-slider-hero-content, .conductor-slider-news-slider,' . "\n";
					$r .= '.front-page-widgets .conductor-slider-testimonials-slider, .front-page-widgets .conductor-slider-hero-content, .front-page-widgets .conductor-slider-news-slider,' . "\n";
					$r .= '.conductor.conductor-baton-landing-page-active .content-conductor-container .conductor-slider-testimonials-slider,' . "\n";
					$r .= '.conductor.conductor-baton-landing-page-active .content-conductor-container .conductor-slider-hero-content,' . "\n";
					$r .= '.conductor.conductor-baton-landing-page-active .content-conductor-container .conductor-slider-news-slider,' . "\n";
					$r .= '.conductor.conductor-baton-landing-page-active .content-conductor-container .conductor-widget-title,' . "\n";
					$r .= '.conductor.conductor-baton-landing-page-active .content-conductor-container .conductor-widget,' . "\n";
					$r .= '.conductor.conductor-baton-landing-page-active .content-conductor-container .widget.conductor-widget,' . "\n";
					$r .= '.conductor.conductor-baton-landing-page-active .content-conductor-container .conductor-widget .baton-conductor-widget-in,' . "\n";
					$r .= '.conductor.conductor-baton-landing-page-active .content-conductor-container .widget.conductor-widget .baton-conductor-widget-in,' . "\n";
					$r .= '.baton-note-sidebar .conductor-slider-testimonials-slider,' . "\n";
					$r .= '.baton-note-sidebar .conductor-slider-hero-content,' . "\n";
					$r .= '.baton-note-sidebar .conductor-slider-news-slider,' . "\n";
					$r .= '.baton-note-sidebar .conductor-widget-title,' . "\n";
					$r .= '.baton-note-sidebar .conductor-widget,' . "\n";
					$r .= '.baton-note-sidebar .widget.conductor-widget,' . "\n";
					$r .= '.baton-note-sidebar .conductor-widget .baton-note-sidebar-widget-in,' . "\n";
					$r .= '.baton-note-sidebar .widget.conductor-widget .baton-note-sidebar-widget-in,' . "\n";
					$r .= '.baton-note-sidebar .conductor-row.conductor-widget-single-flexbox-wrap {' . "\n";
						$r .= 'max-width: ' . $theme_mod_baton_max_width . 'px;' . "\n";
					$r .= '}' . "\n\n";
				}

				// If we have a background color
				if ( ( $baton_background_color = $this->get_theme_mod( 'background_color', $this->get_current_color_scheme_default( 'background_color', get_theme_support( 'custom-background', 'default-color' ) ), 'ltrim_hash', 'ltrim_hash' ) ) ) {
					// Hash the hex color code
					$baton_background_color = ( function_exists( 'maybe_hash_hex_color' ) ) ? maybe_hash_hex_color( $baton_background_color ) : $this->maybe_hash_hex_color( $baton_background_color );
					$baton_background_color_variant = baton_get_color_variant( $baton_background_color );

					$r .= '/* Background Colors */' . "\n";
					$r .= 'hr, th, pre, input, textarea, select, .widget_tag_cloud .tagcloud a {' . "\n";
						$r .= 'background: ' . $baton_background_color . ';' . "\n";
					$r .= '}' . "\n\n";

					$r .= '/* Box Shadow Colors */' . "\n";
					$r .= '.content, .article-author, .after-posts-widgets .widget, .comments, .sidebar-container,' . "\n";
					$r .= '.page-numbers li a, .page-numbers li span, .conductor-default-content {' . "\n";
						$r .= 'box-shadow: 0 2px 0 0 ' . $baton_background_color_variant . ';' . "\n";
					$r .= '}' . "\n\n";

					$r .= '/* Border Right Colors */' . "\n";
					$r .= '.footer-nav .menu li {' . "\n";
						$r .= 'border-right-color: ' . $baton_background_color_variant . ';' . "\n";
					$r .= '}' . "\n\n";

					$r .= '/* Border Bottom Colors */' . "\n";
					$r .= 'tr, .sidebar .widget, .comments-list > li, .comments-list .children > li {' . "\n";
						$r .= 'border-bottom-color: ' . $baton_background_color . ';' . "\n";
					$r .= '}' . "\n\n";

					$r .= '/* Gravity Forms - mc-gravity, mc_gravity, mc-newsletter, mc_newsletter */' . "\n";
					$r .= 'body .mc-gravity, .mc_gravity, body .mc-newsletter, .mc_newsletter, body .mc-gravity_wrapper, .mc_gravity_wrapper, body .mc-newsletter_wrapper, .mc_newsletter_wrapper {' . "\n";
						$r .= 'border-color: ' . $baton_background_color . ';' . "\n";
					$r .= '}' . "\n\n";
				}

				// If we have a primary color selected by the user
				if ( ( $theme_mod_primary_color = $this->get_theme_mod( 'primary_color', $this->theme_mod_primary_color() ) ) ) {
					$r .= '/* Primary Color */' . "\n";
					$r .= 'nav .primary-nav li a {' . "\n";
						$r .= 'color: ' . $theme_mod_primary_color . ';' . "\n";
					$r .= '}' . "\n\n";

					// Media Queries
					$r .= '@media only screen and (max-width: 768px) {' . "\n";
						$r .= 'nav .primary-nav .child-menu-button {' . "\n";
							$r .= 'color: ' . $theme_mod_primary_color . ';' . "\n";
						$r .= '}' . "\n";
					$r .= '}' . "\n\n";
				}

				// If we have a secondary color selected by the user
				if ( ( $theme_mod_secondary_color = $this->get_theme_mod( 'secondary_color', $this->theme_mod_secondary_color() ) ) ) {
					$r .= '/* Secondary Color */' . "\n";
					$r .= '#secondary-nav li a {' . "\n";
						$r .= 'color: ' . $theme_mod_secondary_color . ';' . "\n";
					$r .= '}' . "\n\n";

					// Media Queries
					$r .= '@media only screen and (max-width: 768px) {' . "\n";
						$r .= '#secondary-nav li a {' . "\n";
							$r .= 'color: ' . $theme_mod_secondary_color . ';' . "\n";
						$r .= '}' . "\n";
						$r .= '#secondary-nav .child-menu-button {' . "\n";
							$r .= 'color: ' . $theme_mod_secondary_color . ';' . "\n";
						$r .= '}' . "\n";
					$r .= '}' . "\n\n";
				}

				// If we have a content color selected by the user
				if ( ( $theme_mod_content_color = $this->get_theme_mod( 'content_color', $this->theme_mod_content_color() ) ) ) {
					$r .= '/* Content Color */' . "\n";
					$r .= 'body, .page-numbers li a, h1, h2, h3, h4, h5, h6,' . "\n";
					$r .= 'blockquote, cite, th, .article-title, .article-title a,' . "\n";
					$r .= '.article-post-navigation a, .comments .author-link, .comments .author-link a, .comments-navigation a,' . "\n";
					$r .= '.conductor-widget .article-title a {' . "\n";
						$r .= 'color: ' . $theme_mod_content_color . ';' . "\n";
					$r .= '}' . "\n\n";

					$r .= '/* Secondary Nav Button */' . "\n";
					$r .= '#secondary-nav-button {' . "\n";
						$r .= 'color: ' . $theme_mod_content_color . ';' . "\n";
					$r .= '}' . "\n\n";

					$r .= '/* Secondary Nav Button Hover/Open */' . "\n";
					$r .= '#secondary-nav-button.open, #secondary-nav-button:hover {' . "\n";
						$r .= 'background: ' . $theme_mod_content_color . ';' . "\n";
					$r .= '}' . "\n\n";

					$r .= '/* Page Numbers */' . "\n";
					$r .= '.page-numbers li .current {' . "\n";
						$r .= 'background: ' . $theme_mod_content_color . ';' . "\n";
					$r .= '}' . "\n\n";
				}

				// If we have a content link color selected by the user
				if ( ( $theme_mod_link_color = $this->get_theme_mod( 'link_color', $this->theme_mod_link_color() ) ) ) {
					$r .= '/* Content Link Color */' . "\n";
					$r .= 'a, .article-title a:hover,' . "\n";
					$r .= '.article-post-meta .article-date a:hover, .article-post-meta .article-author-link a:hover, .article-post-meta .article-comments-link a:hover,' . "\n";
					$r .= '.comments .comment-reply-link:hover,' . "\n";
					$r .= '.widget_tag_cloud .tagcloud a, .conductor-widget .article-title a:hover {' . "\n";
						$r .= 'color: ' . $theme_mod_link_color . ';' . "\n";
					$r .= '}' . "\n\n";

					$r .= '/* Baton Features 1 Note Widget Heading Border Color */' . "\n";
					$r .= '.baton-features-widget .baton-features-1 .note-col .note-content h1:after, .baton-features-widget .baton-features-1 .note-col .note-content h2:after, .baton-features-widget .baton-features-1 .note-col .note-content h3:after,' . "\n";
					$r .= '.baton-features-widget .baton-features-1 .note-col .note-content h4:after, .baton-features-widget .baton-features-1 .note-col .note-content h5:after, .baton-features-widget .baton-features-1 .note-col .note-content h6:after {' . "\n";
						$r .= 'border-bottom-color: ' . $theme_mod_link_color . ';' . "\n";
					$r .= '}' . "\n\n";

					$r .= '/* Baton Features 2 Note Widget Odd Column Background Color */' . "\n";
					$r .= '.baton-features-widget .baton-features-2 .note-col-odd, .baton-features-widget .baton-features-2 .note-row-even .note-col-even {' . "\n";
						$r .= 'background: ' . $theme_mod_link_color . ';' . "\n";
					$r .= '}' . "\n\n";

					$r .= '/* Conductor Slider Widgets (Front Page Sidebar) */' . "\n";
					$r .= '.front-page-widgets .conductor-widget.conductor-slider-testimonials-wrap,' . "\n";
					$r .= '.front-page-widgets .conductor-widget.conductor-slider-hero-wrap,' . "\n";
					$r .= '.front-page-widgets .conductor-widget.conductor-slider-news-wrap {' . "\n";
						$r .= 'background: ' . $theme_mod_link_color . ';' . "\n";
					$r .= '}' . "\n\n";

					$r .= '/* Conductor Slider Widgets (Baton Landing Page - Conductor) */' . "\n";
					$r .= '.conductor.conductor-baton-landing-page-active .content-conductor-container .conductor-widget.conductor-slider-testimonials-wrap,' . "\n";
					$r .= '.conductor.conductor-baton-landing-page-active .content-conductor-container .conductor-widget.conductor-slider-hero-wrap,' . "\n";
					$r .= '.conductor.conductor-baton-landing-page-active .content-conductor-container .conductor-widget.conductor-slider-news-wrap {' . "\n";
					$r .= 'background: ' . $theme_mod_link_color . ';' . "\n";
					$r .= '}' . "\n\n";

					$r .= '/* Baton Note Sidebars - Conductor Slider Widgets */' . "\n";
					$r .= '.baton-note-sidebar .conductor-widget.conductor-slider-testimonials-wrap,' . "\n";
					$r .= '.baton-note-sidebar .conductor-widget.conductor-slider-hero-wrap,' . "\n";
					$r .= '.baton-note-sidebar .conductor-widget.conductor-slider-news-wrap {' . "\n";
						$r .= 'background: ' . $theme_mod_link_color . ';' . "\n";
					$r .= '}' . "\n\n";

					$r .= '/* Gravity Forms - mc-gravity, mc_gravity, mc-newsletter, mc_newsletter */' . "\n";
					$r .= 'body .mc-gravity .gform_heading, body .mc_gravity .gform_heading, body .mc-newsletter .gform_heading, body .mc_newsletter .gform_heading,' . "\n";
					$r .= 'body .mc-gravity_wrapper .gform_heading, body .mc_gravity_wrapper .gform_heading, body .mc-newsletter_wrapper .gform_heading, body .mc_newsletter_wrapper .gform_heading,' . "\n";
					$r .= 'body .mc-gravity-confirmation, body .mc_gravity-confirmation, body .mc-newsletter-confirmation, body .mc_newsletter-confirmation {' . "\n";
						$r .= 'background: ' . $theme_mod_link_color . ';' . "\n";
					$r .= '}' . "\n\n";
				}

				// If we have a post title color selected by the user
				if ( ( $theme_mod_baton_post_title_color = $this->get_theme_mod( 'baton_post_title_color', $this->theme_mod_baton_post_title_color() ) ) ) {
					$r .= '/* Post Title Color */' . "\n";
					$r .= '.article-title, .article-title a, .conductor-widget .article-title a {' . "\n";
						$r .= 'color: ' . $theme_mod_baton_post_title_color . ';' . "\n";
					$r .= '}' . "\n\n";
				}

				// If we have an archive title color selected by the user
				if ( ( $theme_mod_baton_archive_title_color = $this->get_theme_mod( 'baton_archive_title_color', $this->theme_mod_baton_archive_title_color() ) ) ) {
					$r .= '/* Archive Title Color */' . "\n";
					$r .= '.archive-title .page-title {' . "\n";
						$r .= 'color: ' . $theme_mod_baton_archive_title_color . ';' . "\n";
					$r .= '}' . "\n\n";
				}

				// If we have a button text color selected by the user
				if ( ( $theme_mod_baton_button_text_color = $this->get_theme_mod( 'baton_button_text_color', $this->theme_mod_baton_button_text_color() ) ) ) {
					$r .= '/* Button Text Color */' . "\n";
					$r .= '.button, a.button, .widget a.button, input[type="submit"], #primary-nav-button.open, #primary-nav-button:hover {' . "\n";
						$r .= 'color: ' . $theme_mod_baton_button_text_color . ';' . "\n";
					$r .= '}' . "\n\n";

					$r .= '/* Button Alternate Background Color */' . "\n";
					$r .= '.button-alt, a.button-alt, .widget a.button-alt {' . "\n";
						$r .= 'background: ' . $theme_mod_baton_button_text_color . ';' . "\n";
					$r .= '}' . "\n\n";

					$r .= '/* Button Alternate Hover Text Color */' . "\n";
					$r .= '.button-alt:hover, a.button-alt:hover, .widget a.button-alt:hover {' . "\n";
						$r .= 'color: ' . $theme_mod_baton_button_text_color . ';' . "\n";
					$r .= '}' . "\n\n";

					// If a hover text color hasn't been set, use the default value to ensure it still works properly
					if ( ! $this->get_theme_mod( 'baton_button_hover_text_color', $this->theme_mod_baton_button_hover_text_color() ) ) {
						$r .= '/* Button Hover Text Color */' . "\n";
						$r .= '.button:hover, a.button:hover, .widget a.button:hover, input[type="submit"]:hover, .page-numbers li a:hover {' . "\n";
							$r .= 'color: ' . $this->theme_mod_baton_button_hover_text_color() . ';' . "\n";
						$r .= '}' . "\n\n";
					}
				}

				// If we have a button background color selected by the user
				if ( ( $theme_mod_baton_button_background_color = $this->get_theme_mod( 'baton_button_background_color', $this->theme_mod_baton_button_background_color() ) ) ) {
					$r .= '/* Button Background Color */' . "\n";
					$r .= '.button, a.button, .widget a.button, input[type="submit"] {' . "\n";
						$r .= 'background: ' . $theme_mod_baton_button_background_color . ';' . "\n";
					$r .= '}' . "\n\n";

					$r .= '/* Primary Navigation Button Background Color */' . "\n";
					$r .= '#primary-nav-button.open, #primary-nav-button:hover {' . "\n";
						$r .= 'background: ' . baton_get_color_variant( $theme_mod_baton_button_background_color, -40 ) . ';' . "\n";
					$r .= '}' . "\n\n";

					$r .= '/* Button Alternate Text Color */' . "\n";
					$r .= '.button-alt, a.button-alt, .widget a.button-alt {' . "\n";
						$r .= 'color: ' . $theme_mod_baton_button_background_color . ';' . "\n";
					$r .= '}' . "\n\n";

					$r .= '/* Button Alternate Hover Background Color */' . "\n";
					$r .= '.button-alt:hover, a.button-alt:hover, .widget a.button-alt:hover {' . "\n";
						$r .= 'background: ' . $theme_mod_baton_button_background_color . ';' . "\n";
					$r .= '}' . "\n\n";
				}

				// If we have a button hover text color selected by the user
				if ( ( $theme_mod_baton_button_hover_text_color = $this->get_theme_mod( 'baton_button_hover_text_color', $this->theme_mod_baton_button_hover_text_color() ) ) ) {
					$r .= '/* Button Hover Text Color */' . "\n";
					$r .= '.button:hover, a.button:hover, .widget a.button:hover, input[type="submit"]:hover, .page-numbers li a:hover {' . "\n";
						$r .= 'color: ' . $theme_mod_baton_button_hover_text_color . ';' . "\n";
					$r .= '}' . "\n\n";

					// If a text color hasn't been set, use the default value to ensure it still works properly
					if ( ! $this->get_theme_mod( 'baton_button_hover_text_color', $this->theme_mod_baton_button_hover_text_color() ) ) {
						$r .= '/* Primary Navigation Button Hover Text Color */' . "\n";
						$r .= '#primary-nav-button.open, #primary-nav-button:hover {' . "\n";
							$r .= 'color: ' . $this->theme_mod_baton_button_hover_text_color() . ';' . "\n";
						$r .= '}' . "\n\n";
					}
					// Otherwise, use the color that is set
					else {
						$r .= '/* Primary Navigation Button Hover Text Color */' . "\n";
						$r .= '#primary-nav-button.open, #primary-nav-button:hover {' . "\n";
							$r .= 'color: ' . $theme_mod_baton_button_text_color . ';' . "\n"; // $theme_mod_baton_button_text_color is set above in a conditional
						$r .= '}' . "\n\n";
					}
				}

				// If we have a button background color selected by the user
				if ( ( $theme_mod_baton_button_hover_background_color = $this->get_theme_mod( 'baton_button_hover_background_color', $this->theme_mod_baton_button_hover_background_color() ) ) ) {
					$r .= '/* Button Hover Background Color */' . "\n";
					$r .= '.button:hover, a.button:hover, .widget a.button:hover, input[type="submit"]:hover, .page-numbers li a:hover {' . "\n";
						$r .= 'background: ' . $theme_mod_baton_button_hover_background_color . ';' . "\n";
					$r .= '}' . "\n\n";
				}

				// If we have a content background color or image selected by the user
				if ( $baton_content_background_css = $this->get_background_image_css( 'content' ) ) {
					// Hash the hex color code
					$baton_content_background_color = $this->get_theme_mod( 'baton_content_background_color', $this->theme_mod_baton_content_background_color() );
					$baton_content_background_color = ( function_exists( 'maybe_hash_hex_color' ) ) ? maybe_hash_hex_color( $baton_content_background_color ) : $this->maybe_hash_hex_color( $baton_content_background_color );

					$r .= '/* Content Background Image & Color */' . "\n";
					$r .= '.content, .article-author, .after-posts-widgets .widget, .comments, .sidebar-container, .page-numbers li a, .conductor-default-content {' . "\n";
						$r .= $baton_content_background_css . "\n";
					$r .= '}' . "\n\n";

					// If we have a color selected
					if ( $baton_content_background_color ) {
						$r .= '/* Secondary Nav Button */' . "\n";
						$r .= '#secondary-nav-button, .home .article-post-meta, .blog .article-post-meta, .search .article-post-meta, .archive .article-post-meta,' . "\n";
						$r .= '.conductor-widget .article-post-meta {' . "\n";
							$r .= 'background: ' . $baton_content_background_color . ';' . "\n";
						$r .= '}' . "\n\n";

						$r .= '/* Secondary Nav Button Hover/Open */' . "\n";
						$r .= '#secondary-nav-button.open, #secondary-nav-button:hover {' . "\n";
							$r .= 'color: ' . $baton_content_background_color . ';' . "\n";
						$r .= '}' . "\n\n";

						$r .= '/* Gravity Forms - mc-gravity, mc_gravity, mc-newsletter, mc_newsletter */' . "\n";
						$r .= 'body .mc-gravity, body .mc_gravity, body .mc-newsletter, body .mc_newsletter, body .mc-gravity_wrapper, body .mc_gravity_wrapper, body .mc-newsletter_wrapper, body .mc_newsletter_wrapper {' . "\n";
							$r .= 'background: ' . $baton_content_background_color . ';' . "\n";
						$r .= '}' . "\n\n";
					}
				}

				// If we have a widget title color selected by the user
				if ( ( $theme_mod_baton_widget_title_color = $this->get_theme_mod( 'baton_widget_title_color', $this->theme_mod_baton_widget_title_color() ) ) ) {
					$r .= '/* Widget Title Color */' . "\n";
					$r .= '.widget-title, #footer .widget-title {' . "\n";
						$r .= 'color: ' . $theme_mod_baton_widget_title_color . ';' . "\n";
					$r .= '}' . "\n\n";
				}

				// If we have a widget text color selected by the user
				if ( ( $theme_mod_baton_widget_color = $this->get_theme_mod( 'baton_widget_color', $this->theme_mod_baton_widget_color() ) ) ) {
					$r .= '/* Widget Text Color */' . "\n";
					$r .= '.widget, #footer .widget {' . "\n";
						$r .= 'color: ' . $theme_mod_baton_widget_color . ';' . "\n";
					$r .= '}' . "\n\n";
				}

				// If we have a widget link color selected by the user
				if ( ( $theme_mod_baton_widget_link_color = $this->get_theme_mod( 'baton_widget_link_color', $this->theme_mod_baton_widget_link_color() ) ) ) {
					$r .= '/* Widget Link Color */' . "\n";
					$r .= '.widget a, #footer .widget a {' . "\n";
						$r .= 'color: ' . $theme_mod_baton_widget_link_color . ';' . "\n";
					$r .= '}' . "\n\n";
				}

				// If we have a site title selected by the user
				if ( ( $theme_mod_baton_site_title_color = $this->get_theme_mod( 'baton_site_title_color', $this->theme_mod_baton_site_title_color() ) ) ) {
					$r .= '/* Site Title Color */' . "\n";
					$r .= '#title a {' . "\n";
						$r .= 'color: ' . $theme_mod_baton_site_title_color . ';' . "\n";
					$r .= '}' . "\n\n";

					$r .= '/* Conductor Slider Widgets (Front Page Sidebar) */' . "\n";
					$r .= '.front-page-widgets .conductor-widget.conductor-slider-testimonials-wrap .widget-title,' . "\n";
					$r .= '.front-page-widgets .conductor-widget.conductor-slider-hero-wrap .widget-title,' . "\n";
					$r .= '.front-page-widgets .conductor-widget.conductor-slider-news-wrap .widget-title,' . "\n";
					$r .= '.front-page-widgets .conductor-slider .arrows .arrow {' . "\n";
						$r .= 'color: ' . $theme_mod_baton_site_title_color . ';' . "\n";
					$r .= '}' . "\n\n";

					$r .= '/* Conductor Slider Widgets (Front Page Sidebar) */' . "\n";
					$r .= '.front-page-widgets .conductor-widget .conductor-slider .dots .dot .dot-inner {' . "\n";
						$r .= 'border-color: ' . $theme_mod_baton_site_title_color . ';' . "\n";
					$r .= '}' . "\n\n";

					$r .= '/* Conductor Slider Widgets (Front Page Sidebar) */' . "\n";
					$r .= '.front-page-widgets .conductor-widget .conductor-slider .dots .dot.active .dot-inner {' . "\n";
						$r .= 'background: ' . $theme_mod_baton_site_title_color . ';' . "\n";
					$r .= '}' . "\n\n";

					$r .= '/* Conductor Slider Widgets (Baton Landing Page - Conductor) */' . "\n";
					$r .= '.conductor.conductor-baton-landing-page-active .content-conductor-container .conductor-widget.conductor-slider-testimonials-wrap .widget-title,' . "\n";
					$r .= '.conductor.conductor-baton-landing-page-active .content-conductor-container .conductor-widget.conductor-slider-hero-wrap .widget-title,' . "\n";
					$r .= '.conductor.conductor-baton-landing-page-active .content-conductor-container .conductor-widget.conductor-slider-news-wrap .widget-title,' . "\n";
					$r .= '.conductor.conductor-baton-landing-page-active .content-conductor-container .conductor-slider .arrows .arrow {' . "\n";
						$r .= 'color: ' . $theme_mod_baton_site_title_color . ';' . "\n";
					$r .= '}' . "\n\n";

					$r .= '/* Conductor Slider Widgets (Baton Landing Page - Conductor) */' . "\n";
					$r .= '.conductor.conductor-baton-landing-page-active .content-conductor-container .conductor-widget .conductor-slider .dots .dot .dot-inner {' . "\n";
						$r .= 'border-color: ' . $theme_mod_baton_site_title_color . ';' . "\n";
					$r .= '}' . "\n\n";

					$r .= '/* Conductor Slider Widgets (Baton Landing Page - Conductor) */' . "\n";
					$r .= '.conductor.conductor-baton-landing-page-active .content-conductor-container .conductor-widget .conductor-slider .dots .dot.active .dot-inner {' . "\n";
						$r .= 'background: ' . $theme_mod_baton_site_title_color . ';' . "\n";
					$r .= '}' . "\n\n";

					$r .= '/* Baton Note Sidebars - Conductor Slider Widgets */' . "\n";
					$r .= '.baton-note-sidebar .conductor-widget.conductor-slider-testimonials-wrap .widget-title,' . "\n";
					$r .= '.baton-note-sidebar .conductor-widget.conductor-slider-hero-wrap .widget-title,' . "\n";
					$r .= '.baton-note-sidebar .conductor-widget.conductor-slider-news-wrap .widget-title,' . "\n";
					$r .= '.baton-note-sidebar .conductor-slider .arrows .arrow {' . "\n";
						$r .= 'color: ' . $theme_mod_baton_site_title_color . ';' . "\n";
					$r .= '}' . "\n\n";

					$r .= '/* Baton Note Sidebars - Conductor Slider Widgets */' . "\n";
					$r .= '.baton-note-sidebar .conductor-widget .conductor-slider .dots .dot .dot-inner {' . "\n";
						$r .= 'border-color: ' . $theme_mod_baton_site_title_color . ';' . "\n";
					$r .= '}' . "\n\n";

					$r .= '/* Baton Note Sidebars - Conductor Slider Widgets */' . "\n";
					$r .= '.baton-note-sidebar .conductor-widget .conductor-slider .dots .dot.active .dot-inner {' . "\n";
						$r .= 'background: ' . $theme_mod_baton_site_title_color . ';' . "\n";
					$r .= '}' . "\n\n";
				}

				// If we have a tagline color selected by the user
				if ( ( $theme_mod_baton_tagline_color = $this->get_theme_mod( 'baton_tagline_color', $this->theme_mod_baton_tagline_color() ) ) ) {
					$r .= '/* Tagline Color */' . "\n";
					$r .= '#slogan {' . "\n";
						$r .= 'color: ' . $theme_mod_baton_tagline_color .';' . "\n";
					$r .= '}' . "\n\n";
				}

				// If we have a primary navigation hover/active color selected by the user
				if ( ( $theme_mod_baton_primary_hover_active_color = $this->get_theme_mod( 'baton_primary_hover_active_color', $this->theme_mod_baton_primary_hover_active_color() ) ) ) {
					$r .= '/* Primary Hover/Active Color */' . "\n";
					$r .= 'nav .primary-nav li:hover > a, nav .primary-nav li.current-menu-item > a, nav .primary-nav li.current_page_item > a,' . "\n";
					$r .= 'nav .primary-nav .sub-menu li:hover > a, nav .primary-nav .children li:hover > a,' . "\n";
					$r .= 'nav .primary-nav .sub-menu li.current-menu-item > a, nav .primary-nav .children li.current-menu-item > a,' . "\n";
					$r .= 'nav .primary-nav .sub-menu li.current_page_item > a, nav .primary-nav .children li.current_page_item > a {' . "\n";
						$r .= 'color: ' . $theme_mod_baton_primary_hover_active_color . ';' . "\n";
					$r .= '}' . "\n\n";
				}

				// If we have a primary navigation sub menu color selected by the user
				if ( ( $theme_mod_baton_primary_sub_menu_color = $this->get_theme_mod( 'baton_primary_sub_menu_color', $this->theme_mod_baton_primary_sub_menu_color() ) ) ) {
					$r .= '/* Primary Navigation Sub Menu Color */' . "\n";
					$r .= 'nav .primary-nav .sub-menu li a, nav .primary-nav .children li a {' . "\n";
						$r .= 'color: ' . $theme_mod_baton_primary_sub_menu_color . ';' . "\n";
					$r .= '}' . "\n\n";

					// Media Queries
					$r .= '@media only screen and (max-width: 768px) {' . "\n";
						$r .= 'nav .primary-nav .sub-menu .child-menu-button, nav .primary-nav .children .child-menu-button {' . "\n";
							$r .= 'color: ' . $theme_mod_baton_primary_sub_menu_color . ';' . "\n";
						$r .= '}' . "\n";
					$r .= '}' . "\n\n";
				}

				// If we have a primary navigation sub menu hover color selected by the user
				if ( ( $theme_mod_baton_primary_sub_menu_hover_color = $this->get_theme_mod( 'baton_primary_sub_menu_hover_color', $this->theme_mod_baton_primary_sub_menu_hover_color() ) ) ) {
					$r .= '/* Primary Navigation Sub Menu Hover Color */' . "\n";
					$r .= 'nav .primary-nav .sub-menu li:hover > a, nav .primary-nav .children li:hover > a {' . "\n";
						$r .= 'color: ' . $theme_mod_baton_primary_sub_menu_hover_color . ';' . "\n";
					$r .= '}' . "\n\n";
				}

				// If we have a primary navigation sub menu background color selected by the user
				if ( ( $theme_mod_baton_primary_sub_menu_background_color = $this->get_theme_mod( 'baton_primary_sub_menu_background_color', $this->theme_mod_baton_primary_sub_menu_background_color() ) ) ) {
					$r .= '/* Primary Navigation Sub Menu Background Color */' . "\n";
					$r .= 'nav .primary-nav .sub-menu li, nav .primary-nav .sub-menu li:first-child, nav .primary-nav .children li, nav .primary-nav .children li:first-child {' . "\n";
						$r .= 'background: ' . $theme_mod_baton_primary_sub_menu_background_color . ';' . "\n";
					$r .= '}' . "\n\n";

					// Media Queries
					$r .= '@media only screen and (max-width: 768px) {' . "\n";
						$r .= 'nav .primary-nav li {' . "\n";
							$r .= 'background: ' . $theme_mod_baton_primary_sub_menu_background_color . ';' . "\n";
						$r .= '}' . "\n\n";

						$r .= 'nav .primary-nav li {' . "\n";
							$r .= 'border-left-color: ' . baton_get_color_variant( $theme_mod_baton_primary_sub_menu_background_color, 80 ) . ';' . "\n";
							$r .= 'border-right-color: ' . baton_get_color_variant( $theme_mod_baton_primary_sub_menu_background_color, 80 ) . ';' . "\n";
						$r .= '}' . "\n\n";

						$r .= 'nav .primary-nav > li:first-child {' . "\n";
							$r .= 'border-top-color: ' . baton_get_color_variant( $theme_mod_baton_primary_sub_menu_background_color, 80 ) . ';' . "\n";
						$r .= '}' . "\n\n";

						$r .= 'nav .primary-nav > li:last-child {' . "\n";
							$r .= 'border-bottom-color: ' . baton_get_color_variant( $theme_mod_baton_primary_sub_menu_background_color, 80 ) . ';' . "\n";
						$r .= '}' . "\n\n";

						$r .= 'nav .primary-nav .sub-menu li, nav .primary-nav .sub-menu li:first-child,';
						$r .= 'nav .primary-nav .children li, nav .primary-nav .children li:first-child {' . "\n";
							$r .= 'background: ' . baton_get_color_variant( $theme_mod_baton_primary_sub_menu_background_color, -20 ) . ';' . "\n";
						$r .= '}' . "\n\n";

						$r .= 'nav .primary-nav .sub-menu .sub-menu li, nav .primary-nav .children .children li {' . "\n";
							$r .= 'background: ' . baton_get_color_variant( $theme_mod_baton_primary_sub_menu_background_color, -40 ) . ';' . "\n";
						$r .= '}' . "\n\n";

						$r .= 'nav .primary-nav .sub-menu .sub-menu .sub-menu li, nav .primary-nav .children .children .children li {' . "\n";
							$r .= 'background: ' . baton_get_color_variant( $theme_mod_baton_primary_sub_menu_background_color, -60 ) . ';' . "\n";
						$r .= '}' . "\n\n";
					$r .= '}' . "\n\n";

					$r .= 'nav .primary-nav .sub-menu li, nav .primary-nav .children li {' . "\n";
						$r .= 'border-color: ' . baton_get_color_variant( $theme_mod_baton_primary_sub_menu_background_color, 80 ) . ';' . "\n";
					$r .= '}' . "\n\n";

					$r .= 'nav .primary-nav .sub-menu li:first-child, nav .primary-nav .children li:first-child, nav .primary-nav > li:first-child {' . "\n";
						$r .= 'border-top-color: ' . baton_get_color_variant( $theme_mod_baton_primary_sub_menu_background_color, 80 ) . ';' . "\n";
					$r .= '}' . "\n\n";

					$r .= 'nav .primary-nav .sub-menu:before, nav .primary-nav .children:before {' . "\n";
						$r .= 'border-bottom-color: ' . $theme_mod_baton_primary_sub_menu_background_color . ';' . "\n";
					$r .= '}' . "\n\n";

					$r .= 'nav .primary-nav .sub-menu:after, nav .primary-nav .children:after, nav .primary-nav li a {' . "\n";
						$r .= 'border-bottom-color: ' . baton_get_color_variant( $theme_mod_baton_primary_sub_menu_background_color, 80 ) . ';' . "\n";
					$r .= '}' . "\n\n";

					$r .= 'nav .primary-nav .sub-menu .sub-menu:before, nav .primary-nav .children .children:before {' . "\n";
						$r .= 'border-right-color: ' . $theme_mod_baton_primary_sub_menu_background_color . ';' . "\n";
					$r .= '}' . "\n\n";

					$r .= 'nav .primary-nav .sub-menu .sub-menu:after, nav .primary-nav .children .children:after {' . "\n";
						$r .= 'border-right-color: ' . baton_get_color_variant( $theme_mod_baton_primary_sub_menu_background_color, 80 ) . ';' . "\n";
					$r .= '}' . "\n\n";
				}

				// If we have a header background color or image selected by the user
				if ( ( $header_background_css = $this->get_background_image_css( 'header' ) ) ) {
					$r .= '/* Header Background Image & Color */' . "\n";
					$r .= '#header {' . "\n";
						$r .= $header_background_css . "\n";
					$r .= '}' . "\n\n";

					// Background color only
					if ( ( $header_background_color = $this->get_theme_mod( 'baton_header_background_color', $this->theme_mod_baton_header_background_color() ) ) ) {
						$r .= '/* Baton Features 2 Note Widget Even Column Background Color */' . "\n";
						$r .= '.baton-features-widget .baton-features-2 .note-col-even, .baton-features-widget .baton-features-2 .note-row-even .note-col-odd {' . "\n";
							$r .= 'background: ' . $header_background_color . ';' . "\n";
						$r .= '}' . "\n\n";

						// Media Queries
						$r .= '@media only screen and (max-width: 768px) {' . "\n";
							$r .= 'nav .primary-nav li {' . "\n";
								$r .= 'background: ' . $header_background_color . ';' . "\n";
							$r .= '}' . "\n";
						$r .= '}' . "\n\n";
					}
				}

				// If we have a secondary navigation hover/active color selected by the user
				if ( ( $theme_mod_baton_secondary_hover_active_color = $this->get_theme_mod( 'baton_secondary_hover_active_color', $this->theme_mod_baton_secondary_hover_active_color() ) ) ) {
					$r .= '/* Secondary Hover/Active Color */' . "\n";
					$r .= '#secondary-nav li:hover > a, #secondary-nav li.current-menu-item > a,' . "\n";
					$r .= '#secondary-nav .sub-menu li:hover > a, #secondary-nav .sub-menu li.current-menu-item > a {' . "\n";
						$r .= 'color: ' . $theme_mod_baton_secondary_hover_active_color . ';' . "\n";
					$r .= '}' . "\n\n";
				}

				// If we have a secondary navigation sub menu color selected by the user
				if ( ( $theme_mod_baton_secondary_header_sub_menu_color = $this->get_theme_mod( 'baton_secondary_header_sub_menu_color', $this->theme_mod_baton_secondary_header_sub_menu_color() ) ) ) {
					$r .= '/* Secondary Navigation Sub Menu Color */' . "\n";
					$r .= '#secondary-nav .sub-menu li a, #secondary-nav .sub-menu .child-menu-button {' . "\n";
						$r .= 'color: ' . $theme_mod_baton_secondary_header_sub_menu_color . ';' . "\n";
					$r .= '}' . "\n\n";
				}

				// If we have a secondary navigation sub menu hover color selected by the user
				if ( ( $theme_mod_baton_secondary_header_sub_menu_hover_color = $this->get_theme_mod( 'baton_secondary_header_sub_menu_hover_color', $this->theme_mod_baton_secondary_header_sub_menu_hover_color() ) ) ) {
					$r .= '/* Secondary Navigation Sub Menu Hover Color */' . "\n";
					$r .= '#secondary-nav .sub-menu li:hover > a {' . "\n";
						$r .= 'color: ' . $theme_mod_baton_secondary_header_sub_menu_hover_color . ';' . "\n";
					$r .= '}' . "\n\n";
				}

				// If we have a secondary navigation sub menu background color selected by the user
				if ( ( $theme_mod_baton_secondary_header_sub_menu_background_color = $this->get_theme_mod( 'baton_secondary_header_sub_menu_background_color', $this->theme_mod_baton_secondary_header_sub_menu_background_color() ) ) ) {
					$r .= '/* Secondary Navigation Sub Menu Background Color */' . "\n";
					$r .= '#secondary-nav .sub-menu li, #secondary-nav .sub-menu li:first-child {' . "\n";
						$r .= 'background: ' . $theme_mod_baton_secondary_header_sub_menu_background_color . ';' . "\n";
					$r .= '}' . "\n\n";

					// Media Queries
					$r .= '@media only screen and (max-width: 768px) {' . "\n";
						$r .= '#secondary-nav li {' . "\n";
							$r .= 'background: ' . $theme_mod_baton_secondary_header_sub_menu_background_color . ';' . "\n";
						$r .= '}' . "\n\n";

						$r .= '#secondary-nav li {' . "\n";
							$r .= 'border-left-color: ' . baton_get_color_variant( $theme_mod_baton_secondary_header_sub_menu_background_color, 80 ) . ';' . "\n";
							$r .= 'border-right-color: ' . baton_get_color_variant( $theme_mod_baton_secondary_header_sub_menu_background_color, 80 ) . ';' . "\n";
						$r .= '}' . "\n\n";

						$r .= '#secondary-nav > li:first-child {' . "\n";
							$r .= 'border-top-color: ' . baton_get_color_variant( $theme_mod_baton_secondary_header_sub_menu_background_color, 80 ) . ';' . "\n";
						$r .= '}' . "\n\n";

						$r .= '#secondary-nav > li:last-child, #secondary-nav li a {' . "\n";
							$r .= 'border-bottom-color: ' . baton_get_color_variant( $theme_mod_baton_secondary_header_sub_menu_background_color, 80 ) . ';' . "\n";
						$r .= '}' . "\n\n";

						$r .= '#secondary-nav .sub-menu li, #secondary-nav .sub-menu li:first-child {' . "\n";
							$r .= 'background: ' . baton_get_color_variant( $theme_mod_baton_secondary_header_sub_menu_background_color, -20 ) . ';' . "\n";
						$r .= '}' . "\n\n";

						$r .= '#secondary-nav .sub-menu .sub-menu li {' . "\n";
							$r .= 'background: ' . baton_get_color_variant( $theme_mod_baton_secondary_header_sub_menu_background_color, -40 ) . ';' . "\n";
						$r .= '}' . "\n\n";

						$r .= '#secondary-nav .sub-menu .sub-menu .sub-menu li {' . "\n";
							$r .= 'background: ' . baton_get_color_variant( $theme_mod_baton_secondary_header_sub_menu_background_color, -60 ) . ';' . "\n";
						$r .= '}' . "\n\n";
					$r .= '}' . "\n\n";

					$r .= '#secondary-nav .sub-menu li {' . "\n";
						$r .= 'border-color: ' . baton_get_color_variant( $theme_mod_baton_secondary_header_sub_menu_background_color, 80 ) . ';' . "\n";
					$r .= '}' . "\n\n";

					$r .= '#secondary-nav .sub-menu li:first-child, #secondary-nav > li:first-child {' . "\n";
						$r .= 'border-top-color: ' . baton_get_color_variant( $theme_mod_baton_secondary_header_sub_menu_background_color, 80 ) . ';' . "\n";
					$r .= '}' . "\n\n";

					$r .= '#secondary-nav .sub-menu:before {' . "\n";
						$r .= 'border-bottom-color: ' . $theme_mod_baton_secondary_header_sub_menu_background_color . ';' . "\n";
					$r .= '}' . "\n\n";

					$r .= '#secondary-nav .sub-menu .sub-menu:before {' . "\n";
						$r .= 'border-right-color: ' . $theme_mod_baton_secondary_header_sub_menu_background_color . ';' . "\n";
					$r .= '}' . "\n\n";
				}

				// If we have a secondary header background color or image selected by the user
				if ( ( $baton_secondary_header_background_css = $this->get_background_image_css( 'secondary_header' ) ) ) {
					$r .= '/* Secondary Header Background Image & Color */' . "\n";
					$r .= '#secondary-nav-wrap {' . "\n";
						$r .= $baton_secondary_header_background_css . "\n";
					$r .= '}' . "\n\n";

					// Background color only
					if ( ( $baton_secondary_background_color = $this->get_theme_mod( 'baton_secondary_header_background_color', $this->theme_mod_baton_secondary_header_background_color() ) ) ) {
						// Media Queries
						$r .= '@media only screen and (max-width: 768px) {' . "\n";
							$r .= '#secondary-nav li {' . "\n";
								$r .= 'background: ' . $baton_secondary_background_color . ';' . "\n";
							$r .= '}' . "\n";
						$r .= '}' . "\n\n";
					}
				}

				// If we have a footer text color selected by the user
				if ( ( $theme_mod_baton_footer_text_color = $this->get_theme_mod( 'baton_footer_text_color', $this->theme_mod_baton_footer_text_color() ) ) ) {
					$r .= '/* Footer Text Color */' . "\n";
					$r .= '#footer, #footer .widget {' . "\n";
						$r .= 'color: ' . $theme_mod_baton_footer_text_color . ';' . "\n";
					$r .= '}' . "\n\n";
				}

				// If we have a footer link color selected by the user
				if ( ( $theme_mod_baton_footer_link_color = $this->get_theme_mod( 'baton_footer_link_color', $this->theme_mod_baton_footer_link_color() ) ) ) {
					$r .= '/* Footer Link Color */' . "\n";
					$r .= '#footer a, #footer .widget a {' . "\n";
						$r .= 'color: ' . $theme_mod_baton_footer_link_color . ';' . "\n";
					$r .= '}' . "\n\n";
				}

				// If we have a footer heading color selected by the user
				if ( ( $theme_mod_baton_footer_heading_color = $this->get_theme_mod( 'baton_footer_heading_color', $this->theme_mod_baton_footer_heading_color() ) ) ) {
					$r .= '/* Footer Heading Color */' . "\n";
					$r .= '#footer h1, #footer h2, #footer h3, #footer h4, #footer h5, #footer h6, #footer .widget-title {' . "\n";
						$r .= 'color: ' . $theme_mod_baton_footer_heading_color . ';' . "\n";
					$r .= '}' . "\n\n";
				}

				// If we have a header background color or image selected by the user
				if ( ( $footer_background_css = $this->get_background_image_css( 'footer' ) ) ) {
					$r .= '/* Footer Background Image & Color */' . "\n";
					$r .= 'footer#footer {' . "\n";
						$r .= $footer_background_css . "\n";
					$r .= '}' . "\n\n";
				}

				// Close </style>
				$r .= '</style>';

				return $r;
			}
		}

		/**
		 * This function outputs CSS for Customizer settings.
		 */
		public function wp_head() {
			// Get Customizer CSS
			echo $this->get_customizer_css();
		}


		/**********************
		 * Internal Functions *
		 **********************/

		/**
		 * This function returns a boolean result comparing WordPress versions.
		 *
		 * @return Boolean
		 */
		public function version_compare( $version, $operator = '>=' ) {
			global $wp_version;

			return version_compare( $wp_version, $version, $operator );
		}

		/**
		 * This function returns a theme mod but first checks to see if it is the default, and if so
		 * no value is returned. This is to prevent unnecessary CSS output in wp_head().
		 */
		public function get_theme_mod( $theme_mod_name, $default = false, $format_function = false, $default_format_function = false ) {
			$theme_mod = get_theme_mod( $theme_mod_name );

			// Should we format the value
			if ( $format_function )
				// Switch based on format function
				switch ( $format_function ) {
					// ltrim_hash (remove the hash symbol)
					case 'ltrim_hash':
						$theme_mod = ltrim( $theme_mod, '#' );
					break;
				}

			// Should we format the default value
			if ( $default_format_function )
				// Switch based on format function
				switch ( $default_format_function ) {
					// ltrim_hash (remove the hash symbol)
					case 'ltrim_hash':
						$default = ltrim( $default, '#' );
					break;
				}

			// Check this theme mod against the default
			if ( $theme_mod === $default )
				$theme_mod = false;

			return $theme_mod;
		}

		/**
		 * This function returns the current color scheme default and returns the $fallback as a fallback.
		 */
		public function get_current_color_scheme_default( $property, $fallback = false ) {
			// Set the default value to the fallback initially
			$default = $fallback;

			// Grab the color scheme default value if it exists
			if ( ! empty( $this->sds_color_scheme ) && isset( $this->sds_color_scheme[$property] ) && ! empty( $this->sds_color_scheme[$property] ) )
				$default = $this->sds_color_scheme[$property];

			return $default;
		}

		/**
		 * This function returns background image CSS properties based on the theme mod parameter.
		 *
		 * Copyright: WordPress Core (3.0), http://wordpress.org/
		 *
		 * We've used WordPress' function as a base and modified it to suit our needs.
		 */
		public function get_background_image_css( $theme_mod_area = 'default' ) {
			// Just get the default background CSS
			if ( $theme_mod_area === 'default' ) {
				// $background is the saved custom image, or the default image.
				$background = set_url_scheme( get_background_image() );

				// $color is the saved custom color.
				// A default has to be specified in style.css. It will not be printed here.
				$color = '#' . get_background_color();

				if ( $color === get_theme_support( 'custom-background', 'default-color' ) )
					$color = false;

				if ( ! $background && ! $color )
					return false;

				$style = $color ? "background-color: #$color;" : '';

				if ( $background ) {
					$image = " background-image: url('$background');";

					$repeat = get_theme_mod( 'background_repeat', get_theme_support( 'custom-background', 'default-repeat' ) );
					if ( ! in_array( $repeat, array( 'no-repeat', 'repeat-x', 'repeat-y', 'repeat' ) ) )
						$repeat = 'repeat';
					$repeat = " background-repeat: $repeat;";

					$position = get_theme_mod( 'background_position_x', get_theme_support( 'custom-background', 'default-position-x' ) );
					if ( ! in_array( $position, array( 'center', 'right', 'left' ) ) )
						$position = 'left';
					$position = " background-position: top $position;";

					$attachment = get_theme_mod( 'background_attachment', get_theme_support( 'custom-background', 'default-attachment' ) );
					if ( ! in_array( $attachment, array( 'fixed', 'scroll' ) ) )
						$attachment = 'scroll';
					$attachment = " background-attachment: $attachment;";

					$style .= $image . $repeat . $position . $attachment;
				}

				return $style;
			}
			// Otherwise get the theme mod area background CSS
			else {
				// $background is the saved custom image, or the default image.
				$background = set_url_scheme( get_theme_mod( 'baton_' . $theme_mod_area . '_background_image' ) );

				// $color is the saved custom color.
				$theme_mod_filter_function = 'theme_mod_baton_' . $theme_mod_area . '_background_color';
				$color = $this->get_theme_mod( 'baton_' . $theme_mod_area . '_background_color', $this->$theme_mod_filter_function() );

				if ( ! $background && ! $color )
					return false;

				$style = $color ? "background-color: $color;" : '';

				if ( $background ) {
					$image = " background-image: url('$background');";

					$repeat = get_theme_mod( 'baton_' . $theme_mod_area . '_background_image_repeat', get_theme_support( 'custom-background', 'default-repeat' ) );
					if ( ! in_array( $repeat, array( 'no-repeat', 'repeat-x', 'repeat-y', 'repeat' ) ) )
						$repeat = 'repeat';
					$repeat = " background-repeat: $repeat;";

					$position = get_theme_mod( 'baton_' . $theme_mod_area . '_background_image_position_x', get_theme_support( 'custom-background', 'default-position-x' ) );
					if ( ! in_array( $position, array( 'center', 'right', 'left' ) ) )
						$position = 'left';
					$position = " background-position: top $position;";

					$attachment = get_theme_mod( 'baton_' . $theme_mod_area . '_background_image_attachment', get_theme_support( 'custom-background', 'default-attachment' ) );
					if ( ! in_array( $attachment, array( 'fixed', 'scroll' ) ) )
						$attachment = 'scroll';
					$attachment = " background-attachment: $attachment;";

					$style .= $image . $repeat . $position . $attachment;
				}

				return $style;
			}
		}

		/**
		 * This function resets transient data to ensure front-end matches Customizer preview.
		 */
		public function reset_transient() {
			// Reset transient data on this class
			$this->transient_data = array();

			// Delete the transient data
			$this->delete_transient();

			// Set the transient data
			$this->set_transient();
		}


		/**
		 * This function gets our transient data. Additionally it calls the set_transient()
		 * method on this class to set and return transient data if the transient data doesn't
		 * currently exist.
		 */
		public function get_transient() {
			// Check for transient data first
			if ( ! $transient_data = get_transient( $this->transient_name ) )
				// Create and return the transient data if it doesn't exist
				$transient_data = $this->set_transient();

			return $transient_data;
		}

		/**
		 * This function stores data in our transient and returns the data.
		 */
		public function set_transient() {
			$baton_theme_helper = Baton_Theme_Helper(); // Grab the Baton_Theme_Helper instance

			$data = array(); // Default

			// Always add the Customizer CSS
			$data['customizer_css'] = $this->get_customizer_css();

			// Always add the theme's version
			$data['version'] = $baton_theme_helper->theme->get( 'Version' );

			// Set the transient
			set_transient( $this->transient_name, $data );

			return $data;
		}

		/**
		 * This function deletes our transient data.
		 */
		public function delete_transient() {
			// Delete the transient
			delete_transient( $this->transient_name );
		}

		/**
		 * This function determines if the site is currently being previewed in the Customizer.
		 */
		public function is_customize_preview() {
			// Less than 4.0
			if ( ! $this->version_compare( '4.0' ) ) {
				global $wp_customize;

				return is_a( $wp_customize, 'WP_Customize_Manager' ) && $wp_customize->is_preview();
			}
			// 4.0 or greater
			else
				return is_customize_preview();
		}

		/**
		 * This function ensures that a color value passed in parameters contains a hash (#)
		 * at the beginning. It is used as a fallback if maybe_hash_hex_color() is not defined at
		 * the time of execution.
		 */
		public function maybe_hash_hex_color( $color ) {
			// If we don't have a hash as the first character, add it
			return ( strpos( $color, '#' ) !== 0 ) ? '#' . $color : $color;
		}
	}


	function Baton_Customizer_Instance() {
		return Baton_Customizer::instance();
	}

	// Starts Baton Customizer
	Baton_Customizer_Instance();
}