// import { library } from '@fortawesome/fontawesome-svg-core'
// import { fas } from '@fortawesome/free-solid-svg-icons'
// import { far } from '@fortawesome/free-regular-svg-icons'
// import { fab } from '@fortawesome/free-brands-svg-icons'
//
// // Add both three icon sets
// library.add( fas, far, fab );
//
// let fasArray = Object.keys( library.definitions.fas );
// let farArray = Object.keys( library.definitions.far );
// let fabArray = Object.keys( library.definitions.fab );

// console.log( library.definitions.fab );

/**
 * @link https://stackoverflow.com/a/46500027/5351316
 */
const { jQuery: $ } = window;

require( '@fonticonpicker/fonticonpicker' )( jQuery );
// console.log( fip );
// const fipInput = $( '#e14_element' ).fontIconPicker( {
// 	      theme:     'fip-darkgrey',
// 	      emptyIcon: false,
//       } )
// ;

// // Add the event on the button
// $( '#e14_buttons button' ).on( 'click', function( e ) {
// 	// Append the fontawesome CDN
// 	if ( !$( '#fontawesome-cdn' ).length ) {
// 		// $( 'head' ).append( '<link rel="stylesheet" href="http://sandbox.connections-pro.com/wp-content/plugins/connections/assets/vendor/fontawesome/css/all.css">' );
// 	}
// 	// Prevent default
// 	e.preventDefault();
// 	// Show processing message
// 	$( this ).prop( 'disabled', true )
// 		.html( '<i class="icon-cog demo-animate-spin"></i> Please wait…' );
// 	// Get the JSON file
// 	$.ajax( {
// 		url:      '//sandbox.connections-pro.com/wp-content/plugins/connections/assets/font-icon-maps/fontawesome/fontawesome-rs-categorized.json',
// 		type:     'GET',
// 		dataType: 'json'
// 	} )
// 		.done( function( response ) {
// 			console.log( response );
// 			setTimeout( function() {
// 				// Reset icons
// 				fipInput.setIcons( response );
//
// 				// Show success message and disable
// 				$( '#e14_buttons button' )
// 					.removeClass( 'btn-primary' )
// 					.addClass( 'btn-success' )
// 					.text( 'Successfully loaded icons' )
// 					.prop( 'disabled', true );
// 			}, 1000 );
// 		} )
// 		.fail( function() {
// 			// Show error message and enable
// 			$( '#e14_buttons button' )
// 				.removeClass( 'btn-primary' )
// 				.addClass( 'btn-danger' )
// 				.text( 'Error: Try Again?' )
// 				.prop( 'disabled', false );
// 		} );
// 	e.stopPropagation();
// } );


/**
 * Example 9
 * Load icons from icomoon JSON selections file
 */

	// Init the font icon picker
const e9_element = $( '#e9_element' ).fontIconPicker( {
		emptyIcon: false,
		theme:     'fip-darkgrey',
	} );

