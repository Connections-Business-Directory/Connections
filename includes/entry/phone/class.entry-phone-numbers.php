<?php

/**
 * Class cnEntry_Phone_Numbers
 *
 * @since 8.10
 */
final class cnEntry_Phone_Numbers extends cnEntry_Object_Collection {

	/**
	 * Get a phone number from the collection by its ID.
	 *
	 * @access public
	 * @since  8.10
	 *
	 * @param int $id The phone number ID to get from the collection.
	 *
	 * @return bool|cnPhone
	 */
	public function get( $id ) {

		if ( FALSE !== $key = $this->getItemKeyByID( $id ) ) {

			return apply_filters( 'cn_phone_number', $this->items->get( $key ) );
		}

		return FALSE;
	}

	/**
	 * Add a @see cnPhone to the collection.
	 *
	 * @access public
	 * @since  8.10
	 *
	 * @param cnEntry_Collection_Item $phone
	 */
	public function add( cnEntry_Collection_Item $phone ) {

		//// Reset the filters just in case filters have been applied to the collection.
		//$this->resetFilters();

		$this->items->push( apply_filters( 'cn_set_phone', $phone ) );

		//// Reset the filters so both the filtered and unfiltered collections are the same after adding a phone number.
		//$this->resetFilters();
	}

	/**
	 * Update a phone number by phone number ID.
	 *
	 * NOTE: This does not update only changed fields within the phone number object, it simply replaces the object with an
	 *       instance of the phone number object which contains the old and changed phone number information.
	 *
	 * @access public
	 * @since  8.10
	 *
	 * @param int     $id    The phone number ID to update.
	 * @param cnPhone $phone The updated phone number object used to replace the old phone number object in the collection.
	 *
	 * @return bool
	 */
	public function update( $id, $phone ) {

		//// Reset the filters just in case filters have been applied to the collection.
		//$this->resetFilters();

		// Using create_function instead of anonymous function or closure for PHP 5.2 compatibility.
		$callback = create_function(
			'$item',
			'return absint(\'' . $id . '\') === $item->getID();'
		);

		$key = $this->items->search( $callback );

		if ( FALSE !== $key ) {

			$this->items->put( $key, apply_filters( 'cn_set_phone', $phone ) );

			//// Reset the filters so both the filtered and unfiltered collections are the same after updating an phone number.
			//$this->resetFilters();

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * NOTE: This method primarily exists to support backwards compatibility for @see cnEntry::setPhoneNumbers().
	 *
	 * @access public
	 * @since  8.10
	 *
	 * @param array $data The data used to update the collection with.
	 */
	public function updateFromArray( $data ) {

		$new               = cnEntry_Phone_Numbers::createFromArray( $this->id, $data )->getCollection();
		$preferred         = isset( $data['preferred'] ) ? $data['preferred'] : NULL;
		$existingPreferred = $this->getPreferred();

		/** @var cnPhone $phone */
		foreach ( $new as $phone ) {

			/*
			 * @todo: Before a phone number is added/updated a check needs to be done to see if the `preferred` flag has been
			 *        set. If it has been, the existing phone number needs to be checked for an existing preferred phone number
			 *        and whether that user has permission to edit that phone number first before changing the preferred
			 *        phone number.
			 */

			// If exists, replace existing cnPhone object with the new one.
			if ( 0 !== $phone->getID() && $this->exists( $phone->getID() ) ) {

				$this->update( $phone->getID(), $phone );

				// If a phone number has an ID of `0`, that means it has not yet been saved to the database, add it to the collection.
			} elseif ( 0 === $phone->getID() ) {

				$this->add( $phone );
			}
		}

		$notEditableID = $this->filterBy( 'visibility', Connections_Directory()->currentUser->canNotView() )
		                      ->getCollection()
		                      ->pluck( 'id' )
		                      ->toArray();
		$existingID = $this->items->pluck( 'id' )->toArray();
		$updatedID  = $new->pluck( 'id' )->toArray();
		$deleted    = array_diff( $existingID, $updatedID, $notEditableID );

		// Remove phone numbers from collection which do not exist in the update array, because they were removed.
		if ( 0 < count( $deleted ) ) {

			foreach ( $deleted as $id ) {

				// Only remove an phone number if the ID exists in the collection.
				if ( 0 !== $id && $this->exists( $id ) ) {

					$this->remove( $id );
				}
			}
		}

		/*
		 * If a preferred phone number was set, ensure the phone number set as preferred does not override a phone number the user
		 * may not have had permission to edit.
		 */
		if ( ! is_null( $preferred ) && $existingPreferred ) {

			if ( ! in_array( $existingPreferred->getVisibility(), Connections_Directory()->currentUser->canView() ) ) {

				$this->setPreferred( $existingPreferred->getID() );

				// Throw the user a message so they know why their choice was overridden.
				cnMessage::set( 'error', 'entry_preferred_overridden_phone' );
			}
		}

		$this->applyFilter( 'cn_set_phone_numbers' );

		// Sync the filtered and unfiltered collections.
		$this->resetFilters();
	}

	/**
	 * Save the collection to the database.
	 *
	 * @todo Instance of the phone number table should be passed as a parameter.
	 *
	 * @access public
	 * @since  8.10
	 */
	public function save() {

		/*
		 * @todo Instead of using the table constant, should use the get table name from the phone number table instance.
		 * @todo The default columns array should be returned from the phone number table instance.
		 */

		$cnDb = new cnEntry_DB( $this->id );

		$cnDb->upsert(
			CN_ENTRY_PHONE_TABLE,
			array(
				'order'      => array( 'key' => 'order', 'format' => '%d' ),
				'preferred'  => array( 'key' => 'preferred', 'format' => '%d' ),
				'type'       => array( 'key' => 'type', 'format' => '%s' ),
				'number'     => array( 'key' => 'number', 'format' => '%s' ),
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
	 * @since  8.10
	 */
	public function delete() {
	}

	/**
	 * Render the phone number collection.
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
	public function render( $template = 'hcard', $atts = array(), $load = TRUE, $buffer = FALSE, $require_once = FALSE ) {

		if ( $this->filtered->isEmpty() ) return NULL;

		$html = cnTemplatePart::get(
			'entry' . DIRECTORY_SEPARATOR . 'phone-numbers' . DIRECTORY_SEPARATOR . 'phone',
			$template,
			array_merge( array( 'phoneNumbers' => $this->getCollection() ), $atts ),
			$load,
			$buffer,
			$require_once
		);

		return $html;
	}

	/**
	 * Return the phone numbers as an instance of @see cnCollection.
	 *
	 * @access public
	 * @since  8.10
	 *
	 * @param int|null $limit The number of items that should be returned from the collection.
	 *
	 * @return cnCollection
	 */
	public function getCollection( $limit = NULL ) {

		$this->applyFilter( 'cn_phone_number' )
		     ->applyFilter( 'cn_phone_numbers' )
		     ->take( $limit );

		return $this->filtered->values();
	}

	/**
	 * Return the phone number collection as an indexed array where the phone number is an associative array.
	 *
	 * NOTE: This method primarily exists to support backwards compatibility in the
	 *       return value of @see cnEntry::getPhoneNumbers().
	 *
	 * @access public
	 * @since  8.10
	 *
	 * @param int|null $limit The number of items that should be returned from the collection.
	 *
	 * @return array
	 */
	public function getCollectionAsArray( $limit = NULL ) {

		$this->applyFilter( 'cn_phone_number' )
		     ->applyFilter( 'cn_phone_numbers' )
		     ->take( $limit );

		return $this->filtered->values()->toArray();
	}

	/**
	 * NOTE: The results of these filters are reset when @see cnEntry_Phone_Numbers::resetFilters() is run.
	 *
	 * @access public
	 * @since  8.10
	 *
	 * @param string $filter The filter to be applied to the collection.
	 *
	 * @return cnEntry_Phone_Numbers
	 */
	public function applyFilter( $filter ) {

		switch ( $filter ) {

			case 'cn_phone_number':

				/**
				 * A phone number object.
				 *
				 * @since 8.10
				 *
				 * @param cnPhone $phone {
				 *     @type int    $id         The phone number ID if it was retrieved from the db.
				 *     @type bool   $preferred  Whether the phone number is the preferred phone number or not.
				 *     @type string $type       The phone number type.
				 *     @type string $number     The number.
				 *     @type string $visibility The phone number visibility.
				 * }
				 */
				$callback = create_function( '$item', 'return apply_filters( \'cn_phone_number\', $item );' );
				break;

			case 'cn_phone_numbers':

				/**
				 * An index array of phone number objects.
				 *
				 * @since unknown
				 *
				 * @param array $results See the documentation for the `cn_phone_number` filter for the params of each
				 *                       item in the phone numbers array.
				 */
				$this->filtered = apply_filters( 'cn_phone_numbers', $this->filtered );
				break;

			case 'cn_set_phone_number':

				$callback = create_function( '$item', 'return apply_filters( \'cn_set_phone_number\', $item );' );
				break;

			case 'cn_set_phone_numbers':

				$this->filtered = apply_filters( 'cn_set_phone_numbers', $this->filtered );
				break;
		}

		if ( isset( $callback ) && 0 < $this->filtered->count() ) {

			$this->filtered->transform( $callback );
			//$this->items->transform( $callback );
		}

		return $this;
	}

	/**
	 * Get the phone number flagged as the "Preferred" phone number.
	 *
	 * @access public
	 * @since  8.10
	 *
	 * @return cnPhone
	 */
	public function getPreferred() {

		return apply_filters( 'cn_phone_number', $this->filtered->where( 'preferred', '===', TRUE )->first() );
	}

	/**
	 * NOTE: The results of these filters are reset when @see cnEntry_Phone_Numbers::resetFilters() is run.
	 *
	 * @access public
	 * @since  8.10
	 *
	 * @param string            $field The field which the collection should be filtered by.
	 * @param array|bool|string $value The values used to filter the collection by.
	 *
	 * @return cnEntry_Phone_Numbers
	 */
	public function filterBy( $field, $value ) {

		if ( in_array( $field, array( 'type') ) ) {

			if ( ! empty( $value ) ) {

				cnFunction::parseStringList( $value );

				$this->filtered = $this->filtered->whereIn( $field, $value );
			}

		} elseif ( 'preferred' === $field ) {

			cnFormatting::toBoolean( $value );

			// Only apply the preferred filter if the filter is TRUE so all phone numbers will be returned if FALSE.
			if ( $value ) $this->filtered = $this->filtered->where( 'preferred', '===', $value );

		} elseif ( 'visibility' === $field ) {

			$this->filtered = $this->filtered->whereIn( 'visibility', $value );
		}

		return $this;
	}

	/**
	 * Get phone numbers by entry ID from phone number table in the database.
	 *
	 * Returns all phone numbers associated to an entry as an instance of @see cnEntry_Phone_Numbers.
	 *
	 * @access public
	 * @since  8.10
	 * @static
	 *
	 * @param int   $id      The entry ID to create the phone number collection for.
	 * @param array $options The options used to perform the database query.
	 *
	 * @return cnEntry_Phone_Numbers
	 */
	public static function createFromQuery( $id, $options = array() ) {

		$phoneNumbers = new cnEntry_Phone_Numbers();

		$phoneNumbers->setEntryID( $id )->query( $options );

		return $phoneNumbers;
	}

	/**
	 * Get phone numbers associated to an entry from phone number table in the database.
	 *
	 * @access public
	 * @since  8.10
	 *
	 * @param array $options The options used to perform the database query.
	 *
	 * @return cnEntry_Phone_Numbers
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
			$data = $instance->retrieve->phoneNumbers( $options, TRUE );
		}

		if ( empty( $data ) ) return $this;

		$this->fromArray( $data );

		return $this;
	}

	/**
	 * Create an instance of @see cnEntry_Phone_Numbers from an array of phone number data.
	 *
	 * @access public
	 * @since  8.10
	 * @static
	 *
	 * @param int   $id   The entry ID to create the phone number collection for.
	 * @param array $data The data used to create the collection with.
	 *
	 * @return cnEntry_Phone_Numbers
	 */
	public static function createFromArray( $id, $data = array() ) {

		$phoneNumbers = new cnEntry_Phone_Numbers();

		$phoneNumbers->setEntryID( $id )->fromArray( $data );

		return $phoneNumbers;
	}

	/**
	 * Populate @see cnEntry_Phone_Numbers with data from an array of phone number data.
	 *
	 * @access public
	 * @since  8.10
	 *
	 * @param array $data The data used to create the collection with.
	 *
	 * @return cnEntry_Phone_Numbers
	 */
	public function fromArray( $data = array() ) {

		$collection = new cnCollection( $data );
		$order      = $collection->max('order');

		/*
		 * The source of $data in Connections core will be from a form submission, object cache or the db.
		 * When the source is from the form submission the preferred item `key` is stored in the 'preferred' array key
		 * in the array of phone numbers. If the array key is set, save the array key value otherwise set it to NULL.
		 *
		 * If the `preferred` array key is set, the preferred phone number will be set based on the array key value.
		 * If it is not, the preferred phone number value will be retained using the `preferred` key within each phone number.
		 */
		$preferred  = isset( $data['preferred'] ) ? $data['preferred'] : NULL;

		foreach ( $collection as $key => $phone ) {

			if ( empty( $phone ) ) continue;

			/*
			 * If the number is empty, no need to store it.
			 */
			if ( empty( $phone['number'] ) ) continue;

			if ( ! isset( $phone['order'] ) ) {

				$phone['order'] = $order;
			}

			if ( ! is_null( $preferred ) ) {

				$phone['preferred'] = $key == $preferred ? TRUE : FALSE;
			}

			/**
			 * Allow plugins to filter raw data before object is setup.
			 *
			 * @since 8.5.19
			 *
			 * @param array $phone
			 */
			$phone = apply_filters( 'cn_phone-pre_setup', $phone );

			//$this->add( cnPhone::create( $phone ) );
			$this->items->push( cnPhone::create( $phone ) );

			$order++;
		}

		// Sync the filtered and unfiltered collections.
		$this->resetFilters();

		return $this;
	}
}
