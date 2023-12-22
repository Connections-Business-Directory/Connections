<?php
/**
 * Class for admin related functions.
 *
 * @package     Connections
 * @subpackage  Admin Functions
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.7.9
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Connections_Directory\Form\Field;

/**
 * Class cnAdminFunction
 */
class cnAdminFunction {

	/**
	 * Stores the instance of this class.
	 *
	 * @since 0.7.9
	 * @var cnAdminFunction
	 */
	private static $instance;

	/**
	 * A dummy constructor to prevent the class from being loaded more than once.
	 *
	 * @since 0.7.9
	 */
	public function __construct() { /* Do nothing here */ }

	/**
	 * Callback for the `admin_init` action.
	 *
	 * Set up the class, if it has already been initialized, return the initialized instance.
	 *
	 * @see cnAdminFunction()
	 *
	 * @since  0.7.9
	 */
	public static function init() {

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		if ( ! isset( self::$instance ) ) {

			self::$instance = new self();

			// Initiate admin messages.
			cnMessage::init();

			// Initiate admin actions.
			cnAdminActions::init();

			// If the user changed the base slugs for the permalinks, flush the rewrite rules.
			if ( get_option( 'connections_flush_rewrite' ) ) {

				flush_rewrite_rules(); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.flush_rewrite_rules_flush_rewrite_rules
				delete_option( 'connections_flush_rewrite' );
			}

			/*
			 * If the home page has not been set, nag the user to set it.
			 */
			$directoryHome = $instance->settings->get( 'connections', 'connections_home_page', 'page_id' );

			if ( ! $directoryHome && current_user_can( 'manage_options' ) ) {
				cnMessage::create( 'notice', 'home_page_set_failed' );
			}

			// Check if the db requires updating, display message if it does.
			if ( version_compare( $instance->options->getDBVersion(), CN_DB_VERSION, '<' ) ) {

				$instance->dbUpgrade = true;

				add_action( 'current_screen', array( __CLASS__, 'displayDBUpgradeNotice' ) );
			}

			/*
			 * Add admin notices if required directories are not present or not writeable.
			 */
			if ( ! file_exists( CN_IMAGE_PATH ) ) {
				cnMessage::create( 'notice', 'image_path_exists_failed' );
			}

			if ( file_exists( CN_IMAGE_PATH ) && ! is_writeable( CN_IMAGE_PATH ) ) {
				cnMessage::create( 'notice', 'image_path_writeable_failed' );
			}

			// Add the screen layout filter.
			add_filter( 'screen_layout_columns', array( __CLASS__, 'screenLayout' ), 10, 2 );

			/*
			 * Set priority `9` so this is run before the `current_screen` filter in the
			 * Screen Options class by Janis Elsts which registers the screen options panels.
			 */
			add_action( 'current_screen', array( __CLASS__, 'screenOptionsPanel' ), 9 );
		}
	}

	/**
	 * Callback for the `current_screen` action.
	 *
	 * Display the database upgrade notice. This will only be shown on non-Connections pages.
	 *
	 * @internal
	 * @since  0.7.5
	 */
	public static function displayDBUpgradeNotice() {

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		$screen = get_current_screen();

		if ( ! in_array( $screen->id, (array) $instance->pageHook ) ) {

			cnMessage::create( 'notice', 'db_update_required' );
		}
	}

