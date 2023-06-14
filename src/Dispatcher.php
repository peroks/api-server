<?php namespace Peroks\ApiServer;

use Psr\EventDispatcher\StoppableEventInterface;

/**
 * A PSR-14 listener provider and event dispatcher.
 *
 * @author Per Egil Roksvaag
 * @copyright Per Egil Roksvaag
 * @license MIT License
 */
class Dispatcher implements DispatcherInterface {

	/**
	 * @var string[] An array of event types currently being processed.
	 */
	protected array $processing = [];

	/**
	 * @var Server The Api Server.
	 */
	protected Server $server;

	/**
	 * Constructor.
	 *
	 * @param Server $server The Api Server.
	 */
	public function __construct( Server $server ) {
		$this->server = $server;
	}

	/**
	 * @param object $event An event for which to return the relevant listeners.
	 *
	 * @return iterable[callable] An iterable of callable event listeners.
	 */
	public function getListenersForEvent( object $event ): iterable {
		foreach ( $this->server->registry->getTypeListeners( $event->type ) as $listener ) {
			yield $listener->id => $listener->callback;
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
		$this->processing[] = $event->type;

		foreach ( $this->getListenersForEvent( $event ) as $callback ) {
			if ( $event instanceof StoppableEventInterface && $event->isPropagationStopped() ) {
				break;
			}
			call_user_func( $callback, $event );
		}

		array_pop( $this->processing );
		return $event;
	}

	/**
	 * Checks if an event type is currently being processed.
	 *
	 * @param string $type The event type to check for.
	 *
	 * @return bool True if the event type is being processed, false otherwise.
	 */
	public function isProcessing( string $type ): bool {
		return in_array( $type, $this->processing, true );
	}
}
