<?php
/**
 * @var array $atts
 * @var cnOutput $entry
 */
?>
<div class="slick-slider-slide">
	<div class="slick-slide-grid">

		<div class='slick-slide-column'>

			<?php
			$entry->getImage(
				array(
					'image'    => $atts['imageType'],
					'size'     => 'custom',
					'width'    => 600,
					'height'   => 600,
					'zc'       => $atts['imageCropMode'],
					'lazyload' => false,
				)
			);
			?>
			<h3><?php echo $entry->getName(); ?></h3>
			<?php

			if ( $atts['displayTitle'] ) {

				$entry->getTitleBlock();
			}

			if ( $atts['displayPhone'] ) {

				// $entry->getPhoneNumberBlock( array( 'format' => '%number%' ) );
				$number = $entry->getPhoneNumberBlock(
					array(
						'preferred' => true,
						'format'    => '%number%',
						'return'    => true,
					)
				);

				if ( $number ) {

					echo $number;

				} else {

					$entry->getPhoneNumberBlock(
						array(
							'format' => '%number%',
							'limit'  => 1,
						)
					);
				}
			}

			if ( $atts['displayEmail'] ) {

				// $entry->getEmailAddressBlock( array( 'format' => '%address%' ) );
				$email = $entry->getEmailAddressBlock(
					array(
						'preferred' => true,
						'format'    => '%address%',
						'return'    => true,
					)
				);

				if ( $email ) {

					echo $email;

				} else {

					$entry->getEmailAddressBlock(
						array(
							'format' => '%address%',
							'limit'  => 1,
						)
					);
				}
			}

			if ( $atts['displaySocial'] ) {

				$entry->getSocialMediaBlock( array( 'size' => 24 ) );
			}

			?>

		</div>

		<div class='slick-slide-column'>
			<?php

			if ( $atts['displayExcerpt'] ) {

				$entry->excerpt(
					array(
						'length' => absint( $atts['excerptWordLimit'] ),
						'more'   => '',
					)
				);
			}

			?>
		</div>

	</div>

</div><!--.slick-slider-item-->
