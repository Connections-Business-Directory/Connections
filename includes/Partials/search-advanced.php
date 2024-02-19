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

use Connections_Directory\Form;

Form\Search::create( $atts )->render();
