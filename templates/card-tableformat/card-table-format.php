<?php
/**
 * @package    Connections
 * @subpackage Template : Table Entry Card
 * @author     Steven A. Zahm
 * @since      0.7.9
 * @license    GPL-2.0+
 * @link       http://connections-pro.com
 * @copyright  2013 Steven A. Zahm
 *
 * @wordpress-plugin
 * Plugin Name:       Connections Table Entry Card - Template
 * Plugin URI:        http://connections-pro.com
 * Description:       This is a variation of the default template which is formatted using a table. This template is recommended when compatibility with Internet Explorer 6 is required.
 * Version:           2.0.1
 * Author:            Steven A. Zahm
 * Author URI:        http://connections-pro.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'CN_Card_Table_Format_Template' ) ) {

	class CN_Card_Table_Format_Template {

		public static function register() {

			$atts = array(
				'class'       => 'CN_Card_Table_Format_Template',
				'name'        => 'Table Entry Card',
				'slug'        => 'card-tableformat',
				'type'        => 'all',
				'version'     => '2.0.1',
				'author'      => 'Steven A. Zahm',
				'authorURL'   => 'connections-pro.com',
				'description' => 'This is a variation of the default template which is formatted using a table. This template is recommended when compatibility with Internet Explorer 6 is required.',
				'custom'      => FALSE,
				'path'        => plugin_dir_path( __FILE__ ),
				'url'         => plugin_dir_url( __FILE__ ),
				'thumbnail'   => 'thumbnail.png',
				'parts'       => array(),
				);

			cnTemplateFactory::register( $atts );
		}

		public function __construct( $template ) {

			$this->template = $template;

			$template->part( array( 'tag' => 'card', 'type' => 'action', 'callback' => array( __CLASS__, 'card' ) ) );
			$template->part( array( 'tag' => 'card-single', 'type' => 'action', 'callback' => array( __CLASS__, 'card' ) ) );
		}

		public static function card( $entry, $template, $atts ) {

			?>

			<div class="cn-entry" style="-moz-border-radius:4px; background-color:#FFFFFF; border:1px solid #E3E3E3; color: #000000; margin:8px 0px; padding:6px; position: relative;">
			<table width="100%" border="0px" bgcolor="#FFFFFF" bordercolor="#E3E3E3" cellspacing="0px" cellpadding="0px" style="margin: 0; vertical-align: top;">
			    <tr>
			        <td align="left" width="50%" valign="top" style="vertical-align: top;">
			        	<?php echo $entry->getImage(); ?>

						<div style="clear:both; margin: 0 5px;">
							<div style="margin-bottom: 10px;">
								<div style="font-size:larger;font-variant: small-caps"><strong><?php echo $entry->getNameBlock(); ?></strong></div>

								<?php $entry->getTitleBlock(); ?>
								<?php $entry->getOrgUnitBlock(); ?>
								<?php $entry->getContactNameBlock(); ?>

							</div>

							<?php $entry->getAddressBlock(); ?>
						</div>
			        </td>
			        <td align="right" valign="top" style="vertical-align: top;">
			        	<div style="clear:both; margin: 5px 5px;">
				        	<?php $entry->getConnectionGroupBlock(); ?>

							<?php $entry->getPhoneNumberBlock(); ?>
							<?php $entry->getEmailAddressBlock(); ?>

							<?php $entry->getImBlock(); ?>
							<?php $entry->getSocialMediaBlock(); ?>
							<?php $entry->getLinkBlock(); ?>
							<?php $entry->getDateBlock(); ?>

						</div>
			        </td>
			    </tr>

			    <tr>
			    	<td colspan="2">

			    		<?php $entry->getContentBlock( $atts['content'], $atts, $template ); ?>

			    		<div style="display: block; margin-bottom: 8px;"><?php $entry->getCategoryBlock( array( 'separator' => ', ', 'before' => '<span>', 'after' => '</span>' ) ); ?></div>

			    	</td>
			    </tr>

			    <tr>
			        <td valign="bottom" style="vertical-align: top;">
			        	<?php if ( cnSettingsAPI::get( 'connections', 'connections_display_entry_actions', 'vcard' ) ) $entry->vcard( array( 'before' => '<span>', 'after' => '</span>' ) ); ?>
			        </td>
					<td align="right" valign="bottom" style="vertical-align: top;">

						<?php

						cnTemplatePart::updated(
							array(
								'timestamp' => $entry->getUnixTimeStamp(),
								'style' => array(
									'font-size'    => 'x-small',
									'font-variant' => 'small-caps',
									'position'     => 'absolute',
									'right'        => '36px',
									'bottom'       => '8px'
								)
							)
						);

						cnTemplatePart::returnToTop( array( 'style' => array( 'position' => 'absolute', 'right' => '8px', 'bottom' => '5px' ) ) );

						?>

			        </td>
			    </tr>
			</table>
			</div>

			<?php
		}

	}

	// This action is ran if the template is loaded by core to register the template.
	add_action( 'cn_register_template', array( 'CN_Card_Table_Format_Template', 'register' ) );
}
