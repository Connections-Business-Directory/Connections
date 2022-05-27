<?php

namespace Connections_Directory\Utility;

use ArrayAccess;
use ArrayIterator;
use CachingIterator;
use Closure;
use cnToArray;
use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use Traversable;

/**
 * More or less copied from Laravel Framework made PHP 5.2 compatible dropping unwanted methods to slim class size.
 *
 * @link https://github.com/laravel/framework
 *
 * @phpcs:disable PEAR.NamingConventions.ValidClassName.Invalid
 * @phpcs:disable PEAR.NamingConventions.ValidClassName.StartWithCapital
 */
class _collection implements Countable, IteratorAggregate, ArrayAccess, cnToArray {

	/**
	 * The items contained in the collection.
	 *
	 * @var array
	 */
	protected $items = array();

	/**
	 * Create a new collection.
	 *
	 * @param mixed $items
	 */
	public function __construct( $items = array() ) {

		$this->items = $this->getArrayableItems( $items );
	}

	/**
	 * Create a new collection instance if the value isn't one already.
	 *
	 * @param mixed $items
	 *
	 * @return static
	 */
	public static function make( $items = array() ) {

		return new self( $items );
	}

	/**
	 * Get all the items in the collection.
	 *
	 * @return array
	 */
	public function all() {

		return $this->items;
	}

	///**
	// * Get the average value of a given key.
	// *
	// * @param callable|string|null $callback
	// *
	// * @return mixed
	// */
	//public function avg( $callback = NULL ) {
	//
	//	if ( $count = $this->count() ) {
	//		return $this->sum( $callback ) / $count;
	//	}
	//}

	///**
	// * Alias for the "avg" method.
	// *
	// * @param callable|string|null $callback
	// *
	// * @return mixed
	// */
	//public function average( $callback = NULL ) {
	//
	//	return $this->avg( $callback );
	//}

	///**
	// * Get the median of a given key.
	// *
	// * @param null $key
	// *
	// * @return mixed
	// */
	//public function median( $key = NULL ) {
	//
	//	$count = $this->count();
	//
	//	if ( $count == 0 ) {
	//		return;
	//	}
	//
	//	$values = with( isset( $key ) ? $this->pluck( $key ) : $this )->sort()->values();
	//
	//	$middle = (int) ( $count / 2 );
	//
	//	if ( $count % 2 ) {
	//		return $values->get( $middle );
	//	}
	//
	//	return ( new static( [
	//		                     $values->get( $middle - 1 ),
	//		                     $values->get( $middle ),
	//	                     ] ) )->average();
	//}

	///**
	// * Get the mode of a given key.
	// *
	// * @param mixed $key
	// *
	// * @return array|null
	// */
	//public function mode( $key = NULL ) {
	//
	//	$count = $this->count();
	//
	//	if ( $count == 0 ) {
	//		return;
	//	}
	//
	//	$collection = isset( $key ) ? $this->pluck( $key ) : $this;
	//
	//	$counts = new self;
	//
	//	$collection->each( function ( $value ) use ( $counts ) {
	//
	//		$counts[ $value ] = isset( $counts[ $value ] ) ? $counts[ $value ] + 1 : 1;
	//	} );
	//
	//	$sorted = $counts->sort();
	//
	//	$highestValue = $sorted->last();
	//
	//	return $sorted->filter( function ( $value ) use ( $highestValue ) {
	//
	//		return $value == $highestValue;
	//	} )->sort()->keys()->all();
	//}

	///**
	// * Collapse the collection of items into a single array.
	// *
	// * @return static
	// */
	//public function collapse() {
	//
	//	return new static( Arr::collapse( $this->items ) );
	//}

	///**
	// * Determine if an item exists in the collection.
	// *
	// * @param mixed $key
	// * @param mixed $operator
	// * @param mixed $value
	// *
	// * @return bool
	// */
	//public function contains( $key, $operator = NULL, $value = NULL ) {
	//
	//	if ( func_num_args() == 1 ) {
	//		if ( $this->useAsCallable( $key ) ) {
	//			return ! is_null( $this->first( $key ) );
	//		}
	//
	//		return in_array( $key, $this->items );
	//	}
	//
	//	if ( func_num_args() == 2 ) {
	//		$value = $operator;
	//
	//		$operator = '=';
	//	}
	//
	//	return $this->contains( $this->operatorForWhere( $key, $operator, $value ) );
	//}

