<?php
/**
 * The role capability admin page.
 *
 * @package     Connections
 * @subpackage  The role capability admin page.
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       unknown
 */

use Connections_Directory\Utility\_nonce;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function connectionsShowRolesPage() {
	/*
	 * Check whether user can edit roles
	 */
	if ( ! current_user_can( 'connections_change_roles' ) ) {
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
		global $connections;

		$form = new cnFormObjects();

		?>
		<div class="wrap cn-roles">

			<h1>Connections : <?php _e( 'Roles &amp; Capabilities', 'connections' ); ?></h1>

			<?php
			$attr = array(
				'action' => '',
				'method' => 'post',
			);

			$form->open( $attr );
			_nonce::field( 'update_role_settings' );
			?>

			<div id="poststuff" class="metabox-holder has-right-sidebar">

				<div class="inner-sidebar" id="side-info-column">
					<div id="submitdiv" class="postbox">
						<h3 class="hndle" style="cursor: auto;">
							<span><?php _e( 'Save Changes or Reset', 'connections' ); ?></span>
						</h3>

						<div class="inside">

							<div id="minor-publishing">
								<label for="reset_all_roles">
									<input type="checkbox" id="reset_all_roles" name="reset_all" value="true">
									<?php _e( 'Reset All Role Capabilities', 'connections' ); ?>
								</label>
							</div>

							<div id="major-publishing-actions">
								<div id="publishing-action">
									<input type="hidden" name="cn-action" value="update_role_capabilities"/>
									<input class="button-primary" type="submit" value="<?php _e( 'Update', 'connections' ); ?>" name="save" />
								</div>
								<div class="clear"></div>
							</div>
						</div>

					</div>
				</div>

				<div class="has-sidebar" id="post-body">
					<div class="has-sidebar-content" id="post-body-content">
						<?php
						$editable_roles = get_editable_roles();

						foreach ( $editable_roles as $role => $details ) {
							$name = translate_user_role( $details['name'] );

							// The administrator should always have all capabilities.
							if ( 'administrator' === $role ) {

								continue;
							}

							$capabilities = cnRole::capabilities();

							echo '<div class="postbox">';

							echo '<h3 class="hndle" style="cursor: auto;"><span>', esc_html( $name ), '</span></h3>';

							echo '<div class="inside">';

							foreach ( $capabilities as $capability => $capabilityName ) {
								// If unregistered users are permitted to view the entry list there is no need for setting this capability.
								if ( 'connections_view_public' === $capability && $connections->options->getAllowPublic() == true ) {

									continue;
								}

								echo '<span style="display: block;"><label for="' . esc_attr( "{$role}_{$capability}" ) . '">';
								echo '<input type="hidden" name="' . esc_attr( "roles[{$role}][capabilities][{$capability}]" ) . '" value="false" />';
								echo '<input type="checkbox" id="' . esc_attr( $role . '_' . $capability ) . '" name="' . esc_attr( "roles[{$role}][capabilities][{$capability}]" ) . '" value="true" ';

								if ( cnRole::hasCapability( $role, $capability ) ) {

									echo 'CHECKED ';
								}

								// The administrator should always have all capabilities.
								if ( 'administrator' === $role ) {

									echo 'DISABLED ';
								}

								echo '/> ' . esc_html( $capabilityName ) . '</label></span>' . "\n";

							}

							echo '<span style="display: block;"><label for="' . esc_attr( "{$role}_reset_capabilities" ) . '">';
							echo '<input type="checkbox" id="' . esc_attr( "{$role}_reset_capabilities" ) . '" name="' . esc_attr( "reset[{$role}]" ) . '" value="' . esc_attr( $name ) . '" /> ';
							printf(
								// translators: The Role name.
								esc_html__( 'Reset %s Capabilities', 'connections' ),
								esc_html( $name )
							);
							echo '</label></span>' . "\n";

							echo '</div>';
							echo '</div>';
						}
						?>
					</div>
				</div>
			</div>

			<?php echo '</form>'; ?>


		</div>
		<div class="clear"></div>

	<?php }
}
?>
