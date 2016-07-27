<?php

// Make sure the Customize Image Control class exists
if ( ! class_exists( 'WP_Customize_Image_Control' ) )
	return false;

/**
 * This class is a custom controller for the Theme Customizer API for Slocum Themes
 * which extends the WP_Customize_Image_Control class provided by WordPress.
 */
// TODO: class_exists() check
class SDS_Theme_Options_Customize_Logo_Control extends WP_Customize_Image_Control {
	/**
	 * Constructor
	 */
	function __construct( $manager, $id, $args ) {
		// Just calling the parent constructor here
		parent::__construct( $manager, $id, $args );
	}

	/**
	 * This function enqueues scripts and styles
	 */
	public function enqueue() {
		wp_enqueue_media(); // Enqueue media scripts
		wp_enqueue_script( 'sds-theme-options-customizer-logo', SDS_Theme_Options::sds_core_url() . '/js/customizer-sds-theme-options-logo.js', array( 'customize-base', 'customize-controls' ), SDS_Theme_Options::VERSION );

		// Call the parent enqueue method here
		parent::enqueue();
	}

	/**
	 * This function renders the control's content.
	 */
	public function render_content() {
		// Grab the SDS Theme Options instance
		$sds_theme_options_instance = SDS_Theme_Options_Instance();
	?>
		<div class="customize-image-picker customize-sds-theme-options-logo-upload">
			<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			<?php $sds_theme_options_instance->sds_theme_options_logo_field( true ); ?>
		</div>
	<?php
	}
}