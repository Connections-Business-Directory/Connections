<div class="cn-entry" style="-moz-border-radius:4px; background-color:#FFFFFF; border:1px solid #E3E3E3; margin:8px 0px; padding:6px; position: relative;">
	<div style="width:49%; float:left">
		<?php echo $entry->getCardImage() ?>
		<div style="clear:both;"></div>
		<div style="margin-bottom: 10px;">
			<span style="font-size:larger;font-variant: small-caps"><strong><?php echo $entry->getFullFirstLastNameBlock() ?></strong></span><br />
			
			<?php echo $entry->getTitleBlock() ?>
			<?php echo $entry->getOrgUnitBlock() ?>
			
		</div>
			
			<?php echo $entry->getAddressBlock() ?>
	</div>
		
	<div align="right">
	
		<?php echo $entry->getConnectionGroupBlock() ?>
		<?php echo $entry->getPhoneNumberBlock() ?>
		<?php echo $entry->getEmailAddressBlock() ?>
		<?php echo $entry->getImBlock() ?>
		<?php echo $entry->getSocialMediaBlock() ?>
		<?php echo $entry->getWebsiteBlock() ?>
		
		<?php echo $entry->getBirthdayBlock('F j') ?>
		<?php echo $entry->getAnniversaryBlock() ?>
		
	</div>	
	
	<div style="clear:both"></div>
	<div class="cn-meta" align="left" style="margin-top: 6px">
		<span><?php echo $vCard->download() ?></span>
		<span style="<?php echo $entry->getLastUpdatedStyle() ?>; font-size:x-small; font-variant: small-caps; position: absolute; right: 6px; bottom: 8px;">Updated <?php echo $entry->getHumanTimeDiff() ?> ago</span><br />
	</div>
	
</div>