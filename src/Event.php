<?php namespace Peroks\ApiServer;

use Psr\EventDispatcher\StoppableEventInterface;

/**
 * A PSR-14 stoppable event.
 *
 * @author Per Egil Roksvaag
 * @copyright Per Egil Roksvaag
 * @license MIT License
 */
class Event implements StoppableEventInterface {

	/**
	 * @var string The event type.
	 */
	public string $type;

	/**
	 * @var mixed The event data.
	 */
	public mixed $data;

	/**
	 * @var bool Whether the event propagation as stopped or not.
	 */
	protected bool $stopped = false;

	/**
	 * Constructor.
	 *
	 * @param string $type The event type.
	 * @param mixed $data The event data.
	 */
	public function __construct( string $type, mixed $data = null ) {
		$this->type = $type;
		$this->data = $data ?? (object) [];
	}

	/**
	 * Stops event propagation.
	 */
	public function stopPropagation(): void {
		$this->stopped = true;
	}

	/**
	 * Checks if propagation should stop.
	 *
	 * @return bool True if no further listeners should be called or false otherwise.
	 */
	public function isPropagationStopped(): bool {
		return $this->stopped;
	}
}
