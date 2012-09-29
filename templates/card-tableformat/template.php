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
				
				<?php echo $entry->getBirthdayBlock('F j'); ?>
				<?php echo $entry->getAnniversaryBlock(); ?>
			</div>
        </td>
    </tr>
    
    <tr>
        <td valign="bottom" style="vertical-align: top;">
        	<?php echo $vCard->download(); ?>
        </td>
		<td align="right" valign="bottom" style="vertical-align: top;">
			<span style="<?php echo $entry->getLastUpdatedStyle() ?>; font-size:x-small; font-variant: small-caps;">Updated <?php echo $entry->getHumanTimeDiff() ?> ago</span>
			<?php echo $entry->returnToTopAnchor(); ?>
        </td>
    </tr>
</table>
</div>