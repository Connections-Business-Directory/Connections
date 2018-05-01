<?php
/**
 * Class cnEntry_Object_Collection
 *
 * @since 8.10
 */
abstract class cnEntry_Object_Collection implements cnToArray {

	/**
	 * The entry ID to which the collection belongs.
	 *
	 * @since 8.10
	 * @var int
	 */
	protected $id;

	/**
	 * @since 8.10
	 * @var cnCollection
	 */
	protected $items;

	/**
	 * @since 8.10
	 * @var cnCollection
	 */
	protected $filtered;

	/**
	 * @see cnEntry_Object_Collection constructor.
	 *
	 * @access public
	 * @since  8.10
	 *
	 * @param int               $id   The entry ID to create the object collection for.
	 * @param null|array|string $data The data used to create the collection with.
	 */
	public function __construct( $id = NULL, $data = NULL ) {

		$this->id       = $id;
		$this->items    = new cnCollection();
		$this->filtered = new cnCollection();

		if ( ! is_null( $data ) ) {

			if ( is_string( $data ) ) {

				$data = maybe_unserialize( $data );
			}

			if ( is_array( $data ) ) {

				$this->fromArray( $data );
			}
		}
	}

	/**
	 * Set the entry ID to which the collection belongs.
	 *
	 * @access public
	 * @since  8.10
	 *
	 * @param int $id
	 *
	 * @return cnEntry_Object_Collection
	 */
	public function setEntryID( $id ) {

		$this->id = $id;

		return $this;
	}

	/**
	 * Get an object from the collection by its ID.
	 *
	 * @access public
	 * @since  8.10
	 *
	 * @param int $id The object ID to get from the collection.
	 *
	 * @return bool|cnEntry_Collection_Item
	 */
	abstract public function get( $id );

	/**
	 * Add a @see cnEntry_Collection_Item to the collection.
	 *
	 * @access public
	 * @since  8.10
	 *
	 * @param cnEntry_Collection_Item $object
	 *
	 * @return void
	 */
	abstract public function add( cnEntry_Collection_Item $object );

	/**
	 * Remove an object from the collection by the object ID.
	 *
	 * @access public
	 * @since  8.10
	 *
	 * @param int $id The object ID in the collection to delete.
	 */
	public function remove( $id ) {

		//// Reset the filters just in case filters have been applied to the collection.
		//$this->resetFilters();

		if ( FALSE !== $key = $this->getItemKeyByID( $id ) ) {

			$this->items->forget( $key );
		}

		//// Reset the filters so both the filtered and unfiltered collections are the same after removing an object.
		//$this->resetFilters();
	}

	/**
	 * Get the collection key for the supplied object ID.
	 *
	 * @access public
	 * @since  8.10
	 *
	 * @param int $id The object ID to search the collection for.
	 *
	 * @return bool|int
	 */
	protected function getItemKeyByID( $id ) {

		// Using create_function instead of anonymous function or closure for PHP 5.2 compatibility.
		$callback = create_function(
			'$item',
			'return absint(\'' . $id . '\') === $item->getID();'
		);

		return $this->items->search( $callback );
	}

	/**
	 * Update object by object ID.
	 *
	 * NOTE: This does not update only changed fields within the object, it simply replaces the object with an
	 *       instance of the object which contains the old and changed object information.
	 *
	 * @access public
	 * @since  8.10
	 *
	 * @param int                     $id     The object ID to update.
	 * @param cnEntry_Collection_Item $object The updated object used to replace the old object in the collection.
	 *
	 * @return bool
	 */
	abstract public function update( $id, $object );

	/**
	 * NOTE: This method primarily exists to support backwards compatibility.
	 *
	 * @access public
	 * @since  8.10
	 *
	 * @param array $data The data used to update the collection with.
	 *
	 * @return void
	 */
	abstract public function updateFromArray( $data );

	/**
	 * Check to see if an object exists within the collection by object ID.
	 *
	 * @access public
	 * @since  8.10
	 *
	 * @param int $id The object ID to search the collection for.
	 *
	 * @return bool
	 */
	public function exists( $id ) {

		return 0 < $this->items->whereStrict( 'id', $id )->count();
	}

	/**
	 * Save the collection to the database.
	 *
	 * @access public
	 * @since  8.10
	 *
	 * @return void
	 */
	abstract public function save();

	/**
	 * Deletes the items in the collection which belongs to an entry from the database by the entry ID.
	 *
	 * @todo: Implement method.
	 *
	 * @access public
	 * @since  8.10
	 *
	 * @return void
	 */
	abstract public function delete();

