<?php
/**
 * Baton Customizer Conductor Typography
 */

// Bail if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

if ( ! class_exists( 'Baton_Customizer_Conductor_Typography' ) ) {
	final class Baton_Customizer_Conductor_Typography {
		/**
		 * @var string
		 */
		public $version = '1.0.0';

		/**
		 * @var string, Transient name
		 */
		public $transient_name = 'baton_conductor_customizer_';

		/**
		 * @var array, Transient data
		 */
		public $transient_data = array();

		/**
		 * @var Baton_Customizer_Conductor_Typography, Instance of the class
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
			add_action( 'after_setup_theme', array( $this, 'after_setup_theme' ), 9999 ); // After Setup Theme (late; load assets based on theme support)
			add_action( 'after_switch_theme', array( $this, 'reset_transient' ), 9999 ); // After Switch Theme (late)
			add_filter( 'baton_has_google_web_font_support', array( $this, 'baton_has_google_web_font_support' ) );
			add_filter( 'baton_has_default_font_families', array( $this, 'baton_has_default_font_families' ) );
			add_filter( 'baton_google_web_font_stylesheet_families', array( $this, 'baton_google_web_font_stylesheet_families' ) );
			add_action( 'wp_head', array( $this, 'wp_head' ) ); // Output Customizer CSS
			add_action( 'customize_save_after', array( $this, 'reset_transient' ) ); // Customize Save (reset transients)
		}

		/**
		 * Include required core files used in admin and on the frontend.
		 */
		private function includes() {
			include_once 'class-baton-customizer-conductor-fonts.php'; // Customizer Conductor Font Settings/Controls
		}

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
		 * This function checks Conductor theme support for font family support.
		 */
		public function baton_has_google_web_font_support( $font_support ) {
			$baton_theme_helper = Baton_Theme_Helper(); // Grab the Baton_Theme_Helper instance

			// Check for Conductor theme support
			if ( $baton_theme_helper->current_theme_supports( 'fonts', 'conductor' ) ) {
				// Conductor font support
				$conductor_support = $baton_theme_helper->get_theme_support_value( 'fonts', 'conductor' );
				$conductor_widget_display_support = array_keys( ( array ) $conductor_support );

				// Loop through the different Conductor Widget Displays
				if ( ! empty( $conductor_widget_display_support ) )
					foreach ( $conductor_widget_display_support as $display ) {
						// Individual widget display support
						$widget_display_support = $conductor_support[$display];

						// Loop through support
						foreach ( $widget_display_support as $support_id => $support )
							// Ignoring the labels
							if ( $support_id !== 'labels' )
								// Loop through the different support types
								foreach ( $support as $support_type => $support_value )
									// Font Family only
									if ( $support_type === 'font_family' ) {
										$font_support = true;
										break 3; // break out of all loops
									}
					}
			}

			return $font_support;
		}

		/**
		 * This function checks to see if default font families are set for Conductor customizer settings.
		 */
		public function baton_has_default_font_families( $has_default ) {
			$baton_theme_helper = Baton_Theme_Helper(); // Grab the Baton_Theme_Helper instance

			// Check for Conductor theme support
			if ( $baton_theme_helper->current_theme_supports( 'fonts', 'conductor' ) ) {
				// Conductor font support
				$conductor_support = $baton_theme_helper->get_theme_support_value( 'fonts', 'conductor' );
				$conductor_widget_display_support = array_keys( ( array ) $conductor_support );

				// Loop through the different Conductor Widget Displays
				if ( ! empty( $conductor_widget_display_support ) )
					foreach ( $conductor_widget_display_support as $display ) {
						// Individual widget display support
						$widget_display_support = $conductor_support[$display];

						// Loop through support
						foreach ( $widget_display_support as $support_id => $support )
							// Ignoring the labels
							if ( $support_id !== 'labels' )
								// Loop through the different support types
								foreach ( $support as $support_type => $support_value )
									// Ignoring the labels and CSS
									if ( $support_type === 'font_family' ) {
										$theme_mod = get_theme_mod( 'baton_conductor_' . $display . '_' . $support_id . '_' . $support_type );

										// Compare current theme mod against the default
										if ( $theme_mod && $theme_mod !== $support_value['default'] ) {
											$has_default = false;
											break 3; // break out of all loops
										}
									}
					}
			}

			return $has_default;
		}

		/**
		 * This function loads Google Web Fonts based on Conductor font settings.
		 */
		public function baton_google_web_font_stylesheet_families( $families ) {
			$baton_theme_helper = Baton_Theme_Helper(); // Grab the Baton_Theme_Helper instance

			// Check for Conductor theme support
			if ( $baton_theme_helper->current_theme_supports( 'fonts', 'conductor' ) ) {
				$baton_customizer_fonts = Baton_Customizer_Fonts();
				$google_web_fonts = $baton_customizer_fonts->get_google_fonts();

				// Array of Conductor Customizer font family theme mods
				$fonts = array();

				// Conductor font support
				$conductor_support = $baton_theme_helper->get_theme_support_value( 'fonts', 'conductor' );
				$conductor_widget_display_support = array_keys( ( array ) $conductor_support );

				// Loop through the different Conductor Widget Displays
				if ( ! empty( $conductor_widget_display_support ) )
					foreach ( $conductor_widget_display_support as $display ) {
						// Individual widget display support
						$widget_display_support = $conductor_support[$display];

						// Loop through support
						foreach ( $widget_display_support as $support_id => $support )
							// Ignoring the labels
							if ( $support_id !== 'labels' )
								// Loop through the different support types
								foreach ( $support as $support_type => $support_value )
									// Font Family only
									if ( $support_type === 'font_family' ) {
										// Get the theme mod
										$theme_mod = get_theme_mod( 'baton_conductor_' . $display . '_' . $support_id . '_' . $support_type );

										// Compare current theme mod against the default
										if ( $theme_mod && $theme_mod !== $support_value['default'] )
											$fonts[] = str_replace( '\'', '', $theme_mod );
									}
					}

				// If we have font families
				if ( ! empty( $fonts ) ) {
					// Build a list of fonts (no duplicates; no array indexes; no empties)
					$fonts = array_values( array_unique( array_filter( $fonts ) ) );

					// Loop through each font
					foreach ( $fonts as $font ) {
						$font = trim( $font ); // Trim whitespace

						// Does the font exist within our data set?
						if ( array_key_exists( $font, $google_web_fonts ) && $google_web_fonts[$font]['type'] === 'google' )
							// Build the family name and variant string (e.g., "Open+Sans:regular,italic,700")
							$families[] = urlencode( $font . ':' . join( ',', $baton_customizer_fonts->get_google_web_font_variation( $font, $google_web_fonts[ $font ]['variants'] ) ) );
					}
				}
			}

			return $families;
		}

		/**
		 * This function outputs all CSS associated with Customizer settings within this plugin.
		 */
		public function wp_head() {
			$baton_theme_helper = Baton_Theme_Helper(); // Grab the Baton_Theme_Helper instance

			// Bail if no support defined by theme
			if ( ! $baton_theme_helper->theme_support || ! is_array( $baton_theme_helper->theme_support ) || empty( $baton_theme_helper->theme_support ) )
				return;

			echo $this->get_customizer_css();
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
		 * This function returns a CSS <style> block for Customizer theme mods.
		 */
		public function get_customizer_css() {
			// Check transient first (not in the Customizer)
			if ( ! $this->is_customize_preview() && ! empty( $this->transient_data ) && isset( $this->transient_data['customizer_css' ] ) )
				return $this->transient_data['customizer_css'];
			// Otherwise return data
			else {
				$baton_theme_helper = Baton_Theme_Helper(); // Grab the Baton_Theme_Helper instance
				$baton_customizer_typography = Baton_Customizer_Typography();

				// Open <style>
				$r = '<style type="text/css" id="baton-' . $baton_theme_helper->theme->get_template() . '-theme-conductor-customizer">' . "\n";

				// Check for Conductor theme support
				if ( $baton_theme_helper->current_theme_supports( 'fonts', 'conductor' ) ) {
					// Conductor font support
					$conductor_support = $baton_theme_helper->get_theme_support_value( 'fonts', 'conductor' );
					$conductor_widget_display_support = array_keys( ( array ) $conductor_support );

					// Loop through the different Conductor Widget Displays
					if ( ! empty( $conductor_widget_display_support ) )
						foreach ( $conductor_widget_display_support as $display ) {
							// Individual widget display support
							$widget_display_support = $conductor_support[$display];

							// Loop through support
							foreach ( $widget_display_support as $support_id => $support ) {
								// Ignoring the labels
								if ( $support_id !== 'labels' ) {
									// Loop through the different support types
									foreach ( $support as $support_type => $support_value ) {
										// Ignoring the labels and CSS
										if ( $support_type !== 'labels' && $support_type !== 'css' ) {
											$theme_mod = get_theme_mod( 'baton_conductor_' . $display . '_' . $support_id . '_' . $support_type );

											// Compare current theme mod against the default
											if ( $theme_mod && $theme_mod !== $support_value['default'] ) {
												$css_properties = array();

												// Support Type
												switch ( $support_type ) {
													// Font Size
													case 'font_size':
														$css_properties = array(
															'selector' => array(), // CSS Selector
															'properties' => array(
																'font-size' => 'px'
															)
														);
													break;
													// Font Family
													case 'font_family':
														$css_properties = array(
															'selector' => array(), // CSS Selector
															'properties' => array(
																'font-family' => ''
															)
														);
													break;
												}

												// Check if the theme has CSS properties defined and merge them if necessary
												if ( isset( $support['css'] ) && isset( $support_value['css'] ) )
													$theme_mod_css_properties = array_filter( array_merge_recursive( ( array ) $support['css'], ( array ) $support_value['css'] ) );
												else if ( ! isset( $support['css'] ) && isset( $support_value['css'] ) )
													$theme_mod_css_properties = $support_value['css'];
												else if ( isset( $support['css'] ) && ! isset( $support_value['css'] ) )
													$theme_mod_css_properties = $support['css'];
												else
													$theme_mod_css_properties = array();

												if ( $theme_mod_css_properties )
													$css_properties = array_merge_recursive( $css_properties, $theme_mod_css_properties );

												// Remove duplicates from CSS properties
												array_walk( $css_properties, array( $baton_customizer_typography, 'remove_duplicate_array_values' ) );

												// Output the CSS selector, properties, value, and units
												$r .= $baton_customizer_typography->get_customizer_setting_css( $css_properties, $theme_mod );
											}
										}
									}
								}
							}
						}
				}

				// Close </style>
				$r .= '</style>';

				return $r;
			}
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

			// Bail if no support defined by theme
			if ( ! $baton_theme_helper->theme_support || ! is_array( $baton_theme_helper->theme_support ) || empty( $baton_theme_helper->theme_support ) )
				return $data;

			// Add the Customizer CSS
			$data['customizer_css'] = $this->get_customizer_css();

			// Add the theme's version
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
			$baton_customizer = Baton_Customizer_Instance();
			$is_wp_4 = $baton_customizer->version_compare( '4.0' );

			// Less than 4.0
			if ( ! $is_wp_4 ) {
				global $wp_customize;

				return is_a( $wp_customize, 'WP_Customize_Manager' ) && $wp_customize->is_preview();
			}
			// 4.0 or greater
			else
				return is_customize_preview();
		}
	}

	/**
	 * Create an instance of the Baton_Customizer_Conductor_Typography class.
	 */
	function Baton_Customizer_Conductor_Typography() {
		return Baton_Customizer_Conductor_Typography::instance();
	}

	Baton_Customizer_Conductor_Typography();
}