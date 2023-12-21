<?php

namespace Connections_Directory\Settings;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Tab
 *
 * @package Connections_Directory\Settings
 * @@author Steven A. Zahm
 * @since   8.30
 */
class Section {

	/**
	 * @since 8.30
	 * @var string
	 */
	private $pluginID;

	/**
	 * @since 8.30
	 * @var string
	 */
	private $id;

	/**
	 * @since 8.30
	 * @var string
	 */
	private $pageHook;

	/**
	 * @since 8.30
	 * @var array
	 */
	private $options;

	/**
	 * 8.30
	 * @var
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
			'position' => 10,
			'title'    => '',
			'desc'     => '',
			'tab'      => '',
		);

		$this->options = wp_parse_args( $options, $defaults );

		if ( 0 < strlen( $options['desc'] ) ) {

			$options['callback'] = function () use ( $options ) {

				echo esc_html( $options['desc'] );
			};
		}

		$this->hooks();
	}

	/**
	 * @since 8.30
	 */
	public function hooks() {

		// Register the section's fields.
		add_filter( 'cn_register_settings_fields', array( $this, 'registerFields' ) );
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
		);

		$field = wp_parse_args( $field, $defaults );

		$field['id']        = $id;
		$field['tab']       = $this->options['tab'];
		$field['section']   = $this->id;
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
			'callback'  => $this->options['callback'],
			'tab'       => $this->options['tab'],
			'page_hook' => $this->pageHook,
			'plugin_id' => $this->pluginID,
		);
	}
}
