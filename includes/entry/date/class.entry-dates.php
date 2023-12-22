<?php

use Connections_Directory\Utility\_parse;

/**
 * Class cnEntry_Dates
 *
 * @since 8.22
 */
final class cnEntry_Dates extends cnEntry_Object_Collection {

	/**
	 * Get a date from the collection by its ID.
	 *
	 * @access public
	 * @since  8.22
	 *
	 * @param int $id The date to get from the collection.
	 *
	 * @return bool|cnEntry_Date
	 */
	public function get( $id ) {

		if ( false !== $key = $this->getItemKeyByID( $id ) ) {

			return apply_filters( 'cn_date', $this->items->get( $key ) );
		}

		return false;
	}

	/**
	 * Add a @see cnEntry_Date to the collection.
	 *
	 * @access public
	 * @since  8.22
	 *
	 * @param cnEntry_Collection_Item $date
	 */
	public function add( cnEntry_Collection_Item $date ) {

		//// Reset the filters just in case filters have been applied to the collection.
		//$this->resetFilters();

		$this->items->push( apply_filters( 'cn_set_date', $date ) );

		//// Reset the filters so both the filtered and unfiltered collections are the same after adding a date.
		//$this->resetFilters();
	}

	/**
	 * Update a date by ID.
	 *
	 * NOTE: This does not update only changed fields within the date object, it simply replaces the object with an
	 *       instance of the date object which contains the old and changed date information.
	 *
	 * @access public
	 * @since  8.22
	 *
	 * @param int          $id   The date ID to update.
	 * @param cnEntry_Date $date The updated date object used to replace the old date object in the collection.
	 *
	 * @return bool
	 */
	public function update( $id, $date ) {

		//// Reset the filters just in case filters have been applied to the collection.
		//$this->resetFilters();

		$key = $this->items->search(
			function ( $item ) use ( $id ) {
				/** @var cnEntry_Collection_Item $item */
				return absint( $id ) === $item->getID();
			}
		);

		if ( false !== $key ) {

			$this->items->put( $key, apply_filters( 'cn_set_date', $date ) );

			//// Reset the filters so both the filtered and unfiltered collections are the same after updating a date.
			//$this->resetFilters();

			return true;
		}

		return false;
	}

	/**
	 * NOTE: This method primarily exists to support backwards compatibility for @see cnEntry::setDates().
	 *
	 * @access public
	 * @since  8.22
	 *
	 * @param array $data The data used to update the collection with.
	 */
	public function updateFromArray( $data ) {

		$new               = cnEntry_Dates::createFromArray( $this->id, $data )->getCollection();
		$preferred         = isset( $data['preferred'] ) ? $data['preferred'] : null;
		$existingPreferred = $this->getPreferred();

		/** @var cnEntry_Date $date */
		foreach ( $new as $date ) {

			/*
			 * @todo: Before a date is added/updated a check needs to be done to see if the `preferred` flag has been
			 *        set. If it has been, the existing date needs to be checked for an existing preferred date
			 *        and whether that user has permission to edit that date first before changing the preferred date.
			 */

			// If exists, replace existing cnEntry_Date object with the new one.
			if ( 0 !== $date->getID() && $this->exists( $date->getID() ) ) {

				$this->update( $date->getID(), $date );

				// If a date has an ID of `0`, that means it has not yet been saved to the database, add it to the collection.
			} elseif ( 0 === $date->getID() ) {

				$this->add( $date );
			}
		}

		$notEditableID = $this->filterBy( 'visibility', Connections_Directory()->currentUser->canNotView() )
							  ->getCollection()
							  ->pluck( 'id' )
							  ->toArray();
		$existingID    = $this->items->pluck( 'id' )->toArray();
		$updatedID     = $new->pluck( 'id' )->toArray();
		$deleted       = array_diff( $existingID, $updatedID, $notEditableID );

		// Remove dates from collection which do not exist in the update array, because they were removed.
		if ( 0 < count( $deleted ) ) {

			foreach ( $deleted as $id ) {

				// Only remove a date if the ID exists in the collection.
				if ( 0 !== $id && $this->exists( $id ) ) {

					$this->remove( $id );
				}
			}
		}

		/*
		 * If a preferred date was set, ensure the date set as preferred does not override a date the user
		 * may not have had permission to edit.
		 */
		if ( ! is_null( $preferred ) && $existingPreferred ) {

			if ( ! in_array( $existingPreferred->getVisibility(), Connections_Directory()->currentUser->canView() ) ) {

				$this->setPreferred( $existingPreferred->getID() );

				// Throw the user a message so they know why their choice was overridden.
				cnMessage::set( 'error', 'entry_preferred_overridden_date' );
			}
		}

		$this->applyFilter( 'cn_set_dates' );

		// Sync the filtered and unfiltered collections.
		$this->resetFilters();
	}

