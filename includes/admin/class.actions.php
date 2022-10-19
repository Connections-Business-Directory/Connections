<?php
/**
 * Class for processing admin action.
 *
 * @package     Connections
 * @subpackage  Admin Actions
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.7.5
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Connections_Directory\Hook\Action\Admin\Template as Template_Actions;
use Connections_Directory\Request;
use Connections_Directory\Utility\_array;
use Connections_Directory\Utility\_nonce;
use Connections_Directory\Utility\_sanitize;
use Connections_Directory\Utility\_string;
use Connections_Directory\Utility\_validate;
use function Connections_Directory\Taxonomy\Category\Admin\Deprecated_Actions\addCategory;
use function Connections_Directory\Taxonomy\Category\Admin\Deprecated_Actions\categoryManagement;
use function Connections_Directory\Taxonomy\Category\Admin\Deprecated_Actions\deleteCategory;
use function Connections_Directory\Taxonomy\Category\Admin\Deprecated_Actions\processEntryCategory;
use function Connections_Directory\Taxonomy\Category\Admin\Deprecated_Actions\updateCategory;
use function Connections_Directory\Utility\_deprecated\_func as _deprecated_function;

/**
 * Class cnAdminActions
 *
 * @phpcs:disable PEAR.NamingConventions.ValidClassName.StartWithCapital
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound
 */
class cnAdminActions {

	/**
	 * Stores the instance of this class.
	 *
	 * @since 0.7.5
	 * @var static
	 */
	private static $instance;

	/**
	 * A dummy constructor to prevent the class from being loaded more than once.
	 *
	 * @since 0.7.5
	 */
	public function __construct() {
		/* Do nothing here */
	}

	/**
	 * Set up the class, if it has already been initialized, return the initialized instance.
	 *
	 * @see cnAdminFunction::init()
	 *
	 * @since 0.7.5
	 */
	public static function init() {

		if ( ! isset( self::$instance ) ) {

			self::$instance = new self();

			self::register();
			self::doActions();
		}
	}

	/**
	 * Return an instance of the class.
	 *
	 * @since 0.7.5
	 *
	 * @return cnAdminActions
	 */
	public static function getInstance() {

		return self::$instance;
	}

	/**
	 * Register admin actions.
	 *
	 * @since 0.7.5
	 */
	private static function register() {

		// Entry Actions.
		add_action( 'cn_add_entry', array( __CLASS__, 'processEntry' ) );
		add_action( 'cn_update_entry', array( __CLASS__, 'processEntry' ) );
		add_action( 'cn_duplicate_entry', array( __CLASS__, 'processEntry' ) );
		add_action( 'cn_delete_entry', array( __CLASS__, 'deleteEntry' ) );
		add_action( 'cn_set_status', array( __CLASS__, 'setEntryStatus' ) );

		// Process entry categories. - Deprecated since 10.2, no longer used.
		// add_action( 'cn_process_taxonomy-category', array( __CLASS__, 'processEntryCategory' ), 9, 2 );

		// Entry Meta Action.
		add_action( 'cn_process_meta-entry', array( __CLASS__, 'processEntryMeta' ), 9, 2 );

		// Save the user's manage admin page actions.
		add_action( 'cn_manage_actions', array( __CLASS__, 'entryManagement' ) );
		add_action( 'cn_filter', array( __CLASS__, 'userFilter' ) );

		// Category Actions - Deprecated since 10.2, no longer used.
		// add_action( 'cn_add_category', array( __CLASS__, 'addCategory' ) );
		// add_action( 'cn_update_category', array( __CLASS__, 'updateCategory' ) );
		// add_action( 'cn_delete_category', array( __CLASS__, 'deleteCategory' ) );
		// add_action( 'cn_category_bulk_actions', array( __CLASS__, 'categoryManagement' ) );

		// Term Actions.
		add_action( 'cn_add-term', array( 'Connections_Directory\Taxonomy\Term\Admin\Actions', 'addTerm' ) );
		add_action( 'cn_update-term', array( 'Connections_Directory\Taxonomy\Term\Admin\Actions', 'updateTerm' ) );
		add_action( 'cn_delete-term', array( 'Connections_Directory\Taxonomy\Term\Admin\Actions', 'deleteTerm' ) );
		add_action( 'cn_bulk-term-action', array( 'Connections_Directory\Taxonomy\Term\Admin\Actions', 'bulkTerm' ) );

		// Term Meta Actions.
		add_action( 'cn_delete_term', array( 'Connections_Directory\Taxonomy\Term\Admin\Actions', 'deleteTermMeta' ), 10, 4 );

		// Template Actions.
		Template_Actions::register();

		// Actions that deal with the system info.
		add_action( 'wp_ajax_download_system_info', array( __CLASS__, 'downloadSystemInfo' ) );
		add_action( 'wp_ajax_email_system_info', array( __CLASS__, 'emailSystemInfo' ) );
		add_action( 'wp_ajax_generate_url', array( __CLASS__, 'generateSystemInfoURL' ) );
		add_action( 'wp_ajax_revoke_url', array( __CLASS__, 'revokeSystemInfoURL' ) );

		// Actions for export/import settings.
		add_action( 'wp_ajax_export_settings', array( __CLASS__, 'downloadSettings' ) );
		add_action( 'wp_ajax_import_settings', array( __CLASS__, 'importSettings' ) );

		// Actions for export/import.
		add_action( 'wp_ajax_export_csv_addresses', array( __CLASS__, 'csvExportAddresses' ) );
		add_action( 'wp_ajax_export_csv_phone_numbers', array( __CLASS__, 'csvExportPhoneNumbers' ) );
		add_action( 'wp_ajax_export_csv_email', array( __CLASS__, 'csvExportEmail' ) );
		add_action( 'wp_ajax_export_csv_dates', array( __CLASS__, 'csvExportDates' ) );
		add_action( 'wp_ajax_export_csv_term', array( __CLASS__, 'csvExportTerm' ) );
		add_action( 'wp_ajax_export_csv_all', array( __CLASS__, 'csvExportAll' ) );
		add_action( 'cn_download_batch_export', array( __CLASS__, 'csvExportBatchDownload' ) );

		add_action( 'wp_ajax_csv_upload', array( __CLASS__, 'uploadCSV' ) );
		add_action( 'wp_ajax_import_csv_term', array( __CLASS__, 'csvImportTerm' ) );

		// Register the action to delete a single log.
		add_action( 'cn_log_bulk_actions', array( __CLASS__, 'logManagement' ) );
		add_action( 'cn_delete_log', array( __CLASS__, 'deleteLog' ) );

		// Register action to set category height.
		add_action( 'wp_ajax_set_category_div_height', array( __CLASS__, 'setUserCategoryDivHeight' ) );

		// Add the Connections Tab to the Add Plugins admin page.
		add_filter( 'install_plugins_tabs', array( __CLASS__, 'installTab' ) );

		// Set up the plugins_api() arguments.
		add_filter( 'install_plugins_table_api_args_connections', array( __CLASS__, 'installArgs' ) );
	}

