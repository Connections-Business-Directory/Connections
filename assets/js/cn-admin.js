/**
 * @author Steven A. Zahm
 */
jQuery(document).ready( function($) {

	var CN_Form = {

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

				$( 'body' ).on( 'focus', '.cn-datepicker', function() {

					CN_Form.datepicker( $( this ) );
				});
			}

			$( '#metabox-address' ).on( 'click', 'a.geocode.button', function(e) {

				CN_Form.geocode( $(this) );

				e.preventDefault();
			});

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

			// Hook in the jQuery Validate on the form.
			CN_Form.validate( $( '#cn-form' ) );

		},
		show : function( type ) {

			switch( type ) {

				case 'individual':

					/*
					 * Remove the 'required' class used by jQuery Validatation plugin to identify required input fields.
					 * Entry type, 'individual' does not require the 'organization' field to be entered.
					 */
					$('input[name=first_name], input[name=last_name]').addClass('required');
					$('input[name=organization]').removeClass('required error').addClass('invalid');
					$('input[name=family_name]').removeClass('required error').addClass('invalid');

					$('#cn-metabox-section-name').slideDown();
					$('#cn-metabox-section-title').slideDown();
					$('#cn-metabox-section-organization').slideDown();
					$('#cn-metabox-section-department').slideDown();
					$('#cn-metabox-section-contact').slideUp();
					$('#cn-metabox-section-family').slideUp();

					break;

				case 'organization':

					/*
					 * Add the 'required' class used by jQuery Validatation plugin to identify required input fields.
					 * Entry type, 'organization' requires the 'organization' field to be entered.
					 */
					$('input[name=organization]').addClass('required');
					$('input[name=first_name], input[name=last_name]').removeClass('required error').addClass('invalid');
					$('input[name=family_name]').removeClass('required error').addClass('invalid');

					$('#cn-metabox-section-name').slideUp();
					$('#cn-metabox-section-title').slideUp();
					$('#cn-metabox-section-organization').slideDown();
					$('#cn-metabox-section-department').slideDown();
					$('#cn-metabox-section-contact').slideDown();
					$('#cn-metabox-section-family').slideUp();

					break;

				case 'family':

					/*
					 * Add the 'required' class used by jQuery Validatation plugin to identify required input fields.
					 * Entry type, 'organization' requires the 'organization' field to be entered.
					 */
					$('input[name=family_name]').addClass('required');
					$('input[name=first_name], input[name=last_name]').removeClass('required error').addClass('invalid');
					$('input[name=organization]').removeClass('required error').addClass('invalid');

					$('#cn-metabox-section-name').slideUp();
					$('#cn-metabox-section-title').slideUp();
					$('#cn-metabox-section-organization').slideUp();
					$('#cn-metabox-section-department').slideUp();
					$('#cn-metabox-section-contact').slideUp();
					$('#cn-metabox-section-family').slideDown();

					break;
			}

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
			$('#' + type + '-row-' + token).slideDown();

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
				yearRange: 'c-100:c+10'
			});
		},
		geocode : function( field ) {

			var address = new Object();
			var lat;
			var lng;
			var uid = field.attr('data-uid');
			//console.log(uid);

			address.line_1 = $('input[name=address\\[' + uid + '\\]\\[line_1\\]]').val();
			address.line_2 = $('input[name=address\\[' + uid + '\\]\\[line_2\\]]').val();
			address.line_3 = $('input[name=address\\[' + uid + '\\]\\[line_3\\]]').val();

			address.city = $('input[name=address\\[' + uid + '\\]\\[city\\]]').val();
			address.state = $('input[name=address\\[' + uid + '\\]\\[state\\]]').val();
			address.zipcode = $('input[name=address\\[' + uid + '\\]\\[zipcode\\]]').val();

			address.country = $('input[name=address\\[' + uid + '\\]\\[country\\]]').val();

			//console.log(address);

			$( '#map-' + uid ).fadeIn( 'slow' , function() {

				$( '#map-' + uid ).goMap({
					maptype: 'ROADMAP'
				});

				$.goMap.clearMarkers();

				$.goMap.createMarker({
					address: '\'' + address.line_1 + ', ' + address.city + ', ' + address.state + ', ' + address.zipcode + ', ' +  '\'' , id: 'baseMarker' , draggable: true
				});

				$.goMap.setMap({ address: '\'' + address.line_1 + ', ' + address.city + ', ' + address.state + ', ' + address.zipcode + ', ' +  '\'' , zoom: 18 });

				$.goMap.createListener( {type:'marker', marker:'baseMarker'} , 'idle', function(event) {
					var lat = event.latLng.lat();
					var lng = event.latLng.lng();

					// console.log(lat);
					// console.log(lng);

					$('input[name=address\\[' + uid + '\\]\\[latitude\\]]').val(lat);
					$('input[name=address\\[' + uid + '\\]\\[longitude\\]]').val(lng);
				});

				$.goMap.createListener( {type:'marker', marker:'baseMarker'} , 'dragend', function(event) {
					var lat = event.latLng.lat();
					var lng = event.latLng.lng();

					// console.log(lat);
					// console.log(lng);

					$('input[name=address\\[' + uid + '\\]\\[latitude\\]]').val(lat);
					$('input[name=address\\[' + uid + '\\]\\[longitude\\]]').val(lng);
				});

			});

			// There has to be a better way than setting a delay. I know I have to use a callback b/c the geocode is an asyn request.
			setTimeout( function() {

				CN_Form.setGEO( uid );
			}, 1500);

		},
		setGEO : function( uid ) {

			var baseMarkerPosition = $( '#map-' + uid ).data('baseMarker').getPosition();

			$('input[name=address\\[' + uid + '\\]\\[latitude\\]]').val( baseMarkerPosition.lat() );
			$('input[name=address\\[' + uid + '\\]\\[longitude\\]]').val( baseMarkerPosition.lng() );
		},
		relation : function() {

			var template = ($('#cn-relation-template').text());
			var d = new Date();
			var token = Math.floor( Math.random() * d.getTime() );

			template = template.replace(
				new RegExp('::FIELD::', 'gi'),
				token
				);

			$('#cn-relations').append( '<div id="relation-row-' + token + '" class="relation" style="display: none;">' + template + '<a href="#" class="cn-remove cn-button button button-warning" data-type="relation" data-token="' + token + '">Remove</a>' + '</div>' );
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

	$('a.detailsbutton').click( function () {

		var $this = $( this );

		$this.text( $this.text() == cn_string.showDetails ? cn_string.hideDetails : cn_string.showDetails ).attr( 'title', $this.attr('title') == cn_string.showDetailsTitle ? cn_string.hideDetailsTitle : cn_string.showDetailsTitle  );

		$( '.child-' + this.id ).each( function( i, elem ) {

			$(elem).toggle();
		});
	});

});
