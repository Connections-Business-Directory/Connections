<?php

/**
 * Class cnEntry_Links
 *
 * @since 8.19
 */
final class cnEntry_Links extends cnEntry_Object_Collection {

	/**
	 * Get a Link ID from the collection by its ID.
	 *
	 * @access public
	 * @since  8.19
	 *
	 * @param int $id The link ID to get from the collection.
	 *
	 * @return bool|cnMessenger
	 */
	public function get( $id ) {

		if ( FALSE !== $key = $this->getItemKeyByID( $id ) ) {

			return apply_filters( 'cn_link', $this->items->get( $key ) );
		}

		return FALSE;
	}

	/**
	 * Add a @see cnLink to the collection.
	 *
	 * @access public
	 * @since  8.19
	 *
	 * @param cnEntry_Collection_Item $link
	 */
	public function add( cnEntry_Collection_Item $link ) {

		//// Reset the filters just in case filters have been applied to the collection.
		//$this->resetFilters();

		$this->items->push( apply_filters( 'cn_set_link', $link ) );

		//// Reset the filters so both the filtered and unfiltered collections are the same after adding a link ID.
		//$this->resetFilters();
	}

	/**
	 * Update a link ID by link ID.
	 *
	 * NOTE: This does not update only changed fields within the link object, it simply replaces the object with an
	 *       instance of the link object which contains the old and changed link information.
	 *
	 * @access public
	 * @since  8.19
	 *
	 * @param int    $id   The link ID to update.
	 * @param cnLink $link The updated link object used to replace the old link object in the collection.
	 *
	 * @return bool
	 */
	public function update( $id, $link ) {

		//// Reset the filters just in case filters have been applied to the collection.
		//$this->resetFilters();

		// Using create_function instead of anonymous function or closure for PHP 5.2 compatibility.
		$callback = create_function(
			'$item',
			'return absint(\'' . $id . '\') === $item->getID();'
		);

		$key = $this->items->search( $callback );

		if ( FALSE !== $key ) {

			$this->items->put( $key, apply_filters( 'cn_set_link', $link ) );

			//// Reset the filters so both the filtered and unfiltered collections are the same after updating a link ID.
			//$this->resetFilters();

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * NOTE: This method primarily exists to support backwards compatibility for @see cnEntry::setLinks().
	 *
	 * @access public
	 * @since  8.19
	 *
	 * @param array $data The data used to update the collection with.
	 */
	public function updateFromArray( $data ) {

		$new               = cnEntry_Links::createFromArray( $this->id, $data )->getCollection();
		$preferred         = isset( $data['preferred'] ) ? $data['preferred'] : NULL;
		$existingPreferred = $this->getPreferred();

		/** @var cnLink $link */
		foreach ( $new as $link ) {

			/*
			 * @todo: Before a link ID is added/updated a check needs to be done to see if the `preferred` flag has been
			 *        set. If it has been, the existing link ID needs to be checked for an existing preferred link ID
			 *        and whether that user has permission to edit that link ID first before changing the preferred
			 *        link ID.
			 */

			// If exists, replace existing cnLink object with the new one.
			if ( 0 !== $link->getID() && $this->exists( $link->getID() ) ) {

				$this->update( $link->getID(), $link );

				// If a link ID has an ID of `0`, that means it has not yet been saved to the database, add it to the collection.
			} elseif ( 0 === $link->getID() ) {

				$this->add( $link );
			}
		}

		$notEditableID = $this->filterBy( 'visibility', Connections_Directory()->currentUser->canNotView() )
		                      ->getCollection()
		                      ->pluck( 'id' )
		                      ->toArray();
		$existingID = $this->items->pluck( 'id' )->toArray();
		$updatedID  = $new->pluck( 'id' )->toArray();
		$deleted    = array_diff( $existingID, $updatedID, $notEditableID );

		// Remove link IDs from collection which do not exist in the update array, because they were removed.
		if ( 0 < count( $deleted ) ) {

			foreach ( $deleted as $id ) {

				// Only remove an link ID if the ID exists in the collection.
				if ( 0 !== $id && $this->exists( $id ) ) {

					$this->remove( $id );
				}
			}
		}

		/*
		 * If a preferred link ID was set, ensure the link ID set as preferred does not override a link ID the user
		 * may not have had permission to edit.
		 */
		if ( ! is_null( $preferred ) && $existingPreferred ) {

			if ( ! in_array( $existingPreferred->getVisibility(), Connections_Directory()->currentUser->canView() ) ) {

				$this->setPreferred( $existingPreferred->getID() );

				// Throw the user a message so they know why their choice was overridden.
				cnMessage::set( 'error', 'entry_preferred_overridden_link' );
			}
		}

		$this->applyFilter( 'cn_set_links' );

		// Sync the filtered and unfiltered collections.
		$this->resetFilters();
	}

	/**
	 * Save the collection to the database.
	 *
	 * @todo Instance of the link table should be passed as a parameter.
	 *
	 * @access public
	 * @since  8.19
	 */
	public function save() {

		/*
		 * @todo Instead of using the table constant, should use the get table name from the link table instance.
		 * @todo The default columns array should be returned from the link table instance.
		 */

		$cnDb = new cnEntry_DB( $this->id );

		$cnDb->upsert(
			CN_ENTRY_LINK_TABLE,
			array(
				'order'      => array( 'key' => 'order', 'format' => '%d' ),
				'preferred'  => array( 'key' => 'preferred', 'format' => '%d' ),
				'type'       => array( 'key' => 'type', 'format' => '%s' ),
				'title'      => array( 'key' => 'title', 'format' => '%s' ),
				'url'        => array( 'key' => 'url', 'format' => '%s' ),
				'target'     => array( 'key' => 'target', 'format' => '%s' ),
				'follow'     => array( 'key' => 'follow', 'format' => '%d' ),
				'image'      => array( 'key' => 'image', 'format' => '%d' ),
				'logo'       => array( 'key' => 'logo', 'format' => '%d' ),
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
	 * @since  8.19
	 */
	public function delete() {
	}

	/**
	 * Render the link collection.
	 *
	 * @access public
	 * @since  8.19
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
			'entry' . DIRECTORY_SEPARATOR . 'links' . DIRECTORY_SEPARATOR . 'link',
			$template,
			array_merge( array( 'links' => $this->getCollection() ), $atts ),
			$load,
			$buffer,
			$require_once
		);

		return $html;
	}

	/**
	 * Return the links as an instance of @see cnCollection.
	 *
	 * @access public
	 * @since  8.19
	 *
	 * @param int|null $limit The number of items that should be returned from the collection.
	 *
	 * @return cnCollection
	 */
	public function getCollection( $limit = NULL ) {

		$this->applyFilter( 'cn_link' )
		     ->applyFilter( 'cn_links' )
		     ->take( $limit );

		return $this->filtered->values();
	}

	/**
	 * Return the links collection as an indexed array where the link is an associative array.
	 *
	 * NOTE: This method primarily exists to support backwards compatibility in the
	 *       return value of @see cnEntry::getLinks().
	 *
	 * @access public
	 * @since  8.19
	 *
	 * @param int|null $limit The number of items that should be returned from the collection.
	 *
	 * @return array
	 */
	public function getCollectionAsArray( $limit = NULL ) {

		$this->applyFilter( 'cn_link' )
		     ->applyFilter( 'cn_links' )
		     ->take( $limit );

		return $this->filtered->values()->toArray();
	}

	/**
	 * NOTE: The results of these filters are reset when @see cnEntry_Links::resetFilters() is run.
	 *
	 * @access public
	 * @since  8.19
	 *
	 * @param string $filter The filter to be applied to the collection.
	 *
	 * @return static
	 */
	public function applyFilter( $filter ) {

		switch ( $filter ) {

			case 'cn_link':

				/**
				 * A link object.
				 *
				 * @since 8.19
				 *
				 * @param cnLink $link {
				 *     @type int    $id         The link as it was retrieved from the db.
				 *     @type string $type       The link type.
				 *     @type string $name       The link type name.
				 *     @type string $visibility The link visibility.
				 *     @type int    $order      The index order of the link.
				 *     @type bool   $preferred  Whether the link is the preferred link or not.
				 *     @type string $title      The link text title.
				 *     @type string $url        The link URL.
				 *     @type string $target     If the link should open in new tab/window or in the same tab/window.
				 *                              VALID: new|same
				 *     @type bool   $follow     Whether or not the link should be followed.
				 *     @type bool   $image      Whether or not the link is attached to the image (photo).
				 *     @type bool   $logo       Whether or not the link is attached to the logo.
				 * }
				 */
				$callback = create_function( '$item', 'return apply_filters( \'cn_link\', $item );' );
				break;

			case 'cn_links':

				/**
				 * An index array of link objects.
				 *
				 * @since 8.19
				 *
				 * @param array $results See the documentation for the `cn_link` filter for the params of each
				 *                       item in the links array.
				 */
				$this->filtered = apply_filters( 'cn_links', $this->filtered );
				break;

			case 'cn_set_link':

				$callback = create_function( '$item', 'return apply_filters( \'cn_set_link\', $item );' );
				break;

			case 'cn_set_links':

				$this->filtered = apply_filters( 'cn_set_links', $this->filtered );
				break;
		}

		if ( isset( $callback ) && 0 < $this->filtered->count() ) {

			$this->filtered->transform( $callback );
			//$this->items->transform( $callback );
		}

		return $this;
	}

	/**
	 * Get the link flagged as the "Preferred" link.
	 *
	 * @access public
	 * @since  8.19
	 *
	 * @return cnLink
	 */
	public function getPreferred() {

		return apply_filters( 'cn_link', $this->filtered->where( 'preferred', '===', TRUE )->first() );
	}

	/**
	 * NOTE: The results of these filters are reset when @see cnEntry_Links::resetFilters() is run.
	 *
	 * @access public
	 * @since  8.19
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

			// Only apply the preferred filter if the filter is TRUE so all links will be returned if FALSE.
			if ( $value ) $this->filtered = $this->filtered->where( 'preferred', '===', $value );

		} elseif ( 'image' === $field ) {

			cnFormatting::toBoolean( $value );

			// Only apply the preferred filter if the filter is TRUE so all links will be returned if FALSE.
			if ( $value ) $this->filtered = $this->filtered->where( 'image', '===', $value );

		} elseif ( 'logo' === $field ) {

			cnFormatting::toBoolean( $value );

			// Only apply the preferred filter if the filter is TRUE so all links will be returned if FALSE.
			if ( $value ) $this->filtered = $this->filtered->where( 'logo', '===', $value );

		} elseif ( 'visibility' === $field ) {

			$this->filtered = $this->filtered->whereIn( 'visibility', $value );
		}

		return $this;
	}

	/**
	 * Get links by entry ID from link table in the database.
	 *
	 * Returns all links associated to an entry as an instance of @see cnEntry_Links.
	 *
	 * @access public
	 * @since  8.19
	 * @static
	 *
	 * @param int   $id      The entry ID to create the link collection for.
	 * @param array $options The options used to perform the database query.
	 *
	 * @return static
	 */
	public static function createFromQuery( $id, $options = array() ) {

		$messengerIDs = new cnEntry_Links();

		$messengerIDs->setEntryID( $id )->query( $options );

		return $messengerIDs;
	}

	/**
	 * Get links associated to an entry from links table in the database.
	 *
	 * @access public
	 * @since  8.19
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
			 * This will reflect how it functions when the table manager and query classes are implemented.
			 */
			$data = Connections_Directory()->retrieve->links( $options, TRUE );
		}

		if ( empty( $data ) ) return $this;

		$this->fromArray( $data );

		return $this;
	}

	/**
	 * Create an instance of @see cnEntry_Links from an array of link data.
	 *
	 * @access public
	 * @since  8.19
	 * @static
	 *
	 * @param int   $id   The entry ID to create the link collection for.
	 * @param array $data The data used to create the collection with.
	 *
	 * @return static
	 */
	public static function createFromArray( $id, $data = array() ) {

		$messengerIDs = new cnEntry_Links();

		$messengerIDs->setEntryID( $id )->fromArray( $data );

		return $messengerIDs;
	}

	/**
	 * Populate @see cnEntry_Links() with data from an array of link data.
	 *
	 * @access public
	 * @since  8.19
	 *
	 * @param array $data The data used to create the collection with.
	 *
	 * @return static
	 */
	public function fromArray( $data = array() ) {

		$preferred     = NULL;
		$attachToLogo  = NULL;
		$attachToPhoto = NULL;

		/*
		 * The source of $data in Connections core will be from a form submission, object cache or the db.
		 * When the source is from the form submission the preferred item `key` is stored in the 'preferred' array key
		 * in the array of links. If the array key is set, save the array key value otherwise set it to NULL.
		 *
		 * If the `preferred` array key is set, the preferred link will be set based on the array key value.
		 * If it is not, the preferred link value will be retained using the `preferred` key within each link.
		 */
		if ( isset( $data['preferred'] ) ) {

			$preferred = $data['preferred'];
			unset( $data['preferred'] );
		}

		if ( isset( $data['logo'] ) ) {

			$attachToLogo = $data['logo'];
			unset( $data['logo'] );
		}

		if ( isset( $data['image'] ) ) {

			$attachToPhoto = $data['image'];
			unset( $data['image'] );
		}

		$collection = new cnCollection( $data );
		$order      = $collection->max('order');

		foreach ( $collection as $key => $link ) {

			if ( empty( $link ) ) continue;

			/*
			 * If the link URL is empty, no need to store it.
			 */
			if ( 0 >= strlen( $link['url'] ) ) continue;

			if ( ! isset( $link['order'] ) ) {

				$link['order'] = $order;
			}

			if ( ! is_null( $preferred ) ) {

				$link['preferred'] = $key == $preferred ? TRUE : FALSE;
			}

			if ( ! is_null( $attachToLogo ) ) {

				$link['logo'] = $key == $attachToLogo ? TRUE : FALSE;
			}

			if ( ! is_null( $attachToPhoto ) ) {

				$link['image'] = $key == $attachToPhoto ? TRUE : FALSE;
			}

			if ( ! is_bool( $link['follow'] ) ) {

				if ( $link['follow'] === 'dofollow' ) {

					$link['follow'] = TRUE;

				} elseif ( $link['follow'] === 'nofollow' ) {

					$link['follow'] = FALSE;

				} else {

					cnFormatting::toBoolean( $link['follow'] );
				}
			}

			/**
			 * Allow plugins to filter raw data before object is setup.
			 *
			 * @since 8.19
			 *
			 * @param array $link
			 */
			$link = apply_filters( 'cn_link-pre_setup', $link );

			//$this->add( cnMessenger::create( $messenger ) );
			$this->items->push( cnLink::create( $link ) );

			$order++;
		}

		// Sync the filtered and unfiltered collections.
		$this->resetFilters();

		return $this;
	}
}
