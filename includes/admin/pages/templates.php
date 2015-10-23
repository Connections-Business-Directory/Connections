<?php
/**
 * The templates admin page.
 *
 * @package     Connections
 * @subpackage  The templates admin page.
 * @copyright   Copyright (c) 2015, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       unknown
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the Template admin page.
 *
 * @access private
 * @since  unknown
 */
function connectionsShowTemplatesPage() {

	/*
	 * Check whether user can edit Settings
	 */
	if ( ! current_user_can( 'connections_manage_template' ) ) {
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

		// Grab an instance of the Connections object.
		$instance      = Connections_Directory();

		$type          = isset( $_GET['type'] ) ? esc_attr( $_GET['type'] ) : 'all';
		$templates     = cnTemplateFactory::getCatalog( $type );
		$adminURL      = self_admin_url( 'admin.php' );
		$pageURL       = add_query_arg( 'page', 'connections_templates', $adminURL );
		$homeID        = cnSettingsAPI::get( 'connections', 'connections_home_page', 'page_id' );
		$homeURL       = get_permalink( $homeID );
		$customizerURL = add_query_arg( 'cn-customize-template', 'true', $homeURL );


		?>
		<div class="wrap">

			<h1>Connections : <?php _e( 'Templates', 'connections' ); ?>
				<a class="button add-new-h2" href="http://connections-pro.com/templates/" target="_blank"><?php _e( 'Get More', 'connections' ); ?></a>
			</h1>

			<ul class="subsubsub">
				<li>
					<a <?php if ( 'all' == $type ) echo 'class="current" ' ?>href="<?php echo esc_url( add_query_arg( 'type', 'all', $pageURL ) ) ?>">
						<?php _e( 'All', 'connections' ); ?>
					</a> |
				</li>
				<li>
					<a <?php if ( 'individual' == $type ) echo 'class="current" ' ?>href="<?php echo esc_url( add_query_arg( 'type', 'individual', $pageURL ) ) ?>">
						<?php _e( 'Individual', 'connections' ); ?>
					</a> |
				</li>
				<li>
					<a <?php if ( 'organization' == $type ) echo 'class="current" ' ?>href="<?php echo esc_url( add_query_arg( 'type', 'organization', $pageURL ) ) ?>">
						<?php _e( 'Organization', 'connections' ); ?>
					</a> |
				</li>
				<li>
					<a <?php if ( 'family' == $type ) echo 'class="current" ' ?>href="<?php echo esc_url( add_query_arg( 'type', 'family', $pageURL ) ) ?>">
						<?php _e( 'Family', 'connections' ); ?>
					</a> |
				</li>
				<li>
					<a <?php if ( 'anniversary' == $type ) echo 'class="current" ' ?>href="<?php echo esc_url( add_query_arg( 'type', 'anniversary', $pageURL ) ) ?>">
						<?php _e( 'Anniversary', 'connections' ); ?>
					</a> |
				</li>
				<li>
					<a <?php if ( 'birthday' == $type ) echo 'class="current" ' ?>href="<?php echo esc_url( add_query_arg( 'type', 'birthday', $pageURL ) ) ?>">
						<?php _e( 'Birthday', 'connections' ); ?>
					</a>
				</li>
			</ul>

			<br class="clear">

			<table cellspacing="0" cellpadding="0" id="currenttheme">
				<tbody>
					<tr>
						<td class="current_template">
							<h2><?php _e( 'Current Template', 'connections' ); ?></h2>

							<div id="current-template">
								<?php
								$slug = $instance->options->getActiveTemplate( $type );

								/** @var cnTemplate $activeTemplate */
								$activeTemplate = cnTemplateFactory::getTemplate( $slug );

								if ( $activeTemplate ) {

									cnTemplateThumbnail( $activeTemplate );
									cnTemplateAuthor( $activeTemplate );
									cnTemplateDescription( $activeTemplate );

									cnTemplateCustomizerButton( $activeTemplate, $customizerURL, $pageURL );

									// Remove the current template so it does not show in the available templates.
									unset( $templates->{$activeTemplate->getSlug()} );

								} else {

									echo '<h3 class="error"> Template ', esc_attr( $slug ), ' can not be found.</h3>';
								}
								?>
							</div>
							<div class="clear"></div>
						</td>

						<td class="template_instructions" colspan="2">
							<p>
								<strong><?php _e( 'Instructions', 'connections' ); ?>:</strong>
							</p>

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
			</table> <!-- /#currenttheme -->

			<table cellspacing="0" cellpadding="0" id="availablethemes">
				<tbody>
					<tr>
						<td class="current_template" colspan="3">
							<h2><?php _e( 'Available Templates', 'connections' ); ?></h2>
						</td>
					</tr>

					<?php
					$slugs = array_keys( (array) $templates );
					natcasesort( $slugs );

					$table = array();
					$rows  = ceil( count( $slugs ) / 3 );

					for ( $row = 1; $row <= $rows; $row ++ ) {
						for ( $col = 1; $col <= 3; $col ++ ) {
							$table[ $row ][ $col ] = array_shift( $slugs );
						}
					}

					foreach ( $table as $row => $cols ) {
						?>
						<tr>
							<?php
							foreach ( $cols as $col => $slug ) {

								if ( ! isset( $templates->$slug ) ) {
									continue;
								}

								/** @var cnTemplate $template */
								$template = $templates->{$slug};

								$class = array( 'available-theme' );
								if ( $row == 1 ) $class[]     = 'top';
								if ( $row == $rows ) $class[] = 'bottom';
								if ( $col == 1 ) $class[]     = 'left';
								if ( $col == 3 ) $class[]     = 'right';
								?>

								<td <?php echo cnHTML::attribute( 'class', $class ); ?>>

									<?php
									cnTemplateThumbnail( $template );
									cnTemplateAuthor( $template );
									cnTemplateDescription( $template );
									cnTemplateDeactivateText( $template );
									cnTemplateShortcodeOverride( $template );
									?>

									<span class="action-links">
										<?php
										cntemplateActivateButton( $template, $type );
										cnTemplateDeleteButton( $template );
										cnTemplateCustomizerButton( $template, $customizerURL, $pageURL );
										?>
									</span>
								</td>
								<?php
							} ?>
						</tr>
						<?php
					}
					?>
				</tbody>
			</table> <!-- /#availablethemes -->
		</div> <!-- /.wrap -->
		<?php
	}
}

