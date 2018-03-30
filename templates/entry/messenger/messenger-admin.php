<?php
/**
 * This is a copy/paste of the code which use to reside in manage.php file.
 *
 * @todo Clean so it is better "template" code.
 *
 * @var cnCollection $networks
 * @var cnMessenger  $messenger
 */

echo '<div class="im-ids">';

foreach ( $networks as $messenger ) {
	$messenger->preferred ? $preferred = '*' : $preferred = '';

	echo '<span class="im"><strong>' , $messenger->name , ':</strong> ' , $messenger->uid , $preferred , '</span>';
}

echo '</div>';
