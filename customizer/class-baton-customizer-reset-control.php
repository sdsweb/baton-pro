<?php
/**
 * Baton Customizer Reset Control
 *
 * Used to reset Baton theme mods to their default values
 */

// Make sure the Customize Control class exists
if ( ! class_exists( 'WP_Customize_Control' ) )
	return false;

if ( ! class_exists( 'Baton_Customizer_Font_Size_Control' ) ) {
	final class Baton_Customizer_Reset_Control extends WP_Customize_Control {
		/*
		 * @var string
		 */
		public $version = '1.0.0';

		/*
		 * @var string
		 */
		public $button_label = 'Reset';

		/*
		 * @var string, Type of reset action to perform (JS)
		 */
		public $reset_type = 'all';

		/**
		 * Constructor
		 */
		function __construct( $manager, $id, $args ) {
			// i18n
			$this->button_label = __( 'Reset', 'baton' );

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

				<?php if ( ! empty( $this->description ) ) : ?>
					<span class="description customize-control-description"><?php echo $this->description; ?></span>
				<?php endif; ?>

				<p class="baton-customizer-reset">
					<span class="button <?php echo esc_attr( ( $this->reset_type === 'all' ) ? 'button-primary' : 'button-secondary' ); ?> <?php echo esc_attr( $this->reset_type ); ?>" data-reset-type="<?php echo esc_attr( $this->reset_type ); ?>"><?php echo $this->button_label; ?></span>
				</p>
			</div>
		<?php
		}
	}
}