	///**
	// * Determine if an item exists in the collection using strict comparison.
	// *
	// * @param mixed $key
	// * @param mixed $value
	// *
	// * @return bool
	// */
	//public function containsStrict( $key, $value = NULL ) {
	//
	//	if ( func_num_args() == 2 ) {
	//		return $this->contains( function ( $item ) use ( $key, $value ) {
	//
	//			return data_get( $item, $key ) === $value;
	//		} );
	//	}
	//
	//	if ( $this->useAsCallable( $key ) ) {
	//		return ! is_null( $this->first( $key ) );
	//	}
	//
	//	return in_array( $key, $this->items, TRUE );
	//}

	/**
	 * Get the items in the collection that are not present in the given items.
	 *
	 * @param mixed $items
	 *
	 * @return static
	 */
	public function diff( $items ) {

		return new self( array_diff( $this->items, $this->getArrayableItems( $items ) ) );
	}

	/**
	 * Get the items in the collection whose keys are not present in the given items.
	 *
	 * @param mixed $items
	 *
	 * @return static
	 */
	public function diffKeys( $items ) {

		return new self( array_diff_key( $this->items, $this->getArrayableItems( $items ) ) );
	}

	/**
	 * Execute a callback over each item.
	 *
	 * @param callable $callback
	 *
	 * @return $this
	 */
	public function each( $callback ) {

		foreach ( $this->items as $key => $item ) {

			if ( $callback( $item, $key ) === false ) {
				break;
			}
		}

		return $this;
	}

	///**
	// * Determine if all items in the collection pass the given test.
	// *
	// * @param string|callable $key
	// * @param mixed           $operator
	// * @param mixed           $value
	// *
	// * @return bool
	// */
	//public function every( $key, $operator = NULL, $value = NULL ) {
	//
	//	if ( func_num_args() == 1 ) {
	//		$callback = $this->valueRetriever( $key );
	//
	//		foreach ( $this->items as $k => $v ) {
	//			if ( ! $callback( $v, $k ) ) {
	//				return FALSE;
	//			}
	//		}
	//
	//		return TRUE;
	//	}
	//
	//	if ( func_num_args() == 2 ) {
	//		$value = $operator;
	//
	//		$operator = '=';
	//	}
	//
	//	return $this->every( $this->operatorForWhere( $key, $operator, $value ) );
	//}

	/**
	 * Get all items except for those with the specified keys.
	 *
	 * @param mixed $keys
	 *
	 * @return static
	 */
	public function except( $keys ) {

		$keys = is_array( $keys ) ? $keys : func_get_args();

		return new self( _array::except( $this->items, $keys ) );
	}

	/**
	 * Run a filter over each of the items.
	 *
	 * @param callable|null $callback
	 *
	 * @return static
	 */
	public function filter( $callback = null ) {

		if ( $callback ) {
			return new self( _array::where( $this->items, $callback ) );
		}

		return new self( array_filter( $this->items ) );
	}

	/**
	 * Apply the callback if the value is truthy.
	 *
	 * @param bool     $value
	 * @param callable $callback
	 * @param callable $default
	 *
	 * @return mixed
	 */
	public function when( $value, $callback, $default = null ) {

		if ( $value ) {
			return $callback( $this );
		} elseif ( $default ) {
			return $default( $this );
		}

		return $this;
	}

	/**
	 * Filter items by the given key value pair.
	 *
	 * @param string $key
	 * @param mixed  $operator
	 * @param mixed  $value
	 *
	 * @return static
	 */
	public function where( $key, $operator, $value = null ) {

		if ( func_num_args() == 2 ) {
			$value = $operator;

			$operator = '=';
		}

		return $this->filter( $this->operatorForWhere( $key, $operator, $value ) );
	}

	/**
	 * Get an operator checker callback.
	 *
	 * @param string $key
	 * @param string $operator
	 * @param mixed  $value
	 *
	 * @return Closure
	 */
	protected function operatorForWhere( $key, $operator, $value ) {

		return function( $item ) use ( $key, $operator, $value ) {

			$retrieved = _array::data_get( $item, $key );

			$strings = array_filter(
				array( $retrieved, $value ),
				function( $value ) {

					return is_string( $value ) || ( is_object( $value ) && method_exists( $value, '__toString' ) );
				}
			);

			if ( count( $strings ) < 2 && 1 == count( array_filter( array( $retrieved, $value ), 'is_object' ) ) ) {

				return in_array( $operator, array( '!=', '<>', '!==' ) );
			}

			switch ( $operator ) {
				default:
				case '=':
				case '==':
					return $retrieved == $value;
				case '!=':
				case '<>':
					return $retrieved != $value;
				case '<':
					return $retrieved < $value;
				case '>':
					return $retrieved > $value;
				case '<=':
					return $retrieved <= $value;
				case '>=':
					return $retrieved >= $value;
				case '===':
					return $retrieved === $value;
				case '!==':
					return $retrieved !== $value;
			}
		};
	}

