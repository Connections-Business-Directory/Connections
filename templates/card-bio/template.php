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
		
		<?php echo $entry->getBirthdayBlock('F j'); ?>
		<?php echo $entry->getAnniversaryBlock(); ?>
		
	</div>
	
	<div style="clear:both"></div>
	
	<?php echo $entry->getBioBlock(); ?>
	
	<div style="clear:both"></div>
	
	<div class="cn-meta" align="left" style="margin-top: 6px">
		<span><?php echo $vCard->download(); ?></span>
		<span style="<?php echo $entry->getLastUpdatedStyle() ?>; font-size:x-small; font-variant: small-caps; position: absolute; right: 26px; bottom: 8px;">Updated <?php echo $entry->getHumanTimeDiff() ?> ago</span><span style="position: absolute; right: 3px; bottom: 5px;"><?php echo $entry->returnToTopAnchor() ?></span><br />
	</div>
	
</div>