<?php
/**
 * @var array    $atts
 * @var cnOutput $entry
 */
?>
<div class="cn-table-row cn-team-member">
	<div class="cn-table-cell cn-team-member-image">
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
	<div class="cn-table-cell cn-team-member-name">
		<?php
		$entry->getNameBlock( array( 'link' => FALSE ) );
		?>
		<?php if ( $atts['displayTitle'] ) : ?>
			<?php $entry->getTitleBlock(); ?>
		<?php endif; ?>
	</div>

	<?php if ( $atts['displayExcerpt'] ) : ?>
		<div class="cn-table-cell cn-team-member-excerpt">
			<?php $entry->excerpt( array( 'length' => absint( $atts['excerptWordLimit'] ), 'more' => '' ) ); ?>
		</div>
	<?php endif; ?>

	<?php if ( $atts['displayPhone'] ) : ?>
		<div class="cn-table-cell cn-team-member-phone">
			<?php
			//$entry->getPhoneNumberBlock( array( 'format' => '%number%' ) );
			$number = $entry->getPhoneNumberBlock( array( 'preferred' => TRUE, 'format' => '%number%', 'return' => TRUE ) );

			if ( $number ) {

				echo $number;

			} else {

				$entry->getPhoneNumberBlock( array( 'format' => '%number%', 'limit' => 1 ) );
			}
			?>
		</div>
	<?php endif; ?>


	<?php if ( $atts['displayEmail'] ) : ?>
		<div class="cn-table-cell cn-team-member-email">
			<?php
			//$entry->getEmailAddressBlock( array( 'format' => '%address%' ) );
			$email = $entry->getEmailAddressBlock( array( 'preferred' => TRUE, 'format' => '%address%', 'return' => TRUE ) );

			if ( $email ) {

				echo $email;

			} else {

				$entry->getEmailAddressBlock( array( 'format' => '%address%', 'limit' => 1 ) );
			}
			?>
		</div>
	<?php endif; ?>


	<?php if ( $atts['displaySocial'] ) : ?>
		<div class="cn-table-cell cn-team-member-social-media">
			<?php $entry->getSocialMediaBlock( array( 'size' => 24 ) ); ?>
		</div>
	<?php endif; ?>

</div>
