<?php

/**
 * The templates admin page.
 *
 * @package     Connections
 * @subpackage  The templates admin page.
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       unknown
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

function connectionsShowTemplatesPage() {

	/*
	 * Check whether user can edit Settings
	 */
	if ( !current_user_can( 'connections_manage_template' ) ) {
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

		// Purge the transient so the page is freshly scanned by the template API.
		delete_transient( 'cn_legacy_templates' );
		// cnTemplateFactory::$templates = new stdClass();
		cnTemplateFactory::registerLegacy();

		$form = new cnFormObjects();

		$type = isset( $_GET['type'] ) ? esc_attr( $_GET['type'] ) : 'all';
		$template = cnTemplateFactory::getCatalog( $type );

	?>
		<div class="wrap">
			<?php echo get_screen_icon( 'connections' ); ?>

			<h2>Connections : <?php _e( 'Templates', 'connections' ); ?> <a class="button add-new-h2" href="http://connections-pro.com/templates/" target="_blank"><?php _e( 'Get More', 'connections' ); ?></a></h2>

			<ul class="subsubsub">
				<li><a <?php if ( $type === 'all' ) echo 'class="current" ' ?>href="admin.php?page=connections_templates&type=all"><?php _e( 'All', 'connections' ); ?></a> | </li>
				<li><a <?php if ( $type === 'individual' ) echo 'class="current" ' ?>href="admin.php?page=connections_templates&type=individual"><?php _e( 'Individual', 'connections' ); ?></a> | </li>
				<li><a <?php if ( $type === 'organization' ) echo 'class="current" ' ?>href="admin.php?page=connections_templates&type=organization"><?php _e( 'Organization', 'connections' ); ?></a> | </li>
				<li><a <?php if ( $type === 'family' ) echo 'class="current" ' ?>href="admin.php?page=connections_templates&type=family"><?php _e( 'Family', 'connections' ); ?></a> | </li>
				<li><a <?php if ( $type === 'anniversary' ) echo 'class="current" ' ?>href="admin.php?page=connections_templates&type=anniversary"><?php _e( 'Anniversary', 'connections' ); ?></a> | </li>
				<li><a <?php if ( $type === 'birthday' ) echo 'class="current" ' ?>href="admin.php?page=connections_templates&type=birthday"><?php _e( 'Birthday', 'connections' ); ?></a></li>
			</ul>

			<br class="clear">

			<table cellspacing="0" cellpadding="0" id="currenttheme">
				<tbody>
					<tr>
						<td class="current_template">
							<h2><?php _e( 'Current Template', 'connections' ); ?></h2>

							<div id="current-template">
								<?php
									$slug = $connections->options->getActiveTemplate( $type );

									$activeTemplate = cnTemplateFactory::getTemplate( $slug );
									// var_dump( $activeTemplate );

									if ( $activeTemplate ) {

										if ( $activeTemplate->getThumbnail() ) {

											$thumbnail = $activeTemplate->getThumbnail();

											if ( ! empty( $thumbnail['name'] ) ) {
												echo '<div class="center-thumbnail"><img class="template-thumbnail" src="' , $thumbnail['url'] , '" /></div>';
											} else {
												echo '<div class="center-thumbnail"><div class="template-thumbnail-none">' , __( 'Thumbnail Not Available', 'connections' ) , '</div></div>';
											}
										}

										if ( $activeTemplate->getAuthorURL() ) {
											$author = '<a title="' . __( 'Visit author\'s homepage.', 'connections' ) . '" href="' . $activeTemplate->getAuthorURL() . '">' . $activeTemplate->getAuthor() . '</a>';
										} else {
											$author = $activeTemplate->getAuthor();
										}

										echo '<h3>', $activeTemplate->getName() , ' ' , $activeTemplate->getVersion() , ' by ' , $author, '</h3>';
										echo '<p class="theme-description">', $activeTemplate->getDescription() , '</p>';

										// Remove the current template so it does not show in the available templates.
										unset( $template->{ $activeTemplate->getSlug() } );
									} else {
										echo '<h3 class="error"> Template ' , esc_attr( $slug ) , ' can not be found.</h3>';
									}
							?>
							</div>
							<div class="clear"></div>
						</td>

						<td class="template_instructions" colspan="2">
							<p><strong><?php _e( 'Instructions', 'connections' ); ?>:</strong></p>
							<p>
								<?php _e( 'By default the <code><a href="http://connections-pro.com/documentation/connections/shortcodes/shortcode-connections/">[connections]</a></code> shortcode will show all entries types. To change the template used when displaying all entry types, select the "All" tab and activate the template. When the <code><a href="http://connections-pro.com/documentation/connections/shortcodes/shortcode-connections/list_type/">list_type</a></code>shortcode option is used to filter the entries based on the entry type, the template for that entry type will be used. To change the template used for a specific entry type, select the appropriate tab and then activate the template. If multiple entry types are specified in the <code><a href="http://connections-pro.com/documentation/connections/shortcodes/shortcode-connections/list_type/">list_type</a></code> shortcode option, the template for the entry type listed first will be used to display the entry list.', 'connections' ); ?>
							</p>

							<p>
								<?php _e( 'The <code><a href="http://connections-pro.com/documentation/connections/shortcodes/shortcode-upcoming-list/">[upcoming_list]</a></code> shortcode which displays the upcoming anniversaries and birthdays will be displayed with the template that is activated under their respective tabs.', 'connections' ); ?>
							</p>

							<p>
								<?php _e( 'The current active template for each template type can be overridden by using the <code><a href="http://connections-pro.com/documentation/connections/shortcodes/shortcode-connections/template-option/">template</a></code> shortcode option.', 'connections' ); ?>
							</p>
						</td>
					</tr>
				</tbody>
			</table>

			<table cellspacing="0" cellpadding="0" id="availablethemes">
				<tbody>
					<tr>
						<td class="current_template" colspan="3">
							<h2><?php _e( 'Available Templates', 'connections' ); ?></h2>
						</td>
					</tr>

					<?php
						$slugs = array_keys( (array) $template );
						natcasesort( $slugs );

						$table = array();
						$rows = ceil( count( $slugs ) / 3 );

						for ( $row = 1; $row <= $rows; $row++ )
							for ( $col = 1; $col <= 3; $col++ )
								$table[$row][$col] = array_shift( $slugs );

						foreach ( $table as $row => $cols ) {
					?>
						<tr>
							<?php
								foreach ( $cols as $col => $slug ) {
									$activateTokenURL = '';
									$deleteTokenURL = '';

									$class = array( 'available-theme' );
									if ( $row == 1 ) $class[] = 'top';
									if ( $row == $rows ) $class[] = 'bottom';
									if ( $col == 1 ) $class[] = 'left';
									if ( $col == 3 ) $class[] = 'right';
							?>

								<td class="<?php echo join( ' ', $class ); ?>">

									<?php
										if ( ! isset( $template->{ $slug } ) ) continue;
										// var_dump( $template->{ $slug } );

										if ( $template->{ $slug }->getThumbnail() ) {

											$thumbnail = $template->{ $slug }->getThumbnail();

											if ( ! empty( $thumbnail['name'] ) ) {
												echo '<div class="center-thumbnail"><img class="template-thumbnail" src="' , $thumbnail['url'] , '" width="300" height="225"></div>';
											} else {
												echo '<div class="center-thumbnail"><div class="template-thumbnail-none" style="width: 300px; height: 225px"><p>' , __( 'Thumbnail Not Available', 'connections' ) , '</p></div></div>';
											}
										}

										if ( $template->{ $slug }->getAuthorURL() ) {
											$author = '<a title="Visit author\'s homepage." href="' . $template->{ $slug }->getAuthorURL() . '">' . $template->{ $slug }->getAuthor() . '</a>';
										} else {
											$author = $template->{ $slug }->getAuthor();
										}

										echo '<h3>', $template->{ $slug }->getName() , ' ' , $template->{ $slug }->getVersion() , ' by ', $author , '</h3>';
										echo '<p class="description">' , $template->{ $slug }->getDescription() , '</p>';
										echo '<p>' , __( 'Shortcode Override:', 'connections' ) , '<code> template="' ,  $slug , '"</code></p>';



									?>

									<span class="action-links">
										<?php
											$activateTokenURL = $form->tokenURL( 'admin.php?cn-action=activate_template&type=' . $type . '&template=' . $template->{ $slug }->getSlug(), 'activate_' . $template->{ $slug }->getSlug() );

											if ( $template->{ $slug }->isCustom() === TRUE && $template->{ $slug }->isLegacy() === TRUE ) {
												$deleteTokenURL = $form->tokenURL( 'admin.php?cn-action=delete_template&type=' . $type . '&template=' . $template->{ $slug }->getSlug(), 'delete_' .  $template->{ $slug }->getSlug() );
											}

										?>

										<a class="button-primary" href="<?php echo esc_attr( $activateTokenURL ); ?>" title="Activate '<?php echo esc_attr( $template->$slug->getName() ); ?>'"><?php _e( 'Activate', 'connections' ); ?></a>

										<?php
											if ( ! empty( $deleteTokenURL ) ) {
										?>
											 | <a class="button button-warning" href="<?php echo esc_attr( $deleteTokenURL ); ?>" title="Delete '<?php echo esc_attr( $template->$slug->getName() ); ?>'" onclick="return confirm('You are about to delete this theme \'<?php echo esc_attr( $template->$slug->getName() ); ?>\'\n  \'Cancel\' to stop, \'OK\' to delete.');">Delete</a>
										<?php
											}
										?>
									</span>
							<?php
									if ( $template->{ $slug }->isCustom() === FALSE ) {
										echo '<p class="description">' , __( 'This a core template and can not be deleted.', 'connections') , '</p>';
									} else if ( $template->{ $slug }->isCustom() === TRUE && $template->{ $slug }->isLegacy() === FALSE ) {
										echo '<p class="description">' , __( 'This template is a plugin. You can deactivate and delete the template from the Plugins admin page.', 'connections') , '</p>';
									}
								}
							?>
								</td>
						</tr>
					<?php
						}
					?>


				</tbody>
			</table>

			<?php
			if ( file_exists( CN_CUSTOM_TEMPLATE_PATH ) && is_writeable( CN_CUSTOM_TEMPLATE_PATH ) ) {
			?>

			<table cellspacing="0" cellpadding="0" id="installthemes">
				<tbody>
					<tr>
						<td class="install_template" colspan="3">
							<h2><?php _e( 'Install Legacy Template', 'connections' ); ?></h2>

							<p><?php _e( 'If you puchased your template after 3.25.2013, please follow these <a href="http://connections-pro.com/documentation/plugin/install/templates/">installation instructions</a>. If you are upgrading your template purchased prior to 3.25.2013, please take note of the special upgrade instructions found on the same page.' , 'connections' ); ?></p>

							<?php
									$formAttr = array(
										'action' => '',
										'method' => 'post',
										'enctype' => 'multipart/form-data'
									);

									$form->open( $formAttr );
									$form->tokenField( 'install_template' );
							?>

							<p>
								<input type="hidden" name="cn-action" value="install_template"/>

								<label for='template'><?php _e( 'Select Template:', 'connections' ); ?>
									<input type='file' value='' name='template' size='25' />
								</label>
								<input type="submit" value="<?php _e( 'Install Now', 'connections' ); ?>" class="button">
							</p>

							<?php $form->close(); ?>
						</td>
					</tr>
				</tbody>
			</table>

			<?php
			}
			?>

		</div>
	<?php
	}
}
?>