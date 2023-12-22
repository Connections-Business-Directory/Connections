<?php

namespace Connections_Directory\Settings;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Connections_Directory\Utility\_escape;

/**
 * Class Tab
 *
 * @package Connections_Directory\Settings
 * @@author Steven A. Zahm
 * @since   8.30
 */
class Tab {

	/**
	 * @since 8.30
	 *
	 * @var string
	 */
	private $pluginID;

	/**
	 * @since 8.30
	 *
	 * @var string
	 */
	private $id;

	/**
	 * @since 8.30
	 *
	 * @var string
	 */
	private $pageHook;

	/**
	 * @since 8.30
	 *
	 * @var array
	 */
	private $options;

	/**
	 * @since 8.30
	 *
	 * @var Section[]
	 */
	private $sections = array();

	/**
	 * 8.30
	 *
	 * @var array
	 */
	private $fields = array();

	/**
	 * Settings constructor.
	 *
	 * @since 8.30
	 *
	 * @param string $id
	 * @param string $pageHook
	 * @param array  $options
	 * @param string $pluginID
	 */
	public function __construct( $id, $pageHook, $options, $pluginID = '' ) {

		$this->id       = $id;
		$this->pageHook = $pageHook;
		$this->pluginID = $pluginID;

		$defaults = array(
			// 'id'        => '',
			'position' => 10,
			'title'    => '',
			// 'page_hook' => '',
		);

		$this->options = wp_parse_args( $options, $defaults );

		$this->hooks();
	}

	/**
	 * @since 8.30
	 */
	public function hooks() {

		// Register the setting's sections.
		add_filter( 'cn_register_settings_sections', array( $this, 'registerSections' ) );

		// Register the section's fields.
		add_filter( 'cn_register_settings_fields', array( $this, 'registerFields' ) );
	}

	/**
	 * @since 8.30
	 *
	 * @param array $sections
	 *
	 * @return array
	 */
	public function registerSections( $sections ) {

		foreach ( $this->sections as $section ) {

			$sections[] = $section->toArray();
		}

		return $sections;
	}

	/**
	 * @since 8.30
	 *
	 * @param array $fields
	 *
	 * @return array
	 */
	public function registerFields( $fields ) {

		foreach ( $this->fields as $field ) {

			$fields[] = $field;
		}

		return $fields;
	}

	/**
	 * @since 8.30
	 *
	 * @param string $id
	 * @param array  $options
	 *
	 * @return Section
	 */
	public function addSection( $id, $options ) {

		$defaults = array(
			'position' => 10,
			'title'    => '',
			'desc'     => '',
		);

		$options = wp_parse_args( $options, $defaults );

		$options['tab'] = $this->id;

		if ( 0 < strlen( $options['desc'] ) ) {

			$options['callback'] = function () use ( $options ) {

				echo _escape::html( $options['desc'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			};
		}

		$section = new Section( $id, $this->pageHook, $options, $this->pluginID );

		$this->sections[] = $section;

		return $section;
	}

	/**
	 * @since 8.30
	 *
	 * @param string $id
	 * @param array  $field
	 *
	 * @return $this
	 */
	public function addField( $id, $field ) {

		$defaults = array(
			'position' => 10,
			'title'    => '',
			'desc'     => '',
			'help'     => '',
			'type'     => '',
			'section'  => 'default',
		);

		$field = wp_parse_args( $field, $defaults );

		$field['id']        = $id;
		$field['tab']       = $this->id;
		$field['page_hook'] = $this->pageHook;
		$field['plugin_id'] = $this->pluginID;

		$this->fields[] = $field;

		return $this;
	}

	/**
	 * @since 8.30
	 *
	 * @return array
	 */
	public function toArray() {

		return array(
			'id'        => $this->id,
			'position'  => $this->options['position'],
			'title'     => $this->options['title'],
			'page_hook' => $this->pageHook,
			'plugin_id' => $this->pluginID,
		);
	}
}
