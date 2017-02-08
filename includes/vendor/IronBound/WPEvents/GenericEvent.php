<?php
/**
 * Contains the GenericEvent class.
 *
 * @author    Iron Bound Designs
 * @since     1.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\WPEvents;

/**
 * Event encapsulation class.
 *
 * Encapsulates events thus decoupling the observer from the subject they encapsulate.
 *
 * @author Drak <drak@zikula.org> Originally from the Symfony EventDispatcher component.
 */
class GenericEvent extends Event implements \ArrayAccess, \IteratorAggregate {

	/**
	 * Event subject.
	 *
	 * @var mixed usually object or callable
	 */
	protected $subject;

	/**
	 * Array of arguments.
	 *
	 * @var array
	 */
	protected $arguments;

	/**
	 * Encapsulate an event with $subject and $args.
	 *
	 * @param mixed $subject   The subject of the event, usually an object.
	 * @param array $arguments Arguments to store in the event.
	 */
	public function __construct( $subject = null, array $arguments = array() ) {
		$this->subject   = $subject;
		$this->arguments = $arguments;
	}

	/**
	 * Getter for subject property.
	 *
	 * @return mixed $subject The observer subject.
	 */
	public function get_subject() {
		return $this->subject;
	}

	/**
	 * Get argument by key.
	 *
	 * @param string $key Key.
	 *
	 * @throws \InvalidArgumentException If key is not found.
	 *
	 * @return mixed Contents of array key.
	 */
	public function get_argument( $key ) {
		if ( $this->has_argument( $key ) ) {
			return $this->arguments[ $key ];
		}

		throw new \InvalidArgumentException( sprintf( 'Argument "%s" not found.', $key ) );
	}

	/**
	 * Add argument to event.
	 *
	 * @param string $key   Argument name.
	 * @param mixed  $value Value.
	 *
	 * @return GenericEvent
	 */
	public function set_argument( $key, $value ) {
		$this->arguments[ $key ] = $value;

		return $this;
	}

	/**
	 * Getter for all arguments.
	 *
	 * @return array
	 */
	public function get_arguments() {
		return $this->arguments;
	}

	/**
	 * Set args property.
	 *
	 * @param array $args Arguments.
	 *
	 * @return GenericEvent
	 */
	public function set_arguments( array $args = array() ) {
		$this->arguments = $args;

		return $this;
	}

	/**
	 * Has argument.
	 *
	 * @param string $key Key of arguments array.
	 *
	 * @return bool
	 */
	public function has_argument( $key ) {
		return array_key_exists( $key, $this->arguments );
	}

	/**
	 * ArrayAccess for argument getter.
	 *
	 * @param string $key Array key.
	 *
	 * @throws \InvalidArgumentException If key does not exist in $this->args.
	 *
	 * @return mixed
	 */
	public function offsetGet( $key ) {
		return $this->get_argument( $key );
	}

	/**
	 * ArrayAccess for argument setter.
	 *
	 * @param string $key   Array key to set.
	 * @param mixed  $value Value.
	 */
	public function offsetSet( $key, $value ) {
		$this->set_argument( $key, $value );
	}

	/**
	 * ArrayAccess for unset argument.
	 *
	 * @param string $key Array key.
	 */
	public function offsetUnset( $key ) {
		if ( $this->has_argument( $key ) ) {
			unset( $this->arguments[ $key ] );
		}
	}

	/**
	 * ArrayAccess has argument.
	 *
	 * @param string $key Array key.
	 *
	 * @return bool
	 */
	public function offsetExists( $key ) {
		return $this->has_argument( $key );
	}

	/**
	 * IteratorAggregate for iterating over the object like an array.
	 *
	 * @return \ArrayIterator
	 */
	public function getIterator() {
		return new \ArrayIterator( $this->arguments );
	}
}