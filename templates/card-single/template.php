<?php

/**
 * Template HTML Output.
 *
 * @package     Connections
 * @subpackage  Template HTML Output
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       unknown
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

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
		<?php $entry->getImBlock(); ?>
		<?php $entry->getSocialMediaBlock(); ?>
		<?php $entry->getLinkBlock(); ?>
		<?php $entry->getDateBlock(); ?>

	</div>

	<div style="clear:both"></div>
	<div class="cn-meta" align="left" style="margin-top: 6px">
		<span><?php $entry->vcard(); ?></span>

		<?php

		cnTemplatePart::updated(
			array(
				'timestamp' => $entry->getUnixTimeStamp(),
				'style' => array(
					'font-size'    => 'x-small',
					'font-variant' => 'small-caps',
					'position'     => 'absolute',
					'right'        => '10px',
					'bottom'       => '8px'
				)
			)
		);

		?>

	</div>

</div>
