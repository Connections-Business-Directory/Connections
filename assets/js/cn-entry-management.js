( function( $ ) {

	var confirm = $( '<div id="dialog" title="Are you sure?"><p style="margin: auto">This action can not be undone.</p></div>' )

	confirm.dialog( {
		autoOpen:  false,
		draggable: false,
		modal:     true,
		resizable: false,
	} );

	$( '.cn-rest-action.cn-delete-entry' ).on(
		'click',
		function( event ) {

			event.preventDefault();

			// Endpoint from wpApiSetting variable passed from wp-api.
			// var endpoint = wpApiSettings.root + 'cn-api/v1/entry/';
			var action = $( this );
			var endpoint = action.attr( 'href' );

			confirm.dialog( {
				buttons: {
					'Confirm': function() {

						$.ajax( {
							url:        endpoint,
							method:     'DELETE',
							beforeSend: function( xhr ) {
								// Set nonce here
								xhr.setRequestHeader( 'X-WP-Nonce', wpApiSettings.nonce );
							},
							// Build post data.
							data:       {},
							context: $( this ),
						} ).done( function( response ) {

							confirm.dialog( {
								title:   'Success',
								buttons: {
									'Done': {
										text:  'Ok',
										id:    'entry-deleted',
										click: function() {
											confirm.dialog( 'close' );
										}
									}
								}
							} );

							confirm.html(
								'<p style="margin: auto">Entry has been deleted.</p>'
							);

						} ).fail( function( response ) {

							confirm.dialog( {
								title:   'Failed',
								buttons: {
									'Done': {
										text:  'Ok',
										id:    'entry-deleted',
										click: function() {
											confirm.dialog( 'close' );
										}
									}
								}
							} );

							confirm.html(
								response.responseJSON.message
							);

						} ).always( function() {
							// Do something regardless of done/fail of ajax request.

							action.closest( 'div' ).remove();
						} );

					},
					'Cancel':  function() {
						confirm.dialog( 'close' );
					}
				}
			} );

			confirm.dialog( 'open' );

		} );
} )( jQuery );
