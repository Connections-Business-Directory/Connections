<?php
/**
 * @var array    $atts
 * @var cnOutput $entry
 */
?>
<div class="cn-team-member">
	<div class="cn-team-member-image">
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
		?>
	</div>
	<div class="cn-team-member-details">
		<?php
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

				echo $number;

			} else {

				$entry->getPhoneNumberBlock( array( 'format' => '%number%', 'limit' => 1 ) );
			}
		}

		if ( $atts['displayEmail'] ) {

			// $entry->getEmailAddressBlock( array( 'format' => '%address%' ) );
			$email = $entry->getEmailAddressBlock( array( 'preferred' => true, 'format' => '%address%', 'return' => true ) );

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
</div>
