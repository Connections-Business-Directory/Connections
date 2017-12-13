<?php
class cnCountries {

	protected static $countries;

	/**
	 * @access public
	 * @since  8.7
	 * @static
	 *
	 * @param bool   $detailed
	 * @param string $return
	 *
	 * @return array|WP_Error
	 */
	public static function getAll( $detailed = FALSE, $return = OBJECT ) {

		$list = $detailed ? 'longlist' : 'shortlist';

		if ( ! isset( self::$countries[ $list ] ) ) {

			self::$countries[ $list ] = json_decode(
				self::getFile( CN_PATH . 'vendor/rinvex/resources/data/' . $list . '.json' ),
				TRUE
			);
		}

		if ( ARRAY_A == $return ) {

			$data = self::$countries[ $list ];

		} elseif ( OBJECT == $return ) {

			$data = array_map( array( __CLASS__, 'initCountryObject' ), self::$countries[ $list ] );

		} else {

			$data = new WP_Error(
				'invalid_return_type_specified',
				__( 'Invalid return type specified. Valid options are ARRAY_A and OBJECT.', 'connections' ),
				$return
			);
		}

		return $data;
	}

	/**
	 * @access public
	 * @since  8.7
	 * @static
	 *
	 * @param string $code
	 * @param string $return
	 *
	 * @return array|cnCountry|WP_Error
	 */
	public static function getByCode( $code, $return = OBJECT ) {

		// @link https://stackoverflow.com/a/39576492/5351316
		$code = strtolower( $code );

		if ( ! isset( self::$countries[ $code ] ) ) {

			$path = CN_PATH . 'vendor/rinvex/resources/data/' . $code . '.json';
			$file = self::getFile( $path );

			if ( ! is_wp_error( $file ) ) {

				$json = json_decode( $file, TRUE );

				if ( is_null( $json ) ) {

					// JSON failed to decode. Corrupt file???
					$country = new WP_Error(
						'json_decode_error',
						__( 'JSON could not be decoded.', 'connections' ),
						$json
					);

				} else {

					// JSON decoded successfully.
					$country = $json;
				}

			} else {

				// Failed to open file, WP_Error.
				$country = $file;
			}

			// Return early if WP_Error.
			if ( is_wp_error( $country ) ) {

				return $country;

			} else {

				self::$countries[ $code ] = $country;
			}

		}

		switch ( $return ) {

			case ARRAY_A:

				return self::$countries[ $code ];
				break;

			case OBJECT:

				return new cnCountry( self::$countries[ $code ] );
				break;

			default:

				return new WP_Error(
					'invalid_return_type_specified',
					__( 'Invalid return type specified. Valid options are ARRAY_A and OBJECT.', 'connections' ),
					$return
				);
		}
	}

	/**
	 * Get contents of the given file path.
	 *
	 * @access public
	 * @since  8.7
	 * @static
	 *
	 * @param $path
	 *
	 * @return string|WP_Error
	 */
	protected static function getFile( $path ) {

		if ( ! file_exists( $path ) ) {

			return new WP_Error(
				'country_data_not_found',
				__( 'Country code may be misspelled, invalid, or data not found.', 'connections' ),
				$path
			);
		}

		return file_get_contents( $path );
	}

	/**
	 * Callback for array_map in @see cnCountries::getAll().
	 *
	 * Return cnCountry object using supplied attributes.
	 *
	 * @param array $attributes
	 *
	 * @return cnCountry
	 */
	protected static function initCountryObject( $attributes ) {

		return new cnCountry( $attributes );
	}
}
