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
<table width="100%" border="0px" bgcolor="#FFFFFF" bordercolor="#E3E3E3" cellspacing="0px" cellpadding="0px" style="margin: 0; vertical-align: top;">
    <tr>
        <td align="left" width="50%" valign="top" style="vertical-align: top;">
        	<?php echo $entry->getImage(); ?>

			<div style="clear:both; margin: 0 5px;">
				<div style="margin-bottom: 10px;">
					<span style="font-size:larger;font-variant: small-caps"><strong><?php echo $entry->getNameBlock(); ?></strong></span>

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
        <td valign="bottom" style="vertical-align: top;">
        	<span><?php $entry->vcard(); ?></span>
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