/**
 * Renders the template thumbnail.
 *
 * @access private
 * @since  8.4
 *
 * @param cnTemplate $template
 */
function cnTemplateThumbnail( $template ) {

	if ( $template->getThumbnail() ) :

		$thumbnail = $template->getThumbnail(); ?>

		<div class="center-thumbnail">
			<img class="template-thumbnail" src="<?php echo esc_url( $thumbnail['url'] ) ?>" width="300" height="225">
		</div>

	<?php else : ?>

		<div class="center-thumbnail">
			<div class="template-thumbnail-none" style="width: 300px; height: 225px">
				<p><?php _e( 'Thumbnail Not Available', 'connections' ); ?></p>
			</div>
		</div>

	<?php endif;
}

/**
 * Renders the template author name and link.
 *
 * @access private
 * @since  8.4
 *
 * @param cnTemplate $template
 */
function cnTemplateAuthor( $template ) {

	if ( $template->getAuthorURL() ) {

		$author = '<a title="' . __( 'Visit author\'s homepage.', 'connections' ) . '" href="' . esc_url( $template->getAuthorURL() ) . '">' .
		          esc_html( $template->getAuthor() ) .
		          '</a>';
	} else {

		$author = esc_html( $template->getAuthor() );
	}
	?>

	<h3><?php echo esc_html( $template->getName() );?> <?php echo esc_html( $template->getVersion() ); ?> by <?php echo $author; ?></h3>
	<?php
}

/**
 * Renders the template description.
 *
 * @access private
 * @since  8.4
 *
 * @param cnTemplate $template
 */
function cnTemplateDescription( $template ) {

	echo '<p>' . esc_html( $template->getDescription() ) . '</p>';
}

/**
 * Renders the deactivate instructions.
 *
 * @access private
 * @since  8.4
 *
 * @param cnTemplate $template
 */
