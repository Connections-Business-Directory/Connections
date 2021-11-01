<?php
/**
 * This is a copy/paste of the code which used to reside in manage.php file.
 *
 * @todo Clean so it is better "template" code.
 *
 * @var cnCollection    $emailAddresses
 * @var cnEmail_Address $email
 *
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 */

echo '<div class="email-addresses">';

foreach ( $emailAddresses as $email ) {

	$preferred = $email->preferred ? '*' : '';

	echo '<span class="email"><strong>' , $email->name , ':</strong> <a href="mailto:' , $email->address , '">' , $email->address , '</a>' , $preferred , '</span>';
}

echo '</div>';
