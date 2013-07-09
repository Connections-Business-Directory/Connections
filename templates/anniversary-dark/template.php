<?php

/**
 * Template HTML Output.
 *
 * @package     Connections
 * @subpackage  Template HTML Output
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       unknown
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

?>

<span class="cn-entry-name" style=""><?php echo $entry->name; ?></span> <span class="cn-upcoming-date"><?php echo $entry->getUpcoming( $atts['list_type'], $atts['date_format'] ); ?></span>