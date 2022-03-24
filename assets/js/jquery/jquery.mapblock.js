/* eslint-disable no-console */
jQuery( function ( $ ) {
	const map = [];
	let id = 0;

	$.fn.mapBlock = function ( options ) {
		console.log( 'mapBlock found.' );

		const attributes = {};

		$.each( $( this ).get( 0 ).attributes, function ( i, attrib ) {
			if ( 'data' === attrib.name.substring( 0, 4 ) ) {
				attributes[ attrib.name ] = attrib.value.replace( /_/g, ' ' );
			}
		} );

		console.log( attributes );

		$.each(
			attributes,
			/**
			 * Iterate through the data attributes and strip off the leading "data-".
			 *
			 * @param {string} key
			 * @param {string} value
			 */
			function ( key, value ) {
				const property = key.replace( 'data-', '' ).split( '-' );

				if ( property.length === 1 ) {
					options[ property[ 0 ] ] = value;
				}

				// if ( property.length === 2 ) {
				// 	if ( typeof options[ property[ 0 ] ] === 'undefined' ) {
				// 		options[ property[ 0 ] ] = {};
				// 	}
				// 	options[ property[ 0 ] ][ property[ 1 ] ] = value;
				// }

				// if ( property.length === 3 ) {
				// 	if ( typeof options[ property[ 0 ] ] === 'undefined' ) {
				// 		options[ property[ 0 ] ] = {};
				// 	}
				// 	if ( typeof options[ property[ 0 ] ][ property[ 1 ] ] === 'undefined' ) {
				// 		options[ property[ 0 ] ][ property[ 1 ] ] = {};
				// 	}
				// 	options[ property[ 0 ] ][ property[ 1 ] ][ property[ 2 ] ] = value;
				// }
				//
				// if ( property.length === 4 ) {
				// 	if ( typeof options[ property[ 0 ] ] === 'undefined' ) {
				// 		options[ property[ 0 ] ] = {};
				// 	}
				// 	if ( typeof options[ property[ 0 ] ][ property[ 1 ] ] === 'undefined' ) {
				// 		options[ property[ 0 ] ][ property[ 1 ] ] = {};
				// 	}
				// 	if ( typeof options[ property[ 0 ] ][ property[ 1 ] ][ property[ 2 ] ] === 'undefined' ) {
				// 		options[ property[ 0 ] ][ property[ 1 ] ][ property[ 2 ] ] = {};
				// 	}
				// 	options[ property[ 0 ] ][ property[ 1 ] ][ property[ 2 ] ][ property[ 3 ] ] = value;
				// }
				//
				// if ( property.length === 5 ) {
				// 	if ( typeof options[ property[ 0 ] ] === 'undefined' ) {
				// 		options[ property[ 0 ] ] = {};
				// 	}
				// 	if ( typeof options[ property[ 0 ] ][ property[ 1 ] ] === 'undefined' ) {
				// 		options[ property[ 0 ] ][ property[ 1 ] ] = {};
				// 	}
				// 	if ( typeof options[ property[ 0 ] ][ property[ 1 ] ][ property[ 2 ] ] === 'undefined' ) {
				// 		options[ property[ 0 ] ][ property[ 1 ] ][ property[ 2 ] ] = {};
				// 	}
				// 	if ( typeof options[ property[ 0 ] ][ property[ 1 ] ][ property[ 2 ] ][ property[ 3 ] ] === 'undefined' ) {
				// 		options[ property[ 0 ] ][ property[ 1 ] ][ property[ 2 ] ][ property[ 3 ] ] = {};
				// 	}
				//
				// 	options[ property[ 0 ] ][ property[ 1 ] ][ property[ 2 ] ][ property[ 3 ] ][ property[ 4 ] ] = value;
				// }
			}
		);

		console.log( options );

		const mapContainerID = $( this ).attr( 'id' );
		console.log( mapContainerID );

		// var tiles = L.tileLayer(
		// 	'//maps.wikimedia.org/osm-intl/{z}/{x}/{y}{r}.png',
		// 	{
		// 		attribution: '<a target="_blank" href="https://wikimediafoundation.org/wiki/Maps_Terms_of_Use">Wikimedia</a>'
		// 	}
		// );

		$( '#' + mapContainerID ).on(
			'appear',
			{ id, data: options },
			function ( event, elements ) {
				elements.each( function () {
					const mapID = event.data.id;
					const mapOptions = event.data.data;
					const mapContainer = $( this ).attr( 'id' );

					console.log( mapContainer + ':isVisible' );

					if ( 'undefined' === typeof map[ mapID ] ) {
						/*
						 * Temporarily set the array index to `addingMap` because maps are added async,
						 * so if the event is triggered while the map is still being added it will
						 * trigger a Leaflet error stating map has already being initialized.
						 *
						 * NOTE: Patching jQuery.appear() with PR gh-66 seems to negate this issue.
						 */
						// map[ mapID ] = 'addingMap';
						map[ mapID ] = L.map( mapContainer ).setView(
							L.latLng( mapOptions.center.split( ',' ) ),
							1
						);

						/*
						 * Clear the attribution, removing the Leaflet back link, so it can be customized.
						 */
						map[ mapID ].attributionControl.setPrefix( '' );

						const basemap = {};

						$.each(
							$( this ).children( 'map-tilelayer' ),
							function ( index, item ) {
								const provider = $( item );

								const tiles = /^google/.test(
									provider.data( 'id' )
								)
									? L.gridLayer.googleMutant( {
											type: provider.data( 'type' ),
											attribution: provider.html(),
									  } )
									: L.tileLayer( provider.data( 'url' ), {
											attribution: provider.html(),
											subdomains: provider.data(
												'subdomains'
											),
											minZoom: provider.data( 'minzoom' ),
											maxZoom: provider.data( 'maxzoom' ),
									  } );

								/*
								 * Only add the first tile layer to the map, so it is selected by the
								 * layer control by default.
								 */
								if ( $.isEmptyObject( basemap ) ) {
									tiles.addTo( map[ mapID ] );
								}

								// Add the control for the tile layer.
								const control = $( 'map-control-layers' ).find(
									'[data-id="' + provider.data( 'id' ) + '"]'
								);

								if ( control.length ) {
									basemap[ control.html() ] = tiles;
								}
							}
						);

						const layers = [];
						const overlay = {};
						const bounds = L.latLngBounds( [] ); // Create an empty instance of latLngBounds. Will be extended as layer groups are added.
						let markerCount = 0;

						/*
						 * Find each layer group attached to the base map and add it as a feature group.
						 */
						$.each(
							$( this ).children( 'map-layergroup' ),
							function ( layerIndex, layerItem ) {
								console.log( layerItem );
								const layer = $( layerItem );
								const layerID = layer.data( 'id' );
								// const layerName = layer.data( 'name' );
								// var enableControl = layer.data( 'control' );
								const markers = [];

								/*
								 * Find each marker attached to the current layer group and attach it to the feature group.
								 * Expand the bounds to fit the markers added to the group.
								 *
								 * NOTE: This code is duplicated below and could be abstracted to remove duplicate code.
								 */
								$.each(
									$( this ).children( 'map-marker' ),
									function ( markerIndex, markerItem ) {
										console.log( markerItem );
										const marker = $( markerItem );
										const latitude = marker.data(
											'latitude'
										);
										const longitude = marker.data(
											'longitude'
										);
										const popup = marker.find(
											'map-marker-popup'
										);

										if ( 0 === popup.length ) {
											markers.push(
												L.marker( [
													latitude,
													longitude,
												] )
											);
										} else {
											markers.push(
												L.marker( [
													latitude,
													longitude,
												] ).bindPopup( popup.html() )
											);
										}

										markerCount++;
									}
								);

								layers[ layerID ] = L.featureGroup(
									markers
								).addTo( map[ mapID ] );

								// Extend the map bounds to fit all makers added to layer group.
								bounds.extend( layers[ layerID ].getBounds() );

								// Add the control for the layer.
								const control = $( 'map-control-layers' ).find(
									'[data-mapID="' + layerID + '"]'
								);

								if ( control.length ) {
									overlay[ control.html() ] =
										layers[ layerID ];
								}
							}
						);

						/*
						 * Add each marker directly to the map to the base map as a feature group.
						 * Expand the bounds to fit the markers added to the group.
						 *
						 * NOTE: This is mostly duplicate code from above and could like be abstracted out.
						 */
						$.each(
							$( this ).children( 'map-marker' ),
							function ( index, item ) {
								const markers = [];

								console.log( item );
								const marker = $( item );
								const latitude = marker.data( 'latitude' );
								const longitude = marker.data( 'longitude' );
								const popup = marker.find( 'map-marker-popup' );

								if ( 0 === popup.length ) {
									markers.push(
										L.marker( [ latitude, longitude ] )
									);
								} else {
									markers.push(
										L.marker( [
											latitude,
											longitude,
										] ).bindPopup( popup.html() )
									);
								}

								const group = L.featureGroup( markers ).addTo(
									map[ mapID ]
								);

								// Extend the map bounds to fit all makers added to layer group.
								bounds.extend( group.getBounds() );

								markerCount++;
							}
						);

						// Only add the control if a layer group has been configured to be toggleable.
						if (
							! $.isEmptyObject( basemap ) ||
							! $.isEmptyObject( overlay )
						) {
							const layerControl = $( this ).find(
								'map-control-layers'
							);

							/*
							 * Whether a layer is "selected" or not in the layer control
							 * depends on whether the layer is added to the map or not
							 * via the `addTo()` method.
							 */
							L.control
								.layers( basemap, overlay, {
									collapsed: layerControl.data( 'collapsed' ),
									hideSingleBase: true,
								} )
								.addTo( map[ mapID ] );
						}

						if ( bounds.isValid() ) {
							// Set the map view to fit bounds of all layer groups.
							map[ mapID ].fitBounds( bounds, {
								padding: L.point( 20, 20 ),
							} );
						}

						// if the marker count is 1, use the supplied zoom value.
						if ( 1 >= markerCount ) {
							map[ mapID ].setZoom( mapOptions.zoom );
						}

						/**
						 * Add a listener to fit map bounds.
						 *
						 * {@link https://stackoverflow.com/a/45303126/5351316}
						 */
						map[ mapID ].on(
							'overlayadd overlayremove',
							function ( e ) {
								console.log( e );
								// map[ mapID ].fitBounds(e.layer.getBounds());

								// Create new empty bounds
								const bounds = new L.LatLngBounds( [] );

								// Iterate the map's layers
								map[ mapID ].eachLayer( function ( layer ) {
									// Check if layer is a feature group.
									if ( layer instanceof L.FeatureGroup ) {
										// Extend bounds with group's bounds
										bounds.extend( layer.getBounds() );
									}
								} );

								// Check if bounds are valid (could be empty)
								if ( bounds.isValid() ) {
									// Valid, fit bounds
									map[ mapID ].fitBounds( bounds );
								} else {
									// Invalid, fit world
									map[ mapID ].fitWorld();
								}
							}
						);
					}
				} );
			}
		);

		$.inView( '#' + mapContainerID );

		/**
		 * {@link https://stackoverflow.com/a/16462443/5351316}
		 * {@link https://stackoverflow.com/a/48303093/5351316}
		 */

		// var observer = new MutationObserver( function( mutations ) {
		// 	console.log( 'Attributes changed!' );
		// });
		//
		// var target = document.querySelector( '#' + mapContainerID );
		//
		// observer.observe( target, {
		// 	attributes: true
		// });

		// var targetNode = document.getElementById( mapContainerID );
		//
		// var observer = new MutationObserver( function( mutations ) {
		//
		// 	// if ( 'none' !== targetNode.style.display ) {
		// 	// 	console.log( 'Attributes changed!' );
		// 	// }
		//
		// 	// For the sake of...observation...let's output the mutation to console to see how this all works
		// 	mutations.forEach( function( mutation ) {
		// 		console.log( mutation.type );
		// 	});
		// });
		//
		// observer.observe( targetNode, { attributes: true, childList: true, characterData: true } );

		/**
		 * {@link https://stackoverflow.com/a/44670818/5351316}
		 */
		// var respondToVisibility = function( element, callback ) {
		//
		// 	var options = {
		// 		root: document.documentElement
		// 	};
		//
		// 	var observer = new IntersectionObserver( ( entries, observer ) => {
		// 		entries.forEach( entry => {
		// 		callback( entry.intersectionRatio > 0 )
		// 		});
		// 	},
		// 		options
		// 	);
		//
		// 	observer.observe( element );
		// };
		//
		// respondToVisibility( document.getElementById( mapContainerID ), visible => {
		//
		// 	console.log( 'isVisible');
		// });

		id++;
	};

	$( 'map-block' ).each( function () {
		const options = {};
		$( this ).mapBlock( options );
	} );

	$.force_appear();
} );

