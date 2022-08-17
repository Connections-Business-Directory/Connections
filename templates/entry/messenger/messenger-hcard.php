<?php
/**
 * This is basically a copy/paste of the code which used to reside in cnOutput::getImBlock().
 *
 * @todo Clean so it is better "template" code.
 *
 * @var array        $atts
 * @var cnOutput     $entry
 * @var cnCollection $networks
 * @var cnMessenger  $messenger
 *
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 * @phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited
 */

use Connections_Directory\Utility\_escape;
use Connections_Directory\Utility\_string;

$rows   = array();
$search = array( '%label%', '%id%', '%separator%' );

foreach ( $networks as $messenger ) {

	$replace = array();

	$classNames = array(
		'im-network',
		'cn-im-network',
	);

	if ( $messenger->preferred ) {

		$classNames[] = 'cn-preferred';
		$classNames[] = 'cn-im-network-preferred';
	}

	$replace[] = empty( $messenger->name ) ? '' : '<span class="im-name">' . esc_html( $messenger->name ) . '</span>';

	switch ( $messenger->type ) {
		case 'aim':
			$replace[] = empty( $messenger->uid ) ? '' : '<a class="url im-id" href="aim:goim?screenname=' . esc_attr( $messenger->uid ) . '">' . esc_html( $messenger->uid ) . '</a>';
			break;

		case 'yahoo':
			$replace[] = empty( $messenger->uid ) ? '' : '<a class="url im-id" href="ymsgr:sendIM?' . esc_attr( $messenger->uid ) . '">' . esc_html( $messenger->uid ) . '</a>';
			break;

		case 'skype':
			$replace[] = empty( $messenger->uid ) ? '' : '<a class="url im-id" href="skype:' . esc_attr( $messenger->uid ) . '?chat">' . esc_html( $messenger->uid ) . '</a>';
			break;

		case 'icq':
			$replace[] = empty( $messenger->uid ) ? '' : '<a class="url im-id" type="application/x-icq" href="https://www.icq.com/people/cmd.php?uin=' . esc_attr( $messenger->uid ) . '&action=message">' . esc_html( $messenger->uid ) . '</a>';
			break;

		case 'telegram':
			$replace[] = empty( $messenger->uid ) ? '' : '<a class="url im-id" href="' . esc_url( "https://t.me/{$messenger->uid}" ) . '">' . esc_html( $messenger->uid ) . '</a>';
			break;

		case 'whatsapp':
			$replace[] = empty( $messenger->uid ) ? '' : '<a class="url im-id" href="' . esc_url( "https://wa.link/{$messenger->uid}" ) . '">' . esc_html( $messenger->uid ) . '</a>';
			break;

		case 'jabber':
		case 'messenger':
		default:
			$replace[] = empty( $messenger->uid ) ? '' : '<span class="im-id">' . esc_html( $messenger->uid ) . '</span>';
			break;
	}

	$replace[] = '<span class="cn-separator">' . esc_html( $atts['separator'] ) . '</span>';

	$row = '<span class="' . _escape::classNames( $classNames ) . '">';

	$row .= str_ireplace(
		$search,
		$replace,
		empty( $atts['format'] ) ? ( empty( $defaults['format'] ) ? '%label%%separator% %id%' : $defaults['format'] ) : $atts['format']
	);

	$row .= '</span>';

	$rows[] = apply_filters( 'cn_output_messenger_id', _string::normalize( $row ), $messenger, $entry, $atts );
}

$block = '<span class="im-network-block">' . PHP_EOL . implode( PHP_EOL, $rows ) . PHP_EOL . '</span>';

$block = apply_filters( 'cn_output_messenger_ids', $block, $networks, $entry, $atts );

// HTML is escape in the loop above.
echo $block; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
