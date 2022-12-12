<?php

use Connections_Directory\Utility\_format;
use Connections_Directory\Utility\_validate;

/**
 * Class cnEntry_Collection_Item
 *
 * @since 8.10
 *
 * @property int    $id
 * @property int    $order
 * @property bool   $preferred
 * @property string $type
 * @property string $visibility
 * @property string $name
 *
 * @phpcs:disable PEAR.NamingConventions.ValidClassName.StartWithCapital
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound
 */
abstract class cnEntry_Collection_Item implements ArrayAccess, cnToArray {

	/**
	 * @since 8.10
	 * @var int
	 */
	protected $id;

	/**
	 * @since 8.10
	 * @var int
	 */
	protected $order;

	/**
	 * @since 8.10
	 * @var bool
	 */
	protected $preferred;

	/**
	 * @since 8.10
	 * @var string
	 */
	protected $type;

	/**
	 * @since 8.10
	 * @var string
	 */
	protected $visibility;

	/**
	 * @since 8.10
	 * @var string
	 */
	protected $name;

	/**
	 * Hash map of the old array keys / object properties to cnAddress properties.
	 *
	 * Used in self::__isset()
	 *
	 * @access protected
	 * @since  8.10
	 * @var    array
	 */
	protected $properties = array();

	/**
	 * Hash map of the old array keys / object properties to cnAddress method callbacks.
	 *
	 * Used in self::__get()
	 *
	 * @access protected
	 * @since  8.10
	 * @var    array
	 */
	protected $methods = array();

	/**
	 * Allow private properties to be checked with isset() and empty() for backward compatibility.
	 *
	 * @access public
	 * @since  8.10
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function __isset( $key ) {

		if ( isset( $this->properties[ $key ] ) ) {

			$name = $this->properties[ $key ];

			if ( property_exists( $this, $name ) && null !== $this->__get( $key ) ) {

				return true;
			}
		}

		return false;
	}

	/**
	 * Make private properties readable by calling their getters for backward compatibility.
	 *
	 * @access public
	 * @since  8.10
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function __get( $key ) {

		if ( isset( $this->methods[ $key ] ) ) {

			return $this->{ $this->methods[ $key ] }();
		}

		return null;
	}

	/**
	 * Make private properties settable for backward compatibility.
	 *
	 * @access public
	 * @since  8.10
	 *
	 * @param string $key
	 * @param mixed  $value
	 */
	public function __set( $key, $value ) {

		if ( isset( $this->properties[ $key ] ) ) {

			$name = $this->properties[ $key ];

			$this->$name = $value;
		}
	}

	/**
	 * @access public
	 * @since  8.10
	 *
	 * @param  string $key
	 *
	 * @return void
	 */
	public function __unset( $key ) {

		if ( isset( $this->properties[ $key ] ) ) {

			$name = $this->properties[ $key ];

			unset( $this->$name );
		}
	}

	/**
	 * @access public
	 * @since  8.10
	 *
	 * @return string
	 */
	public function __toString() {

		return json_encode( $this->toArray() );
	}

	/**
	 * Constructor
	 *
	 * @access public
	 * @since  8.16
	 *
	 * @param array $data
	 */
	abstract public function __construct( $data );

	/**
	 * Create and return an instance.
	 *
	 * @access public
	 * @since  8.16
	 *
	 * @param array $data
	 *
	 * @return static
	 */
	public static function create( $data ) {

		return new static( $data );
	}

	/**
	 * Return a new instance of cnAddress sanitized for saving to the database.
	 *
	 * @access public
	 * @since  8.10
	 *
	 * @return cnEntry_Collection_Item
	 */
	public function sanitizedForSave() {

		$self = clone $this;

		return $this->prepareContext( $self, 'db' );
	}

	/**
	 * Return a new instance of cnAddress escaped for display in HTML forms for editing.
	 *
	 * @access public
	 * @since  8.10
	 *
	 * @return cnEntry_Collection_Item
	 */
	public function escapedForEdit() {

		$self = clone $this;

		return $this->prepareContext( $self, 'edit' );
	}

	/**
	 * Return a new instance of cnAddress escaped for display.
	 *
	 * @access public
	 * @since  8.10
	 *
	 * @return cnEntry_Collection_Item
	 */
	public function escapedForDisplay() {

		$self = clone $this;

		return $this->prepareContext( $self, 'display' );
	}

