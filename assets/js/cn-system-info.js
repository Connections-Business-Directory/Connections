;jQuery(document).ready( function ($) {

	/**
	 * The WP Admin AJAX URL.
	 *
	 * @global
	 * @type {string}
	 */
	let ajaxurl;

	var CN_System_Tools = {

		init: function() {

			// Setup jQuery Validation.
			this.setupValidation();

			// Bind the on click event handler.
			$( 'body' ).on( 'click', '#cn-send-system-info-submit', function( e ) {
				e.preventDefault();

				var id = '#cn-send-system-info';

				CN_System_Tools.clearValidationErrorMessages( id );

				if ( CN_System_Tools.isValid( id ) ) {

					CN_System_Tools.send( id );
					// alert( CN_System_Tools.isValid( id ) );
				}

				// Prevent the default button/submit action.
				return false;
			});

			/**
			 * Generate new Remote View URL and display it on the admin page
			 */
			$( 'input[name="generate-url"]' ).on( 'click', function( e ) {
				e.preventDefault();

				CN_System_Tools.generateURL( this );

				return false;
			});

			/**
			 * Revoke the Remote View URL.
			 */
			$( 'input[name="revoke-url"]' ).on( 'click', function( e ) {
				e.preventDefault();

				CN_System_Tools.revokeURL( this );

				return false;
			});

			/**
			 * Upload a JSON file to import the settings.
			 */
			$( '#cn-import-settings' ).ajaxForm({
				type:         'post',
				dataType:     'json',
				url:          ajaxurl,
				beforeSubmit: function( arr, $form, options ) {
					$( '#cn-import-settings-submit' ).attr( 'disabled', 'disabled' );
				},
				success:      function( response, status, jqXHR ) {
					CN_System_Tools.ajaxSuccess( '#cn-import-settings-response', response.data.message, status, jqXHR );
					$( '#cn-import-settings input[name="import_file"]' ).val( '' );
					$( '#cn-import-settings-submit' ).removeAttr( 'disabled' );
				},
				error:        function( XMLHttpRequest, status, error ) {
					CN_System_Tools.ajaxError( '#cn-import-settings-response', XMLHttpRequest.responseJSON.data.message, status, error );
					$( '#cn-import-settings-submit' ).removeAttr( 'disabled' );
					$( '#cn-import-settings input[name="import_file"]' ).val( '' );
				}
			});

			$('#cn-database-reset').ajaxForm({
				type: 'post',
				dataType: 'json',
				url: ajaxurl,
				beforeSubmit(arr, $form, options) {
					$form.find('input[type="submit"]').attr('disabled', 'disabled');
				},
				success(response, status, jqXHR, $form) {
					CN_System_Tools.ajaxSuccess(
						'#cn-database-reset-response',
						response.data.message,
						status,
						jqXHR
					);
					$form
						.find('input[name="database-reset-confirmation"]')
						.val('');
					$form.find('input[type="submit"]').removeAttr('disabled');
				},
				error(XMLHttpRequest, status, error, $form) {
					CN_System_Tools.ajaxError(
						'#cn-database-reset-response',
						XMLHttpRequest.responseJSON.data.message,
						status,
						error
					);
					$form
						.find('input[name="database-reset-confirmation"]')
						.val('');
					$form.find('input[type="submit"]').removeAttr('disabled');
				},
			});

			$('#cn-settings-reset').ajaxForm({
				type: 'post',
				dataType: 'json',
				url: ajaxurl,
				beforeSubmit(arr, $form, options) {
					$form
						.find('input[type="submit"]')
						.attr('disabled', 'disabled');
				},
				success(response, status, jqXHR, $form) {
					CN_System_Tools.ajaxSuccess(
						'#cn-settings-reset-response',
						response.data.message,
						status,
						jqXHR
					);
					$form
						.find('input[name="settings-reset-confirmation"]')
						.val('');
					$form.find('input[name="submit"]').removeAttr('disabled');
				},
				error(XMLHttpRequest, status, error, $form) {
					CN_System_Tools.ajaxError(
						'#cn-settings-reset-response',
						XMLHttpRequest.responseJSON.data.message,
						status,
						error
					);
					$form
						.find('input[name="settings-reset-confirmation"]')
						.val('');
					$form.find('input[type="submit"]').removeAttr('disabled');
				},
			});
		},

		setupValidation: function () {
			$.validator.setDefaults({
				rules: {
					email: {
						required: true,
						email: true
					},
					subject: 'required',
					message: 'required'
				},
				messages: {
					email: '<p>' + cn_system_info.strErrMsgMissingEmail + '</p>',
					subject: '<p>' + cn_system_info.strErrMsgMissingSubject + '</p>',
					message: '<p>' + cn_system_info.strErrMsgMissingMessage + '</p>'
				},
				errorElement: 'div',
				errorPlacement: function( error, element ) {
					error.insertBefore( element );
				},
				debug: true
			});
		},

		clearValidationErrorMessages : function( id ) {

			// Remove any potential preexisting error messages.
			$( id + ' label.error' ).remove();
		},

		isValid: function( id ) {

			return $( id ).valid();
		},

		data: function( id ) {

			var email   = $( id + ' #cn-email-address' ).val();
			var subject = $( id + ' #cn-email-subject' ).val();
			var message = $( id + ' #cn-email-message' ).val();
			var action  = $( id + ' [name="action"]' ).val();
			var nonce   = $( id + ' [name="_cnonce"]' ).val();

			return {
				email: email,
				subject: subject,
				message: message,
				action: action,
				_cnonce: nonce
			};
		},

		send: function( id ) {

			$( id + ' #cn-send-system-info-submit' )
				.attr( 'disabled', 'disabled' )
				.val( cn_system_info.strSending )
				.promise()
				.done( function() {

					$.when(
						// Post the form data
						$.ajax( {
							type:     'post',
							url:      ajaxurl,
							dataType: 'json',
							data:     CN_System_Tools.data( id ),
							cache:    false,
							success:  function( response, status, jqXHR ) {
								CN_System_Tools.ajaxSuccess( '#cn-email-response', response.data.message, status, jqXHR );
							},
							error:    function( XMLHttpRequest, status, error ) {
								CN_System_Tools.ajaxError( '#cn-email-response', XMLHttpRequest.responseJSON.data.message, status, error );
							}
						} )
					).then( function() {

						CN_System_Tools.clearForm( id );

					}).fail( function() {

						CN_System_Tools.clearForm( id );
					});
				});

		},

		generateURL: function( object ) {

			var wp = window.wp;

			wp.ajax.send( 'generate_url', {
				success: function( response ) {

					$( '#system-info-url' ).val( response.url );
					$( '#system-info-url-text-link' )
						.attr( 'href', response.url )
						.css( 'display', 'inline-block' );

					CN_System_Tools.ajaxSuccess( '#cn-remote-response', response.message );

				},
				error:   function( response ) {

					CN_System_Tools.ajaxSuccess( '#cn-remote-response', response.message );
				},
				data: {
					_cnonce: $( object ).data('nonce')
				}
			});
		},

		revokeURL: function( object ) {

			var wp = window.wp;

			wp.ajax.send( 'revoke_url', {
				success: function( response ) {

					$( '#system-info-url' ).val( '' );
					$( '#system-info-url-text-link' )
						.attr( 'href', '#' )
						.css( 'display', 'none' );

					CN_System_Tools.ajaxSuccess( '#cn-remote-response', response.message );

				},
				error:   function( response ) {

					CN_System_Tools.ajaxSuccess( '#cn-remote-response', response.message );
				},
				data: {
					_cnonce: $( object ).data('nonce')
				}
			});

		},

		ajaxSuccess: function( id, message, status, jqXHR ) {

			switch ( message ) {

				case -3:

					CN_System_Tools.showMessage( id, cn_system_info.strAJAXHeaderErrMsg );
					break;

				case -2:

					CN_System_Tools.showMessage( id, cn_system_info.strErrMsgUserNotPermitted );
					break;

				case -1:

					CN_System_Tools.showMessage( id, cn_system_info.strErrMsgAction );
					break;

				case 0:

					CN_System_Tools.showMessage( id, cn_system_info.strErrMsgAction );
					break;

				case 1:

					CN_System_Tools.showMessage( id, cn_system_info.strSubmitted );
					break;

				default:

					CN_System_Tools.showMessage( id, message );
					break;

			}

		},

		ajaxError: function( id, message, status, error ) {

			CN_System_Tools.showMessage( id, message );
			// CN_System_Tools.showMessage( id, cn_system_info.strAJAXSubmitErrMsg );
		},

		clearForm: function( id ) {

			$( id + ' #cn-email-address' ).val('');
			$( id + ' #cn-email-subject' ).val('');
			$( id + ' #cn-email-message' ).val('');

			$( id + ' #cn-send-system-info-submit' ).removeAttr( 'disabled' ).val( cn_system_info.strSend );

		},

		showMessage: function( id, message ) {

			$( id )
				.html( '<div class="notice notice-warning"><p>' + message + '</p></div>' )
				.slideDown('slow')
				.animate( { opacity: 1.0 }, 1500 )
				.delay(1500)
				.slideUp('slow');

		}
	};

	CN_System_Tools.init();
});
