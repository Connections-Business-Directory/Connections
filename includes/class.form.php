<?php

/**
 * Class for creating various form HTML elements.
 *
 * @todo This class is an absolute mess, clean and optimize.
 *
 * @package     Connections
 * @subpackage  HTML Form Elements
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       unknown
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Create custom HTML forms.
 */
class cnFormObjects {
	private $nonceBase = 'connections';
	private $validate;
	private $visibiltyOptions = array( 'Public'=>'public', 'Private'=>'private', 'Unlisted'=>'unlisted' );

	public function __construct() {
		// Load the validation class.
		$this->validate = new cnValidate();

		/*
		 * Create the visibility option array based on the current user capability.
		 */
		foreach ( $this->visibiltyOptions as $key => $option ) {
			if ( ! $this->validate->userPermitted( $option ) ) unset( $this->visibiltyOptions[$key] );
		}
	}

	/**
	 * The form open tag.
	 *
	 * @todo Finish adding form tag attributes.
	 * @param array
	 * @return string
	 */
	public function open( $attr ) {
		if ( isset( $attr['name'] ) ) $attr['name'] = 'name="' . $attr['name'] . '" ';
		if ( isset( $attr['action'] ) ) $attr['action'] = 'action="' . $attr['action'] . '" ';
		if ( isset( $attr['accept'] ) ) $attr['accept'] = 'accept="' . $attr['accept'] . '" ';
		if ( isset( $attr['accept-charset'] ) ) $attr['accept-charset'] = 'accept-charset="' . $attr['accept-charset'] . '" ';
		if ( isset( $attr['enctype'] ) ) $attr['enctype'] = 'enctype="' . $attr['enctype'] . '" ';
		if ( isset( $attr['method'] ) ) $attr['method'] = 'method="' . $attr['method'] . '" ';

		$out = '<form ';

		foreach ( $attr as $key => $value ) {
			$out .= $value;
		}

		echo $out , '>';
	}

	/**
	 *
	 *
	 * @return string HTML close tag
	 */
	public function close() {
		echo '</form>';
	}

	//Function inspired from:
	//http://www.melbournechapter.net/wordpress/programming-languages/php/cman/2006/06/16/php-form-input-and-cross-site-attacks/
	/**
	 * Creates a random token.
	 *
	 * @param string  $formId The form ID
	 *
	 * @return string
	 */
	public function token( $formId = NULL ) {
		$token = md5( uniqid( rand(), true ) );

		return $token;
	}

	/**
	 * Retrieves or displays the nonce field for forms using wp_nonce_field.
	 *
	 * @param string  $action  Action name.
	 * @param string  $item    [optional] Item name. Use when protecting multiple items on the same page.
	 * @param string  $name    [optional] Nonce name.
	 * @param bool    $referer [optional] Whether to set and display the refer field for validation.
	 * @param bool    $echo    [optional] Whether to display or return the hidden form field.
	 * @return string
	 */
	public function tokenField( $action, $item = FALSE, $name = '_cn_wpnonce', $referer = TRUE, $echo = TRUE ) {
		$name = esc_attr( $name );

		if ( $item == FALSE ) {

			$token = wp_nonce_field( $this->nonceBase . '_' . $action, $name, $referer, FALSE );

		} else {

			$token = wp_nonce_field( $this->nonceBase . '_' . $action . '_' . $item, $name, $referer, FALSE );
		}

		if ( $echo ) echo $token;

		// if ( $referer ) wp_referer_field( $echo, 'previous' );

		return $token;
	}

	/**
	 * Retrieves URL with nonce added to the query string.
	 *
	 * @param string  $actionURL URL to add the nonce to.
	 * @param string  $item      Nonce action name.
	 * @return string
	 */
	public function tokenURL( $actionURL, $item ) {

		return wp_nonce_url( $actionURL, $item );
	}

	/**
	 * Generate the complete nonce string, from the nonce base, the action and an item.
	 *
	 * @param string  $action Action name.
	 * @param string  $item   [optional] Item name. Use when protecting multiple items on the same page.
	 * @return string
	 */
	public function getNonce( $action, $item = FALSE ) {

		if ( $item == FALSE ) {

			$nonce = $this->nonceBase . '_' . $action;

		} else {

			$nonce = $this->nonceBase . '_' . $action . '_' . $item;
		}

		return $nonce;
	}

	/**
	 * Renders a select drop down.
	 *
	 * This is deprecated method, left in place for backward compatility only.
	 *
	 * @access private
	 * @deprecated
	 * @since 0.8
	 * @param string  $name    The input option id/name value.
	 * @param array   $options An associative array. Key is the option value and the value is the option name.
	 * @param string  $value   [optional] The selected option.
	 * @param string  $class   The class applied to the select.
	 * @param string  $id      UNUSED
	 *
	 * @return string
	 */
	public function buildSelect( $name, $options, $value = '', $class='', $id='' ) {

		$select = cnHTML::field(
			array(
				'type'     => 'select',
				'class'    => $class,
				'id'       => $name,
				'options'  => $options,
				'required' => FALSE,
				'label'    => '',
				'return'   => TRUE,
			),
			$value
		);

		return $select;
	}