// // Add the event on the button
// $( '#e9_buttons button' ).on( 'click', function( e ) {
// 	e.preventDefault();
//
// 	// Show processing message
// 	$( this ).prop( 'disabled', true ).html( '<i class="icon-cog demo-animate-spin"></i> Please wait...' );
//
// 	// Get the JSON file
// 	$.ajax( {
// 		url:      '//sandbox.connections-pro.com/wp-content/plugins/connections/assets/vendor/icomoon-brands/selection.json',
// 		type:     'GET',
// 		dataType: 'json'
// 	} )
// 		.done( function( response ) {
//
// 			// Get the class prefix
// 			const classPrefix         = response.preferences.fontPref.prefix,
// 			      icomoon_json_icons  = [],
// 			      icomoon_json_search = [];
//
// 			// For each icon
// 			$.each( response.icons, function( i, v ) {
//
// 				// Set the source
// 				icomoon_json_icons.push( classPrefix + v.properties.name );
//
// 				// Create and set the search source
// 				if ( v.icon && v.icon.tags && v.icon.tags.length ) {
// 					icomoon_json_search.push( v.properties.name + ' ' + v.icon.tags.join( ' ' ) );
// 				} else {
// 					icomoon_json_search.push( v.properties.name );
// 				}
// 			} );
//
// 			console.log( icomoon_json_icons );
//
// 			// Set new fonts on fontIconPicker
// 			e9_element.setIcons( icomoon_json_icons, icomoon_json_search );
//
// 			// Show success message and disable
// 			$( '#e9_buttons button' ).removeClass( 'btn-primary' ).addClass( 'btn-success' ).text( 'Successfully loaded icons' ).prop( 'disabled', true );
//
// 		} )
// 		.fail( function() {
// 			// Show error message and enable
// 			$( '#e9_buttons button' ).removeClass( 'btn-primary' ).addClass( 'btn-danger' ).text( 'Error: Try Again?' ).prop( 'disabled', false );
// 		} );
// 	e.stopPropagation();
// } );

const initModal = () => {

	const modal = $( '#cn-social-network-icon-settings-modal' );

	// initialize the dialog
	modal.dialog( {
		title:         'Social Network Icons Settings',
		dialogClass:   'wp-dialog',
		autoOpen:      false,
		draggable:     false,
		width:         'auto',
		minHeight:     600,
		minWidth:      386,
		modal:         true,
		resizable:     false,
		closeOnEscape: true,
		position:      {
			my: 'center',
			at: 'center',
			of: window
		},
		open:          function() {
			// close dialog by clicking the overlay behind it
			$( '.ui-widget-overlay' ).bind( 'click', function() {
				$( '#cn-social-network-icon-settings-modal' ).dialog( 'close' );
			} )
		},
		create:        function() {
			// style fix for WordPress admin
			$( '.ui-dialog-titlebar-close' ).addClass( 'ui-button' );
		},
	} );

	// bind a button or a link to open the dialog
	$( 'button.cn-social-network-icon-setting-button' ).on( 'click', function( e ) {

		e.preventDefault();

		const button = $( this );
		const parent = button.parent();
		const icon   = parent.find( 'input.cn-brandicon' ).val();

		e9_element.setIcon( 'cn-brandicon-' + icon );

		modal.dialog( 'open' );
	} );

};

$( document ).ready( function() {

	// Get the JSON file
	$.ajax( {
		url:      '//sandbox.connections-pro.com/wp-content/plugins/connections/assets/vendor/icomoon-brands/selection.json',
		type:     'GET',
		dataType: 'json'
	} )
		.done( function( response ) {

			// Get the class prefix
			const classPrefix         = response.preferences.fontPref.prefix,
			      icomoon_json_icons  = [],
			      icomoon_json_search = [];

			// For each icon
			$.each( response.icons, function( i, v ) {

				// Set the source
				icomoon_json_icons.push( classPrefix + v.properties.name );

				// Create and set the search source
				if ( v.icon && v.icon.tags && v.icon.tags.length ) {
					icomoon_json_search.push( v.properties.name + ' ' + v.icon.tags.join( ' ' ) );
				} else {
					icomoon_json_search.push( v.properties.name );
				}
			} );

			console.log( icomoon_json_icons );

			// Set new fonts on fontIconPicker
			e9_element.setIcons( icomoon_json_icons, icomoon_json_search );

			// Init the modal.
			initModal();

			// Show success message and disable
			// $( '#e9_buttons button' ).removeClass( 'btn-primary' ).addClass( 'btn-success' ).text( 'Successfully loaded icons' ).prop( 'disabled', true );

		} )
		.fail( function() {
			// Show error message and enable
			// $( '#e9_buttons button' ).removeClass( 'btn-primary' ).addClass( 'btn-danger' ).text( 'Error: Try Again?' ).prop( 'disabled', false );
			console.log('error fetching selection.json');
		} );

} );
