<?php
/**
 * @var array $atts
 * @var cnOutput $entry
 */
?>
<div class="cn-team-member">
	<?php
	$entry->getImage(
		array( 'width' => 600, 'height' => 600, 'zc' => absint( $atts['imageCropMode'] ), 'quality' => 90 )
	);

	$entry->getNameBlock( array( 'link' => FALSE ) );

	if ( $atts['displayTitle'] ) {

		$entry->getTitleBlock();
	}

	if ( $atts['displayExcerpt'] ) {

		$entry->excerpt( array( 'length' => absint( $atts['excerptWordLimit'] ), 'more' => '' ) );
	}

	if ( $atts['displayPhone'] ) {

		//$entry->getPhoneNumberBlock( array( 'format' => '%number%' ) );
		$number = $entry->getPhoneNumberBlock( array( 'preferred' => TRUE, 'format' => '%number%', 'return' => TRUE ) );

		if ( $number ) {

			echo $number;

		} else {

			$entry->getPhoneNumberBlock( array( 'format' => '%number%', 'limit' => 1 ) );
		}
	}

	if ( $atts['displayEmail'] ) {

		//$entry->getEmailAddressBlock( array( 'format' => '%address%' ) );
		$email = $entry->getEmailAddressBlock( array( 'preferred' => TRUE, 'format' => '%address%', 'return' => TRUE ) );

		if ( $email ) {

			echo $email;

		} else {

			$entry->getEmailAddressBlock( array( 'format' => '%address%', 'limit' => 1 ) );
		}
	}

	if ( $atts['displaySocial'] ) {

		$entry->getSocialMediaBlock( array( 'size' => 24 ) );
	}
	?>
</div>
