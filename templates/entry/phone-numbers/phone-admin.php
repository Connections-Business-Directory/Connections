<?php
/**
 * This is a copy/paste of the code which used to reside in manage.php file.
 *
 * @var cnCollection $phoneNumbers
 * @var cnPhone      $phone
 */

echo '<div class="phone-numbers">';

foreach ( $phoneNumbers as $phone ) {

	$preferred = $phone->preferred ? '*' : '';

	echo '<span class="phone"><strong>' , $phone->name , '</strong>: ' ,  $phone->number , $preferred , '</span>';
}

echo '</div>';
