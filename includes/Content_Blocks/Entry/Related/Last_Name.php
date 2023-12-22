<?php

namespace Connections_Directory\Content_Blocks\Entry\Related;

use Connections_Directory\Content_Blocks\Entry\Related;

/**
 * Class Last_Name
 *
 * @package Connections_Directory\Content_Blocks\Entry
 */
class Last_Name extends Related {

	/**
	 * @since 9.8
	 * @var string
	 */
	const ID = 'entry-related-last_name';

	/**
	 * @since 9.7
	 * @var array
	 */
	private $properties = array(
		'relation' => 'last_name',
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
			'name'                => __( 'Related Entries by Last Name', 'connections' ),
			'permission_callback' => '__return_true',
			'heading'             => __( 'Related by Last Name', 'connections' ),
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

		// Add the last name to the Content Block heading.
		add_filter(
			'Connections_Directory/Entry/Related/Query_Parameters',
			function ( $queryParameters ) {

				if ( is_array( $queryParameters ) && array_key_exists( 'last_name', $queryParameters ) ) {

					$this->set(
						'heading',
						/* translators: A surname, family name, or last name. */
						sprintf( __( 'Related by Last Name - %s', 'connections' ), $queryParameters['last_name'] )
					);
				}

				return $queryParameters;
			}
		);
	}
}
