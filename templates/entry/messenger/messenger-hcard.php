<?php
/**
 * This is basically a copy/paste of the code which use to reside in cnOutput::getImBlock().
 *
 * @todo Clean so it is better "template" code.
 *
 * @var array        $atts
 * @var cnOutput     $entry
 * @var cnCollection $networks
 * @var cnMessenger  $messenger
 */

$rows   = array();
$search = array( '%label%' , '%id%' , '%separator%' );

foreach ( $networks as $messenger ) {
	$replace = array();

	$row = "\t" . '<span class="im-network cn-im-network' . ( $messenger->preferred ? ' cn-preferred cn-im-network-preferred' : '' ) . '">';

	( empty( $messenger->name ) ) ? $replace[] = '' : $replace[] = '<span class="im-name">' . $messenger->name . '</span>';

	switch ( $messenger->type ) {
		case 'aim':
			$replace[] = empty( $messenger->uid ) ? '' : '<a class="url im-id" href="aim:goim?screenname=' . $messenger->uid . '">' . $messenger->uid . '</a>';
			break;

		case 'yahoo':
			$replace[] = empty( $messenger->uid ) ? '' : '<a class="url im-id" href="ymsgr:sendIM?' . $messenger->uid . '">' . $messenger->uid . '</a>';
			break;

		case 'jabber':
			$replace[] = empty( $messenger->uid ) ? '' : '<span class="im-id">' . $messenger->uid . '</span>';
			break;

		case 'messenger':
			$replace[] = empty( $messenger->uid ) ? '' : '<a class="url im-id" href="msnim:chat?contact=' . $messenger->uid . '">' . $messenger->uid . '</a>';
			break;

		case 'skype':
			$replace[] = empty( $messenger->uid ) ? '' : '<a class="url im-id" href="skype:' . $messenger->uid . '?chat">' . $messenger->uid . '</a>';
			break;

		case 'icq':
			$replace[] = empty( $messenger->uid ) ? '' : '<a class="url im-id" type="application/x-icq" href="http://www.icq.com/people/cmd.php?uin=' . $messenger->uid . '&action=message">' . $messenger->uid . '</a>';
			break;

		default:
			$replace[] = empty( $messenger->uid ) ? '' : '<span class="im-id">' . $messenger->uid . '</span>';
			break;
	}

	$replace[] = '<span class="cn-separator">' . $atts['separator'] . '</span>';

	$row .= str_ireplace(
		$search,
		$replace,
		empty( $atts['format'] ) ? ( empty( $defaults['format'] ) ? '%label%%separator% %id%' : $defaults['format'] ) : $atts['format']
	);

	$row .= '</span>' . PHP_EOL;

	$rows[] = apply_filters( 'cn_output_messenger_id', cnString::replaceWhatWith( $row, ' ' ), $messenger, $entry, $atts );
}

$block = '<span class="im-network-block">' . PHP_EOL . implode( PHP_EOL, $rows ) . PHP_EOL .'</span>';

$block = apply_filters( 'cn_output_messenger_ids', $block, $networks, $entry, $atts );

echo $block;
