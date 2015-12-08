<?php

/**
 * Current user object using the User API..
 *
 * @package     Connections
 * @subpackage  User
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       unknown
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class cnUser
 */
class cnUser {

	/**
	 * Integer: stores the current WP user ID
	 *
	 * @var int
	 */
	private $ID;

	/**
	 *
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'setID' ) );
	}

	/**
	 * @return int
	 */
	public function getID() {

		return get_current_user_id();
	}

	/**
	 *
	 */
	public function setID() {

		$this->ID = get_current_user_id();
	}

	/**
	 * @return string
	 */
	public function getFilterEntryType() {

		$user_meta = get_user_meta( $this->ID, 'connections', TRUE );

		if ( ! $user_meta == NULL && isset( $user_meta['filter']['entry_type'] ) ) {
			return $user_meta['filter']['entry_type'];
		} else {
			return 'all';
		}
	}

	/**
	 * @param $entryType
	 *
	 * @return int|bool Meta ID if the key didn't exist, true on successful update, false on failure.
	 */
	public function setFilterEntryType( $entryType ) {

		$permittedEntryTypes = array( 'all', 'individual', 'organization', 'family' );
		$entryType           = esc_attr( $entryType );

		if ( ! in_array( $entryType, $permittedEntryTypes ) ) {
			return FALSE;
		}

		$user_meta = get_user_meta( $this->ID, 'connections', TRUE );

		$user_meta['filter']['entry_type'] = $entryType;

		return update_user_meta( $this->ID, 'connections', $user_meta );

		// Reset the current user's admin manage page.
		//$this->resetFilterPage();
	}

	/**
	 * Returns the cached visibility filter setting as string or FALSE depending if the current user has sufficient
	 * permission.
	 *
	 * @return mixed string|bool
	 */
	public function getFilterVisibility() {

		$user_meta = get_user_meta( $this->ID, 'connections', TRUE );

		if ( ! $user_meta == NULL && isset( $user_meta['filter']['visibility'] ) ) {
			/*
			 * Reset the user's cached visibility filter if they no longer have access.
			 */
			switch ( $user_meta['filter']['visibility'] ) {
				case 'public':
					if ( ! current_user_can( 'connections_view_public' ) ) {
						return FALSE;
					} else {
						return isset( $user_meta['filter']['visibility'] ) ? $user_meta['filter']['visibility'] : FALSE;
					}
					break;

				case 'private':
					if ( ! current_user_can( 'connections_view_private' ) ) {
						return FALSE;
					} else {
						return isset( $user_meta['filter']['visibility'] ) ? $user_meta['filter']['visibility'] : FALSE;
					}
					break;

				case 'unlisted':
					if ( ! current_user_can( 'connections_view_unlisted' ) ) {
						return FALSE;
					} else {
						return isset( $user_meta['filter']['visibility'] ) ? $user_meta['filter']['visibility'] : FALSE;
					}
					break;

				default:
					return FALSE;
					break;
			}
		} else {
			return FALSE;
		}
	}

	/**
	 * @param $visibility
	 *
	 * @return int|bool Meta ID if the key didn't exist, true on successful update, false on failure.
	 */
	public function setFilterVisibility( $visibility ) {

		$permittedVisibility = array( 'all', 'public', 'private', 'unlisted' );
		$visibility          = esc_attr( $visibility );

		if ( ! in_array( $visibility, $permittedVisibility ) ) {
			return FALSE;
		}

		$user_meta = get_user_meta( $this->ID, 'connections', TRUE );

		$user_meta['filter']['visibility'] = $visibility;

		return update_user_meta( $this->ID, 'connections', $user_meta );

		// Reset the current user's admin manage page.
		//$this->resetFilterPage();
	}

