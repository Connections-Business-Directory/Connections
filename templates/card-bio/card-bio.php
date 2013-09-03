<?php

/**
 * Bio Card Template.
 *
 * @package     Connections
 * @subpackage  Template : Bio Card
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.7.9
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
				'version'     => '2.0',
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

			$template->part( array( 'tag' => 'card', 'type' => 'action', 'callback' => array( $this, 'card' ) ) );
		}

		public function card( $entry ) {

			?>

			<div class="cn-entry" style="-moz-border-radius:4px; background-color:#FFFFFF; border:1px solid #E3E3E3; color: #000000; margin:8px 0px; padding:6px; position: relative;">
				<div style="width:49%; float:left">
					<?php $entry->getImage(); ?>
					<div style="clear:both;"></div>
					<div style="margin-bottom: 10px;">
						<span style="font-size:larger;font-variant: small-caps"><strong><?php echo $entry->getNameBlock(); ?></strong></span>
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

	add_action( 'cn_register_template', array( 'CN_Bio_Card_Template', 'register' ) );
}