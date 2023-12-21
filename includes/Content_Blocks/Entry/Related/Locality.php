<?php

namespace Connections_Directory\Content_Blocks\Entry\Related;

use Connections_Directory\Content_Blocks\Entry\Related;

/**
 * Class Locality
 *
 * @package Connections_Directory\Content_Blocks\Entry
 */
class Locality extends Related {

	/**
	 * @since 9.8
	 * @var string
	 */
	const ID = 'entry-related-locality';

	/**
	 * @since 9.7
	 * @var array
	 */
	private $properties = array(
		'relation' => 'locality',
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
			'name'                => __( 'Related Entries by City', 'connections' ),
			'permission_callback' => '__return_true',
			'heading'             => __( 'Related by City', 'connections' ),
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

		// Add the locality to the Content Block heading.
		add_filter(
			'Connections_Directory/Entry/Related/Query_Parameters',
			function ( $queryParameters ) {

				if ( is_array( $queryParameters ) && array_key_exists( 'city', $queryParameters ) ) {

					$this->set(
						'heading',
						/* translators: City name. */
						sprintf( __( 'Related by City - %s', 'connections' ), $queryParameters['city'] )
					);
				}

				return $queryParameters;
			}
		);
	}
}
