<?php
/**
 * This is basically a copy/paste of the code which use to reside in cnOutput::getLinkBlock().
 *
 * @todo Clean so it is better "template" code.
 *
 * @var array        $atts
 * @var cnOutput     $entry
 * @var cnCollection $links
 * @var cnLink       $link
 */

$rows          = array();
$search        = array( '%label%', '%title%', '%url%', '%image%', '%icon%', '%separator%' );
$iconSizes     = array( 16, 24, 32, 48, 64 );
$targetOptions = array( 'new' => '_blank', 'same' => '_self' );

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

	if ( empty( $atts['label'] ) ) {

		$name = empty( $link->name ) ? '' : $link->name;

	} else {

		$name = $atts['label'];
	}

	$url    = cnSanitize::field( 'url', $link->url );
	$target = array_key_exists( $link->target, $targetOptions ) ? $targetOptions[ $link->target ] : '_self';
	$follow = $link->follow ? '' : 'rel="nofollow"';

	$replace[] = '<span class="link-name">' . $name . '</span>';

	// The `notranslate` class is added to prevent Google Translate from translating the text.
	$replace[] = empty( $link->title ) ? '' : '<a class="url" href="' . $url . '"' . ' target="' . $target . '" ' . $follow . '>' . $link->title . '</a>';
	$replace[] = '<a class="url notranslate" href="' . $url . '"' . ' target="' . $target . '" ' . $follow . '>' . $url . '</a>';

	if ( FALSE !== filter_var( $link->url, FILTER_VALIDATE_URL ) &&
	     FALSE !== strpos( $atts['format'], '%image%' ) ) {

		$screenshot = new cnSiteShot(
			array(
				'url'    => $link->url,
				'alt'    => $url,
				'title'  => $name,
				'target' => $target,
				'follow' => $link->follow,
				'return' => TRUE,
			)
		);

		$size = $screenshot->setSize( $atts['size'] );

		/** @noinspection CssInvalidPropertyValue */
		$screenshot->setBefore( '<span class="cn-image-style" style="display: inline-block;"><span style="display: block; max-width: 100%; width: ' . $size['width'] . 'px">' );
		$screenshot->setAfter( '</span></span>' );

		$replace[] = $screenshot->render();

	} else {

		$replace[] = '';
	}

	$replace[] = '<span class="link-icon"><a class="url" title="' . $link->title . '" href="' . $url . '" target="' . $target . '" ' . $follow . '><img src="' . $icon['src'] . '" height="' . $icon['height'] . '" width="' . $icon['width'] . '"/></a></span>';

	$replace[] = '<span class="cn-separator">' . $atts['separator'] . '</span>';

	$row = "\t" . '<span class="link ' . $link->type . ' cn-link' . ( $link->preferred ? ' cn-preferred cn-link-preferred' : '' ) . '">';

	$row .= str_ireplace(
		$search,
		$replace,
		empty( $atts['format'] ) ? ( empty( $defaults['format'] ) ? '%label%%separator% %title%' : $defaults['format'] ) : $atts['format']
	);

	$row .= '</span>';

	$rows[] = apply_filters( 'cn_output_link', cnString::replaceWhatWith( $row, ' ' ), $link, $entry, $atts );
}

$block = '<span class="link-block">' . PHP_EOL . implode( PHP_EOL, $rows ) . PHP_EOL .'</span>';

$block = apply_filters( 'cn_output_links', $block, $links, $entry, $atts );

echo $block;
