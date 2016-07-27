<?php
/**
 * Baton Customizer Font Size Control
 */

// Bail if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

// Make sure the Customize Control class exists
if ( ! class_exists( 'WP_Customize_Control' ) )
	return false;

if ( ! class_exists( 'Baton_Customizer_Font_Size_Control' ) ) {
	final class Baton_Customizer_Font_Size_Control extends WP_Customize_Control {
		/**
		 * @var string
		 */
		public $version = '1.0.0';

		/**
		 * @var array
		 */
		public $input_attrs = array();

		/**
		 * @var string
		 */
		public $unit = 'px';

		/**
		 * @var string
		 */
		public $units = array(
			'value' => 'px'
		);

		/**
		 * This function sets up all of the actions and filters on instance. It also loads (includes)
		 * the required files and assets.
		 */
		function __construct( $manager, $id, $args = array() ) {
			// Hooks
			// TODO

			// Call the parent constructor here
			parent::__construct( $manager, $id, $args );
		}

		/**
		 * This function enqueues scripts and styles
		 */
		public function enqueue() {
			// Stylesheets
			// TODO

			// Scripts
			// TODO

			// Call the parent enqueue method here
			parent::enqueue();
		}

		/**
		 * This function renders the control's content.
		 *
		 * License: GPLv2 or later
		 * Copyright: WordPress Core, http://wordpress.org/
		 *
		 * @link https://github.com/WordPress/WordPress/blob/6b51f14c6f964918a9f92e780aa29871848c7dc8/wp-includes/class-wp-customize-control.php#L490
		 *
		 * We've used WordPress' functionality as a base and modified it to suit our needs.
		 */
		public function render_content() {
		?>
			<label>
				<?php if ( ! empty( $this->label ) ) : ?>
					<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
				<?php endif;
				if ( ! empty( $this->description ) ) : ?>
					<span class="description customize-control-description"><?php echo $this->description; ?></span>
				<?php endif; ?>
				<input type="<?php echo esc_attr( $this->type ); ?>" <?php $this->input_attrs(); ?> value="<?php echo esc_attr( $this->value() ); ?>" <?php $this->link(); ?> />
				<?php if ( ! empty( $this->units ) ) : ?>
					<span class="units" title="<?php echo ( isset( $this->units['title'] ) ) ? esc_attr( $this->units['title'] ) : false; ?>"><?php echo $this->unit; ?></span>
				<?php endif; ?>
			</label>
		<?php
		}

		/**
		 * Render the custom attributes for the control's input element.
		 *
		 * License: GPLv2 or later
		 * Copyright: WordPress Core, http://wordpress.org/
		 *
		 * @link https://github.com/WordPress/WordPress/blob/6b51f14c6f964918a9f92e780aa29871848c7dc8/wp-includes/class-wp-customize-control.php#L376-L386
		 */
		public function input_attrs() {
			foreach( $this->input_attrs as $attr => $value ) {
				echo $attr . '="' . esc_attr( $value ) . '" ';
			}
		}
	}
}