	/**
	 * Render the object collection.
	 *
	 * @access public
	 * @since  8.10
	 *
	 * @param string $template     The template part name to load.
	 * @param array  $atts         An array of arguments that will be extract() if the template part is to be loaded.
	 * @param bool   $load         Whether or not to load the template.
	 * @param bool   $buffer       Whether or not to buffer the template output.
	 * @param bool   $require_once Whether or not to require() or require_once() the template part.
	 *
	 * @return string|null|bool The template path if not $load is FALSE.
	 *                          Output buffer if $buffer is TRUE or template path if $load is TRUE and $buffer is FALSE.
	 *                          NULL will be returned when the filtered collection is empty.
	 */
	abstract public function render( $template = 'hcard', $atts = array(), $load = TRUE, $buffer = FALSE, $require_once = FALSE );

	/**
	 * Return the objects as an instance of @see cnCollection.
	 *
	 * @access public
	 * @since  8.10
	 *
	 * @param int|null $limit The number of items that should be returned from the collection.
	 *
	 * @return cnCollection
	 */
	abstract public function getCollection( $limit = NULL );

	/**
	 * Return the object collection as an indexed array where the object is an associative array.
	 *
	 * NOTE: This method primarily exists to support backwards compatibility.
	 *
	 * @access public
	 * @since  8.10
	 *
	 * @param int|null $limit The number of items that should be returned from the collection.
	 *
	 * @return array
	 */
	abstract public function getCollectionAsArray( $limit = NULL );

	/**
	 * Return object collection as an array of stdClass objects.
	 *
	 * NOTE: This method primarily exists to support backwards compatibility.
	 *
	 * @access public
	 * @since  8.10
	 *
	 * @param int|null $limit The number of items that should be returned from the collection.
	 *
	 * @return array
	 */
	public function getCollectionAsObjects( $limit = NULL ) {

		// Using create_function instead of anonymous function or closure for PHP 5.2 compatibility.
		$callback = create_function( '$item', 'return (object) $item;' );

		return array_map( $callback, $this->getCollectionAsArray( $limit ) );
	}

	/**
	 * NOTE: The results of these filters are reset when @see cnEntry_Object_Collection::resetFilters() is run.
	 *
	 * @access public
	 * @since  8.10
	 *
	 * @param string $filter The filter to be applied to the collection.
	 *
	 * @return cnEntry_Object_Collection
	 */
	abstract public function applyFilter( $filter );

	/**
	 * Get the object flagged as the "Preferred" object.
	 *
	 * @access public
	 * @since  8.10
	 *
	 * @return cnEntry_Collection_Item
	 */
	abstract public function getPreferred();

	/**
	 * Set an object as the "Preferred" object by object ID.
	 *
	 * @access public
	 * @since  8.10
	 *
	 * @param int $id The object ID to be set as the preferred object.
	 *
	 * @return cnEntry_Object_Collection
	 */
	public function setPreferred( $id ) {

		//// Reset the filters just in case filters have been applied to the collection.
		//$this->resetFilters();

		// Using create_function instead of anonymous function or closure for PHP 5.2 compatibility.
		$callback = create_function(
			'$item',
			'return \'' . $id . '\' == $item->getID() ? $item->wherePreferred( TRUE ) : $item->wherePreferred( FALSE );'
		);

		$this->items->transform( $callback );

		//// Reset the filters so both the filtered and unfiltered collections are the same after updating an object.
		//$this->resetFilters();

		return $this;
	}

	/**
	 * NOTE: The escaping is reset when @see cnEntry_Object_Collection::resetFilters() is run.
	 *
	 * @access public
	 * @since  8.10
	 *
	 * @param string $context The context in which to escape the collection.
	 *
	 * @return cnEntry_Object_Collection
	 */
	public function escapeFor( $context = 'raw' ) {

		switch ( $context ) {

			case 'display':

				$this->escapeForDisplay();
				break;

			case 'edit':

				$this->escapeForEdit();
				break;

			case 'db':

				$this->escapeForSaving();
				break;
		}

		return $this;
	}

	/**
	 * NOTE: The escaping is reset when @see cnEntry_Object_Collection::resetFilters() is run.
	 *
	 * @access public
	 * @since  8.10
	 *
	 * @return cnEntry_Object_Collection
	 */
	public function escapeForDisplay() {

		// Using create_function instead of anonymous function or closure for PHP 5.2 compatibility.
		$callback = create_function( '$item', 'return $item->escapedForDisplay();' );

		if ( 0 < $this->filtered->count() ) $this->filtered->transform( $callback );

		return $this;
	}

