<?php

namespace Connections_Directory\Content_Blocks\Entry\Related;

use Connections_Directory\Content_Blocks\Entry\Related;

/**
 * Organization Region
 *
 * @package Connections_Directory\Content_Blocks\Entry
 */
class Organization extends Related {

	/**
	 * @since 9.8
	 * @var string
	 */
	const ID = 'entry-related-organization';

	/**
	 * @since 9.7
	 * @var array
	 */
	private $properties = array(
		'relation' => 'organization',
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
			'name'                => __( 'Related Entries by Organization', 'connections' ),
			'permission_callback' => '__return_true',
			'heading'             => __( 'Related by Organization', 'connections' ),
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

		// Add the organization to the Content Block heading.
		add_filter(
			'Connections_Directory/Entry/Related/Query_Parameters',
			function ( $queryParameters ) {

				if ( is_array( $queryParameters ) && array_key_exists( 'organization', $queryParameters ) ) {

					$this->set(
						'heading',
						/* translators: Organization name; an organized body of people with a particular purpose, a business. */
						sprintf( __( 'Related by Organization - %s', 'connections' ), $queryParameters['organization'] )
					);
				}

				return $queryParameters;
			}
		);
	}
}
