<?php
/**
 * This is a copy/paste of the code which used to reside in manage.php file.
 *
 * @var cnCollection $links
 * @var cnLink       $link
 *
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 * @phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited
 */

echo '<div class="links">';

foreach ( $links as $link ) {

	$preferred = $link->preferred ? '*' : '';

	echo '<span class="link"><strong>', esc_html( $link->name ), ':</strong> <a target="_blank" href="', esc_url( $link->url ), '">', esc_html( $link->url ), '</a>', esc_html( $preferred ), '</span>';
}

echo '</div>';
