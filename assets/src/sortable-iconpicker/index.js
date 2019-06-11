/**
 * @link https://stackoverflow.com/a/46500027/5351316
 */
const { jQuery: $ } = window;

require( '@fonticonpicker/fonticonpicker' )( jQuery );

let sn;

const socialNetwork = class {

	constructor( instance ) {

		if ( instance instanceof jQuery ) {

			this.instance   = instance;
			this.slug       = this.instance.find( 'input.cn-brandicon' );
			this.icon       = this.instance.find( 'i[class^="cn-brandicon"]' );
			this.color      = this.instance.find( 'input.cn-brandicon-color' );
			this.hoverColor = this.instance.find( 'input.cn-brandicon-hover-color' );
		}
	}

	getColor() {

		let iconColor = brandicons.color( this.getSlug() );

		if ( this.color instanceof jQuery && this.color.val() ) {

			iconColor = this.color.val();
		}

		return iconColor;
	}

	setColor( value ) {

		if ( this.color instanceof jQuery ) {

			this.color.val( value );
			this.icon.css( 'backgroundColor', value );
		}
	}

	getHoverColor() {

		let iconColor = brandicons.color( this.getSlug() );

		if ( this.hoverColor instanceof jQuery && this.hoverColor.val() ) {

			iconColor = this.hoverColor.val() ;
		}

		return iconColor;
	}

	setHoverColor( value ) {

		if ( this.hoverColor instanceof jQuery ) {

			this.hoverColor.val( value );

			// Since the hover color can not be set with an inline style, use the mouseenter/mouseleave events.
			this.icon.mouseenter( function() {

				$( this ).css( 'backgroundColor', sn.getHoverColor() );

			} ).mouseleave( function() {

				$( this ).css( 'backgroundColor', sn.getColor() );
			});
		}
	}

	setIcon( value ) {

		if ( this.icon instanceof jQuery ) {

			this.setSlug( socialNetwork.classNameToSlug( value ) );
			this.icon.removeClass()
				.addClass( 'cn-brandicon-size-24' )
				.addClass( value );
		}
	}

	getSlug() {

		if ( this.slug instanceof jQuery ) {

			return this.slug.val();
		}
	}

	setSlug( slug ) {

		if ( this.slug instanceof jQuery ) {

			return this.slug.val( slug );
		}
	}

	getClassname() {

		if ( this.slug instanceof jQuery ) {

			return 'cn-brandicon-' + this.getSlug();
		}
	}

	static classNameToSlug( value ) {

		return value.replace( 'cn-brandicon-', '' );
	}

	static slugToClassName( value ) {

		return 'cn-brandicon-' + value;
	}
};

const brandicons = {

	icons: [],

	add: function( item ) {

		let color = 'rgb(0, 0, 0)';

		if ( item.icon.attrs.length && 'fill' in item.icon.attrs[0] ) {
			color = item.icon.attrs[0].fill;
		}

		this.icons[ item.properties.name ] = {
			color: color,
		};
	},
	get: function( slug ) {

		if ( ( slug in this.icons ) ) {

			return this.icons[ slug ];
		}

		return false;
	},
	color: function( slug, color = 'rgb(0, 0, 0)' ) {

		let icon = this.get( slug );

		if ( false !== icon ) {

			color = icon.color;
		}

		return color;
	}
};

// Init the font icon picker.
const e9_element = $( '#e9_element' ).fontIconPicker( {
	emptyIcon: false,
	theme:     'fip-darkgrey',
} )
	.on( 'change', function() {

		const input = $( this );
		let   value = input.val();

		if ( sn instanceof socialNetwork ) {

			// sn.setSlug( socialNetwork.classNameToSlug( value ) );
			sn.setIcon( value );
		}
	});

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

	// Bind a button to open the dialog.
	$( '.cn-fieldset-social-networks' ).on( 'click', 'a.cn-social-network-icon-setting-button', function( e ) {

		e.preventDefault();

		sn = new socialNetwork( $( this ).parent() );

		// Set the icon to be selected in the font icon picker.
		e9_element.setIcon( sn.getClassname() );

		// Init the icon color picker.
		const iconColorPicker = $( '#cn-icon-color' ).wpColorPicker({
			change: function( event, ui ) {

				// let hex = ui.color.toString();
				sn.setColor( ui.color.toString() );
			}
		} );

		// Init the icon hover color.
		const iconHoverColorPicker = $( '#cn-icon-hover-color' ).wpColorPicker({
			change: function( event, ui ) {

				// let hex = ui.color.toString();
				sn.setHoverColor( ui.color.toString() );
			}
		} );

		// Set the  color pickers to the saved color values before the modal is opened.
		iconColorPicker.wpColorPicker( 'color', sn.getColor() );
		iconHoverColorPicker.wpColorPicker( 'color', sn.getHoverColor() );

		// Open the icon settings modal.
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

				brandicons.add( v );

				// Set the source
				icomoon_json_icons.push( classPrefix + v.properties.name );

				// Create and set the search source
				if ( v.icon && v.icon.tags && v.icon.tags.length ) {
					icomoon_json_search.push( v.properties.name + ' ' + v.icon.tags.join( ' ' ) );
				} else {
					icomoon_json_search.push( v.properties.name );
				}
			} );

			// console.log( icomoon_json_icons );

			// Set new fonts on fontIconPicker
			e9_element.setIcons( icomoon_json_icons, icomoon_json_search );

			// Init the modal.
			initModal();
		} )
		.fail( function() {

			console.log('error fetching selection.json');
		} );

} );
