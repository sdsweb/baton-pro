/**
 * Note Baton Customizer
 */

var note_baton = note_baton || {};

( function ( exports, $ ) {
	"use strict";

	// Bail if the Customizer or Note isn't initialized
	if ( ! wp || ! wp.customize || ! note_baton ) {
		return;
	}

	var api = wp.customize;

	// Note Previewer
	api.NoteBatonPreviewer = {
		// Reference to the Previewer
		previewer: false,
		// Initialization
		init: function( previewer ) {
			previewer = ( previewer === undefined ) ? this.previewer : previewer;

			// Listen for the "note-register-sidebar" event from the Previewer
			previewer.bind( 'note-register-sidebar', function( data ) {
				var note_sidebars_section = api.section( note.sidebars.customizer.section ),
					$note_baton_input,
					note_baton_val,
					post_id = data.post_id;

				// Note Sidebars
				if ( note_sidebars_section ) {
					$note_baton_input = note_sidebars_section.container.find( 'input.note-baton' );

					// Note Sidebars input (if we don't have this, we can't save the data)
					if ( $note_baton_input.length ) {
						// Store a reference to the current value (parsed)
						note_baton_val = JSON.parse( $note_baton_input.val() );

						// If the value is an array, convert it to an object for JSON.stringify() to work properly
						if ( note_baton_val.constructor === Array && _.isEmpty( note_baton_val ) ) {
							note_baton_val = {};
						}

						// If this "post_id" doesn't already contain data
						if ( ! note_baton_val.hasOwnProperty( post_id ) ) {
							// Taxonomy (term)
							if ( note_baton.hasOwnProperty( 'is_tax' ) && note_baton.is_tax === '1' ) {
								note_baton_val[post_id] = {
									'tax_name': note_baton.taxonomy.name,
									'tax_label': note_baton.taxonomy.labels.name,
									'term_id': note_baton.queried_object.term_id,
									'term_label': note_baton.queried_object.name
								};
							}
							// Post Type Archive
							else if ( note_baton.hasOwnProperty( 'is_post_type_archive' ) && note_baton.is_post_type_archive === '1' ) {
								note_baton_val[post_id] = {
									'name': note_baton.queried_object.name,
									'label': note_baton.queried_object.labels.name
								};
							}

							// Stringify the value
							note_baton_val = JSON.stringify( note_baton_val );

							// Update the data string (compare the data string to current data)
							if ( $note_baton_input.val() !== note_baton_val ) {
								// Add data string to Note Baton setting (hidden input elements do not automatically trigger the "change" method)
								$note_baton_input.val( note_baton_val ).trigger( 'change' );
							}
						}
					}
				}
			} );

			// Listen for the "note-unregister-sidebar" event from the Previewer
			previewer.bind( 'note-unregister-sidebar', function( data ) {
				var note_sidebars_section = api.section( note.sidebars.customizer.section ),
					$note_baton_input,
					note_baton_val,
					post_id = data.post_id;

				// Note Sidebars
				if ( note_sidebars_section ) {
					$note_baton_input = note_sidebars_section.container.find( 'input.note-baton' );

					// Note Sidebars input (if we don't have this, we can't save the data)
					if ( $note_baton_input.length ) {
						// Store a reference to the current value (parsed)
						note_baton_val = JSON.parse( $note_baton_input.val() );

						// Bail if we have an empty value
						if ( _.isEmpty( note_baton_val ) ) {
							return;
						}

						// If this "post_id" doesn't already contain data
						if ( note_baton_val.hasOwnProperty( post_id ) ) {
							// Taxonomy (term) or Post Type Archive
							if ( ( note_baton.hasOwnProperty( 'is_tax' ) && note_baton.is_tax === '1' ) || ( note_baton.hasOwnProperty( 'is_post_type_archive' ) && note_baton.is_post_type_archive === '1' ) ) {
								// If there are no sidebars registered
								if ( ! note.sidebars.registered[post_id].length ) {
									// Remove the data
									delete note_baton_val[post_id];
								}
							}

							// Stringify the value
							note_baton_val = JSON.stringify( note_baton_val );

							// Update the data string (compare the data string to current data)
							if ( $note_baton_input.val() !== note_baton_val ) {
								// Add data string to Note Baton setting (hidden input elements do not automatically trigger the "change" method)
								$note_baton_input.val( note_baton_val ).trigger( 'change' );
							}
						}
					}
				}
			} );

			// Listen for the "note-baton-args" event from the Previewer
			previewer.bind( 'note-baton-args', function( data ) {
				// Replace note_baton data with updated data from the Previewer (contains content specific data)
				note_baton = data;
			} );
		}
	};

	// When the API is "ready"
	api.bind( 'ready', function() {
		// Initialize our Previewer
		api.NoteBatonPreviewer.init( api.previewer );
	} );
} )( wp, jQuery );