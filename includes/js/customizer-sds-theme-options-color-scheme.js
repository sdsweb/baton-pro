/**
 * SDS Theme Options Color Scheme
 */

window.wp = window.wp || {};

( function( exports, $, sds_color_schemes_customizer ) {
	"use strict";

	var api = wp.customize;

	// Document ready
	$( function() {
		var color_controls = [];

		// Loop through color scheme customizer controls
		_.each( sds_color_schemes_customizer['controls'], function( control_id ) {
			var control = api.control( control_id );

			// If we have a control
			if ( control ) {
				color_controls.push( control );
			}
		} );

		// SDS Theme Options Color Scheme
		api( 'sds_theme_options[color_scheme]' ).bind( function( to, from ) {
			var to_color_scheme = ( ! to || to === 'default' ) ? sds_color_schemes_customizer['color_schemes']['default'] : sds_color_schemes_customizer['color_schemes'][to],
				from_color_scheme = ( ! from || from === 'default' ) ? sds_color_schemes_customizer['color_schemes']['default'] : sds_color_schemes_customizer['color_schemes'][from];

			// Loop through the color controls
			_.each( color_controls, function( control ) {
				var from_default_color = ( from_color_scheme.hasOwnProperty( 'preview' ) ) ? from_color_scheme.preview : false,
					to_default_color = ( to_color_scheme.hasOwnProperty( 'preview' ) ) ? to_color_scheme.preview : false,
					color = control.setting();

				// If the from color scheme has the property
				if ( from_color_scheme.hasOwnProperty( control.id ) ) {
					// Adjust the default color value
					from_default_color = from_color_scheme[control.id];
				}

				// If the to color scheme has the property
				if ( to_color_scheme.hasOwnProperty( control.id ) ) {
					// Adjust the default color value
					to_default_color = to_color_scheme[control.id];
				}

				// If we have a to color scheme default color
				if ( to_default_color ) {
					// Update default color values
					control.container.find( '.color-picker-hex' ).data( 'data-default-color', to_default_color ).wpColorPicker( 'defaultColor', to_default_color );

					// If we have a default color and it matches the current value
					if ( from_default_color && from_default_color === color ) {
						// Update setting
						control.setting.set( to_default_color );
					}
				}
			} );
		} );
	} );
} )( wp, jQuery, sds_color_schemes_customizer );