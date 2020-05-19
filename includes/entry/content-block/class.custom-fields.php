<?php

namespace Connections_Directory\Entry\Content_Block;

use cnMeta;
use cnOutput;
use Connections_Directory\Entry\Content_Blocks;

/**
 * Class Custom_Fields
 *
 * @package Connections_Directory\Entry\Content_Block
 */
class Custom_Fields {

	/**
	 * @since 9.6
	 */
	public static function add() {

		$atts = array(
			'name'                => __( 'Custom Fields', 'connections' ),
			'slug'                => 'custom-fields',
			'render_callback'     => array( __CLASS__, 'render' ),
			'permission_callback' => array( __CLASS__, 'permission' ),
		);

		Content_Blocks::instance()->add( 'meta', $atts );
	}

	/**
	 * @since 9.6
	 *
	 * @param array $block Content Block attributes.
	 *
	 * @return bool
	 */
	public static function permission( $block ) {

		return true;
	}

	/**
	 * Renders the Custom Fields attached to the entry.
	 *
	 * @since 9.6
	 *
	 * @param cnOutput $entry
	 */
	public static function render( $entry ) {

		$metadata = $entry->getMeta();

		if ( ! empty( $metadata ) ) {

			do_action(
				'Connections_Directory/Entry/Content_Block/Custom_Fields/Before',
				$entry
			);

			self::content( $metadata );

			do_action(
				'Connections_Directory/Entry/Content_Block/Custom_Fields/After',
				$entry
			);
		}
	}

	/**
	 * Renders the data saved in the "Custom Fields" entry metabox.
	 * This should not be confused with the fields registered with
	 * cnMetaboxAPI. Those fields should be output using a registered
	 * action: `"cn_entry_output_content-$id"`.
	 *
	 * @since  9.6
	 *
	 * @param array $metadata The metadata array
	 */
	private static function content( $metadata ) {

		$html = '';

		$defaults = array(
			'container_tag' => 'ul',
			'item_tag'      => 'li',
			'key_tag'       => 'span',
			'value_tag'     => 'span',
			'separator'     => ': ',
			'before'        => '',
			'after'         => '',
		);

		$atts = wp_parse_args( apply_filters( 'cn_output_meta_atts', $defaults ), $defaults );

		foreach ( (array) $metadata as $key => $value ) {

			// Do not render any private keys; ie. ones that begin with an underscore
			// or any fields registered as part of a custom metabox.
			if ( cnMeta::isPrivate( $key, 'entry' ) ) continue;

			$html .= apply_filters(
				'cn_entry_output_meta_key',
				sprintf(
					'<%1$s><%2$s class="cn-entry-meta-key">%3$s%4$s</%2$s><%5$s class="cn-entry-meta-value">%6$s</%5$s></%1$s>' . PHP_EOL,
					$atts['item_tag'],
					$atts['key_tag'],
					trim( $key ),
					$atts['separator'],
					$atts['value_tag'],
					implode( ', ', (array) $value )
				),
				$atts,
				$key,
				$value
			);
		}

		if ( empty( $html ) ) {

			return;
		}

		$html = apply_filters(
			'cn_entry_output_meta_container',
			sprintf(
				'<%1$s class="cn-entry-meta">%2$s</%1$s>' . PHP_EOL,
				$atts['container_tag'],
				$html
			),
			$atts,
			$metadata
		);

		echo $atts['before'] . $html . $atts['after'] . PHP_EOL;
	}
}
