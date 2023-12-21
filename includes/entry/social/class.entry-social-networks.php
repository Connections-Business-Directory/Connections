<?php

use Connections_Directory\Utility\_parse;

/**
 * Class cnEntry_Social_Networks
 *
 * @since 9.1
 */
final class cnEntry_Social_Networks extends cnEntry_Object_Collection {

	/**
	 * Get a social network from the collection by its ID.
	 *
	 * @access public
	 * @since  9.1
	 *
	 * @param int $id The social network to get from the collection.
	 *
	 * @return bool|cnEntry_Social_Network
	 */
	public function get( $id ) {

		if ( false !== $key = $this->getItemKeyByID( $id ) ) {

			return apply_filters( 'cn_social_network', $this->items->get( $key ) );
		}

		return false;
	}

	/**
	 * Add a @see cnEntry_Social_Network to the collection.
	 *
	 * @access public
	 * @since  9.1
	 *
	 * @param cnEntry_Collection_Item $network
	 */
	public function add( cnEntry_Collection_Item $network ) {

		//// Reset the filters just in case filters have been applied to the collection.
		//$this->resetFilters();

		$this->items->push( apply_filters( 'cn_set_social_network', $network ) );

		//// Reset the filters so both the filtered and unfiltered collections are the same after adding a social network.
		//$this->resetFilters();
	}

	/**
	 * Update by ID.
	 *
	 * NOTE: This does not update only the changed fields within the object, it simply replaces the object with an
	 *       instance of the object which contains the old and changed social network information.
	 *
	 * @access public
	 * @since  9.1
	 *
	 * @param int                    $id   The ID to update.
	 * @param cnEntry_Social_Network $network The updated object used to replace the old object in the collection.
	 *
	 * @return bool
	 */
	public function update( $id, $network ) {

		//// Reset the filters just in case filters have been applied to the collection.
		//$this->resetFilters();

		$key = $this->items->search(
			function ( $item ) use ( $id ) {
				/** @var cnEntry_Collection_Item $item */
				return absint( $id ) === $item->getID();
			}
		);

		if ( false !== $key ) {

			$this->items->put( $key, apply_filters( 'cn_set_social_network', $network ) );

			//// Reset the filters so both the filtered and unfiltered collections are the same after updating a social network.
			//$this->resetFilters();

			return true;
		}

		return false;
	}

	/**
	 * NOTE: This method primarily exists to support backwards compatibility for @see cnEntry::setSocialMedia().
	 *
	 * @access public
	 * @since  9.1
	 *
	 * @param array $data The data used to update the collection with.
	 */
	public function updateFromArray( $data ) {

		$new               = cnEntry_Social_Networks::createFromArray( $this->id, $data )->getCollection();
		$preferred         = isset( $data['preferred'] ) ? $data['preferred'] : null;
		$existingPreferred = $this->getPreferred();

		/** @var cnEntry_Social_Network $network */
		foreach ( $new as $network ) {

			/*
			 * @todo: Before a network is added/updated a check needs to be done to see if the `preferred` flag has been
			 *        set. If it has been, the existing network needs to be checked for an existing preferred network
			 *        and whether that user has permission to edit that network first before changing the preferred network.
			 */

			// If exists, replace existing cnEntry_Social_Networks object with the new one.
			if ( 0 !== $network->getID() && $this->exists( $network->getID() ) ) {

				$this->update( $network->getID(), $network );

				// If a network has an ID of `0`, that means it has not yet been saved to the database, add it to the collection.
			} elseif ( 0 === $network->getID() ) {

				$this->add( $network );
			}
		}

		$notEditableID = $this->filterBy( 'visibility', Connections_Directory()->currentUser->canNotView() )
							  ->getCollection()
							  ->pluck( 'id' )
							  ->toArray();
		$existingID    = $this->items->pluck( 'id' )->toArray();
		$updatedID     = $new->pluck( 'id' )->toArray();
		$deleted       = array_diff( $existingID, $updatedID, $notEditableID );

		// Remove networks from collection which do not exist in the update array, because they were removed.
		if ( 0 < count( $deleted ) ) {

			foreach ( $deleted as $id ) {

				// Only remove a network if the ID exists in the collection.
				if ( 0 !== $id && $this->exists( $id ) ) {

					$this->remove( $id );
				}
			}
		}

		/*
		 * If a preferred network was set, ensure the network set as preferred does not override a network the user
		 * may not have had permission to edit.
		 */
		if ( ! is_null( $preferred ) && $existingPreferred ) {

			if ( ! in_array( $existingPreferred->getVisibility(), Connections_Directory()->currentUser->canView() ) ) {

				$this->setPreferred( $existingPreferred->getID() );

				// Throw the user a message so they know why their choice was overridden.
				cnMessage::set( 'error', 'entry_preferred_overridden_social' );
			}
		}

		$this->applyFilter( 'cn_set_social_networks' );

		// Sync the filtered and unfiltered collections.
		$this->resetFilters();
	}

