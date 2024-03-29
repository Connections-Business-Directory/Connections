<?php

namespace Connections_Directory\Utility;

use ArrayAccess;
use cnCollection;

/**
 * More or less copied from Laravel Framework made PHP 5.2 compatible dropping unwanted methods to slim class size.
 *
 * Marked final to prevent it from being extended so maybe in the future this can simply be replaced by the Laravel Framework
 * as a library.
 *
 * @link https://github.com/laravel/framework
 */
final class _array {

	/**
	 * Determine whether the given value is array accessible.
	 *
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public static function accessible( $value ) {

		return is_array( $value ) || $value instanceof ArrayAccess;
	}

	/**
	 * Add an element to an array using "dot" notation if it doesn't exist.
	 *
	 * @param array  $array
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return array
	 */
	public static function add( $array, $key, $value ) {

		if ( is_null( self::get( $array, $key ) ) ) {
			self::set( $array, $key, $value );
		}

		return $array;
	}

	/**
	 * Collapse an array of arrays into a single array.
	 *
	 * @param array $array
	 *
	 * @return array
	 */
	public static function collapse( $array ) {

		$results = $array;

		foreach ( $array as $values ) {
			if ( $values instanceof cnCollection ) {
				$values = $values->all();
			} elseif ( ! is_array( $values ) ) {
				continue;
			}

			$results = array_merge( $results, $values );
		}

		return $results;
	}

	/**
	 * Divide an array into two arrays. One with keys and the other with values.
	 *
	 * @param array $array
	 *
	 * @return array
	 */
	public static function divide( $array ) {

		return array( array_keys( $array ), array_values( $array ) );
	}

	///**
	// * Flatten a multi-dimensional associative array with dots.
	// *
	// * @param  array  $array
	// * @param  string $prepend
	// *
	// * @return array
	// */
	//public static function dot( $array, $prepend = '' ) {
	//
	//	$results = [];
	//
	//	foreach ( $array as $key => $value ) {
	//		if ( is_array( $value ) && ! empty( $value ) ) {
	//			$results = array_merge( $results, static::dot( $value, $prepend . $key . '.' ) );
	//		} else {
	//			$results[ $prepend . $key ] = $value;
	//		}
	//	}
	//
	//	return $results;
	//}

	/**
	 * Get all of the given array except for a specified array of items.
	 *
	 * @param array        $array
	 * @param array|string $keys
	 *
	 * @return array
	 */
	public static function except( $array, $keys ) {

		self::forget( $array, $keys );

		return $array;
	}

	/**
	 * Determine if the given key exists in the provided array.
	 *
	 * @param ArrayAccess|array $array
	 * @param string|int        $key
	 *
	 * @return bool
	 */
	public static function exists( $array, $key ) {

		if ( $array instanceof ArrayAccess ) {
			return $array->offsetExists( $key );
		}

		return array_key_exists( $key, $array );
	}

	/**
	 * Return the first element in an array passing a given truth test.
	 *
	 * @param array         $array
	 * @param callable|null $callback
	 * @param mixed         $default
	 *
	 * @return mixed
	 */
	public static function first( $array, callable $callback = null, $default = null ) {

		if ( is_null( $callback ) ) {
			if ( empty( $array ) ) {
				return $default;
			}

			foreach ( $array as $item ) {
				return $item;
			}
		}

		foreach ( $array as $key => $value ) {
			if ( call_user_func( $callback, $value, $key ) ) {
				return $value;
			}
		}

		return $default;
	}

	/**
	 * Recursively implode a multi-dimensional array.
	 *
	 * @since 8.2
	 *
	 * @param string $glue
	 * @param array  $pieces
	 *
	 * @return string
	 */
	public static function implodeDeep( $glue, $pieces ) {

		$implode = array();

		if ( ! is_array( $pieces ) ) {

			$pieces = array( $pieces );
		}

		foreach ( $pieces as $piece ) {

			if ( is_array( $piece ) ) {

				$implode[] = self::implodeDeep( $glue, $piece );

			} else {

				$implode[] = $piece;
			}
		}

		return implode( $glue, $implode );
	}

