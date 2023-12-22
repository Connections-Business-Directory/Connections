<?php

namespace Connections_Directory\Content_Blocks\Entry\Related;

use Connections_Directory\Content_Blocks\Entry\Related;

/**
 * Class Postal_Code
 *
 * @package Connections_Directory\Content_Blocks\Entry
 */
class Postal_Code extends Related {

	/**
	 * @since 9.8
	 * @var string
	 */
	const ID = 'entry-related-postal-code';

	/**
	 * @since 9.7
	 * @var array
	 */
	private $properties = array(
		'relation' => 'postal_code',
	);

	/**
	 * Related constructor.
	 *
	 * @since 9.8
	 *
	 * @param $id
	 */
	public function __construct( $id ) {

		$atts = array(
			'context'             => 'single',
			'name'                => __( 'Related Entries by Zipcode', 'connections' ),
			'permission_callback' => '__return_true',
			'heading'             => __( 'Related by Zipcode', 'connections' ),
			'script_handle'       => 'Connections_Directory/Block/Carousel/Script',
			'style_handle'        => 'Connections_Directory/Block/Carousel/Style',
		);

		parent::__construct( $id, $atts );

		$this->setProperties( $this->properties );
		$this->hooks();
	}

	/**
	 * Add hooks.
	 *
	 * @since 9.8
	 */
	private function hooks() {

		// Add the postal code to the Content Block heading.
		add_filter(
			'Connections_Directory/Entry/Related/Query_Parameters',
			function ( $queryParameters ) {

				if ( is_array( $queryParameters ) && array_key_exists( 'zip_code', $queryParameters ) ) {

					$this->set(
						'heading',
						/* translators: Postal code. */
						sprintf( __( 'Related by Zipcode - %s', 'connections' ), $queryParameters['zip_code'] )
					);
				}

				return $queryParameters;
			}
		);
	}
}
