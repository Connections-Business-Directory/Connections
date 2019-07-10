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
			this.backgroundColor       = this.instance.find( 'input.cn-brandicon-background-color' );
			this.hoverBackgroundColor  = this.instance.find( 'input.cn-brandicon-hover-background-color' );
			this.backgroundTransparent = this.instance.find( 'input.cn-brandicon-background-transparent' );
			this.foregroundColor       = this.instance.find( 'input.cn-brandicon-foreground-color' );
			this.hoverForegroundColor  = this.instance.find( 'input.cn-brandicon-hover-foreground-color' );
		}
	}

	getBackgroundColor() {

		let iconColor = brandicons.color( this.getSlug() );

		if ( this.backgroundColor instanceof jQuery && this.backgroundColor.val() ) {

			iconColor = this.backgroundColor.val();
		}

		return iconColor;
	}

	setBackgroundColor( value ) {

		if ( this.backgroundColor instanceof jQuery ) {

			this.backgroundColor.val( value );

			// 'transparent' === value ? this.backgroundTransparent.val( '1' ) : this.backgroundTransparent.val( '0' );

			this.writeStyle();
		}
	}

	setBackgroundTransparent( value ) {

		if ( this.backgroundTransparent instanceof jQuery ) {

			this.backgroundTransparent.val( value );

			this.writeStyle();
		}
	}

	isBackgroundTransparent() {

		if ( this.backgroundTransparent instanceof jQuery ) {

			return '1' === this.backgroundTransparent.val();
		}

		return false;
	}

	getForegroundColor() {

		let iconColor = '#FFFFFF';

		if ( this.foregroundColor instanceof jQuery && this.foregroundColor.val() ) {

			iconColor = this.foregroundColor.val();
		}

		return iconColor;
	}

	setForegroundColor( value ) {

		if ( this.foregroundColor instanceof jQuery ) {

			this.foregroundColor.val( value );

			this.writeStyle();
		}
	}

	getHoverBackgroundColor() {

		let iconColor = brandicons.color( this.getSlug() );

		if ( this.hoverBackgroundColor instanceof jQuery && this.hoverBackgroundColor.val() ) {

			iconColor = this.hoverBackgroundColor.val() ;
		}

		return iconColor;
	}

	setHoverBackgroundColor( value ) {

		if ( this.hoverBackgroundColor instanceof jQuery ) {

			this.hoverBackgroundColor.val( value );

			// 'transparent' === value ? this.backgroundTransparent.val( '1' ) : this.backgroundTransparent.val( '0' );

			this.writeStyle();
		}
	}

	getHoverForegroundColor() {

		let iconColor = '#FFFFFF';

		if ( this.hoverForegroundColor instanceof jQuery && this.hoverForegroundColor.val() ) {

			iconColor = this.hoverForegroundColor.val() ;
		}

		return iconColor;
	}

	setHoverForegroundColor( value ) {

		if ( this.hoverForegroundColor instanceof jQuery ) {

			this.hoverForegroundColor.val( value );

			this.writeStyle();
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

	writeStyle() {

		let backgroundColor      = this.getBackgroundColor();
		let backgroundHoverColor = this.getHoverBackgroundColor();
		let foregroundColor      = this.getForegroundColor();
		let foregroundHoverColor = this.getHoverForegroundColor();

		if ( this.isBackgroundTransparent() ) {

			backgroundColor      = 'transparent';
			backgroundHoverColor = 'transparent';
		}

		this.icon.attr( 'style', "--color: " + foregroundColor + '; background-color: ' + backgroundColor );

		/**
		 * Since the hover color can not be set with an inline style, use the mouseenter/mouseleave events.
		 *
		 * Use CSS variable to in an inline style to set the hover colors.
		 * @link https://stackoverflow.com/a/49618941/5351316
		 */
		this.icon.mouseenter( function() {

			$( this ).attr( 'style', '--color: ' + foregroundHoverColor + '; background-color: ' + backgroundHoverColor );

		} ).mouseleave( function() {

			$( this ).attr( 'style', "--color: " + foregroundColor + '; background-color: ' + backgroundColor );
		});

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

		// Init the icon background color picker.
		const iconBackgroundColorPicker = $( '#cn-icon-background-color' ).wpColorPicker({
			change: function( event, ui ) {

				// let hex = ui.color.toString();
				sn.setBackgroundColor( ui.color.toString() );
			}
		} );

		// Init the icon background hover color.
		const iconHoverBackgroundColorPicker = $( '#cn-icon-hover-background-color' ).wpColorPicker({
			change: function( event, ui ) {

				// let hex = ui.color.toString();
				sn.setHoverBackgroundColor( ui.color.toString() );
			}
		} );

		// Set the transparent background checkbox state.
		if ( sn.isBackgroundTransparent() ) {

			$( '#cn-icon-background-transparent' ).prop( 'checked', true );
		}

		/**
		 * Bind event to set transparent color or background colors based on whether the checkbox is enabled or not.
		 *
		 * To prevent the change event from being attached more than once, remove it before adding it again
		 * using a namespace.
		 *
		 * @link https://stackoverflow.com/a/1558382/5351316
		 */
		$( '#cn-icon-background-transparent' ).off( 'change.transparent' ).on( 'change.transparent', function() {

			const checkbox = $( this );

			if ( checkbox.is( ':checked' ) ) {

				// sn.setBackgroundColor( 'transparent' );
				// sn.setHoverBackgroundColor( 'transparent' );
				sn.setBackgroundTransparent( '1' );

			} else {

				// sn.setBackgroundColor( iconBackgroundColorPicker.wpColorPicker( 'color' ) );
				// sn.setHoverBackgroundColor( iconHoverBackgroundColorPicker.wpColorPicker( 'color' ) );
				sn.setBackgroundTransparent( '0' );
			}
		} );

		// Init the icon foreground color picker.
		const iconForegroundColorPicker = $( '#cn-icon-foreground-color' ).wpColorPicker({
			change: function( event, ui ) {

				// let hex = ui.color.toString();
				sn.setForegroundColor( ui.color.toString() );
			}
		} );

		// Init the icon foreground hover color.
		const iconHoverForegroundColorPicker = $( '#cn-icon-hover-foreground-color' ).wpColorPicker({
			change: function( event, ui ) {

				// let hex = ui.color.toString();
				sn.setHoverForegroundColor( ui.color.toString() );
			}
		} );

		// Set the color pickers to the saved color values before the modal is opened.
		iconBackgroundColorPicker.wpColorPicker( 'color', sn.getBackgroundColor() );
		iconHoverBackgroundColorPicker.wpColorPicker( 'color', sn.getHoverBackgroundColor() );
		iconForegroundColorPicker.wpColorPicker( 'color', sn.getForegroundColor() );
		iconHoverForegroundColorPicker.wpColorPicker( 'color', sn.getHoverForegroundColor() );

		// Open the icon settings modal.
		modal.dialog( 'open' );
	} );

};

$( document ).ready( function() {

	// Get the JSON file
	$.ajax( {
		url:      cnBase.url + 'assets/vendor/icomoon-brands/selection.json',
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
