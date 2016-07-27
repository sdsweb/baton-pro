<?php

// Make sure the Customize Control class exists
if ( ! class_exists( 'WP_Customize_Control' ) )
	return false;

/**
 * This class is a custom controller for the Customizer API for Slocum Themes
 * which extends the WP_Customize_Control class provided by WordPress.
 */
// TODO: class_exists() check
class SDS_Theme_Options_Customize_Color_Scheme_Control extends WP_Customize_Control {
	/*
	 * @var array, Customizer Control IDs to adjust defaults values upon color scheme selection
	 */
	public $color_controls = array();

	/**
	 * Constructor
	 */
	function __construct( $manager, $id, $args ) {
		// Call the parent constructor here
		parent::__construct( $manager, $id, $args );
	}

	/**
	 * This function enqueues scripts and styles
	 */
	public function enqueue() {
		wp_enqueue_script( 'sds-theme-options-customizer-color-scheme', SDS_Theme_Options::sds_core_url() . '/js/customizer-sds-theme-options-color-scheme.js', array( 'customize-base', 'customize-controls' ), SDS_Theme_Options::VERSION );
		wp_localize_script( 'sds-theme-options-customizer-color-scheme', 'sds_color_schemes_customizer', array(
			'color_schemes' => sds_color_schemes(),
			'controls' => $this->color_controls
		) );

		// Call the parent enqueue method here
		parent::enqueue();
	}

	/**
	 * This function renders the control's content.
	 */
	public function render_content() {
	?>
		<div class="sds-theme-options-color-schemes-wrap customize-sds-theme-options-color-schemes-wrap">
			<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>

			<?php if ( ! empty( $this->description ) ) : ?>
				<span class="description customize-control-description"><?php echo $this->description; ?></span>
			<?php endif; ?>

			<?php foreach ( sds_color_schemes() as $name => $atts ) :	?>
				<div class="sds-theme-options-color-scheme sds-theme-options-color-scheme-<?php echo $name; ?>">
					<label>
						<input type="radio" id="sds_theme_options_color_scheme_<?php echo $name; ?>" name="sds_theme_options[color_scheme]" <?php checked( ( ( ! $this->value() && isset( $atts['default'] ) && $atts['default'] ) || ( $this->value() === $name ) ) ); ?> value="<?php echo esc_attr( $name ); ?>" <?php $this->link(); ?> />

						<?php if ( isset( $atts['preview'] ) && ! empty( $atts['preview'] ) ) : ?>
							<div class="sds-theme-options-color-scheme-preview" style="background: <?php echo esc_attr( $atts['preview'] ); ?>">&nbsp;</div>
						<?php endif; ?>

						<?php echo ( isset( $atts['label'] ) ) ? $atts['label'] : false; ?>
					</label>
				</div>
			<?php endforeach; ?>
		</div>
	<?php
	}
}