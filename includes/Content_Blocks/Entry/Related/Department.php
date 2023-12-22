<?php

namespace Connections_Directory\Content_Blocks\Entry\Related;

use Connections_Directory\Content_Blocks\Entry\Related;

/**
 * Class Region
 *
 * @package Connections_Directory\Content_Blocks\Entry
 */
class Department extends Related {

	/**
	 * @since 9.8
	 * @var string
	 */
	const ID = 'entry-related-department';

	/**
	 * @since 9.7
	 * @var array
	 */
	private $properties = array(
		'relation' => 'department',
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
			'name'                => __( 'Related Entries by Department', 'connections' ),
			'permission_callback' => '__return_true',
			'heading'             => __( 'Related by Department', 'connections' ),
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

		// Add the department to the Content Block heading.
		add_filter(
			'Connections_Directory/Entry/Related/Query_Parameters',
			function ( $queryParameters ) {

				if ( is_array( $queryParameters ) && array_key_exists( 'department', $queryParameters ) ) {

					$this->set(
						'heading',
						/* translators: Department name; a department is one of the sections in an organization. */
						sprintf( __( 'Related by Department - %s', 'connections' ), $queryParameters['department'] )
					);
				}

				return $queryParameters;
			}
		);
	}
}
