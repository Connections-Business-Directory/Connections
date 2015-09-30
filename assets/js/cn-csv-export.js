;jQuery(document).ready( function($) {

	var CN_CSV_Export = {

		init: function() {

			var self = this;

			$( document.body ).on( 'submit', '.cn-export-form', function(e) {
				e.preventDefault();

				var form = $( this );
				var submit = form.find( 'input[type="submit"]' );

				if ( ! submit.hasClass( 'button-disabled' ) ) {

					submit.addClass( 'button-disabled' ).prop( 'disabled', true );

					var action = submit.data( 'action' );
					var nonce  = submit.data( 'nonce' );

					form.find( '.status' ).remove();
					form.append( '<div class="status"><span class="spinner is-active"></span><div class="export-progress"><div></div></div></div>' );

					self.submit( action, 1, nonce, form, self );
				}
			});

		},

		submit: function( action, step, nonce, form, self ) {

			var wp  = window.wp;

			wp.ajax.send(
				action,
				{
					success: function( response ) {

						console.log( response );

						if ( 'completed' == response.step ) {

							var status = form.find( '.status' );
							var submit = form.find( 'input[type="submit"]' );

							submit.removeClass( 'button-disabled' ).prop( 'disabled', false );

							status.remove();
							window.location = response.url;

						} else {

							var progress = form.find( '.export-progress div' );

							progress.animate({
								width: response.percentage + '%'
							}, 50, function() {
								// Animation complete.
							});

							self.step( action, response.step, response.nonce, form, self );
						}

					},
					error:   function( response ) {

						console.log( response );

						var status = form.find( '.status' );
						var submit = form.find( 'input[type="submit"]' );

						submit.removeClass( 'button-disabled' ).prop( 'disabled', false );
						status.html('<div class="update error"><p>' + response.message + '</p></div>');
					},
					data:    {
						_ajax_nonce: nonce,
						step:        step
					}
				}
			);

		},

		step: function( action, step, nonce, form, self ) {

			self.submit( action, step, nonce, form, self )
		}
	};

	CN_CSV_Export.init();
});
