<?php
/**
 * The Publish entry metabox.
 *
 * @since 10.4.27
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2022, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

namespace Connections_Directory\Metabox\Entry;

use cnMetaboxAPI;
use cnOptions;
use cnSettingsAPI;
use Connections_Directory\Form\Field;
use Connections_Directory\Metabox;
use Connections_Directory\Request;
use Connections_Directory\Utility\_array;
use Connections_Directory\Utility\_parse;

/**
 * Class Publish
 *
 * @package Connections_Directory\Metabox\Entry
 */
final class Publish extends Metabox {

	/**
	 * Metabox id/key.
	 *
	 * @since 10.4.27
	 * @var string
	 */
	protected $id = 'submitdiv';

	/**
	 * Register the metabox.
	 *
	 * @since 10.4.27
	 */
	public static function register() {

		$self = new self();

		cnMetaboxAPI::add(
			array(
				'id'       => $self->getId(),
				'title'    => $self->getTitle(),
				'pages'    => $self->getPageHooks(),
				'context'  => $self->context,
				'priority' => $self->priority,
				'callback' => array( $self, 'renderCallback' ),
			)
		);
	}

	/**
	 * Generate the HTML for the Publish metabox.
	 *
	 * @internal
	 * @since 0.8
	 *
	 * @global string $plugin_page
	 *
	 * @return string
	 */
	public function getHTML() {

		global $plugin_page;

		$defaults = array(
			'action'     => Request\Admin_Action::input()->value(),
			'entry_type' => cnOptions::getEntryTypes(),
			'default'    => array(
				'type'       => 'individual',
				'visibility' => 'public',
			),
		);

		$html = '';

		$type          = cnSettingsAPI::get( 'connections', 'fieldset-publish', 'entry-type' );
		$defaultType   = cnSettingsAPI::get( 'connections', 'fieldset-publish', 'default-entry-type' );
		$defaultStatus = cnSettingsAPI::get( 'connections', 'fieldset-publish', 'default-publish-status' );
		$activeTypes   = _array::get( $type, 'active', array( $defaultType ) );

		// Reorder the based on the user defined settings.
		$defaults['entry_type'] = array_replace( array_flip( $type['order'] ), $defaults['entry_type'] );

		// Remove the disabled entry types based on the user defined settings.
		$defaults['entry_type'] = array_intersect_key( $defaults['entry_type'], array_flip( $activeTypes ) );

		// The options need to be flipped because of an earlier poor decision
		// of setting the array keys the option labels. This provides backward compatibility.
		$defaults['entry_type'] = array_flip( $defaults['entry_type'] );

		$defaults['default']['type']       = $defaultType;
		$defaults['default']['visibility'] = $defaultStatus;

		// // Do not use the `cn_admin_metabox_publish_atts` filter. Let in for backward compatibility for version prior to 0.8.
		// $defaults = wp_parse_args( apply_filters( 'cn_admin_metabox_publish_atts', $atts ), $defaults );

		$atts = _parse::parameters(
			apply_filters( 'Connections_Directory/Metabox/Publish/Parameters', $this->shortcodeAttributes ),
			apply_filters( 'Connections_Directory/Metabox/Publish/Defaults', $defaults ),
			true,
			true,
			array( 'entry_type' )
		);

		$action = $atts['action'];

		$visibility = $this->entry->getId() ? $this->entry->getVisibility() : $atts['default']['visibility'];
		$type       = $this->entry->getId() ? $this->entry->getEntryType() : $atts['default']['type'];

		if ( ! empty( $atts['entry_type'] ) ) {

			$fieldEntryType = Field\Radio_Group::create()
											   ->setContainer( 'div' )
											   ->setPrefix( 'cn' )
											   ->setId( 'entry_type' )
											   ->addClass( 'radio-option' )
											   ->setName( 'entry_type' )
											   ->setValue( $type )
											   ->prepend( '<div id="entry-type">' )
											   ->append( '</div>' );

			/*
			 * The input options need to be flipped because of an earlier poor decision
			 * of setting the array keys the option labels. This provides backward compatibility.
			 */
			foreach ( array_flip( $atts['entry_type'] ) as $entryTypeSlug => $entryTypeLabel ) {

				$fieldEntryType->addInput(
					Field\Radio::create(
						array(
							'id'    => "entry_type[{$entryTypeSlug}]",
							'label' => $entryTypeLabel,
							'value' => $entryTypeSlug,
						)
					)
				);
			}

		} else {

			$fieldEntryType = Field\Hidden::create()
										  ->setId( 'cn-entry_type' )
										  ->setName( 'entry_type' )
										  ->setValue( $type );
		}

		$html .= $fieldEntryType->getHTML();

		$fieldVisibility = Field\Radio_Group::create()
											->setContainer( 'div' )
											->setPrefix( 'cn' )
											->setId( 'visibility' )
											->addClass( 'radio-option' )
											->setName( 'visibility' )
											->setValue( $visibility )
											->prepend( '<div id="visibility">' )
											->append( '</div>' );

		$fieldVisibility->addInput(
			Field\Radio::create(
				array(
					'id'    => 'visibility[public]',
					'label' => __( 'Public', 'connections' ),
					'value' => 'public',
				)
			)
		);

		$fieldVisibility->addInput(
			Field\Radio::create(
				array(
					'id'    => 'visibility[private]',
					'label' => __( 'Private', 'connections' ),
					'value' => 'private',
				)
			)
		);

		$fieldVisibility->addInput(
			Field\Radio::create(
				array(
					'id'    => 'visibility[unlisted]',
					'label' => __( 'Unlisted', 'connections' ),
					'value' => 'unlisted',
				)
			)
		);

		$html .= $fieldVisibility->getHTML();

		// Create URL to current admin page.
		$adminURL = admin_url( 'admin.php', ( is_ssl() ? 'https' : 'http' ) );
		$adminURL = add_query_arg( array( 'page' => $plugin_page ), $adminURL );

		$html .= '<div id="minor-publishing"></div>';

		$html .= '<div id="major-publishing-actions">';

		switch ( true ) {

			case ( 'edit_entry' == $action || 'edit' == $action ):
				$html .= '<input type="hidden" name="cn-action" value="update_entry"/>';
				$html .= '<div id="cancel-button"><a href="' . esc_url( $adminURL ) . '" class="button cn-button cn-button-warning">' . esc_html__( 'Cancel', 'connections' ) . '</a></div>';
				$html .= '<div id="publishing-action"><input  class="button-primary" type="submit" name="update" value="' . esc_attr__( 'Update', 'connections' ) . '" /></div>';

				break;

			case ( 'copy_entry' == $action || 'copy' == $action ):
				$html .= '<input type="hidden" name="cn-action" value="duplicate_entry"/>';
				$html .= '<div id="cancel-button"><a href="' . esc_url( $adminURL ) . '" class="button cn-button cn-button-warning">' . esc_html__( 'Cancel', 'connections' ) . '</a>';
				$html .= '</div><div id="publishing-action"><input class="button-primary" type="submit" name="save" value="' . esc_attr__( 'Add Entry', 'connections' ) . '" /></div>';

				break;

			default:
				$html .= '<input type="hidden" name="cn-action" value="add_entry"/>';
				$html .= '<div id="publishing-action"><input class="button-primary" type="submit" name="save" value="' . esc_attr__( 'Add Entry', 'connections' ) . '" /></div>';

				break;
		}

		$html .= '<div class="clear"></div>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Get the metabox title.
	 *
	 * @since 10.4.27
	 * @return string
	 */
	public function getTitle() {

		return __( 'Publish', 'connections' );
	}
}
