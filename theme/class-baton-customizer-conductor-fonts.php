<?php
/**
 * Baton Customizer Conductor (Conductor Customizer functionality)
 */

// Bail if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

if ( ! class_exists( 'Baton_Customizer_Conductor_Fonts' ) ) {
	final class Baton_Customizer_Conductor_Fonts {
		/**
		 * @var string
		 */
		public $version = '1.0.0';

		/**
		 * @var Baton_Customizer_Conductor_Fonts, Instance of the class
		 */
		protected static $_instance;

		/**
		 * @var array, Supported theme mods for Conductor
		 *
		 * List of current font theme mods available for Conductor
		 * TODO: Add Widget Title?
		 */
		public $conductor_theme_mods = array(
			// Element => Font Size and Font Family Support
			'title' => array( 'font_size', 'font_family' ),
			'author_byline' => array( 'font_size', 'font_family' ),
			'content' => array( 'font_size', 'font_family' ),
			'read_more' => array( 'font_size', 'font_family' )
		);

		/**
		 * Function used to create instance of class.
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) )
				self::$_instance = new self();

			return self::$_instance;
		}


		/**
		 * This function sets up all of the actions and filters on instance. It also loads (includes)
		 * the required files and assets.
		 */
		public function __construct() {
			// Load required assets
			//$this->includes();

			// Filter
			$this->conductor_theme_mods = apply_filters( 'baton_customizer_conductor_theme_mods', $this->conductor_theme_mods );

			// Hooks
			add_action( 'customize_register', array( $this, 'customize_register' ), 20 ); // Customizer Register (late)
			//add_action( 'customize_preview_init', array( $this, 'customize_preview_init' ) ); // Customizer Preview Initialization
			//add_action( 'customize_controls_print_footer_scripts', array( $this, 'customize_controls_print_footer_scripts' ) ); // Customizer Footer Scripts
		}

		/**
		 * Include required core files used in the Customizer.
		 */
		private function includes() {
			// Load Baton Customizer Fonts if necessary
			if ( ! function_exists( 'Baton_Customizer_Fonts' ) )
				include_once 'class-baton-customizer-fonts.php'; // Customizer Font Settings/Controls
		}


		/**
		 * This function registers sections and settings for use in the Customizer.
		 */
		// TODO: need to filter for default conductor values
		public function customize_register( $wp_customize ) {
			// Load required assets
			$this->includes();

			// Check version of WordPres
			$baton_customizer = Baton_Customizer_Instance();
			$is_wp_4 = $baton_customizer->version_compare( '4.0' );

			// Grab the Baton_Theme_Helper instance
			$baton_theme_helper = Baton_Theme_Helper();

			// Grab the Baton_Customizer_Fonts instance
			$baton_customizer_fonts = Baton_Customizer_Fonts();

			// Check for Conductor theme support
			if ( $baton_theme_helper->current_theme_supports( 'fonts', 'conductor' ) ) {
				$section_priority = 25; // Starting priority for sections
				$conductor_support = $baton_theme_helper->get_theme_support_value( 'fonts', 'conductor' );
				$conductor_widget_display_support = array_keys( ( array ) $conductor_support );

				// If we have Conductor Widget Display support
				if ( ! empty( $conductor_widget_display_support ) ) {
					// Less than 4.0
					if ( ! $is_wp_4 ) {
						// TODO
					}
					// 4.0 or greater
					else {
						/*
						 * Conductor Content Layouts Panel
						 */
						$conductor_content_layouts_control = $wp_customize->get_section( 'conductor_content_layouts' ); // Get Control
						$conductor_content_layouts_control->priority = 100; // Adjust Priority

						/*
						 * Conductor Typography Panel
						 */
						$wp_customize->add_panel( 'baton_conductor_fonts', array(
							'priority' => 110, // After Conductor Content Layouts
							'title' => __( 'Conductor Typography', 'baton' )
						) );

						/*
						 * Widgets Panel
						 */
						$widgets_panel = $wp_customize->get_panel( 'widgets' ); // Get Control
						if ( ! empty( $widgets_panel ) && property_exists( $widgets_panel, 'priority' ) )
							$widgets_panel->priority = 120; // Adjust Priority
					}

					// Loop through the different Conductor Widget Displays
					foreach ( $conductor_widget_display_support as $display ) {
						// Individual widget display support
						$widget_display_support = $conductor_support[$display];

						// Less than 4.0
						if ( ! $is_wp_4 ) {
							// TODO
						}
						// 4.0 or greater
						else {
							// Default Setting Priority
							$setting_priority = 10;

							// Section
							$wp_customize->add_section( 'baton_fonts_conductor_' . $display, array(
								'priority' => $section_priority,
								'title' => $widget_display_support['labels']['section'],
								'panel' => 'baton_conductor_fonts',
								'description' => __( 'Selecting multiple Google Web Fonts may impact the speed/performance of your website.', 'baton' )
							) );

							// Loop through support
							foreach ( $widget_display_support as $support_id => $support ) {
								// Ignoring the labels
								if ( $support_id !== 'labels' ) {
									// Loop through the different support types
									foreach ( $support as $support_type => $support_value ) {
										// Ignoring the labels
										if ( $support_type !== 'labels' ) {
											// Switch support type
											// TODO: Dynamic priorities for each setting
											switch ( $support_type ) {
												// Font Size
												case 'font_size':
													// Setting
													$wp_customize->add_setting(
														'baton_conductor_' . $display . '_' . $support_id . '_font_size',
														array(
															'default' => apply_filters( 'baton_conductor_' . $display . '_' . $support_id . '_font_size', 24, 24 ), // Pass the default value as second parameter
															'sanitize_callback' => 'absint',
															'sanitize_js_callback' => 'absint'
														)
													);

													// Control
													$wp_customize->add_control(
														new Baton_Customizer_Font_Size_Control(
															$wp_customize,
															'baton_conductor_' . $display . '_' . $support_id . '_font_size',
															array(
																'label' => sprintf( __( '%1$s Font Size', 'baton' ), $support['labels']['control'] ),
																'section' => 'baton_fonts_conductor_' . $display,
																'settings' => 'baton_conductor_' . $display . '_' . $support_id . '_font_size',
																'priority' => $setting_priority,
																'type' => 'number',
																'input_attrs' => array(
																	'min' => apply_filters( 'baton_conductor_' . $display . '_' . $support_id . '_font_size_min', 18, 18 ), // Pass the default value as second parameter
																	'max' => apply_filters( 'baton_conductor_' . $display . '_' . $support_id . '_font_size_max', 36, 36 ), // Pass the default value as second parameter
																	'placeholder' => apply_filters( 'baton_conductor_' . $display . '_' . $support_id . '_font_size', 24, 24 ), // Pass the default value as second parameter
																	'style' => 'width: 70px;'
																),
																'units' => array(
																	'title' => _x( 'pixels', 'title attribute for this Customizer control', 'baton' )
																)
															)
														)
													);
												break;

												// Font Family
												case 'font_family':
													// Setting
													$wp_customize->add_setting(
														'baton_conductor_' . $display . '_' . $support_id . '_font_family',
														array(
															'default' => apply_filters( 'baton_conductor_' . $display . '_' . $support_id . '_font_family', '', '' ), // Pass the default value as second parameter
															'sanitize_callback' => array( $baton_customizer_fonts, 'sanitize_google_web_font' ),
															'sanitize_js_callback' => array( $baton_customizer_fonts, 'sanitize_google_web_font' )
														)
													);

													// Control
													$wp_customize->add_control(
														new Baton_Customizer_Font_Family_Control(
															$wp_customize,
															'baton_conductor_' . $display . '_' . $support_id . '_font_family',
															array(
																'label' => sprintf( __( '%1$s Font Family', 'baton' ), $support['labels']['control'] ),
																'section' => 'baton_fonts_conductor_' . $display,
																'settings' => 'baton_conductor_' . $display . '_' . $support_id . '_font_family',
																'priority' => $setting_priority + 5,
																'type' => 'select',
																'choices' => $baton_customizer_fonts->get_google_fonts_choices()
															)
														)
													);
												break;
											}

											// Increase setting priority
											$setting_priority += 10;
										}
									}
								}
							}
						}

						// Increase priority
						$section_priority += 5;
					}
				}
			}
		}

		/**
		 * This function returns an array of registered Conductor sidebars ids.
		 */
		public function get_conductor_sidebar_ids() {
			global $wp_registered_sidebars; // Contains the most up-to-date list of registered sidebars at this point

			$conduct_sidebars = Conduct_Sidebars();

			return $conduct_sidebars->find_conductor_sidebar_ids( $wp_registered_sidebars );
		}

		/**
		 * This function returns an array of registered Conductor sidebars.
		 */
		public function get_conductor_sidebars() {
			global $wp_registered_sidebars; // Contains the most up-to-date list of registered sidebars at this point

			$conductor_sidebars = array();

			// Grab Conductor sidebar ids
			$conductor_sidebar_ids = $this->get_conductor_sidebar_ids();

			foreach ( $conductor_sidebar_ids as $sidebar_id )
				$conductor_sidebars[$sidebar_id] = $wp_registered_sidebars[$sidebar_id];

			return $conductor_sidebars;
		}

		/**
		 * This function returns an array of Conductor font theme mods and their values.
		 */
		public function get_theme_mods() {
			// Grab registered Conductor sidebar details
			$conductor_sidebar_ids = $this->get_conductor_sidebar_ids();

			$r = array();

			// Loop through registered Conductor sidebars
			foreach( $conductor_sidebar_ids as $sidebar_id ) {
				if ( ! isset( $r[$sidebar_id] ) )
					$r[$sidebar_id] = array();

				// Loop through font theme mods
				foreach ( $this->conductor_theme_mods as $theme_mod => $theme_mod_types )
					// Loop through supported theme mod types for this theme mod
					foreach ( $theme_mod_types as $type ) {
						if ( ! isset( $r[$sidebar_id][$theme_mod] ) )
							$r[$sidebar_id][$theme_mod] = array();

						switch( $type ) {
							// Font Family
							case 'font_family':
								$r[$sidebar_id][$theme_mod][$type] = "'" . get_theme_mod( 'baton_conductor_' . $sidebar_id . '_' . $theme_mod . '_' . $type ) . "'";
							break;
							// Default
							default:
								$r[$sidebar_id][$theme_mod][$type] = get_theme_mod( 'baton_conductor_' . $sidebar_id . '_' . $theme_mod . '_' . $type );
							break;
						}
					}
			}

			return $r;
		}

		/**
		 * This function returns a float (sanitization).
		 */
		public function floatval( $value, $wp_customize_setting ) {
			// floatval() expects only one argument
			return floatval( $value );
		}
	}

	/**
	 * Create an instance of the Baton_Customizer_Conductor_Fonts class.
	 */
	function Baton_Customizer_Conductor_Fonts() {
		return Baton_Customizer_Conductor_Fonts::instance();
	}

	Baton_Customizer_Conductor_Fonts();
}