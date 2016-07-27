<?php
/**
* Baton Customizer Fonts (Font Customizer functionality)
*/
// TODO: Support for font subsets, etc..

// Bail if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

if ( ! class_exists( 'Baton_Customizer_Fonts' ) ) {
	final class Baton_Customizer_Fonts {
		/**
		 * @var string
		 */
		public $version = '1.0.3';

		/*
		 * var array, font property control IDs
		 */
		public $baton_font_property_control_ids = array();

		/*
		 * var array, font property defaults
		 */
		public $baton_font_property_defaults = array();

		/*
		 * var array, font family control IDs
		 */
		public $baton_font_family_control_ids = array();

		/*
		 * var array, font family defaults
		 */
		public $baton_font_family_defaults = array();

		/**
		 * @var Baton_Customizer_Fonts, Instance of the class
		 */
		protected static $_instance;

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

			// Hooks
			add_action( 'customize_register', array( $this, 'customize_register' ), 1 ); // Customizer Register (before anything else)
			add_action( 'customize_controls_print_styles', array( $this, 'customize_controls_print_styles' ) ); // <style> for Google Fonts (Select2)
			//add_action( 'customize_preview_init', array( $this, 'customize_preview_init' ) ); // Customizer Preview Initialization
			//add_action( 'customize_controls_print_footer_scripts', array( $this, 'customize_controls_print_footer_scripts' ) ); // Customizer Footer Scripts
		}

		/**
		 * Include required core files used in the Customizer.
		 */
		private function includes() {
			include_once get_template_directory() . '/customizer/class-baton-customizer-font-size-control.php'; // Baton Customizer Font Size Control
			include_once get_template_directory() . '/customizer/class-baton-customizer-font-family-control.php'; // Baton Customizer Font Family Control
		}


		/**
		 * This function registers sections and settings for use in the Customizer.
		 */
		public function customize_register( $wp_customize ) {
			$baton_customizer = Baton_Customizer_Instance();
			$is_wp_4 = $baton_customizer->version_compare( '4.0' );

			// Load required assets
			$this->includes();

			// Grab the Baton_Theme_Helper instance
			$baton_theme_helper = Baton_Theme_Helper();

			// Make sure we actually have some font support
			if ( ! empty( $baton_theme_helper->theme_support['fonts'] ) && is_array( $baton_theme_helper->theme_support['fonts'] ) ) {
				// TODO: Test for i18n
				$description = sprintf( __( 'Selecting multiple <a href="%1$s" target="_blank">Google Web Fonts</a> may impact the speed/performance of your website.', 'baton' ), esc_url( 'https://www.google.com/fonts/' ) );
				/**
				 * Fonts
				 */

				// Panel
				$wp_customize->add_panel( 'baton_fonts', array(
					'priority' => 30, // After "Design"
					'title' => __( 'Typography', 'baton' ),
					'description' => $description,
					'theme_supports' => $baton_theme_helper->theme_support_slug
				) );

				// Section (Site Title & Tagline)
				$wp_customize->add_section( 'baton_fonts_site_title_tagline', array(
					'priority' => 10,
					'title' => __( 'Site Title &amp; Tagline', 'baton' ),
					'description' =>$description,
					'panel' => 'baton_fonts'
				) );

				// Section (Navigation)
				$wp_customize->add_section( 'baton_fonts_navigation', array(
					'priority' => 15,
					'title' => __( 'Navigation', 'baton' ),
					'description' => $description,
					'panel' => 'baton_fonts'
				) );

				// Section (Body/Content)
				$wp_customize->add_section( 'baton_fonts_headings_body', array(
					'priority' => 20,
					'title' => __( 'Headings &amp; Body (Content)', 'baton' ),
					'description' => $description,
					'panel' => 'baton_fonts'
				) );

				// Section (Widgets)
				$wp_customize->add_section( 'baton_fonts_widgets', array(
					'priority' => 20,
					'title' => __( 'Fonts', 'baton' ),
					'description' => $description,
					'panel' => 'baton_widget_design'
				) );


				/**
				 * Site Title Font Size
				 */
				if ( $baton_theme_helper->current_theme_supports( 'fonts', 'site_title', 'font_size' ) ) {
					// Setting
					$wp_customize->add_setting(
						'baton_site_title_font_size',
						array(
							'default' => apply_filters( 'theme_mod_baton_site_title_font_size', 24, 24 ), // Pass the default value as second parameter
							'sanitize_callback' => 'absint',
							'sanitize_js_callback' => 'absint'
						)
					);

					// Control
					$wp_customize->add_control(
						new Baton_Customizer_Font_Size_Control(
							$wp_customize,
							'baton_site_title_font_size',
							array(
								'label' => __( 'Site Title Font Size', 'baton' ),
								'section' => ( ! $is_wp_4 ) ? 'baton_fonts' : 'baton_fonts_site_title_tagline',
								'settings' => 'baton_site_title_font_size',
								'priority' => 10,
								'type' => 'number',
								'input_attrs' => array(
									'min' => apply_filters( 'theme_mod_baton_site_title_font_size_min', 18, 18 ), // Pass the default value as second parameter
									'max' => apply_filters( 'theme_mod_baton_site_title_font_size_max', 36, 36 ), // Pass the default value as second parameter
									'placeholder' => apply_filters( 'theme_mod_baton_site_title_font_size', 24, 24 ), // Pass the default value as second parameter
									'style' => 'width: 70px;'
								),
								'units' => array(
									'title' => _x( 'pixels', 'title attribute for this Customizer control', 'baton' )
								)
							)
						)
					);

					// Add this control ID to the list of font property control IDs
					$this->baton_font_property_control_ids[] = 'baton_site_title_font_size';

					// Add the default value to the list of font property defaults
					$this->baton_font_property_defaults['baton_site_title_font_size'] = apply_filters( 'theme_mod_baton_site_title_font_size', 24, 24 ); // Pass the default value as second parameter
				}

				/**
				 * Site Title Font Family
				 */
				if ( $baton_theme_helper->current_theme_supports( 'fonts', 'site_title', 'font_family' ) ) {
					// Setting
					$wp_customize->add_setting(
						'baton_site_title_font_family',
						array(
							'default' => apply_filters( 'theme_mod_baton_site_title_font_family', '', '' ), // Pass the default value as second parameter
							'sanitize_callback' => array( $this, 'sanitize_google_web_font' ),
							'sanitize_js_callback' => array( $this, 'sanitize_google_web_font' )
						)
					);

					// Control
					$wp_customize->add_control(
						new Baton_Customizer_Font_Family_Control(
							$wp_customize,
							'baton_site_title_font_family',
							array(
								'label' => __( 'Site Title Font Family', 'baton' ),
								'section' => ( ! $is_wp_4 ) ? 'baton_fonts' : 'baton_fonts_site_title_tagline',
								'settings' => 'baton_site_title_font_family',
								'priority' => 15,
								'type' => 'select',
								'choices' => $this->get_google_fonts_choices( true )
							)
						)
					);

					// Add this control ID to the list of font family control IDs
					$this->baton_font_family_control_ids[] = 'baton_site_title_font_family';

					// Add the default value to the list of font family defaults
					$this->baton_font_family_defaults['baton_site_title_font_family'] = apply_filters( 'theme_mod_baton_site_title_font_family', '', '' ); // Pass the default value as second parameter
				}

				/**
				 * Site Title Letter Spacing
				 */
				if ( $baton_theme_helper->current_theme_supports( 'fonts', 'site_title', 'letter_spacing' ) ) {
					// Setting
					$wp_customize->add_setting(
						'baton_site_title_letter_spacing',
						array(
							'default' => apply_filters( 'theme_mod_baton_site_title_letter_spacing', 2, 2 ), // Pass the default value as second parameter
							'sanitize_callback' => 'absint',
							'sanitize_js_callback' => 'absint'
						)
					);

					// Control
					$wp_customize->add_control(
						new Baton_Customizer_Font_Size_Control(
							$wp_customize,
							'baton_site_title_letter_spacing',
							array(
								'label' => __( 'Site Title Letter Spacing', 'baton' ),
								'section' => ( ! $is_wp_4 ) ? 'baton_fonts' : 'baton_fonts_site_title_tagline',
								'settings' => 'baton_site_title_letter_spacing',
								'priority' => 20,
								'type' => 'number',
								'input_attrs' => array(
									'min' => apply_filters( 'theme_mod_baton_site_title_letter_spacing_min', 0, 0 ), // Pass the default value as second parameter
									'max' => apply_filters( 'theme_mod_baton_site_title_letter_spacing_max', 6, 6 ), // Pass the default value as second parameter
									'placeholder' => apply_filters( 'theme_mod_baton_site_title_letter_spacing', 2, 2 ), // Pass the default value as second parameter
									'style' => 'width: 70px;'
								),
								'units' => array(
									'title' => _x( 'pixels', 'title attribute for this Customizer control', 'baton' )
								)
							)
						)
					);

					// Add this control ID to the list of font size control IDs
					$this->baton_font_property_control_ids[] = 'baton_site_title_letter_spacing';

					// Add the default value to the list of font property defaults
					$this->baton_font_property_defaults['baton_site_title_letter_spacing'] = apply_filters( 'theme_mod_baton_site_title_letter_spacing', 2, 2 ); // Pass the default value as second parameter
				}


				/**
				 * Tagline Font Size
				 */

				if ( $baton_theme_helper->current_theme_supports( 'fonts', 'tagline', 'font_size' ) ) {
					// Setting
					$wp_customize->add_setting(
						'baton_tagline_font_size',
						array(
							'default' => apply_filters( 'theme_mod_baton_tagline_font_size', 18, 18 ), // px
							'sanitize_callback' => 'absint',
							'sanitize_js_callback' => 'absint'
						)
					);

					// Control
					$wp_customize->add_control(
						new Baton_Customizer_Font_Size_Control(
							$wp_customize,
							'baton_tagline_font_size',
							array(
								'label' => __( 'Tagline Font Size', 'baton' ),
								'section' => ( ! $is_wp_4 ) ? 'baton_fonts' : 'baton_fonts_site_title_tagline',
								'settings' => 'baton_tagline_font_size',
								'priority' => 20,
								'type' => 'number',
								'input_attrs' => array(
									'min' => apply_filters( 'theme_mod_baton_tagline_font_size_min', 12, 12 ), // Pass the default value as second parameter
									'max' => apply_filters( 'theme_mod_baton_tagline_font_size_max', 36, 36 ), // Pass the default value as second parameter
									'placeholder' => apply_filters( 'theme_mod_baton_tagline_font_size', 18, 18 ), // Pass the default value as second parameter
									'style' => 'width: 70px;'
								)
							)
						)
					);

					// Add this control ID to the list of font size control IDs
					$this->baton_font_property_control_ids[] = 'baton_tagline_font_size';

					// Add the default value to the list of font property defaults
					$this->baton_font_property_defaults['baton_tagline_font_size'] = apply_filters( 'theme_mod_baton_tagline_font_size', 18, 18 ); // Pass the default value as second parameter
				}

				/**
				 * Tagline Font Family
				 */

				if ( $baton_theme_helper->current_theme_supports( 'fonts', 'tagline', 'font_family' ) ) {
					// Setting
					$wp_customize->add_setting(
						'baton_tagline_font_family',
						array(
							'default' => apply_filters( 'theme_mod_baton_tagline_font_family', '', '' ), // Pass the default value as second parameter
							'sanitize_callback' => array( $this, 'sanitize_google_web_font' ),
							'sanitize_js_callback' => array( $this, 'sanitize_google_web_font' )
						)
					);

					// Control
					$wp_customize->add_control(
						new Baton_Customizer_Font_Family_Control(
							$wp_customize,
							'baton_tagline_font_family',
							array(
								'label' => __( 'Tagline Font Family', 'baton' ),
								'section' => ( ! $is_wp_4 ) ? 'baton_fonts' : 'baton_fonts_site_title_tagline',
								'settings' => 'baton_tagline_font_family',
								'priority' => 25,
								'type' => 'select',
								'choices' => $this->get_google_fonts_choices( true )
							)
						)
					);

					// Add this control ID to the list of font family control IDs
					$this->baton_font_family_control_ids[] = 'baton_tagline_font_family';

					// Add the default value to the list of font family defaults
					$this->baton_font_family_defaults['baton_tagline_font_family'] = apply_filters( 'theme_mod_baton_tagline_font_family', '', '' ); // Pass the default value as second parameter
				}

				/**
				 * Tagline Letter Spacing
				 */
				if ( $baton_theme_helper->current_theme_supports( 'fonts', 'tagline', 'letter_spacing' ) ) {
					// Setting
					$wp_customize->add_setting(
						'baton_tagline_letter_spacing',
						array(
							'default' => apply_filters( 'theme_mod_baton_tagline_letter_spacing', 2, 2 ), // Pass the default value as second parameter
							'sanitize_callback' => 'absint',
							'sanitize_js_callback' => 'absint'
						)
					);

					// Control
					$wp_customize->add_control(
						new Baton_Customizer_Font_Size_Control(
							$wp_customize,
							'baton_tagline_letter_spacing',
							array(
								'label' => __( 'Tagline Letter Spacing', 'baton' ),
								'section' => ( ! $is_wp_4 ) ? 'baton_fonts' : 'baton_fonts_tagline_tagline',
								'settings' => 'baton_tagline_letter_spacing',
								'priority' => 30,
								'type' => 'number',
								'input_attrs' => array(
									'min' => apply_filters( 'theme_mod_baton_tagline_letter_spacing_min', 0, 0 ), // Pass the default value as second parameter
									'max' => apply_filters( 'theme_mod_baton_tagline_letter_spacing_max', 6, 6 ), // Pass the default value as second parameter
									'placeholder' => apply_filters( 'theme_mod_baton_tagline_letter_spacing', 2, 2 ), // Pass the default value as second parameter
									'style' => 'width: 70px;'
								),
								'units' => array(
									'title' => _x( 'pixels', 'title attribute for this Customizer control', 'baton' )
								)
							)
						)
					);

					// Add this control ID to the list of font size control IDs
					$this->baton_font_property_control_ids[] = 'baton_tagline_letter_spacing';

					// Add the default value to the list of font property defaults
					$this->baton_font_property_defaults['baton_tagline_letter_spacing'] = apply_filters( 'theme_mod_baton_tagline_letter_spacing', 2, 2 ); // Pass the default value as second parameter
				}


				/**
				 * Navigation Font Size
				 */

				// Global support
				if ( $baton_theme_helper->current_theme_supports( 'fonts', 'navigation', 'font_size' ) ) {
					// Setting
					$wp_customize->add_setting(
						'baton_navigation_font_size',
						array(
							'default' => apply_filters( 'theme_mod_baton_navigation_font_size', 16, 16 ), // px
							'sanitize_callback' => 'absint',
							'sanitize_js_callback' => 'absint'
						)
					);

					// Control
					$wp_customize->add_control(
						new Baton_Customizer_Font_Size_Control(
							$wp_customize,
							'baton_navigation_font_size',
							array(
								'label' => __( 'Navigation Font Size', 'baton' ),
								'section' => ( ! $is_wp_4 ) ? 'baton_fonts' : 'baton_fonts_navigation',
								'settings' => 'baton_navigation_font_size',
								'priority' => ( ! $is_wp_4 ) ? 30 : 10,
								'type' => 'number',
								'input_attrs' => array(
									'min' => apply_filters( 'theme_mod_baton_navigation_font_size_min', 10, 10 ), // Pass the default value as second parameter
									'max' => apply_filters( 'theme_mod_baton_navigation_font_size_max', 24, 24 ), // Pass the default value as second parameter
									'placeholder' => apply_filters( 'theme_mod_baton_navigation_font_size', 16, 16 ), // Pass the default value as second parameter
									'style' => 'width: 70px;'
								)
							)
						)
					);

					// Add this control ID to the list of font size control IDs
					$this->baton_font_property_control_ids[] = 'baton_navigation_font_size';

					// Add the default value to the list of font property defaults
					$this->baton_font_property_defaults['baton_navigation_font_size'] = apply_filters( 'theme_mod_baton_navigation_font_size', 16, 16 ); // Pass the default value as second parameter
				}

				/**
				 * Navigation Font Family
				 */
				// Global support
				if ( $baton_theme_helper->current_theme_supports( 'fonts', 'navigation', 'font_family' ) ) {
					// Setting
					$wp_customize->add_setting(
						'baton_navigation_font_family',
						array(
							'default' => apply_filters( 'theme_mod_baton_navigation_font_family', '', '' ), // Pass the default value as second parameter
							'sanitize_callback' => array( $this, 'sanitize_google_web_font' ),
							'sanitize_js_callback' => array( $this, 'sanitize_google_web_font' )
						)
					);

					// Control
					$wp_customize->add_control(
						new Baton_Customizer_Font_Family_Control(
							$wp_customize,
							'baton_navigation_font_family',
							array(
								'label' => __( 'Navigation Font Family', 'baton' ),
								'section' => ( ! $is_wp_4 ) ? 'baton_fonts' : 'baton_fonts_navigation',
								'settings' => 'baton_navigation_font_family',
								'priority' => ( ! $is_wp_4 ) ? 35 : 15,
								'type' => 'select',
								'choices' => $this->get_google_fonts_choices( true )
							)
						)
					);

					// Add this control ID to the list of font family control IDs
					$this->baton_font_family_control_ids[] = 'baton_navigation_font_family';

					// Add the default value to the list of font family defaults
					$this->baton_font_family_defaults['baton_navigation_font_family'] = apply_filters( 'theme_mod_baton_navigation_font_family', '', '' ); // Pass the default value as second parameter
				}

				/**
				 * Navigation Font Size & Font Family (individual support)
				 */

				// Individual Support
				// TODO: 3.9 support
				if ( $baton_theme_helper->has_individual_navigation_support() ) {
					$nav_count = 0;
					$navigation_support = $baton_theme_helper->get_theme_support_value( 'fonts', 'navigation' );
					$registered_nav_menus = get_registered_nav_menus();

					// If we have registered nav menus
					if ( ! empty( $registered_nav_menus ) )
						// Loop through them
						foreach( $registered_nav_menus as $nav_menu_id => $nav_menu_label ) {
							// Theme Support for this navigation menu exists
							if ( array_key_exists( $nav_menu_id, $navigation_support ) ) {
								$nav_count++; // Increase nav count
								$priority = ( ! $is_wp_4 ) ? $nav_count * 50 : $nav_count * 10;

								/**
								 * Font Size
								 */
								if ( array_key_exists( 'font_size', $navigation_support[$nav_menu_id] ) ) {
									// Setting
									$wp_customize->add_setting(
										'baton_navigation_' . $nav_menu_id . '_font_size',
										array(
											'default' => apply_filters( 'theme_mod_baton_navigation_font_size', 16, 16, $nav_menu_id ), // px
											'sanitize_callback' => 'absint',
											'sanitize_js_callback' => 'absint'
										)
									);

									// Control
									$wp_customize->add_control(
										new Baton_Customizer_Font_Size_Control(
											$wp_customize,
											'baton_navigation_' . $nav_menu_id . '_font_size',
											array(
												'label' => sprintf( __( '%1$s Font Size', 'baton' ), $nav_menu_label ),
												'section' => ( ! $is_wp_4 ) ? 'baton_fonts' : 'baton_fonts_navigation',
												'settings' => 'baton_navigation_' . $nav_menu_id . '_font_size',
												'priority' => $priority,
												'type' => 'number',
												'input_attrs' => array(
													'min' => apply_filters( 'theme_mod_baton_navigation_font_size_min', 10, 10, $nav_menu_id ), // Pass the default value as second parameter and nav menu id as third
													'max' => apply_filters( 'theme_mod_baton_navigation_font_size_max', 24, 24, $nav_menu_id ), // Pass the default value as second parameter and nav menu id as third
													'placeholder' => apply_filters( 'theme_mod_baton_navigation_font_size', 16, 16, $nav_menu_id ), // Pass the default value as second parameter and nav menu id as third
													'style' => 'width: 70px;'
												)
											)
										)
									);

									// Add this control ID to the list of font size control IDs
									$this->baton_font_property_control_ids[] = 'baton_navigation_' . $nav_menu_id . '_font_size';

									// Add the default value to the list of font property defaults
									$this->baton_font_property_defaults['baton_navigation_' . $nav_menu_id . '_font_size'] = apply_filters( 'theme_mod_baton_navigation_font_size', 16, 16, $nav_menu_id ); // Pass the default value as second parameter and nav menu id as third
								}

								/**
								 * Font Family
								 */
								if ( array_key_exists( 'font_family', $navigation_support[$nav_menu_id] ) ) {
									// Setting
									$wp_customize->add_setting(
										'baton_navigation_' . $nav_menu_id . '_font_family',
										array(
											'default' => apply_filters( 'theme_mod_baton_navigation_font_family', '', '', $nav_menu_id ), // Pass the default value as second parameter and nav menu id as third
											'sanitize_callback' => array( $this, 'sanitize_google_web_font' ),
											'sanitize_js_callback' => array( $this, 'sanitize_google_web_font' )
										)
									);

									// Control
									$wp_customize->add_control(
										new Baton_Customizer_Font_Family_Control(
											$wp_customize,
											'baton_navigation_' . $nav_menu_id . '_font_family',
											array(
												'label' => sprintf( __( '%1$s Font Family', 'baton' ), $nav_menu_label ),
												'section' => ( ! $is_wp_4 ) ? 'baton_fonts' : 'baton_fonts_navigation',
												'settings' => 'baton_navigation_' . $nav_menu_id . '_font_family',
												'priority' => $priority + 5, // Priority increases by 5
												'type' => 'select',
												'choices' => $this->get_google_fonts_choices( true )
											)
										)
									);

									// Add this control ID to the list of font family control IDs
									$this->baton_font_family_control_ids[] = 'baton_navigation_' . $nav_menu_id . '_font_family';

									// Add the default value to the list of font family defaults
									$this->baton_font_family_defaults['baton_navigation_' . $nav_menu_id . '_font_family'] = apply_filters( 'theme_mod_baton_navigation_font_family', '', '', $nav_menu_id ); // Pass the default value as second parameter and nav menu id as third
								}
							}
						}
				}

				/**
				 * Headings (Heading 1-6) Global Support
				 */

				// Global Heading support (only applies if no individual support declared)
				if ( ( $baton_theme_helper->current_theme_supports( 'fonts', 'headings', 'font_size' ) || $baton_theme_helper->current_theme_supports( 'fonts', 'headings', 'font_family' ) ) && ! $baton_theme_helper->current_theme_supports( 'fonts', 'headings', array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ) ) ) {
					/**
					 * Font Size
					 */

					// Setting
					$wp_customize->add_setting(
						'baton_headings_font_size',
						array(
							'default' => apply_filters( 'theme_mod_baton_headings_font_size', 34, 34 ), // px
							'sanitize_callback' => 'absint',
							'sanitize_js_callback' => 'absint'
						)
					);

					// Control
					$wp_customize->add_control(
						new Baton_Customizer_Font_Size_Control(
							$wp_customize,
							'baton_headings_font_size',
							array(
								'label' => __( 'Heading Font Size', 'baton' ),
								'section' => ( ! $is_wp_4 ) ? 'baton_fonts' : 'baton_fonts_headings_body',
								'settings' => 'baton_headings_font_size',
								'priority' => ( ! $is_wp_4 ) ? 40 : 10,
								'type' => 'number',
								'input_attrs' => array(
									'min' => apply_filters( 'theme_mod_baton_headings_font_size_min', 24, 24 ), // Pass the default value as second parameter
									'max' => apply_filters( 'theme_mod_baton_headings_font_size_max', 56, 56 ), // Pass the default value as second parameter
									'placeholder' => apply_filters( 'theme_mod_baton_headings_font_size', 34, 34 ), // Pass the default value as second parameter
									'style' => 'width: 70px;'
								)
							)
						)
					);

					// Add this control ID to the list of font size control IDs
					$this->baton_font_property_control_ids[] = 'baton_headings_font_size';

					// Add the default value to the list of font property defaults
					$this->baton_font_property_defaults['baton_headings_font_size'] = apply_filters( 'theme_mod_baton_headings_font_size', 34, 34 ); // Pass the default value as second parameter


					/**
					 * Font Family
					 */

					// Setting
					$wp_customize->add_setting(
						'baton_headings_font_family',
						array(
							'default' => apply_filters( 'theme_mod_baton_headings_font_family', '', '' ), // Pass the default value as second parameter
							'sanitize_callback' => array( $this, 'sanitize_google_web_font' ),
							'sanitize_js_callback' => array( $this, 'sanitize_google_web_font' )
						)
					);

					// Control
					$wp_customize->add_control(
						new Baton_Customizer_Font_Family_Control(
							$wp_customize,
							'baton_headings_font_family',
							array(
								'label' => __( 'Heading Font Family', 'baton' ),
								'section' => ( ! $is_wp_4 ) ? 'baton_fonts' : 'baton_fonts_headings_body',
								'settings' => 'baton_headings_font_family',
								'priority' => ( ! $is_wp_4 ) ? 45 : 15,
								'type' => 'select',
								'choices' => $this->get_google_fonts_choices( true )
							)
						)
					);

					// Add this control ID to the list of font family control IDs
					$this->baton_font_family_control_ids[] = 'baton_headings_font_family';

					// Add the default value to the list of font family defaults
					$this->baton_font_family_defaults['baton_headings_font_family'] = apply_filters( 'theme_mod_baton_headings_font_family', '', '' ); // Pass the default value as second parameter
				}

				/**
				 * Headings (Heading 1-6) Individual Support
				 */

				// Individual Heading support (overrides global support)
				if ( $baton_theme_helper->current_theme_supports( 'fonts', 'headings', array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ) ) ) {
					// Get font family support for headings
					$heading_support = $baton_theme_helper->get_theme_support_value( 'fonts', 'headings' );

					// Create an array of labels for font family support
					$labels = array(
						'h1' => __( 'Heading 1', 'baton' ),
						'h2' => __( 'Heading 2', 'baton' ),
						'h3' => __( 'Heading 3', 'baton' ),
						'h4' => __( 'Heading 4', 'baton' ),
						'h5' => __( 'Heading 5', 'baton' ),
						'h6' => __( 'Heading 6', 'baton' )
					);

					// Loop through font family support
					if ( is_array( $heading_support ) )
						foreach( $heading_support as $key => $support ) {
							// Heading 1-6 Support
							if ( in_array( $key, array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ) ) ) {
								$index = ( int ) substr( $key, 1 );
								$priority = ( ! $is_wp_4 ) ? $index * 50 : $index * 10;

								// Defaults
								$font_size = round( 84 / $index );
								$font_size_min = round( 72 / $index );
								$font_size_max = round( 96 / $index );

								/**
								 * Font Size
								 */

								// Setting
								$wp_customize->add_setting(
									'baton_' . $key . '_font_size',
									array(
										'default' => apply_filters( 'theme_mod_baton_' . $key . '_font_size', $font_size, $font_size ), // px
										'sanitize_callback' => 'absint',
										'sanitize_js_callback' => 'absint'
									)
								);

								// Control
								$wp_customize->add_control(
									new Baton_Customizer_Font_Size_Control(
										$wp_customize,
										'baton_' . $key . '_font_size',
										array(
											'label' => sprintf( __( '%1$s Font Size', 'baton' ), $labels[$key] ),
											'section' => ( ! $is_wp_4 ) ? 'baton_fonts' : 'baton_fonts_headings_body',
											'settings' => 'baton_' . $key . '_font_size',
											'priority' => $priority, // Priority increases by 5
											'type' => 'number',
											'input_attrs' => array(
												'min' => apply_filters( 'theme_mod_baton_' . $key . '_font_size_min', $font_size_min, $font_size_min ), // Pass the default value as second parameter
												'max' => apply_filters( 'theme_mod_baton_' . $key . '_font_size_max', $font_size_max, $font_size_max ), // Pass the default value as second parameter
												'placeholder' => apply_filters( 'theme_mod_baton_' . $key . '_font_size', $font_size, $font_size ), // Pass the default value as second parameter
												'style' => 'width: 70px;'
											)
										)
									)
								);

								// Add this control ID to the list of font size control IDs
								$this->baton_font_property_control_ids[] = 'baton_' . $key . '_font_size';

								// Add the default value to the list of font property defaults
								$this->baton_font_property_defaults['baton_' . $key . '_font_size'] = apply_filters( 'theme_mod_baton_' . $key . '_font_size', $font_size, $font_size ); // Pass the default value as second parameter

								/**
								 * Font Family
								 */

								// Setting
								$wp_customize->add_setting(
									'baton_' . $key . '_font_family',
									array(
										'default' => apply_filters( 'theme_mod_baton_' . $key . '_font_family', '', '' ), // Pass the default value as second parameter
										'sanitize_callback' => array( $this, 'sanitize_google_web_font' ),
										'sanitize_js_callback' => array( $this, 'sanitize_google_web_font' )
									)
								);

								// Control
								$wp_customize->add_control(
									new Baton_Customizer_Font_Family_Control(
										$wp_customize,
										'baton_' . $key . '_font_family',
										array(
											'label' => sprintf( __( '%1$s Font Family', 'baton' ), $labels[$key] ),
											'section' => ( ! $is_wp_4 ) ? 'baton_fonts' : 'baton_fonts_headings_body',
											'settings' => 'baton_' . $key . '_font_family',
											'priority' => $priority + 5, // Priority increases by 5
											'type' => 'select',
											'choices' => $this->get_google_fonts_choices( true )
										)
									)
								);

								// Add this control ID to the list of font family control IDs
								$this->baton_font_family_control_ids[] = 'baton_' . $key . '_font_family';

								// Add the default value to the list of font family defaults
								$this->baton_font_family_defaults['baton_' . $key . '_font_family'] = apply_filters( 'theme_mod_baton_' . $key . '_font_family', '', '' ); // Pass the default value as second parameter
							}
						}
				}


				/**
				 * Body (content) Font Size
				 */

				if ( $baton_theme_helper->current_theme_supports( 'fonts', 'body', 'font_size' ) ) {
					// Setting
					$wp_customize->add_setting(
						'baton_body_font_size',
						array(
							'default' => apply_filters( 'theme_mod_baton_body_font_size', 16, 16 ), // px
							'sanitize_callback' => 'absint',
							'sanitize_js_callback' => 'absint'
						)
					);

					// Control
					$wp_customize->add_control(
						new Baton_Customizer_Font_Size_Control(
							$wp_customize,
							'baton_body_font_size',
							array(
								'label' => __( 'Font Size', 'baton' ),
								'section' => ( ! $is_wp_4 ) ? 'baton_fonts' : 'baton_fonts_headings_body',
								'settings' => 'baton_body_font_size',
								'priority' => 10, // First
								'type' => 'number',
								'input_attrs' => array(
									'min' => apply_filters( 'theme_mod_baton_body_font_size_min', 10, 10 ), // Pass the default value as second parameter
									'max' => apply_filters( 'theme_mod_baton_body_font_size_max', 24, 24 ), // Pass the default value as second parameter
									'placeholder' => apply_filters( 'theme_mod_baton_body_font_size', 16, 16 ), // Pass the default value as second parameter
									'style' => 'width: 70px;'
								)
							)
						)
					);

					// Add this control ID to the list of font size control IDs
					$this->baton_font_property_control_ids[] = 'baton_body_font_size';

					// Add the default value to the list of font property defaults
					$this->baton_font_property_defaults['baton_body_font_size'] = apply_filters( 'theme_mod_baton_body_font_size', 16, 16 ); // Pass the default value as second parameter
				}

				/**
				 * Body (content) Line Height
				 */

				if ( $baton_theme_helper->current_theme_supports( 'fonts', 'body', 'line_height' ) ) {
					// Setting
					$wp_customize->add_setting(
						'baton_body_line_height',
						array(
							'default' => apply_filters( 'theme_mod_baton_body_line_height', 16, 16 ), // px
							'sanitize_callback' => 'absint',
							'sanitize_js_callback' => 'absint'
						)
					);

					// Control
					$wp_customize->add_control(
						new Baton_Customizer_Font_Size_Control(
							$wp_customize,
							'baton_body_line_height',
							array(
								'label' => __( 'Line Height', 'baton' ),
								'section' => ( ! $is_wp_4 ) ? 'baton_fonts' : 'baton_fonts_headings_body',
								'settings' => 'baton_body_line_height',
								'priority' => 20, // After Font Size
								'type' => 'number',
								'input_attrs' => array(
									'min' => apply_filters( 'theme_mod_baton_body_line_height_min', 10, 10 ), // Pass the default value as second parameter
									'max' => apply_filters( 'theme_mod_baton_body_line_height_max', 48, 48 ), // Pass the default value as second parameter
									'placeholder' => apply_filters( 'theme_mod_baton_body_line_height', 16, 16 ), // Pass the default value as second parameter
									'style' => 'width: 70px;'
								)
							)
						)
					);

					// Add this control ID to the list of font size control IDs
					$this->baton_font_property_control_ids[] = 'baton_body_line_height';

					// Add the default value to the list of font property defaults
					$this->baton_font_property_defaults['baton_body_line_height'] = apply_filters( 'theme_mod_baton_body_line_height', 16, 16 ); // Pass the default value as second parameter
				}

				/**
				 * Body (content) Font Family
				 */

				if ( $baton_theme_helper->current_theme_supports( 'fonts', 'body', 'font_family' ) ) {
					// Setting
					$wp_customize->add_setting(
						'baton_body_font_family',
						array(
							'default' => apply_filters( 'theme_mod_baton_body_font_family', '', '' ), // Pass the default value as second parameter
							'sanitize_callback' => array( $this, 'sanitize_google_web_font' ),
							'sanitize_js_callback' => array( $this, 'sanitize_google_web_font' )
						)
					);

					// Control
					$wp_customize->add_control(
						new Baton_Customizer_Font_Family_Control(
							$wp_customize,
							'baton_body_font_family',
							array(
								'label' => __( 'Font Family', 'baton' ),
								'section' => ( ! $is_wp_4 ) ? 'baton_fonts' : 'baton_fonts_headings_body',
								'settings' => 'baton_body_font_family',
								'priority' => 30, // After Line Height
								'type' => 'select',
								'choices' => $this->get_google_fonts_choices( true )
							)
						)
					);

					// Add this control ID to the list of font family control IDs
					$this->baton_font_family_control_ids[] = 'baton_body_font_family';

					// Add the default value to the list of font family defaults
					$this->baton_font_family_defaults['baton_body_font_family'] = apply_filters( 'theme_mod_baton_body_font_family', '', '' ); // Pass the default value as second parameter
				}


				/**
				 * Widget Title Font Size
				 */

				if ( $baton_theme_helper->current_theme_supports( 'fonts', 'widget', 'title', 'font_size' ) ) {
					// Setting
					$wp_customize->add_setting(
						'baton_widget_title_font_size',
						array(
							'default' => apply_filters( 'theme_mod_baton_widget_title_font_size', 16, 16 ), // Pass the default value as second parameter
							'sanitize_callback' => 'absint',
							'sanitize_js_callback' => 'absint'
						)
					);

					// Control
					$wp_customize->add_control(
						new Baton_Customizer_Font_Size_Control(
							$wp_customize,
							'baton_widget_title_font_size',
							array(
								'label' => __( 'Widget Title Font Size', 'baton' ),
								'section' => ( ! $is_wp_4 ) ? 'baton_fonts' : 'baton_fonts_widgets',
								'settings' => 'baton_widget_title_font_size',
								'priority' => 10,
								'type' => 'number',
								'input_attrs' => array(
									'min' => apply_filters( 'theme_mod_baton_widget_title_font_size_min', 10, 10 ), // Pass the default value as second parameter
									'max' => apply_filters( 'theme_mod_baton_widget_title_font_size_max', 24, 24 ), // Pass the default value as second parameter
									'placeholder' => apply_filters( 'theme_mod_baton_widget_title_font_size', 16, 16 ), // Pass the default value as second parameter
									'style' => 'width: 70px;'
								),
								'units' => array(
									'title' => _x( 'pixels', 'title attribute for this Customizer control', 'baton' )
								)
							)
						)
					);

					// Add this control ID to the list of font size control IDs
					$this->baton_font_property_control_ids[] = 'baton_widget_title_font_size';

					// Add the default value to the list of font property defaults
					$this->baton_font_property_defaults['baton_widget_title_font_size'] = apply_filters( 'theme_mod_baton_widget_title_font_size', 16, 16 ); // Pass the default value as second parameter
				}

				/**
				 * Widget Title Font Family
				 */

				if ( $baton_theme_helper->current_theme_supports( 'fonts', 'widget', 'title', 'font_family' ) ) {
					// Setting
					$wp_customize->add_setting(
						'baton_widget_title_font_family',
						array(
							'default' => apply_filters( 'theme_mod_baton_widget_title_font_family', '', '' ), // Pass the default value as second parameter
							'sanitize_callback' => array( $this, 'sanitize_google_web_font' ),
							'sanitize_js_callback' => array( $this, 'sanitize_google_web_font' )
						)
					);

					// Control
					$wp_customize->add_control(
						new Baton_Customizer_Font_Family_Control(
							$wp_customize,
							'baton_widget_title_font_family',
							array(
								'label' => __( 'Widget Title Font Family', 'baton' ),
								'section' => ( ! $is_wp_4 ) ? 'baton_fonts' : 'baton_fonts_widgets',
								'settings' => 'baton_widget_title_font_family',
								'priority' => 15,
								'type' => 'select',
								'choices' => $this->get_google_fonts_choices( true )
							)
						)
					);

					// Add this control ID to the list of font family control IDs
					$this->baton_font_family_control_ids[] = 'baton_widget_title_font_family';

					// Add the default value to the list of font family defaults
					$this->baton_font_family_defaults['baton_widget_title_font_family'] = apply_filters( 'theme_mod_baton_widget_title_font_family', '', '' ); // Pass the default value as second parameter
				}

				/**
				 * Widget Font Size
				 */

				if ( $baton_theme_helper->current_theme_supports( 'fonts', 'widget', 'font_size' ) ) {
					// Setting
					$wp_customize->add_setting(
						'baton_widget_font_size',
						array(
							'default' => apply_filters( 'theme_mod_baton_widget_font_size', 16, 16 ), // Pass the default value as second parameter
							'sanitize_callback' => 'absint',
							'sanitize_js_callback' => 'absint'
						)
					);

					// Control
					$wp_customize->add_control(
						new Baton_Customizer_Font_Size_Control(
							$wp_customize,
							'baton_widget_font_size',
							array(
								'label' => __( 'Widget Font Size', 'baton' ),
								'section' => ( ! $is_wp_4 ) ? 'baton_fonts' : 'baton_fonts_widgets',
								'settings' => 'baton_widget_font_size',
								'priority' => 20,
								'type' => 'number',
								'input_attrs' => array(
									'min' => apply_filters( 'theme_mod_baton_widget_font_size_min', 10, 10 ), // Pass the default value as second parameter
									'max' => apply_filters( 'theme_mod_baton_widget_font_size_max', 24, 24 ), // Pass the default value as second parameter
									'placeholder' => apply_filters( 'theme_mod_baton_widget_font_size', 16, 16 ), // Pass the default value as second parameter
									'style' => 'width: 70px;'
								),
								'units' => array(
									'title' => _x( 'pixels', 'title attribute for this Customizer control', 'baton' )
								)
							)
						)
					);

					// Add this control ID to the list of font property control IDs
					$this->baton_font_property_control_ids[] = 'baton_widget_font_size';

					// Add the default value to the list of font property defaults
					$this->baton_font_property_defaults['baton_widget_font_size'] = apply_filters( 'theme_mod_baton_widget_font_size', 16, 16 ); // Pass the default value as second parameter
				}

				/**
				 * Widget Font Family
				 */

				if ( $baton_theme_helper->current_theme_supports( 'fonts', 'widget', 'font_family' ) ) {
					// Setting
					$wp_customize->add_setting(
						'baton_widget_font_family',
						array(
							'default' => apply_filters( 'theme_mod_baton_widget_font_family', '', '' ), // Pass the default value as second parameter
							'sanitize_callback' => array( $this, 'sanitize_google_web_font' ),
							'sanitize_js_callback' => array( $this, 'sanitize_google_web_font' )
						)
					);

					// Control
					$wp_customize->add_control(
						new Baton_Customizer_Font_Family_Control(
							$wp_customize,
							'baton_widget_font_family',
							array(
								'label' => __( 'Widget Font Family', 'baton' ),
								'section' => ( ! $is_wp_4 ) ? 'baton_fonts' : 'baton_fonts_widgets',
								'settings' => 'baton_widget_font_family',
								'priority' => 25,
								'type' => 'select',
								'choices' => $this->get_google_fonts_choices( true )
							)
						)
					);

					// Add this control ID to the list of font family control IDs
					$this->baton_font_family_control_ids[] = 'baton_widget_font_family';

					// Add the default value to the list of font family defaults
					$this->baton_font_family_defaults['baton_widget_font_family'] = apply_filters( 'theme_mod_baton_widget_font_family', '', '' ); // Pass the default value as second parameter
				}
			}
		}

		/**
		 * This function outputs a <style> block for Google Web Fonts within Select2 dropdown elements.
		 */
		public function customize_controls_print_styles() {
			$background_images = array();

			$r = '<style type="text/css">';

			// TODO: make sure this works properly, maybe add styles for standard web fonts too?
			$fonts = $this->get_google_fonts();

			foreach ( $fonts as $font_family => $font ) {
				// Google Web Fonts
				if ( $font['type'] === 'google' ) {
					$selector = sanitize_title( $font_family );

					$css = '.baton-select2-result.' . $selector .' .select2-result-label {' . "\n";
						$css .= ' max-height: 26px;';
						$css .= ' background-image: url(';
						$background_image = apply_filters( 'baton_customize_select2_google_font_background_image', get_template_directory_uri() . '/customizer/images/' . $selector . '.png' );
						$background_images[] = $background_image; // Store the background image reference for pre-load
						$css .= $background_image;
						$css .= ');' . "\n";
					$css .= '}' . "\n";

					$css = apply_filters( 'baton_customize_select2_google_font_background_image', $css, $font_family, $font );

					$r .= $css;
				}
				// Standard Fonts
				else {
					$selector = sanitize_title( $font_family );

					$css = '.baton-select2-result.' . $selector .' .select2-result-label {' . "\n";
					$css .= ' width: 100%;';
					$css .= ' max-height: 26px;';
					$css .= ' padding-left: 2px;';
					$css .= ' font-family: \'' . $font_family .'\';' . "\n";
					$css .= '}' . "\n";

					$css = apply_filters( 'baton_customize_select2_google_font_background_image', $css, $font_family, $font );

					$r .= $css;
				}
			}

			/*
			 * Pre-load background images.
			 *
			 * Select2 dynamically loads items on first "open" even which then causes background images to load.
			 * We pre-load the images using CSS to prevent the "lag" on load time on first "open" event.
			 */
			$background_images = array_filter( $background_images );

			if ( ! empty( $background_images ) ) {
				$r .= 'body:after { display: none; content: ';

				foreach( $background_images as $background_image )
					$r .= ' url(' . $background_image . ') ' . "\n";

				$r .= '; }' . "\n";
			}

			$r .= '</style>';

			echo apply_filters( 'baton_customize_select2_google_fonts_style', $r );
		}

		/**
		 * This function returns a list of available Google Web Fonts to use.
		 *
		 * @param $type string, possible values are 'all' (both standard fonts and Google web fonts),
		 *						'google' (just Google web fonts), or 'standard' (just standard fonts)
		 *
		 * License: GPLv2 or later
		 * Copyright: Make Theme/The Theme Foundry, https://thethemefoundry.com/
		 *
		 * @link https://github.com/thethemefoundry/make/blob/1c31d2951d50cd1299493bc549d763b241c03abc/src/inc/customizer/helpers-fonts.php#L966
		 *
		 * We've used The Theme Foundry's functionality as a base and modified it to suit our needs.
		 */
		// TODO: switch $type ($type is currently not used)
		public function get_google_fonts( $type = 'all' ) {
			// This array contains "standard" fonts as well
			return apply_filters( 'baton_google_fonts', array(
				'Arial' => array(
					'label' => 'Arial',
					'type' => 'standard',
					'format' => 'sans-serif'
				),
				'Arial Black' => array(
					'label' => 'Arial Black',
					'type' => 'standard',
					'format' => 'sans-serif'
				),
				'Courier' => array(
					'label' => 'Courier',
					'type' => 'standard',
					'format' => 'serif'
				),
				'Courier New' => array(
					'label' => 'Courier New',
					'type' => 'standard',
					'format' => 'serif'
				),
				'Georgia' => array(
					'label' => 'Georgia',
					'type' => 'standard',
					'format' => 'serif'
				),
				'Tahoma' => array(
					'label' => 'Tahoma',
					'type' => 'standard',
					'format' => 'sans-serif'
				),
				'Times' => array(
					'label' => 'Times',
					'type' => 'standard',
					'format' => 'serif'
				),
				'Times New Roman' => array(
					'label' => 'Times New Roman',
					'type' => 'standard',
					'format' => 'serif'
				),
				'Trebuchet MS' => array(
					'label' => 'Trebuchet MS',
					'type' => 'standard',
					'format' => 'sans-serif'
				),
				'Verdana' => array(
					'label' => 'Verdana',
					'type' => 'standard',
					'format' => 'sans-serif'
				),
				'Abril Fatface' => array(
					'label' => 'Abril Fatface',
					'variants' => array(
						'regular',
					),
					'subsets' => array(
						'latin',
						'latin-ext',
					),
					'type' => 'google'
				),
				'Alegreya' => array(
					'label' => 'Alegreya',
					'variants' => array(
						'regular',
						'italic',
						'700',
						'700italic',
						'900',
						'900italic',
					),
					'subsets' => array(
						'latin',
						'latin-ext',
					),
					'type' => 'google'
				),
				'Anonymous Pro' => array(
					'label' => 'Anonymous Pro',
					'variants' => array(
						'regular',
						'italic',
						'700',
						'700italic',
					),
					'subsets' => array(
						'latin',
						'greek-ext',
						'cyrillic',
						'greek',
						'latin-ext',
						'cyrillic-ext',
					),
					'type' => 'google'
				),
				'Archivo Narrow' => array(
					'label' => 'Archivo Narrow',
					'variants' => array(
						'regular',
						'italic',
						'700',
						'700italic',
					),
					'subsets' => array(
						'latin',
						'latin-ext',
					),
					'type' => 'google'
				),
				'Arimo' => array(
					'label' => 'Arimo',
					'variants' => array(
						'regular',
						'italic',
						'700',
						'700italic',
					),
					'subsets' => array(
						'latin',
						'greek-ext',
						'cyrillic',
						'greek',
						'vietnamese',
						'latin-ext',
						'cyrillic-ext',
					),
					'type' => 'google'
				),
				'Arvo' => array(
					'label' => 'Arvo',
					'variants' => array(
						'regular',
						'italic',
						'700',
						'700italic',
					),
					'subsets' => array(
						'latin',
					),
					'type' => 'google'
				),
				'Bitter' => array(
					'label' => 'Bitter',
					'variants' => array(
						'regular',
						'italic',
						'700',
					),
					'subsets' => array(
						'latin',
						'latin-ext',
					),
					'type' => 'google'
				),
				'Chivo' => array(
					'label' => 'Chivo',
					'variants' => array(
						'regular',
						'italic',
						'900',
						'900italic',
					),
					'subsets' => array(
						'latin',
					),
					'type' => 'google'
				),
				'Domine' => array(
					'label' => 'Domine',
					'variants' => array(
						'regular',
						'700',
					),
					'subsets' => array(
						'latin',
						'latin-ext',
					),
					'type' => 'google'
				),
				'Dosis' => array(
					'label' => 'Dosis',
					'variants' => array(
						'200',
						'300',
						'regular',
						'500',
						'600',
						'700',
						'800',
					),
					'subsets' => array(
						'latin',
						'latin-ext',
					),
					'type' => 'google'
				),
				'Droid Sans' => array(
					'label' => 'Droid Sans',
					'variants' => array(
						'regular',
						'700',
					),
					'subsets' => array(
						'latin',
					),
					'type' => 'google'
				),
				'Droid Serif' => array(
					'label' => 'Droid Serif',
					'variants' => array(
						'regular',
						'italic',
						'700',
						'700italic',
					),
					'subsets' => array(
						'latin',
					),
					'type' => 'google'
				),
				'Fjalla One' => array(
					'label' => 'Fjalla One',
					'variants' => array(
						'regular',
					),
					'subsets' => array(
						'latin',
						'latin-ext',
					),
					'type' => 'google'
				),
				'Gravitas One' => array(
					'label' => 'Gravitas One',
					'variants' => array(
						'regular',
					),
					'subsets' => array(
						'latin',
					),
					'type' => 'google'
				),
				'Inconsolata' => array(
					'label' => 'Inconsolata',
					'variants' => array(
						'regular',
						'700',
					),
					'subsets' => array(
						'latin',
						'latin-ext',
					),
					'type' => 'google'
				),
				'Josefin Slab' => array(
					'label' => 'Josefin Slab',
					'variants' => array(
						'100',
						'100italic',
						'300',
						'300italic',
						'regular',
						'italic',
						'600',
						'600italic',
						'700',
						'700italic',
					),
					'subsets' => array(
						'latin',
					),
					'type' => 'google'
				),
				'Karla' => array(
					'label' => 'Karla',
					'variants' => array(
						'regular',
						'italic',
						'700',
						'700italic',
					),
					'subsets' => array(
						'latin',
						'latin-ext',
					),
					'type' => 'google'
				),
				'Lato' => array(
					'label' => 'Lato',
					'variants' => array(
						'100',
						'100italic',
						'300',
						'300italic',
						'regular',
						'italic',
						'700',
						'700italic',
						'900',
						'900italic',
					),
					'subsets' => array(
						'latin',
					),
					'type' => 'google'
				),
				'Libre Baskerville' => array(
					'label' => 'Libre Baskerville',
					'variants' => array(
						'regular',
						'italic',
						'700',
					),
					'subsets' => array(
						'latin',
						'latin-ext',
					),
					'type' => 'google'
				),
				'Lobster' => array(
					'label' => 'Lobster',
					'variants' => array(
						'regular',
					),
					'subsets' => array(
						'latin',
						'cyrillic',
						'latin-ext',
						'cyrillic-ext',
					),
					'type' => 'google'
				),
				'Lobster Two' => array(
					'label' => 'Lobster Two',
					'variants' => array(
						'regular',
						'italic',
						'700',
						'700italic',
					),
					'subsets' => array(
						'latin',
					),
					'type' => 'google'
				),
				'Lora' => array(
					'label' => 'Lora',
					'variants' => array(
						'regular',
						'italic',
						'700',
						'700italic',
					),
					'subsets' => array(
						'latin',
						'cyrillic',
						'latin-ext',
					),
					'type' => 'google'
				),
				'Martel Sans' => array(
					'label' => 'Martel Sans',
					'variants' => array(
						'200',
						'300',
						'regular',
						'700',
						'700',
						'800',
						'900',
					),
					'subsets' => array(
						'latin',
					),
					'type' => 'google'
				),
				'Merriweather' => array(
					'label' => 'Merriweather',
					'variants' => array(
						'300',
						'300italic',
						'regular',
						'italic',
						'700',
						'700italic',
						'900',
						'900italic',
					),
					'subsets' => array(
						'latin',
						'latin-ext',
					),
					'type' => 'google'
				),
				'Montserrat' => array(
					'label' => 'Montserrat',
					'variants' => array(
						'regular',
						'700',
					),
					'subsets' => array(
						'latin',
					),
					'type' => 'google'
				),
				'Muli' => array(
					'label' => 'Muli',
					'variants' => array(
						'300',
						'300italic',
						'regular',
						'italic',
					),
					'subsets' => array(
						'latin',
					),
					'type' => 'google'
				),
				'Neuton' => array(
					'label' => 'Neuton',
					'variants' => array(
						'200',
						'300',
						'regular',
						'italic',
						'700',
						'800',
					),
					'subsets' => array(
						'latin',
						'latin-ext',
					),
					'type' => 'google'
				),
				'Old Standard TT' => array(
					'label' => 'Old Standard TT',
					'variants' => array(
						'regular',
						'italic',
						'700',
					),
					'subsets' => array(
						'latin',
					),
					'type' => 'google'
				),
				'Open Sans' => array(
					'label' => 'Open Sans',
					'variants' => array(
						'300',
						'300italic',
						'regular',
						'italic',
						'600',
						'600italic',
						'700',
						'700italic',
						'800',
						'800italic',
					),
					'subsets' => array(
						'latin',
						'greek-ext',
						'cyrillic',
						'greek',
						'vietnamese',
						'latin-ext',
						'devanagari',
						'cyrillic-ext',
					),
					'type' => 'google'
				),
				'Oswald' => array(
					'label' => 'Oswald',
					'variants' => array(
						'300',
						'regular',
						'700',
					),
					'subsets' => array(
						'latin',
						'latin-ext',
					),
					'type' => 'google'
				),
				'Playfair Display' => array(
					'label' => 'Playfair Display',
					'variants' => array(
						'regular',
						'italic',
						'700',
						'700italic',
						'900',
						'900italic',
					),
					'subsets' => array(
						'latin',
						'cyrillic',
						'latin-ext',
					),
					'type' => 'google'
				),
				'PT Sans' => array(
					'label' => 'PT Sans',
					'variants' => array(
						'regular',
						'italic',
						'700',
						'700italic',
					),
					'subsets' => array(
						'latin',
						'cyrillic',
						'latin-ext',
						'cyrillic-ext',
					),
					'type' => 'google'
				),
				'PT Serif' => array(
					'label' => 'PT Serif',
					'variants' => array(
						'regular',
						'italic',
						'700',
						'700italic',
					),
					'subsets' => array(
						'latin',
						'cyrillic',
						'latin-ext',
						'cyrillic-ext',
					),
					'type' => 'google'
				),
				'Questrial' => array(
					'label' => 'Questrial',
					'variants' => array(
						'regular',
					),
					'subsets' => array(
						'latin',
					),
					'type' => 'google'
				),
				'Raleway' => array(
					'label' => 'Raleway',
					'variants' => array(
						'100',
						'200',
						'300',
						'regular',
						'500',
						'600',
						'700',
						'800',
						'900',
					),
					'subsets' => array(
						'latin',
					),
					'type' => 'google'
				),
				'Roboto' => array(
					'label' => 'Roboto',
					'variants' => array(
						'100',
						'100italic',
						'300',
						'300italic',
						'regular',
						'italic',
						'500',
						'500italic',
						'700',
						'700italic',
						'900',
						'900italic',
					),
					'subsets' => array(
						'latin',
						'greek-ext',
						'cyrillic',
						'greek',
						'vietnamese',
						'latin-ext',
						'cyrillic-ext',
					),
					'type' => 'google'
				),
				'Roboto Slab' => array(
					'label' => 'Roboto Slab',
					'variants' => array(
						'100',
						'300',
						'regular',
						'700',
					),
					'subsets' => array(
						'latin',
						'greek-ext',
						'cyrillic',
						'greek',
						'vietnamese',
						'latin-ext',
						'cyrillic-ext',
					),
					'type' => 'google'
				),
				'Signika' => array(
					'label' => 'Signika',
					'variants' => array(
						'300',
						'regular',
						'600',
						'700',
					),
					'subsets' => array(
						'latin',
						'latin-ext',
					),
					'type' => 'google'
				),
				'Source Sans Pro' => array(
					'label' => 'Source Sans Pro',
					'variants' => array(
						'200',
						'200italic',
						'300',
						'300italic',
						'regular',
						'italic',
						'600',
						'600italic',
						'700',
						'700italic',
						'900',
						'900italic',
					),
					'subsets' => array(
						'latin',
						'vietnamese',
						'latin-ext',
					),
					'type' => 'google'
				),
				'Titillium Web' => array(
					'label' => 'Titillium Web',
					'variants' => array(
						'200',
						'200italic',
						'300',
						'300italic',
						'regular',
						'italic',
						'600',
						'600italic',
						'700',
						'700italic',
						'900',
					),
					'subsets' => array(
						'latin',
						'latin-ext',
					),
					'type' => 'google'
				),
				'Ubuntu' => array(
					'label' => 'Ubuntu',
					'variants' => array(
						'300',
						'300italic',
						'regular',
						'italic',
						'500',
						'500italic',
						'700',
						'700italic',
					),
					'subsets' => array(
						'latin',
						'greek-ext',
						'cyrillic',
						'greek',
						'latin-ext',
						'cyrillic-ext',
					),
					'type' => 'google'
				),
				'Varela Round' => array(
					'label' => 'Varela Round',
					'variants' => array(
						'regular',
					),
					'subsets' => array(
						'latin',
					),
					'type' => 'google'
				),
				'Vollkorn' => array(
					'label' => 'Vollkorn',
					'variants' => array(
						'regular',
						'italic',
						'700',
						'700italic',
					),
					'subsets' => array(
						'latin',
					),
					'type' => 'google'
				)
			) );
		}

		/**
		 * This function returns a list of available Google Web Fonts for use in a <select> controller in the Customizer.
		 */
		public function get_google_fonts_choices( $placeholder = false, $javascript = false ) {
			// Placeholder
			if ( $placeholder )
				return apply_filters( 'get_google_fonts_choices', array( '' => __( 'Choose a Font Family', 'baton' ) ), 'placeholder', $placeholder, $javascript ); // Default

			// JavaScript (don't check cached value)
			if ( $javascript ) {
				$fonts = $this->get_google_fonts();

				// Default
				$r = array( '' => array(
					'family' => __( 'Choose a Font Family', 'baton' ),
					'class' => 'placeholder',
					// TODO: Is background URL necessary here?
					'background_url' => ''
				) );

				foreach ( $fonts as $font_family => $font ) {
					$class = sanitize_title( $font_family );
					$r[$font_family] = array(
						'family' => $font_family,
						'class' => ( $font['type'] === 'google' ) ? $class : $class . ' standard-font',
						// TODO: Is background URL necessary here?
						'background_url' => get_template_directory_uri() . '/customizer/images/' . $class .'.png'
					);
				}

				return apply_filters( 'get_google_fonts_choices', $r, 'javascript', $placeholder, $javascript );
			}

			// Check cache first
			if ( ! $r = wp_cache_get( 'google_fonts_choices', 'baton' ) ) {
				$fonts = $this->get_google_fonts();

				$r = array( '' => __( 'Choose a Font Family', 'baton' ) ); // Default

				foreach ( $fonts as $font_family => $font )
					$r[$font_family] = $font_family;

				// Store cache
				wp_cache_add( 'google_fonts_choices', $r, 'baton' );
			}

			return apply_filters( 'get_google_fonts_choices', $r, '', $placeholder, $javascript );
		}

		/**
		 * This function sanitizes a Google Web Font value.
		 *
		 * License: GPLv2 or later
		 * Copyright: Make Theme/The Theme Foundry, https://thethemefoundry.com/
		 *
		 * @link https://github.com/thethemefoundry/make/blob/1c31d2951d50cd1299493bc549d763b241c03abc/src/inc/customizer/helpers-fonts.php#L579
		 *
		 * We've used The Theme Foundry's functionality as a base and modified it to suit our needs.
		 */
		public function sanitize_google_web_font( $value, $wp_customize_setting ) {
			$fonts = $this->get_google_fonts();

			// Is this font valid?
			if ( ! array_key_exists( $value, $fonts ) )
				$value = '';

			return $value;
		}

		/**
		 * This function returns a list of default font families declared in theme support
		 */
		public function get_default_font_families() {
			$baton_customizer_typography = Baton_Customizer_Typography();

			// Check transient first
			if ( ! $baton_customizer_typography->is_customize_preview() && ! empty( $baton_customizer_typography->transient_data ) && isset( $baton_customizer_typography->transient_data['default_font_families'] ) )
				return $baton_customizer_typography->transient_data['default_font_families'];
			// Otherwise return data
			else {
				// Grab the Baton_Theme_Helper instance
				$baton_theme_helper = Baton_Theme_Helper();

				// Theme support defaults
				$families = array(
					// Site Title
					'site_title' => $baton_theme_helper->get_theme_support_value( 'fonts', 'site_title', 'font_family', 'default' ),
					// Tagline
					'tagline' => $baton_theme_helper->get_theme_support_value( 'fonts', 'tagline', 'font_family', 'default' ),
					// Navigation
					'navigation' => $baton_theme_helper->get_theme_support_value( 'fonts', 'navigation', 'font_family', 'default' ),
					// Global Headings (TODO)
					//'headings' => $baton_theme_helper->get_theme_support_value( 'fonts', 'headings', 'font_family', 'default' ),
					// Individual Headings
					'h1' => $baton_theme_helper->get_theme_support_value( 'fonts', 'headings', 'h1', 'font_family' ),
					'h2' => $baton_theme_helper->get_theme_support_value( 'fonts', 'headings', 'h2', 'font_family' ),
					'h3' => $baton_theme_helper->get_theme_support_value( 'fonts', 'headings', 'h3', 'font_family' ),
					'h4' => $baton_theme_helper->get_theme_support_value( 'fonts', 'headings', 'h4', 'font_family' ),
					'h5' => $baton_theme_helper->get_theme_support_value( 'fonts', 'headings', 'h5', 'font_family' ),
					'h6' => $baton_theme_helper->get_theme_support_value( 'fonts', 'headings', 'h6', 'font_family' ),
					// Body (content)
					'body' => $baton_theme_helper->get_theme_support_value( 'fonts', 'body', 'font_family', 'default' ),
					'widget' => $baton_theme_helper->get_theme_support_value( 'fonts', 'widget', 'font_family', 'default' )
				);

				// Widget Title
				$widget_title_support = $baton_theme_helper->get_theme_support_value( 'fonts', 'widget', 'title', 'font_family' );
				if ( isset( $widget_title_support['default'] ) )
					$families['widget_title'] = $widget_title_support['default'];

				// Individual Navigation Support
				$navigation_support = ( $baton_theme_helper->has_individual_navigation_support() ) ? $baton_theme_helper->get_individual_navigation_support() : array();
				if ( ! empty( $navigation_support ) )
					foreach( $navigation_support as $nav_menu_id => $support )
						// Determine if default exists
						if ( isset( $support['support']['font_family'] ) && isset( $support['support']['font_family']['default'] ) )
							// Add to families list
							$families[$nav_menu_id] = $support['support']['font_family']['default'];


				// Get the Individual Heading defaults
				foreach ( $families as $key => &$value )
					switch ( $key ) {
						// Individual Headings
						case 'h1':
						case 'h2':
						case 'h3':
						case 'h4':
						case 'h5':
						case 'h6':
							// Get the default
							$value = $value['default'];
						break;
					}

				return $families;
			}
		}

		/**
		 * This function checks to see if all of the theme mods are set to the default font families.
		 */
		public function has_default_font_families( $google_web_fonts_only = false ) {
			$r = true; // Default

			$baton_customizer_typography = Baton_Customizer_Typography();

			// Check transient first
			if ( ! $baton_customizer_typography->is_customize_preview() && ! empty( $baton_customizer_typography->transient_data ) && ( isset( $baton_customizer_typography->transient_data['has_default_font_families'] ) || ( $google_web_fonts_only && isset( $baton_customizer_typography->transient_data['has_default_google_web_font_families'] ) ) ) )
				return ( $google_web_fonts_only && isset( $baton_customizer_typography->transient_data['has_default_google_web_font_families'] ) ) ? $baton_customizer_typography->transient_data['has_default_google_web_font_families'] :$baton_customizer_typography->transient_data['has_default_font_families'];
			// Otherwise return data
			else {
				// Grab the Baton_Theme_Helper instance
				$baton_theme_helper = Baton_Theme_Helper();

				// Theme support defaults
				$theme_support_defaults = $this->get_default_font_families();

				// Grab fonts list
				$fonts = $this->get_google_fonts();

				// Current theme mods
				$theme_mods = array(
					// Site Title
					'site_title' => get_theme_mod( 'baton_site_title_font_family' ),
					// Tagline
					'tagline' => get_theme_mod( 'baton_tagline_font_family' ),
					// Navigation
					'navigation' => get_theme_mod( 'baton_navigation_font_family' ),
					// Global Headings (TODO)
					//'headings' => get_theme_mod( 'baton_headings_font_family' ),
					// Individual Headings
					'h1' => get_theme_mod( 'baton_h1_font_family' ),
					'h2' => get_theme_mod( 'baton_h2_font_family' ),
					'h3' => get_theme_mod( 'baton_h3_font_family' ),
					'h4' => get_theme_mod( 'baton_h4_font_family' ),
					'h5' => get_theme_mod( 'baton_h5_font_family' ),
					'h6' => get_theme_mod( 'baton_h6_font_family' ),
					// Body (content)
					'body' => get_theme_mod( 'baton_body_font_family' ),
					'widget' => get_theme_mod( 'baton_widget_font_family' ),
					'widget_title' => get_theme_mod( 'baton_widget_title_font_family' )
				);

				// Individual Navigation Support
				$navigation_support = ( $baton_theme_helper->has_individual_navigation_support() ) ? $baton_theme_helper->get_individual_navigation_support() : array();

				if ( ! empty( $navigation_support ) )
					foreach ( $navigation_support as $nav_menu_id => $support )
						// Determine if support exists
						if ( isset( $support['support']['font_family'] ) ) {
							$theme_mod = get_theme_mod( 'baton_navigation_' . $nav_menu_id . '_font_family' );

							// Grab the default value since there is no default filter added to these theme mods
							if ( ! $theme_mod )
								$theme_mod = ( isset( $support['support']['font_family']['default'] ) ) ? $support['support']['font_family']['default'] : '';

							// Add to theme mods list
							$theme_mods[$nav_menu_id] = $theme_mod;
						}

				// Remove empty values from theme support defaults and theme mods
				$theme_support_defaults = array_filter( $theme_support_defaults );
				$theme_mods = array_filter( $theme_mods );

				// Compare theme mods to defaults
				foreach ( $theme_support_defaults as $key => $value ) {
					// Check to see if the default value is different from the theme mod
					if ( ! empty( $theme_mods[$key] ) && $value !== $theme_mods[$key] && ! in_array( $theme_mods[$key], $theme_support_defaults ) )
						$r = false;

					// If we have a value that's different than the default and we're checking Google Web Fonts only
					if ( ! empty( $theme_mods[$key] ) && ! $r && $google_web_fonts_only )
						// If this is a standard font, reset the return value
						if ( $fonts[$theme_mods[$key]]['type'] !== 'google' )
							$r = true;

					// If we don't have a default at this point, we can break
					if ( ! $r )
						break;
				}

				$r = apply_filters( 'baton_has_default_font_families', $r, $google_web_fonts_only, $theme_mods, $theme_support_defaults );

				return $r;
			}
		}

		/**
		 * This function returns a URI string for loading a stylesheet with Google Web Fonts.
		 *
		 * License: GPLv2 or later
		 * Copyright: Make Theme/The Theme Foundry, https://thethemefoundry.com/
		 *
		 * @link https://github.com/thethemefoundry/make/blob/1c31d2951d50cd1299493bc549d763b241c03abc/src/inc/customizer/helpers-fonts.php#L375
		 *
		 * We've used The Theme Foundry's functionality as a base and modified it to suit our needs.
		 */
		public function get_google_web_font_stylesheet_families() {
			$baton_customizer_typography = Baton_Customizer_Typography();

			// Check transient first
			if ( ! $baton_customizer_typography->is_customize_preview() && ! empty( $baton_customizer_typography->transient_data ) && isset( $baton_customizer_typography->transient_data['google_web_font_stylesheet_families'] ) )
				return $baton_customizer_typography->transient_data['google_web_font_stylesheet_families'];
			// Otherwise return data
			else {
				// Grab the Baton_Theme_Helper instance
				$baton_theme_helper = Baton_Theme_Helper();

				$google_web_fonts = $this->get_google_fonts();
				$families = array();

				// Theme support defaults
				$theme_support_defaults = $this->get_default_font_families();

				// Build a base list of fonts
				$fonts = array(
					'site_title' => get_theme_mod( 'baton_site_title_font_family' ),
					'tagline' => get_theme_mod( 'baton_tagline_font_family' ),
					'navigation' => get_theme_mod( 'baton_navigation_font_family' ),
					// Global Headings (TODO)
					//'headings' => get_theme_mod( 'baton_headings_font_family' ),
					// Individual Headings
					'h1' => get_theme_mod( 'baton_h1_font_family' ),
					'h2' => get_theme_mod( 'baton_h2_font_family' ),
					'h3' => get_theme_mod( 'baton_h3_font_family' ),
					'h4' => get_theme_mod( 'baton_h4_font_family' ),
					'h5' => get_theme_mod( 'baton_h5_font_family' ),
					'h6' => get_theme_mod( 'baton_h6_font_family' ),
					'body' => get_theme_mod( 'baton_body_font_family' ),
					'widget' => get_theme_mod( 'baton_widget_font_family' ),
					'widget_title' => get_theme_mod( 'baton_widget_title_font_family' )
				);

				// Individual Navigation Support
				$navigation_support = ( $baton_theme_helper->has_individual_navigation_support() ) ? $baton_theme_helper->get_individual_navigation_support() : array();
				if ( ! empty( $navigation_support ) )
					foreach( $navigation_support as $nav_menu_id => $support )
						// Determine if support exists
						if ( isset( $support['support']['font_family'] ) )
							// Add to theme mods list
							$fonts[$nav_menu_id] = get_theme_mod( 'baton_navigation_' . $nav_menu_id . '_font_family' );

				// Re-build the list of fonts (no duplicates; no array indexes; no empties)
				$fonts = array_values( array_unique( array_filter( $fonts ) ) );

				// Loop through each font
				foreach ( $fonts as $font ) {
					// Ignore defaults
					if ( ! in_array( $font, $theme_support_defaults ) ) {
						$font = trim( $font ); // Trim whitespace

						// Does the font exist within our data set?
						if ( array_key_exists( $font, $google_web_fonts ) && $google_web_fonts[$font]['type'] === 'google' )
							// Build the family name and variant string (e.g., "Open+Sans:regular,italic,700")
							$families[] = urlencode( $font . ':' . join( ',', $this->get_google_web_font_variation( $font, $google_web_fonts[$font]['variants'] ) ) );
					}
				}

				return implode( '|', apply_filters( 'baton_google_web_font_stylesheet_families', $families, $this ) );
			}
		}

		/**
		 * This function builds a Google Web Font variation string based on parameters.
		 *
		 * License: GPLv2 or later
		 * Copyright: Make Theme/The Theme Foundry, https://thethemefoundry.com/
		 *
		 * @link https://github.com/thethemefoundry/make/blob/1c31d2951d50cd1299493bc549d763b241c03abc/src/inc/customizer/helpers-fonts.php#L474
		 *
		 * We've used The Theme Foundry's functionality as a base and modified it to suit our needs.
		 */
		public function get_google_web_font_variation( $font, $variants ) {
			$variation = array();

			// If a "regular" variant is not found, get the first variant
			if ( ! in_array( 'regular', $variants ) )
				$variation[] = $variants[0];
			else
				$variation[] = 'regular';


			// Only add "italic" if it exists
			if ( in_array( 'italic', $variants ) )
				$variation[] = 'italic';

			// Only add "700" if it exists
			if ( in_array( '700', $variants ) )
				$variation[] = '700';

			return $variation;
		}
	}

	/**
	 * Create an instance of the Baton_Customizer_Fonts class.
	 */
	function Baton_Customizer_Fonts() {
		return Baton_Customizer_Fonts::instance();
	}

	Baton_Customizer_Fonts();
}