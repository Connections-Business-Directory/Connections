;jQuery(document).ready( function($) {

	var CN_CSV_Import = {

		init: function() {

			var self = this;

			$( '.cn-import-form' ).ajaxForm({
				beforeSubmit: self.beforeSubmit,
				success:      self.success,
				complete:     self.complete,
				dataType:     'json',
				error:        self.error
			});

		},

		beforeSubmit: function( arr, form, options ) {

			var submit = form.find( '.cn-upload-file input[type="submit"]' );

			if ( ! submit.hasClass( 'button-disabled' ) ) {

				submit.addClass( 'button-disabled' ).prop( 'disabled', true );

				form.find( '.status' ).remove();
				//form.append( '<div class="status"><span class="spinner is-active"></span><div class="import-progress"><div></div></div></div>' );

				return true;
			}
		},

		success: function( responseText, textStatus, xhr, form ) {
			//console.log('success');
			//console.log( responseText );
		},

		complete: function( xhr, textStatus ) {

			var html = window.wp.html;

			var response = jQuery.parseJSON( xhr.responseText );
			//var response = xhr.responseJSON;

			if ( response.success ) {

				//console.log( response.data );

				var form   = $( '#' + response.data.form.id );
				var submit = form.find( 'input[type="submit"]' );

				submit.removeClass( 'button-disabled' ).prop( 'disabled', false );

				form.find( '.cn-upload-file,.status' ).remove();

				// Show column mapping.

				//var select = form.find( 'select.cn-import-csv-column' );
				var table = form.find( '#cn-import-category-options table tbody' );

				//$.each( response.data.columns, function( key, value ) {
				//	select.append( '<option value="' + value + '">' + value + '</option>' );
				//});

				$.each( response.data.headers, function( key, value ) {
					//table.append( '<tr><td><input class="cn-field-name" type="text" value="' + value + '" name="csv_map[' + value + ']" READONLY /></td><td>&nbsp;</td><td>&nbsp;</td></tr>' );

					var input = html.string({
						tag:    'input',
						attrs:  {
							type:     'text',
							class:    'cn-field-name',
							//name: 'csv_map[' + value + ']',
							value:    value,
							readonly: 'READONLY'
						},
						single: true
					});

					var fields = $(
						html.string({
							tag:   'select',
							attrs: {
								//name: 'field[' + value + ']'
								name: value
							}
						})
					);

					$.each( response.data.fields, function( key, value ) {

						var option = html.string({
							tag:     'option',
							content: value,
							attrs:   {
								value: key
							}
						});

						fields.append( option );
					});

					var row = html.string({
						tag:     'tr',
						content: '<td>' + input + '</td><td>' + fields.prop( 'outerHTML' ) + '</td>',
						attrs:   {
							class: 'cn-repeatable-row'
						}
					});

					table.append( row );
				});

				/**
				 * Do no allow the same category field to be mapped to multiple CSV fields.
				 *
				 * @link http://stackoverflow.com/a/4001904/5351316
				 */
				form.find( 'select' ).change( function() {

					// Enable all select options.
					form.find( 'select option' ).prop( 'disabled', false );

					// Store the values from selected options.
					var arr = $.map(

						form.find( 'select option:selected' ), function(n) {
							return n.value;
						}
					);

					// Disable options already selected.
					form.find( 'select option' ).filter(

						function() {

							var value = $(this).val();

							if ( -1 == value || $(this).prop( 'selected' ) ) {
								return false;
							}

							return $.inArray( value, arr ) > -1; //if value is in the array of selected values
						}

					).prop( 'disabled', true );

				});

				form.find( '.cn-import-options' ).slideDown();

				submit.on( 'click', function(e) {

					e.preventDefault();

					submit.addClass( 'button-disabled' ).prop( 'disabled', true );

					form.find( '.status' ).remove();
					form.append( '<div class="status"><div class="notice notice-warning"><p>The CSV file is being imported, please be patient. Do not leave or close this page until you receive the "Import Completed" message. Doing so would interrupt the import process.</p></div><span class="spinner is-active"></span><div class="import-progress"><div></div></div></div>' );

					//response.data.map = form.serialize();
					var values = {};

					form.find( 'select' ).each( function() {

						values[ this.name ] = $( this ).val();
					});

					response.data.map = JSON.stringify( values );
					//console.log( response.data.map );

					CN_CSV_Import.submit( 'import_csv_term', response.data, 1, response.data.nonce, form, CN_CSV_Import );
				});

			} else {

				CN_CSV_Import.error( xhr );
			}

			//console.log( response );
		},

		error : function( xhr, textStatus ) {

			// Something went wrong. This will display error on form.
			console.log( 'error' );
			console.log( xhr );

			//var response = jQuery.parseJSON( xhr.responseText );
			var response = xhr.responseJSON;
			var form     = $( '#' + response.data.form.id );
			var status   = form.find( '.status' );
			var submit   = form.find( 'input[type="submit"]' );

			form.find('.button-disabled').removeClass('button-disabled');

			if ( false == response.success ) {

				submit.removeClass( 'button-disabled' ).prop( 'disabled', false );
				status.html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');

			} else {

				status.remove();
			}
		},

		submit: function( action, data, step, nonce, form, self ) {

			var wp = window.wp;

			wp.ajax.send(
				action,
				{
					success: function( response ) {

						console.log( response );

						if ( 'completed' == response.step ) {

							var status = form.find( '.status' );
							var submit = form.find( 'input[type="submit"]' );

							form.find( '.cn-import-options' ).slideUp();
							//form.find( '#cn-import-category-options table tbody' ).empty();

							submit.removeClass( 'button-disabled' ).prop( 'disabled', false );

							//status.remove();
							status.html('<div class="notice notice-success"><p>' + response.message + '</p></div>');
							//window.location = response.url;

						} else {

							var progress = form.find( '.import-progress div' );

							progress.animate({
								width: response.percentage + '%'
							}, 50, function() {
								// Animation complete.
							});

							self.step( action, data, response.step, response.nonce, form, self );
						}

					},
					error:   function( response ) {

						console.log( response );

						var status = form.find( '.status' );
						var submit = form.find( 'input[type="submit"]' );

						form.find( '.cn-import-options' ).slideUp();

						//submit.removeClass( 'button-disabled' ).prop( 'disabled', false );
						status.html('<div class="notice notice-error"><p>' + response.message + '</p></div>');
					},
					data:    {
						_ajax_nonce: nonce,
						step:        step,
						file:        data.file,
						map:         data.map
					}
				}
			);

		},

		step: function( action, data, step, nonce, form, self ) {

			self.submit( action, data, step, nonce, form, self )
		}
	};

	CN_CSV_Import.init();
});
