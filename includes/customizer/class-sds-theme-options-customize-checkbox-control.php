<?php

// Make sure the Customize Control class exists
if ( ! class_exists( 'WP_Customize_Control' ) )
	return false;

/**
 * This class is a custom controller for the Customizer API for Slocum Themes
 * which extends the WP_Customize_Control class provided by WordPress.
 */
// TODO: class_exists() check
class SDS_Theme_Options_Customize_Checkbox_Control extends WP_Customize_Control {
	/*
	 * @var string, Type of checkbox control
	 */
	public $checkbox_type = 'show-hide';

	/*
	 * @var string, CSS ID used to target this particular control
	 */
	public $css_id = '';

	/*
	 * @var string, CSS class used to target this particular control
	 */
	public $css_class = '';

	/*
	 * @var string, Label for the "checked" state of the checkbox
	 */
	public $checked_label = 'Hide';

	/*
	 * @var string, Label for the "unchecked" state of the checkbox
	 */
	public $unchecked_label = 'Show';

	/*
	 * @var string, CSS <style> block with styles for this particular control
	 */
	public $style = array( 'before' => '', 'after' => '', 'general' => '' );

	/**
	 * Constructor
	 */
	function __construct( $manager, $id, $args ) {
		// i18n
		$this->checked_label = __( 'Show', 'baton-pro' );
		$this->unchecked_label = __( 'Hide', 'baton-pro' );

		// Actions/ Filters
		add_action( 'customize_controls_print_styles', array( $this, 'customize_controls_print_styles' ) ); // Output styles on Customizer

		// Call the parent constructor here
		parent::__construct( $manager, $id, $args );
	}

	/**
	 * This function renders the control's content.
	 */
	public function render_content() {
	?>
		<div class="customize-checkbox customize-sds-theme-options-checkbox">
			<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>

			<?php if ( isset( $this->style['before'] ) || isset( $this->style['after'] ) || isset( $this->style['general'] ) ) : ?>
				<style type="text/css">
					<?php
						// Before
						echo ( isset( $this->style['before'] ) ) ? '.' . $this->css_class .':before { ' . $this->style['before']. ' }' : false;
					?>
					<?php
						// After
						echo ( isset( $this->style['after'] ) ) ? '.' . $this->css_class .':after { ' . $this->style['after']. ' }' : false;
					?>
					<?php
						// General
						echo ( isset( $this->style['general'] ) ) ? '.' . $this->css_class .' { ' . $this->style['general']. ' }' : false;
					?>
				</style>
			<?php endif; ?>

			<div class="checkbox sds-theme-options-checkbox checkbox-<?php echo esc_attr( $this->checkbox_type ); ?> <?php echo esc_attr( $this->css_class ); ?>" data-label-left="<?php echo esc_attr( $this->checked_label ); ?>" data-label-right="<?php echo esc_attr( $this->unchecked_label ); ?>">
				<input type="checkbox" id="<?php echo esc_attr( $this->css_id ); ?>" name="<?php echo esc_attr( $this->id ); ?>" <?php checked( ( $this->checkbox_type === 'show-hide' ) ? ! $this->value() : $this->value() ); ?> <?php $this->link(); ?> />
				<label for="<?php echo esc_attr( $this->css_id ); ?>" title="<?php echo esc_attr( $this->label ); ?>">| | |</label>
			</div>

			<?php if ( ! empty( $this->description ) ) : ?>
				<span class="description customize-control-description"><?php echo $this->description; ?></span>
			<?php endif; ?>
		</div>
	<?php
	}

	/**
	 * This function prints styles on the Customizer only.
	 */
	function customize_controls_print_styles() {
		global $_wp_admin_css_colors;

		$user_admin_color = get_user_meta(  get_current_user_id(), 'admin_color', true );

		// Output styles to match selected admin color scheme
		if ( isset( $_wp_admin_css_colors[$user_admin_color] ) ) :
	?>
			<style type="text/css">
				/* Checkboxes */
				.customize-sds-theme-options-checkbox .sds-theme-options-checkbox:before {
					background: <?php echo $_wp_admin_css_colors[$user_admin_color]->colors[2]; ?>;
				}
			</style>
	<?php
		endif;
	}
}