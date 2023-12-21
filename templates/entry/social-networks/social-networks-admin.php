<?php
/**
 * This is a copy/paste of the code which used to reside in manage.php file.
 *
 * @var cnCollection           $networks
 * @var cnEntry_Social_Network $network
 *
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 */

echo '<div class="social-networks">';

foreach ( $networks as $network ) {

	$preferred = $network->preferred ? '*' : '';

	echo '<span class="social-network"><strong>', esc_html( $network->name ), ':</strong> <a target="_blank" href="', esc_url( $network->url ), '">', esc_html( $network->url ), '</a>', esc_html( $preferred ), '</span>';
}

echo '</div>';