	/**
	 * Filter items by the given key value pair using strict comparison.
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return static
	 */
	public function whereStrict( $key, $value ) {

		return $this->where( $key, '===', $value );
	}

	/**
	 * Filter items by the given key value pair.
	 *
	 * @param string $key
	 * @param mixed  $values
	 * @param bool   $strict
	 *
	 * @return static
	 */
	public function whereIn( $key, $values, $strict = false ) {

		$callback = function( $item ) use ( $key, $values, $strict ) {

			return in_array( _array::data_get( $item, $key ), $values, $strict );
		};

		return $this->filter( $callback );
	}

	/**
	 * Filter items by the given key value pair using strict comparison.
	 *
	 * @param string $key
	 * @param mixed  $values
	 *
	 * @return static
	 */
	public function whereInStrict( $key, $values ) {

		return $this->whereIn( $key, $values, true );
	}

	/**
	 * Get the first item from the collection.
	 *
	 * @param callable|null $callback
	 * @param mixed         $default
	 *
	 * @return mixed
	 */
	public function first( callable $callback = null, $default = null ) {

		return _array::first( $this->items, $callback, $default );
	}

	///**
	// * Get a flattened array of the items in the collection.
	// *
	// * @param int $depth
	// *
	// * @return static
	// */
	//public function flatten( $depth = INF ) {
	//
	//	return new static( _array::flatten( $this->items, $depth ) );
	//}

	/**
	 * Flip the items in the collection.
	 *
	 * @return static
	 */
	public function flip() {

		return new self( array_flip( $this->items ) );
	}

	/**
	 * Remove an item from the collection by key.
	 *
	 * @param string|array $keys
	 *
	 * @return $this
	 */
	public function forget( $keys ) {

		foreach ( (array) $keys as $key ) {
			$this->offsetUnset( $key );
		}

		return $this;
	}

	/**
	 * Get an item from the collection by key.
	 *
	 * @param mixed $key
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public function get( $key, $default = null ) {

		if ( $this->offsetExists( $key ) ) {
			return $this->items[ $key ];
		}

		return $default;
	}

	///**
	// * Group an associative array by a field or using a callback.
	// *
	// * @param callable|string $groupBy
	// * @param bool            $preserveKeys
	// *
	// * @return static
	// */
	//public function groupBy( $groupBy, $preserveKeys = FALSE ) {
	//
	//	$groupBy = $this->valueRetriever( $groupBy );
	//
	//	$results = [];
	//
	//	foreach ( $this->items as $key => $value ) {
	//		$groupKeys = $groupBy( $value, $key );
	//
	//		if ( ! is_array( $groupKeys ) ) {
	//			$groupKeys = [ $groupKeys ];
	//		}
	//
	//		foreach ( $groupKeys as $groupKey ) {
	//			if ( ! array_key_exists( $groupKey, $results ) ) {
	//				$results[ $groupKey ] = new static;
	//			}
	//
	//			$results[ $groupKey ]->offsetSet( $preserveKeys ? $key : NULL, $value );
	//		}
	//	}
	//
	//	return new static( $results );
	//}

	/**
	 * Key an associative array by a field or using a callback.
	 *
	 * @param callable|string $keyBy
	 *
	 * @return static
	 */
	public function keyBy( $keyBy ) {

		//$keyBy = $this->valueRetriever( $keyBy );

		$results = array();

		foreach ( $this->items as $key => $item ) {

			//$resolvedKey = $keyBy( $item, $key );

			$resolvedKey = $this->useAsCallable( $keyBy ) ? $keyBy( $item, $key ) : _array::data_get( $item, $keyBy );

			if ( is_object( $resolvedKey ) ) {
				$resolvedKey = (string) $resolvedKey;
			}

			$results[ $resolvedKey ] = $item;
		}

		return new self( $results );
	}

	/**
	 * Determine if an item exists in the collection by key.
	 *
	 * @param mixed $key
	 *
	 * @return bool
	 */
	public function has( $key ) {

		return $this->offsetExists( $key );
	}

