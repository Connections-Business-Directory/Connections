/**
 * @author Steven A. Zahm
 */

// Use jQuery() instead of $()for WordPress compatibility with the included prototype js library which uses $()
// http://ipaulpro.com/blog/tutorials/2008/08/jquery-and-wordpress-getting-started/
// See http://chrismeller.com/using-jquery-in-wordpress
jQuery(document).ready( function($) {

	/*
	 * Hide the image loading spinner and show the image.
	 */
	$('.connections').cn_preloader({
		delay:200,
		imgSelector:'.cn-image img.photo, .cn-image img.logo',
		beforeShow:function(){
			$(this).closest('.cn-image img').css('visibility','hidden');
		},
		afterShow:function(){
			//var image = $(this).closest('.cn-image');
			//$(image).spin(false);
		}
	});

	var showDetail = 'Show Details',
		hideDetail = 'Hide Details',
		showTitle  = 'Click to show details.',
		hideTitle  = 'Click to hide details.',
		detailLink = $('a.detailsbutton');

	detailLink.click( function () {

		var $this = $( this );

		$this.text( $this.text() == showDetail ? hideDetail : showDetail ).attr( 'title', $this.attr('title') == showTitle ? hideTitle : showTitle  );

		$( '.child-' + this.id ).each( function( i, elem ) {

			$(elem).toggle();
		});
	});

	$(function() {
		$('input[name^=entry_type][value=individual]').click( function() {
				$('#cn-metabox-section-family, #cn-metabox-section-contact').slideUp( 'fast', function() {
					$('#cn-metabox-section-name, #cn-metabox-section-title, #cn-metabox-section-organization, #cn-metabox-section-department').slideDown();
				});
			});
	});

	$(function() {
		$('input[name^=entry_type][value=organization]').click( function() {
				$('#cn-metabox-section-family, #cn-metabox-section-name, #cn-metabox-section-title').slideUp( 'fast', function() {
					$('#cn-metabox-section-organization, #cn-metabox-section-department, #cn-metabox-section-contact').slideDown();
				});
			});
	});

	$(function() {
		$('input[name^=entry_type][value=family]').click( function() {
				$('#cn-metabox-section-name, #cn-metabox-section-title, #cn-metabox-section-organization, #cn-metabox-section-department, #cn-metabox-section-contact').slideUp( 'fast', function() {
					$('#cn-metabox-section-family').slideDown();
				});
			});
	});


	$(function() {

		var $entryType = $('input[name^=entry_type]:checked').val();

		switch ( $entryType ) {

			case 'individual':
				$('#cn-metabox-section-family, #cn-metabox-section-contact').slideUp();
				break;

			case 'organization':
				$('#cn-metabox-section-family, #cn-metabox-section-name, #cn-metabox-section-title').slideUp();
				break;

			case 'family':
				$('#cn-metabox-section-name, #cn-metabox-section-title, #cn-metabox-section-organization, #cn-metabox-section-department, #cn-metabox-section-contact').slideUp();
				break;
		}

	});

	/*
	 * Add relations to the family entry type.
	 */
	$('#add-relation').click(function() {
		var template = ($('#cn-relation-template').text());
		var d = new Date();
		var token = Math.floor( Math.random() * d.getTime() );

		template = template.replace(
			new RegExp('::FIELD::', 'gi'),
			token
			);

		$('#cn-relations').append( '<div id="relation-row-' + token + '" class="relation" style="display: none;">' + template + '<a href="#" class="cn-remove cn-button button button-warning" data-type="relation" data-token="' + token + '">Remove</a>' + '</div>' );
		$('#relation-row-' + token).slideDown();

		/*
		 * Add jQuery Chosen to the family name and relation fields.
		 */
		$('.cn-enhanced-select').chosen();

		return false
	});

	/*
	 * Add jQuery Chosen to the family name and relation fields.
	 */
	if ( $.fn.chosen ) {

		$('.cn-enhanced-select').chosen();
	}

	$('a.cn-add.cn-button').click( function(e) {

		var $this = $(this);
		var type = $this.attr('data-type');
		var container = '#' + $this.attr('data-container');
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
		//console.log(template);
		//console.log(container);

		$(container).append( '<div class="widget ' + type + '" id="' + type + '-row-' + token + '" style="display: none;">' + template + '</div>' );
		$('#' + type + '-row-' + token).slideDown();

		e.preventDefault();
	});

	$('.postbox').on( 'click', 'a.cn-remove.cn-button', function(e) {

		var $this = $(this);
		var token = $this.attr('data-token');
		var type = $this.attr('data-type');
		var id = '#' + type + '-row-' + token;
		//alert(id);
		$(id).slideUp('fast', function(){ $(this).remove(); });

		e.preventDefault();
	});

	/*
	 * Add the jQuery UI Datepicker to the date input fields.
	 */
	if ($.fn.datepicker) {

		$('.cn-datepicker').on('focus', function() {
			$(this).datepicker({
				changeMonth: true,
				changeYear: true,
				showOtherMonths: true,
				selectOtherMonths: true,
				yearRange: 'c-100:c+10'
			});
		});
	}

	//////////////////////////////////////////////////////////////////////////////////
	// Geocode an address and then input the lat / lng into the user input fields. //
	//////////////////////////////////////////////////////////////////////////////////

	// $('a.geocode.button').live('click', function() {
	$('#metabox-address').on( 'click', 'a.geocode.button', function(e) {
		var address = new Object();
		var $this = $(this);
		var lat;
		var lng;

		var uid = $this.attr('data-uid');
		//console.log(uid);

		address.line_1 = $('input[name=address\\[' + uid + '\\]\\[line_1\\]]').val();
		address.line_2 = $('input[name=address\\[' + uid + '\\]\\[line_2\\]]').val();
		address.line_3 = $('input[name=address\\[' + uid + '\\]\\[line_3\\]]').val();

		address.city = $('input[name=address\\[' + uid + '\\]\\[city\\]]').val();
		address.state = $('input[name=address\\[' + uid + '\\]\\[state\\]]').val();
		address.zipcode = $('input[name=address\\[' + uid + '\\]\\[zipcode\\]]').val();

		address.country = $('input[name=address\\[' + uid + '\\]\\[country\\]]').val();

		//console.log(address);

		$( '#map-' + uid ).fadeIn('slow' , function() {
			$( '#map-' + uid ).goMap({
				maptype: 'ROADMAP'/*,
				latitude: 40.366502,
				longitude: -75.887637,
				zoom: 14*/
			});

			$.goMap.clearMarkers();

			$.goMap.createMarker({
				address: '\'' + address.line_1 + ', ' + address.city + ', ' + address.state + ', ' + address.zipcode + ', ' +  '\'' , id: 'baseMarker' , draggable: true
			});

			$.goMap.setMap({ address: '\'' + address.line_1 + ', ' + address.city + ', ' + address.state + ', ' + address.zipcode + ', ' +  '\'' , zoom: 18 });



			$.goMap.createListener( {type:'marker', marker:'baseMarker'} , 'idle', function(event) {
				var lat = event.latLng.lat();
				var lng = event.latLng.lng();

				console.log(lat);
				console.log(lng);

				$('input[name=address\\[' + uid + '\\]\\[latitude\\]]').val(lat);
				$('input[name=address\\[' + uid + '\\]\\[longitude\\]]').val(lng);
			});

			$.goMap.createListener( {type:'marker', marker:'baseMarker'} , 'dragend', function(event) {
				var lat = event.latLng.lat();
				var lng = event.latLng.lng();

				console.log(lat);
				console.log(lng);

				$('input[name=address\\[' + uid + '\\]\\[latitude\\]]').val(lat);
				$('input[name=address\\[' + uid + '\\]\\[longitude\\]]').val(lng);
			});

		});


		// There has to be a better way than setting a delay. I know I have to use a callback b/c the geocode is an asyn request.
		setTimeout( function(){
			setLatLngInfo(uid);
		}, 1500)

		e.preventDefault();
	});

	function setLatLngInfo(uid) {

		var baseMarkerPosition = $( '#map-' + uid ).data('baseMarker').getPosition();

		$('input[name=address\\[' + uid + '\\]\\[latitude\\]]').val( baseMarkerPosition.lat() );
		$('input[name=address\\[' + uid + '\\]\\[longitude\\]]').val( baseMarkerPosition.lng() );

	}

	/////////////////////////////////////////////
	// Custom Fields Metabox Repeatable Field //
	/////////////////////////////////////////////

	// Add a new neta row.
	$('#metabox-meta').on( 'click', '#newmeta-submit', function(e) {
	// $( '#newmeta-submit' ).on( 'click', function() {

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

		// Override the default action.
		e.preventDefault();
	});

	// Delete a meta row.
	$('#metabox-meta').on( 'click', 'input[name^="deletemeta"]', function(e) {
	// $( 'input[name^="deletemeta"]' ).on( 'click', function() {

		var tr = $( this ).closest( 'tr' ).toggle();

		tr.find( 'textarea' ).val( '::DELETED::' );

		// Hide the table head if all meta rows have been removed.
		if ( $( '#list-table' ).is( ':visible' ) ) {

			// Less than or equal to "1" because the row being cloned exists in "#list-table tbody tr".
			if ( $('#list-table tbody tr:visible').length == 0 ) $( '#list-table' ).toggle();
		}

		// Override the default action.
		e.preventDefault();
	});

	// Toggle the visibility of the new meta key select and meta key input.
	$('#metabox-meta').on( 'click', '#enternew, #cancelnew', function(e) {
	// $( '#enternew, #cancelnew' ).on( 'click', function() {

		$( '#metakeyinput, #metakeyselect, #enternew, #cancelnew' ).toggle();

		// Change the select back to the intial value.
		$( '#metakeyselect option' ).eq(0).prop( 'selected', true );

		// Empty the meta key input.
		$( '#metakeyinput' ).val('');

		// Override the default action.
		e.preventDefault();
	});

});
