<?php
/**
 * Postbox for the Tool/Import admin page to inform user of the CSV Import Addon.
 *
 * @since 10.4.35
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\Hook\Action\Admin\Tools
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2022, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

namespace Connections_Directory\Hook\Action\Admin\Tools;

/**
 * Class Import_Entries
 *
 * @package Connections_Directory\Hook\Action\Admin\Tools
 */
final class Import_Entries {

	/**
	 * Callback for the `admin_init` action.
	 *
	 * Register the action hooks.
	 *
	 * @since 10.4.35
	 */
	public static function register() {

		if ( ! class_exists( 'Connections_CSV_Import', false ) ) {

			add_action( 'Connections_Directory/Admin/Page/Tools/Tab/Import', array( __CLASS__, 'postBox' ) );
		}
	}

	/**
	 * Render the postbox message.
	 *
	 * @since 10.4.35
	 *
	 * @return void
	 */
	public static function postBox() {

		$action = new self();

		if ( ! $action->isValid() ) {
			return;
		}
		?>
		<div class="postbox">
			<h3 style="font-size:16px;">
				<span>
				<svg style="width:24px;height:24px;vertical-align:bottom;" viewBox="0 0 24 24">
					<path fill="currentColor" d="M14 2H6C4.9 2 4 2.9 4 4V20C4 21.1 4.9 22 6 22H18C19.1 22 20 21.1 20 20V8L14 2M18 20H6V4H13V9H18V20M10 19L12 15H9V10H15V15L13 19H10" />
				</svg>
				<?php _e( 'Import Entries', 'connections' ); ?>
				</span>
			</h3>
			<div class="inside">
				<p style="font-size:14px;">You can quickly build your directory by bulk importing your entries using the CSV Import addon.</p>
				<p style="text-align:right;">
					<a class="button-primary" style="font-size:16px;" href="https://connections-pro.com/add-on/csv-import/">
						<svg style="width:24px;height:24px;vertical-align:middle;" viewBox="0 0 24 24">
							<path fill="currentColor" d="M12,3L1,9L12,15L21,10.09V17H23V9M5,13.18V17.18L12,21L19,17.18V13.18L12,17L5,13.18Z" />
						</svg>
						<?php _e( 'Learn More', 'connections' ); ?>
					</a>
				</p>
			</div><!-- .inside -->
		</div><!-- .postbox -->
		<?php
	}

	/**
	 * Whether the current user has the required role capability.
	 *
	 * @since 10.4.35
	 *
	 * @return bool
	 */
	private function isValid() {

		return current_user_can( 'import' );
	}
}
