<?php

/**
 * Class cnEntry_Addresses
 *
 * @since 8.6
 */
final class cnEntry_Addresses implements cnToArray {

	/**
	 * The entry ID to which the collection belongs.
	 *
	 * @since 8.6
	 * @var int
	 */
	private $id;

	/**
	 * @since 8.6
	 * @var cnCollection
	 */
	private $items;

	/**
	 * @since 8.6
	 * @var cnCollection
	 */
	private $filtered;

	/**
	 * @see cnEntry_Addresses constructor.
	 *
	 * @access public
	 * @since  8.6
	 *
	 * @param int               $id   The entry ID to create the address collection for.
	 * @param null|array|string $data The data used to create the collection with.
	 */
	public function __construct( $id = NULL, $data = NULL ) {

		$this->id       = $id;
		$this->items    = new cnCollection();
		$this->filtered = new cnCollection();

		if ( ! is_null( $data ) ) {

			if ( is_serialized( $data ) ) {

				$this->fromArray( maybe_unserialize( $data ) );

			} elseif( is_array( $data ) ) {

				$this->fromArray( $data );
			}

		}
	}

	/**
	 * Set the entry ID to which the collection belongs.
	 *
	 * @access public
	 * @since  8.6
	 *
	 * @param int $id
	 *
	 * @return cnEntry_Addresses
	 */
	public function setEntryID( $id ) {

		$this->id = $id;

		return $this;
	}

	/**
	 * Get and address from the collection by its ID.
	 *
	 * @access public
	 * @since  8.6
	 *
	 * @param int $id The address ID to get from the collection.
	 *
	 * @return bool|cnAddress
	 */
	public function get( $id ) {

		if ( FALSE !== $key = $this->getItemKeyByID( $id ) ) {

			return apply_filters( 'cn_address', $this->items->get( $key ) );
		}

		return FALSE;
	}

	/**
	 * Add a @see cnAddress to the collection.
	 *
	 * @access public
	 * @since  8.6
	 *
	 * @param cnAddress $address
	 */
	public function add( cnAddress $address ) {

		//// Reset the filters just in case filters have been applied to the collection.
		//$this->resetFilters();

		$this->items->push( apply_filters( 'cn_set_address', $address ) );

		//// Reset the filters so both the filtered and unfiltered collections are the same after adding an address.
		//$this->resetFilters();
	}

	/**
	 * Remove a @see cnAddress from the collection by address ID.
	 *
	 * @access public
	 * @since  8.6
	 *
	 * @param int $id The address ID in the collection to delete.
	 */
	public function remove( $id ) {

		//// Reset the filters just in case filters have been applied to the collection.
		//$this->resetFilters();

		if ( FALSE !== $key = $this->getItemKeyByID( $id ) ) {

			$this->items->forget( $key );
		}

		//// Reset the filters so both the filtered and unfiltered collections are the same after removing an address.
		//$this->resetFilters();
	}

	/**
	 * Get the collection key for the supplied address ID.
	 *
	 * @access public
	 * @since  8.6
	 *
	 * @param int $id The address ID to search the collection for.
	 *
	 * @return bool|int
	 */
	private function getItemKeyByID( $id ) {

		// Using create_function instead of anonymous function or closure for PHP 5.2 compatibility.
		$callback = create_function(
			'$item',
			'return absint(\'' . $id . '\') === $item->getID();'
		);

		return $this->items->search( $callback );
	}

