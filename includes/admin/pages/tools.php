<?php
/**
 * The tools admin page.
 *
 * @package     Connections
 * @subpackage  The tools admin page.
 * @copyright   Copyright (c) 2015, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       8.3
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Connections_Directory\Utility\_escape;
use Connections_Directory\Utility\_nonce;

function connectionsShowToolsPage() {

	/*
	 * Check whether user can edit Settings
	 */
	if ( ! current_user_can( 'edit_posts' ) ) {

		wp_die(
			'<p id="error-page" style="-moz-background-clip:border;
			-moz-border-radius:11px;
			background:#FFFFFF none repeat scroll 0 0;
			border:1px solid #DFDFDF;
			color:#333333;
			display:block;
			font-size:12px;
			line-height:18px;
			margin:25px auto 20px;
			padding:1em 2em;
			text-align:center;
			width:700px">' . esc_html__( 'You do not have sufficient permissions to access this page.', 'connections' ) . '</p>'
		);

	} else {

		$tabs = cnAdmin_Tools::getTabs();

		?>
		<div class="wrap">
		<?php

		if ( ! empty( $tabs ) ) {

			$first_tab    = $tabs[0];
			$active_tab   = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : $first_tab['id']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$current_page = self_admin_url( 'admin.php?page=connections_tools' );

			?>

			<h2 class="nav-tab-wrapper">
				<?php

				foreach ( $tabs as $tab ) {

					$tab_url = add_query_arg( array( 'tab' => $tab['id'] ), $current_page );
					$active  = $active_tab === $tab['id'] ? 'nav-tab-active' : '';

					echo '<a href="' . esc_url( $tab_url ) . '" title="' . esc_attr( $tab['name'] ) . '" class="' . _escape::classNames( array( 'nav-tab', $active ) ) . '">' . esc_html( $tab['name'] ) . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}

				?>
			</h2>

			<div class="metabox-holder">
				<?php do_action( 'cn_tools_tab_' . $active_tab ); ?>
			</div><!-- .metabox-holder -->

			<?php

		} else {

			?>

			<p id="error-page" style="-moz-background-clip:border;
			-moz-border-radius:11px;
			background:#FFFFFF none repeat scroll 0 0;
			border:1px solid #DFDFDF;
			color:#333333;
			display:block;
			font-size:12px;
			line-height:18px;
			margin:25px auto 20px;
			padding:1em 2em;
			text-align:center;
			width:700px"><?php esc_html_e( 'There are no tools available for your to use.', 'connections' ); ?></p>

			<?php

		}

		?>
		</div><!-- .wrap -->

		<?php
	}
}

/**
 * Class cnAdmin_Tools
 *
 * @phpcs:disable PEAR.NamingConventions.ValidClassName.StartWithCapital
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound
 */
class cnAdmin_Tools {

	/**
	 * Stores the instance of this class.
	 *
	 * @since 8.3
	 *
	 * @var cnAdmin_Tools
	 */
	private static $instance;

	/**
	 * A dummy constructor to prevent the class from being loaded more than once.
	 *
	 * @access public
	 * @since  8.3
	 */
	public function __construct() {
		/* Do nothing here */
	}

