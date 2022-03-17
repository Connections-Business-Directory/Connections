<?php
/**
 * Current user object using the User API.
 *
 * @package     Connections
 * @subpackage  User
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       unknown
 */

use function Connections_Directory\Utility\_deprecated\_func as _deprecated_function;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class cnUser
 *
 * @phpcs:disable PEAR.NamingConventions.ValidClassName.Invalid
 * @phpcs:disable PEAR.NamingConventions.ValidClassName.StartWithCapital
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound
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
	 * @deprecated 10.4.17
	 * @see cnUser::getScreenOption()
	 *
	 * @return string
	 */
	public function getFilterEntryType() {

		_deprecated_function( __METHOD__, '10.4.17', 'cnUser::getScreenOption()' );

		$user_meta = get_user_meta( $this->ID, 'connections', true );

		if ( ! $user_meta == null && isset( $user_meta['filter']['entry_type'] ) ) {
			return $user_meta['filter']['entry_type'];
		} else {
			return 'all';
		}
	}

	/**
	 * @deprecated 10.4.17
	 * @see cnUser::setScreenOption()
	 *
	 * @param $entryType
	 *
	 * @return int|bool Meta ID if the key didn't exist, true on successful update, false on failure.
	 */
	public function setFilterEntryType( $entryType ) {

		_deprecated_function( __METHOD__, '10.4.17', 'cnUser::setScreenOption()' );

		$permittedEntryTypes = array( 'all', 'individual', 'organization', 'family' );
		$entryType           = esc_attr( $entryType );

		if ( ! in_array( $entryType, $permittedEntryTypes ) ) {
			return false;
		}

		$user_meta = get_user_meta( $this->ID, 'connections', true );

		if ( empty( $user_meta ) || ! is_array( $user_meta ) ) {
			$user_meta = array();
		}

		cnArray::set( $user_meta, 'filter.entry_type', $entryType );

		return update_user_meta( $this->ID, 'connections', $user_meta );

		// Reset the current user's admin manage page.
		// $this->resetFilterPage();
	}

	/**
	 * Returns the cached visibility filter setting as string or FALSE depending on if the current user has sufficient
	 * permission.
	 *
	 * @deprecated 10.4.17
	 * @see cnUser::getScreenOption()
	 *
	 * @return string|false
	 */
	public function getFilterVisibility() {

		_deprecated_function( __METHOD__, '10.4.17', 'cnUser::getScreenOption()' );

		$user_meta = get_user_meta( $this->ID, 'connections', true );

		if ( ! $user_meta == null && isset( $user_meta['filter']['visibility'] ) ) {
			/*
			 * Reset the user's cached visibility filter if they no longer have access.
			 */
			switch ( $user_meta['filter']['visibility'] ) {
				case 'public':
					if ( ! current_user_can( 'connections_view_public' ) ) {
						return false;
					} else {
						return isset( $user_meta['filter']['visibility'] ) ? $user_meta['filter']['visibility'] : false;
					}

				case 'private':
					if ( ! current_user_can( 'connections_view_private' ) ) {
						return false;
					} else {
						return isset( $user_meta['filter']['visibility'] ) ? $user_meta['filter']['visibility'] : false;
					}

				case 'unlisted':
					if ( ! current_user_can( 'connections_view_unlisted' ) ) {
						return false;
					} else {
						return isset( $user_meta['filter']['visibility'] ) ? $user_meta['filter']['visibility'] : false;
					}

				default:
					return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * @deprecated 10.4.17
	 * @see cnUser::setScreenOption()
	 *
	 * @param $visibility
	 *
	 * @return int|bool Meta ID if the key didn't exist, true on successful update, false on failure.
	 */
	public function setFilterVisibility( $visibility ) {

		_deprecated_function( __METHOD__, '10.4.17', 'cnUser::setScreenOption()' );

		$permittedVisibility = array( 'all', 'public', 'private', 'unlisted' );
		$visibility          = esc_attr( $visibility );

		if ( ! in_array( $visibility, $permittedVisibility ) ) {
			return false;
		}

		$user_meta = get_user_meta( $this->ID, 'connections', true );

		if ( empty( $user_meta ) || ! is_array( $user_meta ) ) {
			$user_meta = array();
		}

		cnArray::set( $user_meta, 'filter.visibility', $visibility );

		return update_user_meta( $this->ID, 'connections', $user_meta );

		// Reset the current user's admin manage page.
		// $this->resetFilterPage();
	}

	/**
	 * Returns the current set filter to be used to display the entries.
	 * The default is to return only the approved entries if not set.
	 *
	 * @deprecated 10.4.17
	 * @see cnUser::getScreenOption()
	 *
	 * @return string
	 */
	public function getFilterStatus() {

		_deprecated_function( __METHOD__, '10.4.17', 'cnUser::getScreenOption()' );

		$user_meta = get_user_meta( $this->ID, 'connections', true );

		if ( ! $user_meta == null && isset( $user_meta['filter']['status'] ) ) {
			return isset( $user_meta['filter']['status'] ) ? $user_meta['filter']['status'] : '';
		} else {
			return 'approved';
		}
	}

	/**
	 * @deprecated 10.4.17
	 * @see cnUser::setScreenOption()
	 *
	 * @param $status
	 *
	 * @return int|bool Meta ID if the key didn't exist, true on successful update, false on failure.
	 */
	public function setFilterStatus( $status ) {

		_deprecated_function( __METHOD__, '10.4.17', 'cnUser::setScreenOption()' );

		$permittedVisibility = array( 'all', 'approved', 'pending' );
		$status              = esc_attr( $status );

		if ( ! in_array( $status, $permittedVisibility ) ) {
			return false;
		}

		$user_meta = get_user_meta( $this->ID, 'connections', true );

		if ( empty( $user_meta ) || ! is_array( $user_meta ) ) {
			$user_meta = array();
		}

		cnArray::set( $user_meta, 'filter.status', $status );

		return update_user_meta( $this->ID, 'connections', $user_meta );

		// Reset the current user's admin manage page.
		// $this->resetFilterPage();
	}

	/**
	 * @deprecated 10.4.17
	 * @see cnUser::getScreenOption()
	 *
	 * @return string
	 */
	public function getFilterCategory() {

		_deprecated_function( __METHOD__, '10.4.17', 'cnUser::getScreenOption()' );

		$user_meta = get_user_meta( $this->ID, 'connections', true );

		if ( ! $user_meta == null && isset( $user_meta['filter'] ) ) {
			return isset( $user_meta['filter']['category'] ) ? $user_meta['filter']['category'] : '';
		} else {
			return '';
		}
	}

	/**
	 * @deprecated 10.4.17
	 * @see cnUser::setScreenOption()
	 *
	 * @param int $id
	 */
	public function setFilterCategory( $id ) {

		_deprecated_function( __METHOD__, '10.4.17', 'cnUser::setScreenOption()' );

		// If value is -1 from drop down, set to NULL.
		if ( 0 === $id ) {
			$id = 0;
		}

		$user_meta = get_user_meta( $this->ID, 'connections', true );

		if ( empty( $user_meta ) || ! is_array( $user_meta ) ) {
			$user_meta = array();
		}

		cnArray::set( $user_meta, 'filter.category', $id );

		update_user_meta( $this->ID, 'connections', $user_meta );

		// Reset the current user's admin manage page.
		// $this->resetFilterPage();
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

		_deprecated_function( __METHOD__, '9.15', 'cnUser::getScreenOption()' );

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

		_deprecated_function( __METHOD__, '9.15', 'cnUser::setScreenOption()' );

		// If the page name has not been supplied, no need to process further.
		if ( ! isset( $page->name ) ) {
			return false;
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

		$user_meta = get_user_meta( $this->ID, 'connections', true );

		if ( ! $user_meta == null && isset( $user_meta['ui']['category_div_height'] ) ) {

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

		if ( ! is_int( $height ) ) {
			return false;
		}

		$user_meta = get_user_meta( $this->ID, 'connections', true );

		if ( empty( $user_meta ) || ! is_array( $user_meta ) ) {
			$user_meta = array();
		}

		cnArray::set( $user_meta, 'ui.category_div_height', absint( apply_filters( 'cn_admin_ui_category_div_height', $height ) ) );

		return update_user_meta( $this->ID, 'connections', $user_meta );
	}

	/**
	 * Reset the current page.
	 *
	 * @deprecated 10.4.17
	 * @see cnUser::setScreenOption()
	 *
	 * @param string $pageName The screen ID/slug.
	 */
	public function resetFilterPage( $pageName ) {

		_deprecated_function( __METHOD__, '10.4.17', 'cnUser::setScreenOption()' );

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
	 * @param string $screen  The screen ID/slug.
	 * @param string $option  Screen option value. Must be serializable if non-scalar.
	 * @param mixed  $default The default value to return if option is not set.
	 *
	 * @return mixed
	 */
	public function getScreenOption( $screen, $option, $default = null ) {

		return cnArray::get( $this->getScreenOptions( $screen ), $option, $default );
	}

	/**
	 * Set a specific option for a specific admin page.
	 *
	 * @access public
	 * @since  8.13
	 *
	 * @param string $screen The screen ID/slug.
	 * @param string $key    The option ID/slug.
	 * @param mixed  $value  Screen option value. Must be serializable if non-scalar.
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
	 * @param string $screen   The screen ID/slug.
	 * @param array  $defaults The default value to return if option is not set.
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
	 * @param string $screen  The screen ID/slug.
	 * @param array  $options Screen option values. Must be serializable if non-scalar.
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

		$meta = get_user_meta( $this->getID(), 'connections', true );

		/*
		 * Since get_user_meta() can return array|string|false, but we expect only an array,
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
	 * @param array $meta Metadata value. Must be serializable if non-scalar.
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

		_deprecated_function( __METHOD__, '9.15' );

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

			if ( current_user_can( 'connections_view_private' ) ) {
				$visibility[] = 'private';
			}

			if ( current_user_can( 'connections_view_unlisted' ) &&
				 ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) ) {

				$visibility[] = 'unlisted';
			}

		} else {

			// Display the 'public' entries if the user is not required to be logged in.
			if ( ! cnOptions::loginRequired() ) {
				$visibility[] = 'public';
			}

			if ( Connections_Directory()->options->getAllowPublicOverride() ) {
				$visibility[] = 'public';
			}

			if ( Connections_Directory()->options->getAllowPrivateOverride() ) {
				$visibility[] = 'private';
			}
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

			return false;
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
					return false;
			}

		} else {

			// Unlisted entries are not shown on the frontend.
			if ( 'unlisted' == $visibility ) {

				return false;
			}

			if ( cnOptions::loginRequired() ) {

				switch ( $visibility ) {

					case 'public':
						return Connections_Directory()->options->getAllowPublicOverride();

					case 'private':
						return Connections_Directory()->options->getAllowPrivateOverride();

					default:
						return false;
				}

			} else {

				if ( 'public' == $visibility ) {
					return true;
				}
			}

			// If we get here, return FALSE.
			return false;
		}
	}
}
