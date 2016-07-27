<?php
/**
 * Baton Customizer Font Family Control
 */

// Bail if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

// Make sure the Customize Control class exists
if ( ! class_exists( 'WP_Customize_Control' ) )
	return false;

if ( ! class_exists( 'Baton_Customizer_Font_Family_Control' ) ) {
	final class Baton_Customizer_Font_Family_Control extends WP_Customize_Control {
		/**
		 * @var string
		 */
		public $version = '1.0.0';

		/**
		 * @var string, cached <select> choices value
		 */
		public $cached_choices = '';

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
			// Bail if we have no choices
			if ( empty( $this->choices ) )
				return;
		?>
			<label>
				<?php
					if ( ! empty( $this->label ) ) :
				?>
					<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
				<?php
					endif;

					if ( ! empty( $this->description ) ) :
				?>
						<span class="description customize-control-description"><?php echo $this->description; ?></span>
				<?php
					endif;
				?>

				<select <?php $this->link(); ?> class="baton-font-family-select">
					<?php
						// Check cache first
						if ( ! $this->cached_choices = wp_cache_get( 'google_fonts_select_choices', 'baton' ) ) {
							// Build choices
							foreach ( $this->choices as $value => $label )
								// Since the Customizer requires JS, we can skip the "selected" attribut here
								$this->cached_choices .= '<option value="' . esc_attr( $value ) . '">' . $label . '</option>';

							// Store cache
							wp_cache_add( 'google_fonts_select_choices', $this->cached_choices, 'baton' );
						}

						// Output the cached choices
						echo $this->cached_choices;
					?>
				</select>
			</label>
		<?php
		}
	}
}