	/**
	 * Concatenate values of a given key as a string.
	 *
	 * @param string $value
	 * @param string $glue
	 *
	 * @return string
	 */
	public function implode( $value, $glue = null ) {

		$first = $this->first();

		if ( is_array( $first ) || is_object( $first ) ) {
			return implode( $glue, $this->pluck( $value )->all() );
		}

		return implode( $value, $this->items );
	}

	/**
	 * Intersect the collection with the given items.
	 *
	 * @param mixed $items
	 *
	 * @return static
	 */
	public function intersect( $items ) {

		return new self( array_intersect( $this->items, $this->getArrayableItems( $items ) ) );
	}

	/**
	 * Determine if the collection is empty or not.
	 *
	 * @return bool
	 */
	public function isEmpty() {

		return empty( $this->items );
	}

	/**
	 * Determine if the collection is not empty.
	 *
	 * @return bool
	 */
	public function isNotEmpty() {

		return ! $this->isEmpty();
	}

	/**
	 * Determine if the given value is callable, but not a string.
	 *
	 * @param mixed $value
	 *
	 * @return bool
	 */
	protected function useAsCallable( $value ) {

		return /*! is_string( $value ) &&*/ is_callable( $value );
	}

	/**
	 * Get the keys of the collection items.
	 *
	 * @return static
	 */
	public function keys() {

		return new self( array_keys( $this->items ) );
	}

	/**
	 * Get the last item from the collection.
	 *
	 * @param callable|null $callback
	 * @param mixed         $default
	 *
	 * @return mixed
	 */
	public function last( callable $callback = null, $default = null ) {

		return _array::last( $this->items, $callback, $default );
	}

	/**
	 * Get the values of a given key.
	 *
	 * @param string|array $value
	 * @param string|null  $key
	 *
	 * @return static
	 */
	public function pluck( $value, $key = null ) {

		return new self( _array::pluck( $this->items, $value, $key ) );
	}

	/**
	 * Run a map over each of the items.
	 *
	 * @param callable $callback
	 *
	 * @return static
	 */
	public function map( $callback ) {

		$keys = array_keys( $this->items );

		$items = array_map( $callback, $this->items, $keys );

		return new self( array_combine( $keys, $items ) );
	}

	/**
	 * Run an associative map over each of the items.
	 *
	 * The callback should return an associative array with a single key/value pair.
	 *
	 * @param callable $callback
	 *
	 * @return static
	 */
	public function mapWithKeys( $callback ) {

		$result = array();

		foreach ( $this->items as $key => $value ) {
			$assoc = $callback( $value, $key );

			foreach ( $assoc as $mapKey => $mapValue ) {
				$result[ $mapKey ] = $mapValue;
			}
		}

		return new self( $result );
	}

	///**
	// * Map a collection and flatten the result by a single level.
	// *
	// * @param callable $callback
	// *
	// * @return static
	// */
	//public function flatMap( $callback ) {
	//
	//	return $this->map( $callback )->collapse();
	//}

	/**
	 * Get the max value of a given key.
	 *
	 * @param callable|string|null $callback
	 *
	 * @return mixed
	 */
	public function max( $callback = null ) {

		$callback = $this->valueRetriever( $callback );

		return $this->filter(
			function( $value ) {

				return ! is_null( $value );
			}
		)->reduce(
			function( $result, $item ) use ( $callback ) {

				$value = $callback( $item );

				return is_null( $result ) || $value > $result ? $value : $result;
			}
		);
	}

	///**
	// * Get the max value of a given key.
	// *
	// * @param callable|string|null $callback
	// *
	// * @return mixed
	// */
	//public function maxWORKS( $callback = NULL ) {
	//
	//	$key = is_string( $callback ) && ! is_callable( $callback ) ? json_encode( $callback ) : json_encode( NULL );
	//
	//	$filter = create_function( '$value', 'return ! is_null( $value );' );
	//	$reduce = create_function(
	//		'$result, $item',
	//		'$function = create_function( \'$item\', \'return _array::data_get( $item, ' . $key . ' );\' );
	//		$callback = is_callable( \'' . $callback . '\' ) ? \'' . $callback . '\' : $function;
	//		$value = $callback( $item );
	//		return is_null( $result ) || $value > $result ? $value : $result;'
	//	);
	//
	//	return $this->filter( $filter )->reduce( $reduce );
	//}

	/**
	 * Merge the collection with the given items.
	 *
	 * @param mixed $items
	 *
	 * @return static
	 */
	public function merge( $items ) {

		return new self( array_merge( $this->items, $this->getArrayableItems( $items ) ) );
	}