	/**
	 * Save the collection to the database.
	 *
	 * @todo Instance of the social network table should be passed as a parameter.
	 *
	 * @access public
	 * @since  9.1
	 */
	public function save() {

		/*
		 * @todo Instead of using the table constant, should use the get table name from the social network table instance.
		 * @todo The default columns array should be returned from the social network table instance.
		 */

		$cnDb = new cnEntry_DB( $this->id );

		$cnDb->upsert(
			CN_ENTRY_SOCIAL_TABLE,
			array(
				'order'      => array( 'key' => 'order', 'format' => '%d' ),
				'preferred'  => array( 'key' => 'preferred', 'format' => '%d' ),
				'type'       => array( 'key' => 'type', 'format' => '%s' ),
				'url'        => array( 'key' => 'url', 'format' => '%s' ),
				'visibility' => array( 'key' => 'visibility', 'format' => '%s' ),
			),
			$this->resetFilters()->escapeForSaving()->getCollectionAsObjects(),
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
	 * @since  9.1
	 */
	public function delete() {
	}

	/**
	 * Render the social network collection.
	 *
	 * @access public
	 * @since  9.1
	 *
	 * @param string $template     The template part name to load.
	 * @param array  $atts         An array of arguments that will be extract() if the template part is to be loaded.
	 * @param bool   $load         Whether or not to load the template.
	 * @param bool   $buffer       Whether or not to buffer the template output.
	 * @param bool   $require_once Whether or not to require() or require_once() the template part.
	 *
	 * @return string|bool The template path if not $load is FALSE.
	 *                     Output buffer if $buffer is TRUE or template path if $load is TRUE and $buffer is FALSE.
	 *                     Empty string will be returned when the filtered collection is empty.
	 */
	public function render( $template = 'hcard', $atts = array(), $load = true, $buffer = false, $require_once = false ) {

		if ( $this->filtered->isEmpty() ) {
			return '';
		}

		$html = cnTemplatePart::get(
			'entry' . DIRECTORY_SEPARATOR . 'social-networks' . DIRECTORY_SEPARATOR . 'social-networks',
			$template,
			array_merge( array( 'networks' => $this->getCollection() ), $atts ),
			$load,
			$buffer,
			$require_once
		);

		return $html;
	}

	/**
	 * Return the social networks as an instance of @see cnCollection.
	 *
	 * @access public
	 * @since  9.1
	 *
	 * @param int|null $limit The number of items that should be returned from the collection.
	 *
	 * @return cnCollection
	 */
	public function getCollection( $limit = null ) {

		$this->applyFilter( 'cn_social_network' )
			 ->applyFilter( 'cn_social_networks' )
			 ->take( $limit );

		return $this->filtered->values();
	}

	/**
	 * Return the social network collection as an indexed array where the social network is an associative array.
	 *
	 * NOTE: This method primarily exists to support backwards compatibility in the
	 *       return value of @see cnEntry::getSocialMedia().
	 *
	 * @access public
	 * @since  9.1
	 *
	 * @param int|null $limit The number of items that should be returned from the collection.
	 *
	 * @return array
	 */
	public function getCollectionAsArray( $limit = null ) {

		$this->applyFilter( 'cn_social_network' )
			 ->applyFilter( 'cn_social_networks' )
			 ->take( $limit );

		return $this->filtered->values()->toArray();
	}

	/**
	 * NOTE: The results of these filters are reset when @see cnEntry_Social_Networks::resetFilters() is run.
	 *
	 * @access public
	 * @since  9.1
	 *
	 * @param string $filter The filter to be applied to the collection.
	 *
	 * @return static
	 */
	public function applyFilter( $filter ) {

		switch ( $filter ) {

			case 'cn_social_network':
				/**
				 * A social network object.
				 *
				 * @since 9.1
				 *
				 * @param cnEntry_Social_Network $network {
				 *     @type int    $id         The network ID as it was retrieved from the db.
				 *     @type bool   $preferred  Whether the network is the preferred social network or not.
				 *     @type string $type       The network type.
				 *     @type string $url        The network URL.
				 *     @type string $visibility The network visibility.
				 * }
				 */
				$callback = function ( $item ) {
					return apply_filters( 'cn_social_network', $item );
				};
				break;

			case 'cn_social_networks':
				/**
				 * An index array of social network objects.
				 *
				 * @since 9.1
				 *
				 * @param array $results See the documentation for the `cn_social_network` filter for the params of each
				 *                       item in the social network array.
				 */
				$this->filtered = apply_filters( 'cn_social_networks', $this->filtered );
				break;

			case 'cn_set_social_network':
				$callback = function ( $item ) {
					return apply_filters( 'cn_set_social_network', $item );
				};
				break;

			case 'cn_set_social_networks':
				$this->filtered = apply_filters( 'cn_set_social_networks', $this->filtered );
				break;
		}

		if ( isset( $callback ) && 0 < $this->filtered->count() ) {

			$this->filtered->transform( $callback );
			// $this->items->transform( $callback );
		}

		return $this;
	}

	/**
	 * Get the social network flagged as the "Preferred" social network.
	 *
	 * @access public
	 * @since  9.1
	 *
	 * @return cnEntry_Social_Network
	 */
	public function getPreferred() {

		return apply_filters( 'cn_social_network', $this->filtered->where( 'preferred', '===', true )->first() );
	}

	/**
	 * NOTE: The results of these filters are reset when @see cnEntry_Social_Networks::resetFilters() is run.
	 *
	 * @access public
	 * @since  9.1
	 *
	 * @param string            $field The field which the collection should be filtered by.
	 * @param array|bool|string $value The values used to filter the collection by.
	 *
	 * @return static
	 */
	public function filterBy( $field, $value ) {

		if ( in_array( $field, array( 'type' ) ) ) {

			if ( ! empty( $value ) ) {

				_parse::stringList( $value );

				$this->filtered = $this->filtered->whereIn( $field, $value );
			}

		} elseif ( 'preferred' === $field ) {

			cnFormatting::toBoolean( $value );

			// Only apply the preferred filter if the filter is TRUE so all social networks will be returned if FALSE.
			if ( $value ) {
				$this->filtered = $this->filtered->where( 'preferred', '===', $value );
			}

		} elseif ( 'visibility' === $field ) {

			$this->filtered = $this->filtered->whereIn( 'visibility', $value );
		}

		return $this;
	}

	/**
	 * Get social networks by entry ID from social network table in the database.
	 *
	 * Returns all social networks associated to an entry as an instance of @see cnEntry_Social_Networks.
	 *
	 * @access public
	 * @since  9.1
	 * @static
	 *
	 * @param int   $id      The entry ID to create the social network collection for.
	 * @param array $options The options used to perform the database query.
	 *
	 * @return static
	 */
	public static function createFromQuery( $id, $options = array() ) {

		$networks = new cnEntry_Social_Networks();

		$networks->setEntryID( $id )->query( $options );

		return $networks;
	}

	/**
	 * Get social networks associated to an entry from social network table in the database.
	 *
	 * @access public
	 * @since  9.1
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
			 * This will reflect how it will function when the table manager and query classes are implemented.
			 */
			$data = Connections_Directory()->retrieve->socialMedia( $options, true );
		}

		if ( empty( $data ) ) {
			return $this;
		}

		$this->fromArray( $data );

		return $this;
	}

