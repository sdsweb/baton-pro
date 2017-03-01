<?php

// Make sure the Customize Control class exists
if ( ! class_exists( 'WP_Customize_Control' ) )
	return false;

/**
 * This class is a custom controller for the Theme Customizer API for Slocum Themes
 * which extends the WP_Customize_Control class provided by WordPress.
 */
// TODO: class_exists() check
class SDS_Theme_Options_Customize_Web_Font_Control extends WP_Customize_Control {
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
	?>
		<div class="sds-theme-options-web-fonts-wrap customize-sds-theme-options-web-fonts-wrap">
			<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>

			<?php if ( ! empty( $this->description ) ) : ?>
				<span class="description customize-control-description"><?php echo $this->description; ?></span>
			<?php endif; ?>

			<div class="sds-theme-options-web-font sds-theme-options-web-font-default">
				<label>
					<input type="radio" id="sds_theme_options_web_font_default" name="sds_theme_options[web_font]" <?php checked( ! $this->value() || $this->value() === 'default' ); ?> value="default" <?php $this->link(); ?> />
					<div class="sds-theme-options-web-font-selected">&nbsp;</div>
				</label>
				<span class="sds-theme-options-web-font-label-default"><?php _e( 'Default', 'baton-pro' ); ?></span>
			</div>

			<?php
				foreach ( sds_web_fonts() as $name => $atts ) :
					$css_name = strtolower( str_replace( array( '+', ':' ), '-', $name ) );
			?>
					<div class="sds-theme-options-web-font sds-theme-options-web-font-<?php echo $css_name; ?>" style="<?php echo ( isset( $atts['css'] ) && ! empty( $atts['css'] ) ) ? $atts['css'] : false; ?>">
						<label>
							<input type="radio" id="sds_theme_options_web_font_name_<?php echo $css_name; ?>" name="sds_theme_options[web_font]" <?php checked( $this->value() === $name ); ?> value="<?php echo $name; ?>" <?php $this->link(); ?> />
							<div class="sds-theme-options-web-font-selected">&nbsp;</div>
						</label>
						<span class="sds-theme-options-web-font-label"><?php echo ( isset( $atts['label'] ) ) ? $atts['label'] : false; ?></span>
						<span class="sds-theme-options-web-font-preview"><?php _e( 'Grumpy wizards make toxic brew for the evil Queen and Jack.', 'baton-pro' ); ?></span>
					</div>
			<?php
				endforeach;
			?>
		</div>
	<?php
	}
}