	/**
	 * Escaped or sanitize cnAddress based on context.
	 *
	 * @access public
	 * @since  8.10
	 *
	 * @param cnEntry_Collection_Item $self
	 * @param string                  $context
	 *
	 * @return cnEntry_Collection_Item
	 */
	abstract protected function prepareContext( $self, $context );

	/**
	 * @access public
	 * @since  8.10
	 *
	 * @return int
	 */
	public function getID() {

		return $this->id;
	}

	/**
	 * @access public
	 * @since  8.10
	 *
	 * @param int $id
	 *
	 * @return cnEntry_Collection_Item
	 */
	public function setID( $id ) {

		if ( _validate::isPositiveInteger( $id ) ) {
			$this->id = absint( $id );
		}

		return $this;
	}

	/**
	 * @access public
	 * @since  8.10
	 *
	 * @return string
	 */
	public function getType() {

		return $this->type;
	}

	/**
	 * @access public
	 * @since  8.10
	 *
	 * @return string
	 */
	public function getName() {

		return $this->name;
	}

	/**
	 * @access public
	 * @since  8.10
	 *
	 * @param string $type
	 *
	 * @return cnEntry_Collection_Item
	 */
	public function setType( $type ) {

		$this->type = $type;

		return $this;
	}

	/**
	 * @access public
	 * @since  8.10
	 *
	 * @return string
	 */
	public function getVisibility() {

		return $this->visibility;
	}

	/**
	 * @access public
	 * @since  8.10
	 *
	 * @param string $visibility
	 *
	 * @return cnEntry_Collection_Item
	 */
	public function setVisibility( $visibility ) {

		$valid = array( 'public', 'private', 'unlisted' );

		$this->visibility = in_array( $visibility, $valid, true ) ? $visibility : 'public';

		return $this;
	}

	/**
	 * @access public
	 * @since  8.10
	 *
	 * @return int
	 */
	public function getOrder() {

		return $this->order;
	}

	/**
	 * @access public
	 * @since  8.10
	 *
	 * @param int $order
	 *
	 * @return cnEntry_Collection_Item
	 */
	public function setOrder( $order ) {

		if ( _validate::isPositiveInteger( $order ) ) {
			$this->order = absint( $order );
		}

		return $this;
	}

	/**
	 * @access public
	 * @since  8.10
	 *
	 * @return bool
	 */
	public function isPreferred() {

		return $this->getPreferred();
	}

	/**
	 * @access public
	 * @since  8.10
	 *
	 * @return bool
	 */
	public function getPreferred() {

		return $this->preferred;
	}

	/**
	 * @access public
	 * @since  8.10
	 *
	 * @param bool $preferred
	 *
	 * @return cnEntry_Collection_Item
	 */
	public function setPreferred( $preferred ) {

		$this->preferred = _format::toBoolean( $preferred );

		return $this;
	}

	/**
	 * Determine if an item exists at an offset.
	 *
	 * @since 8.10
	 *
	 * @param mixed $offset An offset to check for.
	 *
	 * @return bool
	 */
	#[\ReturnTypeWillChange]
	public function offsetExists( $offset ) {

		return $this->__isset( $offset );
	}

	/**
	 * Get an item at a given offset.
	 *
	 * @since 8.10
	 *
	 * @param mixed $offset The offset to retrieve.
	 *
	 * @return mixed
	 */
	#[\ReturnTypeWillChange]
	public function offsetGet( $offset ) {

		return $this->__get( $offset );
	}

	/**
	 * Set the item at a given offset.
	 *
	 * @since 8.10
	 *
	 * @param mixed $offset The offset to assign the value to.
	 * @param mixed $value  The value to set.
	 *
	 * @return void
	 */
	#[\ReturnTypeWillChange]
	public function offsetSet( $offset, $value ) {

		$this->__set( $offset, $value );
	}

	/**
	 * Unset the item at a given offset.
	 *
	 * @since 8.10
	 *
	 * @param string $offset The offset to unset.
	 */
	#[\ReturnTypeWillChange]
	public function offsetUnset( $offset ) {

		$this->__unset( $offset );
	}
}
