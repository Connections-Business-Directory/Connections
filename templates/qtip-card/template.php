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

<div class="cn-entry">
	<div style="width:49%; float:left">
		<?php $entry->getImage( array( 'width' => 225 , 'zc' => 2 ) ); ?>
	</div>

	<div align="right">
		<div style="margin-bottom: 10px;">
			<?php $entry->getNameBlock(); ?>
			<?php $entry->getTitleBlock(); ?>
			<?php $entry->getOrgUnitBlock(); ?>
		</div>

		<?php $entry->getAddressBlock(); ?>
		<?php $entry->getConnectionGroupBlock(); ?>
		<?php $entry->getPhoneNumberBlock(); ?>
		<?php $entry->getEmailAddressBlock(); ?>
		<?php $entry->getImBlock(); ?>
		<?php $entry->getSocialMediaBlock(); ?>
		<?php $entry->getWebsiteBlock(); ?>
		<?php $entry->getDateBlock(); ?>

	</div>

	<div style="clear:both"></div>
	<div class="cn-meta" align="left" style="margin-top: 6px">
		<span><?php echo $vCard->download(); ?></span>
		<span style="<?php echo $entry->getLastUpdatedStyle() ?>; font-size:x-small; font-variant: small-caps; position: absolute; right: 6px; bottom: 8px;">Updated <?php echo $entry->getHumanTimeDiff() ?> ago</span><br />
	</div>

</div>