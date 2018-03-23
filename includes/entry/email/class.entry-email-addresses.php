<?php

/**
 * Class cnEntry_Email_Addresses
 *
 * @since 8.14
 */
final class cnEntry_Email_Addresses extends cnEntry_Object_Collection {

	/**
	 * Get a email address from the collection by its ID.
	 *
	 * @access public
	 * @since  8.14
	 *
	 * @param int $id The email address ID to get from the collection.
	 *
	 * @return bool|cnEmail_Address
	 */
	public function get( $id ) {

		if ( FALSE !== $key = $this->getItemKeyByID( $id ) ) {

			return apply_filters( 'cn_email_address', $this->items->get( $key ) );
		}

		return FALSE;
	}

	/**
	 * Add a @see cnEmail_Address to the collection.
	 *
	 * @access public
	 * @since  8.14
	 *
	 * @param cnEntry_Collection_Item $email
	 */
	public function add( cnEntry_Collection_Item $email ) {

		//// Reset the filters just in case filters have been applied to the collection.
		//$this->resetFilters();

		$this->items->push( apply_filters( 'cn_set_email_address', $email ) );

		//// Reset the filters so both the filtered and unfiltered collections are the same after adding an email address.
		//$this->resetFilters();
	}

	/**
	 * Update a email address by email address ID.
	 *
	 * NOTE: This does not update only changed fields within the email address object, it simply replaces the object with an
	 *       instance of the email address object which contains the old and changed email address information.
	 *
	 * @access public
	 * @since  8.14
	 *
	 * @param int             $id    The email address ID to update.
	 * @param cnEmail_Address $email The updated email address object used to replace the old email address object in the collection.
	 *
	 * @return bool
	 */
	public function update( $id, $email ) {

		//// Reset the filters just in case filters have been applied to the collection.
		//$this->resetFilters();

		// Using create_function instead of anonymous function or closure for PHP 5.2 compatibility.
		$callback = create_function(
			'$item',
			'return absint(\'' . $id . '\') === $item->getID();'
		);

		$key = $this->items->search( $callback );

		if ( FALSE !== $key ) {

			$this->items->put( $key, apply_filters( 'cn_set_email_address', $email ) );

			//// Reset the filters so both the filtered and unfiltered collections are the same after updating an email address.
			//$this->resetFilters();

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * NOTE: This method primarily exists to support backwards compatibility for @see cnEntry::setEmailAddresses().
	 *
	 * @access public
	 * @since  8.14
	 *
	 * @param array $data The data used to update the collection with.
	 */
	public function updateFromArray( $data ) {

		$new               = cnEntry_Email_Addresses::createFromArray( $this->id, $data )->getCollection();
		$preferred         = isset( $data['preferred'] ) ? $data['preferred'] : NULL;
		$existingPreferred = $this->getPreferred();

		/** @var cnEmail_Address $email */
		foreach ( $new as $email ) {

			/*
			 * @todo: Before a email address is added/updated a check needs to be done to see if the `preferred` flag has been
			 *        set. If it has been, the existing email address needs to be checked for an existing preferred email address
			 *        and whether that user has permission to edit that email address first before changing the preferred
			 *        email address.
			 */

			// If exists, replace existing cnEmail_Address object with the new one.
			if ( 0 !== $email->getID() && $this->exists( $email->getID() ) ) {

				$this->update( $email->getID(), $email );

				// If a email address has an ID of `0`, that means it has not yet been saved to the database, add it to the collection.
			} elseif ( 0 === $email->getID() ) {

				$this->add( $email );
			}
		}

		$notEditableID = $this->filterBy( 'visibility', Connections_Directory()->currentUser->canNotView() )
		                      ->getCollection()
		                      ->pluck( 'id' )
		                      ->toArray();
		$existingID = $this->items->pluck( 'id' )->toArray();
		$updatedID  = $new->pluck( 'id' )->toArray();
		$deleted    = array_diff( $existingID, $updatedID, $notEditableID );

		// Remove email addresses from collection which do not exist in the update array, because they were removed.
		if ( 0 < count( $deleted ) ) {

			foreach ( $deleted as $id ) {

				// Only remove an email address if the ID exists in the collection.
				if ( 0 !== $id && $this->exists( $id ) ) {

					$this->remove( $id );
				}
			}
		}

		/*
		 * If a preferred email address was set, ensure the email address set as preferred does not override a email address the user
		 * may not have had permission to edit.
		 */
		if ( ! is_null( $preferred ) && $existingPreferred ) {

			if ( ! in_array( $existingPreferred->getVisibility(), Connections_Directory()->currentUser->canView() ) ) {

				$this->setPreferred( $existingPreferred->getID() );

				// Throw the user a message so they know why their choice was overridden.
				cnMessage::set( 'error', 'entry_preferred_overridden_email' );
			}
		}

		$this->applyFilter( 'cn_set_email_addresses' );

		// Sync the filtered and unfiltered collections.
		$this->resetFilters();
	}

	/**
	 * Save the collection to the database.
	 *
	 * @todo Instance of the email address table should be passed as a parameter.
	 *
	 * @access public
	 * @since  8.14
	 */
	public function save() {

		/*
		 * @todo Instead of using the table constant, should use the get table name from the email address table instance.
		 * @todo The default columns array should be returned from the email address table instance.
		 */

		$cnDb = new cnEntry_DB( $this->id );

		$cnDb->upsert(
			CN_ENTRY_EMAIL_TABLE,
			array(
				'order'      => array( 'key' => 'order', 'format' => '%d' ),
				'preferred'  => array( 'key' => 'preferred', 'format' => '%d' ),
				'type'       => array( 'key' => 'type', 'format' => '%s' ),
				'address'    => array( 'key' => 'address', 'format' => '%s' ),
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
	 * @since  8.14
	 */
	public function delete() {
	}

	/**
	 * Render the email address collection.
	 *
	 * @access public
	 * @since  8.14
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
			'entry' . DIRECTORY_SEPARATOR . 'email-addresses' . DIRECTORY_SEPARATOR . 'email',
			$template,
			array_merge( array( 'emailAddresses' => $this->getCollection() ), $atts ),
			$load,
			$buffer,
			$require_once
		);

		return $html;
	}

	/**
	 * Return the email addresses as an instance of @see cnCollection.
	 *
	 * @access public
	 * @since  8.14
	 *
	 * @param int|null $limit The number of items that should be returned from the collection.
	 *
	 * @return cnCollection
	 */
	public function getCollection( $limit = NULL ) {

		$this->applyFilter( 'cn_email_address' )
		     ->applyFilter( 'cn_email_addresses' )
		     ->take( $limit );

		return $this->filtered->values();
	}

	/**
	 * Return the email address collection as an indexed array where the email address is an associative array.
	 *
	 * NOTE: This method primarily exists to support backwards compatibility in the
	 *       return value of @see cnEntry::getEmailAddresses().
	 *
	 * @access public
	 * @since  8.14
	 *
	 * @param int|null $limit The number of items that should be returned from the collection.
	 *
	 * @return array
	 */
	public function getCollectionAsArray( $limit = NULL ) {

		$this->applyFilter( 'cn_email_address' )
		     ->applyFilter( 'cn_email_addresses' )
		     ->take( $limit );

		return $this->filtered->values()->toArray();
	}

	/**
	 * NOTE: The results of these filters are reset when @see cnEntry_Email_Addresses::resetFilters() is run.
	 *
	 * @access public
	 * @since  8.14
	 *
	 * @param string $filter The filter to be applied to the collection.
	 *
	 * @return cnEntry_Email_Addresses
	 */
	public function applyFilter( $filter ) {

		switch ( $filter ) {

			case 'cn_email_address':

				/**
				 * An email address object.
				 *
				 * @since 8.14
				 *
				 * @param cnEmail_Address $email {
				 *     @type int    $id         The email address ID if it was retrieved from the db.
				 *     @type bool   $preferred  Whether the email address is the preferred email address or not.
				 *     @type string $type       The email address type.
				 *     @type string $address    The email address.
				 *     @type string $visibility The email address  visibility.
				 * }
				 */
				$callback = create_function( '$item', 'return apply_filters( \'cn_email_address\', $item );' );
				break;

			case 'cn_email_addresses':

				/**
				 * An indexed array of email address objects.
				 *
				 * @since unknown
				 *
				 * @param array $results See the documentation for the `cn_email_address` filter for the params of each
				 *                       item in the email addresses array.
				 */
				$this->filtered = apply_filters( 'cn_email_addresses', $this->filtered );
				break;

			case 'cn_set_email_address':

				$callback = create_function( '$item', 'return apply_filters( \'cn_set_email_address\', $item );' );
				break;

			case 'cn_set_email_addresses':

				$this->filtered = apply_filters( 'cn_set_email_addresses', $this->filtered );
				break;
		}

		if ( isset( $callback ) && 0 < $this->filtered->count() ) {

			$this->filtered->transform( $callback );
			//$this->items->transform( $callback );
		}

		return $this;
	}

	/**
	 * Get the email address flagged as the "Preferred" email address.
	 *
	 * @access public
	 * @since  8.14
	 *
	 * @return cnEmail_Address
	 */
	public function getPreferred() {

		return apply_filters( 'cn_email_address', $this->filtered->where( 'preferred', '===', TRUE )->first() );
	}

	/**
	 * NOTE: The results of these filters are reset when @see cnEntry_Email_Addresses::resetFilters() is run.
	 *
	 * @access public
	 * @since  8.14
	 *
	 * @param string            $field The field which the collection should be filtered by.
	 * @param array|bool|string $value The values used to filter the collection by.
	 *
	 * @return cnEntry_Email_Addresses
	 */
	public function filterBy( $field, $value ) {

		if ( in_array( $field, array( 'type') ) ) {

			if ( ! empty( $value ) ) {

				cnFunction::parseStringList( $value );

				$this->filtered = $this->filtered->whereIn( $field, $value );
			}

		} elseif ( 'preferred' === $field ) {

			cnFormatting::toBoolean( $value );

			// Only apply the preferred filter if the filter is TRUE so all email addresses will be returned if FALSE.
			if ( $value ) $this->filtered = $this->filtered->where( 'preferred', '===', $value );

		} elseif ( 'visibility' === $field ) {

			$this->filtered = $this->filtered->whereIn( 'visibility', $value );
		}

		return $this;
	}

	/**
	 * Get email addresses by entry ID from email address table in the database.
	 *
	 * Returns all email addresses associated to an entry as an instance of @see cnEntry_Email_Addresses.
	 *
	 * @access public
	 * @since  8.14
	 * @static
	 *
	 * @param int   $id      The entry ID to create the email address collection for.
	 * @param array $options The options used to perform the database query.
	 *
	 * @return cnEntry_Email_Addresses
	 */
	public static function createFromQuery( $id, $options = array() ) {

		$email = new cnEntry_Email_Addresses();

		$email->setEntryID( $id )->query( $options );

		return $email;
	}

	/**
	 * Get email addresses  associated to an entry from email address table in the database.
	 *
	 * @access public
	 * @since  8.14
	 *
	 * @param array $options The options used to perform the database query.
	 *
	 * @return cnEntry_Email_Addresses
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
			$data = $instance->retrieve->emailAddresses( $options, TRUE );
		}

		if ( empty( $data ) ) return $this;

		$this->fromArray( $data );

		return $this;
	}

	/**
	 * Create an instance of @see cnEntry_Email_Addresses from an array of email address data.
	 *
	 * @access public
	 * @since  8.14
	 * @static
	 *
	 * @param int   $id   The entry ID to create the email address collection for.
	 * @param array $data The data used to create the collection with.
	 *
	 * @return cnEntry_Email_Addresses
	 */
	public static function createFromArray( $id, $data = array() ) {

		$email = new cnEntry_Email_Addresses();

		$email->setEntryID( $id )->fromArray( $data );

		return $email;
	}

	/**
	 * Populate @see cnEntry_Email_Addresses with data from an array of email address data.
	 *
	 * @access public
	 * @since  8.14
	 *
	 * @param array $data The data used to create the collection with.
	 *
	 * @return cnEntry_Email_Addresses
	 */
	public function fromArray( $data = array() ) {

		$collection = new cnCollection( $data );
		$order      = $collection->max('order');

		/*
		 * The source of $data in Connections core will be from a form submission, object cache or the db.
		 * When the source is from the form submission the preferred item `key` is stored in the 'preferred' array key
		 * in the array of email address. If the array key is set, save the array key value otherwise set it to NULL.
		 *
		 * If the `preferred` array key is set, the preferred email address will be set based on the array key value.
		 * If it is not, the preferred email address value will be retained using the `preferred` key within each email address.
		 */
		$preferred  = isset( $data['preferred'] ) ? $data['preferred'] : NULL;

		foreach ( $collection as $key => $email ) {

			if ( empty( $email ) ) continue;

			/*
			 * If the address is empty, no need to store it.
			 */
			if ( empty( $email['address'] ) ) continue;

			if ( ! isset( $email['order'] ) ) {

				$email['order'] = $order;
			}

			if ( ! is_null( $preferred ) ) {

				$email['preferred'] = $key == $preferred ? TRUE : FALSE;
			}

			/**
			 * Allow plugins to filter raw data before object is setup.
			 *
			 * @since 8.14
			 *
			 * @param array $email
			 */
			$email = apply_filters( 'cn_email-pre_setup', $email );

			//$this->add( cnEmail_Address::create( $email ) );
			$this->items->push( cnEmail_Address::create( $email ) );

			$order++;
		}

		// Sync the filtered and unfiltered collections.
		$this->resetFilters();

		return $this;
	}
}
