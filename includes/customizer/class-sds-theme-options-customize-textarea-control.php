<?php

// Make sure the Customize Control class exists
if ( ! class_exists( 'WP_Customize_Control' ) )
	return false;

/**
 * This class is a custom controller for the Theme Customizer API for Slocum Themes
 * which extends the WP_Customize_Control class provided by WordPress.
 */
// TODO: class_exists() check
class SDS_Theme_Options_Customize_Textarea_Control extends WP_Customize_Control {
	/*
	 * @var int, Number of rows (attribute) for the <textarea> element
	 */
	public $rows = 5;

	/*
	 * @var string, Type of text area ('text', 'css' for textareas that should have CSS code editors attached to them)
	 */
	public $mode = 'plaintext';

	/*
	 * @var array, Available modes for CodeMirror (corresponding with $mode above)
	 */
	private $modes = array(
		'plaintext' => 'plaintext',
		'css' => 'text/css',
		'js' => 'text/javascript'
	);


	/**
	 * Constructor
	 */
	function __construct( $manager, $id, $args ) {
		// Make sure we have a valid mode (default to plain)
		if ( ! array_key_exists( $this->mode, $this->modes ) )
			$this->mode = 'plaintext';

		// Call the parent constructor here
		parent::__construct( $manager, $id, $args );
	}

	/**
	 * This function enqueues scripts and styles
	 */
	public function enqueue() {
		// CSS or JavaScript Textareas
		if ( $this->mode === 'css' || $this->mode === 'js' ) {
			// SDS Theme Options CodeMirror
			wp_enqueue_style( 'sds-theme-options-codemirror', SDS_Theme_Options::sds_core_url() . '/css/codemirror.min.css', false, SDS_Theme_Options::VERSION );

			// SDS Theme Options CodeMirror
			wp_enqueue_script( 'sds-theme-options-codemirror', SDS_Theme_Options::sds_core_url() . '/js/codemirror.min.js', false, SDS_Theme_Options::VERSION );

			// SDS Theme Options Customizer CodeMirror
			wp_enqueue_script( 'sds-theme-options-customizer-codemirror', SDS_Theme_Options::sds_core_url() . '/js/customizer-sds-theme-options-codemirror.js', array( 'sds-theme-options-codemirror', 'customize-base', 'customize-controls' ), SDS_Theme_Options::VERSION );
		}

		// Call the parent enqueue method here
		parent::enqueue();
	}

	/**
	 * This function renders the control's content.
	 */
	public function render_content() {
	?>
		<label>
			<?php if ( ! empty( $this->label ) ) : ?>
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			<?php endif; ?>

			<?php if ( ! empty( $this->description ) ) : ?>
				<span class="description customize-control-description"><?php echo $this->description; ?></span>
			<?php endif; ?>

			<textarea rows="<?php echo esc_attr( $this->rows ); ?>" class="sds-theme-options-textarea sds-theme-options-<?php echo esc_attr( $this->mode ); ?>-textarea" data-mode="<?php echo esc_attr( $this->modes[$this->mode] ); ?>" <?php $this->link(); ?>><?php echo esc_textarea( $this->value() ); ?></textarea>
		</label>
	<?php
	}
}