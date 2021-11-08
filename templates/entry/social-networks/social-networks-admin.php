<?php
/**
 * This is a copy/paste of the code which used to reside in manage.php file.
 *
 * @todo Clean so it is better "template" code.
 *
 * @var cnCollection           $networks
 * @var cnEntry_Social_Network $network
 */
echo '<div class="social-networks">';

foreach ( $networks as $network ) {

	$network->preferred ? $preferred = '*' : $preferred = '';

	echo '<span class="social-network"><strong>', $network->name, ':</strong> <a target="_blank" href="', $network->url, '">', $network->url . '</a>', $preferred, '</span>';
}

echo '</div>';
