;jQuery( function( $ ) {

	var map = [];
	var id  = 0;

	$.fn.mapBlock = function( options ) {
		console.log('mapBlock found.');

		var attributes = {};

		$.each( $( this ).get( 0 ).attributes, function( i, attrib ) {

			if ( 'data' === attrib.name.substr( 0, 4 ) ) {

				attributes[ attrib.name ] = (attrib.value).replace( /_/g, " " );
			}
		});

		console.log( attributes );

		$.each( attributes, function( keyRaw, value ) {

			var key = keyRaw.replace( 'data-', '' );
			var arrKey = key.split( '-' );

			if ( arrKey.length === 1 ) {
				options[ arrKey[ 0 ] ] = value;
			}



			// if ( arrKey.length === 2 ) {
			// 	if ( typeof options[ arrKey[ 0 ] ] === 'undefined' ) {
			// 		options[ arrKey[ 0 ] ] = {};
			// 	}
			// 	options[ arrKey[ 0 ] ][ arrKey[ 1 ] ] = value;
			// }

			// if ( arrKey.length === 3 ) {
			// 	if ( typeof options[ arrKey[ 0 ] ] === 'undefined' ) {
			// 		options[ arrKey[ 0 ] ] = {};
			// 	}
			// 	if ( typeof options[ arrKey[ 0 ] ][ arrKey[ 1 ] ] === 'undefined' ) {
			// 		options[ arrKey[ 0 ] ][ arrKey[ 1 ] ] = {};
			// 	}
			// 	options[ arrKey[ 0 ] ][ arrKey[ 1 ] ][ arrKey[ 2 ] ] = value;
			// }
			//
			// if ( arrKey.length === 4 ) {
			// 	if ( typeof options[ arrKey[ 0 ] ] === 'undefined' ) {
			// 		options[ arrKey[ 0 ] ] = {};
			// 	}
			// 	if ( typeof options[ arrKey[ 0 ] ][ arrKey[ 1 ] ] === 'undefined' ) {
			// 		options[ arrKey[ 0 ] ][ arrKey[ 1 ] ] = {};
			// 	}
			// 	if ( typeof options[ arrKey[ 0 ] ][ arrKey[ 1 ] ][ arrKey[ 2 ] ] === 'undefined' ) {
			// 		options[ arrKey[ 0 ] ][ arrKey[ 1 ] ][ arrKey[ 2 ] ] = {};
			// 	}
			// 	options[ arrKey[ 0 ] ][ arrKey[ 1 ] ][ arrKey[ 2 ] ][ arrKey[ 3 ] ] = value;
			// }
			//
			// if ( arrKey.length === 5 ) {
			// 	if ( typeof options[ arrKey[ 0 ] ] === 'undefined' ) {
			// 		options[ arrKey[ 0 ] ] = {};
			// 	}
			// 	if ( typeof options[ arrKey[ 0 ] ][ arrKey[ 1 ] ] === 'undefined' ) {
			// 		options[ arrKey[ 0 ] ][ arrKey[ 1 ] ] = {};
			// 	}
			// 	if ( typeof options[ arrKey[ 0 ] ][ arrKey[ 1 ] ][ arrKey[ 2 ] ] === 'undefined' ) {
			// 		options[ arrKey[ 0 ] ][ arrKey[ 1 ] ][ arrKey[ 2 ] ] = {};
			// 	}
			// 	if ( typeof options[ arrKey[ 0 ] ][ arrKey[ 1 ] ][ arrKey[ 2 ] ][ arrKey[ 3 ] ] === 'undefined' ) {
			// 		options[ arrKey[ 0 ] ][ arrKey[ 1 ] ][ arrKey[ 2 ] ][ arrKey[ 3 ] ] = {};
			// 	}
			//
			// 	options[ arrKey[ 0 ] ][ arrKey[ 1 ] ][ arrKey[ 2 ] ][ arrKey[ 3 ] ][ arrKey[ 4 ] ] = value;
			// }
		});

		console.log( options );

		var mapContainer = $( this ).attr( 'id' );
		console.log( mapContainer );

		// var tiles = L.tileLayer(
		// 	'//maps.wikimedia.org/osm-intl/{z}/{x}/{y}{r}.png',
		// 	{
		// 		attribution: '<a target="_blank" href="https://wikimediafoundation.org/wiki/Maps_Terms_of_Use">Wikimedia</a>'
		// 	}
		// );

		$( '#' + mapContainer ).on( 'appear', { id: id, data: options }, function( event, elements ) {

			elements.each( function() {

				var id = event.data.id;
				var options = event.data.data;
				var mapContainer = $( this ).attr( 'id' );

				console.log( mapContainer + ':isVisible');

				if ( 'undefined' === typeof map[ id ] ) {

					/*
					 * Temporarily set the array index to `addingMap` because maps are added async,
					 * so if the event is triggered while the map is still being added it will
					 * trigger a Leaflet error stating map has already being initialized.
					 *
					 * NOTE: Patching jQuery.appear() with PR gh-66 seems to negate this issue.
					 */
					// map[ id ] = 'addingMap';
					map[ id ] = L.map( mapContainer ).setView( L.latLng( options.center.split( ',' ) ), 1 );

					/*
					 * Clear the attribution, removing the Leaflet back link, so it can be customized.
					 */
					map[ id ].attributionControl.setPrefix( '' );

					$.each( $( this ).children( 'map-tilelayer' ), function( index, item ) {

						var provider = $( item );

						switch ( provider.data( 'id' ) ) {

							case 'google':

								var tiles = L.gridLayer.googleMutant({
									// type: provider.type,
									attribution: provider.html(),
								});

								break;

							default:

								var tiles = L.tileLayer(
									provider.data( 'url' ),
									{
										attribution: provider.html(),
										subdomains: provider.data( 'subdomains' ),
										minZoom: provider.data( 'minzoom' ),
										maxZoom: provider.data( 'maxzoom' )
									}
								);
						}


						tiles.addTo( map[ id ] );
					});

					var layers = [];
					var overlay = {};
					var bounds = L.latLngBounds([]); // Create an empty instance of latLngBounds. Will be extended as lay groups are added.
					var markerCount = 0;

					/*
					 * Find each layer group attached to the base map and add it as a feature group.
					 */
					$.each( $( this ).children( 'map-layergroup' ), function( index, item ) {

						console.log( item );
						var layer = $( item );
						var layerID = layer.data( 'id' );
						var layerName = layer.data( 'name' );
						var enableControl = layer.data( 'control' );
						var markers = [];

						/*
						 * Find each marker attached to the current layer group and attach it to the feature group.
						 * Expand the bounds to to fit the markers added to the group.
						 *
						 * NOTE: This code is duplicated below and could me abstracted to remove duplicate code.
						 */
						$.each( $( this ).children( 'map-marker' ), function( index, item ) {

							console.log( item );
							var marker    = $( item );
							var latitude  = marker.data( 'latitude' );
							var longitude = marker.data( 'longitude' );
							var popup     = marker.find( 'map-marker-popup' );

							if ( 0 === popup.length ) {

								markers.push( L.marker( [ latitude, longitude ] ) );

							} else {

								markers.push( L.marker( [ latitude, longitude ] ).bindPopup( popup.html() ) );
							}

							markerCount++;
						});

						layers[ layerID ] = L.featureGroup( markers ).addTo( map[ id ] );

						// Extend the map bounds to fit all makers added to layer group.
						bounds.extend( layers[ layerID ].getBounds() );

						// Add the control for the layer.
						if ( enableControl ) {

							overlay[ layerName ] = layers[ layerID ];
						}
					});

					/*
					 * Add each markers added directly to the map to the base map as a feature group.
					 * Expand the bounds to to fit the markers added to the group.
					 *
					 * NOTE: This is mostly duplicate code from above and could like be abstracted out.
					 */
					$.each( $( this ).children( 'map-marker' ), function( index, item ) {

						var markers = [];

						console.log( item );
						var marker    = $( item );
						var latitude  = marker.data( 'latitude' );
						var longitude = marker.data( 'longitude' );
						var popup     = marker.find( 'map-marker-popup' );

						if ( 0 === popup.length ) {

							markers.push( L.marker( [ latitude, longitude ] ) );

						} else {

							markers.push( L.marker( [ latitude, longitude ] ).bindPopup( popup.html() ) );
						}

						var group = L.featureGroup( markers ).addTo( map[ id ] );

						// Extend the map bounds to fit all makers added to layer group.
						bounds.extend( group.getBounds() );

						markerCount++;
					});

					// Only add the control if a layer group has been configured to be toggleable.
					if ( ! $.isEmptyObject( overlay ) ) {

						L.control.layers( {}, overlay ).addTo( map[ id ] );
					}

					if ( bounds.isValid() ) {
						// Set the map view to fit bounds of all layer groups.
						map[ id ].fitBounds( bounds, { padding: L.point( 20, 20 ) } );
					}

					// if the marker count is 1, use the supplied zoom value.
					if ( 1 >= markerCount ) {
						map[ id ].setZoom( options.zoom );
					}

					/*
					 * Add a listener to fit map bounds.
					 * @link https://stackoverflow.com/a/45303126/5351316
					 */
					map[ id ].on( 'overlayadd overlayremove', function(e) {
						console.log(e);
						// map[ id ].fitBounds(e.layer.getBounds());

						// Create new empty bounds
						var bounds = new L.LatLngBounds();

						// Iterate the map's layers
						map[ id ].eachLayer( function (layer) {

							// Check if layer is a featuregroup
							if ( layer instanceof L.FeatureGroup ) {

								// Extend bounds with group's bounds
								bounds.extend( layer.getBounds() );
							}
						});

						// Check if bounds are valid (could be empty)
						if ( bounds.isValid() ) {

							// Valid, fit bounds
							map[ id ].fitBounds( bounds );

						} else {

							// Invalid, fit world
							map[ id ].fitWorld();
						}
					});
				}

			});
		});

		$.inView( '#' + mapContainer );

		/**
		 * @link https://stackoverflow.com/a/16462443/5351316
		 * @link https://stackoverflow.com/a/48303093/5351316
		 */

		// var observer = new MutationObserver( function( mutations ) {
		// 	console.log( 'Attributes changed!' );
		// });
		//
		// var target = document.querySelector( '#' + mapContainer );
		//
		// observer.observe( target, {
		// 	attributes: true
		// });

		// var targetNode = document.getElementById( mapContainer );
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
		 * @link https://stackoverflow.com/a/44670818/5351316
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
		// respondToVisibility( document.getElementById( mapContainer ), visible => {
		//
		// 	console.log( 'isVisible');
		// });


		id++;
	};

	$( 'map-block' ).each( function() {
		var options = {};
		$( this ).mapBlock( options );
	});

	$.force_appear();
});


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
(function($) {
	var selectors = [];

	var check_binded = false;
	var check_lock = false;
	var defaults = {
		interval: 250,
		force_process: false
	};
	var $window = $(window);

	var $prior_appeared = [];
	var $in_view = [];

	function appeared(selector) {
		return $(selector).filter(function() {
			return $(this).is(':appeared');
		});
	}

	function process() {
		check_lock = false;
		for (var index = 0, selectorsLength = selectors.length; index < selectorsLength; index++) {
			var $appeared = appeared(selectors[index]);

			if ($appeared.length && $in_view[index] !== true){
				$appeared.trigger('appear', [$appeared]);
				$in_view[index] = true;
			}

			if ($prior_appeared[index]) {
				var $disappeared = $prior_appeared[index].not($appeared);
				if ($disappeared.length) {
					$disappeared.trigger('disappear', [$disappeared]);
					$in_view[index] = false;
				}
			}
			$prior_appeared[index] = $appeared;
		}
	}

	function add_selector(selector) {
		selectors.push(selector);
		$prior_appeared.push();
	}

	// ":appeared" custom filter
	$.expr.pseudos.appeared = $.expr.createPseudo(function(arg) {
		return function(element) {
			var $element = $(element);
			if (!$element.is(':visible')) {
				return false;
			}

			var window_left = $window.scrollLeft();
			var window_top = $window.scrollTop();
			var offset = $element.offset();
			var left = offset.left;
			var top = offset.top;

			if (top + $element.height() >= window_top &&
				top - ($element.data('in-view-top-offset') || 0) <= window_top + $window.height() &&
				left + $element.width() >= window_left &&
				left - ($element.data('in-view-left-offset') || 0) <= window_left + $window.width()) {
				return true;
			} else {
				return false;
			}
		};
	});

	$.fn.extend({
		// watching for element's appearance in browser viewport
		inView: function(selector, options) {
			$.inView(this, options);
			return this;
		}
	});

	$.extend({
		inView: function(selector, options) {
			var opts = $.extend({}, defaults, options || {});

			if (!check_binded) {
				var on_check = function() {
					if (check_lock) {
						return;
					}
					check_lock = true;

					setTimeout(process, opts.interval);
				};

				// $(window).scroll(on_check).resize(on_check);
				$(window).on( 'scroll', on_check ).on( 'resize', on_check ).on( 'click', on_check );
				check_binded = true;
			}

			if (opts.force_process) {
				setTimeout(process, opts.interval);
			}

			add_selector(selector);
		},
		// force elements's appearance check
		force_appear: function() {
			if (check_binded) {
				process();
				return true;
			}
			return false;
		}
	});
})(jQuery);
