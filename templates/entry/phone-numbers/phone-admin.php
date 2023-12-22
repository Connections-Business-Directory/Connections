<?php
/**
 * This is a copy/paste of the code which used to reside in manage.php file.
 *
 * @var cnCollection $phoneNumbers
 * @var cnPhone      $phone
 *
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 */

echo '<div class="phone-numbers">';

foreach ( $phoneNumbers as $phone ) {

	$preferred = $phone->preferred ? '*' : '';

	echo '<span class="phone"><strong>', esc_html( $phone->name ), '</strong>: ', esc_html( "{$phone->number}{$preferred}" ), '</span>';
}

echo '</div>';