	/**
	 * Create a collection by using this collection for keys and another for its values.
	 *
	 * @param mixed $values
	 *
	 * @return static
	 */
	public function combine( $values ) {

		return new self( array_combine( $this->all(), $this->getArrayableItems( $values ) ) );
	}

	/**
	 * Union the collection with the given items.
	 *
	 * @param mixed $items
	 *
	 * @return static
	 */
	public function union( $items ) {

		return new self( $this->items + $this->getArrayableItems( $items ) );
	}

	///**
	// * Get the min value of a given key.
	// *
	// * @param callable|string|null $callback
	// *
	// * @return mixed
	// */
	//public function min( $callback = NULL ) {
	//
	//	$callback = $this->valueRetriever( $callback );
	//
	//	return $this->filter( function ( $value ) {
	//
	//		return ! is_null( $value );
	//	} )->reduce( function ( $result, $item ) use ( $callback ) {
	//
	//		$value = $callback( $item );
	//
	//		return is_null( $result ) || $value < $result ? $value : $result;
	//	} );
	//}

	/**
	 * Create a new collection consisting of every n-th element.
	 *
	 * @param int $step
	 * @param int $offset
	 *
	 * @return static
	 */
	public function nth( $step, $offset = 0 ) {

		$new = array();

		$position = 0;

		foreach ( $this->items as $item ) {
			if ( $position % $step === $offset ) {
				$new[] = $item;
			}

			$position ++;
		}

		return new self( $new );
	}

	/**
	 * Get the items with the specified keys.
	 *
	 * @param mixed $keys
	 *
	 * @return static
	 */
	public function only( $keys ) {

		if ( is_null( $keys ) ) {
			return new self( $this->items );
		}

		$keys = is_array( $keys ) ? $keys : func_get_args();

		return new self( _array::only( $this->items, $keys ) );
	}

	///**
	// * "Paginate" the collection by slicing it into a smaller collection.
	// *
	// * @param int $page
	// * @param int $perPage
	// *
	// * @return static
	// */
	//public function forPage( $page, $perPage ) {
	//
	//	return $this->slice( ( $page - 1 ) * $perPage, $perPage );
	//}

	///**
	// * Partition the collection into two arrays using the given callback or key.
	// *
	// * @param callable|string $callback
	// *
	// * @return static
	// */
	//public function partition( $callback ) {
	//
	//	$partitions = [ new static, new static ];
	//
	//	$callback = $this->valueRetriever( $callback );
	//
	//	foreach ( $this->items as $key => $item ) {
	//		$partitions[ (int) ! $callback( $item ) ][ $key ] = $item;
	//	}
	//
	//	return new static( $partitions );
	//}

	/**
	 * Pass the collection to the given callback and return the result.
	 *
	 * @param callable $callback
	 *
	 * @return mixed
	 */
	public function pipe( $callback ) {

		return $callback( $this );
	}

	/**
	 * Get and remove the last item from the collection.
	 *
	 * @return mixed
	 */
	public function pop() {

		return array_pop( $this->items );
	}

	/**
	 * Push an item onto the beginning of the collection.
	 *
	 * @param mixed $value
	 * @param mixed $key
	 *
	 * @return $this
	 */
	public function prepend( $value, $key = null ) {

		$this->items = _array::prepend( $this->items, $value, $key );

		return $this;
	}

	/**
	 * Push an item onto the end of the collection.
	 *
	 * @param mixed $value
	 *
	 * @return $this
	 */
	public function push( $value ) {

		$this->offsetSet( null, $value );

		return $this;
	}

	/**
	 * Get and remove an item from the collection.
	 *
	 * @param mixed $key
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public function pull( $key, $default = null ) {

		return _array::pull( $this->items, $key, $default );
	}

	/**
	 * Put an item in the collection by key.
	 *
	 * @param mixed $key
	 * @param mixed $value
	 *
	 * @return $this
	 */
	public function put( $key, $value ) {

		$this->offsetSet( $key, $value );

		return $this;
	}

	/**
	 * Get one or a specified number of items randomly from the collection.
	 *
	 * @param int|null $number
	 *
	 * @return mixed
	 *
	 * @throws InvalidArgumentException
	 */
	public function random( $number = null ) {

		if ( is_null( $number ) ) {

			return _array::random( $this->items );
		}

		return new self( _array::random( $this->items, $number ) );
	}

	/**
	 * Reduce the collection to a single value.
	 *
	 * @param callable $callback
	 * @param mixed    $initial
	 *
	 * @return mixed
	 */
	public function reduce( $callback, $initial = null ) {

		return array_reduce( $this->items, $callback, $initial );
	}

