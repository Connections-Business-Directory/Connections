<?php
/**
 * The search template partial.
 *
 * @since 10.4.40
 *
 * @category   WordPress\Plugin
 * @package    Connections Business Directory
 * @subpackage Connections\
 * @author     Steven A. Zahm
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2022, Steven A. Zahm
 * @link       https://connections-pro.com/
 */

use Connections_Directory\Form\Field;
use Connections_Directory\Request;

$searchValue = Request\Entry_Search_Term::input()->value();

Field\Search::create()
			->setName( 'cn-s' )
			->setValue( $searchValue )
			->addAttribute( 'placeholder', esc_attr_x( 'Search &hellip;', 'placeholder', 'connections' ) )
			->addLabel(
				Field\Label::create()
						   ->text( '<span class="screen-reader-text">' . esc_html_x( 'Search for:', 'label', 'connections' ) . '</span>' ),
				'implicit'
			)
			->render();
Field\Submit::create()
			->addClass( 'search-submit' )
			->setValue( esc_attr_x( 'Search', 'submit button', 'connections' ) )
			->render();
