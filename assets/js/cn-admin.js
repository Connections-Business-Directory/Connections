/**
 * @author Steven A. Zahm
 */
jQuery(document).ready( function($) {

	var $document = $( document );

	// if ( 0 < cnMap.geocoderAPIKey.length ) {
	//
	// 	google.load( 'maps', '3', {
	// 		other_params: 'key=' + cnMap.geocoderAPIKey,
	// 		callback: function() {}
	// 	});
	// }

	var maps = {

		/**
		 * Object to store Leaflet map instances.
		 */
		maps: {},

		/**
		 * Attach a Leaflet map object to an element ID.
		 *
		 * @param id Element ID.
		 * @returns {*}
		 */
		addMap: function( id ) {

			if ( ! this.hasMap( id ) ) {

				/*
				 * Attach the map to the supplied element id.
				 * Define the map tile provider and attach the tiles to the map.
				 */
				var map = L.map( 'map-' + id );

				/*
				 * Clear the attribution, removing the Leaflet back link, so it can be customized.
				 */
				map.attributionControl.setPrefix( '' );

				/*
				 * Get the default provider object from the available map tile providers.
				 */
				var provider = cnMap.basemapProviders[cnMap.basemapDefault];

				switch ( provider.group ) {

					case 'google':

						var tiles = L.gridLayer.googleMutant({
							type: provider.type,
							attribution: provider.attribution
						});

						break;

					default:

						var tiles = L.tileLayer(
							provider.tileLayer,
							{
								attribution: provider.attribution,
								minZoom:     1,
								maxZoom:     19
							}
						);

				}

				/*
				 * Add the map tiles provider to the map.
				 */
				tiles.addTo( map );

				/*
				 * Create a new layer group to attach the map pins to so they can be easily cleared.
				 */
				var pins = new L.LayerGroup();
				pins.addTo( map );

				/*
				 * Store the Leaflet map instance and the layer group to the maps object property.
				 * Add a couple helper functions to the object to make it easier to add and clear pins of the current map object.
				 */
				this.maps[ id ] = {
					id:        id,
					map:       map,
					pins:      pins,
					addPin:    function( LatLng ) {
						return L.marker( LatLng, { draggable: true, autoPan: true } ).addTo( this.pins );
					},
					clearPins: function() {
						this.pins.clearLayers();
					}
				};
			}

			return this.maps[ id ];
		},

		/**
		 * Whether or not a map has been attached to the element ID.
		 *
		 * @param id Element ID.
		 *
		 * @returns {boolean}
		 */
		hasMap: function( id ) {

			return this.maps.hasOwnProperty( id );
		},

		/**
		 * Get a Leaflet map object from element by its ID.
		 *
		 * @param id Element ID.
		 * @returns {*}
		 */
		getMap: function( id ) {

			if ( this.hasMap( id ) ) {

				return this.maps[ id ];
			}
		}
	};

	var CN_Form = {

		map: null,

		init : function() {

			// Show/hide form fields based on the entry type.
			var type;

			if ( $('input[name=entry_type]').length == 1 ) {

				type = ( $('input[name=entry_type]').val() );

			} else if ( $('input[name=entry_type]').length > 1) {

				type = ( $('input[name=entry_type]:checked').val() );
			}

			switch ( type ) {

				case 'individual':

					this.show( type );
					break;

				case 'organization':

					this.show( type );
					break;

				case 'family':

					this.show( type );
					break;
			}

			// Show the `individual` entry fields when the individual entry type radio is clicked.
			$( '#submitdiv' ).on( 'click', 'input[name=entry_type][value=individual]', function() {

				CN_Form.show( 'individual' );
			});

			// Show the `organization` entry fields when the organization entry type radio is clicked.
			$( '#submitdiv' ).on( 'click', 'input[name=entry_type][value=organization]', function() {

				CN_Form.show( 'organization' );
			});

			// Show the `family` entry fields when the family entry type radio is clicked.
			$( '#submitdiv' ).on( 'click', 'input[name=entry_type][value=family]', function() {

				CN_Form.show( 'family' );
			});

			// Add a family relation.
			$( '#cn-metabox-section-family' ).on( 'click', '#add-relation', function() {

				CN_Form.relation();
			});

			// Add repeatable entry data meta type.
			$( '.postbox' ).on( 'click', 'a.cn-add.cn-button', function( e ) {

				CN_Form.add( $( this ) );

				e.preventDefault();
			});

			// Remove repeatable entry data meta type.
			$( '.postbox' ).on( 'click', 'a.cn-remove.cn-button', function( e ) {

				CN_Form.remove( $( this ) );

				e.preventDefault();
			});

			// Add jQuery Chosen to enhanced select drop down fields.
			if ( $.fn.chosen ) {

				$( '.cn-enhanced-select' ).chosen();
			}

			// Add the jQuery UI Datepicker to the date input fields.
			if ( $.fn.datepicker ) {

				$( 'body' ).on( 'focus', '.cn-datepicker', function(e) {

					CN_Form.datepicker( $( this ) );

					e.preventDefault();
				});
			}

			$( '#metabox-address' ).on( 'click', 'a.geocode.button', function(e) {

				CN_Form.geocode( $(this) );

				e.preventDefault();
			});

			// Check full File API support.
			if (window.FileReader && window.File && window.FileList && window.Blob) {

				$('input[name="original_image"], input[name="original_logo"]').on('change', function () {

					//this.files[0].size gets the size of your file.
					var imageField = $('input[name="original_image"]');
					var logoField = $('input[name="original_logo"]');

					if ( cn_string.imageMaxFileSize < this.files[0].size ) {

						//var fileSize = cnFormatBytesTo( this.files[0].size, 'si' );
						var name  = $(this).attr('name');
						var clone = $(this).attr('name') == 'original_image' ? imageField.clone(true) : logoField.clone(true);

						alert( cn_string.imageMaxFileSizeExceeded );

						if (name == 'original_image') {

							imageField.replaceWith(clone);

						} else {

							logoField.replaceWith(clone);
						}

					}

				});

			} else {

				//alert( "Not supported" );
			}

			// Add a new meta row.
			$( '#metabox-meta' ).on( 'click', '#newmeta-submit', function(e) {

				CN_Form.add_meta();

				// Override the default action.
				e.preventDefault();
			});

			// Delete a meta row.
			$('#metabox-meta').on( 'click', 'input[name^="deletemeta"]', function(e) {

				CN_Form.delete_meta( $(this) );

				// Override the default action.
				e.preventDefault();
			});

			// Clear and toggle the visibility of the new meta key select and meta key input.
			$('#metabox-meta').on( 'click', '#enternew, #cancelnew', function(e) {

				CN_Form.clear_meta();

				// Override the default action.
				e.preventDefault();
			});

			$('#cn-relations').sortable();

			// Make the category checklist resizable.
			var categorydiv = $('#taxonomy-category');
			var categorydivHeight = cn_string.categoryDiv.height;

			$( categorydiv ).resizable( {

				maxWidth: Math.floor( categorydiv.width() ),
				minWidth: Math.floor( categorydiv.width() ),

				create:   function( event, ui ) {

					var $this = $( this );

					$this.css( { height: categorydivHeight, width: 'inherit' } );
					$this.children( '.ui-icon' ).css( 'background', 'url(images/resize.gif)' );
				},
				stop:     function( event, ui ) {

					var wp  = window.wp;

					wp.ajax.send(
						'set_category_div_height',
						{
							success: function( response ) {

								// console.log( response );
								//
								// console.log( "Success!" );
								// console.log( "New nonce: " + response.nonce );
								// console.log( "Message from PHP: " + response.message );
							},
							error:   function( response ) {

								// console.log( response );
								//
								// console.log( "Failed!" );
								// console.log( "New nonce: " + response.nonce );
								// console.log( "Message from PHP: " + response.message );
							},
							data:    {
								height: categorydiv.height(),
								_cnonce: cn_string.categoryDiv._cnonce
							}
						}
					);

				}
			} ).css( {
				'max-height': 'none'
			} );

			// Hook in the jQuery Validate on the form.
			CN_Form.validate( $( '#cn-form' ) );

		},
		show : function( type ) {

			$( '#metabox-name .cn-metabox-section' ).slideUp().promise().always( function(){

				switch( type ) {

					case 'individual':

						/*
						 * Remove the 'required' class used by jQuery Validation plugin to identify required input fields.
						 * Entry type, 'individual' does not require the 'organization' field to be entered.
						 */
						$('input[name=first_name], input[name=last_name]').addClass('required');
						$('input[name=organization]').removeClass('required error').addClass('invalid');
						$('input[name=family_name]').removeClass('required error').addClass('invalid');

						// $('#cn-metabox-section-name').slideDown();
						// $('#cn-metabox-section-title').slideDown();
						// if ( $('#cn-metabox-section-organization').hasClass('active') ) {
						// 	$('#cn-metabox-section-organization').slideDown();
						// } else if( $('#cn-metabox-section-organization').hasClass('inactive') ) {
						// 	$('#cn-metabox-section-organization').slideUp();
						// }
						// if ( $('#cn-metabox-section-department').hasClass('active') ) {
						// 	$('#cn-metabox-section-department').slideDown();
						// } else if( $('#cn-metabox-section-department').hasClass('inactive') ) {
						// 	$('#cn-metabox-section-department').slideUp();
						// }
						// $('#cn-metabox-section-contact').slideUp();
						// $('#cn-metabox-section-family').slideUp();

						$( '#metabox-name .cn-metabox-section.cn-individual' ).slideDown();
						// $( '#metabox-name .cn-metabox-section' ).not( '.cn-individual' ).slideUp();

						break;

					case 'organization':

						/*
						 * Add the 'required' class used by jQuery Validation plugin to identify required input fields.
						 * Entry type, 'organization' requires the 'organization' field to be entered.
						 */
						$('input[name=organization]').addClass('required');
						$('input[name=first_name], input[name=last_name]').removeClass('required error').addClass('invalid');
						$('input[name=family_name]').removeClass('required error').addClass('invalid');

						// $('#cn-metabox-section-name').slideUp();
						// $('#cn-metabox-section-title').slideUp();
						// $('#cn-metabox-section-organization').slideDown();
						// $('#cn-metabox-section-department').slideDown();
						// $('#cn-metabox-section-contact').slideDown();
						// $('#cn-metabox-section-family').slideUp();

						$( '#metabox-name .cn-metabox-section.cn-organization' ).slideDown();
						// $( '#metabox-name .cn-metabox-section' ).not( '.cn-organization' ).slideUp();

						break;

					case 'family':

						/*
						 * Add the 'required' class used by jQuery Validation plugin to identify required input fields.
						 * Entry type, 'organization' requires the 'organization' field to be entered.
						 */
						$('input[name=family_name]').addClass('required');
						$('input[name=first_name], input[name=last_name]').removeClass('required error').addClass('invalid');
						$('input[name=organization]').removeClass('required error').addClass('invalid');

						// $('#cn-metabox-section-name').slideUp();
						// $('#cn-metabox-section-title').slideUp();
						// $('#cn-metabox-section-organization').slideUp();
						// $('#cn-metabox-section-department').slideUp();
						// $('#cn-metabox-section-contact').slideUp();
						// $('#cn-metabox-section-family').slideDown();

						$( '#metabox-name .cn-metabox-section.cn-family' ).slideDown();
						// $( '#metabox-name .cn-metabox-section' ).not( '.cn-family' ).slideUp();

						break;
				}

			});

			/**
			 * @summary Fires when an entry type is selected.
			 *
			 * Contains a jQuery object with the relevant postbox element.
			 *
			 * @since 8.6.5
			 * @event input[name=entry_type]
			 * @type {string}
			 */
			$document.trigger( 'entry-type-selected', type );
		},
		add : function( button ) {

			var type = button.attr('data-type');
			var container = '#' + button.attr('data-container');
			var id = '#' + type + '-template';
			//console.log(id);

			var template = $(id).text();
			//console.log(template);

			var d = new Date();
			var token = Math.floor( Math.random() * d.getTime() );

			template = template.replace(
				new RegExp('::FIELD::', 'gi'),
				token
			);
			// console.log(template);
			// console.log(container);

			$(container).append( '<div class="widget ' + type + '" id="' + type + '-row-' + token + '" style="display: none;">' + template + '</div>' );
			$('#' + type + '-row-' + token).slideDown().find( '.cn-enhanced-select' ).chosen();

			/**
			 * @summary Fires when a repeatable field is added.
			 *
			 * Contains a jQuery object with the relevant postbox element.
			 *
			 * @since 8.6.5
			 * @event input[name=entry_type]
			 * @type {string}
			 */
			$document.trigger( 'entry-field-type-added', type );
		},
		remove : function( button ) {

			var token = button.attr('data-token');
			var type = button.attr('data-type');
			var id = '#' + type + '-row-' + token;
			// console.log(id);
			$(id).slideUp('fast', function() { $(id).remove(); });

		},
		add_meta : function() {

			// Clone.
			var row   = $( '#list-table' ).find( 'tbody tr:last-child' );
			var clone = row.clone();

			// Grab the user input values.
			var key   = $( '#metakeyselect' ).val() !== '-1' ? $( '#metakeyselect' ).val() : $( '#metakeyinput' ).val();
			var value = $( '#metavalue' ).val();

			// Clear the user input.
			$( '#metakeyinput, #metavalue' ).val( '' );
			$( '#metakeyselect option' ).eq(0).prop( 'selected', true );

			// Increment name, id and the label for attribute..
			clone.find( 'input, textarea' )
				.attr( 'id', function( index, id ) {
					return id.replace(/(\d+)/, function( fullMatch, n ) {
						return Number(n) + 1;
					});
				});

			clone.find( 'input, textarea' )
				.attr( 'name', function( index, name ) {
					return name.replace(/(\d+)/, function( fullMatch, n ) {
						return Number(n) + 1;
					});
				});

			clone.find( 'label' )
				.attr( 'for', function( index, label ) {
					return label.replace(/(\d+)/, function( fullMatch, n ) {
						return Number(n) + 1;
					});
				});

			// Set the user input values in the clone.
			clone.find( 'input[name^="newmeta"]' ).val( key );
			clone.find( 'textarea' ).val( value );

			// Display the meta list if it is not visable.
			if ( ! $( '#list-table' ).is( ':visible' ) ) {

				$( '#list-table' ).toggle();
			}

			// Make the clone visible.
			clone.toggle();

			// Append the clone.
			$( '#the-list' ).append( clone );

		},
		delete_meta : function( row ) {

			var tr = row.closest( 'tr' ).toggle();

			tr.find( 'textarea' ).val( '::DELETED::' );

			// Hide the table head if all meta rows have been removed.
			if ( $( '#list-table' ).is( ':visible' ) ) {

				// Less than or equal to "1" because the row being cloned exists in "#list-table tbody tr".
				if ( $('#list-table tbody tr:visible').length == 0 ) $( '#list-table' ).toggle();
			}
		},
		clear_meta : function() {

			$( '#metakeyinput, #metakeyselect, #enternew, #cancelnew' ).toggle();

			// Change the select back to the intial value.
			$( '#metakeyselect option' ).eq(0).prop( 'selected', true );

			// Empty the meta key input.
			$( '#metakeyinput' ).val('');
		},
		chosen : function( field ) {

			field.chosen();
		},
		datepicker : function( field ) {

			field.datepicker({
				changeMonth: true,
				changeYear: true,
				showOtherMonths: true,
				selectOtherMonths: true,
				yearRange: 'c-100:c+10',
				dateFormat: 'yy-mm-dd'
			}).keydown(false);
		},
		geocode : function( field ) {

			var address = Object();
			var uid = field.attr('data-uid');
			//console.log(uid);

			address.line_1 = $('input[name=address\\[' + uid + '\\]\\[line_1\\]]').val();
			// address.line_2 = $('input[name=address\\[' + uid + '\\]\\[line_2\\]]').val();
			// address.line_3 = $('input[name=address\\[' + uid + '\\]\\[line_3\\]]').val();

			address.city = $('input[name=address\\[' + uid + '\\]\\[city\\]]').val();
			address.state = $('input[name=address\\[' + uid + '\\]\\[state\\]]').val();
			address.zipcode = $('input[name=address\\[' + uid + '\\]\\[zipcode\\]]').val();

			var country = $( '#cn-address\\[' + uid + '\\]\\[country\\]' );

			// @link https://stackoverflow.com/a/9495029/5351316
			if ( country.is( 'input' ) ) {

				address.country = country.val();

			} else if ( country.is( 'select' ) ) {

				address.country = country.find( ':selected' ).val();
			}

			address = Object.keys( address ).map( function(e) {
				return address[e]
			}).filter( Boolean ).join( ' ' );

			console.log(address);

			$( '#map-' + uid ).fadeIn( 'slow' , function() {

				var latInput = $('input[name=address\\[' + uid + '\\]\\[latitude\\]]');
				var lngInput = $('input[name=address\\[' + uid + '\\]\\[longitude\\]]');

				/*
				 * Create a new Leaflet map instance attaching to the map container element.
				 */
				var map = maps.addMap( uid );

				/*
				 * Create a new Leaflet geocoder instance based on the provider.
				 */
				switch ( cnMap.geocoderDefault ) {

					case 'google':

						var geocoder = new L.Control.Geocoder.GoogleNative();

						break;

					default:

						var geocoder = new L.Control.Geocoder.Nominatim();

				}

				/*
				 * Geocode the address. Pass the Leaflet map instance so it can be referenced as `this`
				 * from within the callback function.
				 */
				geocoder.geocode(
					address,
					function( results ) {

						/*
						 * NOTE: `this` refers to a Leaflet map instance.
						 */

						// console.log( this );
						console.log( results );

						this.clearPins();

						if ( results && results.length ) {

							/*
							 * Pull out the first result from the array.
							 */
							var location = results[0];

							/*
							 * Update the address form inputs.
							 */
							latInput.val( location.center.lat );
							lngInput.val( location.center.lng );

							/*
							 * Center the map on the result and add the map marker.
							 */
							this.map.setView( location.center, 16 );

							/*
							 * Create a map marker and add it to the map layer group.
							 */
							var marker = this.addPin( location.center );

							/*
							 * Attach a `dragend` event to the marker to update the address form inputs.
							 */
							marker.on( 'dragend', function( e ) {

								latInput.val( this.getLatLng().lat );
								lngInput.val( this.getLatLng().lng );
							});

						}

					},
					map
				);

			});

		},
		relation : function() {

			var template = ($('#cn-relation-template').text());
			var d = new Date();
			var token = Math.floor( Math.random() * d.getTime() );

			template = template.replace(
				new RegExp('::FIELD::', 'gi'),
				token
				);

			$('#cn-relations').append( '<li id="relation-row-' + token + '" class="cn-relation" style="display: none;"><i class="fa fa-sort"></i> ' + template + '<a href="#" class="cn-remove cn-button button button-warning" data-type="relation" data-token="' + token + '">Remove</a>' + '</li>' );
			$('#relation-row-' + token).slideDown();

			// Add jQuery Chosen to the family name and relation fields.
			$('.cn-enhanced-select').chosen();
		},
		validate : function( form ) {

			form.validate({
				// Override generation of error label
				errorPlacement: function( error, element ) {},
				ignore: 'ul.chosen-choices li.search-field input, textarea[id$="template"]'
			});
		}
	};

	CN_Form.init();

	$( 'a.detailsbutton' ).on( 'click', function() {

		var $this = $( this );

		$this.text( $this.text() == cn_string.showDetails ? cn_string.hideDetails : cn_string.showDetails ).attr( 'title', $this.attr( 'title' ) == cn_string.showDetailsTitle ? cn_string.hideDetailsTitle : cn_string.showDetailsTitle );

		$( '.child-' + this.id ).each( function( i, elem ) {

			$( elem ).toggle();
		});
	});

	/**
	 * Have user confirm that activating this option, that their data will be deleted.
	 */
	$('input[id="connections_uninstall[maybe_uninstall]"]').on(
		'click',
		function () {
			const $this = $(this);
			const isChecked = $this.is(':checked');

			if (isChecked) {
				if (!confirm( 'If you decide to delete the plugin, all the directory data associated with it will also be deleted permanently. This action cannot be undone, so please make sure you have backed up your important data before deleting the plugin.' ) ) {
					$this.prop('checked', false);
				}
			}
		}
	);

	/**
	 * @link http://stackoverflow.com/a/25651291
	 * @param pBytes the size in bytes to be converted.
	 * @param pUnits 'si'|'iec' si units means the order of magnitude is 10^3, iec uses 2^10
	 *
	 * @returns {string}
	 */
	function cnFormatBytesTo(pBytes, pUnits) {
		// Handle some special cases
		if (pBytes == 0) return '0 Bytes';
		if (pBytes == 1) return '1 Byte';
		if (pBytes == -1) return '-1 Byte';

		var bytes = Math.abs(pBytes)
		if (pUnits && pUnits.toLowerCase() && pUnits.toLowerCase() == 'si') {
			// SI units use the Metric representation based on 10^3 as a order of magnitude
			var orderOfMagnitude = Math.pow(10, 3);
			var abbreviations = ['Bytes', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
		} else {
			// IEC units use 2^10 as an order of magnitude
			var orderOfMagnitude = Math.pow(2, 10);
			var abbreviations = ['Bytes', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'];
		}
		var i = Math.floor(Math.log(bytes) / Math.log(orderOfMagnitude));
		var result = (bytes / Math.pow(orderOfMagnitude, i));

		// This will get the sign right
		if (pBytes < 0) {
			result *= -1;
		}

		// This bit here is purely for show. it drops the precision on numbers greater than 100 before the units.
		// it also always shows the full number of bytes if bytes is the unit.
		if (result >= 99.995 || i == 0) {
			return result.toFixed(0) + ' ' + abbreviations[i];
		} else {
			return result.toFixed(2) + ' ' + abbreviations[i];
		}
	}

});