	/**
	 * @since 8.3
	 *
	 * @return cnAdmin_Tools
	 */
	public static function instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof cnAdmin_Tools ) ) {

			self::init();
		}

		return self::$instance;
	}

	/**
	 * Register the admin tool actions.
	 *
	 * @since 8.3
	 */
	private static function init() {

		self::$instance = new self();

		foreach ( self::getTabs() as $tab ) {

			add_action( 'cn_tools_tab_' . $tab['id'], $tab['callback'] );
		}

		add_action( 'cn_tools_system_after', array( __CLASS__, 'systemInfoEmail' ) );
		add_action( 'cn_tools_system_after', array( __CLASS__, 'systemInfoRemote' ) );
	}

	/**
	 * Register the tabs.
	 *
	 * @since 8.3
	 *
	 * @return array
	 */
	private static function registerTabs() {

		$tabs = array(
			array(
				'id'         => 'export',
				'name'       => __( 'Export', 'connections' ),
				'callback'   => array( __CLASS__, 'export' ),
				'capability' => 'export',
			),
			array(
				'id'         => 'import',
				'name'       => __( 'Import', 'connections' ),
				'callback'   => array( __CLASS__, 'import' ),
				'capability' => 'import',
			),
			array(
				'id'         => 'system_info',
				'name'       => __( 'System Information', 'connections' ),
				'callback'   => array( __CLASS__, 'systemInfo' ),
				'capability' => 'manage_options',
			),
			array(
				'id'         => 'settings_import_export',
				'name'       => __( 'Settings Import/Export', 'connections' ),
				'callback'   => array( __CLASS__, 'settingsImportExport' ),
				'capability' => 'manage_options',
			),
			array(
				'id'         => 'reset',
				'name'       => __( 'Reset', 'connections' ),
				'callback'   => array( __CLASS__, 'reset' ),
				'capability' => 'manage_options',
			),
			array(
				'id'         => 'logs',
				'name'       => __( 'Logs', 'connections' ),
				'callback'   => array( __CLASS__, 'logs' ),
				'capability' => 'manage_options',
			),
		);

		/**
		 * Filter to allow the registration of new admin tool tabs.
		 *
		 * @since 8.3
		 *
		 * @param array $tabs {
		 *     @type string       $id       The tab ID.
		 *     @type string       $name     The display name of the tab.
		 *     @type string|array $callback The tab callback to display the tab content.
		 * }
		 */
		return apply_filters( 'cn_admin_tools_tabs', $tabs );
	}

	/**
	 * Retrieve tools tabs.
	 *
	 * @since 8.3
	 */
	public static function getTabs() {

		$tabs = array();

		foreach ( self::registerTabs() as $tab ) {

			if ( current_user_can( $tab['capability'] ) ) {

				$tabs[] = $tab;
			}
		}

		return $tabs;
	}

	/**
	 * Callback to render export data tools.
	 *
	 * @internal
	 * @since 8.5
	 */
	public static function export() {

		if ( ! current_user_can( 'export' ) ) {
			return;
		}

		do_action( 'cn_tools_export_before' );

		?>
		<div class="postbox">
			<h3><span><?php _e( 'Export Addresses', 'connections' ); ?></span></h3>

			<div class="inside">

				<form id="cn-export-addresses" class="cn-export-form" method="post">

					<p>
						<?php
						_e(
							'Export the entry names and their addresses as a CSV File.',
							'connections'
						);
						?>
					</p>

					<p class="submit">
						<input type="submit" class="button-secondary" name="csv-export-addresses" value="<?php _e( 'Export', 'connections' ); ?>" data-action="export_csv_addresses" data-nonce="<?php echo esc_attr( _nonce::create( 'export_csv_addresses' ) ); ?>"/>
					</p>

				</form>

			</div><!-- .inside -->
		</div><!-- .postbox -->

		<div class="postbox">
			<h3><span><?php _e( 'Export Phone Numbers', 'connections' ); ?></span></h3>

			<div class="inside">

				<form id="cn-export-phone-numbers" class="cn-export-form" method="post">

					<p>
						<?php
						_e(
							'Export the entry names and their phone numbers as a CSV File.',
							'connections'
						);
						?>
					</p>

					<p class="submit">
						<input type="submit" class="button-secondary" name="csv-export-phone-numbers" value="<?php _e( 'Export', 'connections' ); ?>" data-action="export_csv_phone_numbers" data-nonce="<?php echo esc_attr( _nonce::create( 'export_csv_phone_numbers' ) ); ?>"/>
					</p>

				</form>

			</div><!-- .inside -->
		</div><!-- .postbox -->

		<div class="postbox">
			<h3><span><?php _e( 'Export Email Addresses', 'connections' ); ?></span></h3>

			<div class="inside">

				<form id="cn-export-email" class="cn-export-form" method="post">

					<p>
						<?php
						_e(
							'Export the entry names and email addresses as a CSV File.',
							'connections'
						);
						?>
					</p>

					<p class="submit">
						<input type="submit" class="button-secondary" name="csv-export-email" value="<?php _e( 'Export', 'connections' ); ?>" data-action="export_csv_email" data-nonce="<?php echo esc_attr( _nonce::create( 'export_csv_email' ) ); ?>"/>
					</p>

				</form>

			</div><!-- .inside -->
		</div><!-- .postbox -->

		<div class="postbox">
			<h3><span><?php _e( 'Export Dates', 'connections' ); ?></span></h3>

			<div class="inside">

				<form id="cn-export-dates" class="cn-export-form" method="post">

					<p>
						<?php
						_e(
							'Export the entry names and dates as a CSV File.',
							'connections'
						);
						?>
					</p>

					<p class="submit">
						<input type="submit" class="button-secondary" name="csv-export-dates" value="<?php _e( 'Export', 'connections' ); ?>" data-action="export_csv_dates" data-nonce="<?php echo esc_attr( _nonce::create( 'export_csv_dates' ) ); ?>"/>
					</p>

				</form>

			</div><!-- .inside -->
		</div><!-- .postbox -->

		<div class="postbox">
			<h3><span><?php _e( 'Export Categories', 'connections' ); ?></span></h3>

			<div class="inside">

				<form id="cn-export-term" class="cn-export-form" method="post">

					<p>
						<?php
						_e(
							'Export the categories as a CSV File.',
							'connections'
						);
						?>
					</p>

					<p class="submit">
						<input type="submit" class="button-secondary" name="csv-export-term" value="<?php _e( 'Export', 'connections' ); ?>" data-action="export_csv_term" data-nonce="<?php echo esc_attr( _nonce::create( 'export_csv_term' ) ); ?>"/>
					</p>

				</form>

			</div><!-- .inside -->
		</div><!-- .postbox -->

		<div class="postbox">
			<h3><span><?php _e( 'Export All', 'connections' ); ?></span></h3>

			<div class="inside">

				<form id="cn-export-all" class="cn-export-form" method="post">

					<p>
						<?php
						_e(
							'Export the entry data as a CSV File.',
							'connections'
						);
						?>
					</p>

					<p class="submit">
						<input type="submit" class="button-secondary" name="csv-export-all" value="<?php _e( 'Export', 'connections' ); ?>" data-action="export_csv_all" data-nonce="<?php echo esc_attr( _nonce::create( 'export_csv_all' ) ); ?>"/>
					</p>

				</form>

			</div><!-- .inside -->
		</div><!-- .postbox -->

		<?php
		wp_enqueue_script( 'cn-csv-export' );
		do_action( 'cn_tools_export_after' );
	}

	/**
	 * Callback to render import data tools.
	 *
	 * @internal
	 * @since 8.5.5
	 */
	public static function import() {

		do_action( 'Connections_Directory/Admin/Page/Tools/Tab/Import/Before' );
		do_action( 'Connections_Directory/Admin/Page/Tools/Tab/Import' );
		do_action( 'Connections_Directory/Admin/Page/Tools/Tab/Import/After' );
	}

	/**
	 * Callback to display the system info.
	 *
	 * @internal
	 * @since 8.3
	 */
	public static function systemInfo() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		/**
		 * Run before the display of the system info
		 *
		 * @since 8.3
		 */
		do_action( 'cn_tools_system_before' );
		?>

		<div class="postbox">
			<h3><span><?php _e( 'System Information', 'connections' ); ?></span></h3>

			<div class="inside">

					<textarea readonly="readonly" onclick="this.focus();this.select()" name="cn-system-info" title="<?php _e( 'To copy the System Info, click below then press Ctrl + C (PC) or Cmd + C (Mac).', 'connections' ); ?>" style="background: #32373c; color: rgba(240,245,250,.7); display: block; width: 100%; height: 500px; font-size: 12px; font-family: 'Consolas', 'Monaco', monospace; white-space: pre; overflow: auto; padding: 10px;">
