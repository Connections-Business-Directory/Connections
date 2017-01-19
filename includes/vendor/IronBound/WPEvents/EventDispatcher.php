<?php
/**
 * Contains the EventDispatcher class.
 *
 * @author    Iron Bound Designs
 * @since     1.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\WPEvents;

use IronBound\WPEvents\Exception\InvalidListenerException;

/**
 * Class EventDispatcher
 * @package IronBound\WPEvents
 */
class EventDispatcher {

	const PRIORITY = 10;
	const ACCEPTED = 3;

	/**
	 * @var string
	 */
	protected $prefix;

	/**
	 * EventDispatcher constructor.
	 *
	 * @param string $prefix Optionally prefix all event names fired via dispatch() with the given string.
	 */
	public function __construct( $prefix = '' ) {
		$this->prefix = $prefix;
	}

	/**
	 * Dispatch an event.
	 *
	 * @since 1.0
	 *
	 * @param string     $event_name The event name. Will be prefixed with the given prefix.
	 * @param Event|null $event      The event object passed to the listeners.
	 *
	 * @return Event
	 */
	public function dispatch( $event_name, Event $event = null ) {

		if ( is_null( $event ) ) {
			$event = new Event();
		}

		do_action( $this->get_action_name_for_event( $event_name ), $event, $event_name, $this );

		return $event;
	}

	/**
	 * Filter a value.
	 *
	 * Calls each listeners for the given event name. The value will be passed to the listener as
	 * the first parameter, the Event object as the second, the un-prefixed event name as the third parameter,
	 * and `$this` as the fourth parameter.
	 *
	 * Each listeners should return the first argument, whether or not it modified its value.
	 *
	 * @since 1.0
	 *
	 * @param string             $event_name     The event name. Will be prefixed with the given prefix.
	 * @param mixed|GenericEvent $event_or_value Either the value to be filtered, or a GenericEvent object.
	 *                                           If a GenericEvent object, the GenericEvent will be used as the Event
	 *                                           object and GenericEvent::get_subject() will be called for the value.
	 * @param Event|null         $event          The event object passed to the listeners.
	 *
	 * @return mixed The filtered value.
	 */
	public function filter( $event_name, $event_or_value, Event $event = null ) {

		if ( $event_or_value instanceof GenericEvent ) {
			$value = $event_or_value->get_subject();
			$event = $event_or_value;
		} else {
			$value = $event_or_value;
		}

		if ( is_null( $event ) ) {
			$event = new Event();
		}

		return apply_filters( $this->get_action_name_for_event( $event_name ), $value, $event, $event_name, $this );
	}

	/**
	 * Get the action name for an event.
	 *
	 * @since 1.0
	 *
	 * @param string $event_name
	 *
	 * @return string
	 */
	protected function get_action_name_for_event( $event_name ) {
		return $this->prefix . $event_name;
	}

	/**
	 * Add an event listener that listens for a given event.
	 *
	 * @since 1.0
	 *
	 * @param string   $event_name    The fully-qualified event name to listen for.
	 * @param callable $listener      Listener to be called whenever the event occurs.
	 * @param int      $priority      The priority of the listener. Lower priority listeners are called earlier.
	 * @param int      $accepted_args Number of arguments the listener accepts. Defaults to 3.
	 *
	 * @return self
	 */
	public function add_listener( $event_name, $listener, $priority = self::PRIORITY, $accepted_args = self::ACCEPTED ) {

		if ( ! is_callable( $listener, true ) ) {
			throw new InvalidListenerException( '$listener must be callable.' );
		}

		if ( ! is_int( $priority ) ) {
			throw new \InvalidArgumentException( '$priority must be an int.' );
		}

		if ( ! is_int( $accepted_args ) ) {
			throw new \InvalidArgumentException( '$accepted must be an int.' );
		}

		add_filter( $event_name, $listener, $priority, $accepted_args );

		return $this;
	}