	/**
	 * Run admin actions.
	 *
	 * @since 0.7.5
	 */
	private static function doActions() {

		if ( isset( $_POST['cn-action'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing

			do_action( 'cn_' . sanitize_key( $_POST['cn-action'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		}

		if ( isset( $_GET['cn-action'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			do_action( 'cn_' . sanitize_key( $_GET['cn-action'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
	}

	/**
	 * Callback for the `wp_ajax_download_system_info` action.
	 *
	 * AJAX callback used to download the system info.
	 *
	 * @internal
	 * @since 8.3
	 */
	public static function downloadSystemInfo() {

		_validate::ajaxReferer( 'download_system_info' );

		if ( ! current_user_can( 'manage_options' ) ) {

			wp_send_json( __( 'You do not have sufficient permissions to download system information.', 'connections' ) );
		}

		cnSystem_Info::download();
	}

	/**
	 * Callback for the `wp_ajax_email_system_info` action.
	 *
	 * AJAX callback to email the system info.
	 *
	 * @internal
	 * @since 8.3
	 */
	public static function emailSystemInfo() {

		_validate::ajaxReferer( 'email_system_info' );

		if ( ! current_user_can( 'manage_options' ) ) {

			wp_send_json( -2 );
		}

		/**
		 * Since email is sent via an ajax request, let's check for the appropriate header.
		 *
		 * @link https://davidwalsh.name/detect-ajax
		 */
		if ( ! isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && 'xmlhttprequest' !== strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			wp_send_json( -3 );
		}

		$user  = wp_get_current_user();
		$email = Request\Email_System_Info::input()->value();

		$atts = array(
			'from_email' => $user->user_email,
			'from_name'  => $user->display_name,
			'to_email'   => $email['email'],
			'subject'    => $email['subject'],
			'message'    => $email['message'],
		);

		$response = cnSystem_Info::email( $atts );

		if ( $response ) {

			// Success, send success code.
			wp_send_json( 1 );

		} else {

			/** @var PHPMailer $phpmailer */
			global $phpmailer;

			// phpcs:disable Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps
			wp_send_json( $phpmailer->ErrorInfo );
			// phpcs:enable Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps
		}
	}

	/**
	 * Callback for the `wp_ajax_generate_url` action.
	 *
	 * AJAX callback to create a secret URL for the system info.
	 *
	 * @internal
	 * @since 8.3
	 */
	public static function generateSystemInfoURL() {

		if ( ! _validate::ajaxReferer( 'generate_remote_system_info_url', null, null, false ) ) {

			wp_send_json_error( __( 'Invalid AJAX action or nonce validation failed.', 'connections' ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {

			wp_send_json_error( __( 'You do not have sufficient permissions to perform this action.', 'connections' ) );
		}

		/** @todo need to check the $token is not WP_Error. */
		$token   = sanitize_key( _string::random( 32 ) );
		$expires = apply_filters( 'cn_system_info_remote_token_expire', DAY_IN_SECONDS * 3 );

		cnCache::set(
			'system_info_remote_token',
			$token,
			$expires,
			'option-cache'
		);

		$url = home_url() . '/?cn-system-info=' . $token;

		wp_send_json_success(
			array(
				'url'     => $url,
				'message' => __( 'Secret URL has been created.', 'connections' ),
			)
		);
	}

	/**
	 * Callback for the `wp_ajax_revoke_url` action.
	 *
	 * AJAX callback to revoke the secret URL for the system info.
	 *
	 * @internal
	 * @since 8.3
	 */
	public static function revokeSystemInfoURL() {

		if ( ! _validate::ajaxReferer( 'revoke_remote_system_info_url', null, null, false ) ) {

			wp_send_json_error( __( 'Invalid AJAX action or nonce validation failed.', 'connections' ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {

			wp_send_json_error( __( 'You do not have sufficient permissions to perform this action.', 'connections' ) );
		}

		cnCache::clear( 'system_info_remote_token', 'option-cache' );

		wp_send_json_success( __( 'Secret URL has been revoked.', 'connections' ) );
	}

	/**
	 * Callback for the `wp_ajax_export_settings` action.
	 *
	 * AJAX callback to download the settings in a JSON encoded text file.
	 *
	 * @internal
	 * @since 8.3
	 */
	public static function downloadSettings() {

		_validate::ajaxReferer( 'export_settings' );

		if ( ! current_user_can( 'manage_options' ) ) {

			wp_send_json( __( 'You do not have sufficient permissions to export the settings.', 'connections' ) );
		}

		cnSettingsAPI::download();
	}

	/**
	 * Callback for the `wp_ajax_import_settings` action.
	 *
	 * AJAX callback to import settings from a JSON encoded text file.
	 *
	 * @internal
	 * @since 8.3
	 */
	public static function importSettings() {

		_validate::ajaxReferer( 'import_settings' );

		if ( ! current_user_can( 'manage_options' ) ) {

			wp_send_json( __( 'You do not have sufficient permissions to import the settings.', 'connections' ) );
		}

		$file = sanitize_text_field( wp_unslash( $_FILES['import_file']['name'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated

		if ( 'json' !== pathinfo( $file, PATHINFO_EXTENSION ) ) {

			wp_send_json( __( 'Please upload a .json file.', 'connections' ) );
		}

		$file = sanitize_text_field( wp_unslash( $_FILES['import_file']['tmp_name'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated

		if ( empty( $file ) ) {

			wp_send_json( __( 'Please select a file to import.', 'connections' ) );
		}

		$json   = file_get_contents( $file ); // phpcs:ignore WordPressVIPMinimum.Performance.FetchingRemoteData.FileGetContentsUnknown
		$result = cnSettingsAPI::import( $json );

		if ( true === $result ) {

			wp_send_json( __( 'Settings have been imported.', 'connections' ) );

		} else {

			wp_send_json( $result );
		}
	}

	/**
	 * Callback for the `cn_download_batch_export` action.
	 *
	 * Admin ajax callback to download the CSV file.
	 *
	 * @internal
	 * @since 8.5
	 */
	public static function csvExportBatchDownload() {

		if ( false === Request\Nonce::input( 'batch-export-download', null, 'nonce' )->isValid() ) {

			wp_die(
				esc_html__( 'Nonce verification failed.', 'connections' ),
				esc_html__( 'Error', 'connections' ),
				array( 'response' => 403 )
			);
		}

		$type = Request\CSV_Export_Type::input()->value();

		require_once CN_PATH . 'includes/export/class.csv-export.php';
		require_once CN_PATH . 'includes/export/class.csv-export-batch.php';

		switch ( $type ) {

			case 'address':
				require_once CN_PATH . 'includes/export/class.csv-export-batch-addresses.php';

				$export = new cnCSV_Batch_Export_Addresses();
				$export->download();
				break;

			case 'phone':
				require_once CN_PATH . 'includes/export/class.csv-export-batch-phone-numbers.php';

				$export = new cnCSV_Batch_Export_Phone_Numbers();
				$export->download();
				break;

			case 'email':
				require_once CN_PATH . 'includes/export/class.csv-export-batch-email.php';

				$export = new cnCSV_Batch_Export_Email();
				$export->download();
				break;

			case 'date':
				require_once CN_PATH . 'includes/export/class.csv-export-batch-dates.php';

				$export = new cnCSV_Batch_Export_Dates();
				$export->download();
				break;

			case 'category':
				require_once CN_PATH . 'includes/export/class.csv-export-batch-category.php';

				$export = new cnCSV_Batch_Export_Term();
				$export->download();
				break;

			case 'all':
				require_once CN_PATH . 'includes/export/class.csv-export-batch-all.php';

				$export = new cnCSV_Batch_Export_All();
				$export->download();
				break;

			default:
				/**
				 * All plugins to run their own download callback function.
				 *
				 * The dynamic part of the hook is the import type.
				 *
				 * @since 8.5
				 */
				do_action( "cn_csv_batch_export_download-$type" );
				break;
		}
	}

	/**
	 * Callback for the `wp_ajax_export_csv_addresses` action.
	 *
	 * Admin ajax callback to batch export the addresses.
	 *
	 * @internal
	 * @since 8.5
	 */
	public static function csvExportAddresses() {

		_validate::ajaxReferer( 'export_csv_addresses' );

		require_once CN_PATH . 'includes/export/class.csv-export.php';
		require_once CN_PATH . 'includes/export/class.csv-export-batch.php';
		require_once CN_PATH . 'includes/export/class.csv-export-batch-addresses.php';

		$step   = Request\CSV_Export_Step::input()->value();
		$export = new cnCSV_Batch_Export_Addresses();
		$nonce  = _nonce::create( 'export_csv_addresses' );

		self::csvBatchExport( $export, 'address', $step, $nonce );
	}

	/**
	 * Callback for the `wp_ajax_set_category_div_height` action.
	 *
	 * Save the user's defined height of the category metabox.
	 *
	 * @internal
	 * @since 8.6.5
	 */
	public static function setUserCategoryDivHeight() {

		_validate::ajaxReferer( 'set_category_div_height' );

		$height = isset( $_POST['height'] ) ? absint( $_POST['height'] ) : 200;

		if ( Connections_Directory()->currentUser->setCategoryDivHeight( $height ) ) {

			wp_send_json_success(
				array(
					'message' => 'Success!',
					'_cnonce' => _nonce::create( 'set_category_div_height' ),
				)
			);

		} else {

			wp_send_json_error( array( 'message' => __( 'Failed to set user category height.', 'connections' ) ) );
		}
	}

	/**
	 * Callback for the `install_plugins_tabs` filter.
	 *
	 * @see WP_Plugin_Install_List_Table::prepare_items()
	 *
	 * @internal
	 * @since 8.6.8
	 *
	 * @param array $tabs The tabs shown on the Plugin Install screen.
	 *
	 * @return array
	 */
	public static function installTab( $tabs ) {

		$tabs['connections'] = 'Connections';

		return $tabs;
	}

	/**
	 * Callback for the `install_plugins_table_api_args_connections` filter.
	 *
	 * @see WP_Plugin_Install_List_Table::prepare_items()
	 *
	 * @internal
	 * @since 8.6.8
	 *
	 * @param array $args Plugin Install API arguments.
	 *
	 * @return array
	 */
	public static function installArgs( $args ) {

		global $tabs, $tab, $paged, $type, $term;

		$per_page = 30;

		$args = array(
			'page'     => $paged,
			'per_page' => $per_page,
			'fields'   => array(
				'last_updated'    => true,
				'icons'           => true,
				'active_installs' => true,
			),
			// Send the locale and installed plugin slugs to the API, so it can provide context-sensitive results.
			'locale'   => get_user_locale(),
			// 'installed_plugins' => $this->get_installed_plugin_slugs(),
		);

		$args['installed_plugins'] = array( 'connections' );
		$args['author']            = 'shazahm1hotmailcom';
		// $args['search'] = 'Connections Business Directory';

		add_action( 'install_plugins_connections', array( __CLASS__, 'installResults' ), 9, 1 );

		return $args;
	}

	/**
	 * Callback for the `install_plugins_connections` action.
	 *
	 * @see wp-admin/plugin-install.php
	 *
	 * @internal
	 * @since 8.6.8
	 *
	 * @param int $page The current page number of the plugins list table.
	 */
	public static function installResults( $page ) {

		/** @var WP_Plugin_Install_List_Table $wp_list_table */
		global $wp_list_table;

		foreach ( $wp_list_table->items as $key => &$item ) {

			if ( is_array( $item ) ) {

				// Remove the core plugin.
				if ( 'connections' === $item['slug'] ) {

					unset( $wp_list_table->items[ $key ] );
				}

				// Remove any items which do not have Connections in its name.
				if ( false === strpos( $item['name'], 'Connections' ) ) {

					unset( $wp_list_table->items[ $key ] );
				}

			} elseif ( is_object( $item ) ) {

				if ( 'connections' === $item->slug ) {

					unset( $wp_list_table->items[ $key ] );
				}

				if ( false === strpos( $item->name, 'Connections' ) ) {

					unset( $wp_list_table->items[ $key ] );
				}
			}
		}

		// Save the items from the original query.
		$core = $wp_list_table->items;

		// Affiliate URL and preg replace pattern.
		$tslAffiliateURL = 'https://tinyscreenlabs.com/?tslref=connections';
		$pattern         = "/(?<=href=(\"|'))[^\"']+(?=(\"|'))/";

		// phpcs:disable Squiz.Commenting.InlineComment.NoSpaceBefore, Squiz.Commenting.InlineComment.SpacingBefore, Squiz.Commenting.InlineComment.InvalidEndChar
		//$mam = plugins_api(
		//	'plugin_information',
		//	array(
		//		'slug'              => 'mobile-app-manager-for-connections',
		//		'fields'            => array(
		//			'last_updated'    => TRUE,
		//			'icons'           => TRUE,
		//			'active_installs' => TRUE,
		//		),
		//		'locale'            => get_user_locale(),
		//		'installed_plugins' => array( 'connections' ),
		//	)
		//);
		// phpcs:enable Squiz.Commenting.InlineComment.NoSpaceBefore, Squiz.Commenting.InlineComment.SpacingBefore, Squiz.Commenting.InlineComment.InvalidEndChar

		$offers = plugins_api(
			'plugin_information',
			array(
				'slug'              => 'connections-business-directory-offers',
				'fields'            => array(
					'last_updated'    => true,
					'icons'           => true,
					'active_installs' => true,
				),
				'locale'            => get_user_locale(),
				'installed_plugins' => array( 'connections' ),
			)
		);

		// phpcs:disable Squiz.Commenting.InlineComment.NoSpaceBefore, Squiz.Commenting.InlineComment.SpacingBefore, Squiz.Commenting.InlineComment.InvalidEndChar
		//$tsl = plugins_api(
		//	'query_plugins',
		//	array(
		//		'author'            => 'tinyscreenlabs',
		//		'fields'            => array(
		//			'last_updated'    => TRUE,
		//			'icons'           => TRUE,
		//			'active_installs' => TRUE,
		//		),
		//		'locale'            => get_user_locale(),
		//		'installed_plugins' => array( 'connections' ),
		//	)
		//);

		//if ( ! is_wp_error( $tsl ) ) {
		//
		//	foreach ( $tsl->plugins as $plugin ) {
		//
		//		switch ( $plugin->slug ) {
		//
		//			// Add TSL MAM to the top of the plugin items array.
		//			case 'mobile-app-manager-for-connections':
		//
		//				$wp_list_table->items = cnArray::prepend( $wp_list_table->items, $plugin );
		//				break;
		//
		//			// Add TSL Offers to the bottom of the plugin items array.
		//			case 'connections-business-directory-offers':
		//
		//				$wp_list_table->items[] = $plugin;
		//				break;
		//		}
		//	}
		//}
		// phpcs:enable Squiz.Commenting.InlineComment.NoSpaceBefore, Squiz.Commenting.InlineComment.SpacingBefore, Squiz.Commenting.InlineComment.InvalidEndChar

		?>
		<form id="plugin-filter" method="post">
			<?php
			// $wp_list_table->display();
			$wp_list_table->_pagination_args = array();

			if ( 0 < count( $core ) ) {
				$wp_list_table->items = $core;
				self::installDisplayGroup( 'Free' );
			}

			// phpcs:disable Squiz.Commenting.InlineComment.NoSpaceBefore, Squiz.Commenting.InlineComment.SpacingBefore, Squiz.Commenting.InlineComment.InvalidEndChar
			//if ( ! is_wp_error( $mam ) ) {
			//
			//	// Update the links to TSL to use the affiliate URL.
			//	$mam->homepage = $tslAffiliateURL;
			//	$mam->author = preg_replace( $pattern, $tslAffiliateURL, $mam->author );
			//
			//	$wp_list_table->items = array( $mam );
			//	self::installDisplayGroup( 'Mobile App' );
			//}
			// phpcs:enable Squiz.Commenting.InlineComment.NoSpaceBefore, Squiz.Commenting.InlineComment.SpacingBefore, Squiz.Commenting.InlineComment.InvalidEndChar

			if ( ! is_wp_error( $offers ) ) {

				// Update the links to TSL to use the affiliate URL.
				$offers->homepage = $tslAffiliateURL;
				$offers->author   = preg_replace( $pattern, $tslAffiliateURL, $offers->author );

				$wp_list_table->items = array( $offers );
				self::installDisplayGroup( 'Third Party' );
			}
			?>
		</form>
		<?php

		// Restore original items.
		$wp_list_table->items = $core;
	}

	/**
	 * Display the plugin info cards.
	 *
	 * @access private
	 * @since  8.6.8
	 *
	 * @param string $name
	 */
	private static function installDisplayGroup( $name ) {

		/** @var WP_Plugin_Install_List_Table $wp_list_table */
		global $wp_list_table;

		// Needs an extra wrapping div for nth-child selectors to work.
		?>
		<div class="plugin-group"><h3> <?php echo esc_html( $name ); ?></h3>
			<div class="plugin-items">

				<?php $wp_list_table->display(); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Callback for the `wp_ajax_export_csv_phone_numbers` action.
	 *
	 * Admin ajax callback to batch export the phone numbers.
	 *
	 * @internal
	 * @since 8.5
	 */
	public static function csvExportPhoneNumbers() {

		_validate::ajaxReferer( 'export_csv_phone_numbers' );

		require_once CN_PATH . 'includes/export/class.csv-export.php';
		require_once CN_PATH . 'includes/export/class.csv-export-batch.php';
		require_once CN_PATH . 'includes/export/class.csv-export-batch-phone-numbers.php';

		$step   = Request\CSV_Export_Step::input()->value();
		$export = new cnCSV_Batch_Export_Phone_Numbers();
		$nonce  = _nonce::create( 'export_csv_phone_numbers' );

		self::csvBatchExport( $export, 'phone', $step, $nonce );
	}

	/**
	 * Callback for the `wp_ajax_export_csv_email` action.
	 *
	 * Admin ajax callback to batch export the email addresses.
	 *
	 * @internal
	 * @since 8.5
	 */
	public static function csvExportEmail() {

		_validate::ajaxReferer( 'export_csv_email' );

		require_once CN_PATH . 'includes/export/class.csv-export.php';
		require_once CN_PATH . 'includes/export/class.csv-export-batch.php';
		require_once CN_PATH . 'includes/export/class.csv-export-batch-email.php';

		$step   = Request\CSV_Export_Step::input()->value();
		$export = new cnCSV_Batch_Export_Email();
		$nonce  = _nonce::create( 'export_csv_email' );

		self::csvBatchExport( $export, 'email', $step, $nonce );
	}

	/**
	 * Callback for the `wp_ajax_export_csv_dates` action.
	 *
	 * Admin ajax callback to batch export the dates.
	 *
	 * @internal
	 * @since 8.5
	 */
	public static function csvExportDates() {

		_validate::ajaxReferer( 'export_csv_dates' );

		require_once CN_PATH . 'includes/export/class.csv-export.php';
		require_once CN_PATH . 'includes/export/class.csv-export-batch.php';
		require_once CN_PATH . 'includes/export/class.csv-export-batch-dates.php';

		$step   = Request\CSV_Export_Step::input()->value();
		$export = new cnCSV_Batch_Export_Dates();
		$nonce  = _nonce::create( 'export_csv_dates' );

		self::csvBatchExport( $export, 'date', $step, $nonce );
	}

	/**
	 * Callback for the `wp_ajax_export_csv_term` action.
	 *
	 * Admin ajax callback to batch export the category data.
	 *
	 * @internal
	 * @since 8.5.5
	 */
	public static function csvExportTerm() {

		_validate::ajaxReferer( 'export_csv_term' );

		require_once CN_PATH . 'includes/export/class.csv-export.php';
		require_once CN_PATH . 'includes/export/class.csv-export-batch.php';
		require_once CN_PATH . 'includes/export/class.csv-export-batch-category.php';

		$step   = Request\CSV_Export_Step::input()->value();
		$export = new cnCSV_Batch_Export_Term();
		$nonce  = _nonce::create( 'export_csv_term' );

		self::csvBatchExport( $export, 'category', $step, $nonce );
	}

	/**
	 * Callback for the `wp_ajax_import_csv_term` action.
	 *
	 * Admin ajax callback to batch import the term data.
	 *
	 * @internal
	 * @since 8.5.5
	 */
	public static function csvImportTerm() {

		_validate::ajaxReferer( 'import_csv_term' );

		if ( false === Request\Nonce::from( INPUT_POST, 'import_csv_term', null, '_ajax_nonce' )->isValid() ) {

			wp_send_json_error( array( 'message' => __( 'Nonce verification failed', 'connections' ) ) );
		}

		if ( empty( $_REQUEST['file'] ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Missing import file. Please provide an import file.', 'connections' ),
					'request' => $_REQUEST,
				)
			);
		}

		$path = isset( $_REQUEST['file']['path'] ) ? _sanitize::filepath( wp_unslash( $_REQUEST['file']['path'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		if ( empty( $path ) || ! file_exists( $path ) || ! _validate::isCSV( $path ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'The uploaded file does not appear to be a CSV file.', 'connections' ),
					'request' => $_REQUEST,
				)
			);
		}

		require_once CN_PATH . 'includes/import/class.csv-import-batch.php';
		require_once CN_PATH . 'includes/import/class.csv-import-batch-category.php';

		$step   = Request\CSV_Export_Step::input()->value();
		$import = new cnCSV_Batch_Import_Term( $path );
		$nonce  = _nonce::create( 'import_csv_term' );

		self::csvBatchImport( $import, 'category', $step, $nonce );
	}

	/**
	 * Callback for the `wp_ajax_export_csv_all` action.
	 *
	 * Admin ajax callback to batch export the all entry data.
	 *
	 * @internal
	 * @since 8.5.1
	 */
	public static function csvExportAll() {

		_validate::ajaxReferer( 'export_csv_all' );

		require_once CN_PATH . 'includes/export/class.csv-export.php';
		require_once CN_PATH . 'includes/export/class.csv-export-batch.php';
		require_once CN_PATH . 'includes/export/class.csv-export-batch-all.php';

		$step   = Request\CSV_Export_Step::input()->value();
		$export = new cnCSV_Batch_Export_All();
		$nonce  = _nonce::create( 'export_csv_all' );

		self::csvBatchExport( $export, 'all', $step, $nonce );
	}

	/**
	 * Common CSV batch export code to start the batch export step and provide the JSON response.
	 *
	 * @access private
	 * @since 8.5
	 *
	 * @param cnCSV_Batch_Export $export
	 * @param string             $type
	 * @param int                $step
	 * @param string             $nonce
	 */
	private static function csvBatchExport( $export, $type, $step, $nonce ) {

		if ( ! $export->can_export() ) {

			wp_send_json_error(
				array(
					'message' => __( 'You do not have permission to export data.', 'connections' ),
				)
			);
		}

		if ( ! $export->is_writable ) {

			wp_send_json_error(
				array(
					'message' => __( 'Export location or file not writable.', 'connections' ),
				)
			);
		}

		$result = $export->process( $step );

		if ( is_wp_error( $result ) ) {

			wp_send_json_error(
				array(
					'message' => $result->get_error_message(),
				)
			);
		}

		$count      = $export->getCount();
		$exported   = $count > $step * $export->limit ? $step * $export->limit : $count;
		$remaining  = 0 < $count - $exported ? $count - $exported : $count;
		$percentage = $export->getPercentageComplete();

		if ( $result ) {

			wp_send_json_success(
				array(
					'step'       => ++$step,
					'count'      => $count,
					'exported'   => $exported,
					'remaining'  => $remaining,
					'percentage' => $percentage,
					'nonce'      => $nonce,
				)
			);

		} elseif ( true === $export->is_empty ) {

			wp_send_json_error(
				array(
					'message' => __( 'No data found for export parameters.', 'connections' ),
				)
			);

		} else {

			$args = array(
				'cn-action' => 'download_batch_export',
				'type'      => $type,
				'nonce'     => _nonce::create( 'batch-export-download' ),
			);

			$url = add_query_arg( $args, self_admin_url() );

			wp_send_json_success(
				array(
					'step' => 'completed',
					'url'  => $url,
				)
			);
		}
	}

	/**
	 * Callback for the `wp_ajax_csv_upload` action.
	 *
	 * @internal
	 * @since unknown
	 */
	public static function uploadCSV() {

		require_once CN_PATH . 'includes/import/class.csv-import-batch.php';

		if ( false === Request\Nonce::from( INPUT_POST, 'csv_upload', null, 'nonce' )->isValid() ) {

			wp_send_json_error(
				array(
					'form'    => $_POST,
					'message' => __( 'Nonce verification failed', 'connections' ),
				)
			);
		}

		if ( ! (bool) apply_filters( 'cn_csv_import_capability', current_user_can( 'import' ) ) ) {

			wp_send_json_error(
				array(
					'form'    => $_POST,
					'message' => __( 'You do not have permission to import data.', 'connections' ),
				)
			);
		}

		if ( empty( $_FILES ) ) {
			wp_send_json_error(
				array(
					'form'    => $_POST,
					'message' => __( 'No file selected. Please select a file to import.', 'connections' ),
					'request' => $_REQUEST,
				)
			);
		}

		$upload = new cnUpload(
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.InputNotValidated
			$_FILES['cn-import-file'], // Uses `wp_handle_upload()` internally.
			array(
				'mimes' => array(
					'csv' => 'text/csv',
					'txt' => 'text/plain',
				),
			)
		);

		$result = $upload->result();

		if ( ! is_wp_error( $result ) ) {

			$import  = new cnCSV_Batch_Import( $result['path'] );
			$headers = $import->getHeaders();

			if ( is_wp_error( $headers ) ) {

				wp_send_json_error(
					array(
						'form'    => $_POST,
						'message' => $headers->get_error_message(),
					)
				);
			}

			wp_send_json_success(
				array(
					'form'    => $_POST,
					'file'    => $result,
					'fields'  => array(
						'-1'     => esc_html__( 'Do Not Import', 'connections' ),
						'name'   => esc_html__( 'Name', 'connections' ),
						'slug'   => esc_html__( 'Slug', 'connections' ),
						'desc'   => esc_html__( 'Description', 'connections' ),
						'parent' => esc_html__( 'Parent', 'connections' ),
					),
					'headers' => $headers,
					'nonce'   => _nonce::create( 'import_csv_term' ),
				)
			);

		} else {

			wp_send_json_error(
				array(
					'form'    => $_POST,
					'message' => $result->get_error_message(),
				)
			);
		}

		exit;
	}

	/**
	 * Common CSV batch import code to start the batch import step and provide the JSON response.
	 *
	 * @access private
	 * @since  8.5.5
	 *
	 * @param cnCSV_Batch_Import $import
	 * @param string             $taxonomy
	 * @param int                $step
	 * @param string             $nonce
	 */
	private static function csvBatchImport( $import, $taxonomy, $step, $nonce ) {

		if ( ! $import->can_import() ) {

			wp_send_json_error(
				array(
					'form'    => $_POST, // phpcs:ignore WordPress.Security.NonceVerification.Missing
					'message' => __( 'You do not have permission to export data.', 'connections' ),
				)
			);
		}

		/**
		 * Prevent the taxonomy hierarchy from being purged and built after each term insert because
		 * it severely slows down the import as the number of terms being imported increases.
		 *
		 * @see cnTerm::cleanCache()
		 */
		add_filter( "pre_option_cn_{$taxonomy}_children", '__return_empty_array' );

		if ( ! isset( $_REQUEST['map'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			wp_send_json_error(
				array(
					'form'    => $_POST, // phpcs:ignore WordPress.Security.NonceVerification.Missing
					'message' => 'Invalid CSV to field map provided.',
				)
			);

		} else {

			$map = json_decode( sanitize_text_field( wp_unslash( $_REQUEST['map'] ) ), true ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		$import->setMap( $map );

		$result = $import->process( $step );

		if ( is_wp_error( $result ) ) {

			wp_send_json_error(
				array(
					'form'    => $_POST, // phpcs:ignore WordPress.Security.NonceVerification.Missing
					'message' => $result->get_error_message(),
				)
			);
		}

		if ( $result ) {

			$count      = $import->getCount();
			$imported   = $step * $import->limit > $count ? $count : $step * $import->limit;
			$remaining  = 0 < $count - $imported ? $count - $imported : 0;
			$percentage = $import->getPercentageComplete();

			wp_send_json_success(
				array(
					'map'        => wp_json_encode( $import->getMap() ),
					'step'       => ++$step,
					'count'      => $count,
					'imported'   => $imported,
					'remaining'  => $remaining,
					'percentage' => $percentage,
					'nonce'      => $nonce,
				)
			);

		} else {

			$url = add_query_arg(
				array(
					'page' => 'connections_tools',
					'tab'  => 'import',
				),
				self_admin_url( 'admin.php' )
			);

			wp_send_json_success(
				array(
					'step'    => 'completed',
					'message' => esc_html__( 'Import completed.', 'connections' ),
					'url'     => $url,
				)
			);
		}
	}

	/**
	 * Callback for the `cn_manage_actions` action.
	 *
	 * Process controller for action taken by the user.
	 *
	 * @internal
	 * @since 0.7.8
	 */
	public static function entryManagement() {

		$queryVar = array();

		_validate::adminReferer( 'cn_manage_actions' );

		// Process user selected filters.
		self::saveUserFilters();

		// Grab the bulk action requested by user.
		$action = Request\Manage_Bulk_Action::input()->value();
		$ids    = Request\Int_Array::input()->value();

		switch ( $action ) {

			case 'delete':
				// Bulk delete entries.
				self::deleteEntryBulk( $ids );
				break;

			case 'approve':
				// Bulk approve entries.
				self::setEntryStatusBulk( $ids, 'approved' );
				break;

			case 'unapprove':
				// Bulk unapprove entries.
				self::setEntryStatusBulk( $ids, 'pending' );
				break;

			case 'public':
				// Set entries to public visibility in bulk.
				self::setEntryVisibilityBulk( $ids, 'public' );
				break;

			case 'private':
				// Set entries to private visibility in bulk.
				self::setEntryVisibilityBulk( $ids, 'private' );
				break;

			case 'unlisted':
				// Set entries to unlisted visibility in bulk.
				self::setEntryVisibilityBulk( $ids, 'unlisted' );
				break;

			default:
				/* None, blank intentionally. */
				break;
		}

		/*
		 * Set up the redirect.
		 */

		if ( Request\Search::input()->value() ) {

			$queryVar['s'] = urlencode( Request\Search::input()->value() );
		}

		if ( 0 < mb_strlen( Request\Entry_Initial_Character::input()->value() ) ) {

			$queryVar['cn-char'] = urlencode( Request\Entry_Initial_Character::input()->value() );
		}

		/*
		 * Do the redirect.
		 */
		wp_safe_redirect(
			get_admin_url(
				get_current_blog_id(),
				add_query_arg( $queryVar, 'admin.php?page=connections_manage' )
			)
		);

		exit();
	}

	/**
	 * Callback for the `cn_add_entry`, `cn_update_entry`, and `cn_duplicate_entry` action.
	 *
	 * Add / Edit / Update / Copy an entry.
	 *
	 * @internal
	 * @since 0.7.8
	 */
	public static function processEntry() {

		$action = Request\Admin_Action::from( INPUT_POST )->value();

		// Set up the redirect URL.
		$redirect = isset( $_POST['redirect'] ) ? wp_sanitize_redirect( $_POST['redirect'] ) : 'admin.php?page=connections_add';

		switch ( $action ) {

			case 'add_entry':
				/*
				 * Check whether the current user can add an entry.
				 */
				if ( current_user_can( 'connections_add_entry' ) || current_user_can( 'connections_add_entry_moderated' ) ) {

					_validate::adminReferer( 'add_entry' );

					cnEntry_Action::add( $_POST );

				} else {

					cnMessage::set( 'error', 'capability_add' );
				}

				break;

			case 'copy_entry':
				$id = Request\ID::input()->value();

				/*
				 * Check whether the current user can add an entry.
				 */
				if ( 0 < $id && ( current_user_can( 'connections_add_entry' ) || current_user_can( 'connections_add_entry_moderated' ) ) ) {

					_validate::adminReferer( 'add_entry' );

					cnEntry_Action::copy( $id, $_POST );

				} else {

					cnMessage::set( 'error', 'capability_add' );
				}

				break;

			case 'update_entry':
				$id = Request\ID::input()->value();

				// Set up the redirect URL.
				$redirect = isset( $_POST['redirect'] ) ? wp_sanitize_redirect( $_POST['redirect'] ) : 'admin.php?page=connections_manage';

				/*
				 * Check whether the current user can edit an entry.
				 */
				if ( 0 < $id && ( current_user_can( 'connections_edit_entry' ) || current_user_can( 'connections_edit_entry_moderated' ) ) ) {

					_validate::adminReferer( 'update_entry', $id );

					cnEntry_Action::update( $id, $_POST );

				} else {

					cnMessage::set( 'error', 'capability_edit' );
				}

				break;
		}

		wp_safe_redirect( get_admin_url( get_current_blog_id(), $redirect ) );

		exit();
	}

	/**
	 * Callback for the `cn_process_taxonomy-category` action.
	 *
	 * Add, update or delete the entry categories.
	 *
	 * @internal
	 * @since 0.8
	 * @deprecated 10.2 Use the `Connections_Directory/Attach/Taxonomy/{$taxonomySlug}` action hook.
	 * @see \Connections_Directory\Taxonomy::attachTerms()
	 *
	 * @param string $action The action to being performed to an entry.
	 * @param int    $id     The entry ID.
	 *
	 * @noinspection PhpDeprecationInspection
	 * @noinspection PhpUnused
	 */
	public static function processEntryCategory( $action, $id ) {

		_deprecated_function( __METHOD__, '10.2' );

		require_once CN_PATH . 'includes/Taxonomy/Term/Admin/Deprecated_Category_Actions.php';

		ProcessEntryCategory( $action, $id );
	}

	/**
	 * Callback for the `cn_process_meta-entry` action.
	 *
	 * Add, update or delete the entry metadata.
	 *
	 * @internal
	 * @since 0.8
	 *
	 * @param string $action The action to being performed to an entry.
	 * @param int    $id     The entry ID.
	 *
	 * @return array|false An array of meta IDs or FALSE on failure.
	 */
	public static function processEntryMeta( $action, $id ) {

		/** @var wpdb $wpdb */
		global $wpdb;

		if ( ! $id = absint( $id ) ) {

			return false;
		}

		$meta       = array();
		$newmeta    = array();
		$metaSelect = array();
		$metaIDs    = array();

		switch ( $action ) {

			case 'add':
				if ( isset( $_POST['newmeta'] ) || ! empty( $_POST['newmeta'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing

					foreach ( $_POST['newmeta'] as $row ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

						// If the key begins with an underscore, remove it because those are private.
						if ( isset( $row['key'][0] ) && '_' == $row['key'][0] ) {

							$row['key'] = substr( $row['key'], 1 );
						}

						$newmeta[] = cnMeta::add( 'entry', $id, $row['key'], $row['value'] );
					}
				}

				if ( isset( $_POST['metakeyselect'] ) && '-1' !== $_POST['metakeyselect'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing

					$metaSelect[] = cnMeta::add( 'entry', $id, $_POST['metakeyselect'], $_POST['newmeta']['99']['value'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.InputNotValidated
				}

				$metaIDs['added'] = array_merge( $newmeta, $metaSelect );

				break;

			case 'copy':
				// Copy any meta associated with the source entry to the new entry.
				if ( isset( $_POST['meta'] ) || ! empty( $_POST['meta'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing

					foreach ( $_POST['meta'] as $row ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

						// If the key begins with an underscore, remove it because those are private.
						if ( isset( $row['key'][0] ) && '_' == $row['key'][0] ) {

							$row['key'] = substr( $row['key'], 1 );
						}

						// Add the meta except for those that the user deleted for this entry.
						if ( '::DELETED::' !== $row['value'] ) {

							$meta[] = cnMeta::add( 'entry', $id, $row['key'], $row['value'] );
						}
					}
				}

				// Lastly, add any new meta the user may have added.
				if ( isset( $_POST['newmeta'] ) || ! empty( $_POST['newmeta'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing

					foreach ( $_POST['newmeta'] as $row ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

						// If the key begins with an underscore, remove it because those are private.
						if ( isset( $row['key'][0] ) && '_' == $row['key'][0] ) {

							$row['key'] = substr( $row['key'], 1 );
						}

						$metaIDs[] = cnMeta::add( 'entry', $id, $row['key'], $row['value'] );
					}

					// $newmeta = cnMeta::add( 'entry', $id, $_POST['newmeta']['0']['key'], $_POST['newmeta']['99']['value'] );
				}

				if ( isset( $_POST['metakeyselect'] ) && '-1' !== $_POST['metakeyselect'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing

					$metaSelect[] = cnMeta::add( 'entry', $id, $_POST['metakeyselect'], $_POST['newmeta']['99']['value'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.InputNotValidated
				}

				$metaIDs['added'] = array_merge( $meta, $newmeta, $metaSelect );

				break;

			case 'update':
				// Query the meta associated to the entry.
				$results = $wpdb->get_results(
					$wpdb->prepare(
						'SELECT meta_key, meta_value, meta_id, entry_id FROM ' . CN_ENTRY_TABLE_META . ' WHERE entry_id = %d ORDER BY meta_key,meta_id',
						$id
					),
					ARRAY_A
				);

				if ( false !== $results ) {

					// Loop through $results removing any custom meta fields. Custom meta fields are considered to be private.
					foreach ( $results as $metaID => $row ) {

						if ( cnMeta::isPrivate( $row['meta_key'] ) ) {

							unset( $results[ $row['meta_id'] ] );
						}
					}

					// Loop through the associated meta and update any that may have been changed.
					// If the meta id doesn't exist in the $_POST data, assume the user deleted it.
					foreach ( $results as $metaID => $row ) {

						// Update the entry meta if it differs.
						if ( ( isset( $_POST['meta'][ $row['meta_id'] ]['value'] ) && $_POST['meta'][ $row['meta_id'] ]['value'] !== $row['meta_value'] ) || // phpcs:ignore WordPress.Security.NonceVerification.Missing
							 ( isset( $_POST['meta'][ $row['meta_id'] ]['key'] ) && $_POST['meta'][ $row['meta_id'] ]['key'] !== $row['meta_key'] ) && // phpcs:ignore WordPress.Security.NonceVerification.Missing
							 ( '::DELETED::' !== $_POST['meta'][ $row['meta_id'] ]['value'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing

							// If the key begins with an underscore, remove it because those are private.
							// if ( isset( $row['key'][0] ) && '_' == $row['key'][0] ) $row['key'] = substr( $row['key'], 1 );

							// cnMeta::update( 'entry', $id, $_POST['meta'][ $row['meta_id'] ]['key'], $_POST['meta'][ $row['meta_id'] ]['value'], $row['meta_value'], $row['meta_key'], $row['meta_id'] );
							cnMeta::updateByID( 'entry', $row['meta_id'], $_POST['meta'][ $row['meta_id'] ]['value'], $_POST['meta'][ $row['meta_id'] ]['key'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.NonceVerification.Missing

							$metaIDs['updated'] = $row['meta_id'];
						}

						if ( isset( $_POST['meta'][ $row['meta_id'] ]['value'] ) && '::DELETED::' === $_POST['meta'][ $row['meta_id'] ]['value'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing

							// Record entry meta to be deleted.
							cnMeta::deleteByID( 'entry', $row['meta_id'] );

							$metaIDs['deleted'] = $row['meta_id'];
						}
					}
				}

				// Lastly, add any new meta the user may have added.
				if ( isset( $_POST['newmeta'] ) || ! empty( $_POST['newmeta'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing

					foreach ( $_POST['newmeta'] as $row ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

						// If the key begins with an underscore, remove it because those are private.
						if ( isset( $row['key'][0] ) && '_' == $row['key'][0] ) {

							$row['key'] = substr( $row['key'], 1 );
						}

						$metaIDs[] = cnMeta::add( 'entry', $id, $row['key'], $row['value'] );
					}

					// $newmeta = cnMeta::add( 'entry', $id, $_POST['newmeta']['0']['key'], $_POST['newmeta']['99']['value'] );
				}

				if ( isset( $_POST['metakeyselect'] ) && '-1' !== $_POST['metakeyselect'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing

					$metaSelect[] = cnMeta::add( 'entry', $id, $_POST['metakeyselect'], $_POST['newmeta']['99']['value'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.InputNotValidated
				}

				$metaIDs['added'] = array_merge( $newmeta, $metaSelect );

				break;
		}

		return $metaIDs;
	}

	/**
	 * Callback for the `cn_set_status` action.
	 *
	 * Set the entry status to pending or approved.
	 *
	 * @internal
	 * @since 0.7.8
	 */
	public static function setEntryStatus() {

		$id = Request\ID::input()->value();

		_validate::adminReferer( 'entry_status', $id );

		/*
		 * Check whether the current user can edit an entry.
		 */
		if ( current_user_can( 'connections_edit_entry' ) ) {

			$status = Request\Entry_Status::input()->value();

			cnEntry_Action::status( $status, $id );

			switch ( $status ) {

				case 'pending':
					cnMessage::set( 'success', 'form_entry_pending' );
					break;

				case 'approve':
					cnMessage::set( 'success', 'form_entry_approve' );
					break;
			}

		} else {

			cnMessage::set( 'error', 'capability_edit' );
		}

		wp_safe_redirect( get_admin_url( get_current_blog_id(), 'admin.php?page=connections_manage' ) );

		exit();
	}

	/**
	 * Set the approval status of entries in bulk.
	 *
	 * Nonce verification is done in the calling method.
	 * Do not call without performing nonce verification.
	 *
	 * @internal
	 * @since 0.7.8
	 *
	 * @param int[]  $ids    Indexed array of Entry IDs.
	 * @param string $status The entry status that should be set.
	 */
	public static function setEntryStatusBulk( $ids, $status ) {

		$permitted = array(
			'pending',
			'approved',
		);

		if ( ! in_array( $status, $permitted ) ) {

			return;
		}

		if ( current_user_can( 'connections_edit_entry' ) ) {

			cnEntry_Action::status( $status, $ids );

			switch ( $status ) {

				case 'pending':
					cnMessage::set( 'success', 'form_entry_pending_bulk' );
					return;

				case 'approved':
					cnMessage::set( 'success', 'form_entry_approve_bulk' );
					return;
			}

		} else {

			cnMessage::set( 'error', 'capability_edit' );
		}
	}

	/**
	 * Set the visibility status of entries in bulk.
	 *
	 * Nonce verification is done in the calling method.
	 * Do not call without performing nonce verification.
	 *
	 * @internal
	 * @since 0.7.8
	 *
	 * @param int[]  $ids Indexed array of Entry IDs.
	 * @param string $visibility The entry visibility that should be set.
	 */
	public static function setEntryVisibilityBulk( $ids, $visibility ) {

		$permitted = array(
			'public',
			'private',
			'unlisted',
		);

		if ( ! in_array( $visibility, $permitted ) ) {

			return;
		}

		if ( current_user_can( 'connections_edit_entry' ) ) {

			cnEntry_Action::visibility( $visibility, $ids );

			cnMessage::set( 'success', 'form_entry_visibility_bulk' );

		} else {

			cnMessage::set( 'error', 'capability_edit' );
		}
	}

	/**
	 * Callback for the `cn_delete_entry` action.
	 *
	 * Delete an entry.
	 *
	 * @internal
	 * @since 0.7.8
	 */
	public static function deleteEntry() {

		$id = Request\ID::input()->value();

		_validate::adminReferer( 'entry_delete', $id );

		/*
		 * Check whether the current user delete an entry.
		 */
		if ( current_user_can( 'connections_delete_entry' ) ) {

			cnEntry_Action::delete( $id );

			cnMessage::set( 'success', 'form_entry_delete' );

		} else {

			cnMessage::set( 'error', 'capability_delete' );
		}

		wp_safe_redirect( get_admin_url( get_current_blog_id(), 'admin.php?page=connections_manage' ) );

		exit();
	}

	/**
	 * Delete entries in bulk.
	 *
	 * Nonce verification is done in the calling method.
	 * Do not call without performing nonce verification.
	 *
	 * @internal
	 * @since 0.7.8
	 *
	 * @param int[] $ids Indexed array of Entry IDs.
	 */
	public static function deleteEntryBulk( $ids ) {

		if ( current_user_can( 'connections_delete_entry' ) ) {

			cnEntry_Action::delete( $ids );

			cnMessage::set( 'success', 'form_entry_delete_bulk' );

		} else {

			cnMessage::set( 'error', 'capability_delete' );
		}
	}

	/**
	 * Callback for the `cn_filter` action.
	 *
	 * Process user filters.
	 *
	 * @internal
	 * @since 0.7.8
	 */
	public static function userFilter() {

		$queryVar = array();

		_validate::adminReferer( 'filter' );

		self::saveUserFilters();

		/*
		 * Set up the redirect.
		 */
		if ( 0 < mb_strlen( Request\Search::input()->value() ) ) {

			$queryVar['s'] = urlencode( Request\Search::input()->value() );
		}

		if ( 0 < mb_strlen( Request\Entry_Initial_Character::input()->value() ) ) {

			$queryVar['cn-char'] = urlencode( Request\Entry_Initial_Character::input()->value() );
		}

		/*
		 * Do the redirect.
		 */
		wp_safe_redirect(
			get_admin_url(
				get_current_blog_id(),
				add_query_arg(
					$queryVar,
					'admin.php?page=connections_manage'
				)
			)
		);

		exit();
	}

	/**
	 * Save user filters.
	 *
	 * NOTE: The nonce is verified in calling method.
	 *
	 * @internal
	 * @since 0.7.8
	 */
	public static function saveUserFilters() {

		$filter = Request\Manage_Filter::input()->value();
		$option = Connections_Directory()->user->getScreenOptions( 'manage' );

		if ( array_key_exists( 'category', $filter ) ) {

			_array::set( $option, 'filter.category', $filter['category'] );
		}

		if ( array_key_exists( 'pg', $filter ) ) {

			_array::set( $option, 'pagination.current', $filter['pg'] );
		}

		if ( array_key_exists( 'status', $filter ) ) {

			_array::set( $option, 'filter.status', $filter['status'] );
		}

		if ( array_key_exists( 'type', $filter ) ) {

			_array::set( $option, 'filter.type', $filter['type'] );
		}

		if ( array_key_exists( 'visibility', $filter ) ) {

			_array::set( $option, 'filter.visibility', $filter['visibility'] );
		}

		Connections_Directory()->user->setScreenOptions( 'manage', $option );
	}

	/**
	 * Callback for the `cn_add_category` action.
	 *
	 * Add a category.
	 *
	 * @internal
	 * @since 0.7.7
	 * @deprecated 10.2
	 *
	 * @noinspection PhpDeprecationInspection
	 * @noinspection PhpUnused
	 */
	public static function addCategory() {

		_deprecated_function( __METHOD__, '10.2', 'cnAdminActions::addTerm()' );

		require_once CN_PATH . 'includes/Taxonomy/Term/Admin/Deprecated_Category_Actions.php';

		addCategory();
	}

	/**
	 * Callback for the `cn_update_category` action.
	 *
	 * Update a category.
	 *
	 * @internal
	 * @since 0.7.7
	 * @deprecated 10.2
	 *
	 * @noinspection PhpDeprecationInspection
	 * @noinspection PhpUnused
	 */
	public static function updateCategory() {

		_deprecated_function( __METHOD__, '10.2', 'cnAdminActions::updateTerm()' );

		require_once CN_PATH . 'includes/Taxonomy/Term/Admin/Deprecated_Category_Actions.php';

		updateCategory();
	}

	/**
	 * Callback for the `cn_delete_category` action.
	 *
	 * Delete a category.
	 *
	 * @internal
	 * @since 0.7.7
	 * @deprecated 10.2
	 *
	 * @noinspection PhpDeprecationInspection
	 * @noinspection PhpUnused
	 */
	public static function deleteCategory() {

		_deprecated_function( __METHOD__, '10.2', 'cnAdminActions::deleteTerm()' );

		require_once CN_PATH . 'includes/Taxonomy/Term/Admin/Deprecated_Category_Actions.php';

		deleteCategory();
	}

	/**
	 * Callback for the `cn_category_bulk_actions` action.
	 *
	 * Bulk category actions.
	 *
	 * @internal
	 * @since 0.7.7
	 * @deprecated 10.2
	 *
	 * @noinspection PhpDeprecationInspection
	 * @noinspection PhpUnused
	 */
	public static function categoryManagement() {

		_deprecated_function( __METHOD__, '10.2', 'cnAdminActions::bulkTerm()' );

		require_once CN_PATH . 'includes/Taxonomy/Term/Admin/Deprecated_Category_Actions.php';

		categoryManagement();
	}

	/**
	 * Callback for the `cn_log_bulk_actions` hook which processes the action and then redirects back to the current admin page.
	 *
	 * @internal
	 * @since 8.3
	 */
	public static function logManagement() {

		$action = '';

		if ( current_user_can( 'install_plugins' ) ) {

			if ( isset( $_GET['action'] ) && '-1' !== $_GET['action'] ) {

				$action = sanitize_key( $_GET['action'] );

			} elseif ( isset( $_GET['action2'] ) && '-1' !== $_GET['action2'] ) {

				$action = sanitize_key( $_GET['action2'] );
			}

			switch ( $action ) {

				case 'delete':
					check_admin_referer( 'bulk-email' );

					foreach ( $_GET['log'] as $id ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.InputNotValidated

						cnLog::delete( absint( $id ) );
					}

					cnMessage::set( 'success', 'log_bulk_delete' );

					break;
			}

		} else {

			cnMessage::set( 'error', 'capability_manage_logs' );
		}

		$url = add_query_arg(
			array(
				'type'      => isset( $_GET['type'] ) && ! empty( $_GET['type'] ) && '-1' !== $_GET['type'] ? sanitize_key( $_GET['type'] ) : false,
				'cn-action' => false,
				'action'    => false,
				'action2'   => false,
			),
			wp_get_referer()
		);

		wp_safe_redirect( $url );
		exit();
	}

	/**
	 * Callback for the `cn_delete_log` hook which processes the delete action and then redirects back to the current admin page.
	 *
	 * @internal
	 * @since 8.3
	 */
	public static function deleteLog() {

		if ( current_user_can( 'install_plugins' ) ) {

			$id = Request\ID::input()->value();

			_validate::adminReferer( 'log_delete', $id );

			cnLog::delete( $id );

			cnMessage::set( 'success', 'log_delete' );

			$url = add_query_arg(
				array(
					'type' => isset( $_GET['type'] ) && ! empty( $_GET['type'] ) && '-1' !== $_GET['type'] ? sanitize_key( $_GET['type'] ) : false,
				),
				wp_get_referer()
			);

			wp_safe_redirect( $url );
			exit();
		}
	}

}
