<?php

namespace Connections_Directory\Integration;

use Connections_Directory\Taxonomy\Registry;

/**
 * Class Gravity_Forms
 *
 * @package Connections_Directory\Integration
 */
final class Gravity_Forms {

	/**
	 * Stores the instance of this class.
	 *
	 * @since 10.2
	 *
	 * @var static
	 */
	private static $instance;

	/**
	 * @since 10.2
	 */
	public function __construct() {
		/* Do nothing here */
	}

	/**
	 * Callback for the `plugins_loaded` action.
	 *
	 * Action is run at priority 11 because Gravity Forms inits addons at priority 10.
	 *
	 * @since 10.2
	 *
	 * @return static
	 */
	public static function init() {

		if ( ! isset( self::$instance ) &&
			 ! ( self::$instance instanceof self ) &&
			 method_exists( 'GF_Fields', 'register' ) &&
			 class_exists( '\Connections_Directory\Connector\Gravity_Forms\Field\Taxonomy', false )
		) {

			self::$instance = $self = new self();

			$self->hooks();
		}

		return self::$instance;
	}

	private function hooks() {

		add_action( 'setup_theme', array( $this, 'registerTaxonomyFields' ) );
	}

	/**
	 * Register the Connections Categories field with Gravity Forms.
	 *
	 * @internal
	 * @since 10.2
	 * @noinspection PhpUndefinedClassInspection
	 * @noinspection PhpUndefinedNamespaceInspection
	 */
	public function registerTaxonomyFields() {

		$taxonomies = Registry::get()->getTaxonomies();

		foreach ( $taxonomies as $taxonomy ) {

			if ( true !== $taxonomy->isPublic() ) {
				continue;
			}

			try {

				$atts = array(
					'labels' => array(
						'name'          => $taxonomy->getLabels()->name,
						'all_items'     => $taxonomy->getLabels()->all_items,
						'select_items'  => $taxonomy->getLabels()->select_items,
						'singular_name' => $taxonomy->getLabels()->singular_name,
						'field_label'   => $taxonomy->getLabels()->name,
					),
				);

				\GF_Fields::register( new \Connections_Directory\Connector\Gravity_Forms\Field\Taxonomy( $taxonomy->getSlug(), $atts ) );

			} catch ( \Exception $e ) {
				/* Do nothing here */
			}
		}
	}
}