	/**
	 * NOTE: The escaping is reset when @see cnEntry_Object_Collection::resetFilters() is run.
	 *
	 * @access public
	 * @since  8.10
	 *
	 * @return cnEntry_Object_Collection
	 */
	public function escapeForEdit() {

		// Using create_function instead of anonymous function or closure for PHP 5.2 compatibility.
		$callback = create_function( '$item', 'return $item->escapedForEdit();' );

		if ( 0 < $this->filtered->count() ) $this->filtered->transform( $callback );

		return $this;
	}

	/**
	 * NOTE: The escaping is reset when @see cnEntry_Object_Collection::resetFilters() is run.
	 *
	 * @access public
	 * @since  8.10
	 *
	 * @return cnEntry_Object_Collection
	 */
	public function escapeForSaving() {

		// Using create_function instead of anonymous function or closure for PHP 5.2 compatibility.
		$callback = create_function( '$item', 'return $item->sanitizedForSave();' );

		if ( 0 < $this->filtered->count() ) $this->filtered->transform( $callback );

		return $this;
	}

	/**
	 * NOTE: The results of these filters are reset when @see cnEntry_Object_Collection::resetFilters() is run.
	 *
	 * @access public
	 * @since  8.10
	 *
	 * @param string            $field The field which the collection should be filtered by.
	 * @param array|bool|string $value The values used to filter the collection by.
	 *
	 * @return static
	 */
	abstract public function filterBy( $field, $value );

	/**
	 * NOTE: The results of this filter is reset when @see cnEntry_Object_Collection::resetFilters() is run.
	 *
	 * @access public
	 * @since  8.10
	 *
	 * @param int $number The number of objects to limit the collection to.
	 *
	 * @return cnEntry_Object_Collection
	 */
	public function take( $number ) {

		if ( is_int( $number ) ) {

			$this->filtered = $this->filtered->take( $number );
		}

		return $this;
	}

	/**
	 * The filtered collection back to its original state with exception of the added or removed objects
	 * in the collection. They will remain added or removed. The set preferred object will remain set.
	 *
	 * @access public
	 * @since  8.10
	 *
	 * @return cnEntry_Object_Collection
	 */
	public function resetFilters() {

		$this->filtered = $this->items;

		return $this;
	}

	/**
	 * Get objects associated to an entry from object table in the database.
	 *
	 * @access public
	 * @since  8.10
	 *
	 * @param array $options The options used to perform the database query.
	 *
	 * @return cnEntry_Object_Collection
	 */
	abstract public function query( $options = array() );

	/**
	 * Populate @see cnEntry_Object_Collection with data from an array of object data.
	 *
	 * @access public
	 * @since  8.10
	 *
	 * @param array $data The data used to create the collection with.
	 *
	 * @return cnEntry_Object_Collection
	 */
	abstract public function fromArray( $data = array() );

	/**
	 * Maybe unserialize or JSON decode the supplied value.
	 *
	 * @access public
	 * @since  8.19
	 *
	 * @param mixed $data
	 *
	 * @return mixed
	 */
	public function maybeUnserialize( $data ) {

		if ( is_string( $data ) ) {

			$data = maybe_unserialize( $data );
			$data = cnFormatting::maybeJSONdecode( $data );
		}

		return $data;
	}

	/**
	 * Populate @see cnEntry_Object_Collection with data from a serialize array or JSON encoded array of object data.
	 *
	 * @access public
	 * @since  8.19
	 *
	 * @param array|string $data
	 */
	public function fromMaybeSerialized( $data ) {

		$data = $this->maybeUnserialize( $data );

		if ( is_array( $data ) ) {

			$this->fromArray( $data );
		}
	}

	/**
	 * Return the collection data as an array.
	 *
	 * @access public
	 * @since  8.10
	 *
	 * @return array
	 */
	public function toArray() {

		return $this->filtered->values()->toArray();
	}

	/**
	 * Returns the collection data as a serialized array.
	 *
	 * @access public
	 * @since  8.10
	 *
	 * @return string
	 */
	public function serialize() {

		return serialize( $this->toArray() );
	}

	/**
	 * Returns the collection data as a JSON encoded array.
	 *
	 * @access public
	 * @since  8.10
	 *
	 * @return string
	 */
	public function __toString() {

		return json_encode( $this->toArray() );
	}
}
