<?php
/**
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Connections_Directory\Utility\_escape;
use Connections_Directory\Utility\_html;

/**
 * @var array        $atts  The shortcode attributes arrays.
 * @var cnEntry_HTML $entry Instance of the cnEntry_HTML object.
 * @var cnTemplate   $template Instance of the cnTemplate object.
 */
$style = array(
	'background-color' => '#FFF',
	'border'           => $atts['border_width'] . 'px solid ' . $atts['border_color'],
	'border-radius'    => $atts['border_radius'] . 'px',
	'color'            => '#000',
	'margin'           => '8px 0',
	'padding'          => '10px',
	'position'         => 'relative',
);
$bio   = $entry->getBio();
$css   = _html::stringifyCSSAttributes( $style );
$notes = $entry->getNotes();
?>
<div class="cn-entry" style="<?php _escape::css( $css, true ); ?>">

	<div class="cn-left" style="width:49%; float:<?php echo is_rtl() ? 'right' : 'left'; ?>">
		<?php

		if ( 'none' !== $atts['image_type'] ) {

			$entry->getImage(
				array(
					'image'     => $atts['image_type'],
					'width'     => $atts['image_width'],
					'height'    => $atts['image_height'],
					'zc'        => $atts['image_crop_mode'],
					'fallback'  => array(
						'type'   => $atts['image_fallback'] ? 'block' : 'none',
						'string' => $atts['image_fallback_string'],
					),
					'permalink' => cnSettingsAPI::get( 'connections', 'connections_link', 'name' ),
				)
			);

		}

		?>
		<div style="clear:both;"></div>
		<div style="margin-bottom: 10px;">
			<div style="font-size:larger;font-variant: small-caps"><strong><?php echo esc_html( $entry->getNameBlock( array( 'format' => $atts['name_format'] ) ) ); ?></strong></div>
			<?php

			if ( $atts['show_title'] ) {
				$entry->getTitleBlock();
			}

			if ( $atts['show_org'] || $atts['show_dept'] ) {
				$entry->getOrgUnitBlock(
					array(
						'show_org'  => $atts['show_org'],
						'show_dept' => $atts['show_dept'],
					)
				);
			}

			if ( $atts['show_contact_name'] ) {

				$entry->getContactNameBlock(
					array(
						'format' => $atts['contact_name_format'],
						// 'label'  => $atts['str_contact_label']
					)
				);
			}

			?>
		</div>

	</div>

	<div class="cn-right" style="text-align: right;">

		<?php
		if ( $atts['show_addresses'] ) {
			$entry->getAddressBlock(
				array(
					'format' => $atts['address_format'],
					'type'   => $atts['address_types'],
				)
			);
		}

		if ( $atts['show_family'] ) {
			$entry->getFamilyMemberBlock();
		}

		if ( $atts['show_phone_numbers'] ) {
			$entry->getPhoneNumberBlock(
				array(
					'format' => $atts['phone_format'],
					'type'   => $atts['phone_types'],
				)
			);
		}

		if ( $atts['show_email'] ) {
			$entry->getEmailAddressBlock(
				array(
					'format' => $atts['email_format'],
					'type'   => $atts['email_types'],
				)
			);
		}

		if ( $atts['show_im'] ) {
			$entry->getImBlock();
		}

		if ( $atts['show_dates'] ) {
			$entry->getDateBlock(
				array(
					'format' => $atts['date_format'],
					'type'   => $atts['date_types'],
				)
			);
		}

		if ( $atts['show_links'] ) {
			$entry->getLinkBlock(
				array(
					'format' => $atts['link_format'],
					'type'   => $atts['link_types'],
				)
			);
		}

		if ( $atts['show_social_media'] ) {
			$entry->getSocialMediaBlock();
		}

		?>

	</div>

	<div style="clear:both"></div>

	<?php

	if ( $atts['show_bio'] && 0 < strlen( $bio ) ) {

		$entry->getBioBlock(
			array(
				'before' => '<h4>' . esc_html__( 'Biographical Info', 'connections' ) . '</h4>' . PHP_EOL,
				'after'  => '<div class="cn-clear"></div>',
			)
		);

	}

	if ( $atts['show_notes'] && 0 < strlen( $notes ) ) {

		$entry->getNotesBlock(
			array(
				'before' => '<h4>' . esc_html__( 'Notes', 'connections' ) . '</h4>' . PHP_EOL,
				'after'  => '<div class="cn-clear"></div>',
			)
		);

	}

	?>

	<div class="cn-meta" style="margin-top: 6px">
		<?php
		$entry->getContentBlock( $atts['content'], $atts, $template );
		?>
	</div>

	<div class="cn-clear" style="display:table;width:100%;">
		<div style="display:table-cell;vertical-align:middle;">
			<?php
			if ( $atts['show_categories'] ) {

				$entry->getCategoryBlock(
					array( 'separator' => ', ' )
				);
			}
			?>
		</div>
		<div style="display:table-cell;text-align:right;vertical-align:middle;">
			<?php
			if ( $atts['show_last_updated'] ) {

				cnTemplatePart::updated(
					array(
						'timestamp' => $entry->getUnixTimeStamp(),
						'style'     => array(
							'font-size'    => '10px',
							'font-variant' => 'small-caps',
							'margin-right' => '10px',
						),
					)
				);
			}

			if ( $atts['show_return_to_top'] ) {

				cnTemplatePart::returnToTop();
			}
			?>
		</div>
	</div>

</div>
