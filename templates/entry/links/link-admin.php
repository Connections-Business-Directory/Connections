<?php
/**
 * This is a copy/paste of the code which use to reside in manage.php file.
 *
 * @todo Clean so it is better "template" code.
 *
 * @var cnCollection $links
 * @var cnLink       $link
 */

echo '<div class="links">';

foreach ( $links as $link ) {
	( $link->preferred ) ? $preferred = '*' : $preferred = '';

	echo '<span class="link"><strong>' , $link->name , ':</strong> <a target="_blank" href="' , $link->url , '">' , $link->url , '</a>' , $preferred , '</span>';
}

echo '</div>';
