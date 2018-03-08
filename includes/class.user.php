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

		if ( empty( $user_meta ) || ! is_array( $user_meta ) ) $user_meta = array();

		cnArray::set( $user_meta,'filter.entry_type', $entryType );

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

		if ( empty( $user_meta ) || ! is_array( $user_meta ) ) $user_meta = array();

		cnArray::set( $user_meta, 'filter.visibility', $visibility );

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

		if ( empty( $user_meta ) || ! is_array( $user_meta ) ) $user_meta = array();

		cnArray::set( $user_meta,'filter.status', $status );

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

		if ( empty( $user_meta ) || ! is_array( $user_meta ) ) $user_meta = array();

		cnArray::set( $user_meta,'filter.category', $id );

		update_user_meta( $this->ID, 'connections', $user_meta );

		// Reset the current user's admin manage page.
		//$this->resetFilterPage();
	}

	/**
	 * Returns the current page and page limit of the supplied page name.
	 *
	 * @access public
	 * @since  unknown
	 * @deprecated 8.13 Use cnUser::getScreenOption()
	 * @see cnUser::getScreenOption()
	 *
	 * @param string $pageName
	 *
	 * @return object
	 */
	public function getFilterPage( $pageName ) {

		$meta = $this->getScreenOption( 'manage', 'pagination', array( 'current' => 1, 'limit' => 50 ) );

		return (object) $meta;
	}

	/**
	 * @access public
	 * @since  unknown
	 * @deprecated 8.13 Use @see cnUser::setScreenOption()
	 * @see cnUser::setScreenOption()
	 *
	 * @param object $page
	 *
	 * @return bool|int
	 */
	public function setFilterPage( $page ) {

		// If the page name has not been supplied, no need to process further.
		if ( ! isset( $page->name ) ) {
			return FALSE;
		}

		$screen = sanitize_title( $page->name );
		$meta   = $this->getScreenOption( $screen, 'pagination', array( 'current' => 1, 'limit' => 50 ) );

		if ( isset( $page->current ) ) {

			cnArray::set( $meta, 'current', absint( $page->current ) );
		}

		if ( isset( $page->limit ) ) {

			cnArray::set( $meta, 'limit', absint( $page->limit ) );
		}

		return $this->setScreenOption( $page->name, 'pagination', $meta );
	}

	/**
	 * Get the current user's saved height for the category metabox.
	 *
	 * @access private
	 * @since  8.6.5
	 *
	 * @return int
	 */
	public function getCategoryDivHeight() {

		$user_meta = get_user_meta( $this->ID, 'connections', TRUE );

		if ( ! $user_meta == NULL && isset( $user_meta['ui']['category_div_height'] ) ) {

			$height = $user_meta['ui']['category_div_height'];

		} else {

			$height = 200;
		}

		return absint( apply_filters( 'cn_admin_ui_category_div_height', $height ) );
	}

	/**
	 * Set the current user's height for the category metabox.
	 *
	 * @access private
	 * @since  8.6.5
	 *
	 * @param int $height
	 *
	 * @return bool|int
	 */
	public function setCategoryDivHeight( $height ) {

		if ( ! is_int( $height ) ) return FALSE;

		$user_meta = get_user_meta( $this->ID, 'connections', TRUE );

		if ( empty( $user_meta ) || ! is_array( $user_meta ) ) $user_meta = array();

		//$user_meta['ui']['category_div_height'] = absint( apply_filters( 'cn_admin_ui_category_div_height', $height ) );
		cnArray::set( $user_meta, 'ui.category_div_height', absint( apply_filters( 'cn_admin_ui_category_div_height', $height ) ) );

		return update_user_meta( $this->ID, 'connections', $user_meta );
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
	 * Get a specific option for a specific admin page.
	 *
	 * @access public
	 * @since  8.13
	 *
	 * @param string $screen
	 * @param string $option
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	public function getScreenOption( $screen, $option, $default = NULL ) {

		return cnArray::get( $this->getScreenOptions( $screen ), $option, $default );
	}

	/**
	 * Set a specific option for a specific admin page.
	 *
	 * @access public
	 * @since  8.13
	 *
	 * @param string $screen
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return bool|int
	 */
	public function setScreenOption( $screen, $key, $value ) {

		$options = $this->getScreenOptions( $screen );

		cnArray::set( $options, $key, $value );

		return $this->setScreenOptions( $screen, $options );
	}

	/**
	 * Get all screen options for a specific admin page.
	 *
	 * @access public
	 * @since  8.13
	 *
	 * @param string $screen
	 * @param array  $defaults
	 *
	 * @return array
	 */
	public function getScreenOptions( $screen, $defaults = array() ) {

		return cnArray::get( $this->getMeta(), "screen.{$screen}", $defaults );
	}

	/**
	 * Set screen options for a specific admin page.
	 *
	 * @access public
	 * @since  8.13
	 *
	 * @param string $screen
	 * @param array  $options
	 *
	 * @return bool|int
	 */
	public function setScreenOptions( $screen, $options ) {

		$meta = $this->getMeta();

		$current = cnArray::get( $meta, "screen.{$screen}", array() );
		$options = array_replace_recursive( $current, $options );

		cnArray::set( $meta, "screen.{$screen}", $options );

		return $this->setMeta( $meta );
	}

	/**
	 * Get the current user's meta.
	 *
	 * @access public
	 * @since  8.13
	 *
	 * @return array
	 */
	public function getMeta() {

		$meta = get_user_meta( $this->getID(), 'connections', TRUE );

		/*
		 * Since get_user_meta() can return array|string|false but we expect only an array,
		 * check $meta and set it to an array if it is not.
		 */
		if ( ! is_array( $meta ) ) {

			$meta = array();
		}

		return $meta;
	}

	/**
	 * Set the current user's meta.
	 *
	 * @access public
	 * @since  8.13
	 *
	 * @param array $meta
	 *
	 * @return bool|int
	 */
	public function setMeta( $meta ) {

		return update_user_meta( $this->getID(), 'connections', $meta );
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

	/**
	 * @return array
	 */
	public function canView() {

		/*
		 * @todo The visibility status the current user can view needs to be abstracted out
		 * since it can be used in many places throughout the plugin.
		 *
		 * NOTE: Context will need to be taken into account so all entries will be saved regardless of user's
		 * view capability.
		 */
		$visibility = array();

		if ( is_user_logged_in() ) {

			if ( current_user_can( 'connections_view_public' ) || ! cnOptions::loginRequired() ) {

				$visibility[] = 'public';
			}

			if ( current_user_can( 'connections_view_private' ) ) $visibility[] = 'private';

			if ( current_user_can( 'connections_view_unlisted' ) &&
			     ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) ) {

				$visibility[] = 'unlisted';
			}

		} else {

			// Display the 'public' entries if the user is not required to be logged in.
			if ( ! cnOptions::loginRequired() ) $visibility[] = 'public';
		}

		return $visibility;
	}

	/**
	 * @return array
	 */
	public function canNotView() {

		return array_diff( array( 'public', 'private', 'unlisted' ), $this->canView() );
	}

	/**
	 * Will return TRUE|FALSE based on supplied visibility status and the current user view capabilities.
	 *
	 * @access public
	 * @since  8.6
	 *
	 * @param string $visibility
	 *
	 * @return bool
	 */
	public function canViewVisibility( $visibility ) {

		// Ensure a valid option for $visibility.
		if ( ! in_array( $visibility, array( 'public', 'private', 'unlisted' ) ) ) {

			return FALSE;
		}

		if ( is_user_logged_in() ) {

			switch ( $visibility ) {

				case 'public':

					return ( current_user_can( 'connections_view_public' ) || ! cnOptions::loginRequired() );

				case 'private':

					return current_user_can( 'connections_view_private' );

				case 'unlisted':

					return ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) && current_user_can( 'connections_view_unlisted' );

				default:

					return FALSE;
			}

		} else {

			// Unlisted entries are not shown on the frontend.
			if ( 'unlisted' == $visibility ) {

				return FALSE;
			}

			if ( cnOptions::loginRequired() ) {

				switch ( $visibility ) {

					case 'public':

						return Connections_Directory()->options->getAllowPublicOverride();

					case 'private':

						return Connections_Directory()->options->getAllowPrivateOverride();

					default:

						return FALSE;
				}

			} else {

				if ( 'public' == $visibility ) return TRUE;
			}

			// If we get here, return FALSE
			return FALSE;
		}
	}
}
