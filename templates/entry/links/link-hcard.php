<?php
/**
 * This is basically a copy/paste of the code which used to reside in cnOutput::getLinkBlock().
 *
 * @var array        $atts
 * @var cnOutput     $entry
 * @var cnCollection $links
 * @var cnLink       $link
 *
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 * @phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited
 */

use Connections_Directory\Utility\_escape;

$rows          = array();
$search        = array( '%label%', '%title%', '%url%', '%image%', '%icon%', '%separator%' );
$iconSizes     = array( 16, 24, 32, 48, 64 );
$targetOptions = array(
	'new'  => '_blank',
	'same' => '_self',
);

/*
 * Ensure the supplied size is valid, if not reset to the default value.
 */

$icon = array();

$icon['width']  = in_array( $atts['icon_size'], $iconSizes ) ? $atts['icon_size'] : 32;
$icon['height'] = $icon['width'];
$icon['src']    = CN_URL . 'assets/images/icons/link/link_' . $icon['width'] . '.png';

foreach ( $links as $link ) {

	$icon = apply_filters( 'cn_output_link_icon', $icon, $link->type );

	$replace = array();

	$classNames = array(
		'link',
		'cn-link',
		$link->type,
	);

	if ( $link->preferred ) {

		$classNames[] = 'cn-preferred';
		$classNames[] = 'cn-link-preferred';
	}

	if ( empty( $atts['label'] ) ) {

		$name = empty( $link->name ) ? '' : $link->name;

	} else {

		$name = $atts['label'];
	}

	$target = array_key_exists( $link->target, $targetOptions ) ? $targetOptions[ $link->target ] : '_self';
	$follow = $link->follow ? '' : 'rel="nofollow"';

	$replace[] = '<span class="link-name">' . $name . '</span>';

	// The `notranslate` class is added to prevent Google Translate from translating the text.
	$replace[] = empty( $link->title ) ? '' : '<a class="url" href="' . esc_url( $link->url ) . '" target="' . esc_attr( $target ) . '" rel="noopener" ' . $follow . '>' . esc_html( $link->title ) . '</a>';
	$replace[] = '<a class="url notranslate" href="' . esc_url( $link->url ) . '" target="' . esc_attr( $target ) . '" rel="noopener" ' . $follow . '>' . esc_html( $link->url ) . '</a>';

	if ( false !== filter_var( $link->url, FILTER_VALIDATE_URL ) &&
		 false !== strpos( $atts['format'], '%image%' ) ) {

		$screenshot = new cnSiteShot(
			array(
				'url'    => $link->url,
				'alt'    => esc_attr( $link->url ),
				'title'  => $name,
				'target' => $target,
				'follow' => $link->follow,
				'return' => true,
			)
		);

		$size = $screenshot->setSize( $atts['size'] );

		$screenshot->setBefore( '<span class="cn-image-style" style="display: inline-block;"><span style="display: block; max-width: 100%; width: ' . esc_attr( $size['width'] ) . 'px">' );
		$screenshot->setAfter( '</span></span>' );

		$replace[] = $screenshot->render();

	} else {

		$replace[] = '';
	}

	$replace[] = '<span class="link-icon"><a class="url" title="' . esc_attr( $link->title ) . '" href="' . esc_url( $link->url ) . '" target="' . esc_attr( $target ) . '" ' . $follow . '><img src="' . esc_url( $icon['src'] ) . '" height="' . esc_attr( $icon['height'] ) . '" width="' . esc_attr( $icon['width'] ) . '"/></a></span>';

	$replace[] = '<span class="cn-separator">' . esc_html( $atts['separator'] ) . '</span>';

	$row = '<span class="' . _escape::classNames( $classNames ) . '">';

	$row .= str_ireplace(
		$search,
		$replace,
		empty( $atts['format'] ) ? ( empty( $defaults['format'] ) ? '%label%%separator% %title%' : $defaults['format'] ) : $atts['format']
	);

	$row .= '</span>';

	$rows[] = apply_filters( 'cn_output_link', cnString::replaceWhatWith( $row, ' ' ), $link, $entry, $atts );
}

$block = '<span class="link-block">' . PHP_EOL . implode( PHP_EOL, $rows ) . PHP_EOL . '</span>';

$block = apply_filters( 'cn_output_links', $block, $links, $entry, $atts );

// HTML is escape in the loop above.
echo $block; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
