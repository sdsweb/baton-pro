/**
 * Note Baton Previewer
 */

( function ( wp, $ ) {
	'use strict';

	// Bail if the customizer isn't initialized
	if ( ! wp || ! wp.customize ) {
		return;
	}

	var api = wp.customize, OldPreview;

	// Note Preview
	api.NoteBatonPreview = {
		preview: null, // Instance of the Previewer
		// Initialization
		init: function () {
			var self = this;

			// When the previewer is active
			this.preview.bind( 'active', function() {
				// Send the 'note-baton-args' data to the Customizer (specific to the page being displayed)
				self.preview.send( 'note-baton-args', note_baton );
			} );
		}
	};

	/**
	 * Capture the instance of the Preview since it is private
	 */
	OldPreview = api.Preview;
	api.Preview = OldPreview.extend( {
		initialize: function( params, options ) {
			api.NoteBatonPreview.preview = this;
			OldPreview.prototype.initialize.call( this, params, options );
		}
	} );
} )( wp, jQuery );