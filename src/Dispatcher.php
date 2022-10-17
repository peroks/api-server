<?php namespace Peroks\ApiServer;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

/**
 * A PSR-14 listener provider and event dispatcher.
 *
 * @author Per Egil Roksvaag
 * @copyright Per Egil Roksvaag
 * @license MIT License
 */
class Dispatcher implements EventDispatcherInterface, ListenerProviderInterface {

	/**
	 * @var Registry A container for registered event listeners.
	 */
	protected Registry $registry;

	/**
	 * Constructor.
	 *
	 * @param Registry $registry A container for registered event listeners.
	 */
	public function __construct( Registry $registry ) {
		$this->registry = $registry;
	}

	/**
	 * @param object $event An event for which to return the relevant listeners.
	 *
	 * @return iterable[callable] An iterable of callable event listeners.
	 */
	public function getListenersForEvent( object $event ): iterable {
		$listeners = $this->registry->getListeners();

		foreach ( $listeners as $listener ) {
			if ( $listener->type === $event->type ) {
				yield $listener->callback;
			}
		}
	}

	/**
	 * Dispatches the given event to all registered event listeners for processing.
	 *
	 * @param object $event The event to process.
	 *
	 * @return object The given event, now possibly modified by listeners.
	 */
	public function dispatch( object $event ): object {
		foreach ( $this->getListenersForEvent( $event ) as $callback ) {
			if ( $event->isPropagationStopped() ) {
				break;
			}
			call_user_func( $callback, $event );
		}
		return $event;
	}
}
