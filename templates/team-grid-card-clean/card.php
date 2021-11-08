<?php
/**
 * @var array $atts
 * @var cnOutput $entry
 */
?>
<div class="cn-team-member">
	<?php
	$entry->getImage(
		array(
			'image'   => $atts['imageType'],
			'width'   => 600,
			'height'  => 600,
			'zc'      => absint( $atts['imageCropMode'] ),
			'quality' => 90,
		)
	);

	$entry->getNameBlock( array( 'link' => false ) );

	if ( $atts['displayTitle'] ) {

		$entry->getTitleBlock();
	}

	if ( $atts['displayExcerpt'] ) {

		$entry->excerpt( array( 'length' => absint( $atts['excerptWordLimit'] ), 'more' => '' ) );
	}

	if ( $atts['displayPhone'] ) {

		// $entry->getPhoneNumberBlock( array( 'format' => '%number%' ) );
		$number = $entry->getPhoneNumberBlock( array( 'preferred' => true, 'format' => '%number%', 'return' => true ) );

		if ( $number ) {

			// Output is escaped in the `templates/entry/phone-numbers/phone-hcard.php` file.
			echo $number; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		} else {

			$entry->getPhoneNumberBlock( array( 'format' => '%number%', 'limit' => 1 ) );
		}
	}

	if ( $atts['displayEmail'] ) {

		// $entry->getEmailAddressBlock( array( 'format' => '%address%' ) );
		$email = $entry->getEmailAddressBlock( array( 'preferred' => true, 'format' => '%address%', 'return' => true ) );

		if ( $email ) {

			// Output is escaped in the `templates/entry/email-addresses/email-hcard.php` file.
			echo $email; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		} else {

			$entry->getEmailAddressBlock( array( 'format' => '%address%', 'limit' => 1 ) );
		}
	}

	if ( $atts['displaySocial'] ) {

		$entry->getSocialMediaBlock( array( 'size' => 24 ) );
	}
	?>
</div>
