<?php
/**
 * @package    Connections
 * @subpackage Template : Bio Card
 * @author     Steven A. Zahm
 * @since      0.7.9
 * @license    GPL-2.0+
 * @link       http://connections-pro.com
 * @copyright  2013 Steven A. Zahm
 *
 * @wordpress-plugin
 * Plugin Name:       Connections Bio Card - Template
 * Plugin URI:        http://connections-pro.com
 * Description:       This is a variation of the default template which shows the bio field for an entry.
 * Version:           2.0.1
 * Author:            Steven A. Zahm
 * Author URI:        http://connections-pro.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'CN_Bio_Card_Template' ) ) {

	class CN_Bio_Card_Template {

		public static function register() {

			$atts = array(
				'class'       => 'CN_Bio_Card_Template',
				'name'        => 'Bio Entry Card',
				'slug'        => 'card-bio',
				'type'        => 'all',
				'version'     => '2.0.1',
				'author'      => 'Steven A. Zahm',
				'authorURL'   => 'connections-pro.com',
				'description' => 'This is a variation of the default template which shows the bio field for an entry.',
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
				<div style="width:49%; float:<?php echo is_rtl() ? 'right' : 'left'; ?>">
					<?php $entry->getImage(); ?>
					<div style="clear:both;"></div>
					<div style="margin-bottom: 10px;">
						<div style="font-size:larger;font-variant: small-caps"><strong><?php echo $entry->getNameBlock(); ?></strong></div>
						<?php $entry->getTitleBlock(); ?>
						<?php $entry->getOrgUnitBlock(); ?>
						<?php $entry->getContactNameBlock(); ?>

					</div>

						<?php $entry->getAddressBlock(); ?>
				</div>

				<div align="right">

					<?php $entry->getFamilyMemberBlock(); ?>
					<?php $entry->getPhoneNumberBlock(); ?>
					<?php $entry->getEmailAddressBlock(); ?>
					<?php $entry->getSocialMediaBlock(); ?>
					<?php $entry->getImBlock(); ?>
					<?php $entry->getLinkBlock(); ?>
					<?php $entry->getDateBlock(); ?>

				</div>

				<div style="clear:both"></div>

				<?php echo $entry->getBioBlock(); ?>

				<div style="clear:both"></div>

				<div class="cn-meta" align="left" style="margin-top: 6px">

					<?php $entry->getContentBlock( $atts['content'], $atts, $template ); ?>

					<div style="display: block; margin-bottom: 8px;"><?php $entry->getCategoryBlock( array( 'separator' => ', ', 'before' => '<span>', 'after' => '</span>' ) ); ?></div>

					<?php if ( cnSettingsAPI::get( 'connections', 'connections_display_entry_actions', 'vcard' ) ) $entry->vcard( array( 'before' => '<span>', 'after' => '</span>' ) ); ?>

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

				</div>

			</div>

			<?php
		}

	}

	// Register the template.
	add_action( 'cn_register_template', array( 'CN_Bio_Card_Template', 'register' ) );
}
