<?php

namespace Connections_Directory\Content_Blocks\Entry;

use cnEntry;
use cnMeta;
use Connections_Directory\Content_Block;
use Connections_Directory\Utility\_escape;

/**
 * Class Custom_Fields
 *
 * @package Connections_Directory\Content_Block
 */
class Custom_Fields extends Content_Block {

	/**
	 * The Content Block ID.
	 *
	 * @since 9.7
	 * @var string
	 */
	const ID = 'meta';

	/**
	 * Custom_Fields constructor.
	 *
	 * @since 9.7
	 *
	 * @param string $id The Content Block ID.
	 */
	public function __construct( $id ) {

		$atts = array(
			'name'                => __( 'Custom Metadata Fields', 'connections' ),
			'slug'                => 'custom-fields',
			'permission_callback' => array( $this, 'permission' ),
		);

		parent::__construct( $id, $atts );
	}

	/**
	 * Callback for the `permission_callback` parameter.
	 *
	 * @since 9.6
	 *
	 * @return bool
	 */
	public function permission() {

		return true;
	}

	/**
	 * Renders the Custom Metadata Fields attached to the entry.
	 *
	 * @since 9.6
	 */
	protected function content() {

		$entry = $this->getObject();

		if ( ! $entry instanceof cnEntry ) {

			return;
		}

		$metadata = $entry->getMeta();

		if ( false === $metadata ) {

			return;
		}

		if ( is_array( $metadata ) && ! empty( $metadata ) ) {

			do_action(
				"Connections_Directory/Content_Block/Entry/{$this->shortName}/Before",
				$entry
			);

			$this->metaFields( $metadata );

			do_action(
				"Connections_Directory/Content_Block/Entry/{$this->shortName}/After",
				$entry
			);
		}
	}

	/**
	 * Renders the data saved in the "Custom Metadata Fields" entry metabox.
	 * This should not be confused with the fields registered with
	 * cnMetaboxAPI. Those fields should be output using a registered
	 * action: `"cn_entry_output_content-$id"`.
	 *
	 * @since  9.6
	 *
	 * @param array $metadata The metadata array.
	 */
	private function metaFields( $metadata ) {

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

			// Do not render any private keys; i.e. ones that begin with an underscore
			// or any fields registered as part of a custom metabox.
			if ( cnMeta::isPrivate( $key, 'entry' ) ) {
				continue;
			}

			$html .= apply_filters(
				'cn_entry_output_meta_key',
				sprintf(
					'<%1$s><%2$s class="cn-entry-meta-key">%3$s%4$s</%2$s><%5$s class="cn-entry-meta-value">%6$s</%5$s></%1$s>' . PHP_EOL,
					_escape::tagName( $atts['item_tag'] ),
					_escape::tagName( $atts['key_tag'] ),
					esc_html( trim( $key ) ),
					esc_html( $atts['separator'] ),
					_escape::tagName( $atts['value_tag'] ),
					esc_html( implode( ', ', (array) $value ) )
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
				_escape::tagName( $atts['container_tag'] ),
				$html
			),
			$atts,
			$metadata
		);

		// HTML is escaped above.
		echo $atts['before'] . $html . $atts['after'] . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
