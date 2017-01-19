<?php
/**
 * Contains the EventSubscriber interface.
 *
 * @author    Iron Bound Designs
 * @since     1.0
 * @license   AGPL
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\WPEvents;

/**
 * Interface EventSubscriber
 * @package IronBound\WPEvents
 */
interface EventSubscriber {

	/**
	 * Return a list of the events subscribed to.
	 *
	 * The array key is the event name. The value can be:
	 *
	 *      - The method name on this object.
	 *      - An array with the method name and priority.
	 *      - An array with the method name, priority, and accepted arguments number.
	 *
	 * For example:
	 *
	 *      - array( 'event.name' => 'method_name' )
	 *      - array( 'event.name' => array( 'method_name', 15 ) )
	 *      - array( 'event.name' => array( 'method_name', 15, 2 ) )
	 *
	 * @since 1.0
	 *
	 * @return array
	 */
	public static function get_subscribed_events();
}