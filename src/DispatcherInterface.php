<?php declare( strict_types = 1 ); namespace Peroks\ApiServer;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

/**
 * A PSR-14 listener provider and event dispatcher.
 *
 * @author Per Egil Roksvaag
 * @copyright Per Egil Roksvaag
 * @license MIT License
 */
interface DispatcherInterface extends EventDispatcherInterface, ListenerProviderInterface {

	/**
	 * Checks if an event type is currently being processed.
	 *
	 * @param string $type The event type to check for.
	 *
	 * @return bool True if the event type is being processed, false otherwise.
	 */
	public function isProcessing( string $type ): bool;
}