function cnTemplateDeactivateText( $template ) {

	if ( $template->isCustom() === FALSE ) {

		echo '<p class="description">', __( 'This a core template and can not be deleted.', 'connections' ), '</p>';

	} elseif ( $template->isCustom() === TRUE && $template->isLegacy() === FALSE ) {

		echo '<p class="description">', __( 'This template is a plugin. You can deactivate and delete the template from the Plugins admin page.', 'connections' ), '</p>';
	}
}

/**
 * Renders the shortcode override.
 *
 * @access private
 * @since  8.4
 *
 * @param cnTemplate $template
 */
function cnTemplateShortcodeOverride( $template ) {

	?>

	<p>
		<?php _e( 'Shortcode Override:', 'connections' ); ?>
		<input readonly
		       value='template="<?php echo $template->getSlug()  ?>"'
		       onclick="this.focus();this.select()"
		       title="<?php _e(
		           'To copy, click and then press Ctrl + C (PC) or Cmd + C (Mac).',
		           'connections'
		       ); ?>">
	</p>

	<?php
}

/**
 * Renders the "Activate" button.
 *
 * @access private
 * @since  8.4
 *
 * @param cnTemplate $template
 * @param string     $type
 */
function cnTemplateActivateButton( $template, $type = 'all' ) {

	$form = new cnFormObjects();

	$url = $form->tokenURL( 'admin.php?cn-action=activate_template&type=' . $type . '&template=' . $template->getSlug(), 'activate_' . $template->getSlug() );

	?>

	<a class="button-primary"
	   href="<?php echo esc_url( $url ); ?>"
	   title="Activate '<?php echo esc_attr( $template->getName() ); ?>'">
		<?php _e( 'Activate', 'connections' ); ?>
	</a>

	<?php
}

/**
 * Renders the "Delete" button.
 *
 * @access private
 * @since  8.4
 *
 * @param cnTemplate $template
 */
function cnTemplateDeleteButton( $template ) {

	$form = new cnFormObjects();

	if ( $template->isCustom() === TRUE && $template->isLegacy() === TRUE ) {

		$url = $form->tokenURL( 'admin.php?cn-action=delete_template&type=' . $template->getType() . '&template=' . $template->getSlug(), 'delete_' . $template->getSlug() );

		?>

		<a class="button button-warning"
		   href="<?php echo esc_url( $url ); ?>"
		   title="Delete '<?php echo esc_attr( $template->getName() ); ?>'"
		   onclick="return confirm('You are about to delete this template \'<?php echo esc_attr( $template->getName() ); ?>\'\n  \'Cancel\' to stop, \'OK\' to delete.');">
			<?php _e( 'Delete', 'connections' ); ?>
		</a>
		<?php
	}
}

/**
 * Renders the "Customize" button.
 *
 * @access private
 * @since  8.4
 *
 * @param cnTemplate $template
 * @param string     $customizerURL
 * @param string     $returnURL
 */
function cnTemplateCustomizerButton( $template, $customizerURL, $returnURL ) {

	if ( $template->supports( 'customizer' ) ) {

		$href = add_query_arg(
			array(
				'url'         => urlencode( add_query_arg( 'cn-template', $template->getSlug(), $customizerURL ) ),
				'cn-template' => $template->getSlug(),
				'return'      => urlencode( $returnURL ),
			),
			'customize.php'
		);

		/**
		 * NOTE: According to the docs for the JavaScript Customizer API, you can auto focus
		 *       to the panel, section or control you wish via the URL. Which does work.
		 *
		 *       However, if you add this via @see add_query_arg() or escape it using
		 *       @see esc_url() the required bracket will be removed which are required
		 *       for auto focusing via the URL too function. This is why I added it after
		 *       escaping the URL.
		 *
		 * @link https://make.wordpress.org/core/2014/10/27/toward-a-complete-javascript-api-for-the-customizer/#focusing
		 */

		?>
		<a class="button"
		   href="<?php echo esc_url( $href ) . '&autofocus[section]=cn_template_customizer_section_display'; ?>"
		   title="Customize '<?php echo esc_attr( $template->getName() ); ?>'"><?php _e( 'Customize', 'connections' ); ?></a>
		<?php
	}
}