	/**
	 * Create an instance of @see cnEntry_Social_Networks from an array of social network data.
	 *
	 * @access public
	 * @since  9.1
	 * @static
	 *
	 * @param int   $id   The entry ID to create the social network collection for.
	 * @param array $data The data used to create the collection with.
	 *
	 * @return static
	 */
	public static function createFromArray( $id, $data = array() ) {

		$networks = new cnEntry_Social_Networks();

		$networks->setEntryID( $id )->fromArray( $data );

		return $networks;
	}

	/**
	 * Populate @see cnEntry_Social_Networks() with data from an array of social network data.
	 *
	 * @access public
	 * @since  9.1
	 *
	 * @param array $data The data used to create the collection with.
	 *
	 * @return static
	 */
	public function fromArray( $data = array() ) {

		/*
		 * The source of $data in Connections core will be from a form submission, object cache or the db.
		 * When the source is from the form submission the preferred item `key` is stored in the 'preferred' array key
		 * in the array of networks. If the array key is set, save the array key value otherwise set it to NULL.
		 *
		 * If the `preferred` array key is set, the network will be set based on the array key value.
		 * If it is not, the preferred social network value will be retained using the `preferred` key within each network.
		 */
		$preferred  = cnArray::pull( $data, 'preferred', null );
		$collection = new cnCollection( $data );
		$order      = $collection->max( 'order' );

		foreach ( $collection as $key => $network ) {

			if ( empty( $network ) ) {
				continue;
			}

			/*
			 * If the url is empty, no need to store it.
			 */
			if ( 0 >= strlen( $network['url'] ) ) {
				continue;
			}

			if ( ! isset( $network['order'] ) ) {

				$network['order'] = $order;
			}

			if ( ! is_null( $preferred ) ) {

				$network['preferred'] = $key == $preferred ? true : false;
			}

			/**
			 * Allow plugins to filter raw data before object is setup.
			 *
			 * @since 9.1
			 *
			 * @param array $network
			 */
			$network = apply_filters( 'cn_social_network-pre_setup', $network );

			// $this->add( cnEntry_Social_Network::create( $network ) );
			$this->items->push( cnEntry_Social_Network::create( $network ) );

			$order++;
		}

		// Sync the filtered and unfiltered collections.
		$this->resetFilters();

		return $this;
	}
}