	/**
	 * Remove an event listener for a given event.
	 *
	 * @since 1.0
	 *
	 * @param string   $event_name The fully-qualified event name to remove a listener from.
	 * @param callable $listener   The listener to be removed.
	 * @param int      $priority   The priority of the listener.
	 *
	 * @return self
	 */
	public function remove_listener( $event_name, $listener, $priority = self::PRIORITY ) {

		if ( ! is_callable( $listener, true ) ) {
			throw new InvalidListenerException( '$listener must be callable.' );
		}

		if ( ! is_int( $priority ) ) {
			throw new \InvalidArgumentException( '$priority must be an int.' );
		}

		remove_filter( $event_name, $listener, $priority );

		return $this;
	}

	/**
	 * Add an event subscriber.
	 *
	 * This iterates over all of the subscribers events, and adds them as listeners.
	 *
	 * @since 1.0
	 *
	 * @param EventSubscriber $subscriber
	 *
	 * @return self
	 */
	public function add_subscriber( EventSubscriber $subscriber ) {

		foreach ( $subscriber->get_subscribed_events() as $event => $params ) {

			if ( is_string( $params ) ) {
				$this->add_listener( $event, array( $subscriber, $params ) );
			} elseif ( is_array( $params ) && isset( $params[0] ) ) {

				$method        = $params[0];
				$priority      = isset( $params[1] ) ? $params[1] : self::PRIORITY;
				$accepted_args = isset( $params[2] ) ? $params[2] : self::ACCEPTED;

				$this->add_listener( $event, array( $subscriber, $method ), $priority, $accepted_args );
			} else {
				throw new \InvalidArgumentException( 'Invalid return format from EventSubscriber.' );
			}
		}

		return $this;
	}

	/**
	 * Remove an event subscriber.
	 *
	 * This iterates over all of the subscribers events, and removes them as listeners.
	 *
	 * @since 1.0
	 *
	 * @param EventSubscriber $subscriber
	 *
	 * @return self
	 */
	public function remove_subscriber( EventSubscriber $subscriber ) {

		foreach ( $subscriber->get_subscribed_events() as $event => $params ) {

			if ( is_string( $params ) ) {
				$this->add_listener( $event, $params );
			} elseif ( is_array( $params ) && isset( $params[0] ) ) {

				$method   = $params[0];
				$priority = isset( $params[1] ) ? $params[1] : self::PRIORITY;

				$this->remove_listener( $event, array( $subscriber, $method ), $priority );
			} else {
				throw new \InvalidArgumentException( 'Invalid return format from EventSubscriber.' );
			}
		}

		return $this;
	}

	/**
	 * Check if an event has any listeners.
	 *
	 * @since 1.0
	 *
	 * @param string $event_name The fully-qualified event name to check against.
	 *
	 * @return bool
	 */
	public function has_listeners( $event_name ) {
		return has_filter( $event_name );
	}

	/**
	 * Get the listener priority for a given event.
	 *
	 * Lower priority listeners are evaluated earlier.
	 *
	 * @since 1.0
	 *
	 * @param string   $event_name The fully-qualified event name.
	 * @param callable $listener   The listener to check against.
	 *
	 * @return int|null
	 */
	public function get_listener_priority( $event_name, $listener ) {

		if ( ! is_callable( $listener, true ) ) {
			throw new InvalidListenerException( '$listener must be callable.' );
		}

		$priority = has_filter( $event_name, $listener );

		if ( false === $priority ) {
			return null;
		}

		return $priority;
	}

	/**
	 * Get the name of the current event being dispatched.
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	public function current_event() {
		return current_filter();
	}

	/**
	 * Determine if a given event is currently being processed.
	 *
	 * This will return true for nested events as well.
	 *
	 * For example:
	 *
	 *      dispatch( 'book.created' )
	 *        ↳ dispatch( 'author.created' )
	 *
	 *      doing_event( 'book.created' )   -> true
	 *      doing_event( 'author.created' ) -> true
	 *
	 * @since 1.0
	 *
	 * @param string $event_name
	 *
	 * @return bool
	 */
	public function doing_event( $event_name ) {
		return doing_filter( $event_name );
	}
}