/**
 * SDS Theme Options CodeMirror
 */

( function ( $, wp, CodeMirror ) {
	"use strict";

	var api = wp.customize;

	/*
	 * Document ready
	 */
	$( function() {
		var $textareas = $( '.sds-theme-options-textarea' ).filter( function() {
			return $( this ).data( 'mode' ) !== 'plaintext'; // Ignore plaintext textareas
		} );

		// Bind an event listener to each section's expanded state
		api.section.each( function( section ) {
			section.expanded.bind( function( expanded ) {
				// Only if this section was just expanded (controls are visible)
				if ( expanded ) {
					var controls = section.controls();

					// Loop through controls within this section
					_.each( controls, function ( control ) {
						// If this is a SDS CodeMirror control
						if ( control.params.type === 'sds_codemirror' ) {
							var $textarea = control.container.find( 'textarea'),
								cm = $textarea.data( 'sds_codemirror' );

							// If we have a CodeMirror instance
							if ( cm ) {
								// Refresh the CodeMirror editor
								cm.refresh();
							}
						}
					} );
				}
			} );
		} );


		/**
		 * CodeMirror
		 */
		if ( CodeMirror ) {
			// If we have any textareas
			if ( $textareas.length ) {
				// Create a debounced
				var changeEventCallback = _.debounce( function( instance ) {
					// Copy the value from CodeMirror to the textarea
					instance.save();

					// Trigger the change event on the textare (hidden input elements do not automatically trigger the "change" event in browsers)
					$( instance.getTextArea() ).trigger( 'change' );
				}, 500 );

				// Loop through textareas and apply CodeMirror
				$textareas.each( function() {
					var $this = $( this ),
						$customize_control_title = $this.prev( '.customize-control-title' ),
						cm;

					// CodeMirror
					cm = CodeMirror.fromTextArea( this, {
						mode: $this.data( 'mode' ),
						indentUnit: 4,
						lineNumbers: true,
						indentWithTabs: true,
						lineWrapping: true
					} );

					// Listen for changes to CodeMirror instances
					cm.on( 'change', changeEventCallback );

					// Listen to click events on the parent label
					if ( $customize_control_title.length ) {
						$customize_control_title.on( 'click', function() {
							// Focus the CodeMirror editor
							cm.focus();
						} );
					}

					// Store a reference to CodeMirror on the element in jQuery data
					$this.data( 'sds_codemirror', cm );
				} );
			}
		}
	} );
}( jQuery, wp, CodeMirror ) );