<?php
/**
 * The templates admin page.
 *
 * @package     Connections
 * @subpackage  The templates admin page.
 * @copyright   Copyright (c) 2015, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       unknown
 *
 * @phpcs:disable Generic.Commenting.DocComment.SpacingBeforeTags
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
 */

use Connections_Directory\Utility\_escape;
use Connections_Directory\Utility\_nonce;

// Exit if accessed directly.
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
				width:700px">' . esc_html__( 'You do not have sufficient permissions to access this page.', 'connections' ) . '</p>'
		);
	} else {

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		$type          = isset( $_GET['type'] ) ? sanitize_key( $_GET['type'] ) : 'all'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$templates     = cnTemplateFactory::getCatalog( $type );
		$adminURL      = self_admin_url( 'admin.php' );
		$pageURL       = add_query_arg( 'page', 'connections_templates', $adminURL );
		$homeID        = cnSettingsAPI::get( 'connections', 'connections_home_page', 'page_id' );
		$homeURL       = get_permalink( $homeID );
		$customizerURL = add_query_arg( 'cn-customize-template', 'true', $homeURL );

		$templateTypes = array(
			'all'          => __( 'All', 'connections' ),
			'individual'   => __( 'Individual', 'connections' ),
			'organization' => __( 'Organization', 'connections' ),
			'family'       => __( 'Family', 'connections' ),
			'anniversary'  => __( 'Anniversary', 'connections' ),
			'birthday'     => __( 'Birthday', 'connections' ),
		);

		$templateTypeLinks = array();

		foreach ( $templateTypes as $templateType => $templateTypeLabel ) {

			$templateTypeLinks[] = sprintf(
				'<a %shref="%s">%s</a>',
				$type === $templateType ? 'class="current" aria-current="page" ' : '',
				add_query_arg( 'type', $templateType, esc_url( $pageURL ) ),
				$templateTypeLabel
			);
		}
		?>
		<div class="wrap">

			<h1>Connections : <?php _e( 'Templates', 'connections' ); ?>
				<a class="button add-new-h2" href="https://connections-pro.com/templates/" target="_blank"><?php _e( 'Get More', 'connections' ); ?></a>
			</h1>

			<ul class="subsubsub">
				<?php
				echo wp_kses_post( '<li>' . implode( '&ensp;|&ensp;</li><li>', $templateTypeLinks ) . '</li>' );
				?>
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

									echo wp_kses_post(
										'<p class="clear">' . cnTemplateCustomizerButton( $activeTemplate, $customizerURL, $pageURL ) . '</p>'
									);

									// Remove the current template, so it does not show in the available templates.
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
								<strong><?php esc_html_e( 'Instructions', 'connections' ); ?>:</strong>
							</p>

							<p>
								<?php
								printf(
									/* translators: %s: URL to the admin Templates screen. */
									esc_html__( 'To learn more, please refer to the %s.', 'connections' ),
									sprintf(
										'<a href="%s" target="_blank">%s</a>',
										'https://connections-pro.com/documentation/templates/',
										esc_html__( 'documentation', 'connections' )
									)
								);
								?>
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

					for ( $row = 1; $row <= $rows; $row++ ) {
						for ( $col = 1; $col <= 3; $col++ ) {
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

								if ( 1 == $row ) {
									$class[] = 'top';
								}

								if ( $row == $rows ) {
									$class[] = 'bottom';
								}

								if ( 1 == $col ) {
									$class[] = 'left';
								}

								if ( 3 == $col ) {
									$class[] = 'right';
								}
								?>

								<td class="<?php _escape::classNames( $class, true ); ?>">

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
							}
							?>
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

		$thumbnail = $template->getThumbnail();
		?>

		<div class="center-thumbnail">
			<img class="template-thumbnail" src="<?php echo esc_url( $thumbnail['url'] ); ?>" width="300" height="225">
		</div>

	<?php else : ?>

		<div class="center-thumbnail">
			<div class="template-thumbnail-none" style="width: 300px; height: 225px">
				<p><?php _e( 'Thumbnail Not Available', 'connections' ); ?></p>
			</div>
		</div>

		<?php
	endif;
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

	$allowed_html = array(
		'a' => array(
			'href'   => true,
			'target' => true,
			'title'  => true,
		),
	);

	if ( $template->getAuthorURL() ) {

		$author = '<a title="' . esc_attr__( 'Visit author\'s homepage.', 'connections' ) . '" href="' . esc_url( $template->getAuthorURL() ) . '" target="_blank">' . esc_html( $template->getAuthor() ) . '</a>';

	} else {

		$author = esc_html( $template->getAuthor() );
	}
	?>

	<h3><?php echo esc_html( $template->getName() ); ?> <?php echo esc_html( $template->getVersion() ); ?> by <?php echo wp_kses( $author, $allowed_html ); ?></h3>
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
 * Render deactivate instructions.
 *
 * @access private
 * @since  8.4
 *
 * @param cnTemplate $template
 */
function cnTemplateDeactivateText( $template ) {

	if ( $template->isCustom() === false ) {

		echo '<p class="description">' . esc_html__( 'This a core template and can not be deleted.', 'connections' ) . '</p>';

	} elseif ( $template->isCustom() === true && $template->isLegacy() === false ) {

		echo '<p class="description">' . esc_html__( 'This template is a plugin. You can deactivate and delete the template from the Plugins admin page.', 'connections' ) . '</p>';
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
		<input readonly value='template="<?php echo esc_attr( $template->getSlug() ); ?>"' onclick="this.focus();this.select()" title="<?php _e( 'To copy, click and then press Ctrl + C (PC) or Cmd + C (Mac).', 'connections' ); ?>">
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

	$url = _nonce::url( 'admin.php?cn-action=activate_template&type=' . $type . '&template=' . $template->getSlug(), 'activate', $template->getSlug() );

	?>

	<a class="button-primary" href="<?php echo esc_url( $url ); ?>" title="Activate '<?php echo esc_attr( $template->getName() ); ?>'"><?php _e( 'Activate', 'connections' ); ?></a>

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

	if ( $template->isCustom() === true && $template->isLegacy() === true ) {

		$url = _nonce::url( 'admin.php?cn-action=delete_template&type=' . $template->getType() . '&template=' . $template->getSlug(), 'delete', $template->getSlug() );

		?>

		<a class="button button-warning" href="<?php echo esc_url( $url ); ?>" title="Delete '<?php echo esc_attr( $template->getName() ); ?>'" onclick="return confirm('You are about to delete this template \'<?php echo esc_attr( $template->getName() ); ?>\'\n  \'Cancel\' to stop, \'OK\' to delete.');"><?php _e( 'Delete', 'connections' ); ?></a>
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
		 * NOTE: According to the docs for the JavaScript Customizer API, you can autofocus
		 *       to the panel, section or control you wish via the URL. Which does work.
		 *
		 *       However, if you add this via @see add_query_arg() or escape it using
		 *       @see esc_url() the required bracket will be removed which are required
		 *       for autofocusing via the URL to function. This is why I added it after
		 *       escaping the URL.
		 *
		 * @link https://make.wordpress.org/core/2014/10/27/toward-a-complete-javascript-api-for-the-customizer/#focusing
		 */

		?>
		<a class="button" href="<?php echo esc_url( $href ) . '&autofocus[section]=cn_template_customizer_section_display'; ?>" title="Customize '<?php echo esc_attr( $template->getName() ); ?>'"><?php _e( 'Customize', 'connections' ); ?></a>
		<?php
	}
}