	/**
	 * Determine if supplied array is a multidimensional array or not.
	 *
	 * @since 8.5.19
	 *
	 * @param array $value
	 *
	 * @return bool
	 */
	public static function isDimensional( array $value ) {

		return ! ( count( $value ) === count( $value, COUNT_RECURSIVE ) );
	}

	/**
	 * Return the last element in an array passing a given truth test.
	 *
	 * @param array         $array
	 * @param callable|null $callback
	 * @param mixed         $default
	 *
	 * @return mixed
	 */
	public static function last( $array, callable $callback = null, $default = null ) {

		if ( is_null( $callback ) ) {
			return empty( $array ) ? $default : end( $array );
		}

		return self::first( array_reverse( $array, true ), $callback, $default );
	}

	///**
	// * Flatten a multi-dimensional array into a single level.
	// *
	// * @param  array $array
	// * @param  int   $depth
	// *
	// * @return array
	// */
	//public static function flatten( $array, $depth = INF ) {
	//
	//	return array_reduce( $array,
	//		function ( $result, $item ) use ( $depth ) {
	//
	//			$item = $item instanceof cnCollection ? $item->all() : $item;
	//
	//			if ( ! is_array( $item ) ) {
	//				return array_merge( $result, [ $item ] );
	//			} elseif ( $depth === 1 ) {
	//				return array_merge( $result, array_values( $item ) );
	//			} else {
	//				return array_merge( $result, static::flatten( $item, $depth - 1 ) );
	//			}
	//		},
	//		                 [] );
	//}

	/**
	 * Remove one or many array items from a given array using "dot" notation.
	 *
	 * @param array        $array
	 * @param array|string $keys
	 *
	 * @return void
	 */
	public static function forget( &$array, $keys ) {

		$original = &$array;

		$keys = (array) $keys;

		if ( count( $keys ) === 0 ) {
			return;
		}

		foreach ( $keys as $key ) {
			// if the exact key exists in the top-level, remove it
			if ( self::exists( $array, $key ) ) {
				unset( $array[ $key ] );

				continue;
			}

			$parts = explode( '.', $key );

			// clean up before each pass
			$array = &$original;

			while ( count( $parts ) > 1 ) {
				$part = array_shift( $parts );

				if ( isset( $array[ $part ] ) && is_array( $array[ $part ] ) ) {
					$array = &$array[ $part ];
				} else {
					continue 2;
				}
			}

			unset( $array[ array_shift( $parts ) ] );
		}
	}

	/**
	 * Get an item from an array using "dot" notation.
	 *
	 * @param ArrayAccess|array $array
	 * @param string            $key
	 * @param mixed             $default
	 *
	 * @return mixed
	 */
	public static function get( $array, $key, $default = null ) {

		if ( ! self::accessible( $array ) ) {
			return $default;
		}

		if ( is_null( $key ) ) {
			return $array;
		}

		if ( self::exists( $array, $key ) ) {
			return $array[ $key ];
		}

		foreach ( explode( '.', $key ) as $segment ) {
			if ( self::accessible( $array ) && self::exists( $array, $segment ) ) {
				$array = $array[ $segment ];
			} else {
				return $default;
			}
		}

		return $array;
	}