	/**
	 * Save the collection to the database.
	 *
	 * @todo Instance of the date table should be passed as a parameter.
	 *
	 * @access public
	 * @since  8.22
	 */
	public function save() {

		/*
		 * @todo Instead of using the table constant, should use the get table name from the date table instance.
		 * @todo The default columns array should be returned from the date table instance.
		 */

		$cnDb = new cnEntry_DB( $this->id );

		$cnDb->upsert(
			CN_ENTRY_DATE_TABLE,
			array(
				'order'      => array( 'key' => 'order', 'format' => '%d' ),
				'preferred'  => array( 'key' => 'preferred', 'format' => '%d' ),
				'type'       => array( 'key' => 'type', 'format' => '%s' ),
				'date'       => array( 'key' => 'date', 'format' => '%s' ),
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
	 * @since  8.22
	 */
	public function delete() {
	}

	/**
	 * Render the date collection.
	 *
	 * @access public
	 * @since  8.22
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
			'entry' . DIRECTORY_SEPARATOR . 'dates' . DIRECTORY_SEPARATOR . 'date',
			$template,
			array_merge( array( 'dates' => $this->getCollection() ), $atts ),
			$load,
			$buffer,
			$require_once
		);

		return $html;
	}

	/**
	 * Return the dates as an instance of @see cnCollection.
	 *
	 * @access public
	 * @since  8.22
	 *
	 * @param int|null $limit The number of items that should be returned from the collection.
	 *
	 * @return cnCollection
	 */
	public function getCollection( $limit = null ) {

		$this->applyFilter( 'cn_date' )
			 ->applyFilter( 'cn_dates' )
			 ->take( $limit );

		return $this->filtered->values();
	}

	/**
	 * Return the date collection as an indexed array where the date is an associative array.
	 *
	 * NOTE: This method primarily exists to support backwards compatibility in the
	 *       return value of @see cnEntry::getDates().
	 *
	 * @access public
	 * @since  8.22
	 *
	 * @param int|null $limit The number of items that should be returned from the collection.
	 *
	 * @return array
	 */
	public function getCollectionAsArray( $limit = null ) {

		$this->applyFilter( 'cn_date' )
			 ->applyFilter( 'cn_dates' )
			 ->take( $limit );

		return $this->filtered->values()->toArray();
	}

	/**
	 * NOTE: The results of these filters are reset when @see cnEntry_Dates::resetFilters() is run.
	 *
	 * @access public
	 * @since  8.22
	 *
	 * @param string $filter The filter to be applied to the collection.
	 *
	 * @return static
	 */
	public function applyFilter( $filter ) {

		switch ( $filter ) {

			case 'cn_date':
				/**
				 * A date object.
				 *
				 * @since 8.22
				 *
				 * @param cnEntry_Date $date {
				 *     @type int    $id         The date ID as it was retrieved from the db.
				 *     @type bool   $preferred  Whether the date is the preferred date or not.
				 *     @type string $type       The date type.
				 *     @type string $date       The date.
				 *     @type string $visibility The date visibility.
				 * }
				 */
				$callback = function ( $item ) {
					return apply_filters( 'cn_date', $item );
				};
				break;

			case 'cn_dates':
				/**
				 * An index array of date objects.
				 *
				 * @since 8.22
				 *
				 * @param array $results See the documentation for the `cn_date` filter for the params of each
				 *                       item in the date array.
				 */
				$this->filtered = apply_filters( 'cn_dates', $this->filtered );
				break;

			case 'cn_set_date':
				$callback = function ( $item ) {
					return apply_filters( 'cn_set_date', $item );
				};
				break;

			case 'cn_set_dates':
				$this->filtered = apply_filters( 'cn_set_dates', $this->filtered );
				break;
		}

		if ( isset( $callback ) && 0 < $this->filtered->count() ) {

			$this->filtered->transform( $callback );
			// $this->items->transform( $callback );
		}

		return $this;
	}

	/**
	 * Get the date flagged as the "Preferred" date.
	 *
	 * @access public
	 * @since  8.22
	 *
	 * @return cnEntry_Date
	 */
	public function getPreferred() {

		return apply_filters( 'cn_date', $this->filtered->where( 'preferred', '===', true )->first() );
	}

	/**
	 * NOTE: The results of these filters are reset when @see cnEntry_Dates::resetFilters() is run.
	 *
	 * @access public
	 * @since  8.22
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

			// Only apply the preferred filter if the filter is TRUE so all dates will be returned if FALSE.
			if ( $value ) {
				$this->filtered = $this->filtered->where( 'preferred', '===', $value );
			}

		} elseif ( 'visibility' === $field ) {

			$this->filtered = $this->filtered->whereIn( 'visibility', $value );
		}

		return $this;
	}

	/**
	 * Get dates by entry ID from date table in the database.
	 *
	 * Returns all dates associated to an entry as an instance of @see cnEntry_Dates.
	 *
	 * @access public
	 * @since  8.22
	 * @static
	 *
	 * @param int   $id      The entry ID to create the date collection for.
	 * @param array $options The options used to perform the database query.
	 *
	 * @return static
	 */
	public static function createFromQuery( $id, $options = array() ) {

		$dates = new cnEntry_Dates();

		$dates->setEntryID( $id )->query( $options );

		return $dates;
	}

	/**
	 * Get dates associated to an entry from date table in the database.
	 *
	 * @access public
	 * @since  8.22
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
			$data = Connections_Directory()->retrieve->dates( $options, true );
		}

		if ( empty( $data ) ) {
			return $this;
		}

		$this->fromArray( $data );

		return $this;
	}

	/**
	 * Create an instance of @see cnEntry_Dates from an array of date data.
	 *
	 * @access public
	 * @since  8.22
	 * @static
	 *
	 * @param int   $id   The entry ID to create the date collection for.
	 * @param array $data The data used to create the collection with.
	 *
	 * @return static
	 */
	public static function createFromArray( $id, $data = array() ) {

		$dates = new cnEntry_Dates();

		$dates->setEntryID( $id )->fromArray( $data );

		return $dates;
	}

	/**
	 * Populate @see cnEntry_Dates() with data from an array of date data.
	 *
	 * @access public
	 * @since  8.22
	 *
	 * @param array $data The data used to create the collection with.
	 *
	 * @return static
	 */
	public function fromArray( $data = array() ) {

		/*
		 * The source of $data in Connections core will be from a form submission, object cache or the db.
		 * When the source is from the form submission the preferred item `key` is stored in the 'preferred' array key
		 * in the array of dates. If the array key is set, save the array key value otherwise set it to NULL.
		 *
		 * If the `preferred` array key is set, the date will be set based on the array key value.
		 * If it is not, the preferred date value will be retained using the `preferred` key within each date.
		 */
		$preferred  = cnArray::pull( $data, 'preferred', null );
		$collection = new cnCollection( $data );
		$order      = $collection->max( 'order' );

		foreach ( $collection as $key => $date ) {

			if ( empty( $date ) ) {
				continue;
			}

			/*
			 * If the date is empty, no need to store it.
			 */
			if ( 0 >= strlen( $date['date'] ) ) {
				continue;
			}

			if ( ! isset( $date['order'] ) ) {

				$date['order'] = $order;
			}

			if ( ! is_null( $preferred ) ) {

				$date['preferred'] = $key == $preferred ? true : false;
			}

			/**
			 * Allow plugins to filter raw data before object is setup.
			 *
			 * @since 8.22
			 *
			 * @param array $date
			 */
			$date = apply_filters( 'cn_date-pre_setup', $date );

			// $this->add( cnEntry_Date::create( $date ) );

			$item = cnEntry_Date::create( $date );

			if ( $item->getDate() instanceof DateTime ) {

				$this->items->push( $item );

				$order++;
			}
		}

		// Sync the filtered and unfiltered collections.
		$this->resetFilters();

		return $this;
	}
}
