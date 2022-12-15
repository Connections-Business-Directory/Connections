<?php
/**
 * Postbox for the Tool/Import admin page to import categories.
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

use Connections_Directory\Utility\_nonce;

/**
 * Class Import_Entries
 *
 * @package Connections_Directory\Hook\Action\Admin\Tools
 */
final class Import_Categories {

	/**
	 * Callback for the `admin_init` action.
	 *
	 * Register the action hooks.
	 *
	 * @since 10.4.35
	 */
	public static function register() {

		add_action( 'Connections_Directory/Admin/Page/Tools/Tab/Import', array( __CLASS__, 'postBox' ) );
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
			<h3><span><?php _e( 'Import Categories', 'connections' ); ?></span></h3>
			<div class="inside">
				<form id="cn-import-category" class="cn-import-form" action="<?php echo esc_url( self_admin_url( 'admin-ajax.php' ) ); ?>" method="post" enctype="multipart/form-data">
					<div class="cn-upload-file">
						<p>
							<?php esc_html_e( 'Bulk import categories from a CSV File.', 'connections' ); ?>
						</p>
						<p>
							<input name="cn-import-file" id="cn-import-file-term" type="file" />
							<input type="hidden" name="id" value="cn-import-category" />
							<input type="hidden" name="action" value="csv_upload" />
							<input type="hidden" name="type" value="category" />
							<?php _nonce::field( 'csv_upload', null, 'nonce', false ); ?>
						</p>
						<?php submit_button( esc_html__( 'Upload', 'connections' ), 'secondary', 'cn-upload-csv-category' ); ?>
					</div>
					<div class="cn-import-options" id="cn-import-category-options" style="display: none;">
						<table class="widefat cn-repeatable-table" width="100%" cellpadding="0" cellspacing="0" style="table-layout: auto; width: auto;">
							<thead>
							<tr>
								<th><?php _e( 'CSV Column', 'connections' ); ?></th>
								<th style="width: 100%"><?php _e( 'Import into field:', 'connections' ); ?></th>
							</tr>
							</thead>
							<tbody>
							<!--<tr class="cn-repeatable-row"> Rows will be added dynamically via JS. </tr>-->
							</tbody>
						</table>
						<?php submit_button( esc_html__( 'Import', 'connections' ) ); ?>
					</div>
				</form>
			</div><!-- .inside -->
		</div><!-- .postbox -->
		<?php
		wp_enqueue_script( 'cn-csv-import' );
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