	/**
	 * Renders a radio group.
	 *
	 * This is deprecated method, left in place for backward compatility only.
	 *
	 * @access private
	 * @deprecated
	 * @since 0.8
	 * @param string  $name    The input option id/name value.
	 * @param string  $id      UNUSED
	 * @param array   $options An associative array. Key is the option name and the value is the option value.
	 * @param string  $value   [optional] The selected option.
	 *
	 * @return string
	 */
	public function buildRadio( $name, $id, $options, $value = '' ) {

		$radio = cnHTML::field(
			array(
				'type'     => 'radio',
				'display'  => 'block',
				'class'    => '',
				'id'       => $name,
				'options'  => array_flip( $options ), // The options array is flipped to preserve backward compatibility.
				'required' => FALSE,
				'return'   => TRUE,
			),
			$value
		);

		return $radio;
	}

	/**
	 * Registers the entry edit form metaboxes.
	 *
	 * This is deprecated method, left in place for backward compatility only.
	 *
	 * NOTE: This should only be called by the "load-$page_hook" action.
	 *
	 * @access private
	 * @since 0.8
	 * @param string  $pageHook The page hook to add the entry edit metaboxes to.
	 * @return void
	 */
	public function registerEditMetaboxes( $pageHook ) {

		$metaboxes = cnMetaboxAPI::get();

		foreach ( $metaboxes as $metabox ) {

			$metabox['pages'] = array( $pageHook );

			cnMetaboxAPI::add( $metabox );
		}

		cnMetaboxAPI::register();
	}

	/**
	 * Renders the name metabox.
	 *
	 * This is deprecated method, left in place for backward compatility only.
	 *
	 * @access private
	 * @deprecated
	 * @since 0.8
	 * @param object   $entry An instrance of the cnEntry object.
	 */
	public function metaboxName( $entry ) {

		cnEntryMetabox::name( $entry, $metabox = array() );
	}

}


class cnCategoryObjects {
	private $rowClass = '';

	public function buildCategoryRow( $type, $parents, $level = 0, $selected = NULL ) {
		$out = NULL;

		foreach ( $parents as $child ) {
			$category = new cnCategory( $child );

			if ( $type === 'table' ) $out .= $this->buildTableRowHTML( $child, $level );
			if ( $type === 'option' ) $out .= $this->buildOptionRowHTML( $child, $level, $selected );
			if ( $type === 'checklist' ) $out .= $this->buildCheckListHTML( $child, $level, $selected );

			if ( is_array( $category->getChildren() ) ) {
				++$level;
				if ( $type === 'table' ) $out .= $this->buildCategoryRow( 'table', $category->getChildren(), $level );
				if ( $type === 'option' ) $out .= $this->buildCategoryRow( 'option', $category->getChildren(), $level, $selected );
				if ( $type === 'checklist' ) $out .= $this->buildCategoryRow( 'checklist', $category->getChildren(), $level, $selected );
				--$level;
			}

		}

		$level = 0;
		return $out;
	}

	private function buildTableRowHTML( $term, $level ) {
		global $connections;
		$form = new cnFormObjects();
		$category = new cnCategory( $term );
		$pad = str_repeat( '&#8212; ', max( 0, $level ) );
		$this->rowClass = 'alternate' == $this->rowClass ? '' : 'alternate';

		/*
		 * Genreate the edit & delete tokens.
		 */
		$editToken = $form->tokenURL( 'admin.php?page=connections_categories&cn-action=edit_category&id=' . $category->getId(), 'category_edit_' . $category->getId() );
		$deleteToken = $form->tokenURL( 'admin.php?cn-action=delete_category&id=' . $category->getId(), 'category_delete_' . $category->getId() );

		$out = '<tr id="cat-' . $category->getId() . '" class="' . $this->rowClass . '">';
		$out .= '<th class="check-column">';
		$out .= '<input type="checkbox" name="category[]" value="' . $category->getId() . '"/>';
		$out .= '</th>';
		$out .= '<td class="name column-name"><a class="row-title" href="' . $editToken . '">' . $pad . $category->getName() . '</a><br />';

		if ( $category->getSlug() !== 'uncategorized' || $category->getName() !== 'Uncategorized' ) {
			$out .= '<div class="row-actions">';
			$out .= '<span class="edit"><a href="' . $editToken . '">' . __( 'Edit', 'connections' ) . '</a> | </span>';
			$out .= '<span class="delete"><a onclick="return confirm(\'You are about to delete this category. \\\'Cancel\\\' to stop, \\\'OK\\\' to delete\');" href="' . $deleteToken . '">' . __( 'Delete', 'connections' ) . '</a></span>';
			$out .= '</div>';
		}

		$out .= '</td>';
		$out .= '<td class="description column-description">' . $category->getDescription() . '</td>';
		$out .= '<td class="slug column-slug">' . $category->getSlug() . '</td>';
		$out .= '<td>';
		/*
				 * Genreate the category link token URL.
				 */
		// $categoryFilterURL = $form->tokenURL( 'admin.php?connections_process=true&process=manage&action=filter&category_id=' . $category->getId(), 'filter' );
		$categoryFilterURL = $form->tokenURL( 'admin.php?cn-action=filter&category=' . $category->getId(), 'filter' );

		if ( (integer) $category->getCount() > 0 ) {
			$out .= '<strong>' . __( 'Count', 'connections' ) . ':</strong> ' . '<a href="' . $categoryFilterURL . '">' . $category->getCount() . '</a><br />';
		}
		else {
			$out .= '<strong>' . __( 'Count', 'connections' ) . ':</strong> ' . $category->getCount() . '<br />';
		}

		$out .= '<strong>' . __( 'ID', 'connections' ) . ':</strong> ' . $category->getId();
		$out .= '</td>';
		$out .= '</tr>';

		return $out;
	}

