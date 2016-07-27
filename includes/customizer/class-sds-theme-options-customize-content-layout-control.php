<?php

// Make sure the Customize Control class exists
if ( ! class_exists( 'WP_Customize_Control' ) )
	return false;

/**
 * This class is a custom controller for the Customizer API for Slocum Themes
 * which extends the WP_Customize_Control class provided by WordPress.
 */
// TODO: class_exists() check
class SDS_Theme_Options_Customize_Content_Layout_Control extends WP_Customize_Control {
	/*
	 * @var string
	 */
	public $content_layout_id = '';

	/*
	 * @var string
	 */
	public $custom_field_type = false;

	/**
	 * Constructor
	 */
	function __construct( $manager, $id, $args ) {
		// Call the parent constructor here
		parent::__construct( $manager, $id, $args );
	}

	/**
	 * This function renders the control's content.
	 */
	public function render_content() {
		$sds_theme_options_instance = SDS_Theme_Options_Instance();
	?>
		<div class="customize-sds-theme-options-content-layout-wrap customize-sds-theme-options-content-layout-<?php echo esc_attr( $this->content_layout_id ); ?>-wrap">
			<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>

			<?php $sds_theme_options_instance->content_layouts_field( $this->content_layout_id, $this->description, $this->custom_field_type, $this ); ?>
		</div>
	<?php
	}
}