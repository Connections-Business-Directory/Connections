<?php

/**
 * Class cnEntry_Collection_Item
 *
 * @since 8.10
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
	 * Hash map of the the old array keys / object properties to cnAddress method callbacks.
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

			return ( property_exists( $this, $name ) && isset( $this->$name ) );
		}

		return FALSE;
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

		$this->id = (int) $id;

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

		$this->type = cnSanitize::field( 'attribute', $type, 'raw' );

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

		$this->visibility = cnSanitize::field( 'attribute', $visibility, 'raw' );

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

		$this->order = (int) $order;

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

		$this->preferred = (bool) $preferred;

		return $this;
	}

	/**
	 * Determine if an item exists at an offset.
	 *
	 * @access public
	 * @since  8.10
	 *
	 * @param  mixed $key
	 *
	 * @return bool
	 */
	public function offsetExists( $key ) {

		return $this->__isset( $key );
	}

	/**
	 * Get an item at a given offset.
	 *
	 * @access public
	 * @since  8.10
	 *
	 * @param  mixed $key
	 *
	 * @return mixed
	 */
	public function offsetGet( $key ) {

		return $this->__get( $key );
	}

	/**
	 * Set the item at a given offset.
	 *
	 * @access public
	 * @since  8.10
	 *
	 * @param  mixed $key
	 * @param  mixed $value
	 *
	 * @return void
	 */
	public function offsetSet( $key, $value ) {

		$this->__set( $key, $value );
	}

	/**
	 * Unset the item at a given offset.
	 *
	 * @access public
	 * @since  8.10
	 *
	 * @param  string $key
	 *
	 * @return void
	 */
	public function offsetUnset( $key ) {

		$this->__unset( $key );
	}
}
