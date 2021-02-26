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
		clone : function( row, template = true ) {

			var source = $( row );

			// Clone the row.
			var clone = source.clone( true );

			// Change the id and name attributes to the supplied day/period variable.
			clone.find( 'input' ).each( function() {

				if ( ! template ) {

					var name = $( this ).attr( 'name' );

					name = name.replace( /\[([^\]]*)\][^\[]*$/, '[%token%]' );

					// Update id/name attributes.
					$( this ).attr( 'id', name )/*.attr( 'name', name )*/;
				}

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
			// clone.toggle();
			clone.show();

			return clone;
		},
		add : function() {

			$( '.cn-sortable-input-repeatable .cn-add' ).on( 'click', function(e) {

				// Override the default action.
				e.preventDefault();

				var asTemplate = true;
				var li = $( this ).parent().prev('li');

				// https://stackoverflow.com/a/39429530/5351316
				var template = $( this ).closest( 'ul' ).find( 'template' );
				var node = template.prop( 'content' );
				var row = $( node ).find( 'li' );

				if ( 0 === row.length ) {

					console.log( 'no template found, cloning previous row instead' );
					asTemplate = false;
					row = li;
				}

				// Insert the cloned row after the current row.
				li.after( cnSortableRepeatableInputList.clone( row, asTemplate ) );
			});
		},
		setValue : function() {

			$( '.cn-sortable-input-repeatable' ).on( 'keydown', 'li input[type=text]', function(e) {

				var text = $( this );
				var siblings = text.siblings( 'input' );

				// Do not update the registered or previously saved custom type id/name/slug because they should remain consistent.
				if ( 1 === text.data( 'registered' ) || 1 === text.data( 'custom' ) ) return;

				setTimeout( function() {

					// var name  = text.attr( 'name' );
					var value = text.val();

					/*
					 * Create a slug for the new type:
					 * 1. Trim whitespace from beginning/end of string.
					 * 2. Replace all non-alphanumeric characters with a hyphen.
					 * 3. Replace all hyphens from beginning/end of string.
					 * 4. Convert string to lowercase.
					 */
					var slug  = value.trim().replace( /\W+/g, '-' ).replace(/^-+|-+$/g, '' ).toLowerCase();

					var dataID   = ( 'undefined' !== typeof text.data( 'id' ) ) ? text.data( 'id' ) : text.attr( 'id' ).replace( /\[([^\]]*)\][^\[]*$/, '[' + slug + ']' );
					var dataName = ( 'undefined' !== typeof text.data( 'name' ) ) ? text.data( 'name' ) : text.attr( 'name' ).replace( /\[([^\]]*)\][^\[]*$/, '[' + slug + ']' );

					// name = name.replace( /\[([^\]]*)\][^\[]*$/, '[' + slug + ']' );

					// Update id/name/value attribute accordingly of the text input.
					text.attr( 'id', dataID.replace( /%token%/, slug ) )
						.attr( 'name', dataName.replace( /%token%/, slug ) )
						.attr( 'value', value );

					// Update the id/name of the hidden text inputs.
					siblings.each(function() {

						var sibling = $( this );

						var dataID   = ( 'undefined' !== typeof sibling.data( 'id' ) ) ? sibling.data( 'id' ) : sibling.attr( 'id' );
						var dataName = ( 'undefined' !== typeof sibling.data( 'name' ) ) ? sibling.data( 'name' ) : sibling.attr( 'name' );

						sibling.attr( 'id', dataID.replace( /%token%/, slug ) )
							.attr( 'name', dataName.replace( /%token%/, slug ) );
							// .attr( 'value', slug );
					});

					// Set the item slug value.
					siblings.filter( '[name$="[slug]"]' ).each(function() {

						$( this ).attr( 'value', slug );
					});

					// Update the value hidden text inputs for the active/hidden inputs.
					siblings.filter( '[name$="[]"]' ).each(function() {

						$( this ).attr( 'value', slug );
					});

				}, 0 ); // On next loop
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