	/**
	 * Returns the current set filter to be used to display the entries.
	 * The default is to return only the approved entries if not set.
	 *
	 * @return string
	 */
	public function getFilterStatus() {

		$user_meta = get_user_meta( $this->ID, 'connections', TRUE );

		if ( ! $user_meta == NULL && isset( $user_meta['filter']['status'] ) ) {
			return isset( $user_meta['filter']['status'] ) ? $user_meta['filter']['status'] : '';
		} else {
			return 'approved';
		}
	}

	/**
	 * @param $status
	 *
	 * @return int|bool Meta ID if the key didn't exist, true on successful update, false on failure.
	 */
	public function setFilterStatus( $status ) {

		$permittedVisibility = array( 'all', 'approved', 'pending' );
		$status              = esc_attr( $status );

		if ( ! in_array( $status, $permittedVisibility ) ) {
			return FALSE;
		}

		$user_meta = get_user_meta( $this->ID, 'connections', TRUE );

		$user_meta['filter']['status'] = $status;

		return update_user_meta( $this->ID, 'connections', $user_meta );

		// Reset the current user's admin manage page.
		//$this->resetFilterPage();
	}

	/**
	 * @return string
	 */
	public function getFilterCategory() {

		$user_meta = get_user_meta( $this->ID, 'connections', TRUE );

		if ( ! $user_meta == NULL && isset( $user_meta['filter'] ) ) {
			return isset( $user_meta['filter']['category'] ) ? $user_meta['filter']['category'] : '';
		} else {
			return '';
		}
	}

	/**
	 * @param int $id
	 */
	public function setFilterCategory( $id ) {

		// If value is -1 from drop down, set to NULL
		if ( $id === 0 ) {
			$id = 0;
		}

		$user_meta = get_user_meta( $this->ID, 'connections', TRUE );

		$user_meta['filter']['category'] = $id;

		update_user_meta( $this->ID, 'connections', $user_meta );

		// Reset the current user's admin manage page.
		//$this->resetFilterPage();
	}

	/**
	 * Returns the current page and page limit of the supplied page name.
	 *
	 * @param string $pageName
	 *
	 * @return object
	 */
	public function getFilterPage( $pageName ) {

		$user_meta = get_user_meta( $this->ID, 'connections', TRUE );

		if ( ! $user_meta == NULL && isset( $user_meta['filter'][ $pageName ] ) ) {
			$page = (object) $user_meta['filter'][ $pageName ];

			if ( ! isset( $page->limit ) || empty( $page->limit ) ) {
				$page->limit = 50;
			}
			if ( ! isset( $page->current ) || empty( $page->current ) ) {
				$page->current = 1;
			}

			return $page;
		} else {
			$page = new stdClass();

			$page->limit   = 50;
			$page->current = 1;

			return $page;
		}
	}

	/**
	 * @param object $page
	 */
	public function setFilterPage( $page ) {

		// If the page name has not been supplied, no need to process further.
		if ( ! isset( $page->name ) ) {
			return;
		}

		$page->name = sanitize_title( $page->name );

		if ( isset( $page->current ) ) {
			$page->current = absint( $page->current );
		}
		if ( isset( $page->limit ) ) {
			$page->limit = absint( $page->limit );
		}

		$user_meta = get_user_meta( $this->ID, 'connections', TRUE );

		if ( isset( $page->current ) ) {
			$user_meta['filter'][ $page->name ]['current'] = $page->current;
		}
		if ( isset( $page->limit ) ) {
			$user_meta['filter'][ $page->name ]['limit'] = $page->limit;
		}

		update_user_meta( $this->ID, 'connections', $user_meta );
	}

	/**
	 * @param $pageName
	 */
	public function resetFilterPage( $pageName ) {

		$page = $this->getFilterPage( $pageName );

		$page->name    = $pageName;
		$page->current = 1;

		$this->setFilterPage( $page );
	}

	/**
	 * Reset any messages stored in the user's meta.
	 * This is a deprecated helper function left in place until all instances of it are removed from the code base.
	 *
	 * @access     public
	 * @since      unknown
	 * @deprecated 0.7.6
	 */
	public function resetMessages() {

		cnMessage::reset();
	}
}
