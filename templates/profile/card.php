<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @var cnOutput $entry
 */
$style  = array(
	'background-color' => '#FFF',
	'border'           => $atts['border_width'] . 'px solid ' . $atts['border_color'],
	'border-radius'    => $atts['border_radius'] . 'px',
	'color'            => '#000',
	'margin'           => '8px 0',
	'padding'          => '10px',
	'position'         => 'relative',
);
?>
<div class="cn-entry" <?php echo cnHTML::attribute( 'style', $style ); ?>>

	<span style="float: <?php echo is_rtl() ? 'right' : 'left'; ?>; margin-right: 10px;">

		<?php

		if ( 'none' !== $atts['image_type'] ) {

			$entry->getImage(
				array(
					'image'    => $atts['image_type'],
					'preset'   => empty( $atts['image_width'] ) && empty( $atts['image_height'] ) ? 'profile' : NULL,
					'width'    => $atts['image_width'],
					'height'   => $atts['image_height'],
					'zc'       => $atts['image_crop_mode'],
					'fallback' => array(
						'type'   => $atts['image_fallback'] ? 'block' : 'none',
						'string' => $atts['image_fallback_string'],
					),
					'permalink' => TRUE,
				)
			);

		}

		?>

	</span>

	<div style="margin-left: 10px;">
		<span style="font-size:larger;font-variant: small-caps"><strong><?php $entry->getNameBlock(); ?></strong></span>
		<div style="margin-bottom: 20px;">
			<?php

			if ( $atts['show_title'] ) $entry->getTitleBlock();

			if ( $atts['show_org'] || $atts['show_dept'] ) {
				$entry->getOrgUnitBlock(
					array(
						'show_org'  => $atts['show_org'],
						'show_dept' => $atts['show_dept'],
					)
				);
			}

			?>
		</div>
		<?php echo $entry->getBioBlock(); ?>
	</div>


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
						)
					)
				);
			}
			?>
		</div>
	</div>
</div>