<?php
// phpcs:disable Generic.WhiteSpace.ScopeIndent.Incorrect
// Non-standard indentation needed for plain-text display.
cnSystem_Info::display();
// phpcs:enable Generic.WhiteSpace.ScopeIndent.Incorrect
?>
					</textarea>

				<?php // Form used to download .txt file. ?>
				<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( self_admin_url( 'admin-ajax.php' ) ); ?>">
					<input type="hidden" name="action" value="download_system_info"/>
					<?php _nonce::field( 'download_system_info' ); ?>
					<p>
					<?php
					submit_button( __( 'Download System Info as Text File', 'connections' ), 'primary', 'submit', false );
					submit_button(
						__( 'Copy to Clipboard', 'connections' ),
						'secondary',
						'copy-system-info',
						false,
						array(
							'onclick' => 'document.querySelector(\'textarea[name="cn-system-info"]\').select();document.execCommand("copy");return false;',
							'style'   => 'margin-left: 5px;',
						)
					);
					?>
					</p>
				</form>
			</div><!-- .inside -->
		</div><!-- .postbox -->

		<?php

		wp_enqueue_script( 'cn-system-info' );

		/**
		 * Run after the display of the system info.
		 *
		 * @since 8.3
		 */
		do_action( 'cn_tools_system_after' );
	}

	/**
	 * Callback to display the email the system info.
	 *
	 * @internal
	 * @since 8.3
	 */
	public static function systemInfoEmail() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		do_action( 'cn_tools_email_system_info_before' );

		?>

		<div class="postbox">
			<h3><span><?php _e( 'Send to:', 'connections' ); ?></span></h3>

			<div class="inside">

				<div id="cn-email-response"></div>

				<form id="cn-send-system-info" method="post" enctype="multipart/form-data" action="<?php echo esc_url( self_admin_url( 'admin-ajax.php' ) ); ?>">
					<table class="form-table">
						<tr>
							<th scope="row">
								<label for="cn-email-address">
									<?php _e( 'Email Address', 'connections' ); ?>
								</label>
							</th>
							<td>
								<input type="email" name="email" id="cn-email-address" class="regular-text" placeholder="<?php _e( 'user@email.com', 'connections' ); ?>"/>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="cn-email-subject">
									<?php _e( 'Subject', 'connections' ); ?>
								</label>
							</th>
							<td>
								<input type="text" name="subject" id="cn-email-subject" class="regular-text" placeholder="<?php _e( 'Subject', 'connections' ); ?>"/>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="cn-email-message">
									<?php _e( 'Additional Message', 'connections' ); ?>
								</label>
							</th>
							<td>
								<textarea name="message" id="cn-email-message" class="large-text" rows="10" cols="50" placeholder="<?php _e( 'Enter additional message here.', 'connections' ); ?>"></textarea>

								<p class="description">
									<?php
									_e(
										'Your system information will be attached automatically to this email.',
										'connections'
									)
									?>
								</p>

							</td>
						</tr>
					</table>
					<input type="hidden" name="action" value="email_system_info"/>
					<?php _nonce::field( 'email_system_info' ); ?>
					<?php submit_button( __( 'Send Email', 'connections' ), 'secondary', 'submit', true, array( 'id' => 'cn-send-system-info-submit' ) ); ?>
				</form>

			</div><!-- .inside -->
		</div><!-- .postbox -->

		<?php
		do_action( 'cn_tools_email_system_info_after' );
	}

	/**
	 * Callback to display the remote URL for the system info.
	 *
	 * @internal
	 * @since 8.3
	 */
	public static function systemInfoRemote() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$token = cnCache::get( 'system_info_remote_token', 'option-cache' );
		$url   = $token ? home_url() . '/?cn-system-info=' . $token : '';

		?>

		<div class="postbox">
			<h3><span><?php _e( 'Remote Viewing', 'connections' ); ?></span></h3>

			<div class="inside">

				<div id="cn-remote-response"></div>

				<p>
					<?php
					_e(
						'Create a secret URL that support can use to remotely view your system information. The secret URL will expire after 72 hours and can be revoked at any time.',
						'connections'
					)
					?>
				</p>

				<p>
					<input type="text" readonly="readonly" id="system-info-url" class="regular-text" onclick="this.focus();this.select()" value="<?php echo esc_url( $url ? $url : '' ); ?>" title="<?php _e( 'To copy the URL, click then press Ctrl + C (PC) or Cmd + C (Mac).', 'connections' ); ?>"/>&nbsp;&nbsp;<a class="button-secondary" href="<?php echo esc_url( $url ? $url : '#' ); ?>" target="_blank" id="system-info-url-text-link" style="display: <?php echo $url ? 'display-inline' : 'none'; ?>"><?php _e( 'Test', 'connections' ); ?></a>
				</p>

				<p class="submit">
					<input type="submit" onClick="return false;" class="button-secondary" name="generate-url" value="<?php _e( 'Generate URL', 'connections' ); ?>" data-nonce="<?php echo esc_attr( _nonce::create( 'generate_remote_system_info_url' ) ); ?>"/>
					<input type="submit" onClick="return false;" class="button-secondary" name="revoke-url" value="<?php _e( 'Revoke URL', 'connections' ); ?>" data-nonce="<?php echo esc_attr( _nonce::create( 'revoke_remote_system_info_url' ) ); ?>"/>
				</p>

			</div><!-- .inside -->
		</div><!-- .postbox -->

		<?php
	}

	/**
	 * Callback to render import/export settings.
	 *
	 * @internal
	 * @since 8.3
	 */
	public static function settingsImportExport() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		do_action( 'cn_tools_import_export_settings_before' );

		?>
		<div class="postbox">
			<h3><span><?php _e( 'Export Settings', 'connections' ); ?></span></h3>

			<div class="inside">
				<p>
					<?php
					_e(
						'Export the settings for this site as a .json file. This allows you to easily import the configuration into another site.',
						'connections'
					);
					?>
				</p>

				<form method="post" action="<?php echo esc_url( self_admin_url( 'admin-ajax.php' ) ); ?>">
					<input type="hidden" name="action" value="export_settings"/>
					<?php _nonce::field( 'export_settings' ); ?>
					<?php submit_button( __( 'Export', 'connections' ), 'secondary', 'submit' ); ?>
				</form>
			</div><!-- .inside -->
		</div><!-- .postbox -->

		<div class="postbox">
			<h3><span><?php _e( 'Import Settings', 'connections' ); ?></span></h3>

			<div class="inside">

				<div id="cn-import-settings-response"></div>

				<p>
					<?php
					_e(
						'Import the settings from a .json file. This file can be obtained by exporting the settings on another site using the form above.',
						'connections'
					);
					?>
				</p>

				<form id="cn-import-settings" method="post" enctype="multipart/form-data" action="<?php echo esc_url( self_admin_url( 'admin-ajax.php' ) ); ?>">
					<p>
						<input type="file" name="import_file"/>
					</p>

					<input type="hidden" name="action" value="import_settings"/>
					<?php _nonce::field( 'import_settings' ); ?>
					<?php submit_button( __( 'Import', 'connections' ), 'secondary', 'submit', true, array( 'id' => 'cn-import-settings-submit' ) ); ?>
				</form>
			</div><!-- .inside -->
		</div><!-- .postbox -->
		<?php
		wp_enqueue_script( 'cn-system-info' );
		do_action( 'cn_tools_import_export_settings_after' );
	}

	/**
	 * Render the Reset tools.
	 *
	 * @since 10.4.61
	 */
	public static function reset() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		do_action( 'cn_tools_reset_before' );

		?>
		<div class="postbox">
			<h3><span><?php esc_html_e( 'Reset Settings', 'connections' ); ?></span></h3>
			<div class="inside">
				<div id="cn-settings-reset-response"></div>
				<p>
					<?php
					esc_html_e(
						'Reset settings to the default values. This action can not be undone.',
						'connections'
					);
					?>
				</p>
				<form id="cn-settings-reset" method="post" action="<?php echo esc_url( self_admin_url( 'admin-ajax.php' ) ); ?>">
					<p>
						<input type="text" name="settings-reset-confirmation"> <?php esc_html_e( 'Type "reset" to confirm that you wish to reset the settings the the default values.', 'connections' ); ?>
					</p>
					<input type="hidden" name="action" value="settings-reset" />
					<?php _nonce::field( 'settings-reset' ); ?>
					<?php submit_button( __( 'Reset Settings', 'connections' ), 'secondary' ); ?>
				</form>
			</div><!-- .inside -->
		</div><!-- .postbox -->
		<div class="postbox">
			<h3><span><?php esc_html_e( 'Reset Database', 'connections' ); ?></span></h3>
			<div class="inside">
				<div id="cn-database-reset-response"></div>
				<p>
					<?php
					esc_html_e(
						'Reset all Connections Business Directory tables in the WordPress database. This action can not be undone.',
						'connections'
					);
					?>
				</p>
				<form id="cn-database-reset" method="post" action="<?php echo esc_url( self_admin_url( 'admin-ajax.php' ) ); ?>">
					<p>
						<input type="text" name="database-reset-confirmation"> <?php esc_html_e( 'Type "reset" to confirm that you wish to reset the directory database tables.', 'connections' ); ?>
					</p>
					<input type="hidden" name="action" value="database-reset" />
					<?php _nonce::field( 'database-reset' ); ?>
					<?php submit_button( __( 'Reset Database', 'connections' ), 'secondary' ); ?>
				</form>
			</div><!-- .inside -->
		</div><!-- .postbox -->
		<?php
		wp_enqueue_script( 'cn-system-info' );
		do_action( 'cn_tools_reset_after' );
	}

	/**
	 * Callback used to render the log view of the log type being viewed.
	 *
	 * @internal
	 * @since 8.3
	 */
	public static function logs() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$current = cnLog_Email::LOG_TYPE;
		$views   = wp_list_pluck( cnLog::views(), 'id' );
		$view    = isset( $_GET['view'] ) ? sanitize_text_field( $_GET['view'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( array_key_exists( $view, $views ) ) {
			$current = $view;
		}

		?>

		<div class="wrap" id="cn-logs">

			<form id="cn-log-type" method="get" action="<?php echo esc_url( self_admin_url( 'admin.php' ) ); ?>">

				<input type="hidden" name="page" value="connections_tools"/>
				<input type="hidden" name="tab" value="logs"/>

				<?php

				$allLogTypes   = wp_list_pluck( cnLog::types(), 'name', 'id' );
				$emailLogTypes = wp_list_pluck( cnLog_Email::types(), 'name', 'id' );

				unset( $emailLogTypes[ cnLog_Email::LOG_TYPE ] );

				cnHTML::select(
					array(
						'id'      => 'view',
						'options' => array_diff_assoc( $allLogTypes, $emailLogTypes ),
					),
					$current
				);

				submit_button(
					'Switch',
					'secondary',
					'action',
					false,
					array(
						'id' => 'log-type-submit',
					)
				);
				?>

			</form>

			<?php do_action( 'cn_logs_view_' . $current ); ?>

		</div>

		<?php
	}
}

cnAdmin_Tools::instance();
