<?php
/**
 * This is a copy/paste of the code which used to reside in manage.php file.
 *
 * @todo Clean so it is better "template" code.
 *
 * @var cnCollection $networks
 * @var cnMessenger  $messenger
 *
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 */

echo '<div class="im-ids">';

foreach ( $networks as $messenger ) {

	$preferred = $messenger->preferred ? '*' : '';

	echo '<span class="im"><strong>', esc_html( $messenger->name ), ':</strong> ', esc_html( "{$messenger->uid}{$preferred}" ), '</span>';
}

echo '</div>';