	private function buildOptionRowHTML( $term, $level, $selected ) {
		global $rowClass;
		$selectString = NULL;

		$category = new cnCategory( $term );
		$pad = str_repeat( '&nbsp;&nbsp;&nbsp;', max( 0, $level ) );
		if ( $selected == $category->getId() ) $selectString = ' SELECTED ';

		$out = '<option value="' . $category->getId() . '"' . $selectString . '>' . $pad . $category->getName() . '</option>';

		return $out;
	}

	private function buildCheckListHTML( $term, $level, $checked ) {
		global $rowClass;

		$category = new cnCategory( $term );
		$pad = str_repeat( '&nbsp;&nbsp;&nbsp;', max( 0, $level ) );

		if ( !empty( $checked ) ) {
			if ( in_array( $category->getId(), $checked ) ) {
				$checkString = ' CHECKED ';
			}
			else {
				$checkString = NULL;
			}
		}
		else {
			$checkString = NULL;
		}

		$out = '<li id="category-' . $category->getId() . '" class="category"><label class="selectit">' . $pad . '<input id="check-category-' . $category->getId() . '" type="checkbox" name="entry_category[]" value="' . $category->getId() . '" ' . $checkString . '> ' . $category->getName() . '</input></label></li>';

		return $out;
	}

	public function showForm( $data = NULL ) {
		global $connections;
		$form = new cnFormObjects();
		$category = new cnCategory( $data );
		$parent = new cnCategory( $connections->retrieve->category( $category->getParent() ) );
		$level = NULL;

		$out = '<div class="form-field form-required connectionsform">';
		$out .= '<label for="cat_name">' . __( 'Name', 'connections' ) . '</label>';
		$out .= '<input type="text" aria-required="true" size="40" value="' . $category->getName() . '" id="category_name" name="category_name"/>';
		$out .= '<input type="hidden" value="' . $category->getID() . '" id="category_id" name="category_id"/>';
		$out .= '<p>' . __( 'The name is how it appears on your site.', 'connections' ) . '</p>';
		$out .= '</div>';

		$out .= '<div class="form-field connectionsform">';
		$out .= '<label for="category_nicename">' . __( 'Slug', 'connections' ) . '</label>';
		$out .= '<input type="text" size="40" value="' . $category->getSlug() . '" id="category_slug" name="category_slug"/>';
		$out .= '<p>' . __( 'The “slug” is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.', 'connections' ) . '</p>';
		$out .= '</div>';

		$out .= '<div class="form-field connectionsform">';
		$out .= '<label for="category_parent">' . __( 'Parent', 'connections' ) . '</label>';
		$out .= '<select class="postform" id="category_parent" name="category_parent">';
		$out .= '<option value="0">' . __( 'None', 'connections' ) . '</option>';
		$out .= $this->buildCategoryRow( 'option', $connections->retrieve->categories(), $level, $parent->getID() );
		$out .= '</select>';
		$out .= '<p>' . __( 'Categories can have a hierarchy. You might have a Jazz category, and under that have children categories for Bebop and Big Band. Totally optional.', 'connections' ) . '</p>';
		$out .= '</div>';

		// $out .= '<div class="form-field connectionsform">';
		// $out .= '<label for="category_description">' . __( 'Description', 'connections' ) . '</label>';
		// $out .= '<textarea cols="40" rows="5" id="category_description" name="category_description">' . $category->getDescription() . '</textarea>';
		// $out .= '<p>' . __( 'The description is not displayed by default; however, templates may be created or altered to show it.', 'connections' ) . '</p>';
		// $out .= '</div>';

		ob_start();

		wp_editor( $category->getDescription(),
			'category_description',
			array(
				'media_buttons' => FALSE,
				'tinymce' => array(
					'editor_selector' => 'tinymce',
					'theme_advanced_buttons1' => 'bold, italic, underline, |, bullist, numlist, |, justifyleft, justifycenter, justifyright, |, link, unlink, |, pastetext, pasteword, removeformat, |, undo, redo',
					'theme_advanced_buttons2' => '',
					'inline_styles' => TRUE,
					'relative_urls' => FALSE,
					'remove_linebreaks' => FALSE,
					'plugins' => 'inlinepopups,spellchecker,tabfocus,paste,wordpress,wpdialogs'
				)
			)
		);

		$out .= ob_get_clean();

		echo $out;
	}
}
