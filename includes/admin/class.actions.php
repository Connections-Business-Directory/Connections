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

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class cnAdminActions
 */
class cnAdminActions {

	/**
	 * Stores the instance of this class.
	 *
	 * @access private
	 * @since 0.7.5
	 * @var (object)
	*/
	private static $instance;

	/**
	 * A dummy constructor to prevent the class from being loaded more than once.
	 *
	 * @access public
	 * @since 0.7.5
	 * @see cnAdminActions::init()
	 * @see cnAdminActions();
	 */
	public function __construct() { /* Do nothing here */ }

	/**
	 * Setup the class, if it has already been initialized, return the initialized instance.
	 *
	 * @access public
	 * @since 0.7.5
	 * @see cnAdminActions()
	 */
	public static function init() {

		if ( ! isset( self::$instance ) ) {

			self::$instance = new self;

			self::register();
			self::doActions();

		}

	}

	/**
	 * Return an instance of the class.
	 *
	 * @access public
	 * @since 0.7.5
	 * @return (object) cnAdminActions
	 */
	public static function getInstance() {

		return self::$instance;
	}

	/**
	 * Register admin actions.
	 *
	 * @access private
	 * @since  0.7.5
	 *
	 * @uses   add_action()
	 *
	 * @return void
	 */
	private static function register() {

		// Entry Actions
		add_action( 'cn_add_entry', array( __CLASS__, 'processEntry' ) );
		add_action( 'cn_update_entry', array( __CLASS__, 'processEntry' ) );
		add_action( 'cn_duplicate_entry', array( __CLASS__, 'processEntry' ) );
		add_action( 'cn_delete_entry', array( __CLASS__, 'deleteEntry' ) );
		add_action( 'cn_set_status', array( __CLASS__, 'setEntryStatus' ) );

		// Process entry categories.
		add_action( 'cn_process_taxonomy-category', array( __CLASS__, 'processEntryCategory' ), 9, 2 );

		// Entry Meta Action
		add_action( 'cn_process_meta-entry', array( __CLASS__, 'processEntryMeta' ), 9, 2 );

		// Save the user's manage admin page actions.
		add_action( 'cn_manage_actions', array( __CLASS__, 'entryManagement' ) );
		add_action( 'cn_filter', array( __CLASS__, 'userFilter' ) );

		// Role Actions
		add_action( 'cn_update_role_capabilities', array( __CLASS__, 'updateRoleCapabilities' ) );

		// Category Actions
		add_action( 'cn_add_category', array( __CLASS__, 'addCategory' ) );
		add_action( 'cn_update_category', array( __CLASS__, 'updateCategory' ) );
		add_action( 'cn_delete_category', array( __CLASS__, 'deleteCategory' ) );
		add_action( 'cn_category_bulk_actions', array( __CLASS__, 'categoryManagement' ) );

		// Term Actions
		add_action( 'cn_add-term', array( __CLASS__, 'addTerm' ) );
		add_action( 'cn_update-term', array( __CLASS__, 'updateTerm' ) );
		add_action( 'cn_delete-term', array( __CLASS__, 'deleteTerm' ) );
		add_action( 'cn_bulk-term-action', array( __CLASS__, 'bulkTerm' ) );

		// Template Actions
		add_action( 'cn_activate_template', array( __CLASS__, 'activateTemplate' ) );
		add_action( 'cn_delete_template', array( __CLASS__, 'deleteTemplate' ) );

		// Term Meta Actions
		add_action( 'cn_delete_term', array( __CLASS__, 'deleteTermMeta' ), 10, 4 );

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

		// Setup the plugins_api() arguments.
		add_filter( 'install_plugins_table_api_args_connections', array( __CLASS__, 'installArgs' ) );
	}

	/**
	 * Run admin actions.
	 *
	 * @access private
	 * @since  0.7.5
	 *
	 * @uses   do_action()
	 *
	 * @return void
	 */
	private static function doActions() {

		if ( isset( $_POST['cn-action'] ) ) {

			do_action( 'cn_' . $_POST['cn-action'] );
		}

		if ( isset( $_GET['cn-action'] ) ) {

			do_action( 'cn_' . $_GET['cn-action'] );
		}
	}

	/**
	 * AJAX callback used to download the system info.
	 *
	 * @access private
	 * @since  8.3
	 * @static
	 */
	public static function downloadSystemInfo() {

		check_ajax_referer( 'download_system_info' );

		if ( ! current_user_can( 'manage_options' ) ) {

			wp_send_json( __( 'You do not have sufficient permissions to download system information.', 'connections' ) );
		}

		cnSystem_Info::download();
	}

	/**
	 * AJAX callback to email the system info.
	 *
	 * @access private
	 * @since  8.3
	 * @static
	 */
	public static function emailSystemInfo() {

		$form = new cnFormObjects();

		check_ajax_referer( $form->getNonce( 'email_system_info' ), 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {

			wp_send_json( -2 );
		}

		/**
		 * Since email is sent via an ajax request, let's check for the appropriate header.
		 * @link http://davidwalsh.name/detect-ajax
		 */
		if ( ! isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) || 'xmlhttprequest' != strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) ) {

			wp_send_json( -3 );
		}

		$user = wp_get_current_user();

		$atts = array(
			'from_email' => $user->user_email,
			'from_name'  => $user->display_name,
			'to_email'   => $_POST['email'],
			'subject'    => $_POST['subject'],
			'message'    => $_POST['message'],
		);

		$response = cnSystem_Info::email( $atts );

