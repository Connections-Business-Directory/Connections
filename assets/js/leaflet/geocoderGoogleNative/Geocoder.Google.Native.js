(function (L) {

	var GoogleNative = {

		class: L.Class.extend({
			options: {
				// serviceUrl: 'https://maps.googleapis.com/maps/api/geocode/json',
				geocodingQueryParams: {},
				reverseQueryParams: {}
			},

			initialize: function( key, options ) {

				this._key = key;
				L.setOptions( this, options );
			},

			geocode: function( query, cb, context ) {

				var params = {
					address: query
				};

				params = L.Util.extend( params, this.options.geocodingQueryParams );

				var geocoder = new google.maps.Geocoder();

				geocoder.geocode( params, function( response, status ) {

					var results = [],
					    loc,
					    latLng,
					    latLngBounds;

					if ( status === 'OK' ) {

						// console.log( response );

						for ( var i = 0; i <= response.length - 1; i++ ) {

							loc = response[ i ];

							latLng = L.latLng( loc.geometry.location.lat(), loc.geometry.location.lng() );

							latLngBounds = L.latLngBounds(
								L.latLng( loc.geometry.viewport.northeast ),
								L.latLng( loc.geometry.viewport.southwest )
							);

							results[ i ] = {
								name:       loc.formatted_address,
								bbox:       latLngBounds,
								center:     latLng,
								properties: loc.address_components
							};
						}

					} else {

						console.log( 'Geocode was not successful for the following reason: ' + status );
					}

					cb.call( context, results );
				});
			},

			reverse: function( location, scale, cb, context ) {

				var params = {
					location: { lat: parseFloat( location.lat ), lng: parseFloat( location.lng ) }
				};

				params = L.Util.extend( params, this.options.reverseQueryParams );

				var geocoder = new google.maps.Geocoder;

				geocoder.geocode( params, function( response, status ) {

					var results = [],
					    loc,
					    latLng,
					    latLngBounds;

					if ( status === 'OK' ) {

						// console.log( response );

						for ( var i = 0; i <= response.length - 1; i++ ) {

							loc = response[ i ];

							latLng = L.latLng( loc.geometry.location.lat(), loc.geometry.location.lng() );

							latLngBounds = L.latLngBounds(
								L.latLng( loc.geometry.viewport.northeast ),
								L.latLng( loc.geometry.viewport.southwest )
							);

							results[ i ] = {
								name:       loc.formatted_address,
								bbox:       latLngBounds,
								center:     latLng,
								properties: loc.address_components
							};
						}

					} else {

						console.log( 'Geocoder failed due to: ' + status );
					}

					cb.call( context, results );
				});

			}
		}),

		factory: function( key, options ) {

			return new L.Control.Geocoder.Google( key, options );
		}
	};

	L.Util.extend( L.Control.Geocoder, {
		GoogleNative:  GoogleNative.class,
		google_native: GoogleNative.factory
	});

}(L));
