<?php
/**
 * This is a copy/paste of the code which use to reside in manage.php file.
 *
 * @todo Clean so it is better "template" code.
 *
 * @var cnCollection $phoneNumbers
 * @var cnPhone      $phone
 */

echo '<div class="phone-numbers">';

foreach ( $phoneNumbers as $phone ) {
	( $phone->preferred ) ? $preferred = '*' : $preferred = '';

	echo '<span class="phone"><strong>' , $phone->name , '</strong>: ' ,  $phone->number , $preferred , '</span>';
}

echo '</div>';
