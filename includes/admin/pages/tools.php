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

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function connectionsShowToolsPage() {

	/*
	 * Check whether user can edit Settings
	 */
	if ( ! current_user_can( 'install_plugins' ) ) {

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
			width:700px">' . __( 'You do not have sufficient permissions to access this page.', 'connections' ) . '</p>'
		);

	} else {

		$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'system_info';
		$current_page = self_admin_url( 'admin.php?page=connections_tools' );

		?>

		<div class="wrap">
			<h2 class="nav-tab-wrapper">
				<?php

				foreach ( cnAdmin_Tools::getTabs() as $tab ) {

					$tab_url = add_query_arg( array( 'tab' => $tab['id'] ), $current_page );

					$active = $active_tab == $tab['id'] ? ' nav-tab-active' : '';

					echo '<a href="' . esc_url( $tab_url ) . '" title="' . esc_attr( $tab['name'] ) . '" class="nav-tab' . $active . '">' . esc_html( $tab['name'] ) . '</a>';
				}

				?>
			</h2>

			<div class="metabox-holder">
				<?php do_action( 'cn_tools_tab_' . $active_tab );?>
			</div><!-- .metabox-holder -->
		</div><!-- .wrap -->

	<?php }
}

/**
 * Class cnAdmin_Tools
 */
class cnAdmin_Tools {