	///**
	// * Create a collection of all elements that do not pass a given truth test.
	// *
	// * @param callable|mixed $callback
	// *
	// * @return static
	// */
	//public function reject( $callback ) {
	//
	//	if ( $this->useAsCallable( $callback ) ) {
	//		return $this->filter( function ( $value, $key ) use ( $callback ) {
	//
	//			return ! $callback( $value, $key );
	//		} );
	//	}
	//
	//	return $this->filter( function ( $item ) use ( $callback ) {
	//
	//		return $item != $callback;
	//	} );
	//}

	/**
	 * Reverse items order.
	 *
	 * @return static
	 */
	public function reverse() {

		return new self( array_reverse( $this->items, true ) );
	}

	/**
	 * Search the collection for a given value and return the corresponding key if successful.
	 *
	 * @param mixed $value
	 * @param bool  $strict
	 *
	 * @return mixed
	 */
	public function search( $value, $strict = false ) {

		if ( ! $this->useAsCallable( $value ) ) {
			return array_search( $value, $this->items, $strict );
		}

		foreach ( $this->items as $key => $item ) {
			if ( call_user_func( $value, $item, $key ) ) {
				return $key;
			}
		}

		return false;
	}

	/**
	 * Get and remove the first item from the collection.
	 *
	 * @return mixed
	 */
	public function shift() {

		return array_shift( $this->items );
	}

	/**
	 * Shuffle the items in the collection.
	 *
	 * @param int $seed
	 *
	 * @return static
	 */
	public function shuffle( $seed = null ) {

		$items = $this->items;

		if ( is_null( $seed ) ) {

			shuffle( $items );

		} else {

			srand( $seed );

			usort(
				$items,
				function() {
					return rand( - 1, 1 );
				}
			);
		}

		return new self( $items );
	}

	/**
	 * Slice the underlying collection array.
	 *
	 * @param int $offset
	 * @param int $length
	 *
	 * @return static
	 */
	public function slice( $offset, $length = null ) {

		return new self( array_slice( $this->items, $offset, $length, true ) );
	}

	///**
	// * Split a collection into a certain number of groups.
	// *
	// * @param int $numberOfGroups
	// *
	// * @return static
	// */
	//public function split( $numberOfGroups ) {
	//
	//	if ( $this->isEmpty() ) {
	//		return new static;
	//	}
	//
	//	$groupSize = ceil( $this->count() / $numberOfGroups );
	//
	//	return $this->chunk( $groupSize );
	//}

	/**
	 * Chunk the underlying collection array.
	 *
	 * @param int $size
	 *
	 * @return static
	 */
	public function chunk( $size ) {

		if ( $size <= 0 ) {
			return new self();
		}

		$chunks = array();

		foreach ( array_chunk( $this->items, $size, true ) as $chunk ) {
			$chunks[] = new self( $chunk );
		}

		return new self( $chunks );
	}

	/**
	 * Sort through each item with a callback.
	 *
	 * @param callable|null $callback
	 *
	 * @return static
	 */
	public function sort( callable $callback = null ) {

		$items = $this->items;

		$callback ? uasort( $items, $callback ) : asort( $items );

		return new self( $items );
	}

	/**
	 * Sort the collection using the given callback.
	 *
	 * @param callable|string $callback
	 * @param int             $options
	 * @param bool            $descending
	 *
	 * @return static
	 */
	public function sortBy( $callback, $options = SORT_REGULAR, $descending = false ) {

		$results = array();

		// $callback = $this->valueRetriever( $callback );

		// First we will loop through the items and get the comparator from a callback
		// function which we were given. Then, we will sort the returned values and
		// grab the corresponding values for the sorted keys from this array.
		foreach ( $this->items as $key => $value ) {

			// $results[ $key ] = $callback( $value, $key );

			$results[ $key ] = $this->useAsCallable( $callback ) ? $callback( $value, $key ) : _array::data_get( $value, $callback );
		}

		$descending ? arsort( $results, $options ) : asort( $results, $options );

		// Once we have sorted all the keys in the array, we will loop through them
		// and grab the corresponding model, so we can set the underlying items list
		// to the sorted version. Then we'll just return the collection instance.
		foreach ( array_keys( $results ) as $key ) {
			$results[ $key ] = $this->items[ $key ];
		}

		return new self( $results );
	}

