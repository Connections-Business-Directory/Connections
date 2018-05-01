<?php

/**
 * Class cnEntry_Messenger_IDs
 *
 * @since 8.16
 */
final class cnEntry_Messenger_IDs extends cnEntry_Object_Collection {

	/**
	 * Get a messenger ID from the collection by its ID.
	 *
	 * @access public
	 * @since  8.16
	 *
	 * @param int $id The messenger ID to get from the collection.
	 *
	 * @return bool|cnMessenger
	 */
	public function get( $id ) {

		if ( FALSE !== $key = $this->getItemKeyByID( $id ) ) {

			return apply_filters( 'cn_messenger_id', $this->items->get( $key ) );
		}

		return FALSE;
	}

	/**
	 * Add a @see cnMessenger to the collection.
	 *
	 * @access public
	 * @since  8.16
	 *
	 * @param cnEntry_Collection_Item $messenger
	 */
	public function add( cnEntry_Collection_Item $messenger ) {

		//// Reset the filters just in case filters have been applied to the collection.
		//$this->resetFilters();

		$this->items->push( apply_filters( 'cn_set_messenger_id', $messenger ) );

		//// Reset the filters so both the filtered and unfiltered collections are the same after adding a messenger ID.
		//$this->resetFilters();
	}

	/**
	 * Update a messenger ID by messenger ID.
	 *
	 * NOTE: This does not update only changed fields within the messenger object, it simply replaces the object with an
	 *       instance of the messenger object which contains the old and changed messenger information.
	 *
	 * @access public
	 * @since  8.16
	 *
	 * @param int         $id        The messenger ID to update.
	 * @param cnMessenger $messenger The updated messenger object used to replace the old messenger object in the collection.
	 *
	 * @return bool
	 */
	public function update( $id, $messenger ) {

		//// Reset the filters just in case filters have been applied to the collection.
		//$this->resetFilters();

		// Using create_function instead of anonymous function or closure for PHP 5.2 compatibility.
		$callback = create_function(
			'$item',
			'return absint(\'' . $id . '\') === $item->getID();'
		);

		$key = $this->items->search( $callback );

		if ( FALSE !== $key ) {

			$this->items->put( $key, apply_filters( 'cn_set_messenger_id', $messenger ) );

			//// Reset the filters so both the filtered and unfiltered collections are the same after updating a messenger ID.
			//$this->resetFilters();

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * NOTE: This method primarily exists to support backwards compatibility for @see cnEntry::setIm().
	 *
	 * @access public
	 * @since  8.16
	 *
	 * @param array $data The data used to update the collection with.
	 */
	public function updateFromArray( $data ) {

		$new               = cnEntry_Messenger_IDs::createFromArray( $this->id, $data )->getCollection();
		$preferred         = isset( $data['preferred'] ) ? $data['preferred'] : NULL;
		$existingPreferred = $this->getPreferred();

		/** @var cnMessenger $messenger */
		foreach ( $new as $messenger ) {

			/*
			 * @todo: Before a messenger ID is added/updated a check needs to be done to see if the `preferred` flag has been
			 *        set. If it has been, the existing messenger ID needs to be checked for an existing preferred messenger ID
			 *        and whether that user has permission to edit that messenger ID first before changing the preferred
			 *        messenger ID.
			 */

			// If exists, replace existing cnMessenger object with the new one.
			if ( 0 !== $messenger->getID() && $this->exists( $messenger->getID() ) ) {

				$this->update( $messenger->getID(), $messenger );

				// If a messenger ID has an ID of `0`, that means it has not yet been saved to the database, add it to the collection.
			} elseif ( 0 === $messenger->getID() ) {

				$this->add( $messenger );
			}
		}

		$notEditableID = $this->filterBy( 'visibility', Connections_Directory()->currentUser->canNotView() )
		                      ->getCollection()
		                      ->pluck( 'id' )
		                      ->toArray();
		$existingID = $this->items->pluck( 'id' )->toArray();
		$updatedID  = $new->pluck( 'id' )->toArray();
		$deleted    = array_diff( $existingID, $updatedID, $notEditableID );

		// Remove messenger IDs from collection which do not exist in the update array, because they were removed.
		if ( 0 < count( $deleted ) ) {

			foreach ( $deleted as $id ) {

				// Only remove an messenger ID if the ID exists in the collection.
				if ( 0 !== $id && $this->exists( $id ) ) {

					$this->remove( $id );
				}
			}
		}

		/*
		 * If a preferred messenger ID was set, ensure the messenger ID set as preferred does not override a messenger ID the user
		 * may not have had permission to edit.
		 */
		if ( ! is_null( $preferred ) && $existingPreferred ) {

			if ( ! in_array( $existingPreferred->getVisibility(), Connections_Directory()->currentUser->canView() ) ) {

				$this->setPreferred( $existingPreferred->getID() );

				// Throw the user a message so they know why their choice was overridden.
				cnMessage::set( 'error', 'entry_preferred_overridden_im' );
			}
		}

		$this->applyFilter( 'cn_set_messenger_ids' );

		// Sync the filtered and unfiltered collections.
		$this->resetFilters();
	}

	/**
	 * Save the collection to the database.
	 *
	 * @todo Instance of the messenger table should be passed as a parameter.
	 *
	 * @access public
	 * @since  8.16
	 */
	public function save() {

		/*
		 * @todo Instead of using the table constant, should use the get table name from the messenger table instance.
		 * @todo The default columns array should be returned from the messenger table instance.
		 */

		$cnDb = new cnEntry_DB( $this->id );

		$cnDb->upsert(
			CN_ENTRY_MESSENGER_TABLE,
			array(
				'order'      => array( 'key' => 'order', 'format' => '%d' ),
				'preferred'  => array( 'key' => 'preferred', 'format' => '%d' ),
				'type'       => array( 'key' => 'type', 'format' => '%s' ),
				'uid'        => array( 'key' => 'uid', 'format' => '%s' ),
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
	 * @since  8.16
	 */
	public function delete() {
	}

	/**
	 * Render the messenger collection.
	 *
	 * @access public
	 * @since  8.16
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
			'entry' . DIRECTORY_SEPARATOR . 'messenger' . DIRECTORY_SEPARATOR . 'messenger',
			$template,
			array_merge( array( 'networks' => $this->getCollection() ), $atts ),
			$load,
			$buffer,
			$require_once
		);

		return $html;
	}

	/**
	 * Return the messenger IDs as an instance of @see cnCollection.
	 *
	 * @access public
	 * @since  8.16
	 *
	 * @param int|null $limit The number of items that should be returned from the collection.
	 *
	 * @return cnCollection
	 */
	public function getCollection( $limit = NULL ) {

		$this->applyFilter( 'cn_messenger_id' )
		     ->applyFilter( 'cn_messenger_ids' )
		     ->take( $limit );

		return $this->filtered->values();
	}

	/**
	 * Return the messenger ID collection as an indexed array where the messenger ID is an associative array.
	 *
	 * NOTE: This method primarily exists to support backwards compatibility in the
	 *       return value of @see cnEntry::getIm().
	 *
	 * @access public
	 * @since  8.16
	 *
	 * @param int|null $limit The number of items that should be returned from the collection.
	 *
	 * @return array
	 */
	public function getCollectionAsArray( $limit = NULL ) {

		$this->applyFilter( 'cn_messenger_id' )
		     ->applyFilter( 'cn_messenger_ids' )
		     ->take( $limit );

		return $this->filtered->values()->toArray();
	}

	/**
	 * NOTE: The results of these filters are reset when @see cnEntry_Messenger_IDs::resetFilters() is run.
	 *
	 * @access public
	 * @since  8.16
	 *
	 * @param string $filter The filter to be applied to the collection.
	 *
	 * @return static
	 */
	public function applyFilter( $filter ) {

		switch ( $filter ) {

			case 'cn_messenger_id':

				/**
				 * A messenger ID object.
				 *
				 * @since 8.16
				 *
				 * @param cnMessenger $messenger {
				 *     @type int    $id         The messenger ID as it was retrieved from the db.
				 *     @type bool   $preferred  Whether the messenger ID is the preferred messenger ID or not.
				 *     @type string $type       The messenger ID type.
				 *     @type string $number     The messenger user ID.
				 *     @type string $visibility The messenger ID visibility.
				 * }
				 */
				$callback = create_function( '$item', 'return apply_filters( \'cn_messenger_id\', $item );' );
				break;

			case 'cn_messenger_ids':

				/**
				 * An index array of messenger ID objects.
				 *
				 * @since 8.16
				 *
				 * @param array $results See the documentation for the `cn_messenger_id` filter for the params of each
				 *                       item in the messenger ID array.
				 */
				$this->filtered = apply_filters( 'cn_messenger_ids', $this->filtered );
				break;

			case 'cn_set_messenger_id':

				$callback = create_function( '$item', 'return apply_filters( \'cn_set_messenger_id\', $item );' );
				break;

			case 'cn_set_messenger_ids':

				$this->filtered = apply_filters( 'cn_set_messenger_ids', $this->filtered );
				break;
		}

		if ( isset( $callback ) && 0 < $this->filtered->count() ) {

			$this->filtered->transform( $callback );
			//$this->items->transform( $callback );
		}

		return $this;
	}

	/**
	 * Get the messenger ID flagged as the "Preferred" messenger ID.
	 *
	 * @access public
	 * @since  8.16
	 *
	 * @return cnMessenger
	 */
	public function getPreferred() {

		return apply_filters( 'cn_messenger_id', $this->filtered->where( 'preferred', '===', TRUE )->first() );
	}

	/**
	 * NOTE: The results of these filters are reset when @see cnEntry_Messenger_IDs::resetFilters() is run.
	 *
	 * @access public
	 * @since  8.16
	 *
	 * @param string            $field The field which the collection should be filtered by.
	 * @param array|bool|string $value The values used to filter the collection by.
	 *
	 * @return static
	 */
	public function filterBy( $field, $value ) {

		if ( in_array( $field, array( 'type') ) ) {

			if ( ! empty( $value ) ) {

				cnFunction::parseStringList( $value );

				$this->filtered = $this->filtered->whereIn( $field, $value );
			}

		} elseif ( 'preferred' === $field ) {

			cnFormatting::toBoolean( $value );

			// Only apply the preferred filter if the filter is TRUE so all messenger ID will be returned if FALSE.
			if ( $value ) $this->filtered = $this->filtered->where( 'preferred', '===', $value );

		} elseif ( 'visibility' === $field ) {

			$this->filtered = $this->filtered->whereIn( 'visibility', $value );
		}

		return $this;
	}

	/**
	 * Get messengerIDs by entry ID from messenger table in the database.
	 *
	 * Returns all messenger IDs associated to an entry as an instance of @see cnEntry_Messenger_IDs.
	 *
	 * @access public
	 * @since  8.16
	 * @static
	 *
	 * @param int   $id      The entry ID to create the messenger ID collection for.
	 * @param array $options The options used to perform the database query.
	 *
	 * @return static
	 */
	public static function createFromQuery( $id, $options = array() ) {

		$messengerIDs = new cnEntry_Messenger_IDs();

		$messengerIDs->setEntryID( $id )->query( $options );

		return $messengerIDs;
	}

	/**
	 * Get messenger IDs associated to an entry from messenger ID table in the database.
	 *
	 * @access public
	 * @since  8.16
	 *
	 * @param array $options The options used to perform the database query.
	 *
	 * @return static
	 */
	public function query( $options = array() ) {

		// Empty the Collection since fresh data is populating the Collection from the db.
		$this->items    = new cnCollection();
		$this->filtered = new cnCollection();

		if ( ! empty( $this->id ) ) {

			$options['id'] = $this->id;

			/*
			 * Set saving as true to force the query of all entries filtered per supplied attributes.
			 * This will reflect who it function when the table manager and query classes are implemented.
			 */
			$data = Connections_Directory()->retrieve->imIDs( $options, TRUE );
		}

		if ( empty( $data ) ) return $this;

		$this->fromArray( $data );

		return $this;
	}

	/**
	 * Create an instance of @see cnEntry_Messenger_IDs from an array of messenger ID data.
	 *
	 * @access public
	 * @since  8.16
	 * @static
	 *
	 * @param int   $id   The entry ID to create the messenger ID collection for.
	 * @param array $data The data used to create the collection with.
	 *
	 * @return static
	 */
	public static function createFromArray( $id, $data = array() ) {

		$messengerIDs = new cnEntry_Messenger_IDs();

		$messengerIDs->setEntryID( $id )->fromArray( $data );

		return $messengerIDs;
	}

	/**
	 * Populate @see cnEntry_Messenger_IDs() with data from an array of messenger ID data.
	 *
	 * @access public
	 * @since  8.16
	 *
	 * @param array $data The data used to create the collection with.
	 *
	 * @return static
	 */
	public function fromArray( $data = array() ) {

		$collection = new cnCollection( $data );
		$order      = $collection->max('order');
		$preferred  = NULL;

		/*
		 * The source of $data in Connections core will be from a form submission, object cache or the db.
		 * When the source is from the form submission the preferred item `key` is stored in the 'preferred' array key
		 * in the array of messenger IDs. If the array key is set, save the array key value otherwise set it to NULL.
		 *
		 * If the `preferred` array key is set, the preferred messenger ID  will be set based on the array key value.
		 * If it is not, the preferred messenger ID value will be retained using the `preferred` key within each messenger ID.
		 */
		if ( isset( $data['preferred'] ) ) {

			$preferred = $data['preferred'];
			unset( $data['preferred'] );
		}

		foreach ( $collection as $key => $messenger ) {

			if ( empty( $messenger ) ) continue;

			/*
			 * If the messenger user ID is empty, no need to store it.
			 */
			if ( 0 >= strlen( $messenger['uid'] ) ) continue;

			if ( ! isset( $messenger['order'] ) ) {

				$messenger['order'] = $order;
			}

			if ( ! is_null( $preferred ) ) {

				$messenger['preferred'] = $key == $preferred ? TRUE : FALSE;
			}

			/**
			 * Allow plugins to filter raw data before object is setup.
			 *
			 * @since 8.16
			 *
			 * @param array $messenger
			 */
			$messenger = apply_filters( 'cn_im-pre_setup', $messenger );

			//$this->add( cnMessenger::create( $messenger ) );
			$this->items->push( cnMessenger::create( $messenger ) );

			$order++;
		}

		// Sync the filtered and unfiltered collections.
		$this->resetFilters();

		return $this;
	}

	/**
	 * Override the parent so back compatibility can be applied.
	 *
	 * @access private
	 * @since  8.19
	 *
	 * @param array|string $data
	 */
	public function fromMaybeSerialized( $data ) {

		$data = $this->maybeUnserialize( $data );
		$data = $this->backCompatibility( $data );

		$this->fromArray( $data );
	}

	/**
	 * This will probably forever give me headaches,
	 * Previous versions stored the IM ID as id. Now that the data
	 * is stored in a separate table, id is now the unique table `id`
	 * and `uid` is the messenger user ID.
	 *
	 * So I have to make sure to properly map the values. Unfortunately
	 * this differs from the rest of the entry data is where `id` equals
	 * the unique table `id`. So lets map the table `id` to `uid` and the
	 * the table `uid` to `id`.
	 *
	 * Basically swapping the values. This should maintain compatibility.
	 *
	 * @access private
	 * @since  8.19
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public function backCompatibility( $data ) {

		if ( is_array( $data ) ) {

			foreach ( $data as &$messenger ) {

				if ( is_array( $messenger ) ) {

					$id     = $messenger['id'];
					$userID = $messenger['uid'];

					$messenger['id']  = $userID;
					$messenger['uid'] = $id;

				} elseif ( is_object( $messenger ) ) {

					$id     = $messenger->id;
					$userID = $messenger->uid;

					$messenger->id  = $userID;
					$messenger->uid = $id;
				}

			}
		}

		return $data;
	}
}
