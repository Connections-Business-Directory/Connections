<?php

/**
 * Profile Template.
 *
 * @package     Connections
 * @subpackage  Template : Profile
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.7.9
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'CN_Profile_Template' ) ) {

	class CN_Profile_Template {

		public static function register() {

			$atts = array(
				'class'       => 'CN_Profile_Template',
				'name'        => 'Profile Entry Card',
				'slug'        => 'profile',
				'type'        => 'all',
				'version'     => '2.0',
				'author'      => 'Steven A. Zahm',
				'authorURL'   => 'connections-pro.com',
				'description' => 'This will show the entries in a profile format.',
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

					<span style="float: left; margin-right: 10px;"><?php $entry->getImage( array( 'preset' => 'profile' ) ); ?></span>

					<div style="margin-left: 10px;">
						<span style="font-size:larger;font-variant: small-caps"><strong><?php $entry->getNameBlock(); ?></strong></span>
						<div style="margin-bottom: 20px;">
							<?php $entry->getTitleBlock() ?>
							<?php $entry->getOrgUnitBlock(); ?>
						</div>
						<?php echo $entry->getBioBlock(); ?>
					</div>


				<div style="clear:both"></div>
			</div>

			<?php
		}

	}

	add_action( 'cn_register_template', array( 'CN_Profile_Template', 'register' ) );
}