	/**
	 * Update address by address ID.
	 *
	 * NOTE: This does not update only changed fields within the address object, it simply replaces the object with an
	 *       instance of the address object which contains the old and changed address information.
	 *
	 * @access public
	 * @since  8.6
	 *
	 * @param int       $id      The address ID to update.
	 * @param cnAddress $address The updated address object used to replace the old address object in the collection.
	 *
	 * @return bool
	 */
	public function update( $id, $address ) {

		//// Reset the filters just in case filters have been applied to the collection.
		//$this->resetFilters();

		// Using create_function instead of anonymous function or closure for PHP 5.2 compatibility.
		$callback = create_function(
			'$item',
			'return absint(\'' . $id . '\') === $item->getID();'
		);

		$key = $this->items->search( $callback );

		if ( FALSE !== $key ) {

			$this->items->put( $key, apply_filters( 'cn_set_address', $address ) );

			//// Reset the filters so both the filtered and unfiltered collections are the same after updating an address.
			//$this->resetFilters();

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * NOTE: This method primarily exists to support backwards compatibility for @see cnEntry::setAddresses().
	 *
	 * @access public
	 * @since  8.6
	 *
	 * @param array $data The data used to update the collection with.
	 */
	public function updateFromArray( $data ) {

		$new               = cnEntry_Addresses::createFromArray( $this->id, $data )->getCollection();
		$preferred         = isset( $data['preferred'] ) ? $data['preferred'] : NULL;
		$existingPreferred = $this->getPreferred();

		/** @var cnAddress $address */
		foreach ( $new as $address ) {

			/*
			 * @todo: Before an address is added/updated a check needs to be done to see if the `preferred` flag has been
			 *        set. If it has been, the existing address needs to be checked for an existing preferred address
			 *        and whether that user has permission to edit that address first before changing the preferred
			 *        address.
			 */

			// If exists, replace existing cnAddress object with the new one.
			if ( 0 !== $address->getID() && $this->exists( $address->getID() ) ) {

				$this->update( $address->getID(), $address );

			// If an address has an ID of `0`, that means it has not yet been saved to the database, add it to the collection.
			} elseif ( 0 === $address->getID() ) {

				$this->add( $address );
			}
		}

		$notEditableID = $this->filterBy( 'visibility', Connections_Directory()->currentUser->canNotView() )
		                   ->getCollection()
		                   ->pluck( 'id' )
		                   ->toArray();
		$existingID = $this->items->pluck( 'id' )->toArray();
		$updatedID  = $new->pluck( 'id' )->toArray();
		$deleted    = array_diff( $existingID, $updatedID, $notEditableID );

		// Remove addresses from collection which do not exist in the update array, because they were removed.
		if ( 0 < count( $deleted ) ) {

			foreach ( $deleted as $id ) {

				// Only remove an address if the ID exists in the collection.
				if ( 0 !== $id && $this->exists( $id ) ) {

					$this->remove( $id );
				}
			}
		}

		/*
		 * If a preferred address was set, ensure the address set as preferred does not override an address the user
		 * may not have had permission to edit.
		 */
		if ( ! is_null( $preferred ) && $existingPreferred ) {

			if ( ! in_array( $existingPreferred->getVisibility(), Connections_Directory()->currentUser->canView() ) ) {

				$this->setPreferred( $existingPreferred->getID() );

				// Throw the user a message so they know why their choice was overridden.
				cnMessage::set( 'error', 'entry_preferred_overridden_address' );
			}
		}

		$this->applyFilter( 'cn_set_addresses' );

		// Sync the filtered and unfiltered collections.
		$this->resetFilters();
	}

	/**
	 * Check to see if an address exists within the collection by address ID.
	 *
	 * @access public
	 * @since  8.6
	 *
	 * @param int $id The address ID to search the collection for.
	 *
	 * @return bool
	 */
	public function exists( $id ) {

		return 0 < $this->items->whereStrict( 'id', $id )->count();
	}

	/**
	 * Save the collection to the database.
	 *
	 * @todo Instance of the address table should be passed as a parameter.
	 *
	 * @access public
	 * @since  8.6
	 */
	public function save() {

		/*
		 * @todo Instead of using the table constant, should use the get table name from the address table instance.
		 * @todo The default columns array should be returned from the address table instance.
		 */

		/*
		 * NOTE: The format of the lat/lng values must be set as a string.
		 *
		 * WordPress sanitizes the float number, making it safe to write to the database,
		 * the default precision for a float of 14 is used which basically “caps” the decimal place to 6 digits.
		 * It is actually a bit more complicated than that and I would have to delve deeper myself
		 * to better understand myself. But the jist is floating point numbers are approximate representations
		 * of real numbers and they are not exact.
		 *
		 * There’s actually no way to change that precision when telling WordPress to sanitize a float.
		 * So the only solution is to tell WordPress it is a string and let the database deal with the conversion.
		 * Since the table that stores the the lat/lng are setup as decimal (a real number :) ) with a
		 * precision of 15 and a scale of 12, the lat/lng will not get rounded until the 12th decimal place.
		 */

		$cnDb = new cnEntry_DB( $this->id );

		$cnDb->upsert(
			CN_ENTRY_ADDRESS_TABLE,
			array(
				'order'      => array( 'key' => 'order', 'format' => '%d' ),
				'preferred'  => array( 'key' => 'preferred', 'format' => '%d' ),
				'type'       => array( 'key' => 'type', 'format' => '%s' ),
				'line_1'     => array( 'key' => 'line_1', 'format' => '%s' ),
				'line_2'     => array( 'key' => 'line_2', 'format' => '%s' ),
				'line_3'     => array( 'key' => 'line_3', 'format' => '%s' ),
				'line_4'     => array( 'key' => 'line_4', 'format' => '%s' ),
				'district'   => array( 'key' => 'district', 'format' => '%s' ),
				'county'     => array( 'key' => 'county', 'format' => '%s' ),
				'city'       => array( 'key' => 'city', 'format' => '%s' ),
				'state'      => array( 'key' => 'state', 'format' => '%s' ),
				'zipcode'    => array( 'key' => 'zipcode', 'format' => '%s' ),
				'country'    => array( 'key' => 'country', 'format' => '%s' ),
				'latitude'   => array( 'key' => 'latitude', 'format' => '%s' ),
				'longitude'  => array( 'key' => 'longitude', 'format' => '%s' ),
				'visibility' => array( 'key' => 'visibility', 'format' => '%s' ),
			),
			$this->resetFilters()->getCollectionAsObjects(),
			array(
				'id' => array( 'key' => 'id', 'format' => '%d' ),
			)
		);
	}

	/**
	 * Deletes the items in the collection which belongs to an entry from the database by the entry ID.
	 *
	 * @todo: Implement method.
	 *
	 * @access public
	 * @since  8.6
	 */
	public function delete() {
	}

	/**
	 * Render the address collection.
	 *
	 * @access public
	 * @since  8.6
	 *
	 * @param string $template     The template part name to load.
	 * @param array  $atts         An array of arguments that will be extract() if the template part is to be loaded.
	 * @param bool   $load         Whether or not to load the template.
	 * @param bool   $buffer       Whether or not to buffer the template output.
	 * @param bool   $require_once Whether or not to require() or require_once() the template part.
	 *
	 * @return string|null The template path if not $load is FALSE.
	 *                     Output buffer if $buffer is TRUE or template path if $load is TRUE and $buffer is FALSE.
	 *                     NULL will be returned when the filtered collection is empty.
	 */
	public function render( $template = 'hcard', $atts = array(), $load = TRUE, $buffer = FALSE, $require_once = FALSE ) {

		if ( $this->filtered->isEmpty() ) return NULL;

		$html = cnTemplatePart::get(
			'entry' . DIRECTORY_SEPARATOR . 'addresses' . DIRECTORY_SEPARATOR . 'address',
			$template,
			array_merge( array( 'addresses' => $this->getCollection() ), $atts ),
			$load,
			$buffer,
			$require_once
		);

		return $html;
	}

	/**
	 * Return the addresses as an instance of @see cnCollection.
	 *
	 * @access public
	 * @since  8.6
	 *
	 * @param int|null $limit The number of items that should be returned from the collection.
	 *
	 * @return cnCollection
	 */
	public function getCollection( $limit = NULL ) {

		$this->applyFilter( 'cn_address' )
			 ->applyFilter( 'cn_addresses' )
			 ->take( $limit );

		return $this->filtered->values();
	}

	/**
	 * Return the address collection as an indexed array where the address is an associative array.
	 *
	 * NOTE: This method primarily exists to support backwards compatibility in the
	 *       return value of @see cnEntry::getAddresses().
	 *
	 * @access public
	 * @since  8.6
	 *
	 * @param int|null $limit The number of items that should be returned from the collection.
	 *
	 * @return array
	 */
	public function getCollectionAsArray( $limit = NULL ) {

		$this->applyFilter( 'cn_address' )
			 ->applyFilter( 'cn_addresses' )
			 ->take( $limit );

		return $this->filtered->values()->toArray();
	}

	/**
	 * Return address collection as an array of stdClass objects.
	 *
	 * NOTE: This method primarily exists to support backwards compatibility in the
	 *       return value of @see cnEntry::getAddresses().
	 *
	 * @access public
	 * @since  8.6
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
	 * NOTE: The results of these filters are reset when @see cnEntry_Addresses::resetFilters() is run.
	 *
	 * @access public
	 * @since  8.6
	 *
	 * @param string $filter The filter to be applied to the collection.
	 *
	 * @return cnEntry_Addresses
	 */
	public function applyFilter( $filter ) {

		switch ( $filter ) {

			case 'cn_address':

				/**
				 * An address object.
				 *
				 * @since unknown
				 * @todo update filter parameters.
				 *
				 * @param cnAddress $address {
				 *     @type int    $id         The address ID if it was retrieved from the db.
				 *     @type bool   $preferred  Whether the address is the preferred address or not.
				 *     @type string $type       The address type.
				 *     @type string $line_1     Address line 1.
				 *     @type string $line_2     Address line 2.
				 *     @type string $line_3     Address line 3.
				 *     @type string $line_4     Address line 4.
				 *     @type string $district   The address district.
				 *     @type string $county     The address county.
				 *     @type string $city       The address locality.
				 *     @type string $state      The address region.
				 *     @type string $country    The address country.
				 *     @type float  $latitude   The address latitude.
				 *     @type float  $longitude  The address longitude.
				 *     @type string $visibility The address visibility.
				 * }
				 */
				$callback = create_function( '$item', 'return apply_filters( \'cn_address\', $item );' );
				break;

			case 'cn_addresses':

				/**
				 * An index array of address objects.
				 *
				 * @since unknown
				 *
				 * @param array $results See the documentation for the `cn_address` filter for the params of each
				 *                       item in the addresses array.
				 */
				$this->filtered = apply_filters( 'cn_addresses', $this->filtered );
				break;

			case 'cn_set_address':

				$callback = create_function( '$item', 'return apply_filters( \'cn_set_address\', $item );' );
				break;

			case 'cn_set_addresses':

				$this->filtered = apply_filters( 'cn_set_addresses', $this->filtered );
				break;
		}

		if ( isset( $callback ) && 0 < $this->filtered->count() ) {

			$this->filtered->transform( $callback );
			//$this->items->transform( $callback );
		}

		return $this;
	}

	/**
	 * Get the address flagged as the "Preferred" address.
	 *
	 * @access public
	 * @since  8.6
	 *
	 * @return cnAddress
	 */
	public function getPreferred() {

		return apply_filters( 'cn_address', $this->filtered->where( 'preferred', '===', TRUE )->first() );
	}

	/**
	 * Set an address as the "Preferred" address by address ID.
	 *
	 * @access public
	 * @since  8.6
	 *
	 * @param int $id The address ID to be set as the preferred address.
	 *
	 * @return cnEntry_Addresses
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

		//// Reset the filters so both the filtered and unfiltered collections are the same after updating an address.
		//$this->resetFilters();

		return $this;
	}

	/**
	 * NOTE: The escaping is reset when @see cnEntry_Addresses::resetFilters() is run.
	 *
	 * @access public
	 * @since  8.6
	 *
	 * @param string $context The context in which to escape the collection.
	 *
	 * @return cnEntry_Addresses
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
	 * NOTE: The escaping is reset when @see cnEntry_Addresses::resetFilters() is run.
	 *
	 * @access public
	 * @since  8.6
	 *
	 * @return cnEntry_Addresses
	 */
	public function escapeForDisplay() {

		// Using create_function instead of anonymous function or closure for PHP 5.2 compatibility.
		$callback = create_function( '$item', 'return $item->escapedForDisplay();' );

		if ( 0 < $this->filtered->count() ) $this->filtered->transform( $callback );

		return $this;
	}

	/**
	 * NOTE: The escaping is reset when @see cnEntry_Addresses::resetFilters() is run.
	 *
	 * @access public
	 * @since  8.6
	 *
	 * @return cnEntry_Addresses
	 */
	public function escapeForEdit() {

		// Using create_function instead of anonymous function or closure for PHP 5.2 compatibility.
		$callback = create_function( '$item', 'return $item->escapedForEdit();' );

		if ( 0 < $this->filtered->count() ) $this->filtered->transform( $callback );

		return $this;
	}

	/**
	 * NOTE: The escaping is reset when @see cnEntry_Addresses::resetFilters() is run.
	 *
	 * @access public
	 * @since  8.6
	 *
	 * @return cnEntry_Addresses
	 */
	public function escapeForSaving() {

		// Using create_function instead of anonymous function or closure for PHP 5.2 compatibility.
		$callback = create_function( '$item', 'return $item->sanitizedForSave();' );

		if ( 0 < $this->filtered->count() ) $this->filtered->transform( $callback );

		return $this;
	}

	/**
	 * NOTE: The results of these filters are reset when @see cnEntry_Addresses::resetFilters() is run.
	 *
	 * @access public
	 * @since  8.6
	 *
	 * @param string            $field The field which the collection should be filtered by.
	 * @param array|bool|string $value The values used to filter the collection by.
	 *
	 * @return cnEntry_Addresses
	 */
	public function filterBy( $field, $value ) {

		if ( in_array( $field, array( 'type', 'district', 'county', 'city', 'state', 'country' ) ) ) {

			if ( ! empty( $value ) ) {

				cnFunction::parseStringList( $value );

				$this->filtered = $this->filtered->whereIn( $field, $value );
			}

		} elseif ( 'preferred' === $field ) {

			cnFormatting::toBoolean( $value );

			// Only apply the preferred filter if the filter is TRUE so all addresses will be returned if FALSE.
			if ( $value ) $this->filtered = $this->filtered->where( 'preferred', '===', $value );

		} elseif ( 'visibility' === $field ) {

			$this->filtered = $this->filtered->whereIn( 'visibility', $value );
		}

		return $this;
	}

	/**
	 * NOTE: The results of this filter is reset when @see cnEntry_Addresses::resetFilters() is run.
	 *
	 * @access public
	 * @since  8.6
	 *
	 * @param int $number The number of addresses to limit the collection to.
	 *
	 * @return cnEntry_Addresses
	 */
	public function take( $number ) {

		if ( ! is_null( $number ) && is_int( $number ) ) {

			$this->filtered = $this->filtered->take( $number );
		}

		return $this;
	}

	/**
	 * The filtered collection back to its original state with exception of the added or removed addresses
	 * in the collection. They will remain added or removed. The set preferred address will remain set.
	 *
	 * @access public
	 * @since  8.6
	 *
	 * @return cnEntry_Addresses
	 */
	public function resetFilters() {

		$this->filtered = $this->items;

		return $this;
	}

	/**
	 * Get addresses by entry ID from address table in the database.
	 *
	 * Returns all addresses associated to an entry as an instance of @see cnEntry_Addresses.
	 *
	 * @access public
	 * @since  8.6
	 * @static
	 *
	 * @param int   $id      The entry ID to create the address collection for.
	 * @param array $options The options used to perform the database query.
	 *
	 * @return cnEntry_Addresses
	 */
	public static function createFromQuery( $id, $options = array() ) {

		$addresses = new cnEntry_Addresses();

		$addresses->setEntryID( $id )->query( $options );

		return $addresses;
	}

	/**
	 * Get addresses associated to an entry from address table in the database.
	 *
	 * @access public
	 * @since  8.6
	 *
	 * @param array $options The options used to perform the database query.
	 *
	 * @return cnEntry_Addresses
	 */
	public function query( $options = array() ) {

		// Grab an instance of the Connections object.
		$instance  = Connections_Directory();

		// Empty the Collection since fresh data is populating the Collection from the db.
		$this->items    = new cnCollection();
		$this->filtered = new cnCollection();

		if ( ! empty( $this->id ) ) {

			$options['id'] = $this->id;

			/*
			 * Set saving as true to force the query of all entries filtered per supplied attributes.
			 * This will reflect who it function when the table manager and query classes are implemented.
			 */
			$data = $instance->retrieve->addresses( $options, TRUE );
		}

		if ( empty( $data ) ) return $this;

		$this->fromArray( $data );

		return $this;
	}

	/**
	 * Create an instance of @see cnEntry_Addresses from an array of address data.
	 *
	 * @access public
	 * @since  8.6
	 * @static
	 *
	 * @param int   $id   The entry ID to create the address collection for.
	 * @param array $data The data used to create the collection with.
	 *
	 * @return cnEntry_Addresses
	 */
	public static function createFromArray( $id, $data = array() ) {

		$addresses = new cnEntry_Addresses();

		$addresses->setEntryID( $id )->fromArray( $data );

		return $addresses;
	}

	/**
	 * Populate @see cnEntry_Addresses with data from an array of address data.
	 *
	 * @access public
	 * @since  8.6
	 *
	 * @param array $data The data used to create the collection with.
	 *
	 * @return $this
	 */
	public function fromArray( $data = array() ) {

		$collection = new cnCollection( $data );
		$order      = $collection->max('order');

		/*
		 * The source of $data in Connections core will be from a form submission, object cache or the db.
		 * When the source is from the form submission the preferred item `key` is stored in the 'preferred' array key
		 * in the array of addresses. If the array key is set, save the array key value otherwise set it to NULL.
		 *
		 * If the `preferred` array key is set, the preferred address will be set based on the array key value.
		 * If it is not, the preferred address value will be retained using the `preferred` key within each address.
		 */
		$preferred  = isset( $data['preferred'] ) ? $data['preferred'] : NULL;

		foreach ( $collection as $key => $address ) {

			if ( empty( $address ) ) continue;

			/*
			 * Previous versions stored empty arrays for addresses, check for them, continue if found.
			 * NOTE: Checking only the fields available in the previous versions.
			 */
			if ( empty( $address['line_1'] ) &&
			     empty( $address['line_2'] ) &&
			     empty( $address['address_line1'] ) &&
			     empty( $address['address_line2'] ) &&
			     empty( $address['city'] ) &&
			     empty( $address['state'] ) &&
			     empty( $address['zipcode'] ) &&
			     empty( $address['country'] ) &&
			     empty( $address['latitude'] ) &&
			     empty( $address['longitude'] ) ) continue;

			if ( ! isset( $address['order'] ) ) {

				$address['order'] = $order;
			}

			if ( ! is_null( $preferred ) ) {

				$address['preferred'] = $key == $preferred ? TRUE : FALSE;
			}

			/**
			 * Allow plugins to filter raw data before object is setup.
			 *
			 * @since 8.5.19
			 *
			 * @param array $address
			 */
			$address = apply_filters( 'cn_address-pre_setup', $address );

			//$this->add( cnAddress::create( $address ) );
			$this->items->push( cnAddress::create( $address ) );

			$order++;
		}

		// Sync the filtered and unfiltered collections.
		$this->resetFilters();

		return $this;
	}

	/**
	 * Return the collection data as an array.
	 *
	 * @access public
	 * @since  8.6
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
	 * @since  8.6
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
	 * @since  8.6
	 *
	 * @return string
	 */
	public function __toString() {

		return json_encode( $this->toArray() );
	}
}
