;jQuery(document).ready(function ($) {

	/**
	 * Law Licenses Add / Remove license.
	 */
	var cnSortableRepeatableInputList = {

		init : function() {
			this.add();
			this.setValue();
			this.remove();
		},
		clone : function( row ) {

			var source = $( row );

			// Clone the row.
			var clone = source.clone( true );

			// Change the id and name attributes to the supplied day/period variable.
			clone.find( 'input' ).each( function() {

				var name = $( this ).attr( 'name' );

				name = name.replace( /\[([^\]]*)\][^\[]*$/, '[%token%]' );

				// Update id/name attributes.
				$( this ).attr( 'id', name )/*.attr( 'name', name )*/;

				// Reset input values.
				$( this ).val( '' ).attr( 'value', '' ).prop( 'checked', false ).removeAttr( 'checked' );
			});

			// Set the data attribute to `0` in case the cloned row was one of the core registered types or previously save custom type.
			clone.find( 'input[type=text]' )
				.attr( 'data-registered', 0 )
				.attr( 'data-custom', 0 )
				.data( 'registered', 0 )
				.data( 'custom', 0 )
				.prop( 'disabled', false );

			clone.find( 'input[type=checkbox]' )
				.prop( 'disabled', false );

			if ( 0 === clone.find( 'a.cn-remove' ).length ) {
				clone.append( '<a href="#" class="cn-remove cn-button button">Remove</a>' );
			}

			// Unhide the cloned object.
			clone.toggle();

			return clone;
		},
		add : function() {

			$( '.cn-sortable-input-repeatable .cn-add' ).on( 'click', function(e) {

				// Override the default action.
				e.preventDefault();

				var row = $( this ).parent().prev('li');

				// Insert the cloned row after the current row.
				row.after( cnSortableRepeatableInputList.clone( row ) );
			});
		},
		setValue : function() {

			var textInput = $( 'ul.cn-sortable-input-repeatable li input[type=text]' );

			textInput.each( function() {

				var input = $( this );

				input.on( 'keydown', function() {

					var text = $( this );
					var siblings = text.siblings( 'input' );

					// Do not update the registered or previously saved custom type id/name/slug because they should remain consistent.
					if ( 1 === text.data( 'registered' ) || 1 === text.data( 'custom' ) ) return;

					setTimeout( function() {

						var name  = text.attr( 'name' );
						var value = text.val();

						/*
						 * Create a slug for the new type:
						 * 1. Trim whitespace from beginning/end of string.
						 * 2. Replace all non-alphanumeric characters with a hyphen.
						 * 3. Replace all hyphens from beginning/end of string.
						 * 4. Convert string to lowercase.
						 */
						var slug  = value.trim().replace( /\W+/g, '-' ).replace(/^-+|-+$/g, '' ).toLowerCase();

						name = name.replace( /\[([^\]]*)\][^\[]*$/, '[' + slug + ']' );

						// Update id/name/value attributes accordingly.
						text.attr( 'id', name ).attr( 'name', name ).attr( 'value', value );
						siblings.attr( 'id', name ).attr( 'value', slug )/*.attr( 'name', name )*/;

					}, 0 ); // On next loop
				});
			});

		},
		remove : function() {

			$( '.cn-sortable-input-repeatable' ).on( 'click', '.cn-remove', function(e) {

				// Override the default action.
				e.preventDefault();

				$( this ).parent().remove();
			});
		}
	};

	cnSortableRepeatableInputList.init();

});
