/**
 * Baton Customizer Controls
 */

window.wp = window.wp || {};
window.baton_customize_controls = window.baton_customize_controls || {};

( function ( wp, $, baton_customize_controls ) {
	'use strict';

	// Bail if the customizer isn't initialized
	if ( ! wp || ! wp.customize ) {
		return;
	}

	var api = wp.customize;

	/*
	 * Baton Customize Controls
	 */
	api.BatonCustomizeControls = {
		has_font_support: ( baton_customize_controls.hasOwnProperty( 'theme_support' ) && baton_customize_controls.theme_support.hasOwnProperty( 'fonts' ) && baton_customize_controls.theme_support.fonts ),
		baton_font_family_regex: false,
		baton_font_families: baton_customize_controls.baton_font_families || {},
		baton_font_family_choices: '',
		sds_color_schemes: baton_customize_controls.sds_color_schemes || {},
		sds_color_scheme_control_ids: baton_customize_controls.sds_color_scheme_control_ids || {},
		sds_color_scheme_setting_id: baton_customize_controls.sds_color_scheme_setting_id || false,
		baton_background_image_control_ids: baton_customize_controls.baton_background_image_control_ids || {},
		baton_font_family_control_ids: baton_customize_controls.baton_font_family_control_ids || {},
		baton_font_property_control_ids: baton_customize_controls.baton_font_property_control_ids || {},
		controls: {
			colors: [],
			background_images: [],
			font_properties: [],
			font_families: []
		},
		control_default_values: {
			colors: baton_customize_controls.sds_color_schemes || {},
			background_images: baton_customize_controls.baton_background_image_defaults || '',
			font_properties: baton_customize_controls.baton_font_property_defaults || {},
			font_families: baton_customize_controls.baton_font_family_defaults || {}
		},
		background_visibility: {},
		background_control_id_suffixes: [
			'_repeat',
			'_position_x',
			'_attachment'
		],
		// Initialize
		init: function() {
			// If we have font support
			if ( this.has_font_support ) {
				// Setup the font family regex
				this.baton_font_family_regex = new RegExp( baton_customize_controls.baton_font_family_regex );
			}

			// Initialize controls (setup references to Customizer controls)
			this.initControls();

			// Initialize background control visibility
			this.initBackgroundControlVisibility();

			// Initialize font family selections
			this.initFontFamilySelections();
		},
		// Initialize (fetch) controls and store a reference to them
		initControls: function() {
			var self = this;

			/*
			 * Colors
			 */

			// If we don't have color controls yet
			if ( ! this.controls.colors.length ) {
				// Loop through color scheme customizer controls
				_.each( this.sds_color_scheme_control_ids, function( control_id ) {
					// Push this control to the colors array
					self.maybePushControl( control_id, self.controls.colors );
				} );
			}

			/*
			 * Background Images
			 */

			// If we don't have background image controls yet
			if ( ! this.controls.background_images.length ) {
				// Loop through background image customizer controls
				_.each( this.baton_background_image_control_ids, function( control_id ) {
					// Push this control to the background images
					self.maybePushControl( control_id, self.controls.background_images );
				} );
			}

			/*
			 * Font Properties (font size, letter spacing, and line height)
			 */

			// If we don't have font property controls yet
			if ( ! this.controls.font_properties.length ) {
				// Loop through font property customizer controls
				_.each( this.baton_font_property_control_ids, function( control_id ) {
					// Push this control to the background images
					self.maybePushControl( control_id, self.controls.font_properties );
				} );
			}

			/*
			 * Font Families
			 */

			// If we don't have font family controls yet
			if ( ! this.controls.font_families.length ) {
				// Loop through font family customizer controls
				_.each( this.baton_font_family_control_ids, function( control_id ) {
					// Push this control to the background images
					self.maybePushControl( control_id, self.controls.font_families );
				} );
			}
		},
		// Initialize background control visibility
		initBackgroundControlVisibility: function() {
			var self = this;

			// If we don't have background visibility references setup
			if ( _.isEmpty( this.background_visibility ) ) {
				// Loop through background image customizer controls
				_.each( this.baton_background_image_control_ids, function( control_id ) {
					// If this is a Baton background image control ID
					if ( control_id.indexOf( 'baton' ) !== -1 ) {
						// Create the property
						self.background_visibility[control_id] = [];

						// Loop through background control ID suffixes
						_.each( self.background_control_id_suffixes, function( suffix ) {
							// Add this control ID to the property
							self.background_visibility[control_id].push( control_id + suffix );
						} );
					}
				} );
			}

			// Loop through all background visibility data
			_.each( this.background_visibility, function( control_ids, setting_id ) {
				// Grab the setting
				api( setting_id, function( setting ) {
					// Loop through control IDs for this setting
					_.each( control_ids, function( control_id ) {
						// Grab the control
						api.control( control_id, function( control ) {
							// Create a visibility callback
							var visibility = function( to ) {
								control.container.toggle( self.backgroundControlVisibility( to ) );
							};

							// Grab the visibility for this setting
							visibility( setting.get() );

							// Bind the visibility callback to the setting "change" event
							setting.bind( visibility );
						} );
					} );
				} );
			} )
		},
		// Initialize font family selections (also initialize Select2)
		initFontFamilySelections: function() {
			var self = this;

			// If we have Baton font support
			if ( this.has_font_support && this.baton_font_family_regex ) {
				// If we don't have font family choices
				if ( this.baton_font_family_choices.length === 0 ) {
					// Loop through font families
					_.each( this.baton_font_families, function( font_family, font_name ) {
						// String
						if ( typeof font_family === 'string' ) {
							self.baton_font_family_choices += '<option value="' + font_name + '"';
							self.baton_font_family_choices += ( ! font_name ) ? ' disabled="true">' : '>';
							self.baton_font_family_choices += font_family + '</option>';
						}
						// Object
						else {
							self.baton_font_family_choices += ( font_family.hasOwnProperty( 'class' ) ) ? '<option value="' + font_name + '" class="baton-select2-result ' + font_family.class + '"' : '<option value="' + font_name + '" class="baton-select2-result"';
							self.baton_font_family_choices += ( ! font_name ) ? ' disabled="true">' : '>';
							self.baton_font_family_choices += ( font_family.hasOwnProperty( 'family' ) ) ? font_family.family + '</option>' : font_name + '</option>';
						}
					} );
				}

				// Populate font family choices
				api.control.each( function ( control ) {
					// Find Baton Font Family Controls
					if ( control.id.search( self.baton_font_family_regex ) !== -1 ) {
						// Grab the select element within this control
						var $select = control.container.find( 'select' );

						// Populate this control with choices and set the value
						$select.html( self.baton_font_family_choices ).val( control.setting.get() );

						// Setup Select2
						setTimeout( function() {
							$select.baton_select2();
						}, 500 );
					}
				} );
			}
		},
		// Maybe push a control to reference list of controls
		maybePushControl: function( control_id, controls ) {
			// Grab the control object for this control ID
			var control = api.control( control_id );

			// If we have a valid control
			if ( control ) {
				// Add it to the list
				controls.push( control );
			}
		},
		// Background control visibility callback
		backgroundControlVisibility: function( to ) {
			return !! to;
		},
		// Reset control values
		resetControlValues: function( type ) {
			var self = this,
				controls = this.controls[type],
				defaults = this.control_default_values[type],
				default_value = false;

			// Type
			type = type || 'all';

			// If this is a colors reset
			if ( type === 'colors' ) {
				defaults = defaults[api( this.sds_color_scheme_setting_id )()];
			}

			// If we have controls and defaults
			// TODO: What should we do if defaults actually is false?
			if ( controls && controls.length && ( defaults.constructor === Object && ! _.isEmpty( defaults ) || defaults !== false ) ) {
				// Loop through the controls
				_.each( controls, function( control ) {
					// If this is a colors reset
					if ( type === 'colors' && ( defaults.hasOwnProperty( 'preview' ) ) ) {
						// Grab the preview color as a fallback
						default_value = defaults['preview'];
					}

					// If the current set of defaults contains a value for this control ID
					if ( defaults.constructor === Object && defaults.hasOwnProperty( control.id ) ) {
						// Adjust the default color value
						default_value = defaults[control.id];
					}
					// Otherwise treat the defaults as it's own value
					else {
						default_value = defaults;
					}

					// If we have a default value
					// TODO: What should we do if a default value actually is false?
					if ( default_value !== false ) {
						// Reset this control
						self.resetControlValue( control, default_value, type );
					}
				} );
			}
			// Otherwise if we're resetting all controls
			else if ( type === 'all' ) {
				// Loop through control sets
				_.each( this.controls, function ( controls, type ) {
					// Setup the defaults reference
					defaults = self.control_default_values[type];

					// If this is a colors reset
					if ( type === 'colors' ) {
						defaults = defaults[api( self.sds_color_scheme_setting_id )()];
					}

					// If we have controls and defaults
					if ( controls && controls.length && ( defaults.constructor === Object && ! _.isEmpty( defaults ) || defaults !== false ) ) {
						// Loop through the controls
						_.each( controls, function( control ) {
							// If this is a colors reset
							if ( type === 'colors' && ( defaults.hasOwnProperty( 'preview' ) ) ) {
								// Grab the preview color as a fallback
								default_value = defaults['preview'];
							}

							// If the current set of defaults contains a value for this control ID
							if ( defaults.constructor === Object && defaults.hasOwnProperty( control.id ) ) {
								// Adjust the default color value
								default_value = defaults[control.id];
							}
							// Otherwise treat the defaults as it's own value
							else {
								default_value = defaults;
							}

							// If we have a default value
							// TODO: What should we do if a default value actually is false?
							if ( default_value !== false ) {
								// Reset this control
								self.resetControlValue( control, default_value, type );
							}
						} );
					}
				} );
			}
		},
		// Reset a single control value
		resetControlValue: function( control, value, type ) {
			// If we have a default value and it doesn't match the current value
			if ( control.setting() !== value ) {
				// Switch based on type
				switch ( type ) {
					// Background Images
					case 'background_images':
						// Remove the attachment data
						control.params.attachment = {};

						// Update setting
						control.setting( value );

						// Re-render the content
						control.renderContent();
					break;

					// Default
					default:
						// Update setting
						control.setting( value );
					break;
				}
			}
		}
	};

	/*
	 * Document ready
	 */
	$( function() {
		// Initialize Baton Customizer Controls
		api.BatonCustomizeControls.init();

		/*
		 * Baton Reset Controls
		 */
		$( '.baton-customizer-reset .button' ).on( 'click', function() {
			var $this = $( this );

			// Switch based on type
			switch ( $this.data( 'reset-type' ) ) {
				// Colors
				case 'colors':
					api.BatonCustomizeControls.resetControlValues( 'colors' );
				break;

				// Background Images
				case 'background_images':
					api.BatonCustomizeControls.resetControlValues( 'background_images' );
				break;

				// Font Properties (font size, letter spacing, and line height)
				case 'font_properties':
					api.BatonCustomizeControls.resetControlValues( 'font_properties' );
				break;

				// Font Families
				case 'font_families':
					api.BatonCustomizeControls.resetControlValues( 'font_families' );
				break;

				// All (colors, background images, font properties [font size, letter spacing, and line height], and font families)
				default:
					api.BatonCustomizeControls.resetControlValues();
				break;
			}
		} );
	} );
} )( wp, jQuery, baton_customize_controls );