		if ( $response ) {

			// Success, send success code.
			wp_send_json( 1 );

		} else {

			/** @var PHPMailer $phpmailer */
			global $phpmailer;

			wp_send_json( $phpmailer->ErrorInfo );
		}
	}

	/**
	 * AJAX callback to create a secret URL for the system info.
	 *
	 * @access private
	 * @since  8.3
	 * @static
	 */
	public static function generateSystemInfoURL() {

		if ( ! check_ajax_referer( 'generate_remote_system_info_url', FALSE, FALSE ) ) {

			wp_send_json_error( __( 'Invalid AJAX action or nonce validation failed.', 'connections' ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {

			wp_send_json_error( __( 'You do not have sufficient permissions to perform this action.', 'connections' ) );
		}

		/** @todo need to check the $token is not WP_Error. */
		$token   = cnString::random( 32 );
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
				'url' => $url,
				'message' => __( 'Secret URL has been created.', 'connections' ),
			)
		);
	}

	/**
	 * AJAX callback to revoke the secret URL for the system info.
	 *
	 * @access private
	 * @since  8.3
	 * @static
	 */
	public static function revokeSystemInfoURL() {

		if ( ! check_ajax_referer( 'revoke_remote_system_info_url', FALSE, FALSE ) ) {

			wp_send_json_error( __( 'Invalid AJAX action or nonce validation failed.', 'connections' ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {

			wp_send_json_error( __( 'You do not have sufficient permissions to perform this action.', 'connections' ) );
		}

		cnCache::clear( 'system_info_remote_token', 'option-cache' );

		wp_send_json_success( __( 'Secret URL has been revoked.', 'connections' ) );
	}

	/**
	 * AJAX callback to download the settings in a JSON encoded text file.
	 *
	 * @access private
	 * @since  8.3
	 * @static
	 */
	public static function downloadSettings() {

		check_ajax_referer( 'export_settings' );

		if ( ! current_user_can( 'manage_options' ) ) {

			wp_send_json( __( 'You do not have sufficient permissions to export the settings.', 'connections' ) );
		}

		cnSettingsAPI::download();
	}

	/**
	 * AJAX callback to import settings from a JSON encoded text file.
	 *
	 * @access private
	 * @since  8.3
	 * @static
	 */
	public static function importSettings() {

		check_ajax_referer( 'import_settings' );

		if ( ! current_user_can( 'manage_options' ) ) {

			wp_send_json( __( 'You do not have sufficient permissions to import the settings.', 'connections' ) );
		}

		if ( 'json' != pathinfo( $_FILES['import_file']['name'], PATHINFO_EXTENSION ) ) {

			wp_send_json( __( 'Please upload a .json file.', 'connections' ) );
		}

		$file = $_FILES['import_file']['tmp_name'];

		if ( empty( $file ) ) {

			wp_send_json( __( 'Please select a file to import.', 'connections' ) );
		}

		$json   = file_get_contents( $file );
		$result = cnSettingsAPI::import( $json );

		if ( TRUE === $result ) {

			wp_send_json( __( 'Settings have been imported.', 'connections' ) );

		} else {

			wp_send_json( $result );
		}
	}

	/**
	 * Admin ajax callback to download the CSV file.
	 *
	 * @access private
	 * @since  8.5
	 *
	 * @uses   do_action()
	 * @uses   wp_verify_nonce()
	 * @uses   wp_die()
	 * @uses   __()
	 * @uses   cnCSV_Batch_Export_Addresses()
	 * @uses   cnCSV_Batch_Export_Phone_Numbers()
	 * @uses   cnCSV_Batch_Export_Email()
	 * @uses   cnCSV_Batch_Export_Dates()
	 */
	public static function csvExportBatchDownload() {

		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'cn-batch-export-download' ) ) {

			wp_die( __( 'Nonce verification failed.', 'connections' ), __( 'Error', 'connections' ), array( 'response' => 403 ) );
		}

		$type = esc_attr( $_REQUEST['type'] );

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
	 * Admin ajax callback to batch export the addresses.
	 *
	 * @access private
	 * @since  8.5
	 *
	 * @uses   check_ajax_referer()
	 * @uses   absint()
	 * @uses   cnCSV_Batch_Export_Addresses()
	 * @uses   wp_create_nonce()
	 * @uses   cnAdminActions::csvBatchExport()
	 */
	public static function csvExportAddresses() {

		check_ajax_referer( 'export_csv_addresses' );

		require_once CN_PATH . 'includes/export/class.csv-export.php';
		require_once CN_PATH . 'includes/export/class.csv-export-batch.php';
		require_once CN_PATH . 'includes/export/class.csv-export-batch-addresses.php';

		$step   = absint( $_POST['step'] );
		$export = new cnCSV_Batch_Export_Addresses();
		$nonce  = wp_create_nonce( 'export_csv_addresses' );

		self::csvBatchExport( $export, 'address', $step, $nonce );
	}

	/**
	 * Save the user's defined height of the category metabox.
	 *
	 * Callback for the `wp_ajax_set_category_div_height` action.
	 *
	 * @access private
	 * @since  8.6.5
	 */
	public static function setUserCategoryDivHeight() {

		check_ajax_referer( 'set_category_div_height' );

		$height = absint( $_POST['height'] );

		if ( Connections_Directory()->currentUser->setCategoryDivHeight( $height ) ) {

			wp_send_json_success(
				array(
					'message' => 'Success!',
					'nonce'   => wp_create_nonce( 'set_category_div_height' ),
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
	 * @access private
	 * @since  8.6.8
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
	 * @access private
	 * @since  8.6.8
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
				'last_updated'    => TRUE,
				'icons'           => TRUE,
				'active_installs' => TRUE,
			),
			// Send the locale and installed plugin slugs to the API so it can provide context-sensitive results.
			'locale'   => get_user_locale(),
			//'installed_plugins' => $this->get_installed_plugin_slugs(),
		);

		$args['installed_plugins'] = array( 'connections' );
		$args['author'] = 'shazahm1hotmailcom';
		//$args['search'] = 'Connections Business Directory';

		add_action( 'install_plugins_connections', array( __CLASS__, 'installResults' ), 9, 1 );

		return $args;
	}

	/**
	 * Callback for the `install_plugins_connections` action.
	 *
	 * @see wp-admin/plugin-install.php
	 *
	 * @access private
	 * @since  8.6.8
	 *
	 * @param int $page The current page number of the plugins list table.
	 */
	public static function installResults( $page ) {

		/** @var WP_Plugin_Install_List_Table $wp_list_table */
		global $wp_list_table;

		foreach ( $wp_list_table->items as $key => &$item ) {

			// Remove the core plugin.
			if ( 'connections' === $item->slug ) unset( $wp_list_table->items[ $key ] );

			// Remove any items which do not have Connections in its name.
			if ( FALSE === strpos( $item->name, 'Connections' ) ) unset( $wp_list_table->items[ $key ] );
		}

		// Save the items from the original query.
		$core            = $wp_list_table->items;

		// Affiliate URL and preg replace pattern.
		$tslAffiliateURL = 'https://tinyscreenlabs.com/?tslref=connections';
		$pattern         = "/(?<=href=(\"|'))[^\"']+(?=(\"|'))/";

		$mam = plugins_api(
			'plugin_information',
			array(
				'slug'              => 'mobile-app-manager-for-connections',
				'fields'            => array(
					'last_updated'    => TRUE,
					'icons'           => TRUE,
					'active_installs' => TRUE,
				),
				'locale'            => get_user_locale(),
				'installed_plugins' => array( 'connections' ),
			)
		);

		$offers = plugins_api(
			'plugin_information',
			array(
				'slug'              => 'connections-business-directory-offers',
				'fields'            => array(
					'last_updated'    => TRUE,
					'icons'           => TRUE,
					'active_installs' => TRUE,
				),
				'locale'            => get_user_locale(),
				'installed_plugins' => array( 'connections' ),
			)
		);

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

		?>
		<form id="plugin-filter" method="post">
			<?php
			//$wp_list_table->display();
			$wp_list_table->_pagination_args = array();

			if ( ! is_wp_error( $mam ) ) {

				// Update the links to TSL to use the affiliate URL.
				$mam->homepage = $tslAffiliateURL;
				$mam->author = preg_replace( $pattern, $tslAffiliateURL, $mam->author );

				$wp_list_table->items = array( $mam );
				self::installDisplayGroup( 'Featured' );
			}

			if ( 0 < count( $core ) ) {
				$wp_list_table->items = $core;
				self::installDisplayGroup( 'Free' );
			}

			if ( ! is_wp_error( $offers ) ) {

				// Update the links to TSL to use the affiliate URL.
				$offers->homepage = $tslAffiliateURL;
				$offers->author = preg_replace( $pattern, $tslAffiliateURL, $offers->author );

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

		// needs an extra wrapping div for nth-child selectors to work
		?>
		<div class="plugin-group"><h3> <?php echo esc_html( $name ); ?></h3>
			<div class="plugin-items">

				<?php $wp_list_table->display(); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Admin ajax callback to batch export the phone numbers.
	 *
	 * @access private
	 * @since  8.5
	 *
	 * @uses   check_ajax_referer()
	 * @uses   absint()
	 * @uses   cnCSV_Batch_Export_Phone_Numbers()
	 * @uses   wp_create_nonce()
	 * @uses   cnAdminActions::csvBatchExport()
	 */
	public static function csvExportPhoneNumbers() {

		check_ajax_referer( 'export_csv_phone_numbers' );

		require_once CN_PATH . 'includes/export/class.csv-export.php';
		require_once CN_PATH . 'includes/export/class.csv-export-batch.php';
		require_once CN_PATH . 'includes/export/class.csv-export-batch-phone-numbers.php';

		$step   = absint( $_POST['step'] );
		$export = new cnCSV_Batch_Export_Phone_Numbers();
		$nonce  = wp_create_nonce( 'export_csv_phone_numbers' );

		self::csvBatchExport( $export, 'phone', $step, $nonce );
	}

	/**
	 * Admin ajax callback to batch export the email addresses.
	 *
	 * @access private
	 * @since  8.5
	 *
	 * @uses   check_ajax_referer()
	 * @uses   absint()
	 * @uses   cnCSV_Batch_Export_Email()
	 * @uses   wp_create_nonce()
	 * @uses   cnAdminActions::csvBatchExport()
	 */
	public static function csvExportEmail() {

		check_ajax_referer( 'export_csv_email' );

		require_once CN_PATH . 'includes/export/class.csv-export.php';
		require_once CN_PATH . 'includes/export/class.csv-export-batch.php';
		require_once CN_PATH . 'includes/export/class.csv-export-batch-email.php';

		$step   = absint( $_POST['step'] );
		$export = new cnCSV_Batch_Export_Email();
		$nonce  = wp_create_nonce( 'export_csv_email' );

		self::csvBatchExport( $export, 'email', $step, $nonce );
	}

	/**
	 * Admin ajax callback to batch export the dates.
	 *
	 * @access private
	 * @since  8.5
	 *
	 * @uses   check_ajax_referer()
	 * @uses   absint()
	 * @uses   cnCSV_Batch_Export_Dates()
	 * @uses   wp_create_nonce()
	 * @uses   cnAdminActions::csvBatchExport()
	 */
	public static function csvExportDates() {

		check_ajax_referer( 'export_csv_dates' );

		require_once CN_PATH . 'includes/export/class.csv-export.php';
		require_once CN_PATH . 'includes/export/class.csv-export-batch.php';
		require_once CN_PATH . 'includes/export/class.csv-export-batch-dates.php';

		$step   = absint( $_POST['step'] );
		$export = new cnCSV_Batch_Export_Dates();
		$nonce  = wp_create_nonce( 'export_csv_dates' );

		self::csvBatchExport( $export, 'date', $step, $nonce );
	}

	/**
	 * Admin ajax callback to batch export the category data.
	 *
	 * @access private
	 * @since  8.5.5
	 *
	 * @uses   check_ajax_referer()
	 * @uses   absint()
	 * @uses   cnCSV_Batch_Export_Dates()
	 * @uses   wp_create_nonce()
	 * @uses   cnAdminActions::csvBatchExport()
	 */
	public static function csvExportTerm() {

		check_ajax_referer( 'export_csv_term' );

		require_once CN_PATH . 'includes/export/class.csv-export.php';
		require_once CN_PATH . 'includes/export/class.csv-export-batch.php';
		require_once CN_PATH . 'includes/export/class.csv-export-batch-category.php';

		$step   = absint( $_POST['step'] );
		$export = new cnCSV_Batch_Export_Term();
		$nonce  = wp_create_nonce( 'export_csv_term' );

		self::csvBatchExport( $export, 'category', $step, $nonce );
	}

	/**
	 * Admin ajax callback to batch import the term data.
	 *
	 * @access private
	 * @since  8.5.5
	 *
	 * @uses   check_ajax_referer()
	 * @uses   absint()
	 * @uses   cnCSV_Batch_Export_Dates()
	 * @uses   wp_create_nonce()
	 * @uses   cnAdminActions::csvBatchExport()
	 */
	public static function csvImportTerm() {

		check_ajax_referer( 'import_csv_term' );

		if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'import_csv_term' ) ) {

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

		if ( empty( $_REQUEST['file']['type'] ) ||
		     ( ! in_array( wp_unslash( $_REQUEST['file']['type'] ), array( 'text/csv', 'text/plain' ), TRUE ) ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'The uploaded file does not appear to be a CSV file.', 'connections' ),
					'request' => $_REQUEST,
				)
			);
		}

		if ( ! file_exists( $_REQUEST['file']['path'] ) ) {
			wp_send_json_error(
				array(
					'message' => __(
						'Something went wrong during the upload process, please try again.',
						'connections'
					),
					'request' => $_REQUEST,
				)
			);
		}

		require_once CN_PATH . 'includes/import/class.csv-import-batch.php';
		require_once CN_PATH . 'includes/import/class.csv-import-batch-category.php';

		$step   = absint( $_REQUEST['step'] );
		$import = new cnCSV_Batch_Import_Term( $_REQUEST['file']['path'] );
		$nonce  = wp_create_nonce( 'import_csv_term' );

		self::csvBatchImport( $import, 'category', $step, $nonce );
	}

	/**
	 * Admin ajax callback to batch export the all entry data.
	 *
	 * @access private
	 * @since  8.5.1
	 *
	 * @uses   check_ajax_referer()
	 * @uses   absint()
	 * @uses   cnCSV_Batch_Export_Dates()
	 * @uses   wp_create_nonce()
	 * @uses   cnAdminActions::csvBatchExport()
	 */
	public static function csvExportAll() {

		check_ajax_referer( 'export_csv_all' );

		require_once CN_PATH . 'includes/export/class.csv-export.php';
		require_once CN_PATH . 'includes/export/class.csv-export-batch.php';
		require_once CN_PATH . 'includes/export/class.csv-export-batch-all.php';

		$step   = absint( $_POST['step'] );
		$export = new cnCSV_Batch_Export_All();
		$nonce  = wp_create_nonce( 'export_csv_all' );

		self::csvBatchExport( $export, 'all', $step, $nonce );
	}

	/**
	 * Common CSV batch export code to start the batch export step and provide the JSON response.
	 *
	 * @access private
	 * @since  8.5
	 *
	 * @uses   wp_send_json_error()
	 * @uses   is_wp_error()
	 * @uses   wp_send_json_success()
	 * @uses   wp_create_nonce()
	 * @uses   self_admin_url()
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

			$step += 1;

			wp_send_json_success(
				array(
					'step'       => $step,
					'count'      => $count,
					'exported'   => $exported,
					'remaining'  => $remaining,
					'percentage' => $percentage,
					'nonce'      => $nonce,
				)
			);

		} elseif ( TRUE === $export->is_empty ) {

			wp_send_json_error(
				array(
					'message' => __( 'No data found for export parameters.', 'connections' )
				)
			);

		} else {

			$args = array(
				'cn-action' => 'download_batch_export',
				'type'      => $type,
				'nonce'     => wp_create_nonce( 'cn-batch-export-download' ),
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

	public static function uploadCSV() {

		//if ( ! function_exists( 'wp_handle_upload' ) ) {
		//
		//	require_once( ABSPATH . 'wp-admin/includes/file.php' );
		//}

		require_once CN_PATH . 'includes/import/class.csv-import-batch.php';

		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'csv_upload' ) ) {

			wp_send_json_error(
				array(
					'form'    => $_POST,
					'message' => __( 'Nonce verification failed', 'connections' )
				)
			);
		}

		if ( ! (bool) apply_filters( 'cn_csv_import_capability', current_user_can( 'import' ) ) ) {

			wp_send_json_error(
				array(
					'form'    => $_POST,
					'message' => __( 'You do not have permission to import data.', 'connections' )
				)
			);
		}

		if ( empty( $_FILES ) ) {
			wp_send_json_error(
				array(
					'form'    => $_POST,
					'message' => __( 'No file file selected. Please select a file to import.', 'connections' ),
					'request' => $_REQUEST,
				)
			);
		}

		$upload = new cnUpload(
			$_FILES['cn-import-file'],
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

				error_log( print_r( $headers, TRUE ) );

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
					'nonce'   => wp_create_nonce( 'import_csv_term' ),
				)
			);

		} else {

			wp_send_json_error(
				array(
					'form'    => $_POST,
					'message' => $result->get_error_message()
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
	 * @uses   wp_send_json_error()
	 * @uses   is_wp_error()
	 * @uses   wp_send_json_success()
	 * @uses   wp_create_nonce()
	 * @uses   self_admin_url()
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
					'form'    => $_POST,
					'message' => __( 'You do not have permission to export data.', 'connections' ),
				)
			);
		}

		/**
		 * Prevent the taxonomy hierarchy from being purged and built after each term insert because
		 * it severely slows down the import as the number of terms being imported increases.
		 * @see cnTerm::cleanCache()
		 */
		add_filter( "pre_option_cn_{$taxonomy}_children", '__return_empty_array' );

		$import->setMap( json_decode( wp_unslash( $_REQUEST['map'] ) ) );

		$result = $import->process( $step );

		if ( is_wp_error( $result ) ) {

			wp_send_json_error(
				array(
					'form'    => $_POST,
					'message' => $result->get_error_message(),
				)
			);
		}

		if ( $result ) {

			$count      = $import->getCount();
			$imported   = $step * $import->limit > $count ? $count : $step * $import->limit;
			$remaining  = 0 < $count - $imported ? $count - $imported : 0;
			$percentage = $import->getPercentageComplete();

			$step += 1;

			wp_send_json_success(
				array(
					'map'        => json_encode( $import->getMap() ),
					'step'       => $step,
					'count'      => $count,
					'imported'   => $imported,
					'remaining'  => $remaining,
					'percentage' => $percentage,
					'nonce'      => $nonce,
				)
			);

		} else {

			$url = add_query_arg( array( 'page' => 'connections_tools', 'tab' => 'import' ), self_admin_url( 'admin.php' ) );

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
	 * Process controller for action taken by the user.
	 *
	 * @access private
	 * @since  0.7.8
	 *
	 * @uses   wp_redirect()
	 * @uses   get_admin_url()
	 * @uses   get_current_blog_id()
	 *
	 * @return void
	 */
	public static function entryManagement() {

		$form = new cnFormObjects();
		$queryVar = array();

		check_admin_referer( $form->getNonce( 'cn_manage_actions' ), '_cn_wpnonce' );

		/*
		 * Run user requested actions.
		 */

		// Process user selected filters
		self::saveUserFilters();

		// Grab the bulk action requested by user.
		$action = isset( $_POST['bulk_action'] ) && ( isset( $_POST['action'] ) && ! empty( $_POST['action'] ) ) ? $_POST['action'] : 'none';

		switch ( $action ) {

			case 'delete':

				// Bulk delete entries.
				self::deleteEntryBulk();
				break;

			case 'approve':

				// Bulk approve entries.
				self::setEntryStatusBulk( 'approved' );
				break;

			case 'unapprove':

				// Bulk unapprove entries.
				self::setEntryStatusBulk( 'pending' );
				break;

			case 'public':

				// Set entries to public visibility in bulk.
				self::setEntryVisibilityBulk( 'public' );
				break;

			case 'private':

				// Set entries to private visibility in bulk.
				self::setEntryVisibilityBulk( 'private' );
				break;

			case 'unlisted':

				// Set entries to unlisted visibility in bulk.
				self::setEntryVisibilityBulk( 'unlisted' );
				break;

			default:

				/* None, blank intentionally. */

				break;
		}

		/*
		 * Setup the redirect.
		 */

		if ( isset( $_REQUEST['s'] ) && ! empty( $_REQUEST['s'] ) ) {

			$queryVar['s'] = urlencode( wp_unslash( $_REQUEST['s'] ) );
		}

		// if ( isset( $_GET['s'] ) && ! empty( $_GET['s'] ) )
		// 	$queryVar['s'] = urlencode( $_GET['s'] );

		if ( isset( $_GET['cn-char'] ) && 0 < strlen( $_GET['cn-char'] ) )
			$queryVar['cn-char'] = urlencode( $_GET['cn-char'] );

		/*
		 * Do the redirect.
		 */

		wp_redirect( get_admin_url( get_current_blog_id(), add_query_arg( $queryVar, 'admin.php?page=connections_manage' ) ) );

		exit();
	}

	/**
	 * Add / Edit / Update / Copy an entry.
	 *
	 * @access public
	 * @since  0.7.8
	 *
	 * @uses wp_redirect()
	 * @uses get_admin_url()
	 * @uses get_current_blog_id()
	 *
	 * @return void
	 */
	public static function processEntry() {

		$form  = new cnFormObjects();

		$action = isset( $_GET['cn-action'] ) ? $_GET['cn-action'] : $_POST['cn-action'];

		// Setup the redirect URL.
		$redirect = isset( $_POST['redirect'] ) ? $_POST['redirect'] : 'admin.php?page=connections_add';

		switch ( $action ) {

			case 'add_entry':

				/*
				 * Check whether the current user can add an entry.
				 */
				if ( current_user_can( 'connections_add_entry' ) || current_user_can( 'connections_add_entry_moderated' ) ) {

					check_admin_referer( $form->getNonce( 'add_entry' ), '_cn_wpnonce' );

					cnEntry_Action::add( $_POST );

				} else {

					cnMessage::set( 'error', 'capability_add' );
				}

				break;

			case 'copy_entry':

				/*
				 * Check whether the current user can add an entry.
				 */
				if ( current_user_can( 'connections_add_entry' ) || current_user_can( 'connections_add_entry_moderated' ) ) {

					check_admin_referer( $form->getNonce( 'add_entry' ), '_cn_wpnonce' );

					cnEntry_Action::copy( $_GET['id'], $_POST );

				} else {

					cnMessage::set( 'error', 'capability_add' );
				}

				break;

			case 'update_entry':

				// Setup the redirect URL.
				$redirect = isset( $_POST['redirect'] ) ? $_POST['redirect'] : 'admin.php?page=connections_manage';

				/*
				 * Check whether the current user can edit an entry.
				 */
				if ( current_user_can( 'connections_edit_entry' ) || current_user_can( 'connections_edit_entry_moderated' ) ) {

					check_admin_referer( $form->getNonce( 'update_entry' ), '_cn_wpnonce' );

					cnEntry_Action::update( $_GET['id'], $_POST );

				} else {

					cnMessage::set( 'error', 'capability_edit' );
				}

				break;
		}

		// do_action( 'cn_process_meta-entry', $action, $id );
		// do_action( 'cn_process_meta-entry-' . $action, $action, $id );

		wp_redirect( get_admin_url( get_current_blog_id(), $redirect) );

		exit();
	}

	/**
	 * Add, update or delete the entry categories.
	 *
	 * @access public
	 * @since  0.8
	 * @param  string $action The action to being performed to an entry.
	 * @param  int    $id     The entry ID.
	 *
	 * @return void
	 */
	public static function processEntryCategory( $action, $id ) {

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		/*
		 * Save the entry category(ies). If none were checked, send an empty array
		 * which will add the entry to the default category.
		 */
		if ( isset( $_POST['entry_category'] ) && ! empty( $_POST['entry_category'] ) ) {

			$instance->term->setTermRelationships( $id, $_POST['entry_category'], 'category' );

		} else {

			$default = get_option( 'cn_default_category' );

			$instance->term->setTermRelationships( $id, $default, 'category' );
		}

	}

	/**
	 * Add, update or delete the entry meta data.
	 *
	 * @access public
	 * @since  0.8
	 * @param  string $action The action to being performed to an entry.
	 * @param  int    $id     The entry ID.
	 *
	 * @return mixed          array | bool  An array of meta IDs or FALSE on failure.
	 */
	public static function processEntryMeta( $action, $id ) {

		/** @var wpdb $wpdb */
		global $wpdb;

		if ( ! $id = absint( $id ) ) return FALSE;

		$meta       = array();
		$newmeta    = array();
		$metaSelect = array();
		$metaIDs    = array();

		switch ( $action ) {

			case 'add':

				if ( isset( $_POST['newmeta'] ) || ! empty( $_POST['newmeta'] ) ) {

					foreach ( $_POST['newmeta'] as $row ) {

						// If the key begins with an underscore, remove it because those are private.
						if ( isset( $row['key'][0] ) && '_' == $row['key'][0] ) $row['key'] = substr( $row['key'], 1 );

						$newmeta[] = cnMeta::add( 'entry', $id, $row['key'], $row['value'] );
					}
				}

				if ( isset( $_POST['metakeyselect'] ) && $_POST['metakeyselect'] !== '-1' ) {

					$metaSelect[] = cnMeta::add( 'entry', $id, $_POST['metakeyselect'], $_POST['newmeta']['99']['value'] );
				}

				$metaIDs['added'] = array_merge( $newmeta, $metaSelect );

				break;

			case 'copy':

				// Copy any meta associated with the source entry to the new entry.
				if ( isset( $_POST['meta'] ) || ! empty( $_POST['meta'] ) ) {

					foreach ( $_POST['meta'] as $row ) {

						// If the key begins with an underscore, remove it because those are private.
						if ( isset( $row['key'][0] ) && '_' == $row['key'][0] ) $row['key'] = substr( $row['key'], 1 );

						// Add the meta except for those that the user deleted for this entry.
						if ( $row['value'] !== '::DELETED::' ) $meta[] = cnMeta::add( 'entry', $id, $row['key'], $row['value'] );
					}
				}

				// Lastly, add any new meta the user may have added.
				if ( isset( $_POST['newmeta'] ) || ! empty( $_POST['newmeta'] ) ) {

					foreach ( $_POST['newmeta'] as $row ) {

						// If the key begins with an underscore, remove it because those are private.
						if ( isset( $row['key'][0] ) && '_' == $row['key'][0] ) $row['key'] = substr( $row['key'], 1 );

						$metaIDs[] = cnMeta::add( 'entry', $id, $row['key'], $row['value'] );
					}

					// $newmeta = cnMeta::add( 'entry', $id, $_POST['newmeta']['0']['key'], $_POST['newmeta']['99']['value'] );
				}

				if ( isset( $_POST['metakeyselect'] ) && $_POST['metakeyselect'] !== '-1' ) {

					$metaSelect[] = cnMeta::add( 'entry', $id, $_POST['metakeyselect'], $_POST['newmeta']['99']['value'] );
				}

				$metaIDs['added'] = array_merge( $meta, $newmeta, $metaSelect );

				break;

			case 'update':

				// Query the meta associated to the entry.
				//$results = cnMeta::get( 'entry', $id );
				$results =  $wpdb->get_results( $wpdb->prepare("SELECT meta_key, meta_value, meta_id, entry_id
							FROM " . CN_ENTRY_TABLE_META . " WHERE entry_id = %d
							ORDER BY meta_key,meta_id", $id ), ARRAY_A );

				if ( $results !== FALSE ) {

					// Loop thru $results removing any custom meta fields. Custom meta fields are considered to be private.
					foreach ( $results as $metaID => $row ) {

						if ( cnMeta::isPrivate( $row['meta_key'] ) ) unset( $results[ $row['meta_id'] ] );
					}

					// Loop thru the associated meta and update any that may have been changed.
					// If the meta id doesn't exist in the $_POST data, assume the user deleted it.
					foreach ( $results as $metaID => $row ) {

						// Update the entry meta if it differs.
						if ( ( isset( $_POST['meta'][ $row['meta_id'] ]['value'] ) && $_POST['meta'][ $row['meta_id'] ]['value'] !== $row['meta_value'] ) ||
							 ( isset( $_POST['meta'][ $row['meta_id'] ]['key'] )   && $_POST['meta'][ $row['meta_id'] ]['key']   !== $row['meta_key']   ) &&
							 ( $_POST['meta'][ $row['meta_id'] ]['value'] !== '::DELETED::' ) ) {

							// If the key begins with an underscore, remove it because those are private.
							//if ( isset( $row['key'][0] ) && '_' == $row['key'][0] ) $row['key'] = substr( $row['key'], 1 );

							//cnMeta::update( 'entry', $id, $_POST['meta'][ $row['meta_id'] ]['key'], $_POST['meta'][ $row['meta_id'] ]['value'], $row['meta_value'], $row['meta_key'], $row['meta_id'] );
							cnMeta::updateByID( 'entry', $row['meta_id'], $_POST['meta'][ $row['meta_id'] ]['value'], $_POST['meta'][ $row['meta_id'] ]['key'] );

							$metaIDs['updated'] = $row['meta_id'];
						}

						if ( isset( $_POST['meta'][ $row['meta_id'] ]['value'] ) && $_POST['meta'][ $row['meta_id'] ]['value'] === '::DELETED::' ) {

							// Record entry meta to be deleted.
							cnMeta::deleteByID( 'entry', $row['meta_id'] );

							$metaIDs['deleted'] = $row['meta_id'];
						}

					}
				}

				// Lastly, add any new meta the user may have added.
				if ( isset( $_POST['newmeta'] ) || ! empty( $_POST['newmeta'] ) ) {

					foreach ( $_POST['newmeta'] as $row ) {

						// If the key begins with an underscore, remove it because those are private.
						if ( isset( $row['key'][0] ) && '_' == $row['key'][0] ) $row['key'] = substr( $row['key'], 1 );

						$metaIDs[] = cnMeta::add( 'entry', $id, $row['key'], $row['value'] );
					}

					// $newmeta = cnMeta::add( 'entry', $id, $_POST['newmeta']['0']['key'], $_POST['newmeta']['99']['value'] );
				}

				if ( isset( $_POST['metakeyselect'] ) && $_POST['metakeyselect'] !== '-1' ) {

					$metaSelect[] = cnMeta::add( 'entry', $id, $_POST['metakeyselect'], $_POST['newmeta']['99']['value'] );
				}

				$metaIDs['added'] = array_merge( $newmeta, $metaSelect );

				break;
		}

		return $metaIDs;
	}

	/**
	 * Set the entry status to pending or approved.
	 *
	 * @access public
	 * @since  0.7.8
	 *
	 * @uses absint()
	 * @uses wp_redirect()
	 * @uses get_admin_url()
	 * @uses get_current_blog_id()
	 *
	 * @param int $id [optional] Entry ID.
	 * @param string $status [optional] The entry status to be assigned.
	 *
	 * @return void
	 */
	public static function setEntryStatus( $id = 0, $status = '' ) {

		// If no entry ID was supplied, check $_GET.
		$id = empty( $id ) && ( isset( $_GET['id'] ) && ! empty( $_GET['id'] ) ) ? absint( $_GET['id'] ) : absint( $id );

		check_admin_referer( 'entry_status_' . $id );

		/*
		 * Check whether the current user can edit an entry.
		 */
		if ( current_user_can( 'connections_edit_entry' ) ) {

			//  The permitted statuses.
			$permitted = array( 'pending', 'approved' );

			// If `status` was not supplied, check $_GET.
			if ( ( empty( $status ) ) && ( isset( $_GET['status'] ) && ! empty( $_GET['status'] ) ) ) {

				$status = $_GET['status'];

			}

			// Ensure the supplied status is a permitted status, else default `status` to `pending`.
			// If no `status` was supplied, this will default `status` to `pending`.
			$status = in_array( $status, $permitted ) ? $status : 'pending';

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

		wp_redirect( get_admin_url( get_current_blog_id(), 'admin.php?page=connections_manage' ) );

		exit();
	}

	/**
	 * Set the approval status of entries in bulk.
	 *
	 * @access public
	 * @since  0.7.8
	 *
	 * @param  string $status The entry status that should be set.
	 *
	 * @return void
	 */
	public static function setEntryStatusBulk( $status ) {

		/*
		 * Check whether the current user can edit entries.
		 */
		if ( current_user_can( 'connections_edit_entry' ) ) {

			$permitted = array( 'pending', 'approved' );

			if ( ! in_array( $status, $permitted ) ) return;

			cnEntry_Action::status( $status, $_POST['id'] );

			switch ( $status ) {

				case 'pending':

					cnMessage::set( 'success', 'form_entry_pending_bulk' );
					break;

				case 'approved':

					cnMessage::set( 'success', 'form_entry_approve_bulk' );
					break;
			}

		} else {

			cnMessage::set( 'error', 'capability_edit' );
		}
	}

	/**
	 * Set the visibility status of entries in bulk.
	 *
	 * @access public
	 * @since  0.7.8
	 *
	 * @param  string $visibility The entry visibility that should be set.
	 *
	 * @return void
	 */
	static public function setEntryVisibilityBulk( $visibility ) {

		/*
		 * Check whether the current user can edit entries.
		 */
		if ( current_user_can( 'connections_edit_entry' ) ) {

			$permitted = array( 'public', 'private', 'unlisted' );

			if ( ! in_array( $visibility, $permitted ) ) return;

			cnEntry_Action::visibility( $visibility, $_POST['id'] );

			cnMessage::set( 'success', 'form_entry_visibility_bulk' );

		} else {

			cnMessage::set( 'error', 'capability_edit' );
		}
	}

	/**
	 * Delete an entry.
	 *
	 * @access public
	 * @since  0.7.8
	 *
	 * @uses absint()
	 * @uses wp_redirect()
	 * @uses get_admin_url()
	 * @uses get_current_blog_id()
	 *
	 * @param int $id [optional] Entry ID.
	 *
	 * @return void
	 */
	public static function deleteEntry( $id = 0 ) {

		// If no entry ID was supplied, check $_GET.
		$id = empty( $id ) && ( isset( $_GET['id'] ) && ! empty( $_GET['id'] ) ) ? $_GET['id'] : $id;

		check_admin_referer( 'entry_delete_' . $id );

		/*
		 * Check whether the current user delete an entry.
		 */
		if ( current_user_can( 'connections_delete_entry' ) ) {

			cnEntry_Action::delete( $id );

			cnMessage::set( 'success', 'form_entry_delete' );

		} else {

			cnMessage::set( 'error', 'capability_delete' );
		}

		wp_redirect( get_admin_url( get_current_blog_id(), 'admin.php?page=connections_manage' ) );

		exit();
	}

	/**
	 * Delete entries in bulk.
	 *
	 * @access public
	 * @since  0.7.8
	 *
	 * @return void
	 */
	public static function deleteEntryBulk() {

		/*
		 * Check whether the current user delete an entry.
		 */
		if ( current_user_can( 'connections_delete_entry' ) ) {

			// @TODO $POST['id'] should be passed to the method as an attribute.
			if ( ! isset( $_POST['id'] ) || empty( $_POST['id'] ) ) return;

			cnEntry_Action::delete( $_POST['id'] );

			cnMessage::set( 'success', 'form_entry_delete_bulk' );

		} else {

			cnMessage::set( 'error', 'capability_delete' );
		}

	}

	/**
	 * Process user filters.
	 *
	 * @access public
	 * @since 0.7.8
	 *
	 * @uses check_admin_referer()
	 * @uses wp_redirect()
	 * @uses get_admin_url()
	 * @uses get_current_blog_id()
	 *
	 * @return void
	 */
	public static function userFilter() {

		$queryVar = array();

		check_admin_referer( 'filter' );

		self::saveUserFilters();

		/*
		 * Setup the redirect.
		 */

		if ( isset( $_REQUEST['s'] ) && ! empty( $_REQUEST['s'] ) ) {
			$queryVar['s'] = urlencode( $_REQUEST['s'] );
		}

		if ( isset( $_GET['cn-char'] ) && 0 < strlen( $_GET['cn-char'] ) ) {
			$queryVar['cn-char'] = urlencode( $_GET['cn-char'] );
		}

		/*
		 * Do the redirect.
		 */

		wp_redirect( get_admin_url( get_current_blog_id(), add_query_arg( $queryVar, 'admin.php?page=connections_manage' ) ) );

		exit();
	}

	/**
	 * Save user filters.
	 *
	 * @access public
	 * @since  0.7.8
	 *
	 * @return void
	 */
	public static function saveUserFilters() {

		/** @var connectionsLoad $connections */
		global $connections;

		// Set the moderation filter for the current user if set in the query string.
		if ( isset( $_GET['status'] ) ) $connections->currentUser->setFilterStatus( $_GET['status'] );

		if ( isset( $_POST['entry_type'] ) ) $connections->currentUser->setFilterEntryType( esc_attr( $_POST['entry_type'] ) );
		if ( isset( $_POST['visibility_type'] ) ) $connections->currentUser->setFilterVisibility( esc_attr( $_POST['visibility_type'] ) );

		if ( isset( $_POST['category'] ) /*&& ! empty( $_POST['category'] )*/ ) $connections->currentUser->setFilterCategory( absint( $_POST['category'] ) );
		if ( isset( $_GET['category'] ) /*&& ! empty( $_GET['category'] )*/ ) $connections->currentUser->setFilterCategory( absint( $_GET['category'] ) );

		if ( isset( $_POST['pg'] ) && ! empty( $_POST['pg'] ) ) {
			$page = new stdClass();

			$page->name = 'manage';
			$page->current = absint( $_POST['pg'] );

			$connections->currentUser->setFilterPage( $page );
		}

		if ( isset( $_GET['pg'] ) && ! empty( $_GET['pg'] ) ) {
			$page = new stdClass();

			$page->name = 'manage';
			$page->current = absint( $_GET['pg'] );

			$connections->currentUser->setFilterPage( $page );
		}

		if ( isset( $_POST['settings']['page']['limit'] ) ) {
			$page = new stdClass();

			$page->name = 'manage';
			$page->limit = $_POST['settings']['page']['limit'];

			$connections->currentUser->setFilterPage( $page );
		}

	}

	/**
	 * Add a term.
	 *
	 * @access public
	 * @since  8.6.12
	 */
	public static function addTerm() {

		/*
		 * Check whether user can edit terms.
		 */
		if ( current_user_can( 'connections_edit_categories' ) ) {

			$form = new cnFormObjects();

			check_admin_referer( $form->getNonce( 'add-term' ), '_cn_wpnonce' );

			$result = cnTerm::insert(
				$_POST['term_name'],
				$_POST['taxonomy'],
				array(
					'slug'        => $_POST['term_slug'],
					'parent'      => $_POST['term_parent'],
					'description' => $_POST['term_description'],
				)
			);

			if ( is_wp_error( $result ) ) {

				cnMessage::set( 'error', $result->get_error_message() );

			} else {

				cnMessage::set( 'success', 'term_added' );
			}

			wp_safe_redirect( wp_get_raw_referer() );

			exit();

		} else {

			cnMessage::set( 'error', 'capability_categories' );
		}

	}

	/**
	 * Update a category.
	 *
	 * @access public
	 * @since 0.7.7
	 * @uses current_user_can()
	 * @uses check_admin_referer()
	 * @uses wp_redirect()
	 * @uses get_admin_url()
	 * @uses get_current_blog_id()
	 * @return void
	 */
	public static function updateTerm() {
		$form = new cnFormObjects();

		/*
		 * Check whether user can edit Settings
		 */
		if ( current_user_can( 'connections_edit_categories' ) ) {

			check_admin_referer( $form->getNonce( 'update-term' ), '_cn_wpnonce' );

			// Make sure the category isn't being set to itself as a parent.
			if ( $_POST['term_id'] === $_POST['term_parent'] ) {

				cnMessage::set( 'error', 'category_self_parent' );
			}

			remove_filter( 'pre_term_description', 'wp_filter_kses' );

			$result = cnTerm::update(
				$_POST['term_id'],
				$_POST['taxonomy'],
				array(
					'name'        => $_POST['term_name'],
					'slug'        => $_POST['term_slug'],
					'parent'      => $_POST['term_parent'],
					'description' => $_POST['term_description'],
				)
			);

			if ( is_wp_error( $result ) ) {

				cnMessage::set( 'error', $result->get_error_message() );

			} else {

				cnMessage::set( 'success', 'term_updated' );
			}

			wp_safe_redirect( wp_get_raw_referer() );

			exit();

		} else {

			cnMessage::set( 'error', 'capability_categories' );
		}

	}

	public static function deleteTerm() {

		// Use legacy action callback when deleting categories, for now.
		if ( 'category' == $_REQUEST['taxonomy'] ) {

			self::deleteCategory();
		}

		/*
		 * Check whether user can edit terms.
		 */
		if ( current_user_can( 'connections_edit_categories' ) ) {

			$id = esc_attr( $_REQUEST['id'] );
			check_admin_referer( 'term_delete_' . $id );

			$result = cnTerm::delete( $id, $_REQUEST['taxonomy'] );

			if ( is_wp_error( $result ) ) {

				cnMessage::set( 'error', $result->get_error_message() );

			} else {

				cnMessage::set( 'success', 'term_deleted' );
			}

			wp_safe_redirect( wp_get_raw_referer() );

			exit();

		} else {

			cnMessage::set( 'error', 'capability_categories' );
		}
	}

	/**
	 * Bulk term actions.
	 *
	 * @access public
	 * @since  8.6.12
	 */
	public static function bulkTerm() {

		$action   = '';

		if ( isset( $_REQUEST['action'] ) && '-1' !== $_REQUEST['action'] ) {

			$action = $_REQUEST['action'];

		} elseif ( isset( $_REQUEST['action2'] ) && '-1' !== $_REQUEST['action2'] ) {

			$action = $_REQUEST['action2'];
		}

		/*
		 * Check whether user can edit terms.
		 */
		if ( current_user_can( 'connections_edit_categories' ) ) {

			check_admin_referer( 'bulk-terms' );

			switch ( $action ) {

				case 'delete':

					foreach ( (array) $_REQUEST[ $_REQUEST['taxonomy'] ] as $id ) {

						$result = cnTerm::delete( $id, $_REQUEST['taxonomy'] );

						if ( is_wp_error( $result ) ) {

							cnMessage::set( 'error', $result->get_error_message() );

						} else {

							cnMessage::set( 'success', 'term_deleted' );
						}
					}

					break;

				default:

					do_action( "bulk_term_action-{$_REQUEST['taxonomy']}-{$action}" );
			}

			$url = wp_get_raw_referer();

			if ( isset( $_REQUEST['paged'] ) && ! empty( $_REQUEST['paged'] ) ) {

				$page = absint( $_REQUEST['paged'] );

				$url = add_query_arg( array( 'paged' => $page ) , $url);
			}

			wp_redirect( $url );

			exit();

		} else {

			cnMessage::set( 'error', 'capability_categories' );
		}

	}

	/**
	 * Add a category.
	 *
	 * @access public
	 * @since 0.7.7
	 * @uses current_user_can()
	 * @uses check_admin_referer()
	 * @uses wp_redirect()
	 * @uses get_admin_url()
	 * @uses get_current_blog_id()
	 * @return void
	 */
	public static function addCategory() {
		$form = new cnFormObjects();

		/*
		 * Check whether user can edit Settings
		 */
		if ( current_user_can( 'connections_edit_categories' ) ) {

			check_admin_referer( $form->getNonce( 'add_category' ), '_cn_wpnonce' );

			$category = new cnCategory();
			$format   = new cnFormatting();

			$category->setName( $format->sanitizeString( $_POST['category_name'] ) );
			$category->setSlug( $format->sanitizeString( $_POST['category_slug'] ) );
			$category->setParent( $format->sanitizeString( $_POST['category_parent'] ) );
			$category->setDescription( $format->sanitizeString( $_POST['category_description'], TRUE ) );

			$category->save();

			wp_redirect( get_admin_url( get_current_blog_id(), 'admin.php?page=connections_categories' ) );

			exit();

		} else {

			cnMessage::set( 'error', 'capability_categories' );
		}

	}

	/**
	 * Update a category.
	 *
	 * @access public
	 * @since 0.7.7
	 * @uses current_user_can()
	 * @uses check_admin_referer()
	 * @uses wp_redirect()
	 * @uses get_admin_url()
	 * @uses get_current_blog_id()
	 * @return void
	 */
	public static function updateCategory() {
		$form = new cnFormObjects();

		/*
		 * Check whether user can edit Settings
		 */
		if ( current_user_can( 'connections_edit_categories' ) ) {

			check_admin_referer( $form->getNonce( 'update_category' ), '_cn_wpnonce' );

			$category = new cnCategory();
			$format   = new cnFormatting();

			$category->setID( $format->sanitizeString( $_POST['category_id'] ) );
			$category->setName( $format->sanitizeString( $_POST['category_name'] ) );
			$category->setParent( $format->sanitizeString( $_POST['category_parent'] ) );
			$category->setSlug( $format->sanitizeString( $_POST['category_slug'] ) );
			$category->setDescription( $format->sanitizeString( $_POST['category_description'], TRUE ) );

			$category->update();

			wp_redirect( get_admin_url( get_current_blog_id(), 'admin.php?page=connections_categories' ) );

			exit();

		} else {

			cnMessage::set( 'error', 'capability_categories' );
		}

	}

	/**
	 * Delete a category.
	 *
	 * @access public
	 * @since 0.7.7
	 * @uses current_user_can()
	 * @uses check_admin_referer()
	 * @uses wp_redirect()
	 * @uses get_admin_url()
	 * @uses get_current_blog_id()
	 * @return void
	 */
	public static function deleteCategory() {
		global $connections;

		/*
		 * Check whether user can edit Settings
		 */
		if ( current_user_can( 'connections_edit_categories' ) ) {

			$id = esc_attr( $_GET['id'] );
			check_admin_referer( 'term_delete_' . $id );

			$result = $connections->retrieve->category( $id );
			$category = new cnCategory( $result );
			$category->delete();

			wp_redirect( get_admin_url( get_current_blog_id(), 'admin.php?page=connections_categories' ) );

			exit();

		} else {

			cnMessage::set( 'error', 'capability_categories' );
		}

	}

	/**
	 * Callback to delete the term meta when when a term is deleted.
	 *
	 * @access private
	 * @since  8.2
	 * @static
	 *
	 * @param int    $term          Term ID.
	 * @param int    $tt_id         Term taxonomy ID.
	 * @param string $taxonomy      Taxonomy slug.
	 * @param mixed  $deleted_term  Copy of the already-deleted term, in the form specified
	 *                              by the parent function. WP_Error otherwise.
	 */
	public static function deleteTermMeta( $term, $tt_id, $taxonomy, $deleted_term ) {

		if ( ! is_wp_error( $deleted_term ) ) {

			$meta = cnMeta::get( 'term', $term );

			if ( ! empty( $meta ) ) {

				foreach ( $meta as $key => $value ) {

					cnMeta::delete( 'term', $term, $key );
				}
			}
		}
	}

	/**
	 * Bulk category actions.
	 *
	 * @access public
	 * @since 0.7.7
	 * @uses current_user_can()
	 * @uses check_admin_referer()
	 * @uses wp_redirect()
	 * @uses get_admin_url()
	 * @uses get_current_blog_id()
	 * @return void
	 */
	public static function categoryManagement() {

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();
		$action   = '';

		if ( isset( $_REQUEST['action'] ) && '-1' !== $_REQUEST['action'] ) {

			$action = $_REQUEST['action'];

		} elseif ( isset( $_REQUEST['action2'] ) && '-1' !== $_REQUEST['action2'] ) {

			$action = $_REQUEST['action2'];
		}

		/*
		 * Check whether user can edit Settings
		 */
		if ( current_user_can( 'connections_edit_categories' ) ) {

			switch ( $action ) {

				case 'delete':

					check_admin_referer( 'bulk-terms' );

					foreach ( (array) $_POST['category'] as $id ) {

						$result = $instance->retrieve->category( absint( $id ) );
						$category = new cnCategory( $result );
						$category->delete();
					}

					break;

				default:

					do_action( "bulk_term_action-category-{$action}" );
				}

			$url = get_admin_url( get_current_blog_id(), 'admin.php?page=connections_categories' );

			if ( isset( $_REQUEST['paged'] ) && ! empty( $_REQUEST['paged'] ) ) {

				$page = absint( $_REQUEST['paged'] );

				$url = add_query_arg( array( 'paged' => $page ) , $url);
			}

			wp_redirect( $url );

			exit();

		} else {

			cnMessage::set( 'error', 'capability_categories' );
		}

	}

	/**
	 * Activate a template.
	 *
	 * @access public
	 * @since 0.7.7
	 * @uses current_user_can()
	 * @uses check_admin_referer()
	 * @uses delete_transient()
	 * @uses wp_redirect()
	 * @uses get_admin_url()
	 * @uses get_current_blog_id()
	 * @uses add_query_arg()
	 * @return void
	 */
	public static function activateTemplate() {

		/** @var $connections connectionsLoad */
		global $connections;

		/*
		 * Check whether user can manage Templates
		 */
		if ( current_user_can( 'connections_manage_template' ) ) {

			$templateName = esc_attr( $_GET['template'] );
			check_admin_referer( 'activate_' . $templateName );

			$type = esc_attr( $_GET['type'] );
			$slug = esc_attr( $_GET['template'] );

			$connections->options->setActiveTemplate( $type, $slug );

			$connections->options->saveOptions();

			cnMessage::set( 'success', 'template_change_active' );

			delete_transient( 'cn_legacy_templates' );

			! isset( $_GET['type'] ) ? $tab = 'all' : $tab = esc_attr( $_GET['type'] );

			wp_redirect( get_admin_url( get_current_blog_id(), add_query_arg( array( 'type' => $tab ) , 'admin.php?page=connections_templates' ) ) );

			exit();

		} else {

			cnMessage::set( 'error', 'capability_settings' );
		}
	}

	/**
	 * Delete a template.
	 *
	 * @TODO Move the delete to a generic method in cnFileSystem()
	 *
	 * @access public
	 * @since 0.7.7
	 * @uses current_user_can()
	 * @uses check_admin_referer()
	 * @uses delete_transient()
	 * @uses wp_redirect()
	 * @uses get_admin_url()
	 * @uses get_current_blog_id()
	 * @uses add_query_arg()
	 * @return void
	 */
	public static function deleteTemplate() {

		/*
		 * Check whether user can manage Templates
		 */
		if ( current_user_can( 'connections_manage_template' ) ) {

			$templateName = esc_attr( $_GET['template'] );
			check_admin_referer( 'delete_' . $templateName );

			function removeDirectory( $directory ) {
				$deleteError = FALSE;
				$currentDirectory = opendir( $directory );

				while ( ( $file = readdir( $currentDirectory ) ) !== FALSE ) {

					if ( $file != "." && $file != ".." ) {

						chmod( $directory . $file, 0777 );

						if ( is_dir( $directory . $file ) ) {

							chdir( '.' );
							removeDirectory( $directory . $file . '/' );
							rmdir( $directory . $file ) or $deleteError = TRUE;

						} else {

							@unlink( $directory . $file ) or $deleteError = TRUE;
						}

						if ( $deleteError ) return FALSE;
					}
				}

				closedir( $currentDirectory );

				if ( ! rmdir( $directory ) ) return FALSE;

				return TRUE;
			}

			if ( removeDirectory( CN_CUSTOM_TEMPLATE_PATH . '/' . $templateName . '/' ) ) {
				cnMessage::set( 'success', 'template_deleted' );
			} else {
				cnMessage::set( 'error', 'template_delete_failed' );
			}

			delete_transient( 'cn_legacy_templates' );

			! isset( $_GET['type'] ) ? $tab = 'all' : $tab = esc_attr( $_GET['type'] );

			wp_redirect( get_admin_url( get_current_blog_id(), add_query_arg( array( 'type' => $tab ) , 'admin.php?page=connections_templates' ) ) );

			exit();

		} else {

			cnMessage::set( 'error', 'capability_settings' );
		}
	}

	/**
	 * Update the role settings.
	 *
	 * @access private
	 * @since 0.7.5
	 * @uses current_user_can()
	 * @uses check_admin_referer()
	 * @uses wp_redirect()
	 * @uses get_admin_url()
	 * @uses get_current_blog_id()
	 * @return void
	 */
	public static function updateRoleCapabilities() {

		/** @var $wp_roles WP_Roles */
		global $wp_roles;

		$form = new cnFormObjects();

		/*
		 * Check whether user can edit roles
		 */
		if ( current_user_can( 'connections_change_roles' ) ) {

			check_admin_referer( $form->getNonce( 'update_role_settings' ), '_cn_wpnonce' );

			if ( isset( $_POST['roles'] ) ) {

				// Cycle thru each role available because checkboxes do not report a value when not checked.
				foreach ( $wp_roles->get_names() as $role => $name ) {

					if ( ! isset( $_POST['roles'][ $role ] ) ) continue;

					foreach ( $_POST['roles'][ $role ]['capabilities'] as $capability => $grant ) {

						// the administrator should always have all capabilities
						if ( $role == 'administrator' ) continue;

						if ( $grant == 'true' ) {
							cnRole::add( esc_attr( $role ), esc_attr( $capability ) );
						} else {
							cnRole::remove( esc_attr( $role ), esc_attr( $capability ) );
						}

					}
				}
			}

			if ( isset( $_POST['reset'] ) ) cnRole::reset( array_map( 'esc_attr', $_POST['reset'] ) );

			if ( isset( $_POST['reset_all'] ) ) cnRole::reset();

			cnMessage::set( 'success', 'role_settings_updated' );

			wp_redirect( get_admin_url( get_current_blog_id(), 'admin.php?page=connections_roles' ) );
			exit();

		} else {

			cnMessage::set( 'error', 'capability_roles' );
		}

	}

	/**
	 * Callback for the cn_log_bulk_actions hook which processes the action and then redirects back to the current admin page.
	 *
	 * @access private
	 * @since  8.3
	 * @static
	 *
	 * @uses   current_user_can()
	 * @uses   check_admin_referer()
	 * @uses   cnLog::delete()
	 * @uses   cnMessage::set()
	 * @uses   add_query_arg()
	 * @uses   wp_get_referer()
	 * @uses   wp_safe_redirect()
	 */
	public static function logManagement() {

		$action = '';

		if ( current_user_can( 'install_plugins' ) ) {

			if ( isset( $_GET['action'] ) && '-1' !== $_GET['action'] ) {

				$action = $_GET['action'];

			} elseif ( isset( $_GET['action2'] ) && '-1' !== $_GET['action2'] ) {

				$action = $_GET['action2'];

			}

			switch ( $action ) {

				case 'delete':

					check_admin_referer( 'bulk-email' );

					foreach ( $_GET['log'] as $id ) {

						cnLog::delete( $id );
					}

					cnMessage::set( 'success', 'log_bulk_delete' );

					break;
			}

		} else {

			cnMessage::set( 'error', 'capability_manage_logs' );
		}

		$url = add_query_arg(
			array(
				'type'      => isset( $_GET['type'] ) && ! empty( $_GET['type'] ) && '-1' !== $_GET['type'] ? $_GET['type'] : FALSE,
				'cn-action' => FALSE,
				'action'    => FALSE,
				'action2'   => FALSE,
			),
			wp_get_referer()
		);

		wp_safe_redirect( $url );
		exit();
	}

	/**
	 * Callback for the cn_delete_log hook which processes the delete action and then redirects back to the current admin page.
	 *
	 * @access private
	 * @since  8.3
	 * @static
	 *
	 * @uses   current_user_can()
	 * @uses   check_admin_referer()
	 * @uses   cnLog::delete()
	 * @uses   cnMessage::set()
	 * @uses   add_query_arg()
	 * @uses   wp_get_referer()
	 * @uses   wp_safe_redirect()
	 */
	public static function deleteLog() {

		if ( current_user_can( 'install_plugins' ) ) {

			$id = 0;

			if ( isset( $_GET['id'] ) && ! empty( $_GET['id'] ) ) {

				$id = absint( $_GET['id'] );
			}

			check_admin_referer( 'log_delete_' . $id );

			cnLog::delete( $id );

			cnMessage::set( 'success', 'log_delete' );

			$url = add_query_arg(
				array(
					'type' => isset( $_GET['type'] ) && ! empty( $_GET['type'] ) && '-1' !== $_GET['type'] ? $_GET['type'] : FALSE,
				),
				wp_get_referer()
			);

			wp_safe_redirect( $url );
			exit();
		}
	}

}