	/**
	 * Sort the collection in descending order using the given callback.
	 *
	 * @param callable|string $callback
	 * @param int             $options
	 *
	 * @return static
	 */
	public function sortByDesc( $callback, $options = SORT_REGULAR ) {

		return $this->sortBy( $callback, $options, true );
	}

	/**
	 * Splice a portion of the underlying collection array.
	 *
	 * @param int      $offset
	 * @param int|null $length
	 * @param mixed    $replacement
	 *
	 * @return static
	 */
	public function splice( $offset, $length = null, $replacement = array() ) {

		if ( func_num_args() == 1 ) {
			return new self( array_splice( $this->items, $offset ) );
		}

		return new self( array_splice( $this->items, $offset, $length, $replacement ) );
	}

	///**
	// * Get the sum of the given values.
	// *
	// * @param callable|string|null $callback
	// *
	// * @return mixed
	// */
	//public function sum( $callback = NULL ) {
	//
	//	if ( is_null( $callback ) ) {
	//		return array_sum( $this->items );
	//	}
	//
	//	$callback = $this->valueRetriever( $callback );
	//
	//	return $this->reduce( function ( $result, $item ) use ( $callback ) {
	//
	//		return $result + $callback( $item );
	//	},
	//		0 );
	//}

	/**
	 * Take the first or last {$limit} items.
	 *
	 * @param int $limit
	 *
	 * @return static
	 */
	public function take( $limit ) {

		if ( $limit < 0 ) {
			return $this->slice( $limit, abs( $limit ) );
		}

		return $this->slice( 0, $limit );
	}

	/**
	 * Pass the collection to the given callback and then return it.
	 *
	 * @param callable $callback
	 *
	 * @return $this
	 */
	public function tap( $callback ) {

		$callback( new self( $this->items ) );

		return $this;
	}

	/**
	 * Transform each item in the collection using a callback.
	 *
	 * @param callable $callback
	 *
	 * @return $this
	 */
	public function transform( $callback ) {

		$this->items = $this->map( $callback )->all();

		return $this;
	}

	///**
	// * Return only unique items from the collection array.
	// *
	// * @param string|callable|null $key
	// * @param bool                 $strict
	// *
	// * @return static
	// */
	//public function unique( $key = NULL, $strict = FALSE ) {
	//
	//	if ( is_null( $key ) ) {
	//		return new static( array_unique( $this->items, SORT_REGULAR ) );
	//	}
	//
	//	$callback = $this->valueRetriever( $key );
	//
	//	$exists = [];
	//
	//	return $this->reject( function ( $item, $key ) use ( $callback, $strict, &$exists ) {
	//
	//		if ( in_array( $id = $callback( $item, $key ), $exists, $strict ) ) {
	//			return TRUE;
	//		}
	//
	//		$exists[] = $id;
	//	} );
	//}

	///**
	// * Return only unique items from the collection array using strict comparison.
	// *
	// * @param string|callable|null $key
	// *
	// * @return static
	// */
	//public function uniqueStrict( $key = NULL ) {
	//
	//	return $this->unique( $key, TRUE );
	//}

	/**
	 * Reset the keys on the underlying array.
	 *
	 * @return static
	 */
	public function values() {

		return new self( array_values( $this->items ) );
	}

	/**
	 * Get a value retrieving callback.
	 *
	 * @param Closure|string $value
	 *
	 * @return callable
	 */
	protected function valueRetriever( $value ) {

		if ( $this->useAsCallable( $value ) ) {
			return $value;
		}

		return function ( $item ) use ( $value ) {

			return _array::data_get( $item, $value );
		};
	}

	///**
	// * Zip the collection together with one or more arrays.
	// *
	// * e.g. new Collection([1, 2, 3])->zip([4, 5, 6]);
	// *      => [[1, 4], [2, 5], [3, 6]]
	// *
	// * @param mixed ...$items
	// *
	// * @return static
	// */
	//public function zip( $items ) {
	//
	//	$arrayableItems = array_map( function ( $items ) {
	//
	//		return $this->getArrayableItems( $items );
	//	},
	//		func_get_args() );
	//
	//	$params = array_merge( [
	//		                       function () {
	//
	//			                       return new static( func_get_args() );
	//		                       },
	//		                       $this->items,
	//	                       ],
	//	                       $arrayableItems );
	//
	//	return new static( call_user_func_array( 'array_map', $params ) );
	//}

	/**
	 * Get the collection of items as a plain array.
	 *
	 * @return array
	 */
	public function toArray() {

		return array_map(
			function( $value ) {

				return $value instanceof cnToArray ? $value->toArray() : $value;
			},
			$this->items
		);
	}