	/**
	 * Callback for the `screen_layout_columns` filter.
	 *
	 * Register the number of columns permitted for metabox use on the edit entry page.
	 *
	 * @internal
	 * @since 0.7.1.3
	 *
	 * @param array  $columns Screen ID as key with number of columns as the value.
	 * @param string $screen  The screen ID.
	 *
	 * @return array
	 */
	public static function screenLayout( $columns, $screen ) {

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		/*
		 * The Screen Layout options in the Screen Options tab only needs to be added on the manage page if performing an action to an entry.
		 * This is to prevent the Screen Layout options in the Screen Options tab from being displayed on the Manage
		 * admin page when viewing the manage entries table.
		 */
		if ( $screen == $instance->pageHook->manage && ! isset( $_GET['cn-action'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return $columns;
		}

		$columns[ $instance->pageHook->dashboard ] = 2;
		$columns[ $instance->pageHook->manage ]    = 2;
		$columns[ $instance->pageHook->add ]       = 2;

		return $columns;
	}

	/**
	 * Callback for the `current_screen` action.
	 *
	 * Adds the "Show on screen" option to limit number of entries per page on the Connections : Manage admin page.
	 *
	 * @internal
	 * @since 0.8.14
	 *
	 * @param WP_Screen $screen An instance of the WordPress screen object.
	 */
	public static function screenOptionsPanel( $screen ) {

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		/*
		 * In instances such as WP AJAX requests the add_menu() and add_sub_menu() functions are
		 * not run in the admin_menu action, so the properties would not exist and will throw
		 * PHP notices when attempting to access them. If the menus have been added then the
		 * properties will exist so it will be safe to add the actions using the properties.
		 */
		if ( get_object_vars( $instance->pageHook ) && current_user_can( 'connections_view_menu' ) ) {

			/*
			 * The Screen Layout options in the Screen Option tab only needs to be added on the manage page when NOT performing an action to an entry.
			 * This is to prevent the Screen Layout options in the Screen Option tab on the Manage
			 * admin page when performing an action on an entry.
			 */
			if ( $screen->id == $instance->pageHook->manage && ! isset( $_GET['cn-action'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended

				/*
				 * Include the Screen Options class by Janis Elsts
				 * http://w-shadow.com/blog/2010/06/29/adding-stuff-to-wordpress-screen-options/
				 */
				include_once CN_PATH . 'includes/Libraries/screen-options/screen-options.php';

				/*
				 * Add the panel to the "Screen Options" box to the manage page.
				 * NOTE: This relies on the Screen Options class by Janis Elsts.
				 */
				add_screen_options_panel(
					'cn-manage-page-limit',
					_x( 'Show on screen', 'The number of entries to display on the Manage admin page.', 'connections' ),
					array( __CLASS__, 'managePageLimit' ),
					$instance->pageHook->manage
				);

				add_screen_options_panel(
					'cn-manage-image',
					_x( 'Choose Thumbnail to display:', 'The entry image to display on the Manage admin page.', 'connections' ),
					array( __CLASS__, 'manageImageThumbnail' ),
					$instance->pageHook->manage
				);
			}

		}
	}

	/**
	 * Add the page limit panel to the screen options of the manage page.
	 *
	 * NOTE: This relies on the Screen Options class by Janis Elsts.
	 *
	 * @internal
	 * @since unknown
	 *
	 * @return string
	 */
	public static function managePageLimit() {

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		$page = $instance->user->getScreenOption( 'manage', 'pagination' );
		$page = wp_parse_args(
			$page,
			array(
				'current' => 1,
				'limit'   => 50,
			)
		);

		$out  = '<label><input type="number" step="1" min="1" max="999" class="screen-per-page" name="wp_screen_options[value]" id="entries_per_page" maxlength="3" value="' . esc_attr( $page['limit'] ) . '" />' . esc_html__( 'Entries', 'connections' ) . '</label>';
		$out .= '<input type="hidden" name="wp_screen_options[option]" id="edit_entry_per_page_name" value="connections" />';
		$out .= '<input type="submit" name="screen-options-apply" id="entry-per-page-apply" class="button" value="Apply"  />';

		return $out;
	}

	/**
	 * Add the option to the Screen Options tab to display either the entry logo or photo.
	 *
	 * NOTE: This relies on the Screen Options class by Janis Elsts.
	 *
	 * @internal
	 * @since 8.13
	 *
	 * @return string
	 */
	public static function manageImageThumbnail() {

		$html = '';

		$options = array(
			array(
				'label' => __( 'Logo', 'connections' ),
				'value' => 'logo',
			),
			array(
				'label' => __( 'Photo', 'connections' ),
				'value' => 'photo',
			),
		);

		$value = Connections_Directory()->user->getScreenOption( 'manage', 'thumbnail', 'photo' );

		$html .= Field\Radio_Group::create()
								  ->setId( 'wp_screen_options[image_thumbnail]' )
								  ->addClass( 'radio' )
								  ->setName( 'wp_screen_options[image_thumbnail]' )
								  ->createInputsFromArray( $options )
								  ->setValue( $value )
								  ->setContainer( 'span' )
								  ->getHTML();

		$html .= '<input type="submit" name="screen-options-apply" id="entry-image-thumbnail-apply" class="button" value="Apply" />';

		return $html;
	}

	/**
	 * Save the user setting for the page limit on the screen options of the manage page.
	 * NOTE: This is only run during the AJAX callback which is currently disabled.
	 * NOTE: This relies on the Screen Options class by Janis Elsts.
	 *
	 * @internal
	 * @since unknown
	 */
	public static function managePageLimitSaveAJAX() {

		// include_once CN_PATH . 'includes/admin/inc.processes.php';
		// processSetUserFilter();
	}

	/**
	 * Callback for the `set_screen_option_connections` filter.
	 *
	 * Save the user entered value for display n-number of entries and image thumbnail on the Manage admin page.
	 *
	 * NOTE: The nonce is validated in set_screen_options()
	 *
	 * @internal
	 * @since 8.13
	 *
	 * @param false  $false  Return `false` to short-circuit set_screen_options().
	 * @param string $option Screen option name.
	 * @param int    $value  Screen option value.
	 *
	 * @return false
	 */
	public static function setManageScreenOptions( $false, $option, $value ) {

		if ( 'connections' !== $option ) {

			return $false;
		}

		$meta  = Connections_Directory()->user->getScreenOptions( 'manage', array() );
		$image = sanitize_text_field( $_POST['wp_screen_options']['image_thumbnail'] );

		cnArray::set( $meta, 'pagination', array( 'current' => 1, 'limit' => absint( $value ) ) );
		cnArray::set( $meta, 'thumbnail', $image );

		Connections_Directory()->user->setScreenOptions( 'manage', $meta );

		// cnUser::setScreenOptions() saves the user meta, return FALSE to short circuit set_screen_options().
		return false;
	}
}
