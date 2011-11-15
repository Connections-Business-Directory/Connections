<?php
	if ( is_admin() )
	{
		if ( !isset($form) ) $form = new cnFormObjects();
		
		$editTokenURL = $form->tokenURL( 'admin.php?page=connections_manage&action=edit&id=' . $entry->getId(), 'entry_edit_' . $entry->getId() );
		
		if (current_user_can('connections_edit_entry'))
		{
			echo '<span class="cn-entry-name"><a class="row-title" title="Edit ' , $entry->getFullFirstLastName() , '" href="' , $editTokenURL , '"> ' , $entry->getFullFirstLastName() . '</a></span> <span class="cn-upcoming-date">' , $entry->getUpcoming($atts['list_type'], $atts['date_format']) , '</span>';
		}
		else
		{
			echo '<span class="cn-entry-name">' , $entry->getFullFirstLastName() , '</span> <span class="cn-upcoming-date">' , $entry->getUpcoming($atts['list_type'], $atts['date_format']) , '</span>';
		}

	}
?>