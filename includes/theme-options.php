<?php

// Bail if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;


/**
 * SDS Theme Options
 *
 * Description: This Class instantiates the SDS Options Panel providing themes with various options to use.
 *
 * @version 1.4.2
 */
if ( ! class_exists( 'SDS_Theme_Options' ) ) {
	global $sds_theme_options;

	class SDS_Theme_Options {
		/**
		 * @var string, Constant, Version of the class
		 */
		const VERSION = '1.4.2';


		// Private Variables

		/**
		 * @var SDS_Theme_Options, Instance of the class
		 */
		private static $instance; // Keep track of the instance


		// Public Variables

		/**
		 * @var string, Option name
		 */
		public static $option_name = 'sds_theme_options';

		/**
		 * @var array, Array of option defaults
		 */
		public $option_defaults = array();

		/**
		 * @var WP_Theme, Current theme object
		 */
		public $theme;

		/**
		 * @var string, URL
		 */
		public static $update_url = 'https://slocumthemes.com/';

		/*
		 * Function used to create instance of class.
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) )
				self::$instance = new SDS_Theme_Options;

			return self::$instance;
		}

		/**
		 * These functions calls and hooks are added on new instance.
		 */
		function __construct() {
			$this->option_defaults = $this->get_sds_theme_option_defaults();
			$this->theme = $this->get_parent_theme();

			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) ); // Enqueue Theme Options Stylesheet
			add_action( 'admin_menu', array( $this, 'admin_menu' ) ); // Register Appearance Menu Item
			add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ), 999 ); // Add Theme Options Menu to Toolbar
			add_action( 'admin_init', array( $this, 'admin_init' ) ); // Register Settings, Settings Sections, and Settings Fields
			add_filter( 'wp_redirect', array( $this, 'wp_redirect' ) ); // Add "hash" (tab) to URL before re-direct
		}


		/**
		 * This function enqueues our theme options stylesheet, WordPress media upload scripts, and our custom upload script only on our options page in admin.
		 */
		function admin_enqueue_scripts( $hook ) {
			if ( $hook === 'appearance_page_sds-theme-options' ) {
				$protocol = is_ssl() ? 'https' : 'http';

				wp_enqueue_style( 'sds-theme-options', SDS_Theme_Options::sds_core_url() . '/css/sds-theme-options.css', false, self::VERSION );

				wp_enqueue_media(); // Enqueue media scripts
				wp_enqueue_script( 'sds-theme-options', SDS_Theme_Options::sds_core_url() . '/js/sds-theme-options.js', array( 'jquery' ), self::VERSION );

				// Web Fonts
				if ( function_exists( 'sds_web_fonts' ) ) {
					$google_families = $this->get_google_font_families_list();

					wp_enqueue_style( 'google-web-fonts', $protocol . '://fonts.googleapis.com/css?family=' . $google_families, false, self::VERSION );
				}
			}
		}

		/**
		 * This function adds a menu item under "Appearance" in the Dashboard.
		 */
		function admin_menu() {
			add_theme_page( __( 'Theme Options', 'baton' ), __( 'Theme Options', 'baton' ), 'edit_theme_options', 'sds-theme-options', array( $this, 'sds_theme_options_page' ) );
		}

		/**
		 * This function adds a new menu to the Toolbar under the appearance parent group on the front-end.
		 */
		function admin_bar_menu( $wp_admin_bar ) {
			// Make sure we're on the front end and that the current user can either switch_themes or edit_theme_options
			if ( ! is_admin() && ( current_user_can( 'switch_themes' ) || current_user_can( 'edit_theme_options' ) ) ) 
				$wp_admin_bar->add_menu( array(
					'parent' => 'appearance',
					'id'  => 'sds-theme-options',
					'title' => __( 'Theme Options', 'baton' ),
					'href' => admin_url( 'themes.php?page=sds-theme-options' ),
					'meta' => array(
						'class' => 'sds-theme-options'
					)
				) );
		}

		/**
		 * This function registers our setting, settings sections, and settings fields.
		 */
		function admin_init() {
			// Register Setting
			register_setting( self::$option_name, self::$option_name, array( $this, 'sds_theme_options_sanitize' ) );


			/*
			 * General Settings (belong to the sds-theme-options[general] "page", used during page render to display section in tab format)
			 */

			// Logo
			add_settings_section( 'sds_theme_options_logo_section', __( 'Upload A Logo', 'baton' ), array( $this, 'sds_theme_options_logo_section' ), 'sds-theme-options[general]' );
			add_settings_field( 'sds_theme_options_logo_field', __( 'Logo:', 'baton' ), array( $this, 'sds_theme_options_logo_field' ), 'sds-theme-options[general]', 'sds_theme_options_logo_section' );

			// Show/Hide Elements
			add_settings_section( 'sds_theme_options_show_hide_elements_section', __( 'Show or Hide Elements', 'baton' ), array( $this, 'sds_theme_options_show_hide_elements_section' ), 'sds-theme-options[general]' );

			// Hide Tagline
			//add_settings_section( 'sds_theme_options_hide_tagline_section', __( 'Show/Hide Site Tagline', 'baton' ), array( $this, 'sds_theme_options_hide_tagline_section' ), 'sds-theme-options[general]' );
			add_settings_field( 'sds_theme_options_hide_tagline_field', __( 'Show or Hide Site Tagline:', 'baton' ), array( $this, 'sds_theme_options_hide_tagline_field' ), 'sds-theme-options[general]', 'sds_theme_options_show_hide_elements_section' );

			// Hide Archive Titles
			//add_settings_section( 'sds_theme_options_hide_archive_titles_section', __( 'Show/Hide Archive Titles', 'baton' ), array( $this, 'sds_theme_options_hide_archive_titles_section' ), 'sds-theme-options[general]' );
			add_settings_field( 'sds_theme_options_hide_archive_titles_field', __( 'Show or Hide Archive Titles:', 'baton' ), array( $this, 'sds_theme_options_hide_archive_titles_field' ), 'sds-theme-options[general]', 'sds_theme_options_show_hide_elements_section' );

			// Hide Post Meta
			//add_settings_section( 'sds_theme_options_hide_post_meta_section', __( 'Show/Hide Post Meta', 'baton' ), array( $this, 'sds_theme_options_hide_post_meta_section' ), 'sds-theme-options[general]' );
			add_settings_field( 'sds_theme_options_hide_post_meta_field', __( 'Show or Hide Post Meta:', 'baton' ), array( $this, 'sds_theme_options_hide_post_meta_field' ), 'sds-theme-options[general]', 'sds_theme_options_show_hide_elements_section' );

			// Hide Author Meta
			//add_settings_section( 'sds_theme_options_hide_author_meta_section', __( 'Show/Hide Author Details', 'baton' ), array( $this, 'sds_theme_options_hide_author_meta_section' ), 'sds-theme-options[general]' );
			add_settings_field( 'sds_theme_options_hide_author_meta_field', __( 'Show or Hide Author Details:', 'baton' ), array( $this, 'sds_theme_options_hide_author_meta_field' ), 'sds-theme-options[general]', 'sds_theme_options_show_hide_elements_section' );

			// Color Schemes (if specified by theme)
			if ( function_exists( 'sds_color_schemes' ) ) {
				add_settings_section( 'sds_theme_options_color_schemes_section', __( 'Color Scheme', 'baton' ), array( $this, 'sds_theme_options_color_schemes_section' ), 'sds-theme-options[general]' );
				add_settings_field( 'sds_theme_options_color_schemes_field', __( 'Select A Color Scheme:', 'baton' ), array( $this, 'sds_theme_options_color_schemes_field' ), 'sds-theme-options[general]', 'sds_theme_options_color_schemes_section' );
			}

			// Google Web Fonts (if specified by theme)
			if ( function_exists( 'sds_web_fonts' ) ) {
				add_settings_section( 'sds_theme_options_web_fonts_section', __( 'Web Fonts', 'baton' ), array( $this, 'sds_theme_options_web_fonts_section' ), 'sds-theme-options[general]' );
				add_settings_field( 'sds_theme_options_web_fonts_field', __( 'Select A Web Font:', 'baton' ), array( $this, 'sds_theme_options_web_fonts_field' ), 'sds-theme-options[general]', 'sds_theme_options_web_fonts_section' );
			}

			// Featured Image Size
			add_settings_section( 'sds_theme_options_featured_image_size_section', __( 'Featured Image Size', 'baton' ), array( $this, 'sds_theme_options_featured_image_size_section' ), 'sds-theme-options[general]' );
			add_settings_field( 'sds_theme_options_featured_image_size_field', __( 'Featured Image Size:', 'baton' ), array( $this, 'sds_theme_options_featured_image_size_field' ), 'sds-theme-options[general]', 'sds_theme_options_featured_image_size_section' );


			// Footer Branding (Slug)
			add_settings_section( 'sds_theme_options_footer_branding_section', __( 'Footer Branding', 'baton' ), array( $this, 'sds_theme_options_footer_branding_section' ), 'sds-theme-options[general]' );
			add_settings_field( 'sds_theme_options_footer_copyright_field', __( 'Footer Copyright:', 'baton' ), array( $this, 'sds_theme_options_footer_copyright_field' ), 'sds-theme-options[general]', 'sds_theme_options_footer_branding_section' );
			add_settings_field( 'sds_theme_options_footer_branding_field', __( 'Footer Branding:', 'baton' ), array( $this, 'sds_theme_options_footer_branding_field' ), 'sds-theme-options[general]', 'sds_theme_options_footer_branding_section' );

			// Hide Footer Branding (Slug)
			add_settings_field( 'sds_theme_options_hide_footer_branding_field', __( 'Show or Hide Footer Branding:', 'baton' ), array( $this, 'sds_theme_options_hide_footer_branding_field' ), 'sds-theme-options[general]', 'sds_theme_options_footer_branding_section' );


			/*
			 * Content Layout Settings (belong to the sds-theme-options[content-layout] "page", used during page render to display section in tab format)
			 */

			if ( function_exists( 'sds_content_layouts' ) ) {
				add_settings_section( 'sds_theme_options_content_layout_section', __( 'Content Layout', 'baton' ), array( $this, 'sds_theme_options_content_layout_section' ), 'sds-theme-options[content-layout]' );
				add_settings_field( 'sds_theme_options_content_layout_global_field', __( 'Global', 'baton' ), array( $this, 'sds_theme_options_content_layout_global_field' ), 'sds-theme-options[content-layout]', 'sds_theme_options_content_layout_section' );
				add_settings_field( 'sds_theme_options_content_layout_front_page_field', __( 'Front Page', 'baton' ), array( $this, 'sds_theme_options_content_layout_front_page_field' ), 'sds-theme-options[content-layout]', 'sds_theme_options_content_layout_section' );
				add_settings_field( 'sds_theme_options_content_layout_home_field', __( 'Home (Blog)', 'baton' ), array( $this, 'sds_theme_options_content_layout_home_field' ), 'sds-theme-options[content-layout]', 'sds_theme_options_content_layout_section' );
				add_settings_field( 'sds_theme_options_content_layout_single_field', __( 'Single Post', 'baton' ), array( $this, 'sds_theme_options_content_layout_single_field' ), 'sds-theme-options[content-layout]', 'sds_theme_options_content_layout_section' );
				add_settings_field( 'sds_theme_options_content_layout_page_field', __( 'Single Page', 'baton' ), array( $this, 'sds_theme_options_content_layout_page_field' ), 'sds-theme-options[content-layout]', 'sds_theme_options_content_layout_section' );
				add_settings_field( 'sds_theme_options_content_layout_archive_field', __( 'Archive', 'baton' ), array( $this, 'sds_theme_options_content_layout_archive_field' ), 'sds-theme-options[content-layout]', 'sds_theme_options_content_layout_section' );
				add_settings_field( 'sds_theme_options_content_layout_category_field', __( 'Category', 'baton' ), array( $this, 'sds_theme_options_content_layout_category_field' ), 'sds-theme-options[content-layout]', 'sds_theme_options_content_layout_section' );
				add_settings_field( 'sds_theme_options_content_layout_tag_field', __( 'Tag', 'baton' ), array( $this, 'sds_theme_options_content_layout_tag_field' ), 'sds-theme-options[content-layout]', 'sds_theme_options_content_layout_section' );
				add_settings_field( 'sds_theme_options_content_layout_404_field', __( '404 Error', 'baton' ), array( $this, 'sds_theme_options_content_layout_404_field' ), 'sds-theme-options[content-layout]', 'sds_theme_options_content_layout_section' );
			}

			/*
			 * Social Media Settings (belong to the sds-theme-options[social-media] "page", used during page render to display section in tab format)
			 */

 			add_settings_section( 'sds_theme_options_social_media_section', __( 'Social Media', 'baton' ), array( $this, 'sds_theme_options_social_media_section' ), 'sds-theme-options[social-media]' );
			add_settings_field( 'sds_theme_options_social_media_facebook_url_field', __( 'Facebook:', 'baton' ), array( $this, 'sds_theme_options_social_media_facebook_url_field' ), 'sds-theme-options[social-media]', 'sds_theme_options_social_media_section' );
			add_settings_field( 'sds_theme_options_social_media_twitter_url_field', __( 'Twitter:', 'baton' ), array( $this, 'sds_theme_options_social_media_twitter_url_field' ), 'sds-theme-options[social-media]', 'sds_theme_options_social_media_section' );
			add_settings_field( 'sds_theme_options_social_media_linkedin_url_field', __( 'LinkedIn:', 'baton' ), array( $this, 'sds_theme_options_social_media_linkedin_url_field' ), 'sds-theme-options[social-media]', 'sds_theme_options_social_media_section' );
			add_settings_field( 'sds_theme_options_social_media_google_plus_url_field', __( 'Google+:', 'baton' ), array( $this, 'sds_theme_options_social_media_google_plus_url_field' ), 'sds-theme-options[social-media]', 'sds_theme_options_social_media_section' );
			add_settings_field( 'sds_theme_options_social_media_youtube_url_field', __( 'YouTube:', 'baton' ), array( $this, 'sds_theme_options_social_media_youtube_url_field' ), 'sds-theme-options[social-media]', 'sds_theme_options_social_media_section' );
			add_settings_field( 'sds_theme_options_social_media_vimeo_url_field', __( 'Vimeo:', 'baton' ), array( $this, 'sds_theme_options_social_media_vimeo_url_field' ), 'sds-theme-options[social-media]', 'sds_theme_options_social_media_section' );
			add_settings_field( 'sds_theme_options_social_media_instagram_url_field', __( 'Instagram:', 'baton' ), array( $this, 'sds_theme_options_social_media_instagram_url_field' ), 'sds-theme-options[social-media]', 'sds_theme_options_social_media_section' );
			add_settings_field( 'sds_theme_options_social_media_pinterest_url_field', __( 'Pinterest:', 'baton' ), array( $this, 'sds_theme_options_social_media_pinterest_url_field' ), 'sds-theme-options[social-media]', 'sds_theme_options_social_media_section' );
			add_settings_field( 'sds_theme_options_social_media_flickr_url_field', __( 'Flickr:', 'baton' ), array( $this, 'sds_theme_options_social_media_flickr_url_field' ), 'sds-theme-options[social-media]', 'sds_theme_options_social_media_section' );
			//add_settings_field( 'sds_theme_options_social_media_yelp_url_field', __( 'Yelp:', 'baton' ), array( $this, 'sds_theme_options_social_media_yelp_url_field' ), 'sds-theme-options[social-media]', 'sds_theme_options_social_media_section' );
			add_settings_field( 'sds_theme_options_social_media_foursquare_url_field', __( 'Foursquare:', 'baton' ), array( $this, 'sds_theme_options_social_media_foursquare_url_field' ), 'sds-theme-options[social-media]', 'sds_theme_options_social_media_section' );
			add_settings_field( 'sds_theme_options_social_media_rss_url_field', __( 'RSS:', 'baton' ), array( $this, 'sds_theme_options_social_media_rss_url_field' ), 'sds-theme-options[social-media]', 'sds_theme_options_social_media_section' );


			/*
			 * Custom Scripts/Styles Settings (belong to the sds-theme-options[custom-scripts-styles] "page", used during page render to display section in tab format)
			 */

			// Custom Scripts
 			add_settings_section( 'sds_theme_options_custom_scripts_section', __( 'Custom Scripts', 'baton' ), array( $this, 'sds_theme_options_custom_scripts_section' ), 'sds-theme-options[custom-scripts-styles]' );
			add_settings_field( 'sds_theme_options_custom_scripts_head_field', __( 'Head:', 'baton' ), array( $this, 'sds_theme_options_custom_scripts_head_field' ), 'sds-theme-options[custom-scripts-styles]', 'sds_theme_options_custom_scripts_section' );
			add_settings_field( 'sds_theme_options_custom_scripts_footer_field', __( 'Footer:', 'baton' ), array( $this, 'sds_theme_options_custom_scripts_footer_field' ), 'sds-theme-options[custom-scripts-styles]', 'sds_theme_options_custom_scripts_section' );

			// Custom Styles
			add_settings_section( 'sds_theme_options_custom_styles_section', __( 'Custom Styles', 'baton' ), array( $this, 'sds_theme_options_custom_styles_section' ), 'sds-theme-options[custom-scripts-styles]' );
			add_settings_field( 'sds_theme_options_custom_styles_field', __( 'Custom CSS:', 'baton' ), array( $this, 'sds_theme_options_custom_styles_field' ), 'sds-theme-options[custom-scripts-styles]', 'sds_theme_options_custom_styles_section' );


			/*
			 * License Settings (belong to the sds-theme-options[license] "page", used during page render to display section in tab format)
			 */

			add_settings_section( 'sds_theme_options_license_section', __( 'License', 'baton' ), array( $this, 'sds_theme_options_license_section' ), 'sds-theme-options[license]' );
			add_settings_field( 'sds_theme_options_license_key_field', __( 'License Key:', 'baton' ), array( $this, 'sds_theme_options_license_key_field' ), 'sds-theme-options[license]', 'sds_theme_options_license_section' );
		}

		/**
		 * This function is the callback for the logo settings section.
		 */
		function sds_theme_options_logo_section() {
		?>
			<p>
				<?php
					$sds_logo_dimensions = apply_filters( 'sds_theme_options_logo_dimensions', '300x100' );
					printf( __( 'Upload a logo to to replace the site name. Recommended dimensions: %1$s.', 'baton' ), $sds_logo_dimensions );
				?>
			</p>
		<?php
		}

		/**
		 * This function is the callback for the logo settings field.
		 */
		function sds_theme_options_logo_field( $customizer = false ) {
			global $sds_theme_options;

			// Output logo dimensions on Customizer
			if ( $customizer ) :
		?>
				<p>
					<?php
						$sds_logo_dimensions = apply_filters( 'sds_theme_options_logo_dimensions', '300x100' );
						printf( __( 'Upload a logo to to replace the site name. Recommended dimensions: %1$s.', 'baton' ), $sds_logo_dimensions );
					?>
				</p>
		<?php
			endif;
		?>

			<strong><?php _e( 'Current Logo:', 'baton' ); ?></strong>
			<div class="sds-theme-options-preview sds-theme-options-logo-preview">
				<?php
					if ( isset( $sds_theme_options['logo_attachment_id'] ) && $sds_theme_options['logo_attachment_id'] ) :
						echo wp_get_attachment_image( $sds_theme_options['logo_attachment_id'], 'full' );
					else :
				?>
						<div class="description"><?php _e( 'No logo selected.', 'baton' ); ?></div>
				<?php endif; ?>
			</div>

			<input type="hidden" id="sds_theme_options_logo" class="sds-theme-options-upload-value" name="sds_theme_options[logo_attachment_id]"  value="<?php echo ( isset( $sds_theme_options['logo_attachment_id'] ) && ! empty( $sds_theme_options['logo_attachment_id'] ) ) ? esc_attr( $sds_theme_options['logo_attachment_id'] ) : false; ?>" />
			<input type="submit" id="sds_theme_options_logo_attach" class="button-primary sds-theme-options-upload" name="sds_theme_options_logo_attach"  value="<?php esc_attr_e( 'Choose Logo', 'baton' ); ?>" data-media-title="Choose A Logo" data-media-button-text="Use As Logo" />
			<?php submit_button( __( 'Remove Logo', 'baton' ), array( 'secondary', 'button-remove-logo' ), 'sds_theme_options[remove-logo]', false, ( ! isset( $sds_theme_options['logo_attachment_id'] ) || empty( $sds_theme_options['logo_attachment_id'] ) ) ? array( 'disabled' => 'disabled', 'data-init-empty' => 'true' ) : false ); ?>
		<?php
		}

		
		/**
		 * This function is the callback for the show or hide elements settings section.
		 */
		function sds_theme_options_show_hide_elements_section() {
		?>
			<p><?php _e( 'Use this option to show or hide various elements on your site.', 'baton' ); ?></p>
		<?php
		}

		/**
		 * This function is the callback for the show/hide tagline settings field.
		 */
		function sds_theme_options_hide_tagline_field() {
			global $sds_theme_options;
		?>
			<div class="checkbox sds-theme-options-checkbox checkbox-show-hide checkbox-show-hide-tagline" data-label-left="<?php esc_attr_e( 'Show', 'baton' ); ?>" data-label-right="<?php esc_attr_e( 'Hide', 'baton' ); ?>">
				<input type="checkbox" id="sds_theme_options_hide_tagline" name="sds_theme_options[hide_tagline]" <?php ( isset( $sds_theme_options['hide_tagline'] ) ) ? checked( $sds_theme_options['hide_tagline'] ) : checked( false ); ?> />
				<label for="sds_theme_options_hide_tagline">| | |</label>
			</div>
			<span class="description"><?php _e( 'When "show" is displayed, the tagline will be displayed on your site and vise-versa.', 'baton' ); ?></span>
		<?php
		}

		/**
		 * This function is the callback for the show/hide archive titles settings field.
		 */
		function sds_theme_options_hide_archive_titles_field() {
			global $sds_theme_options;
		?>
			<div class="checkbox sds-theme-options-checkbox checkbox-show-hide checkbox-show-hide-archive-titles" data-label-left="<?php esc_attr_e( 'Show', 'baton' ); ?>" data-label-right="<?php esc_attr_e( 'Hide', 'baton' ); ?>">
				<input type="checkbox" id="sds_theme_options_hide_archive_titles" name="sds_theme_options[hide_archive_titles]" <?php ( isset( $sds_theme_options['hide_archive_titles'] ) ) ? checked( $sds_theme_options['hide_archive_titles'] ) : checked( false ); ?> />
				<label for="sds_theme_options_hide_archive_titles">| | |</label>
			</div>
			<span class="description"><?php _e( 'When "show" is displayed, the archive titles will be displayed on your site and vise-versa.', 'baton' ); ?></span>
		<?php
		}

		/**
		 * This function is the callback for the show/hide post meta settings field.
		 */
		function sds_theme_options_hide_post_meta_field() {
			global $sds_theme_options;
		?>
			<div class="checkbox sds-theme-options-checkbox checkbox-show-hide checkbox-show-hide-post-meta" data-label-left="<?php esc_attr_e( 'Show', 'baton' ); ?>" data-label-right="<?php esc_attr_e( 'Hide', 'baton' ); ?>">
				<input type="checkbox" id="sds_theme_options_hide_post_meta" name="sds_theme_options[hide_post_meta]" <?php ( isset( $sds_theme_options['hide_post_meta'] ) ) ? checked( $sds_theme_options['hide_post_meta'] ) : checked( false ); ?> />
				<label for="sds_theme_options_hide_post_meta">| | |</label>
			</div>
			<span class="description"><?php _e( 'When "show" is displayed, the post meta will be displayed on your site and vise-versa.', 'baton' ); ?></span>
		<?php
		}

		/**
		 * This function is the callback for the show/hide author meta settings field.
		 */
		function sds_theme_options_hide_author_meta_field() {
			global $sds_theme_options;
		?>
			<div class="checkbox sds-theme-options-checkbox checkbox-show-hide checkbox-show-hide-author-meta" data-label-left="<?php esc_attr_e( 'Show', 'baton' ); ?>" data-label-right="<?php esc_attr_e( 'Hide', 'baton' ); ?>">
				<input type="checkbox" id="sds_theme_options_hide_author_meta" name="sds_theme_options[hide_author_meta]" <?php ( isset( $sds_theme_options['hide_author_meta'] ) ) ? checked( $sds_theme_options['hide_author_meta'] ) : checked( false ); ?> />
				<label for="sds_theme_options_hide_author_meta">| | |</label>
			</div>
			<span class="description"><?php _e( 'When "show" is displayed, the author details will be displayed on your site and vise-versa.', 'baton' ); ?></span>
		<?php
		}

		
		/**
		 * This function is the callback for the color schemes settings section.
		 */
		function sds_theme_options_color_schemes_section() {
		?>
			<p><?php _e( 'Select a color scheme to use on your site.', 'baton' ); ?></p>
		<?php
		}

		/**
		 * This function is the callback for the color schemes settings field.
		 */
		function sds_theme_options_color_schemes_field() {
			global $sds_theme_options;
		?>
			<div class="sds-theme-options-color-schemes-wrap">
				<?php foreach ( sds_color_schemes() as $name => $atts ) :	?>
					<div class="sds-theme-options-color-scheme sds-theme-options-color-scheme-<?php echo $name; ?>">
						<label>
							<?php if ( ( ! isset( $sds_theme_options['color_scheme'] ) || empty( $sds_theme_options['color_scheme'] ) ) && isset( $atts['default'] ) && $atts['default'] ) : // No color scheme selected, use default ?>
								<input type="radio" id="sds_theme_options_color_scheme_<?php echo $name; ?>" name="sds_theme_options[color_scheme]" <?php checked( true ); ?> value="<?php echo $name; ?>" />
							<?php else: ?>
								<input type="radio" id="sds_theme_options_color_scheme_<?php echo $name; ?>" name="sds_theme_options[color_scheme]" <?php ( isset( $sds_theme_options['color_scheme'] ) ) ? checked( $sds_theme_options['color_scheme'], $name ) : checked( false ); ?> value="<?php echo $name; ?>" />
							<?php endif; ?>

							<?php if ( isset( $atts['preview'] ) && ! empty( $atts['preview'] ) ) : ?>
								<div class="sds-theme-options-color-scheme-preview" style="background: <?php echo $atts['preview']; ?>">&nbsp;</div>
							<?php endif; ?>

							<?php echo ( isset( $atts['label'] ) ) ? $atts['label'] : false; ?>
						</label>
					</div>
				<?php endforeach; ?>
			</div>
		<?php
		}

		
		/**
		 * This function is the callback for the web fonts settings section.
		 */
		function sds_theme_options_web_fonts_section() {
		?>
			<p><?php _e( 'Select a Google Web Font to use on your site.', 'baton' ); ?></p>
		<?php
		}

		/**
		 * This function is the callback for the web fonts settings field.
		 */
		function sds_theme_options_web_fonts_field() {
			global $sds_theme_options;
		?>
			<div class="sds-theme-options-web-fonts-wrap">
				<div class="sds-theme-options-web-font sds-theme-options-web-font-default">
					<label>
						<input type="radio" id="sds_theme_options_web_font_default" name="sds_theme_options[web_font]" <?php ( ! isset( $sds_theme_options['web_font'] ) || empty( $sds_theme_options['web_font'] ) || $sds_theme_options['web_font'] === 'default' ) ? checked( true ) : checked( false ); ?> value="default" />
						<div class="sds-theme-options-web-font-selected">&nbsp;</div>
					</label>
					<span class="sds-theme-options-web-font-label-default"><?php _e( 'Default', 'baton' ); ?></span>
				</div>

				<?php
					foreach ( sds_web_fonts() as $name => $atts ) :
						$css_name = strtolower( str_replace( array( '+', ':' ), '-', $name ) );
				?>
						<div class="sds-theme-options-web-font sds-theme-options-web-font-<?php echo $css_name; ?>" style="<?php echo ( isset( $atts['css'] ) && ! empty( $atts['css'] ) ) ? $atts['css'] : false; ?>">
							<label>
								<input type="radio" id="sds_theme_options_web_font_name_<?php echo $css_name; ?>" name="sds_theme_options[web_font]" <?php ( isset( $sds_theme_options['web_font'] ) ) ? checked( $sds_theme_options['web_font'], $name ) : checked( false ); ?> value="<?php echo $name; ?>" />
								<div class="sds-theme-options-web-font-selected">&nbsp;</div>
							</label>
							<span class="sds-theme-options-web-font-label"><?php echo ( isset( $atts['label'] ) ) ? $atts['label'] : false; ?></span>
							<span class="sds-theme-options-web-font-preview"><?php _e( 'Grumpy wizards make toxic brew for the evil Queen and Jack.', 'baton' ); ?></span>
						</div>
				<?php
					endforeach;
				?>
			</div>
		<?php
		}

		
		/**
		 * This function is the callback for the featured image size settings section.
		 */
		function sds_theme_options_featured_image_size_section() {
		?>
			<p><?php _e( 'Use this section to modify how featured images are displayed on your site.', 'baton' ); ?></p>
		<?php
		}
		
		/**
		 * This function is the callback for the featured image size settings field.
		 */
		function sds_theme_options_featured_image_size_field() {
			global $sds_theme_options;

			// Get all available image sizes and their dimensions
			$avail_image_sizes = $this->get_available_image_sizes();

			$sds_theme_option_defaults = SDS_Theme_Options::get_sds_theme_option_defaults(); // Defaults
			$default_featured_image_size = $sds_theme_option_defaults['featured_image_size'];

			$featured_image_size = ( isset( $sds_theme_options['featured_image_size'] ) && ! empty( $sds_theme_options['featured_image_size'] ) ) ? $sds_theme_options['featured_image_size'] : $default_featured_image_size;
		?>
			<label class="select-label">
				<select id="sds_theme_options_featured_image_size" name="sds_theme_options[featured_image_size]">
					<?php foreach ( $avail_image_sizes as $size => $atts ) : ?>
						<option value="<?php echo esc_attr( $size ); ?>" <?php selected( $featured_image_size, $size ); ?>>
							<?php
								// Default featured image size
								if ( $size === $default_featured_image_size )
									printf( _x( '%1$s %2$s (Default)', 'default featured image size label, %1$s is the image size name, %2$s is the image dimensions', 'baton' ), $size, implode( 'x', $atts ) );
								// Other featured image sizes
								else
									echo $size . ' ' . implode( 'x', $atts );
							?>
						</option>
					<?php endforeach; ?>
				</select>
			</label>
		<?php
		}


		/**
		 * This function is the callback for the footer branding settings section.
		 */
		function sds_theme_options_footer_branding_section() {
		?>
			<p><?php _e( 'Use this section to modify the footer branding of your website. Entering a value here will over-write the default footer branding. You may use the following HTML tags: <code>&lt;a&gt;, &lt;strong&gt;, &lt;em&gt;, &lt;span&gt;</code>', 'baton' ); ?></p>
		<?php
		}

		/**
		 * This function is the callback for the footer copyright settings field.
		 */
		function sds_theme_options_footer_copyright_field() {
			global $sds_theme_options;

			if ( ! isset( $sds_theme_options['footer']['copyright'] ) || $sds_theme_options['footer']['copyright'] === false )
				$sds_theme_options['footer']['copyright'] = sds_get_copyright();
		?>
			<input type="text" id="sds_theme_options_footer_copyright" name="sds_theme_options[footer][copyright]" class="large-text" value="<?php echo esc_attr( $sds_theme_options['footer']['copyright'] ); ?>" />
		<?php
		}

		/**
		 * This function is the callback for the footer branding settings field.
		 */
		function sds_theme_options_footer_branding_field() {
			global $sds_theme_options;

			if ( ! isset( $sds_theme_options['footer']['branding'] ) || $sds_theme_options['footer']['branding'] === false )
				$sds_theme_options['footer']['branding'] = sds_get_copyright_branding();
		?>
			<input type="text" id="sds_theme_options_footer_branding" name="sds_theme_options[footer][branding]" class="large-text" value="<?php echo esc_attr( $sds_theme_options['footer']['branding'] ); ?>" />
		<?php
		}

		/**
		 * This function is the callback for the show/hide footer branding settings field.
		 */
		function sds_theme_options_hide_footer_branding_field() {
			global $sds_theme_options;
		?>
			<div class="checkbox sds-theme-options-checkbox checkbox-show-hide checkbox-show-hide-footer-branding" data-label-left="<?php esc_attr_e( 'Show', 'baton' ); ?>" data-label-right="<?php esc_attr_e( 'Hide', 'baton' ); ?>">
				<input type="checkbox" id="sds_theme_options_hide_footer_branding" name="sds_theme_options[footer][hide_branding]" <?php ( isset( $sds_theme_options['footer']['hide_branding'] ) ) ? checked( $sds_theme_options['footer']['hide_branding'] ) : checked( false ); ?> />
				<label for="sds_theme_options_hide_footer_branding">| | |</label>
			</div>
			<span class="description"><?php _e( 'When "show" is displayed, the footer branding will be displayed on your site and vise-versa.', 'baton' ); ?></span>
		<?php
		}


		/**
		 * This function is the callback for the content layout settings section.
		 */
		function sds_theme_options_content_layout_section() {
		?>
			<p><?php _e( 'Control the layout of the content on your site here. Choose a global layout scheme to be used across your entire site or specifiy individual content type layout schemes by adjusting the options below.', 'baton' ); ?></p>
		<?php
		}

		/**
		 * This function is the callback for the global content layout settings field.
		 */
		function sds_theme_options_content_layout_global_field() {
			$this->content_layouts_field( 'global', __( 'Select a content layout that will be applied globally on your site. Select more specific content layouts below.', 'baton' ) );
		}

		/**
		 * This function is the callback for the front page content layout settings field.
		 */
		function sds_theme_options_content_layout_front_page_field() {
			$this->content_layouts_field( 'front_page', __( 'Select a content layout that will be applied to the front page on your site (if selected in Settings > Reading).', 'baton' ) );
		}

		/**
		 * This function is the callback for the home (blog) page content layout settings field.
		 */
		function sds_theme_options_content_layout_home_field() {
			$this->content_layouts_field( 'home', __( 'Select a content layout that will be applied to the blog on your site.', 'baton' ) );
		}

		/**
		 * This function is the callback for the single post content layout settings field.
		 */
		function sds_theme_options_content_layout_single_field() {
			$this->content_layouts_field( 'single', __( 'Select a content layout that will be applied to single posts on your site.', 'baton' ) );
		}

		/**
		 * This function is the callback for the single page content layout settings field.
		 */
		function sds_theme_options_content_layout_page_field() {
			$this->content_layouts_field( 'page', __( 'Select a content layout that will be applied to single pages on your site.', 'baton' ) );
		}

		/**
		 * This function is the callback for the archive content layout settings field.
		 */
		function sds_theme_options_content_layout_archive_field() {
			$this->content_layouts_field( 'archive', __( 'Select a content layout that will be applied to archives on your site.', 'baton' ) );
		}

		/**
		 * This function is the callback for the category content layout settings field.
		 */
		function sds_theme_options_content_layout_category_field() {
			$this->content_layouts_field( 'category', __( 'Select a content layout that will be applied to category archives on your site.', 'baton' ) );
		}

		/**
		 * This function is the callback for the tag content layout settings field.
		 */
		function sds_theme_options_content_layout_tag_field() {
			$this->content_layouts_field( 'tag', __( 'Select a content layout that will be applied to tag archives on your site.', 'baton' ) );
		}

		/**
		 * This function is the callback for the 404 (error) content layout settings field.
		 */
		function sds_theme_options_content_layout_404_field() {
			$this->content_layouts_field( '404', __( 'Select a content layout that will be applied to the 404 error page on your site.', 'baton' ) );
		}


		/**
		 * This function is the callback for the social media settings section.
		 */
		function sds_theme_options_social_media_section() {
		?>
			<p><?php _e( 'Enter your social media links here. This section is used throughout the site to display social media links to visitors. Some themes display social media links automatically, and some only display them within the Social Media widget.', 'baton' ); ?></p>
		<?php
		}

		/**
		 * This function is the callback for the facebook url settings field.
		 */
		function sds_theme_options_social_media_facebook_url_field() {
			$this->social_media_field( 'facebook_url' );
		}

		/**
		 * This function is the callback for the twitter url settings field.
		 */
		function sds_theme_options_social_media_twitter_url_field() {
			$this->social_media_field( 'twitter_url' );
		}

		/**
		 * This function is the callback for the linkedin url settings field.
		 */
		function sds_theme_options_social_media_linkedin_url_field() {
			$this->social_media_field( 'linkedin_url' );
		}

		/**
		 * This function is the callback for the google_plus url settings field.
		 */
		function sds_theme_options_social_media_google_plus_url_field() {
			$this->social_media_field( 'google_plus_url' );
		}

		/**
		 * This function is the callback for the youtube url settings field.
		 */
		function sds_theme_options_social_media_youtube_url_field() {
			$this->social_media_field( 'youtube_url' );
		}

		/**
		 * This function is the callback for the vimeo url settings field.
		 */
		function sds_theme_options_social_media_vimeo_url_field() {
			$this->social_media_field( 'vimeo_url' );
		}

		/**
		 * This function is the callback for the instagram url settings field.
		 */
		function sds_theme_options_social_media_instagram_url_field() {
			$this->social_media_field( 'instagram_url' );
		}

		/**
		 * This function is the callback for the pinterest url settings field.
		 */
		function sds_theme_options_social_media_pinterest_url_field() {
			$this->social_media_field( 'pinterest_url' );
		}

		/**
		 * This function is the callback for the flickr url settings field.
		 */
		function sds_theme_options_social_media_flickr_url_field() {
			$this->social_media_field( 'flickr_url' );
		}

		/**
		 * This function is the callback for the yelp url settings field.
		 */
		function sds_theme_options_social_media_yelp_url_field() {
			$this->social_media_field( 'yelp_url' );
		}

		/**
		 * This function is the callback for the foursquare url settings field.
		 */
		function sds_theme_options_social_media_foursquare_url_field() {
			$this->social_media_field( 'foursquare_url' );
		}

		/**
		 * This function is the callback for the rss url settings field.
		 */
		function sds_theme_options_social_media_rss_url_field() {
			global $sds_theme_options;
		?>
			<strong><?php _e( 'Use Site RSS Feed:', 'baton' ); ?></strong>
			<div class="checkbox sds-theme-options-checkbox checkbox-social_media-rss_url-use-site-feed" data-label-left="<?php esc_attr_e( 'Yes', 'baton' ); ?>" data-label-right="<?php esc_attr_e( 'No', 'baton' ); ?>">
				<input type="checkbox" id="sds_theme_options_social_media_rss_url_use_site_feed" name="sds_theme_options[social_media][rss_url_use_site_feed]" <?php ( isset( $sds_theme_options['social_media']['rss_url_use_site_feed'] ) ) ? checked( $sds_theme_options['social_media']['rss_url_use_site_feed'] ) : checked( false ); ?> />
				<label for="sds_theme_options_social_media_rss_url_use_site_feed">| | |</label>
			</div>
			<span class="description"><?php _e( 'When "yes" is displayed, the RSS feed for your site will be used.', 'baton' ); ?></span>

			<div id="sds_theme_options_social_media_rss_url_custom">
				<strong><?php _e( 'Custom RSS Feed:', 'baton' ); ?></strong>
				<input type="text" id="sds_theme_options_social_media_rss_url" name="sds_theme_options[social_media][rss_url]" class="large-text" value="<?php echo ( isset( $sds_theme_options['social_media']['rss_url'] ) && ! empty( $sds_theme_options['social_media']['rss_url'] ) ) ? esc_attr( esc_url( $sds_theme_options['social_media']['rss_url'] ) ) : false; ?>" />
			</div>
		<?php
		}


		/**
		 * This function is the callback for the custom scripts section.
		 */
		function sds_theme_options_custom_scripts_section() { ?>
			<p><?php _e( 'Add custom scripts to either the head section or the footer section of your site. See <a href="http://codex.wordpress.org/Using_Javascript" target="_blank">WordPress Codex: Using Javascript</a> for more information.', 'baton' ); ?></p>
		<?php
		}
		
		/**
		 * This function is the callback for the custom scripts head settings field.
		 */
		function sds_theme_options_custom_scripts_head_field() {
			global $sds_theme_options;
		?>
			<textarea id="sds_theme_options_custom_scripts_head" name="sds_theme_options[custom_scripts][wp_head]" rows="10" cols="100"><?php echo ( isset( $sds_theme_options['custom_scripts']['wp_head'] ) ) ? esc_textarea( $sds_theme_options['custom_scripts']['wp_head'] ) : false; ?></textarea>
		<?php
		}
		
		/**
		 * This function is the callback for the custom scripts footer settings field.
		 */
		function sds_theme_options_custom_scripts_footer_field() {
			global $sds_theme_options;
		?>
			<textarea id="sds_theme_options_custom_scripts_footer" name="sds_theme_options[custom_scripts][wp_footer]" rows="10" cols="100"><?php echo ( isset( $sds_theme_options['custom_scripts']['wp_footer'] ) ) ? esc_textarea( $sds_theme_options['custom_scripts']['wp_footer'] ) : false; ?></textarea>
		<?php
		}

		
		/**
		 * This function is the callback for the custom styles section.
		 */
		function sds_theme_options_custom_styles_section() { ?>
			<p><?php _e( 'Add custom styles your site. See <a href="https://developer.mozilla.org/en/CSS" target="_blank">Mozilla Developer Network: CSS</a> for more information.', 'baton' ); ?></p>
		<?php
		}
		
		/**
		 * This function is the callback for the custom styles head settings field.
		 */
		function sds_theme_options_custom_styles_field() {
			global $sds_theme_options;
		?>
			<textarea id="sds_theme_options_custom_styles" name="sds_theme_options[custom_styles]" rows="10" cols="100"><?php echo ( isset( $sds_theme_options['custom_styles'] ) ) ? esc_textarea( $sds_theme_options['custom_styles'] ) : false; ?></textarea>
		<?php
		}


		/**
		 * This function is the callback for the license section.
		 */
		function sds_theme_options_license_section() { ?>
			<p><?php printf( __( 'Enter your license key below to continue to receive updates for %1$s.', 'baton' ), $this->theme->get( 'Name' ) ); ?></p>
		<?php
		}

		/**
		 * This function is the callback for the license key settings field.
		 */
		function sds_theme_options_license_key_field() {
			global $sds_theme_options;

			/*
			 * Determine license status and license key input box CSS classes
			 */
			$license_key_classes = array();

			// Valid
			if ( ! empty( $sds_theme_options['license']['key'] ) && $sds_theme_options['license']['status'] === 'valid' ) {
				$license_key_classes[] = 'has-license';
				$license_key_classes[] = 'active';
			}
			// Invalid
			else if ( ! empty( $sds_theme_options['license']['key'] ) && $sds_theme_options['license']['status'] === 'invalid' ) {
				$license_key_classes[] = 'has-license';
				$license_key_classes[] = 'inactive';
			}
			// No License
			else if ( empty( $sds_theme_options['license']['key'] ) )
				$license_key_classes[] = 'no-license';

			$license_key_classes = implode( ' ', $license_key_classes );

		?>
			<div class="input sds-theme-options-license-key <?php echo $license_key_classes; ?>">
				<input type="text" id="sds_theme_options_license_key" name="sds_theme_options[license][key]" class="large-text" value="<?php echo esc_attr( $sds_theme_options['license']['key'] ); ?>" autocomplete="off" />
			</div>
		<?php
		}


		/**
		 * This function sanitizes input from the user when saving options.
		 */
		function sds_theme_options_sanitize( $input ) {
			// Fetch current options
			$sds_theme_options = SDS_Theme_Options::get_sds_theme_options();

			// Reset to Defaults
			if ( isset( $input['reset'] ) )
				return $this->get_sds_theme_option_defaults();

			// Remove Logo
			if ( isset( $input['remove-logo'] ) ) {
				unset( $input['remove-logo'] ); // We don't want to store this value in the options array

				$input['logo_attachment_id'] = false;
			}

			// Parse arguments, replacing defaults with user input
			$input = wp_parse_args( $input, $this->get_sds_theme_option_defaults() );

			// General
			$input['logo_attachment_id'] = ( ! empty( $input['logo_attachment_id'] ) ) ? ( int ) $input['logo_attachment_id'] : '';
			$input['hide_tagline'] = ( $input['hide_tagline'] ) ? true : false;
			$input['hide_archive_titles'] = ( $input['hide_archive_titles'] ) ? true : false;
			$input['hide_post_meta'] = ( $input['hide_post_meta'] ) ? true : false;
			$input['hide_author_meta'] = ( $input['hide_author_meta'] ) ? true : false;
			$input['color_scheme'] = sanitize_text_field( $input['color_scheme'] );
			$input['web_font'] = ( ! empty( $input['web_font'] ) && $input['web_font'] !== 'default' ) ? sanitize_text_field( $input['web_font'] ) : false;
			$input['featured_image_size'] = sanitize_text_field( $input['featured_image_size'] );
			$input['footer']['copyright'] = wp_kses( $input['footer']['copyright'], array(
				'a' => array(
					'href' => array(),
					'title' => array(),
					'target' => array()
				),
				'strong' => array(),
				'em' => array(),
				'span' => array()
			) );
			$input['footer']['branding'] = wp_kses( $input['footer']['branding'], array(
				'a' => array(
					'href' => array(),
					'title' => array(),
					'target' => array()
				),
				'strong' => array(),
				'em' => array(),
				'span' => array()
			) );
			$input['footer']['hide_branding'] = ( isset( $input['footer']['hide_branding'] ) && $input['footer']['hide_branding'] ) ? true : false; // Checking isset() here due to the nested arrays

			// Color Scheme (remove content/background colors if they match another color scheme's default values)
			if ( function_exists( 'sds_color_schemes' ) && ! empty( $input['color_scheme'] ) ) {
				// Get color schemes
				$sds_color_schemes = sds_color_schemes();

				// Setup color scheme variables
				$default_color_scheme = ( isset( $sds_color_schemes['default'] ) ) ? $sds_color_schemes['default'] : array(); // Default color scheme
				$previous_color_scheme = $sds_color_schemes[$sds_theme_options['color_scheme']]; // Previous color scheme
				$current_color_scheme = $sds_color_schemes[$input['color_scheme']]; // Current color scheme

				// Ignore the following properties
				$ignore_properties = array(
					'label',
					'stylesheet',
					'preview',
					'default',
					'deps'
				);

				// If we have color scheme properties (default color scheme should contain all theme mod properties/values)
				if ( ! empty( $default_color_scheme ) )
					foreach ( $default_color_scheme as $property => $value ) {
						// Make sure this property isn't in the ignore list
						if ( ! in_array( $property, $ignore_properties ) ) {
							// Grab the theme mod for this property (remove hash symbol)
							$theme_mod = ltrim( get_theme_mod( $property ), '#' );

							// Grab the previous color scheme value for this property (remove hash symbol)
							$previous_color_scheme_value = ( isset( $previous_color_scheme[$property] ) ) ? ltrim( $previous_color_scheme[$property], '#' ) : false;

							// Grab the current color scheme value for this property (remove hash symbol)
							$current_color_scheme_value = ( isset( $current_color_scheme[$property] ) ) ? ltrim( $current_color_scheme[$property], '#' ) : false;

							// If we have a value for the theme mod and the previous color scheme, and it matches the previous color scheme property value, update that theme mod with the new value
							if ( ! empty( $theme_mod ) && ! empty( $previous_color_scheme_value ) && $theme_mod === $previous_color_scheme_value && $theme_mod !== $current_color_scheme_value )
								// If we have a new color scheme value for this property
								if ( isset( $current_color_scheme[$property] ) && ! empty( $current_color_scheme[$property] ) )
									// Update the theme mod with the new color scheme value
									set_theme_mod( sanitize_text_field( $property ), sanitize_text_field( $current_color_scheme[$property] ) );
						}
					}
			}

			// Content Layouts
			foreach ( $input['content_layouts'] as $key => &$value )
				$value = ( $value !== 'default' ) ? sanitize_text_field( $value ) : false;

			// Social Media
			foreach ( $input['social_media'] as $key => &$value ) {
				// RSS Feed (use site feed)
				if ( $key === 'rss_url_use_site_feed' && $value ) {
					$value = true;

					$input['social_media']['rss_url'] = '';
				}
				else
					$value = esc_url( $value );
			}

			// Ensure the 'rss_url_use_site_feed' key is set in social media
			if ( ! isset( $input['social_media']['rss_url_use_site_feed'] ) )
				$input['social_media']['rss_url_use_site_feed'] = false;

			/**
			 * Custom Scripts/Styles
			 */

			// Make sure we only allow script tags here
			$input['custom_scripts']['wp_head'] = wp_kses( $input['custom_scripts']['wp_head'],
				array(
					'script' => array(
						'async' => array(),
						'src' => array(),
						'type' => array(),
						'language' => array()
					)
				)
			);

			// Convert stray HTML entities back as they could be part of a script
			$input['custom_scripts']['wp_head'] = htmlspecialchars_decode( $input['custom_scripts']['wp_head'] );

			// If the user entered script tags, set output flag
			$input['custom_scripts']['wp_head_has_tags'] = ( preg_match('%<script[^<]+>%', $input['custom_scripts']['wp_head'] ) ) ? true : false;

			// Make sure we only allow script tags here
			$input['custom_scripts']['wp_footer'] = wp_kses( $input['custom_scripts']['wp_footer'], array(
				'script' => array(
					'async' => array(),
					'src' => array(),
					'type' => array(),
					'language' => array()
				)
			) );

			// Convert stray HTML entities back as they could be part of a script
			$input['custom_scripts']['wp_footer'] = htmlspecialchars_decode( $input['custom_scripts']['wp_footer'] );

			// If the user entered script tags, set output flag
			$input['custom_scripts']['wp_footer_has_tags'] = ( preg_match('%<script[^<]+>%', $input['custom_scripts']['wp_footer'] ) ) ? true : false;


			// Make sure we only allow style tags here
			$input['custom_styles'] = wp_kses( $input['custom_styles'], array(
				'style' => array(
					'type' => array(),
					'media' => array()
				)
			) );

			// Convert stray HTML entities back as they could be part of styles
			$input['custom_styles'] = htmlspecialchars_decode( $input['custom_styles'] );

			// If the user entered style tags, set output flag
			$input['custom_styles_has_tags'] = ( preg_match('%<style[^<]+>%', $input['custom_styles'] ) ) ? true : false;


			/**
			 * License
			 */

			// License Key
			$input['license']['key'] = ( isset( $input['license']['key'] ) && ! empty( $input['license']['key'] ) ) ? trim( sanitize_text_field( $input['license']['key'] ) ) : false; // Checking isset() here due to the nested arrays

			// License Status (determine status of license if it has changed or is invalid)
			if ( ( ! empty( $input['license']['key'] ) && ( ! $sds_theme_options['license']['status'] || $sds_theme_options['license']['status'] === 'invalid' ) ) || ( ! empty( $input['license']['key'] ) && $input['license']['key'] !== $sds_theme_options['license']['key'] ) ) {
				$input['license']['status'] = false;

				// Activation arguments
				$api_args = array(
					'edd_action'=> 'activate_license',
					'license' 	=> $input['license']['key'],
					'item_name' => urlencode( $this->theme->get( 'Name' ) ),
					'url'       => home_url()
				);

				// Call the custom API.
				$response = wp_remote_get( esc_url_raw( add_query_arg( $api_args, SDS_Theme_Options::$update_url ) ), array( 'timeout' => 15, 'sslverify' => false ) );

				// Make sure we have a valid response
				if ( ! is_wp_error( $response ) && ( $license_data = json_decode( wp_remote_retrieve_body( $response ) ) ) )
					// Validate that the request was successful and we have a valid license
					if ( $license_data->success && $license_data->license === 'valid' )
						$input['license']['status'] = 'valid';
					else
						$input['license']['status'] = 'invalid';
				// Otherwise we do not have a valid response
				else
					$input['license']['status'] = 'invalid';
			}
			// License Status (valid)
			else if ( isset( $sds_theme_options['license']['status'] ) && $sds_theme_options['license']['status'] === 'valid' )
				$input['license']['status'] = 'valid';


			return $input;
		}


		/**
		 * This function handles the rendering of the options page.
		 */
		function sds_theme_options_page() {
			global $_wp_admin_css_colors, $wp_version;

			$user_admin_color = get_user_meta(  get_current_user_id(), 'admin_color', true );
		?>
			<div class="wrap about-wrap">
				<?php if ( isset( $_wp_admin_css_colors[$user_admin_color] ) && version_compare( $wp_version, '3.8', '>=' ) ) : // Output styles to match selected admin color scheme ?>
					<style type="text/css">
						/* Checkboxes */
						.sds-theme-options-checkbox:before {
							background: <?php echo $_wp_admin_css_colors[$user_admin_color]->colors[2]; ?>;
						}

						/* Web Fonts */
						.sds-theme-options-web-font input[type=radio]:checked + .sds-theme-options-web-font-selected:before {
							color: <?php echo $_wp_admin_css_colors[$user_admin_color]->colors[2]; ?>;
						}

						/* Content Layouts */
						.sds-theme-options-content-layout:hover .sds-theme-options-content-layout-preview,
						.sds-theme-options-content-layout input[type=radio]:checked + .sds-theme-options-content-layout-preview {
							border: 1px solid <?php echo $_wp_admin_css_colors[$user_admin_color]->colors[2]; ?>;
						}

						.sds-theme-options-content-layout:hover .sds-theme-options-content-layout-preview  .col,
						.sds-theme-options-content-layout input[type=radio]:checked + .sds-theme-options-content-layout-preview .col {
							background: <?php echo $_wp_admin_css_colors[$user_admin_color]->colors[2]; ?>;
						}

						.sds-theme-options-content-layout:hover .sds-theme-options-content-layout-preview  .col-sidebar,
						.sds-theme-options-content-layout input[type=radio]:checked + .sds-theme-options-content-layout-preview .col-sidebar {
							background: <?php echo $_wp_admin_css_colors[$user_admin_color]->colors[3]; ?>;
						}
					</style>
				<?php endif; ?>

				<h1><?php echo $this->theme->get( 'Name' ); ?> <?php _e( 'Theme Options', 'baton' ); ?></h1>
				<div class="about-text sds-about-text"><?php _e( 'Customize your theme to the fullest extent by using the options below.', 'baton' ); ?></div>

				<?php do_action( 'sds_theme_options_notifications' ); ?>

				<?php
					settings_errors( 'general' ); // General Settings Errors
					settings_errors( self::$option_name ); // Theme Options Panel Settings Errors
				?>

				<h3 class="nav-tab-wrapper sds-theme-options-nav-tab-wrapper sds-theme-options-tab-wrap">
					<a href="#general" id="general-tab" class="nav-tab sds-theme-options-tab nav-tab-active"><?php _e( 'General', 'baton' ); ?></a>
					<?php if ( function_exists( 'sds_content_layouts' ) ) : ?>
						<a href="#content-layout" id="content-layout-tab" class="nav-tab sds-theme-options-tab"><?php _e( 'Layout', 'baton' ); ?></a>
					<?php endif; ?>
					<a href="#social-media" id="social-media-tab" class="nav-tab sds-theme-options-tab"><?php _e( 'Social Media', 'baton' ); ?></a>
					<a href="#custom-scripts-styles" id="custom-scripts-styles-tab" class="nav-tab sds-theme-options-tab"><?php _e( 'Custom Scripts/Styles', 'baton' ); ?></a>
					<a href="#license" id="license-tab" class="nav-tab sds-theme-options-tab"><?php _e( 'License', 'baton' ); ?></a>
					<?php do_action( 'sds_theme_options_navigation_tabs' ); // Hook for extending tabs ?>
					<a href="#help-support" id="help-support-tab" class="nav-tab sds-theme-options-tab"><?php _e( 'Support', 'baton' ); ?></a>
				</h3>

				<form method="post" action="options.php" enctype="multipart/form-data" id="sds-theme-options-form">
					<?php settings_fields( self::$option_name ); ?>
					<input type="hidden" name="sds_theme_options_tab" id="sds_theme_options_tab" value="" />

					<?php
					/*
					 * General Settings
					 */
					?>
					<div id="general-tab-content" class="sds-theme-options-tab-content sds-theme-options-tab-content-active">
						<?php do_settings_sections( 'sds-theme-options[general]' ); ?>
					</div>

					<?php
					/*
					 * Content Layout Settings
					 */
					?>
					<?php if ( function_exists( 'sds_content_layouts' ) ) : ?>
						<div id="content-layout-tab-content" class="sds-theme-options-tab-content">
							<?php do_settings_sections( 'sds-theme-options[content-layout]' ); ?>
						</div>
					<?php endif; ?>

					<?php
					/*
					 * Social Media Settings
					 */
					?>
					<div id="social-media-tab-content" class="sds-theme-options-tab-content">
						<?php do_settings_sections( 'sds-theme-options[social-media]' ); ?>
					</div>

					<?php
					/*
					 * Custom Scripts/Styles Settings
					 */
					?>
					<div id="custom-scripts-styles-tab-content" class="sds-theme-options-tab-content">
						<?php do_settings_sections( 'sds-theme-options[custom-scripts-styles]' ); ?>
					</div>

					<?php
					/*
					 * License Settings
					 */
					?>
					<div id="license-tab-content" class="sds-theme-options-tab-content">
						<?php do_settings_sections( 'sds-theme-options[license]' ); ?>
					</div>

					<?php
					/*
					 * Help/Support
					 */
					?>
					<div id="help-support-tab-content" class="sds-theme-options-tab-content">
						<h3><?php _e( 'Help/Support', 'baton' ); ?></h3>

						<p><?php _e( 'If you\'d like to create a suppport ticket, please visit our <a href="http://slocumstudio.freshdesk.com" target="_blank">help desk</a>.', 'baton' ); ?></p>

						<h3><?php _e( 'WordPress Snapshot', 'baton' ); ?></h3>

						<p><?php printf( __( 'The following information can be helpful to us when an issue with %1$s may arise. If a support tech requests a "snapshot", please send us this information by copying and pasting it into the support ticket.', 'baton' ), $this->theme->get( 'Name' ) ); ?></p>

						<?php
							// WordPress Snapshot Details
							$wp_snapshot = $this->get_snapshot_details();

							if ( ! empty( $wp_snapshot ) ) :
						?>
							<textarea class="wp-snapshot" rows="20" cols="86" onclick="this.focus(); this.select()" readonly="readonly">
								<?php
									foreach ( $wp_snapshot as $snapshot_key => $snapshot_item ) {
										echo ( ! empty( $snapshot_item['value'] ) ) ? $snapshot_item['label'] : "\n" . $snapshot_item['label'];
										echo ( empty( $snapshot_item['value'] ) ) ? "\n" . '----------' : false;
										echo ( ! empty( $snapshot_item['value'] ) ) ? ' ' .$snapshot_item['value'] : false;
										echo "\n";
									}
								?>
							</textarea>
						<?php
							endif;
						?>
					</div>

					<?php do_action( 'sds_theme_options_settings' ); // Hook for extending settings ?>

					<p class="submit">
						<?php submit_button( __( 'Save Options', 'baton' ), 'primary', 'submit', false ); ?>
						<?php submit_button( __( 'Restore Defaults', 'baton' ), 'secondary', 'sds_theme_options[reset]', false ); ?>
					</p>
				</form>

				<div id="sds-theme-options-ads" class="sidebar">
					<div class="sds-theme-options-ad">
						<a href="https://conductorplugin.com/slocum-pro-themes/?utm_source=slocum-themes&utm_medium=link&utm_content=sidebar&utm_campaign=slocum-themes">
							<img src="<?php echo SDS_Theme_Options::sds_core_url(); ?>/images/conductor.png" width="300" height="225" alt="Conductor Plugin" title="Conductor Plugin" />
						</a>
					</div>

					<div class="sds-theme-options-ad">
						<div class="yt-subscribe">
							<div class="g-ytsubscribe" data-channel="slocumstudio" data-layout="default"></div>
							<script src="https://apis.google.com/js/plusone.js"></script>
						</div>

						<a href="https://twitter.com/slocumstudio" class="twitter-follow-button" data-show-count="false" data-size="large" data-dnt="true">Follow @slocumstudio</a>
						<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>

						<br />
						<br />

						<div class="slocum-themes">
							<?php printf( __( 'Brought to you by <a href="%1$s" target="_blank">Slocum Themes</a>', 'baton' ), 'http://slocumthemes.com/' ); ?>
						</div>
					</div>

					<?php do_action( 'sds_theme_options_ads' ); ?>
				</div>
			</div>
		<?php
		}

		/*
		 * This function appends the hash for the current tab based on POST data.
		 */
		function wp_redirect( $location ) {
			// Append tab "hash" to end of URL
			if ( strpos( $location, 'sds-theme-options' ) !== false && isset( $_POST['sds_theme_options_tab'] ) && $_POST['sds_theme_options_tab'] )
				$location .= esc_url( $_POST['sds_theme_options_tab'] );

			return $location;
		}



		/**
		 * External Functions (functions that can be used outside of this class to retrieve data)
		 */

		/**
		 * This function returns the current option values.
		 */
		public static function get_sds_theme_options() {
			global $sds_theme_options;

			$sds_theme_options = wp_parse_args( get_option( self::$option_name ), SDS_Theme_Options::get_sds_theme_option_defaults() );

			return $sds_theme_options;
		}

		/**
		 * This function returns the current option name.
		 */
		public static function get_option_name() {
			return self::$option_name;
		}



		/************************************************************************
		 * Internal Functions (functions used internally throughout this class) *
		 ************************************************************************/

		/**
		 * This function returns default values for SDS Theme Options
		 */
		public static function get_sds_theme_option_defaults() {
			$defaults = array(
				// General
				'logo_attachment_id' => false,
				'hide_tagline' => false,
				'hide_archive_titles' => false,
				'hide_post_meta' => false,
				'hide_author_meta' => false,
				'color_scheme' => false,
				'web_font' => false,
				'featured_image_size' => apply_filters( 'sds_theme_options_default_featured_image_size', '' ),
				'footer' => array(
					'copyright' => false,
					'branding' => false,
					'hide_branding' => false
				),

				// Content Layouts
				'content_layouts' => array(
					'global' => false,
					'front_page'=> false,
					'home' => false,
					'single' => false,
					'page' => false,
					'archive' => false,
					'category' => false,
					'tag' => false,
					'404' => false
				),

				// Social Media
				'social_media' => array(
					'facebook_url' => '',
					'twitter_url' => '',
					'linkedin_url' => '',
					'google_plus_url' => '',
					'youtube_url' => '',
					'vimeo_url' => '',
					'instagram_url' => '',
					'pinterest_url' => '',
					'flickr_url' => '',
					//'yelp_url' => '',
					'foursquare_url' => '',
					'rss_url' => '',
					'rss_url_use_site_feed' => false
				),

				// Custom Scripts/Styles
				'custom_scripts' => array(
					'wp_head' => '',
					'wp_head_has_tags' => false,
					'wp_footer' => '',
					'wp_footer_has_tags' => false
				),
				'custom_styles' => '',
				'custom_styles_has_tags' => false,
				// License
				'license' => array(
					'key' => false,
					'status' => false
				)
			);

			return apply_filters( 'sds_theme_options_defaults', $defaults );
		}

		/**
		 * This function returns a formatted list of Google Web Font families for use when enqueuing styles.
		 */
		function get_google_font_families_list() {
			if ( function_exists( 'sds_web_fonts' ) ) {
				$web_fonts = sds_web_fonts();
				$web_fonts_count = count( $web_fonts );
				$google_families = '';

				if ( ! empty( $web_fonts ) && is_array( $web_fonts ) ) {
					foreach( $web_fonts as $name => $atts ) {
						// Google Font Name
						$google_families .= $name;

						if ( $web_fonts_count > 1 )
							$google_families .= '|';
					}

					// Trim last | when multiple fonts are set
					if ( $web_fonts_count > 1 )
						$google_families = substr( $google_families, 0, -1 );
				}

				return $google_families;
			}

			return false;
		}

		/**
		 * This function returns an array of available image sizes with attributes.
		 */
		function get_available_image_sizes() {
			global $_wp_additional_image_sizes;

			$avail_image_sizes = array();

			foreach ( get_intermediate_image_sizes() as $size ) {
				$avail_image_sizes[$size] = array( 0, 0 );

				// Built-in Image Sizes
				if ( in_array( $size, array( 'thumbnail', 'medium', 'medium_large', 'large' ) ) ) {
					$avail_image_sizes[$size][0] = get_option( $size . '_size_w' );
					$avail_image_sizes[$size][1] = get_option( $size . '_size_h' );
				}
				// Additional Image Sizes
				else if ( isset( $_wp_additional_image_sizes ) && isset( $_wp_additional_image_sizes[$size] ) )
					$avail_image_sizes[$size] = array( $_wp_additional_image_sizes[$size]['width'], $_wp_additional_image_sizes[$size]['height'] );

				// If any of the dimension values happen to be zero, make them 9999 (i.e. crop ratio or infinite)
				if ( ( int ) $avail_image_sizes[$size][0] === 0 )
					$avail_image_sizes[$size][0] = 9999;

				if ( ( int ) $avail_image_sizes[$size][1] === 0 )
					$avail_image_sizes[$size][1] = 9999;
			}

			return $avail_image_sizes;
		}

		/**
		 * This function returns an array of available image sizes with attributes for use in the Customizer.
		 */
		function get_available_image_size_choices() {
			$avail_image_size_choices = array();

			$sds_theme_option_defaults = SDS_Theme_Options::get_sds_theme_option_defaults(); // Defaults
			$default_featured_image_size = $sds_theme_option_defaults['featured_image_size'];

			// Loop through available image sizes
			foreach ( $this->get_available_image_sizes() as $size => $atts ) {
				$avail_image_size_choices[$size] = array();

				// Default featured image size
				if ( $size === $default_featured_image_size )
					$avail_image_size_choices[$size] = sprintf( _x( '%1$s %2$s (Default)', 'default featured image size label, %1$s is the image size name, %2$s is the image dimensions', 'baton' ), $size, implode( 'x', $atts ) );
				// Other featured image sizes
				else
					$avail_image_size_choices[$size] = $size . ' ' . implode( 'x', $atts );
			}

			return $avail_image_size_choices;
		}

		/**
		 * This function returns the details of the current parent theme.
		 */
		public function get_parent_theme() {
			if ( is_a( $this->theme, 'WP_Theme' ) )
				return $this->theme;

			return ( is_child_theme() ) ? wp_get_theme()->parent() : wp_get_theme();
		}


		/**
		 * This function returns the HTML output of a social media field.
		 */
		public function social_media_field( $field_id ) {
			global $sds_theme_options;
		?>
			<input type="text" id="sds_theme_options_social_media_<?php echo $field_id; ?>" name="sds_theme_options[social_media][<?php echo $field_id; ?>]" class="large-text" value="<?php echo ( isset( $sds_theme_options['social_media'][$field_id] ) && ! empty( $sds_theme_options['social_media'][$field_id] ) ) ? esc_attr( esc_url( $sds_theme_options['social_media'][$field_id] ) ) : false; ?>" />
		<?php
		}

		/**
		 * This function returns the HTML output of a content layout field.
		 */
		public function content_layouts_field( $field_id, $description = '', $custom_field_type = false, $customizer_control = false ) {
			global $sds_theme_options, $is_IE;
		?>
			<div class="sds-theme-options-content-layout-wrap sds-theme-options-content-layout-<?php echo $field_id; ?> <?php echo $field_id; ?> <?php echo ( $is_IE ) ? 'ie' : false; ?>">
				<?php foreach ( sds_content_layouts() as $name => $atts ) : ?>
					<div class="sds-theme-options-content-layout sds-theme-options-content-layout-<?php echo $name; ?>">
						<label>
							<?php if ( $custom_field_type ) : // Custom Fields ?>
								<?php if ( ( ! isset( $sds_theme_options['content_layouts']['custom'][$custom_field_type][$field_id] ) || empty( $sds_theme_options['content_layouts'][$custom_field_type][$field_id] ) ) && isset( $atts['default'] ) && $atts['default'] ) : // No content layout selected, use default ?>
									<input type="radio" id="sds_theme_options_content_layouts_name_<?php echo $name; ?>" name="sds_theme_options[content_layouts][custom][<?php echo $custom_field_type; ?>][<?php echo $field_id; ?>]" <?php checked( true ); ?> value="<?php echo $name; ?>" <?php echo ( ! empty( $customizer_control ) ) ? $customizer_control->get_link() : false; ?> />
								<?php else: ?>
									<input type="radio" id="sds_theme_options_content_layouts_name_<?php echo $name; ?>" name="sds_theme_options[content_layouts][custom][<?php echo $custom_field_type; ?>][<?php echo $field_id; ?>]" <?php ( isset( $sds_theme_options['content_layouts']['custom'][$custom_field_type][$field_id] ) ) ? checked( $sds_theme_options['content_layouts']['custom'][$custom_field_type][$field_id], $name ) : checked( false ); ?> value="<?php echo $name; ?>" <?php echo ( ! empty( $customizer_control ) ) ? $customizer_control->get_link() : false; ?> />
								<?php endif; ?>
							<?php else: // Default Fields ?>
								<?php if ( ( ! isset( $sds_theme_options['content_layouts'][$field_id] ) || empty( $sds_theme_options['content_layouts'][$field_id] ) ) && isset( $atts['default'] ) && $atts['default'] ) : // No content layout selected, use default ?>
									<input type="radio" id="sds_theme_options_content_layouts_name_<?php echo $name; ?>" name="sds_theme_options[content_layouts][<?php echo $field_id; ?>]" <?php checked( true ); ?> value="<?php echo $name; ?>" <?php echo ( ! empty( $customizer_control ) ) ? $customizer_control->get_link() : false; ?> />
								<?php else: ?>
									<input type="radio" id="sds_theme_options_content_layouts_name_<?php echo $name; ?>" name="sds_theme_options[content_layouts][<?php echo $field_id; ?>]" <?php ( isset( $sds_theme_options['content_layouts'][$field_id] ) ) ? checked( $sds_theme_options['content_layouts'][$field_id], $name ) : checked( false ); ?> value="<?php echo $name; ?>" <?php echo ( ! empty( $customizer_control ) ) ? $customizer_control->get_link() : false; ?> />
								<?php endif; ?>
							<?php endif; ?>

							<div class="sds-theme-options-content-layout-preview">
								<?php
								if ( isset( $atts['preview_values'] ) )
									vprintf( $atts['preview'], $atts['preview_values'] );
								else
									echo $atts['preview'];
								?>
							</div>
						</label>
					</div>
				<?php endforeach; ?>
			</div>
			<span class="description"><?php echo $description; ?></span>
		<?php
		}

		/**
		 * This function returns helpful debug information.
		 *
		 * Some functionality below copyright 2013, Andrew Norcross, http://andrewnorcross.com/
		 * - @see https://github.com/norcross/system-snapshop-report
		 */
		public static function get_snapshot_details() {
			// call WP database
			global $wpdb;

			//$browser = get_browser( null, true );
			$theme_data = wp_get_theme();
			$front_page = get_option( 'page_on_front' );
			$page_for_posts = get_option( 'page_for_posts' );
			$mu_plugins = get_mu_plugins();
			$plugins = get_plugins();
			$active_plugins = get_option( 'active_plugins', array() );
			$nt_plugins = is_multisite() ? wp_get_active_network_plugins() : array();
			$ms_sites = is_multisite() ? wp_get_sites() : null;

			$snapshot = array(
				// Browser
				'browser_info' => array(
					'label' => __( 'Browser Info:', 'baton' ),
					'value' => ''
				),
				/*'browser' => array(
					'label' => __( 'Browser:', 'baton' ),
					'value' => $browser['browser']
				),
				'browser_version' => array(
					'label' => __( 'Browser Version:', 'baton' ),
					'value' => $browser['version']
				),
				'browser_platform' => array(
					'label' => __( 'Platform (Operating System):', 'baton' ),
					'value' => $browser['platform']
				),*/
				'browser_user_agent' => array(
					'label' => __( 'User Agent:', 'baton' ),
					'value' => $_SERVER['HTTP_USER_AGENT']
				),
				// Theme
				'theme' => array(
					'label' => __( 'Theme:', 'baton' ),
					'value' => $theme_data->Name . ' ' . $theme_data->Version
				),
				// Other
				'front_page' => array(
					'label' => __( 'Front Page:', 'baton' ),
					'value' => $front_page ? get_the_title( $front_page ).' (ID# '.$front_page.')'.'' : __( 'n/a', 'baton' )
				),
				'page_for_posts' => array(
					'label' => __( 'Page for Posts:', 'baton' ),
					'value' => $front_page ? get_the_title( $page_for_posts ).' (ID# '.$page_for_posts.')'.'' : __( 'n/a', 'baton' )
				),
				'display_errors' => array(
					'label' => __( 'Display Errors:', 'baton' ),
					'value' => ini_get( 'display_errors' ) != false ? __( 'On', 'baton' ) : __( 'Off', 'baton' )
				),
				'jquery_version' => array(
					'label' => __( 'jQuery Version:', 'baton' ),
					'value' => wp_script_is( 'jquery', 'registered' ) ? $GLOBALS['wp_scripts']->registered['jquery']->ver : __( 'n/a', 'baton' )
				),
				'php_session' => array(
					'label' => __( 'PHP Session:', 'baton' ),
					'value' => isset( $_SESSION ) ? __( 'Enabled', 'baton' ) : __( 'Disabled', 'baton' )
				),
				'php_cookies' => array(
					'label' => __( 'Use Cookies:', 'baton' ),
					'value' => ini_get( 'session.use_cookies' ) ? __( 'On', 'baton' ) : __( 'Off', 'baton' )
				),
				'php_cookies_only' => array(
					'label' => __( 'Use Cookies Only:', 'baton' ),
					'value' => ini_get( 'session.use_only_cookies' ) ? __( 'On', 'baton' ) : __( 'Off', 'baton' )
				),
				'php_fsockopen' => array(
					'label' => __( 'fsockopen() Support:', 'baton' ),
					'value' => function_exists( 'fsockopen' ) ? __( 'Your server supports fsockopen.', 'baton' ) : __( 'Your server does not support fsockopen.', 'baton' )
				),
				'php_curl' => array(
					'label' => __( 'cURL Support:', 'baton' ),
					'value' => function_exists( 'curl_init' ) ? __( 'Your server supports cURL.', 'baton' ) : __( 'Your server does not support cURL.', 'baton' )
				),
				'php_soap_client' => array(
					'label' => __( 'SOAP Client Support:', 'baton' ),
					'value' => class_exists( 'SoapClient' ) ? __( 'Your server has the SOAP Client enabled.', 'baton' ) : __( 'Your server does not have the SOAP Client enabled.', 'baton' )
				),
				'php_suhosin' => array(
					'label' => __( 'SUHOSIN Support:', 'baton' ),
					'value' => extension_loaded( 'suhosin' ) ? __( 'Your server has SUHOSIN installed.', 'baton' ) : __( 'Your server does not have SUHOSIN installed.', 'baton' )
				),
				'php_open_ssl' => array(
					'label' => __( 'OpenSSL Support:', 'baton' ),
					'value' => extension_loaded('openssl') ? __( 'Your server has OpenSSL installed.', 'baton' ) : __( 'Your server does not have OpenSSL installed.', 'baton' )
				),
				'php_version' => array(
					'label' => __( 'PHP Version:', 'baton' ),
					'value' => PHP_VERSION
				),
				'mysql_version' => array(
					'label' => __( 'MySQL Version:', 'baton' ),
					'value' => $wpdb->db_version()
				),
				'server_software' => array(
					'label' => __( 'Server Software:', 'baton' ),
					'value' => $_SERVER['SERVER_SOFTWARE']
				),
				'php_memory_limit' => array(
					'label' => __( 'PHP Memory Limit:', 'baton' ),
					'value' => ini_get( 'memory_limit' )
				),
				'php_upload_max_size' => array(
					'label' => __( 'PHP Maximum Upload Size:', 'baton' ),
					'value' => ini_get( 'upload_max_filesize' )
				),
				'php_post_max_size' => array(
					'label' => __( 'PHP Maximum Post Size:', 'baton' ),
					'value' => ini_get( 'post_max_size' )
				),
				'php_max_execution_time' => array(
					'label' => __( 'PHP Maximum Execution Time:', 'baton' ),
					'value' => ini_get( 'max_execution_time' )
				),
				'php_max_input_vars' => array(
					'label' => __( 'PHP Maximum Input Variables:', 'baton' ),
					'value' => ini_get( 'max_input_vars' )
				),
				'php_session_name' => array(
					'label' => __( 'PHP Session Name:', 'baton' ),
					'value' => esc_html( ini_get( 'session.name' ) )
				),
				'php_cookie_path' => array(
					'label' => __( 'PHP Cookie Path:', 'baton' ),
					'value' => esc_html( ini_get( 'session.cookie_path' ) )
				),
				'php_save_path' => array(
					'label' => __( 'PHP Save Path:', 'baton' ),
					'value' => esc_html( ini_get( 'session.save_path' ) )
				),
				// WordPress
				'wp_site_url' => array(
					'label' => __( 'Site URL:', 'baton' ),
					'value' => site_url()
				),
				'wp_home_url' => array(
					'label' => __( 'Home URL:', 'baton' ),
					'value' => home_url()
				),
				'wp_version' => array(
					'label' => __( 'WordPress Version:', 'baton' ),
					'value' => get_bloginfo( 'version' )
				),
				'wp_permalink_structure' => array(
					'label' => __( 'Permalink Structure:', 'baton' ),
					'value' => get_option( 'permalink_structure' )
				),
				'wp_post_types' => array(
					'label' => __( 'Post Types:', 'baton' ),
					'value' => implode( ', ', get_post_types( '', 'names' ) )
				),
				'wp_post_stati' => array(
					'label' => __( 'Post Stati:', 'baton' ),
					'value' => implode( ', ', get_post_stati() )
				),
				'wp_user_count' => array(
					'label' => __( 'User Count:', 'baton' ),
					'value' => count( get_users() )
				),
				'wp_memory_limit' => array(
					'label' => __( 'Memory Limit:', 'baton' ),
					'value' => WP_MEMORY_LIMIT
				),
				'wp_prefix' => array(
					'label' => __( 'Database Prefix:', 'baton' ),
					'value' => $wpdb->base_prefix
				),
				'wp_prefix_length' => array(
					'label' => __( 'Prefix Length:', 'baton' ),
					'value' => strlen( $wpdb->prefix ) < 16 ? __( 'Acceptable', 'baton' ) : __( 'Too Long', 'baton' )
				),
				'wp_is_multisite' => array(
					'label' => __( 'Multisite:', 'baton' ),
					'value' => is_multisite() ? __( 'Yes', 'baton' ) : __( 'No', 'baton' )
				),
				'wp_is_safe_mode' => array(
					'label' => __( 'Safe Mode:', 'baton' ),
					'value' => is_multisite() ? __( 'Yes', 'baton' ) : __( 'No', 'baton' )
				),
				'wp_is_wp_debug' => array(
					'label' => __( 'WP DEBUG:', 'baton' ),
					'value' => defined( 'WP_DEBUG' ) ? WP_DEBUG ? __( 'Enabled', 'baton' ) : __( 'Disabled', 'baton' ) : __( 'Not Set', 'baton' )
				)
			);

			if ( is_multisite() ) {
				$snapshot['wp_multisite_total'] = array(
					'label' => __( 'Total Sites:', 'baton' ),
					'value' => get_blog_count()
				);

				$snapshot['wp_multisite_base'] = array(
					'label' => __( 'Base Site:', 'baton' ),
					'value' => $ms_sites[0]['domain']
				);

				$snapshot['wp_multisite_all'] = array(
					'label' => __( 'All Sites:', 'baton' ),
					'value' => ''
				);

				foreach ( $ms_sites as $site_index => $site )
					if ( $site['path'] != '/' )
						$snapshot['wp_multisite_all_' . $site_index] =array(
							'label' => sprintf( __( 'Site %1$s:', 'baton' ), $site_index ),
							'value' => $site['domain'] . $site['path']
						);
			}

			if ( $plugins && $mu_plugins )
				$snapshot['wp_total_plugin_count'] = array(
					'label' => __( 'Total Plugins:', 'baton' ),
					'value' => ( count( $plugins ) + count( $mu_plugins ) + count( $nt_plugins ) )
				);

			// output must-use plugins
			if ( $mu_plugins ) {
				$snapshot['wp_must_use_plugins'] = array(
					'label' => __( 'Must-Use Plugins:', 'baton' ),
					'value' => ''
				);

				foreach ( $mu_plugins as $mu_path => $mu_plugin )
					$snapshot['wp_must_use_plugin_' . $mu_path] = array(
						'label' => $mu_plugin['Name'],
						'value' => $mu_plugin['Version']
					);
			}

			// if multisite, grab active network as well
			if ( is_multisite() ) {
				$snapshot['wp_multisite_network_active'] = array(
					'label' => sprintf( __( 'Network Active Plugins (%1$s):', 'baton' ), count( $nt_plugins ) ),
					'value' => ''
				);

				foreach ( $nt_plugins as $plugin_path ) {
					if ( array_key_exists( $plugin_path, $nt_plugins ) )
						continue;

					$plugin = get_plugin_data( $plugin_path );

					$snapshot['wp_multisite_network_active_' . $plugin_path] = array(
						'label' => $plugin['Name'],
						'value' => $plugin['Version']
					);
				}
			}

			// output active plugins
			if ( $plugins ) {
				$snapshot['wp_active_plugins'] = array(
					'label' => sprintf( __( 'Active Plugins (%1$s):', 'baton' ), count( $active_plugins ) ),
					'value' => ''
				);

				foreach ( $plugins as $plugin_path => $plugin ) {
					if ( ! in_array( $plugin_path, $active_plugins ) )
						continue;

					$snapshot['wp_active_plugins_' . $plugin_path] = array(
						'label' => $plugin['Name'],
						'value' => $plugin['Version']
					);
				}
			}

			// output inactive plugins
			if ( $plugins ) {
				$snapshot['wp_inactive_plugins'] = array(
					'label' => sprintf( __( 'Inactive Plugins (%1$s):', 'baton' ), ( count( $plugins ) - count( $active_plugins ) ) ),
					'value' => ''
				);

				foreach ( $plugins as $plugin_path => $plugin ) {
					if ( in_array( $plugin_path, $active_plugins ) )
						continue;

					$snapshot['wp_wp_inactive_plugins_' . $plugin_path] = array(
						'label' => $plugin['Name'],
						'value' => $plugin['Version']
					);
				}
			}

			return $snapshot;
		}


		/********************
		 * Helper Functions *
		 ********************/

		/**
		 * This function returns the directory for SDS Core without a trailing slash. A relative directory
		 * can be returned by passing true for the $relative parameter.
		 */
		public static function sds_core_dir( $relative = false ) {
			// Replace backslashes on Windows machines
			$template_dir = str_replace( array( '\\\\', '\\' ), '/', get_template_directory() );
			$file_dir = str_replace( array( '\\\\', '\\' ), '/', dirname( __FILE__ ) );

			return untrailingslashit( ( $relative ) ? str_replace( $template_dir, '', $file_dir ) : $file_dir );
		}

		/**
		 * This function returns the url for SDS Core without a trailing slash.
		 */
		public static function sds_core_url() {
			return untrailingslashit( get_template_directory_uri() . self::sds_core_dir( true ) );
		}
	}


	function SDS_Theme_Options_Instance() {
		return SDS_Theme_Options::instance();
	}

	// Instantiate SDS_Theme_Options
	SDS_Theme_Options_Instance();
}