	/**
	 * Stores the instance of this class.
	 *
	 * @access private
	 * @since  8.3
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
	public function __construct() { /* Do nothing here */ }

	/**
	 * @access public
	 * @since  8.3
	 * @static
	 *
	 * @uses   cnAdmin_Tools::init()
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
	 * @access public
	 * @since  8.3
	 * @static
	 *
	 * @uses   cnAdmin_Tools::getTabs()
	 * @uses   add_action()
	 */
	private static function init() {

		self::$instance = new self;

		foreach ( self::getTabs() as $tab ) {

			add_action( 'cn_tools_tab_' . $tab['id'], $tab['callback'] );
		}

		add_action( 'cn_tools_system_after', array( __CLASS__, 'systemInfoEmail' ) );
		add_action( 'cn_tools_system_after', array( __CLASS__, 'systemInfoRemote' ) );
	}

	/**
	 * Register the tabs.
	 *
	 * @access public
	 * @since  8.3
	 * @static
	 *
	 * @uses   apply_filters()
	 *
	 * @return array
	 */
	private static function registerTabs() {

		$tabs = array(
			array( 'id'       => 'system_info',
			       'name'     => __( 'System Information', 'connections' ),
			       'callback' => array( __CLASS__, 'systemInfo' )
			),
			array( 'id'       => 'import_export',
			       'name'     => __( 'Settings Import/Export', 'connections' ),
			       'callback' => array( __CLASS__, 'importExport' )
			),
			array( 'id'       => 'logs',
			       'name'     => __( 'Logs', 'connections' ),
			       'callback' => array( __CLASS__, 'logs' )
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
	 * @access public
	 * @since  8.3
	 * @static
	 *
	 * @uses  cnAdmin_Tools::registerTabs()
	 */
	public static function getTabs() {

		return self::registerTabs();
	}

	/**
	 * Callback to display the system info.
	 *
	 * @access public
	 * @since  8.3
	 * @static
	 *
	 * @uses   do_action()
	 * @uses   _e()
	 * @uses   esc_url()
	 * @uses   self_admin_url()
	 * @uses   cnSystem_Info::display()
	 * @uses   wp_nonce_field()
	 */
	public static function systemInfo() {

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

					<textarea readonly="readonly" onclick="this.focus();this.select()"
					          name="cn-system-info"
					          title="<?php _e(
						          'To copy the System Info, click below then press Ctrl + C (PC) or Cmd + C (Mac).',
						          'connections'
					          ); ?>"
					          style="display: block; width: 100%; height: 500px; font-family: 'Consolas', 'Monaco', monospace; white-space: pre; overflow: auto;">
<!-- Non standard indentation needed for plain-text display. -->
<?php cnSystem_Info::display(); ?>
					</textarea>

				<?php // Form used to download .txt file ?>
				<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( self_admin_url( 'admin-ajax.php' ) ); ?>">
					<input type="hidden" name="action" value="download_system_info"/>
					<?php wp_nonce_field( 'download_system_info' ); ?>
					<?php submit_button( __( 'Download System Info as Text File', 'connections' ), 'secondary', 'submit' ); ?>
				</form>
			</div><!-- .inside -->
		</div><!-- .postbox -->

		<?php

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
	 * @access public
	 * @since  8.3
	 * @static
	 *
	 * @uses   cnFormObjects()
	 * @uses   _e()
	 * @uses   __()
	 * @uses   esc_url()
	 * @uses   self_admin_url()
	 * @uses   submit_button()
	 */
	public static function systemInfoEmail() {

		if ( ! current_user_can( 'install_plugins' ) ) {

			return;
		}

		do_action( 'cn_tools_email_system_info_before' );

		$form = new cnFormObjects();

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
								<input type="email" name="email" id="cn-email-address" class="regular-text" placeholder="<?php _e( 'user@email.com', 'connections'); ?>"/>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="cn-email-subject">
									<?php _e( 'Subject', 'connections' ); ?>
								</label>
							</th>
							<td>
								<input type="text" name="subject" id="cn-email-subject" class="regular-text" placeholder="<?php _e( 'Subject', 'connections'); ?>"/>
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
									<?php _e(
										'Your system information will be attached automatically to this email.',
										'connections'
									) ?>
								</p>

							</td>
						</tr>
					</table>
					<input type="hidden" name="action" value="email_system_info"/>
					<?php $form->tokenField( 'email_system_info', FALSE, '_cn_wpnonce', FALSE ); ?>
					<?php submit_button( __( 'Send Email', 'connections' ), 'secondary', 'submit', TRUE, array( 'id' => 'cn-send-system-info-submit' ) ) ?>
				</form>

			</div><!-- .inside -->
		</div><!-- .postbox -->

		<?php
		do_action( 'cn_tools_email_system_info_after' );
	}

	/**
	 * Callback to display the remote URL for the system info.
	 *
	 * @access public
	 * @since  8.3
	 * @static
	 *
	 * @uses   home_url()
	 * @uses   _e()
	 * @uses   esc_url()
	 * @uses   wp_create_nonce()
	 */
	public static function systemInfoRemote() {

		$token = cnCache::get( 'system_info_remote_token', 'option-cache' );
		$url   = $token ? home_url() . '/?cn-system-info=' . $token : '';

		?>

		<div class="postbox">
			<h3><span><?php _e( 'Remote Viewing', 'connections' ); ?></span></h3>

			<div class="inside">

				<div id="cn-remote-response"></div>

				<p>
					<?php _e(
						'Create a secret URL that support can use to remotely view your system information. The secret URL will expire after 72 hours and can be revoked at any time.',
						'connections'
					) ?>
				</p>

				<p>
					<input type="text" readonly="readonly" id="system-info-url" class="regular-text"
					       onclick="this.focus();this.select()" value="<?php echo esc_url( $url ? $url : '' ); ?>"
					       title="<?php _e(
						       'To copy the URL, click then press Ctrl + C (PC) or Cmd + C (Mac).',
						       'connections'
					       ); ?>"/>&nbsp;&nbsp;<a class="button-secondary" href="<?php echo esc_url( $url ? : '#' ); ?>" target="_blank"
					                              id="system-info-url-text-link" style="display: <?php echo $url ? 'display-inline' : 'none' ; ?>"><?php _e( 'Test', 'connections' ); ?></a>
				</p>

				<p class="submit">
					<input type="submit" onClick="return false;" class="button-secondary" name="generate-url"
					       value="<?php _e( 'Generate URL', 'connections' ) ?>"
					       data-nonce="<?php echo wp_create_nonce( 'generate_remote_system_info_url' ); ?>"/>
					<input type="submit" onClick="return false;" class="button-secondary" name="revoke-url"
					       value="<?php _e( 'Revoke URL', 'connections' ) ?>"
					       data-nonce="<?php echo wp_create_nonce( 'revoke_remote_system_info_url' ); ?>"/>
				</p>

			</div><!-- .inside -->
		</div><!-- .postbox -->

		<?php
	}

	/**
	 * Callback to render import/export settings.
	 *
	 * @access public
	 * @since  8.3
	 * @static
	 *
	 * @uses   current_user_can()
	 * @uses   do_action()
	 * @uses   _E()
	 * @uses   esc_url()
	 * @uses   self_admin_url()
	 * @uses   wp_nonce_field()
	 * @uses   submit_button()
	 */
	public static function importExport() {

		if ( ! current_user_can( 'install_plugins' ) ) {

			return;
		}

		do_action( 'cn_tools_import_export_settings_before' );

		?>
		<div class="postbox">
			<h3><span><?php _e( 'Export Settings', 'connections' ); ?></span></h3>

			<div class="inside">
				<p>
					<?php _e(
						'Export the for this site as a .json file. This allows you to easily import the configuration into another site.',
						'connections'
					); ?>
				</p>

				<form method="post" action="<?php echo esc_url( self_admin_url( 'admin-ajax.php' ) ); ?>">
					<input type="hidden" name="action" value="export_settings"/>
					<?php wp_nonce_field( 'export_settings' ); ?>
					<?php submit_button( __( 'Export', 'connections' ), 'secondary', 'submit' ); ?>
				</form>
			</div><!-- .inside -->
		</div><!-- .postbox -->

		<div class="postbox">
			<h3><span><?php _e( 'Import Settings', 'connections' ); ?></span></h3>

			<div class="inside">

				<div id="cn-import-settings-response"></div>

				<p>
					<?php _e(
						'Import the settings from a .json file. This file can be obtained by exporting the settings on another site using the form above.',
						'connections'
					); ?>
				</p>

				<form id="cn-import-settings" method="post" enctype="multipart/form-data" action="<?php echo esc_url( self_admin_url( 'admin-ajax.php' ) ); ?>">
					<p>
						<input type="file" name="import_file"/>
					</p>

					<input type="hidden" name="action" value="import_settings"/>
					<?php wp_nonce_field( 'import_settings' ); ?>
					<?php submit_button( __( 'Import', 'connections' ), 'secondary', 'submit', TRUE, array( 'id' => 'cn-import-settings-submit' ) ); ?>
				</form>
			</div><!-- .inside -->
		</div><!-- .postbox -->
		<?php
		do_action( 'cn_tools_import_export_settings_after' );
	}

	/**
	 * Callback used to render the log view of the log type being viewed.
	 *
	 * @access private
	 * @since  8.3
	 * @static
	 *
	 * @uses   current_user_can()
	 * @uses   wp_list_pluck()
	 * @uses   esc_url()
	 * @uses   self_admin_url()
	 * @uses   cnLog::types()
	 * @uses   cnLog_Email::types()
	 * @uses   cnHTML::select()
	 * @uses   submit_button()
	 * @uses   do_action()
	 */
	public static function logs() {

		if ( ! current_user_can( 'install_plugins' ) ) {
			return;
		}

		$current = cnLog_Email::LOG_TYPE;
		$views   = wp_list_pluck( cnLog::views(), 'id' );

		if ( isset( $_GET['view'] ) && array_key_exists( $_GET['view'], $views ) ) {
			$current = $_GET['view'];
		}

		?>

		<div class="wrap" id="cn-logs">

			<form id="cn-log-type" method="get"
			      action="<?php echo esc_url( self_admin_url( 'admin.php' ) ); ?>">

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
					FALSE,
					array(
						'id'    => 'log-type-submit',
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