	///**
	// * Convert the object into something JSON serializable.
	// *
	// * @return array
	// */
	//public function jsonSerialize() {
	//
	//	return array_map( function ( $value ) {
	//
	//		if ( $value instanceof JsonSerializable ) {
	//			return $value->jsonSerialize();
	//		} elseif ( $value instanceof Jsonable ) {
	//			return json_decode( $value->toJson(), TRUE );
	//		} elseif ( $value instanceof Arrayable ) {
	//			return $value->toArray();
	//		} else {
	//			return $value;
	//		}
	//	},
	//		$this->items );
	//}

	///**
	// * Get the collection of items as JSON.
	// *
	// * @param int $options
	// *
	// * @return string
	// */
	//public function toJson( $options = 0 ) {
	//
	//	return json_encode( $this->jsonSerialize(), $options );
	//}

	/**
	 * Get an iterator for the items.
	 *
	 * @return ArrayIterator
	 */
	#[\ReturnTypeWillChange]
	public function getIterator() {

		return new ArrayIterator( $this->items );
	}

	/**
	 * Get a CachingIterator instance.
	 *
	 * @param int $flags
	 *
	 * @return CachingIterator
	 */
	public function getCachingIterator( $flags = CachingIterator::CALL_TOSTRING ) {

		return new CachingIterator( $this->getIterator(), $flags );
	}

	/**
	 * Count the number of items in the collection.
	 *
	 * @return int
	 */
	#[\ReturnTypeWillChange]
	public function count() {

		return count( $this->items );
	}

	/**
	 * Get a base Support collection instance from this collection.
	 *
	 * @return static
	 */
	public function toBase() {

		return new self( $this );
	}

	/**
	 * Determine if an item exists at an offset.
	 *
	 * @param mixed $offset An offset to check for.
	 *
	 * @return bool
	 */
	#[\ReturnTypeWillChange]
	public function offsetExists( $offset ) {

		return array_key_exists( $offset, $this->items );
	}

	/**
	 * Get an item at a given offset.
	 *
	 * @param mixed $offset The offset to retrieve.
	 *
	 * @return mixed
	 */
	#[\ReturnTypeWillChange]
	public function offsetGet( $offset ) {

		return $this->items[ $offset ];
	}

	/**
	 * Set the item at a given offset.
	 *
	 * @param mixed $offset The offset to assign the value to.
	 * @param mixed $value  The value to set.
	 */
	#[\ReturnTypeWillChange]
	public function offsetSet( $offset, $value ) {

		if ( is_null( $offset ) ) {
			$this->items[] = $value;
		} else {
			$this->items[ $offset ] = $value;
		}
	}

	/**
	 * Unset the item at a given offset.
	 *
	 * @param string $offset The offset to unset.
	 */
	#[\ReturnTypeWillChange]
	public function offsetUnset( $offset ) {

		unset( $this->items[ $offset ] );
	}

	///**
	// * Convert the collection to its string representation.
	// *
	// * @return string
	// */
	//public function __toString() {
	//
	//	return $this->toJson();
	//}

	/**
	 * Results array of items from Collection.
	 *
	 * @param mixed $items
	 *
	 * @return array
	 */
	protected function getArrayableItems( $items ) {

		if ( is_array( $items ) ) {
			return $items;
		} elseif ( $items instanceof self ) {
			return $items->all();
		} elseif ( $items instanceof cnToArray ) {
			return $items->toArray();
		//} elseif ( $items instanceof Jsonable ) {
		//	return json_decode( $items->toJson(), TRUE );
		//} elseif ( $items instanceof JsonSerializable ) {
		//	return $items->jsonSerialize();
		} elseif ( $items instanceof Traversable ) {
			return iterator_to_array( $items );
		}

		return (array) $items;
	}

	///**
	// * Add a method to the list of proxied methods.
	// *
	// * @param string $method
	// *
	// * @return void
	// */
	//public static function proxy( $method ) {
	//
	//	static::$proxies[] = $method;
	//}

	///**
	// * Dynamically access collection proxies.
	// *
	// * @param string $key
	// *
	// * @return mixed
	// *
	// * @throws \Exception
	// */
	//public function __get( $key ) {
	//
	//	if ( ! in_array( $key, static::$proxies ) ) {
	//		throw new Exception( "Property [{$key}] does not exist on this collection instance." );
	//	}
	//
	//	return new HigherOrderCollectionProxy( $this, $key );
	//}
}