/*
 * NOTE: Pull on 9/3/2018
 *       Applied PR gh-66 to make sure appear is only fire once in view.
 *
 * NOTE: Add on click event to $.appear to make sure it is triggered when items in tabs appear.
 *
 * NOTE: Rename `appear` to `inView` to prevent conflict with theme's, such as Tower,
 *       which are using old versions of this script.
 *
 * jQuery appear plugin
 *
 * Copyright (c) 2012 Andrey Sidorov
 * licensed under MIT license.
 *
 * https://github.com/morr/jquery.appear/
 *
 * Version: 0.4.1
 */
( function ( $ ) {
	const selectors = [];

	let checkBinded = false;
	let checkLock = false;
	const defaults = {
		interval: 250,
		force_process: false,
	};
	const $window = $( window );

	const $priorAppeared = [];
	const $inView = [];

	function appeared( selector ) {
		return $( selector ).filter( function () {
			return $( this ).is( ':appeared' );
		} );
	}

	function process() {
		checkLock = false;
		for (
			let index = 0, selectorsLength = selectors.length;
			index < selectorsLength;
			index++
		) {
			const $appeared = appeared( selectors[ index ] );

			if ( $appeared.length && $inView[ index ] !== true ) {
				$appeared.trigger( 'appear', [ $appeared ] );
				$inView[ index ] = true;
			}

			if ( $priorAppeared[ index ] ) {
				const $disappeared = $priorAppeared[ index ].not( $appeared );
				if ( $disappeared.length ) {
					$disappeared.trigger( 'disappear', [ $disappeared ] );
					$inView[ index ] = false;
				}
			}
			$priorAppeared[ index ] = $appeared;
		}
	}

	function addSelector( selector ) {
		selectors.push( selector );
		$priorAppeared.push();
	}

	// ":appeared" custom filter
	$.expr.pseudos.appeared = $.expr.createPseudo( function () {
		return function ( element ) {
			const $element = $( element );
			if ( ! $element.is( ':visible' ) ) {
				return false;
			}

			const windowLeft = $window.scrollLeft();
			const windowTop = $window.scrollTop();
			const offset = $element.offset();
			const left = offset.left;
			const top = offset.top;

			if (
				top + $element.height() >= windowTop &&
				top - ( $element.data( 'in-view-top-offset' ) || 0 ) <=
					windowTop + $window.height() &&
				left + $element.width() >= windowLeft &&
				left - ( $element.data( 'in-view-left-offset' ) || 0 ) <=
					windowLeft + $window.width()
			) {
				return true;
			}
			return false;
		};
	} );

	$.fn.extend( {
		// watching for element's appearance in browser viewport
		inView( selector, options ) {
			$.inView( this, options );
			return this;
		},
	} );

	$.extend( {
		inView( selector, options ) {
			const opts = $.extend( {}, defaults, options || {} );

			if ( ! checkBinded ) {
				const onCheck = function () {
					if ( checkLock ) {
						return;
					}
					checkLock = true;

					setTimeout( process, opts.interval );
				};

				// $(window).scroll(onCheck).resize(onCheck);
				$( window )
					.on( 'scroll', onCheck )
					.on( 'resize', onCheck )
					.on( 'click', onCheck );
				checkBinded = true;
			}

			if ( opts.force_process ) {
				setTimeout( process, opts.interval );
			}

			addSelector( selector );
		},
		// force element's appearance check
		force_appear() {
			if ( checkBinded ) {
				process();
				return true;
			}
			return false;
		},
	} );
} )( jQuery );