	/**
	 * Check if an item or items exist in an array using "dot" notation.
	 *
	 * @param ArrayAccess|array $array
	 * @param string|array      $keys
	 *
	 * @return bool
	 */
	public static function has( $array, $keys ) {

		if ( is_null( $keys ) ) {
			return false;
		}

		$keys = (array) $keys;

		if ( ! $array ) {
			return false;
		}

		if ( array() === $keys ) {
			return false;
		}

		foreach ( $keys as $key ) {
			$subKeyArray = $array;

			if ( self::exists( $array, $key ) ) {
				continue;
			}

			foreach ( explode( '.', $key ) as $segment ) {
				if ( self::accessible( $subKeyArray ) && self::exists( $subKeyArray, $segment ) ) {
					$subKeyArray = $subKeyArray[ $segment ];
				} else {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Determines if an array is associative.
	 *
	 * An array is "associative" if it doesn't have sequential numerical keys beginning with zero.
	 *
	 * @param array $array
	 *
	 * @return bool
	 */
	public static function isAssoc( array $array ) {

		$keys = array_keys( $array );

		return array_keys( $keys ) !== $keys;
	}

	/**
	 * Get a subset of the items from the given array.
	 *
	 * @param array        $array
	 * @param array|string $keys
	 *
	 * @return array
	 */
	public static function only( $array, $keys ) {

		return array_intersect_key( $array, array_flip( (array) $keys ) );
	}

	/**
	 * Pluck an array of values from an array.
	 *
	 * @param array             $array
	 * @param string|array      $value
	 * @param string|array|null $key
	 *
	 * @return array
	 */
	public static function pluck( $array, $value, $key = null ) {

		$results = array();

		list( $value, $key ) = self::explodePluckParameters( $value, $key );

		foreach ( $array as $item ) {
			$itemValue = self::data_get( $item, $value );

			// If the key is "null", we will just append the value to the array and keep
			// looping. Otherwise we will key the array using the value of the key we
			// received from the developer. Then we'll return the final array form.
			if ( is_null( $key ) ) {
				$results[] = $itemValue;
			} else {
				$itemKey = self::data_get( $item, $key );

				$results[ $itemKey ] = $itemValue;
			}
		}

		return $results;
	}

	/**
	 * Explode the "value" and "key" arguments passed to "pluck".
	 *
	 * @param string|array      $value
	 * @param string|array|null $key
	 *
	 * @return array
	 */
	protected static function explodePluckParameters( $value, $key ) {

		$value = is_string( $value ) ? explode( '.', $value ) : $value;

		$key = is_null( $key ) || is_array( $key ) ? $key : explode( '.', $key );

		return array( $value, $key );
	}

	/**
	 * Get an item from an array or object using "dot" notation.
	 *
	 * @param mixed        $target
	 * @param string|array $key
	 * @param mixed        $default
	 *
	 * @return mixed
	 */
	public static function data_get( $target, $key, $default = null ) {

		if ( is_null( $key ) ) {
			return $target;
		}

		$key = is_array( $key ) ? $key : explode( '.', $key );

		while ( ! is_null( $segment = array_shift( $key ) ) ) {
			if ( '*' === $segment ) {
				if ( $target instanceof cnCollection ) {
					$target = $target->all();
				} elseif ( ! is_array( $target ) ) {
					return $default;
				}

				$result = self::pluck( $target, $key );

				return in_array( '*', $key ) ? self::collapse( $result ) : $result;
			}

			if ( self::accessible( $target ) && self::exists( $target, $segment ) ) {
				$target = $target[ $segment ];
			} elseif ( is_object( $target ) && isset( $target->{$segment} ) ) {
				$target = $target->{$segment};
			} else {
				return $default;
			}
		}

		return $target;
	}

	/**
	 * Push an item onto the beginning of an array.
	 *
	 * @param array $array
	 * @param mixed $value
	 * @param mixed $key
	 *
	 * @return array
	 */
	public static function prepend( $array, $value, $key = null ) {

		if ( is_null( $key ) ) {
			array_unshift( $array, $value );
		} else {
			$array = array( $key => $value ) + $array;
		}

		return $array;
	}

	/**
	 * Get a value from the array, and remove it.
	 *
	 * @param array  $array
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	public static function pull( &$array, $key, $default = null ) {

		if ( ! self::accessible( $array ) ) {
			return $default;
		}

		$value = self::get( $array, $key, $default );

		self::forget( $array, $key );

		return $value;
	}

	/**
	 * Push a given value to the end of the array using "dot" notation in a given key
	 *
	 * Inspired by:
	 * @link  https://github.com/adbario/php-dot-notation
	 *
	 * @since 9.9
	 *
	 * @param array $array
	 * @param mixed $key
	 * @param mixed $value
	 * @param mixed $default
	 *
	 * @return array
	 */
	public static function push( &$array, $key, $value = null, $default = null ) {

		if ( is_null( $value ) ) {
			return $array;
		}

		$items = self::get( $array, $key, $default );

		if ( is_array( $items ) || is_null( $items ) ) {
			$items[] = $value;
			$array   = self::set( $array, $key, $items );
		}

		return $array;
	}

	/**
	 * Get one or a specified number of random values from an array.
	 *
	 * @param array      $array
	 * @param int|null   $number
	 * @param bool|false $preserveKeys
	 *
	 * @return mixed
	 *
	 * @throws \InvalidArgumentException
	 */
	public static function random( $array, $number = null, $preserveKeys = false ) {

		$requested = is_null( $number ) ? 1 : $number;

		$count = count( $array );

		if ( $requested > $count ) {
			throw new \InvalidArgumentException(
				"You requested {$requested} items, but there are only {$count} items available."
			);
		}

		if ( is_null( $number ) ) {
			return $array[ array_rand( $array ) ];
		}

		if ( 0 === (int) $number ) {
			return array();
		}

		$keys = array_rand( $array, $number );

		$results = array();

		if ( $preserveKeys ) {
			foreach ( (array) $keys as $key ) {
				$results[ $key ] = $array[ $key ];
			}
		} else {
			foreach ( (array) $keys as $key ) {
				$results[] = $array[ $key ];
			}
		}

		return $results;
	}

	/**
	 * Set an array item to a given value using "dot" notation.
	 *
	 * If no key is given to the method, the entire array will be replaced.
	 *
	 * @param array  $array
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return array
	 */
	public static function set( &$array, $key, $value ) {

		if ( is_null( $key ) ) {
			return $array = $value;
		}

		$keys = explode( '.', $key );

		while ( count( $keys ) > 1 ) {
			$key = array_shift( $keys );

			// If the key doesn't exist at this depth, we will just create an empty array
			// to hold the next value, allowing us to create the arrays to hold final
			// values at the correct depth. Then we'll keep digging into the array.
			if ( ! isset( $array[ $key ] ) || ! is_array( $array[ $key ] ) ) {
				$array[ $key ] = array();
			}

			$array = &$array[ $key ];
		}

		$array[ array_shift( $keys ) ] = $value;

		return $array;
	}

	/**
	 * Shuffle the given array and return the result.
	 *
	 * @param array $array
	 *
	 * @return array
	 */
	public static function shuffle( $array ) {

		shuffle( $array );

		return $array;
	}

	/**
	 * Sort the array using the given callback or "dot" notation.
	 *
	 * @param array           $array
	 * @param callable|string $callback
	 *
	 * @return array
	 */
	public static function sort( $array, $callback ) {

		return cnCollection::make( $array )->sortBy( $callback )->all();
	}

	/**
	 * Recursively sort an array by keys and values.
	 *
	 * @param array $array
	 *
	 * @return array
	 */
	public static function sortRecursive( $array ) {

		foreach ( $array as &$value ) {
			if ( is_array( $value ) ) {
				$value = self::sortRecursive( $value );
			}
		}

		if ( self::isAssoc( $array ) ) {
			ksort( $array );
		} else {
			sort( $array );
		}

		return $array;
	}

	/**
	 * Filter the array using the given callback.
	 *
	 * @param array    $array
	 * @param callable $callback
	 *
	 * @return array
	 */
	public static function where( $array, $callback ) {

		if ( version_compare( PHP_VERSION, '5.6.0' ) >= 0 ) {

			return array_filter( $array, $callback, ARRAY_FILTER_USE_BOTH );

		} else {

			return array_filter( $array, $callback );
		}
	}
}
