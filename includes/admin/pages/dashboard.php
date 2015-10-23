<?php

/**
 * The dashboard admin page.
 *
 * @package     Connections
 * @subpackage  The dashboard admin page.
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       unknown
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

function connectionsShowDashboardPage() {
	/*
	 * Check whether user can view the Dashboard
	 */
	if ( !current_user_can( 'connections_view_dashboard' ) ) {
		wp_die( '<p id="error-page" style="-moz-background-clip:border;
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
				width:700px">' . __( 'You do not have sufficient permissions to access this page.', 'connections' ) . '</p>' );
	} else {
		global $connections;

		$form = new cnFormObjects();
		?>
		<div class="wrap">

			<h1>Connections : <?php _e( 'Dashboard', 'connections' ); ?></h1>

			<div id="dashboard-widgets-wrap">

				<div id="dashboard-widgets" class="metabox-holder columns-<?php echo 1 == get_current_screen()->get_columns() ? '1' : '2'; ?>">

					<div class="postbox-container">
						<?php do_meta_boxes( $connections->pageHook->dashboard, 'left', NULL ); ?>
					</div>

					<div class="postbox-container">
						<?php do_meta_boxes( $connections->pageHook->dashboard, 'right', NULL ); ?>
					</div>

				</div><!-- #dashboard-widgets -->

			</div><!-- .dashboard-widgets-wrap -->

		</div><!-- .wrap -->

		<?php
		$attr = array(
			'action' => '',
			'method' => 'get'
		);

		$form->open( $attr );

		/* Used to save closed metaboxes and their order */
		wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', FALSE );
		wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', FALSE );

		$form->close();
		?>

		<div class="clear"></div>

	<?php
	}
}
